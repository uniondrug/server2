<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2;

use Uniondrug\Framework\Container;
use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;

/**
 * 控制台输出
 * @package Uniondrug\Server2
 */
class Console
{
    const LEVEL_ERROR = 1;
    const LEVEL_WARN = 2;
    const LEVEL_INFO = 4;
    const LEVEL_DEBUG = 8;
    /**
     * 级别定义
     * @var array
     */
    private static $levels = [
        self::LEVEL_ERROR => "ERROR",
        self::LEVEL_WARN => "WARN ",
        self::LEVEL_INFO => "INFO ",
        self::LEVEL_DEBUG => "DEBUG",
    ];
    /**
     * 消息前缀
     * @var string|null
     */
    private $prefix = null;
    /**
     * Phalcon支持
     * @var Container
     */
    private $container;
    /**
     * Server实例
     * @var IHttp|ISocket
     */
    private $server;

    /**
     * 输出Debug信息
     * @param       $text
     * @param array ...$args
     */
    public function debug($text, ... $args)
    {
        $this->stdout(self::LEVEL_DEBUG, $text, ... $args);
    }

    /**
     * 输出Error信息
     * @param       $text
     * @param array ...$args
     */
    public function error($text, ... $args)
    {
        $this->stdout(self::LEVEL_ERROR, $text, ... $args);
    }

    /**
     * 输出Info信息
     * @param       $text
     * @param array ...$args
     */
    public function info($text, ... $args)
    {
        $this->stdout(self::LEVEL_INFO, $text, ... $args);
    }

    /**
     * 输出Warn信息
     * @param       $text
     * @param array ...$args
     */
    public function warn($text, ... $args)
    {
        $this->stdout(self::LEVEL_WARN, $text, ... $args);
    }

    public function warning($text, ... $args)
    {
        $this->warn($text, ... $args);
    }

    /**
     * Console绑定Phalcon容器
     * @param Container $container
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Console绑定Server实例
     * @param IHttp|ISocket $server
     * @return $this
     */
    public function setServer($server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * 设置信息前缀
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 格式蓝色文字
     * @param     $text
     * @param int $width
     * @return string
     */
    public function withBlue($text, $width = 0)
    {
        return $this->withColor($text, 34, 49, $width);
    }

    /**
     * 格式绿色文字
     * @param     $text
     * @param int $width
     * @return string
     */
    public function withGreen($text, $width = 0)
    {
        return $this->withColor($text, 32, 49, $width);
    }

    /**
     * 格式灰色文字
     * @param     $text
     * @param int $width
     * @return string
     */
    public function withGray($text, $width = 0)
    {
        return $this->withColor($text, 37, 49, $width);
    }

    /**
     * 格式红色文字
     * @param     $text
     * @param int $width
     * @return string
     */
    public function withRed($text, $width = 0)
    {
        return $this->withColor($text, 31, 49, $width);
    }

    /**
     * 格式黄色文字
     * @param     $text
     * @param int $width
     * @return string
     */
    public function withYellow($text, $width = 0)
    {
        return $this->withColor($text, 33, 49, $width);
    }

    /**
     * 颜色文字
     * `0`: 黑
     * `1`: 红
     * `2`: 绿
     * `3`: 黄
     * `4`: 蓝
     * `5`: 粉
     * `6`: 青
     * `7`: 灰
     * `8`: 黑-1
     * `9`: 黑-2
     * @param string $text  文字
     * @param int    $fg    文字颜色
     * @param int    $bg    背景颜色
     * @param int    $width 字宽
     * @return string
     */
    public function withColor($text, $fg, $bg, $width = 0)
    {
        $width > 0 && $text = sprintf("%-{$width}s", $text);
        return sprintf("\033[%d;%dm%s\033[0m", $fg, $bg, $text);
    }

    public function print(string $text)
    {
        print($text);
    }

    public function printCell(array $cells, int $size = 0, bool $left = true)
    {
        if ($size === 0) {
            foreach ($cells as $cell) {
                $size = max($size, $this->stdwidth($cell[0]));
            }
        }
        foreach ($cells as $cell) {
            $line = sprintf('%'.($left ? '-' : '').$size.'s', $cell[0]);
            $line = sprintf("%s     %s", $this->withGreen($line), $cell[1]);
            $this->println($line);
        }
    }

    /**
     * 二维数组打印表格式
     * @param array $data
     * @param bool  $showHead
     */
    public function printTable(array $data, bool $showHead = true)
    {
        // 1.1 计算宽度
        $i = 0;
        $size = [];
        $usedData = [];
        foreach ($data as $row) {
            // 1.2. not formatted
            if (!is_array($row)) {
                continue;
            }
            // 1.3. loop
            $ignore = false;
            foreach ($row as $key => $value) {
                // 1.4. no 2 loops
                if (is_array($value)) {
                    $ignore = true;
                    break;
                }
                // 1.5. first row
                if ($i === 0) {
                    $size[$key] = $this->stdwidth($key);
                }
                // 1.6. match max
                $size[$key] = max($size[$key], $this->stdwidth($value));
            }
            // 1.7. ignore
            if ($ignore) {
                continue;
            }
            $usedData[] = $row;
            $i++;
        }
        // 2. parse contents
        $comma = '+';
        $separator = $comma;
        foreach ($size as $key => $width) {
            for ($i = 0; $i < $width + 2; $i++) {
                $separator .= '-';
            }
            $separator .= $comma;
        }
        // 3.1 top separator
        $this->println($separator);
        $i = 0;
        foreach ($usedData as $row) {
            $comma = '|';
            $line = $comma;
            $thead = $line;
            foreach ($row as $key => $value) {
                if ($i == 0) {
                    $thead .= " ".$this->withGreen($key, $size[$key])." ";
                    $thead .= $comma;
                }
                $line .= " ".$this->withGray($value, $size[$key])." ";
                $line .= $comma;
            }
            if ($i == 0 && $showHead) {
                $this->println($thead);
                $this->println($separator);
            }
            $this->println($line);
            $i++;
        }
        $this->println($separator);
    }

    public function println(string $text)
    {
        $this->print("{$text}\n");
    }

    /**
     * 打印消息
     * @param int    $level
     * @param string $text
     * @param array  ...$args
     */
    private function stdout(int $level, string $text, ... $args)
    {
        // 1. 原始文本
        $args = is_array($args) ? $args : [];
        array_unshift($args, $text);
        $text = call_user_func_array('sprintf', $args);
        // 2. 前缀
        $this->prefix === null || $text = "[{$this->prefix}]{$text}";
        // 3. 级别附加
        isset(self::$levels[$level]) && $text = "[".self::$levels[$level]."]{$text}";
        // 4. 附加时间
        $t = new \DateTime();
        $s = $t->format('Y-m-d H:i:s.u O');
        $text = "[{$s}]{$text}";
        // 5. 颜色追加
        switch ($level) {
            case self::LEVEL_ERROR :
                $text = $this->withRed($text);
                break;
            case self::LEVEL_WARN :
                $text = $this->withYellow($text);
                break;
            case self::LEVEL_INFO :
                $text = $this->withBlue($text);
                break;
            case self::LEVEL_DEBUG :
                $text = $this->withGray($text);
                break;
        }
        $this->println($text);
    }

    private function stdwidth($text)
    {
        return strlen($text);
    }
}
