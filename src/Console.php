<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2;

use Phalcon\Di;
use Symfony\Component\Console\Output\ConsoleOutput;
use Uniondrug\Framework\Container;

/**
 * console
 * @package Uniondrug\Server2
 */
class Console extends ConsoleOutput
{
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';

    /**
     * @param string $message
     * @param array  ...$args
     */
    public function debug(string $message, ... $args)
    {
        $this->render(self::LEVEL_DEBUG, $message, ... $args);
    }

    /**
     * @param string $message
     * @param array  ...$args
     */
    public function error(string $message, ... $args)
    {
        $this->render(self::LEVEL_ERROR, $message, ... $args);
    }

    /**
     * @param string $message
     * @param array  ...$args
     */
    public function info(string $message, ... $args)
    {
        $this->render(self::LEVEL_INFO, $message, ... $args);
    }

    /**
     * @param string $message
     * @param array  ...$args
     */
    public function warning(string $message, ... $args)
    {
        $this->render(self::LEVEL_WARNING, $message, ... $args);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array  ...$args
     */
    private function render(string $level, string $message, ... $args)
    {
        // 1. generate contents
        if (is_array($args) && count($args)) {
            $message = call_user_func_array('sprintf', array_merge([$message], $args));
        }
        // 2. generate formatter
        $message = "[".date('r')."][{$level}] ${message}";
        // 3. write in console
        $this->writeln($message);
        // 4. write to file
        $this->writeLogger($level, $message);
    }

    /**
     * write contents to log file
     * @param string $message
     */
    private function writeLogger($level, & $message)
    {
        /**
         * @var Container $di
         */
        $di = Di::getDefault();
        $di->getLogger('server')->log($level, $message);
    }
}
