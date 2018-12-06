<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

/**
 * 查询Server状态与统计
 * @package Uniondrug\Server2\Helpers
 */
class StatusHelper extends Abstracts\Base implements IHelper
{
    public function run()
    {
        $this->console->info("请求{%s}环境的{%s}服务状态", $this->builder->getEnvironment(), $this->builder->getAppName());
        $result = $this->request("GET", "/status");
        if ($result === false) {
            return;
        }
        if (isset($result['stats']) && is_array($result['stats'])) {
            $this->console->debug("服务状态");
            $this->printStats($result['stats']);
        }
        if (isset($result['process']) && is_array($result['process'])) {
            $this->console->debug("进程列表");
            $this->printTable($result['process']);
        }
    }

    public function runHelper()
    {
        $this->console->debug("todo: %s", __METHOD__);
    }
}
