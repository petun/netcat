<?

class pStats {

	private $_db;

	public function __construct() {
		global $nc_core;
		$this->_db = $nc_core->db;
	}

	public function count($where = "DATE = DATE( NOW( ) )") {
		$data = $this->_db->get_results("SELECT SUM(  `Hits` ) Hits, SUM(  `NewVisitors` ) AS Visitors,Catalogue_ID
FROM  `Stats_Attendance` 
WHERE $where
AND  `Catalogue_ID` =1
GROUP BY Catalogue_ID
LIMIT 0 , 30",ARRAY_A);

		if ($data[0]) {
			return $data[0];
		}
		return false;
	}


}