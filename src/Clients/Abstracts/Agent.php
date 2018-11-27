<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-22
 */
namespace Uniondrug\Server2\Clients\Abstracts;

use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Uniondrug\Server2\Clients\Client;
use Uniondrug\Server2\Clients\IAgent;

/**
 * Agent
 * @package Uniondrug\Server2\Clients\Abstracts
 */
abstract class Agent implements IAgent
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    abstract public function run();

    /**
     * 发起HTTP请求
     * @param string     $method
     * @param string     $uri
     * @param array|null $data
     * @return array|false
     */
    protected function request(string $method, string $uri, array $data = null)
    {
        $url = sprintf("http://%s/%s", $this->client->builder->getManagerAddrress(), preg_replace("/[\/]+/", "", $uri));
        $http = new GuzzleHttp();
        try {
            $opts = ['timeout' => 1];
            $data === null || $opts['body'] = $data;
            // 1. send request and read response
            $requeset = $http->request($method, $url, $opts);
            $contents = $requeset->getBody()->getContents();
            // 2. decode
            return \GuzzleHttp\json_decode($contents, JSON_UNESCAPED_UNICODE);
        } catch(\Throwable $e) {
            // 1. error connection
            if ($e instanceof ConnectException) {
                $this->client->console->error("stopped or quited.");
                return false;
            }
            // 2. error client request
            if ($e instanceof ClientException) {
                $this->client->console->error("server response {%s} code.", $e->getCode());
                return false;
            }
            // 3. other error
            $this->client->console->error("error response from server - %s", $e->getMessage());
            return false;
        }
    }

    /**
     * 服务构建信息
     */
    protected function printBuilder()
    {
        $data = [
            'App' => $this->client->builder->getName(),
            'Boot' => $this->client->builder->getEntrypoint(),
            'Listen' => $this->client->builder->getAddress(),
            'Agent' => $this->client->builder->getManagerAddrress(),
        ];
        foreach ($data as $key => $value) {
            $line = $this->client->console->withBlue($key.': ');
            $line .= $this->client->console->withGray($value);
            $this->client->console->println($line);
        }
    }

    /**
     * 打印命令列表
     * @param array $commands
     */
    protected function printCommands(array $commands)
    {
        // 1. print head
        $msg = $this->client->console->withBlue('Commands: ');
        $this->client->console->println($msg);
        // 2. loop
        $prefix = "       ";
        foreach ($commands as $option) {
            // 3. line
            $line = $prefix.$this->client->console->withGreen($option['name'], 16);
            // 4. desc
            $line .= isset($option['desc']) ? $option['desc'] : '';
            $this->client->console->println($line);
        }
    }

    /**
     * 打印选项列表
     * @param array $options
     */
    protected function printOptions(array $options)
    {
        // 1. no options defined
        if (count($options) === 0) {
            return;
        }
        // 2. print head
        $msg = $this->client->console->withBlue('Options: ');
        $this->client->console->println($msg);
        // 3. loop
        $prefix = "  ";
        foreach ($options as $option) {
            // 4. option
            $name = isset($option['short']) && $option['short'] !== '' ? "-{$option['short']}," : "   ";
            $name .= "--{$option['name']}";
            isset($option['value']) && $option['value'] != '' && $name .= "={$option['value']}";
            // 5. line
            $line = $prefix.$this->client->console->withYellow($name, 21);
            // 6. desc
            $line .= isset($option['desc']) ? $option['desc'] : '';
            $this->client->console->println($line);
        }
    }

    /**
     * 打印进程列表
     * @param array $processes
     */
    protected function printProcess(array $processes)
    {
        $data = [];
        foreach ($processes as $process) {
            $data[$process['pid']] = [
                'PPID' => $process['ppid'],
                'PID' => $process['pid'],
                'Task' => $process['onTask'].($process['onFinish'] < $process['onTask'] ? ' (*)' : ''),
                'Started Time' => date('y/m/d H:i', $process['time']),
                'Process Name' => $process['name'],
            ];
        }
        ksort($data);
        reset($data);
        $this->client->console->printTable($data);
    }

    /**
     * 打印服务状态
     * @param array $stats
     */
    protected function printStats(array $stats)
    {
        $data = [];
        foreach ($stats as $key => $value) {
            switch ($key) {
                case 'start_time' :
                    $key = 'Started Time';
                    $value = date('y/m/d H:i');
                    break;
                case 'connection_num' :
                    $key = 'Actived Connections';
                    break;
                case 'accept_count' :
                    $key = 'History Connections';
                    break;
                case 'close_count' :
                    $key = 'Closed Connections';
                    break;
                case 'request_count' :
                    $key = 'TCP Requests';
                    break;
                case 'worker_request_count' :
                    $key = 'Worker Requests';
                    break;
                case 'tasking_num' :
                    $key = 'Tasks In Queue';
                    break;
                case 'coroutine_num' :
                    $key = 'Coroutines';
                    break;
            }
            $data[] = [
                $key,
                $value
            ];
        }
        $this->client->console->printCell($data, 30, false);
    }

    /**
     * 打印语法
     * @param bool $useCommand
     * @param bool $useOptions
     */
    protected function printUsage(bool $useCommand = true, bool $useOptions = true)
    {
        $this->printBuilder();
        $msg = $this->client->console->withBlue("Usage: ");
        $msg .= "php server ";
        // 1. command
        if ($useCommand) {
            $msg .= $this->client->args->getCommand();
        } else {
            $msg .= $this->client->console->withGreen("COMMAND");
        }
        // 2. options
        if ($useOptions) {
            $msg .= $this->client->console->withYellow(" [OPTIONS]");
        }
        // 3. printer
        $this->client->console->println($msg);
        // 4. for help
        $guide = $this->client->console->withGray("you can append '--help' for more information on a command.");
        $this->client->console->println("       {$guide}");
    }
}
