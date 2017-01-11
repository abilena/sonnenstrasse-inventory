<?php
/*
Plugin Name: RP Inventory
Plugin URI: https://wordpress.org/plugins/rp-inventory/
Description: This plugin allows you to display an inventory manager for rpgs in your posts using the shortcode [inventory][/inventory].
Version: 1.00
Author: Klemens
Author URI: https://profiles.wordpress.org/Klemens#content-plugins
Text Domain: rp-inventory
*/ 

function rp_inventory_shortcode($atts, $content) {
   	global $wpdb;
  	global $db_table_name;

	extract(shortcode_atts(array(
		'title' => __('RP Inventory', 'rp-inventory'),
		'name' => 'name',
        'style' => 'default'
	), $atts));

	$title = esc_attr($title);

    $db_result = print_r($wpdb->get_results("show tables"));

	$output  = "\n<div class=\"rp-inventory\">\n";
	$output .= $name . " is " . $db_result;
	$output .= "\n</div>\n";

	return $output;
}
add_shortcode ('rp-inventory', 'rp_inventory_shortcode');







$db_table_name = $wpdb->prefix . 'rp_inventory';
 
// function to create the DB / Options / Defaults					
function rp_inventory_install() {
   	global $wpdb;
  	global $db_table_name;
 
	// create the ECPT metabox database table
	if($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) 
	{
		$sql = "CREATE TABLE " . $db_table_name . " (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`field_1` mediumtext NOT NULL,
		`field_2` tinytext NOT NULL,
		`field_3` tinytext NOT NULL,
		`field_4` tinytext NOT NULL,
		UNIQUE KEY id (id)
		);";
 
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
 
    register_uninstall_hook( __FILE__, 'rp_inventory_uninstall' );
}

function rp_inventory_uninstall() {
    global $wpdb;
    global $db_table_name;

    // delete the database table
    $wpdb->query("DROP TABLE IF EXISTS " . $db_table_name);
}

// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'rp_inventory_install');

?>