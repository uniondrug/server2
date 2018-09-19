<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2;

use swoole_process;
use Uniondrug\Server2\Interfaces\IProcess;
use Uniondrug\Server2\Interfaces\IServer;
use Uniondrug\Server2\Interfaces\ISocket;

/**
 * Process基类
 * @package Uniondrug\Server2
 */
abstract class Process extends swoole_process implements IProcess
{
    /**
     * 配置项
     * @var array
     */
    private $confirations = [];
    /**
     * 进程名称
     * @var string
     */
    protected $processName;

    /**
     * Process构造
     * @param string $name 进程名称
     */
    final public function __construct(string $name)
    {
        $callback = [
            $this,
            'run'
        ];
        parent::__construct($callback);
        $this->processName = $name;
    }

    /**
     * 在Process进程中, 设置其配置参数
     * @param array $data KV键值对
     * @return mixed
     * @throws Exception
     */
    public function configure(array $data)
    {
        if (is_array($data)) {
            $this->confirations = $data;
            return $this;
        }
        throw new Exception("[".get_class($this)."]配置必须为数组");
    }

    /**
     * 从配置项中读取指定配置
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function getConfig(string $key)
    {
        if (isset($this->confirations[$key])) {
            return $this->confirations[$key];
        }
        throw new Exception("[".get_class($this)."]未定义[{$key}]配置选项");
    }

    /**
     * 读取共享的Server实例
     * @return IServer|ISocket
     */
    public function getServer()
    {
        return $this->getConfig('server');
    }

    /**
     * 设置配置项
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setConfig(string $key, $value)
    {
        $this->confirations[$key] = $value;
        return $this;
    }
}
