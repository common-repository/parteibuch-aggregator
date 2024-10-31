<?php 

if ($user_level < 6) die ( __('Cheatin&#8217; uh?') ); 

$result = $bdprss_db->get_site_by_id($rss);
if($result) $cidentifier = $result->{$bdprss_db->cidentifier};

?>

<h2><a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php">Parteibuch Aggregator</a>: Edit site & feed information</h2>


<fieldset>

<form method="post" action="<?php echo $selfreference; ?>">


<?php 

if(!$result || ($rss != $cidentifier) ) {
	echo "<p><b>Unexpected error:</b> could not find feed $rss, or it differed from $cidentifier</p>\n";
} else {
	$cfeedurl = 			bdpDisplayCode($result->{$bdprss_db->cfeedurl});
	$csitename =			bdpDisplayCode($result->{$bdprss_db->csitename});
	$cdescription = 		bdpDisplayCode($result->{$bdprss_db->cdescription});
	$csitelicense =				bdpDisplayCode($result->{$bdprss_db->csitelicense});
	$csiteurl =				bdpDisplayCode($result->{$bdprss_db->csiteurl});
	$cgmtadjust = 			$result->{$bdprss_db->cgmtadjust};
	$csitenameoverride = 	$result->{$bdprss_db->csitenameoverride};
	$cpollingfreqmins = $result->{$bdprss_db->cpollingfreqmins};
	$cnextpolltime = $result->{$bdprss_db->cnextpolltime};
	$ccatchtextfromhtml = $result->{$bdprss_db->ccatchtextfromhtml};
	$ccatchhtmlparas = $result->{$bdprss_db->ccatchhtmlparas};
	$ccatchhtmlparas=unserialize($ccatchhtmlparas);

	if(isset($ccatchhtmlparas['snoopy_max_redirs'])) {
		$pba_loader_sitedef_paras['fetchparas']['snoopy_max_redirs'] = $ccatchhtmlparas['snoopy_max_redirs'];
	}else{
		$pba_loader_sitedef_paras['fetchparas']['snoopy_max_redirs'] = 0;
	}

	if(isset($ccatchhtmlparas['reg_content_part'][1])) {
		$pba_loader_sitedef_paras['parseparas']['reg_content_part'][1] = bdpDisplayCode($ccatchhtmlparas['reg_content_part'][1]);
	}else{
		$pba_loader_sitedef_paras['parseparas']['reg_content_part'][1] = '';
	}

	if(isset($ccatchhtmlparas['reg_content_part'][2])) {
		$pba_loader_sitedef_paras['parseparas']['reg_content_part'][2] = bdpDisplayCode($ccatchhtmlparas['reg_content_part'][2]);
	}else{
		$pba_loader_sitedef_paras['parseparas']['reg_content_part'][2] = '';
	}

	if(isset($ccatchhtmlparas['contentpartseparator'])) {
		$pba_loader_sitedef_paras['parseparas']['contentpartseparator'] = $ccatchhtmlparas['contentpartseparator'];
	}else{
		$pba_loader_sitedef_paras['parseparas']['contentpartseparator'] = '';
	}

	if(isset($ccatchhtmlparas['prebase_urls']) && $ccatchhtmlparas['prebase_urls'] =='Y') {
		$pba_loader_prebase_urls = 'Y';
	}else{
		$pba_loader_prebase_urls = 'N';
	}

	if(isset($ccatchhtmlparas['convert_charset']) && $ccatchhtmlparas['convert_charset'] =='Y') {
		$pba_loader_convert_charset = 'Y';
	}else{
		$pba_loader_convert_charset = 'N';
	}
	
	$pba_csitecomment =				bdpDisplayCode($result->{$bdprss_db->csitecomment});
	
?>


	<table width="100%" cellspacing="2" cellpadding="5" class="editform">

	<tr><td colspan='2'><h3>Site information</h3></td></tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Feed URL:
		</th>
		<td>
			<?php echo "$cfeedurl [id:$cidentifier]"; ?>
			<input type="hidden" name="bdprss_cidentifier" value="<?php echo $cidentifier; ?>" />
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Feed name:
		</th>
		<td>
			<input type="text" name="bdprss_csitename" value="<?php echo $csitename; ?>" size="50" />
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Feed description:
		</th>
		<td>
			<textarea cols="50" rows="4" name="bdprss_cdescription"><?php echo $cdescription; ?></textarea>
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Feed License:
		</th>
		<td>
			<input type="text" name="bdprss_csitelicense" value="<?php echo $csitelicense; ?>" size="50" />
			<br />If the license could not be detected automatically, put in here the license of the feed you aggregate 
			like Public Domain, Piratenlizenz, CC-BY, "owner allowed me to redistribute". If unsure, ask the 
			feed owner about his license modell before you aggregate his feed. 
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Site URL:
		</th>
		<td>
			<input type="text" name="bdprss_csiteurl" value="<?php echo $csiteurl; ?>" size="50" />
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Override site origin information from feed:
		</th>
		<td>
			<input type="checkbox" name="bdprss_csitenameoverride" 
				value="Y" <?php if($csitenameoverride == 'Y') echo ' checked="checked"'; ?>>
				This must be set to save the above information. <b>Attention</b>: Without having this button 
				checked, the Parteibuch Agregator will assume this feed a feed from another aggregator and 
				try to find a name of origin for each item seperately and take this value as site name. Sometimes 
				the origin resolves to values like "admin", so be sure to check this button for all single 
				site feeds.
		</td>
	</tr>

	<tr><td colspan='2'><h3>Edit feed handling options</h3></td></tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			GMT adjustment:
		</th>
		<td>
			<input type="text" name="bdprss_cgmtadjust" value="<?php echo $cgmtadjust; ?>" size="4" />
			hours<br>
			Use range: -48.0 &lt;= adjustment &lt;= 48.0<br>
			Note: this field will be saved even if the override is not set.<br>
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Set a non-standard site polling frequency:
		</th>
		<td>
			<input type="text" name="bdprss_cpollingfreqmins" 
				value="<?php echo $cpollingfreqmins; ?>" size="7" /> minutes<br>
			Use range: 0 &lt;= frequency &lt;= 1000000<br>
			0 means use the standard site polling frequency.<br>
			Note: day = 1440 min, week = 10080 min<br>
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Change feed url:
		</th>
		<td>
			<input type="text" name="bdprss_cnewfeedurl" value="" size="50" />
			<input type="hidden" name="bdprss_coldfeedurl" value="<?php echo $cfeedurl; ?>" />
			<br />leave empty for not touching feed url
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Change next poll timestamp:
		</th>
		<td>
			<input type="text" name="pba_cnewnextpolltime" value="" size="10" maxlength="10" />
			<?php echo '<br />value was: '.$cnextpolltime.' that means: ' .date('r', $cnextpolltime); ?>
			<br />leave empty for not touching next poll timestamp
			<br />if you want to disable this feed, enter 2147483647
		</td>
	</tr>


<?php
	$listresult = $bdprss_db->get_all_lists();
		if($listresult) {
		echo "<tr><td colspan='2'><h3>List inclusions</h3></td></tr>\n";
			$class = '';
			foreach($listresult as $r) {
				$id = $r->{$bdprss_db->lidentifier};
				$name = htmlspecialchars($r->{$bdprss_db->lname});
				$llistall = $r->{$bdprss_db->llistall};
				$lurllist= ',' . $r->{$bdprss_db->lurls} . ',';
				$class = ('alternate' == $class) ? '' : 'alternate';

				echo '<tr valign="top">
					<th  width="40%" scope="row">
					List ID '.$id.' - '.$name.':
					</th>';
				$checked="";
				if(strstr($lurllist, ','. $cidentifier . ',')) $checked=" checked ";
				$dummyfilter=" Check to include this feed into this list";
				if($llistall == 'Y') $dummyfilter=" List $id will display all items anyway.";
				echo '<td>
					<input type="checkbox" name="pba_site_included_in_list_'.$id.'" 
					value="Y" '.$checked.'  >
					'.$dummyfilter.'
					</td>
					</tr>';

				
			}
			
		}
	
?>

<tr><td colspan='2'><h3>HTML Parser options</h3></td></tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Parse information from HTML:<br />&nbsp;
		</th>
		<td>
			<input type="checkbox" name="pba_ccatchtextfromhtml" 
				value="Y" <?php if($ccatchtextfromhtml == 'Y') echo ' checked="checked"'; ?>>
				Check this to enable the following arameters. <br /><b>Attention</b>: Do only enable this option if you know what you are doing. 
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Snoopy max redirs:
		</th>
		<td>
			<input type="text" name="pba_loader_snoopy_max_redirs" 
				value="<?php if(isset($pba_loader_sitedef_paras['fetchparas']['snoopy_max_redirs']))echo $pba_loader_sitedef_paras['fetchparas']['snoopy_max_redirs']; ?>" size="7" /> 
			Use range: 0 - 9, default is 0
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Regex for content part 1:
		</th>
		<td>
			<input type="text" name="pba_loader_reg_content_part_1" 
				value="<?php if(isset($pba_loader_sitedef_paras['parseparas']['reg_content_part'][1]))echo $pba_loader_sitedef_paras['parseparas']['reg_content_part'][1]; ?>" size="60" /> 
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Regex for content part 2:
		</th>
		<td>
			<input type="text" name="pba_loader_reg_content_part_2" 
				value="<?php if(isset($pba_loader_sitedef_paras['parseparas']['reg_content_part'][2])) echo $pba_loader_sitedef_paras['parseparas']['reg_content_part'][2]; ?>" size="60" /> 
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Merge content parts with:
		</th>
		<td>
			<input type="text" name="pba_loader_contentpartseparator" 
				value="<?php if(isset($pba_loader_sitedef_paras['parseparas']['contentpartseparator']))echo $pba_loader_sitedef_paras['parseparas']['contentpartseparator']; ?>" size="20" /> 
			What code to put between content parts
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Extra base filter for urls:
		</th>
		<td>
			<input type="checkbox" name="pba_loader_prebase_urls" 
				value="Y" <?php if($pba_loader_prebase_urls == 'Y') echo ' checked="checked"'; ?>>
		</td>
	</tr>

	<tr valign="top">
		<th  width="40%" scope="row">
			Convert charset encoding:
		</th>
		<td>
			<input type="checkbox" name="pba_loader_convert_charset" 
				value="Y" <?php if($pba_loader_convert_charset == 'Y') echo ' checked="checked"'; ?>>
		</td>
	</tr>

<tr><td colspan='2'><h3>Admin comments</h3></td></tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Just a comment as a memo for admin:
	</th>
	<td><textarea cols="60" rows="10" name="pba_csitecomment"><?php
echo $pba_csitecomment;
?></textarea>
	</td>
</tr>


	<tr valign="top">
		<td colspan="2" align="right">
			<input type="submit" name="bdprss_edit_site_button" value="Edit &raquo;" />
		</td>
	</tr>

	</table>

<?php
}
?>
</form>
</fieldset>
