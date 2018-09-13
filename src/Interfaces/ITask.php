<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Interfaces;

/**
 * ITask/任务接口
 * @package Uniondrug\Server2\Interfaces
 */
interface ITask
{
    /**
     * 任务执行前置
     * @param int $srcWorkerId 从哪个worker触发
     * @param int $workerId    交由哪个worker运行
     * @param int $taskId      任务ID
     * @return bool
     */
    public function beforeRun(int $srcWorkerId, int $workerId, int $taskId);

    /**
     * 读取共享的Server实例
     * @return IServer|ISocket
     */
    public function getServer();

    /**
     * 运行Task任务
     * @param array $data
     */
    public function run(array $data);
}
