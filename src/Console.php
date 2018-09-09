<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2;

use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;

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
        $timeline = date('r');
        try {
            $message = count($args) > 0 ? call_user_func_array('sprintf', array_merge([$message], $args)) : $message;
        } catch(Throwable $e) {
        }
        $this->writeln("[{$timeline}][{$level}] {$message}");
    }
}
