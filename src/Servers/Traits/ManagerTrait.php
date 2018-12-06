<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Traits;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Uniondrug\Server2\Managers\IManager;

/**
 * 事件定义
 * @package Uniondrug\Server2\Servers\Traits
 */
trait ManagerTrait
{
    /**
     * 收到管理请求
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     */
    final public function onManagerRequest($request, $response)
    {
        $this->console->info("收到Manager请求");
        if (!isset($request->header, $request->header['manager-token']) || $request->header['manager-token'] !== $this->managerToken) {
            $response->status(400);
            $response->end("HTTP 400 Bad Request");
            return;
        }
        /**
         * @var IManager $manager
         */
        $uri = isset($request->server, $request->server['request_uri']) && is_string($request->server['request_uri']) ? $request->server['request_uri'] : null;
        if ($uri && preg_match("/([^\/]+)/", $uri, $m) > 0) {
            $class = "\\Uniondrug\\Server2\\Managers\\".ucfirst($m[1])."Manager";
            if (class_exists($class, true) && is_a($class, IManager::class, true)) {
                $this->console->debug("触发{%s}操作", $class);
                $manager = new $class($this);
                $data = $manager->run();
                $response->status(200);
                $response->header("content-type", "application/json");
                $response->end(json_encode($data, JSON_ERROR_NONE));
                return;
            }
        }
        $this->console->debug("管理请求限制{%s}操作", $uri);
        $response->status(403);
        $response->end("HTTP 403 Forbidden");
    }
}
