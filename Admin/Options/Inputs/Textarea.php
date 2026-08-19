<?php
namespace DarkServant\BitrixModuleHelpers\Admin\Options\Inputs;

use DarkServant\BitrixModuleHelpers\Admin\Options\Input;

class Textarea extends Input
{
    const VALIGN_TOP = true;

    protected int $rowCount = 5;
    protected int $columnCount = 5;
    protected bool $readonly = false;

    public function setRowCount(int $rowCount): static
    {
        $this->rowCount = $rowCount;
        return $this;
    }

    public function setColumnCount(int $columnCount): static
    {
        $this->columnCount = $columnCount;
        return $this;
    }

    public function setReadonly(bool $readonly): static
    {
        $this->readonly = $readonly;
        return $this;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getColumnCount(): int
    {
        return $this->columnCount;
    }

    public function getReadonly(): bool
    {
        return $this->readonly;
    }
}
