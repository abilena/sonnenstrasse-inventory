<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$path = $_SERVER['DOCUMENT_ROOT'];

include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

function rp_inventory_swap_item($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';

    $item1 = $arguments["item1"];
    $item2 = $arguments["item2"];

    preg_match('/con_(?P<host>\d+)_(?P<slot>\d+)_(?P<id>\d+)_(?P<owner>\w+)/', $item1, $matches1);
    $old_host = $matches1["host"];
    $old_slot = $matches1["slot"];
    $old_id = $matches1["id"];
    $old_owner = $matches1["owner"];
    $old_container_id = 0;

    preg_match('/con_(?P<host>\d+)_(?P<slot>\d+)_(?P<id>\d+)_(?P<owner>\w+)/', $item2, $matches2);
    $new_host = $matches2["host"];
    $new_slot = $matches2["slot"];
    $new_id = $matches2["id"];
    $new_owner = $matches2["owner"];
    $new_container_id = 0;

    $output = "";
    $output .= "item1: $item1\n";
    $output .= "old_host: $old_host\n";
    $output .= "old_slot: $old_slot\n";
    $output .= "old_id: $old_id\n";
    $output .= "old_owner: $old_owner\n";
    $output .= "\n";

    $output .= "item2: $item2\n";
    $output .= "new_host: $new_host\n";
    $output .= "new_slot: $new_slot\n";
    $output .= "new_id: $new_id\n";
    $output .= "new_owner: $new_owner\n";
    $output .= "\n";

    $wpdb->query('START TRANSACTION');

    $old_updated = 1;
    $new_updated = 1;

    $old_valid = 1;
    $new_valid = 1;

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
            $old_result = $old_results[0];
            $old_container_id = $old_result->hosts_container_id;
            if ($old_result->owner != $old_owner) $old_valid = 0;
            if ($old_result->show_in_container_id != $old_host) $old_valid = 0;
            if ($old_result->slot != $old_slot) $old_valid = 0;
            if ($old_valid == 0)
            {
                $output .= "db_old_host: " . $old_result->show_in_container_id . "\n";
                $output .= "db_old_slot: " . $old_result->slot . "\n";
                $output .= "db_old_id: " . $old_result->item_id . "\n";
                $output .= "db_old_owner: " . $old_result->owner . "\n";
                $output .= "\n";
            }
        }
    }
    if ($new_id > 0)
    {
        $new_results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE item_id=$new_id");
        if (count($new_results) == 0)
        {
            $new_valid = 0;
            $output .= "new_id $new_id not found in table!\n";
            $output .= "\n";
        }
        else
        {
            $new_result = $new_results[0];
            $new_container_id = $new_result->hosts_container_id;
            if ($new_result->owner != $new_owner) $new_valid = 0;
            if ($new_result->show_in_container_id != $new_host) $new_valid = 0;
            if ($new_result->slot != $new_slot) $new_valid = 0;
            if ($new_valid == 0)
            {
                $output .= "db_new_host: " . $new_result->show_in_container_id . "\n";
                $output .= "db_new_slot: " . $new_result->slot . "\n";
                $output .= "db_new_id: " . $new_result->item_id . "\n";
                $output .= "db_new_owner: " . $new_result->owner . "\n";
                $output .= "\n";
            }
        }
    }

    $output .= "old_valid: $old_valid\n";
    $output .= "new_valid: $new_valid\n";
    $output .= "\n";

    if ($old_valid && $new_valid)
    {
        if ($old_id > 0)
            $new_updated = $wpdb->update($db_table_name, array('show_in_container_id' => $new_host, 'slot' => $new_slot, 'owner' => $new_owner), array('item_id' => $old_id));

        if ($new_id > 0)
            $old_updated = $wpdb->update($db_table_name, array('show_in_container_id' => $old_host, 'slot' => $old_slot, 'owner' => $old_owner), array('item_id' => $new_id));
    }

    if ($new_owner != $old_owner) {
        if ($old_id > 0) {
            if (!rp_inventory_swap_content($old_container_id, $new_owner)) {
                $old_updated = 0;
            }
        }
        if ($new_id > 0) {
            if (!rp_inventory_swap_content($new_container_id, $old_owner)) {
                $new_updated = 0;
            }
        }
    }

    // prevent recursive move by checking if a container is not in itself
    if (rp_inventory_is_inside_container($old_id, $old_container_id) > 0) {
        $old_updated = 0;
        $output .= "Item must not be placed into itself!\n\n";
    }
    if (rp_inventory_is_inside_container($new_id, $new_container_id) > 0) {
        $new_updated = 0;
        $output .= "Item must not be placed into itself!\n\n";
    }

    $output .= "old_updated: $old_updated\n";
    $output .= "new_updated: $new_updated\n";
    $output .= "\n";

    if($old_valid && $new_valid && $new_updated && $old_updated) {
        $wpdb->query('COMMIT'); // if you come here then well done
        echo("succeeded");
    }
    else {
        $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
        echo("failed\n\n");
        echo($output);
    }
}

function rp_inventory_swap_content($container_id, $new_owner) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';

    if ($container_id == 0) {
        return 1;
    }

    $db_results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE show_in_container_id=$container_id");
    if (count($db_results) > 0)
    {
        foreach ($db_results as $row_id => $row_data) {
            if ($row_data->hosts_container_id > 0) {
                if (!rp_inventory_swap_content($row_data->hosts_container_id, $new_owner)) {
                    return 0;
                }
            }
        }

        $items_updated = $wpdb->update($db_table_name, array('owner' => $new_owner), array('show_in_container_id' => $container_id));
        if ($items_updated != count($db_results)) {
            return 0;
        }
    }

    return 1;
}

function rp_inventory_is_inside_container($item_id, $container_id) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';

    if ($item_id == 0) {
        return 0;
    }
    if ($container_id == 0) {
        return 0;
    }

    $db_results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE item_id=$item_id");
    if (count($db_results) != 1) {
        return 1;
    }
    $db_result = $db_results[0];
    $show_in_container_id = $db_result->show_in_container_id;
    if ($show_in_container_id == $container_id) {
        return 1;
    }

    while ($show_in_container_id > 0) {
        $db_results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE hosts_container_id=$show_in_container_id");
        if (count($db_results) != 1) {
            return 1;
        }
        $db_result = $db_results[0];
        $show_in_container_id = $db_result->show_in_container_id;
        if ($show_in_container_id == $container_id) {
            return 1;
        }
    }

    return 0;
}


rp_inventory_swap_item($_REQUEST);

?>