<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Tasks;

use Uniondrug\Server2\Servers\IHttp;
use Uniondrug\Server2\Servers\ISocket;

/**
 * 异步任务基类
 * @package Uniondrug\Server2\Tasks
 */
abstract class XTask implements ITask
{
    /**
     * Server实例
     * @var IHttp|ISocket
     */
    protected $server;
    /**
     * 任务ID
     * @var int
     */
    protected $taskId;
    /**
     * 任务入参
     * @var array
     */
    protected $data;

    /**
     * 异步任务实例化
     * @param IHttp|ISocket $server Server实例
     * @param int           $taskId 任务ID
     * @param array         $data   任务入参
     */
    final public function __construct($server, int $taskId, array $data)
    {
        $this->server = $server;
        $this->taskId = $taskId;
        $this->data = $data;
    }

    /**
     * 后置操作
     * 当run()方法成功执行之后触发, 基入参以引用模式操作
     * run()方法的返回值, 可以本方法中操作run()的返回结果
     * @param mixed $result
     * @return void
     */
    public function afterRun(& $result)
    {
    }

    /**
     * 前置操作
     * 在run()方法调用之前触发, 必须返回boolean类型, 当
     * 返回true时, 继续执行run()、afterRun()方法, 反之
     * 跳过run()、afterRun()方法, 并完成任务
     * @return bool
     */
    public function beforeRun()
    {
        return true;
    }
}
