<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

/**
 * 操作Consul
 * @package Uniondrug\Server2\Helpers
 */
class ConsulHelper extends Abstracts\Base implements IHelper
{
    /**
     * 描述
     * @var string
     */
    protected static $description = "微服务管理";

    public function run()
    {
    }

    public function runHelper()
    {
        $this->console->debug("todo: %s", __METHOD__);
    }
}
