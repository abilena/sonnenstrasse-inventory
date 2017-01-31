
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Keep scroll position
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var isFramed = top.frames.length > 0;

// call this function to reload the page
function reloadScroll(parameter, value) {

    var scrollAmount = document.body.scrollTop; // this is the current scroll position
    if (isFramed) {
        top.scrollValue = scrollAmount; // save it in the top frame's namespace
    }
    else {
        document.cookie = "scrollAmount=" + scrollAmount; // save it in cookie
        window.dontkillcookie = true; // just a flag used in onunload
    }
    if (parameter && value) {
        document.location.search = setUriParameter(document.location, parameter, value);
    }
    else {
        window.location.reload();
    }
}

function setUriParameter(uri, key, value) {

    key = encodeURI(key);
    value = encodeURI(value);
    var kvp = document.location.search.substr(1).split('&');
    var i = kvp.length; var x; while (i--) {
        x = kvp[i].split('=');

        if (x[0] == key) {
            x[1] = value;
            kvp[i] = x.join('=');
            break;
        }
    }

    if (i < 0) { kvp[kvp.length] = [key, value].join('='); }

    return kvp.join('&');
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

