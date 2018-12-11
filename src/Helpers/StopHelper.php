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
    /**
     * 描述
     * @var string
     */
    protected static $description = "stop server";
    protected static $options = [
        [
            'name' => 'worker',
            'desc' => 'special the worker process and stop'
        ]
    ];

    public function run()
    {
        $this->request("PUT", "/stop");
    }

    public function runHelper()
    {
        $this->
    }
}
