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
    /**
     * 描述
     * @var string
     */
    protected static $description = "启动服务";
    /**
     * 选项
     * @var array
     */
    protected static $options = [
        [
            'name' => 'daemon',
            'short' => 'd',
            'desc' => 'run with daemonize mode'
        ],
        [
            'name' => 'env',
            'short' => 'e',
            'desc' => 'specify environment name, accepted: development, testing, release, production'
        ],
        [
            'name' => 'host',
            'short' => 'h',
            'desc' => 'specify an ip address, only work for IPv4'
        ],
        [
            'name' => 'ipaddr',
            'desc' => 'same as --host option'
        ],
        [
            'name' => 'port',
            'short' => 'p',
            'desc' => 'specify an listen port, eg: 8080'
        ]
    ];

    public function beforeRun()
    {
        $this->merger();
        putenv("APP_ENV={$this->builder->getEnvironment()}");
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
        $this->printOptions(self::$options);
    }
}
