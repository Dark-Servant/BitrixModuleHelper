<?php
namespace DarkServant\BitrixModuleHelpers\Admin\Options;

use DarkServant\BitrixModuleHelpers\OptionParameter;
use DarkServant\BitrixModuleHelpers\Admin\Options\Inputs\Checkbox;

class Form
{
    const OPTION_PARAMETER_NAME = 'options';
    protected $tabs = [];
    protected $settings = [];

    public function __construct(protected string $moduleID)
    {
        $this->options = (new OptionParameter($this->moduleID))->setName(static::OPTION_PARAMETER_NAME);
    }

    public function getOptions(): OptionParameter
    {
        return $this->options;
    }

    public function addTab(Tab $tab)
    {
        $this->tabs[] = $tab;
        return $this;
    }

    public function addSectionTitle(string $sectionTitle): static
    {
        $this->prepareTabs()->tabs[count($this->tabs) - 1]->addSectionTitle($sectionTitle);
        return $this;
    }

    public function addInput(Input $input): static
    {
        $this->prepareTabs()->tabs[count($this->tabs) - 1]->addInput($input);
        return $this;
    }

    public function renderAsTabControlName(string $tabControlName)
    {
        global $APPLICATION, $mid;
        $tabControl = new \CAdminTabControl($tabControlName, array_map(fn($tab) => $tab->toArray(), $this->prepareTabs()->tabs));
        $this->saveChangesForTabControl($tabControl);

        $savedData = $this->options->getData() ?? [];
        require __DIR__ . '/../../Templates/Options/Form.php';
    }

    protected function saveChangesForTabControl(\CAdminTabControl $tabControl): static
    {
        global $APPLICATION, $mid;
        if (
            ($_SERVER['REQUEST_METHOD'] != 'POST')
            || (
                empty($_POST['Update'])
                && empty($_POST['Apply'])
                && empty($_POST['RestoreDefaults'])
            )
            || !check_bitrix_sessid()
        ) {
            return $this;
        }

        if (!empty($_POST['RestoreDefaults'])) {
            $this->options->setData([]);

        } else {
            foreach ($this->tabs as $tab) {
                foreach ($tab as $element) {
                    if (is_string($element)) continue;

                    $name = $element->getName();
                    $value = $_REQUEST[$element->getName()];
                    if (($element instanceof Checkbox) && ($value != 'Y')) {
                        $value = 'N';
                    }
                    $this->options->addData([$name => $value]);
                }
            }
        }
        $this->options->save();

        if (!empty($_POST['Update']) && !empty($_REQUEST['back_url_settings'])) {
            LocalRedirect($_REQUEST['back_url_settings']);

        } else {
            LocalRedirect(
                    $APPLICATION->GetCurPage()
                        . '?mid=' . urlencode($mid)
                        . '&lang=' . urlencode(LANGUAGE_ID)
                        . '&back_url_settings=' . urlencode($_REQUEST['back_url_settings'])
                        . '&' . $tabControl->ActiveTabParam()
                );
        }
        return $this;
    }

    protected function prepareTabs(): static
    {
        if (!count($this->tabs)) {
            $this->tabs[] = Tab::getDefaultInstance();
        }

        return $this;
    }
}