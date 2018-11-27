<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Clients;

use Uniondrug\Server2\Args;
use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;

/**
 * 连接服务端入口
 * @package Uniondrug\Service\Clients
 */
class Client
{
    /**
     * 命令行入参对象
     * @var Args
     */
    public $args;
    /**
     * 服务参数对象
     * @var Builder
     */
    public $builder;
    /**
     * 控制台对象
     * @var Console
     */
    public $console;

    /**
     * @param Builder $builder
     * @param Args    $args
     */
    public function __construct(Builder $builder, Args $args)
    {
        // 1. base
        $this->args = $args;
        $this->builder = $builder;
        $this->console = new Console();
        // 2. reset host and port
        $host = $this->args->getHost();
        $host !== false && $this->builder->setHost($host);
        $port = $this->args->getPort();
        $port !== false && $this->builder->setPort($port);
        // 3. with daemon or not
        $this->args->getDaemon() && $this->builder->setDaemon(true);
    }

    /**
     * 运行Client
     */
    public function run()
    {
        /**
         * @var IAgent $agent
         */
        try {
            $class = '\\Uniondrug\\Server2\\Clients\\Agents\\'.ucfirst($this->args->getCommand()).'Agent';
            $agent = new $class($this);
        } catch(\Throwable $e) {
            $this->console->error("unknown '%s' command", $this->args->getCommand());
            return;
        }
        try {
            $this->args->hasOption('help') ? $agent->runHelp() : $agent->run();
        } catch(\Throwable $e) {
            $this->console->error("run %s command failure - %s", $this->args->getCommand(), $e->getMessage());
        }
    }
}
