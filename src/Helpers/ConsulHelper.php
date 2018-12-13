<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

/**
 * 操作Consul
 * @package Uniondrug\Server2\Helpers
 */
class ConsulHelper extends Abstracts\Base implements IHelper
{
    /**
     * 描述
     * @var string
     */
    protected static $description = "微服务管理";
    protected static $options = [
        [
            'name' => 'register',
            'desc' => '注册到Consul服务中心'
        ],
        [
            'name' => 'deregister',
            'desc' => '删除已注册服务'
        ],
        [
            'name' => 'health',
            'desc' => '健康检查'
        ]
    ];

    public function beforeRun()
    {
        parent::beforeRun();
        $this->println("服务 - %s", $this->builder->getAppName());
        $this->println("版本 - %s", $this->builder->getAppVersion());
        $this->println("地址 - %s", $this->builder->getAddr());
    }

    public function run()
    {
        $input = ['op' => ''];
        if ($this->helper->hasOption('register')) {
            $input['op'] = 'register';
            $this->println("操作 - 注册到Consul服务中心");
        } else if ($this->helper->hasOption('deregister')) {
            $input['op'] = 'deregister';
            $this->println("操作 - 取消服务中心");
        } else if ($this->helper->hasOption('health')) {
            $this->println("操作 - 健康检查");
            $input['op'] = 'health';
        }
        $result = $this->request("PUT", "/consul", $input);
        if ($result === false) {
            return;
        }
        is_array($result) && $this->printStats($result);
    }

    public function runHelper()
    {
        $this->printOptions(self::$options);
    }
}
