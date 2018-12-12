<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Processes;

use Swoole\Process as SwooleProcess;
use Uniondrug\Server2\Servers\IHttp;
use Uniondrug\Server2\Servers\ISocket;

/**
 * Process基类
 * @package Uniondrug\Server2\Processes
 */
abstract class XProcess extends SwooleProcess implements IProcess
{
    /**
     * 进程入参
     * @var array
     */
    protected $data;
    /**
     * 进程名称
     * @var string
     */
    protected $pidName;
    /**
     * 父进程ID
     * @var int
     */
    protected $ppid;
    /**
     * Server实例
     * @var IHttp|ISocket
     */
    protected $server;
    /**
     * 信号量
     * @var array
     */
    protected $signals = [
        SIGTERM => ['signalTerm'],
        SIGKILL => ['signalKill'],
        SIGUSR1 => ['signalUsr1'],
        SIGUSR2 => ['signalUsr2'],
        SIGCHLD => ['signalChild']
    ];

    /**
     * XProcess constructor.
     * @param IHttp|ISocket $server
     * @param array         $data
     */
    final public function __construct($server, array $data = [])
    {
        $this->data = $data;
        $this->server = $server;
        $stdRedirect = $this->server->getBuilder()->getOption('processStdRedirect') === true;
        $createPipeline = $this->server->getBuilder()->getOption('processCreatePipeline') !== false;
        $this->ppid = posix_getppid();
        parent::__construct([
            $this,
            'runProcess'
        ], $stdRedirect, $createPipeline);
    }

    /**
     * Process进程启动时触发
     */
    final public function runProcess()
    {
        $this->server->getConsole()->setPrefix("[{$this->server->getBuilder()->getAddr()}][pid={$this->pid}][process]");
        $this->beforeRun();
        // 1. 计算进程名称
        $name = sprintf("%s.%s", $this->server->genPidName('process'), get_class($this));
        $this->pidName = $name.($this->pidName ? ".{$this->pidName}" : '');
        $this->server->getPidTable()->addProcess($this->pid, $this->pidName);
        $this->server->setPidName($this->pidName);
        $this->server->getConsole()->info("进程启动");
        // 2. 进程信号监听
        $this->registerSignal();
        // 3. 进入业务
        $this->run();
    }

    /**
     * 执行run()方法前操作
     */
    public function beforeRun()
    {
    }

    /**
     * 收到SIGCHLD/子进程退出
     * @param int $signal
     */
    public function signalChild(int $signal)
    {
    }

    /**
     * 收到SIGKILL/强杀信号
     * 一般和SIGQUIT类似捕获不到
     * @param int $signal
     */
    public function signalKill(int $signal)
    {
    }

    /**
     * 收到SIGTERM/退出信号
     * @param int $signal
     */
    public function signalTerm(int $signal)
    {
        $this->server->getConsole()->warning("进程退出");
        // 1. 先杀掉子进程
        $ps = $this->server->getPidTable()->toArray();
        foreach ($ps as $p) {
            // 1.1 not child
            if ($p['ppid'] != $this->pid) {
                continue;
            }
            // 1.2 is child process
            parent::kill($p['pid'], SIGKILL);
        }
        // 2. Kill本进程
        $this->server->getPidTable()->del($this->pid);
        $this->exit($signal);
    }

    /**
     * 收到SIGUSR1信号
     * @param int $signal
     */
    public function signalUsr1(int $signal)
    {
    }

    /**
     * 收到SIGUSR2信号
     * @param int $signal
     */
    public function signalUsr2(int $signal)
    {
    }

    /**
     * 注册信号量
     * 当run()方法使用了死循环时会阻止信号量触发
     */
    private function registerSignal()
    {
        $signals = array_keys($this->signals);
        $this->server->getConsole()->debug("注册信号");
        foreach ($signals as $signal) {
            parent::signal($signal, function(int $sig){
                $this->registerSignalFired($sig);
            });
        }
    }

    /**
     * 已注册信息被触发
     * @param int $signal
     */
    private function registerSignalFired(int $signal)
    {
        $this->server->getConsole()->info("收到{%d}号信号", $signal);
        // 1. 回调定义检查
        $calls = isset($this->signals[$signal]) && is_array($this->signals[$signal]) && count($this->signals[$signal]) ? $this->signals[$signal] : false;
        if ($calls === false) {
            $this->server->getConsole()->debug("第{%d}号信号无回调", $signal);
            return;
        }
        // 2. 遍历回调
        foreach ($calls as $call) {
            // 2.1 方法定义检查
            if (!method_exists($this, $call)) {
                $this->server->getConsole()->debug("第{%d}号信号未找到{%s}方法", $signal, $call);
                continue;
            }
            try {
                // 2.2 执行回调
                $this->{$call}($signal);
                $this->server->getConsole()->debug("第{%d}号信号触发{%s}方法", $signal, $call);
            } catch(\Throwable $e) {
                // 2.3 执行失败
                $this->server->getConsole()->error("第{%d}号信号触发{%s}方法 - %s", $signal, $call, $e->getMessage());
            }
        }
    }
}
