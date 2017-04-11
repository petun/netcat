# pTranslate Class
Модуль для поддержки мультиязычности в системе Netcat.

## Использование
Для начала необходимо в папке lang с модулем создать файлы с массивов языковых настроек, и назвать его в формате <catalogueId>.php. Пример файла.
```php
$lang['menuSub'] = 'test key value';
$lang['testKey'] = 'test key value';
```

Для использования компонент необходимо проинициализировать. Например в шаблоне:
```php
require_once($_SERVER['DOCUMENT_ROOT'] . 'pmod/translate/pTranslate.class.php');
$translateComponent = new pTranslate($current_catalogue['Catalogue_ID']);
```
Для использования в макете:
```php
$translateComponent->get('testKey');

// проверка хранилища
var_dump($translateComponent->getAll());
var_dump($translateComponent->getCatalogueId());
```