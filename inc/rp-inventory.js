
var selectedItem = null;
var swapItem = null;
var isCreating = false;

function rp_inventory_create_item_popup()
{
    var popup = document.getElementById("rp-inventory-popup-create");
    popup.style.visibility = "visible";
}

function rp_inventory_create_item_close()
{
    var popup = document.getElementById("rp-inventory-popup-create");
    popup.style.visibility = "collapse";

    reloadScroll();
}

function rp_inventory_create_item()
{
    if (isCreating)
    {
        alert("Item creation still pending");
        return;
    }

    var name = document.getElementById("rp-inventory-create-name").value;
    var icon = document.getElementById("rp-inventory-create-icon").value;
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