<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Traits;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\WebSocket\Frame as SwooleFrame;
use swoole_process;
use Uniondrug\Server2\Servers\XHttp;
use Uniondrug\Server2\Servers\XSocket;

/**
 * 事件定义
 * @package Uniondrug\Server2\Servers\Traits
 */
trait EventsTrait
{
    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     * @link https://wiki.swoole.com/wiki/page/p-event/onClose.html
     * @param XHttp|XSocket $server
     * @param int           $fd
     * @param int           $reactorId
     * @return void
     */
    final public function onClose($server, $fd, $reactorId)
    {
    }

    /**
     * 有新的连接进入时，在worker进程中回调
     * @link https://wiki.swoole.com/wiki/page/49.html
     * @param XHttp|XSocket $server
     * @param int           $fd
     * @param int           $reactorId
     * @return void
     */
    final public function onConnect($server, int $fd, int $reactorId)
    {
    }

    /**
     * 当worker进程投递的任务在task_worker中完成
     * @param XHttp|XSocket $server
     * @param int           $taskId
     * @param string        $data
     */
    final public function onFinish($server, int $taskId, string $data)
    {
        $server->doFinish($server, $taskId, $data);
    }

    /**
     * Manager进程启动
     * @link https://wiki.swoole.com/wiki/page/190.html
     * @param XHttp|XSocket $server
     */
    final public function onManagerStart($server)
    {
        $server->console->setPrefix("[{$server->builder->getAddr()}][pid={$server->manager_pid}][manager]");
        // 1. addto: pid table
        $name = $server->genPidName('manager');
        $server->getPidTable()->addManager($server->manager_pid, $name);
        // 2. reset: pid name
        $server->setPidName($name);
        $server->console->info("进程启动");
        // 3. do: manager start
        $server->doManagerStart($server);
    }

    /**
     * Manager进程退出
     * @link https://wiki.swoole.com/wiki/page/191.html
     * @param XHttp|XSocket $server
     */
    final public function onManagerStop($server)
    {
        // 1. del: pid table
        $name = $server->genPidName('manager');
        $server->getPidTable()->del($server->manager_pid);
        $server->console->warning("进程退出");
        // 2. do: manager start
        $server->doManagerStop($server);
    }

    /**
     * Websocket收到消息
     * @link https://wiki.swoole.com/wiki/page/402.html
     * @param XSocket     $server
     * @param SwooleFrame $frame
     */
    final public function onMessage($server, $frame)
    {
        $server->doMessage($server, $frame);
    }

    /**
     * WebSocket打开连接
     * @link https://wiki.swoole.com/wiki/page/401.html
     * @param XSocket       $server
     * @param SwooleRequest $request
     */
    final public function onOpen($server, $request)
    {
    }

    /**
     * 收到管道消息转发异步任务
     * @param XHttp|XSocket $server
     * @param int           $srcWorkerId
     * @param string        $message
     */
    final public function onPipeMessage($server, int $srcWorkerId, $message)
    {
        try {
            $taskId = $server->task($message, -1);
            if ($taskId !== false) {
                $server->console->debug("[task=%d]PIPE转发TASK", $taskId);
                return;
            }
            throw new \Exception("return false from task() called");
        } catch(\Throwable $e) {
            $server->console->error("PIPE转发TASK失败 - %s", $e->getMessage());
        }
    }

    /**
     * Worker进程接收到数据时触发
     * @link https://wiki.swoole.com/wiki/page/50.html
     * @param XHttp|XSocket $server
     * @param int           $fd
     * @param int           $reactor_id
     * @param string        $data
     */
    final public function onReceive($server, int $fd, int $reactor_id, string $data)
    {
    }

    /**
     * 收到HTTP请求
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     */
    final public function onRequest($request, $response)
    {
        $uniqid = isset($request->header['HTTP_UDSDK_REQID']) ? $request->header['HTTP_UDSDK_REQID'] : null;
        $uniqid || $uniqid = uniqid('req');
        $response->header("Server", $this->builder->getAppName().'/'.$this->builder->getAppVersion());
        $response->header("Udsdk-Reqid", $uniqid);
        // HTTP请求日志
        $request->requestId = $uniqid;
        $begin = microtime(true);
        $this->defer(function() use ($request, $response, $begin){
            $this->httpAccessLogger($this, $request, $response, $begin);
        });
        // 管理进程
        if (isset($request->header, $request->header['host']) && $request->header['host'] === $this->builder->getManagerAddr()) {
            $this->onManagerRequest($request, $response);
            return;
        }
        $this->doRequest($request, $response);
    }

    /**
     * Server正常结束时触发
     * @link https://wiki.swoole.com/wiki/page/p-event/onShutdown.html
     * @param XHttp|XSocket $server
     * @return void
     */
    final public function onShutdown($server)
    {
        // 1. addto: pid table
        $server->getPidTable()->del($server->master_pid);
        $server->console->warning("进程退出");
        // 2. do: shutdown
        $server->doShutdown($server);
    }

    /**
     * Server启动在主进程的主线程回调此函数
     * @link https://wiki.swoole.com/wiki/page/p-event/onStart.html
     * @param XHttp|XSocket $server
     * @return void
     */
    final public function onStart($server)
    {
        $server->console->setPrefix("[{$server->builder->getAddr()}][pid={$server->master_pid}][master]");
        // 1. addto: pid table
        $name = $server->genPidName('master');
        $server->getPidTable()->addMaster($server->master_pid, $name);
        // 2. reset: pid name
        $server->setPidName($name);
        $server->console->info("进程启动", $name);
        // 3. do: start
        $server->doStart($server);
    }

    /**
     * Task触发
     * @link https://wiki.swoole.com/wiki/page/54.html
     * @param XHttp|XSocket $server
     * @param int           $taskId
     * @param int           $srcWorkerId
     * @param string        $data
     * @return mixed
     */
    final public function onTask($server, int $taskId, int $srcWorkerId, $data)
    {
        $t = microtime(true);
        $server->getPidTable()->incr($server->getWorkerPid(), 'onTask', 1);
        $server->console->debug("[task=%d][begin]任务开始", $taskId);
        try {
            $done = $server->doTask($server, $taskId, $data);
            $server->console->debug("[task=%d][end][duration=%f]任务完成", $taskId, sprintf("%.06f", microtime(true) - $t));
            $server->getPidTable()->incr($server->getWorkerPid(), 'onFinish', 1);
            return json_encode($done, JSON_UNESCAPED_UNICODE);
        } catch(\Exception $e) {
            $server->getPidTable()->incr($server->getWorkerPid(), 'onFinish', 1);
            $server->console->error("[task=%d][end][duration=%f]任务失败 - %s", $taskId, sprintf("%.06f", microtime(true) - $t), $e->getMessage());
            return "false";
        }
    }

    /**
     * 当Worker/Task进程发生异常后会在Manager进程内触发。
     * @link https://wiki.swoole.com/wiki/page/166.html
     * @param XHttp|XSocket $server
     * @param int           $workerId
     * @param int           $workerPid
     * @param int           $errno
     * @param int           $signal
     */
    final public function onWorkerError($server, int $workerId, int $workerPid, int $errno, int $signal)
    {
        $server->doWorkerError($server, $errno, $signal);
    }

    /**
     * Worker/Task进程启动时触发
     * @link https://wiki.swoole.com/wiki/page/p-event/onWorkerStart.html
     * @param XHttp|XSocket $server
     * @param int           $workerId
     * @return void
     */
    final public function onWorkerStart($server, $workerId)
    {
        // 1. addto: pid table
        $type = $this->taskworker ? 'tasker' : 'worker';
        $server->console->setPrefix("[{$server->builder->getAddr()}][pid={$server->getWorkerPid()}][{$type}={$server->getWorkerId()}]");
        $name = $server->genPidName($type, $workerId);
        $this->taskworker ? $server->getPidTable()->addTasker($server->worker_pid, $name) : $server->getPidTable()->addWorker($server->worker_pid, $name);
        // 2. reset: pid name
        $server->setPidName($name);
        $server->console->info("进程启动");
        // 3. 在Worker/Tasker进程中检查Manager进程是否已退出
        //    若Manager进程已退出, 则退出当前进程
        swoole_timer_tick(3000, function() use ($server, $name){
            if (false === swoole_process::kill($server->getManagerPid(), 0)) {
                $server->console->warning("进程被回收");
                swoole_process::kill($server->getWorkerPid(), SIGTERM);
            }
        });
        // 3. do: workerStart
        $server->doWorkerStart($server);
    }

    /**
     * Worker/Task进程退出时触发
     * @link https://wiki.swoole.com/wiki/page/p-event/onWorkerStop.html
     * @param XHttp|XSocket $server
     * @param int           $workerId
     */
    final public function onWorkerStop($server, int $workerId)
    {
        // 1. del: pid table
        $server->getPidTable()->del($server->worker_pid);
        $server->console->warning("进程退出");
        // 2. do: workerStop
        $server->doWorkerStop($server);
    }
}
