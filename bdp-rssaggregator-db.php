<?php 

if( !class_exists('BDPRSS_DB') ) {

	class BDPRSS_DB 
	{	
		var $sitetable;
		var $cfeedurl, $csitename, $cdescription, $csiteurl, $clastpolltime, $curlindex, 
			$cnextpolltime, $cgmtadjust, $cupdatetime, $cidentifier,
			$csitenameoverride, $cpollingfreqmins, 
			$ccatchtextfromhtml, $ccatchhtmlparas, $csitecomment;

		var $itemtable;
		var $iidentifier, $ifeedurl, $iitemname, $iitemtext, $iitemurl, $iitemtime, 
			 $iitemdate;

		var $pbaoutputtable;
		var $pbaoidentifier, $pbaoname, $pbaopage2hookin,
			$pbaodefaultlist, $pbaomaxitems, $pbaoformattype,
			$pbaotemplate_ticker, $pbaoappend_extra_link, $pbaoappend_cache_link, $pbaoadd_social_bookmarks, 
			$pbaosidebarwidget, 
			$pbaomaxlength, $pbaomaxwordlength, $pbaoitem_date_format, $pbaoallowablexhtmltags, 
			$pbaoiscachable, $otemplate_cache, $pbaocacheviewpage, 
			$pba_channel_rssgenerationallowed, 
			$pba_channel_title, $pba_channel_link, 
			$pba_channel_description, $pba_channel_language, $pba_channel_copyright, 
			$otemplate_kalender, $oarchive_date_format, 
			$okalendermonthslist, $okalenderboxtablecaption, $okalender_last, $okalender_next, 
			$okalenderboxdaysofweeklist, $pbao_superparameter;

		var $listtable;
		var $lidentifier, $lname, $lurls, $llistall;
		
		var $errortable;
		var $eidentifier, $efeedurl, $etime, $etext /* , $efeedindex */;
		
		var $mtablestatus;
		var  $memtable, $mdatetime, $mstatus, $mnotice;

		var $now;
		var $bdprss_prevent_dupe_updates;
		
		var $memtablesok, $memtables_were_ok, $serverstatus;

		function BDPRSS_DB() 
		{
			/* BDPRSS_DB() - initialisition function that sets constant names for later use */

			global $wpdb,  $table_prefix;
			
			$this->now = time();
			$this->bdprss_prevent_dupe_updates=array();
			
			// --- site-table
			$this->sitetable =			$table_prefix.'pba_sites';
			$this->cidentifier = 		'identifier';			// primary key
			$this->cfeedurl = 			'feed_url';				// indexed
			$this->csitename =			'site_name';
			$this->csitenameoverride =	'site_name_overriden';	// manual override for site feed names
			$this->cdescription = 		'description';
			$this->csitelicense =			'site_license';
			$this->csiteurl =			'site_url';
			$this->clastpolltime = 		'last_poll_time';		// time last polled
			$this->cnextpolltime = 		'next_poll_time';		// next scheduked poll
			$this->cpollingfreqmins =	'polling_freq_in_mins';	// adjustable polling frequency
			$this->cupdatetime = 		'site_update_time';		// time last updated
			$this->cgmtadjust = 		'gmt_adjustment';		// GMT adjustment to pubDate
			$this->ccatchtextfromhtml = 'catchtextfromhtml';
			$this->ccatchhtmlparas = 'catchhtmlparas';
			$this->csitecomment = 'sitecomment';
			
			// --- item-table
			$this->itemtable = 			$table_prefix."pba_items";
			$this->iidentifier = 		"identifier";			// primary key
			$this->ifeedurl =			"item_feed_url";		// ( combined unique key
			$this->iitemurl = 			"item_url";				// ( combined unique key
			$this->iitemname =			"item_name";
			$this->iitemsitename =			"item_site_name";
			$this->iitemsiteurl =			"item_site_url";
			$this->iitemlicense =			"item_license";
			$this->iitemtext =			"text_body";
			$this->iitemtime = 			"item_time";			// item pubDate time
			$this->iitemdate = 			"item_date";			// item date
			$this->iitemupdate = 		"item_update_time";		// item last updated time -- for debugging

			//pba options table
			$this->optionstable = $table_prefix."pba_options";

			// --- pbaoutputtable
			$this->pbaoutputtable = $table_prefix."pba_outputs";

//management
			$this->pbaoidentifier = "identifier";										// primary key
			$this->pbaoname = "name";																// a useful handle
			$this->pbaopage2hookin = "page2hookin";									//wp page to hook output in

//item selection
			$this->pbaodefaultlist = "default_list";								// default list filter
			$this->pbaomaxitems =		"items_per_site";								//
			$this->pbaoformattype = 				"type";									// type of list: 

//ticker page formatting
			$this->pbaotemplate_ticker = "template_ticker";
			
			$this->pbaoappend_extra_link = "append_extra_link";
			$this->pbaoappend_cache_link = "append_cache_link";
			$this->pbaoadd_social_bookmarks = "add_social_bookmarks";
			
			$this->pbaosidebarwidget = "template_sidebarwidget";

//item formatting

			$this->pbaomaxlength = 		"max_words_in_synposis";// 0 = no limit
			$this->pbaomaxwordlength = "max_word_length";						//
			$this->pbaoitem_date_format = "item_date_format";				//
			$this->pbaoallowablexhtmltags= "allowable_xhtml_tags";	//

//cache
			$this->pbaoiscachable =			"enable_caching";
			$this->otemplate_cache = "template_cache";
			$this->pbaocacheviewpage =			"cache_view_page";

//feed
			$this->pba_channel_rssgenerationallowed =	"channel_rssgenerationallowed";
			$this->pba_channel_title =	"channel_title";
			$this->pba_channel_link =	"channel_link";
			$this->pba_channel_description =	"channel_description";
			$this->pba_channel_language =	"channel_language";
			$this->pba_channel_copyright =	"channel_copyright";

//kalender
			$this->otemplate_kalender = "template_kalender";
			$this->oarchive_date_format = "archive_date_format";
			$this->okalendermonthslist = "kalendermonthslist";
			$this->okalenderboxtablecaption = "kalenderboxtablecaption";
			$this->okalender_last = "kalender_last";
			$this->okalender_next = "kalender_next";
			$this->okalenderboxdaysofweeklist = "kalenderboxdaysofweeklist";
			$this->pbao_superparameter = "superparameter";

			// --- list-table
			$this->listtable = 			$table_prefix."pba_lists";

			$this->lidentifier = 		"identifier";			// primary key
			$this->lname = 				"name";					// a useful handle
			$this->lurls =				"url_list";				// comma separate list
			$this->llistall =			"list_all";				// list all url ids

			// --- error-table
			$this->errortable =			$table_prefix.'pba_errors';

			$this->eidentifier =		'identifier';
			$this->efeedurl =			'feed_url';
			$this->etime =				'when_it_happened';
			$this->etext = 				'error_text';

			// --- memory status table
			$this->mtablestatus =			$table_prefix.'pba_m_tablestatus';
			$this->memtable =			'memtable';
			$this->mdatetime =			'datetime';
			$this->mstatus =			'status';
			$this->mnotice = 'notice';

			// --- site table in memory - probably obsolete - it is a small table and has to be a copy of sitetable on disk
			$this->msitetable =			$table_prefix.'pba_m_sites';
			$this->midentifier = 		'identifier';			// primary key
			$this->mfeedurl = 			'feed_url';				// indexed
			$this->msitename =			'site_name';
			$this->msitenameoverride =	'site_name_overriden';	// manual override for site feed names
			$this->mdescription = 		'description';
			$this->msitelicense =			'site_license';
			$this->msiteurl =			'site_url';
			$this->mlastpolltime = 		'last_poll_time';		// time last polled
			$this->mnextpolltime = 		'next_poll_time';		// next scheduked poll
			$this->mpollingfreqmins =	'polling_freq_in_mins';	// adjustable polling frequency
			$this->mupdatetime = 		'site_update_time';		// time last updated
			$this->mgmtadjust = 		'gmt_adjustment';		// GMT adjustment to pubDate

			// --- index item table in memory
			$this->mitemtable =			$table_prefix.'pba_m_items';
			$this->miid = 		'identifier';			// primary key
			$this->misiteid = 			'site_id';				//      / index
			$this->miitemtime =			'item_time';				//    | index
			$this->miitemdate =			'item_date';				//    \ index

//initialize values to check serverstatus at class construction
			$this->serverstatus=array();
			$this->memtables_were_ok = 1;
//debug begin
//$this->create();
//$this->prefill_table($this->pbaoutputtable);
//debug end
			$this->memtablesok=$this->get_memtable_status($this->serverstatus);
			if($this->memtablesok == 0){
				$this->memtables_were_ok = 0;
				$this->create();
				$this->prefill_memtables();
				$this->memtablesok=$this->get_memtable_status($this->serverstatus);
			}
			$this->highserverload=false;
			if(isset($this->serverstatus['highloadthreshold']['notice']) && isset($this->serverstatus['pbaload']['notice'])){
				if($this->serverstatus['highloadthreshold']['notice'] < $this->serverstatus['pbaload']['notice']) $this->highserverload=true;
			}
		} // function BDPRSS_DB
		
		/* --- create --- */
		function jobaction($injobname="", $action="insert"){
				//what parameter to take here?
				//1. if injobname!="" we limit the processed jobs by parameter injobname
				
				//what to do here with the result? 
				//1. action="insert" - we INSERT IGNORE new waiting jobs without replacing jobs and try to restore backup from optionstable before
				//2. action="kill" - we REPLACE a job/all jobs to become waiting
				//3. action="start" - we REPLACE a job to become running

			global $wpdb;
			
			$injobname=preg_replace('/[^a-zA_Z0-9_-]/','',$injobname);
			
			//uploading jobs from options table to memtable we will just do on startup
			if($action=="insert"){
				$sql="INSERT IGNORE into $this->mtablestatus ( memtable , datetime , status , notice )
					SELECT name as memtable, last_change as datetime, concat(type, value) as status, 
					notice as notice FROM $this->optionstable WHERE type = 'job'";
				if($injobname != "") $sql .= " AND name = '".$injobname."'";
				$result=$wpdb->query($sql);
			}
			
			$sql="SELECT name, value FROM $this->optionstable WHERE type = 'jobdefinition' ";
			if($injobname != "") $sql .= " AND name = '".$injobname."_jobdefinition'";
			$raw_job_defintions=$wpdb->get_results($sql);

			if(!$raw_job_defintions) return false;

			//build up an array with jobs
			foreach($raw_job_defintions as $gotrow => $raw_job_defintion){
				$jobname=str_replace('_jobdefinition','',$raw_job_defintion->name);
				$valuepairs=explode(',',$raw_job_defintion->value); //a value of a raw_job_defintion to explode looks like: startpoint=a10,\r\nmaxexecutiontime=1000
				foreach($valuepairs as $valuenumber => $valuepair){
					$rawvalues=explode('=',$valuepair); // a valuepair to explode looks like \r\nmaxexecutiontime=1000\r\n
					$jobrow[$jobname][trim($rawvalues[0])]=trim($rawvalues[1]);
				}
				//fine - we have got parsed a jobdefinition - so let us now build the values for the job row to insert into db
				if(!isset($jobrow[$jobname]['startpoint'])){
					$jobrow[$jobname]['startpoint']='a10';
				}else{
					if(substr($jobrow[$jobname]['startpoint'],0,1)=='a'){
						$jobrow[$jobname]['nextstarttimestamp']=time() + abs(intval(substr($jobrow[$jobname]['startpoint'],1)));
					}elseif(substr($jobrow[$jobname]['startpoint'],0,1)=='d'){
						//we need to find out the timepoint of next start, we have pairs like startpoint=d00-03:00h
						//how can we specify, that a job shall be run each hour or each minute?
						//we need in the config a cron like syntax with placeholders?
						$daytmp=substr($jobrow[$jobname]['startpoint'],1,2);
						$shour=substr($jobrow[$jobname]['startpoint'],4,2);
						$sminute=substr($jobrow[$jobname]['startpoint'],7,2);
						//compare the time
						$later = 0;
						if((date("H") . date("i")) >= ($shour . $sminute)) $later = 1;
						if(intval($daytmp) == 0) {
							$sday = date("d") + $later;
							$smonth = date("m");
						}else{
							$nextmonth = 0;
							if((date("d") . date("H") . date("i")) >= ($daytmp . $shour . $sminute)) $nextmonth = 1;
							$sday = $daytmp;
							$smonth = date("m") + $nextmonth;
						}
						$jobrow[$jobname]['nextstarttimestamp'] = mktime($shour, $sminute, 0, $smonth, $sday, date("Y"));
					}//end if startpoint 0,1 ==
					$jobrow[$jobname]['nextstart']=date("Y-m-d H:i:s", $jobrow[$jobname]['nextstarttimestamp']);
					if(!isset($jobrow[$jobname]['maxexecutiontime']))$jobrow[$jobname]['maxexecutiontime'] = 10;
					$jobrow[$jobname]['killtimestamp']= time() + $jobrow[$jobname]['maxexecutiontime'];
					$jobrow[$jobname]['killtime']=date("Y-m-d H:i:s", $jobrow[$jobname]['killtimestamp']);
				}//end if isset startpoint
				
				//we have now the values for this jobdefinition
				$insert="REPLACE";
				if($action=="insert"){
					$insert="INSERT IGNORE";
				}
				$sqlbase=$insert . " INTO $this->mtablestatus ( memtable , datetime , status , notice ) VALUES ";
				$sqlvalues =" ( '".$jobname."', '".$jobrow[$jobname]['nextstart']."', 'jobwaiting', 'Created at: ".date("Y-m-d H:i:s")."')";
				if($action=="start") $sqlvalues ="( '".$jobname."', '".$jobrow[$jobname]['killtime']."', 'jobrunning', 'Started at: ".date("Y-m-d H:i:s")."')";
				$result=$wpdb->query($sqlbase . $sqlvalues);
				//echo "sql was: " . $sqlbase . $sqlvalues;

				//the optionstable shall be just read when memtables are crashed to reconstruct memtables,anyway we have to write
				$sqlbase=$insert . " INTO $this->optionstable ( name , last_change , type , value , notice ) VALUES ";
				$sqlvalues =" ( '".$jobname."', '".$jobrow[$jobname]['nextstart']."', 'job', 'waiting', 'Created at: ".date("Y-m-d H:i:s")."')";
				if($action=="start") $sqlvalues = " ( '".$jobname."', '".$jobrow[$jobname]['killtime']."', 'job', 'running', 'Started at: ".date("Y-m-d H:i:s")."')";
				$result=$wpdb->query($sqlbase . $sqlvalues);
				//echo "Query was: " . $sqlbase . $sqlvalues;

			}// end foreach raw job definitions
			//print_r($jobrow);
			return $jobrow;
		}

		function change_jobstatus($jobname, $newstatus){
			global $wpdb;
			
			$jobname=preg_replace('/[^a-zA_Z0-9_-]/','',$jobname);
			$killtime=date("Y-m-d H:i:s",time()+10);
			$sql="REPLACE INTO $this->mtablestatus ( memtable , datetime , status , notice ) VALUES ";
			$sql .=" ( '".$jobname."', '".$killtime."', 'job".$newstatus."', 'Advertized at: ".date("Y-m-d H:i:s")."')";
			$result=$wpdb->query($sql);
			
			return $result;
		}

		function table_exists($tablename)
		{
			global $wpdb;
			return ($wpdb->get_var("show tables like '$tablename'") == $tablename);
		}

		
		function create($try_memtable_creation=true) {
			// create() - create the database tables if they do not already exist
			
			global $wpdb;
			
			$blog_name=get_option('blogname');

			$engine="MYISAM";
			$btree="";
			if($try_memtable_creation) $engine="memory";
			if($try_memtable_creation) $btree =" USING BTREE ";

		if(!$this->table_exists($this->optionstable)){
			$sql =  "CREATE TABLE IF NOT EXISTS $this->optionstable (
				id INT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'an autoincrement id',
				name VARCHAR( 255 ) NOT NULL COMMENT 'a unique name for the option',
				last_change TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Just automatic timestamp of last update',
				type VARCHAR( 255 ) NOT NULL COMMENT 'array, string, bool etc',
				value TEXT NOT NULL COMMENT 'Binary safe value of the pba option',
				notice TEXT NOT NULL COMMENT 'A notice for the option',
				UNIQUE ( name )
				) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci COMMENT = 'globals options for pba'";
			$result = $wpdb->query($sql);
			$this->prefill_table($this->optionstable);
		}

		if(!$this->table_exists($this->pbaoutputtable)){
			$sql =  "CREATE TABLE IF NOT EXISTS $this->pbaoutputtable (
			  identifier int(10) NOT NULL auto_increment,
			  name varchar(255) NOT NULL default '-',
			  page2hookin int(11) NOT NULL default '0' COMMENT 'The wordpress page to hook in this output',
			  default_list int(11) NOT NULL default '0' COMMENT 'The Default List Filter for the Output format',
			  items_per_site int(4) NOT NULL default '10',
			  type enum('countrecentitem','daterecentitem','sitealpha','siteupdate') NOT NULL default 'countrecentitem',
			  template_ticker text COMMENT 'template for ticker',
			  append_extra_link enum('Y','N') NOT NULL default 'Y',
			  append_cache_link enum('Y','N') NOT NULL default 'Y',
			  add_social_bookmarks enum('Y','N') NOT NULL default 'Y',
			  template_sidebarwidget text COMMENT 'template for sidebar widgets',
			  max_words_in_synposis int(4) NOT NULL default '10',
			  max_word_length int(4) NOT NULL default '35',
			  item_date_format varchar(30) default 'd.m.Y \\\u\\\m H:i\\\h',
			  allowable_xhtml_tags varchar(150) default '',
			  enable_caching enum('Y','N') NOT NULL default 'Y',
			  template_cache text NOT NULL COMMENT 'template for the cache page',
			  cache_view_page varchar(200) default NULL,
			  channel_title varchar(100) default 'Feedmix von ".$blog_name."',
			  channel_link varchar(100) default '',
			  channel_description varchar(200) default 'Feedmix generiert mit dem Parteibuch Aggregator)',
			  channel_language varchar(10) default 'de',
			  channel_copyright varchar(100) default 'Verschiedene (Details Siehe Autorenlinks)',
			  template_kalender text NOT NULL,
			  archive_date_format varchar(255) NOT NULL default 'Y-m-d',
			  kalendermonthslist varchar(255) NOT NULL default 'Januar, Februar, M&auml;rz, April, Mai, Juni, Juli, August, September, Oktober, November, Dezember',
			  kalenderboxtablecaption varchar(255) NOT NULL default ' id=\"kalendertable\"',
			  kalender_last varchar(255) NOT NULL default 'Fr&uuml;her',
			  kalender_next varchar(255) NOT NULL default 'Sp&auml;ter',
			  kalenderboxdaysofweeklist varchar(255) NOT NULL default 'Montag, Dienstag, Mittwoch, Donnerstag, Freitag, Samstag, Sonntag',
			  superparameter text NOT NULL COMMENT 'free style paras',
			  PRIMARY KEY  (identifier)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1";
			$result = $wpdb->query($sql);
			$this->prefill_table($this->pbaoutputtable);
		}

		if(!$this->table_exists($this->listtable)){
			$sql =  "CREATE TABLE IF NOT EXISTS $this->listtable (".
				// about the list
				"$this->lidentifier			int(10) NOT NULL auto_increment, " .
				"$this->lname				varchar(255) NOT NULL default '-', ".
				"$this->lurls				text, ".
				"$this->llistall enum('Y','N') NOT NULL default 'N', " .
				"PRIMARY KEY 				($this->lidentifier) ) CHARSET=latin1"; 
			$result = $wpdb->query($sql);
			$this->prefill_table($this->listtable);
		}

			$sql = "CREATE TABLE IF NOT EXISTS $this->errortable (".
/* changed */	"$this->eidentifier			int(10) NOT NULL auto_increment, ".
				"$this->efeedurl			varchar(240) NOT NULL, ".
				"$this->etime				int(15) NOT NULL, ".
				"$this->etext				text NOT NULL, ".
				"PRIMARY KEY				($this->eidentifier), ".
/* changed */	"INDEX						($this->efeedurl) ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci ";
			$result = $wpdb->query($sql);

		if(!$this->table_exists($this->sitetable)){
			$sql = "CREATE TABLE IF NOT EXISTS $this->sitetable (".
				"$this->cidentifier			int(10) NOT NULL auto_increment, " .
				"$this->cfeedurl			varchar(240) NOT NULL, ".
				"$this->csitename			varchar(255), ".
				"$this->csitenameoverride	enum('Y','N') NOT NULL default 'N', " .
				"$this->cdescription		varchar(255), ".
				"$this->csitelicense			varchar(255), ".
				"$this->csiteurl			varchar(255), ".
				"$this->clastpolltime		int(15) NOT NULL DEFAULT 1, " .
				"$this->cnextpolltime		int(15) NOT NULL DEFAULT 1, " .
				"$this->cupdatetime			int(15) NOT NULL DEFAULT 1, " .
				"$this->cgmtadjust			float(4,1) NOT NULL DEFAULT 0.0, ".
				"$this->cpollingfreqmins	int(6) NOT NULL DEFAULT 0, ".
				"$this->ccatchtextfromhtml ENUM( 'Y', 'N' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'N' COMMENT 'parse site content from html using pba-loader', ".
				"$this->cpollingfreqmins	int(6) NOT NULL DEFAULT 0, ".
				"$this->ccatchhtmlparas TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'array of parameters for html parsing', ".
				"$this->csitecomment TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'a comment in the backend', ".
				"PRIMARY KEY				($this->cidentifier), ".
				"UNIQUE KEY					($this->cfeedurl), ".
				"INDEX						($this->clastpolltime) ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci ";
			$result = $wpdb->query($sql);
			$this->prefill_table($this->sitetable);
		}

			$sql = "CREATE TABLE IF NOT EXISTS $this->itemtable (".
				"$this->iidentifier			int(10) NOT NULL auto_increment, ".
				"$this->ifeedurl			varchar(150) NOT NULL, ".
				"$this->iitemurl			varchar(183) NOT NULL, ".
				"$this->iitemname			varchar(255) NOT NULL, ".
				"$this->iitemsitename			varchar(255) NOT NULL, ".
				"$this->iitemsiteurl			varchar(255) NOT NULL, ".
				"$this->iitemlicense			varchar(255) NOT NULL, ".
				"$this->iitemtext			text NOT NULL, ".
				"$this->iitemtime			int(15) NOT NULL, ".
				"$this->iitemdate			date NOT NULL, ".
				"$this->iitemupdate			int(15) NOT NULL, ".
				"PRIMARY KEY				($this->iidentifier), " .
				"UNIQUE KEY					($this->ifeedurl (150), $this->iitemurl (183)), ".
				"INDEX						($this->iitemdate), ".
				"INDEX						($this->ifeedurl), ".
				"INDEX						($this->iitemtime) ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci ";
			//echo $sql;
			$result = $wpdb->query($sql);

			$sql= "CREATE TABLE IF NOT EXISTS $this->mtablestatus (
$this->memtable CHAR( 60 ) NOT NULL ,
$this->mdatetime DATETIME,
$this->mstatus CHAR( 10 ) NOT NULL ,
$this->mnotice CHAR( 50 ) NOT NULL ,
PRIMARY KEY ( $this->memtable )
) ENGINE = " . $engine . " DEFAULT CHARSET=utf8 COLLATE utf8_general_ci ";
			$result = $wpdb->query($sql);

			$sql= "CREATE TABLE IF NOT EXISTS $this->mitemtable (
			  $this->miid mediumint(8) unsigned NOT NULL,
			  $this->misiteid smallint(5) unsigned NOT NULL,
			  $this->miitemtime int(15) NOT NULL,
			  $this->miitemdate date NOT NULL,
			  PRIMARY KEY  ($this->miid),
			  KEY `Memory_index`  " . $btree . "  ($this->misiteid,$this->miitemtime),
			  KEY `Memory_index_time_id`  " . $btree . "  ($this->miitemtime,$this->misiteid)
				) ENGINE = " . $engine . " DEFAULT CHARSET=utf8 COLLATE utf8_general_ci ";
			$result = $wpdb->query($sql);
			
			if($try_memtable_creation){
				$this->create(false);
			}
			
		}

		function prefill_table($tablename){
			global $wpdb;
			if($tablename == $this->sitetable){
				$feed_url=mysql_real_escape_string(get_pbadefaultsite('feed_url'));
				$site_name=mysql_real_escape_string(get_pbadefaultsite('site_name'));
				$description=mysql_real_escape_string(get_pbadefaultsite('description'));
				$site_license=mysql_real_escape_string(get_pbadefaultsite('site_license'));
				$site_url=mysql_real_escape_string(get_pbadefaultsite('site_url'));
				$sql="INSERT INTO $this->sitetable (
					feed_url , site_name , description , site_license , site_url
				) VALUES (
					'".$feed_url."', 
					'".$site_name."', '".$description."', 
					'".$site_license."' , '".$site_url."'
				)";
			}
			if($tablename == $this->listtable){
				$sql="INSERT INTO $this->listtable (identifier, name, url_list, list_all) 
					VALUES (1, 'Full Text Ticker', '1', 'Y')";
			}
			if($tablename == $this->pbaoutputtable){
				$outputname=mysql_real_escape_string(get_pbadefaultparameter('outputname'));
				$template_ticker=mysql_real_escape_string(get_pbadefaultparameter('template_ticker'));
				$template_sidebarwidget=mysql_real_escape_string(get_pbadefaultparameter('template_sidebarwidget'));
				
				$channel_language=mysql_real_escape_string(get_pbadefaultparameter('channel_language'));
				$channel_copyright=mysql_real_escape_string(get_pbadefaultparameter('channel_copyright'));
				
				$template_cache=mysql_real_escape_string(get_pbadefaultparameter('template_cache'));
				$template_kalender=mysql_real_escape_string(get_pbadefaultparameter('template_kalender'));
				$kalendermonthslist=mysql_real_escape_string(get_pbadefaultparameter('kalendermonthslist'));
				$kalender_last=mysql_real_escape_string(get_pbadefaultparameter('kalender_last'));
				$kalender_next=mysql_real_escape_string(get_pbadefaultparameter('kalender_next'));
				$kalenderboxdaysofweeklist=mysql_real_escape_string(get_pbadefaultparameter('kalenderboxdaysofweeklist'));

				$sql="INSERT INTO $this->pbaoutputtable 
				(name, page2hookin, default_list, items_per_site, type, template_ticker, 
					append_extra_link, append_cache_link, add_social_bookmarks, template_sidebarwidget, 
					max_words_in_synposis, max_word_length, item_date_format, allowable_xhtml_tags, enable_caching, 
					template_cache, cache_view_page, channel_title, channel_link, 
					channel_description, channel_language, channel_copyright, template_kalender, archive_date_format, 
					kalendermonthslist, 
					kalenderboxtablecaption, kalender_last, kalender_next, 
					kalenderboxdaysofweeklist, superparameter) 
				VALUES 
				('".$outputname."', 0, 1, 25, 'countrecentitem', '".$template_ticker."', 
					'Y', 'Y', 'Y', '".$template_sidebarwidget."', 
					100, 40, 'd.m.Y H:i\\\h', '', 'Y', 
					'".$template_cache."', '', 'Parteibuch Aggregator Feed', '', 
					'Parteibuch Aggregator Feedmix', '".$channel_language."', '".$channel_copyright."', '".$template_kalender."', 'Y-m-d', 
					'".$kalendermonthslist."', 
					' style=&quot;text-align: center;&quot; id=&quot;kalendertable&quot;', 
					'".$kalender_last."', '".$kalender_next."', '".$kalenderboxdaysofweeklist."', '')
				";
			}
			if($tablename == $this->optionstable){
				$sql="INSERT INTO $this->optionstable ( name , type , value , notice ) 
					VALUES
					( 'enable_caching', 'string', 'auto', 'shall be one of Y, N, auto, if not set, default shall be auto' )
					, ( 'enable_rewriting', 'string', 'Y', 'shall be one of Y, N, if not set, default shall be Y' )
					, ( 'full_cache_time', 'int', '60', '0 shall disable this cache' )
					, ( 'kalenderquery_cache_time', 'int', '3600', '0 shall disable this cache' )
					, ( 'feedlistquery_cache_time', 'int', '7200', '0 shall disable this cache' )
					, ( 'enable_memtables', 'string', 'auto', 'shall be one of Y, N, auto, if not set, default shall be auto' )
					, ( 'enable_loaddetection', 'string', 'auto', 'shall be one of Y, N, auto, if not set, default shall be auto' )
					, ( 'highloadthreshold', 'int', '10', '0 may disable housekeeping jobs' )
					, ( 'update_oldest_jobdefinition', 'jobdefinition', 'startpoint=a10,\r\nmaxexecutiontime=100', 'shall be started not earlier than 10 seconds after the last time it finished')
					, ( 'housekeeping_cache_jobdefinition', 'jobdefinition', 'startpoint=d00-03:00h,\r\nmaxexecutiontime=3600', 'Shall be executed each day one time the next time after a page was called after 3 oclock in the morning')
					, ( 'process_updates_jobdefinition', 'jobdefinition', 'startpoint=a3600,\r\nmaxexecutiontime=100', 'shall be processed one hour - 3600 seconds - after this job ended the last time')
					, ( 'process_new_jobdefinition', 'jobdefinition', 'startpoint=a3600, \r\nmaxexecutiontime=500', 'new entries to the search index shall be processed 3600 seconds after it run the last time, but only, if there were no unprocessed new records left')
					, ( 'process_deletes_jobdefinition', 'jobdefinition', 'startpoint=a3600,\r\nmaxexecutiontime=100', 'deletions from the search index shall be done monthly at the first page call after day 3 04:17h.')
					";
			}
			$result = $wpdb->query($sql);
			return mysql_insert_id();
		} //end function

		function get_mysql_variables($varname=""){
			global $wpdb;
			$sql="SHOW VARIABLES ";
			if(is_string($varname) && $varname != '') $sql .= " LIKE '".$varname."'";
			$result = $wpdb->get_row($sql);
			if(isset($result->Value)){
				return $result->Value;
			} else {
				return "";
			}
		}

		function get_mysql_tablestatus($tablename=""){
			global $wpdb;
			$sql="SHOW TABLE STATUS ";
			if(is_string($tablename) && $tablename != '') $sql .= " LIKE '".$tablename."'";
			$result = $wpdb->get_row($sql);
			if($result){
				return $result;
			} else {
				return false;
			}
		}


		function prefill_memtables(){
			global $wpdb;
			
//			    echo "<br>Filling memtables ... ";

			//set memory status value for all tables to start
			$sql= "REPLACE INTO $this->mtablestatus (
				$this->memtable , $this->mdatetime , $this->mstatus, $this->mnotice
				) VALUES ( 'sites', NOW( ) , 'start' , 'prefilling'),
				( 'items', NOW( ) , 'start' , 'prefilling')";
			$result = $wpdb->query($sql);

			//fill item index memory table
			$sql= "TRUNCATE TABLE $this->mitemtable ";
			$result = $wpdb->query($sql);

			$sql= "INSERT INTO $this->mitemtable 
				SELECT itemtable.$this->iidentifier as $this->miid, sitetable.$this->cidentifier as $this->misiteid, 
				itemtable.$this->iitemtime as $this->miitemtime, itemtable.$this->iitemdate as $this->miitemdate 
				FROM $this->itemtable itemtable, $this->sitetable sitetable 
				WHERE itemtable.$this->ifeedurl = sitetable.$this->cfeedurl";
			$result = $wpdb->query($sql);

			//prefill jobtable
			$this->jobaction();

			//if all OK, set mem table status to OK
			$sql= "REPLACE INTO $this->mtablestatus (
				$this->memtable , $this->mdatetime , $this->mstatus, $this->mnotice
				) VALUES ( 'sites', NOW( ) , 'ok', 'prefilled' ),
				( 'items', NOW( ) , 'ok' , 'prefilled')";
			$result = $wpdb->query($sql);

//    echo "Memtables filled";

			return true;
		}

		function mark_entry_as_old_in_statustable($name){
			//this will make some memtablerows to be recreated at next pagecall
			global $wpdb;
			$sql= "UPDATE $this->mtablestatus SET $this->mdatetime = FROM_UNIXTIME( '0' )
				WHERE $this->memtable = '".$name."'";
			$result = $wpdb->query($sql);
			return $result;
		}
		

//Check the status of the memory tables
//possible returns
// 0: not filled - need to be filled
// 1: ok - filled and fine
// 2: start - filling in progress going on - please wait
// 3: start - filling in progress hanging already too long - need to be refilled


		function get_memtable_status(&$status)
		{
			global $wpdb;
			$maxstartingtime="7200";
			$memtablestatus=0;
			$totalstatus=0;
			$keycounter=0;
			$status=array();
			$status['memtablestatus']=0;

			
			$sql= "SELECT $this->memtable, UNIX_TIMESTAMP() - UNIX_TIMESTAMP($this->mdatetime) as age, $this->mstatus, $this->mnotice FROM $this->mtablestatus";
			$result=false;
			$result = $wpdb->get_results($sql);
//			echo "<br>Mem table status: ";
			if(!$result) {
//				echo "Mem tables not filled!";
			} else {
				$jobcounter=0;
				$maxagejob['age']=0;
				foreach($result as $key => $r) 
				{
					$keycounter++;
					if($r->{$this->mstatus}=="ok" || $r->{$this->mstatus} == "unusable" 
						|| $r->{$this->mstatus} == "disabled" || $r->{$this->mstatus} == "value"
						|| substr($r->{$this->mstatus},0,3) == "job"
						) $totalstatus++;
					//$totalstatus.=$r->{$this->mstatus};
					if($r->{$this->mstatus}=="start"){
						$memtablestatus=2;
						if($r->age > $maxstartingtime) $memtablestatus=3;
					}
					//copy the detailed status into the overloaded array
					$status[$r->{$this->memtable}]['status'] = $r->{$this->mstatus};
					$status[$r->{$this->memtable}]['age'] = $r->age;
					$status[$r->{$this->memtable}]['notice'] = $r->{$this->mnotice};
					if(substr($r->{$this->mstatus}, 0,3) == "job") $jobcounter++;
					if(substr($r->{$this->mstatus}, 0,3) == "job" && $r->age > 0) {
						//we shall look if we shall do something with a joba job should be run or running
						if(substr($r->{$this->mstatus}, 3) == "running" ){
							$status['job2kill']=$r->{$this->memtable};
						}elseif(substr($r->{$this->mstatus}, 3) == "tostart"){
							$status['job2restart']=$r->{$this->memtable};
						}elseif(substr($r->{$this->mstatus}, 3) == "waiting"){
							if($r->age > $maxagejob['age']){
								$maxagejob['name']=$r->{$this->memtable};
								$maxagejob['age']=$r->age;
								$maxagejob['status']= $r->{$this->mstatus};
							}
						}
					}
				}
			}

			//now let's find out a mechanism to bring the whole status into one value
			if ($totalstatus > 0 && $keycounter == $totalstatus ) {
				$memtablestatus = 1;
				$status['memtablestatus']=1;

				//not really elegant to have the number of available jobs here hardcoded, but who cares
				if($jobcounter == 5){
					if(isset($status['job2kill'])){
						//job is running too long - kill this job - this fallback should just be hit in case of a job crash
						$this->change_jobstatus($status['job2kill'], 'waiting');
					}elseif(isset($status['job2restart'])){
						$this->change_jobstatus($status['job2restart'], 'tostart');
						$status['job2start']['name']=$status['job2restart'];
						$status['job2start']['time']=time();
					}elseif(isset($maxagejob['name'])){
						//announce a new job
						$this->change_jobstatus($maxagejob['name'], 'tostart');
						$status['job2start']['name']=$maxagejob['name'];
						$status['job2start']['time']=time();
					}
				}else{
					$this->jobaction();
				}
			}

			$this->check_load($status);

			if(!isset($status['pbacache']) || $status['pbacache']['age'] > 10800) {
				//check cache
				$this->check_cache($status);
			}
			
					//echo "Wea re here: status_pbacache is " . $status['pbacache'];
			if(isset($status['pbacache']['status']) && $status['pbacache']['status'] == 'ok') {
				if(!isset($status['full_cache_time']) || !isset($status['kalenderquery_cache_time'])  || !isset($status['feedlistquery_cache_time'])){
					$this->check_options($status);
				}
			}

			if(!isset($status['rewriting']) || $status['rewriting']['age'] > 86400) {
				//check rewriting
				$this->check_rewriting($status);
			}

//print_r($status);

			return $memtablestatus;
		}

		function check_rewriting(&$status){
			global $wpdb;
			$pbadefault=$this->pbaoption('enable_rewriting');
			if($pbadefault != 'Y') {
				$status['rewriting']['status'] = 'disabled';
				$status['rewriting']['notice'] = 'N00003: Rewriting option disabled';
				$return = false;
			}elseif($pbadefault == 'Y'){
				$rewrite_array=get_option('rewrite_rules');
				if(is_array($rewrite_array)){
					$rulematch=false;
					foreach($rewrite_array as $key => $value){
						if(strstr($key,'ticker-feed')) $rulematch=true;
					}
					if($rulematch){
						$status['rewriting']['status'] = 'ok';
						$status['rewriting']['notice'] = 'N00004: Permalinks in effect';
						$return = true;
					}
				}
				if(!isset($return)){
					$status['rewriting']['status'] = 'unusable';
					$status['rewriting']['notice'] = 'W00002: Wordpress has not enabled rewrite';
					$return = false;
				}
			}
			$sql= "REPLACE INTO $this->mtablestatus (
				$this->memtable , $this->mdatetime , $this->mstatus, $this->mnotice
				) VALUES ( 'rewriting', NOW( ) , '".$status['rewriting']['status']."' , '".$status['rewriting']['notice']."')";
			$result = $wpdb->query($sql);
			return $return;
		}

		function check_options(&$status){
			global $wpdb;
//initialisation
			$newstatus=array();
			$result = $this->get_all_options();

			if(!$result){
				return false;
			} else {
				foreach($result as $key => $r){
					if($r->name == 'full_cache_time') $newstatus['full_cache_time']=array('status' => 'value', 'notice' => $r->value);
					if($r->name == 'kalenderquery_cache_time') $newstatus['kalenderquery_cache_time']=array('status' => 'value', 'notice' => $r->value);
					if($r->name == 'feedlistquery_cache_time') $newstatus['feedlistquery_cache_time']=array('status' => 'value', 'notice' => $r->value);
					if($r->name == 'highloadthreshold') $newstatus['highloadthreshold']=array('status' => 'value', 'notice' => $r->value);
					if($r->name == 'enable_loaddetection') $newstatus['loaddetection']=array('status' => 'value', 'notice' => $r->value);
				}
	
				if(count($newstatus)>0){
					$sql= "REPLACE INTO $this->mtablestatus (
						$this->memtable , $this->mdatetime , $this->mstatus, $this->mnotice
						) VALUES ";
	
					foreach($newstatus as $fieldname => $values){
						$sql .= "\n( '".$fieldname."', NOW( ) , '".$values['status']."' , '".$values['notice']."'), ";
					}
					$sql = preg_replace('/, $/','', $sql);
					$result = $wpdb->query($sql);
					$status=array_merge($status, $newstatus); //advertising
					return $result;
				}
			}
			return false;
		}

		function pba_loaddetection(){
			if(function_exists('sys_getloadavg')) {
				$load=sys_getloadavg();
				$load[1]=$load[0];
			}elseif(ini_get('safe_mode')){
				if(ini_get('safe_mode_exec_dir') == ""){
					$serverload = "0";
				} else {
					preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",@exec(ini_get('safe_mode_exec_dir') . 'uptime_wrapper.sh'),$load);
				}
			}else{
				//no safe mode, so directly call system command
				preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",@exec('uptime'),$load);
				if(!isset($load[1])) preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",@exec(dirname(__FILE__) . '/wrapper/uptime_wrapper.sh'),$load);
			}
			if(!isset($load[1])) {
				$serverload = 0;
			}else{
				$serverload = abs($load[1]);
			}
			return $serverload;
		}

		function check_load(&$status){
			global $wpdb;
			$detectedload=0;
			//is load detection enabled?

			if(isset($status['loaddetection']['notice']) 
				&& isset($status['pbaload']['notice'])
				&& isset($status['pbaload']['age'])
				){
				//option disabled and load set to zero, nothing to do here
				if($status['loaddetection']['notice'] == 'N' && $status['pbaload']['notice'] == '0') return true;
				
				//call uptime just every three seconds
				if($status['loaddetection']['notice'] == 'Y' && $status['pbaload']['age'] < 3)  return true;
				
				//retry once per hour
				if($status['loaddetection']['notice'] == 'retry' && $status['pbaload']['age'] < 3600) return true;

				if($status['loaddetection']['notice'] == 'Y' 
					|| $status['loaddetection']['notice'] == 'auto' 
					|| $status['loaddetection']['notice'] == 'retry'
				){
					$detectedload=$this->pba_loaddetection();
					if($status['loaddetection']['notice'] == 'auto' || $status['loaddetection']['notice'] == 'retry'){
						$newstatus['loaddetection']['status']='value';
						if($detectedload > 0){
							$newstatus['loaddetection']['notice']='Y';
						}else{
							$newstatus['loaddetection']['notice']='retry';
						}
						$sql= "REPLACE INTO $this->mtablestatus (
							$this->memtable , $this->mdatetime , $this->mstatus, $this->mnotice
							) VALUES ( 'loaddetection', NOW( ) , '".$newstatus['loaddetection']['status']."' , '".$newstatus['loaddetection']['notice']."')";
						$result = $wpdb->query($sql);
					}
				}
			} //end if isset status load detection
			$newstatus['pbaload']=array('status' => 'value', 'notice' => abs($detectedload));
			$sql= "REPLACE INTO $this->mtablestatus (
				$this->memtable , $this->mdatetime , $this->mstatus, $this->mnotice
				) VALUES ( 'pbaload', NOW( ) , '".$newstatus['pbaload']['status']."' , '".$newstatus['pbaload']['notice']."')";
			$result = $wpdb->query($sql);
			$status=array_merge($status, $newstatus); //advertising
			return $result;
		}

		function check_cache(&$status){
			global $wpdb;
			//run on class construction, PBALIB not yet loaded here!!!
			$cache_status=true;
			//$cache_path=dirname(__FILE__)."/pbacache/";
			$cache_path=PBA_CACHE_PATH;
			$cache_file_handle = @fopen($cache_path."cachetest__t", 'w+');
			$content=rand() . 'test';
			if(!@fwrite($cache_file_handle, $content)) $cache_status=false;
			@fclose($cache_file_handle);
			if(!$get_cache=@file_get_contents($cache_path."cachetest__t")) $cache_status=false;
			if(!$get_cache == $content) $cache_status=false;
			//echo "cache_status " . $cache_status;
			//exit;
			@unlink($cache_path."cachetest__t");
			$pbadefault=$this->pbaoption('enable_caching');
			if($cache_status==false) {
				$status['pbacache']['status'] = 'unusable';
				$status['pbacache']['notice'] = 'W00001: Cachepath not writeable';
				$return = false;
			}elseif($pbadefault != 'Y' && $pbadefault != 'auto'){
				$status['pbacache']['status'] = 'disabled';
				$status['pbacache']['notice'] = 'N00005: Cachepath is writable';
				$return = true;
			}else{
				$status['pbacache']['status'] = 'ok';
				$status['pbacache']['notice'] = 'N00002: Checked';
				$return = true;
			}
			
			$sql= "REPLACE INTO $this->mtablestatus (
				$this->memtable , $this->mdatetime , $this->mstatus, $this->mnotice
				) VALUES ( 'pbacache', NOW( ) , '".$status['pbacache']['status']."' , '".$status['pbacache']['notice']."')";
			$result = $wpdb->query($sql);
			return $return;
		}

		/* --- insert --- */ 
		function recordError($url, $text)
		{
			global $wpdb;
			
			if(!$url) return;
			
			// some SQL insertion protection using HTML entities
			$text = preg_replace("/'/", '&#39;', $text);
			$text = preg_replace('/"/', '&quot;', $text);
			
			if(!$text) $text = 'Unknown';
			
			$sql = "INSERT INTO $this->errortable ($this->efeedurl, $this->etime, $this->etext) ".
				"VALUES ('$url', '$this->now', '$text')";
			$result = $wpdb->query($sql);
		}

		function update_feedurl($feed_id, $pba_change_feed_to) {
			global $wpdb;
			//function will return false, if there shall no update of feed url in site tables made
			//check input values
			if($feed_id != abs(intval($feed_id)) || $feed_id == 0) return false;
			if(preg_match('/[\'"]/',$pba_change_feed_to)) return  false;
			
			//return false if the feed url already exists, logic to merge feeds is not implemented
			$sql="SELECT count(*) + 1 as numberoffeeds from $this->sitetable WHERE $this->cfeedurl = '".$pba_change_feed_to."'";
			if( $wpdb->get_var($sql) != 1) return false;
			//OK, so now get the feed url to update
			$sql="SELECT $this->cfeedurl from $this->sitetable WHERE $this->cidentifier = '".$feed_id."'";
			$oldfeedurl=$wpdb->get_var($sql);
			if(!$oldfeedurl) return false;
			$sql="UPDATE $this->itemtable set item_feed_url = '".$pba_change_feed_to."' 
				WHERE item_feed_url = '".$oldfeedurl."'";
			$result = $wpdb->query($sql);
			if($result === false) return false;
			return true;
		}
		
		function createpbaoutput($as_copy_from=false) 
		{
			/* createoutput() -- inserts an empty output format into the output table 
			or copies one with ID as_copy from false*/

			global $wpdb;
			
			if($as_copy_from===false){
				return $this->prefill_table($this->pbaoutputtable);
			} else {
				$sql = "SELECT * FROM $this->pbaoutputtable WHERE $this->pbaoidentifier = '$as_copy_from'";
				$row_to_copy = $wpdb->get_row($sql);
				if($row_to_copy){
					$fieldnames = $this->pbaoname . ", " . $this->pbaopage2hookin;
					$values = "'Copy of " . $row_to_copy->{$this->pbaoname} . "', 0";
					foreach($row_to_copy as $fieldname => $fieldvalue){
						if($fieldname != $this->pbaoidentifier 
							&& $fieldname != $this->pbaoname
							&& $fieldname != $this->pbaopage2hookin
						){
							$fieldnames .= ", " . $fieldname;
							$fieldvalue = mysql_real_escape_string($fieldvalue);
							$values .= ", '" . $fieldvalue . "'";
						}
					}
					$sql="INSERT INTO $this->pbaoutputtable ($fieldnames) ".
					"VALUES ($values)";
					$result = $wpdb->query($sql);
					return mysql_insert_id();
				}
			}
		}



		function createlist($as_copy_from=false) 
		{
			/* createlist() -- inserts an empty output format into the list table 
			or copies one with ID as_copy from false*/
			
			global $wpdb;
			
			if($as_copy_from===false){
				$sql = "INSERT INTO $this->listtable ($this->lname) ".
					"VALUES ('New list: please give it a meaningful name')";
				$result = $wpdb->query($sql);
				return mysql_insert_id();
			} else {
				$sql = "SELECT * FROM $this->listtable WHERE $this->lidentifier = '$as_copy_from'";
				$row_to_copy = $wpdb->get_row($sql);
				if($row_to_copy){
					$fieldnames = "$this->lname";
					$values = "'Copy of " . $row_to_copy->{$this->lname} . "'";
					foreach($row_to_copy as $fieldname => $fieldvalue){
						if($fieldname != $this->lidentifier && $fieldname != $this->lname){
							$fieldnames .= ", " . $fieldname;
							$fieldvalue = mysql_real_escape_string($fieldvalue);
							$values .= ", '" . $fieldvalue . "'";
						}
					}
					$sql="INSERT INTO $this->listtable ($fieldnames) ".
					"VALUES ($values)";
					$result = $wpdb->query($sql);
					return mysql_insert_id();
				}
			}
		}

		function insert_in_sitetable($url, $polltime) 
		{
			global $wpdb;
			
			$sql = "INSERT INTO $this->sitetable ". 
				"($this->cfeedurl, $this->clastpolltime) ". 
				"VALUES ('$url', '$polltime')"; 
			$result = $wpdb->query($sql);

		}
		
		
		/* --- insert or modify --- */
		
		function updateItem($url, $title, $text, $link, $ticks, $itemsitename="", $itemsiteurl="", $itemlicense="", $insertupdatemode='standard'){
			// updateItem($url, $title, $text, $link, $ticks) -- returns false on update and true on insert
			
			global $wpdb, $bdprss_search;

			$sql = "SELECT $this->iidentifier as id, $this->iitemsitename as sitename, $this->iitemname as itemname, $this->iitemtext as itemtext, 
				md5(concat($this->iitemsitename, ' ', $this->iitemname, ' ', $this->iitemtext)) as md5
				FROM $this->itemtable ".
				"WHERE $this->ifeedurl='$url' AND $this->iitemurl='$link'";
			$hid = $wpdb->get_row($sql);

			$md5tocompare=md5($itemsitename . ' ' . $title . ' ' . $text);
			if($hid) {
				$hidfromdb2compare = md5($hid->sitename . ' ' . $hid->itemname . ' ' . $hid->itemtext);
				if($insertupdatemode=='justupdateitemtext') $title = $hid->itemname;
				if($insertupdatemode=='justupdateitemtext') $itemsitename = $hid->sitename;
				if($insertupdatemode=='noupdate') return false;
			}else{
				if($insertupdatemode=='justupdateitemtext') {
					$this->recordError($url, "No hid found for link: " . $link . " ... exiting!");
					return true;
				}
			}

			$hidid=0;
			if($hid) $hidid = $hid->id;
			if($hidid > 0 && ($insertupdatemode=='justupdateitemtext' || (!isset($this->bdprss_prevent_dupe_updates[$hidid]) || (isset($this->bdprss_prevent_dupe_updates[$hidid]) && $this->bdprss_prevent_dupe_updates[$hidid]."" !="Done"))))
			{
				if($md5tocompare != $hid->md5 && $md5tocompare != $hidfromdb2compare && rand(1,10) > 0){

				$md5debug="MD5 Debug on Update: MD5 in DB: " . $hid->md5 . 
					" md5compare: " . $md5tocompare .
	  			" md5direct: " . md5($itemsitename . ' ' . $title . ' ' . $text) .
	  			" mysql_real_escaped: "  . md5(mysql_real_escape_string($itemsitename . ' ' . $title . ' ' . $text)) .
	  			" mysql_stripped_escaped: "  . md5(mysql_real_escape_string(stripslashes($itemsitename . ' ' . $title . ' ' . $text))) .
					  " hidfromdb2compare: " . $hidfromdb2compare;

					$sql = "UPDATE $this->itemtable ".
						"SET $this->iitemupdate='".$this->now."', ".
						"$this->iitemtext='".mysql_real_escape_string($text)."', ".
						"$this->iitemname='".mysql_real_escape_string($title)."', ".
						"$this->iitemsitename='".mysql_real_escape_string($itemsitename)."', ".
						"$this->iitemsiteurl='".mysql_real_escape_string($itemsiteurl)."', ".
						"$this->iitemlicense='".mysql_real_escape_string($itemlicense)."' ".
						"WHERE $this->ifeedurl='".mysql_real_escape_string($url)."' " .
						"AND $this->iitemurl='".mysql_real_escape_string($link)."' ";

					if($insertupdatemode=='justupdateitemtext') $sql = "UPDATE $this->itemtable ".
						"SET $this->iitemupdate='".$this->now."', ".
						"$this->iitemtext='".mysql_real_escape_string($text)."' ".
						"WHERE $this->ifeedurl='".mysql_real_escape_string($url)."' " .
						"AND $this->iitemurl='".mysql_real_escape_string($link)."' ";
					$result = $wpdb->query($sql);

					if(class_exists('BDPRSS_SEARCH')){
					  $result = $bdprss_search->markitem4update($hid->id, $md5tocompare);
					  if(!$result) $this->recordError($url, "Updated ID: " . $hid->id . " result: " .$result. " md5debug: " . $md5debug);
					}

				}
				$this->bdprss_prevent_dupe_updates[$hid->id]="Done";
				return FALSE;
			}
			if($insertupdatemode=='justupdateitemtext') return true;
			$dateStamp = date('Y-m-d', $ticks);
			//echo "<br />Title is: " . $title;
			$sql = "INSERT IGNORE INTO $this->itemtable ($this->ifeedurl, $this->iitemurl, $this->iitemname, ".
				"$this->iitemsitename, $this->iitemsiteurl, $this->iitemlicense, ".
				"$this->iitemtext, $this->iitemupdate, $this->iitemtime, $this->iitemdate)".
				" VALUES ('".mysql_real_escape_string($url)."', 
					'".mysql_real_escape_string($link)."', 
					'".mysql_real_escape_string($title)."', 
					'".mysql_real_escape_string($itemsitename)."', 
					'".mysql_real_escape_string($itemsiteurl)."', 
					'".mysql_real_escape_string($itemlicense)."', ".
				"'".mysql_real_escape_string($text)."', '$this->now', '$ticks', '$dateStamp') ";
			$result = $wpdb->query($sql);

//updating the memtable
			$inserted_row_id=mysql_insert_id();
			if ($inserted_row_id>0){
//mem table preop - shall be replaced later by parameter
				$sql = "SELECT $this->cidentifier FROM $this->sitetable ".
					"WHERE $this->mfeedurl='$url'";
			$site_id = $wpdb->get_var($sql);

//mem table update
				$sql = "INSERT INTO $this->mitemtable 
					($this->miid, $this->misiteid, $this->miitemtime, $this->miitemdate)".
					" VALUES ('$inserted_row_id', '$site_id', '$ticks', '$dateStamp') ";
			$result = $wpdb->query($sql);

			} //inserted row id > 0
			$this->bdprss_prevent_dupe_updates[$inserted_row_id]="Done";
			return TRUE;
		}
		
		/* --- modify --- */
		
		function updateTable($tableName, &$valueArray, $identifier, $specialCase=FALSE, $binaryclean=false){
			global $wpdb;
			
			if(!isset($valueArray[$identifier]) || !$valueArray[$identifier])
			{
					$this->recordError('SNARK', 
						"BDPRSS_DB::updateTable() missing identifier ($identifier)".
						"in valueArray for $tableName -- this should never happen");
					return FALSE;
			}
			
			if($specialCase && $tableName==$this->sitetable)
			{
				if(isset($valueArray['csitename']))		unset($valueArray['csitename']);
				if(isset($valueArray['csiteurl']))		unset($valueArray['csiteurl']);
				if(isset($valueArray['cdescription']))	unset($valueArray['cdescription']);
/*new*/				if(isset($valueArray['csitelicense']))	unset($valueArray['csitelicense']);
			}
			
			$sql = "UPDATE $tableName SET ";
			foreach($valueArray as $key => $value)
			{
				if($key==$identifier) continue;
				if($binaryclean){
					$value = mysql_real_escape_string($value);
				}else{
					$value = preg_replace('/"/',	'&quot;', 	$value);
					$value = preg_replace("/'/",	'&#39;', 	$value);
				}
				$sql .= $this->{$key}."='$value', ";
			}
			$sql = preg_replace('/, $/', '', $sql);
			$sql .= " WHERE ".$this->{$identifier}."='".$valueArray[$identifier]."' ";
			$result = $wpdb->query($sql);
			return $result;
		}
		
		function process_new(){
			//echo "we hit process_new";
			global $bdprss_search;
			$pba_search_config['addheap']=500;
		  $pba_search_config['heapmode']='bulk';
		  $pba_search_config['bdprss_getidmode']='notinstatus';
		  $pba_search_config['bdprss_debug_list_id']=0;
		  $pba_search_config['get_ids4heap2add_max_item_updatetimeage']=100000000;
		  $pba_search_config['get_ids4heap2add_min_updatetimeage']=0;
		
		//syntax for calling the search index processing:
		//  	$bdprss_search->add_heap2search_index($pba_search_config['addheap'], $pba_search_config['heapmode'], false, $insertfromtemptable, 
		//  		$bdprss_getidmode, $bdprss_debug_list_id, 
		//  		$get_ids4heap2add_max_item_updatetimeage, $get_ids4heap2add_min_updatetimeage);
		
			$return = $bdprss_search->add_heap2search_index($pba_search_config['addheap'], $pba_search_config['heapmode'], false, true, 
				$pba_search_config['bdprss_getidmode'], $pba_search_config['bdprss_debug_list_id'], 
				$pba_search_config['get_ids4heap2add_max_item_updatetimeage'], 
				$pba_search_config['get_ids4heap2add_min_updatetimeage']);
				
				return $return;
		}
		
		function process_updates(){
			//echo "we hit process_new";
			global $bdprss_search;

			$pba_search_config['addheap']=20;
		  $pba_search_config['heapmode']='bulk';
		  $pba_search_config['bdprss_getidmode']='processupdates';
		  $pba_search_config['bdprss_debug_list_id']=0;
		  $pba_search_config['get_ids4heap2add_max_item_updatetimeage']=100000000;
		  $pba_search_config['get_ids4heap2add_min_updatetimeage']=1800;

			$return = $bdprss_search->add_heap2search_index($pba_search_config['addheap'], $pba_search_config['heapmode'], false, true, 
				$pba_search_config['bdprss_getidmode'], $pba_search_config['bdprss_debug_list_id'], 
				$pba_search_config['get_ids4heap2add_max_item_updatetimeage'], 
				$pba_search_config['get_ids4heap2add_min_updatetimeage']);
			return $return;
		}

		function process_deletes(){
			//echo "we hit process_new";
			global $bdprss_search;

			$pba_search_config['addheap']=10;
		  $pba_search_config['heapmode']='bulk';
		  $pba_search_config['bdprss_getidmode']='processdeletes';
		  $pba_search_config['bdprss_debug_list_id']=0;
		  $pba_search_config['get_ids4heap2add_max_item_updatetimeage']=100000000;
		  $pba_search_config['get_ids4heap2add_min_updatetimeage']=1800;

			$return = $bdprss_search->add_heap2search_index($pba_search_config['addheap'], $pba_search_config['heapmode'], false, true, 
				$pba_search_config['bdprss_getidmode'], $pba_search_config['bdprss_debug_list_id'], 
				$pba_search_config['get_ids4heap2add_max_item_updatetimeage'], 
				$pba_search_config['get_ids4heap2add_min_updatetimeage']);
			return $return;
		}


		function housekeeping_cache(){
			$dummy1="";
			$dummy2="";
			$cachedeletecounter=PBALIB::pba_cache($dummy1, $dummy2, 'housekeeping', '', 'mixed', 86400, 'OK');
			return $cachedeletecounter;
		}

		function update_oldest() 
		{
			global $wpdb;
			// at most we only want to impose the burden of updating one feed on this site user
			
			$sql ="SELECT * FROM $this->sitetable order by $this->cnextpolltime asc limit 1";
			$site = $wpdb->get_row($sql);

//			echo "DEBUG update oldest: the site is: ";
//			print_r($site);


			if(!$site) return;
			if($site->{$this->cnextpolltime} > $this->now) return;

			BDPRSS2::update($site);
		}
		
		function updateAll() 
		{
			global $wpdb;
			
			$sql = 	"SELECT * FROM $this->sitetable order by next_poll_time LIMIT 5";
			$sites = $wpdb->get_results($sql);
			
			if($sites) 
				foreach($sites as $site) 
					BDPRSS2::update($site);
		} 
		
		
		/* --- retrieve --- */
		
		function get_mysql_version() 
		{
			global $wpdb;
			
			$sql = "SELECT version()";
			$result = $wpdb->get_var($sql);
			
			return $result;
		}
		
		function is_in_sitetable($url) 
		{
			global $wpdb;
			
			$sql = 	"SELECT * FROM $this->sitetable ".
				"WHERE $this->cfeedurl='$url' LIMIT 1";
			$result = $wpdb->get_row($sql);
			
			if($result && $result->{$this->cfeedurl} == $url) return TRUE;
			return FALSE;
		}
		
		function count_in_sitetable() 
		{
			global $wpdb;
			
			$sql = 	"SELECT COUNT(*) FROM $this->sitetable ";
			
			$result = $wpdb->get_var($sql);
			
			return $result;
		}
		
		function countErrors($url='') 
		{
			global $wpdb;
			
			$sql = 	"SELECT COUNT(*) FROM $this->errortable";
			if($url) $sql .= " WHERE $this->efeedurl='$url'";
			$result = $wpdb->get_var($sql);
			
			return $result;
		}
		
		function getErrors($url='') 
		{
			global $wpdb;
			
			$sql = 	"SELECT * FROM $this->errortable";
			if($url) $sql .= " WHERE $this->efeedurl='$url'";
			$sql .= " ORDER BY $this->efeedurl, $this->eidentifier";
			$result = $wpdb->get_results($sql);

			return $result;
		}

		function count_in_listtable() 
		{
			global $wpdb;

			$sql = 	"SELECT COUNT(*) FROM $this->listtable ";
			
			$result = $wpdb->get_var($sql);

			return $result;
		}

		function is_in_itemtable($url) 
		{
			global $wpdb;

			$sql = 	"SELECT * FROM $this->itemtable ".
				"WHERE $this->ifeedurl='$url' LIMIT 1";
			$result = $wpdb->get_row($sql);

			if($result && $result->{$this->ifeedurl} == $url) return TRUE;
			return FALSE;
		}
		
		function get_all_lists() 
		{
			global $wpdb;
			$sql = "SELECT * FROM $this->listtable ".
				"ORDER BY $this->lidentifier ";
			$result = $wpdb->get_results($sql);
			return $result;
		}

		function setoptions($optionsarray){
			global $wpdb;
			$sql = "REPLACE INTO $this->optionstable ( name , type , value , notice 
								) VALUES ";
			foreach($optionsarray as $fieldname => $values){
				$sql .= "\n( '".$fieldname."', '".$values['type']."', '".$values['value']."', '".$values['notice']."'), ";
			}
			if(count($optionsarray)>0){
				$sql = preg_replace('/, $/','', $sql);
				$result = $wpdb->query($sql);
			}
			return $result;
		}
		
		function get_all_options() 
		{
			global $wpdb;
			$sql = "SELECT * FROM $this->optionstable ".
				"ORDER BY name ";
			$result = $wpdb->get_results($sql);
			return $result;
		}

		function detect_memtable($table){
			global $wpdb;
			$sql = "SHOW CREATE TABLE " . $table;
			$show = $wpdb->get_row($sql);
			if(stristr($show->{'Create Table'}, 'ENGINE=MEMORY')) return true;
			return false;
		}

		function pbaoption($name){
			global $wpdb;
			$sql = "SELECT * FROM $this->optionstable ".
				"WHERE name = '".$name."' LIMIT 0,1";
			$result = $wpdb->get_row($sql);
			if($result){
				if($result->type == 'string') {
					//special string handling needed to be binary safe?
					return $result->value;
				} elseif($result->type == 'int'){
					return abs(intval($result->value));
				}
			}
			return $result;
		}

		function get_all_pbaoutputs() 
		{
			global $wpdb;
			$sql = "SELECT * FROM $this->pbaoutputtable ".
				"ORDER BY $this->pbaoidentifier ";
			$result = $wpdb->get_results($sql);
			return $result;
		}

		function get_all_sites($ltype='sitealpha') 
		{
			global $wpdb;
			$sql = "SELECT * FROM $this->sitetable ";
			
			if($ltype == 'sitealpha')
				$sql .= "ORDER BY $this->csitename ";
			elseif($ltype == 'siteupdate')
				$sql .= "ORDER BY $this->cupdatetime DESC";
			
			$result = $wpdb->get_results($sql);
			return $result;
		}
		
		function get_site($url) 
		{
			global $wpdb;
			$sql = "SELECT * FROM $this->sitetable WHERE $this->cfeedurl='$url' ";
			$result = $wpdb->get_row($sql);
			return $result;
		}
		
		function get_site_by_id($id) 
		{
			global $wpdb;
			$sql = "SELECT * FROM $this->sitetable WHERE $this->cidentifier='$id' ";
			$result = $wpdb->get_row($sql);
			return $result;
		}

//function will give back a numeric array of feeds when given in a numeric array of ids
		function get_feedlist_by_ids($ids) 
		{
			global $wpdb;
//			echo "Overload: ";
//			print_r($ids);
			$return=false;
			if($ids){
				$sql = "SELECT $this->cfeedurl as feedurl 
					FROM $this->sitetable WHERE $this->cidentifier in (";
				$virgin = true;
				foreach($ids as $id) {
					if($virgin){
						$sql .= " $id";
					}else{
						$sql .= ", $id";
					}
					$virgin = false;
				}
				$sql .= " ) ";
//				print_r($sql);
				$result = $wpdb->get_results($sql);
				foreach($result as $r) {
					$return[]=$r->feedurl;
				}
			}//if ids
			return $return;
		}

		function get_feedurl_from_site_id($id) 
		{
			global $wpdb;
			$sql = "SELECT $this->cfeedurl FROM $this->sitetable WHERE $this->cidentifier='$id' ";
			$result = $wpdb->get_row($sql);
			if(!$result) return FALSE;
			return $result->{$this->cfeedurl};
		}
		
		function get_siteurl_from_site_id($id) 
		{
			global $wpdb;
			$sql = "SELECT $this->csiteurl FROM $this->sitetable WHERE $this->cidentifier='$id' ";
			$result = $wpdb->get_row($sql);
			if(!$result) return FALSE;
			return $result->{$this->csiteurl};
		}
		
		function get_list($list_id) 
		{
			global $wpdb;
			$sql = "SELECT * FROM $this->listtable WHERE $this->lidentifier='$list_id' ";
			$result = $wpdb->get_row($sql);
			return $result;
		}

		function get_pbaoutput_from_page_id($page_id){
			global $wpdb;
			$sql = "SELECT * FROM $this->pbaoutputtable WHERE page2hookin='$page_id' ORDER by identifier ASC LIMIT 0,1";
			$result = $wpdb->get_row($sql);
			return $result;
		}

		function get_pbaoutput($output_id) 
		{
			global $wpdb;
			$sql = "SELECT * FROM $this->pbaoutputtable WHERE identifier='$output_id' ";
			$result = $wpdb->get_row($sql);
			return $result;
		}

		function get_item($feedurl, $itemurl) 
		{
			global $wpdb;
			$sql = 	"SELECT * FROM $this->itemtable ".
				"WHERE $this->ifeedurl='$feedurl' AND $this->iitemurl='$itemurl' ";
			$result = $wpdb->get_row($sql);
			return $result;
		}
		
		function getItemByID($id) 
		{
			global $wpdb;
			$sql = 	"SELECT * FROM $this->itemtable WHERE $this->iidentifier='$id' ";
			$result = $wpdb->get_row($sql);
			return $result;
		}
		

	function getsiteswithupdatetime($maxage=0, $list_id=0){
	
		global $wpdb;

		//check input values
		$list_id=abs(intval($list_id));
		$maxage=abs(intval($maxage)); //0 means filter disabled, age in seconds
		
		//initialisation
		$search_query_conditions_list=" ";
		$search_query_conditions_maxage=" ";
		
		if($list_id>0) $search_query_conditions_list =" AND r1.site_id in (select sites.identifier AS site_id from (" . $this->listtable . " lists join " . $this->sitetable . " sites) where ((concat(_latin1',',lists.url_list,_latin1',') like concat(_utf8'%,',sites.identifier,_utf8',%')) or (lists.list_all = _latin1'Y')) and lists.identifier = '" . $list_id . "')  \n";
		if($maxage>0) $search_query_conditions_maxage =" AND r1.item_time > ( UNIX_TIMESTAMP() - '" . $maxage . "' ) ";
		
		$sql = "SELECT r1.site_id, max( r1.item_time ) AS lastupdate, si.*
			FROM " . $this->mitemtable . " r1, " . $this->sitetable . " si
			WHERE r1.site_id = si.identifier
			" . $search_query_conditions_list . "
			" . $search_query_conditions_maxage . "
			GROUP BY r1.site_id
			ORDER BY si.site_name";

		$result = $wpdb->get_results($sql);
		return $result;
	}


	function getmonthlyarchivedates($itemdate="", $list_id=0){
		global $wpdb;
		
		//protect input vaues
		$list_id=abs(intval($list_id));
    if (!ereg("[0-9][0-9][0-9][0-9]-[0-1][0-9]-[0-3][0-9]", $itemdate)) $itemdate = date('Y-m-d');

		/* let's get all date results for the month according to itemdate
		+ the last hitting date before the month according to itemdate
		+ the next hitting date after the month according to itemdate
		*/

		$firstsecondthismonth=mktime(0, 0, 0, substr($itemdate,5,2), 1, substr($itemdate,0,4));
		$firstsecondnextmonth=mktime(0, 0, 0, ( substr($itemdate,5,2) +1 ), 1, substr($itemdate,0,4));
		if($list_id>0) $search_query_conditions_list="AND r1.site_id in (select sites.identifier AS site_id from (" . $this->listtable . " lists join " . $this->sitetable . " sites) where ((concat(_latin1',',lists.url_list,_latin1',') like concat(_utf8'%,',sites.identifier,_utf8',%')) or (lists.list_all = _latin1'Y')) and lists.identifier = '" . $list_id . "')  \n";

		//let's try to build a quicker query - is it really quicker?
		if($list_id>0) { 
			$search_table_addition_list=" , (select sites.identifier AS site_id from (" . $this->listtable . " lists join " . $this->sitetable . " sites) where ((concat(_latin1',',lists.url_list,_latin1',') like concat(_utf8'%,',sites.identifier,_utf8',%')) or (lists.list_all = _latin1'Y')) and lists.identifier = '" . $list_id . "') s \n";
			$search_query_conditions_list="AND r1.site_id = s.site_id \n";
		}

/* //leave quicksql as comment, maybe it will be needed later, because for timezone conversions it is fine to use item_time instead of item_date
		$quicksql = "(SELECT IFNULL( FROM_UNIXTIME( min( r1.item_time ) , '%Y-%m-%d' ),'0') AS item_date, 'next' AS type
		  FROM " . $this->mitemtable . " r1 " . $search_table_addition_list . " 
		  WHERE r1.item_time >= '".$firstsecondnextmonth."' " . $search_query_conditions_list . " )
		  UNION DISTINCT
		  (SELECT DISTINCT FROM_UNIXTIME( r1.item_time, '%Y-%m-%d' ) AS item_date, 'normal' AS type
			FROM " . $this->mitemtable . " r1  " . $search_table_addition_list . " 
			WHERE r1.item_time >= '".$firstsecondthismonth."' 
			AND r1.item_time < '".$firstsecondnextmonth."' " . $search_query_conditions_list . " )
			UNION DISTINCT
			( SELECT IFNULL( FROM_UNIXTIME( max( r1.item_time ) , '%Y-%m-%d' ),'0') AS item_date, 'last' AS type
		  FROM " . $this->mitemtable . " r1  " . $search_table_addition_list . " 
		  WHERE r1.item_time < '".$firstsecondthismonth."' " . $search_query_conditions_list . ") ";
		$quicksql .= " ORDER BY type, item_date ASC LIMIT 0,40 ";
*/ //just as demo how to use item_time fields instead of item_date

		$quickersql = "(SELECT IFNULL( FROM_UNIXTIME( min( r1.item_time ) , '%Y-%m-%d' ),'0') AS item_date, 'next' AS type
		  FROM " . $this->mitemtable . " r1 " . $search_table_addition_list . " 
		  WHERE r1.item_time >= '".$firstsecondnextmonth."' " . $search_query_conditions_list . " )
		  UNION DISTINCT
		  (SELECT DISTINCT date_format( r1.item_date, '%Y-%m-%d' ) AS item_date, 'normal' AS type
			FROM " . $this->mitemtable . " r1  " . $search_table_addition_list . " 
			WHERE r1.item_date >= FROM_UNIXTIME('".$firstsecondthismonth."','%Y-%m-%d') 
			AND r1.item_date < FROM_UNIXTIME('".$firstsecondnextmonth."','%Y-%m-%d') " . $search_query_conditions_list . " 
			group by r1.item_time )
			UNION DISTINCT
			( SELECT IFNULL( FROM_UNIXTIME( max( r1.item_time ) , '%Y-%m-%d' ),'0') AS item_date, 'last' AS type
		  FROM " . $this->mitemtable . " r1  " . $search_table_addition_list . " 
		  WHERE r1.item_time < '".$firstsecondthismonth."' " . $search_query_conditions_list . " ) ";
		$quickersql .= " ORDER BY type, item_date ASC LIMIT 0,40 ";


		//echo $quickersql . '<br />';
		$result = $wpdb->get_results($quickersql);
		return $result;
		
	}

		
		function get_most_recent_item_time($feedurl) 
		{
			global $wpdb;
			
			if($this->memtablesok == 1){
				//if feedurl is numeric, we treat it as a site_id
				if(abs(intval($feedurl))."" == $feedurl.""){
					$sql = 	"SELECT MAX( $this->miitemtime ) as $this->iitemtime
					FROM $this->mitemtable
					WHERE $this->misiteid = '$feedurl'";
				}else{
				$sql = 	"SELECT MAX( $this->miitemtime ) as $this->iitemtime
					FROM $this->mitemtable
					WHERE $this->misiteid = (
					SELECT $this->cidentifier
					FROM $this->sitetable
					WHERE $this->cfeedurl = '$feedurl' ) ";
				}
			}else{
				$sql = 	"SELECT MAX($this->iitemtime) FROM $this->itemtable ".
					"WHERE $this->ifeedurl='$feedurl' ";
			}
			$result = $wpdb->get_var($sql);
			return $result;
		}

		//new function to query items for output
		//input: object with ids - just to be overloaded, never to be changed, optional parameter order by site name
		//output: object with set of rows
		function getsitenitems(&$id_result, $orderbysitename=false){
			global $wpdb;
			if(!$id_result){
				return false;
			}
      $sql = "SELECT i.identifier as itemid, s.identifier as siteid, i.*, s.* FROM $this->itemtable i, $this->sitetable s ";
      $sql .= " WHERE i." . $this->ifeedurl . " = s." . $this->cfeedurl . " ";
      $virgin = true;
      foreach($id_result as $tr) {
      	if(!$tr->{$this->miid}) continue;
      	if($virgin)
      		$sql .= "AND ( ";
      	else
      		$sql .= "OR";
      	$sql .= " i." . $this->iidentifier . "='" . $tr->{$this->miid} . "' ";
      	$virgin = false;
      }
      if(!$virgin) $sql .= ") ";
      if($orderbysitename){
        $sql .= " ORDER BY s." . $this->msitename . " ASC ";
      }else{
        $sql .= " ORDER BY i." . $this->iitemtime . " DESC ";
      }
      
      $itemset = $wpdb->get_results($sql);
			return $itemset;
		}
		
		/* --- delete --- */
		
		function deleteFeed($rss) 
		{
			global $wpdb, $bdprss_search;
			
			$rss=trim($rss);
			$url = $this->get_feedurl_from_site_id($rss);
			
			if(!$url) return;
			
			$sql = "DELETE FROM $this->sitetable WHERE $this->cfeedurl='$url'";
			$result = $wpdb->query($sql);
			
			$sql = "DELETE FROM $this->itemtable WHERE $this->ifeedurl='$url'";
			$result = $wpdb->query($sql);
			
			$sql = "DELETE FROM $this->errortable WHERE $this->efeedurl='$url'";
			$result = $wpdb->query($sql);

//delete from search tables
			if(class_exists('BDPRSS_SEARCH')){
			  $result = $bdprss_search->markfeed4delete($rss);
			  $this->recordError("Attention", "Deleted Feed ID: " . $rss);
			}
			$sql = "DELETE FROM $this->mitemtable WHERE $this->misiteid='$rss'";
			$result = $wpdb->query($sql);

		}
		
		function deletelist($list) 
		{
			global $wpdb;
			
			$sql = "DELETE FROM $this->listtable WHERE $this->lidentifier='$list'";
			$result = $wpdb->query($sql);
		}
		
		function deletepbaoutput($list) 
		{
			global $wpdb;
			
			$sql = "DELETE FROM $this->pbaoutputtable WHERE $this->pbaoidentifier='$list'";
			$result = $wpdb->query($sql);
		}

		function deleteErrors($url)
		{
			global $wpdb;
			
			$sql = "DELETE FROM $this->errortable ".
				"WHERE $this->efeedurl='$url' ";
			$result = $wpdb->query($sql);
			
			return $result;
		}
		
		function droptable($tablename)
		{
			global $wpdb;
			
			$sql = "DROP TABLE IF EXISTS $tablename";
			$result = $wpdb->query($sql);
			
			return $result;
		}

		function deleteErrorTable()
		{
			global $wpdb;
			
			$sql = "DROP TABLE IF EXISTS $this->errortable";
			$result = $wpdb->query($sql);
			
			$this->create();
		}
		
		function delete_old_items($url) 
		{
		/* delete_old_items($url)
		 */
			global $wpdb, $bdprss_search;
			
			$oldDefined = (int) get_option('bdprss_keep_howlong');
			if(!$oldDefined) return;
			$oldDefined *= 60 * 60 * 24 * 31; // seconds in a month
			$oldDefined = $this->now - $oldDefined;
			
			$sql = "DELETE FROM $this->itemtable ".
				"WHERE $this->ifeedurl='$url' ".
				"AND ($this->iitemtime<'$oldDefined' OR $this->iitemtime='')";
			$result = $wpdb->query($sql);

//delete from search tables
			if(class_exists('BDPRSS_SEARCH')){
			  $result=$this->get_site($url);
			  $result = $bdprss_search->markfeed4delete($result->{$this->cidentifier}, $oldDefined);
			  $this->recordError($url, "Deleted Entries older as $oldDefined from this feed");
			}

//mem table
			$sql = "DELETE FROM $this->mitemtable ".
				"WHERE $this->misiteid in (
					SELECT $this->cidentifier FROM $this->sitetable 
					where $this->cfeedurl  ='$url') ".
				"AND ($this->miitemtime<'$oldDefined' OR $this->miitemtime='')";
			$result = $wpdb->query($sql);

			return $result;
		}
		
		function list_all_tables(){
			global $bdprss_search;
			$tables=$bdprss_search->pbasearch_list_tables();
			$tablenames = array(
				$this->errortable, $this->listtable, $this->itemtable, $this->sitetable,
				$this->mtablestatus, $this->msitetable, $this->mitemtable, $this->optionstable, $this->pbaoutputtable
			);
			foreach($tablenames as $tablename){
				$tables[$tablename]=true;
			}
			return $tables;
		}
		
		function reset() 
		{
		/* reset() -- delete the database tables - 
		 *		god knows why you would do this -- 
		 *		this is the button of death
		 */
			global $wpdb;
			
			$tablenames = $this->list_all_tables();
			//print_r($tablenames);
			$dummy1="";
			$dummy2="";
			$cachedeletecounter=@PBALIB::pba_cache($dummy1, $dummy2, 'clear', '', 'mixed', 180, 'OK');
//echo "<br>Uninstall outcommented in bdp-rssaggregator-db.php";
//exit;

			delete_option('bdprss_update_frequency');
			delete_option('bdprss_keep_howlong');
			delete_option('widget_name_multi');
			foreach($tablenames as $t => $dummy)
			{
				$sql = "DROP TABLE IF EXISTS $t ";
				$result = $wpdb->query($sql);
			}
			return true;
		}
		
		function get_wp_published_pages(){
			global $wpdb, $table_prefix;
			$sql = "SELECT * FROM " . $table_prefix . "posts 
				WHERE post_type = 'page' AND post_status = 'publish'
				ORDER BY ID";
			$result = $wpdb->get_results($sql);
//			return $sql;
			return $result;
		}
		
	} // class
}//if

// Make a single global instance.
if ( !isset($bdprss_db) ) $bdprss_db = new BDPRSS_DB();

?>