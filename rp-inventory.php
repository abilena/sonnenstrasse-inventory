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

function rp_inventory_shortcode($atts, $content) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";

	extract(shortcode_atts(array(
		'title' => __('RP Inventory', 'rp-inventory'),
		'name' => 'name',
        'style' => 'default'
	), $atts));

	$title = esc_attr($title);
    $owner = $name;
    if (empty($owner) or ($owner == "name")) {
        $owner = "Gruppe";
    }

    // $db_result = $wpdb->get_var("show tables like '$db_table_name'");
    $db_result = $wpdb->get_results("SELECT * FROM $db_table_name WHERE owner = '$owner' ORDER BY show_in_container_id, slot");

    $header_content = "";
    if ($owner == "Gruppe") {
        $icon_files = get_all_files($path_local . "/img/icons/");
        $icon_files_html = implode(":", $icon_files);

        $tpl_inventory_header = new Template($path_local . "/tpl/inventory_header.html");
        $tpl_inventory_header->set("Owner", $owner);
        $tpl_inventory_header->set("IconsList", $icon_files_html);
        $header_content .= $tpl_inventory_header->output();
    }

    $default_container = new stdClass();
    $default_container->name = ($owner === "Gruppe") ? "Gruppe" : "Am K&ouml;rper";
    $default_container->item_id = 0;
    $default_container->owner = $owner;
    $default_container->hosts_container_id = 0;
    $default_container->hosts_container_order = 0;
    $default_container->hosts_container_type = "default";
    $default_container->icon = "am_koerper.png";
    $default_container->type = "mundane";
    $default_container->price = 0.0;
    $default_container->weight = 0.0;

    $container_ids = array(0 => $default_container);
    $container_content = array(0 => array());
    $container_orders = array(0 => 0);

    // enumerate all containers
    foreach ($db_result as $row_id => $row_data) {
        $row_data->name = stripslashes($row_data->name);
        $row_data->icon = stripslashes($row_data->icon);
        $row_data->description = stripslashes($row_data->description);
        $row_data->flavor = stripslashes($row_data->flavor);

        if ($row_data->hosts_container_id > 0) {
            $container_ids[$row_data->hosts_container_id] = $row_data;
            $container_content[$row_data->hosts_container_id] = array();
            $container_orders[$row_data->hosts_container_order] = $row_data->hosts_container_id;
        }
    }
    ksort($container_orders);

    foreach ($db_result as $row_id => $row_data) {
        $show_in_container_id = $row_data->show_in_container_id;
        if (!array_key_exists($show_in_container_id, $container_ids)) {
            $show_in_container_id = 0;
        }

        $content_array = $container_content[$show_in_container_id];
        $content_array[$row_data->slot] = $row_data;
        $container_content[$show_in_container_id] = $content_array;
    }

    $output = "";
    $containers_html = "";
    foreach ($container_orders as $hosts_container_order => $hosts_container_id) {
        $container_content_html = "";
        $container_data = $container_ids[$hosts_container_id];
        $container_type = $container_data->hosts_container_type;
        $sum_rs = array(0, 0, 0, 0, 0, 0, 0, 0);
        $sum_be = 0.0;

        $content_array = $container_content[$hosts_container_id];
        $max_slot = 1;
        if (!empty($content_array)) {
            $max_slot += max(array_keys($content_array));
        }
        if ($container_type == "armor") {
            $max_slot = max($max_slot, 5);
        }
        else
        {
            $max_slot = (ceil(($max_slot + 1) / 15) * 15) - 1;
        }

        for ($slot = 0; $slot <= $max_slot; $slot++) {

            $icon = $path_url . "/img/empty.png";
            $name = "";
            $type = "mundane";
            $item_id = "0";
            $flavor = "";
            $description = "";
            $weight = "";
            $price = "";
            $visibility = "hidden";
            $rs = array("", "", "", "", "", "", "", "");
            $be = "";
            if (array_key_exists($slot, $content_array)) {
                $content_data = $content_array[$slot];
                $icon = $path_url . "/img/icons/" . $content_data->icon;
                $name = $content_data->name;
                $type = $content_data->type;
                $item_id = $content_data->item_id;
                $flavor = str_replace("\n", "<br>", $content_data->flavor);
                $description = str_replace("\n", "<br>", $content_data->description);
                $weight = sprintf("%.0f", $content_data->weight);
                $price = str_replace(".", ",", sprintf("%.2f", $content_data->price));
                $rs = $content_data->rs;
                if (!empty($rs)) {
                    $rs = str_replace("0", "-", $rs);
                    $rs = explode(";", $rs);
                    $be = str_replace(".", ",", sprintf("%.2f", $content_data->be));
                    for ($rs_index = 0; $rs_index < 8; $rs_index++) {
                        $sum_rs[$rs_index] += $rs[$rs_index];
                    }
                    $sum_be += $content_data->be;
                }
                $visibility = "visible";
            }

            $popup_class = "";
            if ($container_type == "default" && ($slot % 15 > 10)) {
                $popup_class = "rp-inventory-item-info-popup-left";
            }

            $tpl_inventory_slot = new Template($path_local . "/tpl/inventory_item_slot.html");
            $tpl_inventory_slot->set("PopupClass", $popup_class);
            $inventory_slot_html = $tpl_inventory_slot->output();

            $tpl_inventory_item = new Template($path_local . "/tpl/inventory_item_" . $container_type . ".html");
            $tpl_inventory_item->set("SlotContent", $inventory_slot_html);
            $tpl_inventory_item->set("ContainerId", $hosts_container_id);
            $tpl_inventory_item->set("Slot", $slot);
            $tpl_inventory_item->set("ItemId", $item_id);
            $tpl_inventory_item->set("Owner", $owner);
            $tpl_inventory_item->set("Icon", $icon);
            $tpl_inventory_item->set("Name", $name);
            $tpl_inventory_item->set("Type", $type);
            $tpl_inventory_item->set("Flavor", $flavor);
            $tpl_inventory_item->set("Description", $description);
            $tpl_inventory_item->set("Weight", $weight);
            $tpl_inventory_item->set("Price", $price);
            $tpl_inventory_item->set("RS_KO", $rs[0]);
            $tpl_inventory_item->set("RS_BR", $rs[1]);
            $tpl_inventory_item->set("RS_RU", $rs[2]);
            $tpl_inventory_item->set("RS_BA", $rs[3]);
            $tpl_inventory_item->set("RS_LA", $rs[4]);
            $tpl_inventory_item->set("RS_RA", $rs[5]);
            $tpl_inventory_item->set("RS_LB", $rs[6]);
            $tpl_inventory_item->set("RS_RB", $rs[7]);
            $tpl_inventory_item->set("BE", $be);
            $tpl_inventory_item->set("Visibility", $visibility);
            $container_content_html .= $tpl_inventory_item->output();
        }

        $tpl_inventory_container = new Template($path_local . "/tpl/inventory_container_" . $container_type . ".html");
        $tpl_inventory_container->set("ContainerName", $container_data->name);
        $tpl_inventory_container->set("ContainerContent", $container_content_html);
        $tpl_inventory_container->set("Sum_RS_KO", sprintf("%.0f", $sum_rs[0]));
        $tpl_inventory_container->set("Sum_RS_BR", sprintf("%.0f", $sum_rs[1]));
        $tpl_inventory_container->set("Sum_RS_RU", sprintf("%.0f", $sum_rs[2]));
        $tpl_inventory_container->set("Sum_RS_BA", sprintf("%.0f", $sum_rs[3]));
        $tpl_inventory_container->set("Sum_RS_LA", sprintf("%.0f", $sum_rs[4]));
        $tpl_inventory_container->set("Sum_RS_RA", sprintf("%.0f", $sum_rs[5]));
        $tpl_inventory_container->set("Sum_RS_LB", sprintf("%.0f", $sum_rs[6]));
        $tpl_inventory_container->set("Sum_RS_RB", sprintf("%.0f", $sum_rs[7]));
        $tpl_inventory_container->set("Sum_BE", str_replace(".", ",", sprintf("%.2f", $sum_be)));
        $containers_html .= $tpl_inventory_container->output();
    }

    $tpl_inventory = new Template($path_local . "/tpl/inventory.html");
    $tpl_inventory->set("HeaderContent", $header_content);
    $tpl_inventory->set("Containers", $containers_html);
    $output .= $tpl_inventory->output();

	return $output;
}

add_shortcode ('rp-inventory', 'rp_inventory_shortcode');








// plugin activation/deactivation

// function to create the DB / Options / Defaults					
function rp_inventory_install() {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';
 
	// create the ECPT metabox database table
	if($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) 
	{
		$sql = "CREATE TABLE " . $db_table_name . " (
		`item_id` mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `owner` tinytext NOT NULL,
        `show_in_container_id` mediumint(9) NOT NULL,
        `slot` mediumint(9) NOT NULL,
        `hosts_container_id` mediumint(9) NOT NULL,
        `hosts_container_order` mediumint(9) NOT NULL,
        `hosts_container_type` tinytext NOT NULL,
        `icon` tinytext NOT NULL,
        `name` tinytext NOT NULL,
        `description` text NOT NULL,
        `flavor` text NOT NULL,
        `type` tinytext NOT NULL, 
        `price` float NOT NULL,
        `weight` float NOT NULL,
        `rs` tinytext,
        `be` float,
		UNIQUE KEY item_id (item_id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

function rp_inventory_uninstall() {
    global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';

    // delete the database table
    $wpdb->query("DROP TABLE IF EXISTS " . $db_table_name);
}

// run the install/uninstall scripts upon plugin activation/deactivation
register_activation_hook(__FILE__, 'rp_inventory_install');
register_deactivation_hook(__FILE__, 'rp_inventory_uninstall');

function rp_inventory_css_and_js() {
    wp_register_style('rp_inventory_css_and_js', plugins_url('inc/rp-inventory.css', __FILE__));
    wp_enqueue_style('rp_inventory_css_and_js');
    wp_register_script('rp_inventory_css_and_js', plugins_url('inc/rp-inventory.js', __FILE__));
    wp_enqueue_script('rp_inventory_css_and_js');
}

add_action('init', 'rp_inventory_css_and_js');

?>