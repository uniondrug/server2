<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2;

use Uniondrug\Server2\Interfaces\IServer;
use Uniondrug\Server2\Interfaces\ISocket;
use Uniondrug\Server2\Interfaces\ITask;

/**
 * Task
 * @package Uniondrug\Server2
 */
abstract class Task implements ITask
{
    /**
     * Server实例
     * @var IServer|ISocket
     */
    private $server;

    /**
     * 任务构造
     * @param IServer|ISocket $server
     */
    final public function __construct(IServer $server)
    {
        $this->server = $server;
    }

    /**
     * 任务执行前置
     * @param int $srcWorkerId 从哪个worker触发
     * @param int $workerId    交由哪个worker运行
     * @param int $taskId      任务ID
     * @return bool
     */
    public function beforeRun(int $srcWorkerId, int $workerId, int $taskId)
    {
        $this->getServer()->getConsole()->warning("[".get_class($this)."][%d] 由[Worker #%d]调度到[Worker #%d]", $taskId, $srcWorkerId, $workerId);
        return true;
    }

    /**
     * 读取共享的Server实例
     * @return IServer|ISocket
     */
    public function getServer()
    {
        return $this->server;
    }
}
