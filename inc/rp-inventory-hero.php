<?php

require_once('rp-inventory-database.php');
require_once('rp-inventory-itemcontainer.php');

function rp_inventory_hero_html($name) {

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";

    $output = "";

    $owner = rp_inventory_get_hero_id_by_name($name);
    if (empty($owner) or ($owner < 0)) {
        $owner = 0;
    }

    if ($owner == 0) {
        $output .= "<i>unknown hero '$name' </i>";
    }
    else {
        $hero = rp_inventory_get_hero($owner);
        $db_result = rp_inventory_get_items($owner);

        $header_content = "";

        $is_user = is_user_logged_in();
        $is_admin = user_can(wp_get_current_user(), 'administrator');
        $is_owner = ($hero->creator == wp_get_current_user()->user_login);

        if ($is_admin) {
            $icon_files = get_all_files($path_local . "../img/icons/");
            $icon_files_html = implode(":", $icon_files);

            $tpl_inventory_header = new Template($path_local . "../tpl/inventory_header.html");
            $tpl_inventory_header->set("Owner", $owner);
            $tpl_inventory_header->set("IconsList", $icon_files_html);
            $header_content .= $tpl_inventory_header->output();
        }

        $default_container = new stdClass();
        $default_container->name = $name;
        $default_container->item_id = 0;
        $default_container->owner = $owner;
        $default_container->hosts_container_id = 0;
        $default_container->hosts_container_order = 0;
        $default_container->hosts_container_type = "default";
        $default_container->icon = "am_koerper.png";
        $default_container->type = "common";
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
        $index = 0;
        foreach ($container_orders as $hosts_container_order => $hosts_container_id) {

            $container = $container_ids[$hosts_container_id];
            $contained_items = $container_content[$hosts_container_id];
            $container_html = rp_inventory_itemcontainer_html($owner, FALSE, $is_user, $is_admin, $is_owner, $container, $contained_items, $hosts_container_id, $index);

            $containers_html .= $container_html;
            $index++;
        }

        $tpl_inventory = new Template($path_local . "../tpl/inventory.html");
        $tpl_inventory->set("HeaderContent", $header_content);
        $tpl_inventory->set("Containers", $containers_html);
        $output .= $tpl_inventory->output();
    }

	return $output;
}

?>