<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class User extends Model
{
    protected $name = 'user';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id'              => 'integer',
        'status'          => 'integer',
        'bandwidth_limit' => 'integer',
        'create_time'     => 'integer',
        'update_time'     => 'integer',
    ];

    public static function findByToken(string $token): ?self
    {
        $client = \app\model\UserNode::where('token', $token)
            ->where('status', 1)
            ->find();

        if (!$client || !$client->user_id) {
            return null;
        }

        return self::where('id', $client->user_id)->find();
    }
}
