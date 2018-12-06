<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2;

/**
 * Builder/构建Server入参
 * @package Uniondrug\Server2
 */
class Builder
{
    const MANAGER_HOST = "127.0.0.1";
    const DEFAULT_ENVIRONMENT = "development";
    /**
     * 应用名称
     * @var string
     */
    private $appName;
    /**
     * 项目根目录
     * @var string
     */
    private $basePath;
    /**
     * 启动服务名
     * @var string
     * @example App\Servers\Http::class
     */
    private $entrypoint;
    /**
     * 环境名称
     * @var string
     */
    private $environment = self::DEFAULT_ENVIRONMENT;
    /**
     * Helper实例
     * @var Helper
     */
    private $helper;
    /**
     * 服务主机IP地址
     * @var string
     */
    private $host = "";
    /**
     * 服务主机端口号
     * @var int
     */
    private $port = 0;
    /**
     * 相关选项
     * @var array
     */
    private $options = [];
    /**
     * Process进程
     * <code>
     * $processes = [
     *     ExampleProcess::class
     * ]
     * </code>
     * @var array
     */
    private $processes = [];
    /**
     * Swoole服务配置
     * @var array
     */
    private $settings = [];
    /**
     * 内存表
     * <code>
     * $tables = [
     *     PidTable::class => 64,
     *     ExampleTable::class => 128
     * ]
     * </code>
     * @var array
     */
    private $tables = [];

    public function decodeTemp()
    {
        $data = ['token' => ''];
        $file = $this->getManagerFile();
        if (file_exists($file)) {
            $text = trim(file_get_contents($file));
            $data['token'] = md5($text);
            $cols = explode('|', $text);
            if (count($cols) >= 3) {
                $data['env'] = $cols[2];
                if (preg_match("/^(\S+):(\d+)$/", $cols[0], $m) > 0) {
                    $data['host'] = $m[1];
                    $data['port'] = $m[2];
                }
                if (preg_match("/^(\S+):(\d+)$/", $cols[1], $m) > 0) {
                    $data['managerHost'] = $m[1];
                    $data['managerPort'] = $m[2];
                }
            }
        }
        return $data;
    }

    /**
     * 生成服务启动后的参数
     * 写入到server.tmp文件, 进行服务管理时使用最近一次的
     * 启动参数, 无需命令行指定参数
     * @return string
     */
    public function encodeTemp()
    {
        return $this->getAddr().'|'.$this->getManagerAddr().'|'.$this->getEnvironment().'|'.microtime(true);
    }

    /**
     * Server完整地址
     * @return string
     * @example return "192.169.10.100:8080"
     */
    public function getAddr()
    {
        return sprintf("%s:%d", $this->getHost(), $this->getPort());
    }

    /**
     * 应用名称
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    public function getAppVersion()
    {
        return "2.x";
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * 启动脚本
     * @return string
     */
    public function getEntrypoint()
    {
        return $this->entrypoint;
    }

    /**
     * 读取环境名
     * @return string
     */
    public function getEnv()
    {
        return substr($this->getEnvironment(), 0, 1);
    }

    /**
     * 读取环境名
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * 读取主机名
     * @return string
     * @example return "192.168.10.100"
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * 管理地址
     * @return string|false
     * @example return "127.0.0.1:8080"
     */
    public function getManagerAddr()
    {
        return sprintf("%s:%d", self::MANAGER_HOST, $this->getPort());
    }

    public function getManagerHost()
    {
        return self::MANAGER_HOST;
    }

    /**
     * 记录启动参数的文件
     * @return false|string
     */
    public function getManagerFile()
    {
        if (isset($this->settings['pid_file'])) {
            return preg_replace("/\.([a-z0-9]+)$/", ".env", $this->settings['pid_file']);
        }
        return false;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getOption(string $key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    /**
     * 读取端口号
     * @return int
     * @example return 8080
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * 外挂进程列表
     * @return array
     */
    public function getProcesses()
    {
        return $this->processes;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * 内存表
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * 从配置文件读取参数
     */
    public function loader()
    {
        $env = $this->helper->getOption('env');
        $conf = [];
        // 1. 读配置文件
        $filenames = [
            'app',
            'server'
        ];
        foreach ($filenames as $filename) {
            $conf[$filename] = [];
            $filepath = $this->basePath.'/config/'.$filename.'.php';
            if (file_exists($filepath)) {
                $filedata = include($filepath);
                if (is_array($filedata)) {
                    $defaults = isset($filedata['default']) && is_array($filedata['default']) ? $filedata['default'] : [];
                    $environments = isset($filedata[$env]) && is_array($filedata[$env]) ? $filedata[$env] : [];
                    $conf[$filename] = array_replace_recursive($defaults, $environments);
                    continue;
                }
            }
        }
        // 2. 读取项目名称
        $this->appName = isset($conf['app']['appName']) && $conf['app']['appName'] ? $conf['app']['appName'] : '';
        // 3. Entrypoint
        $this->entrypoint = isset($conf['server']['class']) && $conf['server']['class'] ? $conf['server']['class'] : '';
        // 4. Server配置
        $this->settings = isset($conf['server']['options']) && is_array($conf['server']['options']) ? $conf['server']['options'] : [];
        // 5. Option选项/历史原因与settings是相反的
        $this->options = isset($conf['server']['settings']) && is_array($conf['server']['settings']) ? $conf['server']['settings'] : [];
        // 6. 外挂进程
        $this->processes = isset($conf['server']['processes']) && is_array($conf['server']['processes']) ? $conf['server']['processes'] : [];
        // 7. 内存表
        $this->tables = isset($conf['server']['tables']) && is_array($conf['server']['tables']) ? $conf['server']['tables'] : [];
        // 8. 解析IP与端口
        $host = isset($conf['server']['host']) && $conf['server']['host'] ? $conf['server']['host'] : null;
        if ($host) {
            if (preg_match("/([0-9\.]+):(\d+)/", $host, $m) > 0) {
                $this->host = $m[1];
                $this->port = (int) $m[2];
            }
        }
    }

    /**
     * 从Help对象合并参数
     * @param Helper $helper
     */
    public function mergeHelper(Helper $helper)
    {
        // 0. env
        $env = $helper->getOption('env');
        $env && $this->setEnvironment($env);
        // 1. host
        $host = $helper->getOption('host');
        $host && $this->setHost($host);
        // 2. port
        $port = $helper->getOption('port');
        $port && $this->setPort($port);
        // 3. daemon
        $daemon = $helper->hasOption('daemon');
        $daemon && $this->settings['daemonize'] = 1;
    }

    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    public function setEntrypoint(string $entrypoint)
    {
        $this->entrypoint = $entrypoint;
        return $this;
    }

    /**
     * 设置环境名称
     * @param string $environment
     * @return $this
     */
    public function setEnvironment(string $environment)
    {
        $this->environment = $environment;
        return $this;
    }

    public function setHelper(Helper $helper)
    {
        $this->helper = $helper;
        return $this;
    }

    public function setHost(string $host)
    {
        $this->host = $host;
        return $this;
    }

    public function setPort(int $port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param Helper $helper
     * @param string $basePath
     * @return Builder
     */
    public static function withBasePath(Helper $helper, string $basePath)
    {
        $builder = new Builder();
        $builder->setHelper($helper)->setBasePath($basePath);
        $builder->loader();
        return $builder;
    }
}
