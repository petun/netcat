<?php


class pTranslate {

	private $_catalogueId;

	private $_storage;

	public function __construct($catalogueId) {
		$this->_catalogueId =  $catalogueId;

		$file = dirname(__FILE__).'/lang/' . $this->_catalogueId . '.php';

		if (file_exists($file)) {			
			include($file);
			/* @var $lang array */
			$this->_storage = $lang;
		}
	}


	public function get($key) {
		if (array_key_exists($key, $this->_storage)) {
			return $this->_storage[$key];
		}
		return null;
	}

	public function getAll() {
		return $this->_storage;
	}

	public function getCatalogueId() {
		return $this->_catalogueId;	
	}


}