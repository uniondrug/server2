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
    public $options = [];
    private $managerDecode = [];

    public function __construct(Console $console, Helper $helper, Builder $builder)
    {
        $this->console = $console;
        $this->helper = $helper;
        $this->builder = $builder;
        $this->beforeRun();
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
    protected function request(string $method, string $uri)
    {
        $uri = preg_replace("/^\/+/", "", $uri);
        $url = sprintf("http://%s/%s", $this->builder->getManagerAddr(), $uri);
        try {
            $client = new Client([
                'timeout' => 1,
                'headers' => [
                    'user-agent' => $this->builder->getAppName(),
                    'manager-token' => isset($this->managerDecode['token']) ? $this->managerDecode['token'] : 'null'
                ]
            ]);
            $request = $client->request($method, $url);
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
}
