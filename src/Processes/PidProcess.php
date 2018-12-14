<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Processes;

/**
 * Process基类
 * @package Uniondrug\Server2\Processes
 */
class PidProcess extends XProcess
{
    /**
     * Process主逻辑
     */
    public function run()
    {
        // 1. 每3秒清理一次已退出进程
        $this->server->tick(3000, function(){
            $this->removeKilledProcesses();
        });
    }

    /**
     * 从内存表去除Killed进程
     */
    private function removeKilledProcesses()
    {
        $ps = $this->server->getPidTable()->toArray();
        foreach ($ps as $p) {
            $alive = parent::kill($p['pid'], 0);
            if ($alive === false) {
                $this->server->getConsole()->warning("清除{%d}已退出进程{%s}", $p['pid'], $p['name']);
                $this->server->getPidTable()->del($p['pid']);
            }
        }
    }
}
