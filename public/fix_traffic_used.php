<?php
// 一次性迁移脚本：添加 traffic_used 列到 RO_client 表
$host = '127.0.0.1';
$port = 3306;
$db   = '127_0_0_1';
$user = '127_0_0_1';
$pass = 'BSpcn8nyrF';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $pdo->query("SHOW COLUMNS FROM `RO_client` LIKE 'traffic_used'");
    if ($stmt->fetch()) {
        echo "✓ traffic_used 列已存在";
    } else {
        $pdo->exec("ALTER TABLE `RO_client` ADD COLUMN `traffic_used` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '已使用流量（字节）' AFTER `status`");
        echo "✓ traffic_used 列添加成功";
    }
} catch (Exception $e) {
    echo "✗ 失败: " . $e->getMessage();
}
