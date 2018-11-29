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
     * Process入参
     * @var array
     */
    public $data;
    /**
     * 信号量定义
     * @var array
     */
    protected $signals = [
        SIGTERM => ['signalQuit'],
        SIGKILL => ['signalQuit'],
        SIGUSR1 => null,
        SIGUSR2 => null,
        SIGCHLD => null
    ];

    /**
     * Process constructor.
     * @param callable $server
     */
    public function __construct($server, array $data = [])
    {
        parent::__construct([
            $this,
            'runProcess'
        ], false, true);
        $this->server = $server;
        $this->data = $data;
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
     * @return IHttp|ISocket
     */
    public function getServer()
    {
        return $this->server;
    }

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
        $this->server->getConsole()->info("[@%d]Process{%s}Started", $this->pid, get_called_class());
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
        $this->server->getConsole()->warn("[@%d]Process{%s}Quit", $this->pid, get_called_class());
        $this->exit($signal);
    }

    /**
     * 注册信号量
     * @return void
     */
    final public function signalRegister()
    {
        $signals = array_keys($this->signals);
        $this->server->getConsole()->debug("[@%d]Process{%s}Register{%s}Signal", $this->pid, get_called_class(), implode(',', $signals));
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
        $this->server->getConsole()->debug("[@%d]Process{%s}Receive{%d}Signal", $this->pid, $this->processName, $signal);
        if (isset($this->signals[$signal]) && is_array($this->signals[$signal])) {
            foreach ($this->signals[$signal] as $call) {
                try {
                    $this->{$call}($signal);
                } catch(\Throwable $e) {
                    $this->server->getConsole()->error("[@%d]Process{%s}Accept{%d}Signal{%s}Fail - %s", $this->pid, get_called_class(), $signal, $call, $e->getMessage());
                }
            }
        }
    }
}
