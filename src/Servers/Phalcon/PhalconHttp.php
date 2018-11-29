<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Servers\Phalcon;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Http\CookieInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Phalcon\Http\Response as PhalconResponse;
use Uniondrug\Framework\Request as PhalconRequest;
use Uniondrug\Framework\Application;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Servers\Http;

/**
 * 基于Phalcon的应用迁移至Swoole默认入口
 * @package Uniondrug\Server2\Servers\Phalcon
 */
class PhalconHttp extends Http
{
    /**
     * 连接检查频次
     * 单位: 秒
     * 用途: 防止Shared实例出现gone away
     */
    const CONNECTION_RELOAD_FREQUENCE = 15000;
    /**
     * Phalcon应用实例
     * @var Application
     */
    private $application;
    /**
     * Phalcon容器实例
     * @var Container
     */
    private $container;
    /**
     * 方法复用
     */
    //    use FrameworkTrait, RedisTrait, MysqlTrait;
    /**
     * 收到HTTP请求
     * @param SwooleRequest  $swooleRequest
     * @param SwooleResponse $swooleResponse
     */
    public function doRequest($swooleRequest, $swooleResponse)
    {
        /**
         * @var string                    $uri
         * @var \Uniondrug\Service\Server $serviceServer
         */
        $uri = $swooleRequest->server['request_uri'];
        // 取消静态资源
        if (preg_match("/\.([a-z]+)$/", $uri) > 0) {
            $swooleResponse->status(404);
            $swooleResponse->end();
            return;
        }
        $requestId = uniqid('sw');
        $requestTime = microtime(true);
        $serviceServer = $this->container->getShared('serviceServer');
        try {
            $this->doRequestBefore($swooleRequest, $requestId);
            $phalconResponse = $this->application->handle($uri);
            if (!($phalconResponse instanceof PhalconResponse)) {
                $phalconResponse = $serviceServer->withError("unknown: response type");
            }
            $this->doRequestAfter($swooleResponse, $phalconResponse);
        } catch(\Throwable $e) {
            $phalconResponse = $serviceServer->withError($e->getMessage(), $e->getCode());
            $this->doRequestAfter($swooleResponse, $phalconResponse);
        }
        $this->doRequestLogger($swooleRequest, $phalconResponse, $requestId, $requestTime);
    }

    /**
     * @param SwooleResponse  $swooleResponse
     * @param PhalconResponse $phalconResponse
     */
    public function doRequestAfter($swooleResponse, $phalconResponse)
    {
        /**
         * 1. cookie
         * @var CookieInterface $cookie
         */
        $cookies = $phalconResponse->getCookies();
        if ($cookies instanceof PhalconResponse\CookiesInterface) {
            foreach ($cookies as $cookie) {
                if ($cookie instanceof CookieInterface) {
                    $swooleResponse->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiration(), $cookie->getPath(), $cookie->getDomain());
                }
            }
        }
        /**
         * 2. header
         * @var PhalconResponse\HeadersInterface $headers
         */
        $headers = $phalconResponse->getHeaders();
        if ($headers instanceof PhalconResponse\HeadersInterface) {
            foreach ($headers as $key => $value) {
                $swooleResponse->header($key, $value);
            }
            $swooleResponse->header("content-type", "application/json");
        }
        // n. status & code
        $swooleResponse->status($phalconResponse->getStatusCode());
        $swooleResponse->end($phalconResponse->getContent());
    }

    /**
     * @param SwooleRequest $swooleRequest
     * @param string        $requestId
     */
    public function doRequestBefore($swooleRequest, string $requestId)
    {
        // 1. 超全局变量
        $_GET = $swooleRequest->get;
        $_POST = $swooleRequest->post;
        $_COOKIE = $swooleRequest->cookie;
        $_FILES = $swooleRequest->files;
        // 2. Heder
        $_SERVER = [];
        foreach ($swooleRequest->server as $key => $value) {
            $_SERVER[strtoupper($key)] = $value;
        }
        $_SERVER['REQUEST_ID'] = $requestId;
        $_SERVER['SERVER_SOFTWARE'] = "UDS/2.0";
        /**
         * @var PhalconRequest $phalconRequest
         */
        $phalconRequest = $this->container->getShared('request');
        $phalconRequest->setRawBody($swooleRequest->rawContent());
    }

    /**
     * @param                  $swooleRequest
     * @param PhalconResponse  $phalconResponse
     * @param string           $requestId
     * @param float            $requestTime
     */
    public function doRequestLogger($swooleRequest, $phalconResponse, string $requestId, float $requestTime)
    {
        $this->console->info(                               // 请求日志
            "[%s][%.06f] %s %d %s %s%s %s",           // 日志格式
            $requestId,                              // 请求ID
            microtime(true) - $requestTime,      // 请求用时
            $swooleRequest->server['server_protocol'],      // 协议与版本
            $phalconResponse->getStatusCode() ?: 200,       // 状态码
            $swooleRequest->server['request_method'],       // 请求方式
            $swooleRequest->header['host'],                 // 域名与端口
            $swooleRequest->server['request_uri'],          // 请求路径
            $swooleRequest->header['user-agent']            // 浏览器
        );
    }

    /**
     * 读取配置
     * @return \Phalcon\Config
     */
    public function getConfig()
    {
        return $this->getContainer()->getConfig();
    }

    /**
     * 读取容器
     * @return Container
     */
    public function getContainer()
    {
        // 1. once
        if ($this->container !== null) {
            return $this->container;
        }
        // 2. create
        if ($this->getWorkerPid() > 0) {
            $this->console->info("[@%d.%d]Container{%s}Initialized", $this->getWorkerPid(), $this->getWorkerId(), Container::class);
        } else {
            $pid = function_exists('posix_getpid') ? posix_getpid() : 0;
            $this->console->info("[@%d]Container{%s}Initialized", $pid, Container::class);
        }
        // 2.1. container
        $this->container = new Container($this->builder->getBasePath());
        $this->application = new Application($this->container);
        $this->application->boot();
        // 2.2. shared
        $this->container->setShared('server', $this);
        // 3.3. connection
        $this->tick(self::CONNECTION_RELOAD_FREQUENCE, [
            $this,
            'reloadConnection'
        ]);
        return $this->container;
    }

    /**
     * 刷新MySQL连接
     */
    public function reloadConnection()
    {
        $dbs = [
            'db',
            'dbSlave'
        ];
        foreach ($dbs as $db) {
            /**
             * MySQL
             * @var Mysql
             */
            $rdb = $this->container->getShared($db);
            try {
                $rdb->query("SELECT 1");
            } catch(\Exception $e) {
                $this->container->removeSharedInstance($db);
                $this->console->warn("Fresh{MySQL}Connection - %s", $e->getMessage());
            }
        }
    }
}
