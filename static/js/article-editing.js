$(document).ready(function () {
    $("#icon").keyup(function () {
        var iconStr = $(this).val();
        $("#iconPreview").empty();
        if (!iconStr)
            return;

        if (iconStr.indexOf("http") == 0)
            $("#iconPreview").append($("<img/>", { src: iconStr }));
        else
            $("#iconPreview").append($("<img/>", { src: g_staticUrl + "/images/wow/icons/large/" + iconStr.toLowerCase() + ".jpg" }));
    });
});

var previewWindow;
function initToolbox() {
    if (window.getSelection || document.getSelection || (document.selection && document.selection.createRange))
        $WH.ge("editToolbar").style.display = "";
}

function updatePreview(a) {
    if (a || $WH.ge("previewupdate").checked) {
        var editor = $WH.ge("editBox");
        clearTimeout(editor.timer);
        editor.timer = setTimeout(function () {
            var buff = Markup.toHtml($WH.ge("editBox").value, { allow: "Markup.CLASS_STAFF", preview: true });
            var qfEditor;
            if ((qfEditor = $WH.ge("changelogBox")) && qfEditor.value != "")
                buff += Markup.toHtml("[changelog open=true]" + qfEditor.value + "[/changelog]", { allow: "Markup.CLASS_STAFF", preview: true });

            if (previewWindow) {
                try {
                    previewWindow.updatePreview(buff);
                }
                catch(e) {
                    detach($WH.ge("detachLink"));
                }
            }
            else
                $WH.ge("livePreview").innerHTML = buff;
        }, 250);
    }
}

function updateQfPreview(a) {
    if (a || $WH.ge("previewupdate").checked) {
        var b = $WH.ge("qfBox");
        clearTimeout(b.timer);
        b.timer = setTimeout(function () {
            var d = Markup.toHtml($WH.ge("qfBox").value.replace(/[\r\n]+/g, ""), { allow: Markup.CLASS_STAFF, root: "ul", preview: true });
            if (previewWindow) {
                try {
                    previewWindow.updateQfPreview(d);
                }
                catch(f) {
                    detach($WH.ge("detachLink"));
                }
            }
            else {
                var c = $WH.ge("liveQfPreview");
                if (d)
                    c.innerHTML = d;

                c.parentNode.parentNode.parentNode.parentNode.style.display = (d ? "" : "none");
            }
        }, 250);
    }
}

function detach(c) {
    if (previewWindow) {
        try {
            previewWindow.close();
        }
        catch(a) {}

        previewWindow = 0;
        updatePreview(1);
        updateQfPreview(1);
        c.innerHTML = "Detach";
    }
    else {
        try {
            previewWindow = window.open("", "", "toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=1,resizable=1");
            previewWindow.document.open();
            var b = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"\n  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n<html xmlns="http://www.w3.org/1999/xhtml">\n';
            b += "<head>\n<title>Live Preview - " + document.title + '</title>\n\n<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />\n\n';
            b += '<link rel="stylesheet" type="text/css" href="<?php echo UrlHelper::GetStaticUrl(\'/css/global.css?255\'); ?>" />\n<link rel="stylesheet" type="text/css" href="<?php echo UrlHelper::GetStaticUrl(\'/css/locale_enus.css?255\'); ?>" /><!--[if IE]>\n<link rel="stylesheet" type="text/css" href="<?php echo UrlHelper::GetStaticUrl(\'/css/global_ie.css?255\'); ?>" /><![endif]--><!--[if lte IE 6]>\n<link rel="stylesheet" type="text/css" href="<?php echo UrlHelper::GetStaticUrl(\'/css/global_ie6.css?255\'); ?>" /><![endif]-->\n\n<script src="<?php echo UrlHelper::GetStaticUrl(\'/js/locale_enus.js?255\'); ?>" type="text/javascript"><\/script>\n<script src="<?php echo UrlHelper::GetStaticUrl(\'/js/global.js?255\'); ?>" type="text/javascript"><\/script>\n\n';
            b += "<script>//<![CDATA[\n\nfunction updatePreview(text)\n{\n\tvar si = $WH.g_getScroll();\n\tge('livePreview').innerHTML = text;\n\tscrollTo(si.x, si.y);\n}\n\nfunction updateQfPreview(text)\n{\n\tvar si = $WH.g_getScroll();\n\tvar qf = $WH.ge('liveQfPreview');\n\tqf.innerHTML = text;\n\tqf.parentNode.parentNode.parentNode.parentNode.style.display = (text? '': 'none');\n\tscrollTo(si.x, si.y);\n}\n\n//]]><\/script>\n\n";
            b += '</head>\n\n<body>\n\n<div id="main-contents" class="main-contents text">\n\n<table class="infobox">\n<tbody><tr><th>Quick Facts</th></tr>\n<tr><td><ul id="liveQfPreview"><li style="display: none"></li></ul></td></tr>\n</tbody></table>\n\n<div id="livePreview"></div>\n\n</div>\n\n</body>\n</html>';
            previewWindow.document.write(b);
            previewWindow.document.close();
            $WH.ge("livePreview").innerHTML = "See popup window.";
            $WH.ge("liveQfPreview").parentNode.parentNode.parentNode.parentNode.style.display = "none";
            updatePreview(1);
            updateQfPreview(1);
            c.innerHTML = "Close popup";
        }
        catch(a) {
            previewWindow = 0;
        }
    }
}

function insertMapLink() {
    var mapStr = prompt("Please enter the ID of the zone.\n\nYou may also import a full map by pasting a ?maps=... link.", "");
    if (mapStr != null) {
        var f = mapStr.match(/maps=(\d+[a-z]?)(:(\d+))?/);
        if (f != null && f[1] != null) {
            var g = "[map zone=" + f[1] + "]\n";
            if (f[3] != null) {
                var e = 0;
                for (var d = 0, b = f[3].length; d < b; d += 6) {
                    var a = (parseFloat(f[3].substring(d, d + 3)) | 0) / 10;
                    var h = (parseFloat(f[3].substring(d + 3, d + 6)) | 0) / 10;
                    if (e++ > 0) {
                        g += "\n";
                    }
                    g += "[pin x=" + a + " y=" + h + " url=?npc=]" + LANG.name + "[/pin]";
                }
            }
            g += "\n[/map]";
            g_insertTag("editBox", g, "");
        }
        else
            g_insertTag("editBox", "[map zone=" + (parseInt(mapStr) | 0) + "]\n", "\n[/map]");
    }
}

function validateForm(form, _) {
    var inp = form.elements.url;
    if (inp) {
        var err = ar_ValidateUrl(inp.value);
        if (err) {
            alert(err);
            return false;
        }
    }

    return true;
}

function leavePage(a) {
    if (window.onbeforeunload == null)
        window.onbeforeunload = function (b) { return "Are you sure you want to leave this page?"; }

    if (a)
        window.onbeforeunload = null;
}

$(document).ready(function () {
    $("#guide-form").submit(function () {
        if (!$("#title").val()) {
            alert("Please enter a title.");
            $("#title").focus();
            return false;
        }

        if (!$("#category").val()) {
            alert("You must choose a category.");
            $("#category").focus();
            return false;
        }

        if (!$("#editBox").val()) {
            alert("Your guide contains no text!");
            $("#editBox").focus();
            return false;
        }
        return true;
    });
});
