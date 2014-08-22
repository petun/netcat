<?php

/**
 * @param string $ctime - Теущее время в формате mysql или unix time
 * @param string $format - Формат вывода, весь формат http://ru2.php.net/manual/en/function.strftime.php
 * @param bool $lower_case - все с нижнем регистре
 * @param bool $single - дата в именительном падеже
 * @return mixed|string
 */
function p_date($ctime = "", $format = "%d %B %Y, %H:%M", $lower_case = false, $single = false) {

	$ctime = empty($ctime) ? time() : $ctime;

	// если дата в формате mysql - делаем обратное преобразование
	if (!preg_match('/^\d+$/', $ctime)) {
		$ctime = strtotime($ctime);
	}

	// добаялем тег для обработки и замены в будущем
	$format = str_replace('%B', '--%m--', $format);

	// для дня недели
	$format = str_replace('%A', '++%u++', $format);


	// формируем время
	$r = strftime($format, $ctime);
	// заменяем месяц на русское название
	$month_callback = $single ? '_get_rus_month_single' : '_get_rus_month';
	$r = preg_replace_callback("/--(\d{1,2})--/", $month_callback, $r);

	// заменяем название дня недели на русское название
	$r = preg_replace_callback("/\+\+(\d{1,2})\+\+/", '_get_rus_day', $r);


	if ($lower_case) {
		$r = mb_strtolower($r);
	}

	return $r;

}

function _get_rus_month($num) {
	$num = $num[1] * 1;

	$months = array(
		1 => "Января"
	, 2 => "Февраля"
	, 3 => "Марта"
	, 4 => "Апреля"
	, 5 => "Мая"
	, 6 => "Июня"
	, 7 => "Июля"
	, 8 => "Августа"
	, 9 => "Сентября"
	, 10 => "Октября"
	, 11 => "Ноября"
	, 12 => "Декабря"
	);
	return $months[$num];
}

function _get_rus_month_single($num) {
	$num = $num[1] * 1;

	$months = array(
		1 => "Январь"
	, 2 => "Февраль"
	, 3 => "Март"
	, 4 => "Апрель"
	, 5 => "Май"
	, 6 => "Июнь"
	, 7 => "Июль"
	, 8 => "Август"
	, 9 => "Сентябрь"
	, 10 => "Октябрь"
	, 11 => "Ноябрь"
	, 12 => "Декабрь"
	);
	return $months[$num];
}


function _get_rus_day($num) {
	$days = array('Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');
	return $days[$num[1] * 1 - 1];
}

/**
 * Выводит полный тайтл для страницы, используется в теге title
 * $separator - разделитель между страницами и разделами (по умолчанию " / ")
 * $reverse - если false, тайтл формируется так: Название сайта / Раздел / Страница,
 *  если true, то наоборот: Страница / Раздел / Название сайта
 */
function p_title($separator = " / ", $reverse = false) {
	global $nc_core;
	global $sub;
	global $current_catalogue;
	global $sub_level_count;


	$browse_top[prefix] = "";
	$browse_top[active] = "%NAME";
	$browse_top[active_link] = "%NAME";
	$browse_top[unactive] = "%NAME";
	$browse_top[divider] = " ::: ";
	$browse_top[suffix] = "";

	if ($nc_core->page->get_title()) {
		return $nc_core->page->get_title();
	} else {
        // Создаем массив элементов "хлебных крошек"
        $arr_titles[] = $current_catalogue[Catalogue_Name];
        // Если главная
        if ($sub == $current_catalogue[Title_Sub_ID]) {
            $arr_titles[] = 'Главная';
        } else {
            $arr_titles = array_merge($arr_titles, explode($browse_top[divider], strip_tags(s_browse_path_range(-1, $sub_level_count - 1, $browse_top))) );
        }
        // Выводим прямой или развернутый тайтл в зависимости от $reverse
        return $reverse ? implode($separator, array_reverse($arr_titles)) : implode($separator, $arr_titles);
	}
}

/**
 * @param $id
 * @param string $field
 * @return mixed
 */
function p_sub($id, $field = "") {
	global $nc_core;
	global $sub;
	global $current_sub;

	if ($id == $sub) {
		return (!empty($field) ? $current_sub[$field] : $current_sub);
	} else {
		return $nc_core->subdivision->get_by_id($id, $field);
	}
}

/**
 * Ссылка на раздел сайта. Выводится либо HiddenURL либо ExternalURL
 * @param $id
 * @return mixed
 */
function p_sub_link($id) {
	$sub = p_sub($id);

	if (!empty($sub['ExternalURL'])) {
		return $sub['ExternalURL'];
	} else {
		return $sub['Hidden_URL'];
	}
}

/**
 * Заголовок раздела
 * @param $id
 * @return mixed
 */
function p_sub_title($id) {
	return p_sub($id, 'Subdivision_Name');
}


function p_cc($cc, $field = "") {
	global $current_cc;
	global $nc_core;

	if ($current_cc['Sub_Class_ID'] == $cc) {
		return empty($field) ? $current_cc : $current_cc[$field];
	} else {
		return $nc_core->sub_class->get_by_id($cc, $field);
	}

}

function p_cc_title($cc) {
	return p_cc($cc, 'Sub_Class_Name');
}

function p_cc_link($cc) {
	$sub = p_cc($cc, 'Subdivision_ID');
	$link = p_cc($cc, 'EnglishName');

	return p_sub_link($sub) . $link . '.html';
}

/**
 * Quick resize picture
 * Функция вызывается ТОЛЬКО из Действия после добавления - изменения
 */
function p_resize($field, $size_x, $size_y, $crop = 0, $quality = 95) {
	global $nc_core;
	global $message;
	global $classID;
	global $DOCUMENT_ROOT;

	p_log('p_resize_call');

	require_once($nc_core->INCLUDE_FOLDER . "classes/nc_imagetransform.class.php");

	$image_path = $DOCUMENT_ROOT . nc_file_path($classID, $message, $field, "");
	if ($image_path && $_FILES['f_' . $field]) {
		p_log('resize images');
		nc_ImageTransform::imgResize($image_path, $image_path, $size_x, $size_y, $crop, 'jpg', $quality, $message, $field);
	}
}

/**
 * Создает превьюшку из другого поля.
 * $sourceField - имя поля большой картинки
 * $destField - имя поля результирующей
 * Функция вызывается ТОЛЬКО из Действия после добавления - изменения. Вызывается после вызова p_resize.
 * $mode - 1 - crop
 */
function p_resize_thumb($sourceField, $destField, $width, $height, $mode = 0, $format = 'jpg', $quality = 95) {
	global $nc_core;
	p_log('p_resize_thumb call');

	// если грузится файл...
	if ($_FILES['f_' . $sourceField]) {
		p_log('p_resize_thumb resize');
		require_once($nc_core->INCLUDE_FOLDER . "classes/nc_imagetransform.class.php");
		nc_ImageTransform::createThumb($sourceField, $destField, $width, $height, $mode, $format, $quality);
	}
}


/**
 * Resize with phpthumb..
 * phpThumb должен находиться в папке /phpthumb/phpThumb.php
 * Возращает ссылку на картинку с учетом ресайза
 * &w=100&h=100&zc=1&q=95
 * &w=800&h=800&q=95&zc=0&aoe=0&far=0
 */
function p_thumb($image_link, $params) {
	return '/phpthumb/phpThumb.php?src=' . $image_link . $params;
}



/**
 * Логирует строку текст в файл netcat_cache/debug.log
 * @param $str
 */
function p_log($str) {
	$log = $_SERVER['DOCUMENT_ROOT'] . '/netcat_cache/debug.log';
	$fh = fopen($log, "a+");
	fwrite($fh, strftime('%d.%m.%Y %T') . ': ' . $str . "\n");
	fclose($fh);
}

/**
 * Возращает массив с дочерними разделами.
 * если указана $field - возражает одномерный массив с колонкой (напр. Subdivision_ID)
 * where - условие выборки
 * sort - соритровка
 */
function p_sub_childs($csub, $field = "", $where = "", $sort = "Priority") {
	global $db;
	global $sub;

	if (empty($csub)) {
		$csub = $sub;
	}

	if ($where) {
		$where = ' AND ' . $where;
	}

	// 
	if (empty($field)) {
		$r = $db->get_results('SELECT * FROM Subdivision WHERE     Parent_Sub_ID = ' . $csub . $where . ' ORDER BY ' . $sort, ARRAY_A);

		// ДОПОЛНИТЕЛЬНАЯ ОБРАБОТКА ССЫЛКИ И ИЗОБРАЖЕНИЯ
		if ($r) {
			foreach ($r as &$s) {
				$link = $s['ExternalURL'] ? $s['ExternalURL'] : $s['Hidden_URL'];
				$img = $s['img'] ? p_file_path($s['img']) : '';
				$s['_link'] = $link;
				$s['_img'] = $img;
			}
		}

		return $r;
	} else {
		return $db->get_col('SELECT ' . $field . ' FROM Subdivision WHERE     Parent_Sub_ID = ' . $csub . $where . ' ORDER BY ' . $sort);
	}

}

function p_sub_all_childs($parent_sub, &$ida, $where = "") {
	$subs = p_sub_childs($parent_sub, "Subdivision_ID", $where);
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

	return $db->get_var('SELECT COUNT(*) FROM Subdivision WHERE     Parent_Sub_ID = ' . $csub);
}

function p_catalogue_link($catalogue_id) {
	global $nc_core;
	return 'http://' . $nc_core->catalogue->get_by_id($catalogue_id, 'Domain');
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


/**
 * @param $list - Список в неткате
 * @param string $where - Условие. (a = 12)
 * @param bool $full - все поля или ключ значение
 * @return array
 */
function p_list($list, $where = "1 = 1", $full = false) {
	global $db;

	$name = 'Classificator_' . $list;
	$field_id = $list.'_ID';
	$field_name = $list.'_Name';
	$field_value = 'Value';
	$field_order = $list.'_Priority';

	$r = array();
	$rows =  $db->get_results("SELECT  $field_id as id,$field_name as name,$field_value as value FROM $name WHERE $where ORDER BY $field_order",ARRAY_A);
	if (is_array($r)) {
		foreach ($rows as $row) {
			if ($full) {
				$r[$row['id']] = array(
					'name'=> $row['name'],
					'value'=>$row['value']
				);
			} else {
				$r[$row['id']] = $row['name'];
			}
		}
	}

	return $r;
}

/**
 * Возвращаем асоциативный массив из запроса
 * */
function p_db_list($query, $idField, $valueField) {
	global $db;
	$result = array();
	$rows = $db->get_results($query, ARRAY_A);

	if ($rows) {
		foreach ($rows as $row) {
			$result[$row[$idField]] = $row[$valueField];
		}
	}

	return $result;
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

	$units = explode(' ', 'Б Kб Mб Гб Тб Пб');
	for ($i = 0; $size > $mod; $i++) {
		$size /= $mod;
	}

	return round($size) . ' ' . $units[$i];
}


function p_human_price($price, $addKopek = false) {
	$r = str_replace(',', ' ', number_format($price));
	if ($addKopek) {
		return $r . '.00';
	} else {
		return $r;
	}
}

function p_human_decl($digit, $variants, $onlyWord = false) {

	$i = $onlyWord ? '' : $digit . ' ';

	if ($digit >= 5 && $digit <= 20) {
		$res = $i . $variants[2];
	} else {
		$digit = $digit % 10;
		if ($digit == 1) {
			$res = $i . $variants[0];
		} else if ($digit >= 2 && $digit <= 4) {
			$res = $i . $variants[1];
		} else {
			$res = $i . $variants[2];
		}
	}
	return $res;
}

/**
 * Функция для формирования массива из выборки.. 1 - столбец = ключ, 2 - значение ключа
 **/
function p_db_options($query) {
	global $db;
	$result = array();

	$items = $db->get_results($query, ARRAY_N);
	if ($items) {
		foreach ($items as $item) {
			$result[$item[0]] = $item[1];
		}
	}

	return $result;
}

function p_file_ext($file_name) {
	$info = pathinfo($file_name, PATHINFO_EXTENSION);
	return strtolower($info);
}

/* возвращает путь до файла от поля */
function p_file_path($field) {
	$paths = explode(':', $field);
	if ($paths[3]) {
		return '/netcat_files/' . $paths[3];
	}
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