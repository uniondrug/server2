<?php
/**
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-12-04
 */
use Uniondrug\Server2\Builder;
use Uniondrug\Server2\Console;
use Uniondrug\Server2\Helper;

error_reporting(E_ALL);
// composer
$basePath = getcwd();
$vendorFile = $basePath."/vendor/autoload.php";
if (file_exists($vendorFile)) {
    include($vendorFile);
} else {
    echo "composer not installed.";
    exit(0);
}
// console
$console = new Console();
set_exception_handler(function(\Throwable $e) use ($console){
    $text = sprintf("%s at line %d of %s", $e->getMessage(), $e->getLine(), $e->getFile());
    $console->error($text);
    exit(1);
});
set_error_handler(function($errno, $error, $file, $line) use ($console){
    $text = sprintf("%s at line %d of %s", $error, $line, $file);
    switch ($errno) {
        case E_NOTICE :
        case E_USER_NOTICE :
            $console->notice($text);
            break;
        case E_CORE_WARNING :
        case E_USER_WARNING :
        case E_WARNING :
            $console->warning($text);
            break;
        default :
            $console->error($text);
            exit(1);
    }
});
$helper = new Helper();
$builder = Builder::withBasePath($helper, $basePath);
Helper::run($console, $helper, $builder);
//echo $basePath."\n";
//echo $helper->getScript()."\n";
