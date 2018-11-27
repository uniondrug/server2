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
        parent::__construct($this->builder->getHost(), $this->builder->getPort());
        $this->console->debug("初始化{%s}服务", $this->builder->getEntrypoint());
        $this->initializeSettings();
        $this->initializeEvents();
        $this->initializeProcesses();
        $this->initializeManagers();
    }

    /**
     * 服务参数
     */
    private function initializeSettings()
    {
        $this->console->debug("服务参数初始化");
        $setting = $this->builder->getSetting();
        $this->builder->isDaemon() && $setting['daemonize'] = 1;
        $this->set($setting);
        foreach ($setting as $key => $value) {
            $this->console->debug("服务参数{%s}的值为{%s}", $key, $value);
        }
    }

    /**
     * 事件监听
     */
    private function initializeEvents()
    {
        $this->console->debug("事件绑定初始化");
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
                $this->console->debug("绑定{%s}事件回调到{%s}方法", $event, $call);
            } else {
                $this->console->warn("未定义{%s}事件回调方法{%s}", $event, $call);
            }
        }
    }

    /**
     * 监听127管理地址
     */
    private function initializeManagers()
    {
        if ($this->builder->getAddress() !== $this->builder->getManagerAddrress()) {
            $this->console->debug("注册{%s}管理监听", $this->builder->getManagerAddrress());
            $this->addListener($this->builder->getManagerHost(), $this->builder->getManagerPort(), SWOOLE_SOCK_TCP);
        }
    }

    /**
     * 注册Process记录
     */
    private function initializeProcesses()
    {
        $this->console->debug("外挂进程Process初始化");
        $processes = $this->builder->getProcess();
        foreach ($processes as $process) {
            if (is_a($process, IProcess::class, true)) {
                $this->addProcess(new $process($this));
                $this->console->debug("进程{%s}加入服务", $process);
            } else {
                $this->console->warn("进程{%s}未实现{%s}接口", $process, IProcess::class);
            }
        }
    }
}
