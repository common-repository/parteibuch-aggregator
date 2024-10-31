<?php

class PBALIB extends BDPRSS2
{

//this class is a collection of the following functions
////functions with parameter definitions
//		function getdefaultparameter(){ -- moved to get_pbadefaultparameter in pba-defaultparameter__LANG__.php
//		function get_para_definition(){
//		function geturlparameter(){
//		function getoutputconfigparameter($outputid=1){
//		function process_superparameter($superparameter){
//		function bootstrap_parameters($firstparafromfunctioncall){
//
////functions needed for debugging
//  	function get_r($invar){
//  	function process_profiler($profilerarray=array()){
//
////functions to process templates
//		function template_replacements($template, &$replacements){
//		function split_template($template, $bodytag=false){
//		function process_template_conditions($processed_template="", $conditionarray=array()){
//		function preprocess_headertemplate(&$resultparas, &$pbaout, $footer=false){
//  	function formatheader(&$resultparas, &$pbaout, $footer=false, $formatteditem=false){
//	  function preprocess_itemtemplate(&$resultparas){
//  	function formatitem(&$item, &$resultparas, $resultrownumber){
//
////functions to generate href values
//		function makesearchactionhref(&$resultparas){
//		function makehiddenformvalues(&$resultparas){
//		function makecachehref(&$resultparas, $itemid){
//		function makefeedhref(&$resultparas){
//		function makehtmlhref(&$resultparas){
//		function makelastpagehref($lastpage, &$resultparas){
//		function makenextpagehref($nextpage, &$resultparas){
//		function makedatepagehref($thedate="", &$resultparas, $regardkalenderlinkpart=false, $forcekalender=false){
//
////functions to generate boxes with content
//		function formatted_feedlist(&$resultparas, $liststyle="sidebar"){
//		function formatkalender($kalenderdates, &$resultparas, $thedate="", &$pbaout){
//
//
////helper functions
//		function analysepagetype(&$resultparas){
//		function analyze_needed_boxes($rawtemplate){
//		function makebox($boxtomake, &$resultparas, &$pbaout){
//		function processtagset($tagsetfromdb){
//		function gettheage($seconds, &$resultparas=false, &$ageunitsstring=false){
//		function pba_cache(&$identifier, &$cache_content, $cache_mode='get', $name='c', $type='mixed', $cache_max_time=180, $serverstatus='OK'){

//functions with parameter definitions
	function get_para_definition(){
		//outcommenting or setting values to false will disable parameters from url

    //list all possible url parameters for looping through
    $paralist["url"]["outputid"]=true; //forbidden anyway by default
    $paralist["url"]["searchphrase"]=true;
    $paralist["url"]["feedrequest"]=true;
    $paralist["url"]['opmlrequest']=true;
    $paralist["url"]['feedlistrequest']=true;
    $paralist["url"]["tickerpage"]=true;
    $paralist["url"]["archivdate"]=true;
    $paralist["url"]["maxitems"]=true;
    $paralist["url"]["listid"]=true; //forbidden anyway by default, may be opened by function call or later by config
    $paralist["url"]["kalreq"]=true;
    $paralist["url"]["cacheid"]=true;
    $paralist["url"]['srequri']=true; //this is the base url

		return $paralist;
	}

  function geturlparameter(){
    
    global $post;
    if(function_exists('get_page_link')){
    	$stripped_guid=get_page_link();
    	if(!strstr($stripped_guid,'?') 
    		&& !preg_match('/\/$/',$stripped_guid)
    		) $stripped_guid = $stripped_guid . '/';
	    $stripped_guid=str_replace($_SERVER['HTTP_HOST'],'',str_replace(array('http://','https://'),'',$stripped_guid));
    }else{
	    $stripped_guid=str_replace($_SERVER['HTTP_HOST'],'',str_replace(array('http://','https://'),'',$post->guid));
    }
    $sanitized_request_uri= substr($_SERVER['REQUEST_URI'],strlen($stripped_guid));

//    echo "<br>stripped_guid: " . $stripped_guid;
//    echo "<br>request_uri: " . $_SERVER['REQUEST_URI'];
//    echo "<br>sanitized_request_uri: " . $sanitized_request_uri;
    
    //searchphrase
    $paras['searchphrase']="";
    $paras['feedrequest']=false;
    $paras['opmlrequest']=false;
    $paras['feedlistrequest']=false;
    $paras['kalreq']=false;
    $paras['tickerpage']=1;
    $paras['cacheid']=false;
    $paras['archivdate']=false;
    $paras['outputid']=false;
    $paras['srequri']=$sanitized_request_uri;
    
    if(preg_match("/^opml\//", $sanitized_request_uri)){
      $paras['opmlrequest']=true;
    }elseif(preg_match("/^feedlist\//", $sanitized_request_uri)){
      $paras['feedlistrequest']=true;
    }elseif(preg_match("/^ticker-feed\//", $sanitized_request_uri)){
      $paras['feedrequest']=true;
    }elseif(preg_match("/^tickerpage\/([0-9]+)?\//", $sanitized_request_uri, $pagetemp)){
      if(preg_match("/^[0-9]+$/",$pagetemp[1])) {
        $paras['tickerpage']=abs(intval($pagetemp[1]));
        if($paras['tickerpage']<1) $paras['tickerpage']=1;
      }
    }elseif(preg_match("/^[kc]alend[ea]r\/([0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2})?\/?/", $sanitized_request_uri, $kalendertemp)){
      $paras['kalreq']=true;
      if(preg_match("/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/",$kalendertemp[1])) $paras['archivdate'] = $kalendertemp[1];
    }elseif(isset($_GET['kalender'])||isset($_GET['calendar'])){
      $paras['kalreq']=true;
    }elseif(isset($_GET['opml'])){
      $paras['opmlrequest']=true;
    }elseif(isset($_GET['feedlist'])){
      $paras['feedlistrequest']=true;
    }elseif(preg_match("/^([0-9]+)\//", $sanitized_request_uri, $cachetemp)){
      $paras['cacheid']=abs(intval($cachetemp[1]));
    }elseif(isset($_GET['cacheid'])){
      $paras['cacheid']=abs(intval($_GET['cacheid']));
    }elseif(preg_match("/([0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2})(\/tickerpage\/)?([0-9]+)?\/(feed\/)?/", $sanitized_request_uri, $datetemp)) {
      if(strstr($datetemp[2] . $datetemp[3]. $datetemp[4],'feed')) $paras['feedrequest']=true;
      $paras['archivdate']=$datetemp[1];
      if(preg_match("/^[0-9]+$/",$datetemp[3])) {
        $paras['tickerpage']=abs(intval($datetemp[3]));
        if($paras['tickerpage']<1) $paras['tickerpage']=1;
      }
    }elseif(preg_match("/^s\/([a-zA-Z0-9-+%_]+)(\/tickerpage\/)?([0-9]+)?\/(feed\/)?/", $sanitized_request_uri, $searchtemp)){
      $paras['searchphrase']=utf8_encode(urldecode(str_replace("_","+",$searchtemp[1])));
      if(strstr($searchtemp[2] . $searchtemp[3]. $searchtemp[4],'feed')) $paras['feedrequest']=true;
      if(preg_match("/^[0-9]+$/",$searchtemp[3])) {
        $paras['tickerpage']=abs(intval($searchtemp[3]));
        if($paras['tickerpage']<1) $paras['tickerpage']=1;
      }
    }elseif(isset($_GET['searchphrase'])){
      $paras['searchphrase']=stripslashes($_GET['searchphrase']);
      $paras['searchphrase']=utf8_encode(urldecode(str_replace("_","+",$paras['searchphrase'])));
    }
    if(!$paras['cacheid'] && !$paras['kalreq']) {
      if(isset($_GET['feed'])) $paras['feedrequest'] = true;
      if(isset($_GET['tickerpage']) && abs(intval($_GET['tickerpage'])) > 0) $paras['tickerpage'] = abs(intval($_GET['tickerpage']));
    }
    if(!$paras['cacheid']){
      if(isset($_GET['bdprssarchivedate'])) {
        if(preg_match("/([0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2})/", $_GET['bdprssarchivedate'], $badtemp)) $paras['archivdate'] = $badtemp[1];
      }elseif(isset($_GET['itemdate'])) {
  	    if(preg_match("/([0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2})/", $_GET['itemdate'], $badtemp)) $paras['archivdate'] = $badtemp[1];
    	}
    }

//echo "itemdate: " . $_GET['itemdate'];
//echo "archivdate: " . $paras['archivdate'];

    if(isset($_GET['bdprsslist'])) $paras['outputid']=abs(intval($_GET['bdprsslist']));
    if(isset($_GET['max'])) {
    	$maxtmp = abs(intval($_GET['max']));
			if($maxtmp > 0 && $maxtmp <= 50) $paras["maxitems"] = $maxtmp;
    }
    //search parameter possible in GET: $search_phrase, $start=0, $max=10, $list_id=0, $itemdate="", $feed="", item_id
    //output parameter possible in GET: page, outputformat
    
    //parameter directly possible in url: search_phrase, itemdate, item_id, page, calendar
    //indirect parameter in url: list, max

    return $paras;
  }

  function getoutputconfigparameter(&$resultparas){
		//just overloading $resultparas for speed, not changing
		global $bdprss_db, $post;
		
		if($resultparas['getoutputconfigbypageid']===true){
			if(abs(intval($post->ID))>0){
				$outputinfo = $bdprss_db->get_pbaoutput_from_page_id(abs(intval($post->ID)));
			}
		}else{
			$outputid=$resultparas['outputid'];
			$outputinfo = $bdprss_db->get_pbaoutput(abs(intval($outputid)));
		}

		if(!isset($outputinfo->{$bdprss_db->pbaoidentifier})){
			return false;
		}

//item selection
    $configparas["outputid"]=abs(intval($outputinfo->{$bdprss_db->pbaoidentifier}));
		$configparas['outputname']=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->pbaoname});
    $configparas["page2hookin"]=abs(intval($outputinfo->page2hookin));

    $configparas["listid"]=abs(intval($outputinfo->{$bdprss_db->pbaodefaultlist}));
    $configparas["maxitems"]=abs(intval($outputinfo->{$bdprss_db->pbaomaxitems}));
    $configparas["formattype"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->pbaoformattype});

//item page formatting
		$configparas["template_ticker"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->pbaotemplate_ticker});
		$extralinkhelper=$outputinfo->{$bdprss_db->pbaoappend_extra_link};
		if($extralinkhelper != 'Y') $configparas['noextralink']= 'Y';
		$configparas["append_cache_link"]=$outputinfo->{$bdprss_db->pbaoappend_cache_link};
		$configparas["add_social_bookmarks"]=$outputinfo->{$bdprss_db->pbaoadd_social_bookmarks};

//item formatting
		$configparas['maxbodylength']= abs(intval($outputinfo->{$bdprss_db->pbaomaxlength}));
		$configparas['maxwordlength']= abs(intval($outputinfo->{$bdprss_db->pbaomaxwordlength}));
		$configparas["itemdateformat"]= BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->pbaoitem_date_format});
		$configparas["tagset"]= BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->pbaoallowablexhtmltags});

//widget paras
		$configparas['template_sidebarwidget'] = BDPRSS2::codeQuotes($outputinfo->template_sidebarwidget);

//cache page options
    $configparas["iscachable"] = $outputinfo->{$bdprss_db->pbaoiscachable};
		$configparas["template_cache"]=BDPRSS2::codeQuotes($outputinfo->template_cache);
    $configparas["cacheviewpage"]= BDPRSS2::codeQuotes($outputinfo->cache_view_page);

//feed options
    $configparas["channel_title"]= BDPRSS2::codeQuotes($outputinfo->channel_title);
    $configparas["htmlpage"]= BDPRSS2::codeQuotes($outputinfo->channel_link);
    $configparas["channel_description"]= BDPRSS2::codeQuotes($outputinfo->channel_description);
    $configparas["channel_language"]= BDPRSS2::codeQuotes($outputinfo->channel_language);
    $configparas["channel_copyright"]= BDPRSS2::codeQuotes($outputinfo->channel_copyright);

//kalender options
		$configparas["template_kalender"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->otemplate_kalender});
		$configparas["archive_date_format"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->oarchive_date_format});
		$configparas["kalendermonthslist"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->okalendermonthslist});
		$configparas["kalenderboxtablecaption"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->okalenderboxtablecaption});
		$configparas["kalender_last"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->okalender_last});
		$configparas["kalender_next"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->okalender_next});
		$configparas["kalenderboxdaysofweeklist"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->okalenderboxdaysofweeklist});

		if($bdprss_db->serverstatus['pbacache']['status']=='ok'){
			if(isset($bdprss_db->serverstatus['full_cache_time']['notice'])) $configparas["pba_full_cache_time"]=$bdprss_db->serverstatus['full_cache_time']['notice'];
			if(isset($bdprss_db->serverstatus['kalenderquery_cache_time']['notice'])) $configparas["pba_kalenderquery_cache_time"]=$bdprss_db->serverstatus['kalenderquery_cache_time']['notice'];
			if(isset($bdprss_db->serverstatus['feedlistquery_cache_time']['notice'])) $configparas["pba_feedlistquery_cache_time"]=$bdprss_db->serverstatus['Delete   	feedlistquery_cache_time']['notice'];
		}
		if(isset($bdprss_db->serverstatus['rewriting']['status'])) $configparas["short_cache_link"] = ($bdprss_db->serverstatus['rewriting']['status']=='ok');

		$configparas["superparameter"]=BDPRSS2::codeQuotes($outputinfo->{$bdprss_db->pbao_superparameter});

		$superparameterarray=PBALIB::process_superparameter($configparas["superparameter"]);
		foreach($superparameterarray as $paraname => $paravalue){
			$configparas[$paraname]=$paravalue;
		}
		unset ($configparas["superparameter"]);

		return $configparas;
	}

	function process_superparameter($superparameter=""){
		$superparameterarray=array();
		preg_match_all("'###SUPERPARAMETER_([A-Za-z0-9_-]+?)_BEGIN###(.*?)###SUPERPARAMETER_[A-Za-z0-9_-]*?END###'s", $superparameter, $match);
		//print_r($match);
		if(is_array($match) && isset($match[1]) && isset($match[2])){
			foreach($match[1] as $match1key => $match1value){
				//echo "<br>matchkey is : " . $matchkey . "value is: ";
				if(trim($match[2][$match1key]) == "false") {
					$superparameterarray[$match1value]=false;
				}elseif(trim($match[2][$match1key]) == "true"){
					$superparameterarray[$match1value]=true;
				}else{
					$superparameterarray[$match1value]=$match[2][$match1key];
				}
			}
			//print_r($superparameterarray);
		}
		return $superparameterarray;
	}

  function bootstrap_parameters($firstparafromfunctioncall){
  	global $post;
  	
		//initialize all neeeded parameters with default values
    $resultparas=get_pbadefaultparameter();
		//get lists in array to name all possible parameters for looping through
    $paralist=PBALIB::get_para_definition();

		$resultparas['url2plugindir'] = get_option('home') . '/wp-content/plugins/'.PBA_DIRECTORY.'/';

    //$pbaout['debug'] .= "\n<br>Debug: Default resultparas: " . PBALIB::get_r($resultparas); 

		//get function call parameters
    if(is_array($firstparafromfunctioncall)) $funcdefparas=$firstparafromfunctioncall;

		//'N' meaning not set used for backward compatibility
		//stupid idea? we possibly may use false to overwrite the maxitems in function call
    if($funcdefparas["maxitems"] == 'N') unset($funcdefparas["maxitems"]);

		//we have got all parameters from function call now and normalized them
    //$pbaout['debug'] .= "\n<br>Debug: funcdefparas: " . PBALIB::get_r($funcdefparas); 

//if exist, we overwrite default output id paras with paras from func call, do other later
//we unset the output id function parameters, so they will not overwrite config parameter values later
    if(isset($funcdefparas['outputid'])) {
    	$resultparas['outputid'] = $funcdefparas['outputid'];
    }else{
    	if(isset($funcdefparas['getoutputconfigbypageid'])) $resultparas['getoutputconfigbypageid'] = $funcdefparas['getoutputconfigbypageid'];
    }
		unset($funcdefparas["outputid"]);
		unset($funcdefparas['getoutputconfigbypageid']);
    if(isset($funcdefparas['outputidfromurlallowed'])) $resultparas['outputidfromurlallowed'] = $funcdefparas['outputidfromurlallowed'];
		unset($funcdefparas["outputidfromurlallowed"]);
		

		//get parameters from url
    $urlparas=PBALIB::geturlparameter();
    //$pbaout['debug'] .= "\n<br>Debug: urlparas: " . PBALIB::get_r($urlparas); 

    //now let's apply limits to url parameter output_id and find out the output id to take
		//first normalize output id parameter from url - check other values later
		$urlparas['outputid']=abs(intval($urlparas['outputid']));
		if(!$urlparas['outputid']>0) unset($urlparas['outputid']);

    if($resultparas['outputidfromurlallowed'] === false){
      unset($urlparas['outputid']);
    }elseif($resultparas['outputidfromurlallowed'] !== true){
      if(!strstr("," . str_replace(" ", '', $resultparas['outputidfromurlallowed']) . ",", $urlparas['outputid'])) unset($urlparas['outputid']);
    }
    if(isset($urlparas['outputid'])) $resultparas['outputid'] = intval($urlparas['outputid']);
    
    //now we know, which output definition we have to take from db -> let's get it
  	$configparas=PBALIB::getoutputconfigparameter($resultparas);
    if(!is_array($configparas)) {
    	return false;
    }

	  if(function_exists('get_page_link')){
	  	if($funcdefparas['show_sidebarwidget'] == 'Y'
	  		&& $configparas['page2hookin'] > 0){
	  		$resultparas['baseurl']=get_page_link($configparas['page2hookin']);
	  		$resultparas['search_page_baseurl']="";
	  	}else{
	  		$resultparas['baseurl']=get_page_link();
	  	}
	  	if(!strstr($resultparas['baseurl'],'?') 
	  		&& !preg_match('/\/$/',$resultparas['baseurl'])
	  		) $resultparas['baseurl'] = $resultparas['baseurl'] . '/';
	  }else{
	  	$resultparas['baseurl'] = $post->guid;
	  }
		if($resultparas['search_page_baseurl']=="") $resultparas['search_page_baseurl'] = $resultparas['baseurl'];
		if($resultparas['htmlpage']=="")$resultparas['htmlpage'] = $resultparas['baseurl'];
    if($configparas["htmlpage"]=="") unset($configparas["htmlpage"]); //this will make the default post->guid be used

    //copy config paras over default paras
    if($configparas){
	    foreach($configparas as $paraname => $paravalue){
	    	$resultparas[$paraname]=$paravalue;
	    }
		}

    //copy func defined paras over default paras
    if($funcdefparas){
	    foreach($funcdefparas as $paraname => $paravalue){
	    	$resultparas[$paraname]=$paravalue;
	    }
		}

		//check if we want to show a sidebar
		if($resultparas['show_sidebarwidget'] == 'Y'){
			$resultparas['template_ticker']=$resultparas['template_sidebarwidget'];
      $resultparas["add_social_bookmarks"]='N';
      unset($urlparas['searchphrase']);
      unset($urlparas['cacheid']);
      unset($urlparas['kalreq']);
		}

    //to be completed filter url parameter to allowed values
    if($resultparas['listidfromurlallowed'] === false){
      unset($urlparas['listid']);
    }elseif($resultparas['listidfromurlallowed'] !== true){
      if(!strstr("," . str_replace(" ", '', $resultparas['listidfromurlallowed']) . ",", $urlparas['listid'])) unset($urlparas['listid']);
    }
		if($urlparas['kalreq'] === false) unset($paralist["url"]["kalreq"]);
		if($urlparas['feedrequest'] === false) unset($paralist["url"]["feedrequest"]);
    if($resultparas['searchenabled']){
    	$resultparas['searchactionhref'] = PBALIB::makesearchactionhref($resultparas);
    	$resultparas['hiddensearchformformvalues'] = PBALIB::makehiddenformvalues($resultparas);
    	$urlparas['searchphrase']=utf8_decode($urlparas['searchphrase']);
    }else{
    	unset($urlparas['searchphrase']);
    	$resultparas['template_search_box']="";
    }

    //copy url paras over result got by funcdef paras copying
    foreach($paralist["url"] as $paraname => $paravalue){
    	if(isset($urlparas[$paraname])){
    		if($paravalue) $resultparas[$paraname]=$urlparas[$paraname]; //false means not overwriting !!!
    	}
    }
		return $resultparas;
	}

//functions needed for debugging
  function get_r($invar){
    ob_start();
    print_r($invar);
    $outvar = ob_get_contents();
    ob_end_clean();
    return $outvar;
  }

  function process_profiler($profilerarray=array()){
		$profilerself['Profiler reached at']=microtime();
  	$profilerarray=array_merge($profilerarray, $profilerself);
  	$precision=4;
  	$first=true;
  	$counter=0;
  	$outstring="\nOutput from Profiler:";
  	foreach($profilerarray as $point => $microtimer){
  		$timearray = explode(" ", $microtimer);
    	$microtimestamp = (substr($timearray[1],6,4) + substr($timearray[0],0,$precision + 2));
    	if($first) { $starttime = $microtimestamp; $last=$microtimestamp; $first=false; $outstring .= "<table>"; }
  		$outstring .= "<tr>\n<td>Checkpoint: " . $point . 
  			"</td><td> Time: " . substr($timearray[1],0,6) . $microtimestamp . 
  			"</td><td> Diff: "  . round($microtimestamp - $last,$precision) .
  			"</td><td> Total: "  . round($microtimestamp - $starttime,$precision) . "</td>\n</tr>";
    	$last=$microtimestamp;
  	}
  	$outstring .= "</table>";
  	return $outstring;
  }

//functions to process page templates
	function template_replacements($template, &$replacements){
		//we will not change input values, but pass replacements like eg resultparas by reference parameters to be quick, 
		//is it really neccessary? or will the php optimizer understand itself not to pass variables as value, 
		//until the value is not changed?
		preg_match_all('/###[0-9A-Z_]+###/', $template, $uppertags);
		foreach($uppertags[0] as $key => $uppertag){
			$tag=str_replace('#','',strtolower($uppertag));
			if(isset($replacements[$tag])) {
				$needle[]=$uppertag;
				$replace[]=$replacements[$tag];
			}
		}
		$processed_template=str_replace($needle, $replace, $template);
		return $processed_template;
	}

	function split_template($template, $bodytag=false, $looptag='LOOP', $headeronly=false){
		if($bodytag===false) $bodytag = '###INSIDELOOP###';
		$split_template['aroundloop']=preg_replace('/###'.$looptag.'BEGIN###.*?###'.$looptag.'END###/s',$bodytag,$template);
		preg_match("'(.*)###".$looptag."BEGIN###(.*?)###".$looptag."END###(.*)'s", $template, $match);
		$split_template['beforeloop']=$match[1];
		$split_template['inloop']=$match[2];
		$split_template['postloop']=$match[3];
		if($headeronly=="header"){
			return $split_template['beforeloop'];
		}elseif($headeronly=="footer"){
			return $split_template['postloop'];
		}elseif($headeronly=="body"){
			return $split_template['inloop'];
		}elseif($headeronly=="aroundloop"){
			return $split_template['aroundloop'];
		}
		return $split_template;
	}

	function process_template_conditions($processed_template="", $conditionarray=array()){
		foreach($conditionarray as $value){
			$conditionaltag=$value['tag'];
			$expression=$value['expression'];
			if($expression !== true) $expression = false;
			if($expression) {
				$conditionaltagarray=array('###'.$conditionaltag.'BEGIN###', '###'.$conditionaltag.'END###');
				$processed_template = str_replace($conditionaltagarray , '', $processed_template);
			} else {
				$processed_template=PBALIB::split_template($processed_template, '', $conditionaltag, 'aroundloop');
			}
		}
		return $processed_template;
	}

	function preprocess_headertemplate(&$resultparas, &$pbaout, $footer=false){
		$pre="pre";
		if($footer) $pre="post";
		if($resultparas["profiler_enabled"]) $pbaout['profiler']['Start making a ' . $pre . ' header']=microtime();
		if($resultparas['kalreq']) {
  		$pbaheader = $resultparas['template_' . $resultparas['template']];
		}elseif($resultparas['pagetype'] == 'cachepage'){
			$pbaheader = $resultparas['template_' . $resultparas['template']];
		}elseif($resultparas['feedrequest']) {
			$pbaheader_tmp = $resultparas['template_' . $resultparas['template']];
			$headeronly="header";
			if($footer) $headeronly="footer";
			$pbaheader = PBALIB::split_template($pbaheader_tmp, false, 'LOOP', $headeronly);
			//	  $pbaheader=$resultparas[$pre . '_loop_feedtemplate'];
		} else {
			$pbaheader_tmp = $resultparas['template_' . $resultparas['template']];
			$headeronly="header";
			if($footer) $headeronly="footer";
			$pbaheader = PBALIB::split_template($pbaheader_tmp, false, 'LOOP', $headeronly);
			//		$pbaheader=$resultparas[$pre . '_loop_template'];
			if($footer && $resultparas['add_social_bookmarks']=="Y") {
				//this looks stupid, why to format it here?
			  $pbaheader = str_replace('###URL2PLUGINDIR###' , $resultparas['url2plugindir'], $resultparas['headersbtemplate']) . $pbaheader;
			}
		}

		//first let's cut out the condional parts, where the condition is false
		//Syntax: $conditionarray[]=array('tag' =>'MYCONDITIONALTAG','expression' => (true));
		
		//why to use such sloppy conditions here - haven't we calculated pagetype for this?
		$conditionarray[]=array('tag' =>'ISSEARCH','expression' => ($resultparas['searchphrase'] !=""));
		$conditionarray[]=array('tag' =>'ISDATE','expression' => ($resultparas['archivdate']!=""));
		$conditionarray[]=array('tag' =>'ISNODATENOSEARCH','expression' => ($resultparas['searchphrase'] =="" && $resultparas['archivdate']==""));
		$conditionarray[]=array('tag' =>'LASTLINK','expression' => ($pbaout['lastpageexists']));
		$conditionarray[]=array('tag' =>'NEXTLINK','expression' => ($pbaout['nextpageexists']));
		
		if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: conditionarray: " . PBALIB::get_r($conditionarray); 
		$pbaheader=PBALIB::process_template_conditions($pbaheader, $conditionarray);

		if($resultparas["debug"]) $pbaout['debug'] .= "\n<br>Debug: See the pbaheader: " . PBALIB::get_r($pbaheader); 

		return $pbaheader;
	}

  function formatheader(&$resultparas, &$pbaout, $footer=false, $formatteditem=false, $pbaheader){

    //will see later if we need more paras

		//variables we understand
		if($resultparas['cacheid']>0) $formattedheader = $formatteditem; //copy over variables from loop to understand them here
		$formattedheader['searchphrase']=$resultparas['searchphrase']; 
		$formattedheader['archivedate']=$resultparas['archivdate']; 
		$formattedheader['feedhref']=$resultparas['feedhref'];
		$formattedheader['founditems']=$pbaout['founditems'];
		$formattedheader['startitem']=$pbaout['startitem'];
		$formattedheader['lastitem']=$pbaout['lastitem'];
		$formattedheader['lastpage']=$pbaout['lastpage'];
		$formattedheader['nextpage']=$pbaout['nextpage'];
		$formattedheader['lastpagehref']=$pbaout['lastpagehref'];
		$formattedheader['nextpagehref']=$pbaout['nextpagehref'];
		$formattedheader['kalenderhref']=$resultparas['kalenderhref'];
		$formattedheader['feedlisthref']=$resultparas['feedlisthref'];
		$formattedheader['feedopmlhref']=$resultparas['feedopmlhref'];
		$formattedheader['outputname']=$resultparas['outputname'];

		$formattedheader['channel_title']=$resultparas["channel_title"];
    $formattedheader['htmlpage']=$resultparas["htmlpage"];
    $formattedheader['channel_description']=$resultparas["channel_description"];
    $formattedheader['channel_language']=$resultparas["channel_language"];
    $formattedheader['channel_copyright']=$resultparas["channel_copyright"];
		$formattedheader['firstitem_datefeed']= $pbaout['firstitem_datefeed'];
		$formattedheader['htmlhref']= $resultparas['htmlhref'];
		$formattedheader['baseurl']=$resultparas['baseurl'];
		$formattedheader['pba_version']= PBA_PRODUCT . " " . PBA_VERSION;
		
		$formattedheader['kalenderdate']=$pbaout['kalenderdate'];

		//boxes we understand
		$formattedheader['kalenderlist_box']=$pbaout['kalenderlist_box'];
		$formattedheader['kalender_box']=$pbaout['kalender_box'];
		$formattedheader['feedlistsidebar_box']=$pbaout['feedlistsidebar_box'];
		$formattedheader['search_box']=$pbaout['search_box'];

		//now do the final replacements
		foreach($formattedheader as $key => $value){
		  $needle[$key]="###" . strtoupper($key) . "###";
			//echo $needle[$key] . "\n"; //take this to find out with variables the engine understands
		  $replace[$key]=$value;
		}

//		if($pre=="post" && $resultparas["debug"]) $pbaout['debug1'] .= "\n<br>Debug: See the needles: " . PBALIB::get_r($needle); 
//		if($pre=="post" && $resultparas["debug"]) $pbaout['debug1'] .= "\n<br>Debug: See the replaces: " . PBALIB::get_r($replace); 
//		if($pre=="post" && $resultparas["debug"]) $pbaout['debug1'] .= "\n<br>Debug: See the template: " . PBALIB::get_r($pbaheader); 
		
		$formattedheader['result']=str_replace($needle, $replace, $pbaheader);
		return $formattedheader;
  }

  function preprocess_itemtemplate(&$resultparas){
		//get the item template
		if($resultparas['cacheid']>0){
			$itemtemplate=""; //just an empty template, we will print it with the footer - is this clever???
		}elseif($resultparas['feedrequest']){
			$itemtemplate_tmp = $resultparas['template_' . $resultparas['template']];//fixme later to template come from resultparas again, was before: $itemtemplate=$resultparas['in_loop_feedtemplate'];
			$itemtemplate = PBALIB::split_template($itemtemplate_tmp, false, 'LOOP', 'body');
		}else{
			$itemtemplate_tmp = $resultparas['template_' . $resultparas['template']];
			$itemtemplate = PBALIB::split_template($itemtemplate_tmp, false, 'LOOP', 'body');
			if($resultparas['add_social_bookmarks']=="Y") $itemtemplate  = str_replace('###ITEM_BODY###' , '###ITEM_BODY###' . $resultparas['itemssbtemplate'], $itemtemplate);
		}
		//add some standard components to the template, if desired
		if($resultparas["iscachable"]=="Y" && $resultparas["append_cache_link"]=="Y") $itemtemplate  = str_replace('###ITEM_BODY###' , '###ITEM_BODY###' . $resultparas['cachelinktemplate'], $itemtemplate);
		if($resultparas["noextralink"]!="Y") $itemtemplate = str_replace('###ITEM_BODY###' , '###ITEM_BODY###' . $resultparas['extralinktemplate'], $itemtemplate);

  	return $itemtemplate;
	}

  function formatitem(&$item, &$resultparas, $itemtemplate, $resultrownumber, $itemvaluesneeded=false){
    //takes an item-site object row from dbquery and gives back
    //in array field result the result, 
    //and further in array field debug some debug output

		//the row number in the list given out
		$formatteditem['result_rownumber']=$resultrownumber + 1;
		
		$formatteditem['baseurl']=$resultparas['baseurl'];
		
		//read in the row ... for site
		$formatteditem['site_id']=$item->siteid;
		$formatteditem['site_name']=$item->site_name;
		$formatteditem['site_feedurl']=$item->feed_url;
		$formatteditem['site_nameoverridden']=$item->site_name_overriden;
		$formatteditem['site_description']=$item->description;
		$formatteditem['site_license']=$item->site_license;
		$formatteditem['site_url']=$item->site_url;
		$formatteditem['site_updatetime']=$item->site_update_time;
		
		//read in the row ... for item
		$formatteditem['item_id']=$item->itemid;
		$formatteditem['item_url']=$item->item_url;
		$formatteditem['item_name']=$item->item_name;
		$formatteditem['item_sitename']=$item->item_site_name;
		$formatteditem['item_siteurl']=$item->item_site_url;
		$formatteditem['item_license']=$item->item_license;
		$formatteditem['item_timestamp']=$item->item_time;
		$formatteditem['item_updatetimestamp']=$item->item_update_time;
		$formatteditem['item_body']=$item->text_body;
		
		//we got the rows from database, 
		//now do some quick processing with the raw row values 
		//and get in this way some new values
		
		$formatteditem['cachehref']=PBALIB::makecachehref($resultparas, $formatteditem['item_id']);
		
		//make an item text as to be displayed as cache item
		if($itemvaluesneeded['item_cachebody']){
			$formatteditem['item_cachebody'] = BDPRSS2::codeQuotes($formatteditem['item_body']);
			$formatteditem['item_cachebody'] = eregi_replace('&lt;' , '<', $formatteditem['item_cachebody']);
			$formatteditem['item_cachebody'] = eregi_replace('&gt;' , '>', $formatteditem['item_cachebody']);
		}

		//process the item body
		if($itemvaluesneeded['item_body'] || $itemvaluesneeded['item_cachebody'] || $itemvaluesneeded['item_feedbody'] || $itemvaluesneeded['item_description']){
			$formatteditem['item_body']=BDPRSS2::remove_link_and_cache_links_from_item($formatteditem['item_body']);
		}
		//this seems to be for feed usage
		if($itemvaluesneeded['item_description']){
			$formatteditem['item_description'] = BDPRSS2::packageItemText($formatteditem['item_body'], $resultparas['maxbodylength'], $resultparas['maxwordlength'], false, $resultparas['formattedtagset']);
		}
		if($itemvaluesneeded['item_feedbody']){
			$formatteditem['item_feedbody'] = BDPRSS2::codeQuotes($formatteditem['item_body']);
			$formatteditem['item_feedbody'] = eregi_replace('&lt;' , '<', $formatteditem['item_feedbody']);
			$formatteditem['item_feedbody'] = eregi_replace('&gt;' , '>', $formatteditem['item_feedbody']);
		}
		$formatteditem['item_datefeed']= date('r', $formatteditem['item_timestamp']);
		if($formatteditem['result_rownumber'] == 1) $formatteditem['firstitem_datefeed'] = $formatteditem['item_datefeed'];

		//package item text for list description display
		if($itemvaluesneeded['item_body']){
			$formatteditem['item_body'] = BDPRSS2::packageItemText($formatteditem['item_body'], $resultparas['maxbodylength'], $resultparas['maxwordlength'], true, $resultparas['formattedtagset']);
		}
		//process item titles decode ": " separator in item titles -- may be buggy, needs to come from site details overridden and have some testing
		if($formatteditem['site_nameoverridden'] != 'Y'){
		  $itemtitlearray=explode(': ', $formatteditem['item_name'], 2);
		  if(strlen($itemtitlearray[1])>0) {
		    $formatteditem['item_name'] = $itemtitlearray[1];
		    $formatteditem['site_name'] = $itemtitlearray[0];
		  }else{
		    //site name not overridden, but no : in item
		    $formatteditem['site_name']=$formatteditem['item_sitename'];
		  }
		} else {
			//site_name overridden
			$formatteditem['site_name'] = $formatteditem['site_name']; //bogus, no need for this
		}

		if(strlen($formatteditem['item_siteurl'])>0) $formatteditem['site_url'] = $formatteditem['item_siteurl'];

		$formatteditem['site_name'] =  BDPRSS2::packageItemText($formatteditem['site_name']);
		$formatteditem['item_name'] =  BDPRSS2::packageItemText($formatteditem['item_name']);
		
		//generate dates in some configured and standard formats
		if(strlen($resultparas["itemdateformat"]."") >0){
			$formatteditem['item_datetime'] = date($resultparas["itemdateformat"]."", ($formatteditem['item_timestamp'])+(0*3600));
		} else {
			$formatteditem['item_datetime'] = PBALIB::gettheage($formatteditem['item_timestamp'], $resultparas['ageunit'], $resultparas['ageunitsstring']);
		}
		
		if(strlen($resultparas["itemdateformat"]."") >0){
			$formatteditem['item_updatedatetime'] = date($resultparas["itemdateformat"]."", ($formatteditem['item_updatetimestamp'])+(0*3600));
		} else {
			$formatteditem['item_updatedatetime'] = PBALIB::gettheage($formatteditem['item_updatetimestamp'], $resultparas['ageunit'], $resultparas['ageunitsstring']);
		}
		$formatteditem['item_updatedate']= date('r', $formatteditem['item_updatetimestamp']);
		
		//unset not needed values, to be done later
		unset ($formatteditem['item_sitename']);
		unset ($formatteditem['item_siteurl']);
		unset ($formatteditem['site_nameoverridden']);
		
		//build up variables for the template
		$result=PBALIB::template_replacements($itemtemplate, &$formatteditem);
		$formatteditem['result']=$result;
//		foreach($formatteditem as $key => $value){
//		  $needle[$key]="###" . strtoupper($key) . "###";
//			echo $needle[$key] . "\n"; //take this to find out with variables the engine understands
//		  $replace[$key]=$value;
//		}
//		$formatteditem['result']=str_replace($needle, $replace, $itemtemplate);
		return $formatteditem;
  }

//functions to generate href values
	function makefeedlisthref(&$resultparas){
		if($resultparas['short_cache_link']){
    	$feedlisthref = $resultparas['baseurl'] . 'feedlist/';
    }else{
			$joiner='?';
			if(strstr($resultparas['baseurl'], '?')) $joiner = '&';
    	$feedlisthref = $resultparas['baseurl'] . $joiner . 'feedlist';
    }
		return $feedlisthref;
	}


	function makefeedopmlhref(&$resultparas){
    if($resultparas['short_cache_link']){
    	$feedopmlhref = $resultparas['baseurl'] . 'opml/';
    }else{
			$joiner='?';
			if(strstr($resultparas['baseurl'], '?')) $joiner = '&';
    	$feedopmlhref = $resultparas['baseurl'] . $joiner . 'opml';
    }
		return $feedopmlhref;
	}

	function makesearchactionhref(&$resultparas){
		if(strstr($resultparas['search_page_baseurl'],'?')) {
			$searchactionhref=$resultparas['search_page_baseurl'];
		}else{
			$searchactionhref=$resultparas['search_page_baseurl'] . 'index.php';
		}
		return $searchactionhref;
	}

	function makehiddenformvalues(&$resultparas){
		$hiddenformvalues = "";
		if(preg_match('/\?(.+)$/',$resultparas['search_page_baseurl'],$baseurlquerystring)) {
			parse_str($baseurlquerystring[1], $baseurlgetarray);
			//print_r($baseurlgetarray);
			foreach($baseurlgetarray as $key => $value){
				$hiddenformvalues .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
		}
		return $hiddenformvalues;
	}

	function makecachehref(&$resultparas, $itemid){
		//not changing resultparas, just passing by reference to gain speed
		$itemid=abs(intval($itemid));
		$baseurl = $resultparas['baseurl'];
		if($resultparas['cacheviewpage'] != "" ) $baseurl = $resultparas['cacheviewpage'];
		
		if($resultparas['short_cache_link']){
			$cachehref = $baseurl . $itemid . '/';
		}else{
			$joiner='?';
			if(strstr($baseurl, '?')) $joiner = '&';
			$cachehref = $baseurl . $joiner . 'cacheid=' . $itemid;
		}
		return $cachehref;
	}


	function makefeedhref(&$resultparas){
		//not changing resultparas, just passing by reference to gain speed
		//calculate the link to the feedalized version of this page
	  if(!$resultparas['feedrequest']){
      if($resultparas['feedpage']!="") {
        //use this url replaced by feedurlbase and strip page info
        $feedhref=$resultparas['feedpage'] . preg_replace("/[&\?]?tickerpage[\/=][0-9]+\/?/s", '', $resultparas['srequri']);
      }else{
        $feedhref= $resultparas['baseurl'] . preg_replace("/[&\?]?tickerpage[\/=][0-9]+\/?/s", '', $resultparas['srequri']);
        if(strstr($feedhref,'?')) {
          $feedhref .= '&feed';
        } else {
          if($resultparas['short_cache_link']){
            $feedhrefworkaround="";
            if($resultparas['searchphrase'] == "" && $resultparas['archivdate'] == "") $feedhrefworkaround="ticker-";
            $feedhref .= $feedhrefworkaround . 'feed/';
          }else{
            $feedhref .= '?feed';
          }
        }
      }
    } else {
      //this is a feedrequest, so let us give out a link to ourself
      //wrong idea - this could also be a feedlist?
      $feedhref = $resultparas['baseurl'] . $resultparas['srequri'];
      //to do: and calculate a nice html link for use in feed header
    }
	return $feedhref;
	}

	function makehtmlhref(&$resultparas){
		//used to calculate the href value to pba html page when displaying a feed
		if($resultparas['searchphrase'] == "" && $resultparas['archivdate'] == ""){
      $htmlhref = $resultparas['htmlpage'];
    } else {
      if($resultparas['short_cache_link']){
      $htmlhref = $resultparas['htmlpage'] . preg_replace("/\/(ticker-)?feed\//s", '/',$resultparas['srequri']);
      }else{
        $htmlhref = $resultparas['htmlpage'] . preg_replace("/[&\?]feed/s", '', str_replace('?feed&' , '&feed?', $resultparas['srequri']));
      }
    }
		return $htmlhref;
	}

	function makelastpagehref($lastpage, &$resultparas){
		//resultparas should not be changed, just overloaded by reference to gain speed in processing
		if($lastpage == 1) {
			if($resultparas['specialpage1url'] 
				&& $resultparas['specialpage1url'] != 'N'
				&& $resultparas['searchphrase']== ""
				&& $resultparas['archivdate']== ""
			){
				$lastpagehref = $resultparas['specialpage1url'];
			}else{
    		$lastpagehref= $resultparas['baseurl'] . preg_replace("/[&\?]?tickerpage[\/=][0-9]+\/?/s", '', $resultparas['srequri']);
    	}
    } else {
    	if(strstr($resultparas['srequri'],"tickerpage/".$resultparas['tickerpage']."/")) {
    		$lastpagehref=$resultparas['baseurl'] . str_replace("tickerpage/".$resultparas['tickerpage']."/","tickerpage/".$lastpage."/",$resultparas['srequri']);
    	}else{
    		$lastpagehref=$resultparas['baseurl'] . str_replace("tickerpage=".$resultparas['tickerpage'],"tickerpage=".$lastpage,$resultparas['srequri']);
    	}
    }
		return $lastpagehref;
	}

	function makenextpagehref($nextpage, &$resultparas){
		//resultparas should not be changed, just overloaded by reference to gain speed in processing
		if($nextpage == 2){ 
			//add the page to the link
			//we have to decide, if we add a short uri rewritten page link or a query style link, on start page, there s no way to determine this from URI, so we need to get it from some config value
			if(strstr($resultparas['srequri'],'?')){
				if($resultparas['short_cache_link']){
					$slash ="/";
					if(strstr($resultparas['srequri'],'/?')) $slash ="";
					$nextpagehref= $resultparas['baseurl'] . str_replace('?', $slash . 'tickerpage/' . $nextpage . '?', $resultparas['srequri']);
				} else {
					$nextpagehref= $resultparas['baseurl'] . $resultparas['srequri'] . '&tickerpage=' . $nextpage;
				}
			}else{ 
				//no ? in url
				if($resultparas['short_cache_link']){
					$slash ="";
					if(substr($resultparas['baseurl'] . $resultparas['srequri'], strlen($resultparas['baseurl'] . $resultparas['srequri']) - 1) != '/') $slash ="/";
					$nextpagehref= $resultparas['baseurl'] . $resultparas['srequri'] . $slash . 'tickerpage/' . $nextpage . '/';
				} else {
					$joiner='?';
					if(strstr($resultparas['baseurl'], '?')) $joiner = '&';
					$nextpagehref= $resultparas['baseurl'] . $resultparas['srequri'] . $joiner . 'tickerpage=' . $nextpage;
				}
			}
		}else{ 
			// nextpage > 2 - replace existing page identifier
			//echo "srequri: " . $resultparas['srequri'] . "<br>";
			if(strstr($resultparas['srequri'],'tickerpage=')){
		  	$nextpagehref= $resultparas['baseurl'] . str_replace('tickerpage=' . $resultparas['tickerpage'], 'tickerpage=' . $nextpage, $resultparas['srequri']);
		  }else{
		  	$nextpagehref= $resultparas['baseurl'] . str_replace('tickerpage/' . $resultparas['tickerpage'] . '/', 'tickerpage/' . $nextpage . '/', $resultparas['srequri']);
		  }
		}
		return $nextpagehref;
	}

	function makedatepagehref($thedate="", &$resultparas, $regardkalenderlinkpart=false, $forcekalender=false){
		//check input values
		if ($thedate!="" && !ereg("[0-9][0-9][0-9][0-9]-[0-1][0-9]-[0-3][0-9]", $thedate)) $thedate = "";
		if($thedate=="") $forcekalender = true;

		//depends on $resultparas['short_cache_link']
		$kalender="";
		$datepart="";
		if($resultparas['short_cache_link']){
			if($forcekalender || ($regardkalenderlinkpart && (substr($resultparas['srequri'], 0, 8) == "calendar" || substr($resultparas['srequri'], 0, 8) == "kalender"))) $kalender="calendar/";
			if ($thedate!="") $datepart = $thedate . '/';
			$datepagehref = $resultparas['baseurl'] . $kalender . $datepart;
		} else {
			if($forcekalender || ($regardkalenderlinkpart && preg_match('/[&\?][kc]alend[ae]r/',$resultparas['srequri']))) $kalender="calendar";
			if ($thedate!="") {
				$datepart = 'itemdate=' . $thedate;
				if($kalender != "") $kalender = "&" . $kalender;
			}
			$joiner='?';
			if(strstr($resultparas['baseurl'], '?')) $joiner = '&';
			$datepagehref= $resultparas['baseurl'] . $joiner . $datepart . $kalender;
		}
		return $datepagehref;
	}

//functions to generate boxes with content
	function formatted_feedlist(&$resultparas, $dateformat="", $template=false){
		global $bdprss_db;
		
		//$liststyle shall be one of sidebar, feedlist, opml
		$maxage=$resultparas['feedlistmaxage'];  //to be done in parameter definition, 0 means filter disabled, age in seconds
		$list_id=$resultparas['listid'];
		
		$split_template=PBALIB::split_template($template, '###INSIDELOOP###');

		if($resultparas['pba_feedlistquery_cache_time'] > 0) {
			$key=array($maxage, $list_id);
			$feedlistcache=@PBALIB::pba_cache($key, $dummy, 'get', 'feedlist', 'mixed', $resultparas['pba_feedlistquery_cache_time'], 'OK');
			if($feedlistcache[1]) {
				if($resultparas["profiler_enabled"]) $pbaout['profiler']['Got feedlist cache']=microtime();
				$result = $feedlistcache[0];
			}
		}
		if(!$feedlistcache[1]){
			if($resultparas["profiler_enabled"]) $pbaout['profiler']['Start query for feedlist']=microtime();
			$result = $bdprss_db->getsiteswithupdatetime($maxage, $list_id);
			if($resultparas['pba_feedlistquery_cache_time'] > 0) {
				$pba_cachereturn=PBALIB::pba_cache($feedlistcache[0], $result, 'write', 'feedlist', 'mixed', 0, 'OK');
			}
		}
		foreach($result as $r) {
			//$feedlist_loop['feedlist_loop_site_id'] = $r->{$bdprss_db->cidentifier};
			$feedlist_loop['feedlist_loop_feedurl'] = $r->{$bdprss_db->cfeedurl};
			$feedlist_loop['feedlist_loop_siteurl'] = $r->{$bdprss_db->csiteurl};
			$feedlist_loop['feedlist_loop_site'] = $r->{$bdprss_db->csitename};
			$updated = $r->lastupdate;
			if(strlen($dateformat . "") >0){
				$feedlist_loop['feedlist_loop_date']=date($dateformat, $updated);
			}else{
				$feedlist_loop['feedlist_loop_date']=PBALIB::gettheage($updated, $resultparas['ageunit'], $resultparas['ageunitsstring']);
			}
			//build up variables for the inner template - here is potential for optimization - resul from test: optimization potential minimal - not measurable
			//we shall analyze the template above to find out, which variables we need in inner loop
			foreach($feedlist_loop as $key => $value){
			  $needle[$key]="###" . strtoupper($key) . "###";
			  $replace[$key]=$value;
			}
			$feedlist['insideloop'] .=str_replace($needle, $replace, $split_template['inloop']);
		}
		//now the outer template
		
		$feedlist['result'] =str_replace('###INSIDELOOP###', $feedlist['insideloop'], $split_template['aroundloop']);
		//print_r($feedlist);
		return $feedlist;
	}

	function formatkalender(&$resultparas, $thedate="", &$pbaout, $longtype="kalenderlist_box", $template=""){
		global $bdprss_db;

		if($resultparas["profiler_enabled"]) { $type="list"; if($longtype == 'kalender_box') $type="box"; }
		if($resultparas['pba_kalenderquery_cache_time'] > 0) {
			$key=array(substr($resultparas['archivdate'],0,7),$resultparas['listid']);
			$kalendercache=@PBALIB::pba_cache($key, $dummy, 'get', 'kalender', 'mixed', $resultparas['pba_kalenderquery_cache_time'], 'OK');
			if($kalendercache[1]) {
				if($resultparas["profiler_enabled"]) $pbaout['profiler']['Got '. $type .' kalender cache']=microtime();
				$kalenderdates = $kalendercache[0];
			}
		}
		if(!$kalendercache[1]){
			if($resultparas["profiler_enabled"]) $pbaout['profiler']['Start query for ' . $type . ' kalenderdates']=microtime();
			$kalenderdates=$bdprss_db->getmonthlyarchivedates($resultparas['archivdate'],$resultparas['listid']);
			if($resultparas['pba_kalenderquery_cache_time'] > 0) {
				$pba_cachereturn=PBALIB::pba_cache($kalendercache[0], $kalenderdates, 'write', 'kalender', 'mixed', 0, 'OK');
			}
		}

		if($resultparas["profiler_enabled"]) $pbaout['profiler']['Start to format ' . $type . ' kalender']=microtime();

		//check input values
		if (!ereg("[0-9][0-9][0-9][0-9]-[0-1][0-9]-[0-3][0-9]", $thedate)) $thedate = date('Y-m-d');
		
		//parameter for all kalender types
		$dateformatinnormallinktext=$resultparas['kalendernormaldateformat'];
		$kalendermonthslist=$resultparas['kalendermonthslist'];

		//get template for basic calendar and loop through to find subtemplates for earlierlink and laterlink
		if($longtype == 'kalenderlist_box'){
			$lastlinktpl=PBALIB::split_template($template, '###KALENDER_LASTLINK###','EARLIERLINK');
			$lastlinktemplate=$lastlinktpl['inloop'];
			$nextlinktpl=PBALIB::split_template($lastlinktpl['aroundloop'], '###KALENDER_NEXTLINK###','LATERLINK');
			$nextlinktemplate=$nextlinktpl['inloop'];
			$split_template=PBALIB::split_template($nextlinktpl['aroundloop'], '###INSIDELOOP###');
		}

		//calculate some values from paras
		$thetimestamp=mktime(0, 0, 0, substr($thedate, 5, 2), substr($thedate, 8, 2), substr($thedate, 0, 4));
		$kalendermonths=explode(",", preg_replace('/\s/','',$kalendermonthslist));
		$formattedkalender['kalenderdate']=$kalendermonths[(substr($thedate, 5, 2) - 1)] . ' ' . substr($thedate, 0, 4);

		//value initialization
		$formattedkalender['insideloop']="";

		//format the body, if not box, else, we need the routine to check later, if a value for a date exists
		foreach($kalenderdates as $k) {
			if($k->type == 'last') {
				$formattedkalender['kalender_lastlink'] = "";
				if ($k->item_date != '0') {
					$lasthref=PBALIB::makedatepagehref($k->item_date, $resultparas, true);
					if($longtype == 'kalenderlist_box') $formattedkalender['kalender_lastlink'] = str_replace('###KALENDER_LASTHREF###',$lasthref, $lastlinktemplate);
				}
			} elseif($k->type == 'next'){
				$formattedkalender['kalender_nextlink'] = "";
				if ($k->item_date != '0') {
					$nexthref=PBALIB::makedatepagehref($k->item_date, $resultparas, true);
					if($longtype == 'kalenderlist_box') $formattedkalender['kalender_nextlink'] = str_replace('###KALENDER_NEXTHREF###',$nexthref,$nextlinktemplate);
				}
			}elseif($k->type == 'normal') {
				$inkalenderloop[$k->item_date]['kalenderloop_href']=PBALIB::makedatepagehref($k->item_date, $resultparas);
				$kalenderloop_timestamp=mktime(0, 0, 0, substr($k->item_date, 5, 2), substr($k->item_date, 8, 2), substr($k->item_date, 0, 4));
				$inkalenderloop[$k->item_date]['kalenderloop_inlinkdate']=date($dateformatinnormallinktext,$kalenderloop_timestamp);
				if($longtype=="kalenderlist_box"){
					foreach($inkalenderloop[$k->item_date] as $key => $value){
					  $needle[$key]="###" . strtoupper($key) . "###";
					  $replace[$key]=$value;
					}
					$formattedkalender['insideloop'] .= str_replace($needle, $replace, $split_template['inloop']);
				}
			}
		}

		if($longtype=="kalenderlist_box"){
			foreach($formattedkalender as $key => $value){
			  $needle[$key]="###" . strtoupper($key) . "###";
			  $replace[$key]=$value;
			}
			if($resultparas["debug"]) $formattedkalender['debug'] .= "\n<br>Debug: we understand the template keywords: " . PBALIB::get_r($needle); 
			$formattedkalender['kalenderlist_box']= str_replace($needle, $replace, $split_template['aroundloop']);
			$formattedkalender['result']=$formattedkalender['kalenderlist_box'];
		}
		if($longtype=="kalender_box"){
			//get templates and other parameters for box calendar
			$kalenderdaysofweeklist=$resultparas['kalenderboxdaysofweeklist'];
			$kalenderdaysofweek=explode(",", preg_replace('/\s/','',$kalenderdaysofweeklist));
			$kalendertablecaption=$resultparas['kalenderboxtablecaption'];
			$formattedkalender['kalender_last']=$resultparas['kalender_last'];
			$formattedkalender['kalender_next']=$resultparas['kalender_next'];

			//calculating some special values
			$firstsecondofthemonth=mktime(0, 0, 0, substr($thedate,5,2), 1, substr($thedate,0,4));
			$weekdayoffirstsecondofthemonth=date('N', $firstsecondofthemonth);
			$offsetdays=$weekdayoffirstsecondofthemonth-1;
			$firstsecondofnextmonth=mktime(0, 0, 0, ( substr($thedate,5,2) +1 ), 1, substr($thedate,0,4));

			$formattedkalender['kalender_box']="<table".$kalendertablecaption."><caption>" . $formattedkalender['kalenderdate'] . "</caption>\n";
			$formattedkalender['kalender_box'].='	<thead><tr>';
			foreach($kalenderdaysofweek as $dayofweeknumber => $dayofweek){
				$formattedkalender['kalender_box'].= '<th abbr="'.$dayofweek.'" scope="col" title="'.$dayofweek.'">'.substr($dayofweek,0,1).'</th>';
			}
			$formattedkalender['kalender_box'].= '</tr></thead>' . "\n";
			$formattedkalender['kalender_box'].='	<tfoot><tr>';
			if($lasthref) {
				$formattedkalender['kalender_box'].='<td abbr="'.$formattedkalender['kalender_last'].'" colspan="3" id="prev"><a href="'.$lasthref.'" title="'.$formattedkalender['kalender_last'].'">'.$formattedkalender['kalender_last'].'</a></td>';
			} else {
				$formattedkalender['kalender_box'].='<td colspan="3" id="prev">&nbsp;</td>';
			}
			$formattedkalender['kalender_box'].='<td>&nbsp;</td>';
			if($nexthref) {
				$formattedkalender['kalender_box'].='<td abbr="'.$formattedkalender['kalender_next'].'" colspan="3" id="next"><a href="'.$nexthref.'" title="'.$formattedkalender['kalender_next'].'">'.$formattedkalender['kalender_next'].'</a></td>';
			} else {
				$formattedkalender['kalender_box'].='<td colspan="3" id="next">&nbsp;</td>';
			}
			$formattedkalender['kalender_box'].='</tr></tfoot><tbody>' . "\n";

			//loop through the rows
			$startdate=date('Y-m-d',mktime(0, 0, 0, substr($thedate,5,2), (1 - $offsetdays), substr($thedate,0,4)));
			while(mktime(0, 0, 0, substr($startdate,5,2), substr($startdate,8,2), substr($startdate,0,4)) < $firstsecondofnextmonth){
				$formattedkalender['kalender_box'].='<tr>';
				foreach($kalenderdaysofweek as $dayofweeknumber => $dayofweek){
					$blank = "";
					$daynumber=(substr($startdate,8,2)+0);
					if($daynumber < 10) $blank = "&nbsp;";
					if(isset($inkalenderloop[$startdate]['kalenderloop_href'])){
						//there exists a link due to db entry, so we want to link it
						$formattedkalender['kalender_box'].='<td>'.$blank.'<a title="'.$inkalenderloop[$startdate]['kalenderloop_inlinkdate'].'" href="'.$inkalenderloop[$startdate]['kalenderloop_href'].'">'. $daynumber . '</a></td>';
					}elseif(substr($startdate,0,7) == substr($thedate,0,7)){
						//startdate in current month
						$formattedkalender['kalender_box'].='<td>'. $blank . $daynumber . '</td>';
					}else{
						$formattedkalender['kalender_box'].='<td>&nbsp;</td>';
					}
					$startdate=date('Y-m-d',mktime(0, 0, 0, substr($startdate,5,2), (substr($startdate,8,2) + 1), substr($startdate,0,4)));
				}
				$formattedkalender['kalender_box'].='</tr>';
			}
			$formattedkalender['kalender_box'].='</tbody></table>';
			$formattedkalender['result']=str_replace('###KALENDERBOX###',$formattedkalender['kalender_box'], $template);
		}
		
		if($resultparas["profiler_enabled"]) $pbaout['profiler']['End of format  ' . $type . ' kalender']=microtime();
		return $formattedkalender;
	}

//helper functions

		function analysepagetype(&$resultparas){
		// we have pages with templates:
		// 1. tickerpage - a tickerpage is a basic output list page
		
		if($resultparas['archivdate'] == "" 
			&& $resultparas['searchphrase']== ""
			&& $resultparas['feedrequest']==false
			&& $resultparas['cacheid']==false
			&& $resultparas['kalreq']==false
			&& $resultparas['tickerpage']>1
			&& !$resultparas['displayonlybox'] ) {
				$pagetype['pagetype']='tickerpage';
				$pagetype['template']='ticker';
		}

		// 2. searchpage - almost same as tickerpage, but with search specifica
		if($resultparas['archivdate'] == "" 
			&& $resultparas['searchphrase']!= ""
			&& $resultparas['feedrequest']==false
			&& $resultparas['cacheid']==false
			&& $resultparas['kalreq']==false
			&& !$resultparas['displayonlybox']) {
				$pagetype['pagetype']='searchpage';
				$pagetype['template']='ticker';
		}

		// 3. datepage - almost same as tickerpage, but with datepage specifica
		if($resultparas['archivdate'] != "" 
			&& $resultparas['searchphrase']== ""
			&& $resultparas['feedrequest']==false
			&& $resultparas['cacheid']==false
			&& $resultparas['kalreq']==false
			&& !$resultparas['displayonlybox'] ) {
				$pagetype['pagetype']='datepage';
				$pagetype['template']='ticker';
		}

		// 8. startpage - almost a tickerpage, but headers can be suppressed and a special uri can be assigned
		if($resultparas['archivdate'] == "" 
			&& $resultparas['searchphrase']== ""
			&& $resultparas['feedrequest']==false
			&& $resultparas['cacheid']==false
			&& $resultparas['kalreq']==false
			&& $resultparas['tickerpage']==1
			&& !$resultparas['displayonlybox'] ) {
				$pagetype['pagetype']='startpage';
				$pagetype['template']='ticker';
		}
		// these pagetypes above may use the same template with a preprocessor doing some modifications?

		// 5. feedpage - a ticker- no search- no date - page with diffrent output preprocessing and diffrent template
		if($resultparas['archivdate'] == "" 
			&& $resultparas['searchphrase']== ""
			&& $resultparas['feedrequest']==true
			&& $resultparas['cacheid']==false
			&& $resultparas['kalreq']==false
			&& !$resultparas['displayonlybox'] ) {
				$pagetype['pagetype']='feedpage';
				$pagetype['template']='feed';
		}

		// 5a. search feedpage - a feed ticker- with search - page 
		if($resultparas['archivdate'] == "" 
			&& $resultparas['searchphrase']!= ""
			&& $resultparas['feedrequest']==true
			&& $resultparas['cacheid']==false
			&& $resultparas['kalreq']==false
			&& !$resultparas['displayonlybox'] ) {
				$pagetype['pagetype']='feedsearchpage';
				$pagetype['template']='feed';
		}

		// 5b. date feedpage - a feed ticker- with date - page 
		if($resultparas['archivdate'] != "" 
			&& $resultparas['searchphrase']== ""
			&& $resultparas['feedrequest']==true
			&& $resultparas['cacheid']==false
			&& $resultparas['kalreq']==false
			&& !$resultparas['displayonlybox'] ) {
				$pagetype['pagetype']='feeddatepage';
				$pagetype['template']='feed';
		}

		// 7. cache page - a tickerpage with diffrent template and just one item requested
		//these pages also use the main loop via search engine
		//information which page was requested is in url, but limited by other config parameters
		if($resultparas['archivdate'] == "" 
			&& $resultparas['searchphrase']== ""
			&& $resultparas['feedrequest']==false //maybe have a cache page delivered as feed?
			&& $resultparas['cacheid']> 0
			&& $resultparas["iscachable"]=="Y"
			&& $resultparas['kalreq']==false
			&& !$resultparas['displayonlybox'] ) {
				$pagetype['pagetype']='cachepage';
				$pagetype['template']='cache';
		}

		// 4. kalenderpage - a special page with no content but showing all our navigation and boxes 
		if( $resultparas['searchphrase']== "" 
			&& $resultparas['feedrequest']==false 
			&& $resultparas['cacheid']==false 
			&& $resultparas['kalreq']==true 
			&& !$resultparas['displayonlybox']) { 
				$pagetype['pagetype']='kalpage'; 
				$pagetype['template']='kalender'; 
		}

		// 9. nopage - maybe just a box was called? - great we could save a lot of time, when we know, that we don't need to process a page template
		if($resultparas['feedrequest']==false
			&& $resultparas['displayonlybox'] // just a box
			) {
				$pagetype['pagetype']='nopage';
				$pagetype['template']= $resultparas['displayonlybox']; 
				// one of 'kalender_box', 'kalenderlist_box', 'search_box', 'feedlistsidebar_box', 'opml_box', 'feedlist_box', 'feedlistsidebar_box' 
		}

		//10. errorpage - probably nobody wants this page, but maybe our system is not ready or it is not understandable what the user wanted
		if(!isset($pagetype['pagetype']) || !isset($pagetype['template'])
			) {
				$pagetype['pagetype']='errorpage';
				$pagetype['template']='error';
				$pagetype['error'].='This error never should happen. Sorry for this. Cannot determine page type or template';
		}
	return $pagetype;
	}

	function analyze_needed_elements($rawtemplate, $type=''){
		//avaliable_boxes: 'search_box, kalender_box, kalenderlist_box, feedlist_box, sendfeedpermail_box'; //do we really need this?
		preg_match_all('/###[0-9A-Z_]+'.$type.'###/', $rawtemplate, $elementname);
		//$element['boxname']=$elementname;
		foreach($elementname[0] as $key => $elementnameupper){
			$element['result'][str_replace('#','',strtolower($elementnameupper))]=true;
		}
		if(!isset($element['result'])) $element['result'] = false;
		return $element;
	}

	function makebox($boxtomake, &$resultparas, &$pbaout){
		$boxtemplate = $resultparas['template_' . $boxtomake]; //Fix this later to be a copy of resultparas
		$preprocessed_boxtemplate=PBALIB::template_replacements($boxtemplate, $resultparas);

		if($boxtomake == 'kalender_box' || $boxtomake == 'kalenderlist_box'){
			//this one is tricky: 
			//1. to query db only once, if we have diffrent kalenderboxes to make, we want them all make at once
			//2. we want to give some (?) other global results back like kalenderdate
			//formatted_kalender gives out: 
			//1. result - as specified with boxtomake
			//2. kalenderdate - needed later in page templates, 
			//3. maybe profiler, when profiler enabled set -> now directly sent to pbaout

				//we shall better work with db query results cache ? - nice to see in profiler: mysql does caching automatically very well
				$formatted_kalender=PBALIB::formatkalender($resultparas, $resultparas['archivdate'], $pbaout, $boxtomake, $preprocessed_boxtemplate);
				$box['result']=$formatted_kalender['result']; //completely unused so far?
				$pbaout['kalenderdate']=$formatted_kalender['kalenderdate'];

		}elseif($boxtomake == 'opml_box' || $boxtomake == 'feedlist_box' || $boxtomake == 'feedlistsidebar_box'){

			//we need to pass over dateformat for inside the loop
			$dateformat="";
			if (isset($resultparas['dateformat_' . $boxtomake])) $dateformat=$resultparas['dateformat_' . $boxtomake];
			$feedlist = PBALIB::formatted_feedlist($resultparas, $dateformat, $preprocessed_boxtemplate);
			$box['result']=$feedlist['result'];
		}else{
			//doing nothing special here is perfect for making a search box
			$box['result']=$preprocessed_boxtemplate; //this
		}

		return $box;
	}

	function processtagset($tagsetfromdb){
		//$bdprssTagSet: tags to possibly keep defined in programm code
    //$tagsetfromdb - the tags defined as allowed as defined per output id
    //$formattedtagset: the resulting tagset applied to the items in packageItemText
    //to do: encapsulate this into function and store it in database!!!
    global $bdprssTagSet;
		$formattedtagset = '';
		if($tagsetfromdb){
			$kts = preg_split("','", $tagsetfromdb, -1, PREG_SPLIT_NO_EMPTY);
			foreach($kts as $t){
				$u = $bdprssTagSet[$t];
				foreach($u as $v)
					$formattedtagset .= "$v,";
			}
		}
		return $formattedtagset;
	}

	function gettheage($seconds, &$resultparas=false, &$ageunitsstring=false){
	
		//this function is used inside inner loops and has to be quick
		//we set $resultparas['ageunit'] on first call with just a string and no array, 
		//so in following calls we won't have to loop

		if(is_array($resultparas['ageunit'])){
			$ageunit=$resultparas['ageunit'];
		}else{
			if(!$ageunitsstring) $ageunitsstring = get_pbadefaultparameter('ageunitsstring');
			$ageunitsarray = explode(",", $ageunitsstring);
			foreach($ageunitsarray as $valuepair){
				$valuepairarray=explode(":", $valuepair);
				$ageunit[trim($valuepairarray[0])]= trim($valuepairarray[1]);
			}
			$resultparas['ageunit']=$ageunit;
		}
		if($seconds < 100000) return $ageunit['never'];		// usually true :)
		
		$age = (time() - $seconds);
		if($age < 0) {
			$future = TRUE;
			$age = -$age;
		}
		
		$unit = $ageunit['seconds'];
		if($age>120.0) {
			$age /= 60;
			$unit = $ageunit['minutes'];
		}	
		
		if($age>120.0 && $unit==$ageunit['minutes']){
			$age /= 60;
			$unit = $ageunit['hours'];
		}	

		if($age>48.0 && $unit == $ageunit['hours']){
			$age /= 24;
			$unit = $ageunit['days'];
		}	
		
		if($age>21.0 && $unit==$ageunit['days']){
			$age /= 7;
			$unit = $ageunit['weeks'];
		}
		
		if($age>13.0 && $unit==$ageunit['weeks']) 
		{				
			$age /= 4.34821;
			$unit = $ageunit['months'];
		}
		
		if($age>=24.0 && $unit==$ageunit['months']){
			$age /= 12;
			$unit = $ageunit['years'];
		}
			
		$age = round($age, 0);
		if(!isset($future)) {
			if($ageunit['before'] != "") $ageunit['before'] = $ageunit['before'] . " ";
			if($ageunit['beforeafter'] != "") $ageunit['beforeafter'] = " " . $ageunit['beforeafter'];
			$return = $ageunit['before'] . "$age $unit" . $ageunit['beforeafter'];
		} else {
			$return = $ageunit['in'] . " $age $unit";
		}
		return $return;
	}

//pba_cache($identifier, $content, 'write', $name, 'mixed', 0, 'OK');
//pba_cache($identifier, $content, 'housekeeping', $name, 'mixed', 500, 'OK');
//pba_cache($identifier, $content, 'clear', $name, 'mixed', 180, 'OK');
//$cachereturn=pba_cache($identifier, $dummy, 'get', $name, 'mixed', 1800, 'OK');
//if($cachereturn[1]) echo "Here is what I got: " . print_r($cachereturn);
//if($cachereturn[0]) echo "Got no cache";

	function pba_cache(&$identifier, &$cache_content, $cache_mode='get', $name='c', $type='mixed', $cache_max_time=180, $serverstatus='OK'){
		$cache_file=false;
		$cache_path=PBA_CACHE_PATH;
		
		//if identifier is just a short string containing nothing else then a-zA-Z0-9_-, we will use it as part of filename instead of md5
		if(is_string($identifier)) {
			if(strlen($identifier)<=40){
				if(preg_match('/^[a-zA-Z0-9_-]+$/',$identifier)){
					$cache_file=$cache_path . $name . '_' . $identifier;
				}
			}
		}
		if(!$cache_file){
			$cache_file=$cache_path . $name . '_' . md5(serialize($identifier));
		}
	
	//to the filenames on disk will be appended:
	//__c for check files, __d for data files, __t for tmp files
	
		if($cache_mode=='write'){
			$wrote_cache=false;
			if ($serverstatus == 'OK' && !file_exists($cache_file."__t") && !(@filemtime($cache_file."__d") + $cache_max_time > time())){
				$cache_file_handle = fopen($cache_file."__t", 'w+');
				
				if($type=='string') {
					if(fwrite($cache_file_handle, $cache_content)) $wrote_cache=true;
				} else {
					if(fwrite($cache_file_handle, serialize($cache_content))) $wrote_cache=true;
				}
				fclose($cache_file_handle);
				rename($cache_file."__t",$cache_file."__d");
				@touch($cache_file."__c");
			}
			return $wrote_cache;
		}
		if($cache_mode=='get'){
			$got_cache=false;
			if (file_exists($cache_file."__c")){
				if( @filemtime($cache_file."__d") + $cache_max_time > time() && $serverstatus == 'OK' ) {
					if($type=='string') {
						if($get_cache=file_get_contents($cache_file."__d")) $got_cache=true;
					} else {
						if($get_cache=unserialize(file_get_contents($cache_file."__d"))) {
							$got_cache=true;
						}
					}
				}
			}
			if(!$got_cache) $get_cache = $identifier;
			return array($get_cache,$got_cache);
		}
	
		if($cache_mode=='housekeeping'){
			$counter=0;
			if($serverstatus == 'OK'){
				foreach (glob($cache_path . "*__?") as $filename) {
					if(preg_match('/__[cd]$/',$filename)){
		    		if(filemtime($filename) + $cache_max_time < time()) {
				    	unlink($filename);
		    			$counter++;
		    		}
		    	}
				}
			}
			return $counter;
		}
	
		if($cache_mode=='clear'){
			$counter=0;
			foreach (glob($cache_path . "*__?") as $filename) {
	    	unlink($filename);
	    	$counter++;
			}
			return $counter;
		}
	}
	

}// end class PBALIB
?>