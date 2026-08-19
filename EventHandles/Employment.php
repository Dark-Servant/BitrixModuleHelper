<?
namespace DarkServant\BitrixModuleHelpers\EventHandles;

final class Employment
{
    private static $instance = null;
    private $bussyStatus = false;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Устанавливает занятость для всех обработчиков событий
     *
     * @return boolean
     */
    public function setBussy()
    {
        if ($this->bussyStatus) return false;

        return $this->bussyStatus = true;
    }

    /**
     * Снимает занятость для всех обработчиков событий
     *
     * @return boolean
     */
    public function setFree()
    {
        $oldFree = $this->bussyStatus;
        $this->bussyStatus = false;

        return !$oldFree;
    }
}