<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

interface IHelper
{
    /**
     * 读取Helper用途
     * @return string
     */
    public static function desc();

    public function run();

    public function runHelper();
}
