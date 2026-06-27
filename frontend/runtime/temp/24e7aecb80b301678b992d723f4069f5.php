<?php /*a:2:{s:47:"D:\frps-tp\RelayOps-php\view/index\console.html";i:1782549349;s:45:"D:\frps-tp\RelayOps-php\view/layout_user.html";i:1782556353;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>控制台 - RelayOps</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #333; }
        a { text-decoration: none; color: inherit; }
        .nav { display: flex; align-items: center; justify-content: space-between; padding: 0 32px; height: 60px; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .nav-logo { font-size: 20px; font-weight: 700; color: #409eff; }
        .nav-links { display: flex; gap: 24px; }
        .nav-links a { color: #666; font-size: 15px; transition: color .2s; }
        .nav-links a:hover { color: #409eff; }
        .nav-right { display: flex; align-items: center; gap: 16px; }
        .btn { display: inline-block; padding: 8px 20px; border-radius: 6px; font-size: 14px; cursor: pointer; border: none; transition: all .2s; }
        .btn-primary { background: #409eff; color: #fff; }
        .btn-primary:hover { background: #337ecc; }
        .btn-outline { background: transparent; color: #409eff; border: 1px solid #409eff; }
        .btn-outline:hover { background: #ecf5ff; }
        .main { min-height: calc(100vh - 120px); padding: 40px 32px; background: #f5f7fa; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 13px; border-top: 1px solid #eee; background: #fff; }
        
.console-header { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
.console-avatar { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, #409eff, #337ecc); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; font-weight: 700; }
.console-info h2 { font-size: 20px; margin-bottom: 4px; }
.console-info p { color: #999; font-size: 13px; }

.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
.stat-card .icon { font-size: 28px; margin-bottom: 12px; }
.stat-card .label { font-size: 13px; color: #999; margin-bottom: 6px; }
.stat-card .value { font-size: 24px; font-weight: 600; color: #333; }

.section-card { background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); margin-bottom: 20px; }
.section-title { font-size: 16px; font-weight: 500; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.info-item .label { font-size: 13px; color: #999; margin-bottom: 4px; }
.info-item .value { font-size: 14px; color: #333; }

.empty-state { text-align: center; padding: 40px 20px; color: #999; }
.empty-state .icon { font-size: 48px; margin-bottom: 16px; }
.empty-state p { font-size: 14px; }

    </style>
</head>
<body>
    <nav class="nav">
        <a href="/" class="nav-logo">⚡ RelayOps</a>
        <div class="nav-links">
            <a href="/">首页</a>
            <?php if (session('user_id')): ?>
                <a href="/console">控制台</a>
                <a href="/console/shop">选购商品</a>
            <?php endif; ?>
        </div>
        <div class="nav-right">
            <?php if (session('user_id')): ?>
                <a href="/console" style="color: #409eff; font-size: 14px;">
                    <?= htmlspecialchars(session('user_username') ?: '控制台') ?>
                </a>
                <a href="/api/user/logout" class="btn btn-outline" onclick="return confirm('确定退出？')" style="font-size: 13px; padding: 6px 16px;">退出</a>
            <?php else: ?>
                <a href="/login" class="btn btn-primary" style="font-size: 13px; padding: 6px 16px;">登录</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="main">
        
<div style="max-width: 960px; margin: 0 auto;">
    <!-- 用户信息头部 -->
    <div class="console-header">
        <div class="console-avatar">
            <?= mb_substr($user['nickname'] ?: $user['username'], 0, 1) ?>
        </div>
        <div class="console-info">
            <h2><?= htmlspecialchars($user['nickname'] ?: $user['username']) ?></h2>
            <p><?= htmlspecialchars($user['email'] ?: '未绑定邮箱') ?></p>
        </div>
    </div>

    <!-- 统计卡片 -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="icon">🖥️</div>
            <div class="label">我的节点</div>
            <div class="value"><?= $nodeCount ?></div>
        </div>
        <div class="stat-card">
            <div class="icon">📅</div>
            <div class="label">注册时间</div>
            <div class="value" style="font-size: 16px;"><?= date('Y-m-d', $user['create_time']) ?></div>
        </div>
    </div>

    <!-- 账户信息 -->
    <div class="section-card">
        <div class="section-title">账户信息</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">用户名</div>
                <div class="value"><?= htmlspecialchars($user['username']) ?></div>
            </div>
            <div class="info-item">
                <div class="label">昵称</div>
                <div class="value"><?= htmlspecialchars($user['nickname'] ?: '-') ?></div>
            </div>
            <div class="info-item">
                <div class="label">邮箱</div>
                <div class="value"><?= htmlspecialchars($user['email'] ?: '未绑定') ?></div>
            </div>
            <div class="info-item">
                <div class="label">状态</div>
                <div class="value"><?= $user['status'] ? '<span style="color:#67c23a">正常</span>' : '<span style="color:#f56c6c">已禁用</span>' ?></div>
            </div>
        </div>
    </div>

    <!-- 节点列表（占位） -->
    <div class="section-card">
        <div class="section-title">我的节点</div>
        <div class="empty-state">
            <div class="icon">🖥️</div>
            <p>暂无节点，等待分配</p>
        </div>
    </div>
</div>

    </div>

    <footer class="footer">
        Copyright &copy; <?= date('Y') ?> RelayOps. All rights reserved.
    </footer>
</body>
</html>
