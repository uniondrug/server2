<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
namespace Uniondrug\Server2\Helpers;

/**
 * 帮助中心
 * @package Uniondrug\Server2\Helpers
 */
class HelpHelper extends Abstracts\Base implements IHelper
{
    protected static $description = "show help center";

    /**
     * Helper主入口
     */
    public function run()
    {
        $this->runHelper();
    }

    /**
     * Helper帮助
     */
    public function runHelper()
    {
        $commands = $this->findCommands();
        $this->printCommands($commands);
    }

    /**
     * 通过反射找到Command
     * @return array
     */
    private function findCommands()
    {
        $data = [];
        $path = dir(__DIR__);
        while (false !== ($entry = $path->read())) {
            if (preg_match("/^([A-Z][a-zA-Z0-9]*)Helper\.php/", $entry, $m)) {
                /**
                 * @var IHelper $class
                 */
                $class = "\\Uniondrug\\Server2\\Helpers\\{$m[1]}Helper";
                if (is_a($class, IHelper::class, true) && class_exists($class)) {
                    $key = lcfirst($m[1]);
                    $data[$key] = [
                        'name' => $key,
                        'desc' => $class::desc()
                    ];
                }
            }
        }
        $path->close();
        return $data;
    }
}
