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
        $this->getServer()->getConsole()->debug("[task:run][%s] 刷新%s连接", __CLASS__, "MySQL");
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
            return true;
        } catch(\Exception $e) {
            $this->getServer()->getContainer()->remove($db);
            $this->getServer()->getConsole()->error("[task:failure][%s] 刷新%s失败 - %s.", __CLASS__, "MySQL", $e->getMessage());
            return false;
        }
    }
}
