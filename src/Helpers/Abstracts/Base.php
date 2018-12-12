<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers\Abstracts;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Helper;

/**
 * Help基类
 * @package Uniondrug\Server2\Helpers\Abstracts
 */
abstract class Base
{
    public $builder;
    public $console;
    public $helper;
    /**
     * 当前命令支持的选项定义
     * @var array
     */
    private $managerDecode = [];
    protected static $description;
    protected static $options = [];

    public function __construct(Console $console, Helper $helper, Builder $builder)
    {
        $this->console = $console;
        $this->helper = $helper;
        $this->builder = $builder;
        $this->beforeRun();
        // 1. current command
        $cmd = $this->helper->getCommand();
        $cmd || $cmd = 'COMMAND';
        // 2. current options
        $opt = '[OPTIONS]';
        // 3. information
        echo sprintf("App      : %s/%s\n", $this->builder->getAppName(), $this->builder->getAppVersion());
        echo sprintf("Env      : %s\n", $this->builder->getEnvironment());
        echo sprintf("Path     : %s\n", $this->builder->getBasePath());
        echo sprintf("Listen   : %s\n", $this->builder->getAddr());
        echo sprintf("Manager  : %s\n", $this->builder->getManagerAddr());
        echo sprintf("Usage    : %s %s %s\n", $this->helper->getScript(), $cmd, $opt);
    }

    protected function beforeRun()
    {
        $this->decode();
        $this->merger();
    }

    protected function decode()
    {
        $this->managerDecode = $this->builder->decodeTemp();
        if (is_array($this->managerDecode)) {
            foreach ($this->managerDecode as $key => $value) {
                $this->helper->setOption($key, $value);
            }
        }
    }

    /**
     * Helper用途描述
     */
    public static function desc()
    {
        return static::$description;
    }

    protected function merger()
    {
        $this->builder->mergeHelper($this->helper);
    }

    /**
     * 以API请求Manager
     * @param string $method
     * @param string $uri
     * @return bool|mixed
     */
    protected function request(string $method, string $uri, array $data = null)
    {
        $uri = preg_replace("/^\/+/", "", $uri);
        $url = sprintf("http://%s/%s", $this->builder->getManagerAddr(), $uri);
        try {
            $opts = [
                'timeout' => 1,
                'headers' => [
                    'user-agent' => $this->builder->getAppName(),
                    'manager-token' => isset($this->managerDecode['token']) ? $this->managerDecode['token'] : 'null'
                ]
            ];
            if (is_array($data)) {
                $opts['json'] = $data;
            }
            $client = new Client();
            $request = $client->request($method, $url, $opts);
            $content = $request->getBody()->getContents();
            if ($content !== '') {
                return json_decode($content, true);
            }
            return false;
        } catch(ConnectException $e) {
            $this->console->error("服务已退出");
        } catch(\Throwable $e) {
            $this->console->error("无效的{%d}应答", $e->getCode());
        }
        return false;
    }

    /**
     * 打印命令列表
     * @param array $commands
     */
    protected function printCommands(array $commands)
    {
        echo sprintf("Commands :\n");
        foreach ($commands as $c) {
            echo sprintf("           %-18s %s\n", $c['name'], $c['desc']);
        }
    }

    /**
     * 打印命令列表
     * @param array $options
     */
    protected function printOptions(array $options)
    {
        $options[] = [
            'name' => 'help',
            'desc' => 'show options, accepted by any command.'
        ];
        echo sprintf("Options  :\n");
        foreach ($options as $c) {
            $short = isset($c['short']) && $c['short'] ? "-{$c['short']}," : '   ';
            $name = $c['name'];
            $desc = isset($c['desc']) && $c['desc'] !== '' ? $c['desc'] : null;
            echo sprintf("           %-18s %s\n", $short.'--'.$name, $desc);
        }
    }

    /**
     * 打印状态
     * @param array $data
     * @internal param array $stats
     */
    protected function printStats(array $data)
    {
        $size = [
            0,
            0
        ];
        foreach ($data as $key => $value) {
            $size[0] = max($size[0], strlen($key));
            $size[1] = max($size[1], strlen($value));
        }
        $separator = '+';
        foreach ($size as $s) {
            for ($n = 0; $n < ($s + 2); $n++) {
                $separator .= '-';
            }
            $separator .= '+';
        }
        echo sprintf("%s\n", $separator);
        foreach ($data as $key => $value) {
            echo sprintf("| %{$size[0]}s | %-{$size[1]}s |\n", $key, $value);
        }
        echo sprintf("%s\n", $separator);
    }

    /**
     * 打印表格
     * @param array $datas
     */
    protected function printTable(array $datas)
    {
        $i = 0;
        $size = [];
        foreach ($datas as $data) {
            foreach ($data as $key => $value) {
                $i === 0 && $size[$key] = strlen($key);
                $size[$key] = max($size[$key], strlen($value));
            }
            $i++;
        }
        $i = 0;
        $separator = '+';
        foreach ($datas as $data) {
            $head = '|';
            $line = '|';
            foreach ($data as $key => $value) {
                if ($i === 0) {
                    $head .= sprintf(" %-{$size[$key]}s |", $key);
                    for ($n = 0; $n < ($size[$key] + 2); $n++) {
                        $separator .= '-';
                    }
                    $separator .= '+';
                }
                $line .= sprintf(" %-{$size[$key]}s |", $value);
            }
            if ($i === 0) {
                echo sprintf("%s\n", $separator);
                echo sprintf("%s\n", $head);
                echo sprintf("%s\n", $separator);
            }
            echo sprintf("%s\n", $line);
            $i++;
        }
        echo sprintf("%s\n", $separator);
    }

    /**
     * 打印内容
     * @param string $format
     * @param array  ...$args
     */
    protected function println(string $format, ... $args)
    {
        $args = is_array($args) ? $args : [];
        array_unshift($args, $format);
        $text = call_user_func_array('sprintf', $args);
        if (false === $text) {
            $text = $format."^A".implode("^C", $args);
        }
        // green
        $text = preg_replace_callback("/\[([^\]]+)\]/", function($a){
            return "[\033[31;49m{$a[1]}\033[0m]";
        }, $text);
        file_put_contents("php://stdout", "{$text}\n");
    }
}