<select name="<?=htmlspecialcharsbx($this->name)?><?if ($this->isMultiple):?>[]<?endif;?>"
        <?=$this->isMultiple ? 'multiple size="'.$this->multipleSize.'"' : ''?>><?
$currentGroupIndex = null;
$lastElementIndex = count($this->elements) - 1;
foreach ($this->elements as $elementIndex => $element):
    if ($currentGroupIndex !== $element['groupIndex']):
        if (isset($currentGroupIndex)):?>
    </optgroup><?
        endif;
        $currentGroupIndex = $element['groupIndex'];
        if (isset($currentGroupIndex)):?>
    <optgroup label="<?=$this->groupTitles[$element['groupIndex']]?>"><?
        endif;
    endif;?>
    <option value="<?=$element['value']?>"<?=in_array($element['value'], $this->value ?? []) ? ' selected' : '' ?>><?=htmlspecialcharsbx($element['title'])?></option><?
    if (($lastElementIndex == $elementIndex) && isset($currentGroupIndex)):?>
    </optgroup><?
    endif;
endforeach;?>
</select>