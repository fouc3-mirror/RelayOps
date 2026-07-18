<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class UserNode extends Model
{
    protected $name = 'client';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id'              => 'integer',
        'user_id'         => 'integer',
        'node_id'         => 'integer',
        'port'            => 'integer',
        'local_port'      => 'integer',
        'bandwidth_limit' => 'integer',
        'status'          => 'integer',
        'expire_time'     => 'integer',
        'create_time'     => 'integer',
        'update_time'     => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function node()
    {
        return $this->belongsTo(Node::class, 'node_id', 'id');
    }

    public static function findByProxyName(int $nodeId, string $proxyName): ?self
    {
        return self::where('node_id', $nodeId)
            ->where('proxy_name', $proxyName)
            ->find();
    }
}
