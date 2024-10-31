<?php 

if ($user_level < 6) die ( __('Cheatin&#8217; uh?') ); 

global $bdprss_db, $bdprss_search;

$pbastartconfig=0;
if(isset($_POST['pbstart'])){
	$pbastartconfig=abs(intval($_POST['pbstart']));
}elseif(isset($_GET['pbstart'])){
	$pbastartconfig=abs(intval($_GET['pbstart']));
}


//let's check if basic functionality is working
$pba_wp_pages=$bdprss_db->get_wp_published_pages();
$pba_all_outputs=$bdprss_db->get_all_pbaoutputs();

if(isset($pba_all_outputs[0]->page2hookin)) {
	$pba_page_hooked_in['id']=$pba_all_outputs[0]->page2hookin;
	//echo '<br>We hooked into page: ' . $pba_page_hooked_in['id'];
	if($pba_page_hooked_in['id'] > 0){
		//let's get some more information about that page
		foreach($pba_wp_pages as $page_index => $pba_wp_page){
			//print_r($pba_wp_page);
			if($pba_wp_page->ID == $pba_page_hooked_in['id']){
				$pba_page_hooked_in['title']=preg_replace(array('/"/',"/'/"), array('&quot;','&#39;'), $pba_wp_page->post_title);
				//echo '<br>Title of that page is: ' . $pba_page_hooked_in['title'];
				$pba_page_hooked_in['guid']=$pba_wp_page->guid;
				//echo '<br>guid of that page is: ' . $pba_page_hooked_in['guid'];
			}
		}
		if(function_exists('get_page_link')) {
			$pba_page_href = get_page_link($pba_page_hooked_in['id']);
			//echo '<br>href to that page is: ' . $pba_page_href;
		}elseif(isset($pba_page_hooked_in['guid'])){
			$pba_page_href = $pba_page_hooked_in['guid'];
		}
	}// end if isset($pba_all_outputs[0]->page2hookin)
	//check if we get output
	$pba_whileloopcounter=1;
	while ($pba_whileloopcounter <= 3) {
		$pba_whileloopcounter++;
		$pba_config['outputid']=$pba_all_outputs[0]->identifier;
		$pba_config["maxitems"]=1;
		if($pbastartconfig==0) $pba_config['listid']=0;
		$pba_result=@PBA::outputwrapper($pba_config);
		$pba_found_items=$pba_result['founditems'];
		if($pba_found_items > 1){
			//echo "You have already " . $pba_found_items . " items in your Aggregator.";
			break;
		}else{
			echo '<p>Please be patient for a minute. The Parteibuch Aggregator tries to poll some feeds for you.</p>';
			flush();
			$bdprss_db->updateAll();
		}
	}// end while loop
} // end if isset($pba_all_outputs[0]->page2hookin)

if($pbastartconfig == 1 && $pba_found_items > 0){
	$bdprss_db->process_new();
	echo '<h2>Congratulations!</h2>';
}else{
	echo '<h2>Programmer&#39;s hell - Status page of the <a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php">Parteibuch Aggregator</a> plugin</h2>';
}

echo '<table width="100%" cellspacing="2" cellpadding="5" class="editform">';

if($pbastartconfig == 1 && $pba_found_items > 0){

	echo '<tr valign="top">
		<td align="left" colspan="2"><h4>You successfully managed to install and configure the <a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php">Parteibuch Aggregator</a> plugin.</h4>';

	echo '<br><br>Your Parteibuch Aggregator aggregated already ' . $pba_found_items . ' items.';

	echo '<br><br><b>So what&#39;s next?</b>';
	
	if(isset($pba_page_href)){
		echo '<br><br>Maybe you want to see your RSS page "'.$pba_page_hooked_in['title'].'"? So check it out <a target="_blank" title="Opens in new window: &quot;'.$pba_page_hooked_in['title'].'&quot;with output collected by the Parteibuch Aggregator" href="'.$pba_page_href.'">here</a>. Does it fit your likings? Fine. 
		
		<br><br>Or do you want to change the layout of the output? If so, go into <a title="Modify configuration of output #1" href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php&action=editpbaoutput&pboutput=1">additional parameters of the output</a> and modify it.';
	}
	if(isset($pba_all_outputs[0])){
		echo '<br><br>Maybe you want to show RSS headlines in the sidebar of your blog? So just <a title="Put a RSS Aggregator widget into your sidebar" href="widgets.php">take the RSS Aggregator widget</a>.';
		
		echo "<br><br>Maybe you don't use widgets and you want to add the Parteibuch Aggregator sidebar code manually to your sidebar? <br/>
		So here is the code to add to your sidebar:<br/><br/>
		\$pba_config['outputid']=1; <br/>
		\$pba_config['show_sidebarwidget'] = 'Y'; <br/>
		\$pba_return=@PBA::outputwrapper(\$pba_config); <br/>
		echo \$pba_return['result'];";
	}
	
	echo '<br><br>Maybe you want to add your favorite feeds to your aggregator or find out about the options of your aggregator? So just go to your <a title="Go to Parteibuch Aggregator management page" href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php">Parteibuch Aggregator management page</a>.';

//print_r($pba_all_outputs);
//print_r($pba_wp_pages);

	echo '</td>
	</tr>';
} elseif($pbastartconfig == 1) {
		echo '<tr valign="top">';
		echo '<td align="left" colspan="2"><h2>Oops, what&#39;s going on here?</h2>';
		echo '<br><br>You successfully installed the Parteibuch Aggregator, but we can&#39;t find any aggregated items. It may happen that the feed we tried to poll is just not available or not readable at the moment. We recommend <a title="Add your favorite feeds to your RSS Aggregator" href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php#addfeeds">to add your favorite feeds</a> or just wait for some hours until the preinstalled feed become readable again.';
	echo '</td>
	</tr>';

}else{
	echo '<tr valign="top">';
	echo '<td align="left" colspan="2"><h4>Some basic information</h4>';
	echo '</td>
		</tr>';

echo '<tr valign="top">
	<td width="40%" align="right">
	Available items:&nbsp;
	</td>
	<td>
	'.$pba_found_items .'
	</td>
	</tr>';

	echo '<tr valign="top">';
	echo '<td align="left" colspan="2"><h4>Information on hooked pages</h4>';
	echo '</td>
		</tr>';

	foreach($pba_all_outputs as $pba_output_index => $pba_all_output){
		$pageshookedin[$pba_all_output->page2hookin]=$pba_all_output->identifier;
	}

	foreach($pba_wp_pages as $page_index => $pba_wp_page){
		if(isset($pageshookedin[$pba_wp_page->ID])){
		echo '<tr valign="top">
			<td width="40%" align="right">
			Page '.$pba_wp_page->ID.':&nbsp;
			</td>
			<td>
			hooked by Output ID '.$pageshookedin[$pba_wp_page->ID] .'
			</td>
			</tr>';
		} //end if isset($pageshookedin[$pba_wp_page->ID])
	} // end foreach($pba_wp_pages as $page_index => $pba_wp_page)

	echo '<tr valign="top">';
	echo '<td align="left" colspan="2"><h4>Information about serverstatus variable</h4>';
	echo '</td>
	</tr>';

	foreach($bdprss_db->serverstatus as $stproperty => $stvalue){
		echo '<tr valign="top">
			<td width="40%" align="right">
			&quot;'.$stproperty.'&quot;:&nbsp;
			</td>
			<td>
			'.PBALIB::get_r($stvalue) .'
			</td>
			</tr>';
	}

	echo '<tr valign="top">';
	echo '<td align="left" colspan="2"><h4>Information from options table</h4>';
	echo '</td>
		</tr>';

	foreach($bdprss_db->get_all_options() as $optionkey => $optionvalue){
		foreach($optionvalue as $optionkey => $optionvaltmp){
			if($optionkey != 'name') $optionvaltmp2[$optionkey] = $optionvaltmp;
		}
		echo '<tr valign="top">
			<td width="40%" align="right">
			Option &quot;'.$optionvalue->name.'&quot;:&nbsp;
			</td>
			<td>
			'.PBALIB::get_r($optionvaltmp2) .'
			</td>
			</tr>';
	} // end foreach($bdprss_db->get_all_options() as $optionkey => $optionvalue)

	echo '<tr valign="top">';
	echo '<td align="left" colspan="2"><h4>Information about the Parteibuch Aggregator database tables</h4>';
	echo '</td>
	</tr>';

	$pba_tables=$bdprss_db->list_all_tables();
	foreach($pba_tables as $pba_table => $dummy){
		$tablestatus=$bdprss_db->get_mysql_tablestatus($pba_table);
		echo '<tr valign="top">
			<td width="40%" align="right">
			Table &quot;'.$pba_table.'&quot;:&nbsp;
			</td>
			<td>
			'.PBALIB::get_r($tablestatus) .'
			</td>
				</tr>';
	}//end foreach pba tables
	
	echo '<tr valign="top">';
	echo '<td align="left" colspan="2"><h4>Information on wordpress rewrite rules option setting</h4>';
	echo '</td>
	</tr>';

	echo '<tr valign="top">';
	echo '<td align="left" colspan="2">';
	echo "This blog's wordpress option rewrite_rules is set to: " . str_replace("\n",'<br>',PBALIB::get_r(get_option('rewrite_rules')));
	echo '</td>
	</tr>';


	echo '<tr valign="top">';
	echo '<td align="left" colspan="2"><br><br><b>I have seen enough about this hell, let me out here going <a href="' . $selfreference . '">back to the Parteibuch Aggregator admin page</a></b>';
	echo '</td>
	</tr>';

}//end no pbstart

echo '</table>';

//echo 'pbastartconfig is: ' . $pbastartconfig;

?>