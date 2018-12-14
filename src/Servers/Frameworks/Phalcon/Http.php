<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Frameworks\Phalcon;

use Phalcon\Http\Response as PhalconResponse;
use Phalcon\Logger\AdapterInterface;
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
    private $application;
    /**
     * Phalcon容器
     * @var Container
     */
    private $container;
    /**
     * 监听HTTP事件
     * @var array
     */
    public $events = [
        'request'
    ];

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
        $code = (int) $pr->getStatusCode();
        $code || $code = 200;
        $response->statusCode = $code;
        $response->header("Content-Type", "application/json");
        $response->status($code);
        $response->end($pr->getContent());
    }

    /**
     * 读取Phalcon应用
     * @return Application
     */
    public function getApplication()
    {
        if ($this->application === null) {
            $this->loadFramework();
        }
        return $this->application;
    }

    /**
     * 读取Phalcon容器
     * @return Container
     */
    public function getContainer()
    {
        if ($this->container === null) {
            $this->loadFramework();
        }
        return $this->container;
    }

    /**
     * @return AdapterInterface
     */
    public function getLogger()
    {
        return $this->getContainer()->getLogger('server');
    }

    /**
     * 载入Framework
     */
    private function loadFramework()
    {
        putenv("APP_ENV={$this->builder->getEnvironment()}");
        $this->console->debug("初始化{%s}环境框架{%s}容器", $this->builder->getEnvironment(), Container::class);
        $this->container = new Container($this->builder->getBasePath());
        $this->container->setShared('server', $this);
        $this->container->setShared('request', new Request());
        $this->application = new Application($this->container);
        $this->application->boot();
        // 4. MySQL/Redis健康检查定时器
        //        $this->startFrameworkMysqlTimer($this);
        //        $this->startFrameworkRedisTimer($this);
    }
}
