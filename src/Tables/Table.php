<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Tables;

use Swoole\Table as SwooleTable;

/**
 * Table基类
 * @package Uniondrug\Server2\Tables
 */
abstract class Table extends SwooleTable implements ITable
{
    /**
     * 内存表的列定义
     * @var array
     */
    protected $columns = [];

    /**
     * @param int $size
     */
    public function __construct($size)
    {
        parent::__construct($size);
        foreach ($this->columns as $name => $opts) {
            $this->column($name, $opts[0], $opts[1]);
        }
    }

    /**
     * 内存数据转数组
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this as $tmp) {
            $data[] = $tmp;
        }
        return $data;
    }

    /**
     * 内存数据转JSON字符串
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), true);
    }

    /**
     * 初始化内存表
     * @param int $size
     * @return static
     * @throws \Exception
     */
    final public static function setup(int $size)
    {
        $table = new static($size);
        if ($table->create()) {
            return $table;
        }
        throw new \Exception("create memory table failure");
    }
}

