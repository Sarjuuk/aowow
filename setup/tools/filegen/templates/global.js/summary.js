/*
Global functions related to the Summary class (item comparison, item set summary)
*/

var suDialog;

function su_addToSaved(items, nItems, newWindow, level)
{
    if (!items)
        return;

    if (!suDialog)
        suDialog = new Dialog();

    var doCompare = function(data)
    {
        var saved = g_getWowheadCookie('compare_groups'),
            url   = '?compare';

        if (data.action > 1) // Save
        {
            if (saved)
                items = saved + ';' + items;

            g_setWowheadCookie('compare_groups', items, true);

            if (level)
                g_setWowheadCookie('compare_level', level, true);
        }
        else // Don't save
            url += '=' + items + (level ? '&l=' + level : '');

        if (data.action < 3) // View now
        {
            if (newWindow)
                window.open(url);
            else
                location.href = url;
        }
    };

    suDialog.show('docompare', {
        data: { selecteditems: nItems, action: 1 },
        onSubmit: doCompare
    });
}

Dialog.templates.docompare = {

    title: LANG.dialog_compare,
    width: 400,
 // buttons: [['check', LANG.ok], ['x', LANG.cancel]],
    buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],

    fields:
    [
        {
            id: 'selecteditems',
            type: 'caption',
            compute: function(field, value, form, td)
            {
                td.innerHTML = $WH.sprintf((value == 1 ? LANG.dialog_selecteditem : LANG.dialog_selecteditems), value);
            }
        },
        {
            id: 'action',
            type: 'radio',
            label: '',
            value: 3,
            submitOnDblClick: 1,
            options: {
                1: LANG.dialog_nosaveandview,
                2: LANG.dialog_saveandview,
                3: LANG.dialog_saveforlater
            }
        }
    ]
};
