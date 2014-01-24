<?

require_once dirname(__FILE__)."/pCurrencyCbr.class.php";

class pCurrencyNC extends pCurrencyCbr {


	private $_classId;
	private $_sub;
	private $_cc;
	private $_core;

	public function __construct($classId,$sub,$cc) {
		global $nc_core;

		$this->_classId = $classId;
		$this->_sub = $sub;
		$this->_cc = $cc;

		$this->_core = $nc_core;

	}
	/**
	* $date - Дата в формате SQK
	**/
	public function import($date = null) {
		if (!$date) {$date = strftime('%Y-%m-%d');}

		$data = $this->parse($date);

		if ($data) {
			foreach ($data as $item) {
				$sqls[] = sprintf("INSERT INTO Message%d (Subdivision_ID,Sub_Class_ID,NumCode,CharCode,Nominal,Name,Value,cdate) 
					VALUES (%d,%d,'%s','%s','%s','%s','%s','%s')",$this->_classId,$this->_sub,$this->_cc,$item['NumCode'],$item['CharCode'],$item['Nominal'],$item['Name'],$item['Value'],$date);
			}

			// truncate all data
			$this->_core->db->query(sprintf("DELETE FROM Message%d WHERE  Subdivision_ID = %d AND Sub_Class_ID = %d AND DATE(cdate) = DATE('%s')",$this->_classId,$this->_sub,$this->_cc,$date));
			
			// insert new data
			foreach ($sqls as $s) {
				$this->_core->db->query($s);
			}


			// вставляем курс евро к доллару
			$query = sprintf("INSERT INTO Message%d (Subdivision_ID,Sub_Class_ID,NumCode,CharCode,Nominal,Name,Value,cdate)
(
SELECT 
 %d as `Subdivision_ID`
 ,%d as `Sub_Class_ID`
 ,'000' as `NumCode`
 ,'EUR/USD' as `CharCode`
 , 1 as Nominal
 , 'Отношение евро к доллару' as `Name`
 ,ROUND( (s1.value/s2.value),4) as `Value`
 , DATE(s1.cdate)  as cdate
FROM 
 `Message226` s1 
JOIN Message226 s2 ON (DATE(s1.cdate) = DATE(s2.cdate) AND s2.CharCode = 'USD')

WHERE s1.CharCode = 'EUR' AND DATE(s1.cdate) = DATE('%s'))"
			,$this->_classId,$this->_sub,$this->_cc,$date);

			//echo $query;
			$this->_core->db->query( $query );
		}

	}


}