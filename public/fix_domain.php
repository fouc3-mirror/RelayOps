<?php
// 临时修复脚本：添加 domain 列到 RO_node 表
// 访问一次本文件即可，完成后建议删除

$host = '127.0.0.1';
$port = 3306;
$db   = '127_0_0_1';
$user = '127_0_0_1';
$pass = 'BSpcn8nyrF';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // 检查列是否已存在
    $stmt = $pdo->query("SHOW COLUMNS FROM `RO_node` LIKE 'domain'");
    if ($stmt->fetch()) {
        echo "✓ domain 列已存在，无需操作";
    } else {
        $pdo->exec("ALTER TABLE `RO_node` ADD COLUMN `domain` varchar(200) NOT NULL DEFAULT '' COMMENT '域名（如 frp.example.com）' AFTER `auth_token`");
        echo "✓ domain 列添加成功";
    }
} catch (Exception $e) {
    echo "✗ 失败: " . $e->getMessage();
}
