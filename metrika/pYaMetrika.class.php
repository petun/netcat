<?

class pYaMetrika {

	protected $_token;


	protected $_statDayUrl = "http://api-metrika.yandex.ru/stat/traffic/summary.json?id=%d&oauth_token=%s&pretty=1";
	protected $_statMonthUrl = "http://api-metrika.yandex.ru/stat/traffic/summary.json?id=%d&oauth_token=%s&pretty=1&group=month&date1=%s&date2=%s";


	protected $_dStorage = array();

	const DATEFORMAT = '%Y%m%d';

	public function __construct($token) {
		$this->_token = $token;
	}

	public function today($counter_id) {		
		$json = $this->download(sprintf($this->_statDayUrl,$counter_id, $this->_token));
		$data =  $this->parseData($json);
		if ($data) {
			return $this->dataFromObject( $data->data[0] );			
		}
	}

	private function dataFromObject($object) {
		return array(
				'visitors'=>$object->visitors,
				'visits'=>$object->visits,
				'page_views'=>$object->page_views,
		); 
	}

	public function week($counter_id) {
		$json = $this->download(sprintf($this->_statDayUrl,$counter_id, $this->_token));
		$data =  $this->parseData($json);
		if ($data) {	
			return $this->dataFromObject( $data->totals );							
		}
	}

	public function lastMonth($counter_id) {
		$date1 = strftime(self::DATEFORMAT,time() - 30*24*60*60 );
		$date2 = strftime(self::DATEFORMAT);
		return $this->month($counter_id,$date1,$date2);
	}

	public function month($counter_id,$date1,$date2) {
		$json = $this->download(sprintf($this->_statMonthUrl,$counter_id, $this->_token,$date1,$date2));
		$data =  $this->parseData($json);
		if ($data) {			
			return $this->dataFromObject( $data->totals );							
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