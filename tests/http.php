<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */

$basePath = realpath(__DIR__.'/../../../../');

require_once $basePath.'/vendor/autoload.php';

$console = new \Uniondrug\Server2\Console();
$helper = new \Uniondrug\Server2\Helper();
$builder = \Uniondrug\Server2\Builder::withBasePath($helper, $basePath);
\Uniondrug\Server2\Helper::run($console, $helper, $builder);
