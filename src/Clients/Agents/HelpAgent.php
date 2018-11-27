<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-22
 */
namespace Uniondrug\Server2\Clients\Agents;

use Uniondrug\Server2\Clients\Abstracts\Agent;

/**
 * 命令行帮助信息
 * @package Uniondrug\Server2\Clients\Agents
 */
class HelpAgent extends Agent
{
    public function run()
    {
        $this->runHelp();
    }

    public function runHelp()
    {
        $this->printUsage(false, false);
        $this->printCommands($this->client->args->getAllowCommands());
    }
}

