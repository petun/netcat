<?

class pUrlCreator
{

	private $_classes;

	/**
	 * @param $classToMonitor array массив, где ключами являются ID классов,
	 * а значениями поле - источник для формирования тайтла
	 */
	public function __construct($classToMonitor) {
		$this->_classes = $classToMonitor;

		$nc_core = nc_Core::get_object();
		$nc_core->event->bind($this, array('addMessage' => 'addMessage'));
	}

	public function addMessage($Catalogue_ID, $Subdivision_ID, $Sub_Class_ID, $Class_ID, $Message_ID) {
		if (array_key_exists($Class_ID, $this->_classes)) {
			$message = nc_Core::get_object()->message->get_by_id($Class_ID, $Message_ID);
			if (empty($message['Keyword'])) {
				$keyword = $this->_titleFromString($message[$this->_classes[$Class_ID]]);
				if (!empty($keyword)) {
					$keyword = $keyword.'-id'.$Message_ID;
					p_log('Generate title and save. ID '.$Message_ID.', keyword: '.$keyword);
					nc_Core::get_object()->db->query("UPDATE Message$Class_ID SET Keyword = '$keyword' WHERE Message_ID = $Message_ID");
				}
			}
		}
	}

	private function _titleFromString($str) {
		// заменяем пробелы на - , и удаляем все ненужные символы
		$from = array('/\s/',"/[\\²\"<>#\|\{\}\^\[\]`;\?:@=\+\$\,\.\!\(\)№\*_\«\»]/ui","/\//");
		$to = array('-','','-');
		$str = preg_replace($from,$to, trim($str));

		// почему тоне заменял \ пришлось
		$str = str_replace("\\", '-', $str);

		// заменяем двойные -- на одиночные
		$from = array('/-+/');
		$to = array('-');
		for ($i=0; $i<2; $i++) {
			$str = preg_replace($from,$to, $str);
		}

		// обрезаем края с тире
		$str = trim($str,'-');

		return mb_strtolower($str);
	}


}