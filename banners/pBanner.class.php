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
		global $sub;

		$query = "SELECT * FROM ".$this->table." WHERE  Area = ".$area_id." ORDER By RAND()";
		
		$all_banners = $nc_core->db->get_results($query,ARRAY_A);


		$banner = false;
		if ($all_banners) {
			foreach ($all_banners as $b) {
//print_r($b);
				if (empty($b['inSub'])) {
					$banner = $b;
				} else {
					$ids = explode(',',$b['inSub']);
					$ids = array_map('trim',$ids);
//print_r($ids);
					if (in_array($sub,$ids)) {
						$banner = $b;
					}

				}
				if ($banner) {break;}
			}
		}



//		$id = $nc_core->db->get_var($query);


		if ($banner) {
			$file = nc_file_path($this->class_id,$banner['Message_ID'],'File');
			$blank = $banner['inNewPage'] ? "target='_blank'" : NULL;

			return "<a href='/netcat/modules/default/petun/banners/go.php?id=".$banner['Message_ID']."' $blank><img src='".$file."' alt='' /></a>";

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