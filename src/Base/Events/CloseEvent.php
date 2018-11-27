<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * 关闭连接
 * 1. http
 * 2. websocket
 * @package Uniondrug\Server2\Agent\Events
 */
trait CloseEvent
{
    /**
     * 关闭连接
     * @param IHttp|ISocket $server
     * @param int $fd
     * @param int $reactorId
     */
    public function doClose($server, $fd, $reactorId)
    {
    }

    /**
     * 关闭连接
     * @param IHttp|ISocket $server
     * @param int $fd
     * @param int $reactorId
     */
    final public function onClose($server, $fd, $reactorId)
    {
        $this->doClose($server, $fd, $reactorId);
    }
}
