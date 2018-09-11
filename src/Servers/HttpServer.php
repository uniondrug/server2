<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Servers;

use Phalcon\Di;
use swoole_http_server;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Interfaces\IServer;
use Uniondrug\Server2\Servers\Traits\BaseTrait;
use Uniondrug\Server2\Servers\Traits\EventsTrait;

/**
 * HTTP服务
 * @package Uniondrug\Server2\Servers
 */
abstract class HttpServer extends swoole_http_server implements IServer
{
    const SERVER_DEFAULT_HOST = '0.0.0.0';
    const SERVER_DEFAULT_PORT = 8080;
    /**
     * load traits
     */
    use BaseTrait, EventsTrait;

    /**
     * 创建服务实例待启动
     * @param string $name    eg. application
     * @param string $address eg. 127.0.0.1:8080
     * @param array  $conf
     * @return IServer
     */
    public static function createServer($name, $address, $conf = [])
    {
        /**
         * 参数初始化
         * @var Container $container
         */
        $container = Di::getDefault();
        $host = self::SERVER_DEFAULT_HOST;
        $port = self::SERVER_DEFAULT_PORT;
        $mode = $container->getConfig()->path('server.settings.startMode', SWOOLE_PROCESS);
        $sock = $container->getConfig()->path('server.settings.startSockType', SWOOLE_SOCK_TCP);
        if (preg_match("/([0-9\.]+):(\d+)$/", $address, $m) > 0) {
            $host = $m[1];
            $port = $m[2];
        }
        /**
         * 创建实例
         */
        $iserver = new static($host, $port, $mode, $sock);
        $iserver->setAppName($name);
        $iserver->setAddress("{$host}:{$port}");
        $iserver->setContainer($container);
        $iserver->setConsole(new Console());
        $iserver->getConsole()->debug("[服务地址] {$iserver->address}");
        /**
         * instance configurations
         */
        if (is_array($conf)) {
            $iserver->set($conf);
            foreach ($conf as $key => $value) {
                $iserver->getConsole()->debug("[配置参数] 字段'{$key}'分配'{$value}'值");
            }
        }
        /**
         * register events
         */
        foreach ($iserver->events as $event) {
            $eventMethod = 'on'.ucfirst($event);
            if (method_exists($iserver, $eventMethod)) {
                $iserver->on($event, [
                    $iserver,
                    $eventMethod
                ]);
                $iserver->getConsole()->debug("[绑定事件] 事件'{$event}'绑定到'{$eventMethod}'方法");
            } else {
                $iserver->getConsole()->warning("[无效事件] 方法'{$eventMethod}'未定义, 事件'{$event}'被忽略");
            }
        }
        return $iserver;
    }
}
