<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Base;

use Swoole\Http\Server as SwooleHttp;
use Uniondrug\Server2\Base\Traits\Common;
use Uniondrug\Server2\Base\Traits\Construct;
use Uniondrug\Server2\Base\Traits\Properties;
use Uniondrug\Server2\Base\Traits\Task;
use Uniondrug\Server2\Base\Events\CloseEvent;
use Uniondrug\Server2\Base\Events\ManagerStartEvent;
use Uniondrug\Server2\Base\Events\ManagerStopEvent;
use Uniondrug\Server2\Base\Events\ShutdownEvent;
use Uniondrug\Server2\Base\Events\StartEvent;
use Uniondrug\Server2\Base\Events\WorkerStartEvent;
use Uniondrug\Server2\Base\Events\WorkerStopEvent;
use Uniondrug\Server2\Base\Events\Task\FinishEvent;
use Uniondrug\Server2\Base\Events\Task\PipeMessageEvent;
use Uniondrug\Server2\Base\Events\Task\TaskEvent;
use Uniondrug\Server2\Base\Events\Http\RequestEvent;

/**
 * Http服务基类
 * @package Uniondrug\Server2\Base
 */
abstract class Http extends SwooleHttp implements IHttp
{
    public $events = ['request'];
    use Properties, Construct, Common, Task;
    /**
     * 进程事件
     */
    use CloseEvent, ManagerStartEvent, ManagerStopEvent, ShutdownEvent, StartEvent, WorkerStartEvent, WorkerStopEvent;
    /**
     * Task事件
     */
    use FinishEvent, PipeMessageEvent, TaskEvent;
    /**
     * HTTP事件
     */
    use RequestEvent;
}
