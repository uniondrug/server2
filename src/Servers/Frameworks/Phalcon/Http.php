<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Frameworks\Phalcon;

use Phalcon\Db\Adapter;
use Phalcon\Http\CookieInterface;
use Phalcon\Http\Response as PhalconResponse;
use Uniondrug\Server2\Servers\Frameworks\Phalcon\Request as PhalconRequest;
use Phalcon\Logger\AdapterInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Uniondrug\Framework\Application;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Servers\XHttp;
use Uniondrug\Service\Server as ServiceServer;

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
    private $sharedLoggerDate = 0;
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
        $pr = $this->handleRequest($request, $response);
        $code = (int) $pr->getStatusCode();
        $code || $code = 200;
        $response->statusCode = $code;
        $response->header("Content-Type", "application/json");
        $response->status($code);
        $response->end($pr->getContent());
    }

    /**
     * Server主进程启动
     * @param Http $server
     */
    public function doStart($server)
    {
        parent::doStart($server);
        $server->setFramework();
    }

    /**
     * Manager进程启动
     * @param Http $server
     */
    public function doManagerStart($server)
    {
        parent::doManagerStart($server);
        $server->setFramework();
    }

    /**
     * Worker/Tasker进程启动
     * @param Http $server
     */
    public function doWorkerStart($server)
    {
        parent::doWorkerStart($server);
        $server->setFramework();
    }

    /**
     * 读取Phalcon应用
     * @return Application
     * @throws \Exception
     */
    public function getApplication()
    {
        if ($this->application === null) {
            throw new \Exception("Framework未加载");
        }
        return $this->application;
    }

    /**
     * 读取Phalcon容器
     * @return Container
     * @throws \Exception
     */
    public function getContainer()
    {
        if ($this->container === null) {
            throw new \Exception("Framework未加载");
        }
        return $this->container;
    }

    /**
     * 读取Logger实例
     * @return AdapterInterface
     */
    public function getLogger()
    {
        $date = (int) date('Ymd');
        $container = $this->getContainer();
        if ($this->sharedLoggerDate !== $date) {
            $this->sharedLoggerDate = $date;
            if ($container->hasSharedInstance('logger')) {
                $container->removeSharedInstance('logger');
            }
        }
        return $container->getLogger('server');
    }

    /**
     * 用Swoole请求换取Phalcon请求
     * @param SwooleRequest  $swooleRequest
     * @param SwooleResponse $swooleResponse
     * @return PhalconResponse
     */
    public function handleRequest($swooleRequest, $swooleResponse)
    {
        /**
         * 1. initialize
         * @var ServiceServer  $serviceServer ;
         * @var PhalconRequest $phalconRequest
         */
        $serviceServer = $this->container->getShared('serviceServer');
        $phalconRequest = $this->container->getShared('request');
        $phalconRequest->initialize($swooleRequest);
        // 2. dispatch phalcon controller
        try {
            $phalconResponse = $this->application->handle($phalconRequest->getURI());
            if (!($phalconResponse instanceof PhalconResponse)) {
                throw new \Exception("unknown response type");
            }
        } catch(\Throwable $e) {
            $this->console->error("Framework错误 - %s", $e->getMessage());
            return $serviceServer->withError($e->getMessage(), $e->getCode());
        }
        // 3. render cookie
        $cookies = $phalconResponse->getCookies();
        if ($cookies instanceof PhalconResponse\CookiesInterface) {
            /**
             * @var CookieInterface $cookie
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
     * 载入Framework
     * @param bool $mysqlTimer
     * @param bool $redisTimer
     * @return $this
     */
    public function setFramework($mysqlTimer = true, $redisTimer = true)
    {
        // 1. setted already
        if ($this->application !== null && $this->container !== null) {
            return $this;
        }
        // 2. reset framework
        putenv("APP_ENV={$this->builder->getEnvironment()}");
        $this->console->debug("初始化{%s}环境框架{%s}容器", $this->builder->getEnvironment(), Container::class);
        $this->container = new Container($this->builder->getBasePath());
        $this->container->setShared('server', $this);
        $this->container->setShared('request', new Request());
        $this->application = new Application($this->container);
        $this->application->boot();
        // 3. MySQL连接检查
        if ($mysqlTimer === true) {
            $sec = (int) $this->builder->getOption('reconnectMysqlSeconds');
            $sec || $sec = 10;
            $this->tick($sec * 1000, [
                $this,
                'setMysqlConnection'
            ]);
        }
        // 4. Redis连接检查
        if ($redisTimer === true) {
            $sec = (int) $this->builder->getOption('reconnectRedisSeconds');
            $sec || $sec = 10;
            $this->tick($sec * 1000, [
                $this,
                'setRedisConnection'
            ]);
        }
        // 5. 完成
        return $this;
    }

    /**
     * 设置MySQL连接
     * @return $this
     */
    public function setMysqlConnection()
    {
        // 1. framework not loaded
        if ($this->container === null) {
            return $this;
        }
        // 2. shared names
        $names = [
            'db',
            'dbSlave'
        ];
        // 3. each check
        foreach ($names as $name) {
            // 4. not shared
            if (!$this->container->hasSharedInstance($name)) {
                continue;
            }
            // 5. test connection
            try {
                /**
                 * 6. run query
                 * @var Adapter $db
                 */
                $db = $this->container->getShared($name);
                $db->query("SELECT 1");
            } catch(\Throwable $e) {
                // 7. connection failure
                if (preg_match("/gone\s+away/i", $e->getMessage())) {
                    $this->container->removeSharedInstance($name);
                }
                $this->console->error("MySQL连接检查 - %s", $e->getMessage());
            }
        }
        return $this;
    }

    /**
     * 设置Redis连接
     * @return $this
     */
    public function setRedisConnection()
    {
        // 1. framework not loaded
        if ($this->container === null) {
            return $this;
        }
        // 2. shared names
        $names = ['redis'];
        // 3. each check
        foreach ($names as $name) {
            // 4. not shared
            if (!$this->container->hasSharedInstance($name)) {
                continue;
            }
            // 5. test connection
            try {
                /**
                 * 6. run query
                 * @var \Redis $redis
                 */
                $redis = $this->container->getShared($name);
                $redis->exists("test");
            } catch(\Throwable $e) {
                // 7. connection failure
                if (preg_match("/went\s+away/i", $e->getMessage())) {
                    $this->container->removeSharedInstance($name);
                }
                $this->console->error("Redis连接检查 - %s", $e->getMessage());
            }
        }
        return $this;
    }
}
