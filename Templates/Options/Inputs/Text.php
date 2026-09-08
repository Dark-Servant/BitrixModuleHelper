<input type="<?=$this->type?>"
    size="<?=$this->size?>"
    maxlength="255"
    value="<?=htmlspecialcharsbx($this->value)?>"
    name="<?=htmlspecialcharsbx($this->name)?>"
    <?=$this->readonly ? 'readonly' : ''?>>