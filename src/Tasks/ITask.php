<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Tasks;

/**
 * ITask
 * @package Uniondrug\Server2\Tasks
 */
interface ITask
{
    /**
     * @param $result
     */
    public function afterRun(& $result);

    /**
     * @return bool
     */
    public function beforeRun();

    /**
     * 任务执行过程
     * @return mixed
     */
    public function run();
}
