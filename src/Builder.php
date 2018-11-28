<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2;

use Uniondrug\Server2\Processes\PidProcess;

/**
 * 构建服务启动入参
 * @package Uniondrug\Server2
 */
class Builder
{
    const PID_PROCESS = PidProcess::class;
    /**
     * 服务地址
     * @var string
     */
    private $address;
    private $basePath;
    private $daemon = false;
    /**
     * 入口类名
     * @var string
     */
    private $entrypoint;
    /**
     * 环境名
     * @var string
     */
    private $environment;
    /**
     * IP地址
     * @var string
     */
    private $host = '';
    /**
     * 应用名称
     * @var string
     */
    private $name = '';
    /**
     * PID内存表容量
     * @var int
     */
    private $pidTableSize = 512;
    /**
     * 端口号
     * @var int
     */
    private $port = 0;
    /**
     * 外挂进程
     * @var array
     */
    private $process = [
        self::PID_PROCESS
    ];
    /**
     * 服务参数
     * @var array
     */
    private $setting = [];
    private $managerAddress = "";
    private $managerHost = "127.0.0.1";
    private $startMode = SWOOLE_PROCESS;
    private $startSockType = SWOOLE_SOCK_TCP;

    /**
     * Builder constructor.
     * @param string $name
     * @param string $host
     * @param int    $port
     */
    public function __construct(string $name = null, string $host = null, int $port = 0)
    {
        $name && $this->name = $name;
        $host && $this->host = $host;
        $port && $this->port = $port;
        $this->initialize();
    }

    /**
     * 读服务完整地址
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * 读入口类(IHttp/ISocket)名
     * @return string
     */
    public function getEntrypoint()
    {
        return $this->entrypoint;
    }

    /**
     * 读服务地址
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * 读管理地址/IP
     * @return string
     */
    public function getManagerAddrress()
    {
        return $this->managerAddress;
    }

    /**
     * 读管理地址/IP
     * @return string
     */
    public function getManagerHost()
    {
        return $this->managerHost;
    }

    /**
     * 读取管理端口
     * @return int
     */
    public function getManagerPort()
    {
        return $this->getPort();
    }

    /**
     * 调取项目名称
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 读取进程列表内存对象
     * @return int
     */
    public function getPidTableSize()
    {
        return $this->pidTableSize;
    }

    /**
     * 读端口号
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * 读进程列表
     * @return array
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * 读配置参数
     * @return array
     */
    public function getSetting()
    {
        return $this->setting;
    }

    public function getStartMode()
    {
        return $this->startMode;
    }

    public function getStartSockType()
    {
        return $this->startSockType;
    }

    /**
     * 初始化服务地址与端口
     */
    public function initialize()
    {
        $this->address = "{$this->host}:{$this->port}";
        $this->managerAddress = "{$this->managerHost}:{$this->port}";
    }

    /**
     * 是否启动守护进程
     * @return bool
     */
    public function isDaemon()
    {
        return $this->daemon == true;
    }

    /**
     * 检查访问地址是否为管理地址
     * @param string $address
     * @return bool
     */
    public function isManagerAddress(string $address)
    {
        return $address === $this->managerAddress;
    }

    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    public function setDaemon($daemon = true)
    {
        $this->daemon = $daemon;
        return $this;
    }

    /**
     * 设置启动类(IHttp/ISocket接口)名
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
     * 设置服务地址/IP
     * @param string $host
     * @return $this
     */
    public function setHost(string $host)
    {
        $this->host = $host;
        $this->initialize();
        return $this;
    }

    /**
     * 设置应用名称
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * 设置端口号
     * @param int $port
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;
        $this->initialize();
        return $this;
    }

    /**
     * 设置外挂进程列表
     * @param array $process
     * @return $this
     */
    public function setProcess(array $process)
    {
        in_array(self::PID_PROCESS, $process) || $process[] = self::PID_PROCESS;
        $this->process = $process;
        return $this;
    }

    /**
     * 设置服务启动参数
     * @param array $setting
     * @return $this
     */
    public function setSetting(array $setting)
    {
        $this->setting = $setting;
        return $this;
    }

    /**
     * 按项目路径与环境名构建服务入参
     * @param string       $basePath
     * @param string       $environment
     * @param Console|null $console
     * @return static
     */
    public static function withPath(string $basePath, string $environment, Console $console = null)
    {
        $console || $console = new Console();
        // 1. 扫描配置文件目录
        $fileData = [];
        $fileList = [
            'app',
            'server'
        ];
        foreach ($fileList as $name) {
            $fileData[$name] = [];
            $filePath = "{$basePath}/config/{$name}.php";
            if (file_exists($filePath)) {
                /** @noinspection PhpIncludeInspection */
                $fileTemp = include($filePath);
                if (is_array($fileTemp)) {
                    $fileDefault = isset($fileTemp['default']) && is_array($fileTemp['default']) ? $fileTemp['default'] : [];
                    $fileEnvironment = isset($fileTemp[$environment]) && is_array($fileTemp[$environment]) ? $fileTemp[$environment] : [];
                    $fileData[$name] = array_replace_recursive($fileDefault, $fileEnvironment);
                }
            } else {
                $console->error("配置文件{%s}不存在或丢失", $filePath);
            }
        }
        // 2. 提取名称
        $name = isset($fileData['app']['appName']) && $fileData['app']['appName'] !== '' ? $fileData['app']['appName'] : null;
        if ($name === null) {
            $console->error("配置文件无{%s}字段定义应用名称", "app.appName");
        } else {
            $name = substr($environment, 0, 1).'.'.$name;
        }
        // 3. 提交Host与Port
        $host = '';
        $port = 0;
        $address = isset($fileData['server']['host']) && $fileData['server']['host'] !== '' ? $fileData['server']['host'] : null;
        if ($address && preg_match("/([0-9\.]+):(\d+)/", $address, $m) > 0) {
            $host = $m[1];
            $port = (int) $m[2];
        }
        // 3. 构建实例
        $builder = new static($name, $host, $port);
        $builder->setBasePath($basePath);
        // 4. 入口类名
        if (isset($fileData['server']['class']) && is_string($fileData['server']['class'])) {
            $builder->setEntrypoint($fileData['server']['class']);
        }
        // 5. 服务配置
        if (isset($fileData['server']['options']) && is_array($fileData['server']['options'])) {
            $builder->setSetting($fileData['server']['options']);
        }
        // 6. Process进程
        if (isset($fileData['server']['processes']) && is_array($fileData['server']['processes'])) {
            $builder->setProcess($fileData['server']['processes']);
        }
        // 7. 返回实例
        return $builder;
    }
}
