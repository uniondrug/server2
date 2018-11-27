<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Servers\Phalcon;

use Uniondrug\Framework\Application;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Processes\Process;
use Uniondrug\Server2\Servers\Phalcon\Traits\FrameworkTrait;
use Uniondrug\Server2\Servers\Phalcon\Traits\MysqlTrait;
use Uniondrug\Server2\Servers\Phalcon\Traits\RedisTrait;

/**
 * 基于Phalcon的Process基类
 * @package Uniondrug\Server2\Servers\Phalcon
 */
abstract class PhalconProcess extends Process
{
    /**
     * 连接检查频次
     * 单位: 秒
     * 用途: 防止Shared实例出现gone away
     */
    const CONNECTION_RELOAD_FREQUENCE = 15000;
    /**
     * HTTP对象
     * @var PhalconHttp
     */
    public $server;
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
     * 公共方法
     */
    use FrameworkTrait, MysqlTrait, RedisTrait;

    /**
     * 构建Process实例.
     * @param $server
     */
    public function __construct($server)
    {
        parent::__construct($server);
    }

    /**
     * 前设置操作
     * @return bool
     */
    public function beforeRun()
    {
        // 预载入: Phalcon框架支持
        $this->loadFramework($this);
        // 定时器: 检查MySQL/Redis连接
        swoole_timer_tick(self::CONNECTION_RELOAD_FREQUENCE, [
            $this,
            'beforeRunTick'
        ]);
        return true;
    }

    public function beforeRunTick()
    {
        $this->loadMysqlConnection($this->server, $this->container);
        $this->loadRedisConnection($this->server, $this->container);
    }
}
