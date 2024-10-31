<?php 

if ($user_level < 6) die ( __('Cheatin&#8217; uh?') ); 

global $bdprss_db;
$result = $bdprss_db->get_all_options();

//initialisation
$pba_enable_caching="auto";
$pba_enable_rewriting="Y";
$pba_full_cache_time=60;
$pba_kalenderquery_cache_time=7200;
$pba_feedlistquery_cache_time=3600;
$pba_options_enable_loaddetection="auto";
$pba_highloadthreshold="3";

if(!$result ) {
	echo "<p><b>Unexpected error:</b> could not find feed $rss, or it differed from $cidentifier</p>\n";
} else {
	foreach($result as $key => $r){
		if($r->name == 'enable_caching') $pba_enable_caching=$r->value;
		if($r->name == 'enable_rewriting') $pba_enable_rewriting=$r->value;
		if($r->name == 'full_cache_time') $pba_full_cache_time=$r->value;
		if($r->name == 'kalenderquery_cache_time') $pba_kalenderquery_cache_time=$r->value;
		if($r->name == 'feedlistquery_cache_time') $pba_feedlistquery_cache_time=$r->value;
		if($r->name == 'enable_loaddetection') $pba_options_enable_loaddetection=$r->value;
		if($r->name == 'highloadthreshold') $pba_highloadthreshold=$r->value;
	}

//	print_r($result);
//	echo $pba_enable_caching;

	echo '<h2>Edit some global options of the <a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php">Parteibuch Aggregator</a> plugin</h2>';
?>


<fieldset>

<form method="post" action="<?php echo $selfreference; ?>">

	<table width="100%" cellspacing="2" cellpadding="5">

<tr valign="top">
	<td align="left" colspan="3"><h3>Caching</h3>
	
	Info: Sensible caching can speed up the Parteibuch Aggregator and reduce the server load<br><br>
	
		<?php 
		if($bdprss_db->serverstatus['pbacache']['status'] != 'ok') echo '(<b>Attention</b>: Caching can only work, if your cache directory ' . PBA_CACHE_PATH . ' is writable for your webserver. You may do this with your ftp client by setting permissions of the cache directory to read, write, execute for all, ie "chmod 777 pbacache")<br/>'; 
		echo '<br/>';
		?>
		</td>
</tr>
<tr valign="top">
	<td width="25%">
		Enable Caching?
		<br />&nbsp;<br />&nbsp;<br />
	</td>
	<td width="25%">
<select name="pba_options_enable_caching">
<?php 
$namedescarray=array('Y' => 'Yes', 'N' => 'No', 'auto' => 'Automatic&nbsp;&nbsp;');
foreach($namedescarray as $key => $value){
$selected="";
if($pba_enable_caching == $key ) $selected = ' selected';
	echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
}
echo '</select></td><td width="50%" align="left">'; 
$boldcacheinfo=array('','');
if($bdprss_db->serverstatus['pbacache']['status'] == 'unusable') $boldcacheinfo=array("<b>","</b>");
echo $boldcacheinfo[0] . "Memory info for 'pbacache': " . $bdprss_db->serverstatus['pbacache']['status'] . ", Notice: " . $bdprss_db->serverstatus['pbacache']['notice'] . $boldcacheinfo[1]; 

?>

</td></tr>

<tr valign="top">
	<td>
		Cache time for main loop
		<br />&nbsp;<br />&nbsp;<br />
	</td>
	<td>
			<input type="text" name="pba_full_cache_time" value="<?php echo $pba_full_cache_time; ?>" size="10" />
			<br/>(In seconds)<br/>
<?php echo '</td><td align="left">'; 
if(isset($bdprss_db->serverstatus['full_cache_time']['status'])){
	echo "Memory info for 'full_cache_time': " . $bdprss_db->serverstatus['full_cache_time']['status'] . ", Notice: " . $bdprss_db->serverstatus['full_cache_time']['notice'] . '<br />'; 
}
?>
This cache is a file cache around the whole output workhorse routine. Zero (0) will disable this cache.  Default value is 60 seconds.
</td></tr>

<tr valign="top">
	<td>
		Cache time for calendar query
		<br />&nbsp;<br />&nbsp;<br />
	</td>
	<td>
			<input type="text" name="pba_kalenderquery_cache_time" value="<?php echo $pba_kalenderquery_cache_time; ?>" size="10" />
			<br/>(In seconds)<br/>
<?php echo '</td><td align="left">'; 
if(isset($bdprss_db->serverstatus['kalenderquery_cache_time']['status'])){
	echo "Memory info for 'kalenderquery_cache_time': " . $bdprss_db->serverstatus['kalenderquery_cache_time']['status'] . ", Notice: " . $bdprss_db->serverstatus['kalenderquery_cache_time']['notice'] . '<br />'; 
}
?>
This cache is a file cache around the query to build calendars. Zero (0) will disable this cache. Default value is 7200 seconds.
</td></tr>

<tr valign="top">
	<td>
		Cache time for the feedlist query
		<br />&nbsp;<br />&nbsp;<br />
	</td>
	<td>
			<input type="text" name="pba_feedlistquery_cache_time" value="<?php echo $pba_feedlistquery_cache_time; ?>" size="10" />
			<br/>(In seconds)<br/>
<?php echo '</td><td align="left">'; 
if(isset($bdprss_db->serverstatus['feedlistquery_cache_time']['status'])){
	echo "Memory info for 'feedlistquery_cache_time': " . $bdprss_db->serverstatus['feedlistquery_cache_time']['status'] . ", Notice: " . $bdprss_db->serverstatus['feedlistquery_cache_time']['notice'] . '<br />'; 
}
?>
This cache is a file cache around the query to build feedlists. Zero (0) will disable this cache.  Default value is 3600 seconds.
</td></tr>

<tr valign="top">
	<td >
		Clear the cache now?
	</td>
	<td>
		<input type="checkbox" name="pba_clear_cache_now" 
			value="Y" >
	</td><td>Checking this box will completely clean your Parteibuch Aggregator cache. If you clear your cache and you have a lot of files cached, please be patient, it may take a while
	</td>
</tr>



<tr valign="top">
	<td align="left" colspan="3"><h3>Permalinks</h3>
	Rewritten permalinks are a popular feature of wordpress. They make URIs of the blog look like http://_mydomain_/_aboutme_/ instead of http://_mydomain_/index.php?pagename=_pagename_ If you like, the Parteibuch Aggregator can hook into the permalink structure of wordpress and generate similar rewritten permalinks for the pages delivered by the Parteibuch Aggregator<br><br>
		(<b>Attention</b>: You need to <a target="_blank" title="Opens in new window: refresh the permalinks of your blog" href="options-permalink.php">refresh your permalinks</a> after changing this option, if you don't do this, the change will have no effect. You don't have to change your blog's rewrite options, ie just hit the button to "save changes" there, just hit the button to "save changes" and that's it.)<br/><br/>
		</td>
</tr>

<tr valign="top">
	<th scope="row">
		Enable Permalinks?
		<br />&nbsp;<br />&nbsp;<br />
	</th>
	<td>
<select name="pba_options_link_rewriting">
<?php 

$namedescarray=array('Y' => 'Yes&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 'N' => 'No');
foreach($namedescarray as $key => $value){
$selected="";
if($pba_enable_rewriting == $key ) $selected = ' selected';
	echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
}
echo '</select>
</td><td align="left">'; 

echo "Memory info for 'rewriting': " . $bdprss_db->serverstatus['rewriting']['status'] . ", Notice: " . $bdprss_db->serverstatus['rewriting']['notice'] . "<br />"; 

	$rewrite_array=get_option('rewrite_rules');
	if(!is_array($rewrite_array)) {
		echo 'This blog has not enabled permalinks. To use permalink rewriting feature of the Parteibuch Aggregator you have to <a target="_blank" title="Opens in new window: enable permalinks for your blog" href="options-permalink.php">enable permalinks for your blog</a>.';
	}elseif(count($rewrite_array) == 0){
		 echo 'Either your blog doesn&#39;t use permalinks or we can&#39;t detect them. Parteibuch Aggregator recommends therefore to <a target="_blank" title="Opens in new window: refresh permalinks of your blog" href="options-permalink.php">refresh your permalinks</a>.';
	}elseif(count($rewrite_array) > 0){
		$rulematch=false;
		foreach($rewrite_array as $key => $value){
			if(strstr($key,'ticker-feed')) $rulematch=true;
		}
		if(!$rulematch && $pba_enable_rewriting != 'N') {
			echo 'Though enabled, the Parteibuch Aggregator permalinks seem not to be in effect. Parteibuch Aggregator recommends therefore to <a target="_blank" title="Opens in new window: refresh permalinks of your blog" href="options-permalink.php">refresh your permalinks</a>.';
		}elseif(!$rulematch && $pba_enable_rewriting == 'N') {
			echo 'Enable the permalink rewriting feature of the Parteibuch Aggregator to make the Aggregator URLs look like /page/2/ instead of ?tickerpage=2';
		}elseif($rulematch && $pba_enable_rewriting != 'N') {
			echo 'Fine. Permalink rewriting feature of Parteibuch Aggregator is enabled and shall be working.';
		}elseif($rulematch && $pba_enable_rewriting == 'N'){
			echo 'Though disabled, there seem to be still Parteibuch Aggregator rules enabled in your blog. Parteibuch Aggregator recommends therefore to <a target="_blank" title="Opens in new window: refresh permalinks of your blog" href="options-permalink.php">refresh your permalinks</a>.';
		}
	}

//echo "The rules are: " . str_replace("\n",'<br>',PBALIB::get_r(get_option('rewrite_rules')));

?>    </td></tr>

<tr valign="top">
	<td align="left" colspan="3"><h3>Memory tables</h3>
	MySQL is able to write <a target="_blank" title="Opens in new window: MySQL 5.0 documentation for memory tables" href="http://dev.mysql.com/doc/refman/5.0/en/memory-storage-engine.html">tables into memory</a>. Having index tables in memory will speed up large databases a lot. Disadvatage is, that these memory tables will occupy memory permanently, even if nobody accesses the site and that they need to be refilled always when MySQL is restarted. Parteibuch Aggregator is designed to use memory tables. However, it is possible to turn off this feature.<br><br>
		</td>
</tr>

<tr valign="top">
	<td>
		Enable memory tables?
		<br />&nbsp;<br />&nbsp;<br />
	</td>
	<td>
<select name="pba_options_enable_memtables">
<?php 
$oldvaluetmp=$bdprss_db->pbaoption('enable_memtables');
if($oldvaluetmp == 'Y'){
	$old_pba_options_enable_memtables ='Y';
}elseif($oldvaluetmp == 'N') {
	$old_pba_options_enable_memtables ='N';
}elseif($oldvaluetmp == 'auto') {
	$old_pba_options_enable_memtables ='auto';
}else{
	$old_pba_options_enable_memtables ='undefined';
}

$namedescarray=array('Y' => 'Yes', 'N' => 'No', 'auto' => 'Automatic&nbsp;&nbsp;');
foreach($namedescarray as $key => $value){
$selected="";
if($old_pba_options_enable_memtables == $key ) $selected = ' selected';
	echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
}
echo '</select>';
echo '<input type="hidden" name="old_pba_options_enable_memtables" value="' . $old_pba_options_enable_memtables . '" />';
echo '</td><td align="left">'; 

echo "Information from server: ";

$pba_max_heap_table_size=$bdprss_db->get_mysql_variables('max_heap_table_size');
if($pba_max_heap_table_size != "") echo "This MySQL Server allows a maximum size of " . $pba_max_heap_table_size . " bytes for memory tables. ";

$pba_memtables_array=array('mtablestatus' => $bdprss_db->mtablestatus, 'mitemtable' => $bdprss_db->mitemtable);

foreach($pba_memtables_array as $pba_shorttablename => $pba_tablenameindb){
	$pba_not_tmp='not ';
	if($bdprss_db->detect_memtable($pba_tablenameindb)) {
		$pba_not_tmp = '';
	}
	$pba_table_status_row_object=$bdprss_db->get_mysql_tablestatus($pba_tablenameindb);
	
	//echo "The pba_table_status_row_object: " . str_replace("\n",'<br>',PBALIB::get_r($pba_table_status_row_object));
	$pba_table_size='Something strange happens. Parteibuch Aggregator cannot retrieve status information for table '.$pba_tablenameindb.'.';
	if(is_object($pba_table_status_row_object)){
		$pba_table_size= 'Size is ' . ($pba_table_status_row_object->Data_length + $pba_table_status_row_object->Index_length) . ' bytes. ';
	}
	echo 'Table '.$pba_shorttablename.' is '.$pba_not_tmp.'a memory table. ' . $pba_table_size;
}

?>

</td></tr>

<tr valign="top">
	<td align="left" colspan="3"><h3>Uninstaller</h3>
	By default, the Parteibuch Aggregator will keep it's data and configuration settings, when you disable the plugin. 
	This has the advantage, that you can re-enable the Aggregator plugin and you will not have lost your data.
	However, if you want to completely remove the Parteibuch Aggregator and all it's data and all the configuration settings, 
	you shall check the checkbox below before deactivation of the Parteibuch Aggregator plugin.<br><br>
		</td>
</tr>

<tr valign="top">
	<td >
		Delete all data on plugin deactivation?
	</td>
	<td>
		<input type="checkbox" name="pba_delete_alldata"
			value="Y" >
	</td><td>Checking this box will delete all the data of the Parteibuch Aggregator, when you deactivate the Parteibuch Aggregator plugin. <b>There is no way back</b>. If you use Parteibuch Aggregator widgets, remove the widgets before deactivation from your sidebar. If you use the rewritten URL feature, flush your permalinks after plugin deactivation.
	</td>
</tr>

<tr valign="top">
	<td align="left" colspan="3"><h3>Server load detection</h3>
	The Parteibuch Aggregator can try to detect the serverload with sys_getloadavg or as the first load average figure from the output of a Unix system call to <a target='_blank' title='Opens in new window: Find articles about uptime via Google' href='http://www.google.com/search?q=uptime+command+UNIX'>uptime</a> and decide on that basis, if it is a good time to do some resource intensive housekeeping jobs or if they better should be processed at a later time.<br><br>
		</td>
</tr>

<tr valign="top">
	<td>
		Enable load detection?
		<br />&nbsp;<br />&nbsp;<br />
	</td>
	<td>
<select name="pba_options_enable_loaddetection">
<?php 
$namedescarray=array('Y' => 'Yes', 'N' => 'No', 'auto' => 'Automatic&nbsp;&nbsp;');
foreach($namedescarray as $key => $value){
$selected="";
if($pba_options_enable_loaddetection == $key ) $selected = ' selected';
	echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
}
echo '</select></td><td align="left">'; 

echo "Info from server: ";
if(function_exists('sys_getloadavg')) {
	$load=sys_getloadavg();
	$pbaload=$load[0];
	echo "Fine. Your php has a function sys_getloadavg, so we have no problems to get server load data.";
}elseif(ini_get('safe_mode')){
	echo 'The <a target="_blank" title="Opens in new window: See php manual: safe_mode" href="http://php.net/features.safe-mode">safe_mode feature</a> of PHP is enabled on your server. ';
	if(ini_get('safe_mode_exec_dir') == ""){
		echo ' No safe_mode_exec_dir is set, if you want to use the load detection feature of the Parteibuch Aggregator, your server administrator has to disable safe_mode or set a safe_mode_exec_dir.';
		$pbaload=0;
	}else{
		$pbaload=$bdprss_db->pba_loaddetection();
		if($pbaload>0) echo 'Using safe_mode uptime wrapper script. ';
		if($pbaload==0) echo ' PHP safe_mode_exec_dir is set to ' . ini_get('safe_mode_exec_dir') . '. ' .
			" Parteibuch Aggregator doesn't get back a sensible value from uptime_wrapper.sh. 
			If you want to use the Parteibuch Aggregator load detection feature then make sure you copy uptime_wrapper.sh into your safe_mode_exec_dir, that it is readable and 
			executable for your php environment and that it produces an ouput like a standard unix call 
			to uptime. Make sure that paths in the wrapper script fit to your server and <a target='_blank' title='Opens in new window: See newline article in Wikipedia' href='http://en.wikipedia.org/wiki/Newline'>newline</a> line breaks 
			in the script are Unix like.";
	}
}else{
	//no safe mode
	$pbaload=$bdprss_db->pba_loaddetection();
	if($pbaload == 0) echo ' The Parteibuch Aggregator cannot detect a server load. 
		If you want to use the Parteibuch Aggregator load detection feature then you may try to 
		modify the script uptime_wrapper.sh in directory "'.dirname(__FILE__) . '/wrapper/'. '", 
		so that there will be produced output as like from a uptime unix system command.' . 
		" Make sure that the script is readable and executable for your php environment and 
		that paths in the wrapper script fit to your server and 
		<a target='_blank' title='Opens in new window: See newline article in Wikipedia' href='http://en.wikipedia.org/wiki/Newline'>newline</a> 
		line breaks in the script are Unix like.";
}

if($pbaload > 0) echo ' Server load we got from your system is at this moment: ' . $pbaload . '. ';
if(isset($bdprss_db->serverstatus['loaddetection']['notice'])){
	echo " Memory notice for 'loaddetection': Notice: " . $bdprss_db->serverstatus['loaddetection']['notice']. '. '; 
}
echo " Memory notice for 'pbaload': Notice: " . $bdprss_db->serverstatus['pbaload']['notice']. '. '; 
if(isset($bdprss_db->serverstatus['highloadthreshold']['notice'])){
	echo " Memory notice for 'highloadthreshold': Notice: " . $bdprss_db->serverstatus['highloadthreshold']['notice']. '. '; 
}
?>

<br /><br /><br /><br /></td></tr>

<?php if($pbaload > 0){
	//we will hide this configuration row, if we cannot detect a load
?>

<tr valign="top">
	<td>
		Define a high load threshold
		<br />&nbsp;<br />&nbsp;<br />
	</td>
	<td>
<select name="pba_highloadthreshold">
<?php 
$namedescarray=array('1' => '&nbsp;&nbsp;1&nbsp;&nbsp;', 
							'2' => '&nbsp;&nbsp;2&nbsp;&nbsp;', 
							'3' => '&nbsp;&nbsp;3&nbsp;&nbsp;', 
							'4' => '&nbsp;&nbsp;4&nbsp;&nbsp;', 
							'5' => '&nbsp;&nbsp;5&nbsp;&nbsp;', 
							'10' => '&nbsp;&nbsp;10&nbsp;&nbsp;', 
							'20' => '&nbsp;&nbsp;20&nbsp;&nbsp;');

foreach($namedescarray as $key => $value){
$selected="";
if($pba_highloadthreshold == $key ) $selected = ' selected';
	echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
}
echo '</select></td><td align="left">'; 

echo "If server load detection is enabled, the high load threshold tells the Parteibuch Aggregator, above what server load value housekeeping jobs shall be deferred. Please tell the Parteibuch Aggregator above what uptime load average value the Parteibuch Aggregator shall run in high serverload mode. Default is a highload threshold value of 3. ";

echo "</td></tr>";

} //end conditional row if pbaload > 0

?>

	<tr valign="top">
		<td colspan="3" align="right"><br><br>
			<a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php">All fine</a>&nbsp;(exit this page without saving any changes) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="pba_edit_options_button" value="Save configuration &raquo;" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
	</tr>

	</table>

<?php
}
?>
</form>
</fieldset>
