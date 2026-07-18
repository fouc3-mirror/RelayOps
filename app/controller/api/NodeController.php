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

class NodeController extends BaseController
{
    public function user(): Response
    {
        $token = trim($this->request->param('token', ''));

        if (empty($token)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $client = UserNode::where('token', $token)->find();

        if (!$client) {
            return json(['code' => 0, 'msg' => 'token 无效']);
        }

        $trafficLimit    = (int) ($client->traffic_limit ?? 0);
        $trafficUsed     = (int) ($client->traffic_used ?? 0);
        $trafficExhausted = ($trafficLimit > 0 && $trafficUsed >= $trafficLimit);

        if ($trafficExhausted && $client->status === 1) {
            $client->save(['status' => 0]);
            $client->status = 0;
        }

        if ($client->status === 2 || ($client->expire_time > 0 && $client->expire_time < time())) {
            if ($client->status !== 2) {
                $client->save(['status' => 2]);
            }
            return json(['code' => 0, 'msg' => '隧道已过期']);
        }

        if ($client->status !== 1) {
            return json(['code' => 0, 'msg' => '隧道未启用']);
        }

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

    public function canCreateProxy(): Response
    {
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

    public function traffic(): Response
    {
        $node  = $this->request->node;
        $items = $this->request->param('items', []);

        if (empty($items) || !is_array($items)) {
            return json(['code' => 0, 'msg' => '请求数据为空']);
        }

        if (count($items) > 500) {
            return json(['code' => 0, 'msg' => '单次提交数据不能超过 500 条']);
        }

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

    public function heartbeat(): Response
    {
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
