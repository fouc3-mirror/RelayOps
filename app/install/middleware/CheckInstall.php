<?php
declare(strict_types=1);

namespace app\install\middleware;

use Closure;
use think\App;
use think\Request;
use think\Response;

class CheckInstall
{
    protected string $lockFile;

    public function __construct(protected App $app)
    {
        $this->lockFile = $this->app->getRootPath() . 'install.lock';
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isInstalled()) {
            $path = $request->pathinfo();
            if (str_starts_with($path, 'install')) {
                return redirect('/index/index');
            }
        }

        return $next($request);
    }

    protected function isInstalled(): bool
    {
        return file_exists($this->lockFile);
    }
}
