<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

class Mail
{
    protected static function getConfig(): array
    {
        $config = [];
        $fields = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from', 'smtp_name', 'smtp_ssl', 'verify_expire'];

        foreach ($fields as $field) {
            $row = Db::name('setting')->where('name', $field)->find();
            $config[$field] = $row['value'] ?? '';
        }

        return $config;
    }

    protected static function getSiteSettings(): array
    {
        $settings = [];
        $fields = ['site_name', 'site_footer'];

        foreach ($fields as $field) {
            $row = Db::name('setting')->where('name', $field)->find();
            $settings[$field] = $row['value'] ?? '';
        }

        if (empty($settings['site_name'])) {
            $settings['site_name'] = '雨梦FRPS多节点管理系统';
        }

        return $settings;
    }

    public static function send(string $to, string $subject, string $content): bool
    {
        $config = self::getConfig();

        if (empty($config['smtp_host']) || empty($config['smtp_user'])) {
            return false;
        }

        $host = $config['smtp_host'];
        $port = (int) $config['smtp_port'];
        $user = $config['smtp_user'];
        $pass = $config['smtp_pass'];
        $from = $config['smtp_from'] ?: $user;
        $name = $config['smtp_name'] ?: '雨梦FRPS多节点管理系统';
        $ssl = $config['smtp_ssl'] === '1';

        $socket = self::connect($host, $port, $ssl);
        if (!$socket) {
            return false;
        }

        try {
            self::readResponse($socket);

            self::sendCommand($socket, "EHLO relayops");
            self::readResponse($socket);

            if ($ssl && $port === 587) {
                self::sendCommand($socket, "STARTTLS");
                self::readResponse($socket);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
                self::sendCommand($socket, "EHLO relayops");
                self::readResponse($socket);
            }

            self::sendCommand($socket, "AUTH LOGIN");
            self::readResponse($socket);

            self::sendCommand($socket, base64_encode($user));
            self::readResponse($socket);

            self::sendCommand($socket, base64_encode($pass));
            self::readResponse($socket);

            self::sendCommand($socket, "MAIL FROM:<{$from}>");
            self::readResponse($socket);

            self::sendCommand($socket, "RCPT TO:<{$to}>");
            self::readResponse($socket);

            self::sendCommand($socket, "DATA");
            self::readResponse($socket);

            $headers = "From: =?UTF-8?B?" . base64_encode($name) . "?= <{$from}>\r\n";
            $headers .= "To: <{$to}>\r\n";
            $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
            $headers .= "Date: " . date('r') . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "\r\n";

            $body = $headers . $content . "\r\n.\r\n";
            fwrite($socket, $body);
            self::readResponse($socket);

            self::sendCommand($socket, "QUIT");
            self::readResponse($socket);

            fclose($socket);

            return true;
        } catch (\Throwable $e) {
            @fclose($socket);
            return false;
        }
    }

    protected static function connect(string $host, int $port, bool $ssl)
    {
        $errno = 0;
        $errstr = '';

        if ($ssl) {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $scheme = ($port === 465) ? 'ssl' : 'tcp';
            $socket = @stream_socket_client("{$scheme}://{$host}:{$port}", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
        } else {
            $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 10);
        }

        return $socket ?: null;
    }

    protected static function sendCommand($socket, string $command): void
    {
        fwrite($socket, $command . "\r\n");
    }

    protected static function readResponse($socket): string
    {
        $response = '';
        while (true) {
            $line = fgets($socket, 512);
            if ($line === false) {
                break;
            }
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    }

    public static function generateCode(): string
    {
        return str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function saveCode(string $email, string $code, string $scene = 'register'): bool
    {
        $config = self::getConfig();
        $expire = (int) ($config['verify_expire'] ?: 300);

        Db::name('email_verify')
            ->where('email', $email)
            ->where('scene', $scene)
            ->where('used', 0)
            ->delete();

        $result = Db::name('email_verify')->insert([
            'email'       => $email,
            'code'        => $code,
            'scene'       => $scene,
            'used'        => 0,
            'expire_time' => time() + $expire,
            'create_time' => time(),
        ]);

        return $result > 0;
    }

    public static function verifyCode(string $email, string $code, string $scene = 'register'): bool
    {
        $record = Db::name('email_verify')
            ->where('email', $email)
            ->where('code', $code)
            ->where('scene', $scene)
            ->where('used', 0)
            ->where('expire_time', '>', time())
            ->find();

        if (!$record) {
            return false;
        }

        Db::name('email_verify')
            ->where('id', $record['id'])
            ->update(['used' => 1]);

        return true;
    }

    public static function sendVerifyCode(string $email, string $scene = 'register'): array
    {
        $config = self::getConfig();

        if (empty($config['smtp_host']) || empty($config['smtp_user'])) {
            return ['code' => 0, 'msg' => '邮件服务未配置'];
        }

        $code = self::generateCode();

        if (!self::saveCode($email, $code, $scene)) {
            return ['code' => 0, 'msg' => '验证码保存失败'];
        }

        $expire = (int) ($config['verify_expire'] ?: 300);
        $siteSettings = self::getSiteSettings();
        $subject = $siteSettings['site_name'] . ' 验证码';
        $content = self::buildVerifyEmail($code, $expire, $scene, $siteSettings);

        if (!self::send($email, $subject, $content)) {
            return ['code' => 0, 'msg' => '邮件发送失败，请检查SMTP配置'];
        }

        return ['code' => 1, 'msg' => '验证码已发送，请查收邮件'];
    }

    protected static function buildVerifyEmail(string $code, int $expire, string $scene, array $siteSettings = []): string
    {
        $sceneText = $scene === 'register' ? '用户注册' : '密码重置';
        $currentYear = date('Y');
        $siteName = htmlspecialchars($siteSettings['site_name'] ?? '雨梦FRPS多节点管理系统');
        $siteFooter = $siteSettings['site_footer'] ?? "Copyright &copy; {$currentYear} {$siteName}. All rights reserved.";

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7fa; margin: 0; padding: 40px 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #409eff 0%, #337ecc 100%); padding: 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 24px; }
        .content { padding: 40px 30px; text-align: center; }
        .code { font-size: 48px; font-weight: bold; color: #409eff; letter-spacing: 8px; margin: 30px 0; padding: 20px; background: #f0f7ff; border-radius: 8px; }
        .info { color: #666; font-size: 14px; margin: 20px 0; }
        .expire { color: #f56c6c; font-weight: 500; }
        .footer { padding: 20px; text-align: center; color: #999; font-size: 12px; border-top: 1px solid #f0f0f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚡ {$siteName}</h1>
        </div>
        <div class="content">
            <p class="info">您正在进行 <strong>{$sceneText}</strong> 操作</p>
            <div class="code">{$code}</div>
            <p class="info">验证码有效期为 <span class="expire">{$expire} 秒</span></p>
            <p class="info" style="color:
        </div>
        <div class="footer">
            {$siteFooter}
        </div>
    </div>
</body>
</html>
HTML;
    }
}
