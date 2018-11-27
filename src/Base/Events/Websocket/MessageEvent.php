<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events\Websocket;

use Swoole\WebSocket\Frame as SwooleFrame;
use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * Websocket收到消息
 * @package Uniondrug\Server2\Agent\Events
 */
trait MessageEvent
{
    /**
     * 收到Socket客户端消息
     * @param IHttp|ISocket $server
     * @param SwooleFrame $frame
     */
    public function doMessage($server, $frame)
    {
    }

    /**
     * Websocket收到消息
     * @param IHttp|ISocket $server
     * @param SwooleFrame $frame
     */
    final public function onMessage($server, $frame)
    {
        $this->doMessage($server, $frame);
    }
}
