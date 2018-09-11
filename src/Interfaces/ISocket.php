<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Interfaces;

/**
 * ISocket
 * @package Uniondrug\Server2\Interfaces
 */
interface ISocket extends IServer
{
    /**
     * 向指定WebSocket连接发消息
     * @param int          $fd
     * @param array|string $data
     * @param bool         $binary
     * @param bool         $finish
     * @return bool
     */
    public function push($fd, $data, $binary = false, $finish = true);
}
