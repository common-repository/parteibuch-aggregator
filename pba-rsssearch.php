<?php

//initialization of config variables, 
//these allow 
//1. optional cronjobs to call the pba-rsssearch.php directly
//2. have the search index stored in a seperate database
//no good idea to place config here, better define them in your wp-config.php
$pba_search_directcall_password="putinsomethingfullinyourwpconfig"; //to override, define PBA_SEARCH_DIRECTCALL_PASSWORD in your wp-config
$pba_search_enable_directcall=false; //disabled by default with false, to override, define PBA_SEARCH_ENABLE_DIRECTCALL as true in your wp-config
$pba_search_database=""; //empty string "" for default database name, to override, define PBA_SEARCH_CONFIG in wp-config

//internal variable initialisation
$bdprsssearchdebug=false; //bdprsssearchdebug tells this script and the aggregator also that it is called in direct mode
$bdprsssearchdebug_with_searchindex_info=true;
$pba_search_directcall_comparepassword="";

$pba_search_direct_call_attempt=false;
if( !class_exists('BDPRSS2') && !isset($wpdb)){
	$pba_search_direct_call_attempt=true;
	require_once('../../../wp-config.php');
}

if(defined('PBA_SEARCH_DATABASE')) $pba_search_database = PBA_SEARCH_DATABASE;
if(defined('PBA_SEARCH_ENABLE_DIRECTCALL')) $pba_search_enable_directcall = PBA_SEARCH_ENABLE_DIRECTCALL;
if(defined('PBA_SEARCH_DIRECTCALL_PASSWORD')) $pba_search_directcall_password = PBA_SEARCH_DIRECTCALL_PASSWORD;

if(isset($_GET['pbasearchpassword']))$pba_search_directcall_comparepassword=stripslashes($_GET['pbasearchpassword']);

if($pba_search_direct_call_attempt){
	if(!$pba_search_enable_directcall || $pba_search_directcall_comparepassword != $pba_search_directcall_password){
		die ( __('Exiting pba_rsssearch before execution: either pba_search_enable_directcall not enabled or pba_search_directcall_password wrong.') );
	}else{
		$bdprsssearchdebug=true;
		echo "Debug ... loading wp-config.php ... ";
	}
}


if( !class_exists('BDPRSS_SEARCH') ) {

	class BDPRSS_SEARCH
	{	
//config values
var $heap_to_add;
var $default_heapmode;
var $bulklines;

//internal script variables
var $bdprss_searchtable_prefix;
var $bdprss_searchtable_temp;
var $bdprss_searchtable_status;
var $bdprss_searchtable=array();

var $bdprss_globalcounter;
var $bdprss_bulksql;
var $bdprss_item_status_sql;
var $bdprss_item_delete_sql;
var $get_ids4heap2add_mode_default;
var $get_ids4heap2add_min_updatetimeage_default;
var $get_ids4heap2add_max_item_updatetimeage_default;
var $process_updates;
var $process_deletes;

		function BDPRSS_SEARCH() 
		{
			/* BDPRSS_SEARCH() - initialisition function that sets constant names for later use */
			
			global $pba_search_database, $table_prefix;
			
			//config values
			$this->heap_to_add=1000; //maximum possible heap, depends on memory and exec time restricts in php
			$this->default_heapmode="bulk";  //possible values: "bulk", "temptable"
			$this->bulklines=20; //never set to zero, may result in division by zero error, max possible value depends on ph memory
			$this->get_ids4heap2add_mode_default="notinstatus"; //possible values: "notinstatus", "maxitem_id"
			$this->get_ids4heap2add_min_updatetimeage_default="30"; 
			$this->get_ids4heap2add_max_item_updatetimeage_default="86400";
			$this->process_updates=false;
			$this->process_deletes=false;

			if($pba_search_database != "") $pba_search_database .= ".";
			$this->bdprss_searchtable_prefix=$pba_search_database . $table_prefix . "pba_index_";
			
			$this->bdprss_searchtable[9]=array($this->bdprss_searchtable_prefix . "9","w","{");
			$this->bdprss_searchtable[8]=array($this->bdprss_searchtable_prefix . "8","u","w");
			$this->bdprss_searchtable[7]=array($this->bdprss_searchtable_prefix . "7","s","u");
			$this->bdprss_searchtable[6]=array($this->bdprss_searchtable_prefix . "6","n","s");
			$this->bdprss_searchtable[5]=array($this->bdprss_searchtable_prefix . "5","j","n");
			$this->bdprss_searchtable[4]=array($this->bdprss_searchtable_prefix . "4","h","j");
			$this->bdprss_searchtable[3]=array($this->bdprss_searchtable_prefix . "3","e","h");
			$this->bdprss_searchtable[2]=array($this->bdprss_searchtable_prefix . "2","d","e");
			$this->bdprss_searchtable[1]=array($this->bdprss_searchtable_prefix . "1","a","d");
			$this->bdprss_searchtable[0]=array($this->bdprss_searchtable_prefix . "0","0",":");

			$this->bdprss_searchtable_temp=$this->bdprss_searchtable_prefix . "temp";
			$this->bdprss_searchtable_status=$this->bdprss_searchtable_prefix . "status"; // 'OK', 'UPDATE', 'INTEMP'

			$this->bdprss_globalcounter=0;
			$this->bdprss_bulksql=array();
			$this->bdprss_item_status_sql="";
			$this->bdprss_item_delete_sql="";
		} //init function BDPRSS_SEARCH

function bdprss_make_entities_from_searchphrase($searchword=""){
  global $bdprsssearchdebug;
  $return_array=array();
  $linepointer=0;
  $is_in_quot_mode=false;
  $continue=false;
  
  //find chunks enclosed by doublequots
  $searchword_array=str_split(" " . $searchword . " ");
    //print_r($searchword_array);

  foreach($searchword_array as $searchword_pos => $searchword_letter){
    if($continue){ $continue = false; continue; }
    $return_array[$linepointer]['type']='Plain';
    if($searchword_letter == " " && ($searchword_array[$searchword_pos + 1] == '"' || $searchword_array[$searchword_pos + 1] == "'") && $is_in_quot_mode==false){
      if(strlen($return_array[$linepointer]['string'])>0) $linepointer++;
      $is_in_quot_mode=true;
      $searchword_letter="";
      $continue=true;
    } elseif(($searchword_letter == '"' || $searchword_letter == "'") && $searchword_array[$searchword_pos + 1] == ' ' && $is_in_quot_mode==true){
      $return_array[$linepointer]['type']='Quoted';
      if(strlen($return_array[$linepointer]['string'])>0) $linepointer++;
      $return_array[$linepointer]['type']='Plain';
      $is_in_quot_mode=false;
      $searchword_letter="";
    }
    $return_array[$linepointer]['string']=$return_array[$linepointer]['string'] . $searchword_letter;
  }
  $array_counter=0;
  $plain_merger="";
  $sorted_array[0]=array();
  foreach($return_array as $linepointer => $line){
    $return_array[$linepointer]['stemmed']= $this->stem_search_text($line['string']);
    if($line['type']=='Quoted'){
      $array_counter++;
      $sorted_array[$array_counter]= explode(' ', $this->stem_search_text($line['string']));
    } else {
      $plain_merger=$plain_merger . " " . $line['string'];
    }
  }
  $plain_merger=$this->stem_search_text($plain_merger);
  if(strlen($plain_merger)>0) $sorted_array[0] = explode(' ', $plain_merger);
  if($bdprsssearchdebug) {echo "<br>Debug: sorted_array: "; print_r($sorted_array);}
  return $sorted_array; //in array[0] is single words, in array [1-n] is search phrases
}

function bdprss_findtableforword($searchword=""){
  $char2compare=ord(substr($searchword,0,1));
  foreach($this->bdprss_searchtable as $table_to_search){
    if($char2compare >= ord($table_to_search[1]) && $char2compare < ord($table_to_search[2])) { 
      return $table_to_search[0]; 
    } 
  }
  return false;
  
}

function pbasearch_list_tables(){
	$tables=array();
	foreach($this->bdprss_searchtable as $table_to_search){
		$tables[$table_to_search[0]]=true;
	}
	$tables[$this->bdprss_searchtable_status]=true;
	$tables[$this->bdprss_searchtable_temp]=true;
	return $tables;
}

function bdprss_create_proc(){
  global $bdprss_db, $wpdb, $bdprsssearchdebug;

  foreach($this->bdprss_searchtable as $table_to_search){
    $threshold=chr(ord($table_to_search[2])-1);
    $sql="CREATE TABLE IF NOT EXISTS $table_to_search[0] (
      item_id int(10) NOT NULL,
      index_word varchar(255) NOT NULL,
      index_position int(10) NOT NULL,
      PRIMARY KEY  (item_id,index_position),
      KEY idx_word_id (index_word,item_id)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='$table_to_search[1]-$threshold'";
    $result = $wpdb->query($sql);
  }

    $sql="CREATE TABLE IF NOT EXISTS $this->bdprss_searchtable_temp (
      item_id int(10) NOT NULL,
      index_word varchar(255) NOT NULL,
      index_position int(10) NOT NULL,
      PRIMARY KEY  (item_id,index_position)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='temp'";
    $result = $wpdb->query($sql);

    $sql="CREATE TABLE IF NOT EXISTS $this->bdprss_searchtable_status (
  item_id int(10) NOT NULL,
  md5 char(32) NOT NULL,
  status char(6) NOT NULL,
  item_time int(15) NOT NULL,
  item_update_time int(15) NOT NULL,
  PRIMARY KEY  (item_id),
  KEY idx_status_item_id (status,item_id)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='index status per item id'";
    $result = $wpdb->query($sql);

  return $result;
}

function stem_search_text($bdprss_itemtext2search){
//strip text of html and script tags
  $bdprss_itemtext2search=BDPRSS2::remove_link_and_cache_links_from_item($bdprss_itemtext2search);
  $bdprss_itemtext2search=BDPRSS2::packageItemText($bdprss_itemtext2search, 0, 1000000, FALSE, '');

//replace non text with blanks and decapitalize
  $strg=array("ä","ö","ü","ß");
  $rpl=array("ae","oe","ue","ss");
  $bdprss_itemtext2search=utf8_encode(str_replace($strg,$rpl,html_entity_decode(utf8_decode(strtolower(trim($bdprss_itemtext2search))))));
  $bdprss_itemtext2search=trim(preg_replace('/[^a-z0-9]+/si',' ',$bdprss_itemtext2search));

  return $bdprss_itemtext2search;
}

function delete_item_from_search($bdprss_delete_searchitem_id){
  global $bdprss_db, $wpdb, $bdprsssearchdebug;
  $this->bdprss_item_delete_sql .= ", '$bdprss_delete_searchitem_id'";
}

function add_item_to_search($bdprss_add_searchitem_id, $addheapmode="", $dodelete=false){

  global $bdprss_db, $wpdb, $bdprsssearchdebug;
  
  if($addheapmode == "") $addheapmode = $this->default_heapmode;
  if( $this->process_updates ) $dodelete=true;
  
  $search_item=$bdprss_db->getItemByID($bdprss_add_searchitem_id);
  if(!$search_item->item_site_name && !$search_item->item_name && !$search_item->text_body) return;
  $bdprss_itemtime2search=$search_item->item_time;

//Here we got all the info needed for an entry in search index
  $bdprss_itemtext2search=$search_item->item_site_name . " " . $search_item->item_name . " " . $search_item->text_body;

  $bdprss_itemtext2search=$this->stem_search_text($bdprss_itemtext2search);

//delete previous items to search index

//add item to search index
  if($addheapmode == "temptable"){
    //echo "Just testing, no real insert done";
    $bdprss_itemtext2searcharray=explode(' ',$bdprss_itemtext2search);
    foreach($bdprss_itemtext2searcharray as $bdprss_itemtext2searcharraykey => $bdprss_itemtext2searcharrayvalue){
      $bdprss_tmptable_sql .= ", ($bdprss_add_searchitem_id, '$bdprss_itemtext2searcharrayvalue', $bdprss_itemtext2searcharraykey)";
    }
    $bdprss_tmptable_sql= "INSERT INTO " . $this->bdprss_searchtable_temp . " (item_id, index_word , index_position) VALUES " . substr($bdprss_tmptable_sql,2);
    //echo $bdprss_tmptable_sql;
    $result = $wpdb->query($bdprss_tmptable_sql);
    $this->bdprss_item_status_sql .= ", '$bdprss_add_searchitem_id'";
  } elseif($addheapmode == "bulk"){
    //echo "Just testing, no real insert done";
    $bdprss_itemtext2searcharray=explode(' ',$bdprss_itemtext2search);
    foreach($bdprss_itemtext2searcharray as $bdprss_itemtext2searcharraykey => $bdprss_itemtext2searcharrayvalue){
      $table4word=$this->bdprss_findtableforword($bdprss_itemtext2searcharrayvalue);
      //echo " $bdprss_itemtext2searcharrayvalue -> $table4word";
      if($table4word) {
      	if(!isset($this->bdprss_bulksql[$table4word])) $this->bdprss_bulksql[$table4word] ="";
      	$this->bdprss_bulksql[$table4word] .= ", ($bdprss_add_searchitem_id, '$bdprss_itemtext2searcharrayvalue', $bdprss_itemtext2searcharraykey)";
      }
    }
    $this->bdprss_item_status_sql .= ", '$bdprss_add_searchitem_id'";
    if($dodelete) $this->bdprss_item_delete_sql .= ", '$bdprss_add_searchitem_id'";
  } else {
    $sql = "call insme($bdprss_add_searchitem_id, '" . $bdprss_itemtext2search . "', $bdprss_itemtime2search)";
    if($bdprsssearchdebug) echo "Debug: Error: no valid addheapmode: $addheapmode ";
    //$result = $wpdb->query($sql); //broken procedure may be for new addheapmode
    return false;
  }
  $insertcounter=substr_count(utf8_decode($bdprss_itemtext2search), ' ')+1;
  $this->bdprss_globalcounter+=$insertcounter;
  if($bdprsssearchdebug) echo " " . $insertcounter . "w. ";
  flush();
  return true;
}

function bdprss_search4items($search_phrase, $start=0, $max=10, $ids=false, $list_id=0, $itemdate="", $feed="", $fromtimestamp=0, $totimestamp=0, $opsfilter=false, $orderbysitename=false, $itemid=false){
  global $wpdb, $bdprss_db, $found_tickeritems, $bdprsssearchdebug;

  //$itemdate="2009-02-28"; //leave for testing purposes
  //$feed="http://evilboy.ej.am/blog/?feed=rss2";  //leave for testing purposes

  //make variables secure
  $list_id=abs(intval($list_id));
  $fromtimestamp=abs(intval($fromtimestamp));
  $totimestamp=abs(intval($totimestamp));
  $start=abs(intval($start));
  $max=abs(intval($max));
  if($itemid) $itemid = abs(intval($itemid));
  if($itemdate && $itemdate!=""){
    if (!ereg("[0-9][0-9][0-9][0-9]-[0-1][0-9]-[0-3][0-9]", $itemdate)){
			$itemdate = "";
		}
  } else {
		$itemdate = "";
  }

//just a test, switch model to be programmed later in a more fitting place
  if($infeed==""){
    $infeed=preg_replace("/(feed:[^ ]+).*/",'${1}',$search_phrase);
    if(strstr(substr($infeed, 0, 5), 'feed:')) {
      $search_phrase=preg_replace("/(feed:[^ ]+)/",'',$search_phrase);
      $infeed = mysql_real_escape_string(str_replace("feed:", "", $infeed));
      if($bdprsssearchdebug) echo "<br>Debug: feed from switch is: " . $infeed . " Remaining searchphrase is: " . $search_phrase;
    } else {
      $infeed="";
    }
  }
  if($feed && $feed != ""){
		$feed = mysql_real_escape_string($feed);
  } else {
		$feed = "";
  }


//following is just for testing purposes, get site ids from list
	if(false && $bdprsssearchdebug && $list_id > 0){
		$listInfo = $bdprss_db->get_list($list_id);
		$lurls = $listInfo->{$bdprss_db->lurls};
		$ids = preg_split("','", $lurls, -1, PREG_SPLIT_NO_EMPTY);
		$list_id=0;
	}

//stem search phrase
  $sorted_array=$this->bdprss_make_entities_from_searchphrase($search_phrase);
  
  $argument_counter=1;
  if(false) $straight=" STRAIGHT_JOIN ";
  if($bdprsssearchdebug) $no_sqlcache=" SQL_NO_CACHE ";
  if(count($sorted_array[0]) + count($sorted_array[1]) > 0) $checkstatus=true;
  
  $found_rows = 'SQL_CALC_FOUND_ROWS ';
  $search_query="SELECT $straight $no_sqlcache $found_rows distinct r1.identifier as $bdprss_db->miid FROM ";

  $search_query_tables="";
  $search_query_conditions_items="";
  $search_query_conditions_words="";
  $search_query_conditions_position="";
  $search_query_conditions_status="";
  $search_query_conditions_list="";
	$search_query_conditions_ids="";
	$search_query_conditions_itemdate="";
	$search_query_conditions_feed="";
	$search_query_conditions_infeed="";
	$search_query_conditions_ops="";
	$search_query_conditions_obsn="";
	$search_query_conditions_itemid="";

  foreach($sorted_array as $sorted_array_key => $sorted_array_value){
    foreach($sorted_array_value as $sorted_array_value_key => $sorted_array_value_value){
      $search_query_tables.=", " . $this->bdprss_findtableforword($sorted_array_value_value) . " i" . $argument_counter . "\n";
      $search_query_conditions_items .= "AND r1.identifier = i" . $argument_counter . ".item_id \n";
      $search_query_conditions_words .= "AND i" . $argument_counter . ".index_word = '" . $sorted_array_value_value . "' \n";
      if($sorted_array_key > 0 && $sorted_array_value_key > 0) {
        $argument_helper = $argument_counter -1;
        $search_query_conditions_position .= "AND i" . $argument_counter . ".index_position = i" . $argument_helper . ".index_position + 1 \n";
      }
      $argument_counter++;
    }
  }
  if($argument_counter > 1) $search_query=str_replace('r1.identifier','i1.item_id', $search_query);
  $search_query_tables=" " . $bdprss_db->mitemtable . " r1 " . $search_query_tables ;
  if($checkstatus) {
    $search_query_tables = " " . $this->bdprss_searchtable_status . " sts, " . $search_query_tables;
    $search_query_conditions_status="AND r1.identifier = sts.item_id AND ( sts.status = 'OK' OR sts.status = 'UPDATE' ) \n";
  }
  
  //one page per site filter
  if($opsfilter){
    //give only out one article per site
    //tough query, but still far from perfect
    //1st problem - cannot specify, give out two, three or n articles from each site
    //2nd problem - search filter will search only in latest article per site, 
    //but better would be to search first and then filter doublettes
    $search_query_tables = " ( SELECT max( ops4.identifier ) as identifier
      FROM " . $bdprss_db->mitemtable . " ops4, (
        SELECT ops3.site_id , max( ops3.item_time ) maxtime FROM " . $bdprss_db->mitemtable . " ops3 GROUP BY ops3.site_id
      )ops2 WHERE ops4.site_id = ops2.site_id AND ops4.item_time = ops2.maxtime
      GROUP BY ops4.site_id ) ops, " . $search_query_tables;
    $search_query_conditions_ops="AND r1.identifier = ops.identifier \n";
  }
  
  if($feed != ""){
    if($argument_counter > 1) {
      $search_query_conditions_feed="AND r1.site_id = (select mst.identifier AS site_id from " . $bdprss_db->sitetable . " mst WHERE mst.feed_url = '" . $feed . "') \n";
    } else {
      $search_query_tables = " (select mst.identifier AS site_id from " . $bdprss_db->sitetable . " mst WHERE mst.feed_url = '" . $feed . "') s2, " . $search_query_tables;
      $search_query_conditions_feed="AND r1.site_id = s2.site_id \n";
    }
  } elseif($infeed != ""){
    if($argument_counter > 1) {
      $search_query_conditions_feed="AND r1.site_id IN (select mst.identifier AS site_id from " . $bdprss_db->sitetable . " mst WHERE mst.feed_url LIKE '%" . $infeed . "%') \n";
    } else {
      $search_query_tables = " (select mst.identifier AS site_id from " . $bdprss_db->sitetable . " mst WHERE mst.feed_url LIKE '%" . $infeed . "%') s2, " . $search_query_tables;
      $search_query_conditions_feed="AND r1.site_id = s2.site_id \n";
    }
  } 
  
  if($list_id > 0) {
    if($argument_counter > 1) {
      //both list options are logically synonym, but mysql optimizer treats both query styles very diffrent
      $search_query_conditions_list="AND r1.site_id in (select sites.identifier AS site_id from (" . $bdprss_db->listtable . " lists join " . $bdprss_db->sitetable . " sites) where ((concat(_latin1',',lists.url_list,_latin1',') like concat(_utf8'%,',sites.identifier,_utf8',%')) or (lists.list_all = _latin1'Y')) and lists.identifier = '" . $list_id . "')  \n";
    } else {
      $search_query_tables = " (select sites.identifier AS site_id from (" . $bdprss_db->listtable . " lists join " . $bdprss_db->sitetable . " sites) where ((concat(_latin1',',lists.url_list,_latin1',') like concat(_utf8'%,',sites.identifier,_utf8',%')) or (lists.list_all = _latin1'Y')) and lists.identifier = '" . $list_id . "') s1, " . $search_query_tables;
      $search_query_conditions_list="AND r1.site_id = s1.site_id \n";
    }
  } elseif($ids) {
    if($argument_counter > 1) {
			$virgin = true;
			foreach($ids as $id) {
				if(!$id) continue;
				if($virgin) 
					$search_query_conditions_ids .= "AND ( ";
				else
					$search_query_conditions_ids .= "OR";
				$search_query_conditions_ids .= " $bdprss_db->misiteid='" . abs(intval($id)) . "' ";
				$virgin = false;
			}
			if(!$virgin) $search_query_conditions_ids .= ") ";
		}else{
			$replace_ids="'-1'";
			foreach($ids as $id) {
				$replace_ids .= ", '".abs(intval($id))."'";
			}
			$replace_ids=str_replace("'-1',",'', $replace_ids);
      $search_query_tables = " (select sites.identifier AS site_id from " . $bdprss_db->sitetable . " sites WHERE identifier IN (".$replace_ids.")) s1, " . $search_query_tables;
      $search_query_conditions_ids="AND r1.site_id = s1.site_id \n";
		}
	}
  if($itemdate!="") $search_query_conditions_itemdate .="AND r1.item_time >= UNIX_TIMESTAMP( '" . $itemdate . "' ) AND r1.item_time < UNIX_TIMESTAMP( '" . $itemdate . "' ) + 24 *60 *60 \n";
  if($fromtimestamp > 0) $search_query_conditions_itemdate .="AND r1.item_time >= '" . $fromtimestamp . "' \n";
  if($totimestamp > 0) $search_query_conditions_itemdate .="AND r1.item_time <= '" . $totimestamp . "' \n";

  if($orderbysitename){
    //this order seems to be only sensible together with opsfilter
    $search_query_tables = " " . $bdprss_db->sitetable . " obsn, " . $search_query_tables;
    $search_query_conditions_obsn="AND r1.site_id = obsn.identifier \n";
    $search_query_order=" ORDER BY obsn.site_name ASC ";
  }else{
    $search_query_order=" ORDER BY r1.item_time DESC ";
  }

  //looks strange, but gives possibility to check, if item_id is hit with all the other filter applied
  if($itemid) $search_query_conditions_itemid="AND r1.identifier = '".$itemid."' \n";

  $search_query_conditions="WHERE 1 " . $search_query_conditions_words . $search_query_conditions_items . $search_query_conditions_position . $search_query_conditions_ids . $search_query_conditions_status . $search_query_conditions_list . $search_query_conditions_itemdate . $search_query_conditions_feed . $search_query_conditions_ops . $search_query_conditions_obsn . $search_query_conditions_itemid;
  $search_query_conditions=str_replace('WHERE 1 AND','WHERE', $search_query_conditions);

  $search_query_limits=" LIMIT $start , $max ";
  
  $search_query= $search_query . $search_query_tables . $search_query_conditions . $search_query_order . $search_query_limits;
  if($bdprsssearchdebug) echo "<br>Debug: search_query: " . $search_query;

	$tmp_result = $wpdb->get_results($search_query);
	if ( $search_query_limits ) {
		$found_tickeritems_query = apply_filters( 'found_tickeritems_query', 'SELECT FOUND_ROWS()' );
		$found_tickeritems = $wpdb->get_var( $found_tickeritems_query );
	}

  if($bdprsssearchdebug) echo "<br>Debug: Rows found: " . $found_tickeritems ;

//give out item_ids

  return $tmp_result;
}

function process_delete_sql(){
  global $wpdb, $bdprss_db, $bdprsssearchdebug;
  if(strlen($this->bdprss_item_delete_sql)>0){
    foreach($this->bdprss_searchtable as $table_to_search){
      $deletesql = "DELETE FROM $table_to_search[0] WHERE item_id IN ( " . substr($this->bdprss_item_delete_sql,2) . " ) \n";
      if($bdprsssearchdebug) echo "<br>Debug " . date("H:i:s") . ": Deleting from " . $table_to_search[0];
      flush();
      $result = $wpdb->query($deletesql);
    }
    if($this->process_deletes){
      $deletesql = "DELETE FROM " . $this->bdprss_searchtable_status . " WHERE item_id IN ( " . substr($this->bdprss_item_delete_sql,2) . " ) \n";
      if($bdprsssearchdebug) echo "<br>Debug " . date("H:i:s") . ": Deleting from statustable ... " . $deletesql;
      flush();
      $result = $wpdb->query($deletesql);
    }
    $this->bdprss_item_delete_sql="";
  }
}

function process_bulk_sql(){
  global $wpdb, $bdprss_db, $bdprsssearchdebug;
  if(strlen($this->bdprss_item_delete_sql)>0){
    $this->process_delete_sql();
  }
  if($bdprsssearchdebug) echo "<br>Debug " . date("H:i:s") . ": Inserting ... ";
  flush();
  foreach($this->bdprss_bulksql as $bdprss_bulksql_table => $bdprss_bulksql_value){
    $bdprss_bulksql_value = "INSERT INTO " . $bdprss_bulksql_table . " (item_id, index_word , index_position) VALUES " . substr($bdprss_bulksql_value,2) . "\n";
    $result = $wpdb->query($bdprss_bulksql_value);
  }
  if($bdprsssearchdebug) echo " Debug " . date("H:i:s") . ": inserts done.";
  flush();
  if($result && strlen($this->bdprss_item_status_sql)>0){
    $this->bdprss_item_status_sql = "REPLACE INTO " . $this->bdprss_searchtable_status . " 
      Select identifier as item_id, md5(concat(item_site_name, ' ', item_name, ' ', text_body)) as md5, 'OK' as status, item_time, item_update_time from " . $bdprss_db->itemtable . " 
        WHERE identifier IN ( " . substr($this->bdprss_item_status_sql,2) . ")";
    $result = $wpdb->query($this->bdprss_item_status_sql);
  }
  $this->bdprss_bulksql=array();
  $this->bdprss_item_status_sql ="";
}

function markitem4update($item_id="", $md5tocompare=""){
  $item_id=abs(intval($item_id));
  global $wpdb, $bdprss_db, $bdprsssearchdebug;
  
  if($item_id > 0 || $item_id == '0'){
    $sql="UPDATE " . $this->bdprss_searchtable_status . " sts, " . $bdprss_db->itemtable . " i
      SET sts.status = 'UPDATE' 
      WHERE sts.item_id = '".$item_id."'
      AND sts.status = 'OK'
      AND sts.md5 != '" . $md5tocompare . "'
      AND sts.item_id = i.identifier
      AND sts.md5 != md5( concat( i.item_site_name, ' ', i.item_name, ' ', i.text_body ) )
      ";
    $result = $wpdb->query($sql);
    return $wpdb->rows_affected;
  }
  
  return false;
}

function markitem4delete($item_id=""){
  $item_id=abs(intval($item_id));
  global $wpdb, $bdprss_db, $bdprsssearchdebug;
  
  if($item_id > 0 || $item_id == '0'){
    $sql="UPDATE " . $this->bdprss_searchtable_status . " 
      SET status = 'DELETE' 
      WHERE item_id = '".$item_id."'";
    $result = $wpdb->query($sql);
    return true;
  }
  
  return false;
}

function markfeed4delete($feed_id="", $oldDefined=false){
  global $wpdb, $bdprss_db, $bdprsssearchdebug;
  $feed_id=abs(intval($feed_id));
  $oldDefined=abs(intval($oldDefined));
  
  if($oldDefined>1){
    if($feed_id > 0 || $feed_id == '0'){
      $sql="UPDATE " . $this->bdprss_searchtable_status . " sts, " . $bdprss_db->mitemtable . " mi
        SET sts.status = 'DELETE' 
        WHERE sts.item_id = mi.identifier
        AND mi.site_id = '" . $feed_id . "'
        AND (mi.item_time < '" . $oldDefined . "' OR mi.item_time = '')";
      $result = $wpdb->query($sql);
      return true;
    }
  }else{
    if($feed_id > 0 || $feed_id == '0'){
      $sql="UPDATE " . $this->bdprss_searchtable_status . " sts, " . $bdprss_db->mitemtable . " mi
        SET sts.status = 'DELETE' 
        WHERE sts.item_id = mi.identifier
        AND mi.site_id = '" . $feed_id . "'";
      $result = $wpdb->query($sql);
      return true;
    }
  }
  return false;
}

function get_ids4heap2add($heap_to_add=1, $get_ids4heap2add_mode="", 
  $get_ids4heap2add_min_updatetimeage="", $get_ids4heap2add_max_item_updatetimeage="", $list_id=0){
  global $wpdb, $bdprss_db, $bdprsssearchdebug;

  $list_id=abs(intval($list_id));
  if($get_ids4heap2add_mode=="") $get_ids4heap2add_mode=$this->get_ids4heap2add_mode_default;

  if($get_ids4heap2add_min_updatetimeage==="") $get_ids4heap2add_min_updatetimeage=$this->get_ids4heap2add_min_updatetimeage_default;
  $get_ids4heap2add_min_updatetimeage=abs(intval($get_ids4heap2add_min_updatetimeage));
  if($get_ids4heap2add_max_item_updatetimeage==="") $get_ids4heap2add_max_item_updatetimeage=$this->get_ids4heap2add_max_item_updatetimeage_default;
  $get_ids4heap2add_max_item_updatetimeage=abs(intval($get_ids4heap2add_max_item_updatetimeage));

  $list_condition="";
  $maxagecondition="";

  if($get_ids4heap2add_mode=="maxitem_id"){
    if($list_id > 0) $list_condition=" AND site_id in (select sites.identifier AS site_id from (" . $bdprss_db->listtable . " lists join " . $bdprss_db->sitetable . " sites) where ((concat(_latin1',',lists.url_list,_latin1',') like concat(_utf8'%,',sites.identifier,_utf8',%')) or (lists.list_all = _latin1'Y')) and lists.identifier = '" . $list_id . "')  \n";
    $sql = "SELECT IFNULL(max(a.mval),0) from( \n";
    foreach($this->bdprss_searchtable as $table_to_search){
      $sql .= "SELECT IFNULL(max(item_id),0) mval FROM $table_to_search[0] union \n";
    }
    $sql .= "SELECT IFNULL(max(item_id),0) mval FROM $this->bdprss_searchtable_temp
      ) as a";
    $sql = 	"SELECT identifier FROM " . $bdprss_db->mitemtable . " 
      WHERE identifier > (" . $sql . ") " . $list_condition . " order by identifier limit 0, $heap_to_add";
  } elseif($get_ids4heap2add_mode=="notinstatus"){
//range scan not a problem when range is small
//item_update_time > UNIX_TIMESTAMP() - 86400 ran 0.1 seconds in test with newly created index
    if($list_id > 0) $list_condition=" AND i.item_feed_url IN (select sites.feed_url AS item_feed_url from (" . $bdprss_db->listtable . " lists join " . $bdprss_db->sitetable . " sites) where ((concat(_latin1',',lists.url_list,_latin1',') like concat(_utf8'%,',sites.identifier,_utf8',%')) or (lists.list_all = _latin1'Y')) and lists.identifier = '" . $list_id . "')  \n";
    if($get_ids4heap2add_max_item_updatetimeage > 0) $maxagecondition=" AnD i.item_update_time > UNIX_TIMESTAMP( ) - " . $get_ids4heap2add_max_item_updatetimeage . " \n";
    $sql="SELECT i.identifier as identifier
      FROM " . $bdprss_db->itemtable . " i
      LEFT JOIN " . $this->bdprss_searchtable_status . " sts 
      ON i.identifier = sts.item_id 
      WHERE sts.item_id IS NULL 
      $maxagecondition
      AND i.item_update_time < UNIX_TIMESTAMP( ) - " . $get_ids4heap2add_min_updatetimeage . "
      $list_condition
      ORDER BY identifier
      LIMIT 0, $heap_to_add";
  }elseif($get_ids4heap2add_mode=="processupdates"){
    $this->process_updates=true;
    $sql="SELECT i.identifier as identifier FROM " . $bdprss_db->itemtable . " i, " . $this->bdprss_searchtable_status . " sts
      WHERE i.identifier = sts.item_id
      AND sts.status = 'UPDATE'
      AND i.item_update_time < UNIX_TIMESTAMP() - " . $get_ids4heap2add_min_updatetimeage . "
      ORDER BY identifier LIMIT 0, $heap_to_add";
      //echo "Debug: getitem_id_sql: <br>". $sql . "<br>";
  }elseif($get_ids4heap2add_mode=="processdeletes"){
    $this->process_deletes=true;
    $sql="SELECT sts.item_id as identifier FROM " . $this->bdprss_searchtable_status . " sts
      WHERE sts.status = 'DELETE'
      ORDER BY identifier LIMIT 0, $heap_to_add";
    //echo "Debug: getitem_id_sql: <br>". $sql . "<br>";
  }

  
  if($bdprsssearchdebug) echo "Debug: sql in get_ids4heap2add is: $sql";
	if(strlen($sql)>0)$item_ids2add = $wpdb->get_results($sql);
  //if($bdprsssearchdebug) print_r($item_ids2add);
  $records_found=count($item_ids2add);
  if($bdprsssearchdebug) echo " Records found: " . $records_found;
  return $item_ids2add;
}

function add_heap2search_index($heap_to_add=1, $addheapmode="", $item_ids2add=false, 
    $insertfromtemptable=true, $bdprss_getidmode="", $list_id=0, $get_ids4heap2add_max_item_updatetimeage="", $get_ids4heap2add_min_updatetimeage=""){
  //get max item id in search index
  global $wpdb, $bdprss_db, $bdprsssearchdebug;
  
	if($bdprss_db->highserverload || $bdprss_db->memtablesok!=1){
		if($bdprsssearchdebug) echo "<br>Debug: Load threshold is at: " . $bdprss_db->serverstatus['pbaload']['notice'] . " ... exiting!";
		flush();
		sleep (abs(intval($bdprss_db->serverstatus['pbaload']['notice'])) * 10);
		return false;
	}

  if($addheapmode == "") $addheapmode = $this->default_heapmode;
  if($addheapmode == "bulk" && $this->bulklines == 0) return false;
  if($item_ids2add===false) $item_ids2add=$this->get_ids4heap2add($heap_to_add, $bdprss_getidmode, $get_ids4heap2add_min_updatetimeage, $get_ids4heap2add_max_item_updatetimeage, $list_id);
  
  //add_items in loop
  $bdprss_rowcounter=0;
  if(is_array($item_ids2add)) {
    foreach($item_ids2add as $add_item_id){
      $bdprss_rowcounter++;
      if($bdprsssearchdebug) echo " Debug: #$bdprss_rowcounter: ID $add_item_id->identifier -";
      flush();
      if($bdprss_getidmode=="processdeletes"){
        $this->delete_item_from_search($add_item_id->identifier);
      } else {
        $this->add_item_to_search($add_item_id->identifier, $addheapmode);
      }
      if($addheapmode == "bulk"){
        if($bdprss_rowcounter/$this->bulklines == intval($bdprss_rowcounter/$this->bulklines)) {
          if($bdprsssearchdebug) echo "Debug: Rows processed: " . $bdprss_rowcounter;
          if(count($this->bdprss_bulksql)>0) $this->process_bulk_sql();
          if(strlen($this->bdprss_item_delete_sql)>0) $this->process_delete_sql();
        }
      }
    }
  }
  if($addheapmode == "temptable" && strlen($this->bdprss_item_status_sql)>0){
    $this->bdprss_item_status_sql = "REPLACE into " . $this->bdprss_searchtable_status . " 
      Select identifier as item_id, md5(concat(item_site_name, ' ', item_name, ' ', text_body)) as md5, 'INTEMP' as status, item_time, item_update_time FROM " . $bdprss_db->itemtable . " 
        WHERE identifier IN ( " . substr($this->bdprss_item_status_sql,2) . ")";
        if($bdprsssearchdebug) echo " Debug bdprss_item_status_sql: " . $this->bdprss_item_status_sql;
        flush();
    $result = $wpdb->query($this->bdprss_item_status_sql);
    $this->bdprss_item_status_sql="";
  }
  if(!$this->process_deletes && $insertfromtemptable && $addheapmode == "temptable"){
    if($bdprsssearchdebug) echo "<br>Debug: Copying temp_table entries to indexed tables ...";
    flush();
    foreach($this->bdprss_searchtable as $table_to_search){
      //print_r($table_to_search);
      if($bdprsssearchdebug) echo "<br>Debug: Working on table $table_to_search[0]";
      flush();
      if($this->process_updates){
        if($bdprsssearchdebug) echo " Debug " . date("H:i:s") . ": Deleting - ";
        $deletesql="DELETE FROM $table_to_search[0] USING $table_to_search[0] INNER JOIN $this->bdprss_searchtable_status
                WHERE " . $table_to_search[0] . ".item_id = " . $this->bdprss_searchtable_status . ".item_id
                AND " . $this->bdprss_searchtable_status . ".status = 'INTEMP'";
        if($bdprsssearchdebug) echo " Debug sql: " . $deletesql;
        flush();
        $result = $wpdb->query($deletesql);
      }
      if($bdprsssearchdebug) echo " Debug " . date("H:i:s") . ": Inserting - ";
      flush();
      $sql="Insert into $table_to_search[0] SELECT i.item_id AS item_id, i.index_word AS index_word, i.index_position 
            FROM $this->bdprss_searchtable_temp i
            WHERE ASCII( SUBSTRING( index_word , 1, 1 ) ) >= ASCII('$table_to_search[1]')
            AND ASCII( SUBSTRING( index_word , 1, 1 ) ) <  ASCII('$table_to_search[2]')";
      $result = $wpdb->query($sql);
      if($bdprsssearchdebug) echo " Debug " . date("H:i:s") . ": Done " . $table_to_search[0];
      flush();
    }
    $sql=" TRUNCATE TABLE $this->bdprss_searchtable_temp";
    $result = $wpdb->query($sql);
    $sql="UPDATE " . $this->bdprss_searchtable_status . " SET status = 'OK' WHERE status = 'INTEMP'";
    $result = $wpdb->query($sql);
  } elseif($addheapmode == "bulk"){
    if(count($this->bdprss_bulksql)>0) $this->process_bulk_sql();
  }
  if(strlen($this->bdprss_item_delete_sql)>0) $this->process_delete_sql();
}

//only for debug purpose so far
function get_search_index_info(){
   global $wpdb, $bdprss_db, $bdprsssearchdebug;

  $sql = "SELECT status , count( status ) count
    FROM " . $this->bdprss_searchtable_status . "
    GROUP BY status";
  $searchindex_status=$wpdb->get_results($sql);

  if($bdprsssearchdebug) echo "\n<br><br>Debug: Status of search index: ";
  if($bdprsssearchdebug) print_r($searchindex_status);
  
  return $searchindex_status;
}


  } //class BDPRSS_SEARCH
} //if class BDPRSS_SEARCH not exists

// Make a singleton global instance.
if ( !isset($bdprss_search) ) $bdprss_search = new BDPRSS_SEARCH();

if($bdprsssearchdebug){
  //function is broken in direct use mode, neither tmp tables nor bulk mode is executed then!!!
  //deletion before insert still missing, too
  //$bdprss_add_searchitem_id=abs(intval($_GET['additemid'])); 
  $bdprss_addheap=abs(intval($_GET['addheap']));
  $bdprss_create_proc=$_GET['createproc'];
  $bdprss_searchphrase=stripslashes($_GET['searchphrase']);
  $bdprss_heapmode=stripslashes($_GET['heapmode']);
  if($bdprss_heapmode == "temp") $bdprss_heapmode = "temptable"; //just a short alias
  $bdprss_getidmode=stripslashes($_GET['getidmode']);
  if($bdprss_getidmode!="notinstatus" && $bdprss_getidmode!="maxitem_id" && $bdprss_getidmode!="processupdates" && $bdprss_getidmode!="processdeletes") {
    $bdprss_getidmode="";
  } else {
    echo "Debug getidmode is $bdprss_getidmode ... ";
  }
  if(isset($_GET['mifd'])) $bdprss_mifd=abs(intval($_GET['mifd']));
  if(isset($_GET['mifu'])) $bdprss_mifu=abs(intval($_GET['mifu']));
  if(isset($_GET['listid'])) {
    $bdprss_debug_list_id=abs(intval($_GET['listid']));
  }else{
    $bdprss_debug_list_id=0;
  }
  if(isset($_GET['maxage'])) {
    $get_ids4heap2add_max_item_updatetimeage=abs(intval($_GET['maxage']));
  }else{
    $get_ids4heap2add_max_item_updatetimeage="";
  }

  if(isset($_GET['minage'])) {
    $get_ids4heap2add_min_updatetimeage=abs(intval($_GET['minage']));
  }else{
    $get_ids4heap2add_min_updatetimeage="";
  }


  if($bdprss_mifd > 0 || $bdprss_mifd == '0'){
    echo "Marking item #" . $bdprss_mifd . " for delete ... ";
    $bdprss_debug_result=$bdprss_search->markitem4delete($bdprss_mifd);
    echo "<br>Debugresult given out:<br>";
    print_r($bdprss_debug_result);
  }elseif($bdprss_mifu > 0 || $bdprss_mifu == '0'){
    echo "Marking item #" . $bdprss_mifu . " for update ... ";
    $bdprss_debug_result=$bdprss_search->markitem4update($bdprss_mifu);
    echo "<br>Debugresult given out:<br>";
    print_r($bdprss_debug_result);
  }elseif($bdprss_create_proc > 0) {
    echo "Recreating procedure ... ";
    $bdprss_debug_result=$bdprss_search->bdprss_create_proc();
    echo "<br>Debugresult given out:<br>";
    print_r($bdprss_debug_result);
  }elseif($bdprss_searchphrase != "") {
    $bdprss_searchphrase=urldecode(str_replace("_","+",$bdprss_searchphrase));
    echo "Searchphrase " . utf8_encode($bdprss_searchphrase) . " ... ";
    
    $bdprss_search_result=$bdprss_search->bdprss_search4items(utf8_encode($bdprss_searchphrase),0, 20, false, $bdprss_debug_list_id);
    echo "<br>Searchresult given out:<br>";
    print_r($bdprss_search_result);
  }elseif($bdprss_add_searchitem_id > 0) {
    echo "item_id = $bdprss_add_searchitem_id";
    //add_item_to_search($bdprss_add_searchitem_id); //broken !!! siehe oben
  } elseif($bdprss_addheap > 0 || $bdprss_heapmode == "temptable") {
    if($bdprss_addheap > $bdprss_search->heap_to_add) $bdprss_addheap = $bdprss_search->heap_to_add;
    echo " processing $bdprss_addheap entries ...";
    
    $heapmode=$bdprss_search->default_heapmode;
    $insertfromtemptable=true;
    
    if($bdprss_heapmode == "temptable") {
      if($bdprss_getidmode!="processdeletes") echo " using temp table $bdprss_search->bdprss_searchtable_temp ...";
      $heapmode="temptable";
    } elseif($bdprss_heapmode == "justinsert2temptable") {
      echo " inserting 2 temp table $bdprss_search->bdprss_searchtable_temp without copying temp table to productive tables...";
      $heapmode="temptable";
      $insertfromtemptable=false;
    } 
    if($heapmode=="bulk") {
      if($bdprss_search->bulklines>0) echo " using bulks of $bdprss_search->bulklines lines ...";
    }
    $bdprss_search->add_heap2search_index($bdprss_addheap, $heapmode, false, $insertfromtemptable, $bdprss_getidmode, $bdprss_debug_list_id, $get_ids4heap2add_max_item_updatetimeage, $get_ids4heap2add_min_updatetimeage);
  } else {
    echo " nothing to do, use GET switches to do something in debug mode";
  }

  if($bdprsssearchdebug_with_searchindex_info) $bdprss_search->get_search_index_info();
  echo "<br><Br>Debug: $bdprss_search->bdprss_globalcounter Words. " . $wpdb->num_queries . " queries. " . number_format(timer_stop(),3) . " seconds. </small>"; 

}// if bdprsssearchdebug



?>