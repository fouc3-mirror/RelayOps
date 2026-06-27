<?php /*a:2:{s:47:"D:\frps-tp\RelayOps-php\view/index\product.html";i:1782557612;s:45:"D:\frps-tp\RelayOps-php\view/layout_user.html";i:1782556353;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品详情 - RelayOps</title>
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
        
<div style="max-width:800px;margin:0 auto;">
    <a href="/console/shop" style="color:#409eff;text-decoration:none;font-size:14px;display:inline-block;margin-bottom:24px;">← 返回商品列表</a>

    <div id="productDetail" style="background:#fff;border-radius:10px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
        <div style="text-align:center;padding:40px;color:#999;">加载中...</div>
    </div>
</div>

<script>
var productId = window.location.pathname.split('/').pop();

fetch('/api/user/product/' + productId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.code !== 1 || !res.data) {
            document.getElementById('productDetail').innerHTML = '<div style="text-align:center;padding:60px;color:#999;"><div style="font-size:48px;margin-bottom:16px;">❌</div><p>' + (res.msg || '商品不存在') + '</p><a href="/console/shop" style="color:#409eff;margin-top:16px;display:inline-block;">返回商品列表</a></div>';
            return;
        }
        var p = res.data;
        var tagColor = {tcp:'#409eff',udp:'#e6a23c',http:'#67c23a',https:'#f56c6c'}[p.proxy_type] || '#999';

        // 生成端口选项
        var portOptions = '';
        if (p.available_ports && p.available_ports.length > 0) {
            portOptions = p.available_ports.map(function(port) {
                return '<option value="' + port + '">端口 ' + port + '</option>';
            }).join('');
        }

        // 生成时长选项
        var durationOptions = '';
        if (p.durations && p.durations.length > 0) {
            durationOptions = p.durations.map(function(d) {
                return '<option value="' + d + '">' + d + ' 个月</option>';
            }).join('');
        }

        document.getElementById('productDetail').innerHTML =
            '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">' +
                '<h1 style="margin:0;font-size:24px;">' + p.name + '</h1>' +
                '<span style="background:' + tagColor + ';color:#fff;padding:4px 12px;border-radius:4px;font-size:14px;">' + p.proxy_type.toUpperCase() + '</span>' +
            '</div>' +
            '<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:24px;">' +
                '<div><div style="font-size:13px;color:#999;margin-bottom:4px;">节点</div><div style="font-size:15px;font-weight:500;">' + p.node_name + '</div></div>' +
                '<div><div style="font-size:13px;color:#999;margin-bottom:4px;">服务器</div><div style="font-size:15px;font-weight:500;">' + p.server_addr + ':' + p.server_port + '</div></div>' +
                '<div><div style="font-size:13px;color:#999;margin-bottom:4px;">端口范围</div><div style="font-size:15px;font-weight:500;font-family:monospace;">' + p.port_start + ' - ' + p.port_end + '</div></div>' +
                '<div><div style="font-size:13px;color:#999;margin-bottom:4px;">可用端口</div><div style="font-size:15px;font-weight:500;' + (p.available_count === 0 ? 'color:#f56c6c;' : '') + '">' + p.available_count + ' 个</div></div>' +
            '</div>' +
            (p.description ? '<div style="margin-bottom:24px;"><h3 style="font-size:16px;margin-bottom:8px;">商品描述</h3><p style="color:#666;line-height:1.6;">' + p.description + '</p></div>' : '') +
            '<hr style="border:none;border-top:1px solid #f0f0f0;margin:24px 0;">' +
            '<h3 style="font-size:18px;margin-bottom:20px;">订购配置</h3>' +
            '<div style="margin-bottom:20px;">' +
                '<label style="display:block;margin-bottom:8px;font-size:14px;font-weight:500;">选择端口</label>' +
                '<select id="portSelect" style="width:100%;padding:10px 14px;border:1px solid #dcdfe6;border-radius:6px;font-size:14px;">' +
                    (portOptions || '<option value="">暂无可用端口</option>') +
                '</select>' +
                '<div style="font-size:12px;color:#999;margin-top:4px;">已选端口将独占使用，直到订单到期</div>' +
            '</div>' +
            '<div style="margin-bottom:20px;">' +
                '<label style="display:block;margin-bottom:8px;font-size:14px;font-weight:500;">购买时长</label>' +
                '<select id="durationSelect" style="width:100%;padding:10px 14px;border:1px solid #dcdfe6;border-radius:6px;font-size:14px;" onchange="updatePrice()">' +
                    durationOptions +
                '</select>' +
            '</div>' +
            '<div style="margin-bottom:24px;">' +
                '<label style="display:block;margin-bottom:8px;font-size:14px;font-weight:500;">费用合计</label>' +
                '<div style="display:flex;align-items:baseline;gap:8px;">' +
                    '<span style="font-size:20px;font-weight:600;color:#f56c6c;">¥' + parseFloat(p.price).toFixed(2) + '</span>' +
                    '<span style="font-size:14px;color:#999;">/月 × <span id="durationText">1</span> 个月 =</span>' +
                    '<span id="totalPrice" style="font-size:28px;font-weight:700;color:#f56c6c;">¥' + parseFloat(p.price).toFixed(2) + '</span>' +
                '</div>' +
            '</div>' +
            '<div style="text-align:center;">' +
                '<button id="orderBtn" onclick="createOrder()" style="background:#409eff;color:#fff;border:none;padding:12px 48px;border-radius:6px;font-size:16px;cursor:pointer;">立即订购</button>' +
            '</div>';

        // 存储商品数据
        window.productData = p;
        updatePrice();
    })
    .catch(function(err) {
        document.getElementById('productDetail').innerHTML = '<div style="text-align:center;padding:40px;color:#f56c6c;">网络错误</div>';
    });

function updatePrice() {
    if (!window.productData) return;
    var duration = parseInt(document.getElementById('durationSelect').value) || 1;
    var total = window.productData.price * duration;
    document.getElementById('durationText').textContent = duration;
    document.getElementById('totalPrice').textContent = '¥' + total.toFixed(2);
}

function createOrder() {
    var port = document.getElementById('portSelect').value;
    var duration = parseInt(document.getElementById('durationSelect').value) || 1;
    var btn = document.getElementById('orderBtn');

    if (!port) {
        alert('请选择端口');
        return;
    }

    btn.disabled = true;
    btn.textContent = '下单中...';

    fetch('/api/user/order/create-direct', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            product_id: window.productData.id,
            port: parseInt(port),
            duration: duration
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.code === 1 && res.data) {
            if (res.data.pay_url) {
                window.location.href = res.data.pay_url;
            } else {
                alert('订单创建成功');
                window.location.href = '/console/orders';
            }
        } else {
            alert(res.msg || '下单失败');
            btn.disabled = false;
            btn.textContent = '立即订购';
        }
    })
    .catch(function() {
        alert('网络错误');
        btn.disabled = false;
        btn.textContent = '立即订购';
    });
}
</script>

    </div>

    <footer class="footer">
        Copyright &copy; <?= date('Y') ?> RelayOps. All rights reserved.
    </footer>
</body>
</html>
