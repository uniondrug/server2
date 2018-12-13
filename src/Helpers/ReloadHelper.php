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
class ReloadHelper extends Abstracts\Base implements IHelper
{
    /**
     * 描述
     * @var string
     */
    protected static $description = "退出Tasker/Worker进程, 然后启动";

    /**
     * 发起Reload请求
     */
    public function run()
    {
        $this->println("操作 - 发起Worker/Tasker进程重启请求");
        $response = $this->request("PUT", "/reload");
        if (false === $response) {
            return;
        }
        $this->println("完成");
    }
}
