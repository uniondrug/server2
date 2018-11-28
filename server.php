<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
use Uniondrug\Server2\Args;
use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Clients\Client;

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');
// 1. vendor
//$basePath = realpath(__DIR__.'/../../../');
$basePath = getcwd();
include "{$basePath}/vendor/autoload.php";
// 2. console & error
$console = new Console();
set_exception_handler(function(\Throwable $e) use ($console){
    $console->error($e->getMessage());
    $console->debug("%s (%d)\n%s", $e->getFile(), $e->getLine(), $e->getTraceAsString());
    exit(0);
});
set_error_handler(function($errno, $error, $file, $line, ... $_) use ($console){
    $console->error($error);
    exit(0);
});
// 3. argument explain
$args = new Args();
// 4. builder
$builder = Builder::withPath($basePath, $args->getEnvironment());
// 5. client
$args->setBuilder($builder);
$client = new Client($builder, $args);
$client->run();
