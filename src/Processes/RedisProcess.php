<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Processes;

use Uniondrug\Server2\Process;
use Uniondrug\Server2\Tasks\Reconnect\RedisTask;

/**
 * Redis刷新连接
 * @package Uniondrug\Server2\Processes
 */
class RedisProcess extends Process
{
    private $reconnectSeconds = 5;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->getServer()->setProcessName($this->processName.' '.__CLASS__);
        $seconds = (int) $this->getServer()->getContainer()->getConfig()->path('server.settings.reconnectRedisSeconds', 0);
        $seconds > 0 || $seconds = $this->reconnectSeconds;
        $this->getServer()->tick(1000 * $seconds, [
            $this,
            'reconnect'
        ]);
    }

    public function reconnect()
    {
        $this->getServer()->runTask(RedisTask::class);
    }
}
