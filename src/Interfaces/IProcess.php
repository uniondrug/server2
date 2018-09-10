<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Interfaces;

use Uniondrug\Server2\Exception;

/**
 * IProcess
 * @package Uniondrug\Server2\Interfaces
 */
interface IProcess
{
    /**
     * 传递IProcess配置参数
     * @param array $data
     * @return mixed
     */
    public function configure(array $data);

    /**
     * 读取IProcess参数配置
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function getConfig(string $key);

    /**
     * 读取IServer实例
     * @return IServer
     */
    public function getServer();

    /**
     * 运行IProcess
     * 在回调中自动触, 不需调用
     */
    public function run();
}
