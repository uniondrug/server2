<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Traits;

use Uniondrug\Server2\Processes\Process;

/**
 * 公共方法
 * @package Uniondrug\Server2\Agent\Traits
 */
trait Task
{
    /**
     * 发送异步任务
     * in{Worker|Tasker|Process}
     * @param string $class
     * @param array  $params
     * @return bool
     */
    final public function runTask(string $class, array $params = [])
    {
        // 1. 消息内容
        $data = ['class' => $class];
        $data['params'] = $params;
        $message = json_encode($data, JSON_UNESCAPED_UNICODE);
        // 2. 在Worker进程中触发
        if (isset($this->worker_pid) && $this->worker_pid > 0) {
            // 2.1 worker:worker
            if (!$this->taskworker) {
                $this->console->debug("[@%d.%d][task=call]发送{%s}任务", $this->getWorkerPid(), $this->getWorkerId(), $class);
                return $this->runTaskInWorker($message);
            }
            // 2.2 worker:tasker
            $this->console->debug("[@%d.%d][task=call]发送{%s}任务", $this->getWorkerPid(), $this->getWorkerId(), $class);
            return $this->runTaskInTasker($message);
        }
        // 3. process
        $pid = isset($this->pid) ? $this->pid : 'n';
        $this->console->debug("[@%s][task=call]发送{%s}任务", $pid, $class);
        return $this->runTaskInTasker($message);
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
