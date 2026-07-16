<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 流量明细模型
 *
 * @property int $id
 * @property int $node_id
 * @property int $user_id
 * @property string $proxy_name
 * @property int $in_bytes
 * @property int $out_bytes
 * @property int $record_time  记录时间（分钟级时间戳）
 * @property int $create_time
 */
class TrafficLog extends Model
{
    protected $name = 'traffic_log';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = false; // 流量记录不需要更新时间

    protected $type = [
        'id'          => 'integer',
        'node_id'     => 'integer',
        'user_id'     => 'integer',
        'in_bytes'    => 'integer',
        'out_bytes'   => 'integer',
        'record_time' => 'integer',
        'create_time' => 'integer',
    ];

    /**
     * 批量写入流量明细（事务保护）
     *
     * @param int   $nodeId
     * @param array $items  [['user_id' => 1, 'proxy_name' => 'tcp', 'in_bytes' => ..., 'out_bytes' => ..., 'record_time' => ...], ...]
     */
    public static function batchInsert(int $nodeId, array $items): bool
    {
        $rows = [];
        $now  = time();

        foreach ($items as $item) {
            $rows[] = [
                'node_id'     => $nodeId,
                'user_id'     => (int) ($item['user_id'] ?? 0),
                'proxy_name'  => $item['proxy_name'] ?? '',
                'in_bytes'    => (int) ($item['in'] ?? $item['in_bytes'] ?? 0),
                'out_bytes'   => (int) ($item['out'] ?? $item['out_bytes'] ?? 0),
                'record_time' => (int) ($item['record_time'] ?? $now),
                'create_time' => $now,
            ];
        }

        if (empty($rows)) {
            return false;
        }

        $model = new static();
        return $model->saveAll($rows) !== false;
    }
}
