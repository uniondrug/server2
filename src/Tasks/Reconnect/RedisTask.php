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
        $this->getServer()->getConsole()->debug("[task:run][%s] 刷新%s连接", __CLASS__,  "Redis");
    }
}
