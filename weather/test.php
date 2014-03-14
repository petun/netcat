<?php
require_once('pWeather.class.php');

$w = new pWeather('2124390');

$image = "/external_modules/weather/" . $w->getConditionName(true);
echo $w->getConditionName();
$tomorrow = $w->getForecast(1);


echo '<div class="weather-wid">';
	echo '<a href="http://vykza.ru/weather.html" class="weather-wid--title">Погода в Выксе</a>';
	echo '<figure class="weather-wid--img">';
		echo '<img src="'.$image.'" alt="Погода в Выксе" title="Погода в Выксе" width="30px" height="30px" />';
		echo '</figure><span class="weather-wid--current">';
echo $w->getTemp();
echo '</span><p class="weather-wid--day">';

		echo "Завтра днем ".$tomorrow['high'].", ночью ".$tomorrow['low'].", ".$tomorrow['codeText'];
		echo '</p></div>';