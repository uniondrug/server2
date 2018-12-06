<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2;

use Uniondrug\Server2\Helpers\HelpHelper;
use Uniondrug\Server2\Helpers\IHelper;

/**
 * Helper/命令行解析
 * @package Uniondrug\Server2
 */
class Helper
{
    /**
     * 默认环境名称
     */
    const DEFAULT_ENVIRONMENT = "development";
    /**
     * 从命令行中收集到的选项
     * @var array
     */
    private $options = [];
    /**
     * 执行脚本路径
     * @var string
     */
    private $script = null;
    /**
     * 命令名称
     * @var string
     */
    private $command = null;

    public function __construct(array $args = [])
    {
        $this->argumentParser($args);
    }

    /**
     * 读取命令名
     * @return string|null
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * 设置选项值
     * @param string $key
     * @return mixed|null
     */
    public function getOption(string $key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    /**
     * 脚本路径
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * 是否有指定选项
     * @param string $key
     * @return bool
     */
    public function hasOption(string $key)
    {
        return $this->getOption($key) !== null;
    }

    /**
     * 从上次启动的信息里导出参数
     */
    public function loader()
    {
    }

    /**
     * 收集选项
     * @param string $key
     * @param string $value
     */
    public function setOption(string $key, string $value = '')
    {
        switch ($key) {
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

    /**
     * 按Helper运行
     * @param Helper  $helper
     * @param Builder $builder
     */
    public static function run(Console $console, Helper $helper, Builder $builder)
    {
        /**
         * @var string  $cmd
         * @var IHelper $handle
         */
        $cmd = $helper->getCommand();
        if ($cmd !== null) {
            $class = "\\Uniondrug\\Server2\\Helpers\\".ucfirst($cmd)."Helper";
            if (class_exists($class, true)) {
                $handle = new $class($console, $helper, $builder);
                $helper->hasOption('help') ? $handle->runHelper() : $handle->run();
                return;
            }
        }
        $handle = new HelpHelper($console, $helper, $builder);
        $handle->runHelper();
    }

    /**
     * 解析命令行
     * @param array $args
     */
    private function argumentParser(array $args = [])
    {
        // 1. 读取Args
        if (count($args) === 0) {
            $args = isset($_SERVER['argv']) && is_array($_SERVER['argv']) ? $_SERVER['argv'] : [];
        }
        // 2. 拼接Args
        $lastKey = null;
        $rexpKey = "/[\-]+([^=]+)/";
        $rexpValue = "/[\-]+([^=]+)[\s|=]+(.+)/";
        foreach ($args as $i => $arg) {
            if (preg_match($rexpValue, $arg, $m) > 0) {
                $this->setOption($m[1], $m[2]);
                continue;
            }
            if (preg_match($rexpKey, $arg, $m) > 0) {
                $lastKey = $m[1];
                $this->setOption($m[1]);
                continue;
            }
            if ($lastKey !== null) {
                $this->setOption($lastKey, $arg);
                continue;
            }
            switch ($i) {
                case 0 :
                    $this->script = $arg;
                    break;
                case 1 :
                    $this->command = $arg;
                    break;
            }
        }
        // 3. 默认选项
        if (!isset($this->options['env']) || !$this->options['env']) {
            $this->options['env'] = self::DEFAULT_ENVIRONMENT;
        }
    }
}
