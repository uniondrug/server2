<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Traits;

/**
 * 公共方法
 * @package Uniondrug\Server2\Agent\Traits
 */
trait Process
{
    /**
     * 启动一个进程
     * @param string $class
     * @param array  $params
     * @return bool
     */
    public function runProcess(string $class, array $params = [])
    {
        $this->getConsole()->warn("Start processes {%s}", $class);
        return false;
    }
}
