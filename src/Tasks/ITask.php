<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-16
 */
namespace Uniondrug\Server2\Tasks;

/**
 * Task接口
 * @package Uniondrug\Servx\Tasks
 */
interface ITask
{
    /**
     * 任务后置操作
     * 当执行完成{方法run()执行完成}后, 触发本方法, 其入参
     * 即为run()方法的返回值; 同时本方法对入参的操作会影响到
     * run()方法的原始返回值.
     * @param mixed $result run()的返回值
     * @return void
     */
    public function afterRun(& $result);

    /**
     * 任务前置操作
     * 本方法返加boolean类型值, 决定了run()、afterRun()方法是
     * 否会继续执行, 当返回true时, 继续执行, 反之将跳过run()、
     * afterRun()方法, 退出任务
     * @return bool
     */
    public function beforeRun();

    /**
     * Task执行过程
     * @return mixed
     */
    public function run();
}
