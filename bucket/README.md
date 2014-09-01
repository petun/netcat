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
require_once(dirname(__FILE__).'/pmod/bucket/pBucketInfo.class.php');
```

## Использование ##
### Отображение корзины ###
После этого, класс для работы с корзиной доступен в шаблонах дизайна. В шбалоне дизайна, в месте отображения корзины отображаем
```php
<?php
$b = new pBucket();
$count = $b->count();
$bucket_txt = $count > 0 ? "<a href='".p_sub_link(10)."'>У вас в корзине <span class='cart-mini--num'>$count ".p_human_decl($count,array('товар','товара','товаров'),true)."</span></a>" : 'Корзина пуста';		
?>
```


### Формирование ссылок на добаление в корзину ###
Для добавления товара требуется создать форму
```html
<form action='' method='post'>
    <input type='hidden' name='pb_action' value='add' />
    <input type='hidden' name='pb_count' value='1' />
    <input type='hidden' name='pb_id' value='<item_id>' />
    <input type='submit' value='В корзину' />
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

Далее создаем компонент корзина, пример кода префикса списка товаров:
```php
<? echo $f_AdminCommon; ?>
<?php
$b = new pBucket();
if ($b->count() > 0) {
    $ids = array_keys($b->get());
    echo s_list_class(7,17,'nc_ctpl=2008&id='.implode(',',$ids));
} else {?>
    <p><i>Ваша корзина пуста</i></p>
<?}
?>
```

