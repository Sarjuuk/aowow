Listview.templates.emote = {
    sort: [1],
    searchable: 1,
    filtrable: 1,

    columns: [
        {
            id: 'name',
            name: LANG.name,
            type: 'text',
            align: 'left',
            value: 'name',
            compute: function(emote, td, tr) {
                var wrapper = $WH.ce('div');

                var a = $WH.ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = this.getItemLink(emote);
                $WH.ae(a, $WH.ct(emote.name));

                $WH.ae(wrapper, a);

                $WH.ae(td, wrapper);
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(a.name, b.name);
            },
            getVisibleText: function(emote) {
                return emote.name;
            }
        },
        {
            id: 'preview',
            name: LANG.preview,
            type: 'text',
            align: 'left',
            value: 'name',
            compute: function(emote, td, tr) {
                var prev = '';
                if (emote.preview) {
                    td.className = 's4';
                    prev = emote.preview.replace(/%\d?\$?s/g, '<' + LANG.name + '>');
                    $WH.ae(td, $WH.ct(prev));
                }
                else {
                    td.className = 'q0';
                    td.style.textAlign = 'right';
                    td.style.Align = 'right';

                    var
                        sm = $WH.ce('small'),
                         i = $WH.ce('i');

                    sm.style.paddingRight = '8px';

                    $WH.ae(i, $WH.ct(LANG.lvnodata));
                    $WH.ae(sm, i);
                    $WH.ae(td, sm);
                }
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(a.preview.replace(/%\d?\$?s/g, ''), b.preview.replace(/%\d?\$?s/g, ''));
            },
            getVisibleText: function(emote) {
                return emote.preview.replace(/%\d?\$?s/g, '');
            }
        }
    ],
    getItemLink: function(emote) {
        return '?emote=' + emote.id;
    }
}
