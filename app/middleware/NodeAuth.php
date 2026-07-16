<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 节点鉴权中间件
 *
 * 通过 Authorization: Bearer <auth_token> 校验 FRPS 节点身份
 * 校验通过后将 node_id 注入到请求属性中
 */
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

        // 在 RO_node 表中查找匹配的 auth_token
        $node = \app\model\Node::findByToken($token);

        if (!$node) {
            return json([
                'code' => 401,
                'msg'  => '节点令牌无效或节点已禁用',
            ], 401);
        }

        // 将节点信息注入到请求中，后续控制器可直接使用
        $request->node = $node;
        $request->nodeId = $node->id;

        return $next($request);
    }
}
