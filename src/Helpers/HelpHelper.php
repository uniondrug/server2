<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

/**
 * 帮助中心
 * @package Uniondrug\Server2\Helpers
 */
class HelpHelper extends Abstracts\Base implements IHelper
{
    /**
     * Helper主入口
     */
    public function run()
    {
        $this->runHelper();
    }

    /**
     * Helper帮助
     */
    public function runHelper()
    {
        echo "Usage: \n";
        echo "Commands: \n";
    }
}
