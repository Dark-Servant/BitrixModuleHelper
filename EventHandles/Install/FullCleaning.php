<?
namespace DarkServant\BitrixModuleHelpers\EventHandles\Install;

final class FullCleaning
{
    private static $instance = null;

    private bool $canToSaving = true;

    private function __construct()
    {
        parse_str(
            (string)parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_QUERY),
            $refererParams
        );

        $this->canToSaving = !(
                    (isset($refererParams['DarkServantMdlClean']) && strtolower($refererParams['DarkServantMdlClean']) == 'y')
                    || (defined('DARK_SERVANT_MDL_CLEAN') && constant('DARK_SERVANT_MDL_CLEAN') === true)
                );
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function canToSaving(): bool
    {
        return $this->canToSaving;
    }
}
