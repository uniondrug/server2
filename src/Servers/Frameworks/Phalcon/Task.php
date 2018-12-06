<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Frameworks\Phalcon;

use Uniondrug\Server2\Tasks\XTask;

/**
 * Phalcon模式下的Task任务
 * @package Uniondrug\Server2\Servers
 */
abstract class Task extends XTask
{
    public function beforeRun()
    {
        parent::beforeRun();
    }
}
