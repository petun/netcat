<?

class pYaMetrika {

	protected $_token;


	//protected $_statDayUrl = "http://api-metrika.yandex.ru/stat/traffic/summary.json?id=%d&oauth_token=%s&pretty=1";
	//protected $_statMonthUrl = "http://api-metrika.yandex.ru/stat/traffic/summary.json?id=%d&oauth_token=%s&pretty=1&group=month&date1=%s&date2=%s";
  protected $_statDayUrl = "https://api-metrika.yandex.ru/stat/v1/data?id=%d&oauth_token=%s&metrics=ym:s:visits,ym:s:pageviews,ym:s:users&date1=today&pretty=1";
  protected $_statPeriodUrl = "https://api-metrika.yandex.ru/stat/v1/data?id=%d&oauth_token=%s&metrics=ym:s:visits,ym:s:pageviews,ym:s:users&date1=%s&date2=%s&pretty=1";


	protected $_dStorage = array();

	const DATEFORMAT = '%Y%m%d';

	public function __construct($token) {
		$this->_token = $token;
	}

	public function today($counter_id) {		
		$json = $this->download(sprintf($this->_statDayUrl,$counter_id, $this->_token));
		$data =  $this->parseData($json);
		if ($data) {
			return $this->dataFromObject( array_pop($data->data) );			
		}
	}
  /*
	private function dataFromObject($object) {
		return array(
				'visitors'=>$object->visitors,
				'visits'=>$object->visits,
				'page_views'=>$object->page_views,
		); 
	}
  */     
  private function dataFromObject($object) {
		return array(
				'visits'=>$object->metrics[0],
				'page_views'=>$object->metrics[1],
				'visitors'=>$object->metrics[2],
		); 
	}
  /*
	public function week($counter_id) {
		$json = $this->download(sprintf($this->_statDayUrl,$counter_id, $this->_token));
		$data =  $this->parseData($json);
		if ($data) {	
			return $this->dataFromObject( $data->totals );							
		}
	} */
	public function lastWeek($counter_id) {
		$date1 = strftime(self::DATEFORMAT,time() - 7*24*60*60 );
		$date2 = strftime(self::DATEFORMAT);
		return $this->period($counter_id,$date1,$date2);
	}
	public function lastMonth($counter_id) {
		$date1 = strftime(self::DATEFORMAT,time() - 30*24*60*60 );
		$date2 = strftime(self::DATEFORMAT);
		return $this->period($counter_id,$date1,$date2);
	}

	public function period($counter_id,$date1,$date2) {
		$json = $this->download(sprintf($this->_statPeriodUrl,$counter_id,$this->_token,$date1,$date2));
		$data =  $this->parseData($json);
		if ($data) {			
			return $this->dataFromObject( array_pop($data->data) );							
		}
	}

	private function parseData($json) {
		return json_decode($json);
	}

	private function download($url) {		
		if (array_key_exists($url, $this->_dStorage)) {
			return $this->_dStorage[$url];
		}

		$raw =  file_get_contents($url);
		if ($raw) {
			$this->_dStorage[$url] = $raw;
			return $raw;
		}

		return false;
	}
}
