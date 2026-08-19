<?
// Основные константы
define('EMPTY_MODULE_MODULE_ID', basename(__DIR__));

// Данные о версии модуля
require __DIR__ . '/install/version.php';
foreach ($arModuleVersion as $key => $value) {
    define('EMPTY_MODULE_' . $key, $value);
}

/**
 * Здесь надо указывать константы для идентификаторов и названий, которые начинают
 * использоваться в дочернем модуле
 */