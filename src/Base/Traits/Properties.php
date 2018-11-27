<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base\Traits;

use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Tables\PidTable;

/**
 * 属性定义
 * @package Uniondrug\Server2\Base\Traits
 */
trait Properties
{
    /**
     * Server入参
     * @var Builder
     */
    public $builder;
    /**
     * 控制台输出
     * @var Console
     */
    public $console;
    /**
     * 进程内存表
     * @var PidTable
     */
    public $pidTable;
    /**
     * 预定义事件
     * @var array
     */
    private $mergedEvents = [
        'finish',
        'managerStart',
        'managerStop',
        'pipeMessage',
        'shutdown',
        'start',
        'task',
        'workerStart',
        'workerStop'
    ];
}
