<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class NodeAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('authorization', '');

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            return json([
                'code' => 401,
                'msg'  => '缺少 Authorization 头或格式错误',
            ], 401);
        }

        $token = substr($authHeader, 7);

        if (empty($token)) {
            return json([
                'code' => 401,
                'msg'  => '节点令牌为空',
            ], 401);
        }

        $node = \app\model\Node::findByToken($token);

        if (!$node) {
            return json([
                'code' => 401,
                'msg'  => '节点令牌无效或节点已禁用',
            ], 401);
        }

        $request->node = $node;
        $request->nodeId = $node->id;

        return $next($request);
    }
}
