
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Partys
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

var isLoading = false;
var isEditing = false;
var isCreating = false;

function getSelectedParty(doShowPartyDetails) {

    var partySelector = document.getElementById("rp-inventory-admin-select-party");
    if (partySelector.selectedIndex < 0)
        return;

    var selectedParty = partySelector.options[partySelector.selectedIndex];

    document.getElementById("rp-inventory-admin-table-party-add-shading").style.display = "block";
    isLoading = true;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (doShowPartyDetails) {
                var party = JSON.parse(this.responseText);
                setParty(party);
                showPartyDetails(false);
            }
            else {
                reloadScroll("page=rp-inventory&party_id=" + selectedParty.value);
            }
        }
    };
    xhttp.open("GET", "../wp-content/plugins/rp-inventory/get-party.php?id=" + selectedParty.value, true);
    xhttp.send();
}

function setParty(party) {

    if (party == null) {
        document.getElementById("rp-inventory-admin-table-party-add-headline").textContent = "Neue Gruppe";
        document.getElementById("rp-inventory-admin-create-party-new").style.display = "inline-block";
        document.getElementById("rp-inventory-admin-create-party-edit").style.display = "none";
        document.getElementById("rp-inventory-admin-create-party-name").value = "Gruppe";
        gotoCurrentDate();
    }
    else {
        document.getElementById("rp-inventory-admin-table-party-add-headline").textContent = "Gruppe bearbeiten";
        document.getElementById("rp-inventory-admin-create-party-new").style.display = "none";
        document.getElementById("rp-inventory-admin-create-party-edit").style.display = "inline-block";
        document.getElementById("rp-inventory-admin-create-party-name").value = party.name;
        document.getElementById("rp-inventory-admin-create-party-current-year").value = party.current_year;
        document.getElementById("rp-inventory-admin-create-party-current-month").value = party.current_month;
        document.getElementById("rp-inventory-admin-create-party-current-day").value = party.current_day;
    }

    isLoading = false;
    document.getElementById("rp-inventory-admin-table-party-add-shading").style.display = "none";
}

function hidePartyDetails() {

    document.getElementById("rp-inventory-admin-table-party-add").style.display = "none";
    isCreating = false;
    isEditing = false;
}

function showPartyDetails(empty) {
    document.getElementById("rp-inventory-admin-table-party-add").style.display = "block";
    isCreating = empty;
    isEditing = !empty;
}

function openPartyDetails(empty) {

    if ((empty && isCreating) || (!empty && isEditing)) {
        hidePartyDetails();
    } 
    else if (empty) {
        // show empty party data
        setParty(null);
        showPartyDetails(empty);
    }
    else {
        // load and show party data
        isCreating = false;
        getSelectedParty(true);
    }
}

function updateSelectedMonth() {

    var current_month = document.getElementById("rp-inventory-admin-create-party-current-month").value;
    var hideDays = (current_month == 13);
    var current_day_element = document.getElementById("rp-inventory-admin-create-party-current-day");
    for (var index = 0; index < 30; index++) {
        var day_option = current_day_element.options[index];
        day_option.style.display = (hideDays && (index >= 5)) ? "none" : "block";
    }
    if (hideDays && (current_day_element.selectedIndex >= 5)) {
        current_day_element.selectedIndex = 0;
    }
}

function gotoCurrentDate() {
    
    var today = new Date();
    var newYear = new Date("07/01/" + (today.getFullYear() + (today.getMonth() < 6 ? -1 : 0)));
    var dayDiff = Math.min(364, Math.floor((today.getTime() - newYear.getTime()) / (1000 * 3600 * 24)));
    var dsaYear = today.getFullYear() - 977 + (today.getMonth() < 6 ? -1 : 0);
    var dsaMonth = Math.floor(dayDiff / 30) + 1;
    var dsaDay = dayDiff - ((dsaMonth - 1) * 30) + 1;

    document.getElementById("rp-inventory-admin-create-party-current-year").value = dsaYear;
    document.getElementById("rp-inventory-admin-create-party-current-month").value = dsaMonth;
    document.getElementById("rp-inventory-admin-create-party-current-day").value = dsaDay;
}

function addNewParty() {

    var name = document.getElementById("rp-inventory-admin-create-party-name").value;
    var current_year = document.getElementById("rp-inventory-admin-create-party-current-year").value;
    var current_month = document.getElementById("rp-inventory-admin-create-party-current-month").value;
    var current_day = document.getElementById("rp-inventory-admin-create-party-current-day").value;

    name = encodeURIComponent(name);
    current_year = encodeURIComponent(current_year);
    current_month = encodeURIComponent(current_month);
    current_day = encodeURIComponent(current_day);

    var parameters = "name=" + name;
    parameters += "&current_year=" + current_year;
    parameters += "&current_month=" + current_month;
    parameters += "&current_day=" + current_day;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.substring(0, 9).toLowerCase() != "")
                alert(this.responseText);

            reloadScroll();
        }
    };

    xhttp.open("POST", "../wp-content/plugins/rp-inventory/create-party.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("Content-length", parameters.length);
    xhttp.send(parameters);
}

function editParty() {

    var partySelector = document.getElementById("rp-inventory-admin-select-party");
    if (partySelector.selectedIndex < 0)
        return;

    var selectedParty = partySelector.options[partySelector.selectedIndex];

    var name = document.getElementById("rp-inventory-admin-create-party-name").value;
    var current_year = document.getElementById("rp-inventory-admin-create-party-current-year").value;
    var current_month = document.getElementById("rp-inventory-admin-create-party-current-month").value;
    var current_day = document.getElementById("rp-inventory-admin-create-party-current-day").value;

    name = encodeURIComponent(name);
    current_year = encodeURIComponent(current_year);
    current_month = encodeURIComponent(current_month);
    current_day = encodeURIComponent(current_day);

    var parameters = "name=" + name;
    parameters += "&current_year=" + current_year;
    parameters += "&current_month=" + current_month;
    parameters += "&current_day=" + current_day;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.substring(0, 9).toLowerCase() != "")
                alert(this.responseText);

            reloadScroll();
        }
    };

    xhttp.open("POST", "../wp-content/plugins/rp-inventory/edit-party.php?id=" + selectedParty.value, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("Content-length", parameters.length);
    xhttp.send(parameters);
}

function deleteParty() {

    var partySelector = document.getElementById("rp-inventory-admin-select-party");
    if (partySelector.selectedIndex < 0)
        return;

    var selectedParty = partySelector.options[partySelector.selectedIndex];
    if (!confirm("Sind sie sicher, dass sie die Abenteuergruppe '" + selectedParty.text + "' inkl. aller darin enthaltenen Helden löschen wollen?"))
        return;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.substring(0, 9).toLowerCase() != "succeeded")
                alert(this.responseText);

            reloadScroll();
        }
    };
    xhttp.open("GET", "../wp-content/plugins/rp-inventory/delete-party.php?id=" + selectedParty.value, true);
    xhttp.send();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Heroes
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function createNewHero() {
    
    var partySelector = document.getElementById("rp-inventory-admin-select-party");
    if (partySelector.selectedIndex < 0)
        return;

    var selectedParty = partySelector.options[partySelector.selectedIndex];

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.substring(0, 9).toLowerCase() != "")
                alert(this.responseText);

            reloadScroll();
        }
    };
    xhttp.open("GET", "../wp-content/plugins/rp-inventory/create-hero.php?party_id=" + selectedParty.value, true);
    xhttp.send();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Properties
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function saveProperty(hero_id, property_type, property_id) {
    var gp = document.getElementById("rp-inventory-admin-table-property-gp").value;
    var tgp = document.getElementById("rp-inventory-admin-table-property-tgp").value;
    var ap = document.getElementById("rp-inventory-admin-table-property-ap").value;
    var name = document.getElementById("rp-inventory-admin-table-property-name").value;
    var variant = "";
    var info = "";
    var value = "";

    hero_id = encodeURIComponent(hero_id);
    property_type = encodeURIComponent(property_type);
    property_id = encodeURIComponent(property_id);
    name = encodeURIComponent(name);
    variant = encodeURIComponent(variant);
    info = encodeURIComponent(info);
    value = encodeURIComponent(value);
    gp = encodeURIComponent(gp);
    tgp = encodeURIComponent(tgp);
    ap = encodeURIComponent(ap);

    var parameters = "hero=" + hero_id;
    parameters += "&type=" + property_type;
    parameters += "&name=" + name;
    parameters += "&variant=" + variant;
    parameters += "&info=" + info;
    parameters += "&value=" + value;
    parameters += "&gp=" + gp;
    parameters += "&tgp=" + tgp;
    parameters += "&ap=" + ap;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.substring(0, 9).toLowerCase() != "")
                alert(this.responseText);

            var query = document.location.search;
            if (query.indexOf("property_edit") < 0) {
                reloadScroll();
            }
            else {
                query = query.replace(new RegExp("\\&property_edit\\=[0-9]+", "ig"), "");
                reloadScroll(query);
            }
        }
    };

    xhttp.open("POST", "../wp-content/plugins/rp-inventory/edit-property.php?property_id=" + property_id, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("Content-length", parameters.length);
    xhttp.send(parameters);
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Details
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function saveDetail(hero_id, detail_type) {
    var detail_value = document.getElementById("rp-inventory-admin-table-detail").value;

    hero_id = encodeURIComponent(hero_id);
    detail_type = encodeURIComponent(detail_type);
    detail_value = encodeURIComponent(detail_value);

    var parameters = "hero=" + hero_id;
    parameters += "&type=" + detail_type;
    parameters += "&value=" + detail_value;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText.substring(0, 9).toLowerCase() != "succeeded")
                alert(this.responseText);

            var query = document.location.search;
            if (query.indexOf("detail") < 0) {
                reloadScroll();
            }
            else {
                query = query.replace(new RegExp("\\&detail\\=[A-Za-z_]+", "ig"), "");
                query = query.replace(new RegExp("\\&detail_label\\=[A-Za-z_]+", "ig"), "");
                reloadScroll(query);
            }
        }
    };

    xhttp.open("POST", "../wp-content/plugins/rp-inventory/edit-detail.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.setRequestHeader("Content-length", parameters.length);
    xhttp.send(parameters);    
}