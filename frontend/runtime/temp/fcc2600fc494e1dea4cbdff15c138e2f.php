<?php /*a:2:{s:45:"D:\frps-tp\RelayOps-php\view/index\index.html";i:1782560998;s:45:"D:\frps-tp\RelayOps-php\view/layout_user.html";i:1782560893;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($site['site_name']) ? htmlentities((string) $site['site_name']) : 'RelayOps'; ?> - FRPS 节点管理</title>
    <link rel="icon" href="<?php echo isset($site['site_favicon']) ? htmlentities((string) $site['site_favicon']) : '/favicon.ico'; ?>" type="image/x-icon">
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
        
    </style>
</head>
<body>
    <nav class="nav">
        <a href="/" class="nav-logo"><?php if (!empty($site['site_logo'])): ?><img src="<?= htmlspecialchars($site['site_logo']) ?>" alt="<?= htmlspecialchars($site['site_name'] ?? 'RelayOps') ?>" style="height: 32px; vertical-align: middle;"><?php else: ?>⚡ <?= htmlspecialchars($site['site_name'] ?? 'RelayOps') ?><?php endif; ?></a>
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
        
<div style="max-width: 900px; margin: 0 auto; text-align: center; padding: 80px 20px;">
    <h1 style="font-size: 42px; margin-bottom: 16px; color: #1a1a1a;">⚡ <?= htmlspecialchars($site['site_name'] ?? 'RelayOps') ?></h1>
    <p style="font-size: 18px; color: #666; margin-bottom: 40px;"><?php echo isset($site['site_description']) ? htmlentities((string) $site['site_description']) : '高效管理你的 FRPS 节点，一站式反向代理管理平台'; ?></p>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 40px;">
        <div style="background: #fff; padding: 32px 24px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <div style="font-size: 36px; margin-bottom: 12px;">🖥️</div>
            <h3 style="margin-bottom: 8px;">节点管理</h3>
            <p style="color: #888; font-size: 14px;">集中管理多个 FRPS 节点，实时监控状态</p>
        </div>
        <div style="background: #fff; padding: 32px 24px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <div style="font-size: 36px; margin-bottom: 12px;">🔒</div>
            <h3 style="margin-bottom: 8px;">安全可靠</h3>
            <p style="color: #888; font-size: 14px;">管理员与用户权限分离，数据安全有保障</p>
        </div>
        <div style="background: #fff; padding: 32px 24px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <div style="font-size: 36px; margin-bottom: 12px;">📊</div>
            <h3 style="margin-bottom: 8px;">实时监控</h3>
            <p style="color: #888; font-size: 14px;">节点心跳检测，异常即时告警</p>
        </div>
    </div>

    <div style="margin-top: 48px; display: flex; gap: 16px; justify-content: center;">
        <?php if (session('user_id')): ?>
            <a href="/console" class="btn btn-primary" style="padding: 12px 36px; font-size: 16px;">进入控制台</a>
            <a href="/console/shop" class="btn" style="padding: 12px 36px; font-size: 16px; background: #fff; color: #333; border: 1px solid #dcdfe6; border-radius: 6px;">选购商品</a>
        <?php else: ?>
            <a href="/login" class="btn btn-primary" style="padding: 12px 36px; font-size: 16px;">立即登录</a>
        <?php endif; ?>
    </div>
</div>

    </div>

    <footer class="footer">
        <?= !empty($site['site_footer']) ? $site['site_footer'] : 'Copyright &copy; ' . date('Y') . ' ' . htmlspecialchars($site['site_name'] ?? 'RelayOps') . '. All rights reserved.' ?>
    </footer>
</body>
</html>
