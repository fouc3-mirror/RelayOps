<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 前台用户模型
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $nickname
 * @property string $email
 * @property int $status
 * @property int $bandwidth_limit 带宽限制（字节/秒），0=不限
 * @property int $create_time
 * @property int $update_time
 */
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

    /**
     * 通过 token 查找用户
     * token 存储在 RO_client 表中，一对多关系
     */
    public static function findByToken(string $token): ?self
    {
        // 先从 RO_client 查 token
        $client = \app\model\UserNode::where('token', $token)
            ->where('status', 1)
            ->find();

        if (!$client || !$client->user_id) {
            return null;
        }

        return self::where('id', $client->user_id)->find();
    }
}
