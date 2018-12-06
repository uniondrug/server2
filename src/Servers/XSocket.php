<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers;

use Swoole\WebSocket\Server as SwooleWebSocketServer;
use Uniondrug\Server2\Servers\Calls\CommonTrait;
use Uniondrug\Server2\Servers\Calls\DoTrait;
use Uniondrug\Server2\Servers\Traits\AccessTrait;
use Uniondrug\Server2\Servers\Traits\ConstractTrait;
use Uniondrug\Server2\Servers\Traits\EventsTrait;
use Uniondrug\Server2\Servers\Traits\ManagerTrait;
use Uniondrug\Server2\Servers\Traits\OverrideTrait;

/**
 * XSocket/WebSocket基类
 * @package Uniondrug\Server2\Servers
 */
class XSocket extends SwooleWebSocketServer implements ISocket
{
    use ConstractTrait, EventsTrait, ManagerTrait, OverrideTrait, AccessTrait;
    use CommonTrait, DoTrait;
}
