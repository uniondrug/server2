<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Calls;

use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Tables\ITable;
use Uniondrug\Server2\Tables\PidTable;

trait CommonTrait
{
    /**
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return Console
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * Master进程ID
     * @return int
     */
    public function getMasterPid()
    {
        return $this->master_pid;
    }

    /**
     * Manager进程ID
     * @return int
     */
    public function getManagerPid()
    {
        return $this->manager_pid;
    }

    /**
     * 读取内存表实例
     * @param string $name
     * @return false|ITable
     */
    public function getTable(string $name)
    {
        if (isset($this->initedTables[$name])) {
            return $this->initedTables[$name];
        }
        return false;
    }

    /**
     * 读取全部内存表
     * @return array
     */
    public function getTables()
    {
        return $this->initedTables;
    }

    /**
     * 读取PID进程表
     * @return PidTable|false
     */
    public function getPidTable()
    {
        return $this->getTable('pidTable');
    }

    /**
     * Worker进程ID
     * @return int
     */
    public function getWorkerId()
    {
        return $this->worker_id;
    }

    /**
     * Worker进程ID
     * @return int
     */
    public function getWorkerPid()
    {
        return $this->worker_pid;
    }

    /**
     * 发起异步任务
     * @param string $class
     * @param array  $params
     * @return bool
     */
    public function runTask(string $class, array $params = [])
    {
        // 1. task message
        $data = json_encode([
            'class' => $class,
            'params' => $params
        ], JSON_UNESCAPED_UNICODE);
        $charset = "UTF-8";
        $length = mb_strlen($data, $charset);
        $maxLength = 150;
        if ($length > $maxLength) {
            $this->getConsole()->debug("调用了{%s}方法 - %s", "runTask", mb_substr($data, 0, $maxLength, $charset).'...');
        } else {
            $this->getConsole()->debug("调用了{%s}方法 - %s", "runTask", $data);
        }
        // 2. in worker
        if ($this->worker_pid > 0) {
            if (!$this->taskworker) {
                return $this->runTaskInWorker($data);
            }
        }
        return $this->runTaskNotWorker($data);
    }

    /**
     * 在Worker进程中发起异步任务
     * @param string $data
     * @return bool
     */
    private function runTaskInWorker(string & $data)
    {
        try {
            $taskId = $this->task($data, -1);
            if ($taskId === false) {
                throw new \Exception("return false for task()");
            }
            $this->getConsole()->debug("[task=%d]Worker触发Task", $taskId);
            return true;
        } catch(\Throwable $e) {
            $this->getConsole()->error("Worker触发Task失败 - %s", $e->getMessage());
            return $this->runTaskNotWorker($data);
        }
    }

    /**
     * 在Task/Process等进程发起异步任务
     * @param string $data
     * @return bool
     */
    private function runTaskNotWorker(string & $data)
    {
        $this->getConsole()->debug("PIPE转发TASK");
        return $this->sendMessage($data, 0);
    }

    /**
     * 设置定时器
     * @param int      $ms       毫秒数
     * @param callable $callable 回调方法
     */
    final public function tick($ms, $callable)
    {
        $text = sprintf("设置{%d}秒定时器 - %s", sprintf("%.02f", $ms / 1000), gettype($callable));
        $this->getConsole()->debug($text);
        parent::tick($ms, $callable);
    }
}
