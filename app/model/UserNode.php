<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 用户代理客户端（隧道）模型
 *
 * @property int $id
 * @property int $user_id
 * @property int $node_id
 * @property int $port
 * @property string $token
 * @property string $proxy_name
 * @property string $proxy_type
 * @property string $local_ip
 * @property int $local_port
 * @property int $bandwidth_limit    字节/秒，0 表示不限
 * @property int $status             0=停用 1=运行中 2=已过期
 * @property int $expire_time
 * @property int $create_time
 * @property int $update_time
 */
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

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 关联节点
     */
    public function node()
    {
        return $this->belongsTo(Node::class, 'node_id', 'id');
    }

    /**
     * 通过 proxy_name 查找客户端
     */
    public static function findByProxyName(int $nodeId, string $proxyName): ?self
    {
        return self::where('node_id', $nodeId)
            ->where('proxy_name', $proxyName)
            ->find();
    }
}
