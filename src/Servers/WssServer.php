<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Servers;

use Phalcon\Di;
use swoole_websocket_server;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Interfaces\IServer;
use Uniondrug\Server2\Interfaces\ISocket;
use Uniondrug\Server2\Servers\Traits\BaseTrait;
use Uniondrug\Server2\Servers\Traits\EventsTrait;

/**
 * WebSocket服务
 * @package Uniondrug\Server2\Servers
 */
abstract class WssServer extends swoole_websocket_server implements ISocket
{
    const SERVER_DEFAULT_HOST = '0.0.0.0';
    const SERVER_DEFAULT_PORT = 8080;
    /**
     * load traits
     */
    use BaseTrait, EventsTrait;

    /**
     * 创建WebSocket实例
     * @param string $name
     * @param string $address
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
            $port = (int) $m[2];
        }
        /**
         * 指定IP
         */
        if (isset($_SERVER['argv']) && is_array($_SERVER['argv'])) {
            $rexp = "/^[\-]+(ipaddr|i)=([0-9\.]+)[:]?(\d*)$/i";
            foreach ($_SERVER['argv'] as $v) {
                if (preg_match($rexp, trim($v), $x)) {
                    $host = $x[2];
                    if ($x[3] !== '') {
                        $port = (int) $x[3];
                    }
                }
            }
        }
        /**
         * 创建实例
         * @var ISocket $iserver
         */
        $iserver = new static($host, $port, $mode, $sock);
        $iserver->setAppName($name);
        $iserver->setAddress("{$host}:{$port}");
        $iserver->setContainer($container);
        $iserver->setConsole(new Console($iserver, "{$host}:{$port}"));
        $iserver->getConsole()->debug("[server:address] 监听{$iserver->address}地址");
        /**
         * instance configurations
         */
        if (is_array($conf)) {
            $iserver->set($conf);
            foreach ($conf as $key => $value) {
                $iserver->getConsole()->debug("[server:config] 配置[%s]字段值为[%s]", $key, $value);
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
                $iserver->getConsole()->debug("[server:event] 绑定[%s]事件到[%s]方法", $event, $eventMethod);
            } else {
                $iserver->getConsole()->error("[server:error] 事件[%s]未定义[%s]方法", $event, $eventMethod);
            }
        }
        return $iserver;
    }
}
