<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-05
 */
namespace Uniondrug\Server2\Managers\Abstracts;

use Uniondrug\Server2\Managers\IManager;
use Uniondrug\Server2\Servers\XHttp;
use Uniondrug\Server2\Servers\XSocket;

abstract class Manager implements IManager
{
    /**
     * @var XHttp|XSocket
     */
    protected $server;

    /**
     * Manager constructor.
     * @param XHttp|XSocket $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }
}
