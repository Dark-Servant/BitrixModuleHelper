<?php
namespace DarkServant\BitrixModuleHelpers\Admin\Options\Inputs;

use DarkServant\BitrixModuleHelpers\Admin\Options\Input;

class Checkbox extends Input
{
    public function setValue($value): static
    {
        $this->value = is_string($value) ? strtolower($value) == 'y' : !empty($value);
        return $this;
    }
}