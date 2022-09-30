<?php

function rp_inventory_create_tables() {
   	global $wpdb;

    $db_table_name = $wpdb->prefix . 'sonnenstrasse_inventory';
	// create the ECPT metabox database table
	if($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) 
	{
		$sql = "CREATE TABLE " . $db_table_name . " (
		`item_id` mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `owner` mediumint(9) NOT NULL,
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
        `wp_dk` tinytext,
        `wp_ini` float,
        `wp_tp_dices` float,
        `wp_tp_bonus` float,
        `wp_tp_type` tinytext,
        `wp_tp_kk_req` float,
        `wp_tp_kk_span` float,
        `wp_wm_at` float,
        `wp_wm_pa` float,
        `wp_bf` float,
        `wp_skill` tinytext,
		UNIQUE KEY item_id (item_id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

function rp_inventory_drop_tables() {
    global $wpdb;

    // delete the database tables
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'sonnenstrasse_inventory');
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Partys
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_get_partys() {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_partys';
    
    $db_results = $wpdb->get_results("SELECT * FROM $db_table_name ORDER BY name");

    return $db_results;
}

function rp_inventory_get_party($id) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_partys';
    
    if ($id > 0)
    {
        $results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE party_id=$id");
        if (count($results) == 1)
        {
             return wp_json_encode($results[0]);
        }
    }
}

function rp_inventory_create_party($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_partys';

    $wpdb->query('START TRANSACTION');

    $values = array(
        'name' => $arguments['name'], 
        'current_year' => $arguments['current_year'], 
        'current_month' => $arguments['current_month'],
        'current_day' => $arguments['current_day']
    );
    $wpdb->insert($db_table_name, $values);

    $wpdb->query('COMMIT');
}

function rp_inventory_edit_party($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_partys';

    $wpdb->query('START TRANSACTION');

    $values = array(
        'name' => $arguments['name'], 
        'current_year' => $arguments['current_year'], 
        'current_month' => $arguments['current_month'],
        'current_day' => $arguments['current_day']
    );
    $wpdb->update($db_table_name, $values, array('party_id' => $arguments['id']));

    $wpdb->query('COMMIT');
}

function rp_inventory_delete_party($id) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_partys';

    $output = "";
    $output .= "id: $id\n";
    $output .= "\n";

    $deleted = FALSE;
    $wpdb->query('START TRANSACTION');

    if ($id > 0)
    {
        $results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE party_id=$id");
        if (count($results) == 0)
        {
            $output .= "party with id $id not found in table!\n";
            $output .= "\n";
        }
        else
        {
            $rows = $wpdb->query("DELETE FROM $db_table_name WHERE party_id=$id");
            
            if ($rows == 1)
            {
                $deleted = TRUE;
            }
        }
    }

    if ($deleted === TRUE)
    {
        $wpdb->query('COMMIT'); // if you come here then well done
        return "succeeded";
    }
    else {
        $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
        return "failed\n\n" . $output;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Heroes
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_get_heroes($party_id) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_heroes';
    
    $db_results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE party=$party_id ORDER BY name");

    return $db_results;
}

function rp_inventory_get_hero($hero_id) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_heroes';
    
    $db_results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE hero_id=$hero_id");

    return $db_results[0];
}

function rp_inventory_get_hero_id_by_name($name) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_heroes';
    
    $id = $wpdb->get_var("SELECT hero_id FROM $db_table_name WHERE name='$name'");

    return $id;
}

function rp_inventory_create_hero($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_heroes';

    $wpdb->query('START TRANSACTION');

    $values = array(
        'party' => $arguments['party_id'], 
        'creator' => wp_get_current_user()->user_login, 
        'name' => "Neuer Held"
    );
    $wpdb->insert($db_table_name, $values);

    $wpdb->query('COMMIT');    
}

function rp_inventory_get_my_heroes() {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_heroes';
    
    $user = wp_get_current_user()->user_login;

    $db_results = $wpdb->get_results("SELECT hero_id FROM $db_table_name WHERE (creator='$user' OR creator='') AND (hero_type='hero' OR hero_type='shared')");

    $heroes = array_map(function($hero) { return $hero->hero_id; }, $db_results);

    return $heroes;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Properties
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_get_properties($hero_id, $property_type) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_properties';
    
    $db_results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE hero=$hero_id AND type='$property_type' ORDER BY name");

    return $db_results;
}

function rp_inventory_property_format_cost($cost) {
    if ($cost == NULL) {
        return "";
    } else if ($cost == 0) {
        return "-";
    } else {
        return $cost;
    }
}

function rp_inventory_property_parse_cost($value) {
    if ($value == "") {
        return NULL;
    } else if ($value == "-") {
        return 0;
    } else {
        return $value;
    }
}

function rp_inventory_edit_property($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_properties';

    if (empty($arguments['name'])) {
        return;
    }

    $wpdb->query('START TRANSACTION');

    $id = $arguments['property_id'];
    $values = array(
		'hero' => $arguments['hero'], 
        'type' => $arguments['type'], 
        'name' => $arguments['name'], 
        'variant' => $arguments['variant'], 
        'info' => $arguments['info'], 
        'value' => $arguments['value'], 
        'gp' => rp_inventory_property_parse_cost($arguments['gp']), 
        'tgp' => rp_inventory_property_parse_cost($arguments['tgp']), 
        'ap' => rp_inventory_property_parse_cost($arguments['ap'])
    );

    if ($id > 0) {
        $wpdb->update($db_table_name, $values, array('property_id' => $arguments['property_id']));
    } else {
        $wpdb->insert($db_table_name, $values);
    }

    $wpdb->query('COMMIT');
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Details
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_get_detail($hero_id, $detail_type) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_heroes';

    $detail_value = $wpdb->get_var("SELECT $detail_type FROM $db_table_name WHERE hero_id=$hero_id");

    return $detail_value;
}

function rp_inventory_edit_detail($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_heroes';

    $wpdb->query('START TRANSACTION');

    $id = $arguments['hero'];
    $type = $arguments['type'];
    $value = $arguments['value'];
    $updated = FALSE;

    $hero = rp_inventory_get_hero($id);
    if (empty($hero))
    {
        $output .= "A hero with id $id was not found in table!\n";
        $output .= "\n";
    }
    else {
        $old_value = rp_inventory_get_detail($id, $type);
        if ($old_value === $value) {
            $updated = 1;
        }
        else {
            $updated = $wpdb->update($db_table_name, array($type => $value), array('hero_id' => $id));
        }
    }

    if ($updated === 1)
    {
        $wpdb->query('COMMIT'); // if you come here then well done
        return "succeeded";
    }
    else {
        $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
        return "failed\n\n" . $output;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_get_item($item_id) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_inventory';

    $db_result = $wpdb->get_results("SELECT * FROM $db_table_name WHERE item_id=$item_id");

    return $db_result[0];
}

function rp_inventory_get_items($owner_id) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_inventory';

    $db_result = $wpdb->get_results("SELECT * FROM $db_table_name WHERE owner=$owner_id ORDER BY show_in_container_id, slot");

    return $db_result;
}

function rp_inventory_get_item_containers($owner_id, $owner_name, &$container_ids, &$container_content, &$container_orders)
{
    $db_result = rp_inventory_get_items($owner_id);

    $default_container = new stdClass();
    $default_container->name = "Am Körper";
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
}

function rp_inventory_create_item($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_inventory';

    $owner = $arguments['owner'];

    $wpdb->query('START TRANSACTION');

    $slot = $wpdb->get_var("SELECT MAX(slot) FROM $db_table_name WHERE owner='$owner' AND show_in_container_id=0");
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
        'owner' => $owner, 
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
        'be' => str_replace(",", ".", $arguments['be']),
        'wp_dk' => $arguments['wp_dk'],
        'wp_ini' => str_replace(",", ".", $arguments['wp_ini']),
        'wp_tp_dices' => str_replace(",", ".", $arguments['wp_tp_dices']),
        'wp_tp_bonus' => str_replace(",", ".", $arguments['wp_tp_bonus']),
        'wp_tp_type' => $arguments['wp_tp_type'],
        'wp_tp_kk_req' => str_replace(",", ".", $arguments['wp_tp_kk_req']),
        'wp_tp_kk_span' => str_replace(",", ".", $arguments['wp_tp_kk_span']),
        'wp_wm_at' => str_replace(",", ".", $arguments['wp_wm_at']),
        'wp_wm_pa' => str_replace(",", ".", $arguments['wp_wm_pa']),
        'wp_bf' => str_replace(",", ".", $arguments['wp_bf']),
        'wp_skill' => $arguments['wp_skill']
    );
    $wpdb->insert($db_table_name, $values);

    $wpdb->query('COMMIT');
	
	return "succeeded";
}

function rp_inventory_edit_item($arguments)
{
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_inventory';

    $item = $arguments["item"];
    preg_match('/con_(?P<host>\d+)_(?P<slot>\d+)_(?P<id>\d+)_(?P<owner>\w+)/', $item, $matches);
    $host = $matches["host"];
    $slot = $matches["slot"];
    $id = $matches["id"];
    $owner = $matches["owner"];

    $output = "";
    $output .= "item: $item\n";
    $output .= "host: $host\n";
    $output .= "slot: $slot\n";
    $output .= "id: $id\n";
    $output .= "owner: $owner\n";
    $output .= "\n";

    $updated = 0;
    $wpdb->query('START TRANSACTION');

    if ($id > 0)
    {
        $values = array(
            'icon' => $arguments['icon'],
            'name' => $arguments['name'],
            'description' => $arguments['description'],
            'flavor' => $arguments['flavor'],
            'type' => $arguments['type'],
            'price' => str_replace(",", ".", $arguments['price']),
            'weight' => str_replace(",", ".", $arguments['weight']),
            'rs' => str_replace(",", ".", $arguments['rs']),
            'be' => str_replace(",", ".", $arguments['be']),
            'wp_dk' => $arguments['wp_dk'],
            'wp_ini' => str_replace(",", ".", $arguments['wp_ini']),
            'wp_tp_dices' => str_replace(",", ".", $arguments['wp_tp_dices']),
            'wp_tp_bonus' => str_replace(",", ".", $arguments['wp_tp_bonus']),
            'wp_tp_type' => $arguments['wp_tp_type'],
            'wp_tp_kk_req' => str_replace(",", ".", $arguments['wp_tp_kk_req']),
            'wp_tp_kk_span' => str_replace(",", ".", $arguments['wp_tp_kk_span']),
            'wp_wm_at' => str_replace(",", ".", $arguments['wp_wm_at']),
            'wp_wm_pa' => str_replace(",", ".", $arguments['wp_wm_pa']),
            'wp_bf' => str_replace(",", ".", $arguments['wp_bf']),
            'wp_skill' => $arguments['wp_skill']
        );
        $updated = $wpdb->update($db_table_name, $values, array('item_id' => $id));
    }

    if ($updated == 1)
    {
        $wpdb->query('COMMIT'); // if you come here then well done
        return "succeeded";
    }
    else {
        $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
        return "failed\n\n" . $output;
    }
}

function rp_inventory_delete_item($item) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_inventory';

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
        return "succeeded";
    }
    else {
        $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
        return "failed\n\n" . $output;
    }
}

function rp_inventory_get_selected_item($item) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_inventory';

    preg_match('/con_(?P<host>\d+)_(?P<slot>\d+)_(?P<id>\d+)_(?P<owner>\w+)/', $item, $matches);
    $host = $matches["host"];
    $slot = $matches["slot"];
    $id = $matches["id"];
    $owner = $matches["owner"];

    $output = "";
    $output .= "item: $item\n";
    $output .= "host: $host\n";
    $output .= "slot: $slot\n";
    $output .= "id: $id\n";
    $output .= "owner: $owner\n";
    $output .= "\n";

    if ($id > 0)
    {
        $item_record = rp_inventory_get_item($id);
        if (!empty($item_record))
        {
            $item_record->name = stripslashes($item_record->name);
            $item_record->description = stripslashes($item_record->description);
            $item_record->flavor = stripslashes($item_record->flavor);
            $item_record->price = str_replace(".", ",", $item_record->price);
            $item_record->weight = str_replace(".", ",", $item_record->weight);
            $item_record->rs = str_replace(".", ",", $item_record->rs);
            $item_record->be = str_replace(".", ",", $item_record->be);
            $item_record->wp_dk = stripslashes($item_record->wp_dk);
            $item_record->wp_ini = str_replace(",", ".", $item_record->wp_ini);
            $item_record->wp_tp_dices = str_replace(",", ".", $item_record->wp_tp_dices);
            $item_record->wp_tp_bonus = str_replace(",", ".", $item_record->wp_tp_bonus);
            $item_record->wp_tp_type = stripslashes($item_record->wp_tp_type);
            $item_record->wp_tp_kk_req = str_replace(",", ".", $item_record->wp_tp_kk_req);
            $item_record->wp_tp_kk_span = str_replace(",", ".", $item_record->wp_tp_kk_span);
            $item_record->wp_wm_at = str_replace(",", ".", $item_record->wp_wm_at);
            $item_record->wp_wm_pa = str_replace(",", ".", $item_record->wp_wm_pa);
            $item_record->wp_bf = str_replace(",", ".", $item_record->wp_bf);
            $item_record->wp_skill = stripslashes($item_record->wp_skill);
            return wp_json_encode($item_record);
        }
    }

    return "failed\n\n" . $output;
}

function rp_inventory_buy_items($hero_id, $merchant_id, $items) {

    return rp_inventory_transfer_items($merchant_id, $hero_id, $items);
}

function rp_inventory_sell_items($hero_id, $merchant_id, $items) {

    return rp_inventory_transfer_items($hero_id, $merchant_id, $items);
}


function rp_inventory_transfer_items($from_hero_id, $to_hero_id, $items) {
   	global $wpdb;
    $db_table_name_items = $wpdb->prefix . 'sonnenstrasse_inventory';
    $db_table_name_heroes = $wpdb->prefix . 'sonnenstrasse_heroes';

    $buyer = rp_inventory_get_hero($to_hero_id);
    $seller = rp_inventory_get_hero($from_hero_id);
    $itemsList = array();
    $failed = FALSE;

    if (empty($buyer)) {
        $output .= "Invalid buyer!\n";
        $failed = TRUE;
    }
    else if (empty($seller)) {
        $output .= "Invalid seller!\n";
        $failed = TRUE;
    }
    else {
        $hasContainers = FALSE;
        $total_price = 0;
        $itemSplits = explode(",", $items);
        foreach ($itemSplits as $index => $itemSplit) {
            preg_match('/con_(?P<host>\d+)_(?P<slot>\d+)_(?P<id>\d+)_(?P<owner>\w+)/', $itemSplit, $matches);
            $old_id = $matches["id"];

            $item = rp_inventory_get_item($old_id);

            if (empty($item) || ($item->owner != $seller->hero_id)) {
                $output .= "Invalid item: $old_id!\n";
                $failed = TRUE;
            }

            array_push($itemsList, $item);
        }

        $itemsList = rp_inventory_expand_containers($itemsList);

        foreach ($itemsList as $index => $item) {
            $total_price += $item->price;
        }

        if ($buyer->gold < $total_price) {
            $output .= "Insufficient gold!\n";
            $failed = TRUE;
        }
    }

    if (!$failed) {
        
        $wpdb->query('START TRANSACTION');

        $updated = 0;
        foreach ($itemsList as $index => $item) {
            $updated += $wpdb->update($db_table_name_items, array('owner' => $buyer->hero_id), array('item_id' => $item->item_id));
        }

        if ($updated != sizeof($itemsList)) {
            $output .= "Failed to update items!\n";
            $failed = TRUE;
        }

        $new_gold_buyer = $buyer->gold - $total_price;
        $new_gold_seller = $seller->gold + $total_price;
        $updated = $wpdb->update($db_table_name_heroes, array('gold' => $new_gold_buyer), array('hero_id' => $buyer->hero_id));
        $updated = $wpdb->update($db_table_name_heroes, array('gold' => $new_gold_seller), array('hero_id' => $seller->hero_id));
    }

    if (!$failed)
    {
        $wpdb->query('COMMIT'); // if you come here then well done
        return "succeeded";
    }
    else {
        $wpdb->query('ROLLBACK'); // // something went wrong, Rollback
        return "failed\n\n" . $output;
    }
}

function rp_inventory_expand_containers($itemsList) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'sonnenstrasse_inventory';
    
    $new_items = array();
    foreach ($itemsList as $index => $item) {
        if ($item->hosts_container_id > 0) {
            $container_id = $item->hosts_container_id;
            $contained_items = $wpdb->get_results("SELECT * FROM $db_table_name WHERE show_in_container_id=$container_id");
            if (!empty($contained_items)) {
                rp_inventory_expand_containers($contained_items);
                $new_items = array_merge_unique($new_items, $contained_items); 
            }
        }
    }

    return array_merge_unique($itemsList, $new_items);
}

function array_merge_unique($array1, $array2) {
    $result = array_merge(array(), $array1);
    foreach ($array2 as $key2 => $value2) {
        foreach ($result as $key1 => $value1) {
            if (array_search($value, $result) === FALSE)
                array_push($result, $value);
        }
    }
    return $result;
}

?>