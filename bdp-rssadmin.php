<?php 

/* ----- Access validation ----- */

if ($user_level < 6) die ( __('Cheatin&#8217; uh?') );

if(!defined('PBA_DIRECTORY')) {
	echo "<h1>Parteibuch Aggregator</h1><br><br>Please <a href='plugins.php'>activate</a> the Parteibuch RSS Aggregator plugin before trying to use it";
	return false;
}

/* ----- Admin functions and initialisation ----- */

function bdpSetFeed($sitelist='') 
{
	global $bdprss_db;

	$sitelist = trim($sitelist);
	if($sitelist == '') 
		return	'<div class="wrap"><h3>Warning</h3>No RSS site was specified\n</div>';
			
	$sites = preg_split("'[ \n\t\r]+'", $sitelist, -1, PREG_SPLIT_NO_EMPTY); // space separated list
			
	// we start at two so that $bdprss_db->clastpolltime != $bdprss_db->cupdatetime
	$count = 2;	 
			
	// add to the site list
	foreach($sites as $s) 
	{
		$s = trim($s);
				
		if(!$s) continue;
				
		if(!$bdprss_db->is_in_sitetable($s)) 
		{ 
			$result = $bdprss_db->insert_in_sitetable($s, $count);
			$count++;
		} 
	}
	return FALSE;
}

function bdpSetFrequency($freq='') 
{
	if(!$freq || !is_int($freq) || $freq<1 || $freq>999) 
		$freq = 60;	// 60 minutes is the default
	update_option('bdprss_update_frequency', $freq);
}

function bdpDisplayCode($text)
{
//	Stored in DB	Displayed		Note
//	-----------------------------------------------------------
//	>				&gt;			close tag
//  &gt;			&amp;gt;		right angle bracket
//	&quot;			&quot;			double quotation mark --> "
//	&nbsp;			&amp;nbsp;		non breaking space

	$text = mb_ereg_replace('&#39;', "'", $text);
	$text = mb_ereg_replace('&quot;', '"', $text);

	$text = mb_ereg_replace('&', '&amp;', $text);

	$text = mb_ereg_replace("'", '&#39;', $text);
	$text = mb_ereg_replace('"', '&quot;', $text);
	$text = mb_ereg_replace('<', '&lt;', $text);
	$text = mb_ereg_replace('>', '&gt;', $text);

	return ($text);
}

function nomagicquotes($text=""){
	if(get_magic_quotes_gpc()) {
		$text = stripslashes($text);
	}
	return $text;
}

global $bdprss_db;

if(!(int) get_option('bdprss_update_frequency')) bdpSetFrequency();	// initialise polling frequency
if(!(int) get_option('bdprss_keep_howlong')) {
			update_option('bdprss_keep_howlong', 0);	// storage in months, 0 = forever
}


/* ----- Capture and process form variables ----- */


if( isset($_POST['bdprss_add_feed_button']) )
{
	$r = bdpSetFeed($_POST['bdprss_new_feed_name']);
	if($r) echo $r;
}

if( isset($_POST['bdprss_change_frequency_button']) )
{
	bdpSetFrequency((int) $_POST['bdprss_new_frequency']);

	$howlong = (int) $_POST['bdprss_keep_howlong'];
	if($howlong <= 0 || $howlong > 999) $howlong = 0;	// months, zero default means forever
	update_option('bdprss_keep_howlong', 		$howlong);
}

if( isset($_POST['pba_edit_options_button']) ){
	$bdprss_db->check_cache($bdprss_db->serverstatus);
	$bdprss_db->check_rewriting($bdprss_db->serverstatus);
	$optionsarray = array();
	if(isset($_POST['pba_options_enable_caching'])){

		if($_POST['pba_options_enable_caching'] == 'Y' ) {
			$optionsarray['enable_caching']=array('type' => 'string', 'value' => 'Y', 'notice' => 'user request');
		}elseif($_POST['pba_options_enable_caching'] == 'N' ){
			$optionsarray['enable_caching']=array('type' => 'string', 'value' => 'N', 'notice' => 'user request');
		}elseif($_POST['pba_options_enable_caching'] == 'auto' ){
			$optionsarray['enable_caching']=array('type' => 'string', 'value' => 'auto', 'notice' => 'user request');
		}

		if($_POST['pba_options_link_rewriting'] == 'Y' ) {
			$optionsarray['enable_rewriting']=array('type' => 'string', 'value' => 'Y', 'notice' => 'user request');
		}elseif($_POST['pba_options_link_rewriting'] == 'N' ){
			$optionsarray['enable_rewriting']=array('type' => 'string', 'value' => 'N', 'notice' => 'user request');
		}elseif($_POST['pba_options_link_rewriting'] == 'auto' ){
			$optionsarray['enable_rewriting']=array('type' => 'string', 'value' => 'auto', 'notice' => 'user request');
		}

		if(isset($_POST['pba_full_cache_time'])) {
			$optionsarray['full_cache_time']=array('type' => 'int', 'value' => abs(intval($_POST['pba_full_cache_time'])), 'notice' => 'user request');
		}
		if(isset($_POST['pba_kalenderquery_cache_time'])) {
			$optionsarray['kalenderquery_cache_time']=array('type' => 'int', 'value' => abs(intval($_POST['pba_kalenderquery_cache_time'])), 'notice' => 'user request');
		}
		if(isset($_POST['pba_feedlistquery_cache_time'])) {
			$optionsarray['feedlistquery_cache_time']=array('type' => 'int', 'value' => abs(intval($_POST['pba_feedlistquery_cache_time'])), 'notice' => 'user request');
		}

		if(isset($_POST['pba_options_enable_memtables']) && isset($_POST['old_pba_options_enable_memtables'])) {
		
			if($_POST['pba_options_enable_memtables'] != $_POST['old_pba_options_enable_memtables']
			&& $_POST['pba_options_enable_memtables'] != 'auto'){

				$memtables=array(
					$bdprss_db->mtablestatus => $bdprss_db->detect_memtable($bdprss_db->mtablestatus),
					$bdprss_db->mitemtable => $bdprss_db->detect_memtable($bdprss_db->mitemtable)
				);
				$tables_need_recreation = false;
				foreach($memtables as $memtablename => $isreallymemtable){
					if($_POST['pba_options_enable_memtables'] == 'Y' && !$isreallymemtable){
						$bdprss_db->droptable($memtablename);
						$try_memtable_creation=true; //try recreate as memtable, 
						$tables_need_recreation = true;
					}elseif($_POST['pba_options_enable_memtables'] == 'N' && $isreallymemtable){
						$bdprss_db->droptable($memtablename);
						$try_memtable_creation=false; //recreate as plaintable, 
						$tables_need_recreation = true;
					}
				}
				if($tables_need_recreation){
					$bdprss_db->create($try_memtable_creation);
					$bdprss_db->prefill_memtables();
				}
			}
			if($_POST['pba_options_enable_memtables'] == 'Y' ) {
				$optionsarray['enable_memtables']=array('type' => 'string', 'value' => 'Y', 'notice' => 'user request');
			}elseif($_POST['pba_options_enable_memtables'] == 'N' ){
				$optionsarray['enable_memtables']=array('type' => 'string', 'value' => 'N', 'notice' => 'user request');
			}elseif($_POST['pba_options_enable_memtables'] == 'auto' ){
				$optionsarray['enable_memtables']=array('type' => 'string', 'value' => 'auto', 'notice' => 'user request');
			}
		}

		if(isset($_POST['pba_options_enable_loaddetection'])) {
			if($_POST['pba_options_enable_loaddetection'] == 'Y' ) {
				$optionsarray['enable_loaddetection']=array('type' => 'string', 'value' => 'Y', 'notice' => 'user request');
			}elseif($_POST['pba_options_enable_loaddetection'] == 'N' ){
				$optionsarray['enable_loaddetection']=array('type' => 'string', 'value' => 'N', 'notice' => 'user request');
			}elseif($_POST['pba_options_enable_loaddetection'] == 'auto' ){
				$optionsarray['enable_loaddetection']=array('type' => 'string', 'value' => 'auto', 'notice' => 'user request');
			}
		}

		if(isset($_POST['pba_highloadthreshold'])) {
			$pba_checkedhighloadthreshold=abs(intval($_POST['pba_highloadthreshold']));
			if($pba_checkedhighloadthreshold > 0) $optionsarray['highloadthreshold']=array('type' => 'int', 'value' => $pba_checkedhighloadthreshold, 'notice' => 'user request');
		}

		if(isset($_POST['pba_clear_cache_now'])) {
			$dummy1="";
			$dummy2="";
			$cachedeletecounter=PBALIB::pba_cache($dummy1, $dummy2, 'clear', '', 'mixed', 180, 'OK');
		}

		$optionsarray['delete_alldata']=array('type' => 'string', 'value' => 'N', 'notice' => 'user request');
		if(isset($_POST['pba_delete_alldata'])) {
			if($_POST['pba_delete_alldata'] == 'Y' ) {
				$optionsarray['delete_alldata']=array('type' => 'string', 'value' => 'Y', 'notice' => 'user request');
			}
		}

	}
	$success=$bdprss_db->setoptions($optionsarray);
	$bdprss_db->check_options($bdprss_db->serverstatus);
	$bdprss_db->check_cache($bdprss_db->serverstatus);
}


if( isset($_POST['bdprss_poll_all_button']) )
{
	$bdprss_db->updateAll();
}

if( isset($_POST['bdprss_edit_site_button']) )
{
	$siteArray = array();

	$siteArray['csitenameoverride'] = 'N';
	if(isset($_POST['bdprss_csitenameoverride']) 
		&& $_POST['bdprss_csitenameoverride'] == 'Y'
		) $siteArray['csitenameoverride'] = 'Y';

	$siteArray['cidentifier'] = $_POST['bdprss_cidentifier'];
	$siteArray['csitename'] = BDPFeed::title_recode($_POST['bdprss_csitename']);
	$siteArray['csiteurl'] = BDPFeed::title_recode($_POST['bdprss_csiteurl']);
	$siteArray['cdescription'] = mb_substr(BDPFeed::title_recode($_POST['bdprss_cdescription']), 0, 250);
	$siteArray['csitelicense'] = mb_substr(BDPFeed::title_recode($_POST['bdprss_csitelicense']), 0, 250);
	$siteArray['cgmtadjust'] = floatval($_POST['bdprss_cgmtadjust']);

	$cpollingfreqmins = intval($_POST['bdprss_cpollingfreqmins']);
	if($cpollingfreqmins < 0 || $cpollingfreqmins > 1000000) $cpollingfreqmins = 0;
	$siteArray['cpollingfreqmins'] =  $cpollingfreqmins ;

	if(isset($_POST['pba_cnewnextpolltime']) 
		&& $_POST['pba_cnewnextpolltime'] != "") {
		$siteArray['cnextpolltime'] =  abs(intval($_POST['pba_cnewnextpolltime']));
		if( $siteArray['cnextpolltime'] > 2147483647 ) unset($siteArray['cnextpolltime']);
	}

	//do we want to change he feed url?
	if(isset($_POST['bdprss_cnewfeedurl']) 
		&& isset($_POST['bdprss_coldfeedurl'])
		&& $_POST['bdprss_cnewfeedurl'] != ""
		&& $_POST['bdprss_cnewfeedurl'] != $_POST['bdprss_coldfeedurl']
	) {
		$pba_change_feed_to=$_POST['bdprss_cnewfeedurl'];
		$pba_do_feedurlupdate=$bdprss_db->update_feedurl($siteArray['cidentifier'], $pba_change_feed_to);
		if($pba_do_feedurlupdate) $siteArray['cfeedurl'] = $pba_change_feed_to;
	}

	$siteArray['ccatchtextfromhtml'] = 'N';
	if(isset($_POST['pba_ccatchtextfromhtml']) 
		&& $_POST['pba_ccatchtextfromhtml'] == 'Y'
		) $siteArray['ccatchtextfromhtml'] = 'Y';

//	$_POST['pba_loader_snoopy_max_redirs'];
//	$_POST['pba_loader_reg_content_part_1'];
//	$_POST['pba_loader_reg_content_part_2'];
//	$_POST['pba_loader_contentpartseparator'];

$ccatchhtmlparas['fetchparas']['snoopy_max_redirs'] = $_POST['pba_loader_snoopy_max_redirs'];
$ccatchhtmlparas['parseparas']['reg_content_part'][1] = nomagicquotes($_POST['pba_loader_reg_content_part_1']);
$ccatchhtmlparas['parseparas']['reg_content_part'][2] = nomagicquotes($_POST['pba_loader_reg_content_part_2']);
$ccatchhtmlparas['parseparas']['contentpartseparator'] = nomagicquotes($_POST['pba_loader_contentpartseparator']);

	$ccatchhtmlparas['parseparas']['prebase_urls'] = 'N';
	if(isset($_POST['pba_loader_prebase_urls']) 
		&& $_POST['pba_loader_prebase_urls'] == 'Y'
		) $ccatchhtmlparas['parseparas']['prebase_urls'] = 'Y';

	$ccatchhtmlparas['parseparas']['convert_charset'] = 'N';
	if(isset($_POST['pba_loader_convert_charset']) 
		&& $_POST['pba_loader_convert_charset'] == 'Y'
		) $ccatchhtmlparas['parseparas']['convert_charset'] = 'Y';

$siteArray['ccatchhtmlparas']=serialize(array_merge($ccatchhtmlparas['fetchparas'], $ccatchhtmlparas['parseparas']));

$siteArray['csitecomment']= nomagicquotes($_POST['pba_csitecomment']);

	$bdprss_db->updateTable($bdprss_db->sitetable, $siteArray, 'cidentifier', false, true);
	
	
	//add or remove this site to or from lists
	$listresult = $bdprss_db->get_all_lists();
	if($listresult) {
		foreach($listresult as $list) {
			$listArray = array();
			$listid = $list->{$bdprss_db->lidentifier};
			$lurllist= ',' . $list->{$bdprss_db->lurls} . ',';
			if(isset($_POST['pba_site_included_in_list_'.$listid])
				&& $_POST['pba_site_included_in_list_'.$listid] == 'Y'
			){
				//make sure this site is included in the list
				if(!strstr($lurllist, ','.$siteArray['cidentifier'].',')){
					//adding this site to this list needed
					$listArray['lidentifier'] = $listid;
					$listArray['lurls'] = trim($list->{$bdprss_db->lurls} . ',' . $siteArray['cidentifier'],',');
				}
			}else{
				//make sure this site is not included in the list
				if(strstr($lurllist, ','.$siteArray['cidentifier'].',')){
					//removing this site from this list needed
					$listArray['lidentifier'] = $listid;
					$listArray['lurls'] = trim(str_replace(','.$siteArray['cidentifier'].',',',',$lurllist),',');
				}
			} // end if(isset($_POST['pba_site_included
			if(isset($listArray['lidentifier'])) $bdprss_db->updateTable($bdprss_db->listtable, $listArray, 'lidentifier');

		} //  end foreach($listresult as $list)
	} // end if($listresult) 
} // end if( isset($_POST['bdprss_edit_site_button']) )

if( isset($_POST['bdprss_edit_list_button']) )
{
	$listArray = array();
	
	$listArray['lidentifier'] = $_POST['bdprss_lidentifier'];
	$listArray['lname'] = htmlspecialchars($_POST['bdprss_lname']);

	// booleans
	$llistall = 'N';
	if(isset($_POST['bdprss_llistall'])) $llistall = $_POST['bdprss_llistall'];
	if($llistall != 'Y') $llistall = 'N';
	$listArray['llistall'] = $llistall;

	$lurls = '';
	$result = $bdprss_db->get_all_sites();
	if($result) 
	{
		$subsequent = false;
		foreach($result as $r) 
		{
			$id = $r->{$bdprss_db->cidentifier};
			$url = $r->{$bdprss_db->cfeedurl};
			
			$feed = 'bdprss_feed_' . $id;
			if( isset($_POST[$feed]) ) 
			{
				if($subsequent) $lurls .= ',';
				$subsequent = true;
				$lurls .= $id;
			}
		}
	}
	$listArray['lurls'] = $lurls;

	$bdprss_db->updateTable($bdprss_db->listtable, $listArray, 'lidentifier');
}

if( isset($_POST['pba_edit_output_button']) )
{
	//Format: $pbaoutputArray['bdprssdb: this->table_column_namedefinition']= $_POST['name of field in form'];
	
	$pbaoutputArray = array();
	$pbaoutputArray['pbaoidentifier'] = $_POST['pbaoidentifier'];
	$pbaoutputArray['pbaoname'] = htmlspecialchars($_POST['pbaoname']);
	$pbaoutputArray['pbaopage2hookin'] = abs(intval($_POST['pbaopage2hookin']));

//item formatting
	$pbaoutputArray['pbaodefaultlist'] = $_POST['pbaodefaultlist'];
	$pbaoutputArray['pbaomaxitems'] = $_POST['pbaomaxitems'];
	$pbaoutputArray['pbaoformattype'] = $_POST['pbaoformattype'];

	$pbaoutputArray['pbaotemplate_ticker'] = $_POST['pbaotemplate_ticker'];
	
	//if set to N, php will throw out a notice, because $_POST['pbaoappend_extra_link'] does not exist
	$pbaoutputArray['pbaoappend_extra_link'] = $_POST['pbaoappend_extra_link'];
	if($pbaoutputArray['pbaoappend_extra_link'] != 'Y') $pbaoutputArray['pbaoappend_extra_link'] = 'N';
	
	$pbaoutputArray['pbaoappend_cache_link'] = 'Y';
	if(!isset($_POST['pbaoappend_cache_link']) || $pbaoutputArray['pbaoappend_cache_link'] != 'Y') $pbaoutputArray['pbaoappend_cache_link'] = 'N';

	$pbaoutputArray['pbaoadd_social_bookmarks'] = 'Y';
	if(!isset($_POST['pbaoadd_social_bookmarks']) || $pbaoutputArray['pbaoadd_social_bookmarks'] != 'Y') $pbaoutputArray['pbaoadd_social_bookmarks'] = 'N';

	$pbaoutputArray['pbaosidebarwidget'] = $_POST['pbaosidebarwidget'];

	$pbaoutputArray['pbaomaxlength'] = abs(intval($_POST['pbaomaxlength']));
	$pbaoutputArray['pbaomaxwordlength'] = abs(intval($_POST['pbaomaxwordlength']));
	$pbaoutputArray['pbaoitem_date_format'] = $_POST['pbaoitem_date_format'];

	// $bdprssTagSet is declared in bdp-rss-aggregator before including this file
	$pbaoallowablexhtmltags = '';
	$first = TRUE;
	foreach($bdprssTagSet as $key => $value)
	{
		$tag = BDPRSS2::tagalise($key);
		if(isset($_POST[$tag]))
		{
			if(!$first) $pbaoallowablexhtmltags .= ',';
			$pbaoallowablexhtmltags .= $key;
			$first = FALSE;
		}
	}
	$pbaoutputArray['pbaoallowablexhtmltags'] = $pbaoallowablexhtmltags;

//cache
	$pbaoutputArray['pbaoiscachable'] = $_POST['pbaoiscachable'];
	$pbaoutputArray['otemplate_cache'] = $_POST['pba_otemplate_cache'];
	$pbaoutputArray['pbaocacheviewpage'] = $_POST['pbaocacheviewpage'];

//feed
	$pbaoutputArray['pba_channel_title'] = $_POST['pba_channel_title'];
	$pbaoutputArray['pba_channel_link'] = $_POST['pba_channel_link'];
	$pbaoutputArray['pba_channel_description'] = $_POST['pba_channel_description'];
	$pbaoutputArray['pba_channel_language'] = $_POST['pba_channel_language'];
	$pbaoutputArray['pba_channel_copyright'] = $_POST['pba_channel_copyright'];

//kalender
	$pbaoutputArray['otemplate_kalender'] = $_POST['pba_otemplate_kalender'];
	$pbaoutputArray['oarchive_date_format'] = $_POST['pba_oarchive_date_format'];
	$pbaoutputArray['okalendermonthslist'] = $_POST['pba_okalendermonthslist'];
	$pbaoutputArray['okalenderboxtablecaption'] = $_POST['pba_okalenderboxtablecaption'];
	$pbaoutputArray['okalender_last'] = $_POST['pba_okalender_last'];
	$pbaoutputArray['okalender_next'] = $_POST['pba_okalender_next'];
	$pbaoutputArray['okalenderboxdaysofweeklist'] = $_POST['pba_okalenderboxdaysofweeklist'];

	$pbaoutputArray['pbao_superparameter'] = $_POST['pbao_superparameter'];

	$bdprss_db->updateTable($bdprss_db->pbaoutputtable, $pbaoutputArray, 'pbaoidentifier');
}

/* ----- Capture and process calling arguments ----- */

$argumentSet = array('action', 'rss', 'list', 'pboutput');
for ($i = 0;  $i < count($argumentSet);  $i++) 
{
	$variable = $argumentSet[$i];
	if (!isset($$variable)) // don't override if already set
	{
		if (!empty($_POST[$variable]))
			$$variable = $_POST[$variable];
		elseif (!empty($_GET[$variable]))
			$$variable = $_GET[$variable];
		else
			$$variable = '';
		if($variable == 'action') $pbaction = $$variable;
	}elseif($variable == 'action'){
		if (!empty($_POST[$variable]))
			$pbaction = $_POST[$variable];
		elseif (!empty($_GET[$variable]))
			$pbaction = $_GET[$variable];
		else
			$pbaction = '';
	}
}

$editpbaoutput=false;
$editlist = false;
$editfeed = false;
$errorlist = false;
$options = false;
$status = false;

switch($pbaction) 
{
	case 'update':
		$r = $bdprss_db->get_site_by_id($rss);
		if($r) BDPRSS2::update($r);
	break;

	case 'delete':
		$bdprss_db->deleteFeed($rss);
	break;

	case 'createlist':
		if($list){
			$list = abs(intval($list));
			$list = $bdprss_db->createlist($list);
		}else{
			$list = $bdprss_db->createlist();
		}
		$editlist = true;
		// no break - flows into editlist

	case 'editlist':
		$editlist = true;
	break;

	case 'createpbaoutput':
		if($pboutput){
			$pboutput = abs(intval($pboutput));
			$pboutput = $bdprss_db->createpbaoutput($pboutput);
		}else{
			$pboutput = $bdprss_db->createpbaoutput();
		}
		$editpbaoutput = true;
		// no break - flows into editpbaoutput

	case 'editpbaoutput':
		$editpbaoutput = true;
	break;

	case 'errorlist':
		$errorlist = true;
	break;

	case 'errordelete':
		$bdprss_db->deleteErrorTable($rss);
	break;

	case 'editfeed':
		$editfeed = true;
	break;

	case 'dellist':
		if($list) $bdprss_db->deletelist($list);
	break;

	case 'delpbaoutput':
		if($pboutput) $bdprss_db->deletepbaoutput($pboutput);
	break;

	case 'options':
		$options = true;
	break;

	case 'status':
		$status = true;
	break;
}

$selfreference = get_option('siteurl') . '/wp-admin/edit.php?page=' .PBA_DIRECTORY. '/bdp-rssadmin.php';

/* ----- Drop in the appropriate administration page ----- */

echo "<div class='wrap'>\n";

if ($editpbaoutput && $pboutput) 
{
	include (dirname(__FILE__)."/pba-admin-output.php");
} 
elseif ($editlist && $list) 
{
	include (dirname(__FILE__)."/bdp-rssadmin-edit.php");
} 
elseif ($editfeed && $rss) 
{
	include (dirname(__FILE__)."/bdp-rssadmin-sno.php");
} 
elseif ($errorlist) 
{
	include (dirname(__FILE__)."/bdp-rssadmin-error.php");
}
elseif ($options) 
{
	include (dirname(__FILE__)."/pba-admin-options.php");
}
elseif ($status) 
{
	include (dirname(__FILE__)."/pba-admin-status.php");
}
else
	include (dirname(__FILE__)."/bdp-rssadmin-general.php");

echo '<p align="center">&nbsp;<br />This page was brought to you by the<br />'.
	'<a href="http://www.mein-parteibuch.com/blog/parteibuch-aggregator/">'.
	'<strong>' .PBA_PRODUCT. ' version ' .PBA_VERSION. "</strong></a></p>\n";

echo "</div>\n";
?>