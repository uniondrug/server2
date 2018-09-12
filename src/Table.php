<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-09-05
 */
namespace Uniondrug\Server2;

use Swoole\Table as SwooleTable;
use Throwable;
use Uniondrug\Server2\Interfaces\ITable;

/**
 * Table
 * @package Uniondrug\Server2
 */
abstract class Table extends SwooleTable implements ITable
{
    /**
     * 内存表字段
     * @var array
     */
    public $columns = [];

    /**
     * 初始化内存表
     * @param int $size 最大记录数
     * @return static
     * @throws Exception
     */
    public static function setup(int $size)
    {
        $table = new static($size);
        foreach ($table->columns as $field => $conf) {
            $table->column($field, $conf[0], $conf[1]);
        }
        try {
            if ($table->create()) {
                return $table;
            }
        } catch(Throwable $e) {
        }
        throw new Exception("TABLE: create memory table failure");
    }
}
