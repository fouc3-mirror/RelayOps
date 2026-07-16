-- ============================================================
-- 雨梦FRPS业务管理系统 Node API — 数据库变更 SQL
-- 新增 traffic_log 表 + 现有表新增字段
-- ============================================================

-- ------------------------------------------------------------
-- 1. RO_node — 新增节点控制字段
-- ------------------------------------------------------------
ALTER TABLE `RO_node`
    ADD COLUMN `online_count` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '当前在线客户端数' AFTER `last_heartbeat`,
    ADD COLUMN `allow_create_proxy` tinyint(1) NOT NULL DEFAULT 1 COMMENT '允许新建隧道 0=关闭 1=开启' AFTER `online_count`;

-- ------------------------------------------------------------
-- 2. RO_user — 新增带宽限制字段
-- ------------------------------------------------------------
ALTER TABLE `RO_user`
    ADD COLUMN `bandwidth_limit` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '带宽限制（字节/秒），0=不限' AFTER `status`;

-- ------------------------------------------------------------
-- 3. RO_client — 新增 proxy_name 和带宽限制字段
-- ------------------------------------------------------------
ALTER TABLE `RO_client`
    ADD COLUMN `proxy_name` varchar(100) NOT NULL DEFAULT '' COMMENT '代理名称（如 tcp_8080）' AFTER `token`,
    ADD COLUMN `bandwidth_limit` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '带宽限制（字节/秒），0=不限' AFTER `status`;

-- 为 proxy_name 添加索引（用于 traffic 上报关联查询）
ALTER TABLE `RO_client`
    ADD KEY `idx_node_proxy_name` (`node_id`, `proxy_name`);

-- ------------------------------------------------------------
-- 4. RO_traffic_log — 流量明细表
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `RO_traffic_log` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='流量明细表（节点上报）';
