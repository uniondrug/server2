<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2;

/**
 * 命令行入参解析
 * @package Uniondrug\Server2
 */
class Args
{
    /**
     * 原始命令行参数
     * @var array
     */
    private $argv = [];
    private $options = [];
    private $command;
    private $defaultCommand = 'help';
    private $defaultEnvironment = 'development';
    /**
     * @var Builder
     */
    private $builder;

    /**
     * Args constructor.
     * @param null $argv
     */
    public function __construct($argv = null)
    {
        $argv === null && $argv = isset($_SERVER['argv']) && is_array($_SERVER['argv']) ? $_SERVER['argv'] : [];
        $this->argv = $argv;
        $this->parseArgv();
    }

    /**
     * 读取命令名称
     * @return string
     */
    public function getCommand()
    {
        return $this->command ?: $this->defaultCommand;
    }

    /**
     * 是否守护启动
     * @return bool
     */
    public function getDaemon()
    {
        return isset($this->options['daemon']);
    }

    /**
     * 读取环境名称
     * @return string
     */
    public function getEnvironment()
    {
        if (isset($this->options['env']) && $this->options['env'] !== '') {
            return $this->options['env'];
        }
        return $this->defaultEnvironment;
    }

    /**
     * 命令行参数指定的IP地址
     * @return bool|mixed
     */
    public function getHost()
    {
        return isset($this->options['host']) && $this->options['host'] !== '' ? $this->options['host'] : false;
    }

    /**
     * 命令行参数指定的端口号
     * @return bool|mixed
     */
    public function getPort()
    {
        return isset($this->options['port']) && $this->options['port'] !== '' ? $this->options['port'] : false;
    }

    /**
     * 读取命令列表
     * @return array
     */
    public function getAllowCommands()
    {
        return [
            [
                'name' => 'status',
                'desc' => 'show server status and statistics'
            ],
            [
                'name' => 'start',
                'desc' => 'start HTTP/WebSocket server'
            ],
            [
                'name' => 'reload',
                'desc' => 'send {SIGTERM} to worker/tasker and start again'
            ],
            [
                'name' => 'stop',
                'desc' => 'quit server'
            ]
        ];
    }

    /**
     * 执行start命令时的选项
     * @return array
     */
    public function getStartOptions()
    {
        return [
            [
                'name' => 'container',
                'short' => 'c',
                'desc' => 'running in docker container',
            ],
            [
                'name' => 'daemon',
                'short' => 'd',
                'desc' => 'run a daemon process'
            ],
            [
                'name' => 'env',
                'short' => 'e',
                'desc' => 'set environment name',
                'default' => 'development',
                'value' => '<env>'
            ],
            [
                'name' => 'host',
                'short' => 'h',
                'desc' => 'set listen ip address',
                'default' => $this->builder ? $this->builder->getHost() : null,
                'value' => '<ip>'
            ],
            [
                'name' => 'ipaddr',
                'desc' => 'same as --host',
                'value' => '<ip>'
            ],
            [
                'name' => 'port',
                'short' => 'p',
                'desc' => 'set listen port',
                'default' => $this->builder ? $this->builder->getPort() : null,
                'value' => '<port>'
            ]
        ];
    }

    /**
     * 执行stop命令时的选项
     * @return array
     */
    public function getStopOptions()
    {
        return [
            [
                'name' => 'force',
                'short' => 'f',
                'desc' => 'send {SIGTERM} signal to running process'
            ],
            [
                'name' => 'kill',
                'desc' => 'send {SIGKILL} signal to running process'
            ],
            [
                'name' => 'list',
                'short' => 'l',
                'desc' => 'list running processes'
            ],
            [
                'name' => 'pid',
                'desc' => 'specify the master process id',
                'value' => '<int>'
            ]
        ];
    }

    /**
     * 读取status命令时的选项
     * 读取状态选项
     */
    public function getStatusOptions()
    {
        return [];
    }

    /**
     * 按键名读取选项值
     * @param string $key
     * @return bool|mixed
     */
    public function getOption(string $key)
    {
        if ($this->hasOption($key)) {
            return $this->options[$key];
        }
        return false;
    }

    /**
     * 读取是否定义了指定入参
     * @param string $key
     * @return bool
     */
    public function hasOption(string $key)
    {
        return isset($this->options[$key]);
    }

    /**
     * 绑定Builder
     * @param Builder $builder
     * @return $this
     */
    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;
        return $this;
    }

    /**
     * 解析命令行参数
     */
    private function parseArgv()
    {
        $lastIdx = 0;
        $lastKey = null;
        $lastCommand = null;
        foreach ($this->argv as $argv) {
            $argv = trim($argv);
            if (preg_match("/^[\-]+([^=]+)[=]?(\S*)/", $argv, $m) > 0) {
                $m[1] = trim($m[1]);
                $m[2] = trim($m[2]);
                $this->parseValue($m[1], $m[2]);
                if ($m[2] === "") {
                    $lastKey = $m[1];
                }
                continue;
            }
            if ($lastKey === null) {
                if ($lastIdx === 1) {
                    $this->command = $argv;
                }
            } else {
                $this->parseValue($lastKey, $argv);
                $lastKey = null;
            }
            $lastIdx++;
        }
    }

    /**
     * @param string $key
     * @param string $value
     */
    private function parseValue($key, $value)
    {
        switch ($key) {
            case 'c' :
                $key = 'container';
                break;
            case 'd' :
                $key = 'daemon';
                break;
            case 'e' :
                $key = 'env';
                break;
            case 'h' :
            case 'ipaddr' :
                $key = 'host';
                break;
            case 'p' :
                $key = 'port';
                break;
        }
        $this->options[$key] = $value;
    }
}
