<?php

$host = '127.0.0.1';
$port = 3306;
$db   = '127_0_0_1';
$user = '127_0_0_1';
$pass = 'BSpcn8nyrF';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $pdo->query("SHOW COLUMNS FROM `RO_product` LIKE 'traffic_limit'");
    if ($stmt->fetch()) {
        echo "✓ traffic_limit 列已存在";
    } else {
        $pdo->exec("ALTER TABLE `RO_product` ADD COLUMN `traffic_limit` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '流量限制（字节），0=不限' AFTER `sort`");
        echo "✓ traffic_limit 列添加成功";
    }
} catch (Exception $e) {
    echo "✗ 失败: " . $e->getMessage();
}
