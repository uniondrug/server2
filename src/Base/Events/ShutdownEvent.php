<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Server退出后触发
 * @package Uniondrug\Server2\Base\Events
 */
trait ShutdownEvent
{
    /**
     * Master进程退出
     * @param IHttp|ISocket $server
     */
    public function doShutdown($server)
    {
    }

    /**
     * Master进程退出
     * @param IHttp|ISocket $server
     */
    final public function onShutdown($server)
    {
        $name = $server->genPidName("master");
        $server->getPidTable()->del($server->getMasterPid());
        $server->getConsole()->warn("[@%d]Master{%s}Quit", $server->getMasterPid(), $name);
        $this->doShutdown($server);
    }
}
