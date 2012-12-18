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
    global $sub;
    global $current_sub;
    
    if ($id == $sub) {
        return (!empty($field) ? $current_sub[$field] : $current_sub);
    } else {
        return $nc_core->subdivision->get_by_id($id,$field);  
    }
}

/**
 * Quick get link for sub
 */
function p_sub_link($id) {
    $sub = p_sub($id);
       
    if (!empty($sub['ExternalURL'])){
        return $sub['ExternalURL'];
    } else {
        return $sub['Hidden_URL'];
    }    
}

/**
 * Quick get title for sub
 */
function p_sub_title($id) {    
    return p_sub($id,'Subdivision_Name');
}

/**
 * Quick resize picture
 * Функция вызывается ТОЛЬКО из Действия после добавления - изменения
 */
function p_resize($field,$size_x,$size_y,$crop = 0,$quality = 95) {
    global $nc_core;
    global $message;
    global $classID;
    global $DOCUMENT_ROOT;

    p_log('p_resize_call');

    require_once($nc_core->INCLUDE_FOLDER."classes/nc_imagetransform.class.php");

    $image_path = $DOCUMENT_ROOT.nc_file_path($classID,  $message, $field, "");
    if ($image_path && $_FILES['f_'.$field]) {
        p_log('resize images');
        nc_ImageTransform::imgResize($image_path,$image_path,$size_x,$size_y, $crop, 'jpg', $quality, $message, $field);
    }
}


/**
 * Log str to regular file
 */
function p_log($str) {
    $log = $_SERVER['DOCUMENT_ROOT'].'/netcat_cache/debug.log';
    $fh = fopen($log,"a+");
    fwrite($fh, strftime('%d.%m.%Y %T') . ': '. $str."\n");
    fclose($fh);
}

/**
 * Возращает массив с дочерними разделами.
 * если указана $field - возражает одномерный массив с колонкой (напр. Subdivision_ID) 
 */
function p_sub_childs($csub,$field = "") {
    global $db;
    global $sub;
    
    if (empty($csub)) {
        $csub = $sub;
    }
    
    // 
    if (empty($field)) {
        return $db->get_results('SELECT * FROM Subdivision WHERE     Parent_Sub_ID = '.$csub,ARRAY_A);
    } else {        
        return $db->get_col('SELECT '.$field.' FROM Subdivision WHERE     Parent_Sub_ID = '.$csub);
    }    
        
}

function p_sub_child_count($csub = null) {
    global $db;
    global $sub;

    if (empty($csub)) {
        $csub = $sub;
    }

    return $db->get_var('SELECT COUNT(*) FROM Subdivision WHERE     Parent_Sub_ID = '.$csub);
}

/**
 * Возращает размер файла в человекопонятном виде
 */
function p_human_size($size) {
    $mod = 1024;
    
    $units = explode(' ','Б Kб Mб Гб Тб Пб');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
    
    return round($size) . ' ' . $units[$i];
}

function p_file_ext($file_name) {
    $info = pathinfo($file_name,PATHINFO_EXTENSION);
    return strtolower($info);
}