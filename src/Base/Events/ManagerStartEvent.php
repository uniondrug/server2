<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Manager进程启动
 * @package Uniondrug\Server2\Base\Events
 */
trait ManagerStartEvent
{
    /**
     * Manager进程启动
     * @param IHttp|ISocket $server
     */
    public function doManagerStart($server)
    {
    }

    /**
     * Manager进程启动
     * @param IHttp|ISocket $server
     */
    final public function onManagerStart($server)
    {
        $name = $server->genPidName("manager");
        $server->setPidName($name);
        $server->getPidTable()->addManager($server->getManagerPid(), $name);
        $server->getConsole()->info("[@%d]Manager{%s}Started", $server->getManagerPid(), $name);
        $this->doManagerStart($server);
    }
}
