<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-19
 */
namespace Uniondrug\Server2\Processes;

/**
 * PID健康检查
 * @package Uniondrug\Servx\Processes
 */
class PidProcess extends Process
{
    const PID_TIMER_FREQUENCE = 5000;

    /**
     * 执行过程
     * @return void
     */
    public function run()
    {
        swoole_timer_tick(self::PID_TIMER_FREQUENCE, [
            $this,
            'health'
        ]);
    }

    /**
     * 进程健康检查
     * @return void
     */
    public function health()
    {
        $procs = $this->server->getPidTable();
        foreach ($procs as $proc) {
            $this->healthPid($proc['pid'], $proc['name']);
        }
    }

    /**
     * 单个进程检查
     * @param int    $pid
     * @param string $name
     * @return void
     */
    public function healthPid(int $pid, string $name)
    {
        // 1. 进程OK
        if (process::kill($pid, 0)) {
            return;
        }
        // 2. 去除记录
        $this->server->getPidTable()->del($pid);
        $this->server->getConsole()->warn("[@%d]Clear{%d}Process{%s}", $this->pid, $pid, $name);
    }
}
