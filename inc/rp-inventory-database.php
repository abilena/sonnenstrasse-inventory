<?php

function rp_inventory_create_tables() {
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

function rp_inventory_drop_tables() {
    global $wpdb;
    $db_table_name = $wpdb->prefix . 'rp_inventory';

    // delete the database table
    $wpdb->query("DROP TABLE IF EXISTS " . $db_table_name);
}

?>