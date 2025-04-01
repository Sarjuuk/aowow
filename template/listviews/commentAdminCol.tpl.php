var _ = [
    {
        id: 'manage',
        name: 'Manage',
        type: 'text',
        align: 'center',
        value: 'subject',
        sortable: false,
        compute: function(comment, td, tr) {
            let wrapper = $WH.ce('div');

            let send = function (el, id, status)
            {
                $.ajax({cache: false, url: '?admin=comment', type: 'POST',
                    error: function() {
                        alert('Operation failed.');
                    },
                    success: function(json) {
                        if (json != 1)
                            alert('Operation failed.');
                        else
                            $WH.de(el.parentNode);
                    },
                    data: { id: id, status: status }
                })

                return true;
            };

            let a = $WH.ce('a');
            a.style.fontFamily = 'Verdana, sans-serif';
            a.style.marginLeft = '10px';
            a.href = '#';

            _ = a.cloneNode();
            _.className = 'icon-tick';
            _.onclick = send.bind(this, td, comment.id, 0);
            g_addTooltip(_, LANG.lvcomment_uptodate);
            $WH.ae(wrapper, _);

            _ = a.cloneNode();
            _.className = 'icon-delete';
            _.onclick = send.bind(this, td, comment.id, 1);
            g_addTooltip(_, LANG.delete);
            $WH.ae(wrapper, _);

            $WH.ae(td, wrapper);
        }
    }
];
