<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Response;

class Admin extends BaseController
{
    protected function spa(): Response
    {
        $file = public_path() . 'static/dist/index.html';
        if (!is_file($file)) {
            return Response::create('Building...', 'html');
        }
        $html = file_get_contents($file);
        $siteName = Db::name('setting')->where('name', 'site_name')->value('value') ?: '雨梦FRPS';
        $html = preg_replace('/<title>.*?<\/title>/', '<title>' . htmlspecialchars($siteName) . '</title>', $html);
        return Response::create($html, 'html');
    }

    public function index(): Response     { return redirect('/admin/dashboard'); }
    public function login(): Response     { return $this->spa(); }
    public function dashboard(): Response { return $this->spa(); }
    public function nodes(): Response     { return $this->spa(); }
    public function users(): Response     { return $this->spa(); }
    public function settings(): Response  { return $this->spa(); }
    public function products(): Response  { return $this->spa(); }
    public function orders(): Response    { return $this->spa(); }
}
