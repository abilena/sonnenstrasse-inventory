<?php

function rp_inventory_get_icons() {

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";

    $icon_files = RPInventory\get_all_files($path_local . "../img/icons/");
    
    $output = "var icons = " . json_encode($icon_files) . ";";
    
    return $output;
}

?>