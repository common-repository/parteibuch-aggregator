<?php

//usage: we have two modes, we can use in direct call to fill up search index
//1. mode feedurl loads a feed from a specific url and books items onto another feed
//http://www.example.com/blog/wp-content/plugins/parteibuch-aggregator/pba_loader.php?mode=feedurl&feedurl=http://www.example.com/blog/2005/05/28/feed/&feedurl4booking=http://www.example.com/blog/feed/&pbaloaderpassword=somethingusefull

//2. mode yearmonth loads the blogposts of the daily feeds from a whole month
// http://www.example.com/blog/wp-content/plugins/parteibuch-aggregator/pba_loader.php?mode=yearmonth&yearmonth=200505&urlpattern=http://www.example.com/blog/---YEAR---/---MONTH---/---DAY---/feed/&feedurl4booking=http://www.example.com/blog/feed/&pbaloaderpassword=somethingusefull


$pba_loader_enable_directcall=false;
$pba_loader_directcall_password="putinsomethingfullinyourwpconfig"; //to override, define PBA_SEARCH_DIRECTCALL_PASSWORD in your wp-config

$pba_loader_directcall_comparepassword="";

$pba_loader_direct_call_attempt=false;
if( !class_exists('BDPRSS2') && !isset($wpdb)){
	$pba_loader_direct_call_attempt=true;
	require_once('../../../wp-config.php');
}

if(defined('PBA_LOADER_ENABLE_DIRECTCALL')) $pba_loader_enable_directcall = PBA_LOADER_ENABLE_DIRECTCALL;
if(defined('PBA_LOADER_DIRECTCALL_PASSWORD')) $pba_loader_directcall_password = PBA_LOADER_DIRECTCALL_PASSWORD;

if(isset($_GET['pbaloaderpassword']))$pba_loader_directcall_comparepassword=stripslashes($_GET['pbaloaderpassword']);

if($pba_loader_direct_call_attempt){
	if(!$pba_loader_enable_directcall || $pba_loader_directcall_comparepassword != $pba_loader_directcall_password){
		die ( __('Exiting pba_rssloader before execution: either pba_loader_enable_directcall not enabled or pba_loader_directcall_password wrong.') );
	}else{
		echo "Debug ... loaded wp-config.php ... ";
	}
}

if( !class_exists('PBA_LOADER') ) {
	class PBA_LOADER
	{	
//config values
	var $pba_loader_config; //declare some useful initialisation variables here

		function PBA_LOADER() {
			//do some initialisation here
			$this->pba_loader_config['sleep']=3;
		}
		
		function loadpage($pba_loader_paras){
			global $bdprss_db, $pba_loader_direct_call_attempt;
			//echo date('r',strtotime('Tue, 14 Apr 2009 23:19:58 +0200'));
			
			if($pba_loader_direct_call_attempt) echo "<br>We are going to load " .
				$pba_loader_paras['url'] . " ... ";
			if($pba_loader_direct_call_attempt) flush();
			
			//add here some usefull functions to load stuff into pba
			//check if url is feed or html
			//convert html to feed
			//check robots.txt
			//load feed
			
			$site4booking=$bdprss_db->get_site($pba_loader_paras['feedurl4booking']);
			
						// Get the feed
			$feed = new BDPFeed($pba_loader_paras['url']);
			$pfeed = $feed->parse();

			$itemupdateparas['url']=$pba_loader_paras['feedurl4booking'];

			$itemupdateparas['itemsitename']=$site4booking->{$bdprss_db->csitename};
			$itemupdateparas['itemsiteurl']=$site4booking->{$bdprss_db->csiteurl};
			$itemupdateparas['itemlicense']=$site4booking->{$bdprss_db->csitelicense}; //here could go a notice about robots.txt
			$itemupdateparas['csitenameoverride']=true;
			$itemupdateparas['cgmtadjust']=0;
			$itemupdateparas['lastupdated']=0;

			$itemupdateparas['now']=time();
			$itemupdateparas['siteArray']=array();
			$itemupdateparas['counter'] = 1;

			$itemupdateparas['pfeedtitle']=$pfeed['title'];
			$itemupdateparas['pfeedlink']=$pfeed['link'];
			$itemupdateparas['pfeedcopyright']=$pfeed['copyright'];

			foreach ($pfeed['items'] as $item){
				print_r($item);
				exit;
				BDPRSS2::process_parsed_feed_item($item, $itemupdateparas);
				$itemupdateparas['counter']++;
			}
			if($pba_loader_direct_call_attempt) echo " loaded ".($itemupdateparas['counter']-1)." entries.";
			if($pba_loader_direct_call_attempt) flush();
		}

		function loadmonth($pba_loader_paras){
			$year=substr($pba_loader_paras['yearmonth'],0,4);
			$month=substr($pba_loader_paras['yearmonth'],4,2);
			$lastday=date('j',mktime(0, 0, 0, $month+1, 0, $year));
			$day=1;
			while($day <= $lastday){
				$search=array('---YEAR---','---MONTH---','---DAY---');
				$replaceday=$day;
				if($replaceday < 10) $replaceday = '0' . $replaceday;
				$replace=array($year, $month, $replaceday);
				$pba_loader_paras['url']=str_replace($search, $replace, $pba_loader_paras['urlpattern']);
				//print_r($pba_loader_paras);
				$this->loadpage($pba_loader_paras);
				$day++;
				sleep($this->pba_loader_config['sleep']);
			}
		}
		function get_sitedef($pba_loader_paras){
			if($pba_loader_paras['feedurl4booking']=='http://www.radio-utopie.de/feeds/index.rss2'){
				$sitedef_paras['fetchparas']['snoopy_max_redirs']=0;
				$sitedef_paras['parseparas']['reg_content_part'][1]='<div[^>]*serendipity_entry_body[^>]*>(.*?)</div>';
				$sitedef_paras['parseparas']['reg_content_part'][2]='<div[^>]*serendipity_entry_extended[^>]*>(.*?)</div>';
				$sitedef_paras['parseparas']['contentpartseparator']=' ';
				$sitedef_paras['parseparas']['prebase_urls']=true;
			}elseif($pba_loader_paras['feedurl4booking']=='http://www.nachdenkseiten.de/?feed=rss2'){
				$sitedef_paras['fetchparas']['snoopy_max_redirs']=0; 
				$sitedef_paras['parseparas']['reg_content_part'][1]='<span[^>]*preview[^>]*>(.*?)</span>';
				$sitedef_paras['parseparas']['reg_content_part'][2]='<span[^>]*id[^>]*></span>(.*?)<div class="hr_wrap">';
				$sitedef_paras['parseparas']['contentpartseparator']=' ';
			}elseif($pba_loader_paras['feedurl4booking']=='http://www.linkezeitung.de/cms/index.php?option=com_rss&feed=RSS2.0&no_html=1'){
				$sitedef_paras['fetchparas']['snoopy_max_redirs']=0; 
				$sitedef_paras['parseparas']['reg_content_part'][1]='(<table[^>]*contentpaneopen[^>]*>.*?</table>).*<table[^>]*contentpaneopen[^>]*>.*?</table>';
				$sitedef_paras['parseparas']['reg_content_part'][2]='<table[^>]*contentpaneopen[^>]*>.*?</table>.*(<table[^>]*contentpaneopen[^>]*>.*?</table>)';
				$sitedef_paras['parseparas']['contentpartseparator']=' ';
				$sitedef_paras['parseparas']['convert_charset']='Y';
			}
			return $sitedef_paras;
		}

		function prebase_urls($content, $siteURL, $options=array()){
			$content = mb_eregi_replace('<a [^>]*href="([^"\'>]*)"[^>]*>', "<a href='\\1'>", $content);
			$content = mb_eregi_replace( "<a href='([^'>]+'[^>]*)>", "<a href='".$siteURL."/\\1>", $content);
			return $content;
		}

		function convert_charset($content){
			$old_charset = BDPFeed::reg_capture("'<?xml[^>]*encoding=\"(.*?)\"[^>]*?>'", $content);
			$new_charset = get_option( 'blog_charset' ); 

			// sort out character encoding
			if($old_charset != $new_charset){
				mb_detect_order('WINDOWS-1252, UTF-8, ISO-8859-1');
				if(!$old_charset) $old_charset = mb_detect_encoding( $content ); 
				//print 'DEBUG: ' . $old_charset;
				$content = @mb_convert_encoding($content, /*to*/$new_charset, /*from*/$old_charset);
			}
			return $content;
		}


		function catch_html($pba_loader_paras){
			global $bdprss_db, $pba_loader_direct_call_attempt;

			$siteURL = mb_eregi_replace("(http://[^/]*).*$", "\\1", $pba_loader_paras['htmlurl']);
			$link = $pba_loader_paras['htmlurl'];
			$pba_loader_paras['htmlurl'] = str_replace('&amp;','&',$pba_loader_paras['htmlurl']);
			if($pba_loader_direct_call_attempt) echo "<br>link is: " . $link;
			$url=$pba_loader_paras['feedurl4booking'];
			if($pba_loader_direct_call_attempt) echo "<br>url is: " . $url;

			$snoopy = new Snoopy();
			$snoopy->agent = PBA_PRODUCT . ' ' . PBA_VERSION;
			$snoopy->read_timeout = 8;	// THINK ABOUT THIS!
			$snoopy->curl_path = FALSE;	// THINK ABOUT THIS!
			$snoopy->maxredirs =$pba_loader_paras['fetchparas']['snoopy_max_redirs'];
			
			if(! @$snoopy->fetch($pba_loader_paras['htmlurl'])){
				$bdprss_db->recordError($url, "Could not open ".$pba_loader_paras['htmlurl']);
				return FALSE;
			}
			$content = $snoopy->results;
			//print_r($snoopy);

			//make this run after a content definition plan valid for the site
			if(isset($pba_loader_paras['parseparas']['convert_charset']) && $pba_loader_paras['parseparas']['convert_charset'] == 'Y') $content= $this->convert_charset($content);

			$contentpart[1] = BDPFeed::reg_capture($pba_loader_paras['parseparas']['reg_content_part'][1], $content);
			$contentpart[2] = BDPFeed::reg_capture($pba_loader_paras['parseparas']['reg_content_part'][2], $content);
			$text = $contentpart[1] . $pba_loader_paras['parseparas']['contentpartseparator'] . $contentpart[2];
			if(isset($pba_loader_paras['parseparas']['prebase_urls']) && $pba_loader_paras['parseparas']['prebase_urls'] === true) $text= $this->prebase_urls($text, $siteURL);
			$text = BDPFeed::rebaseAddresses($text, $siteURL);
			//$bdprss_db->recordError($url, "DEBUG: See our parsed text: " . str_replace(array('<','>'),array('&lt;','&gt;'),$text));
			
			//let's check, if we have already an item for feedurl and itemurl
			$item=$bdprss_db->get_item($url, $link);
			if($item) {
				if($pba_loader_direct_call_attempt) {
					echo "<br>item found. Item is: ";
				}else{
					true; //$bdprss_db->recordError($url, "DEBUG: item found for link: " . $link);
				}
				if($pba_loader_direct_call_attempt) print_r($item);
				if(strlen($text) > strlen($item->text_body)){
					$didnoupdate=$bdprss_db->updateItem($url, '', $text, $link, 0, "", "", "", "justupdateitemtext");
					if(!$didnoupdate) {
						if($pba_loader_direct_call_attempt) {
							echo "<br>We updated link $link booked on url $url with text: " . str_replace(array('<','>'),array('&lt;','&gt;'),$text).".";
						}else{
							$bdprss_db->recordError($url, "DEBUG: We updated link ".$link." booked on url ".$url." with text: " . str_replace(array('<','>'),array('&lt;','&gt;'),$text).".");
						}
					}
				}else{
					if($pba_loader_direct_call_attempt) {
						echo "<br>We did no update, because text in database is not smaller than our parsed text."; 
						}else{
							$bdprss_db->recordError($url, "We did no update, because text in database is not smaller than our parsed text: " . str_replace(array('<','>'),array('&lt;','&gt;'),$text));
						}
				}
			}else{
				if($pba_loader_direct_call_attempt) {
					echo "<br>no item for url feedurl combination found, we need to do an insert. Not implemented yet.";
				}else{
					$bdprss_db->recordError($url, "No item for url feedurl combination found, we need to do an insert. Not implemented yet.");
				}
			}
		}

	} // end class
}// end if !class exists

// Make a singleton global instance.
//fix me later: when we include this file and have no loader job, we should not make an instance of this class
if ( !isset($pba_loader) ) $pba_loader = new PBA_LOADER();

if($pba_loader_enable_directcall && $pba_loader_direct_call_attempt){
	//get some paras like url from GET parameter
	if($_GET['mode'] == 'feedurl'){
		$pba_loader_paras['url']=urldecode($_GET['feedurl']);
		$pba_loader_paras['feedurl4booking']=urldecode($_GET['feedurl4booking']);
		$pba_loader->loadpage($pba_loader_paras);
	}elseif($_GET['mode'] == 'yearmonth'){
		$pba_loader_paras['urlpattern']=urldecode($_GET['urlpattern']);
		$pba_loader_paras['feedurl4booking']=urldecode($_GET['feedurl4booking']);
		echo "<br>Booking items on: " . $pba_loader_paras['feedurl4booking'];
		$pba_loader_paras['yearmonth']=$_GET['yearmonth'];
		$pba_loader->loadmonth($pba_loader_paras);
	}elseif($_GET['mode'] == 'htmlurl'){
		$pba_loader_paras['htmlurl']=urldecode($_GET['htmlurl']);
		$pba_loader_paras['feedurl4booking']=urldecode($_GET['feedurl4booking']);
		$pba_loader_paras=array_merge($pba_loader->get_sitedef($pba_loader_paras),$pba_loader_paras);
		$pba_loader->catch_html($pba_loader_paras);
	}
}


?>