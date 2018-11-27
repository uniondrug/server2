<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Managers;

use Uniondrug\Server2\Base\IHttp;
use Uniondrug\Server2\Base\ISocket;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * 服务管理入口
 * @package Uniondrug\Server2\Managers
 */
class Manager
{
    /**
     * @var SwooleRequest
     */
    public $request;
    /**
     * @var SwooleResponse
     */
    public $response;
    /**
     * @var IHttp|ISocket
     */
    public $server;

    /**
     * Manager constructor.
     * @param IHttp|ISocket $server
     */
    public function __construct($server)
    {
        $this->server = $server;
    }

    /**
     * 收到HTTP请求
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     */
    final public function run($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        // 1. not validated URI
        $uri = $request->server['request_uri'];
        if (preg_match("/\.[\w]+$/", $uri) > 0) {
            $this->response->status(401);
            $this->response->end("Unauthorized");
            return;
        }
        // 2. class
        $class = '\\Uniondrug\\Server2\\Managers\\Agents'.preg_replace_callback("/[\/]+([^\/]+)/", function($a){
                return '\\'.ucfirst($a[1]);
            }, $uri).'Agent';
        // 3. existed or not
        try {
            $client = new $class($this->server);
            $this->server->getConsole()->warn("[@%d.%d]收到管理请求{%s}", $this->server->getWorkerPid(), $this->server->getWorkerId(), $class);
            $result = $client->run();
            $this->response->status(200);
            $this->response->header('content-type', 'application/json');
            $this->response->end(json_encode($result, JSON_UNESCAPED_UNICODE));
        } catch(\Throwable $e) {
            $this->response->status(403);
            $this->response->end("Forbidden");
            return;
        }
    }
}
