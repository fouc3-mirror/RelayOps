<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class Node extends Model
{
    protected $name = 'node';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

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

    public static function findByToken(string $token): ?self
    {
        return self::where('auth_token', $token)
            ->where('status', 1)
            ->find();
    }

    public function activeClientCount(): int
    {
        return $this->hasMany(UserNode::class, 'node_id', 'id')
            ->where('status', 'in', [0, 1])
            ->count();
    }

    public function canCreateProxy(): bool
    {
        if ($this->status !== 1) {
            return false;
        }

        if ($this->getData('allow_create_proxy') === 0) {
            return false;
        }

        $active = $this->activeClientCount();
        if ($active >= $this->max_pool_count) {
            return false;
        }

        return true;
    }
}
