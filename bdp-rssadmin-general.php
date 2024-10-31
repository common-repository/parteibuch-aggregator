<?php
	if ($user_level < 6) die ( __('Cheatin&#8217; uh?') );  	// safety net to ctach cheets
?>

<h2>Managing the <a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php">Parteibuch Aggregator</a> plugin</h2>

<h3>The Parteibuch Aggregator has some stuff to configure:</h3>

<ul>
<li><b><a href="#addfeeds">1. add and manage the feeds</a></b> you wish to have in your Parteibuch Aggregator</li>
<li><b><a href="#createlist">2. create/update lists</a></b> to group your feeds to lists that can be selected for filtering output</li>
<li><b><a href="#createpbaoutput">3. create/update output formats and assign them to your blog pages</a></b></li>
<li><b><a href="#globals">4. set/change some global configuration options</a></b> for the Parteibuch Aggregator</li>
<?php
if(function_exists('wp_register_sidebar_widget')){
	echo '<li><b><a href="widgets.php">5. add widgets</a></b> with aggregated feeds to your sidebar</li>';
}

?>
</ul>



<p><hr /></p>

<fieldset>
<a name="addfeeds"></a>
<h3>1: Feeds</h3>
<h4>Add a new feed</h4>
<form method="post" action="<?php echo $selfreference; ?>">
	<table border="0" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
		<tr valign="middle">
			<td align="left"><strong>RSS Address:</strong>
				<input type="text" name="bdprss_new_feed_name" value="" size="60" />
				 <br />(multiple feeds can be entered as a comma separated list)<br/></td>
			<td align="right">
				<input type="submit" name="bdprss_add_feed_button" value="Add Feed &raquo;" />
			</td>
		</tr>
	</table>
</form>

<?php 
global $bdprss_db;
$overriden = FALSE;
$result = $bdprss_db->get_all_sites();
if($result) {
?>
	<hr width='50%' />
	<h4>Current feeds</h4>
	<table width="100%" cellpadding="3" cellspacing="3">
<?php 
	$file = get_option('siteurl') . '/wp-admin/edit.php?page=' . PBA_DIRECTORY. '/bdp-rssadmin.php';
	$now = $bdprss_db->now;

	echo "<tr><th align='left'>Site and feed status</th><th>Site updated</th>".
		"<th>GMT adj</th><th>Polling freq</th>".
		"<th>Last poll</th><th>Cache updated</th><th>Next poll</th>".
		"<th colspan='3'>Actions</th></tr>\n";
	
	$benchmark = $now - (60 * 60 * 24 * 7);	// highlight blogs with updates older than 7 days
	
	if($result) {
		$class='';
		foreach($result as $r) {
			$ref = $r->{$bdprss_db->cidentifier};
			$url = $r->{$bdprss_db->cfeedurl};
			$site = $r->{$bdprss_db->csitename};
			$siteurl = $r->{$bdprss_db->csiteurl};
			$description = $r->{$bdprss_db->cdescription};
			$gmt = $r->{$bdprss_db->cgmtadjust};
			$polled = PBALIB::gettheage( $r->{$bdprss_db->clastpolltime} );
			$updated = PBALIB::gettheage( $r->{$bdprss_db->cupdatetime} );
			$scheduled = PBALIB::gettheage( $r->{$bdprss_db->cnextpolltime} );
			$pollingfreq = $r->{$bdprss_db->cpollingfreqmins};
			if($scheduled == 'never') $scheduled = 'now';
			$sno = $r->{$bdprss_db->csitenameoverride} == 'Y';
			
			$siteUpdate = '';
			$name = $url;
			if($site) 
			{
				$italics = ''; $unitalics = '';
				if($sno) { $italics = '<b>*</b> <em>'; $unitalics = '</em>'; $overriden = TRUE; }
				$name = "$italics<a href='$siteurl' title='$description'>"."$site</a>$unitalics";
				$name .= " [<a href='$url' title='$url'>feed</a>]";
				
				$ticks = $bdprss_db->get_most_recent_item_time($ref);
				$bold = ''; $unbold ='';
				if($ticks < $benchmark) { $bold = '<strong>'; $unbold ='</strong>'; }
				$siteUpdate = " $bold".PBALIB::gettheage($ticks)."$unbold"; 
			}
			
			$bold = '';
			$unbold = '';
			if( $r->{$bdprss_db->clastpolltime} != $r->{$bdprss_db->cupdatetime} ) { 
				$bold = '<strong>'; 
				$unbold = '</strong>'; 
			}
			
			$sbold = ''; $sunbold = '';
			if($r->{$bdprss_db->cnextpolltime} < $now)
			{
				$sbold = '<strong>'; 
				$sunbold = '</strong>'; 
			}
			
			$class = ('alternate' == $class) ? '' : 'alternate';
			
			$errorCount = $bdprss_db->countErrors($url);
			if($errorCount) 
				$name .= " <a href='".$file.
					"&amp;action=errorlist&amp;rss=$ref'>[<strong>E&nbsp;$errorCount</strong>]</a>";
			
			echo "<tr class='$class' valign='middle' align='center'>\n".
				"\t<td align='left'>$name</td>\n".
				"\t<td align='center'>$siteUpdate</td>\n".
				"\t<td align='center'>";
			if($gmt != 0.0)	echo $gmt;
			echo "</td>\n".
				"\t<td align='center'>";
			if(	$pollingfreq ) echo "$pollingfreq minutes";
			echo "</td>\n" .
				"\t<td align='center'>$polled</td>\n" .
				"\t<td align='center'>$bold $updated $unbold</td>\n".
				"\t<td align='center'>$sbold $scheduled $sunbold</td>\n".
				"\t<td align='center'><a href='" .$file. 
				"&amp;action=editfeed&amp;rss=$ref'>Edit</a></td>\n".
				"\t<td align='center'><a href='" .$file. 
				"&amp;action=update&amp;rss=$ref'>Poll</a></td>\n".
				"\t<td align='center'><a href='" .$file. 
				"&amp;action=delete&amp;rss=$ref'>Delete</a></td>\n".
				"</tr>\n";
		}
	}
}
?>
</table>

<?php

if($overriden) 
	echo "<p><b>Note:</b> Sites in italics have had their feed details overridden. ".
	"To restore feed details: first edit the site, second un-tick the box to switch off the feed override, ".
	"then hit the edit button, and finally poll the feed. </p>\n";

// improved monitoring of feed errors
if($bdprss_db->countErrors())
{
	echo "<hr width='50%' />\n\n<h4>Recent feed errrors</h4>\n\n";

	echo "<table width='100%' cellpadding='3' cellspacing='3'>\n";
	echo "<tr><td align='left'>".
		"<a href='".$file."&amp;action=errorlist'>List all feed errors</a></td>\n";
	echo "<td align='right'>".
		"<a href='".$file."&amp;action=errordelete'>Delete the feed error table</a></td>\n";
	echo "</tr></table>";
}

// lets put the list of URLs in the OUTPUT - they can be useful!
if($result) 
{
	echo "<hr width='50%' />\n\n<h4>A list of feeds</h4>\n\n";

	echo "<p>This list can be copied and saved to a file. If you delete everything, ".
		"you can paste the list into the add feeds box.</p>\n<p>";

echo '<textarea cols="120" rows="5" readonly>';

	$subsequent = false;
	foreach($result as $r) 
	{
		if($subsequent) echo " ";
		$subsequent = true;
		$url = $r->{$bdprss_db->cfeedurl};
		echo $url;
	}
	echo "</textarea></p>\n";
}
?>
</fieldset>

<hr width='50%' />

<fieldset>
<h4>Manage feed related values</h4>

<form method="post" action="<?php echo $selfreference; ?>">
	
	<table border="0" width="100%" cellspacing="2" cellpadding="5" class="editform"> 
	<tr valign="top">
		<th  width="40%" scope="row">
			Standard feed polling frequency:
		</th>
		<td>
			<input type="range" name="bdprss_new_frequency" 
					value="<?php echo get_option('bdprss_update_frequency'); ?>" 
					maxlength="3" size="3" /> minutes<br>
					This frequency is used for all sites unless a non-standard
					frequency is set above.
		</td>
	</tr>
	<tr valign="top">
		<th  width="40%" scope="row">
			Keep feed items for:
		</th>
		<td>
			<input type="range" name="bdprss_keep_howlong" 
					value="<?php echo intval(get_option('bdprss_keep_howlong')); ?>" 
					maxlength="3" size="3" /> months (zero = forever)
		</td>
	</tr>
	<tr valign="top">
		<td align="right" colspan="2">
			<input type="submit" name="bdprss_change_frequency_button" 
			value="Change &raquo;" />
		</td>
	</tr>
	</table>

</form>
</fieldset>
	
<hr width='50%' />
	
<fieldset>
<h4>Poll some RSS feeds</h4>
<?php
	echo "<table width='100%' cellpadding='3' cellspacing='3'>\n";
	echo "<tr><td align='left'>".
		"Be patient, this can take some time.";
?></td><td align="right">

<form method="post" action="<?php echo $selfreference; ?>">
			<input type="submit" name="bdprss_poll_all_button" value="Poll some feeds" />
</form>
</td></tr></table>
</fieldset>

<p><hr /></p>

<fieldset>
<a name="createlist"></a>
<h3>2: List filters</h3>

<h4><a href="<?php echo "$selfreference&amp;action=createlist"; ?>">Create a new list filter</a></h4>

<?php 
global $bdprss_db;
$result = $bdprss_db->get_all_lists();
$pbaoutputs = $bdprss_db->get_all_pbaoutputs();
if($result) {
	if($pbaoutputs){
		foreach($pbaoutputs as $r) {
			if(!isset($isdefaultlist[$r->{$bdprss_db->pbaodefaultlist}])) $isdefaultlist[$r->{$bdprss_db->pbaodefaultlist}] ="";
			$isdefaultlist[$r->{$bdprss_db->pbaodefaultlist}] .= $r->{$bdprss_db->pbaoidentifier} . " ";
		}
	}

?>

	<h4>Current lists</h4>
	<table width="100%" cellpadding="3" cellspacing="3">
<?php 
	global $bdprss_db;

	$file = get_option('siteurl') . '/wp-admin/edit.php?page=' . PBA_DIRECTORY. '/bdp-rssadmin.php';

	echo "<tr><th>List ID</th><th width='60%'>Name</th><th colspan='3'>Actions</th></tr>\n";

	if($result) {
		$class = '';
		foreach($result as $r) {
			$id = $r->{$bdprss_db->lidentifier};
			$name = htmlspecialchars($r->{$bdprss_db->lname});

			$class = ('alternate' == $class) ? '' : 'alternate';

			echo "<tr class='$class' valign='middle' align='center'>\n".
				"<td>$id</td><td width='60%'>$name</td>".
				"<td><a href='$selfreference&amp;action=editlist&amp;list=$id'>Edit</a></td><td>".
				"<a title='Copy list filter #$id and edit it as new list ' href='$selfreference&amp;action=createlist&amp;list=$id'>Copy to new</a></td><td>";
			if(!isset($isdefaultlist[$id])) {
				echo "<a href='$selfreference&amp;action=dellist&amp;list=$id'>Delete</a>";
			}else{
				echo "Used by $isdefaultlist[$id]";
				$displaylistnote=true;
			}
			echo "</td></tr>";
		}
	}
}

?>
</table>
</fieldset>
<?php	if($displaylistnote) echo "<p><b>Note:</b> List filters can only be deleted when they are not used as default list filter by any output format</p>\n"; ?>

<p><hr /></p>

<?php 
?>

<fieldset>
<a name="createpbaoutput"></a>
<h3>3: Output streams</h3>

<h4><a href="<?php echo "$selfreference&amp;action=createpbaoutput"; ?>">Create a new output format</a></h4>

<?php 
global $bdprss_db;
if(count($pbaoutputs)>=1) {
?>

	<h4>Current output streams</h4>
	<table width="100%" cellpadding="3" cellspacing="3">
<?php 
	global $bdprss_db;

	$file = get_option('siteurl') . '/wp-admin/edit.php?page=' . PBA_DIRECTORY. '/bdp-rssadmin.php';

	echo "<tr><th>Output format ID</th><th width='60%'>Name</th><th colspan='3'>Actions</th></tr>\n";

	if($pbaoutputs) {
		$class = '';
		foreach($pbaoutputs as $r) {
			$id = $r->{$bdprss_db->pbaoidentifier};
			$name = htmlspecialchars($r->{$bdprss_db->pbaoname});

			$class = ('alternate' == $class) ? '' : 'alternate';

			echo "<tr class='$class' valign='middle' align='center'>\n".
				"<td>$id</td><td width='60%'>$name</td>".
				"<td><a href='$selfreference&amp;action=editpbaoutput&amp;pboutput=$id'>Edit</a></td>".
				"<td><a title='Copy output format #$id and edit it as new pba output ' href='$selfreference&amp;action=createpbaoutput&amp;pboutput=$id'>Copy to new</a></td>".
				"<td><a href='$selfreference&amp;action=delpbaoutput&amp;pboutput=$id'>Delete</a></td>".
				"</tr>";
		}
	}
}

?>
</table>
</fieldset>



<p><hr /></p>
<a name="globals"></a>
<h3>4. Manage some global options of the Parteibuch Aggregator</h3>
<?php echo "<a href='$selfreference&amp;action=options'><b>Manage global options</b></a>"; ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Do you want to have a look into programmer&#39;s hell? Go and get here <?php echo "<a href='$selfreference&amp;action=status'><b>some raw status info</b></a>."; ?>

<p><hr /></p>

<fieldset>
<h3>Notes:</h3> 
<ul>

<li>Although <b>designed for <a href="http://wordpress.org/">Wordpress'</a> single site RSS feeds</b>, 
this system should work with the RDF feeds generated by <a href="http://www.sixapart.com/">Typepad</a> 
and the ATOM feeds generated by <a href="http://www.blogger.com/start">Blogger</a>.
It will not work with synthetic or multi-channel RSS or ATOM feeds.</li>

<li>The number of feeds you will be able to carry will depend on the volume of traffic to your site,
and the polling frequency. Typically each hit to your site will update one feed. The more visitors per hour, 
and the wider the polling interval, the more feeds you will be able to carry. </li>

</ul>
</fieldset>

