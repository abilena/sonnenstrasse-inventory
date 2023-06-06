<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/sonnenstrasse-character/inc/rp-character-constants-talente.php');

function rp_inventory_format_bonus($value) {
    
    return (($value < 0) ? "" : "+") . $value;
}

function rp_inventory_itemcontainer_html($owner, $is_admin_page, $is_merchant, $is_user, $is_admin, $is_owner, $container, $contained_items, $hosts_container_id, $index, $sum_be) {

    global $talent_data;

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/sonnenstrasse-inventory";

    $template_prefix = $is_admin_page ? "_admin" : "";
    $output = "";

    $container_content_html = "";
    $container_type = $container->hosts_container_type;
    $sum_rs = array(0, 0, 0, 0, 0, 0, 0, 0);

    $max_slot = 1;
    if (!empty($contained_items)) {
        $max_slot += max(array_keys($contained_items));
    }
    if ($container_type == "armor") {
        $max_slot = max($max_slot, 5);
    }
    elseif ($container_type == "weapon") {
        $max_slot = 1;
    }
    else
    {
        $max_slot = max(6, (ceil(($max_slot + 1) / 7) * 7) - 1);
    }

    $basiswerte = rp_inventory_get_properties($owner, "basic");
    $eigenschaften = rp_inventory_get_properties($owner, "ability");
    $sonderfertigkeiten = rp_inventory_get_properties($owner, "feat");
    $talente = rp_inventory_get_properties($owner, "skill");

    $mu_wert = @array_column($eigenschaften, null, "name")["Mut"]->value ?? 0;
    $in_wert = @array_column($eigenschaften, null, "name")["Intuition"]->value ?? 0;
    $ge_wert = @array_column($eigenschaften, null, "name")["Gewandtheit"]->value ?? 0;
    $kk_wert = @array_column($eigenschaften, null, "name")["Körperkraft"]->value ?? 0;
    $ini_basis = round(($mu_wert+$mu_wert+$in_wert+$ge_wert)/5);
    $at_basis = round(($mu_wert+$ge_wert+$kk_wert)/5);
    $pa_basis = round(($in_wert+$ge_wert+$kk_wert)/5);
    $has_aw1 = @array_column($sonderfertigkeiten, null, "name")["Ausweichen I"] != null;
    $has_aw2 = @array_column($sonderfertigkeiten, null, "name")["Ausweichen II"] != null;
    $has_aw3 = @array_column($sonderfertigkeiten, null, "name")["Ausweichen III"] != null;
    $has_ini1 = @array_column($sonderfertigkeiten, null, "name")["Kampfreflexe"] != null;
    $has_ini2 = @array_column($sonderfertigkeiten, null, "name")["Kampfgespür"] != null;
    $has_ruest_gew1 = @array_column($sonderfertigkeiten, null, "name")["Rüstungsgewöhnung I"] != null;
    $has_ruest_gew2 = @array_column($sonderfertigkeiten, null, "name")["Rüstungsgewöhnung II"] != null;
    $has_ruest_gew3 = @array_column($sonderfertigkeiten, null, "name")["Rüstungsgewöhnung III"] != null;
    
    $ini_sf = 0;
    $aw_sf = 0;
    $rs_gewoehnung = 0;

    $ini_tooltip_header = "<table><tr><td>" . rp_inventory_format_bonus($ini_basis) . "</td><td>INI Basis</td></tr>";
    $tp_tooltip_header = "<table>";
    $at_tooltip_header = "<table><tr><td>" . rp_inventory_format_bonus($at_basis) . "</td><td>AT Basis</td></tr>";
    $pa_tooltip_header = "<table><tr><td>" . rp_inventory_format_bonus($pa_basis) . "</td><td>PA Basis</td></tr>";
    $aw_tooltip_header = "<table><tr><td>" . rp_inventory_format_bonus($pa_basis) . "</td><td>PA Basis</td></tr>";
    $be_tooltip_header = "<table>";

    if ($has_ini1) { $ini_tooltip_header .= "<tr><td>+4</td><td>Kampfreflexe</td></tr>"; $ini_sf += 4; }
    if ($has_ini2) { $ini_tooltip_header .= "<tr><td>+2</td><td>Kampfgespür</td></tr>"; $ini_sf += 2; }
    if ($has_aw1) { $aw_tooltip_header .= "<tr><td>+3</td><td>Ausweichen I</td></tr>"; $aw_sf += 3; }
    if ($has_aw2) { $aw_tooltip_header .= "<tr><td>+3</td><td>Ausweichen II</td></tr>"; $aw_sf += 3; }
    if ($has_aw3) { $aw_tooltip_header .= "<tr><td>+3</td><td>Ausweichen III</td></tr>"; $aw_sf += 3; }
    if      ($has_ruest_gew3) { $be_tooltip_header .= "<tr><td>Rüstungsgewöhnung III</td></tr>"; $rs_gewoehnung = 2; }
    else if ($has_ruest_gew2) { $be_tooltip_header .= "<tr><td>Rüstungsgewöhnung II</td></tr>"; $rs_gewoehnung = 1; }
    else if ($has_ruest_gew1) { $be_tooltip_header .= "<tr><td>Rüstungsgewöhnung I</td></tr>"; $rs_gewoehnung = 1; }
    else                      { $be_tooltip_header .= "<tr><td>Keine Rüstungsgewöhnung</td></tr>"; $rs_gewoehnung = 0; }

    $real_be = max(0, round($sum_be) - $rs_gewoehnung);

    $ini_tooltip_footer = "<tr><td>" . rp_inventory_format_bonus(-$real_be) . "</td><td>BE</td></tr></table>";
    $tp_tooltip_footer = "</table>";
    $at_tooltip_footer = "</table>";
    $pa_tooltip_footer = "</table>";
    $aw_tooltip_footer = "<tr><td>" . rp_inventory_format_bonus(-$real_be) . "</td><td>BE</td></tr></table>";
    $be_tooltip_footer = "</table>";

    $count_empty = 0;
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
        $wp_dk = "";
        $wp_ini = "";
        $wp_tp = "";
        $wp_at = "";
        $wp_pa = "";
        $wp_aw = "";
        $wp_bf = "";

        $wp_ini_wm = "";
        $wp_ini_basis = "";
        $wp_ini_sf = "";
        $wp_at_basis = "";
        $wp_pa_basis = "";
        $wp_at_taw = "";
        $wp_pa_taw = "";
        $wp_at_wm = "";
        $wp_pa_wm = "";
        $wp_at_taw_be = "";
        $wp_pa_taw_be = "";
        $wp_aw_sf = "";

        $ini_tooltip_wp = "";
        $tp_tooltip_wp = ""; 
        $at_tooltip_wp = ""; 
        $pa_tooltip_wp = ""; 
        $aw_tooltip_wp = ""; 
        $be_tooltip_wp = ""; 

        if (array_key_exists($slot, $contained_items)) {
            $item = $contained_items[$slot];

            if (!$is_admin_page && ($item->hosts_container_type == "armor" || $item->hosts_container_type == "weapon")) {
                $max_slot++;
                continue;
            }

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
                    $sum_rs[$rs_index] += intval($rs[$rs_index]);
                }
            }
            if (!empty($item->wp_dk)) {

                $taw_at = 0;
                $taw_pa = 0;
                $taw_be_at = 0;
                $taw_be_pa = 0;
                $taw_spez = 0;
                $tp_kk_bonus = 0;
                if (!empty($item->wp_skill))
                {
                    foreach ($talente as $talent)
                    {
                        if (strpos($talent->name, $item->wp_skill) === 0) {
                            $taw_ebe = intval(@str_replace('$be', '', $talent_data[$item->wp_skill]['eBE']));
                            $taw_at = max(0, floor(($talent->value + $talent->at) / 2.0));
                            $taw_pa = $talent->value - $taw_at;
                            $taw_be_at = max(0, ceil(($real_be + $taw_ebe) / 2.0));
                            $taw_be_pa = ($real_be + $taw_ebe) - $taw_be_at;
                            $tp_kk_bonus = floor(max(0, $kk_wert - $item->wp_tp_kk_req) / max(1, $item->wp_tp_kk_span));
                            $has_wp_spez = false;
                            foreach ($sonderfertigkeiten as $sf) {
                                if (($sf->name == "Waffenspezialisierung") && ($sf->variant == ($item->wp_skill.", ".($item->wp_base ?? $item->name)))) {
                                    $has_wp_spez = true;
                                }
                            }
                            $taw_spez = ($has_wp_spez ? 1 : 0);

                            $ini_tooltip_wp .= "<tr><td>" . rp_inventory_format_bonus($item->wp_ini) . "</td><td>INI WM</td></tr>";
                            $tp_tooltip_wp .= "<tr><td>" . $item->wp_tp_dices . "W" . rp_inventory_format_bonus($item->wp_tp_bonus) . "</td><td>" . $item->name . "</td></tr>"; 
                            $tp_tooltip_wp .= "<tr><td>" . rp_inventory_format_bonus($tp_kk_bonus) . "</td><td>Bonus TP/KK</td></tr>"; 
                            $at_tooltip_wp .= "<tr><td>" . rp_inventory_format_bonus($taw_at) . "</td><td>AT TaW (" . $talent->name . ")</td></tr>";
                            $at_tooltip_wp .= "<tr><td>" . rp_inventory_format_bonus(0-$taw_be_at) . "</td><td>AT BE nach eBE (BE" . rp_inventory_format_bonus($taw_ebe) . ")</td></tr>";
                            $at_tooltip_wp .= "<tr><td>" . rp_inventory_format_bonus($item->wp_wm_at) . "</td><td>AT WM (" . $item->wp_wm_at . "/" . $item->wp_wm_pa . ")</td></tr>";
                            $pa_tooltip_wp .= "<tr><td>" . rp_inventory_format_bonus($taw_pa) . "</td><td>PA TaW (" . $talent->name . ")</td></tr>";
                            $pa_tooltip_wp .= "<tr><td>" . rp_inventory_format_bonus(0-$taw_be_pa) . "</td><td>PA BE nach eBE (BE" . rp_inventory_format_bonus($taw_ebe) . ")</td></tr>";
                            $pa_tooltip_wp .= "<tr><td>" . rp_inventory_format_bonus($item->wp_wm_pa) . "</td><td>PA WM (" . $item->wp_wm_at . "/" . $item->wp_wm_pa . ")</td></tr>";
                            if ($has_wp_spez) {
                                $at_tooltip_wp .= "<tr><td>+1</td><td>Waffenspezialisierung</td></tr>";
                                $pa_tooltip_wp .= "<tr><td>+1</td><td>Waffenspezialisierung</td></tr>";
                            }
                        }
                    }
                }

                $wp_dk = $item->wp_dk;
                $wp_ini = $ini_basis + $ini_sf + $item->wp_ini - $real_be;
                $wp_tp = $item->wp_tp_dices . "W" . rp_inventory_format_bonus($item->wp_tp_bonus + $tp_kk_bonus) . (empty($item->wp_tp_type) ? "" : (" (" . $item->wp_tp_type . ")"));
                $wp_at = $at_basis + $taw_at + $item->wp_wm_at - $taw_be_at + $taw_spez;
                $wp_pa = $pa_basis + $taw_pa + $item->wp_wm_pa - $taw_be_pa + $taw_spez;
                $wp_aw = $pa_basis + $aw_sf - $real_be;
                $wp_bf = $item->wp_bf;
            }
            $visibility = "visible";
        }

        $is_empty = "";
        if ($name == "")
        {
            $is_empty = (($count_empty > 0) ? "rp-inventory-container-slot-empty" : "");
            $count_empty++;
        }

        $popup_class = "";
        if ($is_merchant) {
            if ($is_owner) {
                $popup_class = "rp-inventory-item-info-popup-left";                
            }
        } else {
            if ($container_type == "default" && ($index % 2 == 1)) {
                $popup_class = "rp-inventory-item-info-popup-left";
            }
        }

        $tpl_inventory_slot = new RPInventory\Template($path_local . "../tpl/inventory" . $template_prefix . "_item_slot.html");
        $tpl_inventory_slot->set("PopupClass", $popup_class);
        if ($is_merchant) {
            $tpl_inventory_slot->set("OnClick", ($is_user && ($item_id > 0)) ? "rp_inventory_select_item(event, 'rp-inventory-equipment-of-$owner')" : "");
        } else {
            $tpl_inventory_slot->set("OnClick", ($is_admin || $is_owner) ? "rp_inventory_click_item(event, 'rp-inventory-equipment-of-$owner')" : "");
        }
        $inventory_slot_html = $tpl_inventory_slot->output();

        $tpl_inventory_item = new RPInventory\Template($path_local . "../tpl/inventory" . $template_prefix . "_item_" . $container_type . ".html");
        $tpl_inventory_item->set("SlotContent", $inventory_slot_html);
        $tpl_inventory_item->set("ContainerId", $hosts_container_id);
        $tpl_inventory_item->set("Slot", $slot);
        $tpl_inventory_item->set("IsEmpty", $is_empty);
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
        $tpl_inventory_item->set("DK", $wp_dk);
        $tpl_inventory_item->set("INI", $wp_ini);
        $tpl_inventory_item->set("TP", $wp_tp);
        $tpl_inventory_item->set("AT", $wp_at);
        $tpl_inventory_item->set("PA", $wp_pa);
        $tpl_inventory_item->set("AW", $wp_aw);
        $tpl_inventory_item->set("BF", $wp_bf);
        $tpl_inventory_item->set("INI_TOOLTIP", $ini_tooltip_header . $ini_tooltip_wp . $ini_tooltip_footer);
        $tpl_inventory_item->set("TP_TOOLTIP", $tp_tooltip_header . $tp_tooltip_wp . $tp_tooltip_footer);
        $tpl_inventory_item->set("AT_TOOLTIP", $at_tooltip_header . $at_tooltip_wp . $at_tooltip_footer);
        $tpl_inventory_item->set("PA_TOOLTIP", $pa_tooltip_header . $pa_tooltip_wp . $pa_tooltip_footer);
        $tpl_inventory_item->set("AW_TOOLTIP", $aw_tooltip_header . $aw_tooltip_wp . $aw_tooltip_footer);
        $tpl_inventory_item->set("Visibility", $visibility);
        $container_content_html .= $tpl_inventory_item->output();
    }

    global $rp_inventory_index;
    
    if ($container_type == "armor") { $container_class = "rp-inventory-equipment-rs-overview"; }
    else if ($container_type == "weapon") { $container_class = "rp-inventory-equipment-wp-details"; }
    else { $container_class = "rp-inventory-equipment-grid"; }

    $container_class_from_cookie = @$_COOKIE["rp-inventory-equipment-container-$rp_inventory_index-$owner-$container->item_id"];
    if (!empty($container_class_from_cookie)) { $container_class = $container_class_from_cookie; }

    $tpl_inventory_container = new RPInventory\Template($path_local . "../tpl/inventory" . $template_prefix . "_container_" . $container_type . ".html");
    $tpl_inventory_container->set("ContainerClass", $container_class);
	$tpl_inventory_container->set("PluginBaseUri", $path_url);
    $tpl_inventory_container->set("ShortcodeId", $rp_inventory_index);
    $tpl_inventory_container->set("OwnerId", $owner);
    $tpl_inventory_container->set("ContainerId", $container->item_id);
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
    $tpl_inventory_container->set("Real_BE", sprintf("%.0f", $real_be));
    $tpl_inventory_container->set("Text_BE", $be_tooltip_header . $be_tooltip_wp . $be_tooltip_footer);
    $output .= $tpl_inventory_container->output();

    return $output;
}

?>