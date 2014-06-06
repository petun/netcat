<?

/**
 * Class pCommentsCounter
 * Использование:
 * 1. Нужно что бы в нужном Message было поле - commentsCount - если оно есть, то кол-во считается
 * 2. Подключить модуль require_once($_SERVER['DOCUMENT_ROOT'].'/pmod/comments/pCommentsCounter.class.php');
 * 3. Нужно тупо создать экземпляр класса для работы $cc = new pCommentsCounter();
 */
class pCommentsCounter
{

	private $_core;

	private $_field;

	public function __construct($field = 'commentsCount') {
		$this->_core = nc_Core::get_object();
		$this->_field = $field;

		$this->_core->event->bind($this, array('addComment' => 'processComment'));
		$this->_core->event->bind($this, array('dropComment' => 'processComment'));
		$this->_core->event->bind($this, array('checkComment' => 'processComment'));
		$this->_core->event->bind($this, array('uncheckComment' => 'processComment'));
	}

	/**
	 * @param $Catalogue_ID
	 * @param $Subdivision_ID
	 * @param $Sub_Class_ID
	 * @param $Class_ID
	 * @param $Message_ID
	 * @param $Comment_ID
	 *
	 */
	public function processComment($Catalogue_ID, $Subdivision_ID, $Sub_Class_ID, $Class_ID, $Message_ID, $Comment_ID) {
		//p_log('processComment');
		$cnt = $this->_core->db->query(sprintf("SELECT * FROM `Field` WHERE Class_ID = %d AND Field_Name = '%s'", $Class_ID, $this->_field));
		if (!empty($cnt)) {
			$commentsCount = $this->_core->db->get_var("SELECT COUNT(*) FROM `Comments_Text` WHERE Message_ID = ".$Message_ID." AND Checked = 1 AND Sub_Class_ID = ".$Sub_Class_ID);
			//p_log('Total comments for message '.$Message_ID . ' is '.$commentsCount);
			$this->_core->db->query("UPDATE Message".$Class_ID." SET ".$this->_field." = ".$commentsCount. " WHERE Message_ID = ".$Message_ID);
		} else {
			//p_log("Skip class ".$Class_ID.". Field ".$this->_field." does not exists");
		}

	}


}