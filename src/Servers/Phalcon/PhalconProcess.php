<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Servers\Phalcon;

use Uniondrug\Server2\Processes\Process;

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
     * @return PhalconHttp
     */
    public function getServer()
    {
        return $this->server;
    }
}
