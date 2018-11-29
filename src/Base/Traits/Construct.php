<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Traits;

use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Processes\IProcess;
use Uniondrug\Server2\Tables\PidTable;

/**
 * Server实例构造
 * @package Uniondrug\Server2\Agent\Traits
 */
trait Construct
{
    /**
     * 构造Server实例
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
        $this->console = new Console();
        $this->console->setPrefix($this->builder->getAddress());
        $this->pidTable = PidTable::setup($this->builder->getPidTableSize());
        parent::__construct($this->builder->getHost(), $this->builder->getPort(), $this->builder->getStartMode(), $this->builder->getStartSockType());
        $this->console->info("Server{%s}Initialized", $this->builder->getEntrypoint());
        $this->initializeSettings();
        $this->initializeEvents();
        $this->initializeProcesses();
        $this->initializeManagers();
        $this->beforeStart();
    }

    /**
     * 启动前处理
     */
    public function beforeStart()
    {
    }

    /**
     * 服务参数
     */
    private function initializeSettings()
    {
        $setting = $this->builder->getSetting();
        $this->builder->isDaemon() && $setting['daemonize'] = 1;
        $this->set($setting);
        $this->console->info("SettingInitialized");
        foreach ($setting as $key => $value) {
            $this->console->debug("Setting{%s}AssignTo{%s}", $key, $value);
        }
    }

    /**
     * 事件监听
     */
    private function initializeEvents()
    {
        $this->console->info("EventInitialized");
        $events = array_merge($this->events, $this->mergedEvents);
        ksort($events);
        reset($events);
        foreach ($events as $event) {
            $call = 'on'.ucfirst($event);
            if (method_exists($this, $call)) {
                $this->on($event, [
                    $this,
                    $call
                ]);
                $this->console->debug("Event{%s}Bind{%s}Method", $event, $call);
            } else {
                $this->console->warn("Event{%s}Unregister{%s}Method", $event, $call);
            }
        }
    }

    /**
     * 监听127管理地址
     */
    private function initializeManagers()
    {
        if ($this->builder->getAddress() !== $this->builder->getManagerAddrress()) {
            $this->console->warn("Manager{%s}Listenning", $this->builder->getManagerAddrress());
            $this->addListener($this->builder->getManagerHost(), $this->builder->getManagerPort(), SWOOLE_SOCK_TCP);
        }
    }

    /**
     * 注册Process记录
     */
    private function initializeProcesses()
    {
        $this->console->info("ProcessInitialized");
        $processes = $this->builder->getProcess();
        foreach ($processes as $process) {
            if (is_a($process, IProcess::class, true)) {
                $this->addProcess(new $process($this));
                $this->console->debug("Process{%s}Join{%s}", $process, $this->builder->getEntrypoint());
            } else {
                $this->console->warn("Process{%s}Unimplment{%s}Interface", $process, IProcess::class);
            }

        }
    }
}
