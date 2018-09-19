<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Interfaces;

/**
 * ISocket/WebSocket服务器接口
 * @link https://wiki.swoole.com/wiki/page/397.html
 * @package Uniondrug\Server2\Interfaces
 */
interface ISocket extends IServer
{
    /**
     * @return []
     */
    public function getConnections();
    /**
     * 向指定WebSocket连接发消息
     * @param int          $fd
     * @param array|string $data
     * @param bool         $binary
     * @param bool         $finish
     * @return true|string
     */
    public function push($fd, $data, $binary = false, $finish = true);
}
