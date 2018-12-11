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
class ReloadallManager extends Abstracts\Manager
{
    /**
     * Reload服务
     * 1. 退出Task/Worker进程
     * 2. 退出Process进程
     */
    public function run()
    {
        // 1. 向Process进程发送SIGTERM退出信号
        $pp = $this->server->getPidTable()->getProcessPid();
        foreach ($pp as $p) {
            $this->server->console->warning("强制Kill{%d}号{%s}进程", $p['pid'], $p['name']);
            XProcess::kill($p['pid'], SIGTERM);
        }
        // 2. 退出Task/Worker进程
        $this->server->reload();
        // 3. 返回列表
        $result = ['stats' => $this->server->stats()];
        $result['process'] = $this->server->getPidTable()->toArray();
        return $result;
    }
}
