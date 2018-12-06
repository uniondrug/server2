<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Tables;

interface ITable
{
    /**
     * 读取表名
     * @return string
     */
    public function getName();

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
}
