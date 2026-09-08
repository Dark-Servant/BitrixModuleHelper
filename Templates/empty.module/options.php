<?php
/**
 * Пример создания настроек модуля
 */
use DarkServant\BitrixModuleHelpers\Admin\Options\{Form, Tab};
use DarkServant\BitrixModuleHelpers\Admin\Options\Inputs\{
    Checkbox,
    Text,
    Textarea,
    Radio,
    Selectbox
};

function checkingData(Form $form, array $oldData): void
{
    $newData = $form->getOptions()->getData() ?? [];
    if (empty($newData['wiski'])) {
        $form->addErrorMessageForName('Пожалуйста, введите значение "Виски"', 'wiski');
    }
}

(new Form(basename(__DIR__)))
    ->setThrowableCheckingCallBack(checkingData(...))
    ->addSectionTitle('Тра-ли-вали')
    ->addInput((new Checkbox('Чу-чу', 'chuchu'))->setValue(true))
    ->addInput((new Text('Виски', 'wiski'))->setSizeValue(20))
    ->addInput(
            (new Radio('Мальчики', 'boys'))
                ->addListValue(['vasya', 'Вася'])
                ->addListValue(['petya'])
                ->addListValue(['kolya', 'Коля'])
                ->setValue('petya')
        )
    ->addSectionTitle('Пити-пити')
    ->addInput((new Textarea('Бейби', 'baby'))->setRowCount(5)->setColumnCount(20)->setValue('Бейби-бум'))
    ->addInput((new Selectbox('Овечки', 'sheeps'))
                ->setMultiple(true, 5)
                ->addOption('sweezy', 'Свизи')
                ->addOption('dizzy', 'Дизи')
                ->addOption('fizzy', 'Физи')
                ->addOption('jerry', 'Джерри')
                ->addOption('tommy', 'Томми')
                ->addOption('billy', 'Билли')
                ->addOption('bobby', 'Бобби')
                ->addOption('bob', 'Боб')
                ->addOption('bubba', 'Бубба')
                ->setValue(['billy', 'fizzy'])
        )
    ->addInput((new Selectbox('Цветочки', 'flowers'))
                ->addOption('red', 'Красный')
                ->addOption('green', 'Зелёный')
                ->addOption('blue', 'Синий')
                ->addGroupTitle('Яркие цвета')
                ->addOption('yellow', 'Жёлтый')
                ->addOption('orange', 'Оранжевый')
                ->addGroupTitle('Пастельные цвета')
                ->addOption('pink', 'Розовый')
                ->addOption('lightblue', 'Голубой')
                ->closeGroup()
                ->addOption('lightgreen', 'Светло-зелёный')
                ->setValue('blue')
        )
    ->addTab(new Tab('tab2', 'Вторая вкладка', 'second_tab', 'Вторая вкладка, заголовок посерьезнее'))
    ->addInput((new Text('Виды какао бобов', 'cacao_kinds'))->setSizeValue(20)->setValue('Колумбийский, Ганский, Бразильский'))
    ->renderAsTabControlName('tabControl')
;