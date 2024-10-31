<?php 

if ($user_level < 6) die ( __('Cheatin&#8217; uh?') ); 

?>

<h2>Edit list filter #<?php echo $list; ?></h2>

<fieldset>

<form method="post" action="<?php echo $selfreference; ?>">

<?php 

global $bdprss_db;

$result = $bdprss_db->get_list($list);
if(!$result) {
	echo "<p><b>Unexpected error:</b> could not find output format #$list </p>\n";
} else {
	// setup stuff
	$lname = bdpDisplayCode($result->{$bdprss_db->lname});
	$llistall = $result->{$bdprss_db->llistall};

	$lurls = $result->{$bdprss_db->lurls};
	$ids = preg_split("','", $lurls, -1, PREG_SPLIT_NO_EMPTY);
	//for($i = 0; $i < sizeof($ids); $i++) echo "DEBUG '$ids[$i]'\n";		/* DEBUG */

?>

<table width="100%" cellspacing="2" cellpadding="5" class="editform">
<tr valign="top"><td align="left" colspan="2"><h4>About this list</h4></td></tr>
<tr valign="top">
	<th  width="40%" scope="row">
		List ID:
	</th>
	<td>
		<?php echo $list; ?>
		<input type="hidden" name="bdprss_lidentifier" value="<?php echo $list; ?>" />
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Name of list filter:
	</th>
	<td>
		<input type="text" name="bdprss_lname" value="<?php echo $lname; ?>" size="50" /><br />
		(Only used for list management)
	</td>
</tr>

<tr valign="top">
	<th  width="40%" scope="row">
		Disable filter (List all sites):
	</th>
	<td>
		<input type="checkbox" name="bdprss_llistall" 
			value="Y" <?php if($llistall == 'Y') echo ' checked="checked"'; ?>>
			(If checked, the filter will not filter and all items are displayed)
	</td>
</tr>

<tr valign="top"><td align="left" colspan="2"><h4>Manage the sites</h4></td></tr>

<tr><td colspan="2">
<table width="100%" cellpadding="3" cellspacing="3">
<?php

	global $bdprss_db;
	$result = $bdprss_db->get_all_sites();

	if($result) {
		echo "<tr align='center'><td><b>Check</b></td><td><b>ID</b></td>".
			"<td align='left'><b>Feed</b></td><td><b>Cache Last Updated</b></td></tr>\n";
		$class = '';

		foreach($result as $r) {
			$id = $r->{$bdprss_db->cidentifier};
			$url = $r->{$bdprss_db->cfeedurl};
			$site = $r->{$bdprss_db->csitename};
			
			$updated = PBALIB::gettheage( $r->{$bdprss_db->cupdatetime} );
			$name = ($site == '') ? $url : "<a href='$url' title='$url'>"."$site</a>";
			
			$class = ('alternate' == $class) ? '' : 'alternate';
			
			echo "<tr class='$class' valign='middle' align='center'>\n";
			echo "\t<td><input type='checkbox' name='bdprss_feed_$id' value='1'";
			if(array_search($id, $ids) !== false) echo " checked='checked'";
			echo "></td>\n";

			echo "\t<td>$id</td>\n";
			echo "\t<td align='left'>$name</td>\n";
			echo "\t<td>$updated</td>\n";
			echo "</tr>\n";
		}
	}
?>
</table>
</td></tr>


<tr valign="top"><td align="left" colspan="2"><h4>And hit the edit button to complete</h4></td></tr>

<tr valign="top">
	<td colspan="2" align="right">
		<input type="submit" name="bdprss_edit_list_button" value="Edit &raquo;" />
	</td>
</tr>



</table>

</form>

</fieldset>

<?php
}
?>