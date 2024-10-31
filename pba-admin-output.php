<?php 

if ($user_level < 6) die ( __('Cheatin&#8217; uh?') ); 

?>

<h2><a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php">Parteibuch Aggregator</a>: Configuration of output format #<?php echo $pboutput; ?></h2>

<fieldset>

<form method="post" action="<?php echo $selfreference; ?>">


<?php 

global $bdprss_db;

$pbastartconfig=0;
if(isset($_GET['pbstart'])){
	$pbastartconfig=abs(intval($_GET['pbstart']));
}
$pba_editpbaoutput_button='<input type="submit" name="pba_edit_output_button" value="&raquo;Save&raquo;" />';

$result = $bdprss_db->get_pbaoutput($pboutput);

if(!$result) {
	echo "<p><b>Unexpected error:</b> could not find output format #$pboutput </p>\n";
} else {
	if($pbastartconfig > 0){
		echo '<input type="hidden" name="pbstart" value="'.$pbastartconfig.'" />';
		echo '<input type="hidden" name="action" value="status" />';
	}
	
	// setup stuff
	$pbaoname = bdpDisplayCode($result->{$bdprss_db->pbaoname});
	$pbaopage2hookin = abs(intval($result->{$bdprss_db->pbaopage2hookin}));

//items to be displayed
	$pbaodefaultlist = $result->{$bdprss_db->pbaodefaultlist};
	$pbaoformattype = $result->{$bdprss_db->pbaoformattype};
	$pbaomaxitems = $result->{$bdprss_db->pbaomaxitems};

//item formatting
	$pbaotemplate_ticker = bdpDisplayCode($result->{$bdprss_db->pbaotemplate_ticker});

	$pbaoappend_extra_link = $result->{$bdprss_db->pbaoappend_extra_link};
	$pbaoappend_cache_link = $result->{$bdprss_db->pbaoappend_cache_link};
	$pbaoadd_social_bookmarks = $result->{$bdprss_db->pbaoadd_social_bookmarks};

	$pbaosidebarwidget = bdpDisplayCode($result->{$bdprss_db->pbaosidebarwidget});

	$pbaomaxlength = $result->{$bdprss_db->pbaomaxlength};
	$pbaomaxwordlength = $result->{$bdprss_db->pbaomaxwordlength};
	$pbaoitem_date_format = bdpDisplayCode($result->{$bdprss_db->pbaoitem_date_format});
	$pbaoallowablexhtmltags = preg_split("','", $result->{$bdprss_db->pbaoallowablexhtmltags}, -1, PREG_SPLIT_NO_EMPTY);

//cachepage options
	$pbaoiscachable = bdpDisplayCode($result->{$bdprss_db->pbaoiscachable});
	$pbaocacheviewpage = bdpDisplayCode($result->{$bdprss_db->pbaocacheviewpage});
	$pba_otemplate_cache = bdpDisplayCode($result->{$bdprss_db->otemplate_cache});

//feed options
	$pba_channel_title = bdpDisplayCode($result->{$bdprss_db->pba_channel_title});
	$pba_channel_link = bdpDisplayCode($result->{$bdprss_db->pba_channel_link});
	$pba_channel_description = bdpDisplayCode($result->{$bdprss_db->pba_channel_description});
	$pba_channel_language = bdpDisplayCode($result->{$bdprss_db->pba_channel_language});
	$pba_channel_copyright = bdpDisplayCode($result->{$bdprss_db->pba_channel_copyright});

//kalender options
	$pba_otemplate_kalender = bdpDisplayCode($result->{$bdprss_db->otemplate_kalender});
	$pba_oarchive_date_format = bdpDisplayCode($result->{$bdprss_db->oarchive_date_format});
	$pba_okalendermonthslist = bdpDisplayCode($result->{$bdprss_db->okalendermonthslist});
	$pba_okalenderboxtablecaption = bdpDisplayCode($result->{$bdprss_db->okalenderboxtablecaption});
	$pba_okalender_last = bdpDisplayCode($result->{$bdprss_db->okalender_last});
	$pba_okalender_next = bdpDisplayCode($result->{$bdprss_db->okalender_next});
	$pba_okalenderboxdaysofweeklist = bdpDisplayCode($result->{$bdprss_db->okalenderboxdaysofweeklist});

	$pbao_superparameter = bdpDisplayCode($result->{$bdprss_db->pbao_superparameter});

	$pba_wp_pages=$bdprss_db->get_wp_published_pages();

?>

<table width="100%" cellspacing="2" cellpadding="5" class="editform">

<tr valign="top">
	<td align="left" colspan="2"><h4>Basic configuration</h4>
		(You may change all values again as often as you like <a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php">via the tools section of your blog&#39;s admin panel</a>)<br />&nbsp;<br />
		</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row" align="left">
		1. Give this output stream a name:<br />&nbsp;<br />&nbsp;<br />
	</th>
	<td>
		<input type="hidden" name="pbaoidentifier" value="<?php echo $pboutput; ?>" />
		<input type="text" name="pbaoname" value="<?php echo $pbaoname; ?>" size="50" />
		<br/>(Just type in any name you like)<br /><br />
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row" align="left">
		2. Select a page where to display this output:
		<br />&nbsp;<br />&nbsp;<br />
	</th>
	<td>

<select name="pbaopage2hookin">

<?php
$pba_pagecount=count($pba_wp_pages);

//now comes the select element to hook into a wordpress page;

if($pba_pagecount > 0){
	foreach($pba_wp_pages as $pba_page_key => $pba_wp_page){
		$pba_selected_page="";
		if ($pba_wp_page->ID == $pbaopage2hookin) $pba_selected_page=" selected";
		if(0 == $pbaopage2hookin && $pbastartconfig == 1 && ($pba_page_key + 1) == $pba_pagecount) $pba_selected_page=" selected";
		$pba_prepared_post_title=bdpDisplayCode($pba_wp_page->post_title);
		if(strlen($pba_prepared_post_title) > 60) $pba_prepared_post_title = substr($pba_prepared_post_title,0,60) . " ...";
		echo '<option value="'.$pba_wp_page->ID.'"'.$pba_selected_page.'>'.$pba_wp_page->ID.' - '.$pba_prepared_post_title.'</option>';
	}
}

		$pba_selected_page="";
		if ((0 == $pbaopage2hookin && $pbastartconfig == 0) || $pba_pagecount == 0) $pba_selected_page=" selected";
		echo '<option value="0"'.$pba_selected_page.'>Do not display this output as a page of my blog&nbsp;&nbsp;&nbsp;</option>';

echo '</select>';
echo '<br />No choice fitting? Just <a title="Opens in new window: Create and publish a page on your blog" href="page-new.php" target="_blank">publish a new page</a> and then <a title="Refresh this page" href="">refresh</a>.<br /><br />';

if($pba_pagecount == 0) echo '<br /><br /><b>Attentation please: No page found.</b> The Parteibuch Aggregator could not find any published <b>page</b> in your blog to hook into and show output. Maybe you want to <a title="Opens in new window: Create and publish a page on your blog" href="page-new.php" target="_blank">create and publish a page on your blog</a>? <br/><br/>After creating a page, you can come back here, <a title="Refresh this page" href="">refresh this page</a> and select the newly created page to hook this output stream into.<br /><br />'; 
?>

	</td>
</tr>

<?php 
//$mypbaformdebug .= $pbaopage2hookin;
//if(true) echo '<tr valign="top"><td align="left" colspan="2">Debug: '.$mypbaformdebug . '</td></tr>';
?>

<tr valign="top">
	<th  width="40%" scope="row" align="left">
		3. And hit the save button to complete:<br />&nbsp;<br />&nbsp;<br />
	</th>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $pba_editpbaoutput_button; ?>
	</td>
</tr>

</table>

<hr/>

<script type="text/javascript">
function toggledisplay(pbatoggleid){
	var pbatoggle = document.getElementById(pbatoggleid);
	if (pbatoggle.style.display != 'block') {
		pbatoggle.style.display = 'block';
	}else {
		if (pbatoggle.style.display == 'block') {
			pbatoggle.style.display = 'none';
		}
	}
}
document.write('Do you love lot&#39;s of parameters? The above configuration will be probably enough to display a nice RSS aggregated page on your blog. But if you like, you may view and change with the link beneath many additional parameters. If you are unsure, just don&#39;t use them at all and leave all the additional parameters unchanged.<br /><br />');
document.write(' [<a href="#" onclick="toggledisplay(\'pbaoutextendedconfig\');return false;">Toggle Additional Parameters</a>]<div id="pbaoutextendedconfig" style="display:none;">');
</script><noscript>
The above settings will be probably enough to display a nice RSS aggregated page on your blog. But if you are a freak and love parameters and templates, here you have some additional parameters for this output stream. If you don&#39;t know what to do with the following parameters and templates, just leave all the following parameters unchanged.
</noscript>

<table width="100%" cellspacing="2" cellpadding="5" class="editform">
<tr valign="top"><td align="left" colspan="2"><h4>Additional parameters for this output configuration</h4></td></tr>
<tr valign="top"><td align="left" colspan="2"><h4>Select which items shall be displayed in this output stream</h4></td></tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Select a list filter:
	</th>
	<td>

		<input type="radio" name="pbaodefaultlist" 
			value="0"<?php if(!$pbaodefaultlist) echo ' checked="checked"'; ?>>
			No list filter - display items of all lists<br />
<?php

	global $bdprss_db;
	$result = $bdprss_db->get_all_lists();
		foreach($result as $r) {
			$checked="";
			if($r->identifier == $pbaodefaultlist) $checked=' checked="checked"';
			echo '<input type="radio" name="pbaodefaultlist" 
			value="'.$r->identifier.'" '.$checked.'> '.$r->identifier.': 
			'.$r->name.'<br />';
		}
?>
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Maximum items per page:
	</th>
	<td>
		<input type="text" name="pbaomaxitems" 
			value="<?php echo $pbaomaxitems; ?>" size="5" />
			(Zero (0) means as many as possible)
			
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Output special sort order and time filter:
	</th>
	<td>

		<input type="radio" name="pbaoformattype" 
			value="countrecentitem"<?php if($pbaoformattype == 'countrecentitem') echo ' checked="checked"'; ?>>
			Standard - list by most recent items<br />
		<input type="radio" name="pbaoformattype" 
			value="daterecentitem"<?php if($pbaoformattype == 'daterecentitem') echo ' checked="checked"'; ?>>
			Special - list items of last 24 hours and sort by most recent item time<br />
		<input type="radio" name="pbaoformattype" 
			value="sitealpha"<?php if($pbaoformattype == 'sitealpha') echo ' checked="checked"'; ?>>
			Special - list just one item per site of last 31 days and sort alphabetically<br />
		<input type="radio" name="pbaoformattype" 
			value="siteupdate"<?php if($pbaoformattype == 'siteupdate') echo ' checked="checked"'; ?>>
			Special - list just one item per site of last 31 days and sort by most recent item time
	</td>
</tr>

<tr valign="top"><td align="left" colspan="2"><h4>Format the output page</h4></td></tr>

<tr valign="top">
	<td width="40%">
		<p>Template for this output page: <br>
		see <a target="_blank" href="edit.php?page=parteibuch-aggregator/variables.php">here</a> some placeholder
</p>
	</td>
	<td><textarea cols="60" rows="20" name="pbaotemplate_ticker"><?php
echo $pbaotemplate_ticker;
?></textarea>
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Append links to the items:
	</th>
	<td>
		Link to item: <input type="checkbox" name="pbaoappend_extra_link" 
			value="Y" <?php if($pbaoappend_extra_link == 'Y') echo ' checked="checked"'; ?>>
		&nbsp;&nbsp;&nbsp;To cache: <input type="checkbox" name="pbaoappend_cache_link" 
			value="Y" <?php if($pbaoappend_cache_link == 'Y') echo ' checked="checked"'; ?>>
		&nbsp;&nbsp;&nbsp;Social bookmarks: <input type="checkbox" name="pbaoadd_social_bookmarks" 
			value="Y" <?php if($pbaoadd_social_bookmarks == 'Y') echo ' checked="checked"'; ?>>
	</td>
</tr>

<tr valign="top"><td align="left" colspan="2"><h4>Format the items</h4></td></tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Maximum words for each item:
	</th>
	<td>
		<input type="text" name="pbaomaxlength" value="<?php echo $pbaomaxlength; ?>" size="5" /><br />
		(zero = no maximum word limit)
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Maximum length of words in each item:
	</th>
	<td>
		<input type="text" name="pbaomaxwordlength" 
			value="<?php echo $pbaomaxwordlength; ?>" size="5" /><br />
		(zero = no maximum word limit)
	</td>
</tr>


<tr valign="top">
	<th  width="40%" scope="row">
		Item date format:
	</th>
	<td>
			<input type="text" name="pbaoitem_date_format" 
			value="<?php echo $pbaoitem_date_format; ?>" size="30" /> (leave empty to show item age)
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		XHTML tags to retain in list output:
	</th>
	<td>&nbsp;
	</td>
</tr>

<?php

	foreach($bdprssTagSet as $key => $value)
	{
		$tag = BDPRSS2::tagalise($key);
		echo '<tr valign="top"><td width="40%"  align="right">';
		echo $key;
		$checked = array_search($key, $pbaoallowablexhtmltags);
		if($checked !== FALSE) $checked = "checked='checked' "; else $checked='';
		echo "</td><td><input type='checkbox' name='$tag' $checked /> [";
			foreach($value as $v) echo " $v";
		echo "&nbsp;] </td></tr>\n";
	}
?>

<tr valign="top"><td align="left" colspan="2"><h4>Format the widget</h4></td></tr>

<tr valign="top">
	<td width="40%">
		<p>
		<?php
			echo "This is the template for what is output by the RSS Aggregator <a href='widgets.php'>widget</a>. 
			If you prefer to add the sidebar code manually to your sidebar, you may do it like this (the \"outputid\" defines which output you show):<br/>
		\$pba_config['outputid']=".$pboutput."; <br/>
		\$pba_config['show_sidebarwidget'] = 'Y'; <br/>
		\$pba_return=@PBA::outputwrapper(\$pba_config); <br/>
		echo \$pba_return['result'];";

		?>
	<td><textarea cols="60" rows="10" name="pbaosidebarwidget"><?php
echo $pbaosidebarwidget;
?></textarea>
	</td>


<tr valign="top"><td align="left" colspan="2"><h4>Cache item presentation</h4></td></tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Allow people to view the cache for the items:
	</th>
	<td>
		<input type="checkbox" name="pbaoiscachable" 
			value="Y" <?php if($pbaoiscachable == 'Y') echo ' checked="checked"'; ?>>
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Template of cache page:
	</th>
	<td><textarea cols="60" rows="10" name="pba_otemplate_cache"><?php
echo $pba_otemplate_cache;
?></textarea>
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Special page for cache viewing:
	</th>
	<td>
			<input type="text" name="pbaocacheviewpage" 
			value="<?php echo $pbaocacheviewpage; ?>" size="50" /><br />
			(If you have a special page for cache viewing, put the URL in here)
	</td>
</tr>



<tr valign="top"><td align="left" colspan="2"><h4>Feed Output Options</h4></td></tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Feed Title in Output:
	</th>
	<td>
			<input type="text" maxlength="100" name="pba_channel_title" 
			value="<?php echo $pba_channel_title; ?>" size="50" />
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Feed Channel WWW - Link:
	</th>
	<td>
			<input type="text" maxlength="100" name="pba_channel_link" 
			value="<?php echo $pba_channel_link; ?>" size="50" /><br/>(if empty, post-&gt;guid from wordpress will be used)
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Feed Channel Description:
	</th>
	<td>
			<input type="text" maxlength="200" name="pba_channel_description" 
			value="<?php echo $pba_channel_description; ?>" size="50" /> 
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Feed Channel Language Code:
	</th>
	<td>
			<input type="text" maxlength="10" name="pba_channel_language" 
			value="<?php echo $pba_channel_language; ?>" size="10" /> 
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Feed Channel License:
	</th>
	<td>
			<input type="text" maxlength="100" name="pba_channel_copyright" 
			value="<?php echo $pba_channel_copyright; ?>" size="50" /> 
	</td>
</tr>

<tr valign="top">
	<td align="left" colspan="2">
		<h4>Calendar features</h4>
		<p>The following values affect all calendar related features</p>
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Template of calendar page:
	</th>
	<td><textarea cols="60" rows="10" name="pba_otemplate_kalender"><?php
echo $pba_otemplate_kalender;
?></textarea>
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Format of calendar dates:
	</th>
	<td>
		<input type="text" name="pba_oarchive_date_format" 
			value="<?php echo $pba_oarchive_date_format; ?>" size="30" /><br />
		(Uses <a href="http://www.php.net/date">PHP date format</a>)
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Months in calendar:
	</th>
	<td>
		<input type="text" name="pba_okalendermonthslist" 
			value="<?php echo $pba_okalendermonthslist; ?>" size="30" />
	</td>
</tr>

<tr valign="top">
	<td align="left" colspan="2">
		<p>Additional values just for the calendar box</p>
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Caption id for calendar box table:
	</th>
	<td>
		<input type="text" name="pba_okalenderboxtablecaption" 
			value="<?php echo $pba_okalenderboxtablecaption; ?>" size="20" />
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Caption: Earlier:
	</th>
	<td>
			<input type="text" name="pba_okalender_last" 
			value="<?php echo $pba_okalender_last; ?>" size="10" /><br />
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Caption: Later
	</th>
	<td>
			<input type="text" name="pba_okalender_next" 
			value="<?php echo $pba_okalender_next; ?>" size="10" /><br />
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		List of weeks:
	</th>
	<td>
			<input type="text" name="pba_okalenderboxdaysofweeklist" 
			value="<?php echo $pba_okalenderboxdaysofweeklist; ?>" size="50" /><br />
	</td>
</tr>

<tr valign="top">
	<td align="left" colspan="2">
		<h4>Free flow parameter overwriting</h4>
		<p>The following box allows to overwrite any default parameter</p>
	</td>
</tr>

<tr valign="top">
	<td>Free parameter input:<br>
		Syntax: <br>
		###SUPERPARAMETER_Defaultparameter1_BEGIN###
		New value for defaultparameter1
		###SUPERPARAMETER_END###
		###SUPERPARAMETER_Defaultparameter2_BEGIN###
		New value for defaultparameter2
		###SUPERPARAMETER_END###
		etc, any newline breaks will be recognized as 
		part of the parameter value, single true / false
		will be converted to bool true and false
	</td>
	<td><textarea cols="60" rows="10" name="pbao_superparameter"><?php
echo $pbao_superparameter;
?></textarea>
	</td>
</tr>


<tr valign="top">
	<th  width="40%" scope="row" align="left">
		Hit the save button to save this configuration:<br />&nbsp;<br />&nbsp;<br />
	</th>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $pba_editpbaoutput_button; ?>
	</td>
</tr>

</table>

<script type="text/javascript">
document.write('</div>');
</script>

</form>

</fieldset>

<?php
}
?>