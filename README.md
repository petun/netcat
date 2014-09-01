Netcat Helpers
==============
Решение представляет набор классов и функций для опрощенной работы с CMS Netcat.

Установка решения
---
- cd netcat/modules/default
- git clone https://github.com/petun/netcat.git pmod

Далее в начале файла netcat/modules/default/function.php прописать
```sh
require_once(dirname(FILE).'/pmod/functions.php');
```

Проект представляет из себя большое количество разнообразных решений.

Основные функции
---

```php
/**
 * Форматирует дату
 * @param string $ctime - Теущее время в формате mysql или unix time
 * @param string $format - Формат вывода, весь формат http://ru2.php.net/manual/en/function.strftime.php
 * @param bool $lower_case - все с нижнем регистре
 * @param bool $single - дата в именительном падеже
 * @return mixed|string
 */
function p_date($ctime = "", $format = "%d %B %Y, %H:%M", $lower_case = false, $single = false)

/**
 * Выводит полный тайтл для страницы, используется в теге title
 * $separator - разделитель между страницами и разделами (по умолчанию " / ")
 * $reverse - если false, тайтл формируется так: Название сайта / Раздел / Страница,
 * если true, то наоборот: Страница / Раздел / Название сайта
 */
function p_title($separator = " / ", $reverse = false)

/**
 * Алиас к вызову $nc_core->subdivision->get_by_id($id, $field)
 * @param $id
 * @param string $field
 * @return mixed
 */
function p_sub($id, $field = "")

/**
 * Ссылка на раздел сайта. Выводится либо HiddenURL либо ExternalURL
 * @param $id
 * @return mixed
 */
function p_sub_link($id)

/**
 * Заголовок раздела
 * @param $id
 * @return mixed
 */
function p_sub_title($id)

// по аналогии три функции для работы с компонентами раздела
function p_cc($cc, $field = "")
function p_cc_title($cc)
function p_cc_link($cc)

// функции для работы с сайтом
function p_catalogue_link($catalogueId)
function p_catalogue_link_from_sub($subId)
function p_catalogue_title()


/**
 * Возвращает массив по списку в системе.
 * @param $list - Список в неткате
 * @param string $where - Условие. (a = 12)
 * @param bool $full - все поля или ключ значение
 * @return array
 */
function p_list($list, $where = "1 = 1", $full = false)

/**
 * Возвращаем массив из любого запроса.
 * @param $query
 * @param $idField
 * @param $valueField
 * @return array
 */
function p_db_list($query, $idField, $valueField)

/**
 * Quick resize picture
 * Функция вызывается ТОЛЬКО из Действия после добавления - изменения
 */
function p_resize($field, $size_x, $size_y, $crop = 0, $quality = 95);
// пример вызова функции
p_resize('preview', 100, 100, 1, 95);

/**
 * Создает превьюшку из другого поля.
 * $sourceField - имя поля большой картинки
 * $destField - имя поля результирующей
 * Функция вызывается ТОЛЬКО из Действия после добавления - изменения. Вызывается после вызова p_resize.
 * $mode - 1 - crop
 */
function p_resize_thumb($sourceField, $destField, $width, $height, $mode = 0, $format = 'jpg', $quality = 95)


/**
 * Resize with phpthumb..
 * phpThumb должен находиться в папке /phpthumb/phpThumb.php
 * Возращает ссылку на картинку с учетом ресайза
 * &w=100&h=100&zc=1&q=95
 * &w=800&h=800&q=95&zc=0&aoe=0&far=0
 */
function p_thumb($image_link, $params)

/**
 * Логирует строку текст в файл netcat_cache/debug.log
 * @param $str
 */
function p_log($str)


/**
 * Возращает массив с дочерними разделами.
 * если указана $field - возражает одномерный массив с колонкой (напр. Subdivision_ID)
 * where - условие выборки
 * sort - соритровка
 */
function p_sub_childs($csub, $field = "", $where = "", $sort = "Priority")

/**
 * Возвращает кол-во разделов в разделе $cSub
 * @param null $cSub
 * @return mixed
 */
function p_sub_child_count($cSub = null)


// строковые функции
/**
 * Возращает размер файла в человекопонятном виде
 */
function p_human_size($size)

/**
 * Отображает цены в удобочитаемом виде
 * @param $price Цисло
 * @param bool $addTrifle Добавлять или нет копейки.
 * @return mixed|string
 */
function p_human_price($price, $addTrifle = false)


/**
 * Правильное формирование окончаний, в зависимости от количества
 * @param $digit Число
 * @param $variants Массив из трех вариантов ('один','два','три')
 * @param bool $onlyWord возвращать только строку, в противном случае вместе с числом
 * @return string
 */
function p_human_decl($digit, $variants, $onlyWord = false) 

// функции для работы с файлами
/**
 * Возвращает разрешение файла
 * @param $file_name
 * @return string
 */
function p_file_ext($file_name) 

/**
 * Возвращает путь до файла из поля БД
 * @param $field
 * @return string
 */
function p_file_path($field)

/**
 * Возвращает расширение по mime типу
 * @param $file_type
 * @return mixed
 */
function p_file_ext_from_type($file_type)
```





```php
<?=nc_browse_sub(19,$browse_secondary,0,'ExternalURL IS NOT NULL');?>
<?=$nc_core->widget->generate('Sidebar', array('show_news'=>true));?>
```




```php
MultiField
Array ( [Field_ID] => 2298 [Message_ID] => 1 [Priority] => [Name] => [Size] => 2538435 [Path] => /netcat_files/multifile/2298/user.pdf [Preview] => /netcat_files/multifile/2298/preview_user.pdf [ID] => 1 )
```