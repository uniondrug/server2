<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Frameworks\Phalcon;

use Uniondrug\Server2\Processes\XProcess;

/**
 * Phalcon模式下的HTTP请求
 * @package Uniondrug\Server2\Servers
 */
abstract class Process extends XProcess
{
    /**
     * @var Http
     */
    protected $server;

    public function beforeRun()
    {
        $this->server->startFramework($this->server);
        parent::beforeRun();
    }
}
