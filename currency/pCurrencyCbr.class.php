<?

class pCurrencyCbr {

	protected $_url = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=%s';

	public function __construct() {

	}

	/**
	*
	**/
	public function today() {
		return $this->parse(strftime('%Y-%m-%d'));
	}


	/**
	* $date - Дата в формате MySQL
	**/
	public function parse($date) {
		$date = strftime('%d.%m.%Y',strtotime($date));
		$xml = $this->_getXmlContent( sprintf($this->_url,$date)  );
		if ($xml) {
			//print_r( (array)$xml );
			foreach ($xml->Valute as $Valute) {				
				$data = array(
					'NumCode'=>(string) $Valute->NumCode,
					'CharCode'=>(string) $Valute->CharCode,
					'Nominal'=>(string) $Valute->Nominal,
					'Name'=>(string) $Valute->Name,
					'Value'=>(string) $Valute->Value,					
					);
				$r[] = $data;
			}			
		}

		return $r;
	}

	protected function _getXmlContent($url) {
		$retries = 5;
		$try = 1;

		$context = stream_context_create(array('http' => array( 
      	  'timeout' => 1
        	)) );

		// пытаемся получить содержимое 3 раз
		while ($try <= $retries) {
			$raw = file_get_contents($url,0,$context);
			if ($raw) {
				break;
			}	
			echo "Timeout or error - sleep 5 seconds... Try ". $try++;		
			sleep(5);
		}
		
		return simplexml_load_string($raw);
	}
}