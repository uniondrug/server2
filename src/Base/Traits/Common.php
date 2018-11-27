<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Traits;

use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Tables\PidTable;

/**
 * 公共方法
 * @package Uniondrug\Server2\Base\Traits
 */
trait Common
{
    /**
     * 生成进程名称
     * @param string   $name
     * @param int|null $id
     * @return string
     */
    public function genPidName(string $name, int $id = null)
    {
        /**
         * @var Builder $builder
         */
        $builder = $this->builder;
        $name = sprintf("%s.%s", $builder->getName(), $name);
        $id === null || $name = sprintf("%s.%s", $name, $id);
        return $name;
    }

    /**
     * 设置进程名称
     * @param string $name
     * @return $this
     */
    public function setPidName(string $name)
    {
        process_rename($name);
        return $this;
    }

    /**
     * 读取服务启动入参
     * @return Builder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * 读取Consol对象
     * @return Console
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * 读取Manager进程ID
     * @return int
     */
    public function getManagerPid()
    {
        return $this->manager_pid;
    }

    /**
     * 读取主进程ID
     * @return int
     */
    public function getMasterPid()
    {
        return $this->master_pid;
    }

    /**
     * 读取Pid管理实例
     * @return PidTable
     */
    public function getPidTable()
    {
        return $this->pidTable;
    }

    /**
     * 读取Worker ID
     * @return mixed
     */
    public function getWorkerId()
    {
        return $this->worker_id;
    }

    /**
     * 读取Worker进程ID
     * @return int
     */
    public function getWorkerPid()
    {
        return $this->worker_pid;
    }

    /**
     * 是否为Tasker(Worker进程一种)
     * @return bool
     */
    public function isTasker()
    {
        return $this->taskworker;
    }
}
