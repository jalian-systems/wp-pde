<?php
/*
Based on the Instrument Hooks for WordPress plugin. Modified to suit wp-pde.
Description: Instruments Hooks for a Page. Outputs during the Shutdown Hook.
Version: 0.1
Original Author: Mike Schinkel
Original Author URI: http://mikeschinkel.com


Usage:
	wp-pde uses a hooks table to populate the actions/filters dropdown boxes. You need to create the table using
	the following instructions.
	For initializing the hooks table:
		goto: http://example.com?instrucment=hooks&init=1	
	For creating hooks invoked for a particular page
		if the URL is of form http://example.com/page add ?instrument=hooks to the end of the URL
		if the URL is of form http://example.com/page?q=xx add &instrument=hooks to the end of the URL
*/

if (isset($_GET['instrument']) && $_GET['instrument']=='hooks') {

	add_action('shutdown','wppde_instrument_hooks');
	function wppde_instrument_hooks() {
		global $wpdb;
		$hooks = $wpdb->get_results("SELECT * FROM wp_pde_hook_list ORDER BY hook_name");
		$html = array();
		$html[] = '<style>#instrumented-hook-list table,#instrumented-hook-list th,#instrumented-hook-list td {border:1px solid gray;padding:2px 5px;}</style>
<div align="center" id="instrumented-hook-list">
	<table>
		<tr>
		<th>Hook Name</th>
		<th>Hook Type</th>
		<th>Arg Count</th>
		</tr>';
		foreach($hooks as $hook) {
			$html[] = "<tr>
			<td>{$hook->hook_name}</td>
			<td>{$hook->hook_type}</td>
			<td>{$hook->arg_count}</td>
			</tr>";
		}
		$html[] = '</table></div>';
		echo implode("\n",$html);
	}

	add_action('all','wppde_record_hook_usage');
	function wppde_record_hook_usage($hook){
		global $wpdb;
		static $in_hook = false;
		static $first_call = 1;
		$callstack = debug_backtrace();
		if (!$in_hook) {
			$in_hook = true;
			$args = func_get_args();
			$arg_count = count($args)-1;
			$hook_type = str_replace('do_','',
				str_replace('apply_filters','filter',
					str_replace('_ref_array','',
						$callstack[3]['function'])));
			$results = $wpdb->get_results("SELECT * FROM wp_pde_hook_list WHERE hook_name = '$hook' AND hook_type = '$hook_type'");
			if(count($results)==0)
				$wpdb->query("INSERT wp_pde_hook_list
					(hook_name,hook_type,arg_count)
					VALUES ('$hook','$hook_type',$arg_count)");
			$first_call++;
			$in_hook = false;
		}
	}

	if (isset($_GET['init']) && $_GET['init'] == '1') {
		$results = $wpdb->get_results("SHOW TABLE STATUS LIKE 'wp_pde_hook_list'");
		if (count($results)==1) {
			$wpdb->query("TRUNCATE TABLE wp_pde_hook_list");
		} else {
			$wpdb->query("CREATE TABLE wp_pde_hook_list (
			hook_name varchar(96) NOT NULL,
			hook_type varchar(15) NOT NULL,
			arg_count tinyint(4) NOT NULL,
			PRIMARY KEY (hook_name))"
			);
		}
	}
}
