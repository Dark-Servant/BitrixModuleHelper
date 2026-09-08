<?php
namespace DarkServant\BitrixModuleHelpers\Admin\Options\Inputs;

use DarkServant\BitrixModuleHelpers\Admin\Options\Input;

class Text extends Input
{
    protected string $type;
    protected int $size;
    protected bool $readonly = false;

    public function __construct()
    {
        parent::__construct(...func_get_args());
        $this->type = strtolower((new \ReflectionClass(static::class))->getShortName());
    }

    public function setSizeValue(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function setReadonly(bool $readonly): static
    {
        $this->readonly = $readonly;
        return $this;
    }

    public function getSizeValue(): static
    {
        return $this->size;
    }

    public function getReadonly(): static
    {
        return $this->readonly;
    }
}
