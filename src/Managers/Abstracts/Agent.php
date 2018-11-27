<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-22
 */
namespace Uniondrug\Server2\Managers\Abstracts;

use Uniondrug\Server2\Servers\Http;

/**
 * 服务管理基类
 * @package Uniondrug\Server2\Managers\Abstracts
 */
abstract class Agent
{
    /**
     * @var Http
     */
    protected $server;

    /**
     * Agent constructor.
     * @param Http $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }

    abstract public function run();
}
