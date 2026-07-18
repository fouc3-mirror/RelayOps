<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

class OrderService
{
    public static function generateOrderNo(): string
    {
        return date('YmdHis') . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function verifyPort(int $nodeId, int $port): array
    {
        $node = Db::name('node')->where('id', $nodeId)->where('status', 1)->find();
        if (!$node) {
            return ['ok' => false, 'msg' => '节点不存在或已禁用'];
        }

        $rangeStart = (int) $node['port_range_start'];
        $rangeEnd = (int) $node['port_range_end'];

        if ($rangeStart > 0 && $rangeEnd > 0) {
            if ($port < $rangeStart || $port > $rangeEnd) {
                return ['ok' => false, 'msg' => "端口不在允许范围内 ({$rangeStart}-{$rangeEnd})"];
            }
        }

        $occupied = Db::name('client')
            ->where('node_id', $nodeId)
            ->where('port', $port)
            ->where('status', '<>', 2)
            ->find();
        if ($occupied) {
            return ['ok' => false, 'msg' => '该端口已被占用'];
        }

        try {
            if (RedisService::isPortLocked($nodeId, $port)) {
                return ['ok' => false, 'msg' => '该端口正在被其他用户购买，请稍后重试'];
            }
        } catch (\Throwable $e) {
        }

        return ['ok' => true, 'msg' => ''];
    }

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

                $check = self::verifyPort($nodeId, $port);
                if (!$check['ok']) {
                    Db::rollback();
                    return ['ok' => false, 'msg' => "端口 {$port}: {$check['msg']}", 'orders' => []];
                }

                try {
                    if (!RedisService::lockPort($nodeId, $port)) {
                        Db::rollback();
                        return ['ok' => false, 'msg' => "端口 {$port}: 并发冲突，请重试", 'orders' => []];
                    }
                } catch (\Throwable $e) {
                }

                $nodeName = Db::name('node')->where('id', $nodeId)->value('name') ?? '';

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
                    'status'     => 0,
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

    public static function activateService(int $orderId, string $tradeNo = ''): bool
    {
        $order = Db::name('order')->where('id', $orderId)->where('status', 0)->find();
        if (!$order) {
            return false;
        }

        Db::startTrans();
        try {
            Db::name('order')->where('id', $orderId)->update([
                'status'   => 1,
                'trade_no' => $tradeNo,
                'pay_time' => time(),
                'update_time' => time(),
            ]);

            $token = self::generateToken();

            $expireTime = time() + ($order['duration'] * 30 * 86400);

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

            try {
                RedisService::setAuth($order['node_id'], $token, $expireTime - time());

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

    public static function getPrice(int $productId): float
    {
        $row = Db::name('product')->where('id', $productId)->where('status', 1)->find();
        return (float) ($row['price'] ?? 0);
    }
}
