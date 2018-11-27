<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-22
 */
namespace Uniondrug\Server2\Clients\Agents;

use swoole_process;
use Uniondrug\Server2\Clients\Abstracts\Agent;

/**
 * 停止服务
 * @package Uniondrug\Server2\Clients\Agents
 */
class StopAgent extends Agent
{
    /**
     * 发送停止指令
     */
    public function run()
    {
        if ($this->client->args->hasOption('l') || $this->client->args->hasOption('list') || $this->client->args->hasOption('f') || $this->client->args->hasOption('force') || $this->client->args->hasOption('kill')) {
            $this->stopBySignal();
        } else {
            $this->stopByAgent();
        }
    }

    /**
     * 打印帮助
     */
    public function runHelp()
    {
        $this->printUsage();
        $this->printOptions($this->client->args->getStopOptions());
    }

    /**
     * 列出进程列表
     * @return int|false
     */
    private function readPid()
    {
        $setting = $this->client->builder->getSetting();
        // 1. defined or not
        if (!isset($setting['pid_file']) || !is_string($setting['pid_file'])) {
            $this->client->console->error("pid file not defined by {config/server.php}");
            return false;
        }
        // 2. exists or not
        if (!file_exists($setting['pid_file'])) {
            $this->client->console->error("can not find pid file {%s}", $setting['pid_file']);
            return false;
        }
        // 3. pid
        $pid = trim(file_get_contents($setting['pid_file']));
        if (preg_match("/^[\d]+$/", $pid) > 0) {
            $pid = (int) $pid;
            if ($pid > 0) {
                return $pid;
            }
        }
        // 4. error
        $this->client->console->error("error pid value '{%s}'.", $pid);
        return false;
    }

    /**
     * 按进程ID读取列表
     */
    private function readProcesses(int $pid)
    {
        $ps = $this->readProcessesOfOs();
        $ids = [];
        $data = [];
        for ($i = 0; $i < 5; $i++) {
            foreach ($ps as $k => $p) {
                if ($p['pid'] === $pid) {
                    $ids[] = $p['pid'];
                    $data[$k] = $p;
                    continue;
                }
                if (in_array($p['ppid'], $ids)) {
                    $ids[] = $p['pid'];
                    $data[$k] = $p;
                    continue;
                }
            }
        }
        return $data;
    }

    private function readProcessesOfOs()
    {
        $data = [];
        try {
            $cmd = 'ps -x -o pid,ppid,time,args';
            $res = shell_exec($cmd);
            foreach (explode("\n", $res) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $line = preg_replace("/\s+/", " ", $line);
                $cols = explode(" ", $line);
                $data[$cols[0]] = [
                    'pid' => (int) $cols[0],
                    'ppid' => (int) $cols[1],
                    'time' => $cols[2],
                    'args' => substr(implode(' ', array_slice($cols, 3)), 0, 50),
                    'signal' => null
                ];
            }
            ksort($data);
            reset($data);
        } catch(\Throwable $e) {
            $this->client->console->error("list process failure - %s", $e->getMessage());
        }
        return $data;
    }

    /**
     * 向Agent发向Stop指令
     */
    private function stopByAgent()
    {
        $this->client->console->info("send stop request to {%s} service.", $this->client->builder->getName());
        $data = $this->request("PUT", "stop");
        // 1. contents has or not
        if ($data === false) {
            return;
        }
        // 2. stats
        $this->printStats($data);
    }

    /**
     * 发送Signal信息
     */
    private function stopBySignal()
    {
        // 1. read pid
        $pid = $this->client->args->getOption('pid');
        if ($pid === false) {
            $pid = $this->readPid();
            if ($pid === false) {
                return;
            }
        } else {
            $errorPid = true;
            if (preg_match("/^[\d]+$/", $pid) > 0) {
                $pid = (int) $pid;
                if ($pid > 0) {
                    $errorPid = false;
                }
            }
            if ($errorPid) {
                $this->client->console->error("error pid value '{%s}' by option setting", $pid);
                return;
            }
        }
        // 2. read process list
        $processes = $this->readProcesses($pid);
        if (count($processes) === 0) {
            $this->client->console->error("no process found of the no.%d pid", $pid);
            return;
        }
        // 3. send signal
        $signal = SIGTERM;
        $signalName = "SIGTERM";
        if ($this->client->args->hasOption('kill')) {
            $signal = SIGKILL;
            $signalName = "SIGKILL";
        }
        // 4. list only
        $listOnly = $this->client->args->hasOption('list') || $this->client->args->hasOption('l');
        foreach ($processes as & $process) {
            $process['signal'] = $signalName;
            if (!$listOnly) {
                try {
                    swoole_process::kill($process['pid'], $signal);
                } catch(\Throwable $e) {
                    $process['signal'] .= ' '.$e->getMessage();
                }
            }
        }
        $this->client->console->printTable($processes);
    }
}

