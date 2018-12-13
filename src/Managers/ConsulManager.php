<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-05
 */
namespace Uniondrug\Server2\Managers;

/**
 * 服务注册
 * @package Uniondrug\Server2\Managers
 */
class ConsulManager extends Abstracts\Manager
{
    /**
     * 向Consul发起服务注册
     * @return array
     * @throws \Exception
     */
    public function run()
    {
        $raw = $this->request->rawContent();
        $data = json_decode($raw, true);
        $data['op'] = isset($data['op']) ? $data['op'] : null;
        switch ($data['op']) {
            case 'register' :
                return $this->runRegister();
                break;
            case 'deregister' :
                return $this->runDeregister();
                break;
            case 'health' :
                return $this->runHealth();
                break;
            default :
                throw new \Exception("unknown operation");
                break;
        }
    }

    /**
     * 注册服务
     * @return array
     */
    private function runRegister()
    {
        return $this->server->stats();
    }

    /**
     * 取消注册
     * @return array
     */
    private function runDeregister()
    {
        return $this->server->stats();
    }

    /**
     * 健康检查
     * @return array
     */
    private function runHealth()
    {
        return $this->server->stats();
    }
}
