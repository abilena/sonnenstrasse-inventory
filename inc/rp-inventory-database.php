<?php

function rp_inventory_create_tables() {
   	global $wpdb;

    $db_table_name = $wpdb->prefix . 'rp_partys';
	// create the ECPT metabox database table
	if($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) 
	{
		$sql = "CREATE TABLE " . $db_table_name . " (
		`party_id` mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` tinytext NOT NULL,
        `current_year` smallint NOT NULL,
        `current_month` smallint NOT NULL,
        `current_day` smallint NOT NULL,
		UNIQUE KEY party_id (party_id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

        $wpdb->insert($db_table_name, array(
            'name' => 'Gruppe', 
            'description' => 'Demo Abenteuergruppe', 
            'current_year' => 1013, 
            'current_month' => 12,
            'current_day' => 18));
	}

    $db_table_name = $wpdb->prefix . 'rp_heroes';
	// create the ECPT metabox database table
	if($wpdb->get_var("show tables like '$db_table_name'") != $db_table_name) 
	{
		$sql = "CREATE TABLE " . $db_table_name . " (
		`hero_id` mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`party` mediumint(9) NOT NULL,
        `creator` tinytext NOT NULL,
        `name` tinytext NOT NULL,
        `display_name` tinytext NOT NULL,
        `gender` tinytext NOT NULL,
        `weight` float NOT NULL,
        `height` float NOT NULL,
        `birth_year` smallint NOT NULL,
        `birth_month` smallint NOT NULL,
        `birth_day` smallint NOT NULL,
        `birth_place` tinytext NOT NULL,
        `race` tinytext NOT NULL,
        `culture` tinytext NOT NULL,
        `culture_variant` tinytext NOT NULL,
        `culture_info` tinytext NOT NULL,
        `profession` tinytext NOT NULL,
        `profession_variant` tinytext NOT NULL,
		UNIQUE KEY hero_id (hero_id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

    $db_table_name = $wpdb->prefix . 'rp_inventory';
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
		UNIQUE KEY item_id (item_id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

function rp_inventory_drop_tables() {
    global $wpdb;

    // delete the database tables
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'rp_inventory');
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'rp_heroes');
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'rp_partys');
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Partys
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_get_partys() {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_partys';
    
    $db_results = $wpdb->get_results("SELECT * FROM $db_table_name ORDER BY name");

    return $db_results;
}

function rp_inventory_get_party($id) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_partys';
    
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
    $db_table_name = $wpdb->prefix . 'rp_partys';

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
    $db_table_name = $wpdb->prefix . 'rp_partys';

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
    $db_table_name = $wpdb->prefix . 'rp_partys';

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
    $db_table_name = $wpdb->prefix . 'rp_heroes';
    
    $db_results = $wpdb->get_results("SELECT * FROM $db_table_name WHERE party=$party_id ORDER BY name");

    return $db_results;
}

function rp_inventory_create_hero($arguments) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_heroes';

    $wpdb->query('START TRANSACTION');

    $values = array(
        'party' => $arguments['party_id'], 
        'creator' => wp_get_current_user()->user_login, 
        'name' => "Neuer Held"
    );
    $wpdb->insert($db_table_name, $values);

    $wpdb->query('COMMIT');    
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

    $wpdb->query('COMMIT');
}

function rp_inventory_delete_item($item) {
   	global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';

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

?>