<?php

	function get_pbadefaultparameter($parametername=''){

		$defaultparameter['outputid']=1;
		$defaultparameter['outputname']='New output: please give it a meaningful name';
		$defaultparameter['outputidfromurlallowed']=false;
		$defaultparameter['searchphrase']="";

		$defaultparameter['feedrequest']=false;
		$defaultparameter['tickerpage']="1";
		$defaultparameter['archivdate']="";
		$defaultparameter['maxitems']="N";
		$defaultparameter['listid']=false;
		$defaultparameter['listidfromurlallowed']=false;
		$defaultparameter['hookintorewriterules']='auto';
		$defaultparameter['short_cache_link']=false;
		$defaultparameter['kalreq']=false; //true means, kalenderpage with kalender headers is desired by url
		$defaultparameter['cacheid']=false;
		$defaultparameter['formattype']="countrecentitem";
		$defaultparameter['daterecentitemthreshold']=24 * 60 *60;
		$defaultparameter['fromtimestamp']=0;
		$defaultparameter['totimestamp']=0;
		$defaultparameter['opsfilter']=false;
		$defaultparameter['orderbysitename']=false;
		$defaultparameter['sitealphathreshold']=31 * 24 * 60 *60;

		$defaultparameter['getoutputconfigbypageid']=false;
		$defaultparameter['page2hookin']=0;

//additional parameter to simulate known Parteibuch behaviour
		$defaultparameter['specialpage1url']='N'; //'http://www.mein-parteibuch.com'; //set to N or to false to disable
		$defaultparameter['suppressheaderonpage1']='N'; //only 'Y' will enable this feature
		$defaultparameter['forceboxmaking']='';
		
//search related values
		$defaultparameter['searchenabled']=true;
		$defaultparameter['search_page_baseurl']=""; //if empty, baseurl will be taken
		$defaultparameter['searchactionhref'] = ""; //will be calculated in parameter bootstrap
		$defaultparameter['hiddensearchformformvalues'] = ""; //will be calculated in parameter bootstrap

//caching control
		$defaultparameter['pba_full_cache_time']= 0; //use 0 to disable full this cache
		$defaultparameter['pba_kalenderquery_cache_time']= 0; //use 0 to disable this cache
		$defaultparameter['pba_feedlistquery_cache_time']= 0; //use 0 to disable this cache

		//boxonly: if just a box shall be given out, name the box here
		//this single parameter shall replace parameters displayonlykalender, displayonlysearchbox, makeonlyfeedlist
		$defaultparameter['displayonlybox']=""; //unset, false and 'N' should also mean, that not only a box was requested

		//parameters just for historic reference, replaced just after parameter bootstrap, 
		$defaultparameter['displayonlysearchbox']='N'; ////to be completely replaced by parameter displayonlybox
		$defaultparameter['displayonlykalender']='N'; //to be completely replaced by parameter displayonlybox
		$defaultparameter['makeonlyfeedlist']=false; //to be completely replaced by parameter displayonlybox
		//just used, when ! displayonlybox ) 
		$defaultparameter['opmlrequest']=false; //to be mapped to parameter displayonlybox
		$defaultparameter['feedlistrequest']=false; //to be mapped to parameter displayonlybox

    //then all default parameters needed for output

		$defaultparameter['maxbodylength']=50;
		$defaultparameter['maxwordlength']=35;
		$defaultparameter['tagset']="";
		$defaultparameter['formattedtagset']=""; //to be calculated later from $defaultparameter['tagset']
		$defaultparameter['baseurl']="";
		$defaultparameter['noextralink']="N"; //"Y" will suppress the extra link at the end of the item body
		$defaultparameter['extralinktemplate']=" [<a href='###ITEM_URL###' >Link</a>]";
		$defaultparameter['append_cache_link']="Y";
		$defaultparameter['iscachable']="Y";
		$defaultparameter['itemdateformat']="\ \(d.m.Y H:i\h\)\:";
		$defaultparameter['splittitlebyseparator']=true; // separator shall not be clever chosen, but shall not be configurable, was before $useitemtitles
		$defaultparameter['cacheviewpage']="";
		$defaultparameter['cachelinktemplate']=" [<a href='###CACHEHREF###' rel='nofollow'>Cache</a>]";
		$defaultparameter['add_social_bookmarks']='Y';
		$defaultparameter['itemssbtemplate']='<script type="text/javascript">
document.write(\' [<a href="#" onclick="sbbview(\\\'sbbpt_id_###ITEM_ID###\\\',\\\'###ITEM_URL###\\\',\\\'###ITEM_NAME###\\\');return false;">Bookmarking</a>]<div id="sbbpt_id_###ITEM_ID###" class="social_bookmark" style="display:none;"></div>\');
</script>';
		$defaultparameter['headersbtemplate']='<script type="text/javascript"
src="###URL2PLUGINDIR###social_bookmarks.php">
</script>';
		$defaultparameter['url2plugindir']=""; // where to set such a value? this is a constant from wordpress and plugin installation
		$defaultparameter['feedpage']="";
		$defaultparameter['srequri']="";

	//widget values
		$defaultparameter['show_sidebarwidget'] = 'N';
		$defaultparameter['template_sidebarwidget'] = <<<HEREDOC
<li><b style="font-size: 1.2em;"><a title='Go to page of ###OUTPUTNAME###' href='###BASEURL###'>###OUTPUTNAME###</a><b> 
(<a title='Link to feed of ###OUTPUTNAME###' href='###FEEDHREF###'>xml</a>)</li> 

###LOOPBEGIN###<li><a href="###SITE_URL###" title="###SITE_DESCRIPTION###">###SITE_NAME###</a><small> (###ITEM_DATETIME###):</small> <b><a href="###ITEM_URL###">###ITEM_NAME###</a></b></li>
###LOOPEND###

<li><b>Feedroll</b> (<a title='List of feeds of ###OUTPUTNAME### as OPML' href='###FEEDOPMLHREF###'>opml</a>) (<a  title='List of feeds of ###OUTPUTNAME###' href='###FEEDLISTHREF###'>feedlist</a>)</li>

###FEEDLISTSIDEBAR_BOX###

<li style="text-align: center;"><br/>###SEARCH_BOX###</li>

<li style="text-align: center;"><br/><small><a title="The Parteibuch Aggregator is a Wordpress-Plugin for feed aggregation" href="http://www.mein-parteibuch.com/blog/parteibuch-aggregator/">###PBA_VERSION###</a></small></li>
HEREDOC;

	//cache page values
		$defaultparameter['template_cache'] = <<<HEREDOC
<h2>Cache-Eintrag ###ITEM_ID### im <a href='###BASEURL###'>Parteibuch Aggregator</a></h2>
	<div class='centreblock'><h3><a href='###ITEM_URL###'>###ITEM_NAME###</a></h3>
	###ITEM_CACHEBODY###
	<p class='postbyline'><em> By: <a href='###SITE_URL###' title='###SITE_DESCRIPTION###'>###ITEM_URL###</a>
	<br />Posted: ###ITEM_DATEFEED### (ca.)
	<br />RSS cached: ###ITEM_UPDATEDATE###
	<br />Aggregator: <a title="The Parteibuch Aggregator is a Wordpress-Plugin for feed aggregation" href="http://www.mein-parteibuch.com/blog/parteibuch-aggregator/">###PBA_VERSION###</a>
</em></p></div>
HEREDOC;

	//feed values
		$defaultparameter['channel_title']= "Parteibuch Aggregator Feed";
		$defaultparameter['htmlpage']=""; //field channel_link in database, if empty, post->guid from wordpress will be used
		$defaultparameter['channel_description']= "A fine feed collection generated with Parteibuch Aggregator";
		$defaultparameter['channel_language']= "en";
		$defaultparameter['channel_copyright']= "Pirate license";
		$defaultparameter['template_feed'] = <<<HEREDOC
<?xml version="1.0" encoding="UTF-8"?>
<!-- generator="###PBA_VERSION###" -->
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	>
<channel>
	<title>###CHANNEL_TITLE######ISSEARCHBEGIN### - Items containing ###SEARCHPHRASE######ISSEARCHEND######ISDATEBEGIN### - Items from date ###ARCHIVEDATE######ISDATEEND###</title>
	<link>###HTMLHREF###</link>
	<description>###CHANNEL_DESCRIPTION###</description>
	<pubDate>###FIRSTITEM_DATEFEED###</pubDate>
	<generator>###PBA_VERSION###</generator>
	<language>###CHANNEL_LANGUAGE###</language>
	<copyright>###CHANNEL_COPYRIGHT###</copyright>
###LOOPBEGIN###	<item>
		<title>###SITE_NAME###: ###ITEM_NAME###</title>
		<link>###ITEM_URL###</link>
		<pubDate>###ITEM_DATEFEED###</pubDate>
		<dc:creator>###SITE_NAME###</dc:creator>
		<dc:source>###SITE_URL###</dc:source>
		<dc:rights><![CDATA[###ITEM_LICENSE###]]></dc:rights>
		<guid isPermaLink="true">###ITEM_URL###</guid>
		<description><![CDATA[###ITEM_DESCRIPTION###]]></description>
		<content:encoded><![CDATA[###ITEM_FEEDBODY###]]></content:encoded>
	</item>
###LOOPEND###</channel>
</rss>
HEREDOC;


	//kalender values
		$defaultparameter['template_kalender'] = <<<HEREDOC
<p><a href='###BASEURL###'>PBA</a>: Archive for ###KALENDERDATE###</p>
<p>###KALENDER_BOX###</p>
<p>###SEARCH_BOX###</p>  
<p>(<a href='###FEEDLISTHREF###'>Feeds</a>) (<a href='###FEEDOPMLHREF###'>OPML</a>) </p> <p>###FEEDLISTSIDEBAR_BOX###</p>
<p>###KALENDERLIST_BOX###</p>

<p>Generated with <a title="The Parteibuch Aggregator is a Wordpress-Plugin for feed aggregation" href="http://www.mein-parteibuch.com/blog/parteibuch-aggregator/">###PBA_VERSION###</a></p>
HEREDOC;

		$defaultparameter['kalender_as_list']=false; // nasty - true shall mean, that we will calculate a kalender as a list, whatever template analysis says
		$defaultparameter['kalender_as_box']=false; // nasty - true shall mean, that we will calculate a kalender as a list, whatever template analysis says
		$defaultparameter['kalendermonthslist']="January, February, March, April, May, June, July, August, September, October, November, December";
		$defaultparameter['kalendernormaldateformat']='j.n.Y';

		//get parameters for box calendar
		$defaultparameter['kalender_last']='Earlier'; //only needed for kalender as a box
		$defaultparameter['kalender_next']='Later'; //only needed for kalender as a box
		$defaultparameter['kalenderboxdaysofweeklist']="Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday";
		$defaultparameter['kalenderboxtablecaption']=' style="text-align: center;" id="kalendertable"';

		//feedlist values
		$defaultparameter['feedlistmaxage'] = (31 * 24 * 60 *60); //age of feeds to be listed in feedlist, in seconds, 0 means disabled age filter
		$defaultparameter['dateformat_feedlistsidebar_box']='';
		$defaultparameter['dateformat_feedlist_box']='';
		$defaultparameter['dateformat_opml_box']='';

		//cache page format values

		//get age function value
		$defaultparameter['ageunitsstring']="never: never, seconds: seconds, minutes: minutes, hours: hours, days: days, weeks: weeks, months: months, years: years, before: , beforeafter: ago,in: in";
		$defaultparameter['ageunit']=false; //leave this false, gettheage will calculate an array from into it on first call

    //boundary values
		$defaultparameter['maxitemslimit']=1000;

		//here are some default templates
		$defaultparameter['template_ticker'] = <<<HEREDOC
<p>
###ISSEARCHBEGIN###
<a href="###BASEURL###">###OUTPUTNAME###</a>: Items containing ###SEARCHPHRASE### 
###ISSEARCHEND###

###ISDATEBEGIN###
<a href="###BASEURL###">###OUTPUTNAME###</a>: Items from date ###ARCHIVEDATE### 
###ISDATEEND###

###ISNODATENOSEARCHBEGIN###
<a href="###BASEURL###">###OUTPUTNAME###</a>
###ISNODATENOSEARCHEND###

(###STARTITEM### - ###LASTITEM### of about ###FOUNDITEMS###) (<a href='###FEEDHREF###'>xml</a>) (<a href='###FEEDLISTHREF###'>Feedlist</a>)</p>

<p>###SEARCH_BOX###</p>

###LOOPBEGIN###<p class="centreblock"><a href="###SITE_URL###" title="###SITE_DESCRIPTION###">###SITE_NAME###</a><small> (###ITEM_DATETIME###):</small> <b><a href="###ITEM_URL###">###ITEM_NAME###</a></b><br /><div class="storyContent">###ITEM_BODY###</div></p>
###LOOPEND###

<p><span style='position:relative; float:left; overflow:hidden; margin-left:10px;'>###LASTLINKBEGIN###<a href="###LASTPAGEHREF###">Page back</a>###LASTLINKEND###</span><span style='position:relative; float:left; overflow:hidden; margin-left:30px;'>Item (###STARTITEM### - ###LASTITEM### of about ###FOUNDITEMS###)</span><span style='position:relative; float:right; overflow:hidden; margin-right:10px;'>###NEXTLINKBEGIN###<a href="###NEXTPAGEHREF###">Next page</a>###NEXTLINKEND###</span></p>

<p>&nbsp;</p>
<p>###KALENDERLIST_BOX###</p>
<p style="text-align: center;">Generiert mit <a title="The Parteibuch Aggregator is a Wordpress-Plugin for feed aggregation" href="http://www.mein-parteibuch.com/blog/parteibuch-aggregator/">###PBA_VERSION###</a></p>
HEREDOC;

		$defaultparameter['template_feedlist'] = <<<HEREDOC
###LOOPBEGIN######FEEDLIST_LOOP_FEEDURL### ###LOOPEND###
HEREDOC;

		$defaultparameter['template_opml'] = <<<HEREDOC
		<?xml version="1.0" encoding="UTF-8"?>
<opml version="1.1">
	<head>
<title>Parteibuch Aggregator OPML</title>

<dateCreated>###NOW###</dateCreated>
<ownerName>PBA</ownerName>
</head>
	<body>
	###LOOPBEGIN###	<outline title="###FEEDLIST_LOOP_SITE###" text="###FEEDLIST_LOOP_SITE###" htmlUrl="###FEEDLIST_LOOP_SITEURL###" type="rss" xmlUrl="###FEEDLIST_LOOP_FEEDURL###" />
###LOOPEND###</body>
</opml>
HEREDOC;

		$defaultparameter['template_error'] = <<<HEREDOC
Oops! We have got errors: ###ERRORMESSAGE###
HEREDOC;

//search box
		$defaultparameter['template_search_box'] = <<<HEREDOC
<form method="get" id="searchform" action="###SEARCHACTIONHREF###">###HIDDENSEARCHFORMFORMVALUES###
		<input type="text" value="###SEARCHPHRASE###" name="searchphrase" id="searchphrase" />
			<input type="submit" id="searchsubmit" value="search aggregator" /></form>
HEREDOC;

		$defaultparameter['template_opml_box'] = <<<HEREDOC
<?xml version="1.0" encoding="UTF-8"?>
<opml version="1.1">
	<head>
<title>Parteibuch Aggregator OPML</title>
<dateCreated>###NOW###</dateCreated>
<ownerName>PBA</ownerName>
</head>
	<body>
	###LOOPBEGIN###	<outline title="###FEEDLIST_LOOP_SITE###" text="###FEEDLIST_LOOP_SITE###" htmlUrl="###FEEDLIST_LOOP_SITEURL###" type="rss" xmlUrl="###FEEDLIST_LOOP_FEEDURL###" />
###LOOPEND###</body>
</opml>
HEREDOC;

		$defaultparameter['template_feedlist_box'] = <<<HEREDOC
###LOOPBEGIN######FEEDLIST_LOOP_FEEDURL### ###LOOPEND###
HEREDOC;

		$defaultparameter['template_feedlistsidebar_box'] = <<<HEREDOC
###LOOPBEGIN###<li style="margin: 2px 0 0;"><small><a href='###FEEDLIST_LOOP_SITEURL###' 
title='###FEEDLIST_LOOP_SITE###'>###FEEDLIST_LOOP_SITE###</a>&nbsp;<a href='###FEEDLIST_LOOP_FEEDURL###' 
title='Feed - Last update: ###FEEDLIST_LOOP_DATE###'>(xml)</a></small></li>
###LOOPEND### 
HEREDOC;

		$defaultparameter['template_kalenderlist_box'] = <<<HEREDOC
<a title="Archive" href="###KALENDERHREF###">Archive</a>:&nbsp;
	###EARLIERLINKBEGIN###<a href='###KALENDER_LASTHREF###'>Earlier</a>###EARLIERLINKEND### 
	###LOOPBEGIN###<a href='###KALENDERLOOP_HREF###'>###KALENDERLOOP_INLINKDATE###</a> &nbsp###LOOPEND###
	###LATERLINKBEGIN###<a href='###KALENDER_NEXTHREF###'>Later</a>###LATERLINKEND### 
	<br />
HEREDOC;

		$defaultparameter['template_kalender_box'] = <<<HEREDOC
###KALENDERBOX###
HEREDOC;

    //migration && debug values
		$defaultparameter['forceoldoutput']=false;
		$defaultparameter['debug']=false;
		$defaultparameter['profiler_enabled']=false;
		
		if($parametername=="") {
			return $defaultparameter;
		} else {
			if(isset($defaultparameter[$parametername])) {
				$returnparameter = $defaultparameter[$parametername];
				return $returnparameter;
			}
		}
	} //end of function getdefaultparameter

	function get_pbadefaultsite($parametername=''){
		//feed_url, site_name , description , site_license , site_url
		$defaultparameter['feed_url']='http://www.mein-parteibuch.org/blog/the-cats-news-ticker-feed/';
		$defaultparameter['site_name']='The Cats News Ticker';
		$defaultparameter['description']='A Cats Feedmix';
		$defaultparameter['site_license']='Pirate license';
		$defaultparameter['site_url']='http://www.mein-parteibuch.org/';
		if($parametername=="") {
			return $defaultparameter;
		} else {
			if(isset($defaultparameter[$parametername])) {
				$returnparameter = $defaultparameter[$parametername];
				return $returnparameter;
			}
		}
	} //end of function get_pbadefaultsite

?>