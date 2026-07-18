<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class TrafficLog extends Model
{
    protected $name = 'traffic_log';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $type = [
        'id'          => 'integer',
        'node_id'     => 'integer',
        'user_id'     => 'integer',
        'in_bytes'    => 'integer',
        'out_bytes'   => 'integer',
        'record_time' => 'integer',
        'create_time' => 'integer',
    ];

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
