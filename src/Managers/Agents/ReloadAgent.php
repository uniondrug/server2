<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-22
 */
namespace Uniondrug\Server2\Managers\Agents;

use Uniondrug\Server2\Managers\Abstracts\Agent;

/**
 * ReloadAgent
 * @package Uniondrug\Server2\Managers\Agents
 */
class ReloadAgent extends Agent
{
    public function run()
    {
        $this->server->reload();
        return $this->server->stats();
    }
}
