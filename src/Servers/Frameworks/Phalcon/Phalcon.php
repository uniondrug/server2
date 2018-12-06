<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-06
 */
namespace Uniondrug\Server2\Servers\Frameworks\Phalcon;

use Phalcon\Http\CookieInterface;
use Phalcon\Http\Response;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Uniondrug\Framework\Application;
use Uniondrug\Framework\Container;
use Uniondrug\Framework\Request as PhalconRequest;
use Uniondrug\Service\Server;

/**
 * 基于Phalcon的复用
 * @package Uniondrug\Server2\Servers\Frameworks\Phalcon
 */
trait Phalcon
{
    /**
     * 收到HTTP请求
     * Server收到Http请求时, 转发给
     * @param Http           $server
     * @param SwooleRequest  $swooleRequest
     * @param SwooleResponse $swooleResponse
     * @return Response
     * @throws \Exception
     */
    public function handleRequest($server, $swooleRequest, $swooleResponse)
    {
        // 1. 未导入Phalcon框架
        if (!($server->container instanceof Container)) {
            throw new \Exception("framework not initialized");
        }
        // 2. $service
        $service = $server->container->getShared('serviceServer');
        if (!($service instanceof Server)) {
            throw new \Exception("framework initialized failure");
        }
        // 3. assets
        $uri = $swooleRequest->server['request_uri'];
        if (preg_match("/\.[a-zA-Z0-9]+/", $uri)) {
            return $service->withError("Forbidden: assets resources ignored");
        }
        // 4. init global variables
        $_GET = $swooleRequest->get;
        $_POST = $swooleRequest->post;
        $_COOKIE = $swooleRequest->cookie;
        $_FILES = $swooleRequest->files;
        $_SERVER = [];
        foreach ($swooleRequest->server as $key => $value) {
            $_SERVER[strtoupper($key)] = $value;
        }
        $_SERVER['REQUEST_ID'] = $swooleRequest->requestId;
        $_SERVER['SERVER_SOFTWARE'] = $server->builder->getAppName()."/".$server->builder->getAppVersion();
        /**
         * 5. reset phalcon request
         * @var PhalconRequest $phalconRequest
         */
        $phalconRequest = $server->container->getShared('request');
        $phalconRequest->setRawBody($swooleRequest->rawContent());
        // 6. execute request
        $phalconResponse = $server->application->handle($uri);
        if ($phalconResponse instanceof Response) {
            /**
             * 7. cookie
             * @var CookieInterface $cookie
             */
            $cookies = $phalconResponse->getCookies();
            if ($cookies instanceof CookieInterface) {
                foreach ($cookies as $cookie) {
                    if ($cookie instanceof CookieInterface) {
                        $swooleResponse->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiration(), $cookie->getPath(), $cookie->getDomain());
                    }
                }
            }
            /**
             * 8. header
             * @var Response\HeadersInterface $headers
             */
            $headers = $phalconResponse->getHeaders();
            if ($headers instanceof Response\HeadersInterface) {
                foreach ($headers as $key => $value) {
                    $swooleResponse->header($key, $value);
                }
            }
            // 9. transfer headers
            return $phalconResponse;
        }
        return $service->withError("unknown response");
    }

    /**
     * 刷新MySQL连接
     * @param Http $server
     */
    public function refreshMysqlConnection($server)
    {
        // 1. 容器未加载
        if (!($server->container instanceof Container)) {
            $server->console->warning("框架{%s}未加载,跳过DB连接刷新", Container::class);
            return;
        }
        // 2. 主从DB注入名称
        $names = [
            'db',
            'dbSlave'
        ];
        $rexps = [
            "/gone\s+away/"
        ];
        /**
         * 3. 依次检查
         * @var Mysql $db
         */
        foreach ($names as $name) {
            // 3.1 未注入或从未使用过
            if (!$server->container->hasSharedInstance($name)) {
                continue;
            }
            // 3.2 识别连接
            try {
                // 3.2.1 读取连接
                $db = $server->container->getShared($name);
                $db->query("SELECT 1");
            } catch(\Throwable $e) {
                foreach ($rexps as $rexp) {
                    if (preg_match($rexp, $e->getMessage())) {
                        $server->container->removeSharedInstance($name);
                        $server->console->warning("移除{%s}环境下已断开的{%s}的MySQL连接 - %s", $server->builder->getEnvironment(), $name, $e->getMessage());
                        break;
                    }
                }
            }
        }
    }

    /**
     * 刷新Redis连接
     * @param Http $server
     */
    public function refreshRedisConnection($server)
    {
    }

    /**
     * 载入Phalcon支持
     * @param Http $server
     */
    public function startFramework($server)
    {
        // 1. 已启动
        if ($server->container !== null && $server->application !== null) {
            return;
        }
        // 2. 初始化Framework
        $server->getConsole()->error("初始化框架{%s}容器", Container::class);
        putenv("APP_ENV={$server->builder->getEnvironment()}");
        $server->container = new Container($server->builder->getBasePath());
        $server->application = new Application($server->container);
        $server->application->boot();
        $server->container->setShared('server', $server);
        // 3. 定时刷新MySQL连接/防止MysqlGoneAway
        $server->tick($server->refreshMysqlSeconds * 1000, function() use ($server){
            $server->refreshMysqlConnection($server);
        });
        // 4. 定时刷新Redis连接/防止RedisGoneAway
        $server->tick($server->refreshRedisSeconds * 1000, function() use ($server){
            $server->refreshRedisConnection($server);
        });
    }
}
