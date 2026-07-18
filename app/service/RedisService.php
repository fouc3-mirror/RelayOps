<?php
declare(strict_types=1);

namespace app\service;

use \Redis;

class RedisService
{
    private static ?Redis $client = null;

    public static function getClient(): Redis
    {
        if (self::$client !== null) {
            return self::$client;
        }

        $raw = config('redis');

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

    public static function delAuth(int|string $nodeId, string $token): bool
    {
        $redis = self::getClient();
        return (bool) $redis->del("auth:{$nodeId}:{$token}");
    }

    public static function hasAuth(int|string $nodeId, string $token): bool
    {
        $redis = self::getClient();
        return $redis->exists("auth:{$nodeId}:{$token}") > 0;
    }


    public static function setClientProxies(int $clientId, array $proxies): bool
    {
        $redis = self::getClient();
        $key = "client:{$clientId}:proxies";
        return $redis->set($key, json_encode($proxies));
    }

    public static function getClientProxies(int $clientId): ?array
    {
        $redis = self::getClient();
        $key = "client:{$clientId}:proxies";
        $data = $redis->get($key);
        return $data ? json_decode($data, true) : null;
    }

    public static function delClientProxies(int $clientId): bool
    {
        $redis = self::getClient();
        return (bool) $redis->del("client:{$clientId}:proxies");
    }


    public static function incrTraffic(int $clientId, string $datetime, int $bytes): bool
    {
        $redis = self::getClient();
        $key = "traffic:{$clientId}:{$datetime}";
        $redis->incrBy($key, $bytes);
        $redis->expire($key, 7 * 86400);
        return true;
    }

    public static function getTraffic(int $clientId, string $datetime): int
    {
        $redis = self::getClient();
        $key = "traffic:{$clientId}:{$datetime}";
        return (int) $redis->get($key);
    }


    public static function lockPort(int $nodeId, int $port, int $ttl = 60): bool
    {
        $redis = self::getClient();
        $key = "port_lock:{$nodeId}:{$port}";
        return $redis->set($key, '1', ['NX', 'EX' => $ttl]) === 'OK';
    }

    public static function unlockPort(int $nodeId, int $port): bool
    {
        $redis = self::getClient();
        $key = "port_lock:{$nodeId}:{$port}";
        return (bool) $redis->del($key);
    }

    public static function isPortLocked(int $nodeId, int $port): bool
    {
        $redis = self::getClient();
        $key = "port_lock:{$nodeId}:{$port}";
        return $redis->exists($key) > 0;
    }
}
