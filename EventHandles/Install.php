<?
namespace DarkServant\BitrixModuleHelpers\EventHandles;

use DarkServant\BitrixModuleHelpers\EventHandles\Install\Options;

class Install
{
    protected Options $options;

    public function __construct(protected string $moduleID)
    {
        $this->options = new Options($this->moduleID);
    }

    public function getModuleNamespace(): string
    {
        return implode('\\', array_map('ucfirst', explode('.', $this->moduleID)));
    }

    public function getInstallClassName(): string
    {
        $subNamespace = implode('\\', array_slice(explode('\\', static::class), 2, -1));

        return $this->getModuleNamespace()
                . (!empty($subNamespace) ? '\\' . $subNamespace : '')
                . '\Install';
    }

    public function onBeforeModuleInstallationMethods()
    {
        $className = $this->getInstallClassName();
        if (class_exists($className)) {
            (new $className())->onBeforeModuleInstallationMethods();
        }
    }

    public function onAfterModuleInstallationMethods()
    {
        $this->options->onAfterModuleInstallationMethods();
        $className = $this->getInstallClassName();
        if (class_exists($className)) {
            (new $className())->onAfterModuleInstallationMethods();
        }
    }

    public function onBeforeModuleRemovingMethods()
    {
        $this->options->onBeforeModuleRemovingMethods();
        $className = $this->getInstallClassName();
        if (class_exists($className)) {
            (new $className())->onBeforeModuleRemovingMethods();
        }
    }

    public function onAfterModuleRemovingMethods()
    {
        $className = $this->getInstallClassName();
        if (class_exists($className)) {
            (new $className())->onAfterModuleRemovingMethods();
        }
    }
}
