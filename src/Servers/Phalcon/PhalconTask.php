<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Servers\Phalcon;

use Uniondrug\Server2\Tasks\Task;

/**
 * 基于Phalcon的Task基类
 * @package Uniondrug\Server2\Servers\Phalcon
 */
abstract class PhalconTask extends Task
{
    /**
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