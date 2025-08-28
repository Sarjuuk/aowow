function pm() {
    var pass1 = $('#newpass').val();
    var pass2 = $('#confirmpass').val();
    var
        bracket = '',
        buff    = '';

    if (pass1 != '' && $WH.trim(pass1).length < 6)
        buff = '<span class="q10">' + LANG.message_passwordmin + '</span>';

    if (pass1 != '' && pass2 != '') {
        if (buff != '')
            buff += '<br />';

        if (pass1 == pass2)
            buff += '<span class="q2">' + LANG.myaccount_passmatch + '</span>';
        else
            buff += '<span class="q10">' + LANG.myaccount_passdontmatch + '</span>';
    }

    if (buff != '')
        bracket = '}';

    $WH.ge('pm1').innerHTML = bracket;
    $WH.ge('pm2').innerHTML = buff;
}

function spd(form) {
    var desc = form.elements.desc;
    if (desc.value.length == 0)
        return true;

    if (desc.value.length < 10) {
        alert(LANG.message_descriptiontooshort);
        return false;
    }

    var charLimit = Listview.funcBox.coGetCharLimit(2);
    if (desc.value.length > charLimit)
        if (!confirm($WH.sprintf(LANG.confirm_descriptiontoolong, charLimit, desc.value.substring(charLimit - 30, charLimit))))
            return false;

    return true;
}

function sfs(form) {
    var sig = form.elements.sig;
    sig.value = $WH.trim(sig.value);
    if (sig.value.length == 0)
        return true;

    var charLimit = Listview.funcBox.coGetCharLimit(4);
    if (sig.value.length > charLimit)
        if (!confirm($WH.sprintf(LANG.confirm_signaturetoolong, charLimit, sig.value.substring(charLimit - 30, charLimit))))
            return false;

    var nLines;
    if ((nLines = sig.value.indexOf("\n")) != -1 && (nLines = sig.value.indexOf("\n", nLines + 1)) != -1 && (nLines = sig.value.indexOf("\n", nLines + 1)) != -1)
        if (!confirm($WH.sprintf(LANG.confirm_signaturetoomanylines, 3)))
            return false;

    return true;
}

$(document).ready(function () {
    $('form#change-password').submit(function () {
        var curPass   = $('input[name=currentPassword]');
        var newPass   = $('input[name=newPassword]');
        var checkPass = $('input[name=confirmPassword]');

        if (!curPass.val() && !newPass.val() && !checkPass.val()) {
            alert(LANG.message_enteremailorpass);
            return false;
        }

        if (newPass.val() || checkPass.val()) {
            if (!curPass.val()) {
                alert(LANG.message_enterpassword);
                curPass[0].focus();
                return false;
            }

            if ($WH.trim(newPass.val()).length < 6) {
                alert(LANG.message_passwordmin);
                newPass[0].focus();
                return false;
            }

            if ($WH.trim(newPass.val()) === $WH.trim(curPass.val())) {
                alert(LANG.message_newpassdifferent);
                newPass[0].focus();
                return false;
            }

            if (newPass.val() !== checkPass.val()) {
                alert(LANG.message_passwordsdonotmatch);
                newPass[0].focus();
                return false;
            }
        }

        return true;
    });

    $('form#change-email').submit(function () {
        var curMail = $('input[name=current-email]');
        var newMail = $('input[name=newemail]');

        if (!newMail.val()) {
            alert(LANG.message_enteremailorpass);
            return false;
        }

        if (newMail.val()) {
            if (newMail.val() == curMail.val()) {
                alert(LANG.message_newemaildifferent);
                newMail[0].focus();
                return false;
            }

            if (!g_isEmailValid(newMail.val())) {
                alert(LANG.message_emailnotvalid);
                newMail[0].focus();
                return false;
            }
        }

        return true;
    });

    $('form#change-username').submit(function () {
        var curName = $('input[name=current-username]');
        var newName = $('input[name=newUsername]');

        if (!newName.val()) {
            alert(LANG.message_enterusername);
            newName[0].focus();
            return false;
        }
        if ($WH.trim(newName.val()).length < 4) {
            alert(LANG.message_usernamemin);
            newName[0].focus();
            return false;
        }
        if (!g_isUsernameValid(newName.val())) {
            alert(LANG.message_usernamenotvalid);
            newName[0].focus();
            return false;
        }
        if (newName.val() == curName.val()) {
            alert(LANG.message_newnamedifferent);
            newName[0].focus();
            return false;
        }
    });
});

function fa_validateForm(form) {
    if (form.elements.avatar[2].checked && form.elements.customicon.selectedIndex == 0) {
        form.action = '?upload=image-crop';
        form.enctype = 'multipart/form-data';
    }
    else {
        form.action = '?account=forum-avatar';
        form.enctype = 'application/x-www-form-urlencoded';
    }

    return true;
}

function faChange(mode) {
    $WH.ge('avaSel1').style.display = (mode == 1 ? '': 'none');
    $WH.ge('avaSel2').style.display = (mode == 2 ? '': 'none');
}

function spawi() {
    var inp = $WH.ge('wowicon');
    inp.value = $WH.trim(inp.value);

    var preview = $WH.ge('avaPre1');
    while (preview.firstChild)
        $WH.de(preview.firstChild);

    $WH.ae(preview, Icon.createUser(1, inp.value, 2, null, ((g_user.roles & U_GROUP_PREMIUM) ? g_user.settings.premiumborder : Icon.STANDARD_BORDER)));
}

function spawj() {
    var avSelect = $WH.ge('customicon');
    var preview  = $WH.ge('avaPre2');
    while (preview.firstChild)
        $WH.de(preview.firstChild);

    if (avSelect.selectedIndex != 0) {
        $WH.ge('iconbrowse').style.display = 'none';
        iconId = avSelect.options[avSelect.selectedIndex].value;
        $WH.ae(preview, Icon.createUser(2, iconId, 2, null, ((g_user.roles & U_GROUP_PREMIUM) ? g_user.settings.premiumborder : Icon.STANDARD_BORDER)));
        preview.style.display = '';
    }
    else {
        preview.style.display = 'none';
        $WH.ge('iconbrowse').style.display = '';
    }
}

var imageDetailDialog = new Dialog();
Listview.templates.avatar = {
    sort: [4],
    nItemsPerPage: -1,
    mode: 1,
    poundable: 0,
    columns: [{
        id: 'name',
        name: LANG.name,
        type: 'text',
        value: 'name',
        align: 'left',
        compute: function (data, td, tr) {
            tr.onclick = imageDetailDialog.show.bind(null, 'imageupload', {
                data: data,
                onSubmit: this.template.updateImageInfo.bind(this, data)
            });
            var avIcon = Icon.createUser(2, data.id, 0, null, (g_user.roles & U_GROUP_PREMIUM) ? g_user.settings.premiumborder : Icon.STANDARD_BORDER);
            avIcon.style.cssFloat = avIcon.style.styleFloat = 'left';
            td.style.position = 'relative';
            $WH.ae(td, avIcon);
            $WH.ae(td, $WH.ce('span', { style: { paddingLeft: '7px', lineHeight: '1.8em' }, innerHTML: data.name }));
            if (data.current) {
                $WH.ae(td, $WH.ce('span', {
                    style: {
                        fontStyle: 'italic',
                        cssFloat: 'right',
                        styleFloat: 'right',
                        marginTop: '3px'
                    },
                    className: 'small',
                    innerHTML: 'Current'
                }));
            }
        },
        getVisibleText: function (a) {
            return a.caption;
        }
    },
    {
        id: 'size',
        name: 'Size',
        type: 'number',
        value: 'size',
        width: '125px',
        compute: function (a, b) {
            return Listview.funcBox.coFormatFileSize(a.size)
        }
    },
    {
        id: 'status',
        name: 'Status',
        type: 'text',
        value: 'status',
        width: '100px',
        compute: function (a, b) {
            if (a.status == 2)
                $WH.ae(b, $WH.ce('span', { className: 'q10', innerHTML: 'Rejected' }))
            else
                return 'Ready';
        }
    },
    {
        id: 'when',
        name: 'When',
        type: 'date',
        value: 'when',
        width: '150px',
        compute: function (b, d) {
            var c = $WH.ce('span');
            var a = new Date(b.when);
            g_formatDate(c, (g_serverTime - a) / 1000, a);
            $WH.ae(d, c)
        }
    }],
    onBeforeCreate: function () {
        for (i in this.data)
            this.data[i].pos = i;
    },
    createCbControls: function (e, d) {
        if (!d && this.data.length < 15)
            return;

        var c = $WH.ce('input'),
            b = $WH.ce('input'),
            a = $WH.ce('input');

        c.type = b.type = a.type = 'button';

        c.value = 'Delete';
        b.value = 'Set as avatar';
        a.value = 'Upload new one';

        c.onclick = this.template.deleteFiles.bind(this);
        b.onclick = this.template.useAvatar.bind(this);
        a.onclick = this.template.jumpToUpload.bind(this);

        $WH.ae(e, b);
        $WH.ae(e, c);
        $WH.ae(e, a);
    },
    updateImageInfo: function (b, a) {
        if (b.name != a.name) {
            $.post('?account=rename-icon', {
                id: a.id,
                name: a.name
            });
            this.setRow(a);
        }
    },
    deleteFiles: function () {
        var rows = this.getCheckedRows();
        if (!rows.length)
            return;

        var ids = '',
        first = true;
        $WH.array_walk(rows, function (x) {
            if (first)
                first = false;
            else
                ids += ',';

            ids += x.id;
        });

        var _ = confirm('Are you sure you want to delete these icons?');
        if (_ == false)
            return;

        $.post('?account=delete-icon', { id: ids });

        this.deleteRows(rows);
        this.resetCheckedRows();
        this.refreshRows();
    },
    useAvatar: function () {
        var rows = this.getCheckedRows();
        if (!rows.length)
            return;

        if (rows.length > 1) {
            alert('Please select only 1 image to use as your avatar.');
            return;
        }

        var row = rows[0];
        $WH.array_walk(this.data, function (x) {
            x.current = 0;
            x.__tr = null
        });
        row.current = 1;

        new Ajax('?account=forum-avatar&avatar=2&customicon=' + row.id);
        this.refreshRows()
    },
    jumpToUpload: function () {
     // aowow - community is not on idx:2 for extAuth cases
     // _.show(2);
        _.show(_.tabs.findIndex((x) => x.id == 'community'));
        location.href = '?account#community';

        var a = $WH.ac(document.fa);
        window.scrollTo(0, a.y);

        document.fa.avatar[2].click();
        document.fa.customicon.selectedIndex = 0;

        spawj();
    },
    onNoData: function (lv) {
        var sp = $WH.ce('span');
        var a  = $WH.ce('a');

        a.onclick = this.template.jumpToUpload.bind(this);
        a.href = 'javascript:;';
        $WH.ae(a, $WH.ct('Upload'));

        $WH.ae(sp, $WH.ct("You havn't uploaded any custom avatars yet. "));
        $WH.ae(sp, a);
        $WH.ae(sp, $WH.ct(' one now!'));

        $WH.ae(lv, sp);
    }
};

Dialog.templates.imageupload = {
    title: LANG.dialog_imagedetails,
    // aowow - adapted to existing css - buttons: [['check', LANG.ok], ['x', LANG.cancel]],
    buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],
    fields: [
        {
            id: 'id',
            type: 'hidden',
            label: ' ',
            size: 30,
            required: 0,
            compute: function (field, value, form, td, tr) {
                var div = $WH.ce('div');
                div.style.position = 'relative';

                var div2 = $WH.ce('div');
                div2.style.position = 'relative';

                var img = $WH.ce('img');
                switch (this.data.type) {
                    case 1:
                        img = Icon.createUser(2, null, 2, null, (g_user.roles & U_GROUP_PREMIUM) ? g_user.settings.premiumborder : Icon.STANDARD_BORDER);
                        break;
                }

                $WH.ae(div2, img);
                this.icon = img;

                $WH.ae(div, field);
                $WH.ae(div, div2);

                $WH.ae(td, div);
            }
        },
        {
            id: 'name',
            type: 'text',
            label: LANG.dialog_imagename,
            size: 20,
            required: 1,
            submitOnEnter: 1,
            validate: function (newValue, data) {
                if (newValue.match(/^[a-zA-Z][a-zA-Z0-9 ]{0,19}$/))
                    return true;
                else {
                    alert(LANG.message_invalidname);
                    return false;
                }
            }
        },
    ],
    onBeforeShow: function () {
        switch (this.data.type) {
            case 1:
                this.template.width = 300;
                break;
        }
    },
    onShow: function (form) {
        switch (this.data.type) {
            case 1:
                var url = g_staticUrl + '/uploads/avatars/' + this.data.id + '.jpg';
                Icon.setTexture(this.icon, 2, url);
                break;
        }
        setTimeout(function () {
            var inp = form.elements.name;
            inp.focus();
            inp.select();
        }, 1);
    }
};
