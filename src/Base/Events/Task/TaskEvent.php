<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Events\Task;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;
use Uniondrug\Server2\Tasks\ITask;

/**
 * Task触发
 * @package Uniondrug\Server2\Agent\Events\Task
 */
trait TaskEvent
{
    /**
     * 执行任务
     * @param IHttp|ISocket $server
     * @param string        $data
     * @param int           $taskId
     * @return mixed
     * @throws \Exception
     */
    public function doTask($server, $data, int $taskId)
    {
        // 1. 解析任务
        $data = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }
        // 2. 执行类型
        $tasker = isset($data['class']) && is_string($data['class']) ? $data['class'] : null;
        if (!$tasker) {
            throw new \Exception("未定义任务Handler");
        }
        // 3. 接口检查
        if (!is_a($tasker, ITask::class, true)) {
            throw new \Exception("任务{$tasker}未实现".ITask::class."接口");
        }
        /**
         * 4. 执行过程
         * @var ITask $tasker
         * @var ITask $handler
         */
        $data['params'] = isset($data['params']) && is_array($data['params']) ? $data['params'] : [];
        $handler = new $tasker($server, $data['params'], $taskId);
        if ($handler->beforeRun() !== true) {
            throw new \Exception("任务{$tasker}的前置beforeRun()未返回TRUE");
        }
        $result = $handler->run();
        $handler->afterRun($result);
        return $result;
    }

    /**
     * Task触发
     * @param IHttp|ISocket $server
     * @param int           $taskId
     * @param int           $srcWorkerId
     * @param string        $data
     * @return mixed
     */
    final public function onTask($server, int $taskId, int $srcWorkerId, $data)
    {
        // 1. 收到任务/记数器+1
        $server->getPidTable()->incr($server->getWorkerPid(), 'onTask', 1);
        $server->getConsole()->debug("[@%d.%d][task=%d]事件onTask已触发", $this->getWorkerPid(), $this->getWorkerId(), $taskId);
        try {
            // 2. 执行过程
            $begin = microtime(true);
            $result = $this->doTask($server, $data, $taskId);
            // 3. 记录结果
            if (is_array($result)) {
                $result = json_encode($result, true);
            } else if (is_object($result)) {
                if (method_exists($result, 'toJson')) {
                    $result = $result->toJson();
                } else if (method_exists($result, 'toArray')) {
                    $result = json_encode($result->toArray(), true);
                } else {
                    $result = json_encode($result);
                }
            } else if (!is_string($result)) {
                $result = $result === true ? "true" : "false";
            }
            // 4. 任务完成
            $duration = sprintf("%.06f", microtime(true) - $begin);
            $server->getConsole()->debug("[@%d.%d][task=%d]共用时{%f}秒完成 - %s", $this->getWorkerPid(), $this->getWorkerId(), $taskId, $duration, $result);
        } catch(\Throwable $e) {
            // n. 执行失败
            $result = $e->getMessage();
            $server->getConsole()->error("[@%d.%d][task=%d]任务执行失败 - %s", $this->getWorkerPid(), $this->getWorkerId(), $taskId, $result);
        }
        // m. 完成任务/记数器+1
        $server->getPidTable()->incr($server->getWorkerPid(), 'onFinish', 1);
        return $result;
    }

    /**
     * 向Task池发送异步任务
     * @param array|string $data
     * @param int          $workerId
     * @param callable     $callback
     * @return int|bool
     */
    public function task($data, $workerId = null, $callback = null)
    {
        // 1. 开始投递任务
        $data = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
        /** @noinspection PhpUndefinedMethodInspection */
        $taskId = parent::task($data, $workerId, $callback);
        // 2. 投递失败
        if ($taskId === false) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->console->error("[@%d.%d][task=reject]投递任务到任务池失败 - %s", $this->worker_pid, $this->worker_id, $data);
            return false;
        }
        // 3. 投递成功
        /** @noinspection PhpUndefinedMethodInspection */
        $this->console->debug("[@%d.%d][task=%d]投递到任务池", $this->worker_pid, $this->worker_id, $taskId);
        return $taskId;
    }
}
