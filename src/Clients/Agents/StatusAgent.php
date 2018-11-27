<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-22
 */
namespace Uniondrug\Server2\Clients\Agents;

use Uniondrug\Server2\Clients\Abstracts\Agent;

/**
 * 列出服务状态
 * @package Uniondrug\Server2\Clients\Agents
 */
class StatusAgent extends Agent
{
    public function run()
    {
        $this->client->console->info("send status request to {%s} service.", $this->client->builder->getName());
        $data = $this->request("PUT", "status");
        // 1. contents has or not
        if ($data === false) {
            return;
        }
        // 2. stats
        if (isset($data['stats']) && is_array($data['stats'])) {
            $this->printStats($data['stats']);
        }
        // 3. process
        if (isset($data['process']) && is_array($data['process'])) {
            $this->printProcess($data['process']);
        }
    }

    /**
     * 打印帮助
     */
    public function runHelp()
    {
        $options = $this->client->args->getStatusOptions();
        $this->printUsage();
        $this->printOptions($options);
    }
}

