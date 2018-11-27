<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-16
 */
namespace Uniondrug\Server2\Tasks;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Task基类
 * @package Uniondrug\Servx\Tasks
 */
abstract class Task implements ITask
{
    /**
     * Server实例
     * Task所在Tasker进程
     * @var IHttp|ISocket
     */
    protected $server;
    /**
     * Task源数据
     * @var array $data 任务参数
     */
    protected $data;
    /**
     * 任务ID
     * @var int
     */
    protected $taskId;

    /**
     * @param IHttp|ISocket $server Tasker对象
     * @param array|string  $data   任务参数
     * @param int           $taskId 任务ID
     */
    final public function __construct($server, array $data, int $taskId)
    {
        $this->server = $server;
        $this->data = $data;
        $this->taskId = $taskId;
    }

    /**
     * 任务后置操作
     * 当执行完成{方法run()执行完成}后, 触发本方法, 其入参
     * 即为run()方法的返回值; 同时本方法对入参的操作会影响到
     * run()方法的原始返回值.
     * @param mixed $result run()的返回值
     * @return void
     */
    public function afterRun(& $result)
    {
    }

    /**
     * 任务前置操作
     * 本方法返加boolean类型值, 决定了run()、afterRun()方法是
     * 否会继续执行, 当返回true时, 继续执行, 反之将跳过run()、
     * afterRun()方法, 退出任务
     * @return bool
     */
    public function beforeRun()
    {
        return true;
    }

    /**
     * Task执行过程
     * @return mixed
     */
    abstract function run();
}
