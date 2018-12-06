<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Traits;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

/**
 * HTTP请求日志
 * @package Uniondrug\Server2\Servers\Traits
 */
trait AccessTrait
{
    private $loggerPath;

    /**
     * 异步写入HTTP请求日志
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     * @param                $begin
     */
    protected function httpAccessLogger($server, $request, $response, $begin)
    {
        /**
         * @var int    $code      HTTP状态码
         * @var string $requestId 请求ID
         * @var string $time      记录时间
         * @var float  $duration  请求时长
         */
        $code = isset($response->statusCode) ? $response->statusCode : 200;
        $requestId = isset($request->requestId) ? $request->requestId : '';
        $time = (new \DateTime())->format('d/M/Y:H:i:s.u');
        $uri = sprintf("%s %d %s %s%s", $request->server['server_protocol'], $code, $request->server['request_method'], $request->header['host'], $request->server['request_uri']);
        $duration = sprintf("%.06f", microtime(true) - $begin);
        $userAgent = $request->header['user-agent'];
        $text = sprintf(            // 导出格式
            "%s|%f|%s|%s|%s",  //
            $time,                  // 第1列: 时间
            $duration,              // 第2列: 时长
            $requestId,             // 第3列: 请求标记
            $uri,                   // 第4列: 请求地址与方式
            $userAgent              // 第5列: 浏览器标记
        );
        try {
            $server->loggerPath = $server->builder->getBasePath()."/log/http";
            if (!is_dir($server->loggerPath)) {
                mkdir($server->loggerPath, 0777, true);
            }
            swoole_async_write($server->loggerPath.'/'.date('Y-m-d').'.log', $text."\n");
        } catch(\Throwable $e) {
            $server->console->error("%s", $e->getMessage());
        }
    }
}
