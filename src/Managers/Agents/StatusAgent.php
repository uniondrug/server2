<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-22
 */
namespace Uniondrug\Server2\Managers\Agents;

use Uniondrug\Server2\Managers\Abstracts\Agent;

/**
 * StatusAgent
 * @package Uniondrug\Server2\Managers\Agents
 */
class StatusAgent extends Agent
{
    public function run()
    {
        $result = ['stats' => $this->server->stats()];
        $result['stats']['workerId'] = $this->server->getWorkerId();
        $result['stats']['workerPid'] = $this->server->getWorkerPid();
        $result['process'] = $this->server->getPidTable()->toArray();
        return $result;
    }
}
