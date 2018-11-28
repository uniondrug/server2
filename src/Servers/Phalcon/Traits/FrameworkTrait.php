<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-27
 */
namespace Uniondrug\Server2\Servers\Phalcon\Traits;

//use Uniondrug\Framework\Application;
//use Uniondrug\Framework\Container;
//use Uniondrug\Server2\Servers\Phalcon\PhalconHttp;
//use Uniondrug\Server2\Servers\Phalcon\PhalconProcess;

/**
 * 加载Phalcon框架
 * @package Uniondrug\Server2\Servers\Phalcon\Traits
 */
trait FrameworkTrait
{
//    /**
//     * 载入载架
//     * @param PhalconHttp|PhalconProcess $src
//     */
//    public function loadFramework($src)
//    {
//        // 1. server validation
//        $id = 0;
//        $pid = 0;
//        $server = null;
//        if ($src instanceof PhalconProcess) {
//            $pid = $src->pid;
//            $server = $src->server;
//        } else if ($src instanceof PhalconHttp) {
//            $id = $src->getWorkerId();
//            $pid = $src->getWorkerPid();
//            $server = $src;
//        }
//        if ($server === null){
//            return;
//        }
//        // 2. 重复载入
//        if ($src->container instanceof Container) {
//            $server->getConsole()->warn("[@%d.%d][phalcon]Framework重复载入", $pid, $id);
//            return;
//        }
//        // 3. 执行载入
//        try {
//            $src->container = new Container($server->getBuilder()->getBasePath());
//            $src->application = new Application($src->container);
//            $src->application->boot();
//            // shared
//            $src->container->setShared('server', $server);
//            $server->console->setContainer($src->container);
//            // prepare
//            $_GET = $_POST = $_SERVER = $_REQUEST = $_FILES = [];
//            $server->getConsole()->debug("[@%d.%d][phalcon]初始化Framework", $pid, $id);
//        } catch(\Throwable $e) {
//            $server->getConsole()->error("[@%d.%d][phalcon]初始化Framework失败 - %s", $pid, $id, $e->getMessage()."\n".$e->getFile()."::".$e->getLine());
//            return;
//        }
//    }
}
