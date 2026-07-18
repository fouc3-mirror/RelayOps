<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = session('admin_id');

        if (empty($adminId)) {
            if ($request->isAjax() || str_contains($request->header('accept', ''), 'application/json')) {
                return json([
                    'code'     => 401,
                    'msg'      => '未登录或登录已过期',
                    'redirect' => '/admin/login',
                ], 401);
            }
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
