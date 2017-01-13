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

    $values = array(
        'owner' => 'MyHero', 
        'show_in_container_id' => 0,
        'slot' => 0,
        'hosts_container_id' => 0,
        'hosts_container_order' => 0,
        'icon' => 'broadsword.png',
        'name' => "Breitschwert",
        'description' => "Mein Breitschwert",
        'flavor' => "Flavor",
        'type' => "mundane",
        'price' => 0.5,
        'weight' => 0.7
    );
    $wpdb->insert($db_table_name, $values); /* , array('%s', '%d') */

    $desc = $wpdb->get_results("SELECT * FROM $db_table_name");

    print_r($desc);

    echo("created!");


}


rp_inventory_create_item($_REQUEST);

?>