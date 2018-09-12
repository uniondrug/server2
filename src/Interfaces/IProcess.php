<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Interfaces;

use Uniondrug\Server2\Exception;

/**
 * IProcess
 * @link    https://wiki.swoole.com/wiki/page/p-process.html
 * @package Uniondrug\Server2\Interfaces
 */
interface IProcess
{
    /**
     * 在Process进程中, 设置其配置参数
     * @param array $data KV键值对
     * @return mixed
     * @throws Exception
     */
    public function configure(array $data);

    /**
     * 从配置项中读取指定配置
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function getConfig(string $key);

    /**
     * 读取共享的Server实例
     * @return IServer|ISocket
     */
    public function getServer();

    /**
     * 运行Process进程
     */
    public function run();

    /**
     * 设置配置项
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setConfig(string $key, $value);
}
