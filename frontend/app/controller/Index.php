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

        $fields = ['site_name', 'site_favicon', 'site_logo', 'site_description', 'site_footer'];
        $settings = [];
        foreach ($fields as $field) {
            $row = Db::name('setting')->where('name', $field)->find();
            $settings[$field] = $row['value'] ?? '';
        }

        // 设置默认值
        if (empty($settings['site_name'])) {
            $settings['site_name'] = 'RelayOps';
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

        // 获取用户节点数
        $nodeCount = 0;
        try {
            $nodeCount = Db::name('node')->where('user_id', $userId)->count();
        } catch (\Exception $e) {}

        $siteSettings = $this->getSiteSettings();
        return $this->view('index/console', [
            'user'      => $user,
            'nodeCount' => $nodeCount,
            'site'      => $siteSettings,
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
