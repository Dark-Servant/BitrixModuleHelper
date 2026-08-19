<?
foreach ($this->listValues as $positionCode => $value):
[$unitName, $unitValue] = $value;?>
<div>
    <input type="radio"
           id="<?=htmlspecialcharsbx($this->name . '_' . $positionCode)?>"
           name="<?=htmlspecialcharsbx($this->name)?>"
           value="<?=htmlspecialcharsbx($unitValue)?>"<?if ($this->value == $unitValue):?> checked<?endif;?>><?
    if (!empty($unitName)):?>
        <label for="<?=htmlspecialcharsbx($this->name . '_' . $positionCode)?>"><?=$unitName?></label><?
    endif;?>
</div><?
endforeach;
