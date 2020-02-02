
<?php

if (ob_get_level() == 0) ob_start();
require_once("include/bittorrent.inc.php");
require_once("include/benc.php");
dbconn();

$r = "d" . benc_str("files") . "d";// $r волшебная константа для files в benc_str массив типа бля ee в конце

$fields = "info_hash, times_completed, seeders, leechers"; // так поля

class Announcer {
  protected static $sDB=null;
   
   public function __construct( $db=false ){
	   if ($db !== false) $this->sDB=$db;
	   else $this->sDB=$GLOBALS["___mysqli_ston"];
   }

   function err($msg) {
        	benc_resp(array("failure reason" => array("type" => "string", "value" => $msg)));
        	//exit();
   }


   public function getRSize($ask, $default=50){
     foreach(array("num want", "numwant", "num_want") as $k) {
        if (isset($ask[$k])) {
                return intval($ask[$k]);
                break;
	}
	return $default;

   }
   protected function checkPort($port){
	if (!$port || $port > 0xffff) return false;
	return true;
   }
   protected function getTorrentByID($ihash){
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id, banned, seeders + leechers AS numpeers FROM torrents WHERE " . hash_where("info_hash", $ihash));

	$torrent = mysqli_fetch_assoc($res);
	if (!$torrent){
		err("torrent not registered with this tracker");
		return false;
	}

	return $torrent;
   }
   protected function getPeersByTorrentID($id){
	$res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT $fields FROM peers WHERE torrent = $torrentid AND (1 OR connectable = 'yes') $limit");

	$resp = "d" . benc_str("interval") . "i" . $announce_interval . "e" . benc_str("peers") . "l";
	//unset($self);
	while ($row = mysqli_fetch_assoc($res)) {
        $row["peer_id"] = hash_pad($row["peer_id"]);

        //if ($row["peer_id"] === $peer_id) {
                //$self = $row;//???!
          //      continue;
        //}

        $resp .= "d" .
                benc_str("ip") . benc_str($row["ip"]) .
                benc_str("peer id") . benc_str($row["peer_id"]) .
                benc_str("port") . "i" . $row["port"] . "e" .
                "e";
	}
	$resp .= "ee";
	return $resp;
   }

   const defreq = "info_hash:peer_id:ip:port:uploaded:downloaded:left:!event";      
   public function announce($ask){
	$opt=0;
	$rsize=50;
	foreach (explode(":", self::defreq) as $element) {
        	if ($element[0] == "!") {
                	$element = substr($element, 1);
                	$opt = 1; // 
        	}
        	else
                	$opt = 0;
        	if (!isset($ask[$element])) {
                	if (!$opt)
                        	$this->err("missing key");
                	continue;
        	}
        	$GLOBALS[$element] = unesc($ask[$element]);
	}
	foreach (array("info_hash","peer_id") as $x) 
         if (strlen($GLOBALS[$x]) != 20)
		 err("invalid $x (" . strlen($GLOBALS[$x]) . " - " . urlencode($GLOBALS[$x]) . ")");
	$this->constructAnswer();
	$rsize = $this->getRSize();//rsize какой то хуй знает чо какие то цифры там блять хотят
	if (!$this->checkPort){
		err("invalid port");
		exit();
	}
	if (!isset($event)) $this->event="";
	else $this->event=$event;
	$this->seeder = ($this->left == 0) ? "yes" : "no";
	dbconn(0);
	//$this->info_hash=$info_hash;
	$torrent = $this->getTorrentByID($info_hash);// блять потом поправить сука
	$torrentid = $torrent["id"];
	$limit = "";
	if ($torrent["numpeers"] > $rsize)
		$limit = "ORDER BY RAND() LIMIT $rsize";



	
	


}

		


   } 
 public function bigintval($value) {
  	$value = trim($value);
  	if (ctype_digit($value)) {
    		return $value;
  	}
  	$value = preg_replace("/[^0-9](.*)$/", '', $value);
  	if (ctype_digit($value)) {
    		return $value;
  	}
  	return 0;
 }
 protected function constructAnswer(){

   $this->port = intval($port);
   $this->downloaded = bigintval($downloaded);
   $this->uploaded = bigintval($uploaded);
   $this->left = bigintval($left);
 }



	
};

//if (!isset($_GET["info_hash"]))
//        $query = "SELECT $fields FROM torrents ORDER BY info_hash";
//else
//        $query = "SELECT $fields FROM torrents WHERE " . hash_where("info_hash", $_GET["info_hash"]);
//
//$res = mysqli_query($GLOBALS["___mysqli_ston"], $query);
//
//while ($row = mysqli_fetch_assoc($res)) {
//        $r .= "20:" . hash_pad($row["info_hash"]) . "d" .
//                benc_str("complete") . "i" . $row["seeders"] . "e" .
//                benc_str("downloaded") . "i" . $row["times_completed"] . "e" .
//                benc_str("incomplete") . "i" . $row["leechers"] . "e" .
//                "e";
//}
//
//$r .= "ee";
//
//print($r);
//
