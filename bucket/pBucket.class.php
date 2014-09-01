<?php

class pBucket
{

	private $__storage = array();

	private $__db;

	private $__pBucketId;

	public function __construct() {
		global $nc_core;
		$this->__db = $nc_core->db;

		// set cookie id to pbucketid
		$this->__getId();

		// get data or create one in pbucket table
		$st = $this->__db->get_var("SELECT data FROM pbucket WHERE pbucketid ='" . $this->__pBucketId . "'");
		if ($st) {
			//p_log("id exists. serialize data");

			$this->__storage = unserialize($st);
		} else {
			//p_log("create new record");
			$this->__db->query(
				"INSERT INTO pbucket( pbucketid,data )  VALUES ('" . $this->__pBucketId . "', '" . serialize(
					$this->__storage
				) . "' )"
			);
		}


		// process request GET and POST for add or delete items
		$this->process_request();
	}

	public function __destruct() {
		// flush to db
		$this->__db->query(
			"UPDATE pbucket SET data = '" . serialize($this->__storage) . "' WHERE pbucketid = '" . $this->__pBucketId . "'"
		);

		//p_log("flush bucket to db");
	}


	private function process_request() {

		if ($_REQUEST['pb_action'] == 'add') {
			if (intval($_REQUEST['pb_id'])) {
				$cnt = intval($_REQUEST['pb_count']) ? intval($_REQUEST['pb_count']) : 1;
				$price = intval($_REQUEST['pb_price']) ? intval($_REQUEST['pb_price']) : 1;

				//p_log("add item to bucket - ".$_REQUEST['pb_id']) . 'count: '. $cnt;

				$this->add(intval($_REQUEST['pb_id']), $cnt, $price);
			}
		} else {
			if ($_REQUEST['pb_action'] == 'remove') {
				if (!empty($_REQUEST['pb_id'])) {
					//p_log('delete item form bucket');
					$this->remove($_REQUEST['pb_id']);
				}
			} else {
				if ($_REQUEST['pb_action'] == 'recount') {
					if (!empty($_REQUEST['pb_id']) && is_array($_REQUEST['pb_id'])) {
						//p_log('recount');

						// clear all bucket
						$this->clear();

						foreach ($_REQUEST['pb_id'] as $i => $id) {
							$this->add($id,
								intval($_REQUEST['pb_count'][$i]) ? intval($_REQUEST['pb_count'][$i]) : 1,
								intval($_REQUEST['pb_price'][$i]) ? intval($_REQUEST['pb_price'][$i]) : 0
							);
						}

					}
				}
			}
		}


		p_log('bucket is '.print_r($this->__storage,true));

	}

	/**
	 * Добавляет товарв в корзину
	 * @param     $id
	 * @param int $count
	 * @param int $price
	 */
	public function add($id, $count = 1, $price = 0) {
		if (array_key_exists($id, $this->__storage)) {
			$this->__storage[$id]['count'] += $count;
		} else {
			$this->__storage[$id] = array(
				'count'=>$count,
				'price'=>$price,
			);
		}
	}

	public function remove($id) {
		if (array_key_exists($id, $this->__storage)) {
			unset($this->__storage[$id]);
		}
	}

	public function clear() {
		$this->__storage = array();
	}

	/**
	 * @param null $id
	 * Возвращает хранилище в виде массива array(<id>=>array('count'=>10,'price'=>120.....)
	 * @return array|mixed
	 */
	public function get($id = null) {
		if (!empty($id)) {
			return $this->__storage[$id];
		} else {
			return $this->__storage;
		}
	}

	public function get_count($id) {
		return array_key_exists($id, $this->__storage) ? $this->__storage[$id] : 0;
	}

	/**
	 * $include_all - если включена, считаются все товары, которые по несколько штук..
	 * а так считаются только уникальные
	 **/
	public function count($include_all = false) {
		$total = 0;
		if ($include_all) {
			$total = count($this->__storage);
		} else {
			if (!empty($this->__storage)) {
				foreach ($this->__storage as $item) {
					$total += $item['count'];
				}
			}
		}

		return $total;
	}

	/**
	 * Возвращает общую сумму товаров в корзине
	 */
	public function sum() {
		$sum = 0;
		if ($this->__storage) {
			foreach ($this->__storage as $info) {
				$sum += $info['price'] * $info['count'];
			}
		}
		return $sum;
	}



	private function __getId() {
		if (empty($_COOKIE['pbucketid'])) {
			$id = md5(uniqid());
			setcookie('pbucketid', $id, time() + 60 * 60 * 24 * 30);
		}

		$this->__pBucketId = $_COOKIE['pbucketid'];
	}

}