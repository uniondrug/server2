<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Manager进程退出
 * @package Uniondrug\Server2\Base\Events
 */
trait ManagerStopEvent
{
    /**
     * Manager进程退出
     * @param IHttp|ISocket $server
     */
    public function doManagerStop($server)
    {
    }

    /**
     * Manager进程退出
     * @param IHttp|ISocket $server
     */
    final public function onManagerStop($server)
    {
        $name = $server->genPidName("manager");
        $server->getPidTable()->del($server->getManagerPid());
        $server->getConsole()->warn("[@%d]Manager{%s}Quit", $server->getManagerPid(), $name);
        $this->doManagerStop($server);
    }
}
