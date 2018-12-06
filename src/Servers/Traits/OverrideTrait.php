<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Servers\Traits;

/**
 * 可覆盖方法
 * @package Uniondrug\Server2\Servers\Traits
 */
trait OverrideTrait
{
    /**
     * 前置前业务
     * 子类可覆盖本方法, 当返回true时继续启动, 反之
     * 取消启动
     * @return bool
     */
    protected function beforeStart()
    {
        return true;
    }
}
