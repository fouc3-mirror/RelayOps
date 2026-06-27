<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\facade\Log;
use think\Response;

/**
 * 支付回调控制器（易支付）
 */
class Pay extends BaseController
{
    /**
     * 异步通知回调（易支付 POST 调用）
     */
    public function notify(): Response
    {
        $params = $this->request->param();

        Log::info('支付回调收到: ' . json_encode($params));

        // 1. 验证签名
        if (!\app\service\EpayService::verifyNotify($params)) {
            Log::warning('支付回调签名验证失败');
            return Response::create('sign error', 'html')->code(200);
        }

        $tradeStatus = $params['trade_status'] ?? '';
        $orderNo = $params['out_trade_no'] ?? '';
        $tradeNo = $params['trade_no'] ?? '';

        // 2. 检查交易状态
        if ($tradeStatus !== 'TRADE_SUCCESS') {
            Log::info("支付回调状态非成功: {$tradeStatus}");
            return Response::create('fail', 'html')->code(200);
        }

        // 3. 查找订单
        $order = Db::name('order')
            ->where('order_no', $orderNo)
            ->where('status', 0)
            ->find();

        if (!$order) {
            Log::warning("订单不存在或已处理: {$orderNo}");
            return Response::create('success', 'html')->code(200);
        }

        // 4. 金额校验（防止篡改）
        $paidAmount = (float) ($params['money'] ?? 0);
        $orderAmount = (float) $order['amount'];
        if (abs($paidAmount - $orderAmount) > 0.01) {
            Log::warning("支付金额不匹配: 订单 {$orderAmount}, 实际 {$paidAmount}, 订单号 {$orderNo}");
            return Response::create('fail', 'html')->code(200);
        }

        // 5. 开通服务
        $activated = \app\service\OrderService::activateService($order['id'], $tradeNo);

        if ($activated) {
            Log::info("订单 {$orderNo} 支付成功，服务已开通");
            return Response::create('success', 'html')->code(200);
        }

        Log::error("订单 {$orderNo} 服务开通失败");
        return Response::create('fail', 'html')->code(200);
    }

    /**
     * 同步跳转回调（支付完成后浏览器跳转）
     */
    public function returnPage(): Response
    {
        $params = $this->request->param();
        $orderNo = $params['out_trade_no'] ?? '';
        $tradeStatus = $params['trade_status'] ?? '';

        if ($tradeStatus === 'TRADE_SUCCESS' && !empty($orderNo)) {
            return redirect("/console/orders?paid=1&order_no={$orderNo}");
        }

        return redirect('/console/orders');
    }
}
