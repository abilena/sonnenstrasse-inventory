
var selectedItem = null;
var swapItem = null;

function rp_inventory_create_item()
{
    alert("rp_inventory_create_item");

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            alert(this.responseText);
        }
    };
    xhttp.open("GET", "wp-content/plugins/rp-inventory/create-item.php?q=test", true);
    xhttp.send();
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