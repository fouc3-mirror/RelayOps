<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class UserAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');

        if (empty($userId)) {
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
