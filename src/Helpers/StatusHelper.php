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
    /**
     * 描述
     * @var string
     */
    protected static $description = "show server status and stats";
    /**
     * 选项
     * @var array
     */
    protected static $options = [];

    /**
     * 运行过程
     */
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
        if (isset($result['tables']) && is_array($result['tables'])) {
            foreach ($result['tables'] as $name => $data) {
                $this->console->debug("内存{%s}表", $name);
                $this->printTable($data);
            }
        }
    }

    /**
     * 打印帮助选项
     */
    public function runHelper()
    {
        $this->printOptions(self::$options);
    }

    private function byForce()
    {
    }
}
