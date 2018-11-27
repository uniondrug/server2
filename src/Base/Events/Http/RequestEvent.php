<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events\Http;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Uniondrug\Server2\Managers\Manager;

/**
 * HTTP请求
 * 1. HTTP
 * 2. WebSocket
 * @package Uniondrug\Server2\Agent\Events\Http
 */
trait RequestEvent
{
    /**
     * @var Manager
     */
    private static $managerInstance;

    /**
     * HTTP请求
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     */
    public function doRequest($request, $response)
    {
    }

    /**
     * 触发管理请求
     * @param $request
     * @param $response
     */
    public function doRequestManager($request, $response)
    {
        if (self::$managerInstance === null) {
            self::$managerInstance = new Manager($this);
        }
        self::$managerInstance->run($request, $response);
    }

    /**
     * 收到HTTP请求
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     */
    final public function onRequest($request, $response)
    {
        if (isset($request->header, $request->header['host']) && $this->builder->isManagerAddress($request->header['host'])) {
            $this->doRequestManager($request, $response);
        } else {
            $this->doRequest($request, $response);
        }
    }
}
