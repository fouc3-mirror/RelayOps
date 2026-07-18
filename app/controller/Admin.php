<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\View;
use think\Response;

class Admin extends BaseController
{
    protected function getSiteSettings(): array
    {
        static $settings = null;
        if ($settings !== null) {
            return $settings;
        }

        $fields = ['site_name', 'site_favicon', 'site_logo'];
        $settings = [];
        foreach ($fields as $field) {
            $row = Db::name('setting')->where('name', $field)->find();
            $settings[$field] = $row['value'] ?? '';
        }

        if (empty($settings['site_name'])) {
            $settings['site_name'] = '雨梦FRPS多节点管理系统';
        }
        if (empty($settings['site_favicon'])) {
            $settings['site_favicon'] = '/favicon.ico';
        }

        return $settings;
    }

    public function index(): Response
    {
        return redirect('/admin/dashboard');
    }

    public function login(): Response
    {
        if (session('admin_id')) {
            return redirect('/admin/dashboard');
        }
        $siteSettings = $this->getSiteSettings();
        return $this->view('admin/login', ['site' => $siteSettings]);
    }

    public function dashboard(): Response
    {
        $data = [
            'active'      => 'dashboard',
            'nodeCount'   => Db::name('node')->count(),
            'onlineCount' => Db::name('node')->where('status', 1)->count(),
            'userCount'   => 0,
            'orderCount'  => 0,
            'monthlyIncome' => 0,
            'phpVersion'  => PHP_VERSION,
            'tpVersion'   => \think\facade\App::version(),
        ];
        try {
            $data['userCount'] = Db::name('user')->count();
        } catch (\Exception $e) {
        }
        try {
            $data['orderCount'] = Db::name('order')->count();
            $monthStart = strtotime(date('Y-m-01'));
            $data['monthlyIncome'] = Db::name('order')
                ->where('status', 1)
                ->where('pay_time', '>=', $monthStart)
                ->sum('amount') ?? 0;
        } catch (\Exception $e) {
        }
        $data['site'] = $this->getSiteSettings();
        return $this->view('admin/dashboard', $data);
    }

    public function nodes(): Response
    {
        $siteSettings = $this->getSiteSettings();
        return $this->view('admin/nodes', ['active' => 'nodes', 'site' => $siteSettings]);
    }

    public function users(): Response
    {
        $siteSettings = $this->getSiteSettings();
        return $this->view('admin/users', ['active' => 'users', 'site' => $siteSettings]);
    }

    public function settings(): Response
    {
        $groups = ['basic', 'system', 'email', 'pay'];
        $settings = [];

        foreach ($groups as $group) {
            $rows = Db::name('setting')->where('group', $group)->select()->toArray();
            foreach ($rows as $row) {
                $settings[$group][$row['name']] = $row['value'] ?? '';
            }
        }

        $siteSettings = $this->getSiteSettings();
        return $this->view('admin/settings', [
            'active'   => 'settings',
            'settings' => $settings,
            'site'     => $siteSettings,
        ]);
    }

    public function products(): Response
    {
        $siteSettings = $this->getSiteSettings();
        return $this->view('admin/products', ['active' => 'products', 'site' => $siteSettings]);
    }

    public function orders(): Response
    {
        $siteSettings = $this->getSiteSettings();
        return $this->view('admin/orders', ['active' => 'orders', 'site' => $siteSettings]);
    }

    protected function view(string $template, array $data = []): Response
    {
        $viewPath = app()->getRootPath() . 'view/';
        View::config([
            'view_path'   => $viewPath,
            'view_suffix' => 'html',
        ]);

        $content = View::fetch($template, $data);
        return Response::create($content, 'html');
    }
}
