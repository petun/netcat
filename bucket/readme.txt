Два класса. Один служит для функционала самой корзины, и не привязан к какому то компоненту, второй как раз свазывает данные корзины с определенным компонентом. 
Для работы необходима таблица:

CREATE TABLE IF NOT EXISTS `pbucket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pbucketid` varchar(255) NOT NULL,
  `data` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;



Пример использования класса:

1. В заголовке помещаем код:
 <?
	$b = new pBucket();
    $count = $b->count();
$bucket_txt = $count > 0 ? "<a href='".p_sub_link(10)."'>У вас в корзине <span class='cart-mini--num'>$count ".p_human_decl($count,array('товар','товара','товаров'),true)."</span></a>" : 'Корзина пуста';		
	?>

Класс делает все необходимые вещи, удаляет и добавляет товары, считает кол-во, формирует сессию и т.д.

2. В товаре создаем форму:

method='post'

pb_action=add
pb_count=1
pb_id=6


3. В самой корзине используются три копмонента:
3.1 Компонент самой корзины - тут удаление и пересчет стоимости товаров. 
Пример кода:

$b = new pBucket();
if ($b->count() > 0) {
 $bi = new pBucketInfo($b,'2004');
 foreach ($bi->items() as $el) { 	
   // $el - массиыв данных о товаре, кроме этого доступны
   // name,price,id,count,total_sum
 }

}

Ссылка на удаление товара: 
<a class="delete-btn" href="<? echo "$subLink?pb_action=remove&pb_id=".$el['id']?>">&times;</a>


А сама форма, где отображаются товары:
<form method="post">
 <input type="hidden" name="pb_action" value="recount" />

 // а все товары отображаются с инпутами
 <input type="hidden" name="pb_id[]" value="2">
<select name="pb_count[]" class="-select-">
	<option selected="true" value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option>	</select>
</form>



3.2 Форма отправки товара. 
Тут простая форма с полями + формируется список товаров:
// email logic
	$text = "<h2>Добавлен новый заказ c сайта</h2>\n";	
	$text .= "<h3>Список товаров</h3>\n";
	$text .= "<ul>\n";
	
	$bi = new pBucketInfo(new pBucket(),2004);
	foreach ($bi->items() as $item) {
		$text .= "<li>".$item['name']. "(".$item['count']." шт.) - ".$item['total_sum']." руб.</li>\n";
	}
	$text .= "</ul>\n";
	$text .= "<p><strong>Сумма заказа: ".$bi->total_sum()." руб.</strong></p>\n";
	
	$text .= "<h3>Контактные данные</h3>";
	$text .= "<p>ФИО: ".$f_Fio."</p>\n";
	$text .= "<p>Телефон: ".$f_Telephone."</p>\n";
	$text .= "<p>Email: ".$f_Email."</p>\n";
	$text .= "<p>Адрес доставки: ".$f_Address."</p>\n";
	$text .= "<p>Комментарии: ".$f_Comments."</p>";
	
	
	 // update date for ad
     $nc_core->db->query("UPDATE Message2009 SET ProdInfo = '$text' WHERE Message_ID = $message");
	//p_log($text);
	
$mailer = new CMIMEMail();
$mailer->mailbody(strip_tags($text),$text);
$mailer->send(($cc_settings[EmailTo] ? $cc_settings[EmailTo] : $system_env[SpamFromEmail]), $system_env[SpamFromEmail], $system_env[SpamFromEmail], 'Новый заказ с сайта', $system_env[SpamFromEmail]);
	
	
	ob_end_clean();
	header('Location: '.p_cc_link(22));	


3.3 Компонент, очистка корзины
<?
 $b = new pBucket();
 $b->clear();
?>

Хотя наверное этот код можно вставить после формирования письма просто.







