<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

use Uniondrug\Server2\Servers\IHttp;
use Uniondrug\Server2\Servers\ISocket;

/**
 * 启动服务
 * @package Uniondrug\Server2\Helpers
 */
class StartHelper extends Abstracts\Base implements IHelper
{
    public function beforeRun()
    {
        $this->merger();
    }

    /**
     * 启动服务
     */
    public function run()
    {
        $entrypoint = $this->builder->getEntrypoint();
        if (!$entrypoint || !is_a($entrypoint, IHttp::class, true)) {
            $this->console->error("server {%s} not implements {%s}", $entrypoint, IHttp::class);
            return;
        }
        /**
         * @var IHttp|ISocket $server
         */
        $server = new $entrypoint($this->console, $this->builder);
        $server->start();
    }

    /**
     * 帮助中心
     */
    public function runHelper()
    {
        // todo: help for start
        $this->console->debug("todo: %s", __METHOD__);
    }
}
