<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2;

use Uniondrug\Server2\Interfaces\IServer;
use Uniondrug\Server2\Interfaces\ITask;

/**
 * Task
 * @package Uniondrug\Server2
 */
abstract class Task implements ITask
{
    /**
     * @var IServer
     */
    private $server;

    final public function __construct(IServer $server)
    {
        $this->server = $server;
    }

    /**
     * @return IServer
     */
    public function getServer()
    {
        return $this->server;
    }
}
