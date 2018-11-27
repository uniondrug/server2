<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-22
 */
namespace Uniondrug\Server2\Clients\Agents;

use Uniondrug\Server2\Clients\Abstracts\Agent;

/**
 * 刷新服务
 * @package Uniondrug\Server2\Clients\Agents
 */
class ReloadAgent extends Agent
{
    public function run()
    {
        $this->client->console->info("sending reload request to {%s}.", $this->client->builder->getName());
        $data = $this->request("PUT", "reload");
        if ($data === false) {
            return;
        }
        $this->printStats($data);
    }

    public function runHelp()
    {
        $this->printUsage();
        $this->printOptions([]);
    }
}

