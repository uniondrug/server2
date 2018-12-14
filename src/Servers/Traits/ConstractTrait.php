<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Traits;

use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Processes\IProcess;
use Uniondrug\Server2\Processes\PidProcess;
use Uniondrug\Server2\Tables\ITable;
use Uniondrug\Server2\Tables\PidTable;

/**
 * 构造方法
 * @package Uniondrug\Server2\Servers\Traits
 */
trait ConstractTrait
{
    /**
     * 服务启动参数
     * @var Builder
     */
    public $builder;
    /**
     * 控制台实例
     * @var Console
     */
    public $console;
    public $events = [];
    protected $managerToken;
    private $placeholderEvents = [
        'finish',
        'managerStart',
        'managerStop',
        'pipeMessage',
        'shutdown',
        'start',
        'task',
        'workerStart',
        'workerStop'
    ];
    private $initedTables = [];

    /**
     * 构造Http/WebSocket服务
     * @param Console $console
     * @param Builder $builder
     * @throws \Exception
     */
    public function __construct(Console $console, Builder $builder)
    {
        $this->console = $console;
        $this->builder = $builder;
        $mode = $this->builder->getOption('startMode');
        $mode || $mode = SWOOLE_PROCESS;
        $sockType = $this->builder->getOption('startSockType');
        $sockType || $sockType = SWOOLE_SOCK_TCP;
        // is validated ip addr
        $host = $this->builder->getHost();
        if (!preg_match("/^\d+\.\d+\.\d+\.\d+$/", $host)) {
            throw new \Exception("can not convert '{$host}' to ip addr");
        }
        $this->console->setPrefix("[{$this->builder->getAddr()}]");
        $this->console->info("[server=init]初始化{%s/%s}服务", $this->builder->getAppName(), $this->builder->getAppVersion());
        $this->console->debug("入口{%s}实例", $this->builder->getEntrypoint());
        $this->console->debug("运行{%s}环境", $this->builder->getEnvironment());
        $this->console->debug("监听{%s}地址", $this->builder->getAddr());
        parent::__construct($host, $this->builder->getPort(), $mode, $sockType);
        $this->initializeSettings();
        $this->initializeEvents();
        $this->initializeManager();
        $this->initializeTables();
        $this->initializeProcesses();
    }

    /**
     * 初始化事件监听回调
     */
    public function initializeEvents()
    {
        $events = array_merge($this->events, $this->placeholderEvents);
        $this->console->info("[server=event]绑定{%d}个事件回调", count($events));
        foreach ($events as $event) {
            $call = 'on'.ucfirst($event);
            if (method_exists($this, $call)) {
                $this->on($event, [
                    $this,
                    $call
                ]);
                $this->console->debug("绑定{%s}事件到{%s}方法", $event, $call);
            } else {
                $this->console->warning("事件{%s}未找到{%s}方法", $event, $call);
            }
        }
    }

    /**
     * 初始化管理监听
     * 当以127.0.0.1或0.0.0.0启动时, 不开始额外监听, 用于
     * 在对启动的服务进行管理时以API方式进行数据交互
     */
    private function initializeManager()
    {
        $addr = $this->builder->getAddr();
        if (preg_match("/127\.0\.0\.1:\d+/", $addr) || preg_match("/0\.0\.0\.0:\d+/", $addr)) {
            return;
        }
        $managerAddr = $this->builder->getManagerAddr();
        $this->console->info("[server=manager]监听{%s}管理地址", $managerAddr);
        $sockType = $this->builder->getOption('startSockType');
        $sockType || $sockType = SWOOLE_SOCK_TCP;
        $this->addListener($this->builder->getManagerHost(), $this->builder->getPort(), $sockType);
    }

    /**
     * 初始化外挂Process进程
     */
    private function initializeProcesses()
    {
        // 1. 自定义Process
        $processes = $this->builder->getProcesses();
        // 2. 内建Process
        $pidProcess = PidProcess::class;
        if (!in_array($pidProcess, $processes)) {
            $processes[] = $pidProcess;
        }
        // 3. 初始化Process
        $this->console->info("[server=process]导入{%d}个Process进程", count($processes));
        foreach ($processes as $process) {
            // 4. 未实现接口
            if (!is_a($process, IProcess::class, true)) {
                $this->console->warning("Process{%s}未实现{%s}接口", $process, IProcess::class);
                continue;
            }
            // 5. 创建实例
            try {
                $this->addProcess(new $process($this));
                $this->console->debug("Process{%s}加入{%s}服务", $process, $this->builder->getAppName());
            } catch(\Throwable $e) {
                $this->console->error("创建Process{%s}失败 - %s", $process, $e->getMessage());
            }
        }
    }

    /**
     * 初始化Server设置
     */
    private function initializeSettings()
    {
        $settings = $this->builder->getSettings();
        $this->set($settings);
        $this->console->info("[server=setting]初始化{%d}个服务配置参数", count($settings));
        foreach ($settings as $key => $value) {
            $this->console->debug("赋{%s}值为{%s}", $key, $value);
        }
    }

    /**
     * 初始化内存表
     */
    private function initializeTables()
    {
        // 1. 自定义Table
        $tables = $this->builder->getTables();
        // 2. 内建Table
        $pidTable = PidTable::class;
        if (!isset($tables[$pidTable])) {
            $tables[$pidTable] = 128;
        }
        // 3. 初始化Table
        $this->console->info("[server=table]初始化{%d}个内存表", count($tables));
        foreach ($tables as $table => $size) {
            if (is_a($table, ITable::class, true)) {
                try {
                    /**
                     * @var ITable $t
                     */
                    $t = new $table($size);
                    $k = $t->getName();
                    $this->initedTables[$k] = $t;
                    $this->console->debug("初始化名为{%s}的内存表{%s}, 初始化空间{%d}条记录", $k, $table, $size);
                } catch(\Throwable $e) {
                    $this->console->error("初始化{%s}内存表失败 - %s", $table, $e->getMessage());
                }
            } else {
                $this->console->warning("内存表{%s}未实现{%s}接口", $table, ITable::class);
            }
        }
    }

    /**
     * 生成进程名称
     * @param string   $name
     * @param int|null $id
     * @return string
     */
    final public function genPidName(string $name, int $id = null)
    {
        /**
         * @var Builder $builder
         */
        $name = sprintf("%s.%s.%s", $this->builder->getEnv(), $this->builder->getAppName(), $name);
        $id === null || $name = sprintf("%s.%s", $name, $id);
        return $name;
    }

    /**
     * 设置进程名称
     * 在master/manager/worker进程中
     * @param string   $name
     * @param int|null $id
     * @return $this
     */
    final public function setPidName(string $name, int $id = null)
    {
        $id === null || $name = "{$name}.{$id}";
        if (PHP_OS !== "Darwin" && function_exists('swoole_set_process_name')) {
            swoole_set_process_name($name);
        }
        return $this;
    }

    /**
     * 启动服务
     */
    final public function start()
    {
        // 1. 启动前触发
        if (true !== $this->beforeStart()) {
            $this->console->error("[server=start]被覆盖方法{%s::beforeRun}未返回{true}, 取消启动", $this->builder->getEntrypoint());
            return;
        }
        // 2. 启动参数
        if ($this->setting['pid_file']) {
            $managerFile = $this->builder->getManagerFile();
            if ($managerFile !== false) {
                $encode = $this->builder->encodeTemp();
                $this->managerToken = md5($encode);
                file_put_contents($managerFile, $encode);
            }
        }
        // 3. 启动服务
        parent::start();
    }

    final public function tick($ms, $callable)
    {
        $this->console->debug("设置{%d}秒定时器", $ms / 1000);
        parent::tick($ms, $callable);
    }
}
