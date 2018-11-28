<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events\Task;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Task完成
 * @package Uniondrug\Server2\Agent\Events\Task
 */
trait FinishEvent
{
    /**
     * 任务执行完成
     * @param IHttp|ISocket $server
     * @param int           $taskId
     * @param string        $data
     */
    public function doFinish($server, $taskId, $data)
    {
    }

    /**
     * 任务执行完成
     * @param IHttp|ISocket $server
     * @param int           $taskId
     * @param string        $data
     */
    final public function onFinish($server, int $taskId, string $data)
    {
        $server->getConsole()->debug("[@%d.%d][task=%d]事件onFinish已触发", $server->getWorkerPid(), $server->getWorkerId(), $taskId);
        $this->doFinish($server, $taskId, $data);
    }
}
