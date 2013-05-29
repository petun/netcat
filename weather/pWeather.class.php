<?

/**
* ID http://weather.yahoo.com/
* http://weather.yahoo.com/russia/nizhny-novgorod-oblast/vyksa-2124390/ - Vyksa
* http://weather.yahoo.com/russia/tsentralniy-federalniy-okrug/moscow-2122265/
* http://weather.yahoo.com/russia/uralskiy-federalniy-okrug/chelyabinsk-1997422/
* 2049384 - Nab Chelny
**/


class pWeather {

	private $_link;
	private $_cacheDir;
	private $_w;
	private $_cacheFile;
	private $_city;
	//private $_imgDir;

	private $_wind;
	private $_tempreture;	
	private $_condition;

	private $_filePath;
	private $_urlPath;

	const CACHE_TIME = 3600;

	public function __construct($w) {
		$this->_w = $w;
		$this->_link = "http://weather.yahooapis.com/forecastrss?w=".$w."&u=c";
		$this->_cacheDir = dirname(__FILE__).'/cache/';
		$this->_filePath = dirname(__FILE__).'/images/';

		$this->_urlPath = 'images/';

		
		$this->_cacheFile = $this->_cacheDir . $this->_w . '.xml';

		$rss = $this->_getRss();


		if ($rss) {
			$xml = new SimpleXMLElement($rss);
			// Ветер
			$tmp = $xml->xpath('/rss/channel/yweather:wind');
			if($tmp === false) throw new Exception("Error parsing XML.");
			$this->_wind = $tmp[0];

			// Текущая температура воздуха и погода
			$tmp = $xml->xpath('/rss/channel/item/yweather:condition');
			if($tmp === false) throw new Exception("Error parsing XML.");
			$tmp = $tmp[0];
			
			$this->_tempreture = (int)$tmp['temp'];			
			$this->_condition = (int)$tmp['code'];

			//$this->condition_text = strtolower((string)$tmp['text']);

			$location = $xml->xpath('/rss/channel/yweather:location');
			$this->_city = (string)$location[0]['city'];
			
		}


	}	

	private function _getRss() {
		if (file_exists($this->_cacheFile)) {			
			if ( (time() - filemtime($this->_cacheFile)) < self::CACHE_TIME) {
				return @file_get_contents($this->_cacheFile);
			}
		}

		return $this->_updateCache();
	}

	private function _updateCache() {
		$xml_contents = @file_get_contents($this->_link);
		if ($xml_contents) {		
			file_put_contents($this->_cacheFile , $xml_contents);
			return $xml_contents;
		}
	}

	public function getConditionName($asImage = false) {
		$cond = array(
			0 => 'торнадо'
			,1 => 'тропический шторм'
			,2 => 'ураган'
			,3 => 'сильная гроза'
			,4 => 'грозы'
			,5 => 'дождь со снегом'
			,6 => 'дождь со снегом'
			,7 => 'дождь со снегом'
			,8 => 'изморозь'
			,9 => 'небольшой дождь'
			,10 => 'ледяной дождь'
			,11 => 'дожди'
			,12 => 'дожди'
			,13 => 'порывы снега'
			,14 => 'небольшой снег'
			,15 => 'метель'
			,16 => 'снег'
			,17 => 'град'
			,18 => 'мокрый снег'
			,19 => 'dust'
			,20 => 'туман'
			,21 => 'haze'
			,22 => 'smoky'
			,23 => 'ветренно'
			,24 => 'ветренно'
			,25 => 'холодно'
			,26 => 'облачно'
			,27 => 'облачно'
			,28 => 'облачно'
			,29 => 'небольшая облачность'
			,30 => 'небольшая облачность'
			,31 => 'ясно'
			,32 => 'солнечно'
			,33 => 'fair (night)'
			,34 => 'ясно'
			,35 => 'дождь с градом'
			,36 => 'жарко'
			,37 => 'местами грозы'
			,38 => 'местами грозы'
			,39 => 'местами грозы'
			,40 => 'местами дожди'
			,41 => 'снег'
			,42 => 'снег'
			,43 => 'снегопад'
			,44 => 'переменная облачность'
			,45 => 'гроза'
			,46 => 'снег'
			,47 => 'местами грозы'
			,3200 => 'недоступно'
			);

		if ($asImage) {
			return $this->_urlPath . $this->getCondition() . '.png';
		} else {
			return $cond[$this->getCondition()];	
		}
		
	}

	public function getWind() {
		
		$wind_chill = (int)$this->_wind['chill'];
		$wind_direction = (int)$this->_wind['direction'];
		$wind_speed = (int)$this->_wind['speed'];

		return $wind_chill . ", " . $wind_speed . " км/ч";
	}

	public function getTemp() {
		$prefix = '';
		if ($this->_tempreture > 0) {
			$prefix =  '+';
		} else if ($this->_tempreture < 0) {
			$prefix = '-';
		}
		return $prefix . $this->_tempreture;
	}	

	public function getCondition() {
		return $this->_condition;
	}

	public function getCity() {
		return $this->_city;
	}


}