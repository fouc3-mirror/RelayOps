<?php /*a:2:{s:44:"D:\frps-tp\RelayOps-php\view/index\shop.html";i:1782558793;s:45:"D:\frps-tp\RelayOps-php\view/layout_user.html";i:1782556353;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品列表 - RelayOps</title>
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
        
<div style="max-width:1100px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <h2 style="margin:0;">购买隧道节点</h2>
        <a href="/console" style="color:#409eff;text-decoration:none;font-size:14px;">← 返回控制台</a>
    </div>

    <div id="productList" style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div style="text-align:center;padding:60px;color:#999;grid-column:1/-1;">加载中...</div>
    </div>
</div>

<script>
fetch('/api/user/products', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.code !== 1 || !res.data || !res.data.length) {
            document.getElementById('productList').innerHTML = '<div style="text-align:center;padding:80px;color:#999;grid-column:1/-1;"><div style="font-size:48px;margin-bottom:16px;">📦</div><p>暂无在售商品</p></div>';
            return;
        }
        var html = res.data.map(function(p) {
            var tagColor = {tcp:'#409eff',udp:'#e6a23c',http:'#67c23a',https:'#f56c6c'}[p.proxy_type] || '#999';
            return '<div style="background:#fff;border-radius:10px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.06);display:flex;flex-direction:column;">' +
                '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">' +
                    '<h3 style="margin:0;font-size:16px;">' + p.name + '</h3>' +
                    '<span style="background:' + tagColor + ';color:#fff;padding:2px 10px;border-radius:4px;font-size:12px;">' + p.proxy_type.toUpperCase() + '</span>' +
                '</div>' +
                '<div style="font-size:13px;color:#666;margin-bottom:6px;">🖥️ ' + p.node_name + '</div>' +
                '<div style="font-size:13px;color:#999;margin-bottom:8px;">端口范围：<span style="font-family:monospace;">' + p.port_start + '-' + p.port_end + '</span></div>' +
                '<div style="font-size:14px;color:#f56c6c;margin-bottom:8px;">¥<strong style="font-size:22px;">' + parseFloat(p.price).toFixed(2) + '</strong>/月</div>' +
                (p.description ? '<div style="font-size:12px;color:#999;margin-bottom:8px;">' + p.description + '</div>' : '') +
                '<div style="flex:1;"></div>' +
                '<a href="/product/' + p.id + '" style="display:block;text-align:center;background:#409eff;color:#fff;padding:10px;border-radius:6px;text-decoration:none;font-size:14px;margin-top:12px;">立即订购</a>' +
            '</div>';
        }).join('');
        document.getElementById('productList').innerHTML = html;
    })
    .catch(function() {
        document.getElementById('productList').innerHTML = '<div style="text-align:center;padding:40px;color:#f56c6c;grid-column:1/-1;">加载失败</div>';
    });
</script>

    </div>

    <footer class="footer">
        Copyright &copy; <?= date('Y') ?> RelayOps. All rights reserved.
    </footer>
</body>
</html>
