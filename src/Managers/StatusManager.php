<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-05
 */
namespace Uniondrug\Server2\Managers;

use Uniondrug\Server2\Tables\ITable;

/**
 * 列出服务状态
 * @package Uniondrug\Server2\Managers
 */
class StatusManager extends Abstracts\Manager
{
    /**
     * 列出服务状态
     * @return array
     */
    public function run()
    {
        $result = [
            'stats' => $this->server->stats(),
            'tables' => []
        ];
        /**
         * @var array $tables
         * @var ITable $table
         */
        $tables = $this->server->getTables();
        foreach ($tables as $name => $table) {
            $result['tables'][$name] = $table->toArray();
        }
        return $result;
    }
}
