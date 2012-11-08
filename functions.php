<?php
/**
 * Русская дата, включая день недели - %B - месяц, %A - день недели
 */
function p_date($ctime = "", $format = "%d %B %Y, %H:%M") {

    $ctime = empty($ctime) ? time() : $ctime;
    
    // если дата в формате mysql - делаем обратное преобразование
    if (!preg_match('/^\d+$/',$ctime)) {
        $ctime = strtotime ($ctime);
    }

    // добаялем тег для обработки и замены в будущем
    $format = str_replace('%B','--%m--',$format);

    // для дня недели
    $format = str_replace('%A','++%u++',$format);
        
    
    // формируем время
    $r = strftime($format,$ctime);
    // заменяем месяц на русское название
    $r = preg_replace_callback("/--(\d{1,2})--/",'_get_rus_month',$r);
    
    // заменяем название дня недели на русское название
    $r = preg_replace_callback("/\+\+(\d{1,2})\+\+/",'_get_rus_day',$r);
    
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


function _get_rus_day($num) {
    $days = array('Понедельник','Вторник','Среда','Четверг','Пятница','Суббота','Воскресенье');       
    return $days[ (int)$num + 1 ];
}

/**
 * Выводит полный тайтл для страницы, используется в теге title
 */
function p_title() {
    global $nc_core;
    global $sub;
    global $current_catalogue;
    global $sub_level_count;
    
    
    $browse_top[prefix] = "";
    $browse_top[active] = "%NAME";
    $browse_top[active_link] = "%NAME";
    $browse_top[unactive] = "%NAME";
    $browse_top[divider] = " / ";
    $browse_top[suffix] = "";
    
    if ($nc_core->page->get_title()) {
        return $nc_core->page->get_title();
    } else {
        // если главная
        if ($sub == $current_catalogue[Title_Sub_ID]) {
            return $current_catalogue[Catalogue_Name] . ' / Главная';
        } else {
            return $current_catalogue[Catalogue_Name] . ' / ' . strip_tags(s_browse_path_range(-1,$sub_level_count-1,$browse_top));
        }
    }
}

/**
 * Shortname for $nc_core->subdivision->get_by_id($id,$field)
 */
function p_sub($id,$field = "") {
    global $nc_core;
    return $nc_core->subdivision->get_by_id($id,$field);
}

/**
 * Quick get link for sub
 */
function p_sub_link($id) {
    return p_sub($id,'Hidden_URL');
}

/**
 * Quick get title for sub
 */
function p_sub_title($id) {
    return p_sub($id,'Subdivision_Name');
}



function p_sub_child_count($csub = null) {
    global $db;
    global $sub;
    
    if (empty($csub)) {$csub = $sub;}
    
    return $db->get_var('SELECT COUNT(*) FROM Subdivision WHERE     Parent_Sub_ID = '.$csub);
}