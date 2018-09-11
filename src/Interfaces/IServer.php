<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Interfaces;

use Uniondrug\Framework\Container;
use Uniondrug\Server2\Console;

/**
 * IServer/HTTP服务接口
 * @link    https://wiki.swoole.com/wiki/page/p-server.html
 * @package Uniondrug\Server2\Interfaces
 */
interface IServer
{
    /**
     * 读取应用名称
     * @return string
     */
    public function getAppName();

    /**
     * 读取服务地址
     * @return string
     */
    public function getAddress();

    /**
     * 读取控制台实例
     * @return Console
     */
    public function getConsole();

    /**
     * 读取Phalcon容器
     * @return Container
     */
    public function getContainer();

    /**
     * 读取Master进程PID
     * @return int
     */
    public function getMasterPid();

    /**
     * 读取Manager进程PID
     * @return int
     */
    public function getManagerPid();

    /**
     * 读取Worker进程ID
     * @return int
     */
    public function getWorkerId();

    /**
     * 读取Worker进程PID
     * @return int
     */
    public function getWorkerPid();

    /**
     * 设置进程名称
     * @param string $name
     * @return $this
     */
    public function setProcessName(string $name);

    /**
     * 运行Process进程
     * @param string $class
     * @param array  $params
     * @return int|false
     */
    public function runProcess(string $class, array $params = []);

    /**
     * 投递任务
     * @param string $class
     * @param array  $params
     * @return mixed
     */
    public function runTask(string $class, array $params = []);

    /**
     * @param      $data
     * @param int  $dstWorkerId
     * @param null $callback
     * @return mixed
     */
    public function task($data, $dstWorkerId = -1, $callback = null);

    /**
     * 设置定时器
     * @param int      $ms       毫秒
     * @param callable $callback 回调
     * @return mixed
     */
    public function tick($ms, $callback);
}
