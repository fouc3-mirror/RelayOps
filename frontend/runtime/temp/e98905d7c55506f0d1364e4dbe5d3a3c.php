<?php /*a:2:{s:45:"D:\frps-tp\RelayOps-php\view/admin\users.html";i:1782562762;s:46:"D:\frps-tp\RelayOps-php\view/layout_admin.html";i:1782560509;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理 - <?php echo isset($site['site_name']) ? htmlentities((string) $site['site_name']) : 'RelayOps'; ?></title>
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
                <div class="header-title">用户管理</div>
                <div class="header-right">
                    <span><?= session('admin_username') ?: '管理员' ?></span>
                    <a href="/api/admin/logout" onclick="return confirm('确定退出？')">退出</a>
                </div>
            </header>

            <div class="body">
                

<div class="card">
    <div class="card-title" style="display:flex; justify-content:space-between; align-items:center;">
        <span>用户列表</span>
        <a href="javascript:;" onclick="showAddModal()" style="padding:6px 16px; background:#409eff; color:#fff; border-radius:4px; font-size:13px;">+ 添加用户</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>用户名</th>
                <th>昵称</th>
                <th>邮箱</th>
                <th>手机</th>
                <th>状态</th>
                <th>注册时间</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody id="userList">
            <tr><td colspan="8" style="text-align:center; color:#999; padding:40px;">加载中...</td></tr>
        </tbody>
    </table>
</div>

<!-- 添加/编辑用户模态框 -->
<div id="userModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:8px; padding:24px; width:500px; max-height:90vh; overflow-y:auto;">
        <h3 id="modalTitle" style="margin:0 0 20px 0; font-size:18px;">添加用户</h3>
        <form id="userForm">
            <input type="hidden" id="userId" value="0">

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-size:14px; font-weight:500;">用户名 <span style="color:#f56c6c;">*</span></label>
                <input type="text" id="userName" placeholder="3-20位字母数字下划线" style="width:100%; padding:8px 12px; border:1px solid #dcdfe6; border-radius:4px; font-size:14px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-size:14px; font-weight:500;">密码 <span id="passwordRequired" style="color:#f56c6c;">*</span></label>
                <input type="password" id="userPassword" placeholder="编辑时留空则不修改密码" style="width:100%; padding:8px 12px; border:1px solid #dcdfe6; border-radius:4px; font-size:14px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-size:14px; font-weight:500;">昵称</label>
                <input type="text" id="userNickname" placeholder="留空则使用用户名" style="width:100%; padding:8px 12px; border:1px solid #dcdfe6; border-radius:4px; font-size:14px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-size:14px; font-weight:500;">邮箱 <span style="color:#f56c6c;">*</span></label>
                <input type="email" id="userEmail" placeholder="user@example.com" style="width:100%; padding:8px 12px; border:1px solid #dcdfe6; border-radius:4px; font-size:14px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-size:14px; font-weight:500;">手机</label>
                <input type="text" id="userPhone" placeholder="选填" style="width:100%; padding:8px 12px; border:1px solid #dcdfe6; border-radius:4px; font-size:14px;">
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; margin-bottom:6px; font-size:14px; font-weight:500;">状态</label>
                <select id="userStatus" style="width:100%; padding:8px 12px; border:1px solid #dcdfe6; border-radius:4px; font-size:14px;">
                    <option value="1">正常</option>
                    <option value="0">禁用</option>
                </select>
            </div>

            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="closeModal()" style="padding:8px 20px; border:1px solid #dcdfe6; border-radius:4px; font-size:14px; cursor:pointer; background:#fff;">取消</button>
                <button type="submit" style="padding:8px 20px; background:#409eff; color:#fff; border:none; border-radius:4px; font-size:14px; cursor:pointer;">保存</button>
            </div>
        </form>
    </div>
</div>

<script>
// 加载用户列表
function loadUsers() {
    fetch('/api/admin/users', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            var list = res.data || [];
            var tbody = document.getElementById('userList');
            if (!list.length) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color:#999; padding:40px;">暂无用户</td></tr>';
                return;
            }
            tbody.innerHTML = list.map(function(u) {
                return '<tr>' +
                    '<td>' + u.id + '</td>' +
                    '<td><strong>' + u.username + '</strong></td>' +
                    '<td>' + (u.nickname || '-') + '</td>' +
                    '<td>' + (u.email || '-') + '</td>' +
                    '<td>' + (u.phone || '-') + '</td>' +
                    '<td><span class="tag ' + (u.status == 1 ? 'tag-green' : 'tag-red') + '">' + (u.status == 1 ? '正常' : '禁用') + '</span></td>' +
                    '<td>' + (u.create_time || '-') + '</td>' +
                    '<td>' +
                        '<a href="javascript:;" onclick="editUser(' + u.id + ')" style="color:#409eff; margin-right:8px;">编辑</a>' +
                        '<a href="javascript:;" onclick="toggleUser(' + u.id + ')" style="color:#e6a23c; margin-right:8px;">' + (u.status == 1 ? '禁用' : '启用') + '</a>' +
                        '<a href="javascript:;" onclick="deleteUser(' + u.id + ')" style="color:#f56c6c;">删除</a>' +
                    '</td>' +
                '</tr>';
            }).join('');
        })
        .catch(function() {
            document.getElementById('userList').innerHTML = '<tr><td colspan="8" style="text-align:center; color:#f56c6c; padding:40px;">加载失败</td></tr>';
        });
}

// 显示添加模态框
function showAddModal() {
    document.getElementById('modalTitle').textContent = '添加用户';
    document.getElementById('userId').value = '0';
    document.getElementById('userForm').reset();
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('userModal').style.display = 'flex';
}

// 编辑用户
function editUser(id) {
    fetch('/api/admin/user/detail?id=' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.code !== 1) {
                alert(res.msg || '加载失败');
                return;
            }
            var u = res.data;
            document.getElementById('modalTitle').textContent = '编辑用户';
            document.getElementById('userId').value = u.id;
            document.getElementById('userName').value = u.username;
            document.getElementById('userPassword').value = '';
            document.getElementById('userNickname').value = u.nickname || '';
            document.getElementById('userEmail').value = u.email || '';
            document.getElementById('userPhone').value = u.phone || '';
            document.getElementById('userStatus').value = u.status;
            document.getElementById('passwordRequired').style.display = 'none';
            document.getElementById('userModal').style.display = 'flex';
        });
}

// 关闭模态框
function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

// 保存用户
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var data = {
        id: document.getElementById('userId').value,
        username: document.getElementById('userName').value,
        password: document.getElementById('userPassword').value,
        nickname: document.getElementById('userNickname').value,
        email: document.getElementById('userEmail').value,
        phone: document.getElementById('userPhone').value,
        status: document.getElementById('userStatus').value,
    };

    fetch('/api/admin/user/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.code === 1) {
            alert(res.msg);
            closeModal();
            loadUsers();
        } else {
            alert(res.msg || '保存失败');
        }
    })
    .catch(function() {
        alert('网络错误');
    });
});

// 切换用户状态
function toggleUser(id) {
    if (!confirm('确定切换用户状态？')) return;

    fetch('/api/admin/user/toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.code === 1) {
            alert(res.msg);
            loadUsers();
        } else {
            alert(res.msg || '操作失败');
        }
    });
}

// 删除用户
function deleteUser(id) {
    if (!confirm('确定删除该用户？此操作不可恢复！')) return;

    fetch('/api/admin/user/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.code === 1) {
            alert(res.msg);
            loadUsers();
        } else {
            alert(res.msg || '删除失败');
        }
    });
}

// 页面加载时获取用户列表
loadUsers();
</script>

            </div>
        </div>
    </div>
</body>
</html>
