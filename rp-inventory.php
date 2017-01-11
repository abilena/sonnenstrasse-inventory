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
	extract(shortcode_atts(array(
		'title' => __('RP Inventory', 'rp-inventory'),
		'name' => 'name',
        'style' => 'default'
	), $atts));

	$title = esc_attr($title);

	$output  = "\n<div class=\"rp-inventory\">\n";
	$output .= $name;
	$output .= "\n</div>\n";

	return $output;
}
add_shortcode ('rp-inventory', 'rp_inventory_shortcode');

?>