<?php
declare(strict_types=1);

namespace app\service;

use \Redis;

/**
 * Redis 操作封装（FRPS 鉴权/流量/客户端配置）
 *
 * Redis Key 命名规范：
 *   鉴权:       frp:auth:{node_id}:{token}
 *   客户端配置:  frp:client:{client_id}:proxies
 *   流量统计:    frp:traffic:{client_id}:{yyyyMMddHHmm}
 */
class RedisService
{
    private static ?Redis $client = null;

    /**
     * 获取 Redis 连接（单例）
     */
    public static function getClient(): Redis
    {
        if (self::$client !== null) {
            return self::$client;
        }

        $raw = config('redis');

        // 解析闭包配置（从数据库读取）
        $config = [];
        foreach ($raw as $key => $value) {
            $config[$key] = is_callable($value) ? $value() : $value;
        }

        $redis = new Redis();
        $connected = $redis->connect(
            $config['host'],
            $config['port'],
            $config['timeout'] ?? 3
        );

        if (!$connected) {
            throw new \RuntimeException('Redis 连接失败: ' . $config['host'] . ':' . $config['port']);
        }

        if (!empty($config['password'])) {
            $redis->auth($config['password']);
        }

        $redis->select($config['select'] ?? 0);
        $redis->setOption(Redis::OPT_PREFIX, $config['prefix'] ?? 'frp:');

        self::$client = $redis;
        return self::$client;
    }

    // ========================
    // 鉴权 Key: frp:auth:{node_id}:{token}
    // ========================

    /**
     * 写入鉴权信息
     * @param int|string $nodeId 节点ID
     * @param string $token 客户端鉴权token
     * @param int $ttl 过期时间（秒），0表示永久
     */
    public static function setAuth(int|string $nodeId, string $token, int $ttl = 0): bool
    {
        $redis = self::getClient();
        $key = "auth:{$nodeId}:{$token}";
        $value = json_encode(['node_id' => $nodeId, 'token' => $token, 'time' => time()]);

        if ($ttl > 0) {
            return $redis->setex($key, $ttl, $value);
        }
        return $redis->set($key, $value);
    }

    /**
     * 删除鉴权信息
     */
    public static function delAuth(int|string $nodeId, string $token): bool
    {
        $redis = self::getClient();
        return (bool) $redis->del("auth:{$nodeId}:{$token}");
    }

    /**
     * 检查鉴权是否存在
     */
    public static function hasAuth(int|string $nodeId, string $token): bool
    {
        $redis = self::getClient();
        return $redis->exists("auth:{$nodeId}:{$token}") > 0;
    }

    // ========================
    // 客户端配置: frp:client:{client_id}:proxies
    // ========================

    /**
     * 写入客户端代理配置
     * @param int $clientId 客户端ID
     * @param array $proxies 代理配置列表
     */
    public static function setClientProxies(int $clientId, array $proxies): bool
    {
        $redis = self::getClient();
        $key = "client:{$clientId}:proxies";
        return $redis->set($key, json_encode($proxies));
    }

    /**
     * 获取客户端代理配置
     */
    public static function getClientProxies(int $clientId): ?array
    {
        $redis = self::getClient();
        $key = "client:{$clientId}:proxies";
        $data = $redis->get($key);
        return $data ? json_decode($data, true) : null;
    }

    /**
     * 删除客户端代理配置
     */
    public static function delClientProxies(int $clientId): bool
    {
        $redis = self::getClient();
        return (bool) $redis->del("client:{$clientId}:proxies");
    }

    // ========================
    // 流量统计: frp:traffic:{client_id}:{yyyyMMddHHmm}
    // ========================

    /**
     * 记录流量（累加）
     * @param int $clientId 客户端ID
     * @param string $datetime 格式: yyyyMMddHHmm
     * @param int $bytes 流量字节数
     */
    public static function incrTraffic(int $clientId, string $datetime, int $bytes): bool
    {
        $redis = self::getClient();
        $key = "traffic:{$clientId}:{$datetime}";
        $redis->incrBy($key, $bytes);
        // 流量数据保留 7 天
        $redis->expire($key, 7 * 86400);
        return true;
    }

    /**
     * 获取某时段的流量
     */
    public static function getTraffic(int $clientId, string $datetime): int
    {
        $redis = self::getClient();
        $key = "traffic:{$clientId}:{$datetime}";
        return (int) $redis->get($key);
    }

    // ========================
    // 端口占用锁（防并发）
    // ========================

    /**
     * 尝试锁定端口（Redis SET NX 防并发）
     * @return bool true=锁定成功 false=端口已被占用
     */
    public static function lockPort(int $nodeId, int $port, int $ttl = 60): bool
    {
        $redis = self::getClient();
        $key = "port_lock:{$nodeId}:{$port}";
        return $redis->set($key, '1', ['NX', 'EX' => $ttl]) === 'OK';
    }

    /**
     * 释放端口锁
     */
    public static function unlockPort(int $nodeId, int $port): bool
    {
        $redis = self::getClient();
        $key = "port_lock:{$nodeId}:{$port}";
        return (bool) $redis->del($key);
    }

    /**
     * 端口是否已锁定
     */
    public static function isPortLocked(int $nodeId, int $port): bool
    {
        $redis = self::getClient();
        $key = "port_lock:{$nodeId}:{$port}";
        return $redis->exists($key) > 0;
    }
}
