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
    protected static $description = "按auto/name/pid模式退出服务";
    /**
     * 接受参数
     * @var array
     */
    protected static $options = [
        [
            'name' => 'mode',
            'short' => 'm',
            'value' => '[=name|pid|auto]',
            'default' => 'name',
            'desc' => '指定退出模式.'
        ],
        [
            'name' => 'pid',
            'value' => '=int',
            'desc' => '当模式为pid时, 指定Master进程ID'
        ],
        [
            'name' => 'name',
            'short' => 'n',
            'value' => '=string',
            'desc' => '当模式为name时, 指定进程名称关键词'
        ],
        [
            'name' => 'kill',
            'value' => '[=no|yes]',
            'default' => 'no',
            'desc' => '退出方式, no:发送SIGTERM信号, yes:发送SIGKILL信号'
        ]
    ];

    /**
     * 运行过程
     */
    public function run()
    {
        // 1. 读取模式名称
        $mode = $this->helper->getOption('m');
        $mode || $mode = $this->helper->getOption('mode');
        switch ($mode) {
            // 2. 发送shutdown命令
            case 'auto' :
                $this->byAuto();
                break;
            // 3. 按Master进程ID
            case 'pid' :
                $this->byPid();
                break;
            // 4. 按进程名称
            default :
                $this->byName();
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
        $this->println("操作 - 向服务器发送{%s}请求", "shutdown");
        if (false !== $this->request("PUT", "/stop")) {
            $this->println("完成");
        }
    }

    /**
     * 按进程名称退出
     */
    private function byName()
    {
        // 1. 计算进程名称
        $name = $this->helper->getOption('n');
        $name || $name = $this->helper->getOption('name');
        $names = [];
        if ($name) {
            $names = explode(" ", $name);
        } else {
            $name = sprintf("%s.%s", $this->builder->getEnv(), $this->builder->getAppName());
            $names[] = $name;
        }
        // 2. 读取进程列表
        $this->println("操作 - 列出名为{%s}的全部进程列表", $name);
        $ps = $this->listProcessByName($names);
        // 3. 未发现进程
        if (count($ps) === 0) {
            $this->println("结果 - 未发现进程");
            return;
        }
        // 4. 打印进程列表
        $hasKill = $this->helper->hasOption('kill');
        if (!$hasKill) {
            $this->printTable($ps);
            $this->println("提示 - 请通过'--kill[=yes|no]'对进程操作");
            return;
        }
        // 5. 操作进程
        $signal = strtolower($this->helper->getOption('kill')) === 'yes' ? SIGKILL : SIGTERM;
        foreach ($ps as & $p) {
            $p['signal'] = 'kill -'.$signal;
            $this->println("信号 - 向{%s}进程发{%d}信号", $p['args'], $signal);
            XProcess::kill($p['pid'], $signal);
        }
        $this->printTable($ps);
    }

    /**
     * 按Master进程ID退出
     */
    private function byPid()
    {
        // 1. 计算PID
        $pid = $this->runningPid();
        if ($pid === 0) {
            $this->println("结果 - 未指定进程ID, 请通过'--pid=int'参数指定");
            return;
        }
        // 2. 列出进程列表
        $this->println("操作 - 列出主进程ID为{%s}的全部进程列表", $pid);
        $ps = $this->listProcessByPid($pid);
        // 3. 未发现进程
        if (count($ps) === 0) {
            $this->println("结果 - 未发现进程");
            return;
        }
        // 4. 打印进程列表
        $hasKill = $this->helper->hasOption('kill');
        if (!$hasKill) {
            $this->printTable($ps);
            $this->println("提示 - 请通过'--kill[=yes|no]'对进程操作");
            return;
        }
        // 5. 操作进程
        $signal = strtolower($this->helper->getOption('kill')) === 'yes' ? SIGKILL : SIGTERM;
        foreach ($ps as & $p) {
            $p['signal'] = 'kill -'.$signal;
            $this->println("信号 - 向{%s}进程发{%d}信号", $p['args'], $signal);
            XProcess::kill($p['pid'], $signal);
        }
        $this->printTable($ps);
    }

    /**
     * 按进程名列出进程
     * @param array $names
     * @return array
     */
    private function listProcessByName(array $names)
    {
        // 1. make command
        $cmd = "ps x -o ppid,pid,args | grep -v grep";
        foreach ($names as $name) {
            $name = trim($name);
            if ($name !== '') {
                $cmd .= " | grep '{$name}'";
            }
        }
        // 2. execute command
        $str = shell_exec($cmd);
        $psx = [];
        foreach (explode("\n", $str) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match("/\s+[\-]+(n|name)\s+/", $line) > 0 && preg_match("/\s+stop/", $line) > 0) {
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
        // 3. return process list
        ksort($psx);
        reset($psx);
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
