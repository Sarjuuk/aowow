var updatePlaceholderAfter = 2000;
$(document).ready(function () {
    updateDescription();
    var a = null;

    $("#description").keyup(function () {
        if (a) {
            clearTimeout(a);
            a = null;
        }

        updateDescription();
    });

    $("#editBox").keyup(function () {
        if (a)
            clearTimeout(a);

        a = setTimeout(function () {
            updateDescription();
            a = null;
        }, updatePlaceholderAfter);
    })
});

function setInfoText(text, color) {
    $("#desc-info").css("color", color).text(text);
}

var infoTexts = [
    [0,       LANG.descriptionlengthzero_tip,          "#FF8000"],
    [99,      LANG.descriptionlengthshort_tip,         "#FF0000"],
    [129,     LANG.descriptionlengthslightlyshort_tip, "#0070DD"],
    [150,     LANG.descriptionlengthoptimal_tip,       "#1EFF00"],
    [155,     LANG.descriptionlengthslightlylong_tip,  "#0070DD"],
    [1000000, LANG.descriptionlengthlong_tip,          "#FF0000"]
];

function supportsPlaceholder() {
    var el = document.createElement("input");
    return "placeholder" in el;
}

function updateDescription() {
    var len = $("#description").val().length;

    for (var i in infoTexts) {
        i = parseInt(i);
        var text = infoTexts[i];
        if (len > text[0])
            continue;

        var diff = 0;
        if (text[0] == 1000000)
            diff = len - infoTexts[i - 1][0];
        else
            diff = infoTexts[i][0] - len + 1;

        if (diff == 1)
            diff += " character";
        else
            diff += " characters";

        setInfoText($WH.sprintf(text[1], diff), text[2]);
        break;
    }

    if (!len)
        setInfoText('', '');

    updatePlaceholder();
}

function updatePlaceholder() {
    $.post("?get-description", {
        description: $("#editBox").val()
    },
    function (data) {
        if (supportsPlaceholder())
            $("#description").attr("placeholder", data);
        else
            $("#description").text(data);
    })
};