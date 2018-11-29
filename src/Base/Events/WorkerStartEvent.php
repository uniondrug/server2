<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Worker进程启动
 * @package Uniondrug\Server2\Base\Events
 */
trait WorkerStartEvent
{
    /**
     * Worker进程启动
     * @param IHttp|ISocket $server
     * @param int           $workerId
     */
    public function doWorkerStart($server, $workerId)
    {
    }

    /**
     * Worker进程启动
     * @param IHttp|ISocket $server
     * @param int           $workerId
     */
    final public function onWorkerStart($server, $workerId)
    {
        $type = $server->isTasker() ? "tasker" : "worker";
        $name = $server->genPidName($type, $workerId);
        $server->setPidName($name);
        $server->getPidTable()->addWorker($server->getWorkerPid(), $name);
        $server->getConsole()->info("[@%d.%d]%s{%s}Started", $server->getWorkerPid(), $server->getWorkerId(), ucfirst($type), $name);
        $this->doWorkerStart($server, $workerId);
    }
}
