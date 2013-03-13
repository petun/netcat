<?

class pBanner {

	private $class_id;
	private $table;

	public function __construct($class_id) {
		$this->class_id = $class_id;
		$this->table = 'Message'.$this->class_id;
	}

	public function rotate($area_id) {
		global $nc_core;

		$query = "SELECT Message_ID FROM ".$this->table." WHERE  Area = ".$area_id." ORDER By RAND() LIMIT 1";
		p_log("Rotate ".$area_id. " ".$query);

		$id = $nc_core->db->get_var($query);
		if ($id) {

			$banner = $this->get_banner($id);
			$file = nc_file_path($this->class_id,$id,'File');
			p_log( "<a href='/netcat/modules/default/petun/banners/go.php?id=".$id."' target='_blank'><img src='".$file."' alt='' /></a>");


			return "<a href='/netcat/modules/default/petun/banners/go.php?id=".$id."' target='_blank'><img src='".$file."' alt='' /></a>";

		}

		return false;
	}
	
	public function redirect($id) {
		global $nc_core;
		$b = $this->get_banner($id);
		if ($b) {
			// udpate view count
			$query = "UPDATE ".$this->table." SET ViewCount = ViewCount+1 WHERE Message_ID = ".$id;
			$nc_core->db->query($query);
			p_log("Update view cound for $id " . $query);


			p_log("redirect to ".$b['Link']);
			ob_end_clean();
			header("Location: ".$b['Link']);
			exit;
		}
	}

	private function get_banner($id) {
		global $nc_core;
		return $nc_core->message->get_by_id ($this->class_id,$id);
	}


}