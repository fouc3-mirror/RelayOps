<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * FRPS节点模型
 *
 * @property int $id
 * @property string $name
 * @property string $server_addr
 * @property int $server_port
 * @property string $auth_token
 * @property int $port_range_start
 * @property int $port_range_end
 * @property int $max_pool_count
 * @property int $status
 * @property int $online_count
 * @property int $last_heartbeat
 * @property int $allow_create_proxy
 * @property int $create_time
 * @property int $update_time
 */
class Node extends Model
{
    // 绑定 RO_node 表
    protected $name = 'node';

    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 类型转换
    protected $type = [
        'id'            => 'integer',
        'server_port'   => 'integer',
        'port_range_start' => 'integer',
        'port_range_end'   => 'integer',
        'max_pool_count'   => 'integer',
        'status'           => 'integer',
        'online_count'     => 'integer',
        'allow_create_proxy' => 'integer',
        'last_heartbeat'   => 'integer',
        'create_time'      => 'integer',
        'update_time'      => 'integer',
    ];

    /**
     * 通过 auth_token 查找节点
     */
    public static function findByToken(string $token): ?self
    {
        return self::where('auth_token', $token)
            ->where('status', 1)
            ->find();
    }

    /**
     * 获取节点有效客户端数量
     */
    public function activeClientCount(): int
    {
        return $this->hasMany(UserNode::class, 'node_id', 'id')
            ->where('status', 'in', [0, 1])
            ->count();
    }

    /**
     * 是否允许创建新隧道
     */
    public function canCreateProxy(): bool
    {
        // 节点必须启用
        if ($this->status !== 1) {
            return false;
        }

        // 全局开关关闭则不允许
        if ($this->getData('allow_create_proxy') === 0) {
            return false;
        }

        // 检查在线客户端数是否超过最大连接池
        $active = $this->activeClientCount();
        if ($active >= $this->max_pool_count) {
            return false;
        }

        return true;
    }
}
