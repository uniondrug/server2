<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

/**
 * 重载服务
 * @package Uniondrug\Server2\Helpers
 */
class ReloadallHelper extends Abstracts\Base implements IHelper
{
    /**
     * 描述
     * @var string
     */
    protected static $description = "reload all processes";

    public function run()
    {
        $this->request("PUT", "/reload");
    }

    public function runHelper()
    {
        // todo: help for stop
        $this->console->debug("todo: %s", __METHOD__);
    }
}
