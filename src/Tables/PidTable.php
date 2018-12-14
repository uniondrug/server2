<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Tables;

/**
 * PidTable
 * @package Uniondrug\Server2\Tables
 */
class PidTable extends XTable
{
    const PID_MASTER = 1;
    const PID_MANAGER = 2;
    const PID_WORKER = 3;
    const PID_TASKER = 4;
    const PID_PROCESS = 5;
    /**
     * 进程角色定义
     * @var array
     */
    private static $pidRoles = [
        self::PID_MASTER => 'Master',
        self::PID_MANAGER => 'Manager',
        self::PID_WORKER => 'Worker',
        self::PID_TASKER => 'Tasker',
        self::PID_PROCESS => 'Process',
    ];
    /**
     * 内存表的列定义
     * @var array
     */
    protected $columns = [
        'ppid' => [
            XTable::TYPE_INT,
            4
        ],
        'pid' => [
            XTable::TYPE_INT,
            4
        ],
        'role' => [
            XTable::TYPE_STRING,
            32
        ],
        'onTask' => [
            XTable::TYPE_INT,
            4
        ],
        'onFinish' => [
            XTable::TYPE_INT,
            4
        ],
        'type' => [
            XTable::TYPE_INT,
            1
        ],
        'time' => [
            XTable::TYPE_STRING,
            20
        ],
        'name' => [
            XTable::TYPE_STRING,
            256
        ]
    ];
    protected $name = 'pidTable';

    /**
     * 加入Master进程
     * @param int    $pid
     * @param string $name
     * @return bool
     */
    public function addMaster(int $pid, string $name)
    {
        return $this->set($pid, [
            'pid' => $pid,
            'type' => self::PID_MASTER,
            'name' => $name
        ]);
    }

    /**
     * 加入Manager进程
     * @param int    $pid
     * @param string $name
     * @return bool
     */
    public function addManager(int $pid, string $name)
    {
        return $this->set($pid, [
            'pid' => $pid,
            'type' => self::PID_MANAGER,
            'name' => $name
        ]);
    }

    /**
     * 加入Worker进程
     * @param int    $pid
     * @param string $name
     * @return bool
     */
    public function addWorker(int $pid, string $name)
    {
        return $this->set($pid, [
            'pid' => $pid,
            'type' => self::PID_WORKER,
            'name' => $name
        ]);
    }

    /**
     * 加入Tasker进程
     * @param int    $pid
     * @param string $name
     * @return bool
     */
    public function addTasker(int $pid, string $name)
    {
        return $this->set($pid, [
            'pid' => $pid,
            'type' => self::PID_TASKER,
            'name' => $name
        ]);
    }

    /**
     * 加入Process进程
     * @param int    $pid
     * @param string $name
     * @return bool
     */
    public function addProcess(int $pid, string $name)
    {
        return $this->set($pid, [
            'pid' => $pid,
            'type' => self::PID_PROCESS,
            'name' => $name
        ]);
    }

    /**
     * 删除进程
     * @param int $pid 进程ID
     * @return bool
     */
    public function del($pid)
    {
        return parent::del($pid);
    }

    /**
     * 读取Process进程列表
     * @return array
     */
    public function getProcessPid()
    {
        return $this->filterByTypes([self::PID_PROCESS]);
    }

    /**
     * 读取Tasker进程列表
     * @return array
     */
    public function getTaskerPid()
    {
        return $this->filterByTypes([self::PID_TASKER]);
    }

    /**
     * 读取Worker进程列表
     * @return array
     */
    public function getWorkerPid()
    {
        return $this->filterByTypes([self::PID_WORKER]);
    }

    /**
     * 设置记录
     * @param int   $pid
     * @param array $data
     * @return bool
     */
    public function set($pid, array $data)
    {
        $data['ppid'] = posix_getppid();
        $data['onTask'] = 0;
        $data['onFinish'] = 0;
        $data['time'] = date('ymd/H:i');
        $data['role'] = self::$pidRoles[$data['type']];
        return parent::set($pid, $data);
    }

    /**
     * 按类型过滤
     * @param array $types 类型列表
     * @return array
     */
    private function filterByTypes(array $types = [])
    {
        $datas = [];
        $procs = $this->toArray();
        foreach ($procs as $proc) {
            if (in_array($proc['type'], $types)) {
                $datas[] = $proc;
            }
        }
        return $datas;
    }
}