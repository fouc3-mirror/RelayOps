<?php /*a:1:{s:45:"D:\frps-tp\RelayOps-php\view/admin\login.html";i:1782533201;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - RelayOps</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #1d1e2c 0%, #2c3e6b 100%); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .login-box { width: 400px; background: #fff; border-radius: 12px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .login-box h2 { text-align: center; margin-bottom: 8px; font-size: 22px; color: #1d1e2c; }
        .login-box .sub { text-align: center; margin-bottom: 30px; font-size: 14px; color: #999; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 14px; color: #555; }
        .form-group input { width: 100%; padding: 10px 14px; border: 1px solid #dcdfe6; border-radius: 6px; font-size: 14px; outline: none; transition: border .2s; }
        .form-group input:focus { border-color: #409eff; }
        .form-btn { width: 100%; padding: 12px; background: #409eff; color: #fff; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; transition: background .2s; }
        .form-btn:hover { background: #337ecc; }
        .form-tip { text-align: center; margin-top: 16px; font-size: 13px; min-height: 20px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #999; font-size: 13px; text-decoration: none; }
        .back-link a:hover { color: #409eff; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>⚡ RelayOps</h2>
        <div class="sub">管理员登录</div>
        <form onsubmit="return handleLogin(event)">
            <div class="form-group">
                <label>管理员账号</label>
                <input type="text" id="username" placeholder="请输入账号" required autofocus>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" id="password" placeholder="请输入密码" required>
            </div>
            <button type="submit" class="form-btn">登 录</button>
        </form>
        <div class="form-tip" id="msg"></div>
        <div class="back-link"><a href="/">← 返回首页</a></div>
    </div>

    <script>
    async function handleLogin(e) {
        e.preventDefault();
        var msg = document.getElementById('msg');
        msg.style.color = '#999';
        msg.textContent = '登录中...';

        try {
            var res = await fetch('/api/admin/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: 'username=' + encodeURIComponent(document.getElementById('username').value) +
                      '&password=' + encodeURIComponent(document.getElementById('password').value)
            });
            var json = await res.json();
            if (json.code === 1) {
                msg.style.color = '#388e3c';
                msg.textContent = '登录成功，正在跳转...';
                setTimeout(function() { location.href = '/admin/dashboard'; }, 800);
            } else {
                msg.style.color = '#f56c6c';
                msg.textContent = json.msg || '登录失败';
            }
        } catch(err) {
            msg.style.color = '#f56c6c';
            msg.textContent = '请求失败';
        }
    }
    </script>
</body>
</html>
