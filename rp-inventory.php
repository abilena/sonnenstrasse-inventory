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

include 'template.class.php';

require_once('inc/rp-inventory-install.php'); 
require_once('inc/rp-inventory-hero.php'); 
require_once('inc/rp-inventory-merchant.php'); 

register_deactivation_hook(__FILE__, 'rp_inventory_uninstall');
register_activation_hook(__FILE__, 'rp_inventory_install');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 'rp-inventory' Hero Shortcode
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_shortcode ('rp-inventory', 'rp_inventory_shortcode');

function rp_inventory_shortcode($atts, $content) {

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

	extract(shortcode_atts(array(
		'title' => __('RP Inventory', 'rp-inventory'),
		'name' => '',
        'style' => 'default'
	), $atts));

	return rp_inventory_hero_html($name);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 'rp-inventory-merchant' Merchant Shortcode
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_shortcode ('rp-inventory-merchant', 'rp_inventory_merchant_shortcode');

function rp_inventory_merchant_shortcode($atts, $content) {

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

	extract(shortcode_atts(array(
		'title' => __('RP Inventory', 'rp-inventory'),
		'name' => '',
        'style' => 'default'
	), $atts));

    return rp_inventory_merchant_html($name);
}

?>