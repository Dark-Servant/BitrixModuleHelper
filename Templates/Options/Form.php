<?php
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/options.php');

$tabControl->Begin();?>
<form method="post" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>"><?

foreach ($this->tabs as $tabNumber => $tab):
    $tabControl->BeginNextTab();
    foreach ($tab as $element):
        if (is_string($element)):?>
    <tr class="heading">
        <td colspan="2"><?=$element?></td>
    </tr><?
            continue;
        endif;?>

    <tr>
        <td width="40%" nowrap <?if ((new ReflectionClass($element))->getConstant('VALIGN_TOP')):?> class="adm-detail-valign-top"<?endif;?>>
            <label for="<?=htmlspecialcharsbx($element->getName())?>"><?=$element->getTitle()?>:</label>
        </td>
        <td width="60%"><?
        $element->setValueFromList($savedData)->render();?>
        </td>
    </tr><?
    endforeach;
endforeach;

$tabControl->Buttons();?>
    <input type="submit"
           name="Update"
           value="<?=Loc::getMessage('MAIN_SAVE')?>"
           title="<?=Loc::getMessage('MAIN_OPT_SAVE_TITLE')?>"
           class="adm-btn-save">
    <input type="submit"
           name="Apply"
           value="<?=Loc::getMessage('MAIN_OPT_APPLY')?>"
           title="<?=Loc::getMessage('MAIN_OPT_APPLY_TITLE')?>"><?
    if (!empty($_REQUEST['back_url_settings'])):?>
    <input type="button"
           name="Cancel"
           value="<?=Loc::getMessage('MAIN_OPT_CANCEL')?>"
           title="<?=Loc::getMessage('MAIN_OPT_CANCEL_TITLE')?>"
           onclick="window.location='<?=htmlspecialcharsbx(addslashes($_REQUEST['back_url_settings']))?>'">
    <input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST['back_url_settings'])?>"><?
    endif?>
    <input type="submit"
           name="RestoreDefaults"
           value="<?=Loc::getMessage('MAIN_RESTORE_DEFAULTS')?>"
           title="<?=Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS')?>"
           onclick="return confirm('<?=addslashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>')">
    <?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>