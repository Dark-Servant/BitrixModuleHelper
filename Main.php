<?php
namespace DarkServant\BitrixModuleHelpers;

use Bitrix\Main\{
    Localization\Loc,
    Loader,
    Config\Option
};
use DarkServant\BitrixModuleHelpers\EventHandles\{Employment, Install as InstallEventHandle};

abstract class Main extends \CModule
{
    public $MODULE_ID;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;

    protected $nameSpaceValue;
    protected $subLocTitle;
    protected $optionParameter = null;
    protected $definedContants;
    protected $installEventHandler;

    protected static $defaultSiteID;

    function __construct()
    {
        $this->initModuleClassPath()
             ->initModuleId()
             ->initNameSpaceValue()
             ->initMainTitles()
             ->initVersionTitles()
        ;
        $this->optionParameter = new OptionParameter($this->MODULE_ID);
        $this->installEventHandler = new InstallEventHandle($this->MODULE_ID);
    }

    /**
     * Запоминает и возвращает код модуля, к которому относится текущий класс
     * 
     * @return string
     */
    protected function initModuleId(): static
    {
        $this->MODULE_ID = basename(dirname($this->moduleClassPath));
        return $this;
    }

    /**
     * Запоминает и возвращает название именного пространства для классов из
     * библиотеки модуля
     * 
     * @return string
     */
    protected function initNameSpaceValue(): static
    {
        $this->nameSpaceValue = preg_replace('/\.+/', '\\\\', ucwords($this->MODULE_ID, '.'));
        return $this;
    }

    /**
     * Запоминает и возвращает настоящий путь к текущему классу
     * 
     * @return string
     */
    protected function initModuleClassPath(): static
    {
        $this->moduleClass = new \ReflectionClass(static::class);
        // не надо заменять на __DIR__, так как могут быть дополнительные модули $this->moduleClassPath
        $this->moduleClassPath = rtrim(preg_replace('/[^\/\\\\]+$/', '', $this->moduleClass->getFileName()), '\//');
        return $this;
    }

    /**
     * Инициализирует название и описание модуля, а так же в процессе инициализации проходят
     * инициализацию другие переменные объекта класса, например, идентификатор модуля
     *
     * @return static
     */
    protected function initMainTitles(): static
    {
        Loc::loadMessages($this->moduleClassPath . '/index.php');

        $this->subLocTitle = strtoupper(static::class) . '_';
        $this->MODULE_NAME = Loc::getMessage($this->subLocTitle . 'MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage($this->subLocTitle . 'MODULE_DESCRIPTION');

        $this->PARTNER_NAME = Loc::getMessage($this->subLocTitle . 'PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage($this->subLocTitle . 'PARTNER_URI');

        return $this;
    }


    /**
     * Инициализирует переменные объекта класса, используя параметры из файла
     *      modules/<ID модуля>/install/version.php
     * и создает переменные объекта по правилу
     *      MODULE_<символьный код параметра> = <значение параметра>
     *
     * @return static
     */
    protected function initVersionTitles(): static
    {
        $versionFile = $this->moduleClassPath . '/version.php';
        if (!file_exists($versionFile)) {
            return $this;
        }

        include $versionFile;
        if (empty($arModuleVersion) || !is_array($arModuleVersion)) {
            return $this;
        }

        foreach ($arModuleVersion as $versionParameterCode => $versionParameterValue) {
            $parameterCode = 'MODULE_' . strtoupper($versionParameterCode);
            $this->$parameterCode = $versionParameterValue;
        }
        return $this;
    }

    /**
     * Запоминает и возвращает кода сайта по-умолчанию
     * 
     * @return string
     */
    protected static function getDefaultSiteID()
    {
        if (self::$defaultSiteID) {
            return self::$defaultSiteID;
        }

        return self::$defaultSiteID = CSite::GetDefSite();
    }

    /**
     * По переданному имени возвращает значение константы текущего класса с учетом того, что эта константа
     * точно была (пере)объявлена в этом классе модуля. Конечно, получить значение константы класса можно
     * и через <название класса>::<название константы>, но такая запись не учитывает для дочерних классов,
     * что константа не была переобъявлена, тогда она может хранить ненужные старые данные, из-за чего требуется
     * ее переобъявлять, иначе дочерние модули начнуть устанавливать то же, что и родительские, а переобъявление
     * требует дополнительного внимания к каждой константе и дополнительных строк в коде дочерних модулей
     * 
     * @param string $constName - название константы
     * @return array
     */
    protected function getModuleConstantValue(string $constName)
    {
        $constant = $this->moduleClass->getReflectionConstant($constName);
        if (
            ($constant === false)
            || ($constant->getDeclaringClass()->getName() != static::class)
        ) return [];

        return $constant->getValue();
    }

    /**
     * Подключает модуль и сохраняет созданные им константы
     * 
     * @return void
     */
    protected function initDefinedContants()
    {
        /**
         * array_keys нужен, так как в array_filter функция isset дает
         * лишнии результаты
         */
        $this->definedContants = array_keys(get_defined_constants());

        Loader::IncludeModule($this->MODULE_ID);
        $this->definedContants = array_filter(
            get_defined_constants(),
            function($key) {
                return !in_array($key, $this->definedContants);
            }, ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Выполняется основные операции по установке модуля
     * 
     * @return void
     */
    protected function runInstallMethods()
    {
    }

    /**
     * Устанавливает модуль, но сначала проверяет не является ли он
     * дочерним, а, если это так, то при условии, что родительские модули
     * не установлены, сначала устанавливает их
     * 
     * @return void
     */
    protected function initFullInstallation()
    {
        set_time_limit(0);
        $parentClassName = get_parent_class(get_called_class());
        if (($parentClassName != self::class) && !(new $parentClassName())->IsInstalled())
            (new $parentClassName())->DoInstall(false);

        RegisterModule($this->MODULE_ID);
    }

    /**
     * Функция, вызываемая при установке модуля
     *
     * @param bool $stopAfterInstall - указывает модулю остановить после
     * своей установки весь процесс установки
     * 
     * @return void
     */
    public function DoInstall(bool $stopAfterInstall = true) 
    {
        global $APPLICATION;
        $this->initFullInstallation();
        $this->initDefinedContants();

        try {
            Employment::getInstance()->setBussy();
            $this->installEventHandler->onBeforeModuleInstallationMethods();
            $this->runInstallMethods();
            $this->optionParameter->setConstants(array_keys($this->definedContants));
            $this->optionParameter->setInstallShortData([
                'INSTALL_DATE' => date('Y-m-d H:i:s'),
                'VERSION' => $this->MODULE_VERSION,
                'VERSION_DATE' => $this->MODULE_VERSION_DATE,
            ]);
            $this->optionParameter->save();
            $this->installEventHandler->onAfterModuleInstallationMethods();
            Employment::getInstance()->setFree();
            if ($stopAfterInstall) {
                $APPLICATION->IncludeAdminFile(
                    Loc::getMessage($this->subLocTitle . 'MODULE_WAS_INSTALLED'),
                    $this->moduleClassPath . '/step1.php'
                );
            }

        } catch (\Exception $error) {
            $this->removeAll();
            $APPLICATION->ThrowException($error->getMessage());
            Employment::getInstance()->setFree();
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage($this->subLocTitle . 'MODULE_NOT_INSTALLED'),
                $this->moduleClassPath . '/error.php'
            );
        }
    }

    /**
     * Выполняется основные операции по удалению модуля
     * 
     * @return void
     */
    protected function runRemoveMethods()
    {
    }

    /**
     * Основной метод, очищающий систему от данных, созданных им
     * при установке
     * 
     * @return void
     */
    protected function removeAll()
    {
        $this->definedContants = array_fill_keys($this->optionParameter->getConstants() ?? [], '');
        array_walk($this->definedContants, function(&$value, $key) { $value = constant($key); });
        $this->runRemoveMethods();
        UnRegisterModule($this->MODULE_ID); // удаляем модуль
    }

    /**
     * Проверяет, есть ли у модуля дочернии модули среди установленных.
     * Если такие есть, то сначала удаляются они
     * 
     * @return void
     */
    protected function killAllChildren()
    {
        $className = get_called_class();
        $modules = self::GetList();
        while ($module = $modules->Fetch()) {
            $childClass = str_replace('.', '_', $module['ID']);
            if (!class_exists($childClass) || (get_parent_class($childClass) != $className))
                continue;

            (new $childClass())->DoUninstall(false);
        }
    }

    /**
     * Функция, вызываемая при удалении модуля
     *
     * @param bool $stopAfterDeath - указывает модулю остановить после
     * своего удаления весь процесс удаления
     * 
     * @return void
     */
    public function DoUninstall(bool $stopAfterDeath = true) 
    {
        global $APPLICATION;
        $this->killAllChildren();
        Loader::IncludeModule($this->MODULE_ID);
        Employment::getInstance()->setBussy();
        $this->installEventHandler->onBeforeModuleRemovingMethods();
        $this->removeAll();
        Option::delete($this->MODULE_ID);
        $this->installEventHandler->onAfterModuleRemovingMethods();
        Employment::getInstance()->setFree();
        if ($stopAfterDeath)
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage($this->subLocTitle . 'MODULE_WAS_DELETED'),
                $this->moduleClassPath . '/unstep1.php'
            );
    }
}
