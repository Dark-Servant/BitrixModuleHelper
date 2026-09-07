<?php

use DarkServant\BitrixModuleHelpers\Commands\NewModule;
use Symfony\Component\Console\Application;

if (!file_exists('./bitrix/modules/main/bx_root.php')) {
    fwrite(STDERR, 'Команду надо запускать внутри папки портала' . PHP_EOL);
    exit(1);
}

define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);

$_SERVER['DOCUMENT_ROOT'] = realpath('./');
require_once './bitrix/modules/main/bx_root.php';
require_once './bitrix/modules/main/include/prolog_before.php';

if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
    
} elseif (file_exists('local/vendor/autoload.php')) {
    require 'local/vendor/autoload.php';

} else {
    fwrite(STDERR, 'Не найден ни файл vendor/autoload.php, ни файл local/vendor/autoload.php' . PHP_EOL);
    exit(1);
}

$application = new Application();
$application->add(new NewModule());
$application->run();
