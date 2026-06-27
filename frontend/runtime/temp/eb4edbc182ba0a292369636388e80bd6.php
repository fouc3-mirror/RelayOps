<?php /*a:2:{s:49:"D:\frps-tp\RelayOps-php\view/admin\dashboard.html";i:1782562928;s:46:"D:\frps-tp\RelayOps-php\view/layout_admin.html";i:1782560509;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>仪表盘 - <?php echo isset($site['site_name']) ? htmlentities((string) $site['site_name']) : 'RelayOps'; ?></title>
    <link rel="icon" href="<?php echo isset($site['site_favicon']) ? htmlentities((string) $site['site_favicon']) : '/favicon.ico'; ?>" type="image/x-icon">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #333; }
        a { text-decoration: none; color: inherit; }

        .layout { display: flex; min-height: 100vh; }

        /* 侧边栏 */
        .sidebar { width: 220px; background: #1d1e2c; color: #fff; display: flex; flex-direction: column; }
        .sidebar-logo { padding: 20px 24px; font-size: 18px; font-weight: 700; color: #409eff; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-menu { flex: 1; padding: 12px 0; }
        .sidebar-menu a { display: flex; align-items: center; gap: 10px; padding: 12px 24px; color: #a0a3bd; font-size: 14px; transition: all .2s; }
        .sidebar-menu a:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sidebar-menu a.active { background: #409eff; color: #fff; }
        .sidebar-menu a span.icon { font-size: 18px; width: 24px; text-align: center; }

        /* 主区域 */
        .content { flex: 1; display: flex; flex-direction: column; }
        .header { display: flex; align-items: center; justify-content: space-between; padding: 0 28px; height: 56px; background: #fff; border-bottom: 1px solid #eee; }
        .header-title { font-size: 16px; font-weight: 500; }
        .header-right { display: flex; align-items: center; gap: 16px; font-size: 14px; color: #666; }
        .header-right a { color: #409eff; }
        .body { flex: 1; padding: 24px; background: #f0f2f5; }

        .card { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        .card-title { font-size: 16px; font-weight: 500; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 14px; text-align: left; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        th { color: #888; font-weight: 500; background: #fafafa; }
        .tag { display: inline-block; padding: 2px 10px; border-radius: 4px; font-size: 12px; }
        .tag-green { background: #e8f5e9; color: #388e3c; }
        .tag-red { background: #fde8e8; color: #d32f2f; }
        .tag-gray { background: #f5f5f5; color: #999; }

        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        .stat-card .label { font-size: 13px; color: #999; margin-bottom: 8px; }
        .stat-card .value { font-size: 28px; font-weight: 600; color: #333; }

        
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-logo"><?php if (!empty($site['site_logo'])): ?><img src="<?= htmlspecialchars($site['site_logo']) ?>" alt="<?= htmlspecialchars($site['site_name'] ?? 'RelayOps') ?>" style="height: 28px; vertical-align: middle;"><?php else: ?>⚡ <?= htmlspecialchars($site['site_name'] ?? 'RelayOps') ?><?php endif; ?></div>
            <nav class="sidebar-menu">
                <a href="/admin/dashboard" class="<?php if($active == 'dashboard'): ?>active<?php endif; ?>">
                    <span class="icon">📊</span> 仪表盘
                </a>
                <a href="/admin/nodes" class="<?php if($active == 'nodes'): ?>active<?php endif; ?>">
                    <span class="icon">🖥️</span> 节点管理
                </a>
                <a href="/admin/products" class="<?php if($active == 'products'): ?>active<?php endif; ?>">
                    <span class="icon">📦</span> 商品管理
                </a>
                <a href="/admin/users" class="<?php if($active == 'users'): ?>active<?php endif; ?>">
                    <span class="icon">👥</span> 用户管理
                </a>
                <a href="/admin/settings" class="<?php if($active == 'settings'): ?>active<?php endif; ?>">
                    <span class="icon">⚙️</span> 系统设置
                </a>
            </nav>
        </aside>

        <div class="content">
            <header class="header">
                <div class="header-title">仪表盘</div>
                <div class="header-right">
                    <span><?= session('admin_username') ?: '管理员' ?></span>
                    <a href="/api/admin/logout" onclick="return confirm('确定退出？')">退出</a>
                </div>
            </header>

            <div class="body">
                

<div class="stat-grid">
    <div class="stat-card">
        <div class="label">节点总数</div>
        <div class="value"><?php echo htmlentities((string) (isset($nodeCount) && ($nodeCount !== '')?$nodeCount:'0')); ?></div>
    </div>
    <div class="stat-card">
        <div class="label">在线节点</div>
        <div class="value" style="color:#388e3c;"><?php echo htmlentities((string) (isset($onlineCount) && ($onlineCount !== '')?$onlineCount:'0')); ?></div>
    </div>
    <div class="stat-card">
        <div class="label">用户总数</div>
        <div class="value"><?php echo htmlentities((string) (isset($userCount) && ($userCount !== '')?$userCount:'0')); ?></div>
    </div>
    <div class="stat-card">
        <div class="label">订单总数</div>
        <div class="value"><?php echo htmlentities((string) (isset($orderCount) && ($orderCount !== '')?$orderCount:'0')); ?></div>
    </div>
    <div class="stat-card">
        <div class="label">本月收入</div>
        <div class="value" style="color:#f56c6c;">¥<?php echo htmlentities((string) (isset($monthlyIncome) && ($monthlyIncome !== '')?$monthlyIncome:'0')); ?></div>
    </div>
    <div class="stat-card">
        <div class="label">系统状态</div>
        <div class="value" style="color:#388e3c; font-size:18px; padding-top:8px;">运行中</div>
    </div>
</div>

<div class="card">
    <div class="card-title">系统信息</div>
    <table>
        <tr><td style="width:160px; color:#888;">系统版本</td><td><?php echo isset($site['site_name']) ? htmlentities((string) $site['site_name']) : 'RelayOps'; ?> v1.0</td></tr>
        <tr><td style="color:#888;">ThinkPHP</td><td>v<?php echo htmlentities((string) $tpVersion); ?></td></tr>
        <tr><td style="color:#888;">PHP 版本</td><td><?php echo htmlentities((string) $phpVersion); ?></td></tr>
    </table>
</div>

            </div>
        </div>
    </div>
</body>
</html>
