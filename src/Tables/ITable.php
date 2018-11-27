<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
namespace Uniondrug\Server2\Tables;

/**
 * Table接口
 * @package Uniondrug\Server2\Tables
 */
interface ITable
{
    /**
     * 内存数据转数组
     * @return array
     */
    public function toArray();

    /**
     * 内存数据转JSON字符串
     * @return string
     */
    public function toJson();

    /**
     * 安装内存表
     * @param int $size
     * @return mixed
     */
    public static function setup(int $size);
}