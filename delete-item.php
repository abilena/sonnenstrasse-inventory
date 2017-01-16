<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$path = $_SERVER['DOCUMENT_ROOT'];

include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

function rp_inventory_delete_item($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';

    $item = $arguments["item"];

    preg_match('/con_(?P<host>\d+)_(?P<slot>\d+)_(?P<id>\d+)_(?P<owner>\w+)/', $item, $matches);
    $old_host = $matches["host"];
    $old_slot = $matches["slot"];
    $old_id = $matches["id"];
    $old_owner = $matches["owner"];

    $output = "";
    $output .= "item: $item\n";
    $output .= "old_host: $old_host\n";
    $output .= "old_slot: $old_slot\n";
    $output .= "old_id: $old_id\n";
    $output .= "old_owner: $old_owner\n";
    $output .= "\n";

    $deleted = FALSE;
    $wpdb->query('START TRANSACTION');

    if ($old_id > 0)
    {
        $old_results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE item_id=$old_id");
        if (count($old_results) == 0)
        {
            $old_valid = 0;
            $output .= "old_id $old_id not found in table!\n";
            $output .= "\n";
        }
        else
        {
            $rows = $wpdb->query("DELETE FROM $db_table_name WHERE item_id=$old_id");
            
            if ($rows == 1)
            {
                $deleted = TRUE;
            }
        }
    }

    if ($deleted === TRUE)
    {
        $wpdb->query('COMMIT'); // if you come here then well done
        echo("succeeded");
    }
    else {
        $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
        echo("failed\n\n");
        echo($output);
    }
}


rp_inventory_delete_item($_REQUEST);

?>