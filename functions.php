<?php

function ru_date($ctime = "", $format = "%d %B %Y, %H:%M") {

    $ctime = empty($ctime) ? time() : $ctime;
    
    // если дата в формате mysql - делаем обратное преобразование
    if (!preg_match('/^\d+$/',$ctime)) {
        $ctime = strtotime ($ctime);
    }

    // добаялем тег для обработки и замены в будущем
    $format = str_replace('%B','--%m--',$format);
    // формируем время
    $r = strftime($format,$ctime);
    // заменяем месяц на русское название
    $r = preg_replace_callback("/--(\d{1,2})--/",'_get_rus_month',$r);

    return $r;
    
}

function _get_rus_month($num) {
    $num = $num[1]*1;
    
    $months = array(
            1=>"Января"
            ,2=>"Февраля"
            ,3=>"Марта"
            ,4=>"Апреля"
            ,5=>"Мая"
            ,6=>"Июня"
            ,7=>"Июля"
            ,8=>"Августа"
            ,9=>"Сентября"
            ,10=>"Октября"
            ,11=>"Ноября"
            ,12=>"Декабря"
    );
    return $months[$num];
}




/*private function unix_date($ctime = "", $format = "%d %B %Y, %H:%M") {
    $ctime = empty($ctime) ? time() : $ctime;

    // добаялем тег для обработки и замены в будущем
    $format = str_replace('%B','--%m--',$format);
    // формируем время
    $r = strftime($format,$ctime);
    // заменяем месяц на русское название
    $r = preg_replace_callback("/--(\d{1,2})--/",array($this,'get_rus_month'),$r);

    return $r;
}

private function mysql_date($ctime,$format = "%d %B %Y, %H:%M") {
    $r = strtotime ($ctime);
    if ($r) {
        return $this->unix_date($r,$format);
    } else {
        return false;
    }
}*/