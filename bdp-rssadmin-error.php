<?php 
if ($user_level < 6) die ( __('Cheatin&#8217; uh?') ); 

if($rss) 
	$result = $bdprss_db->get_site_by_id(intval($rss));
else
	$result ='';

if($result)
	$url = $result->{$bdprss_db->cfeedurl};
else
	$url = '';

$result = $bdprss_db->getErrors($url);
$cache = array();
$sitename = false;
?>
<h2>Recent feed errors</h2>
<?php
if($result)
{
?>

	<table width="100%" cellpadding="3" cellspacing="3">
	<tr><th align='left'>Feed</th><th>#</th><th>Time of error</th><th align='left'>Error</th></tr>
	
<?php
	foreach($result as $r)
	{
			$url = $r->{$bdprss_db->efeedurl};
			$id = $r->{$bdprss_db->eidentifier};
			$ticks = $r->{$bdprss_db->etime};
			$error = $r->{$bdprss_db->etext};
			
			$error = preg_replace("/&#39;/", "'", $error);
			$error = preg_replace('/&quot;/', '"', $error);

			if(isset($cache[$url]))$sitename = $cache[$url];
			if(!$sitename)
			{
				$result = $bdprss_db->get_site($url);
				if($result)
				{
					$sitename = $result->{$bdprss_db->csitename};
					$cache[$url] = $sitename;
				}
			}
			
			if($sitename)
				$url = "<a href='$url'>$sitename</a>";
			else
				$url = "<a href='$url'>$url</a>";

			$age = PBALIB::gettheage($ticks);
			
			echo "<tr align='center'><td align='left'>$url</td><td>$id</td>".
				"<td>$age</td><td align='left'>".$error."</td></tr>\n";
	}
?>
	</table>
<?php
}
else
{
?>
	<p>No errors found.</p>
<?php
}
?>
