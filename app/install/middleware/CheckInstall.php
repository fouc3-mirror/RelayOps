<?php
declare(strict_types=1);

namespace app\install\middleware;

use Closure;
use think\App;
use think\Request;
use think\Response;

/**
 * 安装路由中间件
 * 用于在安装路由组中检查是否已安装
 */
class CheckInstall
{
    /**
     * 安装锁文件路径
     */
    protected string $lockFile;

    /**
     * 构造方法
     */
    public function __construct(protected App $app)
    {
        $this->lockFile = $this->app->getRootPath() . 'install.lock';
    }

    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 如果已安装，禁止访问安装页面
        if ($this->isInstalled()) {
            // 如果访问的是安装页面，重定向到首页
            $path = $request->pathinfo();
            if (str_starts_with($path, 'install')) {
                return redirect('/index/index');
            }
        }

        return $next($request);
    }

    /**
     * 检查是否已安装
     */
    protected function isInstalled(): bool
    {
        return file_exists($this->lockFile);
    }
}
