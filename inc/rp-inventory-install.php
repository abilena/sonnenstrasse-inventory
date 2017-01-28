<?php
    
require_once('rp-inventory-database.php'); 

////////////////////////////////////////////////////////////////////////////////////////////////////////////

register_activation_hook(__FILE__, 'rp_inventory_install');

// function to create the DB / Options / Defaults					
function rp_inventory_install() {
    rp_inventory_create_tables();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

register_deactivation_hook(__FILE__, 'rp_inventory_uninstall');

function rp_inventory_uninstall() {
    rp_inventory_drop_tables();
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('init', 'rp_inventory_css_and_js');

function rp_inventory_css_and_js() {
    wp_register_style('rp_inventory_css_and_js', plugins_url('rp-inventory.css', __FILE__));
    wp_enqueue_style('rp_inventory_css_and_js');
    wp_register_script('rp_inventory_css_and_js', plugins_url('rp-inventory.js', __FILE__));
    wp_enqueue_script('rp_inventory_css_and_js');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_action('admin_init', 'rp_inventory_register_options' );

function rp_inventory_register_options() {
	register_setting( 'rp_inventory', 'rp_inventory' );
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_filter('plugin_action_links', 'rp_inventory_plugin_actions', 10, 2);

function rp_inventory_plugin_actions($links, $file) {
 	if ($file == 'rp-inventory/rp-inventory.php' && function_exists("admin_url")) {
		$settings_link = '<a href="' . admin_url('options-general.php?page=rp-inventory') . '">' . __('Settings', 'rp-inventory') . '</a>';
		array_unshift($links, $settings_link); 
	}
	return $links;
}

add_action('admin_menu', 'rp_inventory_add_pages');

function rp_inventory_add_pages() {
    // Add a new submenu under Options:
	$css = add_options_page('RP Inventory', 'RP Inventory', 'manage_options', 'rp-inventory', 'rp_inventory_options');
	add_action("admin_head-$css", 'rp_inventory_css');
}

function rp_inventory_css() { ?>
    <style type="text/css">
    #next-page, #parent-page, #previous-page { float: left; width: 30%; margin-right: 5%; }
    #next-page { margin-right: 0; }
    </style>
<?php 
}

// displays the options page content
function rp_inventory_options() { ?>	
    <div class="wrap">
	<form method="post" id="next_page_form" action="options.php">
		<?php settings_fields('rp_inventory');
		$options = get_option('rp_inventory'); ?>

    <h1>RP Inventory</h1>
    
	<p class="submit">
	<input type="submit" name="submit" class="button-primary" value="<?php _e('Update Options', 'rp-inventory'); ?>" />
	</p>
	</form>
	</div>
<?php 
} // end function next_page_options() 

?>