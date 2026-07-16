<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Response;

class Index extends BaseController
{
    /**
     * 获取系统设置
     */
    protected function getSiteSettings(): array
    {
        static $settings = null;
        if ($settings !== null) {
            return $settings;
        }

        $fields = ['site_name', 'site_favicon', 'site_logo', 'site_description', 'site_footer', 'site_banner_1', 'site_banner_2', 'site_banner_3'];
        $settings = [];
        foreach ($fields as $field) {
            $row = Db::name('setting')->where('name', $field)->find();
            $settings[$field] = $row['value'] ?? '';
        }

        // 设置默认值
        if (empty($settings['site_name'])) {
            $settings['site_name'] = '雨梦FRPS业务管理系统';
        }
        if (empty($settings['site_favicon'])) {
            $settings['site_favicon'] = '/favicon.ico';
        }

        return $settings;
    }

    /**
     * 用户前台首页
     */
    public function index(): Response
    {
        $siteSettings = $this->getSiteSettings();
        return $this->view('index/index', ['site' => $siteSettings]);
    }

    /**
     * 用户登录页
     */
    public function login(): Response
    {
        // 已登录则跳转控制台
        if (session('user_id')) {
            return redirect('/console');
        }
        $siteSettings = $this->getSiteSettings();
        return $this->view('index/login', ['site' => $siteSettings]);
    }

    /**
     * 用户控制台
     */
    public function console(): Response
    {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $userId = session('user_id');

        // 获取用户信息
        $user = Db::name('user')->where('id', $userId)
            ->field('id,username,nickname,email,status,create_time')
            ->find();

        // 获取用户产品列表（已激活/运行中的隧道）
        $products = [];
        try {
            $products = Db::name('client')
                ->alias('c')
                ->leftJoin('node n', 'c.node_id = n.id')
                ->leftJoin('product p', 'c.node_id = p.node_id AND p.status = 1')
                ->where('c.user_id', $userId)
                ->where('c.status', 'in', [0, 1])
                ->field('c.id, c.node_id, c.port, c.proxy_type, c.status, c.expire_time, c.create_time, c.traffic_used, n.name as node_name, n.server_addr, p.traffic_limit')
                ->order('c.id', 'desc')
                ->select()
                ->toArray();

            // 处理过期状态和流量
            $now = time();
            foreach ($products as &$p) {
                $p['is_expired'] = ($p['expire_time'] > 0 && $p['expire_time'] < $now);
                $p['expire_date'] = $p['expire_time'] ? date('Y-m-d', $p['expire_time']) : '-';
                $p['created_date'] = $p['create_time'] ? date('Y-m-d', $p['create_time']) : '-';
                // 剩余流量
                $trafficLimit = (int) ($p['traffic_limit'] ?? 0);
                $trafficUsed  = (int) ($p['traffic_used'] ?? 0);
                if ($trafficLimit > 0) {
                    $remaining = $trafficLimit - $trafficUsed;
                    $p['remaining_traffic'] = $remaining > 0 ? $remaining : 0;
                } else {
                    $p['remaining_traffic'] = -1; // -1 表示不限
                }
            }
            unset($p);
        } catch (\Exception $e) {}

        $productCount = count($products);
        $activeCount = 0;
        foreach ($products as $p) {
            if (!$p['is_expired'] && $p['status'] == 1) $activeCount++;
        }

        $siteSettings = $this->getSiteSettings();
        return $this->view('index/console', [
            'user'          => $user,
            'productCount'  => $productCount,
            'activeCount'   => $activeCount,
            'products'      => $products,
            'nodeCount'     => $productCount,
            'site'          => $siteSettings,
        ]);
    }

    /**
     * 商品列表页
     */
    public function shop(): Response
    {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $siteSettings = $this->getSiteSettings();
        return $this->view('index/shop', ['site' => $siteSettings]);
    }

    /**
     * 商品详情页
     */
    public function product(): Response
    {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $siteSettings = $this->getSiteSettings();
        return $this->view('index/product', ['site' => $siteSettings]);
    }

    /**
     * 我的产品详情页
     */
    public function clientDetail(): Response
    {
        if (!session('user_id')) {
            return redirect('/login');
        }

        $siteSettings = $this->getSiteSettings();
        return $this->view('index/client_detail', ['site' => $siteSettings]);
    }

    /**
     * 关于我们页
     */
    public function about(): Response
    {
        $siteSettings = $this->getSiteSettings();
        return $this->view('index/about', ['site' => $siteSettings]);
    }

    /**
     * 渲染视图
     */
    protected function view(string $template, array $data = []): Response
    {
        $viewPath = app()->getRootPath() . 'view/';
        \think\facade\View::config([
            'view_path'   => $viewPath,
            'view_suffix' => 'html',
        ]);

        $content = \think\facade\View::fetch($template, $data);
        return Response::create($content, 'html');
    }
}
