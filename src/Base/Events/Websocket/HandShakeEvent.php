<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events\Websocket;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * Websocket握手
 * @package Uniondrug\Server2\Agent\Events
 */
trait HandShakeEvent
{
    /**
     * Websocket握手
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     * @return bool
     */
    public function doHandShake($request, $response)
    {
        return true;
    }

    /**
     * Websocket握手
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     * @return bool
     */
    final public function onHandShake($request, $response)
    {
        return $this->doHandShake($request, $response);
    }
}
