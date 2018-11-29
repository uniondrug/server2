<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Worker进程退出
 * @package Uniondrug\Server2\Base\Events
 */
trait WorkerStopEvent
{
    /**
     * Worker进程退出
     * @param IHttp|ISocket $server
     * @param int           $workerId
     */
    public function doWorkerStop($server, int $workerId)
    {
    }

    /**
     * Worker进程退出
     * @param IHttp|ISocket $server
     * @param int           $workerId
     */
    final public function onWorkerStop($server, int $workerId)
    {
        $type = $server->isTasker() ? "tasker" : "worker";
        $name = $server->genPidName($type, $workerId);
        $server->getPidTable()->del($server->getWorkerPid());
        $server->getConsole()->warn("[@%d.%d]%s{%s}Quit", $server->getWorkerPid(), $server->getWorkerId(), ucfirst($type), $name);
        $this->doWorkerStop($server, $workerId);
    }
}
