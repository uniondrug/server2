<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-05
 */
namespace Uniondrug\Server2\Managers;

/**
 * 列出服务状态
 * @package Uniondrug\Server2\Managers
 */
class StatusManager extends Abstracts\Manager
{
    /**
     * 列出服务状态
     * @return array
     */
    public function run()
    {
        $result = ['stats' => $this->server->stats()];
        $result['process'] = $this->server->getPidTable()->toArray();
        return $result;
    }
}
