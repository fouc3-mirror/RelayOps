<?php
declare(strict_types=1);

namespace app\install\controller;

use app\BaseController;
use think\App;
use think\facade\View;
use think\Response;

class Install extends BaseController
{
    protected string $lockFile;

    protected string $envFile;

    protected array $installLog = [];

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->lockFile = $this->app->getRootPath() . 'install.lock';
        $this->envFile  = $this->app->getRootPath() . '.env';
    }

    public function index(): Response
    {
        if ($this->isInstalled()) {
            return redirect('/index/index');
        }

        $checkResult = $this->checkEnvironment();
        if ($checkResult !== true) {
            return $this->view('error', ['message' => $checkResult]);
        }

        return $this->view('index', ['php_version' => PHP_VERSION]);
    }

    public function step2(): Response
    {
        if ($this->isInstalled()) {
            return redirect('/index/index');
        }

        $envCheck   = $this->checkEnvironment();
        $phpVersion = PHP_VERSION;
        $extensions = $this->checkExtensions();

        return $this->view('step2', [
            'php_version' => $phpVersion,
            'env_check'   => $envCheck,
            'extensions'  => $extensions,
        ]);
    }

    public function step3(): Response
    {
        if ($this->isInstalled()) {
            return redirect('/index/index');
        }

        return $this->view('step3');
    }

    public function step4(): Response
    {
        if ($this->isInstalled()) {
            return redirect('/index/index');
        }

        return $this->view('step4');
    }

    public function step5(): Response
    {
        if ($this->isInstalled()) {
            return redirect('/index/index');
        }

        return $this->view('step5');
    }

    public function complete(): Response
    {
        if (!$this->isInstalled()) {
            return redirect('/install');
        }

        return $this->view('complete');
    }

    public function testDb(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        $host = $this->request->param('db_host', '127.0.0.1');
        $port = $this->request->param('db_port', '3306');
        $name = $this->request->param('db_name', '');
        $user = $this->request->param('db_user', '');
        $pass = $this->request->param('db_pass', '');

        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
            ]);

            $pdo->exec("USE `{$name}`");

            return json(['code' => 1, 'msg' => '数据库连接成功！']);
        } catch (\PDOException $e) {
            return json(['code' => 0, 'msg' => '数据库连接失败：' . $e->getMessage()]);
        }
    }

    public function install(): Response
    {
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }

        if ($this->isInstalled()) {
            return json(['code' => 0, 'msg' => '系统已安装，请勿重复安装']);
        }

        $dbHost    = $this->request->param('db_host', '127.0.0.1');
        $dbPort    = $this->request->param('db_port', '3306');
        $dbName    = $this->request->param('db_name', '');
        $dbUser    = $this->request->param('db_user', '');
        $dbPass    = $this->request->param('db_pass', '');
        $adminUser  = $this->request->param('admin_user', 'admin');
        $adminPass  = $this->request->param('admin_pass', '');
        $adminEmail = $this->request->param('admin_email', '');
        $siteName   = $this->request->param('site_name', '雨梦FRPS多节点管理系统');
        $siteDescription = $this->request->param('site_description', '');

        if (empty($dbName) || empty($dbUser) || empty($adminPass) || empty($adminEmail)) {
            return json(['code' => 0, 'msg' => '请填写完整信息']);
        }

        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            return json(['code' => 0, 'msg' => '管理员邮箱格式不正确']);
        }

        $this->log('安装开始 - 数据库: ' . $dbName);

        while (ob_get_level()) {
            ob_end_clean();
        }

        $streamResponse = Response::create('', 'html', 200)->header([
            'Content-Type'        => 'text/plain; charset=utf-8',
            'Cache-Control'       => 'no-cache',
            'X-Accel-Buffering'   => 'no',
        ]);

        try {
            $this->sendProgress('connecting_db', 10, '正在连接数据库服务器...');
            $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            $this->log('连接数据库: ' . $dsn);
            $pdo = new \PDO($dsn, $dbUser, $dbPass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 10,
            ]);

            $this->sendProgress('creating_db', 20, '正在创建数据库...');
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");
            $this->log('数据库创建/选择成功');

            $tables = $pdo->query("SHOW TABLES LIKE 'RO_%'")->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($tables)) {
                $this->sendProgress('cleaning_tables', 30, '正在清理旧数据表...', ['table_count' => count($tables)]);
                $this->log('发现已存在的表，正在删除: ' . implode(', ', $tables));
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                foreach ($tables as $table) {
                    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
                }
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                $this->log('已删除 ' . count($tables) . ' 个旧表');
            }

            $this->sendProgress('importing_schema', 40, '正在导入数据库表结构...');
            $sqlFile = app()->getBasePath() . 'install/sql/schema.sql';
            if (file_exists($sqlFile)) {
                $this->log('读取SQL文件: ' . $sqlFile);
                $sql = file_get_contents($sqlFile);

                $sql = preg_replace('/--.*$/m', '', $sql);
                $statements = array_filter(array_map('trim', explode(';', $sql)));

                $sqlCount = 0;
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                        $sqlCount++;
                    }
                }
                $this->log('SQL执行完成，共执行 ' . $sqlCount . ' 条语句');
                $this->sendProgress('schema_imported', 50, '数据库表结构导入完成', ['sql_count' => $sqlCount]);
            } else {
                $this->log('警告: SQL文件不存在: ' . $sqlFile);
            }

            $this->sendProgress('creating_admin', 60, '正在创建管理员账号...');
            $adminPassHash = password_hash($adminPass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO `RO_admin` (`username`, `password`, `nickname`, `email`, `status`, `create_time`, `update_time`) VALUES (?, ?, ?, ?, 1, ?, ?)");
            $stmt->execute([$adminUser, $adminPassHash, $adminUser, $adminEmail, time(), time()]);
            $this->log('管理员账号创建成功: ' . $adminUser);

            $this->sendProgress('configuring_site', 70, '正在配置系统设置...');
            $time = time();
            $siteNameEscaped = $pdo->quote($siteName);
            $siteDescEscaped = $pdo->quote($siteDescription);
            $pdo->exec("UPDATE `RO_setting` SET `value` = {$siteNameEscaped}, `update_time` = {$time} WHERE `name` = 'site_name'");
            $pdo->exec("UPDATE `RO_setting` SET `value` = {$siteDescEscaped}, `update_time` = {$time} WHERE `name` = 'site_description'");
            $this->log('系统设置已更新: site_name=' . $siteName);

            $this->sendProgress('generating_env', 80, '正在生成 .env 配置文件...');
            $envContent  = "APP_DEBUG = false\n\n";
            $envContent .= "DB_TYPE = mysql\n";
            $envContent .= "DB_HOST = {$dbHost}\n";
            $envContent .= "DB_NAME = {$dbName}\n";
            $envContent .= "DB_USER = {$dbUser}\n";
            $envContent .= "DB_PASS = {$dbPass}\n";
            $envContent .= "DB_PORT = {$dbPort}\n";
            $envContent .= "DB_CHARSET = utf8mb4\n";
            $envContent .= "DB_PREFIX = RO_\n\n";
            $envContent .= "DEFAULT_LANG = zh-cn\n";

            if (!file_put_contents($this->envFile, $envContent)) {
                throw new \RuntimeException('无法写入 .env 配置文件，请检查目录权限');
            }
            $this->log('.env 文件写入成功');

            $this->sendProgress('creating_lock', 90, '正在创建安装锁文件...');
            $installInfo = json_encode([
                'install_time' => date('Y-m-d H:i:s'),
                'admin_user'   => $adminUser,
            ], JSON_UNESCAPED_UNICODE);
            if (!file_put_contents($this->lockFile, $installInfo)) {
                throw new \RuntimeException('无法创建 install.lock 文件，请检查目录权限');
            }
            $this->log('install.lock 文件创建成功');

            $this->log('安装完成!');
            $this->sendProgress('complete', 100, '安装完成！', [
                'url' => (string) url('/install/complete'),
            ]);

        } catch (\PDOException $e) {
            $errorMsg = '数据库错误: ' . $e->getMessage();
            $this->log('安装失败 - ' . $errorMsg);
            $this->sendError('安装失败：' . $errorMsg);
        } catch (\RuntimeException $e) {
            $this->log('安装失败 - ' . $e->getMessage());
            $this->sendError($e->getMessage());
        } catch (\Exception $e) {
            $errorMsg = '系统错误: ' . $e->getMessage();
            $this->log('安装失败 - ' . $errorMsg);
            $this->sendError('安装失败：' . $errorMsg);
        }

        return $streamResponse;
    }

    private function sendProgress(string $step, int $progress, string $message, array $extra = []): void
    {
        $data = array_merge([
            'type'     => 'progress',
            'step'     => $step,
            'progress' => $progress,
            'message'  => $message,
        ], $extra);
        $this->streamLine(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private function sendError(string $message): void
    {
        $this->streamLine(json_encode([
            'type'    => 'error',
            'message' => $message,
            'log'     => $this->installLog,
        ], JSON_UNESCAPED_UNICODE));
    }

    private function streamLine(string $line): void
    {
        echo $line . "\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    protected function log(string $message): void
    {
        $time = date('Y-m-d H:i:s');
        $log  = "[{$time}] {$message}";

        $this->installLog[] = $log;

        $logDir  = app()->getRuntimePath() . 'log/';
        $logFile = $logDir . date('Ym') . '/install_' . date('d') . '.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $monthDir = $logDir . date('Ym') . '/';
        if (!is_dir($monthDir)) {
            mkdir($monthDir, 0755, true);
        }

        file_put_contents($logFile, $log . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    protected function checkEnvironment(): true|string
    {
        if (PHP_VERSION < '8.0') {
            return 'PHP版本至少需要8.0，当前版本：' . PHP_VERSION;
        }

        if (!extension_loaded('pdo_mysql')) {
            return '缺少PHP扩展：pdo_mysql';
        }

        if (!extension_loaded('mbstring')) {
            return '缺少PHP扩展：mbstring';
        }

        return true;
    }

    protected function checkExtensions(): array
    {
        $extensions = [
            'pdo_mysql' => 'PDO MySQL驱动',
            'mbstring'  => 'Mbstring扩展',
            'json'      => 'JSON扩展',
            'curl'      => 'Curl扩展',
            'gd'        => 'GD扩展',
            'xml'       => 'XML扩展',
            'fileinfo'  => 'Fileinfo扩展',
            'openssl'   => 'OpenSSL扩展',
        ];

        $result = [];
        foreach ($extensions as $ext => $name) {
            $result[] = [
                'name'   => $name,
                'loaded' => extension_loaded($ext),
            ];
        }

        return $result;
    }

    protected function isInstalled(): bool
    {
        return file_exists($this->lockFile);
    }

    protected function view(string $template, array $data = []): Response
    {
        $viewPath = app()->getBasePath() . 'install/view/';
        View::config([
            'view_path'   => $viewPath,
            'view_suffix' => 'html',
        ]);

        $content = View::fetch($template, $data);
        return Response::create($content, 'html');
    }
}
