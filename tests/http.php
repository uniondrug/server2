<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-11-21
 */
use Uniondrug\Server2\Args;
use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Clients\Client;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Servers\Phalcon\PhalconHttp;

$basePath = realpath(__DIR__.'/../../../../');
$environment = 'testing';
include "{$basePath}/vendor/autoload.php";
$console = new Console();
set_exception_handler(function(\Throwable $e) use ($console){
    $console->error($e->getMessage());
    exit(0);
});
set_error_handler(function($errno, $error, $file, $line, ... $_) use ($console){
    $console->error($error);
    exit(0);
});

$builder = Builder::withPath($basePath, $environment);
$builder->setEntrypoint(PhalconHttp::class);
$client = new Client($builder, new Args());
$client->start();

