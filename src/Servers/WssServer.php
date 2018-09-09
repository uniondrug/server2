<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Servers;

use swoole_websocket_server;
use Uniondrug\Server2\Servers\Traits\EventsTrait;

/**
 * WebSocket服务
 * @package Uniondrug\Server2\Servers
 */
abstract class WssServer extends swoole_websocket_server
{
    use EventsTrait;

    /**
     * @param string $name
     * @param string $address
     * @param array  $conf
     */
    public static function createServer($name, $address, $conf = [])
    {
    }
}
