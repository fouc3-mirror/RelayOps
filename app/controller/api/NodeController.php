<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Node;
use app\model\User;
use app\model\UserNode;
use app\model\TrafficLog;
use think\facade\Db;
use think\Response;

/**
 * FRPS 节点通信控制器
 *
 * 所有接口由 FRPS 节点通过 HTTP 调用，需 Bearer token 鉴权
 */
class NodeController extends BaseController
{
    /**
     * GET /api/node/user?token=xxx
     *
     * 校验用户 token，返回用户信息
     *
     * @return Response
     */
    public function user(): Response
    {
        $token = trim($this->request->param('token', ''));

        if (empty($token)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        // 在 RO_client 表中查找 token 对应的客户端记录
        $client = UserNode::where('token', $token)->find();

        if (!$client) {
            return json(['code' => 0, 'msg' => 'token 无效']);
        }

        // 检测流量是否耗尽
        $trafficLimit    = (int) ($client->traffic_limit ?? 0);
        $trafficUsed     = (int) ($client->traffic_used ?? 0);
        $trafficExhausted = ($trafficLimit > 0 && $trafficUsed >= $trafficLimit);

        // 流量耗尽时自动停用隧道
        if ($trafficExhausted && $client->status === 1) {
            $client->save(['status' => 0]);
            $client->status = 0;
        }

        // 检查隧道是否过期
        if ($client->status === 2 || ($client->expire_time > 0 && $client->expire_time < time())) {
            // 自动标记为过期
            if ($client->status !== 2) {
                $client->save(['status' => 2]);
            }
            return json(['code' => 0, 'msg' => '隧道已过期']);
        }

        if ($client->status !== 1) {
            return json(['code' => 0, 'msg' => '隧道未启用']);
        }

        // 查找用户
        $user = User::find($client->user_id);

        if (!$user || $user->status !== 1) {
            return json(['code' => 0, 'msg' => '用户不存在或已禁用']);
        }

        return json([
            'code' => 0,
            'msg'  => 'success',
            'data' => [
                'user_id'           => (int) $user->id,
                'username'          => $user->username,
                'status'            => (int) $user->status,
                'bandwidth_limit'   => (int) ($user->bandwidth_limit ?: 0),
                'expire_time'       => (int) ($client->expire_time ?: 0),
                'client_id'         => (int) $client->id,
                'enabled'           => ($client->status === 1 && $user->status === 1),
                'traffic_exhausted' => $trafficExhausted,
                'traffic_limit'     => $trafficLimit,
                'traffic_used'      => $trafficUsed,
            ],
        ]);
    }

    /**
     * GET /api/node/can-create-proxy
     *
     * 检查当前节点是否允许新建隧道
     *
     * @return Response
     */
    public function canCreateProxy(): Response
    {
        /** @var Node $node */
        $node = $this->request->node;

        $allowed = $node->canCreateProxy();

        return json([
            'code' => 0,
            'msg'  => 'success',
            'data' => [
                'allowed'        => $allowed,
                'reason'         => $allowed ? '' : ($node->status !== 1 ? '节点已禁用' : ($node->getData('allow_create_proxy') === 0 ? '全局新建通道已关闭' : '已达最大连接数')),
                'max_pool_count' => (int) $node->max_pool_count,
                'active_count'   => $node->activeClientCount(),
            ],
        ]);
    }

    /**
     * POST /api/node/traffic
     *
     * 接收节点上报的批量流量数据，写入 traffic_log 表
     *
     * 请求体格式：
     * {
     *     "items": [
     *         {"user_id": 1, "proxy_name": "tcp_8080", "in": 1024, "out": 2048},
     *         {"user_id": 2, "proxy_name": "http_80", "in": 512, "out": 256}
     *     ]
     * }
     *
     * @return Response
     */
    public function traffic(): Response
    {
        /** @var Node $node */
        $node  = $this->request->node;
        $items = $this->request->param('items', []);

        if (empty($items) || !is_array($items)) {
            return json(['code' => 0, 'msg' => '请求数据为空']);
        }

        // 限制单次批量大小，防止恶意超大请求
        if (count($items) > 500) {
            return json(['code' => 0, 'msg' => '单次提交数据不能超过 500 条']);
        }

        // 事务写入流量明细
        Db::startTrans();
        try {
            $insertData = [];
            $now        = time();

            foreach ($items as $item) {
                $userId    = (int) ($item['user_id'] ?? 0);
                $proxyName = $item['proxy_name'] ?? '';
                $inBytes   = (int) ($item['in'] ?? $item['in_bytes'] ?? 0);
                $outBytes  = (int) ($item['out'] ?? $item['out_bytes'] ?? 0);

                if ($userId <= 0 || empty($proxyName)) {
                    continue;
                }

                // 取整到分钟的时间戳，用于聚合
                $recordTime = $now - ($now % 60);

                $totalBytes   = $inBytes + $outBytes;
                $insertData[] = [
                    'node_id'     => $node->id,
                    'user_id'     => $userId,
                    'proxy_name'  => $proxyName,
                    'in_bytes'    => $inBytes,
                    'out_bytes'   => $outBytes,
                    'record_time' => $recordTime,
                    'create_time' => $now,
                ];

                // 扣除对应 client 的流量
                if ($totalBytes > 0) {
                    $client = UserNode::where('user_id', $userId)
                        ->where('node_id', $node->id)
                        ->where('proxy_name', $proxyName)
                        ->where('status', 1)
                        ->find();
                    if ($client) {
                        Db::name('client')
                            ->where('id', $client->id)
                            ->where('status', 1)
                            ->inc('traffic_used', $totalBytes)
                            ->update();

                        // 流量耗尽：停用隧道 + 禁用用户
                        $trafficLimit    = (int) ($client->traffic_limit ?? 0);
                        $trafficUsedAfter = (int) ($client->traffic_used ?? 0) + $totalBytes;
                        if ($trafficLimit > 0 && $trafficUsedAfter >= $trafficLimit) {
                            Db::name('client')
                                ->where('id', $client->id)
                                ->update(['status' => 0, 'update_time' => time()]);

                            $user = User::find($userId);
                            if ($user && $user->status === 1) {
                                $user->save(['status' => 0]);
                            }
                        }
                    }
                }
            }

            if (empty($insertData)) {
                Db::commit();
                return json(['code' => 0, 'msg' => '无有效数据']);
            }

            // 批量插入
            (new TrafficLog())->saveAll($insertData);

            Db::commit();

            return json([
                'code' => 0,
                'msg'  => 'success',
                'data' => [
                    'count' => count($insertData),
                ],
            ]);
        } catch (\Throwable $e) {
            Db::rollback();
            return json(['code' => 1, 'msg' => '流量数据写入失败：' . $e->getMessage()]);
        }
    }

    /**
     * POST /api/node/heartbeat
     *
     * 接收节点心跳，更新在线数和心跳时间
     *
     * 请求体格式：
     * {
     *     "online_count": 5
     * }
     *
     * @return Response
     */
    public function heartbeat(): Response
    {
        /** @var Node $node */
        $node        = $this->request->node;
        $onlineCount = (int) $this->request->param('online_count', 0);

        if ($onlineCount < 0) {
            $onlineCount = 0;
        }

        $node->save([
            'online_count'   => $onlineCount,
            'last_heartbeat' => time(),
        ]);

        return json([
            'code' => 0,
            'msg'  => 'success',
            'data' => [
                'server_time' => time(),
            ],
        ]);
    }
}
