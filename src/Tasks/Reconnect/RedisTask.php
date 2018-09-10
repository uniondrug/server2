<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-06
 */
namespace Uniondrug\Server2\Tasks\Reconnect;

use Uniondrug\Server2\Task;

/**
 * Reconnect Redis
 * @package Uniondrug\Server2\Tasks\Reconnect
 */
class RedisTask extends Task
{
    /**
     * run task progress
     * @param array $data
     */
    public function run(array $data)
    {
        $this->getServer()->getConsole()->debug("[刷新连接] 刷新[%s]连接 - %s", "Redis", __CLASS__);
    }
}