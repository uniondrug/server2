<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

/**
 * 重启服务
 * 1. 先退出
 * 2. 后启动
 * @package Uniondrug\Server2\Helpers
 */
class RestartHelper extends Abstracts\Base implements IHelper
{
    /**
     * 描述
     * @var string
     */
    protected static $description = "先退出服务再启动";

    /**
     * 发起Reload请求
     */
    public function run()
    {
        $this->runStop();
        $this->runStart();
    }

    private function runStop()
    {
        $this->helper->setCommand('stop');
        $this->helper->setOption('mode', 'pid');
        $this->helper->setOption('kill', 'yes');
        $stop = new StopHelper($this->console, $this->helper, $this->builder);
        $stop->run();
    }

    private function runStart()
    {
        $this->helper->setCommand('stop');
        $this->helper->setOption('daemon', true);
        $this->helper->unsetOption('mode', 'kill');
        $start = new StartHelper($this->console, $this->helper, $this->builder);
        $start->run();
    }
}
