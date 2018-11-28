<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base;

use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Tables\PidTable;

interface IHttp
{
    /**
     * 生成进程名称
     * @param string   $name
     * @param int|null $id
     * @return string
     */
    public function genPidName(string $name, int $id = null);

    /**
     * 设置进程名称
     * @param string $name
     * @return IHttp|ISocket
     */
    public function setPidName(string $name);

    /**
     * 读取Builder实例
     * @return Builder
     */
    public function getBuilder();

    /**
     * 读取Console实例
     * @return Console
     */
    public function getConsole();

    /**
     * 读取Manager进程ID
     * @return int
     */
    public function getManagerPid();

    /**
     * 读取主进程ID
     * @return int
     */
    public function getMasterPid();

    /**
     * PID管理器实例
     * @return PidTable
     */
    public function getPidTable();

    /**
     * 读取Worker进程ID(Tasker/Worker)
     * @return int
     */
    public function getWorkerId();

    /**
     * 读取Worker进程ID(Tasker/Worker)
     * @return int
     */
    public function getWorkerPid();

    /**
     * 是否为Tasker进程(Worker进程一种)
     * @return bool
     */
    public function isTasker();

    /**
     * 启到一个Process进程
     * @param string $class
     * @param array  $params
     * @return mixed
     */
    public function runProcess(string $class, array $params = []);

    /**
     * 发送异步Task任务
     * @param string $class
     * @param array  $params
     * @return mixed
     */
    public function runTask(string $class, array $params = []);

    /**
     * 启动Server
     * @return mixed
     */
    public function start();
}
