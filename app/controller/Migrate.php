<?php
declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Response;

/**
 * 数据库迁移工具
 *
 * 访问 /migrate 执行已部署系统的数据库变更
 * 用于将旧数据库升级到新的结构（新增 traffic_log 表、新字段等）
 */
class Migrate extends BaseController
{
    /**
     * 迁移页面
     */
    public function index(): Response
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>数据库迁移 - 雨梦FRPS业务管理系统</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 700px;
            padding: 40px;
        }
        h1 { font-size: 22px; color: #333; margin-bottom: 8px; }
        p  { color: #666; font-size: 14px; margin-bottom: 24px; }
        .sql-box {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 16px;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.6;
            font-family: 'Consolas', monospace;
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .btn {
            display: inline-block;
            padding: 12px 32px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #409eff;
            color: #fff;
        }
        .btn-primary:hover { background: #337ecc; }
        .btn-primary:disabled { background: #a0cfff; cursor: not-allowed; }
        .btn-danger {
            background: #f56c6c;
            color: #fff;
        }
        .btn-danger:hover { background: #e04040; }
        .msg { margin-top: 16px; padding: 12px 16px; border-radius: 6px; font-size: 14px; display: none; }
        .msg.success { display: block; background: #d4edda; color: #155724; }
        .msg.error { display: block; background: #f8d7da; color: #721c24; }
        .msg.info { display: block; background: #d1ecf1; color: #0c5460; }
        .back { margin-top: 20px; text-align: center; }
        .back a { color: #999; font-size: 13px; text-decoration: none; }
        .back a:hover { color: #409eff; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔄 数据库迁移</h1>
    <p>将现有数据库升级到最新结构，新增 <code>RO_traffic_log</code> 表及所需字段（含节点域名）。</p>

    <div class="sql-box" id="sqlPreview">加载中...</div>

    <button class="btn btn-primary" id="execBtn" onclick="execute()">执行迁移</button>
    <button class="btn btn-danger" onclick="location.reload()">取消</button>
    <div class="msg" id="msg"></div>
    <div class="back"><a href="/">← 返回首页</a></div>
</div>

<script>
fetch('/migrate/preview')
    .then(r => r.json())
    .then(data => {
        if (data.code === 0) {
            document.getElementById('sqlPreview').textContent = data.data.sql || '无需变更';
            if (!data.data.has_changes) {
                document.getElementById('execBtn').disabled = true;
                document.getElementById('execBtn').textContent = '已是最新';
                showMsg('info', '数据库已是最新结构，无需迁移');
            }
        } else {
            document.getElementById('sqlPreview').textContent = '加载失败: ' + data.msg;
        }
    });

async function execute() {
    var btn = document.getElementById('execBtn');
    var msg = document.getElementById('msg');
    btn.disabled = true;
    btn.textContent = '执行中...';
    msg.className = 'msg';

    try {
        var res = await fetch('/migrate/execute', { method: 'POST' });
        var json = await res.json();
        if (json.code === 0) {
            showMsg('success', json.msg);
            btn.textContent = '执行完成';
        } else {
            showMsg('error', json.msg);
            btn.disabled = false;
            btn.textContent = '重试';
        }
    } catch (e) {
        showMsg('error', '网络错误: ' + e.message);
        btn.disabled = false;
        btn.textContent = '重试';
    }
}

function showMsg(type, text) {
    var msg = document.getElementById('msg');
    msg.className = 'msg ' + type;
    msg.textContent = text;
}
</script>
</body>
</html>
HTML;

        return \think\Response::create($html, 'html');
    }

    /**
     * 预览迁移 SQL
     */
    public function preview(): Response
    {
        $sqls = $this->buildMigrationSql();

        return json([
            'code' => 0,
            'data' => [
                'sql'         => implode(";\n\n", $sqls) ?: '-- 无变更',
                'has_changes' => !empty($sqls),
            ],
        ]);
    }

    /**
     * 执行迁移
     */
    public function execute(): Response
    {
        $sqls = $this->buildMigrationSql();

        if (empty($sqls)) {
            return json(['code' => 0, 'msg' => '数据库已是最新结构，无需迁移']);
        }

        try {
            foreach ($sqls as $sql) {
                Db::execute($sql);
            }
            return json(['code' => 0, 'msg' => '迁移成功！共执行 ' . count($sqls) . ' 条 SQL 语句']);
        } catch (\Throwable $e) {
            return json(['code' => 1, 'msg' => '迁移失败：' . $e->getMessage()]);
        }
    }

    /**
     * 生成迁移 SQL（仅生成表尚不存在的语句）
     */
    private function buildMigrationSql(): array
    {
        $prefix = env('DB_PREFIX', 'RO_');
        $sqls   = [];

        // 获取当前已有表
        $tables = Db::query("SHOW TABLES");
        $tableNames = array_column($tables, "Tables_in_" . env('DB_NAME', 'relayops'));

        // 1. RO_node — 新增字段
        if (in_array("{$prefix}node", $tableNames)) {
            if (!$this->columnExists("{$prefix}node", 'online_count')) {
                $sqls[] = "ALTER TABLE `{$prefix}node` ADD COLUMN `online_count` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前在线客户端数' AFTER `last_heartbeat`";
            }
            if (!$this->columnExists("{$prefix}node", 'allow_create_proxy')) {
                $sqls[] = "ALTER TABLE `{$prefix}node` ADD COLUMN `allow_create_proxy` tinyint(1) NOT NULL DEFAULT 1 COMMENT '允许新建隧道 0=关闭 1=开启' AFTER `online_count`";
            }
            if (!$this->columnExists("{$prefix}node", 'domain')) {
                $sqls[] = "ALTER TABLE `{$prefix}node` ADD COLUMN `domain` varchar(200) NOT NULL DEFAULT '' COMMENT '域名（如 frp.example.com）' AFTER `auth_token`";
            }
        }

        // 2. RO_user — 新增带宽限制
        if (in_array("{$prefix}user", $tableNames)) {
            if (!$this->columnExists("{$prefix}user", 'bandwidth_limit')) {
                $sqls[] = "ALTER TABLE `{$prefix}user` ADD COLUMN `bandwidth_limit` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '带宽限制（字节/秒），0=不限' AFTER `status`";
            }
        }

        // 3. RO_client — 新增字段
        if (in_array("{$prefix}client", $tableNames)) {
            if (!$this->columnExists("{$prefix}client", 'proxy_name')) {
                $sqls[] = "ALTER TABLE `{$prefix}client` ADD COLUMN `proxy_name` varchar(100) NOT NULL DEFAULT '' COMMENT '代理名称（如 tcp_8080）' AFTER `token`";
            }
            if (!$this->columnExists("{$prefix}client", 'bandwidth_limit')) {
                $sqls[] = "ALTER TABLE `{$prefix}client` ADD COLUMN `bandwidth_limit` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '带宽限制（字节/秒），0=不限' AFTER `status`";
            }
            if (!$this->indexExists("{$prefix}client", 'idx_node_proxy_name')) {
                $sqls[] = "ALTER TABLE `{$prefix}client` ADD KEY `idx_node_proxy_name` (`node_id`, `proxy_name`)";
            }
        }

        // 3b. RO_product — 新增流量限制
        if (in_array("{$prefix}product", $tableNames)) {
            if (!$this->columnExists("{$prefix}product", 'traffic_limit')) {
                $sqls[] = "ALTER TABLE `{$prefix}product` ADD COLUMN `traffic_limit` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '流量限制（字节），0=不限' AFTER `sort`";
            }
        }

        // 3c. RO_client — 新增已使用流量
        if (in_array("{$prefix}client", $tableNames)) {
            if (!$this->columnExists("{$prefix}client", 'traffic_used')) {
                $sqls[] = "ALTER TABLE `{$prefix}client` ADD COLUMN `traffic_used` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '已使用流量（字节）' AFTER `status`";
            }
        }

        // 4. RO_traffic_log — 新建表
        if (!in_array("{$prefix}traffic_log", $tableNames)) {
            $sqls[] = <<<SQL
CREATE TABLE IF NOT EXISTS `{$prefix}traffic_log` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `node_id` int(11) UNSIGNED NOT NULL COMMENT '节点ID',
    `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
    `proxy_name` varchar(100) NOT NULL DEFAULT '' COMMENT '代理名称',
    `in_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '入站流量（字节）',
    `out_bytes` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '出站流量（字节）',
    `record_time` int(11) UNSIGNED NOT NULL COMMENT '记录时间（分钟级时间戳）',
    `create_time` int(11) UNSIGNED NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx_node_id` (`node_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_record_time` (`record_time`),
    KEY `idx_node_record` (`node_id`, `record_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='流量明细表（节点上报）'
SQL;
        }

        return $sqls;
    }

    /**
     * 检查表中是否存在某列
     */
    private function columnExists(string $table, string $column): bool
    {
        try {
            $columns = Db::query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
            return !empty($columns);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 检查表中是否存在某索引
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = Db::query("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
            return !empty($indexes);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
