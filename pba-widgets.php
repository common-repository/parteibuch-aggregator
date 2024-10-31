<?php

	//let's make some widgets
	//Thanks to brainfart for the lesson ;-)
	//http://brainfart.com.ua/post/lesson-wordpress-multi-widgets/

		add_action('init', 'pba_register_widgets');

function pba_register_widgets() {
	if(!function_exists('wp_register_sidebar_widget')) return false;

	$prefix = 'pba_itemtitles'; // $id prefix
	$name = __('RSS Aggregator');
	$widget_ops = array('classname' => 'widget_name_multi', 'description' => __('Add item titles of your aggregated feeds to your blog&#39;s sidebar'));
	$control_ops = array('width' => 200, 'height' => 200, 'id_base' => $prefix);
 
	$options = get_option('widget_name_multi');
	if(isset($options[0])) unset($options[0]);
 
	if(!empty($options)){
		foreach(array_keys($options) as $widget_number){
			wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'widget_name_multi', $widget_ops, array( 'number' => $widget_number ));
			wp_register_widget_control($prefix.'-'.$widget_number, $name, 'widget_name_multi_control', $control_ops, array( 'number' => $widget_number ));
		}
	} else{
		$options = array();
		$widget_number = 1;
		wp_register_sidebar_widget($prefix.'-'.$widget_number, $name, 'widget_name_multi', $widget_ops, array( 'number' => $widget_number ));
		wp_register_widget_control($prefix.'-'.$widget_number, $name, 'widget_name_multi_control', $control_ops, array( 'number' => $widget_number ));
	}
}

function widget_name_multi($args) {
	if(!function_exists('wp_register_sidebar_widget')) return false;
		$prefix = 'pba_itemtitles'; // $id prefix
    extract($args);
		$options = get_option('widget_name_multi');

//    echo "<br>See the calling arguments for the widget with id " . $widget_id . ":<br>";
//    print_r($args);
//    echo "<br>See all the saved options:";
//    print_r($options);

		if(preg_match('/'.$prefix.'-([0-9]+)/i', $widget_id, $match)){
			$widget_number = $match[1];
			//echo "<br>Fine, this is our widget number " . $widget_number;
			if(isset($options[$widget_number])){
				$pba_option_set=$options[$widget_number];
//				echo "<br>Great, we have an option set, which we can use as input parameter to our stuff: ";
				//print_r($pba_option_set);
				echo $before_widget; echo $before_title; 
				if(isset($pba_option_set['title'])) echo $pba_option_set['title'];
				echo $after_title;
				if(isset($pba_option_set['pba_outputid'])) {
					//echo "Output ID: " . $pba_option_set['pba_outputid'];
					$pba_config['outputid']=$pba_option_set['pba_outputid'];
					$pba_config['show_sidebarwidget'] = 'Y';
					$pba_return=@PBA::outputwrapper($pba_config);
					echo $pba_return["result"];
				}
				echo $after_widget;
			}
		}
 }

function widget_name_multi_control($args) {
 	if(!function_exists('wp_register_sidebar_widget')) return false;

	$prefix = 'pba_itemtitles'; // $id prefix
 
	$options = get_option('widget_name_multi');
	if(empty($options)) $options = array();
	if(isset($options[0])) unset($options[0]);
 
	// update options array
	if(!empty($_POST[$prefix]) && is_array($_POST)){
		foreach($_POST[$prefix] as $widget_number => $values){
			if(empty($values) && isset($options[$widget_number])) // user clicked cancel
				continue;
 
			if(!isset($options[$widget_number]) && $args['number'] == -1){
				$args['number'] = $widget_number;
				$options['last_number'] = $widget_number;
			}
			$options[$widget_number] = $values;
		}
 
		// update number
		if($args['number'] == -1 && !empty($options['last_number'])){
			$args['number'] = $options['last_number'];
		}
 
		// clear unused options and update options in DB. return actual options array
		$options = bf_smart_multiwidget_update($prefix, $options, $_POST[$prefix], $_POST['sidebar'], 'widget_name_multi');
	}
 
	// $number - is dynamic number for multi widget, gived by WP
	// by default $number = -1 (if no widgets activated). In this case we should use %i% for inputs
	//   to allow WP generate number automatically
	$number = ($args['number'] == -1)? '%i%' : $args['number'];
 
	// now we can output control
	$opts = @$options[$number];
 
	$title = @$opts['title'];
 
  echo 'Please enter a name for your widget:<br />'; 
	echo '<input type="text" ' . 
		'name="' . $prefix . '[' . $number . '][title]" ' . 
		'value="' . $title . '" /><br /><br />';

	global $bdprss_db;
	$pba_output_streams=$bdprss_db->get_all_pbaoutputs();
	if($pba_output_streams) {
		//print_r($pba_output_streams);
		echo 'Please select an output stream:<br />'; 
		echo '<select name="' . $prefix . '[' . $number . '][pba_outputid]">';
		$didselect=false;
		foreach($pba_output_streams as $pbaosnr => $pba_os_object){
			$selected="";
			if($pba_os_object->identifier == @$opts['pba_outputid']) {
				$selected=" selected ";
				$didselect=true;
			}
			if(($pbaosnr + 1) == count($pba_output_streams) && !$didselect) $selected=" selected ";
			echo '<option value="'.$pba_os_object->identifier.'"'.$selected.' >ID: '.$pba_os_object->identifier.'&nbsp;-&nbsp;'.$pba_os_object->name.'&nbsp;</option>' . "\n";
		}
		echo '</select>';
	}else{
		echo 'Parteibuch Aggregator could not detect any output format to show in this widget. 
			You need to <a href="edit.php?page=parteibuch-aggregator/bdp-rssadmin.php&action=createpbaoutput">create an output format</a> first.';
	}
}

// helper function can be defined in another plugin
if(!function_exists('bf_smart_multiwidget_update')){
	function bf_smart_multiwidget_update($id_prefix, $options, $post, $sidebar, $option_name = ''){
		global $wp_registered_widgets;
		static $updated = false;
 
		// get active sidebar
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();
 
		// search unused options
		foreach ( $this_sidebar as $_widget_id ) {
			if(preg_match('/'.$id_prefix.'-([0-9]+)/i', $_widget_id, $match)){
				$widget_number = $match[1];
 
				// $_POST['widget-id'] contain current widgets set for current sidebar
				// $this_sidebar is not updated yet, so we can determine which was deleted
				if(!in_array($match[0], $_POST['widget-id'])){
					unset($options[$widget_number]);
				}
			}
		}
 
		// update database
		if(!empty($option_name)){
			update_option($option_name, $options);
			$updated = true;
		}
 
		// return updated array
		return $options;
	}
}

?>