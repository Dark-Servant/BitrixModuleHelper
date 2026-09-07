<?php

use DarkServant\BitrixModuleHelpers\Commands\NewModule;
use Symfony\Component\Console\Application;

define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);

function includeBXRootFileViaRelativelyPath(string $relativelyBXRoot): bool
{
    $rootFile = $relativelyBXRoot . '/bitrix/modules/main/bx_root.php';
    if (!file_exists($rootFile)) {
        return false;
    }

    $_SERVER['DOCUMENT_ROOT'] = realpath($relativelyBXRoot);
    require_once $rootFile;
    require_once $relativelyBXRoot . '/bitrix/modules/main/include/prolog_before.php';
    return true;
}

function includeComposerAutoloadViaRelativelyPath(string $relativelyPath): bool
{
    $autoloadFile = $relativelyPath . '/vendor/autoload.php';
    if (!file_exists($autoloadFile)) {
        return false;
    }

    require $autoloadFile;
    return true;
}

if (!includeBXRootFileViaRelativelyPath('.') && !includeBXRootFileViaRelativelyPath('..')) {
    fwrite(STDERR, 'Команду надо запускать внутри папки портала или папки относительно корня портала, но не глубже одного уровня' . PHP_EOL);
    exit(1);
}

if (
    !includeComposerAutoloadViaRelativelyPath('.')
    && !includeComposerAutoloadViaRelativelyPath('..')
    && !includeComposerAutoloadViaRelativelyPath('./local')
    && !includeComposerAutoloadViaRelativelyPath('../local')
) {
    fwrite(STDERR, 'Не найден файл vendor/autoload.php' . PHP_EOL);
    exit(1);
}

$application = new Application();
$application->add(new NewModule());
$application->run();
