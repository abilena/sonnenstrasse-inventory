<?php
    
require_once('rp-inventory-database.php'); 
require_once('rp-inventory-admin.php'); 

////////////////////////////////////////////////////////////////////////////////////////////////////////////

// function to create the DB / Options / Defaults					
function rp_inventory_install() {
	
	if( !class_exists( 'Sonnenstrasse\Template' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( __( 'Please install and activate Sonnenstrasse Base Shortcodes .', 'sonnenstrasse-inventory' ), 'Plugin dependency check', array( 'back_link' => true ) );
    }
	
	//sets up activation hook
	register_activation_hook(__FILE__, 'rp_inventory_install');
	
    rp_inventory_create_tables();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_uninstall() {
    rp_inventory_drop_tables();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('init', 'rp_inventory_css_and_js');

function rp_inventory_css_and_js() {
    wp_register_style('rp_inventory_css', plugins_url('rp-inventory.css', __FILE__));
    wp_enqueue_style('rp_inventory_css');
    wp_register_script('rp_inventory_js', plugins_url('rp-inventory.js', __FILE__));
    wp_enqueue_script('rp_inventory_js');
    wp_register_script('rp_inventory_reload_js', plugins_url('rp-inventory-reload.js', __FILE__));
    wp_enqueue_script('rp_inventory_reload_js');
    wp_enqueue_style('dashicons');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

?>