var updateSnippetAfter = 2000;
var ICON_OK = {
    url: g_staticUrl + "/images/icons/tick.png",
    title: "The snippet is up to date"
};
var ICON_UPDATING = {
    url: g_staticUrl + "/images/icons/ajax.gif",
    title: "The snippet is being updated"
};
var ICON_OUT_OF_DATE = {
    url: g_staticUrl + "/images/icons/delete.gif",
    title: "The snippet is out of date"
};
$(document).ready(function () {
    updateDescription();
    var a = null;
    $("#description").keyup(function () {
        setIcon(ICON_OUT_OF_DATE);
        if (a) {
            clearTimeout(a);
            a = null
        }
        updateDescription()
    });
    $("#editBox").keyup(function () {
        setIcon(ICON_OUT_OF_DATE);
        if (a) {
            clearTimeout(a)
        }
        a = setTimeout(function () {
            updateDescription();
            a = null
        },
        updateSnippetAfter)
    })
});
function setIcon(a) {
    var b = supportsPlaceholder() ? $("#placeholder-icon") : $("#no-placeholder-icon");
    b.html("");
    if (!a) {
        return
    }
    b.html('<img src="' + a.url + '" title="' + a.title + '" />')
}
function setInfoText(b, a) {
    $("#desc-info").css("color", a).text(b)
}
var infoTexts = [
    [0, "You have not entered any text. The description will be automatically generated for you.", "#FF8000"],
    [99, "Your description is too short! $1 to go...", "#FF0000"],
    [129, "Your description looks okay, but it could be a bit longer. Try adding $1.", "#0070DD"],
    [150, "Your description is optimal. Good job!", "#1EFF00"],
    [155, "Argh, your description is getting too long! But it's still acceptable.", "#0070DD"],
    [1000000, "Oh noez! Your description is $1 too long! Google will most likely truncate it for you :(", "#FF0000"],
];
function supportsPlaceholder() {
    var a = document.createElement("input");
    return "placeholder" in a
}
function updateDescription() {
    var d = $("#description").val().length;
    setIcon(null);
    for (var a in infoTexts) {
        a = parseInt(a);
        var c = infoTexts[a];
        if (d > c[0]) {
            continue
        }
        var b = 0;
        if (c[0] == 1000000) {
            b = d - infoTexts[a - 1][0]
        } else {
            b = infoTexts[a][0] - d + 1
        }
        if (b == 1) {
            b += " character"
        } else {
            b += " characters"
        }
        setInfoText($WH.sprintf(c[1], b), c[2]);
        break
    }
    if (d) {
        $("#snippet-row").hide();
        return
    }
    updateSnippet()
}
function updateSnippet() {
    setIcon(ICON_UPDATING);
    $.post("/edit=article", {
        "get-description": $("#editBox").val()
    },
    function (a) {
        setIcon(ICON_OK);
        if (supportsPlaceholder()) {
            $("#description").attr("placeholder", a)
        } else {
            $("#snippet-row").show();
            $("#snippet").val(a)
        }
    })
};