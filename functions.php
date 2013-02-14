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


function p_cc($cc,$field = "") {
    global $current_cc;
    global $nc_core;
    
    if ($current_cc['Sub_Class_ID'] == $cc) {
        return empty($field) ? $current_cc : $current_cc[$field];
    } else {
        return $nc_core->sub_class->get_by_id($cc,$field);
    }
    
}

function p_cc_title($cc) {    
    return p_cc($cc,'Sub_Class_Name');
}

function p_cc_link($cc) {
    $sub = p_cc($cc, 'Subdivision_ID');    
    $link = p_cc($cc, 'EnglishName');
    
    return p_sub_link($sub).$link.'.html';
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
 * Resize with phpthumb.. 
 * phpThumb должен находиться в папке /phpthumb/phpThumb.php
 * Возращает ссылку на картинку с учетом ресайза
 */
function p_thumb($image_link,$params) {
    return '/phpthumb/phpThumb.php?src='.$image_link.$params;
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
 * ДОБИТЬ WHERE!!! - пока не работает
 */
function p_sub_childs($csub,$field = "",$where = "") {
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

function p_sub_all_childs($parent_sub,&$ida,$where = "") {           
      $subs = p_sub_childs($parent_sub,"Subdivision_ID",$where); 
      if ($subs) { 
          foreach ($subs as $s) { 
              $ida[] = $s; 
              p_sub_all_childs($s['Subdivision_ID'], $ida); 
          } 
           
      } else { 
          return false; 
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

function p_catalogue_link($catalogue_id) {    
    global $nc_core;
    return 'http://'. $nc_core->catalogue->get_by_id($catalogue_id,'Domain');    
}

function p_catalogue_link_from_sub($subid) {
    global $db;
    return $db->get_var("SELECT 
 CONCAT('http://',Catalogue.domain)
 FROM
Subdivision
JOIN 
 Catalogue ON (Catalogue.Catalogue_ID = Subdivision.Catalogue_ID)
 WHERE 
 Subdivision.Subdivision_ID = $subid 
 ");
}

function p_catalogue_title() {
    global $current_catalogue;
    return $current_catalogue['Catalogue_Name'];
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


function p_human_price($price) {
    return str_replace(',', ' ', number_format($price));
}

function p_human_decl($digit, $variants,$onlyWord = false) {    
    
    $i = $onlyWord  ? '' : $digit . ' ';
    
    if ($digit >= 5 && $digit <= 20) {            
        $res = $i . $variants[2];            
    } else {
        $digit = $digit % 10;
        if($digit == 1) {$res = $i . $variants[0];}
        else if($digit >=2 && $digit <=4) {$res = $i . $variants[1];}
        else  {$res = $i . $variants[2];}
    }        
    return $res;
}

function p_file_ext($file_name) {
    $info = pathinfo($file_name,PATHINFO_EXTENSION);
    return strtolower($info);
}

function p_file_ext_from_type($file_type) {
    $mime_types = array("323" => "text/h323",
        "acx" => "application/internet-property-stream",
        "ai" => "application/postscript",
        "aif" => "audio/x-aiff",
        "aifc" => "audio/x-aiff",
        "aiff" => "audio/x-aiff",
        "asf" => "video/x-ms-asf",
        "asr" => "video/x-ms-asf",
        "asx" => "video/x-ms-asf",
        "au" => "audio/basic",
        "avi" => "video/x-msvideo",
        "axs" => "application/olescript",
        "bas" => "text/plain",
        "bcpio" => "application/x-bcpio",
        "bin" => "application/octet-stream",
        "bmp" => "image/bmp",
        "c" => "text/plain",
        "cat" => "application/vnd.ms-pkiseccat",
        "cdf" => "application/x-cdf",
        "cer" => "application/x-x509-ca-cert",
        "class" => "application/octet-stream",
        "clp" => "application/x-msclip",
        "cmx" => "image/x-cmx",
        "cod" => "image/cis-cod",
        "cpio" => "application/x-cpio",
        "crd" => "application/x-mscardfile",
        "crl" => "application/pkix-crl",
        "crt" => "application/x-x509-ca-cert",
        "csh" => "application/x-csh",
        "css" => "text/css",
        "dcr" => "application/x-director",
        "der" => "application/x-x509-ca-cert",
        "dir" => "application/x-director",
        "dll" => "application/x-msdownload",
        "dms" => "application/octet-stream",
        "doc" => "application/msword",
        "docx" => "application/msword",
        "dot" => "application/msword",
        "dvi" => "application/x-dvi",
        "dxr" => "application/x-director",
        "eps" => "application/postscript",
        "etx" => "text/x-setext",
        "evy" => "application/envoy",
        "exe" => "application/octet-stream",
        "fif" => "application/fractals",
        "flr" => "x-world/x-vrml",
        "gif" => "image/gif",
        "gtar" => "application/x-gtar",
        "gz" => "application/x-gzip",
        "h" => "text/plain",
        "hdf" => "application/x-hdf",
        "hlp" => "application/winhlp",
        "hqx" => "application/mac-binhex40",
        "hta" => "application/hta",
        "htc" => "text/x-component",
        "htm" => "text/html",
        "html" => "text/html",
        "htt" => "text/webviewhtml",
        "ico" => "image/x-icon",
        "ief" => "image/ief",
        "iii" => "application/x-iphone",
        "ins" => "application/x-internet-signup",
        "isp" => "application/x-internet-signup",
        "jfif" => "image/pipeg",
        "jpe" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "jpg" => "image/jpeg",
        "js" => "application/x-javascript",
        "latex" => "application/x-latex",
        "lha" => "application/octet-stream",
        "lsf" => "video/x-la-asf",
        "lsx" => "video/x-la-asf",
        "lzh" => "application/octet-stream",
        "m13" => "application/x-msmediaview",
        "m14" => "application/x-msmediaview",
        "m3u" => "audio/x-mpegurl",
        "man" => "application/x-troff-man",
        "mdb" => "application/x-msaccess",
        "me" => "application/x-troff-me",
        "mht" => "message/rfc822",
        "mhtml" => "message/rfc822",
        "mid" => "audio/mid",
        "mny" => "application/x-msmoney",
        "mov" => "video/quicktime",
        "movie" => "video/x-sgi-movie",
        "mp2" => "video/mpeg",
        "mp3" => "audio/mpeg",
        "mpa" => "video/mpeg",
        "mpe" => "video/mpeg",
        "mpeg" => "video/mpeg",
        "mpg" => "video/mpeg",
        "mpp" => "application/vnd.ms-project",
        "mpv2" => "video/mpeg",
        "ms" => "application/x-troff-ms",
        "mvb" => "application/x-msmediaview",
        "nws" => "message/rfc822",
        "oda" => "application/oda",
        "p10" => "application/pkcs10",
        "p12" => "application/x-pkcs12",
        "p7b" => "application/x-pkcs7-certificates",
        "p7c" => "application/x-pkcs7-mime",
        "p7m" => "application/x-pkcs7-mime",
        "p7r" => "application/x-pkcs7-certreqresp",
        "p7s" => "application/x-pkcs7-signature",
        "pbm" => "image/x-portable-bitmap",
        "pdf" => "application/pdf",
        "pfx" => "application/x-pkcs12",
        "pgm" => "image/x-portable-graymap",
        "pko" => "application/ynd.ms-pkipko",
        "pma" => "application/x-perfmon",
        "pmc" => "application/x-perfmon",
        "pml" => "application/x-perfmon",
        "pmr" => "application/x-perfmon",
        "pmw" => "application/x-perfmon",
        "pnm" => "image/x-portable-anymap",
        "pot" => "application/vnd.ms-powerpoint",
        "ppm" => "image/x-portable-pixmap",
        "pps" => "application/vnd.ms-powerpoint",
        "ppt" => "application/vnd.ms-powerpoint",
        "prf" => "application/pics-rules",
        "png" => "image/png",
        "ps" => "application/postscript",
        "pub" => "application/x-mspublisher",
        "qt" => "video/quicktime",
        "ra" => "audio/x-pn-realaudio",
        "ram" => "audio/x-pn-realaudio",
        "ras" => "image/x-cmu-raster",
        "rgb" => "image/x-rgb",
        "rmi" => "audio/mid",
        "roff" => "application/x-troff",
        "rtf" => "application/rtf",
        "rtx" => "text/richtext",
        "scd" => "application/x-msschedule",
        "sct" => "text/scriptlet",
        "setpay" => "application/set-payment-initiation",
        "setreg" => "application/set-registration-initiation",
        "sh" => "application/x-sh",
        "shar" => "application/x-shar",
        "sit" => "application/x-stuffit",
        "snd" => "audio/basic",
        "spc" => "application/x-pkcs7-certificates",
        "spl" => "application/futuresplash",
        "src" => "application/x-wais-source",
        "sst" => "application/vnd.ms-pkicertstore",
        "stl" => "application/vnd.ms-pkistl",
        "stm" => "text/html",
        "svg" => "image/svg+xml",
        "sv4cpio" => "application/x-sv4cpio",
        "sv4crc" => "application/x-sv4crc",
        "t" => "application/x-troff",
        "tar" => "application/x-tar",
        "tcl" => "application/x-tcl",
        "tex" => "application/x-tex",
        "texi" => "application/x-texinfo",
        "texinfo" => "application/x-texinfo",
        "tgz" => "application/x-compressed",
        "tif" => "image/tiff",
        "tiff" => "image/tiff",
        "tr" => "application/x-troff",
        "trm" => "application/x-msterminal",
        "tsv" => "text/tab-separated-values",
        "txt" => "text/plain",
        "uls" => "text/iuls",
        "ustar" => "application/x-ustar",
        "vcf" => "text/x-vcard",
        "vrml" => "x-world/x-vrml",
        "wav" => "audio/x-wav",
        "wcm" => "application/vnd.ms-works",
        "wdb" => "application/vnd.ms-works",
        "wks" => "application/vnd.ms-works",
        "wmf" => "application/x-msmetafile",
        "wps" => "application/vnd.ms-works",
        "wri" => "application/x-mswrite",
        "wrl" => "x-world/x-vrml",
        "wrz" => "x-world/x-vrml",
        "xaf" => "x-world/x-vrml",
        "xbm" => "image/x-xbitmap",
        "xla" => "application/vnd.ms-excel",
        "xlc" => "application/vnd.ms-excel",
        "xlm" => "application/vnd.ms-excel",
        "xls" => "application/vnd.ms-excel",
        "xlt" => "application/vnd.ms-excel",
        "xlw" => "application/vnd.ms-excel",
        "xof" => "x-world/x-vrml",
        "xpm" => "image/x-xpixmap",
        "xwd" => "image/x-xwindowdump",
        "z" => "application/x-compress",
        "zip" => "application/zip");
    
    if ($type = array_search($file_type, $mime_types)) {
        return $type;
    }
    return $file_type;
}