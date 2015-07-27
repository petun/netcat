# pTranslate Class
Модуль для поддержки мультиязычности в системе Netcat.

## Использование
Для начала необходимо в папке lang с модулем создать файлы с массивов языковых настроек, и назвать его в формате <catalogueId>.php. Пример файла.
```php
$lang['menuSub'] = 'test key value';
$lang['testKey'] = 'test key value';
```

Для использования компонент необходимо проинициализировать.
В файле /netcat/modules/default/functions.php
```php
require_once("pmod/translate/pTranslate.php");
$translateComponent = new pTranslate($current_catalogue['Catalogue_ID']);
```
Для использования в макете:
```php
$translateComponent->get('testKey');
```