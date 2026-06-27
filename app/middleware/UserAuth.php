<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 用户鉴权中间件
 * 未登录：API 请求返回 401，页面请求重定向到 /login
 */
class UserAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');

        if (empty($userId)) {
            // API 请求返回 JSON，页面请求重定向
            if ($request->isAjax() || str_contains($request->header('accept', ''), 'application/json')) {
                return json([
                    'code'     => 401,
                    'msg'      => '未登录或登录已过期',
                    'redirect' => '/login',
                ], 401);
            }
            return redirect('/login');
        }

        return $next($request);
    }
}
