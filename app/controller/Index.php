<?php

namespace app\controller;

use app\BaseController;
use think\Response;

class Index extends BaseController
{
    protected function spa(): Response
    {
        $file = public_path() . 'static/dist/index.html';
        if (is_file($file)) {
            return Response::create(file_get_contents($file), 'html');
        }
        return Response::create('正在构建前端...', 'html');
    }

    public function index(): Response      { return $this->spa(); }
    public function login(): Response       { return $this->spa(); }
    public function console(): Response     { return $this->spa(); }
    public function shop(): Response        { return $this->spa(); }
    public function product(): Response     { return $this->spa(); }
    public function clientDetail(): Response { return $this->spa(); }
    public function about(): Response       { return $this->spa(); }
}
