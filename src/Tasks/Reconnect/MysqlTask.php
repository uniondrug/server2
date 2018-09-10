<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-06
 */
namespace Uniondrug\Server2\Tasks\Reconnect;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Uniondrug\Server2\Task;

/**
 * Reconnect MySQL
 * @package Uniondrug\Server2\Tasks\Reconnect
 */
class MysqlTask extends Task
{
    /**
     * run task progress
     * @param array $data
     */
    public function run(array $data)
    {
        $this->getServer()->getConsole()->debug("[刷新连接] 刷新[%s]连接 - %s", "MySQL", __CLASS__);
        // 1. no shared db instance
        $this->checkInstance('db');
    }

    private function checkInstance(string $name)
    {
        // 1. no instance
        if (!$this->getServer()->getContainer()->has($name)) {
            return false;
        }
        // 2. use instance
        /**
         * @var Mysql $db
         */
        $db = $this->getServer()->getContainer()->get($name);
        try {
            $db->query("SELECT 1");
        } catch(\Exception $e) {
            $this->getServer()->getConsole()->error("[刷新连接] 去除错误连接 - %s", $e->getMessage());
            $this->getServer()->getContainer()->remove($db);
        }
    }
}
