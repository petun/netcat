<?
require_once("pTranslate.class.php");

$t = new pTranslate(999);
var_dump($t->get('testKey'));
var_dump($t->get('unused_key'));


$t = new pTranslate(1);
var_dump($t->get('unknown_catalogue_key'));