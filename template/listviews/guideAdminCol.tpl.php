var _ = [
    {
        id: 'description',
        name: LANG.ct_dialog_description,
        type: 'text',
        align: 'left',
        value: 'description',
        after: 'title',
        width: '50%',
        compute: function(guide, td, tr) {
            td.innerText = guide.description;
        },
        sortFunc: function(a, b, col) {
            return $WH.strcmp(a.description, b.description);
        },
        getVisibleText: function(guide) {
            return guide.description;
        }
    },
    {
        id: 'manage',
        name: 'Manage',
        type: 'text',
        align: 'center',
        value: 'subject',
        sortable: false,
        compute: function(guide, td, tr) {
            let wrapper = $WH.ce('div');

            let send = function (el, id, status)
            {
                let message = '';
                if (status == 4) // rejected
                {
                    while (message === '')
                        message = prompt('Please provide your reasoning.');

                    if (message === null)
                        return false;
                }

                $.ajax({cache: false, url: '?admin=guide', type: 'POST',
                    error: function() {
                        alert('Operation failed.');
                    },
                    success: function(json) {
                        if (json != 1)
                            alert('Operation failed.');
                        else
                            $WH.de(el.parentNode);
                    },
                    data: { id: id, status: status, msg: message }
                })

                return true;
            };

            let a = $WH.ce('a');
            a.style.fontFamily = 'Verdana, sans-serif';
            a.style.marginLeft = '10px';
            a.href = '#';

            _ = a.cloneNode();
            _.className = 'icon-edit';
            _.href = '?guide=edit&id=' + guide.id;
            g_addTooltip(_, 'Edit');
            $WH.ae(wrapper, _);

            _ = a.cloneNode();
            _.className = 'icon-tick';
            _.onclick = send.bind(this, td, guide.id, 3);
            g_addTooltip(_, 'Approve');
            $WH.ae(wrapper, _);

            _ = a.cloneNode();
            _.className = 'icon-delete';
            _.onclick = send.bind(this, td, guide.id, 4);
            g_addTooltip(_, 'Reject');
            $WH.ae(wrapper, _);

            $WH.ae(td, wrapper);
        }
    }
];
