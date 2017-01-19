<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$path = $_SERVER['DOCUMENT_ROOT'];

include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

function rp_inventory_create_item($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';

    $wpdb->query('START TRANSACTION');

    $slot = $wpdb->get_var("SELECT MAX(slot) FROM $db_table_name WHERE owner='Gruppe' AND show_in_container_id=0");
    $slot += 1;

    $container_order = 0;
    $container_id = 0;
    $container_type = "";
    if ($arguments['is_container'] == "true") {
        $container_id = $wpdb->get_var("SELECT MAX(hosts_container_id) FROM $db_table_name");
        $container_id += 1;
        $container_order = $arguments['container_order'];
        $container_type = $arguments['container_type'];
    }

    $values = array(
        'owner' => "Gruppe", 
        'show_in_container_id' => 0,
        'slot' => $slot,
        'hosts_container_id' => $container_id,
        'hosts_container_order' => $container_order,
        'hosts_container_type' => $container_type,
        'icon' => $arguments['icon'],
        'name' => $arguments['name'],
        'description' => $arguments['description'],
        'flavor' => $arguments['flavor'],
        'type' => $arguments['type'],
        'price' => str_replace(",", ".", $arguments['price']),
        'weight' => str_replace(",", ".", $arguments['weight']),
        'rs' => str_replace(",", ".", $arguments['rs']),
        'be' => str_replace(",", ".", $arguments['be'])
    );
    $wpdb->insert($db_table_name, $values);

    $desc = $wpdb->get_results("SELECT * FROM $db_table_name");

    $wpdb->query('COMMIT');
}


rp_inventory_create_item($_REQUEST);

?>