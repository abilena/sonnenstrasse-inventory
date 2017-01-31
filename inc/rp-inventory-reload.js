
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

