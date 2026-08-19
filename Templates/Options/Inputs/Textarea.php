<textarea rows="<?=$this->rowCount?>"
    cols="<?=$this->columnCount?>"
    name="<?=htmlspecialcharsbx($this->name)?>"
    <?=$this->readonly ? 'readonly' : ''?>><?=htmlspecialcharsbx($this->value)?></textarea>