<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

/**
 * 易支付接口封装
 * 支持：支付宝当面付、微信 Native、QQ钱包等
 */
class EpayService
{
    /**
     * 获取易支付配置
     */
    protected static function getConfig(): array
    {
        $fields = ['epay_url', 'epay_pid', 'epay_key'];
        $config = [];

        foreach ($fields as $field) {
            $row = Db::name('setting')->where('name', $field)->find();
            $config[$field] = $row['value'] ?? '';
        }

        return $config;
    }

    /**
     * 生成支付链接，返回跳转 URL
     * @param string $orderNo 内部订单号
     * @param float $amount 金额
     * @param string $subject 商品描述
     * @param string $payType 支付方式: alipay/wxpay/qqpay
     * @return array{ok: bool, msg: string, url: string}
     */
    public static function createPayment(string $orderNo, float $amount, string $subject, string $payType = 'alipay'): array
    {
        $config = self::getConfig();

        if (empty($config['epay_url']) || empty($config['epay_pid']) || empty($config['epay_key'])) {
            return ['ok' => false, 'msg' => '易支付配置不完整', 'url' => ''];
        }

        // 构建参数
        $params = [
            'pid'        => $config['epay_pid'],
            'type'       => $payType,
            'out_trade_no' => $orderNo,
            'notify_url' => self::getNotifyUrl(),
            'return_url' => self::getReturnUrl(),
            'name'       => $subject,
            'money'      => number_format($amount, 2, '.', ''),
        ];

        // 生成签名
        $params['sign'] = self::buildSign($params, $config['epay_key']);
        $params['sign_type'] = 'MD5';

        // 拼接支付 URL
        $payUrl = rtrim($config['epay_url'], '/') . '/submit.php?' . http_build_query($params);

        return ['ok' => true, 'msg' => '', 'url' => $payUrl];
    }

    /**
     * 验证异步回调签名
     */
    public static function verifyNotify(array $params): bool
    {
        $config = self::getConfig();

        if (empty($config['epay_key'])) {
            return false;
        }

        $sign = $params['sign'] ?? '';
        unset($params['sign'], $params['sign_type']);

        // 过滤空值
        $params = array_filter($params, function ($v) {
            return $v !== '' && $v !== null;
        });

        $expectedSign = self::buildSign($params, $config['epay_key']);

        return hash_equals($expectedSign, $sign);
    }

    /**
     * 生成签名（MD5）
     */
    protected static function buildSign(array $params, string $key): string
    {
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
            if ($v !== '' && $v !== null) {
                $str .= $k . '=' . $v . '&';
            }
        }
        $str = rtrim($str, '&') . $key;
        return md5($str);
    }

    /**
     * 获取异步通知地址
     */
    protected static function getNotifyUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . '/api/pay/notify';
    }

    /**
     * 获取同步跳转地址
     */
    protected static function getReturnUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . '/api/pay/return';
    }
}
