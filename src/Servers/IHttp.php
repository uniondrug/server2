<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers;

use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Tables\ITable;
use Uniondrug\Server2\Tables\PidTable;

/**
 * IHttp/HTTP接口
 * @package Uniondrug\Server2\Servers
 */
interface IHttp
{
    /**
     * @param string   $name
     * @param int|null $id
     * @return string
     */
    public function genPidName(string $name, int $id = null);

    /**
     * @return Console
     */
    public function getConsole();

    /**
     * @return Builder
     */
    public function getBuilder();

    /**
     * @param string $name
     * @return ITable|false
     */
    public function getTable(string $name);

    /**
     * @return PidTable
     */
    public function getPidTable();

    /**
     * @param string $class
     * @param array  $params
     * @return mixed
     */
    public function runTask(string $class, array $params = []);

    /**
     * 设置进程名称
     * @param string   $name
     * @param int|null $id
     * @return mixed
     */
    public function setPidName(string $name, int $id = null);

    /**
     * 启动服务
     * @return bool
     */
    public function start();

    /**
     * 发起Task
     * @param      $data
     * @param null $workerId
     * @param null $callback
     * @return mixed
     */
    public function task($data, $workerId = null, $callback = null);

    public function tick($ms, $call);
}
