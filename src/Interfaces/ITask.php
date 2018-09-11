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
     * @return IServer
     */
    public function getServer();

    /**
     * run task progress
     * @param array $data
     */
    public function run(array $data);
}
