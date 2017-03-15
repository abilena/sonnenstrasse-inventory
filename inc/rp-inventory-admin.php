<?php

require_once('rp-inventory-database.php');

function rp_inventory_detail($hero_id, $detail_type, $detail_label, $detail_value) {
    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";
    $edit_query = http_build_query(array_merge($_GET, array("detail" => $detail_type, "detail_label" => $detail_label)));

    $detail_html = "";
    $detail_label = "<a href=\"?$edit_query\"></a> <strong>$detail_label:</strong>";

    $newlinepos = strpos($detail_value, "\n");
    if ($newlinepos > 0) {
        $detail_value = substr($detail_value, 0, $newlinepos) . " (...)";
    }

    $tpl_inventory_admin_detail = new Template($path_local . "../tpl/inventory_admin_detail.html");
    $tpl_inventory_admin_detail->set("Label", $detail_label);
    $tpl_inventory_admin_detail->set("Value", $detail_value);
    $tpl_inventory_admin_detail->set("BaseUrl", $path_url);
    $detail_html .= $tpl_inventory_admin_detail->output();

    return $detail_html;
}

function rp_inventory_property($hero_id, $property_type, $property_label, $show_detailed) {
    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";
    $edit_query = http_build_query(array_merge($_GET, array("property" => $property_type, "property_label" => $property_label)));

    $property_html = "";
    $property_label = "<a href=\"?$edit_query\"></a> <strong>$property_label:</strong>";
    $properties = rp_inventory_get_properties($hero_id, $property_type);
    if (count($properties) > 0) {
        if ($show_detailed) {
            foreach ($properties as $row_id => $property) {
                $name = $property->name;
                if (!empty($property->value)) {
                    $name .= " " . $property->value;
                }
                if (!empty($property->variant)) {
                    $name .= " (" . $property->variant . ")";
                }
                if (!empty($property->info)) {
                    $name .= " (" . $property->info . ")";
                }

                $tpl_inventory_admin_property = new Template($path_local . "../tpl/inventory_admin_property.html");
                $tpl_inventory_admin_property->set("Label", $property_label);
                $tpl_inventory_admin_property->set("GP", rp_inventory_property_format_cost($property->gp));
                $tpl_inventory_admin_property->set("TGP", rp_inventory_property_format_cost($property->tgp));
                $tpl_inventory_admin_property->set("AP", rp_inventory_property_format_cost($property->ap));
                $tpl_inventory_admin_property->set("Name", $name);
                $tpl_inventory_admin_property->set("BaseUrl", $path_url);
                $property_html .= $tpl_inventory_admin_property->output();

                $property_label = "";
            }
        }
        else {
            $sum_gp = 0;
            $sum_tgb = 0;
            $sum_ap = 0;
            foreach ($properties as $row_id => $property) {
                $sum_gp += $property->gp;
                $sum_tgp += $property->tgp;
                $sum_ap += $property->ap;
            }

            $tpl_inventory_admin_property = new Template($path_local . "../tpl/inventory_admin_property.html");
            $tpl_inventory_admin_property->set("Label", $property_label);
            $tpl_inventory_admin_property->set("GP", $sum_gp);
            $tpl_inventory_admin_property->set("TGP", $sum_tgb);
            $tpl_inventory_admin_property->set("AP", $sum_ap);
            $tpl_inventory_admin_property->set("Name", "");
            $tpl_inventory_admin_property->set("BaseUrl", $path_url);
            $property_html .= $tpl_inventory_admin_property->output();
        }
    }
    else {
        $tpl_inventory_admin_property = new Template($path_local . "../tpl/inventory_admin_property.html");
        $tpl_inventory_admin_property->set("Label", $property_label);
        $tpl_inventory_admin_property->set("GP", "");
        $tpl_inventory_admin_property->set("TGP", "");
        $tpl_inventory_admin_property->set("AP", "");
        $tpl_inventory_admin_property->set("Name", "");
        $tpl_inventory_admin_property->set("BaseUrl", $path_url);
        $property_html .= $tpl_inventory_admin_property->output();
    }

    return $property_html;
}

// displays the options page content
function rp_inventory_admin_options() { ?>	
    <div class="wrap">
	<form method="post" id="next_page_form" action="options.php">
		<?php settings_fields('rp_inventory');
		$options = get_option('rp_inventory'); ?>

    <h1>RP Inventory</h1>
    <div class="rp-inventory-admin">
<?php

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/rp-inventory";

    $partys_html = "";
    $partys = rp_inventory_get_partys();
    $party_id = (array_key_exists("party_id", $_REQUEST) ? $_REQUEST["party_id"] : ((count($partys) > 0) ? $partys[0]->party_id : 0));
    foreach ($partys as $row_id => $party) {

        $tpl_inventory_admin_party = new Template($path_local . "../tpl/inventory_admin_party.html");
        $tpl_inventory_admin_party->set("Id", $party->party_id);
        $tpl_inventory_admin_party->set("Name", $party->name);
        $tpl_inventory_admin_party->set("Selected", ($party->party_id == $party_id) ? "selected" : "");
        $partys_html .= $tpl_inventory_admin_party->output();
    }

    $tpl_inventory_admin_partys = new Template($path_local . "../tpl/inventory_admin_partys.html");
    $tpl_inventory_admin_partys->set("Partys", $partys_html);
    echo ($tpl_inventory_admin_partys->output());

    if (count($partys) > 0) {
        $heroes_html = "";
        $heroes = rp_inventory_get_heroes($party_id);
        $hero_id = (array_key_exists("hero_id", $_REQUEST) ? $_REQUEST["hero_id"] : 0);
        $selected_hero = NULL;
        foreach ($heroes as $row_id => $hero) {

            if ($hero->hero_id == $hero_id) {
                $selected_hero = $hero;
            }

            $portrait = $hero->portrait;
            if (empty($portrait)) {
                $portrait = $path_url . "/img/shapes/" . (($hero->gender == 'female') ? "portrait_female.png" : "portrait_male.png");
            }

            $tpl_inventory_admin_hero = new Template($path_local . "../tpl/inventory_admin_hero.html");
            $tpl_inventory_admin_hero->set("Party", $party_id);
            $tpl_inventory_admin_hero->set("Id", $hero->hero_id);
            $tpl_inventory_admin_hero->set("Name", $hero->name);
            $tpl_inventory_admin_hero->set("Portrait", $portrait);
            $heroes_html .= $tpl_inventory_admin_hero->output();
        }

        $tpl_inventory_admin_heroes = new Template($path_local . "../tpl/inventory_admin_heroes.html");
        $tpl_inventory_admin_heroes->set("Heroes", $heroes_html);
        echo ($tpl_inventory_admin_heroes->output());

        if (!empty($selected_hero)) {
            $detail_type = (array_key_exists("detail", $_REQUEST) ? $_REQUEST["detail"] : "");
            $detail_label = (array_key_exists("detail_label", $_REQUEST) ? $_REQUEST["detail_label"] : "");
            $property_type = (array_key_exists("property", $_REQUEST) ? $_REQUEST["property"] : "");
            $property_label = (array_key_exists("property_label", $_REQUEST) ? $_REQUEST["property_label"] : "");

            if (!empty($detail_type)) {

                $detail_value = rp_inventory_get_detail($hero_id, $detail_type);
                $tpl_inventory_admin_hero_detail = new Template($path_local . "../tpl/inventory_admin_hero_detail_edit.html");
                $tpl_inventory_admin_hero_detail->set("Hero", $hero_id);
                $tpl_inventory_admin_hero_detail->set("Type", $detail_type);
                $tpl_inventory_admin_hero_detail->set("Label", $detail_label);

                if ($detail_type == "biography" || $detail_type == "flavor") {
                    $tpl_inventory_admin_hero_detail->set("InputElement", "<textarea id=\"rp-inventory-admin-table-detail\">" . $detail_value . "</textarea>");
                }
                else {
                    $tpl_inventory_admin_hero_detail->set("InputElement", "<input id=\"rp-inventory-admin-table-detail\" value=\"" . $detail_value . "\"></input>");
                }

                echo ($tpl_inventory_admin_hero_detail->output());
                                
            }
            else if (!empty($property_type)) {

                $property_edit_id = (array_key_exists("property_edit", $_REQUEST) ? $_REQUEST["property_edit"] : 0);
                $properties_html = "";
                $properties = rp_inventory_get_properties($hero_id, $property_type);
                foreach ($properties as $row_id => $property) {
                    $edit_query = http_build_query(array_merge($_GET, array("property_edit" => $property->property_id)));
                    $edit = ($property_edit_id == $property->property_id) ? "_edit" : "";
                    $tpl_inventory_admin_hero_property = new Template($path_local . "../tpl/inventory_admin_hero_property" . $edit . ".html");
                    $tpl_inventory_admin_hero_property->set("Id", $property->property_id);
                    $tpl_inventory_admin_hero_property->set("Hero", $hero_id);
                    $tpl_inventory_admin_hero_property->set("Type", $property_type);
                    $tpl_inventory_admin_hero_property->set("Name", $property->name);
                    $tpl_inventory_admin_hero_property->set("GP", rp_inventory_property_format_cost($property->gp));
                    $tpl_inventory_admin_hero_property->set("TGP", rp_inventory_property_format_cost($property->tgp));
                    $tpl_inventory_admin_hero_property->set("AP", rp_inventory_property_format_cost($property->ap));
                    $tpl_inventory_admin_hero_property->set("EditQuery", $edit_query);
                    $properties_html .= $tpl_inventory_admin_hero_property->output();
                }

                $edit_query = http_build_query(array_merge($_GET, array("property_edit" => 0)));
                $edit = ($property_edit_id == 0) ? "_edit" : "";
                $tpl_inventory_admin_hero_property = new Template($path_local . "../tpl/inventory_admin_hero_property" . $edit . ".html");
                $tpl_inventory_admin_hero_property->set("Id", "0");
                $tpl_inventory_admin_hero_property->set("Hero", $hero_id);
                $tpl_inventory_admin_hero_property->set("Type", $property_type);
                $tpl_inventory_admin_hero_property->set("Name", "");
                $tpl_inventory_admin_hero_property->set("GP", "");
                $tpl_inventory_admin_hero_property->set("TGP", "");
                $tpl_inventory_admin_hero_property->set("AP", "");
                $tpl_inventory_admin_hero_property->set("EditQuery", $edit_query);
                $properties_html .= $tpl_inventory_admin_hero_property->output();

                $tpl_inventory_admin_hero_properties = new Template($path_local . "../tpl/inventory_admin_hero_properties.html");
                $tpl_inventory_admin_hero_properties->set("Label", $property_label);
                $tpl_inventory_admin_hero_properties->set("Properties", $properties_html);
                echo ($tpl_inventory_admin_hero_properties->output());                
            }
            else {

                $tpl_inventory_admin_hero_details = new Template($path_local . "../tpl/inventory_admin_hero_details.html");
                $tpl_inventory_admin_hero_details->set("Id", $selected_hero->hero_id);
                $tpl_inventory_admin_hero_details->set("Heading", $selected_hero->name);
                $tpl_inventory_admin_hero_details->set("Name", rp_inventory_detail($selected_hero->hero_id, "name", "Kurzname", $selected_hero->name));
                $tpl_inventory_admin_hero_details->set("DisplayName", rp_inventory_detail($selected_hero->hero_id, "display_name", "Name", $selected_hero->display_name));
                $tpl_inventory_admin_hero_details->set("Biography", rp_inventory_detail($selected_hero->hero_id, "biography", "Biografie", $selected_hero->biography));
                $tpl_inventory_admin_hero_details->set("Flavor", rp_inventory_detail($selected_hero->hero_id, "flavor", "Flavor", $selected_hero->flavor));
                $tpl_inventory_admin_hero_details->set("Portrait", rp_inventory_detail($selected_hero->hero_id, "portrait", "Portrait", $selected_hero->portrait));
                $tpl_inventory_admin_hero_details->set("Race", rp_inventory_property($selected_hero->hero_id, "race", "Rasse", true));
                $tpl_inventory_admin_hero_details->set("Culture", rp_inventory_property($selected_hero->hero_id, "culture", "Kultur", true));
                $tpl_inventory_admin_hero_details->set("Profession", rp_inventory_property($selected_hero->hero_id, "profession", "Profession", true));
                $tpl_inventory_admin_hero_details->set("Vorteile", rp_inventory_property($selected_hero->hero_id, "advantage", "Vorteile", true));
                $tpl_inventory_admin_hero_details->set("Nachteile", rp_inventory_property($selected_hero->hero_id, "disadvantage", "Nachteile", true));
                $tpl_inventory_admin_hero_details->set("Eigenschaften", rp_inventory_property($selected_hero->hero_id, "ability", "Eigenschaften", false));
                $tpl_inventory_admin_hero_details->set("Basiswerte", rp_inventory_property($selected_hero->hero_id, "basic", "Basiswerte", false));
                $tpl_inventory_admin_hero_details->set("Talente", rp_inventory_property($selected_hero->hero_id, "skill", "Talente", false));
                $tpl_inventory_admin_hero_details->set("Zauber", rp_inventory_property($selected_hero->hero_id, "spell", "Zauber", false));
                $tpl_inventory_admin_hero_details->set("Sonderfertigkeiten", rp_inventory_property($selected_hero->hero_id, "feat", "Sonderfertigkeiten", false));
                echo ($tpl_inventory_admin_hero_details->output());
            }
        }
    }

 ?>
    </div>
    <p class="submit">
	<input type="submit" name="submit" class="button-primary" value="<?php _e('Update Options', 'rp-inventory'); ?>" />
	</p>
	</form>
	</div>
<?php 
} // end function rp_inventory_admin_options() 

?>