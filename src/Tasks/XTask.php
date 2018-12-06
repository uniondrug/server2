<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Tasks;

use Uniondrug\Server2\Servers\IHttp;
use Uniondrug\Server2\Servers\ISocket;

/**
 * Task基类
 * @package Uniondrug\Server2\Tasks
 */
abstract class XTask implements ITask
{
    /**
     * @var IHttp|ISocket
     */
    protected $server;
    protected $taskId;
    /**
     * 任务入参
     * @var array
     */
    protected $data;

    /**
     * XTask constructor.
     * @param IHttp|ISocket $server
     * @param int           $taskId
     * @param array         $data
     */
    final public function __construct($server, int $taskId, array $data)
    {
        $this->server = $server;
        $this->taskId = $taskId;
        $this->data = $data;
    }

    /**
     * 任务后置操作
     * @param mixed $result 由run()方法返回值
     */
    public function afterRun(& $result)
    {
    }

    /**
     * 任务前置操作
     * @return bool
     */
    public function beforeRun()
    {
        return true;
    }
}
