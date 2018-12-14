<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-06
 */
namespace Uniondrug\Server2\Servers\Frameworks\Phalcon;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Http\CookieInterface as PhalconCookie;
use Uniondrug\Framework\Application;
use Uniondrug\Framework\Container;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Phalcon\Http\Response as PhalconResponse;
use Uniondrug\Server2\Servers\Frameworks\Phalcon\Request as PhalconRequest;
use Uniondrug\Service\Server as ServiceServer;

/**
 * 基于Phalcon的复用
 * @package Uniondrug\Server2\Servers\Frameworks\Phalcon
 */
trait Phalcon
{
    /**
     * @param Http           $server
     * @param SwooleRequest  $swooleRequest
     * @param SwooleResponse $swooleResponse
     * @return PhalconResponse
     */
    public function handleRequest($server, $swooleRequest, $swooleResponse)
    {
        /**
         * 1. initialize
         * @var ServiceServer  $serviceServer ;
         * @var PhalconRequest $phalconRequest
         */
        $serviceServer = $server->container->getShared('serviceServer');
        $phalconRequest = $server->container->getShared('request');
        $phalconRequest->initialize($swooleRequest);
        // 2. dispatch phalcon controller
        try {
            $phalconResponse = $server->application->handle($phalconRequest->getURI());
            if (!($phalconResponse instanceof PhalconResponse)) {
                throw new \Exception("unknown response type");
            }
        } catch(\Throwable $e) {
            return $serviceServer->withError($e->getMessage(), $e->getCode());
        }
        // 3. render cookie
        $cookies = $phalconResponse->getCookies();
        if ($cookies instanceof PhalconResponse\CookiesInterface) {
            /**
             * @var PhalconCookie $cookie
             */
            foreach ($cookies as $cookie) {
                $swooleResponse->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiration(), $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure());
            }
        }
        // 4. render header
        $headers = $phalconResponse->getHeaders();
        if ($headers instanceof PhalconResponse\HeadersInterface) {
            foreach ($headers as $key => $value) {
                $swooleResponse->header($key, $value);
            }
        }
        // 5. completed
        return $phalconResponse;
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
        $server->console->debug("初始化{%s}环境框架{%s}容器", $server->builder->getEnvironment(), Container::class);
        putenv("APP_ENV={$server->builder->getEnvironment()}");
        $server->container = new Container($server->builder->getBasePath());
        $server->application = new Application($server->container);
        $server->application->boot();
        $server->container->setShared('server', $server);
        // 3. 覆盖Request
        $server->container->setShared('request', new Request());
        // 4. MySQL/Redis健康检查定时器
        $this->startFrameworkMysqlTimer($server);
        $this->startFrameworkRedisTimer($server);
    }

    /**
     * 检查MySQL连接
     * @param Http $server
     */
    private function startFrameworkMysqlCheck($server)
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
     * 设置MySQL定时刷新
     * @param Http $server
     */
    private function startFrameworkMysqlTimer($server)
    {
        $sec = (int) $server->builder->getOption('reconnectMysqlSeconds');
        $sec || $sec = 10;
        $server->tick($sec * 1000, function() use ($server){
            $this->startFrameworkMysqlCheck($server);
        });
    }

    /**
     * 检查Redis连接
     * @param Http $server
     */
    private function startFrameworkRedisCheck($server)
    {
    }

    /**
     * 设置Redis定时刷新
     * @param Http $server
     */
    private function startFrameworkRedisTimer($server)
    {
        $sec = (int) $server->builder->getOption('reconnectRedisSeconds');
        $sec || $sec = 10;
        $server->tick($sec * 1000, function() use ($server){
            $this->startFrameworkRedisCheck($server);
        });
    }
}
