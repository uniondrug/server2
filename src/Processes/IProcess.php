<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-19
 */
namespace Uniondrug\Server2\Processes;

/**
 * Process接口
 * @package Uniondrug\Server2\Processes
 */
interface IProcess
{
    /**
     * Process过程
     * @return void
     */
    public function run();
}
