<?php /*a:2:{s:48:"D:\frps-tp\RelayOps-php\view/admin\settings.html";i:1782559967;s:46:"D:\frps-tp\RelayOps-php\view/layout_admin.html";i:1782560509;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置 - <?php echo isset($site['site_name']) ? htmlentities((string) $site['site_name']) : 'RelayOps'; ?></title>
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

        
.tab-bar { display: flex; gap: 0; margin-bottom: 0; border-bottom: 2px solid #f0f0f0; }
.tab-bar .tab-item { padding: 10px 24px; cursor: pointer; font-size: 14px; color: #666; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all .2s; }
.tab-bar .tab-item:hover { color: #409eff; }
.tab-bar .tab-item.active { color: #409eff; border-bottom-color: #409eff; font-weight: 500; }
.tab-panel { display: none; padding-top: 20px; }
.tab-panel.active { display: block; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
.form-row.single { grid-template-columns: 1fr; }
.form-group { margin-bottom: 0; }
.form-group label { display: block; margin-bottom: 6px; font-size: 14px; color: #555; font-weight: 500; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid #dcdfe6; border-radius: 6px; font-size: 14px; outline: none; transition: border .2s; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: #409eff; }
.form-group textarea { resize: vertical; min-height: 80px; }
.form-hint { font-size: 12px; color: #999; margin-top: 4px; }
.required { color: #f56c6c; }
.form-actions { display: flex; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid #f0f0f0; }
.btn { padding: 10px 24px; border-radius: 6px; font-size: 14px; cursor: pointer; border: none; transition: all .2s; }
.btn-primary { background: #409eff; color: #fff; }
.btn-primary:hover { background: #337ecc; }
.btn-secondary { background: #f5f7fa; color: #666; border: 1px solid #dcdfe6; }
.btn-secondary:hover { background: #ecf5ff; color: #409eff; border-color: #b3d8ff; }
.form-tip { font-size: 13px; min-height: 20px; margin-top: 8px; }

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
                <div class="header-title">系统设置</div>
                <div class="header-right">
                    <span><?= session('admin_username') ?: '管理员' ?></span>
                    <a href="/api/admin/logout" onclick="return confirm('确定退出？')">退出</a>
                </div>
            </header>

            <div class="body">
                
<div class="card">
    <!-- 分类标签 -->
    <div class="tab-bar">
        <div class="tab-item active" onclick="switchTab('basic')">🌐 基本设置</div>
        <div class="tab-item" onclick="switchTab('system')">🔧 系统信息</div>
        <div class="tab-item" onclick="switchTab('email')">📧 邮件配置</div>
        <div class="tab-item" onclick="switchTab('pay')">💰 支付配置</div>
        <div class="tab-item" onclick="switchTab('redis')">⚡ Redis 配置</div>
    </div>

    <!-- 基本设置 -->
    <div id="tab-basic" class="tab-panel active">
        <form onsubmit="return false;">
            <div class="form-row">
                <div class="form-group">
                    <label>系统名称</label>
                    <input type="text" data-name="site_name" value="<?php echo isset($settings['basic']['site_name']) ? htmlentities((string) $settings['basic']['site_name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>网站关键词</label>
                    <input type="text" data-name="site_keywords" value="<?php echo isset($settings['basic']['site_keywords']) ? htmlentities((string) $settings['basic']['site_keywords']) : ''; ?>">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label>系统描述</label>
                    <textarea data-name="site_description"><?php echo isset($settings['basic']['site_description']) ? htmlentities((string) $settings['basic']['site_description']) : ''; ?></textarea>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>网站 Logo</label>
                    <input type="text" data-name="site_logo" value="<?php echo isset($settings['basic']['site_logo']) ? htmlentities((string) $settings['basic']['site_logo']) : ''; ?>" placeholder="图片URL">
                </div>
                <div class="form-group">
                    <label>网站 Favicon</label>
                    <input type="text" data-name="site_favicon" value="<?php echo isset($settings['basic']['site_favicon']) ? htmlentities((string) $settings['basic']['site_favicon']) : ''; ?>" placeholder="图标URL">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label>页脚代码</label>
                    <textarea data-name="site_footer"><?php echo isset($settings['basic']['site_footer']) ? htmlentities((string) $settings['basic']['site_footer']) : ''; ?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="saveGroup('basic')">保存基本设置</button>
            </div>
            <div id="msg-basic" class="form-tip"></div>
        </form>
    </div>

    <!-- 系统信息 -->
    <div id="tab-system" class="tab-panel">
        <form onsubmit="return false;">
            <div class="form-row">
                <div class="form-group">
                    <label>管理员邮箱</label>
                    <input type="email" data-name="admin_email" value="<?php echo isset($settings['system']['admin_email']) ? htmlentities((string) $settings['system']['admin_email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>管理员电话</label>
                    <input type="text" data-name="admin_phone" value="<?php echo isset($settings['system']['admin_phone']) ? htmlentities((string) $settings['system']['admin_phone']) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>ICP 备案号</label>
                    <input type="text" data-name="icp_number" value="<?php echo isset($settings['system']['icp_number']) ? htmlentities((string) $settings['system']['icp_number']) : ''; ?>">
                </div>
                <div class="form-group"></div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="saveGroup('system')">保存系统信息</button>
            </div>
            <div id="msg-system" class="form-tip"></div>
        </form>
    </div>

    <!-- 邮件配置 -->
    <div id="tab-email" class="tab-panel">
        <form onsubmit="return false;">
            <div class="form-row">
                <div class="form-group">
                    <label>SMTP 服务器 <span class="required">*</span></label>
                    <input type="text" data-name="smtp_host" value="<?php echo isset($settings['email']['smtp_host']) ? htmlentities((string) $settings['email']['smtp_host']) : ''; ?>" placeholder="smtp.qq.com">
                    <div class="form-hint">例如：smtp.qq.com / smtp.163.com / smtp.gmail.com</div>
                </div>
                <div class="form-group">
                    <label>SMTP 端口</label>
                    <input type="number" data-name="smtp_port" value="<?php echo isset($settings['email']['smtp_port']) ? htmlentities((string) $settings['email']['smtp_port']) : '465'; ?>" placeholder="465">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>SMTP 用户名 <span class="required">*</span></label>
                    <input type="text" data-name="smtp_user" value="<?php echo isset($settings['email']['smtp_user']) ? htmlentities((string) $settings['email']['smtp_user']) : ''; ?>" placeholder="your-email@qq.com">
                </div>
                <div class="form-group">
                    <label>SMTP 密码 <span class="required">*</span></label>
                    <input type="password" data-name="smtp_pass" value="<?php echo isset($settings['email']['smtp_pass']) ? htmlentities((string) $settings['email']['smtp_pass']) : ''; ?>" placeholder="授权码/密码">
                    <div class="form-hint">QQ邮箱等需要使用授权码，不是登录密码</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>发件人邮箱</label>
                    <input type="email" data-name="smtp_from" value="<?php echo isset($settings['email']['smtp_from']) ? htmlentities((string) $settings['email']['smtp_from']) : ''; ?>" placeholder="留空则使用SMTP用户名">
                </div>
                <div class="form-group">
                    <label>发件人名称</label>
                    <input type="text" data-name="smtp_name" value="<?php echo isset($settings['email']['smtp_name']) ? htmlentities((string) $settings['email']['smtp_name']) : 'RelayOps'; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>启用 SSL</label>
                    <select data-name="smtp_ssl">
                        <option value="1" {($settings.email.smtp_ssl ?? '1') === '1' ? 'selected' : ''}>启用</option>
                        <option value="0" {($settings.email.smtp_ssl ?? '1') === '0' ? 'selected' : ''}>禁用</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>验证码有效期（秒）</label>
                    <input type="number" data-name="verify_expire" value="<?php echo isset($settings['email']['verify_expire']) ? htmlentities((string) $settings['email']['verify_expire']) : '300'; ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="saveGroup('email')">保存邮件配置</button>
                <button type="button" class="btn btn-secondary" onclick="testEmail()">发送测试邮件</button>
            </div>
            <div id="msg-email" class="form-tip"></div>
        </form>
    </div>

    <!-- 支付配置 -->
    <div id="tab-pay" class="tab-panel">
        <form onsubmit="return false;">
            <div class="form-row single">
                <div class="form-group">
                    <label>易支付接口地址 <span class="required">*</span></label>
                    <input type="text" data-name="epay_url" value="<?php echo isset($settings['pay']['epay_url']) ? htmlentities((string) $settings['pay']['epay_url']) : ''; ?>" placeholder="https://pay.example.com">
                    <div class="form-hint">易支付平台的接口地址，例如 https://pay.example.com</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>商户 ID <span class="required">*</span></label>
                    <input type="text" data-name="epay_pid" value="<?php echo isset($settings['pay']['epay_pid']) ? htmlentities((string) $settings['pay']['epay_pid']) : ''; ?>" placeholder="商户ID">
                </div>
                <div class="form-group">
                    <label>商户密钥 <span class="required">*</span></label>
                    <input type="text" data-name="epay_key" value="<?php echo isset($settings['pay']['epay_key']) ? htmlentities((string) $settings['pay']['epay_key']) : ''; ?>" placeholder="商户密钥">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="saveGroup('pay')">保存支付配置</button>
            </div>
            <div id="msg-pay" class="form-tip"></div>
        </form>
    </div>

    <!-- Redis 配置 -->
    <div id="tab-redis" class="tab-panel">
        <form onsubmit="return false;">
            <div class="form-row">
                <div class="form-group">
                    <label>Redis 服务器地址</label>
                    <input type="text" data-name="redis_host" value="<?php echo isset($settings['redis']['redis_host']) ? htmlentities((string) $settings['redis']['redis_host']) : '127.0.0.1'; ?>">
                </div>
                <div class="form-group">
                    <label>Redis 端口</label>
                    <input type="number" data-name="redis_port" value="<?php echo isset($settings['redis']['redis_port']) ? htmlentities((string) $settings['redis']['redis_port']) : '6379'; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Redis 密码</label>
                    <input type="text" data-name="redis_password" value="<?php echo isset($settings['redis']['redis_password']) ? htmlentities((string) $settings['redis']['redis_password']) : ''; ?>" placeholder="无密码留空">
                </div>
                <div class="form-group">
                    <label>Redis 数据库编号</label>
                    <input type="number" data-name="redis_select" value="<?php echo isset($settings['redis']['redis_select']) ? htmlentities((string) $settings['redis']['redis_select']) : '0'; ?>">
                </div>
            </div>
            <div class="form-row single">
                <div class="form-group">
                    <label>Redis Key 前缀</label>
                    <input type="text" data-name="redis_prefix" value="<?php echo isset($settings['redis']['redis_prefix']) ? htmlentities((string) $settings['redis']['redis_prefix']) : 'frp:'; ?>">
                    <div class="form-hint">FRPS 相关的 Redis Key 都会以此为前缀</div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="saveGroup('redis')">保存 Redis 配置</button>
            </div>
            <div id="msg-redis" class="form-tip"></div>
        </form>
    </div>
</div>

<script>
function switchTab(name) {
    document.querySelectorAll('.tab-item').forEach(function(el) { el.classList.remove('active'); });
    document.querySelectorAll('.tab-panel').forEach(function(el) { el.classList.remove('active'); });
    event.target.classList.add('active');
    document.getElementById('tab-' + name).classList.add('active');
}

async function saveGroup(group) {
    var panel = document.getElementById('tab-' + group);
    var msg = document.getElementById('msg-' + group);
    var items = {};
    panel.querySelectorAll('[data-name]').forEach(function(el) {
        items[el.getAttribute('data-name')] = el.value;
    });

    msg.style.color = '#999';
    msg.textContent = '保存中...';

    try {
        var res = await fetch('/api/admin/settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ group: group, items: items })
        });
        var json = await res.json();
        if (json.code === 1) {
            msg.style.color = '#388e3c';
            msg.textContent = '保存成功，页面将在1秒后刷新...';
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            msg.style.color = '#f56c6c';
            msg.textContent = json.msg || '保存失败';
        }
    } catch(err) {
        msg.style.color = '#f56c6c';
        msg.textContent = '网络错误';
    }
}

async function testEmail() {
    var email = prompt('请输入测试邮箱地址：');
    if (!email) return;
    var msg = document.getElementById('msg-email');
    msg.style.color = '#999';
    msg.textContent = '发送中...';
    try {
        var res = await fetch('/api/admin/test-email', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ email: email })
        });
        var json = await res.json();
        msg.style.color = json.code === 1 ? '#388e3c' : '#f56c6c';
        msg.textContent = json.code === 1 ? '测试邮件已发送' : (json.msg || '发送失败');
    } catch(err) {
        msg.style.color = '#f56c6c';
        msg.textContent = '网络错误';
    }
}
</script>

            </div>
        </div>
    </div>
</body>
</html>
