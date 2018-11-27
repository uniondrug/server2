<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events\Websocket;

use Swoole\Http\Request as SwooleRequest;
use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * WebSocket打开连接
 * @package Uniondrug\Server2\Agent\Events
 */
trait OpenEvent
{
    /**
     * WebSocket打开连接
     * @param IHttp|ISocket $server
     * @param SwooleRequest $request
     */
    public function doOpen($server, $request)
    {
    }

    /**
     * WebSocket打开连接
     * @param IHttp|ISocket $server
     * @param SwooleRequest $request
     */
    final public function onOpen($server, $request)
    {
        $this->doOpen($server, $request);
    }
}
