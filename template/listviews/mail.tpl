Listview.templates.mail = {
    sort: [1],
    searchable: 1,
    filtrable: 1,

    columns: [
        {
            id: 'id',
            name: 'ID',
            width: '5%',
            value: 'id',
            compute: function(data, td) {
                if (data.id) {
                    $WH.ae(td, $WH.ct(data.id));
                }
            }
        },
        {
            id: 'subject',
            name: LANG.subject,
            type: 'text',
            align: 'left',
            value: 'subject',
            compute: function(mail, td, tr) {
                var wrapper = $WH.ce('div');

                var a = $WH.ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = this.getItemLink(mail);

                $WH.ae(a, $WH.ct(mail.subject));
                $WH.ae(wrapper, a);
                $WH.ae(td, wrapper);
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(a.subject, b.subject);
            },
            getVisibleText: function(mail) {
                return mail.subject;
            }
        },
        {
            id: 'body',
            name: LANG.text,
            type: 'text',
            align: 'left',
            value: 'body',
            compute: function(mail, td, tr) {
                td.innerText = mail.body;
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(a.body, b.body);
            },
            getVisibleText: function(mail) {
                return mail.body;
            }
        },
        {
            id: 'attachments',
            name: 'Attachments',
            type: 'text',
            compute: function(mail, td) {
                if (!mail.attachments.length)
                    return;

                mail.attachments.forEach(function(item, idx, arr) {
                    if (g_items && g_items[item]) {
                        i = Icon.create(g_items[item].icon, 0, false, '?item=' + item, 0, 0, false, false, true);
                        if (idx !== arr.length - 1)
                            i.style.paddingLeft = '5px';
                        $WH.ae(td, i);
                    }
                });
            },
            getVisibleText: function(mail) {
                if (!mail.attachments.length)
                    return null;                            // no attachments

                var itemId = $(mail.attachments).first()[0];
                    if (g_items && g_items[itemId])
                        return g_items[itemId]['name_' + Locale.getName()];

                return '';                                  // unk item
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
            }
        }
    ],
    getItemLink: function(mail) {
        return '?mail=' + mail.id;
    }
}
