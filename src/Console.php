<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2;

/**
 * Console/异步Logger
 * @package Uniondrug\Server2
 */
class Console
{
    const LEVEL_ERROR = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_INFO = 4;
    const LEVEL_NOTICE = 8;
    const LEVEL_DEBUG = 16;
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
    private static $levelTexts = [
        self::LEVEL_ERROR => " ERROR",
        self::LEVEL_WARNING => "  WARN",
        self::LEVEL_INFO => "  INFO",
        self::LEVEL_DEBUG => " DEBUG",
        self::LEVEL_NOTICE => "NOTICE",
    ];
    private $prefix;

    public function debug($text, ... $args)
    {
        $this->printer(self::LEVEL_DEBUG, $text, ... $args);
    }

    public function error($text, ... $args)
    {
        $this->printer(self::LEVEL_ERROR, $text, ... $args);
    }

    public function info($text, ... $args)
    {
        $this->printer(self::LEVEL_INFO, $text, ... $args);
    }

    public function notice($text, ... $args)
    {
        $this->printer(self::LEVEL_NOTICE, $text, ... $args);
    }

    public function warning($text, ... $args)
    {
        $this->printer(self::LEVEL_WARNING, $text, ... $args);
    }

    public function printer(int $level, string $format, ... $args)
    {
        $this->prefix === null || $format = $this->prefix.$format;
        // 1. for sprintf arguments
        $args = is_array($args) ? $args : [];
        array_unshift($args, $format);
        $text = call_user_func_array('sprintf', $args);
        if (false === $text) {
            $text = implode("^A", $args);
        }
        // m. level
        $label = isset(self::$levelTexts[$level]) ? self::$levelTexts[$level] : '';
        $text = sprintf("[%s]%s", $label, $text);
        // n. color
        if (isset(self::$levelColors[$level])) {
            $color = self::$levelColors[$level];
            $text = sprintf("\033[%d;%dm%s\033[0m", $color[0], $color[1], $text);
        }
        // x. print
        echo sprintf("%s\n", $text);
    }

    public function getPrefix()
    {
        return (string) $this->prefix;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }
}
