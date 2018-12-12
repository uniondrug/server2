<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

use Uniondrug\Server2\Processes\XProcess;

/**
 * 停止服务
 * @package Uniondrug\Server2\Helpers
 */
class StopHelper extends Abstracts\Base implements IHelper
{
    /**
     * 描述
     * @var string
     */
    protected static $description = "stop server";
    /**
     * 接受参数
     * @var array
     */
    protected static $options = [
        [
            'name' => 'mode',
            'desc' => 'special stop mode; accept: pid、name、auto.'
        ],
        [
            'name' => 'pid',
            'desc' => 'specify the master process id by pid mode'
        ],
        [
            'name' => 'kill',
            'desc' => 'send shutdown request with manager agent'
        ]
    ];

    /**
     * 运行过程
     */
    public function run()
    {
        $mode = $this->helper->getOption('mode');
        switch ($mode) {
            case 'name' :
                $this->byName();
                break;
            case 'pid' :
                $this->byPid();
                break;
            default :
                $this->byAuto();
                break;
        }
    }

    /**
     * 打印帮助
     */
    public function runHelper()
    {
        $this->printOptions(self::$options);
    }

    /**
     * 发送shutdown请求
     */
    private function byAuto()
    {
        $this->println("[mode=manager] send shutdown request to manager agent.");
        $this->request("PUT", "/stop");
    }

    /**
     * 按进程名称退出
     */
    private function byName()
    {
        $name = sprintf("%s.%s", $this->builder->getEnv(), $this->builder->getAppName());
        $this->println("[mode=name][name=%s] stop server by process name", $name);
        $ps = $this->listProcessByName($name);
        if ($this->helper->hasOption('kill')) {
            $i = 0;
            foreach ($ps as $p) {
                $i++;
                XProcess::kill($p['pid'], SIGKILL);
                $this->println("[mode=pid][name=%s] kill {%s} process", $name, $p['args']);
            }
            $this->println("[mode=pid][name=%s] total %d process killed", $name, $i);
        } else {
            count($ps) && $this->printTable($ps);
        }
    }

    /**
     * 按Master进程ID退出
     */
    private function byPid()
    {
        $pid = $this->runningPid();
        if ($pid === 0) {
            $this->println("[mode=pid] can not find process id");
            return;
        }
        $this->println("[mode=pid][pid=%d] stop server by process id and kill them by '--kill' option.", $pid);
        $ps = $this->listProcessByPid($pid);
        if ($this->helper->hasOption('kill')) {
            $i = 0;
            foreach ($ps as $p) {
                $i++;
                XProcess::kill($p['pid'], SIGKILL);
                $this->println("[mode=pid][pid=%d] kill {%s} process", $pid, $p['args']);
            }
            $this->println("[mode=pid][pid=%d] total %d process killed", $pid, $i);
        } else {
            count($ps) && $this->printTable($ps);
        }
    }

    /**
     * 按进程名列出进程
     * @param string $name
     * @return array
     */
    private function listProcessByName(string $name)
    {
        $cmd = "ps x -o ppid,pid,args | grep -v grep | grep '{$name}'";
        $str = shell_exec($cmd);
        $psx = [];
        foreach (explode("\n", $str) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cols = explode(" ", preg_replace("/\s+/", " ", $line));
            if (count($cols) < 3) {
                continue;
            }
            $psx[$cols[1]] = [
                'ppid' => $cols[0],
                'pid' => $cols[1],
                'args' => implode(" ", array_slice($cols, 2))
            ];
        }
        return $psx;
    }

    /**
     * @param int $pid
     * @return array
     */
    private function listProcessByPid(int $pid)
    {
        // 1. list all process
        $cmd = 'ps x -o ppid,pid,args';
        $str = shell_exec($cmd);
        $psx = [];
        foreach (explode("\n", $str) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cols = explode(" ", preg_replace("/\s+/", " ", $line));
            if (count($cols) < 3) {
                continue;
            }
            $psx[$cols[1]] = [
                'ppid' => $cols[0],
                'pid' => $cols[1],
                'args' => implode(" ", array_slice($cols, 2))
            ];
        }
        // 2. filter pid
        $ids = [$pid];
        for ($i = 0; $i < 3; $i++) {
            foreach ($psx as $id => $proc) {
                in_array($proc['ppid'], $ids) && $ids[] = $proc['pid'];
            }
        }
        // 3. select item
        $data = [];
        foreach ($psx as $id => $proc) {
            in_array($id, $ids) && $data[$id] = $proc;
        }
        // 4. result
        ksort($data);
        reset($data);
        return $data;
    }

    /**
     * @return int
     */
    private function runningPid()
    {
        // 1. 指定PID
        if ($this->helper->hasOption('pid')) {
            $pid = $this->helper->getOption('pid');
            if (is_numeric($pid) && $pid > 1) {
                return (int) $pid;
            }
            return 0;
        }
        // 2. 读启动记录
        $settings = $this->builder->getSettings();
        if (is_array($settings) && isset($settings['pid_file']) && file_exists($settings['pid_file'])) {
            $pid = file_get_contents($settings['pid_file']);
            if ($pid !== false) {
                $pid = trim($pid);
                if (is_numeric($pid) && $pid > 0) {
                    return (int) $pid;
                }
            }
        }
        // 3. not found
        return 0;
    }
}
