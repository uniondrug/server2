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
         */
        $iserver = new static($host, $port, $mode, $sock);
        $iserver->appName = $name;
        $iserver->address = "{$host}:{$port}";
        $iserver->container = $container;
        $iserver->console = new Console();
        $iserver->console->debug("[服务地址] {$iserver->address}");
        /**
         * instance configurations
         */
        if (is_array($conf)) {
            $iserver->set($conf);
            foreach ($conf as $key => $value) {
                $iserver->console->debug("[配置参数] 字段'{$key}'分配'{$value}'值");
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
                $iserver->console->debug("[绑定事件] 事件'{$event}'绑定到'{$eventMethod}'方法");
            } else {
                $iserver->console->warning("[无效事件] 方法'{$eventMethod}'未定义, 事件'{$event}'被忽略");
            }
        }
        return $iserver;
    }

    /**
     * 向指定WebSocket连接发消息
     * @param int          $fd
     * @param array|string $data
     * @param bool         $binary
     * @param bool         $finish
     * @return bool
     */
    public function push($fd, $data, $binary = false, $finish = true)
    {
        $data = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
        return parent::push($fd, $data, $binary, $finish);
    }
}
