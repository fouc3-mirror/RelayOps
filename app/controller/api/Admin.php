<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\Response;

class Admin extends BaseController
{
    /**
     * 管理员登录
     */
    public function login(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $username = $this->request->param('username', '');
        $password = $this->request->param('password', '');

        if (empty($username) || empty($password)) {
            return json(['code' => 0, 'msg' => '请输入用户名和密码']);
        }

        $admin = Db::name('admin')
            ->where('username', $username)
            ->where('status', 1)
            ->find();

        if (!$admin || !password_verify($password, $admin['password'])) {
            return json(['code' => 0, 'msg' => '用户名或密码错误']);
        }

        session('admin_id', $admin['id']);
        session('admin_username', $admin['username']);

        Db::name('admin')->where('id', $admin['id'])->update([
            'last_login_time' => time(),
            'last_login_ip'   => $this->request->ip(),
        ]);

        return json([
            'code' => 1,
            'msg'  => '登录成功',
            'data' => [
                'id'       => $admin['id'],
                'username' => $admin['username'],
                'nickname' => $admin['nickname'],
            ],
        ]);
    }

    /**
     * 获取管理员信息
     */
    public function info(): Response
    {
        $adminId = session('admin_id');
        $admin = Db::name('admin')
            ->where('id', $adminId)
            ->field('id,username,nickname,email,status,last_login_time')
            ->find();

        if (!$admin) {
            return json(['code' => 0, 'msg' => '用户不存在']);
        }

        return json(['code' => 1, 'data' => $admin]);
    }

    /**
     * 管理员登出
     */
    public function logout(): Response
    {
        session(null);
        return json(['code' => 1, 'msg' => '已退出']);
    }

    /**
     * 管理员发送重置密码验证码
     */
    public function sendResetVerify(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $username = trim($this->request->param('username', ''));

        if (empty($username)) {
            return json(['code' => 0, 'msg' => '请输入管理员账号']);
        }

        // 查找管理员
        $admin = Db::name('admin')->where('username', $username)->find();
        if (!$admin) {
            return json(['code' => 0, 'msg' => '管理员账号不存在']);
        }

        if (empty($admin['email'])) {
            return json(['code' => 0, 'msg' => '该账号未设置邮箱，无法重置密码']);
        }

        $email = $admin['email'];

        // 检查发送频率（60秒内不能重复发送）
        $lastSend = Db::name('email_verify')
            ->where('email', $email)
            ->where('scene', 'admin_reset')
            ->order('id', 'desc')
            ->find();

        if ($lastSend && (time() - $lastSend['create_time']) < 60) {
            $wait = 60 - (time() - $lastSend['create_time']);
            return json(['code' => 0, 'msg' => "请等待 {$wait} 秒后再试"]);
        }

        $result = \app\service\Mail::sendVerifyCode($email, 'admin_reset');

        return json($result);
    }

    /**
     * 管理员重置密码
     */
    public function resetPassword(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $username        = trim($this->request->param('username', ''));
        $code            = trim($this->request->param('code', ''));
        $password        = $this->request->param('password', '');
        $passwordConfirm = $this->request->param('password_confirm', '');

        if (empty($username) || empty($code) || empty($password)) {
            return json(['code' => 0, 'msg' => '请填写完整信息']);
        }

        if (strlen($password) < 6) {
            return json(['code' => 0, 'msg' => '密码至少需要6位']);
        }

        if ($password !== $passwordConfirm) {
            return json(['code' => 0, 'msg' => '两次输入的密码不一致']);
        }

        // 查找管理员
        $admin = Db::name('admin')->where('username', $username)->find();
        if (!$admin) {
            return json(['code' => 0, 'msg' => '管理员账号不存在']);
        }

        if (empty($admin['email'])) {
            return json(['code' => 0, 'msg' => '该账号未设置邮箱，无法重置密码']);
        }

        // 验证邮箱验证码
        if (!\app\service\Mail::verifyCode($admin['email'], $code, 'admin_reset')) {
            return json(['code' => 0, 'msg' => '验证码错误或已过期']);
        }

        // 更新密码
        $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
        Db::name('admin')
            ->where('id', $admin['id'])
            ->update([
                'password'    => $newPasswordHash,
                'update_time' => time(),
            ]);

        return json(['code' => 1, 'msg' => '密码重置成功，请使用新密码登录']);
    }

    /**
     * 用户列表
     */
    public function userList(): Response
    {
        $list = Db::name('user')
            ->field('id,username,nickname,email,phone,status,last_login_time,create_time')
            ->select()
            ->toArray();

        return json(['code' => 1, 'data' => $list]);
    }

    /**
     * 获取用户详情
     */
    public function userDetail(): Response
    {
        $id = (int) $this->request->param('id', 0);

        if ($id <= 0) {
            return json(['code' => 0, 'msg' => '无效的用户ID']);
        }

        $user = Db::name('user')
            ->field('id,username,nickname,email,phone,status')
            ->where('id', $id)
            ->find();

        if (!$user) {
            return json(['code' => 0, 'msg' => '用户不存在']);
        }

        return json(['code' => 1, 'data' => $user]);
    }

    /**
     * 添加/编辑用户
     */
    public function userSave(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $id = (int) $this->request->param('id', 0);
        $username = trim($this->request->param('username', ''));
        $password = $this->request->param('password', '');
        $nickname = trim($this->request->param('nickname', ''));
        $email = trim($this->request->param('email', ''));
        $phone = trim($this->request->param('phone', ''));
        $status = (int) $this->request->param('status', 1);

        // 数据验证
        if (empty($username)) {
            return json(['code' => 0, 'msg' => '用户名不能为空']);
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            return json(['code' => 0, 'msg' => '用户名长度为3-20个字符']);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return json(['code' => 0, 'msg' => '用户名只能包含字母、数字和下划线']);
        }

        if (empty($email)) {
            return json(['code' => 0, 'msg' => '邮箱不能为空']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '邮箱格式不正确']);
        }

        // 新增时密码必填，编辑时可选
        if ($id <= 0 && empty($password)) {
            return json(['code' => 0, 'msg' => '新增用户时密码不能为空']);
        }

        if (!empty($password) && strlen($password) < 6) {
            return json(['code' => 0, 'msg' => '密码长度不能少于6位']);
        }

        // 检查用户名是否已存在（排除自身）
        $exists = Db::name('user')->where('username', $username);
        if ($id > 0) {
            $exists->where('id', '<>', $id);
        }
        if ($exists->find()) {
            return json(['code' => 0, 'msg' => '用户名已存在']);
        }

        // 检查邮箱是否已存在（排除自身）
        $emailExists = Db::name('user')->where('email', $email);
        if ($id > 0) {
            $emailExists->where('id', '<>', $id);
        }
        if ($emailExists->find()) {
            return json(['code' => 0, 'msg' => '邮箱已被使用']);
        }

        $time = time();
        $saveData = [
            'username'    => $username,
            'nickname'    => $nickname ?: $username,
            'email'       => $email,
            'phone'       => $phone,
            'status'      => $status,
            'update_time' => $time,
        ];

        // 密码不为空时更新密码
        if (!empty($password)) {
            $saveData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($id > 0) {
            // 编辑
            Db::name('user')->where('id', $id)->update($saveData);
            return json(['code' => 1, 'msg' => '用户已更新']);
        } else {
            // 新增
            $saveData['create_time'] = $time;
            $saveData['last_login_time'] = null;
            $saveData['last_login_ip'] = '';
            Db::name('user')->insert($saveData);
            return json(['code' => 1, 'msg' => '用户已添加']);
        }
    }

    /**
     * 删除用户
     */
    public function userDelete(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $id = (int) $this->request->param('id', 0);

        if ($id <= 0) {
            return json(['code' => 0, 'msg' => '无效的用户ID']);
        }

        $user = Db::name('user')->where('id', $id)->find();
        if (!$user) {
            return json(['code' => 0, 'msg' => '用户不存在']);
        }

        // 检查是否有订单关联
        $orderCount = Db::name('order')->where('user_id', $id)->count();
        if ($orderCount > 0) {
            return json(['code' => 0, 'msg' => '该用户有关联订单，无法删除']);
        }

        // 检查是否有客户端关联
        $clientCount = Db::name('client')->where('user_id', $id)->count();
        if ($clientCount > 0) {
            return json(['code' => 0, 'msg' => '该用户有关联客户端，无法删除']);
        }

        Db::name('user')->where('id', $id)->delete();
        return json(['code' => 1, 'msg' => '用户已删除']);
    }

    /**
     * 切换用户状态
     */
    public function userToggle(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $id = (int) $this->request->param('id', 0);

        $user = Db::name('user')->where('id', $id)->find();
        if (!$user) {
            return json(['code' => 0, 'msg' => '用户不存在']);
        }

        $newStatus = $user['status'] == 1 ? 0 : 1;
        Db::name('user')->where('id', $id)->update([
            'status'      => $newStatus,
            'update_time' => time(),
        ]);

        return json(['code' => 1, 'msg' => $newStatus == 1 ? '用户已启用' : '用户已禁用']);
    }

    /**
     * 节点列表（管理端）
     */
    public function nodeList(): Response
    {
        $list = Db::name('node')
            ->field('id,name,server_addr,server_port,dashboard_port,status,last_heartbeat')
            ->order('id asc')
            ->select()
            ->toArray();

        return json(['code' => 1, 'data' => ['list' => $list]]);
    }

    /**
     * 获取节点详情
     */
    public function nodeDetail(): Response
    {
        $id = (int) $this->request->param('id', 0);

        if ($id <= 0) {
            return json(['code' => 0, 'msg' => '无效的节点ID']);
        }

        $node = Db::name('node')->where('id', $id)->find();

        if (!$node) {
            return json(['code' => 0, 'msg' => '节点不存在']);
        }

        return json(['code' => 1, 'data' => $node]);
    }

    /**
     * 添加/编辑节点
     */
    public function nodeSave(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $id = (int) $this->request->param('id', 0);
        $name = trim($this->request->param('name', ''));
        $serverAddr = trim($this->request->param('server_addr', ''));
        $serverPort = (int) $this->request->param('server_port', 7000);
        $authToken = trim($this->request->param('auth_token', ''));
        $httpPort = (int) $this->request->param('http_port', 80);
        $httpsPort = (int) $this->request->param('https_port', 443);
        $dashboardPort = (int) $this->request->param('dashboard_port', 7500);
        $dashboardUser = trim($this->request->param('dashboard_user', ''));
        $dashboardPass = trim($this->request->param('dashboard_pass', ''));
        $portRangeStart = (int) $this->request->param('port_range_start', 0);
        $portRangeEnd = (int) $this->request->param('port_range_end', 0);
        $status = (int) $this->request->param('status', 1);
        $description = $this->request->param('description', '');

        // 数据验证
        if (empty($name)) {
            return json(['code' => 0, 'msg' => '节点名称不能为空']);
        }

        if (empty($serverAddr)) {
            return json(['code' => 0, 'msg' => '服务器地址不能为空']);
        }

        // 验证IP或域名格式
        if (!filter_var($serverAddr, FILTER_VALIDATE_IP) && !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/', $serverAddr)) {
            return json(['code' => 0, 'msg' => '服务器地址格式不正确（支持IP或域名）']);
        }

        if ($serverPort <= 0 || $serverPort > 65535) {
            return json(['code' => 0, 'msg' => '服务端口不正确']);
        }

        if ($dashboardPort <= 0 || $dashboardPort > 65535) {
            return json(['code' => 0, 'msg' => 'Dashboard端口不正确']);
        }

        // 验证端口范围
        if ($portRangeStart > 0 && $portRangeEnd > 0) {
            if ($portRangeStart > $portRangeEnd) {
                return json(['code' => 0, 'msg' => '端口范围起始不能大于结束']);
            }
            if ($portRangeStart < 1024 || $portRangeEnd > 65535) {
                return json(['code' => 0, 'msg' => '端口范围应在1024-65535之间']);
            }
        }

        // 检查节点名称是否已存在（排除自身）
        $exists = Db::name('node')->where('name', $name);
        if ($id > 0) {
            $exists->where('id', '<>', $id);
        }
        if ($exists->find()) {
            return json(['code' => 0, 'msg' => '节点名称已存在']);
        }

        $time = time();
        $saveData = [
            'name'            => $name,
            'server_addr'     => $serverAddr,
            'server_port'     => $serverPort,
            'auth_token'      => $authToken,
            'http_port'       => $httpPort,
            'https_port'      => $httpsPort,
            'dashboard_port'  => $dashboardPort,
            'dashboard_user'  => $dashboardUser,
            'dashboard_pass'  => $dashboardPass,
            'port_range_start' => $portRangeStart,
            'port_range_end'  => $portRangeEnd,
            'status'          => $status,
            'description'     => $description,
            'update_time'     => $time,
        ];

        if ($id > 0) {
            // 编辑
            Db::name('node')->where('id', $id)->update($saveData);
            return json(['code' => 1, 'msg' => '节点已更新']);
        } else {
            // 添加
            $saveData['create_time'] = $time;
            Db::name('node')->insert($saveData);
            return json(['code' => 1, 'msg' => '节点已添加']);
        }
    }

    /**
     * 删除节点
     */
    public function nodeDelete(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $id = (int) $this->request->param('id', 0);

        if ($id <= 0) {
            return json(['code' => 0, 'msg' => '无效的节点ID']);
        }

        $node = Db::name('node')->where('id', $id)->find();
        if (!$node) {
            return json(['code' => 0, 'msg' => '节点不存在']);
        }

        // 检查是否有商品关联
        $productCount = Db::name('product')->where('node_id', $id)->count();
        if ($productCount > 0) {
            return json(['code' => 0, 'msg' => '该节点下有商品关联，无法删除']);
        }

        // 检查是否有客户端关联
        $clientCount = Db::name('client')->where('node_id', $id)->count();
        if ($clientCount > 0) {
            return json(['code' => 0, 'msg' => '该节点下有客户端关联，无法删除']);
        }

        Db::name('node')->where('id', $id)->delete();
        return json(['code' => 1, 'msg' => '节点已删除']);
    }

    /**
     * 切换节点状态
     */
    public function nodeToggle(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $id = (int) $this->request->param('id', 0);

        $node = Db::name('node')->where('id', $id)->find();
        if (!$node) {
            return json(['code' => 0, 'msg' => '节点不存在']);
        }

        $newStatus = $node['status'] == 1 ? 0 : 1;
        Db::name('node')->where('id', $id)->update([
            'status'      => $newStatus,
            'update_time' => time(),
        ]);

        return json(['code' => 1, 'msg' => $newStatus == 1 ? '节点已启用' : '节点已禁用']);
    }

    // ========================================================
    // 系统设置
    // ========================================================

    /**
     * 获取系统设置（按分组）
     */
    public function settings(): Response
    {
        $group = $this->request->param('group', '');

        $query = Db::name('setting');
        if ($group) {
            $query->where('group', $group);
        }

        $rows = $query->select()->toArray();
        $config = [];
        foreach ($rows as $row) {
            $config[$row['group']][$row['name']] = $row['value'] ?? '';
        }

        return json(['code' => 1, 'data' => $config]);
    }

    /**
     * 保存系统设置（通用：接收任意分组的 key-value）
     */
    public function saveSettings(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $data = $this->request->param();
        $group = $data['group'] ?? '';
        $items = $data['items'] ?? [];

        if (empty($group) || !is_array($items)) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }

        $time = time();
        foreach ($items as $name => $value) {
            $exists = Db::name('setting')->where('name', $name)->find();
            if ($exists) {
                Db::name('setting')->where('name', $name)->update([
                    'value'       => $value,
                    'update_time' => $time,
                ]);
            } else {
                Db::name('setting')->insert([
                    'group'       => $group,
                    'name'        => $name,
                    'value'       => $value,
                    'type'        => 'text',
                    'title'       => $name,
                    'create_time' => $time,
                    'update_time' => $time,
                ]);
            }
        }

        return json(['code' => 1, 'msg' => '配置已保存']);
    }

    /**
     * 测试发送邮件
     */
    public function testEmail(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $email = $this->request->post('email', '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '请输入正确的邮箱地址']);
        }

        $result = \app\service\Mail::sendVerifyCode($email, 'test');
        return json($result);
    }

    // ========================================================
    // 商品管理
    // ========================================================

    /**
     * 商品列表
     */
    public function productList(): Response
    {
        $list = Db::name('product')
            ->alias('p')
            ->join('node n', 'p.node_id = n.id', 'left')
            ->field('p.*, n.name as node_name, n.server_addr')
            ->order('p.sort asc, p.id desc')
            ->select()
            ->toArray();

        return json(['code' => 1, 'data' => $list]);
    }

    /**
     * 添加/编辑商品
     */
    public function productSave(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $id = (int) $this->request->param('id', 0);
        $nodeId = (int) $this->request->param('node_id', 0);
        $name = trim($this->request->param('name', ''));
        $proxyType = $this->request->param('proxy_type', 'tcp');
        $portStart = (int) $this->request->param('port_start', 0);
        $portEnd = (int) $this->request->param('port_end', 0);
        $price = (float) $this->request->param('price', 0);
        $durationOptions = trim($this->request->param('duration_options', '1,3,6,12'));
        $status = (int) $this->request->param('status', 1);
        $sort = (int) $this->request->param('sort', 0);
        $description = $this->request->param('description', '');

        // 参数校验
        if (empty($name)) {
            return json(['code' => 0, 'msg' => '请填写商品名称']);
        }
        if ($nodeId <= 0) {
            return json(['code' => 0, 'msg' => '请选择节点']);
        }
        if (!in_array($proxyType, ['tcp', 'udp', 'http', 'https'])) {
            return json(['code' => 0, 'msg' => '无效的代理类型']);
        }
        if ($portStart <= 0 || $portEnd <= 0 || $portStart > $portEnd) {
            return json(['code' => 0, 'msg' => '端口范围不正确']);
        }
        if ($price < 0) {
            return json(['code' => 0, 'msg' => '价格不能为负数']);
        }

        // 检查节点是否存在
        $node = Db::name('node')->where('id', $nodeId)->find();
        if (!$node) {
            return json(['code' => 0, 'msg' => '节点不存在']);
        }

        // 检查端口范围是否在节点范围内
        if ($node['port_range_start'] > 0 && $node['port_range_end'] > 0) {
            if ($portStart < $node['port_range_start'] || $portEnd > $node['port_range_end']) {
                return json(['code' => 0, 'msg' => "端口范围超出节点限制 ({$node['port_range_start']}-{$node['port_range_end']})"]);
            }
        }

        // 检查端口范围是否与其他商品冲突
        $conflict = Db::name('product')
            ->where('node_id', $nodeId)
            ->where('proxy_type', $proxyType)
            ->where('status', 1)
            ->where('port_start', '<=', $portEnd)
            ->where('port_end', '>=', $portStart);
        if ($id > 0) {
            $conflict->where('id', '<>', $id);
        }
        if ($conflict->find()) {
            return json(['code' => 0, 'msg' => '该端口范围已被其他商品占用']);
        }

        $saveData = [
            'node_id'          => $nodeId,
            'name'             => $name,
            'proxy_type'       => $proxyType,
            'port_start'       => $portStart,
            'port_end'         => $portEnd,
            'price'            => $price,
            'duration_options' => $durationOptions,
            'status'           => $status,
            'sort'             => $sort,
            'description'      => $description,
            'update_time'      => time(),
        ];

        if ($id > 0) {
            Db::name('product')->where('id', $id)->update($saveData);
        } else {
            $saveData['create_time'] = time();
            Db::name('product')->insert($saveData);
        }

        return json(['code' => 1, 'msg' => $id > 0 ? '商品已更新' : '商品已添加']);
    }

    /**
     * 删除商品
     */
    public function productDelete(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $id = (int) $this->request->param('id', 0);
        if ($id <= 0) {
            return json(['code' => 0, 'msg' => '无效的商品ID']);
        }

        Db::name('product')->where('id', $id)->delete();
        return json(['code' => 1, 'msg' => '商品已删除']);
    }

    /**
     * 切换商品状态
     */
    public function productToggle(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $id = (int) $this->request->param('id', 0);
        $product = Db::name('product')->where('id', $id)->find();
        if (!$product) {
            return json(['code' => 0, 'msg' => '商品不存在']);
        }

        $newStatus = $product['status'] == 1 ? 0 : 1;
        Db::name('product')->where('id', $id)->update(['status' => $newStatus, 'update_time' => time()]);

        return json(['code' => 1, 'msg' => $newStatus == 1 ? '已上架' : '已下架']);
    }
}
