<?


require_once("pYaMetrika.class.php");

$token = 'da2fd287e9c74bb6b8b278b8cd5e33ba';
$ps_counter_id = '22081498';

$m = new pYaMetrika('da2fd287e9c74bb6b8b278b8cd5e33ba');
$today = $m->today($ps_counter_id);
print_r($today);


$week = $m->week($ps_counter_id);
print_r($week);


$month = $m->lastMonth($ps_counter_id);
print_r($month);