<?php

class pBucket {

 private $storage = array();
 private $db;
 private $pbucketid;

 public function __construct(){
   global $nc_core;
   $this->db =$nc_core->db;
   
   // set cookie id to pbucketid
   $this->getid();
   
   // get data or create one in pbucket table
   $st =  $this->db->get_var("SELECT data FROM pbucket WHERE pbucketid ='". $this->pbucketid."'");
   if ($st) {
     //p_log("id exists. serialize data");
    
     $this->storage = unserialize($st);
   } else { 
      //p_log("create new record");
      $this->db->query("INSERT INTO pbucket( pbucketid,data )  VALUES ('".$this->pbucketid."', '".serialize($this->storage)."' )");
   }
   
      
   // process request GET and POST for add or delete items
   $this->process_request();
}

public function __destruct() {
  // flush to db
  $this->db->query("UPDATE pbucket SET data = '".serialize($this->storage)."' WHERE pbucketid = '".$this->pbucketid."'");
  
  //p_log("flush bucket to db");
}


private function process_request() {
  
  if ($_REQUEST['pb_action'] == 'add') {
    if (intval($_REQUEST['pb_id'])) {
      $cnt = intval($_REQUEST['pb_count']) ?  intval($_REQUEST['pb_count']) : 1;
      
      //p_log("add item to bucket - ".$_REQUEST['pb_id']) . 'count: '. $cnt;
      
      $this->add(intval($_REQUEST['pb_id']),$cnt);
    }  
  } else if ($_REQUEST['pb_action'] == 'remove') {
    if (!empty($_REQUEST['pb_id'])) {
        //p_log('delete item form bucket');
        $this->remove($_REQUEST['pb_id']);
    }
  } else if ($_REQUEST['pb_action'] == 'recount') {
    if (!empty($_REQUEST['pb_id']) && is_array($_REQUEST['pb_id'])) {
      //p_log('recount');
      
      // clear all bucket
      $this->clear();
      
      foreach ($_REQUEST['pb_id'] as $i => $id) {
        $this->add($id, intval($_REQUEST['pb_count'][$i]) ? intval($_REQUEST['pb_count'][$i]) : 1);
      }
      
    }
  }
  
  
  
  
  
  //p_log('bucket is '.print_r($this->storage,true));
  
}

public function add($id,$count=1) {
 if (array_key_exists($id, $this->storage)) { $this->storage[$id] += $count;}
 else { $this->storage[$id] = $count;}
}
public function remove($id) {
 if (array_key_exists($id, $this->storage)) { unset($this->storage[$id]); }
}

public function clear() {
  $this->storage = array();
}

public function get(){
  return $this->storage;
}

public function get_count($id) {
  return array_key_exists($id, $this->storage) ?  $this->storage[$id] : 0;
}

/**
* $include_all - если включена, считаются все товары, которые по несколько штук..
* а так считаются только уникальные
**/
public function count($include_all = false) {
  $total = 0;
  if ($include_all) {
    $total = count($this->storage);  
  } else {    
    if (!empty($this->storage)) {      
      foreach ($this->storage as $id => $cnt) {
        $total += $cnt;
      }
    }
  }
  
  return $total;  
}



private function getid() {
 if (empty($_COOKIE['pbucketid'])){
   $id = md5(uniqid());
   setcookie('pbucketid',$id, time()+60*60*24*30);   
 }
 
 $this->pbucketid= $_COOKIE['pbucketid'];
}

}