<?php

function rp_inventory_itemcontainer_html($owner, $container, $contained_items, $hosts_container_id) {

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";

    $output = "";

    $container_content_html = "";
    $container_type = $container->hosts_container_type;
    $sum_rs = array(0, 0, 0, 0, 0, 0, 0, 0);
    $sum_be = 0.0;

    $max_slot = 1;
    if (!empty($contained_items)) {
        $max_slot += max(array_keys($contained_items));
    }
    if ($container_type == "armor") {
        $max_slot = max($max_slot, 5);
    }
    else
    {
        $max_slot = (ceil(($max_slot + 1) / 15) * 15) - 1;
    }

    for ($slot = 0; $slot <= $max_slot; $slot++) {

        $icon = $path_url . "/img/empty.png";
        $name = "";
        $type = "common";
        $item_id = "0";
        $flavor = "";
        $description = "";
        $weight = "";
        $price = "";
        $visibility = "hidden";
        $rs = array("", "", "", "", "", "", "", "");
        $be = "";
        if (array_key_exists($slot, $contained_items)) {
            $item = $contained_items[$slot];
            $icon = $path_url . "/img/icons/" . $item->icon;
            $name = $item->name;
            $type = $item->type;
            $item_id = $item->item_id;
            $flavor = str_replace("\n", "<br>", $item->flavor);
            $description = str_replace("\n", "<br>", $item->description);
            $weight = sprintf("%.0f", $item->weight);
            $price = str_replace(".", ",", sprintf("%.2f", $item->price));
            $rs = $item->rs;
            if (!empty($rs)) {
                $rs = str_replace("0", "-", $rs);
                $rs = explode(";", $rs);
                $be = str_replace(".", ",", sprintf("%.2f", $item->be));
                for ($rs_index = 0; $rs_index < 8; $rs_index++) {
                    $sum_rs[$rs_index] += $rs[$rs_index];
                }
                $sum_be += $item->be;
            }
            $visibility = "visible";
        }

        $popup_class = "";
        if ($container_type == "default" && ($slot % 15 > 10)) {
            $popup_class = "rp-inventory-item-info-popup-left";
        }

        $tpl_inventory_slot = new Template($path_local . "../tpl/inventory_item_slot.html");
        $tpl_inventory_slot->set("PopupClass", $popup_class);
        $inventory_slot_html = $tpl_inventory_slot->output();

        $tpl_inventory_item = new Template($path_local . "../tpl/inventory_item_" . $container_type . ".html");
        $tpl_inventory_item->set("SlotContent", $inventory_slot_html);
        $tpl_inventory_item->set("ContainerId", $hosts_container_id);
        $tpl_inventory_item->set("Slot", $slot);
        $tpl_inventory_item->set("ItemId", $item_id);
        $tpl_inventory_item->set("Owner", $owner);
        $tpl_inventory_item->set("Icon", $icon);
        $tpl_inventory_item->set("Name", $name);
        $tpl_inventory_item->set("Type", $type);
        $tpl_inventory_item->set("Flavor", $flavor);
        $tpl_inventory_item->set("Description", $description);
        $tpl_inventory_item->set("Weight", $weight);
        $tpl_inventory_item->set("Price", $price);
        $tpl_inventory_item->set("RS_KO", $rs[0]);
        $tpl_inventory_item->set("RS_BR", $rs[1]);
        $tpl_inventory_item->set("RS_RU", $rs[2]);
        $tpl_inventory_item->set("RS_BA", $rs[3]);
        $tpl_inventory_item->set("RS_LA", $rs[4]);
        $tpl_inventory_item->set("RS_RA", $rs[5]);
        $tpl_inventory_item->set("RS_LB", $rs[6]);
        $tpl_inventory_item->set("RS_RB", $rs[7]);
        $tpl_inventory_item->set("BE", $be);
        $tpl_inventory_item->set("Visibility", $visibility);
        $container_content_html .= $tpl_inventory_item->output();
    }

    $tpl_inventory_container = new Template($path_local . "../tpl/inventory_container_" . $container_type . ".html");
    $tpl_inventory_container->set("ContainerName", $container->name);
    $tpl_inventory_container->set("ContainerContent", $container_content_html);
    $tpl_inventory_container->set("Sum_RS_KO", sprintf("%.0f", $sum_rs[0]));
    $tpl_inventory_container->set("Sum_RS_BR", sprintf("%.0f", $sum_rs[1]));
    $tpl_inventory_container->set("Sum_RS_RU", sprintf("%.0f", $sum_rs[2]));
    $tpl_inventory_container->set("Sum_RS_BA", sprintf("%.0f", $sum_rs[3]));
    $tpl_inventory_container->set("Sum_RS_LA", sprintf("%.0f", $sum_rs[4]));
    $tpl_inventory_container->set("Sum_RS_RA", sprintf("%.0f", $sum_rs[5]));
    $tpl_inventory_container->set("Sum_RS_LB", sprintf("%.0f", $sum_rs[6]));
    $tpl_inventory_container->set("Sum_RS_RB", sprintf("%.0f", $sum_rs[7]));
    $tpl_inventory_container->set("Sum_BE", str_replace(".", ",", sprintf("%.2f", $sum_be)));
    $output .= $tpl_inventory_container->output();

    return $output;
}

?>