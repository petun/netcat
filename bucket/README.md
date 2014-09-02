# Простая корзина товаров #
## Установка ##
```mysql
CREATE TABLE IF NOT EXISTS `pbucket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pbucketid` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;
```
Далее требуется включить компонент в работу нетката.

```php
require_once(dirname(__FILE__).'/pmod/bucket/pBucket.class.php');
```

## Использование ##
### Отображение корзины ###
После этого, класс для работы с корзиной доступен в шаблонах дизайна. В шбалоне дизайна, в месте отображения корзины отображаем
```php
<?php
$b = new pBucket();
$bucketCount = $b->count();
$bucketSum = $b->sum();
$b = new pBucket();
?>
<div class="box" id="basket">
            	<ul>
                	<li><a href="<?=p_sub_link(16);?>">Ваша корзина</a></li>
                    <? if ($bucketCount) {?>
                    <li><? echo $bucketCount ." ".p_human_decl($bucketCount,array('товар','товара','товаров'),true);?></li>
                    <li><?=p_human_price($bucketSum);?> руб.</li>
                    <? } else { ?>
                    <li>корзина пуста</li>
                    <? } ?>
                </ul>
            </div>
?>
```


### Формирование ссылок на добаление в корзину ###
Для добавления товара требуется создать форму
```html
<form action='' method='post'>
    <input type='hidden' name='pb_action' value='add' />
    <input type='hidden' name='pb_count' value='1' />
    <input type='hidden' name='pb_price' value='<?=$f_price;?>' />
    <input type='hidden' name='pb_id' value='<?=$f_RowID;?>' />  
    <a href="#" class='add-to-busket'><?=p_human_price($f_price);?> <span>руб.</span></a>
</form>
```

### Отображение страницы с корзиной ###
Для отображения списка товаров легче всего использовать s_list_class. 
Для этого нужно создать еще один макет в компоненте Каталог. В системных настройках указываем
```php
<?
$ignore_sub = 1;
$ignore_cc = 1;

if ($ids) {
    $query_where = "a.Message_ID IN ($ids)";    
}
?>
```

Далее, если корзина и форма заказа на одной странице, проще всего  корзину сделать в макете, а уже в раздел с этим макетом добавлять компонент формы заказа.

### Макет ###
```php
%Header
<?php
$b = new pBucket();
if ($b->count() > 0) {
    $ids = array_keys($b->get());    
    echo s_list_class(7,17,'nc_ctpl=2008&ids='.implode(',',$ids));
} else {?>
<p style='padding-left: 60px;'><i>Ваша корзина пуста</i></p>
<?}
?>
```

### Компонент отображения товаров в корзине ###
```php
// prefix
<? $b = new pBucket(); ?>

// cyrcle
<? $item = $b->get($f_RowID); ?>
<input type="hidden" name="pb_id[]" value="<?=$f_RowID;?>">
<input type="hidden" name="pb_price[]" value="<?=$item['price'];?>">
<input class='arrow-text' type="text" name="pb_count[]" placeholder="<?=$item['count'];?>" value="<?=$item['count'];?>" />
<a href="<? echo p_sub_link(16)."?pb_action=remove&pb_id=".$f_RowID?>"><img src="/template/img/remove.png" width="11" height="11"></a>
```



### Шаблон письма для менеджера ###
```php
$text = "<h2>Добавлен новый заказ c сайта</h2>\n";	
	$text .= "<h3>Список товаров</h3>\n";
	$text .= "<ul>\n";
	
	$b = new pBucket();
    $items = $b->get();
	foreach ($items as $id => $item) {
        $itemInfo = $nc_core->message->get_by_id(2003, $id);
		$text .= "<li>".$itemInfo['name']. "(".$item['count']." шт.) - ".$item['price']." руб.</li>\n";
	}
	$text .= "</ul>\n";
	$text .= "<p><strong>Сумма заказа: ".$b->sum()." руб.</strong></p>\n";
	
	$text .= "<h3>Контактные данные</h3>";
	$text .= "<p>ФИО: ".$f_fio."</p>\n";
	$text .= "<p>Телефон: ".$f_telephone."</p>\n";
	$text .= "<p>Email: ".$f_email."</p>\n";
	$text .= "<p>Город: ".$f_town."</p>\n";
    $text .= "<p>Улица: ".$f_street."</p>\n";
    $text .= "<p>Дом: ".$f_house."</p>\n";
    $text .= "<p>Корпус: ".$f_corp."</p>\n";
    $text .= "<p>Этаж: ".$f_floor."</p>\n";
    $text .= "<p>Подъезд: ".$f_pod."</p>\n";
        $text .= "<p>Квартира: ".$f_flat."</p>\n";
	$text .= "<p>Домофон: ".$f_domofon."</p>";

$mailer = new CMIMEMail();
$mailer->mailbody(strip_tags($text),$text);
$mailer->send(($cc_settings[EmailTo] ? $cc_settings[EmailTo] : $system_env[SpamFromEmail]), $system_env[SpamFromEmail], $system_env[SpamFromEmail], 'Новый заказ с сайта', $system_env[SpamFromEmail]);

    ob_end_clean();
    header('Location: '.p_sub_link(18));
    exit;

```

