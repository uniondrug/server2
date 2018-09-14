<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2;

use Phalcon\Di;
use Phalcon\Logger\Adapter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;
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
     * @var Adapter
     */
    private $logger;

    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = null, $formatter = null)
    {
        parent::__construct($verbosity, $decorated, $formatter);
        $this->logger = Di::getDefault()->getLogger('server');
    }

    /**
     * @param string $message
     * @param array  ...$args
     */
    public function debug(string $message, ... $args)
    {
        $message = $this->formatContents($message, ... $args);
        $this->logger->debug($message);
        $this->printConsole(self::LEVEL_DEBUG, $message);
    }

    /**
     * @param string $message
     * @param array  ...$args
     */
    public function error(string $message, ... $args)
    {
        $message = $this->formatContents($message, ... $args);
        $this->logger->error($message);
        $this->printConsole(self::LEVEL_ERROR, $message);
    }

    /**
     * @param string $message
     * @param array  ...$args
     */
    public function info(string $message, ... $args)
    {
        $message = $this->formatContents($message, ... $args);
        $this->logger->info($message);
        $this->printConsole(self::LEVEL_INFO, $message);
    }

    /**
     * @param string $message
     * @param array  ...$args
     */
    public function warning(string $message, ... $args)
    {
        $message = $this->formatContents($message, ... $args);
        $this->logger->warning($message);
        $this->printConsole(self::LEVEL_WARNING, $message);
    }

    /**
     * 计算Log内容
     * @param string $message
     * @param array  ...$args
     * @return string
     */
    private function formatContents(string $message, ... $args)
    {
        if (count($args) > 0) {
            try {
                return (string) call_user_func_array('sprintf', array_merge([$message], $args));
            } catch(Throwable $e) {
            }
        }
        return $message;
    }

    /**
     * 打印控制台内容
     * @param string $level
     * @param string $contents
     */
    private function printConsole(string $level, string $contents)
    {
        $this->writeln("[{$level}]".$contents);
    }
}
