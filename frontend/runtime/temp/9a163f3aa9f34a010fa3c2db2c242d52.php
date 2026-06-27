<?php /*a:2:{s:48:"D:\frps-tp\RelayOps-php\view/admin\products.html";i:1782559996;s:46:"D:\frps-tp\RelayOps-php\view/layout_admin.html";i:1782560509;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理 - <?php echo isset($site['site_name']) ? htmlentities((string) $site['site_name']) : 'RelayOps'; ?></title>
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

        
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.form-row.triple { grid-template-columns: 1fr 1fr 1fr; }
.form-row.single { grid-template-columns: 1fr; }
.form-group { margin-bottom: 0; }
.form-group label { display: block; margin-bottom: 6px; font-size: 14px; color: #555; font-weight: 500; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid #dcdfe6; border-radius: 6px; font-size: 14px; outline: none; transition: border .2s; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #409eff; }
.form-group textarea { resize: vertical; min-height: 60px; }
.form-hint { font-size: 12px; color: #999; margin-top: 4px; }
.required { color: #f56c6c; }

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
                <div class="header-title">商品管理</div>
                <div class="header-right">
                    <span><?= session('admin_username') ?: '管理员' ?></span>
                    <a href="/api/admin/logout" onclick="return confirm('确定退出？')">退出</a>
                </div>
            </header>

            <div class="body">
                
<!-- 商品列表 -->
<div class="card" id="listPanel">
    <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
        <span>商品列表</span>
        <a href="javascript:;" onclick="showForm()" style="padding:6px 16px; background:#409eff; color:#fff; border-radius:4px; font-size:13px;">+ 添加商品</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>商品名称</th>
                <th>节点</th>
                <th>代理类型</th>
                <th>端口范围</th>
                <th>月单价</th>
                <th>可选时长</th>
                <th>状态</th>
                <th>排序</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody id="productList">
            <tr><td colspan="10" style="text-align:center; color:#999; padding:40px;">加载中...</td></tr>
        </tbody>
    </table>
</div>

<!-- 添加/编辑表单 -->
<div class="card" id="formPanel" style="display:none;">
    <div class="card-title" id="formTitle">添加商品</div>
    <form onsubmit="return false;">
        <input type="hidden" id="editId" value="0">

        <div class="form-row">
            <div class="form-group">
                <label>商品名称 <span class="required">*</span></label>
                <input type="text" id="p_name" placeholder="例如：北京节点-TCP隧道">
            </div>
            <div class="form-group">
                <label>关联节点 <span class="required">*</span></label>
                <select id="p_node_id"><option value="">加载中...</option></select>
            </div>
        </div>

        <div class="form-row triple">
            <div class="form-group">
                <label>代理类型 <span class="required">*</span></label>
                <select id="p_proxy_type">
                    <option value="tcp">TCP</option>
                    <option value="udp">UDP</option>
                    <option value="http">HTTP</option>
                    <option value="https">HTTPS</option>
                </select>
            </div>
            <div class="form-group">
                <label>端口范围起始 <span class="required">*</span></label>
                <input type="number" id="p_port_start" placeholder="例如 10000">
            </div>
            <div class="form-group">
                <label>端口范围结束 <span class="required">*</span></label>
                <input type="number" id="p_port_end" placeholder="例如 10100">
            </div>
        </div>

        <div class="form-row triple">
            <div class="form-group">
                <label>月单价（元）<span class="required">*</span></label>
                <input type="number" id="p_price" step="0.01" placeholder="例如 10.00">
            </div>
            <div class="form-group">
                <label>可选时长（月）</label>
                <input type="text" id="p_duration_options" value="1,3,6,12" placeholder="逗号分隔，如 1,3,6,12">
                <div class="form-hint">用户购买时可选的月数</div>
            </div>
            <div class="form-group">
                <label>排序</label>
                <input type="number" id="p_sort" value="0" placeholder="越小越靠前">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>状态</label>
                <select id="p_status">
                    <option value="1">上架</option>
                    <option value="0">下架</option>
                </select>
            </div>
            <div class="form-group">
                <label>商品描述</label>
                <input type="text" id="p_description" placeholder="选填">
            </div>
        </div>

        <div class="form-actions" style="display:flex; gap:12px; margin-top:24px; padding-top:20px; border-top:1px solid #f0f0f0;">
            <button type="button" class="btn btn-primary" onclick="saveProduct()" style="padding:10px 24px; border-radius:6px; font-size:14px; cursor:pointer; border:none; background:#409eff; color:#fff;">保存商品</button>
            <button type="button" onclick="hideForm()" style="padding:10px 24px; border-radius:6px; font-size:14px; cursor:pointer; border:1px solid #dcdfe6; background:#f5f7fa; color:#666;">取消</button>
        </div>
        <div id="formMsg" style="font-size:13px; min-height:20px; margin-top:8px;"></div>
    </form>
</div>

<script>
var nodes = [];

// 加载节点列表
fetch('/api/admin/nodes', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        nodes = res.data?.list || [];
        var sel = document.getElementById('p_node_id');
        sel.innerHTML = '<option value="">请选择节点</option>' +
            nodes.map(function(n) {
                return '<option value="' + n.id + '">' + n.name + ' (' + n.server_addr + ')</option>';
            }).join('');
    });

// 加载商品列表
function loadProducts() {
    fetch('/api/admin/products', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            var list = res.data || [];
            var tbody = document.getElementById('productList');
            if (!list.length) {
                tbody.innerHTML = '<tr><td colspan="10" style="text-align:center; color:#999; padding:40px;">暂无商品</td></tr>';
                return;
            }
            tbody.innerHTML = list.map(function(p) {
                var proxyLabel = {tcp:'TCP',udp:'UDP',http:'HTTP',https:'HTTPS'}[p.proxy_type] || p.proxy_type;
                return '<tr>' +
                    '<td>' + p.id + '</td>' +
                    '<td><strong>' + p.name + '</strong></td>' +
                    '<td>' + (p.node_name || '-') + '</td>' +
                    '<td><span class="tag tag-green">' + proxyLabel + '</span></td>' +
                    '<td style="font-family:monospace;">' + p.port_start + '-' + p.port_end + '</td>' +
                    '<td style="color:#f56c6c;font-weight:600;">¥' + parseFloat(p.price).toFixed(2) + '/月</td>' +
                    '<td>' + (p.duration_options || '1') + ' 个月</td>' +
                    '<td><span class="tag ' + (p.status == 1 ? 'tag-green' : 'tag-red') + '">' + (p.status == 1 ? '上架' : '下架') + '</span></td>' +
                    '<td>' + p.sort + '</td>' +
                    '<td>' +
                        '<a href="javascript:;" onclick="editProduct(' + p.id + ')" style="color:#409eff;margin-right:8px;">编辑</a>' +
                        '<a href="javascript:;" onclick="toggleProduct(' + p.id + ')" style="color:#e6a23c;margin-right:8px;">' + (p.status == 1 ? '下架' : '上架') + '</a>' +
                        '<a href="javascript:;" onclick="deleteProduct(' + p.id + ')" style="color:#f56c6c;">删除</a>' +
                    '</td>' +
                '</tr>';
            }).join('');
        })
        .catch(function() {
            document.getElementById('productList').innerHTML = '<tr><td colspan="10" style="text-align:center; color:#f56c6c; padding:40px;">加载失败</td></tr>';
        });
}

function showForm() {
    document.getElementById('listPanel').style.display = 'none';
    document.getElementById('formPanel').style.display = 'block';
    document.getElementById('formTitle').textContent = '添加商品';
    document.getElementById('editId').value = '0';
    // 清空表单
    document.getElementById('p_name').value = '';
    document.getElementById('p_node_id').value = '';
    document.getElementById('p_proxy_type').value = 'tcp';
    document.getElementById('p_port_start').value = '';
    document.getElementById('p_port_end').value = '';
    document.getElementById('p_price').value = '';
    document.getElementById('p_duration_options').value = '1,3,6,12';
    document.getElementById('p_sort').value = '0';
    document.getElementById('p_status').value = '1';
    document.getElementById('p_description').value = '';
    document.getElementById('formMsg').textContent = '';
}

function hideForm() {
    document.getElementById('listPanel').style.display = 'block';
    document.getElementById('formPanel').style.display = 'none';
}

function editProduct(id) {
    fetch('/api/admin/products', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            var p = (res.data || []).find(function(item) { return item.id == id; });
            if (!p) return;
            showForm();
            document.getElementById('formTitle').textContent = '编辑商品';
            document.getElementById('editId').value = p.id;
            document.getElementById('p_name').value = p.name;
            document.getElementById('p_node_id').value = p.node_id;
            document.getElementById('p_proxy_type').value = p.proxy_type;
            document.getElementById('p_port_start').value = p.port_start;
            document.getElementById('p_port_end').value = p.port_end;
            document.getElementById('p_price').value = p.price;
            document.getElementById('p_duration_options').value = p.duration_options || '1,3,6,12';
            document.getElementById('p_sort').value = p.sort;
            document.getElementById('p_status').value = p.status;
            document.getElementById('p_description').value = p.description || '';
        });
}

async function saveProduct() {
    var msg = document.getElementById('formMsg');
    var data = {
        id: document.getElementById('editId').value,
        name: document.getElementById('p_name').value,
        node_id: document.getElementById('p_node_id').value,
        proxy_type: document.getElementById('p_proxy_type').value,
        port_start: document.getElementById('p_port_start').value,
        port_end: document.getElementById('p_port_end').value,
        price: document.getElementById('p_price').value,
        duration_options: document.getElementById('p_duration_options').value,
        sort: document.getElementById('p_sort').value,
        status: document.getElementById('p_status').value,
        description: document.getElementById('p_description').value,
    };

    if (!data.name || !data.node_id || !data.port_start || !data.port_end || !data.price) {
        msg.style.color = '#f56c6c';
        msg.textContent = '请填写所有必填项';
        return;
    }

    msg.style.color = '#999';
    msg.textContent = '保存中...';

    try {
        var res = await fetch('/api/admin/product/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data)
        });
        var json = await res.json();
        if (json.code === 1) {
            msg.style.color = '#388e3c';
            msg.textContent = json.msg;
            setTimeout(function() { hideForm(); loadProducts(); }, 800);
        } else {
            msg.style.color = '#f56c6c';
            msg.textContent = json.msg || '保存失败';
        }
    } catch(err) {
        msg.style.color = '#f56c6c';
        msg.textContent = '网络错误';
    }
}

async function toggleProduct(id) {
    try {
        var res = await fetch('/api/admin/product/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ id: id })
        });
        var json = await res.json();
        if (json.code === 1) loadProducts();
    } catch(err) {}
}

async function deleteProduct(id) {
    if (!confirm('确定删除此商品？')) return;
    try {
        var res = await fetch('/api/admin/product/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ id: id })
        });
        var json = await res.json();
        if (json.code === 1) loadProducts();
    } catch(err) {}
}

loadProducts();
</script>

            </div>
        </div>
    </div>
</body>
</html>
