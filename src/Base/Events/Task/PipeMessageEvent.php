<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events\Task;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * 进程间Pipe消息
 * @package Uniondrug\Server2\Agent\Events\Task
 */
trait PipeMessageEvent
{
    /**
     * 收到管道消息
     * @param IHttp|ISocket $server
     * @param int           $srcWorkerId
     * @param string        $message
     */
    public function doPipeMessage($server, int $srcWorkerId, $message)
    {
        $this->task($message, -1);
    }

    /**
     * 收到管道消息
     * @param IHttp|ISocket $server
     * @param int           $srcWorkerId
     * @param string        $message
     */
    final public function onPipeMessage($server, int $srcWorkerId, $message)
    {
        $this->doPipeMessage($server, $srcWorkerId, $message);
    }
}
