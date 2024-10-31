<?php

/*
Plugin Name: Parteibuch RSS Aggregator
Plugin URI: http://www.mein-parteibuch.com/blog/parteibuch-aggregator/
Description: A template based RSS Aggregator with search capability. After first activation <a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php&action=editpbaoutput&pboutput=1&pbstart=1">start configuration here</a>
Version: 0.5.3 dev
Author: Mein Parteibuch
Author URI: http://www.mein-parteibuch.com/

The Parteibuch Aggregator is
based on Bryan Palmers (bryan@ozpolitics.info) 
http://www.ozpolitics.info/blog/?p=87
BDP RSS Aggregator. 

Thank Bryan for the great work, but please don't bother 
him with support requests for the Parteibuch Aggregator.

Bookmarks come from social bookmarks plugin.

Note: tabs set to two spaces
*/

/*  Copyright 2009  Mein Parteibuch  (email : katzen_no_xxx_spam_freund@mein-parteibuch.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* ----- constants ----- */
define ('PBA_PRODUCT',		'Parteibuch Aggregator');	// Doh!
define ('PBA_VERSION',		'0.5.3 dev'); // CHECK: should be the same as above!
define ('PBA_DIRECTORY',	'parteibuch-aggregator');				// base in plugins directory

define ('PBA_CACHE_PATH',dirname(__FILE__)."/pbacache/"); // the directory to be used as disk cache
define ('BDPRSS2_DEBUG',		FALSE);

/* ----- initialisation ----- */

if ( !(phpversion() >= '5.0') )
	die( 'Your server is running PHP version ' . phpversion() . 
	' but the Parteibuch Aggregator Wordpress plugin requires at least 5.0' );

if( !function_exists('mb_internal_encoding') || !function_exists('mb_regex_encoding') )
	die( 'Your installation of PHP does not appear to support multibyte strings. '.
	'This support is needed by the Parteibuch Aggregator plugin. '.
	'You should ask your web-hoster to install it; it is easy to install. '.
	'For more information, refer to <a href="http://www.phpbuilder.com/manual/ref.mbstring.php">'.
	'http://www.phpbuilder.com/manual/ref.mbstring.php</a>.');

//$timeshift=get_option('gmt_offset');
//echo $timeshift;

/* ----- includes ----- */
if( !class_exists('Snoopy') ) require_once(ABSPATH."wp-includes/class-snoopy.php");
if(defined('WPLANG') && file_exists(dirname(__FILE__) . '/pba-defaultparameter_'. substr(WPLANG,0,2) .'.php')){
	include_once(dirname(__FILE__) . '/pba-defaultparameter_'. substr(WPLANG,0,2) .'.php');
}else{
	include_once(dirname(__FILE__) . '/pba-defaultparameter.php');
}

require_once(dirname(__FILE__) . '/bdp-rssaggregator-db.php');
require_once(dirname(__FILE__) . '/bdp-rssfeed.php');

/* ----- main game ----- */
if( !class_exists('BDPRSS2') ) 	// for protection only
{
	/* ----- globals ----- */
	
	// this seems to be the first reliable header, let us test, when it is called, just testing, it seems to be loaded before headers are sent
	//add_action('plugins_loaded', array('BDPRSS2', 'testingheaders'));
	
	// this seems to be the first reliable header, let us test, when it is called, just testing, it seems usually to be loaded before headers are sent
	//add_action('init', array('BDPRSS2', 'testingheaders'));

	add_action('template_redirect', array('BDPRSS2', 'pba_catch_template_redirect'));
	add_action('wp_head', array('BDPRSS2', 'tag'));				// advertising
	add_action('admin_menu', array('BDPRSS2', 'adminMenu'));	// link in the relevant admin menus
	add_action('shutdown', array('BDPRSS2', 'pba_shutdown'));	// routine updating
	add_filter('the_content', array('BDPRSS2', 'replace_page_content'));
	add_filter('rewrite_rules_array', array('BDPRSS2', 'pba_rewrite'));
	if(function_exists('register_deactivation_hook')) register_deactivation_hook( __FILE__, 'pba_uninstaller' );

	function pba_uninstaller(){
		global $bdprss_db;
		if($bdprss_db->pbaoption('delete_alldata') == 'Y'){
			//echo 'Parteibuch Aggregator: "Delete all data" option set';
			$bdprss_db->reset();
		}else{
			true;
			//nothing to do, we are before sending headers, so we can't send a message to the user
			//echo 'Parteibuch Aggregator: "Delete all data" option not set, keeping data for reactivation of the aggregator plugin.';
		}
	}

	//like to have some widgets? here you have:
		include_once(dirname(__FILE__) . '/pba-widgets.php');

	$bdprssTagSet = array(
		'Links'			=> array('a'),
		'Images'		=> array('img'),
		'Paragraphs' 	=> array('p'),
		'Line breaks' 	=> array('br'),
		'Italics' 		=> array('em', 'i'),
		'Underlining' 	=> array('u'),
		'Bolding' 		=> array('b', 'strong'),
		'Spans' 		=> array('span'),
		'Text formating'=> array('abbr', 'cite', 'code', 'dfn', 'kbd', 'object', 'pre', 
			'quote', 'ruby', 'samp', 'strike', 'style', 'sub', 'sup', 'var' ),
		'Tables' 		=> array('table', 'tr', 'th', 'td', 'thead', 'tbody', 'tfoot'),
		'Lists' 		=> array('ol', 'ul', 'dl', 'nl', 'li', 'di', 'dd', 'dt', 'label'),
		'Headings' 		=> array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'),
		'Block quotes' 	=> array('blockquote'),
		'Divisions' 	=> array('div'),
		'Separators' 	=> array('separator', 'hr')
	);
	
	$bdprssdate = "bdprssarchivedate";
	$bdprssCacheItem = "bdprsscacheitem";
	$bdprssList = "bdprsslist";
	
	//globals
	
	$remember_pbapage=false;
	
	class BDPRSS2 
	{
		/* --- hooks --- */
		
//some functions triggered by the hooks


		function pba_rewrite(&$rules){
			
			global $bdprss_db;
			$pbadefault=$bdprss_db->pbaoption('enable_rewriting'); // can we access this already when generating rewrite rules?
			$doit=false;
			if(isset($pbadefault)) {
				if($pbadefault == 'Y' && count($rules) > 5) $doit = true;
			}
			//hook and function is only fired, when rewrite rules are recreated
			//Now the funny part
			$pba_rules = array(
				//tickersucheregex
				'(.+?)?/s/[^/]+(/tickerpage/[0-9]+)?(/feed)?/?$' => 'index.php?&pagename=$matches[1]',
				//kalenderregex
				'([^/]+?)/([kc]alend[ae]r|opml|feedlist|ticker-feed|tickerpage)(/[0-9/-]+)?/?$' => 'index.php?&pagename=$matches[1]',
				//dateregex
				'(.+?)?/?[0-9]{4}-[0-9]{2}-[0-9]{2}(/tickerpage/[0-9]{1,}|/feed|/ticker-feed)?/?$' => 'index.php?&pagename=$matches[1]',
				//cacheregex
				'([^/page0-9]+?)/([0-9]+)/?$' => 'index.php?&pagename=$matches[1]'
			);
			
			//echo "Debug: myrules: " . print_r($pba_rules);
			if($doit) $rules =  array_merge($pba_rules, $rules);
			
			//tricky stuff: the following line will make the rewrite entry in mstatustable be recreated at next page call
			//if we would just recreate the memtablestatus rewriting row now, then the rules change would 
			//not be detected, because wordpress is not finished processing the rule
			
			$bdprss_db->mark_entry_as_old_in_statustable('rewriting');
			
			return $rules;
		}

//see here, MSIE is buggy http://us3.php.net/manual/en/function.ob-gzhandler.php#84493
		function isBuggyIe() {
		  $ua = $_SERVER['HTTP_USER_AGENT'];
		  // quick escape for non-IEs
		  if (0 !== strpos($ua, 'Mozilla/4.0 (compatible; MSIE ')
		      || false !== strpos($ua, 'Opera')) {
		      return false;
		  }
		  // no regex = faaast
		  $version = (float)substr($ua, 30);
		  return ( $version <= 8 );
		}

		function pba_catch_template_redirect(){
			//here some lines to debug rewrite rules
			//global $post;
			//echo "<br><b>We passed pba_catch_template_redirect, post is: " . PBALIB::get_r($post) . "</b>";
			//echo "The rules are: " . str_replace("\n",'<br>',PBALIB::get_r(get_option('rewrite_rules')));
			
			if(is_page()){

				global $remember_pbapage;
				$pba_page_config['getoutputconfigbypageid']=true;
				$pba_page=@PBA::outputwrapper($pba_page_config);
	
				if(isset($pba_page['shutdown']) && $pba_page['shutdown'] === true) {
					BDPRSS2::isBuggyIe() || ob_start("ob_gzhandler");
					echo $pba_page['result']; //xml output goes here
					do_action('shutdown');
					wp_cache_close();
					exit;
				} elseif(isset($pba_page['redirect']) && $pba_page['redirect'] === true){
					//do redirect
					header("Location: " . $pba_page['result']);
					exit;
				} else {
					if($pba_page['result'] !== false){
						$remember_pbapage=$pba_page;
					}
				}
			}
		}
		
		function tag() 
		{
		/* tag() 
		 *  -- called early on to place a comment tag in the page 
		 *	-- I use the tag when people ask for help debugging why the plugin doesn't work for them.
		 */
			global $bdprss_db;
//			echo "\n\t<!-- ".PBA_PRODUCT." " .PBA_VERSION. " -->\n";
//parteibuch deactiveated due to security concerns			if( $bdprss_db->get_mysql_version() < '4.0' )
//parteibuch deactiveated due to security concerns				echo "\t<!-- Warning: Your version of MySQL (" . $bdprss_db->get_mysql_version() . 
//parteibuch deactiveated due to security concerns				") appears old. You should update it. -->\n";
		}
		
		function adminMenu()
		{
		/* adminMenu() -- called when the administration pages are being displayed
		 *	-- this function hooks the RSS Feeds page into the admin menus
		 */
			if (function_exists('add_management_page')) 
				add_management_page('Parteibuch RSS Aggregator', 'RSS Aggregator', 9, 
					dirname(__FILE__).'/bdp-rssadmin.php');
		}
		
		function pba_shutdown() 
		{
		// pba_shutdown() -- called at shutdown - processed the jobs
		//print_r(getrusage());

			global $bdprss_db;

//print_r($bdprss_db->serverstatus);

			ignore_user_abort(true);
			wp_cache_close();
			flush();
			if(!ini_get('safe_mode')) set_time_limit(0);
//			sleep(15); //just for simulating time intensive operations

			if(isset($bdprss_db->serverstatus['job2start']['name'])&& isset($bdprss_db->serverstatus['job2start']['time'])){
				//we will not touch any jobs announced more than 8 seconds ago, the job manager could be already trying to reassign them
				if(time() - $bdprss_db->serverstatus['job2start']['time'] < 8) $pba_job=$bdprss_db->serverstatus['job2start']['name'];
			}
			if(isset($pba_job)
				&& !$bdprss_db->highserverload
				&& $bdprss_db->memtablesok
				&& method_exists($bdprss_db, $pba_job)
			){
				$bdprss_db->jobaction($pba_job, "start");
				$return=$bdprss_db->$pba_job();
			}
			if(isset($pba_job)){
				//echo "killing the job ... ";
				$bdprss_db->jobaction($pba_job, "kill");
				//echo "... done";
			}
		}
		
		//see an example of wp hooks here: http://www.devlounge.net/articles/wordpress-plugin-filters
		function replace_page_content($content = ''){
			global $remember_pbapage;
		  if($remember_pbapage !== false){
				$content .= $remember_pbapage['result'];
		  }
			return $content;
		}
		
		/* ----- utilities ----- */
		function tagalise($key)
		{
			return 'bdprss_xhtml_' . preg_replace("'[\s]*'", '', strtolower($key));
		}
		
		
		/* --- core input functions --- */
		
		function update(&$row){
		// does the grunt work of updating a feed, 
			global $bdprss_db, $pba_loader;
			
			//$bdprss_db->recordError('Debug', 'Im here 1 at update');
			
			// Check we have a row from the site-table
			if(!isset($row) || !$row || !$row->{$bdprss_db->cidentifier} || !$row->{$bdprss_db->cfeedurl})
			{
				$bdprss_db->recordError('Snark', 
					"Snark: update() called without a row from the siteTable (this should never happen)");
				return;
			}
			$now = time();
			$cidentifier = $row->{$bdprss_db->cidentifier};
			$url = $row->{$bdprss_db->cfeedurl};
			$lastupdated = (int) $row->{$bdprss_db->cupdatetime};
			
			// set the next poll time
			$siteArray = array();
			$siteArray['cidentifier'] = $cidentifier;
			$siteArray['clastpolltime'] = $now;
			if($row->{$bdprss_db->cpollingfreqmins})
				$siteArray['cnextpolltime'] = $now + (60 * (int) $row->{$bdprss_db->cpollingfreqmins});
			else
				$siteArray['cnextpolltime'] = $now + (60 * (int) get_option('bdprss_update_frequency'));
			$bdprss_db->updateTable($bdprss_db->sitetable, $siteArray, 'cidentifier');

			// Clear errorBase
			$bdprss_db->deleteErrors($url);
			
			// Get the feed
			$feed = new BDPFeed($url);
			$pfeed = $feed->parse();
			if(!$pfeed)
			{
				$bdprss_db->recordError($url, "Failed to parse $url");
				return;
			}
			
			// extract and save key site information
			if(isset($pfeed['title'])) { $siteArray['csitename'] = mb_substr($pfeed['title'], 0, 250); } else { $siteArray['csitename'] = ""; };
			$siteArray['csiteurl'] =  mb_substr($pfeed['link'], 0, 250);
			if(isset($pfeed['copyright'])) $siteArray['csitelicense'] =  mb_substr($pfeed['copyright'], 0, 250);
			if(isset($pfeed['description'])) {
				$siteArray['cdescription'] = mb_substr($pfeed['description'], 0, 250);
			}elseif(isset($pfeed['tagline'])){
				$siteArray['cdescription'] = mb_substr($pfeed['tagline'], 0, 250);
			}else{
				$siteArray['cdescription'] = "";
			}
			$siteArray['cupdatetime'] = $now;
			$siteArray['csitename'] = mysql_real_escape_string($siteArray['csitename']);
			if(isset($siteArray['csitelicense'])) $siteArray['csitelicense'] = mysql_real_escape_string($siteArray['csitelicense']);
			$siteArray['cdescription'] = mysql_real_escape_string($siteArray['cdescription']);
			$bdprss_db->updateTable($bdprss_db->sitetable, $siteArray, 'cidentifier',
				$row->{$bdprss_db->csitenameoverride}=='Y');

			if(BDPRSS2_DEBUG) $bdprss_db->recordError($url, "I am a virgin site");
			
			// extract and save key item information
			$itemupdateparas['url']=$url;
			$itemupdateparas['itemsitename']=$row->{$bdprss_db->csitename};
			$itemupdateparas['itemsiteurl']=$row->{$bdprss_db->csiteurl};
			$itemupdateparas['itemlicense']=$row->{$bdprss_db->csitelicense};
			$itemupdateparas['csitenameoverride']=($row->{$bdprss_db->csitenameoverride}=='Y');
			$itemupdateparas['cgmtadjust']=$row->{$bdprss_db->cgmtadjust};
			$itemupdateparas['lastupdated']=(int) $row->{$bdprss_db->cupdatetime};
			$itemupdateparas['now']=$now;
			$itemupdateparas['siteArray']=$siteArray;
			$itemupdateparas['counter'] = 1;
			$itemupdateparas['pfeedtitle']="";
			$itemupdateparas['pfeedlink']="";
			$itemupdateparas['pfeedcopyright']="";
			if(isset($pfeed['title'])) $itemupdateparas['pfeedtitle']=$pfeed['title'];
			if(isset($pfeed['link'])) $itemupdateparas['pfeedlink']=$pfeed['link'];
			if(isset($pfeed['copyright'])) $itemupdateparas['pfeedcopyright']=$pfeed['copyright'];

			$insertupdatemode='standard';
			if($row->{$bdprss_db->ccatchtextfromhtml} == 'Y') $insertupdatemode='noupdate';

			foreach ($pfeed['items'] as $item){
				$insertedrow=BDPRSS2::process_parsed_feed_item($item, $itemupdateparas, $insertupdatemode);
				$itemupdateparas['counter']++;
				if($insertedrow && $row->{$bdprss_db->ccatchtextfromhtml} == 'Y'){
						$ccatchhtmlparas = $row->{$bdprss_db->ccatchhtmlparas};
						$ccatchhtmlparas=unserialize($ccatchhtmlparas);

						if(!isset($item['link']) && isset($item['guid'])) $item['link'] = $item['guid'];
						if(!isset($item['link'])) continue;
						$pba_loader_paras['htmlurl']= mb_substr($item['link'], 0, 250);
						$pba_loader_paras['feedurl4booking']=$url;

						//default loader config values
						$pba_loader_paras['fetchparas']['snoopy_max_redirs'] = 0;
						$pba_loader_paras['parseparas']['reg_content_part'][1] = '';
						$pba_loader_paras['parseparas']['reg_content_part'][2] = '';
						$pba_loader_paras['parseparas']['contentpartseparator'] = '';
						$pba_loader_paras['parseparas']['prebase_urls'] = 'N';
						$pba_loader_paras['parseparas']['convert_charset'] = 'N';

						//extract loader config values from unserialized array in site table
						if(isset($ccatchhtmlparas['snoopy_max_redirs'])) $pba_loader_paras['fetchparas']['snoopy_max_redirs'] = $ccatchhtmlparas['snoopy_max_redirs'];
						if(isset($ccatchhtmlparas['reg_content_part'][1])) $pba_loader_paras['parseparas']['reg_content_part'][1] = $ccatchhtmlparas['reg_content_part'][1];
						if(isset($ccatchhtmlparas['reg_content_part'][2])) $pba_loader_paras['parseparas']['reg_content_part'][2] = $ccatchhtmlparas['reg_content_part'][2];
						if(isset($ccatchhtmlparas['contentpartseparator'])) $pba_loader_paras['parseparas']['contentpartseparator'] = $ccatchhtmlparas['contentpartseparator'];
						if(isset($ccatchhtmlparas['prebase_urls']) && $ccatchhtmlparas['prebase_urls'] =='Y') $pba_loader_paras['parseparas']['prebase_urls'] = 'Y';
						if(isset($ccatchhtmlparas['convert_charset']) && $ccatchhtmlparas['convert_charset'] =='Y') $pba_loader_paras['parseparas']['convert_charset'] = 'Y';

						$pba_loader->catch_html($pba_loader_paras);
				}
			}
			$bdprss_db->delete_old_items($url);
		}
		

		function process_parsed_feed_item(&$item, &$itemupdateparas, $insertupdatemode='standard'){
			//parameter overloading just to get more speed
			//function to replace lines in the loop from 373 to 550 in aggregator.php

			global $bdprss_db;

			//input variables: 
			$url = $itemupdateparas['url'];
			$lastupdated = $itemupdateparas['lastupdated'];
			$virgin = ($lastupdated == 1);
			$counter = $itemupdateparas['counter'];
			$now = $itemupdateparas['now'];
			$siteArray = $itemupdateparas['siteArray'];
			$pfeed['title']=$itemupdateparas['pfeedtitle'];
			$pfeed['link']=$itemupdateparas['pfeedlink'];
			$pfeed['copyright']=$itemupdateparas['pfeedcopyright'];
			
			$ticks = 0;
			
			$link = $item['link'];
			if(isset($item['title'])) {
				$title = $item['title'];
			}else{
				$title=false;
			}

//parteibuch fill the new item site name, url and license info
			$itemsitename="";
			$itemsiteurl="";
			$itemlicense="";

			$itemsitename .= $itemupdateparas['itemsitename'];
			$itemsiteurl .= $itemupdateparas['itemsiteurl'];
			$itemlicense .= $itemupdateparas['itemlicense'];

			if(!($itemupdateparas['csitenameoverride'])){
				if(isset($item['dc:creator']) && strlen($item['dc:creator'])>0) {
				  $itemsitename = $item['dc:creator'];
				} elseif(strlen(mb_substr($pfeed['title'], 0, 250))>0){
				  $itemsitename = mb_substr($pfeed['title'], 0, 250);
				}
				if(isset($item['dc:source']) && strlen($item['dc:source'])>0) {
				  $itemsiteurl = $item['dc:source'];
				} elseif(strlen(mb_substr($pfeed['link'], 0, 250))>0){
				  $itemsiteurl = mb_substr($pfeed['link'], 0, 250);
				}
				if(isset($item['dc:rights']) && strlen($item['dc:rights'])>0) {
				  $itemlicense = $item['dc:rights'];
				} elseif(isset($pfeed['copyright']) && strlen(mb_substr($pfeed['copyright'], 0, 250))>0){
				  $itemlicense = mb_substr($pfeed['copyright'], 0, 250);
				}
			}

			if(!$link && isset($item['guid']))
			{
				// A work around for Penny Sharpe's blog - http://pennysharpe.com/redleather/
				$link = $item['guid']; // in RSS guid stands for globally unique identifier 
			}
				
			// some error reporting sequences
			if($link) 
				{ $preError = "<a href='$link'>"; $postError = '</a>'; } 
			if($title && $link)
				$errorLink = ' ('.$preError.$title.$postError.') ';
			elseif($title && !$link)
				$errorLink = ' ('.$title.') ';
			elseif($link && !$title)
				$errorLink = ' ['.$preError.'link'.$postError.'] ';
			else
				$errorLink = '';
				
			if(!$title)
			{
				$bdprss_db->recordError($url, "No title in feed for item $errorLink");
				// lets see if we can make a half a meaningful title from the link
				$title = 'No title';
			}
			
			if(!$link) 
			{
				$bdprss_db->recordError($url, "No link in feed for item $errorLink");
				continue; // a URL link for the item is needed for the database
			}

//Parteibuch Debug
			if(false && $url=='http://www.blog.de/srv/xml/xmlfeed.php?blog=140501&mode=rss2.0') 
			{
				$bdprss_db->recordError($url, "Debug about wrong link for $errorLink");
			}

			
			$link =	mb_substr($link, 0, 250);	// keep it short buddy 
			$title = mb_substr($title, 0, 250);	// keep it short buddy 
				
			// get the itemtext
			if(isset($item['content:encoded'])) 
				$itemtext = $item['content:encoded'];
			elseif(isset($item['description']))
				$itemtext = $item['description'];
			elseif(isset($item['content']))
				$itemtext = $item['content'];
			elseif(isset($item['summary'])) 
				$itemtext = $item['summary'];
			else
				$itemtext = '';
				
			// get the time - this is tricky because many feeds don't provide a timestamp
			$ticks = 0;
			$timeType = 
				array('pubDate', 'dc:date', 'created', 'issued', 'published', 'updated', 'modified');
			$done = FALSE;
			foreach($timeType as $t)
			{
				if(isset($item[$t]))
				{
					$done = TRUE;
					$ticks = strtotime($item[$t]);
					if($ticks > 0) break;
					$ticks = preg_replace("'[- +a-z]*$'si", '', $item[$t]);
					$ticks = strtotime($ticks);
					if($ticks > 0) break;
					$ticks = BDPRSS2::w3cdtf($item[$t]); 
					if($ticks > 0) break;
					$bdprss_db->recordError($url, 'Exact time of item post not correctly encoded: '.
						$t.'['.$item[$t]."] $errorLink");
					$done = FALSE;
				}
				if($ticks < 0)	$ticks = 0;
			}
			if(!$done) $bdprss_db->recordError($url, 
				"No time-stamp in feed for post item $errorLink");
			$rawTicks = $ticks; //only used for error message
			$gmtadjust_seconds = intval(floatval($itemupdateparas['cgmtadjust']) * 3600);
			$ticks += $gmtadjust_seconds;
			
			// make time adjustments -- including for those feeds without timestamps
			$windforward = FALSE;
			$windback = FALSE;
			$gmt_adjust = 0.0;
			if($ticks <= 1000000) 
			{
				if($virgin) 
					$ticks = 0; 
				else 
					$ticks = $now - $counter;
			} 
			else
			{
				if (!$virgin) 
				{
					// reprogram any necessary GMT adjustments for out of sync time stamps
					// we can change $ticks here as ...
					// ... $bdprss_db->update_itemstable() only updates $ticks on inserts
					// ... it will not affect updates
					if($ticks > intval($now+300)) 
					{
						$windback = TRUE;	
						$gmt_adjust = -0.5;
					}
					if($ticks < intval($lastupdated-300)) {
						$windforward = TRUE;
						$ticks = $now - $counter; 
						$gmt_adjust = 0.5;
					}
				}
			}
			
			// one final tweak to prevent zero time and forward time
			if($ticks < 1000000 || $ticks > $now) $ticks = $now - $counter;
			// update/insert item information
			$insertedrow = $bdprss_db->updateItem($url, $title, $itemtext, $link, $ticks, $itemsitename, $itemsiteurl, $itemlicense, $insertupdatemode);
			if ($insertedrow && !$virgin) {
				// it was an item insert (and not an update) for an old feedurl
				// -- let's see if we need to do an automatic adjustmemt to the time!
				if($windback || $windforward) {
					$bdprss_db->recordError($url, "Raw time stamp: " . 
						PBALIB::gettheage($rawTicks). $errorLink);
					$gmt_adjust += floatval($itemupdateparas['cgmtadjust']);
					if($gmt_adjust > 48.0) $gmt_adjust = 48.0;
					if($gmt_adjust < -48.0) $gmt_adjust = -48.0; 
					$siteArray['cgmtadjust'] = $gmt_adjust;
					$bdprss_db->updateTable($bdprss_db->sitetable, $siteArray, 'cidentifier',
						$itemupdateparas['csitenameoverride']);
					$bdprss_db->recordError($url, "New GMT adjustment: $gmt_adjust hours $errorLink");
				}
			}
			return $insertedrow;
		}

		function w3cdtf($dateString=''){
			// w3cdtf() -- modified from parse_w3cdtf() in functions-rss.php in Wordpress!
			//parteibuch: get rid of milliseconds if any
			$dateString=preg_replace('/\.\d{3}/','',$dateString);
			 
			// regex to match wc3dtf
			$pat = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(:(\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))?/i";
			
			if ( preg_match( $pat, $dateString, $match ) ) 
			{
				list( $year, $month, $day, $hours, $minutes, $seconds) = 
					array( intval($match[1]), intval($match[2]), intval($match[3]), 
					intval($match[4]), intval($match[5]), intval($match[6]));
				
				if ( $match[10] != 'Z' ) 
				{
					list( $tz_mod, $tz_hour, $tz_min ) =
						array( $match[8], intval($match[9]), intval($match[10]));
					
					// zero out the variables
					if ( ! $tz_hour ) { $tz_hour = 0; }
					if ( ! $tz_min ) { $tz_min = 0; }
					
					$offset = (($tz_hour*60)+$tz_min)*60;
					
					// is timezone ahead of GMT?  then subtract offset
					if ( $tz_mod == '+' ) { $offset *= -1; }
				}
				else
				{
					$offset = 0;
				}
				
				$secondsSinceEpoch = gmmktime( $hours, $minutes, $seconds, $month, $day, $year) + $offset;
			}
			else
			{
				$secondsSinceEpoch = -1;	// error
			}
			
			return $secondsSinceEpoch;
		}
		
		
		/* --- core output functions --- */
		
		function remove_link_and_cache_links_from_item($itemtext=''){
			//Remove old Cache Link at end of posting
			$itemtext=preg_replace('/\[[^\]]*&gt;Cache&lt;\/a&gt;\]$/','',$itemtext);
			//Remove old Link Link at end of posting
			$itemtext=preg_replace('/\[[^\]]*&gt;Link&lt;\/a&gt;\] ?$/','',$itemtext);
			return $itemtext;
		}
		
		function packageItemText($string, $wordCount=0, $maxWordLength=50, $processTags=FALSE, $tagSet='')
		{
			global $bdp_output;
			// keep acceptable tags
			$string = mb_eregi_replace("\&lt;", 	'<', 	$string);
			$string = mb_eregi_replace("\&gt;", 	'>', 	$string);
			if($processTags && $tagSet)
			{
				$tagSet = preg_split("','", $tagSet, -1, PREG_SPLIT_NO_EMPTY);
				foreach($tagSet as $ts)
				{
					// space out tags so they are are easy to identify
					$string = mb_eregi_replace("<($ts [^>]*)>",	" &lt;\\1&gt;",	$string);
					$string = mb_eregi_replace("<($ts)>",		" &lt;\\1&gt;",	$string);
					$string = mb_eregi_replace("<(/$ts)>",		" &lt;\\1&gt;",	$string);
				}
			}
			// delete unrequired tags
			$string = mb_eregi_replace("<[a-zA-Z]+[^>]*>", 	'',	$string);
			$string = mb_eregi_replace("</[a-zA-Z]+[^>]*>",	'',	$string);
			// restore required tags
			$string = mb_eregi_replace("\&lt;", 	'<', 	$string);
			$string = mb_eregi_replace("\&gt;", 	'>', 	$string);
				
			// count words
			$words = explode(' ', $string);
			$outWords = array();
			$count = count($words);
			
			$inTag = false;
			$HTMLclosure = array();
			$token = false;
			
			if(!$wordCount) $wordCount= -1;	// backward compatibility so that zero = no limit
			
			for($i=0; $i<$count && $wordCount!=0; $i++)
			{
				// trim
				$outWords[$i] = mb_ereg_replace("^\s+",	"",	$words[$i]);
				$outWords[$i] = mb_ereg_replace("\s+$",	"",	$outWords[$i]);
				
				if(!$outWords[$i]) continue;
			
				if($processTags)
				{
					if(mb_ereg('^<', $outWords[$i])) 
					{
						if($inTag) $bdp_output .= "<!-- glitch nested tags? -->\n";
						$inTag = TRUE;
						if(mb_ereg('^<([a-zA-Z]+)', $outWords[$i], $matches))
						{
							//open tag
							$m = mb_strtolower($matches[1]);
							array_push($HTMLclosure, $m); 
							$token = $m;
						}
						if(mb_ereg('^</([a-zA-Z]+).*', $outWords[$i], $matches))
						{
							// close tag
							$m = mb_strtolower($matches[1]);
							$t = array_pop($HTMLclosure);
							if($t && $t!=$m)
								array_push($HTMLclosure, $t);
							$inTag = FALSE;
						}
					}
					
					if($inTag)
					{
						if(mb_ereg('/>', $outWords[$i]))
						{
							// closure
							$m = $token;
							$t = array_pop($HTMLclosure);
							if($t && $t!=$m) array_push($HTMLclosure, $t);
						}
						// quotes in tags must be respected
						$outWords[$i] = mb_eregi_replace('&quot;',	'"', 	$outWords[$i]);
						$outWords[$i] = mb_eregi_replace('&#39;', 	"'",  	$outWords[$i]);
						if(mb_ereg('>', $outWords[$i]))  
						{					
							$inTag = FALSE;
							$token = FALSE;
						}
						continue;
					}
				}
				$len = mb_strlen($outWords[$i]);
				if($maxWordLength && $len > $maxWordLength)
				{ 
					$outWords[$i] =  mb_substr($outWords[$i], 0, $maxWordLength);
					$outWords[$i] .= '~';
				}
				$wordCount--;
			}
			
			$ret =  implode(' ', $outWords);
			
			if($inTag) $ret .= '>';
			
			if(count($words) > count($outWords)) $ret .= ' ...';
			
			if($processTags)
			{
				// close open tags
				while($t = array_pop($HTMLclosure)) $ret .= "</$t>"; 
				
				// tighten up the HTML 
				$ret = mb_eregi_replace(" (</[a-zA-Z]+>)", "\\1", $ret);
				$ret = mb_eregi_replace("([\(\$\[\{]) (<[a-zA-Z]+[^\>]*>)", "\\1\\2", $ret);
				$ret = mb_eregi_replace("&quot; (<[a-zA-Z]+[^\>]*>)", "&quot;\\1", $ret);
				$ret = mb_eregi_replace("&#34; (<[a-zA-Z]+[^\>]*>)", "&#34;\\1", $ret);
				$ret = mb_eregi_replace("&#39; (<[a-zA-Z]+[^\>]*>)", "&#39;\\1", $ret);
				$ret = mb_eregi_replace("&#8216; (<[a-zA-Z]+[^\>]*>)", "&#8216;\\1", $ret);
				$ret = mb_eregi_replace("&lsquo; (<[a-zA-Z]+[^\>]*>)", "&lsquo;\\1", $ret);
				$ret = mb_eregi_replace("&#8220; (<[a-zA-Z]+[^\>]*>)", "&#8220;\\1", $ret);
				$ret = mb_eregi_replace("&ldquo; (<[a-zA-Z]+[^\>]*>)", "&ldquo;\\1", $ret);
				$ret = mb_eregi_replace("(<[a-zA-Z]+[^\>]*>) (<[a-zA-Z]+[^\>]*>)", "\\1\\2", $ret);
			}
			return ($ret);
		}
		
		function codeQuotes($text)
		{
			$text = ereg_replace('&#39;' ,"'", $text);
			$text = eregi_replace('&quot;' ,'"' , $text);
			return $text;
		}

	} // class BDPRSS2 
} // if( !class_exists('BDPRSS2') )


//here cames the parteibuch aggregator stuff

  if(!(isset($pba_loader_direct_call_attempt) && $pba_loader_direct_call_attempt === true)&& !class_exists('PBA_LOADER') ) {
		require_once(dirname(__FILE__) . '/pba-loader.php');
	}
  if(!(isset($bdprsssearchdebug) && $bdprsssearchdebug ===true)&& !class_exists('BDPRSS_SEARCH') ) {
    require_once(dirname(__FILE__) . '/pba-rsssearch.php');
    require_once(dirname(__FILE__) . '/pba_output_function.php');
		global $bdprss_db;
    if($bdprss_db->memtables_were_ok == 0) $result=$bdprss_search->bdprss_create_proc();
  }
  


?>