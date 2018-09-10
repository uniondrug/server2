<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2\Servers\Traits;

use Throwable;
use Uniondrug\Framework\Container;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Exception;
use Uniondrug\Server2\Interfaces\IProcess;

/**
 * 公共逻辑
 * @package Uniondrug\Server2\Servers\Traits
 */
trait BaseTrait
{
    /**
     * 服务地址
     * 例如: 127.0.0.1:8080
     * @var string
     */
    public $address;
    /**
     * 应用名称
     * @var string
     */
    public $appName;
    /**
     * Phalcon注入容器
     * @var Container
     */
    public $container;
    /**
     * 控制吧实例
     * @var Console
     */
    public $console;
    /**
     * 默认TaskWorker编号
     * 执行task/pipe依赖
     * @var int
     */
    public static $defaultTaskWorkerId = 0;

    /**
     * 读取应用名称
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * 读取服务地址
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * 读取控制台实例
     * @return Console
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * 读取Phalcon容器
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * 读取Master进程PID
     * @return int
     */
    public function getMasterPid()
    {
        return isset($this->master_pid) ? $this->master_pid : -1;
    }

    /**
     * 读取Manager进程PID
     * @return int
     */
    public function getManagerPid()
    {
        return isset($this->manager_pid) ? $this->manager_pid : -1;
    }

    /**
     * 读取Worker进程ID
     * @return int
     */
    public function getWorkerId()
    {
        return isset($this->worker_id) ? $this->worker_id : -1;
    }

    /**
     * 读取Worker进程PID
     * @return int
     */
    public function getWorkerPid()
    {
        return isset($this->worker_pid) ? $this->worker_pid : -1;
    }

    /**
     * 运行一个Process子进程
     * @param string $class  IProgress/类名称
     * @param array  $params IProgress执行前, 调用configure()方法设为配置参数
     * @return int|false
     */
    public function runProcess(string $class, array $params = [])
    {
        if (!is_a($class, IProcess::class, true)) {
            $this->console->error("[无效进程] PHP类'%s'未实现'%s'接口", $class, IProcess::class);
            return false;
        }
        try {
            /**
             * @var \Swoole\Process|IProcess $proc
             */
            $proc = new $class($this->appName.' process');
            $proc->configure([
                'server' => $this,
                'params' => $params
            ]);
            $pid = $proc->start();
            if ($pid > 0) {
                return $pid;
            }
            throw new Exception("进程ID无效");
        } catch(Throwable $e) {
            $this->console->error("[执行进程] 执行'%s'进程失败 - %s", $class, $e->getMessage());
        }
        return false;
    }

    /**
     * 投递任务
     * 本方法与`task()`相同
     * @param string $class  ITask/任务类名
     * @param array  $params 传递给待执行的操作入参
     * @return bool
     */
    public function runTask(string $class, array $params = [])
    {
        return $this->task([
            'class' => $class,
            'params' => $params
        ], self::$defaultTaskWorkerId);
    }

    /**
     * 修改进程名称
     * 在部分系统(如: MAC)中不生效
     * @param string $name
     */
    public function setProcessName(string $name)
    {
        $fns = [
            'swoole_set_process_name',
            'cli_set_process_title',
            'setproctitle',
        ];
        foreach ($fns as $fn) {
            if (!function_exists($fn)) {
                continue;
            }
            try {
                $fn($name);
                continue;
            } catch(\Exception $e) {
            }
        }
    }

    /**
     * 投递任务
     * @link https://wiki.swoole.com/wiki/page/134.html
     * @param mixed $data        执行任务时传给任务的入参
     * @param int   $dstWorkerId 指定哪个TaskWorker进程执行任务
     * @param null  $callback    执行完成回调
     * @return bool
     */
    public function task($data, $dstWorkerId = -1, $callback = null)
    {
        try {
            return parent::task($data, $dstWorkerId, $callback) !== false;
        } catch(Throwable $e) {
            return $this->sendMessage(json_encode($data), $dstWorkerId);
        }
    }
}