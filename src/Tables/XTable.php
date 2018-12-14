<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Tables;

use Swoole\Table as SwooleTable;

/**
 * 内存表基类
 * @package Uniondrug\Server2\Tables
 */
abstract class XTable extends SwooleTable implements ITable
{
    /**
     * 内存表列定义
     * <code>
     * $columns = [
     *     'name' => [
     *         parent::TYPE_INT,
     *         4
     *     ]
     * ]
     * </code>
     * @var array
     */
    protected $columns = [];
    /**
     * 内存表命名
     * @var string
     */
    protected $name;

    /**
     * XTable constructor.
     * @param int $size 内存表最大空间
     */
    public function __construct($size)
    {
        parent::__construct($size);
        foreach ($this->columns as $name => $opts) {
            $this->column($name, $opts[0], $opts[1]);
        }
        $this->create();
    }

    /**
     * 内存表命名
     * 本方法可以子类中覆盖, 重新自定义名称, 当未定义时
     * 则通过计算类名的方式, 生成名称
     * @return string
     */
    public function getName()
    {
        if ($this->name === null) {
            $name = get_class($this);
            if (preg_match("/.*\\\([_a-zA-Z0-9]+)$/", $name, $m) > 0) {
                $name = $m[1];
            }
            $this->name = lcfirst($name);
        }
        return $this->name;
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
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
