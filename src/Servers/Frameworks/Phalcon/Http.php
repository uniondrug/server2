<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Frameworks\Phalcon;

use Phalcon\Http\Response;
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
     * MySQL刷新时长
     * @var int
     */
    public $refreshMysqlSeconds = 5;
    /**
     * Redis刷新时长
     * @var int
     */
    public $refreshRedisSeconds = 5;
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
        try {
            /**
             * @var Response $resp
             */
            $resp = $this->handleRequest($this, $request, $response);
            if ($resp instanceof Response) {
                $response->statusCode = $resp->getStatusCode();
                $response->status($resp->getStatusCode());
                $response->end($resp->getContent());
                return;
            }
            throw new \Exception("unknown response");
        } catch(\Throwable $e) {
            $response->statusCode = 500;
            $response->status($response->statusCode);
            $response->end($e->getMessage());
        }
    }

    /**
     * Worker进程启动时
     * 初始化Phalcon框架
     * @param Http $server
     */
    public function doWorkerStart($server)
    {
        $this->startFramework($this);
    }
}
