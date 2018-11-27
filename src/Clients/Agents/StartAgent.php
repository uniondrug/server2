<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-22
 */
namespace Uniondrug\Server2\Clients\Agents;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;
use Uniondrug\Server2\Clients\Abstracts\Agent;

/**
 * 启动服务
 * @package Uniondrug\Server2\Clients\Agents
 */
class StartAgent extends Agent
{
    public function run()
    {
        $entry = $this->client->builder->getEntrypoint();
        $this->client->console->debug("启动{%s}服务 - {%s}", $this->client->builder->getName(), $entry);
        try {
            /**
             * @var IHttp|ISocket $server
             */
            $server = new $entry($this->client->builder);
            $server->start();
        } catch(\Throwable $e) {
            $this->client->console->error("启动{%s}失败 - {%s}", $this->client->builder->getName(), $e->getMessage());
        }
    }

    public function runHelp()
    {
        $this->printUsage();
        $this->printOptions($this->client->args->getStartOptions());
    }
}

