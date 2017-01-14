
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Item creation
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var isCreating = false;

function rp_inventory_create_item_popup()
{
    var popup = document.getElementById("rp-inventory-popup-create");
    popup.style.visibility = "visible";

    if (icons_list == null) 
        populateFolders();
}

function rp_inventory_create_item_close()
{
    var popup = document.getElementById("rp-inventory-popup-create");
    popup.style.visibility = "collapse";

    reloadScroll();
}

function rp_inventory_create_item_icon_popup()
{
    var popup = document.getElementById("rp-inventory-popup-create-icon");
    popup.style.visibility = "visible";
}

function rp_inventory_create_item_icon_close()
{
    var popup = document.getElementById("rp-inventory-popup-create-icon");
    popup.style.visibility = "collapse";
}

function rp_inventory_create_item()
{
    if (isCreating)
    {
        alert("Item creation still pending");
        return;
    }

    var name = document.getElementById("rp-inventory-create-name").value;
    var icon = document.getElementById("rp-inventory-create-folder").value + "/";
    icon += document.getElementById("rp-inventory-create-subfolder").value + "/";
    icon += document.getElementById("rp-inventory-create-icon-file").value;
    var type = document.getElementById("rp-inventory-create-type").value;
    var price = document.getElementById("rp-inventory-create-price").value;
    var weight = document.getElementById("rp-inventory-create-weight").value;
    var is_container = document.getElementById("rp-inventory-create-is-container").checked;
    var container_order = document.getElementById("rp-inventory-create-container-order").value;
    var description = document.getElementById("rp-inventory-create-description").value;
    var flavor = document.getElementById("rp-inventory-create-flavor").value;

    name = encodeURIComponent(name);
    icon = encodeURIComponent(icon);
    type = encodeURIComponent(type);
    price = encodeURIComponent(price);
    weight = encodeURIComponent(weight);
    is_container = encodeURIComponent(is_container);
    container_order = encodeURIComponent(container_order);
    description = encodeURIComponent(description);
    flavor = encodeURIComponent(flavor);

    var parameters = "name=" + name;
    parameters += "&icon=" + icon;
    parameters += "&type=" + type;
    parameters += "&price=" + price;
    parameters += "&weight=" + weight;
    parameters += "&is_container=" + is_container;
    parameters += "&container_order=" + container_order;
    parameters += "&description=" + description;
    parameters += "&flavor=" + flavor;

    isCreating = true;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            isCreating = false;
        }
    };

    xhttp.open("POST", "wp-content/plugins/rp-inventory/create-item.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("Content-length", parameters.length);
    xhttp.send(parameters);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Item swapping
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var selectedItem = null;
var swapItem = null;

function rp_inventory_click_item(e)
{
    if (!e)
        e = window.event;
    var sender = e.srcElement || e.target;

    if (swapItem != null)
        return;

    var slot = sender;
    if (sender.nodeName.toLowerCase() == "img")
        slot = sender.parentNode;

    if (selectedItem == null) {
        selectedItem = slot;
        slot.style.border="2px solid yellow";
    }
    else {
        swapItem = slot;
        slot.style.border="2px solid yellow";

        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                if (this.responseText.substring(0, 9).toLowerCase() != "succeeded")
                    alert(this.responseText);

                reloadScroll();
            }
        };
        xhttp.open("GET", "wp-content/plugins/rp-inventory/swap-item.php?item1=" + selectedItem.id + "&item2=" + swapItem.id, true);
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
    var icons_str = document.getElementById("rp-inventory-create-icon-all-files").value;
    icons_list = icons_str.split(":");

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
        if (icon_path.startsWith(folder)) {
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
        if (icon_path.startsWith(folder)) {
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
        borderSunkenElement.setAttribute("class", "rp-inventory-item-border-sunken");
        borderSunkenElement.setAttribute("style", "display: inline-block;");
        var borderSelectElement = document.createElement("div");
        borderSelectElement.setAttribute("class", "rp-inventory-item-slot-border-select");
        borderSelectElement.setAttribute("id", "rp-inventory-preview-icon-" + fileName);
        borderSelectElement.onclick = function() { selectClickedIcon(); };
        var imageElement = document.createElement("img");
        imageElement.setAttribute("class", "rp-inventory-item-icon");
        imageElement.setAttribute("src", "wp-content/plugins/rp-inventory/img/icons/" + folder + fileName);
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
    imageElement.src = "wp-content/plugins/rp-inventory/img/icons/" + file;

    var borderSelectElement = document.getElementById("rp-inventory-preview-icon-" + fileName);
    borderSelectElement.style.border = "2px solid yellow";

    if (prevFileName) {
        borderSelectElement = document.getElementById("rp-inventory-preview-icon-" + prevFileName);
        if (borderSelectElement)
            borderSelectElement.style.border = "2px solid black";
    }

    prevFileName = fileName;
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

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Keep scroll position
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var isFramed = top.frames.length > 0;

// call this function to reload the page
function reloadScroll() {
    var scrollAmount = document.body.scrollTop; // this is the current scroll position
    if (isFramed) {
        top.scrollValue = scrollAmount; // save it in the top frame's namespace
    }
    else {
        document.cookie = "scrollAmount=" + scrollAmount; // save it in cookie
        window.dontkillcookie = true; // just a flag used in onunload
    }
    window.location.reload();
}

window.onload = function() // when the window is reloaded, check if a scroll value has been saved
{
    var scrollAmount;
    if (isFramed) {
        scrollAmount = top.scrollValue;
    }
    else {
        var cook = document.cookie; // parse the cookie
        var pos = cook.indexOf("scrollAmount=");
        if (pos >= 0) {
            scrollAmount = parseInt(cook.substr(pos + 13));
        }
    }
    if (scrollAmount) // and reset the scrolling. et voilà.
    {
        document.body.scrollTop = scrollAmount;
    }
}

window.onunload = function() {
    // reset the cookie to zero, this way the window won't
    // scroll the next time the user accesses it
    if (!window.dontkillcookie) {
        document.cookie = "scrollAmount=0";
    }
}