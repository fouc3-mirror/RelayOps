<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use think\facade\Db;
use think\facade\Log;
use think\Response;

class Pay extends BaseController
{
    public function notify(): Response
    {
        $params = $this->request->param();

        Log::info('支付回调收到: ' . json_encode($params));

        if (!\app\service\EpayService::verifyNotify($params)) {
            Log::warning('支付回调签名验证失败');
            return Response::create('sign error', 'html')->code(200);
        }

        $tradeStatus = $params['trade_status'] ?? '';
        $orderNo = $params['out_trade_no'] ?? '';
        $tradeNo = $params['trade_no'] ?? '';

        if ($tradeStatus !== 'TRADE_SUCCESS') {
            Log::info("支付回调状态非成功: {$tradeStatus}");
            return Response::create('fail', 'html')->code(200);
        }

        $order = Db::name('order')
            ->where('order_no', $orderNo)
            ->where('status', 0)
            ->find();

        if (!$order) {
            Log::warning("订单不存在或已处理: {$orderNo}");
            return Response::create('success', 'html')->code(200);
        }

        $paidAmount = (float) ($params['money'] ?? 0);
        $orderAmount = (float) $order['amount'];
        if (abs($paidAmount - $orderAmount) > 0.01) {
            Log::warning("支付金额不匹配: 订单 {$orderAmount}, 实际 {$paidAmount}, 订单号 {$orderNo}");
            return Response::create('fail', 'html')->code(200);
        }

        $activated = \app\service\OrderService::activateService($order['id'], $tradeNo);

        if ($activated) {
            Log::info("订单 {$orderNo} 支付成功，服务已开通");
            return Response::create('success', 'html')->code(200);
        }

        Log::error("订单 {$orderNo} 服务开通失败");
        return Response::create('fail', 'html')->code(200);
    }

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
