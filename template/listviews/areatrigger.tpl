Listview.templates.areatrigger = {
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
            id: 'name',
            name: LANG.name,
            type: 'text',
            align: 'left',
            value: 'name',
            compute: function(areatrigger, td, tr) {
                var wrapper = $WH.ce('div');

                var a = $WH.ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = this.getItemLink(areatrigger);

                $WH.ae(a, $WH.ct(areatrigger.name));
                $WH.ae(wrapper, a);
                $WH.ae(td, wrapper);
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(a.name, b.name);
            },
            getVisibleText: function(areatrigger) {
                return areatrigger.name;
            }
        },
        {
            id: 'location',
            name: LANG.location,
            type: 'text',
            compute: function(areatrigger, td) {
                return Listview.funcBox.location(areatrigger, td);
            },
            getVisibleText: function(areatrigger) {
                return Listview.funcBox.arrayText(areatrigger.location, g_zones);
            },
            sortFunc: function(a, b, col) {
                return Listview.funcBox.assocArrCmp(a.location, b.location, g_zones);
            }
        },
        {
            id: 'type',
            name: LANG.type,
            type: 'text',
            value: 'type',
            width: '12%',
            compute: function(areatrigger, td, tr) {
                if (g_trigger_types[areatrigger.type])
                    $WH.ae(td, $WH.ct(g_trigger_types[areatrigger.type]))
                else
                    $WH.ae(td, $WH.ct(g_trigger_types[0]));
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
            },
            getVisibleText: function(areatrigger) {
                return g_trigger_types[areatrigger.type];
            }
        }
    ],
    getItemLink: function(areatrigger) {
        return '?areatrigger=' + areatrigger.id;
    }
}
