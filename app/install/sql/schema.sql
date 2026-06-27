--RelayOps 安装向导 - 数据库表结构
-- 表前缀: RO_

-------------------------------------------------------------
-- 管理员表
-------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `RO_admin` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
    `username` varchar(50) NOT NULL COMMENT '用户名',
    `password` varchar(255) NOT NULL COMMENT '密码',
    `nickname` varchar(50) DEFAULT '' COMMENT '昵称',
    `email` varchar(100) DEFAULT '' COMMENT '邮箱',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0:禁用 1:正常',
    `last_login_time` int(11) DEFAULT NULL COMMENT '最后登录时间',
    `last_login_ip` varchar(50) DEFAULT '' COMMENT '最后登录IP',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-------------------------------------------------------------
-- 配置表
-------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `RO_setting` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
    `group` varchar(30) DEFAULT '' COMMENT '配置分组',
    `name` varchar(100) NOT NULL COMMENT '配置名称',
    `value` text COMMENT '配置值',
    `type` varchar(20) DEFAULT 'text' COMMENT '类型: text,number,textarea,image,switch',
    `title` varchar(100) NOT NULL COMMENT '配置标题',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统配置表';

-- --------------------------------------------------------
-- FRPS节点表
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `RO_node` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '节点ID',
    `name` varchar(100) NOT NULL COMMENT '节点名称',
    `server_addr` varchar(100) NOT NULL COMMENT '服务器IP地址',
    `server_port` int(5) UNSIGNED NOT NULL DEFAULT 7000 COMMENT 'frps服务端口',
    `auth_token` varchar(100) NOT NULL COMMENT 'API密钥/认证令牌',
    `http_port` int(5) UNSIGNED NOT NULL DEFAULT 80 COMMENT 'HTTP虚拟主机端口',
    `https_port` int(5) UNSIGNED NOT NULL DEFAULT 443 COMMENT 'HTTPS虚拟主机端口',
    `dashboard_port` int(5) UNSIGNED NOT NULL DEFAULT 7500 COMMENT 'Dashboard端口',
    `dashboard_user` varchar(50) DEFAULT '' COMMENT 'Dashboard用户名',
    `dashboard_pass` varchar(255) DEFAULT '' COMMENT 'Dashboard密码',
    `admin_port` int(5) UNSIGNED NOT NULL DEFAULT 7000 COMMENT 'frps管理端口',
    `bind_addr` varchar(100) DEFAULT '0.0.0.0' COMMENT '绑定地址',
    `vhost_http_port` int(5) UNSIGNED NOT NULL DEFAULT 80 COMMENT '虚拟主机HTTP端口',
    `vhost_https_port` int(5) UNSIGNED NOT NULL DEFAULT 443 COMMENT '虚拟主机HTTPS端口',
    `subdomain_host` varchar(100) DEFAULT '' COMMENT '子域名主机',
    `max_pool_count` int(11) UNSIGNED NOT NULL DEFAULT 50 COMMENT '最大连接池',
    `port_range_start` int(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '可用端口起始',
    `port_range_end` int(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '可用端口结束',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0:禁用 1:正常',
    `description` text COMMENT '节点描述',
    `last_heartbeat` int(11) DEFAULT NULL COMMENT '最后心跳时间',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='FRPS节点表';

-- --------------------------------------------------------
-- 用户表
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `RO_user` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
    `username` varchar(50) NOT NULL COMMENT '用户名',
    `password` varchar(255) NOT NULL COMMENT '密码',
    `nickname` varchar(50) DEFAULT '' COMMENT '昵称',
    `email` varchar(100) DEFAULT '' COMMENT '邮箱',
    `phone` varchar(20) DEFAULT '' COMMENT '手机号',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0:禁用 1:正常',
    `last_login_time` int(11) DEFAULT NULL COMMENT '最后登录时间',
    `last_login_ip` varchar(50) DEFAULT '' COMMENT '最后登录IP',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- --------------------------------------------------------
-- 邮箱验证码表
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `RO_email_verify` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `email` varchar(100) NOT NULL COMMENT '邮箱地址',
    `code` varchar(10) NOT NULL COMMENT '验证码',
    `scene` varchar(20) NOT NULL DEFAULT 'register' COMMENT '场景: register=注册, reset=重置密码',
    `used` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已使用 0:未使用 1:已使用',
    `expire_time` int(11) NOT NULL COMMENT '过期时间',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    KEY `idx_email_scene` (`email`, `scene`),
    KEY `idx_expire` (`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邮箱验证码表';

-- --------------------------------------------------------
-- 客户端/隧道表
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `RO_client` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '客户端ID',
    `user_id` int(11) UNSIGNED NOT NULL COMMENT '关联用户',
    `node_id` int(11) UNSIGNED NOT NULL COMMENT '关联节点',
    `port` int(5) UNSIGNED NOT NULL COMMENT '分配的端口',
    `token` varchar(128) NOT NULL COMMENT '唯一鉴权token',
    `proxy_type` varchar(20) NOT NULL DEFAULT 'tcp' COMMENT '代理类型: tcp/udp/http/https',
    `local_ip` varchar(100) DEFAULT '127.0.0.1' COMMENT '本地监听地址',
    `local_port` int(5) UNSIGNED DEFAULT 0 COMMENT '本地监听端口',
    `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0:未激活 1:运行中 2:已过期',
    `expire_time` int(11) NOT NULL COMMENT '到期时间',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_node_port` (`node_id`, `port`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_expire_time` (`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户端/隧道表';

-- --------------------------------------------------------
-- 订单表
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `RO_order` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单ID',
    `order_no` varchar(32) NOT NULL COMMENT '订单号',
    `user_id` int(11) UNSIGNED NOT NULL COMMENT '关联用户',
    `node_id` int(11) UNSIGNED NOT NULL COMMENT '购买的节点',
    `node_name` varchar(100) DEFAULT '' COMMENT '节点名称(冗余)',
    `port` int(5) UNSIGNED NOT NULL COMMENT '购买的端口',
    `proxy_type` varchar(20) NOT NULL DEFAULT 'tcp' COMMENT '代理类型',
    `duration` int(11) NOT NULL DEFAULT 1 COMMENT '购买时长(月)',
    `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '订单金额',
    `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0:待支付 1:已支付 2:已过期 3:已取消',
    `trade_no` varchar(64) DEFAULT '' COMMENT '第三方支付单号',
    `pay_time` int(11) DEFAULT NULL COMMENT '支付时间',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_order_no` (`order_no`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

-- 插入默认配置（使用 INSERT IGNORE 避免重复插入报错）
INSERT IGNORE INTO `RO_setting` (`group`, `name`, `value`, `type`, `title`, `create_time`, `update_time`) VALUES
('basic', 'site_name', '我的网站', 'text', '网站名称', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('basic', 'site_description', 'RelayOps是一个基于ThinkPHP8开发的开源项目', 'textarea', '网站描述', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('basic', 'site_keywords', 'ThinkPHP8,CMS', 'text', '网站关键词', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('basic', 'site_logo', '', 'image', '网站Logo', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('basic', 'site_favicon', '', 'image', '网站图标', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('basic', 'site_footer', 'Copyright © All Rights Reserved.', 'textarea', '页脚代码', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('system', 'admin_email', 'admin@example.com', 'text', '管理员邮箱', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('system', 'admin_phone', '', 'text', '管理员电话', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('system', 'icp_number', '', 'text', 'ICP备案号', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('system', 'install_time', '0', 'text', '安装时间', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- SMTP 邮件配置
INSERT IGNORE INTO `RO_setting` (`group`, `name`, `value`, `type`, `title`, `create_time`, `update_time`) VALUES
('email', 'smtp_host', '', 'text', 'SMTP服务器', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('email', 'smtp_port', '465', 'number', 'SMTP端口', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('email', 'smtp_user', '', 'text', 'SMTP用户名', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('email', 'smtp_pass', '', 'text', 'SMTP密码', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('email', 'smtp_from', '', 'text', '发件人邮箱', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('email', 'smtp_name', 'RelayOps', 'text', '发件人名称', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('email', 'smtp_ssl', '1', 'switch', '启用SSL', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('email', 'verify_expire', '300', 'number', '验证码有效期(秒)', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 易支付配置
INSERT IGNORE INTO `RO_setting` (`group`, `name`, `value`, `type`, `title`, `create_time`, `update_time`) VALUES
('pay', 'epay_url', '', 'text', '易支付接口地址', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('pay', 'epay_pid', '', 'text', '易支付商户ID', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('pay', 'epay_key', '', 'text', '易支付商户密钥', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- Redis 配置
INSERT IGNORE INTO `RO_setting` (`group`, `name`, `value`, `type`, `title`, `create_time`, `update_time`) VALUES
('redis', 'redis_host', '127.0.0.1', 'text', 'Redis服务器地址', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('redis', 'redis_port', '6379', 'number', 'Redis端口', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('redis', 'redis_password', '', 'text', 'Redis密码', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('redis', 'redis_select', '0', 'number', 'Redis数据库编号', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('redis', 'redis_prefix', 'frp:', 'text', 'Redis Key前缀', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- --------------------------------------------------------
-- 商品表
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `RO_product` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品ID',
    `node_id` int(11) UNSIGNED NOT NULL COMMENT '关联节点',
    `name` varchar(100) NOT NULL COMMENT '商品名称',
    `proxy_type` varchar(20) NOT NULL DEFAULT 'tcp' COMMENT '代理类型: tcp/udp/http/https',
    `port_start` int(5) UNSIGNED NOT NULL COMMENT '端口范围起始',
    `port_end` int(5) UNSIGNED NOT NULL COMMENT '端口范围结束',
    `price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '月单价(元)',
    `duration_options` varchar(100) DEFAULT '1,3,6,12' COMMENT '可选时长(月),逗号分隔',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 0:下架 1:上架',
    `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
    `description` text COMMENT '商品描述',
    `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
    `update_time` int(11) DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `idx_node_id` (`node_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品表';
