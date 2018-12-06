<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-05
 */
namespace Uniondrug\Server2\Managers;

/**
 * 服务注册
 * @package Uniondrug\Server2\Managers
 */
class ConsulManager extends Abstracts\Manager
{
    /**
     * 向Consul发起服务注册
     * @return array
     */
    public function run()
    {
        $result = ['stats' => $this->server->stats()];
        return $result;
    }
}
