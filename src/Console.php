<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2;

use Phalcon\Di;
use Phalcon\Logger\AdapterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Interfaces\IServer;
use Uniondrug\Server2\Interfaces\ISocket;

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
     * @var string
     */
    private $address;
    /**
     * @var AdapterInterface
     */
    private $logger;
    /**
     * @var ISocket|IServer
     */
    private $server;

    public function __construct($server, $address)
    {
        parent::__construct();
        $this->server = $server;
        $this->address = $address;
        /**
         * Dependence Injectable
         * @var Container $container
         */
        $container = Di::getDefault();
        $this->logger = $container->getLogger('server');
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
        $prefix = '['.$this->address.']';
        if (count($args) > 0) {
            try {
                return $prefix.call_user_func_array('sprintf', array_merge([$message], $args));
            } catch(Throwable $e) {
            }
        }
        return $prefix.$message;
    }

    /**
     * 打印控制台内容
     * @param string $level
     * @param string $contents
     */
    private function printConsole(string $level, string $contents)
    {
        $this->writeln("[{$level}] ".$contents);
    }
}
