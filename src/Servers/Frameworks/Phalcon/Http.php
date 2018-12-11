<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Frameworks\Phalcon;

use Phalcon\Http\Response as PhalconResponse;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Uniondrug\Framework\Application;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Servers\XHttp;

/**
 * Phalcon模式下的HTTP请求
 * @package Uniondrug\Server2\Servers
 */
class Http extends XHttp
{
    /**
     * Phalcon应用
     * @var Application
     */
    public $application;
    /**
     * Phalcon容器
     * @var Container
     */
    public $container;
    /**
     * 监听HTTP事件
     * @var array
     */
    public $events = [
        'request'
    ];
    /**
     * 复用
     */
    use Phalcon;

    /**
     * 收到HTTP请求
     * Server收到Http请求时, 转发给
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     */
    public function doRequest($request, $response)
    {
        /**
         * @var PhalconResponse $pr
         */
        $pr = $this->handleRequest($this, $request, $response);
        $response->statusCode = $pr->getStatusCode();
        $response->statusCode || $response->statusCode = 200;
        $response->header("Content-Type", "application/json");
        $response->status($response->statusCode);
        $response->end($pr->getContent());
    }

    /**
     * Worker进程启动时
     * 初始化Phalcon框架
     * @param Http $server
     */
    public function doWorkerStart($server)
    {
        $this->startFramework($server);
    }
}
