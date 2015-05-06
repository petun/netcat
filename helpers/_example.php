<?php

//require_once("pTranslit.class.php");
//
//$str = $_GET['str'];
//
//$t = new pTranslit();
//echo $t->enToRu($str);



require_once("CHtml.php");

echo '<br /><br />';
echo CHtml::link('Tesxt of a link','http://google.com', array('class'=>'test class'));

echo '<br /><br />';
echo CHtml::textField('fio', 'value');

echo '<br /><br />';
echo CHtml::textArea('textarea', 'value');

echo '<br /><br />';
echo CHtml::dropDownList('select', 1, array(2=>'testst',1=>'selected item'), array(
    'empty'=>'-- empty --'
));



echo '<br /><br />';
echo CHtml::submitButton('Submit button');

echo '<br /><br />';
echo CHtml::css('a {color: red;}','all');

echo '<br /><br />';
echo CHtml::cssFile('testcss.css');

//echo CHtml::script('alert(1);');
echo CHtml::scriptFile('file.js');

echo CHtml::checkBoxList('checklist',1,array(1=>'first option',2=>'second option',3=>'third option'));
echo '<br /><br />';
echo CHtml::radioButtonList('radiolist',1,array(1=>'first option',2=>'second option',3=>'third option'));