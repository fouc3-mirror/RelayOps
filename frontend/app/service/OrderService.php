<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

/**
 * 订单核心服务
 */
class OrderService
{
    /**
     * 生成唯一订单号
     */
    public static function generateOrderNo(): string
    {
        return date('YmdHis') . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * 生成客户端鉴权 token
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * 二次校验端口合法性（后端强制确认）
     * @return array{ok: bool, msg: string}
     */
    public static function verifyPort(int $nodeId, int $port): array
    {
        // 1. 检查节点是否存在且启用
        $node = Db::name('node')->where('id', $nodeId)->where('status', 1)->find();
        if (!$node) {
            return ['ok' => false, 'msg' => '节点不存在或已禁用'];
        }

        // 2. 检查端口是否在节点的允许范围内
        $rangeStart = (int) $node['port_range_start'];
        $rangeEnd = (int) $node['port_range_end'];

        if ($rangeStart > 0 && $rangeEnd > 0) {
            if ($port < $rangeStart || $port > $rangeEnd) {
                return ['ok' => false, 'msg' => "端口不在允许范围内 ({$rangeStart}-{$rangeEnd})"];
            }
        }

        // 3. 检查端口是否已被其他用户占用（数据库层面）
        $occupied = Db::name('client')
            ->where('node_id', $nodeId)
            ->where('port', $port)
            ->where('status', '<>', 2) // 排除已过期
            ->find();
        if ($occupied) {
            return ['ok' => false, 'msg' => '该端口已被占用'];
        }

        // 4. Redis 并发锁检查
        try {
            if (RedisService::isPortLocked($nodeId, $port)) {
                return ['ok' => false, 'msg' => '该端口正在被其他用户购买，请稍后重试'];
            }
        } catch (\Throwable $e) {
            // Redis 不可用时跳过锁检查，仅依赖数据库唯一索引
        }

        return ['ok' => true, 'msg' => ''];
    }

    /**
     * 从购物车批量创建订单
     * @param int $userId
     * @param array $cartItems 购物车数据 [{node_id, port, proxy_type, duration, price}, ...]
     * @return array{ok: bool, msg: string, orders: array}
     */
    public static function createOrders(int $userId, array $cartItems): array
    {
        if (empty($cartItems)) {
            return ['ok' => false, 'msg' => '购物车为空', 'orders' => []];
        }

        $orders = [];
        $totalAmount = 0;

        Db::startTrans();
        try {
            foreach ($cartItems as $item) {
                $nodeId = (int) ($item['node_id'] ?? 0);
                $port = (int) ($item['port'] ?? 0);
                $proxyType = $item['proxy_type'] ?? 'tcp';
                $duration = (int) ($item['duration'] ?? 1);
                $price = (float) ($item['price'] ?? 0);

                // 二次校验端口
                $check = self::verifyPort($nodeId, $port);
                if (!$check['ok']) {
                    Db::rollback();
                    return ['ok' => false, 'msg' => "端口 {$port}: {$check['msg']}", 'orders' => []];
                }

                // Redis 端口锁
                try {
                    if (!RedisService::lockPort($nodeId, $port)) {
                        Db::rollback();
                        return ['ok' => false, 'msg' => "端口 {$port}: 并发冲突，请重试", 'orders' => []];
                    }
                } catch (\Throwable $e) {
                    // Redis 不可用时继续
                }

                // 获取节点名称（冗余存储）
                $nodeName = Db::name('node')->where('id', $nodeId)->value('name') ?? '';

                // 创建订单
                $orderNo = self::generateOrderNo();
                $amount = round($price * $duration, 2);

                $orderId = Db::name('order')->insertGetId([
                    'order_no'   => $orderNo,
                    'user_id'    => $userId,
                    'node_id'    => $nodeId,
                    'node_name'  => $nodeName,
                    'port'       => $port,
                    'proxy_type' => $proxyType,
                    'duration'   => $duration,
                    'amount'     => $amount,
                    'status'     => 0, // 待支付
                    'create_time' => time(),
                    'update_time' => time(),
                ]);

                $totalAmount += $amount;
                $orders[] = [
                    'id'       => $orderId,
                    'order_no' => $orderNo,
                    'amount'   => $amount,
                ];
            }

            Db::commit();

            return [
                'ok'     => true,
                'msg'    => '订单创建成功',
                'orders' => $orders,
                'total'  => $totalAmount,
            ];
        } catch (\Throwable $e) {
            Db::rollback();
            return ['ok' => false, 'msg' => '订单创建失败: ' . $e->getMessage(), 'orders' => []];
        }
    }

    /**
     * 支付成功后开通服务
     * @param int $orderId 订单ID
     * @param string $tradeNo 第三方支付单号
     */
    public static function activateService(int $orderId, string $tradeNo = ''): bool
    {
        $order = Db::name('order')->where('id', $orderId)->where('status', 0)->find();
        if (!$order) {
            return false;
        }

        Db::startTrans();
        try {
            // 1. 更新订单状态
            Db::name('order')->where('id', $orderId)->update([
                'status'   => 1,
                'trade_no' => $tradeNo,
                'pay_time' => time(),
                'update_time' => time(),
            ]);

            // 2. 生成客户端 token
            $token = self::generateToken();

            // 3. 计算到期时间
            $expireTime = time() + ($order['duration'] * 30 * 86400);

            // 4. 写入客户端表
            $clientId = Db::name('client')->insertGetId([
                'user_id'    => $order['user_id'],
                'node_id'    => $order['node_id'],
                'port'       => $order['port'],
                'token'      => $token,
                'proxy_type' => $order['proxy_type'],
                'local_ip'   => '127.0.0.1',
                'local_port' => 0,
                'status'     => 1,
                'expire_time' => $expireTime,
                'create_time' => time(),
                'update_time' => time(),
            ]);

            // 5. 写入 Redis 鉴权
            try {
                RedisService::setAuth($order['node_id'], $token, $expireTime - time());

                // 写入客户端代理配置
                $proxyConfig = [
                    [
                        'type'      => $order['proxy_type'],
                        'node_id'   => $order['node_id'],
                        'port'      => $order['port'],
                        'token'     => $token,
                    ]
                ];
                RedisService::setClientProxies($clientId, $proxyConfig);
            } catch (\Throwable $e) {
                // Redis 写入失败不阻断流程，日志记录
                \think\facade\Log::error('Redis 写入失败: ' . $e->getMessage());
            }

            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollback();
            \think\facade\Log::error('开通服务失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取商品价格（从 product 表读取）
     */
    public static function getPrice(int $productId): float
    {
        $row = Db::name('product')->where('id', $productId)->where('status', 1)->find();
        return (float) ($row['price'] ?? 0);
    }
}
