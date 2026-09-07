<?
namespace DarkServant\BitrixModuleHelpers\EventHandles\Install;

use Bitrix\Main\Config\Option;
use DarkServant\BitrixModuleHelpers\Admin\Options\Form;

class Options
{
    protected Form $form;
    protected string $mainMdlPropertyName;

    public function __construct(protected string $moduleID)
    {
        $this->form = new Form($this->moduleID);
        $this->mainMdlPropertyName = $this->moduleID . '.' . Form::OPTION_PARAMETER_NAME;
    }

    public function getMainMdlPropertyName(): string
    {
        return $this->mainMdlPropertyName;
    }

    public function onAfterModuleInstallationMethods()
    {
        $data = json_decode(
                    (string)Option::get('main', $this->mainMdlPropertyName, false, \CSite::GetDefSite()),
                    true
                );

        if (!empty($data)) {
            $options = $this->form->getOptions();
            $options->setData($data);
            $options->save();
        }
    }

    public function onBeforeModuleRemovingMethods()
    {
        if (FullCleaning::getInstance()->canToSaving()) {
            $data = $this->form->getOptions()->getData();
            if (!empty($data)) {
                Option::set('main', $this->mainMdlPropertyName, json_encode($data));
            }

        } else {
            Option::delete('main', ['name' => $this->mainMdlPropertyName]);
        }
    }
}
