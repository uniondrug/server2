<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-19
 */
namespace Uniondrug\Server2\Processes;

use Swoole\Process as SwooleProcess;
use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Process基类
 * @package Uniondrug\Servx\Processes
 */
abstract class Process extends SwooleProcess implements IProcess
{
    /**
     * 进程名称
     * @var string
     */
    public $processName;
    /**
     * Server实例
     * @var IHttp|ISocket
     */
    public $server;
    /**
     * 信号量定义
     * @var array
     */
    protected $signals = [
        SIGTERM => ['signalQuit'],
        SIGKILL => ['signalQuit'],
        SIGUSR1 => null,
        SIGUSR2 => null
    ];

    /**
     * Process constructor.
     * @param callable $server
     */
    public function __construct($server)
    {
        parent::__construct([
            $this,
            'runProcess'
        ], false, true);
        $this->server = $server;
        $this->processName = sprintf("%s.process.%s", $this->server->getBuilder()->getName(), get_called_class());
    }

    /**
     * 前置操作
     * @return bool
     */
    public function beforeRun()
    {
        return true;
    }

    /**
     * 业务过程
     * @return void
     */
    abstract public function run();

    /**
     * 执行过程
     * @return void
     */
    final public function runProcess()
    {
        $this->signalRegister();
        $this->beforeRun();
        // 1. process info
        $this->server->getPidTable()->addProcess($this->pid, $this->processName);
        $this->server->getConsole()->debug("[@%d]进程{%s}启动", $this->pid, $this->processName);
        // 2. run process
        $this->server->setPidName($this->processName);
        $this->run();
    }

    /**
     * 退出进程
     * @param int $signal
     * @return void
     */
    final public function signalQuit($signal = 0)
    {
        $this->server->getPidTable()->del($this->pid);
        $this->server->getConsole()->warn("[@%d]进程{%s}退出", $this->pid, $this->processName);
        $this->exit($signal);
    }

    /**
     * 注册信号量
     * @return void
     */
    final public function signalRegister()
    {
        $signals = array_keys($this->signals);
        $this->server->getConsole()->debug("[@%d]进程{%s}注册{%s}信息量", $this->pid, $this->processName, implode(',', $signals));
        foreach ($signals as $signal) {
            process::signal($signal, [
                $this,
                'signalTriggered'
            ]);
        }
    }

    /**
     * 收到信号量
     * @param int $signal
     * @return void
     */
    final public function signalTriggered($signal = 0)
    {
        $this->server->getConsole()->debug("[@%d]进程{%s}收到{%d}信号量", $this->pid, $this->processName, $signal);
        if (isset($this->signals[$signal]) && is_array($this->signals[$signal])) {
            foreach ($this->signals[$signal] as $call) {
                try {
                    $this->{$call}($signal);
                } catch(\Throwable $e) {
                    $this->server->getConsole()->error("[@%d]进程{%s}接受{%d}信号量触发{%s}失败 - %s", $this->pid, $this->processName, $signal, $call, $e->getMessage());
                }
            }
        }
    }
}
