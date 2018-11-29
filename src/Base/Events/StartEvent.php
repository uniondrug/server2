<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Server启动后触发
 * @package Uniondrug\Server2\Base\Events
 */
trait StartEvent
{
    /**
     * Master进程启动
     * @param IHttp|ISocket $server
     */
    public function doStart($server)
    {
    }

    /**
     * Master进程启动
     * @param IHttp|ISocket $server
     */
    final public function onStart($server)
    {
        $name = $server->genPidName("master");
        $server->setPidName($name);
        $server->getPidTable()->addMaster($server->getMasterPid(), $name);
        $server->getConsole()->info("[@%d]Master{%s}Started", $server->getMasterPid(), $name);
        $this->doStart($server);
    }
}
