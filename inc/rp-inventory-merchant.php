<?php

require_once('rp-inventory-database.php');

function rp_inventory_merchant_html($name) {

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";
    $output = "";

    $merchant_id = rp_inventory_get_hero_id_by_name($name);
    if (empty($merchant_id) or ($merchant_id < 0)) {
        $merchant_id = 0;
    }

    if ($merchant_id > 0) {
        $merchant = rp_inventory_get_hero($merchant_id);
        $heroes = rp_inventory_get_my_heroes();

        $merchant_html = rp_inventory_merchant_details("merchant", $merchant_id, array(0 => $merchant_id));
        $hero_html = rp_inventory_merchant_details("hero", $merchant_id, $heroes);

        $tpl_inventory_merchant = new RPInventory\Template($path_local . "../tpl/inventory_merchant.html");
        $tpl_inventory_merchant->setObject($merchant);
        $tpl_inventory_merchant->set("Merchants", $merchant_html);
        $tpl_inventory_merchant->set("Heroes", $hero_html);
        $output .= $tpl_inventory_merchant->output();
    }

	return $output;
}

function rp_inventory_merchant_details($owner_type, $merchant_id, $owners) {

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";
    $output = "";

    foreach ($owners as $index => $owner_id) {
        
        $hero = rp_inventory_get_hero($owner_id);

        $equipment_html = rp_inventory_merchant_items($owner_id, ($owner_id != $merchant_id));

        $tpl_inventory_merchant_selector = new RPInventory\Template($path_local . "../tpl/inventory_merchant_selector.html");
        $tpl_inventory_merchant_selector->setObject($hero);
        $tpl_inventory_merchant_selector->set("Display", (($index == 0) ? "block" : "none"));
        $tpl_inventory_merchant_selector->set("ButtonsEnabled", "rp-inventory-heroselector-container-button-" . (($owner_type == "hero") ? "enabled" : "disabled"));
        $tpl_inventory_merchant_selector->set("OwnerType", $owner_type);
        $tpl_inventory_merchant_selector->set("MerchantId", $merchant_id);
        $tpl_inventory_merchant_selector->set("Gold", str_replace(".", ",", sprintf("%.2f", $hero->gold)));
        $tpl_inventory_merchant_selector->set("Equipment", $equipment_html);
        $output .= $tpl_inventory_merchant_selector->output();
    }

    return $output;
}

function rp_inventory_merchant_items($owner_id, $is_owner) {

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";
    $output = "";

    $db_result = rp_inventory_get_items($owner_id);

    $header_content = "";

    $is_user = is_user_logged_in();
    $is_admin = user_can(wp_get_current_user(), 'administrator');

    $default_container = new stdClass();
    $default_container->name = "Waren";
    $default_container->item_id = 0;
    $default_container->owner = $owner_id;
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
        $container_html = rp_inventory_itemcontainer_html($owner_id, FALSE, TRUE, $is_user, $is_admin, $is_owner, $container, $contained_items, $hosts_container_id, $index);

        $containers_html .= $container_html;
        $index++;
    }

    $tpl_inventory_merchant_equipment = new RPInventory\Template($path_local . "../tpl/inventory_containers_all.html");
    $tpl_inventory_merchant_equipment->set("OwnerId", $owner_id);
    $tpl_inventory_merchant_equipment->set("Content", $containers_html);
    $output .= $tpl_inventory_merchant_equipment->output();

	return $output;
}

?>