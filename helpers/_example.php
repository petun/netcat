<?php

require_once("pTranslit.class.php");

$str = $_GET['str'];

$t = new pTranslit();
echo $t->enToRu($str);