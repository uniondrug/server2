<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Servers\Phalcon;

use Uniondrug\Server2\Servers\Phalcon\Traits\MysqlTrait;
use Uniondrug\Server2\Servers\Phalcon\Traits\RedisTrait;
use Uniondrug\Server2\Tasks\Task;

/**
 * 基于Phalcon的Task基类
 * @package Uniondrug\Server2\Servers\Phalcon
 */
abstract class PhalconTask extends Task
{
    use MysqlTrait, RedisTrait;

    public function beforeRun()
    {
        $result = parent::beforeRun();
        if ($result === true) {
            $this->loadMysqlConnection($this->server);
            $this->loadRedisConnection($this->server);
        }
        return $result;
    }
}
