<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 管理员鉴权中间件
 * 未登录返回 401 并携带重定向地址 /admin/login
 */
class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = session('admin_id');

        if (empty($adminId)) {
            // API 请求返回 JSON，页面请求重定向
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
