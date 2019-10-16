var ss_managedRow       = null;
var ss_getAll           = false;                            // never changed (maybe with ?admin=screenshots&amp;all)
var ssm_ViewedRow       = null;
var ssm_screenshotData  = [];
var ssm_screenshotPages = [];
var ssm_numPagesFound   = 0;
var ssm_numPages        = 0;                                // never accessed
var ssm_numPending      = 0;
var ssm_statuses        = {
    0  : 'Pending',
    999: 'Deleted',
    100: 'Approved',
    105: 'Sticky'
};

function makePipe() {
    var sp = $WH.ce('span');
    $WH.ae(sp, $WH.ct(' '));

    var sm = $WH.ce('small');
    sm.className = 'q0';
    $WH.ae(sm, $WH.ct('|'));

    $WH.ae(sp, sm);
    $WH.ae(sp, $WH.ct(' '));

    return sp;
}

function ss_OnResize() {
    var _ = Math.max(100, Math.min($WH.g_getWindowSize().h - 50, 700));

    $WH.ge('menu-container').style.height = $WH.ge('pages-container').style.height = _ + 'px';
    $WH.ge('data-container').style.height = _ + 'px';
}

$WH.aE(window, 'resize', ss_OnResize);

function ss_Refresh(openNext, type, typeId) {
    new Ajax('?admin=screenshots&action=list' + (ss_getAll ? '&all' : ''), {
        method: 'get',
        onSuccess: function (xhr) {
            eval(xhr.responseText);

            if (ssm_screenshotPages.length > 0) {
                $WH.ge('show-all-pages').innerHTML = ' &ndash; <a href="?admin=screenshots&amp;all">Show All</a> (' + ssm_numPagesFound + ')';

                ssm_UpdatePages();

                if (openNext) {
                    ss_Manage($WH.ge('pages-container').firstChild.firstChild, ssm_screenshotPages[0].type, ssm_screenshotPages[0].typeId, true);
                }
                else if (type && typeId) {
                    ss_Manage(null, type, typeId, true);
                }
            }
            else {
                $WH.ee($WH.ge('show-all-pages'));
                $WH.ge('pages-container').innerHTML = 'NO SCREENZSHOT NEEDS 2 BE APPRVED NOW KTHX. :)';
                if (type && typeId) {
                    ss_Manage(null, type, typeId, true);
                }
            }
        }
    });
}

function ss_Manage(_this, type, typeId, openNext) {
    new Ajax('?admin=screenshots&action=manage&type=' + type + '&typeid=' + typeId, {
        method: 'get',
        onSuccess: function (xhr) {

            eval(xhr.responseText);
            ssm_numPending = 0;

            for (var i in ssm_screenshotData) {
                if (ssm_screenshotData[i].pending) {
                    ssm_numPending++;
                }
            }

            var nRows = ssm_screenshotData.length;
            $WH.ge('screenshotTotal').innerHTML = nRows + ' total' + (nRows == 100 ? ' (limit reached)' : '');

            ssm_UpdateList(openNext);
            ssm_UpdateMassLinks();

            if (ss_managedRow != null) {
                ss_ColorizeRow('transparent');
            }

            ss_managedRow = _this;

            if (ss_managedRow != null) {
                ss_ColorizeRow('#282828');
            }
        }
    });
}

function ss_ManageUser() {
    var username = $WH.ge('usermanage');
    username.value = $WH.trim(username.value);

    if (username.value.length < 4) {
        alert('Username must be at least 4 characters long.');
        username.focus();

        return false;
    }

    if (username.value.match(/[^a-z0-9]/i) != null) {
        alert('Username can only contain letters and numbers.');
        username.focus();

        return false;
    }

    new Ajax('?admin=screenshots&action=manage&user=' + username.value, {
        method: 'get',
        onSuccess: function (xhr) {
            eval(xhr.responseText);
            var nRows = ssm_screenshotData.length;
            $WH.ge('screenshotTotal').innerHTML = nRows + ' total' + (nRows == 100 ? ' (limit reached)' : '');
            ssm_UpdateList();
            ssm_UpdateMassLinks();
            if (ss_managedRow != null) {
                ss_ColorizeRow('transparent');
            }
        }
    });

    return true;
}

function ss_ColorizeRow(color) {
    for (var i = 0; i < ss_managedRow.childNodes.length; ++i) {
        ss_managedRow.childNodes[i].style.backgroundColor = color;
    }
}

function ssm_GetScreenshot(id) {
    for (var i in ssm_screenshotData) {
        if (ssm_screenshotData[i].id == id) {
            return ssm_screenshotData[i];
        }
    }

    return null;
}

function ssm_View(row, id) {
    if (ssm_ViewedRow != null) {
        ssm_ColorizeRow('transparent');
    }

    ssm_ViewedRow = row;
    ssm_ColorizeRow('#282828');

    var screenshot = ssm_GetScreenshot(id);
    if (screenshot != null) {
        ScreenshotManager.show(screenshot);
    }
}

function ssm_ColorizeRow(color) {
    for (var i = 0; i < ssm_ViewedRow.childNodes.length; ++i) {
        ssm_ViewedRow.childNodes[i].style.backgroundColor = color;
    }
}

function ssm_ConfirmMassApprove() {
    ajaxAnchor(this);                                       // aowow custom - there has to be something in place or we are manually using a script for ajax

    return false;
    // return true;
}

function ssm_ConfirmMassDelete() {
    if (confirm('Delete selected screenshot(s)?'))          // aowow custom - see above
        ajaxAnchor(this);

    return false;
    // return confirm('Delete selected screenshot(s)?');
}

function ssm_ConfirmMassSticky() {
    if (confirm('Sticky selected screenshot(s)?'))          // aowow custom - see above
        ajaxAnchor(this);

    return false;
    // return confirm('Sticky selected screenshot(s)?');
}

function ssm_UpdatePages(UNUSED) {
    var pc = $WH.ge('pages-container');
    $WH.ee(pc);

    var tbl = $WH.ce('table');
    tbl.className = 'grid';
    tbl.style.width = '400px';

    var tr = $WH.ce('tr');

    var th = $WH.ce('th');
    $WH.ae(th, $WH.ct('Page'));
    $WH.ae(tr, th);

    th = $WH.ce('th');
    $WH.ae(th, $WH.ct('Submitted'));
    $WH.ae(tr, th);

    th = $WH.ce('th');
    th.align = 'right';
    $WH.ae(th, $WH.ct('#'));
    $WH.ae(tr, th);

    $WH.ae(tbl, tr);

    var now = new Date();
    for (var i in ssm_screenshotPages) {
        var ssPage = ssm_screenshotPages[i];
        tr = $WH.ce('tr');
        tr.onclick = ss_Manage.bind(tr, tr, ssPage.type, ssPage.typeId, true, i);

        var td = $WH.ce('td');
        var a = $WH.ce('a');
        a.href = '?' + g_types[ssPage.type] + '=' + ssPage.typeId;
        a.target = '_blank';
        $WH.ae(a, $WH.ct(ssPage.name));
        $WH.ae(td, a);
        $WH.ae(tr, td);

        td = $WH.ce('td');
        var elapsed = new Date(ssPage.date);
        $WH.ae(td, $WH.ct(g_formatTimeElapsed((now.getTime() - elapsed.getTime()) / 1000) + ' ago'));
        $WH.ae(tr, td);

        td = $WH.ce('td');
        td.align = 'right';
        $WH.ae(td, $WH.ct(ssPage.count));
        $WH.ae(tr, td);

        $WH.ae(tbl, tr);
    }

    $WH.ae(pc, tbl);
}

function ssm_UpdateList(openNext) {
    var tsl   = $WH.ge('theScreenshotsList');
    var tBody = false;
    var i     = 1;

    while (tsl.childNodes.length > i) {
        if (tsl.childNodes[i].nodeName == 'TR' && tBody) {
            $WH.de(tsl.childNodes[i]);
        }
        else if (tsl.childNodes[i].nodeName == 'TR') {
            tBody = true;
        }
        else {
            i++;
        }
    }

    var now = new Date();
    var ssId = 0;
    for (var i in ssm_screenshotData) {
        var screenshot = ssm_screenshotData[i];
        var tr = $WH.ce('tr');
        if (ssId == 0 && screenshot.pending) {
            ssId = screenshot.id;
            tr.id = 'highlightedRow';
        }

        var td = $WH.ce('td');
        td.align = 'center';

        var a = $WH.ce('a');
        a.href = g_staticUrl + '/uploads/screenshots/' + (screenshot.status != 999 && !screenshot.pending ? 'normal' : 'pending') + '/' + screenshot.id + '.jpg';
        a.target = '_blank';
        a.onclick = function (id, e) {
            $WH.sp(e);
            (ssm_View.bind(null, this, id))();
            return false;
        }.bind(tr, screenshot.id);

        var img = $WH.ce('img');
        img.src = g_staticUrl + '/uploads/screenshots/' + (screenshot.status != 999 && !screenshot.pending ? 'thumb' : 'pending') + '/' + screenshot.id + '.jpg';
        img.height = 50;
        $WH.ae(a, img);

        $WH.ae(td, a);
        $WH.ae(tr, td);

        td = $WH.ce('td');
        if (screenshot.status != 999 && !screenshot.pending) {
            var a = $WH.ce('a');
            a.href = '?' + g_types[screenshot.type] + '=' + screenshot.typeId + '#screenshots:id=' + screenshot.id;
            a.target = '_blank';
            a.onclick = function (e) {
                $WH.sp(e);
            };
            $WH.ae(a, $WH.ct(screenshot.id));
            $WH.ae(td, a);
        }
        else {
            $WH.ae(td, $WH.ct(screenshot.id));
        }
        $WH.ae(tr, td);

        td = $WH.ce('td');
        td.id = 'alt-' + screenshot.id;

        var sp = $WH.ce('span');
        sp.style.paddingRight = '8px';
        if (screenshot.caption) {
            var sp2 = $WH.ce('span');
            sp2.className = 'q2';
            var b = $WH.ce('b');
            $WH.ae(b, $WH.ct(screenshot.caption));
            $WH.ae(sp2, b);
            $WH.ae(sp, sp2);
        }
        else {
            var it = $WH.ce('i');
            it.className = 'q0';
            $WH.ae(it, $WH.ct('NULL'));
            $WH.ae(sp, it);
        }
        $WH.ae(td, sp);

        sp = $WH.ce('span');
        sp.style.whiteSpace = 'nowrap';

        var a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = function (id, e) {
            $WH.sp(e);
            (ssm_ShowEdit.bind(this, id))()
        }.bind(a, screenshot);
        $WH.ae(a, $WH.ct('Edit'));
        $WH.ae(sp, a);
        $WH.ae(sp, makePipe());

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = function (id, e) {
            $WH.sp(e);
            (ssm_Clear.bind(this, id))()
        }.bind(a, screenshot);
        $WH.ae(a, $WH.ct('Clear'));
        $WH.ae(sp, a);
        $WH.ae(td, sp);
        $WH.ae(tr, td);

        td = $WH.ce('td');
        var elapsed = new Date(screenshot.date);
        $WH.ae(td, $WH.ct(g_formatTimeElapsed((now.getTime() - elapsed.getTime()) / 1000) + ' ago'));
        $WH.ae(tr, td);

        td = $WH.ce('td');
        a = $WH.ce('a');
        a.href = '?user=' + screenshot.user;
        a.target = '_blank';
        a.onclick = function (e) {
            $WH.sp(e);
        };
        $WH.ae(a, $WH.ct(screenshot.user));
        $WH.ae(td, a);
        $WH.ae(tr, td);

        td = $WH.ce('td');
        $WH.ae(td, $WH.ct(ssm_statuses[screenshot.status]));
        $WH.ae(tr, td);

        td = $WH.ce('td');
        var cb = $WH.ce('input');
        cb.type = 'checkbox';
        cb.value = screenshot.id;
        cb.onclick = function (e) {
            $WH.sp(e);
            (ssm_UpdateMassLinks.bind(this))();
        }.bind(cb);
        $WH.ae(td, cb);
        $WH.ae(td, $WH.ct(' '));

        if (screenshot.status != 999) {
            tr.onclick = function (id) {
                ssm_View(this, id);
                return false;
            }.bind(tr, screenshot.id);

            if (screenshot.id == ssId && openNext) {
                ssm_View(tr, screenshot.id);
            }

            if (screenshot.pending) {
                a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = function (e) {
                    $WH.sp(e);
                    (ssm_Approve.bind(this, false))()
                }.bind(screenshot);
                $WH.ae(a, $WH.ct('Approve'));
                $WH.ae(td, a);
            }
            else {
                $WH.ae(td, $WH.ct('Approve'));
            }

            $WH.ae(td, makePipe());

            if (screenshot.status != 105) {
                a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = function (e) {
                    $WH.sp(e);
                    (ssm_Sticky.bind(this, false))();
                }.bind(screenshot);
                $WH.ae(a, $WH.ct('Make sticky'));
                $WH.ae(td, a);
            }
            else {
                $WH.ae(td, $WH.ct('Make sticky'));
            }
            $WH.ae(td, makePipe());

            a = $WH.ce('a');
            a.href = 'javascript:;';
            a.onclick = function (e) {
                $WH.sp(e);
                (ssm_Delete.bind(this, false))();
            }.bind(screenshot);
            $WH.ae(a, $WH.ct('Delete'));
            $WH.ae(td, a);
            $WH.ae(td, makePipe());

            a = $WH.ce('a');
            a.href = 'javascript:;';
            a.onclick = function (e) {
                $WH.sp(e);
                var id = prompt('Enter the ID to move this screenshot to:');
                (ssm_Relocate.bind(this, id))();
            }.bind(screenshot);
            $WH.ae(a, $WH.ct('Relocate'));
            $WH.ae(td, a);
        }

        $WH.ae(tr, td);
        $WH.ae(tsl, tr);
    }
}

function ssm_UpdateMassLinks() {
    var buff = '';
    var i = 0;
    var tSL = $WH.ge('theScreenshotsList');
    var inp = $WH.gE(tSL, 'input');

    $WH.array_walk(inp, function (x) {
        if (x.checked) {
            buff += x.value + ',';
            ++i;
        }
    });

    buff = $WH.rtrim(buff, ',');

    var selCnt = $WH.ge('withselected');
    if (i > 0) {
        selCnt.style.display = '';
        $WH.gE(selCnt, 'b')[0].firstChild.nodeValue = '(' + i + ')';

        var c = $WH.ge('massapprove');
        var b = $WH.ge('massdelete');
        var a = $WH.ge('masssticky');

        c.href    = '?admin=screenshots&action=approve&id=' + buff;
        c.onclick = ssm_ConfirmMassApprove;

        b.href    = '?admin=screenshots&action=delete&id=' + buff;
        b.onclick = ssm_ConfirmMassDelete;

        a.href    = '?admin=screenshots&action=sticky&id=' + buff;
        a.onclick = ssm_ConfirmMassSticky;
    }
    else {
        selCnt.style.display = 'none';
    }
}

function ssm_MassSelect(action) {
    var tSL = $WH.ge('theScreenshotsList');
    var inp = $WH.gE(tSL, 'input');

    switch (parseInt(action)) {
        case 1:
            $WH.array_walk(inp, function (x) { x.checked = true });
            break;
        case 0:
            $WH.array_walk(inp, function (x) { x.checked = false });
            break;
        case -1:
            $WH.array_walk(inp, function (x) { x.checked = !x.checked });
            break;
        case 2:
            $WH.array_walk(inp, function (x) { x.checked = ssm_GetScreenshot(x.value).status == 0 });
            break;
        case 5:
            $WH.array_walk(inp, function (x) { x.checked = ssm_GetScreenshot(x.value).unique == 1 && ssm_GetScreenshot(x.value).status == 0 });
            break;
        case 3:
            $WH.array_walk(inp, function (x) { x.checked = ssm_GetScreenshot(x.value).status == 100 });
            break;
        case 4:
            $WH.array_walk(inp, function (x) { x.checked = ssm_GetScreenshot(x.value).status == 105 });
            break;
        default:
            return;
    }

    ssm_UpdateMassLinks();
}

function ssm_ShowEdit(screenshot, isAlt) {
    var node;

    if (isAlt) {
        node = $WH.ge('alt2-' + screenshot.id)
    }
    else {
        node = $WH.ge('alt-' + screenshot.id)
    }

    var sp = $WH.gE(node, 'span')[0];
    var div = $WH.ce('div');
    div.style.whiteSpace = 'nowrap';
    var iCaption = $WH.ce('input');
    iCaption.type = 'text';
    iCaption.value = screenshot.caption;
    iCaption.maxLength = 200;
    iCaption.size = 35;
    iCaption.onclick = function (e) { $WH.sp(e); }          // aowow - custom to inhibit screenshot popup, when clicking into input element
    div.appendChild(iCaption);

    var btn = $WH.ce('input');
    btn.type = 'button';
    btn.value = 'Update';
    btn.onclick = function (i, j, k) {
        if (!j) {
            $WH.sp(k);
        }

        (ssm_Edit.bind(this, i, j))();
    }.bind(btn, screenshot, isAlt);
    div.appendChild(btn);

    var c = $WH.ce('span');
    c.appendChild($WH.ct(' '));
    div.appendChild(c);

    btn = $WH.ce('input');
    btn.type = 'button';
    btn.value = 'Cancel';
    btn.onclick = function (i, j, k) {
        if (!j) {
            $WH.sp(k);
        }

        (ssm_CancelEdit.bind(this, i, j))();
    }.bind(btn, screenshot, isAlt);
    div.appendChild(btn);

    sp.style.display = 'none';
    sp.nextSibling.style.display = 'none';
    node.insertBefore(div, sp);

    iCaption.focus()
}

function ssm_CancelEdit(screenshot, isAlt) {
    var node;

    if (isAlt) {
        node = $WH.ge('alt2-' + screenshot.id);
    }
    else {
        node = $WH.ge('alt-' + screenshot.id);
    }

    var sp = $WH.gE(node, 'span')[1];
    sp.style.display = '';
    sp.nextSibling.style.display = '';

    node.removeChild(node.firstChild);
}

function ssm_Edit(screenshot, isAlt) {
    var node;

    if (isAlt) {
        node = $WH.ge('alt2-' + screenshot.id);
    }
    else {
        node = $WH.ge('alt-' + screenshot.id);
    }

    var desc = node.firstChild.childNodes;
    if (desc[0].value == screenshot.caption) {
        ssm_CancelEdit(screenshot, isAlt);
        return
    }
    screenshot.caption = desc[0].value;

    ssm_CancelEdit(screenshot, isAlt);

    node = node.firstChild;
    while (node.childNodes.length > 0) {
        node.removeChild(node.firstChild);
    }
    $WH.ae(node, $WH.ct(screenshot.caption));

    new Ajax('?admin=screenshots&action=editalt&id=' + screenshot.id, {
        method: 'POST',
        params: 'alt=' + $WH.urlencode(screenshot.caption)
    })
}

function ssm_Clear(screenshot, isAlt) {
    var node;

    if (isAlt) {
        node = $WH.ge('alt2-' + screenshot.id);
    }
    else {
        node = $WH.ge('alt-' + screenshot.id);
    }

    var sp = $WH.gE(node, 'span');
    var a  = $WH.gE(sp[1], 'a');
    sp = sp[0];

    if (screenshot.caption == '') {
        return;
    }

    screenshot.caption = '';
    sp.innerHTML = '<i class="q0">NULL</i>';

    new Ajax('?admin=screenshots&action=editalt&id=' + screenshot.id, {
        method: 'POST',
        params: 'alt=' + $WH.urlencode('')
    })
}

function ssm_Approve(openNext) {
    var _self = this;
    new Ajax('?admin=screenshots&action=approve&id=' + _self.id, {
        method: 'get',
        onSuccess: function (x) {
            Lightbox.hide();
            if (ssm_numPending == 1 && _self.pending) {
                ss_Refresh(true);
            }
            else {
                ss_Refresh();
                ss_Manage(ss_managedRow, _self.type, _self.typeId, openNext, 0);
            }
        }
    })
}

function ssm_Sticky(openNext) {
    var _self = this;
    new Ajax('?admin=screenshots&action=sticky&id=' + _self.id, {
        method: 'get',
        onSuccess: function (x) {
            Lightbox.hide();
            if (ssm_numPending == 1 && _self.pending) {
                ss_Refresh(true);
            }
            else {
                ss_Refresh();
                ss_Manage(ss_managedRow, _self.type, _self.typeId, openNext, 0);
            }
        }
    })
}

function ssm_Delete(openNext) {
    var _self = this;
    new Ajax('?admin=screenshots&action=delete&id=' + _self.id, {
        method: 'get',
        onSuccess: function (x) {
            Lightbox.hide();
            if (ssm_numPending == 1 && _self.pending) {
                ss_Refresh(true);
            }
            else {
                ss_Refresh();
                ss_Manage(ss_managedRow, _self.type, _self.typeId, openNext, 0);
            }
        }
    });
}

function ssm_Relocate(typeId) {
    var _self = this;
    new Ajax('?admin=screenshots&action=relocate&id=' + _self.id + '&typeid=' + typeId, {
        method: 'get',
        onSuccess: function (x) {
            ss_Refresh();
            ss_Manage(ss_managedRow, _self.type, typeId);
        }
    });
}

var ScreenshotManager = new function () {
    var
        screenshot,
        pos,
        imgWidth,
        imgHeight,
        scale,
        desiredScale,
        container,
        screen,
        imgDiv,
        aPrev,
        aNext,
        aCover,
        aOriginal,
        divFrom,
        divCaption,
        __div,
        h2Name,
        u,
        aEdit,
        aClear,
        spApprove,
        aApprove,
        aMakeSticky,
        aDelete,
        loadingImage,
        lightboxComponents;

    function computeDimensions(captionExtraHeight) {
        var availHeight = Math.max(50, Math.min(618, $WH.g_getWindowSize().h - 122 - captionExtraHeight));

        if (screenshot.id) {
            desiredScale = Math.min(772 / screenshot.width, 618 / screenshot.height);
            scale = Math.min(772 / screenshot.width, availHeight / screenshot.height);
        }
        else {
            desiredScale = scale = 1;
        }

        if (desiredScale > 1) {
            desiredScale = 1;
        }

        if (scale > 1) {
            scale = 1;
        }

        imgWidth  = Math.round(scale * screenshot.width);
        imgHeight = Math.round(scale * screenshot.height);
        var lbWidth = Math.max(480, imgWidth);

        Lightbox.setSize(lbWidth + 20, imgHeight + 116 + captionExtraHeight);

        if (captionExtraHeight) {
            imgDiv.firstChild.width  = imgWidth;
            imgDiv.firstChild.height = imgHeight;
        }
    }

    function render(resizing) {
        if (resizing && (scale == desiredScale) && $WH.g_getWindowSize().h > container.offsetHeight) {
            return;
        }

        container.style.visibility = 'hidden';

        var
            resized = (screenshot.width > 772 || screenshot.height > 618);

        computeDimensions(0);

        var url = g_staticUrl + '/uploads/screenshots/' + (screenshot.pending ? 'pending' : 'normal') + '/' + screenshot.id + '.jpg';

        var html = '<img src="' + url + '" width="' + imgWidth + '" height="' + imgHeight + '"';
        html += '>';

        imgDiv.innerHTML = html;

        if (!resizing) {
            aOriginal.href = g_staticUrl + '/uploads/screenshots/' + (screenshot.pending ? 'pending' : 'normal') + '/' + screenshot.id + '.jpg';
            var hasFrom1 = screenshot.date && screenshot.user;
            if (hasFrom1) {
                var
                    postedOn = new Date(screenshot.date),
                    elapsed = (g_serverTime - postedOn) / 1000;

                var a = divFrom.firstChild.childNodes[1];
                a.href = '?user=' + screenshot.user;
                a.innerHTML = screenshot.user;

                var s = divFrom.firstChild.childNodes[3];

                $WH.ee(s);
                g_formatDate(s, elapsed, postedOn);

                divFrom.firstChild.style.display = '';
            }
            else {
                divFrom.firstChild.style.display = 'none';
            }

            divFrom.style.display = (hasFrom1 ? '' : 'none');

            var hasCaption = (screenshot.caption != null && screenshot.caption.length);
            if (hasCaption) {
                var html = '';
                if (hasCaption) {
                    html += '<span class="screenshotviewer-caption"><b>' + Markup.toHtml(screenshot.caption, { mode: Markup.MODE_SIGNATURE }) + '</b></span>';
                }

                divCaption.innerHTML = html;
            }
            else {
                divCaption.innerHTML = '<i class="q0">NULL</i>';
            }

            __div.id = 'alt2-' + screenshot.id;

            aEdit.onclick  = ssm_ShowEdit.bind(aEdit, screenshot, true);
            aClear.onclick = ssm_Clear.bind(aClear, screenshot, true);

            if (screenshot.next !== undefined) {
                aPrev.style.display  = aNext.style.display = '';
                aCover.style.display = 'none';
            }
            else {
                aPrev.style.display  = aNext.style.display = 'none';
                aCover.style.display = '';
            }
        }

        Lightbox.reveal();

        if (divCaption.offsetHeight > 18) {
            computeDimensions(divCaption.offsetHeight - 18);
        }
        container.style.visibility = 'visible';
    }

    function nextScreenshot() {
        if (screenshot.next !== undefined) {
            screenshot = ssm_screenshotData[screenshot.next];
        }

        onRender();
    }

    function prevScreenshot() {
        if (screenshot.prev !== undefined) {
            screenshot = ssm_screenshotData[screenshot.prev];
        }

        onRender();
    }

    function onResize() {
        render(1);
    }

    function onHide() {
        aApprove.onclick = aMakeSticky.onclick = aDelete.onclick = null;
        cancelImageLoading();
    }

    function onShow(dest, first, opt) {
        screenshot = opt;
        container  = dest;

        if (first) {
            dest.className = 'screenshotviewer';

            screen = $WH.ce('div');

            screen.className = 'screenshotviewer-screen';

            aPrev = $WH.ce('a');
            aNext = $WH.ce('a');
            aPrev.className = 'screenshotviewer-prev';
            aNext.className = 'screenshotviewer-next';
            aPrev.href = 'javascript:;';
            aNext.href = 'javascript:;';

            var foo = $WH.ce('span');
            $WH.ae(foo, $WH.ce('b'));
            $WH.ae(aPrev, foo);
            var foo = $WH.ce('span');
            $WH.ae(foo, $WH.ce('b'));
            $WH.ae(aNext, foo);

            aPrev.onclick = prevScreenshot;
            aNext.onclick = nextScreenshot;

            aCover = $WH.ce('a');
            aCover.className = 'screenshotviewer-cover';
            aCover.href = 'javascript:;';
            aCover.onclick = Lightbox.hide;
            var foo = $WH.ce('span');
            $WH.ae(foo, $WH.ce('b'));
            $WH.ae(aCover, foo);
            $WH.ae(screen, aPrev);
            $WH.ae(screen, aNext);
            $WH.ae(screen, aCover);
            var _div = $WH.ce('div');
            _div.className = 'text';
            h2Name = $WH.ce('h2');
            h2Name.className = 'first';
            $WH.ae(h2Name, $WH.ct(screenshot.name));
            $WH.ae(_div, h2Name);
            $WH.ae(dest, _div);

            imgDiv = $WH.ce('div');
            $WH.ae(screen, imgDiv);

            $WH.ae(dest, screen);

            var _div = $WH.ce('div');
            _div.style.paddingTop = '6px';
            _div.style.cssFloat = _div.style.styleFloat = 'right';
            _div.className = 'bigger-links';
            aApprove = $WH.ce('a');
            aApprove.href = 'javascript:;';
            $WH.ae(aApprove, $WH.ct('Approve'));
            $WH.ae(_div, aApprove);
            spApprove = $WH.ce('span');
            spApprove.style.display = 'none';
            $WH.ae(spApprove, $WH.ct('Approve'));
            $WH.ae(_div, spApprove);
            $WH.ae(_div, makePipe());
            aMakeSticky = $WH.ce('a');
            aMakeSticky.href = 'javascript:;';
            $WH.ae(aMakeSticky, $WH.ct('Make sticky'));
            $WH.ae(_div, aMakeSticky);
            $WH.ae(_div, makePipe());
            aDelete = $WH.ce('a');
            aDelete.href = 'javascript:;';
            $WH.ae(aDelete, $WH.ct('Delete'));
            $WH.ae(_div, aDelete);
            u = _div;
            $WH.ae(dest, _div);
            divFrom = $WH.ce('div');
            divFrom.className = 'screenshotviewer-from';
            var sp = $WH.ce('span');
            $WH.ae(sp, $WH.ct(LANG.lvscreenshot_from));
            $WH.ae(sp, $WH.ce('a'));
            $WH.ae(sp, $WH.ct(' '));
            $WH.ae(sp, $WH.ce('span'));
            $WH.ae(divFrom, sp);
            $WH.ae(dest, divFrom);
            _div = $WH.ce('div');
            _div.className = 'clear';
            $WH.ae(dest, _div);
            var aClose = $WH.ce('a');
            aClose.className = 'screenshotviewer-close';
            aClose.href = 'javascript:;';
            aClose.onclick = Lightbox.hide;
            $WH.ae(aClose, $WH.ce('span'));
            $WH.ae(dest, aClose);

            aOriginal = $WH.ce('a');
            aOriginal.className = 'screenshotviewer-original';
            aOriginal.href = 'javascript:;';
            aOriginal.target = '_blank';
            $WH.ae(aOriginal, $WH.ce('span'));
            $WH.ae(dest, aOriginal);

            __div = $WH.ce('div');
            divCaption = $WH.ce('span');
            divCaption.style.paddingRight = '8px';
            $WH.ae(__div, divCaption);
            var sp = $WH.ce('span');
            sp.style.whiteSpace = 'nowrap';
            aEdit = $WH.ce('a');
            aEdit.href = 'javascript:;';
            $WH.ae(aEdit, $WH.ct('Edit'));
            $WH.ae(sp, aEdit);
            $WH.ae(sp, makePipe());
            aClear = $WH.ce('a');
            aClear.href = 'javascript:;';
            $WH.ae(aClear, $WH.ct('Clear'));
            $WH.ae(sp, aClear);
            $WH.ae(__div, sp);
            $WH.ae(dest, __div);
            _div = $WH.ce('div');
            _div.className = 'clear';
            $WH.ae(dest, _div);
        }
        else {
            $WH.ee(h2Name);
            $WH.ae(h2Name, $WH.ct(screenshot.name));
        }

        onRender();
    }

    function onRender() {
        if (screenshot.pending) {
            aApprove.onclick    = ssm_Approve.bind(screenshot, true);
            aMakeSticky.onclick = ssm_Sticky.bind(screenshot, true);
            aDelete.onclick     = ssm_Delete.bind(screenshot, true);
        }
        else {
            aMakeSticky.onclick = ssm_Sticky.bind(screenshot, true);
            aDelete.onclick     = ssm_Delete.bind(screenshot, true);
        }

        aApprove.style.display  = screenshot.pending ? '' : 'none';
        spApprove.style.display = screenshot.pending ? 'none' : '';

        if (!screenshot.width || !screenshot.height) {
            if (loadingImage) {
                loadingImage.onload  = null;
                loadingImage.onerror = null;
            }
            else {
                container.className = '';
                lightboxComponents = [];

                while (container.firstChild) {
                    lightboxComponents.push(container.firstChild);
                    $WH.de(container.firstChild);
                }
            }

            var lightboxTimer = setTimeout(function () {
                screenshot.width  = 126;
                screenshot.height = 22;

                computeDimensions(0);

                screenshot.width  = null;
                screenshot.height = null;

                var div = $WH.ce('div');
                div.style.margin = '0 auto';
                div.style.width = '126px';

                var img = $WH.ce('img');
                img.src = g_staticUrl + '/images/ui/misc/progress-anim.gif';
                img.width = 126;
                img.height = 22;

                $WH.ae(div, img);
                $WH.ae(container, div);

                Lightbox.reveal();
                container.style.visiblity = 'visible';
            }, 150);

            loadingImage = new Image();
            loadingImage.onload = (function (screen, timer) {
                clearTimeout(timer);
                screen.width = this.width;
                screen.height = this.height;
                loadingImage = null;
                restoreLightbox();
                render();
            }).bind(loadingImage, screenshot, lightboxTimer);
            loadingImage.onerror = (function (timer) {
                clearTimeout(timer);
                loadingImage = null;
                Lightbox.hide();
                restoreLightbox();
            }).bind(loadingImage, lightboxTimer);
            loadingImage.src = (screenshot.url ? screenshot.url : g_staticUrl + '/uploads/screenshots/' + (screenshot.pending ? 'pending' : 'normal') + '/' + screenshot.id + '.jpg');
        }
        else {
            render();
        }
    }

    function cancelImageLoading() {
        if (!loadingImage) {
            return;
        }

        loadingImage.onload = null;
        loadingImage.onerror = null;
        loadingImage = null;

        restoreLightbox();
    }

    function restoreLightbox() {
        if (!lightboxComponents) {
            return;
        }

        $WH.ee(container);
        container.className = 'screenshotviewer';
        for (var i = 0; i < lightboxComponents.length; ++i) {
            $WH.ae(container, lightboxComponents[i]);
        }

        lightboxComponents = null;
    }

    this.show = function (opt) {
        Lightbox.show('screenshotmanager', {
            onShow:   onShow,
            onHide:   onHide,
            onResize: onResize
        }, opt);
    }
};
