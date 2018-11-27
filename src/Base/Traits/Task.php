<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Traits;

/**
 * 公共方法
 * @package Uniondrug\Server2\Agent\Traits
 */
trait Task
{
    /**
     * 发送异步任务
     * in{Worker|Tasker}
     * @param string $class
     * @param array  $params
     * @return bool
     */
    final public function runTask(string $class, array $params = [])
    {
        // 1. 消息内容
        $message = json_encode([
            'class' => $class,
            'params' => $params
        ], true);
        // 2. 按进程类型分发
        $this->console->debug("[@%d.%d][task:called]准备{%s}任务", $this->getWorkerPid(), $this->getWorkerId(), $class);
        return $this->isTasker() ? $this->runTaskInTasker($message) : $this->runTaskInWorker($message);
    }

    /**
     * @param string $message
     * @return bool
     */
    private function runTaskInTasker(string $message)
    {
        return $this->sendMessage($message, 0);
    }

    /**
     * @param string $message
     * @return bool
     */
    private function runTaskInWorker(string $message)
    {
        return false !== $this->task($message, -1, null);
    }
}
