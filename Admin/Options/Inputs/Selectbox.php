<?php
namespace DarkServant\BitrixModuleHelpers\Admin\Options\Inputs;

use DarkServant\BitrixModuleHelpers\Admin\Options\Input;

class Selectbox extends Input
{
    protected bool $isMultiple = false;
    protected int $multipleSize = 1;
    protected array $elements = [];
    protected array $groupTitles = [];
    protected ?int $currentGroupIndex = null;

    public function setValue($value): static
    {
        $this->value = is_array($value) ? $value : [$value];
        return $this;
    }

    public function setMultiple(bool $isMultiple = true, int $size = 1): static
    {
        $this->isMultiple = $isMultiple;
        $this->multipleSize = $size;
        return $this;
    }

    public function addGroupTitle(string $title): static
    {
        $this->groupTitles[] = $title;
        $this->currentGroupIndex = count($this->groupTitles) - 1;
        return $this;
    }

    public function closeGroup(): static
    {
        $this->currentGroupIndex = null;
        return $this;
    }

    public function addOption(string $value, string $title): static
    {
        $this->elements[] = [
            'value' => $value,
            'title' => $title,
            'groupIndex' => $this->currentGroupIndex
        ];
        return $this;
    }
}
