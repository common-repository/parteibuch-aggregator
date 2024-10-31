<?php
class PBA extends BDPRSS2
{

  // Redefine the output function by wrapping the old output function
  function outputwrapper($pba_calling_paras=1){
		//when called with a single number, we understand output id is meant
    $numargs = func_num_args();
    if(!is_array($pba_calling_paras) && $numargs > 0) {
      if(preg_match('/^([0-9]+)$/', $pba_calling_paras)) {
      	$pba_calling_paras_tmp=$pba_calling_paras;
      	$pba_calling_paras=array();
				$pba_calling_paras['outputid']=$pba_calling_paras_tmp;
      }
    }

		$pbaout['debug'] .= "\n<br>Debug: using new outputwrapper from class PBA extending BDPRSS2";
		$pbaout['profiler']['Start of outputwrapper']=microtime();

		//global objects
		global $bdprss_db, $bdprss_search;

		//quick - time is money - to answer questions: 
		//do we want the plugin to show anything at all?
		//do we need to shutdown wordpress after outputwrapper was called?
		//what to do - we expect parameter array containing output id, but just have $post
		//can we cache parameter bootstrap?
		
		$resultparas=PBALIB::bootstrap_parameters($pba_calling_paras); 

		if(!is_array($resultparas)){ //fix sidebar and box displaying and detection of correct output page later?
			$pbaout['shutdown']=false;
			$pbaout['result']=false;
			return $pbaout;
		}

		//check for redirect - fix me later, this would be much better placed in the library and use the resultparas calculated before
		if($resultparas['short_cache_link']){
				$redirect_debug=false;
				$exp2match="'" . get_option('home') . "/([a-zA-Z0-9-]+)/(index.php)?\?searchphrase=([a-zA-Z0-9-+%_]+)'s";
				if($redirect_debug) echo "<br/>exp2match: " . $exp2match;
				$request="http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				if($redirect_debug) echo "<br>request_uri: " . $request;
				preg_match($exp2match, $request, $matches);
				if($redirect_debug) echo "<br>matches: " . PBALIB::get_r($matches);
				if(isset($matches[1]) && isset($matches[3])){
				  $searchstring=str_replace("_","%5F",$matches[3]);
				  $searchstring=str_replace("+","_",$searchstring);
				  $searchstring=urlencode(str_replace("/","_",urldecode($searchstring)));
				  $redirect = get_option('home') . '/' . $matches[1] . "/s/" . $searchstring . "/";
					if($redirect_debug) echo "<br/>redirect: " . $redirect;
					//header("Location: " . $redirect);
					//exit;
					$pbaout['redirect']=true;
					$pbaout['result']=$redirect;
					return $pbaout;
				}
		} // end checking for redirect

		//checkpoint parameter bootstrap finished 
		//the one and only result from all lines before this point shall be the resultparas array
		//which has exactly the same keys as the default parameter array
		//but we also got pbaout debug and pbaout profiler
		if($resultparas["profiler_enabled"]) $pbaout['profiler']['End of parameter bootstrap']=microtime();
		if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: resultparas at checkpoint parameter bootstrap finished: " . PBALIB::get_r($resultparas); 

		if($resultparas['pba_full_cache_time'] > 0) {
			$fullcache=@PBALIB::pba_cache($resultparas, $dummy, 'get', 'full', 'mixed', $resultparas['pba_full_cache_time'], 'OK');
			if($fullcache[1]) {
				$profilertmp = $pbaout['profiler'];
				$pbaout = $fullcache[0];
				$pbaout['profiler'] = $profilertmp;
				if($resultparas["profiler_enabled"]) {
					$pbaout['profiler']['Got full cache']=microtime();
					$pbaout['profiler']=PBALIB::process_profiler($pbaout['profiler']);
				}
				return $pbaout;
			}
		}

		//let's do some calculations based on these parameters not producing output
		//but just producing more parameters, that shall make us living more easy later :-)
		
		//calculate displayonlybox parameter - this is only a temporary solution and shall be removed later
		//and unset all other displayonly, makeonly and feedlistrequest parameters
		//lot's of parameter junk avoided
		if($resultparas['opmlrequest'] == true || $resultparas['opmlrequest'] == 'Y'){
			$resultparas['displayonlybox']='opml_box';
		}elseif($resultparas['feedlistrequest'] == true || $resultparas['feedlistrequest'] == 'Y'){
			$resultparas['displayonlybox']='feedlist_box';
		}elseif(strlen($resultparas['displayonlybox'])>1){
			//displayonlybox parameter is used, we will not care for more displayonly and makeonly parameters
			true; //no need to unset display only and makeonly values here, unset a few rows later anyway
		}elseif($resultparas['displayonlykalender'] == 'Y'){
			if($resultparas['kalender_as_box']){
				$resultparas['displayonlybox']='kalender_box';
			}else{
				$resultparas['displayonlybox']='kalenderlist_box';
			}
		}else{
			true; //no need to unset here, anyway done in next lines, unset($resultparas['displayonlybox']);
		}
		unset($resultparas['displayonlykalender']);
		unset($resultparas['displayonlysearchbox']);
		unset($resultparas['makeonlyfeedlist']);
		unset($resultparas['feedlistrequest']);
		unset($resultparas['opmlrequest']);
		if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: Displayonlybox calculated: " . PBALIB::get_r($resultparas['displayonlybox']);

		//generally available system constants
		$resultparas["now"]=date('r');

		//quickly calculate some href  values for global navigation - 
		//these might be used in boxes and anywhere else in templates

    //calculate some more global href values
    //uses $resultparas['feedrequest'], $resultparas['feedpage'], 
    //$resultparas['srequri'], $resultparas['baseurl'], $resultparas['short_cache_link']
    //$resultparas['searchphrase'], $resultparas['archivdate']

    //calculate the href to the feedalized version of this page
		$resultparas['feedhref']= PBALIB::makefeedhref($resultparas);
		
    if($resultparas['feedrequest']){
			$resultparas['htmlhref']= PBALIB::makehtmlhref($resultparas);
    }
		
    //calculate the href to the feedlist and opml for this page - where is output_id and list_id?
    $resultparas['feedlisthref']= PBALIB::makefeedlisthref($resultparas);
    $resultparas['feedopmlhref']= PBALIB::makefeedopmlhref($resultparas);

		//link to kalender start page makedatepagehref($thedate="", &$resultparas, $regardkalenderlinkpart=false, $forcekalender=false)
		$resultparas['kalenderhref']=PBALIB::makedatepagehref("", $resultparas, false, true);

		//what page template was requested with this output function call? 
		//let's find out $resultparas['pagetype'], $resultparas['template']

		if($resultparas["profiler_enabled"]) $pbaout['profiler']['Before analyze pagetype']=microtime();

		$pagetype=PBALIB::analysepagetype($resultparas);
		$resultparas['pagetype']=$pagetype['pagetype'];
		$resultparas['template']=$pagetype['template'];
		$pbaout['error'].=$pagetype['error'];

		//$resultparas['pagetype'] is now one of these: 'tickerpage'; 'searchpage'; 'datepage'; 'startpage'; 'feedpage'; 'feedsearchpage'; 'feeddatepage'; 'cachepage'; 'kalpage'; 'feedlistpage'; 'opmlpage'; 'nopage'; 'errorpage';
		//$resultparas['template'] is one of these: ticker, feed, cache, kalender, $resultparas['displayonlybox']
		//or a pseudotemplate for boxonlyrequests: kalender_box, kalenderlist_box, search_box, feedlistsidebar_box
		//or error if we can't find out

		if($resultparas['template'] == 'feed'
			|| $resultparas['template'] == 'feedlist_box'
			|| $resultparas['template'] == 'opml_box'
			) $pbaout['shutdown'] = true;

		//calculate resultparas rawtemplate to understand which boxes we will have to build
		if($resultparas['pagetype']=='nopage') {
			$resultparas['rawtemplate'] = '###' . strtoupper($resultparas['template']) . '###';
		} elseif(strlen($resultparas['template'])>0) {
			//to do: this shall come via resultparas from db later
			$resultparas['rawtemplate'] = $resultparas['template_' . $resultparas['template']];
		} 
		if(!isset($resultparas['rawtemplate'])){
			//to do: this shall come via resultparas from db later
			$resultparas['rawtemplate'] = $resultparas['template_error'];
			$pbaout['error'].= "Something went wrong with page type detection and template assignment:" . $resultparas['template'];
		}

		if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: Before analyze needed boxes: pagetype: " . PBALIB::get_r($pagetype) . " Raw Template: " . $resultparas['rawtemplate']; 
		if($resultparas["profiler_enabled"]) $pbaout['profiler']['Before analyze needed boxes']=microtime();

		//now we can analyze our rawtemplate
		//do we need boxes? which boxes do we need?
		// we have the following boxes: 
		//1. search_box, 2. kalender_box, 3. kalenderlist_box, 4. feedlistsidebar_box, 5. sendfeedpermail_box  - uh missing, but should be here

		//ask config if to change this list, remove boxes, which are forbidden by config, add those explicitely required by config
		$resultparas['rawtemplate'] .= $resultparas['forceboxmaking'];

		$boxesneeded=PBALIB::analyze_needed_elements($resultparas['rawtemplate'], '_BOX');
		$resultparas["boxesneeded"]=$boxesneeded['result'];
		
		
		if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: Detected needed boxes: " . PBALIB::get_r($resultparas["boxesneeded"]);
		//now we know which boxes we need and can make them
		if(is_array($resultparas["boxesneeded"])){
			//no array means no box to make
			foreach($resultparas["boxesneeded"] as $boxtomake => $dummy){
				if($resultparas["profiler_enabled"]) $pbaout['profiler']['Before making ' . $boxtomake]=microtime();
				$box[$boxtomake]=PBALIB::makebox($boxtomake, $resultparas, $pbaout);
				//if($resultparas["profiler_enabled"]) $pbaout['profiler']=array_merge($pbaout['profiler'], $box[$boxtomake]['profiler']);
			}
		} //end if(is_array($resultparas["boxesneeded"]))
		
		if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: Box making done. box: " . PBALIB::get_r($box); 
		if($resultparas["profiler_enabled"]) $pbaout['profiler']['Box making done']=microtime();

		if($resultparas["debug"]) $pbaout['debug1'] .= "\n<br>See my box collection: " . PBALIB::get_r($box); 
		//process boxes, shall be much earlier? why pbaout?
		if(is_array($box)){
			foreach($box as $boxname => $boxvalue){
				if($boxvalue['result']) $pbaout[$boxname] = $boxvalue['result'];
			}
		}
		if($resultparas["profiler_enabled"]) $pbaout['profiler']['After copying boxes to pbaout']=microtime();

		if($resultparas['displayonlybox']){
			$pbaout['result']=$box[$resultparas['displayonlybox']]['result'];
			if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: We exit here, because box making done and resultparas['displayonlybox']: " . $resultparas['displayonlybox']; 
      if($resultparas["profiler_enabled"]) $pbaout['profiler']=PBALIB::process_profiler($pbaout['profiler']);
			return $pbaout;
		}

    //to be completed - sensible input and prevent system outpage due to misconfiguration
    if( $resultparas['maxitems'] > $resultparas['maxitemslimit'] 
      || $resultparas['maxitems'] == 0 || $resultparas['maxitems'] == 'N'
      ) $resultparas['maxitems'] = $resultparas['maxitemslimit'];

    //calculate some basic parameters like used in old output routine
    //start parameter for db query from tickerpage and maxitems
    $resultparas['start']=($resultparas['tickerpage']-1) * $resultparas['maxitems'];

		//disable social bookmarks for search results - more conditions for disabling? where to do it best?
		if($resultparas['searchphrase'] !="") $resultparas['add_social_bookmarks']="N";

		//checkpoint all resultparas calculated - better name would be: before main loop
		// here are all input resultparas calculated, no more resultparas changing after this line. exception: gettheage set's it's array resultparameter on first call
		if($resultparas["profiler_enabled"]) $pbaout['profiler']['All resultparas calculated']=microtime();
		if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: resultparas at checkpoint all resultparas calculated: " . PBALIB::get_r($resultparas); 

//use new output routine
//when not a kalender request, - onlybox requests exited already - let's query the ids, for kalender request we need completely diffrent handling
    if($resultparas['pagetype']!='errorpage' && $resultparas['pagetype']!= 'kalpage' && !$resultparas['displayonlybox']){
			//prepare some basic values - may be do better later, when explicitely requested?

	    // prepare the acceptable tags - it's constant per output id, so it could be made already when storing the output config !!!
			$resultparas['formattedtagset']=PBALIB::processtagset($resultparas['tagset']);
	
	    //set parameters depending on formattype
	    //possible formattypes: 'countrecentitem', 'daterecentitem', 'sitealpha', 'siteupdate'
	    
	    if($resultparas['formattype']=='daterecentitem') $resultparas['fromtimestamp'] = time() - $resultparas['daterecentitemthreshold'];
	    if($resultparas['formattype']=='siteupdate') $resultparas['opsfilter']=true;
	    if($resultparas['formattype']=='sitealpha') {
	      $resultparas['opsfilter']=true;
	      $resultparas['orderbysitename']=true;
	      $resultparas['fromtimestamp'] = time() - $resultparas['sitealphathreshold'];
	    }

      global $bdprsssearchdebug;
      $bdprsssearchdebug=false;
      //parameter site ids and parameter feed not used so far
			if($resultparas["profiler_enabled"]) $pbaout['profiler']['Before search4items']=microtime();
      $id_result = $bdprss_search->bdprss_search4items($resultparas['searchphrase'], $resultparas['start'], $resultparas['maxitems'], false, $resultparas['listid'], $resultparas['archivdate'], "", $resultparas['fromtimestamp'], $resultparas['totimestamp'], $resultparas['opsfilter'], $resultparas['orderbysitename'], $resultparas['cacheid']);
      $bdprsssearchdebug=false;
      global $found_tickeritems;
      $pbaout['founditems']=$found_tickeritems;
      $pbaout['startitem']=$resultparas['start'] + 1;
      if($found_tickeritems == 0) $pbaout['startitem'] = "0";
			$pbaout['lastitem']=min($found_tickeritems,$resultparas['start']+$resultparas['maxitems']);
      if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: we got an item id list from the search engine: " . PBALIB::get_r($id_result); 

//calculate last page and next page href
      if($resultparas['tickerpage']>1) { 
      	$pbaout['lastpageexists']=true;
      	$pbaout['lastpage']=$resultparas['tickerpage'] - 1;
				$pbaout['lastpagehref']=PBALIB::makelastpagehref($pbaout['lastpage'], $resultparas);
      }

			if($pbaout['lastitem'] < $pbaout['founditems']) {
			  $pbaout['nextpageexists']=true;
				$pbaout['nextpage']=$resultparas['tickerpage'] + 1;
				$pbaout['nextpagehref']=PBALIB::makenextpagehref($pbaout['nextpage'], $resultparas);
			}

//get the real row data
//to do better would be to have row wise query in while loop
      if($id_result){

				if($resultparas["profiler_enabled"]) $pbaout['profiler']['Before getsitenitems']=microtime();
      	$itemset = $bdprss_db->getsitenitems($id_result, $resultparas['orderbysitename']);
      	if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: we got result from the item table: " . PBALIB::get_r($itemset); 
      	
      	//might be sensible to make programmer interface to give out original db row results as option?
      	//$pbaout['rawrowdata']=$itemset;


      	//now let's loop through the data rows and format them
      	if($itemset){
					if($resultparas["profiler_enabled"]) $pbaout['profiler']['Before formatting items']=microtime();
					
					if($resultparas['pagetype']!='cachepage') {
						$itemtemplate=PBALIB::preprocess_itemtemplate($resultparas);
					}else{
						$footertemplate=PBALIB::preprocess_headertemplate($resultparas, $pbaout, true);
						$itemtemplate=$footertemplate;
					}
					$itemvaluesneeded=PBALIB::analyze_needed_elements($itemtemplate);
					if($resultparas['cacheid']>0) $itemtemplate ="";
					if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: we need to calculate the following item values: " . PBALIB::get_r($itemvaluesneeded); 
	      	foreach($itemset as $resultrownumber => $item) {
						if($resultparas["profiler_enabled"] > 1) $pbaout['profiler']['Before formatitem ' . $resultrownumber]=microtime();
						$formatteditem = PBALIB::formatitem($item, $resultparas, $itemtemplate, $resultrownumber, $itemvaluesneeded['result']);
						if(!$resultparas['cacheid']>0) $pbaout['body'] .= $formatteditem['result']; //cache page made in footer!!!
						if($resultparas["debug"]) $pbaout['debug'] .= $formatteditem['debug'];
						$pbaout['firstitem_datefeed'] .= $formatteditem['firstitem_datefeed'];
	      	}
					if($resultparas["profiler_enabled"]) $pbaout['profiler']['After formatting items']=microtime();
      	} // end if itemset, to do: fine error output, when no row found
      } //end if($id_result), to do: fine error output, when no row found
      
    } //end if (!$resultparas['kalreq'] && !$resultparas['displayonlybox'])


    if(!$resultparas['cacheid']>0) $formatteditem = false; // we need these items only for cache displaying
    //make a header
    if(!$resultparas['cacheid']>0 && !$resultparas['kalreq'] && !$resultparas['displayonlybox'] 
    	&& !($resultparas['suppressheaderonpage1'] == 'Y' && $resultparas['pagetype'] == 'startpage')
    	) {
      $headertemplate=PBALIB::preprocess_headertemplate($resultparas, $pbaout);
      $formattedheader = PBALIB::formatheader($resultparas, $pbaout, false, false, $headertemplate);
		  $pbaout['header'] = $formattedheader['result'];
		  if($resultparas["debug"]) $pbaout['debug'] .= $formattedheader['debug'];
		}

    //make a footer
    if(!$resultparas['displayonlybox']) {
    	if(!$footertemplate) $footertemplate=PBALIB::preprocess_headertemplate($resultparas, $pbaout, true);
    	$formattedfooter = PBALIB::formatheader($resultparas, $pbaout, true, $formatteditem, $footertemplate);
			$pbaout['footer'] = $formattedfooter['result'];
			if($resultparas["debug"]) $pbaout['debug'] .= $formattedfooter['debug'];
		}

		//some more output
		$pbaout['searchphrase']=$resultparas['searchphrase'];
		$pbaout['archivdate']=$resultparas['archivdate'];
		$pbaout['feedhref']=$resultparas['feedhref'];
		$pbaout['feedlisthref']=$resultparas['feedlisthref'];
		$pbaout['feedopmlhref']=$resultparas['feedopmlhref'];
		$pbaout['kalreq']=$resultparas['kalreq'];
		$pbaout['cacheid']=$resultparas['cacheid'];

    $pbaout['result']=$pbaout['header'] . $pbaout['body'] . $pbaout['footer'];
    if(strlen($pbaout['error']) >0) $pbaout['result'] .= str_replace('###ERRORMESSAGE###', $pbaout['error'], $resultparas['template_error']);
		if($resultparas["profiler_enabled"]) $pbaout['profiler']=PBALIB::process_profiler($pbaout['profiler']);

		if($resultparas['pba_full_cache_time'] > 0) {
			$pba_cachereturn=PBALIB::pba_cache($fullcache[0], $pbaout, 'write', 'full', 'mixed', 0, 'OK');
			if($resultparas["debug"] && $pba_cachereturn) $pbaout['debug'] .= "\n<br>Wrote cache: full"; 
		}

    return $pbaout;
  } // end of new output function

}

require_once(dirname(__FILE__) . '/pba_output_library.php');
?>