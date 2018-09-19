<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Servers\Traits;

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Throwable;
use Uniondrug\Server2\Interfaces\IProcess;
use Uniondrug\Server2\Interfaces\IServer;
use Uniondrug\Server2\Interfaces\ISocket;
use Uniondrug\Server2\Interfaces\ITask;

/**
 * 事件定义
 * @package Uniondrug\Server2\Servers\Traits
 */
trait EventsTrait
{
    /**
     * 事件定义
     * @var array
     */
    public $events = [];

    /**
     * 断开连接
     * @param ISocket $server
     * @param int     $fd
     * @param int     $reactorId
     * @since 2.1
     */
    public function onClose(ISocket $server, int $fd, int $reactorId)
    {
    }

    /**
     * 客户端成功连接到服务器
     * @param IServer $server
     * @param int     $fd
     * @param int     $reactorId
     * @since 2.0
     */
    public function onConnect(IServer $server, int $fd, int $reactorId)
    {
    }

    /**
     * 任务执行完成
     * @param IServer $server
     * @param int     $taskId
     * @param string  $data
     * @since 2.0
     */
    public function onFinish(IServer $server, int $taskId, string $data)
    {
    }

    /**
     * Manager进程启动
     * @param IServer $server
     * @since 2.0
     */
    public function onManagerStart(IServer $server)
    {
        $name = $server->getAppName()." manager ";
        $server->setProcessName($name);
        $server->getConsole()->debug("[manager:start] Manager进程启动, 进程名[%s], 进程ID[%d].", $name, $server->getManagerPid());
    }

    /**
     * Manager进程退出
     * @param IServer $server
     * @since 2.0
     */
    public function onManagerStop(IServer $server)
    {
        $server->getConsole()->debug("[manager:stop] Manager进程退出, 进程名[%s], 进程ID[%d].", $server->getAppName()." manager", $server->getManagerPid());
    }

    /**
     * 收到WebSocket消息
     * @param IServer $server
     * @param Frame   $frame
     * @since 2.1
     */
    public function onMessage(IServer $server, Frame $frame)
    {
    }

    /**
     * 打开连接
     * @param ISocket $server
     * @param Request $request
     * @since 2.1
     */
    public function onOpen(ISocket $server, Request $request)
    {
    }

    /**
     * Worker进程收到Pipe消息
     * @param IServer $server
     * @param int     $srcWorkerId 由哪个Worker发送
     * @param mixed   $message
     * @since 2.0
     */
    final public function onPipeMessage(IServer $server, int $srcWorkerId, $message)
    {
        $data = json_decode($message, true);
        if (!is_array($data)) {
            $server->getConsole()->error("[pipe:error] 限JSON格式消息");
            return;
        }
        if (!isset($data['class'])) {
            $server->getConsole()->error("[pipe:error] JSON消息未定义[class]字段");
            return;
        }
        if (is_a($data['class'], ITask::class, true)) {
            $server->getConsole()->debug("[pipe:task] 由[%d]号Worker触发[%s]Task", $srcWorkerId, $data['class']);
            $server->task($data, self::$defaultTaskWorkerId);
            return;
        }
        if (is_a($data['class'], IProcess::class, true)) {
            $server->getConsole()->debug("[pipe:process] 由[%d]号Worker触发[%s]Process", $srcWorkerId, $data['class']);
            $server->runProcess($data['class'], $data['params']);
            return;
        }
    }

    /**
     * Worker进程收到数据
     * @param IServer $server
     * @param int     $fd
     * @param int     $reactor_id
     * @param string  $data
     * @since 2.0
     */
    public function onReceive(IServer $server, int $fd, int $reactor_id, string $data)
    {
    }

    /**
     * 收到HTTP请求
     * @param $request
     * @param $response
     * @since 2.0
     */
    public function onRequest($request, $response)
    {
    }

    /**
     * 服务启动后触发
     * @param IServer $server
     * @since 2.0
     */
    public function onShutdown(IServer $server)
    {
        $name = $server->getAppName()." master";
        $server->getConsole()->debug("[server:shutdown] 服务停止, 进程名[%s], 进程ID[%d].", $name, $server->getMasterPid());
    }

    /**
     * 服务启动后触发
     * @param IServer $server
     * @since 2.0
     */
    public function onStart(IServer $server)
    {
        $name = $server->getAppName()." master";
        $server->setProcessName($name);
        $server->getConsole()->debug("[server:start] 服务启动, 进程名[%s], 进程ID[%d].", $name, $server->getMasterPid());
    }

    /**
     * task triggered
     * <code>
     * $data = [
     *     'class' => ExampleTask,
     *     'param' => [
     *         'key' => 'value'
     *     ]
     * ]
     * </code>
     * @param IServer $server
     * @param int     $taskId      Task任务ID
     * @param int     $srcWorkerId Task任务由哪个Worker触发
     * @param mixed   $data
     * @since 2.0
     */
    final public function onTask(IServer $server, int $taskId, int $srcWorkerId, $data)
    {
        // 1. 入能检查
        if (!is_array($data) || !isset($data['class'])) {
            $server->getConsole()->error("[task:error] 仅接受数组参数.", $taskId);
            return;
        }
        $data['params'] = isset($data['params']) && is_array($data['params']) ? $data['params'] : [];
        /**
         * @var ITask $itask
         */
        try {
            $itask = new $data['class']($server);
            if (false === $itask->beforeRun($srcWorkerId, $this->worker_id, $taskId)) {
                $server->getConsole()->debug("[task:failure][%s] 忽略执行", $data['class']);
            } else {
                $itask->run($data['params']);
                $server->getConsole()->debug("[task:success][%s] 执行完成", $data['class']);
            }
        } catch(Throwable $e) {
            $server->getConsole()->error("[task:failure][%s] 执行失败 - %s.", $data['class'], $e->getMessage());
        }
    }

    /**
     * Worker进程启动
     * @param IServer $server
     * @param int     $workerId
     * @since 2.0
     */
    public function onWorkerStart(IServer $server, int $workerId)
    {
        $name = $server->getAppName()." worker ".$workerId;
        $server->setProcessName($name);
        $server->getConsole()->debug("[worker:start] 第%d号Worker启动, 进程名[%s], 进程ID[%d].", $workerId, $name, $server->getWorkerPid());
    }

    /**
     * Worker进程退出
     * @param IServer $server
     * @param int     $workerId
     * @since 2.0
     */
    public function onWorkerStop(IServer $server, int $workerId)
    {
        $server->getConsole()->debug("[worker:stoped] 第%d号Worker停止, 进程名[%s], 进程ID[%d].", $workerId, $server->getAppName()." worker {$workerId}", $server->getWorkerPid());
    }
}
