<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\Response;

class User extends BaseController
{
    /**
     * 发送邮箱验证码
     */
    public function sendVerify(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $email = trim($this->request->param('email', ''));
        $scene = $this->request->param('scene', 'register');

        if (empty($email)) {
            return json(['code' => 0, 'msg' => '请输入邮箱地址']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '邮箱格式不正确']);
        }

        // 检查发送频率（60秒内不能重复发送）
        $lastSend = Db::name('email_verify')
            ->where('email', $email)
            ->where('scene', $scene)
            ->order('id', 'desc')
            ->find();

        if ($lastSend && (time() - $lastSend['create_time']) < 60) {
            $wait = 60 - (time() - $lastSend['create_time']);
            return json(['code' => 0, 'msg' => "请等待 {$wait} 秒后再试"]);
        }

        $result = \app\service\Mail::sendVerifyCode($email, $scene);

        return json($result);
    }

    /**
     * 用户注册
     */
    public function register(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $username = trim($this->request->param('username', ''));
        $password = $this->request->param('password', '');
        $nickname = trim($this->request->param('nickname', ''));
        $email = trim($this->request->param('email', ''));
        $verifyCode = trim($this->request->param('verify_code', ''));

        // 参数验证
        if (empty($username) || empty($password)) {
            return json(['code' => 0, 'msg' => '用户名和密码不能为空']);
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            return json(['code' => 0, 'msg' => '用户名长度为3-20个字符']);
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return json(['code' => 0, 'msg' => '用户名只能包含字母、数字和下划线']);
        }

        if (strlen($password) < 6) {
            return json(['code' => 0, 'msg' => '密码长度至少6位']);
        }

        if (empty($email)) {
            return json(['code' => 0, 'msg' => '邮箱不能为空']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '邮箱格式不正确']);
        }

        if (empty($verifyCode)) {
            return json(['code' => 0, 'msg' => '请输入验证码']);
        }

        // 验证邮箱验证码
        if (!\app\service\Mail::verifyCode($email, $verifyCode, 'register')) {
            return json(['code' => 0, 'msg' => '验证码无效或已过期']);
        }

        // 检查用户名是否已存在
        $exists = Db::name('user')->where('username', $username)->find();
        if ($exists) {
            return json(['code' => 0, 'msg' => '用户名已存在']);
        }

        // 检查邮箱是否已注册
        $emailExists = Db::name('user')->where('email', $email)->find();
        if ($emailExists) {
            return json(['code' => 0, 'msg' => '该邮箱已被注册']);
        }

        // 创建用户
        $userId = Db::name('user')->insertGetId([
            'username'       => $username,
            'password'       => password_hash($password, PASSWORD_DEFAULT),
            'nickname'       => $nickname ?: $username,
            'email'          => $email,
            'status'         => 1,
            'last_login_time' => null,
            'last_login_ip'   => '',
            'create_time'    => time(),
            'update_time'    => time(),
        ]);

        if ($userId) {
            return json(['code' => 1, 'msg' => '注册成功']);
        }

        return json(['code' => 0, 'msg' => '注册失败，请稍后重试']);
    }

    /**
     * 用户登录
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

        $user = Db::name('user')
            ->where('username', $username)
            ->where('status', 1)
            ->find();

        if (!$user || !password_verify($password, $user['password'])) {
            return json(['code' => 0, 'msg' => '用户名或密码错误']);
        }

        session('user_id', $user['id']);
        session('user_username', $user['username']);

        Db::name('user')->where('id', $user['id'])->update([
            'last_login_time' => time(),
            'last_login_ip'   => $this->request->ip(),
        ]);

        return json([
            'code' => 1,
            'msg'  => '登录成功',
            'data' => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'nickname' => $user['nickname'],
            ],
        ]);
    }

    /**
     * 获取用户信息
     */
    public function info(): Response
    {
        $userId = session('user_id');

        $user = Db::name('user')
            ->where('id', $userId)
            ->field('id,username,nickname,email,phone,status,last_login_time')
            ->find();

        if (!$user) {
            return json(['code' => 0, 'msg' => '用户不存在']);
        }

        return json(['code' => 1, 'data' => $user]);
    }

    /**
     * 用户登出
     */
    public function logout(): Response
    {
        session(null);

        // 页面请求重定向，API 请求返回 JSON
        if ($this->request->isAjax() || str_contains($this->request->header('accept', ''), 'application/json')) {
            return json(['code' => 1, 'msg' => '已退出']);
        }
        return redirect('/login');
    }

    // ========================================================
    // 商品与节点
    // ========================================================

    /**
     * 获取上架商品列表（用户端）
     */
    public function products(): Response
    {
        $list = Db::name('product')
            ->alias('p')
            ->join('node n', 'p.node_id = n.id', 'left')
            ->where('p.status', 1)
            ->where('n.status', 1)
            ->field('p.id,p.name,p.node_id,p.proxy_type,p.port_start,p.port_end,p.price,p.duration_options,p.description,n.name as node_name,n.server_addr')
            ->order('p.sort asc, p.id asc')
            ->select()
            ->toArray();

        // 解析 duration_options 为数组
        foreach ($list as &$item) {
            $item['durations'] = array_map('intval', explode(',', $item['duration_options'] ?? '1'));
            $item['price'] = (float) $item['price'];
        }
        unset($item);

        return json(['code' => 1, 'data' => $list]);
    }

    /**
     * 获取商品详情（用户端）
     */
    public function productDetail(): Response
    {
        $id = (int) $this->request->param('id', 0);

        if ($id <= 0) {
            return json(['code' => 0, 'msg' => '商品ID无效']);
        }

        $product = Db::name('product')
            ->alias('p')
            ->join('node n', 'p.node_id = n.id', 'left')
            ->where('p.id', $id)
            ->where('p.status', 1)
            ->where('n.status', 1)
            ->field('p.id,p.name,p.node_id,p.proxy_type,p.port_start,p.port_end,p.price,p.duration_options,p.description,n.name as node_name,n.server_addr,n.server_port,n.port_range_start,n.port_range_end')
            ->find();

        if (!$product) {
            return json(['code' => 0, 'msg' => '商品不存在或已下架']);
        }

        // 解析 duration_options 为数组
        $product['durations'] = array_map('intval', explode(',', $product['duration_options'] ?? '1'));
        $product['price'] = (float) $product['price'];

        // 获取该节点可用端口
        $occupied = Db::name('client')
            ->where('node_id', $product['node_id'])
            ->where('status', '<>', 2)
            ->column('port');

        $availablePorts = [];
        $rangeStart = max((int) $product['port_start'], (int) $product['port_range_start']);
        $rangeEnd = min((int) $product['port_end'], (int) $product['port_range_end']);

        for ($port = $rangeStart; $port <= $rangeEnd; $port++) {
            if (!in_array($port, $occupied)) {
                $availablePorts[] = $port;
            }
        }

        $product['available_ports'] = $availablePorts;
        $product['available_count'] = count($availablePorts);

        return json(['code' => 1, 'data' => $product]);
    }

    /**
     * 直接创建订单（不经过购物车）
     */
    public function orderCreateDirect(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $userId = session('user_id');
        if (!$userId) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }

        $productId = (int) $this->request->param('product_id', 0);
        $port = (int) $this->request->param('port', 0);
        $duration = (int) $this->request->param('duration', 1);

        if ($productId <= 0 || $port <= 0) {
            return json(['code' => 0, 'msg' => '请选择商品和端口']);
        }

        // 查询商品
        $product = Db::name('product')
            ->alias('p')
            ->join('node n', 'p.node_id = n.id', 'left')
            ->where('p.id', $productId)
            ->where('p.status', 1)
            ->where('n.status', 1)
            ->field('p.*, n.name as node_name')
            ->find();

        if (!$product) {
            return json(['code' => 0, 'msg' => '商品不存在或已下架']);
        }

        // 校验时长
        $allowedDurations = array_map('intval', explode(',', $product['duration_options'] ?? '1'));
        if (!in_array($duration, $allowedDurations)) {
            return json(['code' => 0, 'msg' => '无效的购买时长']);
        }

        // 校验端口在商品范围内
        if ($port < $product['port_start'] || $port > $product['port_end']) {
            return json(['code' => 0, 'msg' => "端口不在商品范围内 ({$product['port_start']}-{$product['port_end']})"]);
        }

        // 后端二次校验端口未被占用
        $check = \app\service\OrderService::verifyPort($product['node_id'], $port);
        if (!$check['ok']) {
            return json(['code' => 0, 'msg' => $check['msg']]);
        }

        // 计算金额
        $amount = round((float) $product['price'] * $duration, 2);

        // 生成订单号
        $orderNo = date('YmdHis') . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // 创建订单
        $orderId = Db::name('order')->insertGetId([
            'order_no'    => $orderNo,
            'user_id'     => $userId,
            'node_id'     => $product['node_id'],
            'node_name'   => $product['node_name'] ?? '',
            'port'        => $port,
            'proxy_type'  => $product['proxy_type'],
            'duration'    => $duration,
            'amount'      => $amount,
            'status'      => 0, // 待支付
            'create_time' => time(),
            'update_time' => time(),
        ]);

        if (!$orderId) {
            return json(['code' => 0, 'msg' => '订单创建失败']);
        }

        // 生成支付链接
        $subject = "RelayOps - {$product['name']} 端口 {$port} ({$product['proxy_type']})";

        $payResult = \app\service\EpayService::createPayment(
            $orderNo,
            $amount,
            $subject,
            'alipay'
        );

        return json([
            'code' => 1,
            'msg'  => '订单创建成功',
            'data' => [
                'order_id' => $orderId,
                'order_no' => $orderNo,
                'amount'   => $amount,
                'pay_url'  => $payResult['url'] ?? '',
            ],
        ]);
    }

    /**
     * 获取节点列表（用户端）
     */
    public function nodes(): Response
    {
        $list = Db::name('node')
            ->where('status', 1)
            ->field('id,name,server_addr,server_port,port_range_start,port_range_end,description')
            ->select()
            ->toArray();

        return json(['code' => 1, 'data' => $list]);
    }

    /**
     * 获取节点可用端口列表（排除已占用）
     */
    public function ports(): Response
    {
        $nodeId = (int) $this->request->param('node_id', 0);

        if ($nodeId <= 0) {
            return json(['code' => 0, 'msg' => '请选择节点']);
        }

        $node = Db::name('node')->where('id', $nodeId)->where('status', 1)->find();
        if (!$node) {
            return json(['code' => 0, 'msg' => '节点不存在或已禁用']);
        }

        $rangeStart = (int) $node['port_range_start'];
        $rangeEnd = (int) $node['port_range_end'];

        if ($rangeStart <= 0 || $rangeEnd <= 0 || $rangeStart > $rangeEnd) {
            return json(['code' => 1, 'data' => []]);
        }

        // 查询该节点下已占用的端口
        $occupied = Db::name('client')
            ->where('node_id', $nodeId)
            ->where('status', '<>', 2) // 排除已过期
            ->column('port');

        // 生成可用端口列表
        $available = [];
        for ($port = $rangeStart; $port <= $rangeEnd; $port++) {
            if (!in_array($port, $occupied)) {
                $available[] = $port;
            }
        }

        return json([
            'code' => 1,
            'data' => [
                'ports'      => $available,
                'total'      => $rangeEnd - $rangeStart + 1,
                'occupied'   => count($occupied),
                'available'  => count($available),
            ],
        ]);
    }

    // ========================================================
    // 购物车（Session 存储）
    // ========================================================

    /**
     * 加入购物车
     */
    public function cartAdd(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $productId = (int) $this->request->param('product_id', 0);
        $port = (int) $this->request->param('port', 0);
        $duration = (int) $this->request->param('duration', 1);

        if ($productId <= 0 || $port <= 0) {
            return json(['code' => 0, 'msg' => '请选择商品和端口']);
        }

        // 查询商品（必须是上架状态）
        $product = Db::name('product')
            ->alias('p')
            ->join('node n', 'p.node_id = n.id', 'left')
            ->where('p.id', $productId)
            ->where('p.status', 1)
            ->where('n.status', 1)
            ->field('p.*, n.name as node_name')
            ->find();

        if (!$product) {
            return json(['code' => 0, 'msg' => '商品不存在或已下架']);
        }

        // 校验时长
        $allowedDurations = array_map('intval', explode(',', $product['duration_options'] ?? '1'));
        if (!in_array($duration, $allowedDurations)) {
            return json(['code' => 0, 'msg' => '无效的购买时长']);
        }

        // 校验端口在商品范围内
        if ($port < $product['port_start'] || $port > $product['port_end']) {
            return json(['code' => 0, 'msg' => "端口不在商品范围内 ({$product['port_start']}-{$product['port_end']})"]);
        }

        // 后端二次校验端口未被占用
        $check = \app\service\OrderService::verifyPort($product['node_id'], $port);
        if (!$check['ok']) {
            return json(['code' => 0, 'msg' => $check['msg']]);
        }

        // 初始化购物车
        $cart = session('cart') ?: [];

        // 检查是否已在购物车中（同一端口+节点）
        foreach ($cart as $item) {
            if ($item['node_id'] == $product['node_id'] && $item['port'] == $port) {
                return json(['code' => 0, 'msg' => '该端口已在购物车中']);
            }
        }

        // 加入购物车（价格从商品表读取）
        $cart[] = [
            'product_id' => $productId,
            'node_id'    => $product['node_id'],
            'node_name'  => $product['node_name'] ?? '',
            'port'       => $port,
            'proxy_type' => $product['proxy_type'],
            'duration'   => $duration,
            'price'      => (float) $product['price'],
        ];

        session('cart', $cart);

        return json(['code' => 1, 'msg' => '已加入购物车', 'data' => ['count' => count($cart)]]);
    }

    /**
     * 查看购物车
     */
    public function cartList(): Response
    {
        $cart = session('cart') ?: [];

        // 补充节点名称
        $nodeIds = array_unique(array_column($cart, 'node_id'));
        $nodeNames = [];
        if (!empty($nodeIds)) {
            $nodes = Db::name('node')->whereIn('id', $nodeIds)->column('name', 'id');
            $nodeNames = $nodes;
        }

        $result = [];
        $total = 0;
        foreach ($cart as $index => $item) {
            $amount = round($item['price'] * $item['duration'], 2);
            $total += $amount;
            $result[] = array_merge($item, [
                'index'     => $index,
                'node_name' => $item['node_name'] ?? ($nodeNames[$item['node_id']] ?? '未知节点'),
                'amount'    => $amount,
            ]);
        }

        return json([
            'code'  => 1,
            'data'  => ['items' => $result, 'total' => $total, 'count' => count($cart)],
        ]);
    }

    /**
     * 删除购物车项
     */
    public function cartRemove(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $index = (int) $this->request->param('index', -1);
        $cart = session('cart') ?: [];

        if ($index < 0 || $index >= count($cart)) {
            return json(['code' => 0, 'msg' => '无效的购物车项']);
        }

        array_splice($cart, $index, 1);
        session('cart', $cart);

        return json(['code' => 1, 'msg' => '已删除', 'data' => ['count' => count($cart)]]);
    }

    /**
     * 清空购物车
     */
    public function cartClear(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        session('cart', []);

        return json(['code' => 1, 'msg' => '购物车已清空']);
    }

    // ========================================================
    // 订单
    // ========================================================

    /**
     * 从购物车创建订单
     */
    public function orderCreate(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $userId = session('user_id');
        $cart = session('cart') ?: [];

        if (empty($cart)) {
            return json(['code' => 0, 'msg' => '购物车为空']);
        }

        $result = \app\service\OrderService::createOrders($userId, $cart);

        if ($result['ok']) {
            // 清空购物车
            session('cart', []);
        }

        return json($result);
    }

    /**
     * 发起支付（返回支付跳转 URL）
     */
    public function orderPay(): Response
    {
        $orderId = (int) $this->request->param('id', 0);
        $payType = $this->request->param('pay_type', 'alipay');

        if ($orderId <= 0) {
            return json(['code' => 0, 'msg' => '无效的订单']);
        }

        $userId = session('user_id');
        $order = Db::name('order')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->where('status', 0)
            ->find();

        if (!$order) {
            return json(['code' => 0, 'msg' => '订单不存在或已支付']);
        }

        $subject = "RelayOps - {$order['node_name']} 端口 {$order['port']} ({$order['proxy_type']})";

        $result = \app\service\EpayService::createPayment(
            $order['order_no'],
            (float) $order['amount'],
            $subject,
            $payType
        );

        return json($result);
    }

    /**
     * 我的订单列表
     */
    public function orderList(): Response
    {
        $userId = session('user_id');

        $list = Db::name('order')
            ->where('user_id', $userId)
            ->order('id', 'desc')
            ->select()
            ->toArray();

        // 状态文本映射
        $statusMap = [0 => '待支付', 1 => '已支付', 2 => '已过期', 3 => '已取消'];

        $result = array_map(function ($item) use ($statusMap) {
            $item['status_text'] = $statusMap[$item['status']] ?? '未知';
            $item['create_time_text'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['pay_time_text'] = $item['pay_time'] ? date('Y-m-d H:i:s', $item['pay_time']) : '-';
            return $item;
        }, $list);

        return json(['code' => 1, 'data' => $result]);
    }

    /**
     * 获取订单详情
     */
    public function orderDetail(): Response
    {
        $id = (int) $this->request->param('id', 0);
        $userId = session('user_id');

        if ($id <= 0) {
            return json(['code' => 0, 'msg' => '订单ID无效']);
        }

        $order = Db::name('order')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->find();

        if (!$order) {
            return json(['code' => 0, 'msg' => '订单不存在']);
        }

        // 状态文本映射
        $statusMap = [0 => '待支付', 1 => '已支付', 2 => '已过期', 3 => '已取消'];
        $order['status_text'] = $statusMap[$order['status']] ?? '未知';
        $order['create_time_text'] = date('Y-m-d H:i:s', $order['create_time']);
        $order['pay_time_text'] = $order['pay_time'] ? date('Y-m-d H:i:s', $order['pay_time']) : '-';

        return json(['code' => 1, 'data' => $order]);
    }
}
