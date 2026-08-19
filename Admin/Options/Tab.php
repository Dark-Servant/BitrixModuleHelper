<?php
namespace DarkServant\BitrixModuleHelpers\Admin\Options;

use Bitrix\Main\Localization\Loc;

class Tab implements \IteratorAggregate
{
    protected static $defaultInstance = null;
    protected $elements = [];

    public function __construct(
            protected string $div,
            protected string $tabTitle,
            protected string $icon,
            protected string $title
    )
    {
    }

    public static function getDefaultInstance(): static
    {
        if (is_null(static::$defaultInstance)) {
            IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/options.php');
            
            static::$defaultInstance = new static(
                'edit1',
                Loc::getMessage('MAIN_TAB_SET'),
                'ib_settings',
                Loc::getMessage('MAIN_TAB_TITLE_SET')
            );
        }
        return static::$defaultInstance;
    }

    public function addInput(Input $input): static
    {
        $this->elements[] = $input;
        return $this;
    }

    public function addSectionTitle(string $sectionTitle): static
    {
        $this->elements[] = $sectionTitle;
        return $this;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->elements);
    }

    public function toArray(): array
    {
        return [
            'DIV' => $this->div,
            'TAB' => $this->tabTitle,
            'ICON' => $this->icon,
            'TITLE' => $this->title
        ];
    }
}