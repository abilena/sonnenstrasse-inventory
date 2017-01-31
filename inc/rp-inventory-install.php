<?php
    
require_once('rp-inventory-database.php'); 

////////////////////////////////////////////////////////////////////////////////////////////////////////////

// function to create the DB / Options / Defaults					
function rp_inventory_install() {
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

function rp_inventory_css() {
    wp_register_style('rp_inventory_admin_css', plugins_url('rp-inventory-admin.css', __FILE__));
    wp_enqueue_style('rp_inventory_admin_css');
    wp_register_script('rp_inventory_admin_js', plugins_url('rp-inventory-admin.js', __FILE__));
    wp_enqueue_script('rp_inventory_admin_js');
    wp_register_script('rp_inventory_reload_js', plugins_url('rp-inventory-reload.js', __FILE__));
    wp_enqueue_script('rp_inventory_reload_js');
}

// displays the options page content
function rp_inventory_options() { ?>	
    <div class="wrap">
	<form method="post" id="next_page_form" action="options.php">
		<?php settings_fields('rp_inventory');
		$options = get_option('rp_inventory'); ?>

    <h1>RP Inventory</h1>
    <div class="rp-inventory-admin">
<?php

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";

    $partys_html = "";
    $partys = rp_inventory_get_partys();
    $party_id = (array_key_exists("party_id", $_REQUEST) ? $_REQUEST["party_id"] : ((count($partys) > 0) ? $partys[0]->party_id : 0));
    foreach ($partys as $row_id => $party) {

        $tpl_inventory_admin_party = new Template($path_local . "../tpl/inventory_admin_party.html");
        $tpl_inventory_admin_party->set("Id", $party->party_id);
        $tpl_inventory_admin_party->set("Name", $party->name);
        $tpl_inventory_admin_party->set("Selected", ($party->party_id == $party_id) ? "selected" : "");
        $partys_html .= $tpl_inventory_admin_party->output();
    }

    $tpl_inventory_admin_partys = new Template($path_local . "../tpl/inventory_admin_partys.html");
    $tpl_inventory_admin_partys->set("Partys", $partys_html);
    echo ($tpl_inventory_admin_partys->output());

    if (count($partys) > 0) {
        $heroes_html = "";
        $heroes = rp_inventory_get_heroes($party_id);
        $hero_id = (array_key_exists("hero_id", $_REQUEST) ? $_REQUEST["hero_id"] : 0);
        $selected_hero = NULL;
        foreach ($heroes as $row_id => $hero) {

            if ($hero->hero_id == $hero_id) {
                $selected_hero = $hero;
            }

            $portrait = $hero->portrait;
            if (empty($portrait)) {
                $portrait = $path_url . "/img/shapes/" . (($hero->gender == 'female') ? "portrait_female.png" : "portrait_male.png");
            }

            $tpl_inventory_admin_hero = new Template($path_local . "../tpl/inventory_admin_hero.html");
            $tpl_inventory_admin_hero->set("Id", $hero->hero_id);
            $tpl_inventory_admin_hero->set("Name", $hero->name);
            $tpl_inventory_admin_hero->set("Portrait", $portrait);
            $heroes_html .= $tpl_inventory_admin_hero->output();
        }

        $tpl_inventory_admin_heroes = new Template($path_local . "../tpl/inventory_admin_heroes.html");
        $tpl_inventory_admin_heroes->set("Heroes", $heroes_html);
        echo ($tpl_inventory_admin_heroes->output());

        if (!empty($selected_hero)) {
            $tpl_inventory_admin_hero_details = new Template($path_local . "../tpl/inventory_admin_hero_details.html");
            $tpl_inventory_admin_hero_details->set("Id", $selected_hero->hero_id);
            $tpl_inventory_admin_hero_details->set("Name", $selected_hero->name);
            $tpl_inventory_admin_hero_details->set("Portrait", $selected_hero->portrait);
            $tpl_inventory_admin_hero_details->set("Info", wp_json_encode($selected_hero));
            echo ($tpl_inventory_admin_hero_details->output());
        }
    }

 ?>
    </div>
    <p class="submit">
	<input type="submit" name="submit" class="button-primary" value="<?php _e('Update Options', 'rp-inventory'); ?>" />
	</p>
	</form>
	</div>
<?php 
} // end function next_page_options() 

?>