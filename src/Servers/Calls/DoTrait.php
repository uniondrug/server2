<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Calls;

use Uniondrug\Server2\Servers\IHttp;
use Uniondrug\Server2\Servers\ISocket;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\WebSocket\Frame as SwooleFrame;
use Uniondrug\Server2\Tasks\ITask;

trait DoTrait
{
    /**
     * Task完成后回调
     * @param IHttp|ISocket $server
     * @param               $data
     */
    public function doFinish($server, $taskId, $data)
    {
    }

    /**
     * Manager进程启动回调
     * @param IHttp|ISocket $server
     */
    public function doManagerStart($server)
    {
    }

    /**
     * Manager进程退出回调
     * @param IHttp|ISocket $server
     */
    public function doManagerStop($server)
    {
    }

    /**
     * 收到Message消息
     * @param ISocket     $server
     * @param SwooleFrame $frame
     */
    public function doMessage($server, $frame)
    {
    }

    /**
     * 收到HTTP请求
     * @param SwooleRequest  $request
     * @param SwooleResponse $response
     */
    public function doRequest($request, $response)
    {
        $response->status(405);
        $response->end("method ".get_class($this)."::doRequest() not overrided.");
    }

    /**
     * Master进程退出回调
     * @param IHttp|ISocket $server
     */
    public function doShutdown($server)
    {
    }

    /**
     * Master进程启动回调
     * @param IHttp|ISocket $server
     */
    public function doStart($server)
    {
    }

    /**
     * 执行Task任务
     * @param IHttp|ISocket $server
     * @param int           $taskId
     * @param string        $data
     * @return mixed
     * @throws \Exception
     */
    public function doTask($server, $taskId, $data)
    {
        // 1. 解析JSON
        $json = json_decode($data, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception("Task数据反解析JSON失败 - ".json_last_error_msg());
        }
        // 2. 必须字段
        $json['class'] = isset($json['class']) && is_string($json['class']) && $json['class'] !== '' ? $json['class'] : null;
        $json['params'] = isset($json['params']) && is_array($json['params']) ? $json['params'] : [];
        if ($json['class'] === null) {
            throw new \Exception("未定义TASK回调");
        }
        // 3. 接口实现
        if (!is_a($json['class'], ITask::class, true)) {
            throw new \Exception(sprintf("Task回调{%s}未实现{%s}接口", $json['class'], ITask::class));
        }
        /**
         * 4. 执行Task
         * @var ITask $task
         */
        $task = new $json['class']($server, $taskId, $json['params']);
        if ($task->beforeRun()) {
            $result = $task->run();
            $task->afterRun($result);
            return $result;
        }
        return false;
    }

    /**
     * Worker进程错误回调
     * @param IHttp|ISocket $server
     * @param int           $errno
     * @param int           $signal
     */
    public function doWorkerError($server, int $errno, int $signal)
    {
        $server->getConsole()->error("Worker进程意外退出{errno=%d}/{signal=%d}", $errno, $signal);
    }

    /**
     * Worker进程启动回调
     * @param IHttp|ISocket $server
     */
    public function doWorkerStart($server)
    {
    }

    /**
     * Worker进程退出回调
     * @param IHttp|ISocket $server
     */
    public function doWorkerStop($server)
    {
    }
}
