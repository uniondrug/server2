<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers;

/**
 * ISocket/WebSocket接口
 * @package Uniondrug\Server2\Servers
 */
interface ISocket extends IHttp
{
    /**
     * @param int $fd
     * @return bool
     */
    public function exist($fd);

    /**
     * @param int  $fd
     * @param      $data
     * @param int  $opcode
     * @param bool $finish
     * @return bool
     */
    public function push($fd, $data, $opcode = null, $finish = null);

    /**
     * @param      $fd
     * @param      $data
     * @param null $reactorId
     * @return mixed
     */
    public function send($fd, $data, $reactorId = null);
}
