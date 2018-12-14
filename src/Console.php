<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2;

use Phalcon\Di;
use Phalcon\Logger\AdapterInterface;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Servers\IHttp;
use Uniondrug\Server2\Servers\ISocket;

/**
 * Console/异步Logger
 * @package Uniondrug\Server2
 */
class Console
{
    /**
     * Logger级别
     */
    const LEVEL_ERROR = 3;
    const LEVEL_WARNING = 4;
    const LEVEL_NOTICE = 5;
    const LEVEL_INFO = 6;
    const LEVEL_DEBUG = 7;
    /**
     * Logger颜色支持
     * @var array
     */
    private static $levelColors = [
        self::LEVEL_ERROR => [
            31,
            49
        ],
        self::LEVEL_WARNING => [
            31,
            47
        ],
        self::LEVEL_NOTICE => [
            35,
            47
        ],
        self::LEVEL_DEBUG => [
            37,
            49
        ],
        self::LEVEL_INFO => [
            34,
            49
        ],
    ];
    /**
     * Logger级别文本
     * @var array
     */
    private static $levelTexts = [
        self::LEVEL_ERROR => "ERROR",
        self::LEVEL_WARNING => "WARN",
        self::LEVEL_INFO => "INFO",
        self::LEVEL_DEBUG => "DEBUG",
        self::LEVEL_NOTICE => "NOTICE",
    ];
    /**
     * Logger前缀
     * @var string
     */
    private $prefix;
    /**
     * @var IHttp|ISocket
     */
    private $server;
    private $serverLogDate = 0;

    /**
     * DEBUG/调试信息
     * @param       $text
     * @param array ...$args
     */
    public function debug($text, ... $args)
    {
        $this->printer(self::LEVEL_DEBUG, $text, ... $args);
    }

    /**
     * ERROR/错误信息
     * @param       $text
     * @param array ...$args
     */
    public function error($text, ... $args)
    {
        $this->printer(self::LEVEL_ERROR, $text, ... $args);
    }

    /**
     * INFO/信息
     * @param       $text
     * @param array ...$args
     */
    public function info($text, ... $args)
    {
        $this->printer(self::LEVEL_INFO, $text, ... $args);
    }

    /**
     * NOTICE/警告信息
     * @param       $text
     * @param array ...$args
     */
    public function notice($text, ... $args)
    {
        $this->printer(self::LEVEL_NOTICE, $text, ... $args);
    }

    /**
     * WARNING/警告信息
     * @param       $text
     * @param array ...$args
     */
    public function warning($text, ... $args)
    {
        $this->printer(self::LEVEL_WARNING, $text, ... $args);
    }

    /**
     * 打印日志
     * @param int    $level
     * @param string $format
     * @param array  ...$args
     */
    public function printer(int $level, string $format, ... $args)
    {
        // 1.1 Logger前缀
        $contents = $this->prefix === null ? "" : $this->prefix;
        // 1.2 Logger正文
        $args = is_array($args) ? $args : [];
        array_unshift($args, $format);
        $buffer = call_user_func_array('sprintf', $args);
        if (false === $buffer) {
            $buffer = $format."^A".implode("^C", $args);
        }
        $contents .= $buffer;
        // 2. 写入Logger文件
        if ($this->server !== null) {
            // 2.1 定义了Container容器
            if (isset($this->server->container)) {
                // 2.2 启动容器
                if ($this->server->container === null && method_exists($this->server, 'startFramework')) {
                    $this->server->startFramework();
                }
            }
            // 2.3 写入日志
            if ($this->server->container instanceof Container) {
                $date = (int) date('Ymd');
                if ($this->serverLogDate !== $date) {
                    $this->serverLogDate = $date;
                    $this->server->container->removeSharedInstance('logger');
                }
                /**
                 * @var AdapterInterface $logger
                 */
                $logger = $this->server->container->getLogger('server');
                switch ($level) {
                    case self::LEVEL_INFO :
                        $logger->info($contents);
                        break;
                    case self::LEVEL_DEBUG :
                        $logger->debug($contents);
                        break;
                    case self::LEVEL_ERROR :
                        $logger->error($contents);
                        break;
                    case self::LEVEL_WARNING :
                        $logger->warning($contents);
                        break;
                    case self::LEVEL_NOTICE :
                        $logger->notice($contents);
                        break;
                    default :
                        $logger->log(0, $contents);
                        break;
                }
                return;
            }
        }
        // 3. 控制台输出
        $label = isset(self::$levelTexts[$level]) ? self::$levelTexts[$level] : 'OTHERS';
        $stdout = sprintf("[%s]%s", $label, $contents);
        if (isset(self::$levelColors[$level])) {
            $color = self::$levelColors[$level];
            $stdout = sprintf("\033[%d;%dm%s\033[0m", $color[0], $color[1], $stdout);
        }
        file_put_contents("php://output", "{$stdout}\n");
        //        $container = Di::getDefault();
        //        if ($container instanceof Container) {
        //            // 2.1 写入Logger
        //            $date = (int) date('Ymd');
        //            if ($date !== self::$bufferDate) {
        //                self::$bufferDate = $date;
        //                $container->removeSharedInstance('logger');
        //            }
        //            if (self::$bufferCount > 0) {
        //                $contents = self::$bufferText.$contents;
        //                self::$bufferText = "";
        //                self::$bufferCount = 0;
        //            }
        //            $container->getLogger('server')->log($level, $contents);
        //        } else {
        //            if (self::$bufferCount >= 1000) {
        //                self::$bufferCount = 0;
        //                self::$bufferText = "";
        //            }
        //            // 2.2 加入Buffer
        //            self::$bufferCount++;
        //            self::$bufferText .= "{$contents}\n";
        //        }
        //        // 3. StdOut
        //        $label = isset(self::$levelTexts[$level]) ? self::$levelTexts[$level] : 'OTHERS';
        //        $stdout = sprintf("[%s]%s", $label, $contents);
        //        if (isset(self::$levelColors[$level])) {
        //            $color = self::$levelColors[$level];
        //            $stdout = sprintf("\033[%d;%dm%s\033[0m", $color[0], $color[1], $stdout);
        //        }
        //        file_put_contents("php://output", "{$stdout}\n");
    }

    /**
     * 读取Logger前缀
     * @return string
     */
    public function getPrefix()
    {
        return (string) $this->prefix;
    }

    /**
     * 设置Logger前缀
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 设置Server实例
     * @param IHttp|ISocket $server
     * @return $this
     */
    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }
}
