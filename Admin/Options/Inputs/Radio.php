<?php
namespace DarkServant\BitrixModuleHelpers\Admin\Options\Inputs;

use DarkServant\BitrixModuleHelpers\Admin\Options\Input;

class Radio extends Input
{
    protected $listValues = [];

    public function getListValue(): mixed
    {
        return $this->listValues;
    }

    public function addListValue(array $values): static
    {
        $unitTitle = $unitValue = '';
        if (is_array($values)) {
            [$unitValue, $unitTitle] = $values;

        } else {
            $unitValue = $values;
        }
        $code = (empty($unitTitle) ? '' : $unitTitle) . count($this->listValues);
        $this->listValues[hash('sha256', $code)] = [$unitTitle, $unitValue];
        return $this;
    }
}
