<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-09
 */
namespace Uniondrug\Server2\Servers\Frameworks\Phalcon;

use Uniondrug\Framework\Request as FrameworkRequest;
use Swoole\Http\Request as SwooleRequest;

/**
 * Request
 * @package Uniondrug\Server2\Servers\Frameworks\Phalcon
 */
class Request extends FrameworkRequest
{
    /**
     * 请求ID
     * @var string
     */
    public $requestId;

    public function initialize(SwooleRequest $request)
    {
        // 1. Super/GPC
        $_GET = isset($request->get) && is_array($request->get) ? $request->get : [];
        $_POST = isset($request->post) && is_array($request->post) ? $request->post : [];
        $_COOKIE = isset($request->cookie) && is_array($request->cookie) ? $request->cookie : [];
        // 2.
        $_FILES = isset($request->files) && is_array($request->files) ? $request->files : [];
        // 3. Super/S
        $header = isset($request->header) && is_array($request->header) ? $request->header : [];
        $server = isset($request->server) && is_array($request->server) ? $request->server : [];
        $_SERVER = [];
        foreach (array_merge($header, $server) as $key => $value) {
            try {
                $_SERVER[strtoupper($key)] = $value;
            } catch(\Throwable $e) {
            }
        }
        // 3. rawContents
        $this->_rawBody = $request->rawContent();
        // 4. Request ID
        $this->requestId = $request->requestId;
    }
}
