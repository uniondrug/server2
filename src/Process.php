<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2;

use swoole_process;
use Uniondrug\Server2\Interfaces\IProcess;
use Uniondrug\Server2\Interfaces\IServer;

/**
 * Process
 * @package Uniondrug\Server2
 */
abstract class Process extends swoole_process implements IProcess
{
    private $confirations = [];
    protected $processName;

    /**
     * Process constructor.
     * @param string $name
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
     * 设置配置项
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function configure(array $data)
    {
        if (is_array($data)) {
            $this->confirations = $data;
            return $this;
        }
        throw new Exception("process: configuration must be array");
    }

    /**
     * 读取配置项
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    public function getConfig(string $key)
    {
        if (isset($this->confirations[$key])) {
            return $this->confirations[$key];
        }
        throw new Exception("process: undefined {$key} configuration");
    }

    /**
     * 读取IServer实例
     * @return IServer
     */
    public function getServer()
    {
        return $this->getConfig('server');
    }
}
