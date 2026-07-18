<?php

namespace app\controller;

use app\BaseController;
use think\facade\Db;
use think\Response;

class Index extends BaseController
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

    public function index(): Response      { return $this->spa(); }
    public function login(): Response       { return $this->spa(); }
    public function console(): Response     { return $this->spa(); }
    public function shop(): Response        { return $this->spa(); }
    public function product(): Response     { return $this->spa(); }
    public function clientDetail(): Response { return $this->spa(); }
    public function about(): Response       { return $this->spa(); }
}
