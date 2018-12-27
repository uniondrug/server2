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
     * 应用版本
     * @var string
     */
    private $appVersion;
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
     * 服务主机规则
     * @var string
     */
    private $hostRegexp = "/^\d+\.\d+\.\d+\.\d+$/";
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

    /**
     * 从临时文件中解析, 获取最近一次启
     * 动时的参数
     * @return array
     */
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

    /**
     * 应用版本号
     * @return string
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * 读取项目路径
     * @return string
     */
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
     * 读取主机IP
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

    /**
     * 读取管理地址
     * @return string
     */
    public function getManagerHost()
    {
        return self::MANAGER_HOST;
    }

    /**
     * 记录启动参数的文件
     * @return string
     */
    public function getManagerFile()
    {
        return getcwd().'/tmp/server.env';
    }

    /**
     * 读取选项
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
     * 读取Server配置
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
     * 导入参数
     * 扫描项目目录下的配置文件，从配置文件中提取
     * 关键项, 并赋值初始值
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
        // 2. 读取项目名称与版本
        $this->appName = isset($conf['app']['appName']) && $conf['app']['appName'] ? $conf['app']['appName'] : '';
        $this->appVersion = isset($conf['app']['appVersion']) && $conf['app']['appVersion'] ? $conf['app']['appVersion'] : 'x.x';
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
            if (preg_match("/([_a-zA-Z0-9\-\.]+):(\d+)/", $host, $m) > 0) {
                $this->setHost($m[1]);
                $this->setPort($m[2]);
            }
        }
    }

    /**
     * 合并参数
     * 从Help对象合并参数(来自命令行指定)
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

    /**
     * 设置基础路径
     * @param string $basePath
     * @return $this
     */
    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * 设置入口类名
     * @param string $entrypoint
     * @return $this
     */
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

    /**
     * 设置关联Helper
     * @param Helper $helper
     * @return $this
     */
    public function setHelper(Helper $helper)
    {
        $this->helper = $helper;
        return $this;
    }

    /**
     * 设置IP地址
     * @param string $host
     * @return $this
     */
    public function setHost(string $host)
    {
        if (preg_match($this->hostRegexp, $host) > 0) {
            $this->host = $host;
        } else {
            $read = $this->parseNetwork($host);
            $this->host = $read !== false ? $read : $host;
        }
        return $this;
    }

    /**
     * 设置Server端口
     * @param int $port
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * 按路径创建
     * 使用路径扫描配置文件, 从配置文件中提取参数
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

    /**
     * 解析IP
     * 按Host网卡名称读取IP
     * @param string $host
     * @return false|string
     */
    private function parseNetwork(string $host)
    {
        $addr = $this->parseNetworkIpadd($host);
        $addr === false && $addr = $this->parseNetworkIfconfig($host);
        return $addr === false ? false : $addr;
    }

    /**
     * @param string $host
     * @return false|string
     */
    private function parseNetworkIfconfig(string $host)
    {
        // 1. read all
        $cmd = 'ifconfig';
        $str = shell_exec($cmd);
        $str = preg_replace("/\n\s+/", " ", $str);
        // 2. filter host
        if (preg_match("/({$host}[^\n]+)/", $str, $m) === 0) {
            return false;
        }
        // 3. filter ip addr
        //    inet addr:10.168.74.190
        //    inet 192.168.10.116
        if (preg_match("/inet\s+[a-z:]*(\d+\.\d+\.\d+\.\d+)/", $m[1], $z) > 0) {
            return $z[1];
        }
        return false;
    }

    /**
     * 解析IP地址
     * 以阿里云为例
     * 1. eth0, 内网IP
     * 2. eth1, 公网IP
     * @param string $host
     * @return false|string
     */
    private function parseNetworkIpadd(string $host)
    {
        $cmd = "ip -o -4 addr list '{$host}' | head -n1 | awk '{print \$4}' | cut -d/ -f1";
        $addr = shell_exec($cmd);
        $addr = trim($addr);
        if ($addr !== "" && preg_match($this->hostRegexp, $addr) > 0) {
            return $addr;
        }
        return false;
    }
}
