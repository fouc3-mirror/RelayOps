<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\App;
use think\Request;
use think\Response;

class InstallCheck
{
    protected string $lockFile;

    protected string $envFile;

    public function __construct(protected App $app)
    {
        $this->lockFile = $this->app->getRootPath() . 'install.lock';
        $this->envFile  = $this->app->getRootPath() . '.env';
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isInstalled()) {
            return $next($request);
        }

        $path = $request->pathinfo();

        if (str_starts_with($path, 'install')) {
            return $next($request);
        }

        if (!$this->envExists()) {
            return redirect('/install');
        }

        if (!$this->isDbConfigured()) {
            return redirect('/install');
        }

        return $next($request);
    }

    protected function isInstalled(): bool
    {
        return file_exists($this->lockFile);
    }

    protected function envExists(): bool
    {
        return file_exists($this->envFile);
    }

    protected function isDbConfigured(): bool
    {
        if (file_exists($this->envFile)) {
            $envContent = file_get_contents($this->envFile);

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
