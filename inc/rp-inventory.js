
var rp_inventory_baseuri = "/wp-content/plugins/sonnenstrasse-inventory";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Merchant show/hide
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_toggle_merchant(expander, merchant)
{
    var merchant = document.getElementById(merchant);
    if (merchant.style.display == "block") {
        merchant.style.display = "none";
        expander.className = "rp-inventory-banner-expander-default";
    } else {
        merchant.style.display = "block";
        expander.className = "rp-inventory-banner-expander-pressed";
    }
}

var equipment_type_switchers = {
  "default": "grid,list,compact",
  "armor": "grid,list,compact,rs-details,rs-overview",
  "weapon": "grid,list,compact,wp-details",
};

function rp_inventory_toggle_equipment_display(container_name) {
    var container = document.getElementById(container_name);
	var className = container.className.substring(23);
	var type = container.dataset.type;
	var switcher = equipment_type_switchers[type].split(',');
	var switcher_index = (switcher.indexOf(className) + 1) % switcher.length;
	container.className = "rp-inventory-equipment-" + switcher[switcher_index];
    document.cookie = (container_name + "=" + container.className + ";");
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Merchant prev/next
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_merchant_heroselector_prev(container_name) {
    rp_inventory_merchant_heroselector_move(container_name, -1);
}

function rp_inventory_merchant_heroselector_next(container_name) {
    rp_inventory_merchant_heroselector_move(container_name, +1);
}

function rp_inventory_merchant_heroselector_move(container_name, delta) {
    var container = document.getElementById(container_name);
    var children = document.querySelectorAll('#' + container_name + ' .rp-inventory-container-heroselector');

    var oldSelectedIndex = parseInt(container.dataset.selectedIndex);
    var newSelectedIndex = (oldSelectedIndex + delta);
    newSelectedIndex = (children.length + newSelectedIndex) % (children.length);

    children.item(oldSelectedIndex).style.display = "none";
    children.item(newSelectedIndex).style.display = "block";

    container.dataset.selectedIndex = newSelectedIndex;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Merchant selection
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_select_item(e, owner_id)
{
    if (!e)
        e = window.event;
    var sender = e.srcElement || e.target;

    var ownerElement = document.getElementById(owner_id);
    var selectedItems = ownerElement.dataset.selectedItem;
    var selectedItemsList = selectedItems.split(',').filter(s => s != "");

    var slot = sender;
    if (sender.nodeName.toLowerCase() == "img")
        slot = sender.parentNode;

    if (!stringStartsWith(slot.id, "con_"))
        return;

    var index = selectedItemsList.indexOf(slot.id);
    if (index < 0) {
        slot.style.border="2px solid yellow";
        selectedItemsList.push(slot.id);
        selectedItems = selectedItemsList.join(",");
        ownerElement.dataset.selectedItem = selectedItems;
    }
    else {
        slot.style.border="2px solid black";
        selectedItemsList.splice(index, 1);
        selectedItems = selectedItemsList.join(",");
        ownerElement.dataset.selectedItem = selectedItems;
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Merchant buy/sell
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_transfer_items(transaction, merchant_id, hero_id) {
    
    var ownerElement = document.getElementById('rp-inventory-equipment-of-' + hero_id);
    var selectedItems = ownerElement.dataset.selectedItem;

    var merchant = rp_inventory_transfer_get_select_owner(merchant_id, 'merchant');
    var hero = rp_inventory_transfer_get_select_owner(merchant_id, 'hero');
    
    if (!confirm("Are you sure you want to " + transaction + " these items?"))
        return;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.substring(0, 9).toLowerCase() != "succeeded")
                alert(this.responseText);

            reloadScroll();
        }
    };
    xhttp.open("GET", rp_inventory_baseuri + "/" + transaction + "-items.php?hero=" + hero + "&merchant=" + merchant + "&items=" + selectedItems, true);
    xhttp.send();
}

function rp_inventory_transfer_get_select_owner(merchant_id, owner_type) {

    var container_name = 'rp-inventory-merchantcontainer-' + merchant_id + '-' + owner_type;
    var merchantcontainer = document.getElementById(container_name);
    var selectedIndex = merchantcontainer.dataset.selectedIndex;
    var children = document.querySelectorAll('#' + container_name + ' .rp-inventory-container-heroselector');
    var selectedOwner = children.item(selectedIndex);
    var ownerId = selectedOwner.dataset.heroId;
    return ownerId;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Item swapping
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function rp_inventory_click_item(e, owner_id)
{
    if (!e)
        e = window.event;
    var sender = e.srcElement || e.target;

    var ownerElement = document.getElementById(owner_id);
    var selectedItem = document.getElementById(ownerElement.dataset.selectedItem);
    var swappedItem = document.getElementById(ownerElement.dataset.swappedItem);

    if (swappedItem != null)
        return;

    var slot = sender;
    if (sender.nodeName.toLowerCase() == "img")
        slot = sender.parentNode;

    if (!stringStartsWith(slot.id, "con_"))
        return;

    if (selectedItem == null) {
        selectedItem = slot;
        slot.style.border="2px solid yellow";
        ownerElement.dataset.selectedItem = selectedItem.id;
    }
    else {
        swappedItem = slot;

        if (swappedItem.id == selectedItem.id) {
            ownerElement.dataset.selectedItem = null;
            ownerElement.dataset.swappedItem = null;
            slot.style.border="2px solid black";
            return;
        }

        slot.style.border="2px solid yellow";
        ownerElement.dataset.swappedItem = swappedItem.id;

        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                if (this.responseText.substring(0, 9).toLowerCase() != "succeeded")
                    alert(this.responseText);

                reloadScroll();
            }
        };
        xhttp.open("GET", rp_inventory_baseuri + "/swap-item.php?item1=" + selectedItem.id + "&item2=" + swappedItem.id, true);
        xhttp.send();
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Populate item creation icon popup option
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var icons_list = null;
var prevFileName = null;

function populateFolders()
{
    //var icons_str = document.getElementById("rp-inventory-create-icon-all-files").value;
    //icons_list = icons_str.split(":");
    icons_list = icons;

    var selectElement = document.getElementById("rp-inventory-create-folder");

    var folder_list = [];
    for (var index = 0; index < icons_list.length; index++) {
        var icon_path = icons_list[index];
        var icon_folder = icon_path.substring(0, icon_path.indexOf("/"));
        if (folder_list.indexOf(icon_folder) < 0)
            folder_list.push(icon_folder);
    }

    for (var index = 0; index < folder_list.length; index++) {
        var folderName = folder_list[index];
        var optionElement = document.createElement("option");
        optionElement.setAttribute("value", folderName);
        optionElement.textContent = folderName;
        selectElement.appendChild(optionElement);
    }

    populateSubFolders();
}

function populateSubFolders()
{
    var folder = document.getElementById("rp-inventory-create-folder").value + "/";
    var selectElement = document.getElementById("rp-inventory-create-subfolder");
    while (selectElement.firstChild) {
        selectElement.removeChild(selectElement.firstChild);
    }
    
    var folder_list = [];
    for (var index = 0; index < icons_list.length; index++) {
        var icon_path = icons_list[index];
        if (stringStartsWith(icon_path, folder)) {
            icon_path = icon_path.substring(folder.length);
            var icon_folder = icon_path.substring(0, icon_path.indexOf("/"));
            if (folder_list.indexOf(icon_folder) < 0)
                folder_list.push(icon_folder);
        }
    }

    for (var index = 0; index < folder_list.length; index++) {
        var folderName = folder_list[index];
        var optionElement = document.createElement("option");
        optionElement.setAttribute("value", folderName);
        optionElement.textContent = folderName;
        selectElement.appendChild(optionElement);
    }

    populateFiles();
}

function populateFiles()
{
    var folder = document.getElementById("rp-inventory-create-folder").value + "/";
    folder += document.getElementById("rp-inventory-create-subfolder").value + "/";
    var selectElement = document.getElementById("rp-inventory-create-icon-file");
    while (selectElement.firstChild) {
        selectElement.removeChild(selectElement.firstChild);
    }
    var divElement = document.getElementById("rp-inventory-popup-create-icon-list");
    while (divElement.firstChild) {
        divElement.removeChild(divElement.firstChild);
    }

    var file_list = [];
    for (var index = 0; index < icons_list.length; index++) {
        var icon_path = icons_list[index];
        if (stringStartsWith(icon_path, folder)) {
            icon_path = icon_path.substring(folder.length);
            if (file_list.indexOf(icon_path) < 0)
                file_list.push(icon_path);
        }
    }

    for (var index = 0; index < file_list.length; index++) {
        var fileName = file_list[index];
        var optionElement = document.createElement("option");
        optionElement.setAttribute("value", fileName);
        optionElement.textContent = fileName;
        selectElement.appendChild(optionElement);

        var borderSunkenElement = document.createElement("div");
        borderSunkenElement.setAttribute("class", "rp-inventory-equipment-item-slot-border");
        borderSunkenElement.setAttribute("style", "display: inline-block;");
        var borderSelectElement = document.createElement("div");
        borderSelectElement.setAttribute("class", "rp-inventory-equipment-item-slot-select");
        borderSelectElement.setAttribute("id", "rp-inventory-preview-icon-" + fileName);
        borderSelectElement.onclick = function() { selectClickedIcon(); };
        var imageElement = document.createElement("img");
        imageElement.setAttribute("class", "rp-inventory-item-icon");
        imageElement.setAttribute("src", rp_inventory_baseuri + "/img/icons/" + folder + fileName);
        borderSelectElement.appendChild(imageElement);
        borderSunkenElement.appendChild(borderSelectElement);
        divElement.appendChild(borderSunkenElement);
    }

    selectIcon();
}

function selectIcon()
{
    var fileName = document.getElementById("rp-inventory-create-icon-file").value;
    var file = document.getElementById("rp-inventory-create-folder").value + "/";
    file += document.getElementById("rp-inventory-create-subfolder").value + "/";
    file += fileName;

    var imageElement = document.getElementById("rp-inventory-preview-icon");
    imageElement.src = rp_inventory_baseuri + "/img/icons/" + file;

    var borderSelectElement = document.getElementById("rp-inventory-preview-icon-" + fileName);
    if (borderSelectElement != null)
        borderSelectElement.style.border = "2px solid yellow";

    if (prevFileName) {
        borderSelectElement = document.getElementById("rp-inventory-preview-icon-" + prevFileName);
        if (borderSelectElement)
            borderSelectElement.style.border = "2px solid black";
    }

    prevFileName = fileName;
}

function updateItemText()
{
    var name = document.getElementById("rp-inventory-create-name");
    var type = document.getElementById("rp-inventory-create-type");
    
    var itemText = document.getElementById("rp-inventory-preview-name");
    itemText.textContent = name.value;
    itemText.className = "rp-inventory-item-name-text rp-inventory-item-" + type.value;
}

function selectClickedIcon(e)
{
    if (!e)
        e = window.event;
    var sender = e.srcElement || e.target;

    var slot = sender;
    if (sender.nodeName.toLowerCase() == "img")
        slot = sender.parentNode;

    if (slot) {
        var prefix = "rp-inventory-preview-icon-";
        var fileName = slot.id.substring(prefix.length);

        var selectElement = document.getElementById("rp-inventory-create-icon-file");
        for (var index = 0; index < selectElement.options.length; index++) {
            if (selectElement.options[index].value === fileName) {
                selectElement.selectedIndex = index;
                selectIcon();
                break;
            }
        }
    }
}

function stringStartsWith(testString, startPattern)
{
    return (testString.substring(0, startPattern.length).toLowerCase() == startPattern.toLowerCase());
}
