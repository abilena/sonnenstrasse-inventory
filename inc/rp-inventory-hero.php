<?php

require_once('rp-inventory-database.php');
require_once('rp-inventory-itemcontainer.php');

function rp_inventory_hero_html($name) {

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/sonnenstrasse-inventory";

    $hero_id = rp_inventory_get_hero_id_by_name($name);
    if (empty($hero_id) or ($hero_id < 0)) {
        $hero_id = 0;
    }

	return rp_inventory_hero_html_by_id($hero_id, $name);
}

function rp_inventory_hero_html_by_id($hero_id, $name) {

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/sonnenstrasse-inventory";

    if ($hero_id == 0) {
        $output .= "<i>unknown hero '$name' </i>";
    }
    else {
        $hero = rp_inventory_get_hero($hero_id);

        rp_inventory_get_item_containers($hero_id, $name, $container_ids, $container_content, $container_orders);

        $header_content = "";
        $is_user = is_user_logged_in();
        $is_admin = user_can(wp_get_current_user(), 'administrator');
        $is_owner = ($hero->creator == wp_get_current_user()->user_login);

        if ($is_admin) {
            $icon_files = RPInventory\get_all_files($path_local . "../img/icons/");
            $icon_files_html = implode(":", $icon_files);

            $tpl_inventory_header = new RPInventory\Template($path_local . "../tpl/inventory_header.html");
            $tpl_inventory_header->set("Owner", $hero_id);
            $tpl_inventory_header->set("IconsList", $icon_files_html);
            $header_content .= $tpl_inventory_header->output();
        }

        $output = "";
        $containers_html = "";
        $index = 0;
        foreach ($container_orders as $hosts_container_order => $hosts_container_id) {

            $container = $container_ids[$hosts_container_id];
            $contained_items = $container_content[$hosts_container_id];
            $container_html = rp_inventory_itemcontainer_html($hero_id, FALSE, FALSE, $is_user, $is_admin, $is_owner, $container, $contained_items, $hosts_container_id, $index);

            $containers_html .= $container_html;
            $index++;
        }

        $tpl_inventory = new RPInventory\Template($path_local . "../tpl/inventory.html");
        $tpl_inventory->set("PluginBaseUri", $path_url);
        $tpl_inventory->set("HeaderContent", $header_content);
        $tpl_inventory->set("Containers", $containers_html);
        $output .= $tpl_inventory->output();
    }

	return $output;
}

?>