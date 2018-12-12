<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-05
 */
namespace Uniondrug\Server2\Managers;

use Uniondrug\Server2\Processes\XProcess;

/**
 * 重载服务
 * @package Uniondrug\Server2\Managers
 */
class ReloadManager extends Abstracts\Manager
{
    /**
     * Reload服务
     * 1. 退出Task/Worker进程
     * 2. 退出Process进程
     */
    public function run()
    {
        $this->server->reload();
        $result = ['stats' => $this->server->stats()];
        $result['process'] = $this->server->getPidTable()->toArray();
        return $result;
    }
}