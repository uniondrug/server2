<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers;

use Swoole\Http\Server as SwooleHttpServer;
use Uniondrug\Server2\Servers\Calls\CommonTrait;
use Uniondrug\Server2\Servers\Calls\DoTrait;
use Uniondrug\Server2\Servers\Traits\AccessTrait;
use Uniondrug\Server2\Servers\Traits\ConstractTrait;
use Uniondrug\Server2\Servers\Traits\EventsTrait;
use Uniondrug\Server2\Servers\Traits\ManagerTrait;
use Uniondrug\Server2\Servers\Traits\OverrideTrait;

/**
 * XHttp/HTTP基类
 * @package Uniondrug\Server2\Servers
 */
class XHttp extends SwooleHttpServer implements IHttp
{
    use ConstractTrait, EventsTrait, ManagerTrait, OverrideTrait, AccessTrait;
    use CommonTrait, DoTrait;
}
