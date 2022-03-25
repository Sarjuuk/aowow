var _ = {
    id: 'restock',
    name: LANG.restock,
    width: '10%',
    value: 'restock',
    after: 'stack',
    compute: function(data, td) {
        if (data.restock) {
            let t = g_formatTimeElapsed(data.restock);

            $WH.ae(td, $WH.ct(t));
        }
    }
};
