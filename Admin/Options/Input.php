<?php
namespace DarkServant\BitrixModuleHelpers\Admin\Options;

class Input
{
    const VALIGN_TOP = false;
    protected $value = null;

    public function __construct(
            protected string $title,
            protected string $name
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue($value): static
    {
        $this->value = $value;
        return $this;
    }

    public function setValueFromList(array $values): static
    {
        if (array_key_exists($this->name, $values)) {
            $this->setValue($values[$this->name]);
        }
        return $this;
    }

    public function render(): void
    {
        $template = __DIR__ . '/../../Templates/Options/Inputs/'
                  . (new \ReflectionClass(static::class))->getShortName() . '.php';

        require $template;
    }
}