<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

/**
 * 停止服务
 * @package Uniondrug\Server2\Helpers
 */
class StopHelper extends Abstracts\Base implements IHelper
{
    public function run()
    {
        $this->request("PUT", "/stop");
    }

    public function runHelper()
    {
        // todo: help for stop
        $this->console->debug("todo: %s", __METHOD__);
    }
}
