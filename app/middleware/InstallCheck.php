<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\App;
use think\Request;
use think\Response;

/**
 * 全局安装检查中间件
 * 检测 .env 是否存在或 DB_HOST 是否配置，未配置则跳转安装页面
 */
class InstallCheck
{
    /**
     * 安装锁文件路径
     */
    protected string $lockFile;

    /**
     * .env文件路径
     */
    protected string $envFile;

    /**
     * 构造方法
     */
    public function __construct(protected App $app)
    {
        $this->lockFile = $this->app->getRootPath() . 'install.lock';
        $this->envFile  = $this->app->getRootPath() . '.env';
    }

    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 如果已安装，不做处理
        if ($this->isInstalled()) {
            return $next($request);
        }

        // 获取当前请求路径
        $path = $request->pathinfo();

        // 如果是安装页面的请求，放行
        if (str_starts_with($path, 'install')) {
            return $next($request);
        }

        // 检查.env文件是否存在
        if (!$this->envExists()) {
            return redirect('/install');
        }

        // 检查DB_HOST是否配置
        if (!$this->isDbConfigured()) {
            return redirect('/install');
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

    /**
     * 检查.env文件是否存在
     */
    protected function envExists(): bool
    {
        return file_exists($this->envFile);
    }

    /**
     * 检查数据库是否已配置
     */
    protected function isDbConfigured(): bool
    {
        // 检查.env文件中是否有DB_HOST配置
        if (file_exists($this->envFile)) {
            $envContent = file_get_contents($this->envFile);

            // 检查是否有DB_HOST配置且不为空
            if (preg_match('/DB_HOST\s*=\s*(.+)/', $envContent, $matches)) {
                $host = trim((string) $matches[1]);
                if (!empty($host)) {
                    return true;
                }
            }
        }

        return false;
    }
}
