var _ = [
    {
        id: 'standing',
        after: 'reqlevel',
        name: LANG.standing,
        width: '12%',
        value: 'standing',
        type: 'text',
        getValue: function(item)
        {
            return g_reputation_standings[item.standing];
        },
        compute: function(item, td)
        {
            return g_reputation_standings[item.standing];
        }
    }
];
