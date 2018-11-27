<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-27
 */
namespace Uniondrug\Server2\Servers\Phalcon\Traits;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Servers\Phalcon\PhalconHttp;
use Uniondrug\Server2\Servers\Phalcon\PhalconProcess;

/**
 * 刷新MySQL连接
 * @package Uniondrug\Server2\Servers\Phalcon\Traits
 */
trait MysqlTrait
{
    /**
     * 载入MySQL连接
     * @param PhalconHttp $server
     * @param Container   $container
     */
    public function loadMysqlConnection($server, $container)
    {
        $shareds = [
            'db',
            'dbSlave'
        ];
        foreach ($shareds as $shared) {
            $db = $container->getShared($shared);
            if (!($db instanceof Mysql)) {
                continue;
            }
            try {
                $db->query("SELECT 1");
            } catch(\Throwable $e) {
                $id = 0;
                $pid = 0;
                if ($server instanceof PhalconProcess) {
                    $pid = $server->pid;
                } else {
                    $id = $server->getWorkerId();
                    $pid = $server->getWorkerPid();
                }
                $server->getConsole()->warn("[@%d.%d]清除已断开的{%s}连接 - %s", $pid, $id, $shared, $e->getMessage());
                $container->removeSharedInstance($shared);
            }
        }
    }
}
