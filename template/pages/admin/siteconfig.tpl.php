<?php $this->brick('header'); ?>

    <script type="text/javascript">
        function createStatusIcon(errTxt)
        {
            function fadeout()
            {
                $(this).animate({ opacity: '0.0' }, 250, null, function() {
                    $WH.de(this);
                    $WH.Tooltip.hide()
                });
            }

            var a = $WH.ce('a');
            a.style.opacity = 0;
            a.className = errTxt ? 'icon-report' : 'icon-tick';
            g_addTooltip(a, errTxt || 'success', 'q');
            a.onclick = fadeout.bind(a);
            setTimeout(function () { $(a).animate({ opacity: '1.0' }, 250); }, 50);
            setTimeout(fadeout.bind(a), 10000);

            return a;
        }

        function cfg_add(el)
        {
            _self = el.parentNode.parentNode;

            var tr = $WH.ce('tr');

            tr.style.position = 'relative';

            var td  = $WH.ce('td'),
                key = $WH.ce('input');

            key.type = 'text';
            key.name = 'key';
            $WH.ae(td, key);
            $WH.ae(tr, td);

            var td  = $WH.ce('td'),
                val = $WH.ce('input');

            val.type = 'text';
            val.name = 'value';
            $WH.ae(td, val);
            $WH.ae(tr, td);

            var td      = $WH.ce('td'),
                aCancel = $WH.ce('a'),
                aSubmit = $WH.ce('a'),
                status  = $WH.ce('span');

            aSubmit.className  = 'icon-save tip';
            g_addTooltip(aSubmit, 'Submit Setting', 'q');

            aCancel.className  = 'icon-delete tip';
            g_addTooltip(aCancel, 'Cancel', 'q');

            aSubmit.onclick = cfg_new.bind(aSubmit, key, val);
            aCancel.onclick = function () {
                $WH.Tooltip.hide();
                $WH.de(this.parentNode.parentNode);
            };

            status.className = 'status';

            $WH.ae(td, aSubmit);
            $WH.ae(td, $WH.ct('|'));
            $WH.ae(td, aCancel);
            $WH.ae(td, status);
            $WH.ae(tr, td);

            _self.parentNode.insertBefore(tr, _self);
            key.focus();
        }

        function cfg_new(elKey, elVal)
        {
            var
                _td     = this.parentNode,
                _row    = this.parentNode.parentNode,
                _status = $(_td).find('.status')[0];

            // already performing action
            if (_status.lastChild && _status.lastChild.tagName == 'IMG')
                return;
            else if (_status.lastChild && _status.lastChild.tagName == 'A')
                $WH.ee(_status);

            if (!elKey.value || !elVal.value)
            {
                $WH.ae(_status, createStatusIcon('key or value are empty'));
                return;
            }

            var
                key   = elKey.value.toLowerCase().trim(),
                value = elVal.value.trim();

            $(_status).append(CreateAjaxLoader());

            new Ajax('?admin=siteconfig&action=add&key=' + key + '&val=' + escape(value), {
                method: 'get',
                onSuccess: function(xhr) {
                    $WH.ee(_status);

                    if (!xhr.responseText) {
                        $WH.ee(_row);
                        $(_row).append($('<td>' + key + '</td>')).append($('<td><input id="' + key + '" type="text" name="' + key + '" value="' + value + '" /></td>'));

                        var
                            td = $WH.ce('td'),
                            a  = $WH.ce('a'),
                            sp = $WH.ce('span');

                        g_addTooltip(a, 'Save Changes', 'q');
                        a.onclick = cfg_submit.bind(a, key);
                        a.className = 'icon-save tip';
                        $WH.ae(td, a);

                        a  = $WH.ce('a');
                        a.className = 'icon-refresh tip disabled';
                        $WH.ae(td, $WH.ct('|'));
                        $WH.ae(td, a);

                        a  = $WH.ce('a');
                        g_addTooltip(a, 'Remove Setting', 'q');
                        a.onclick = cfg_remove.bind(a, key);
                        a.className = 'icon-delete tip';
                        $WH.ae(td, $WH.ct('|'));
                        $WH.ae(td, a);

                        sp.className = 'status';
                        $WH.ae(sp, createStatusIcon());
                        $WH.ae(td, sp);
                        $WH.ae(_row, td);
                    }
                    else {
                        $WH.ae(_status, createStatusIcon(xhr.responseText));
                    }

                }
            });
        }

        function cfg_submit(id)
        {
            var
                node = $WH.ge(id),
                _td  = this.parentNode,
                _status = $(_td).find('.status')[0];

            if (!node)
                return;

            var value = 0;

            // already performing action
            if (_status.lastChild && _status.lastChild.tagName == 'IMG')
                return;
            else if (_status.lastChild && _status.lastChild.tagName == 'A')
                $WH.ee(_status);

            if (node.tagName == 'DIV')
            {
                // bitmask
                $(node).find('input[type="checkbox"]').each(function(idx, opt) {
                    if (opt.checked)
                        value |= (1 << opt.value);
                });

                // boolean
                $(node).find('input[type="radio"]').each(function(idx, opt) {
                    if (opt.checked)
                        value = opt.value;
                });
            }
            else if (node.tagName == 'SELECT')                  // opt-list
            {
                $(node).find('option').each(function(idx, opt) {
                    if (opt.selected)
                        value = opt.value;
                });
            }
            else if (node.tagName == 'INPUT')                   // string or numeric
            {
                if (node.value && node.value.search(/[^\d\s\/\*\-\+\.]/i) == -1 && node.value.split('.').length < 3)
                    node.value = eval(node.value);

                value = node.value;
            }

            value = value.toString().trim();

            if (!value.length && (node.tagName != 'INPUT' || node.type != 'text'))
            {
                $WH.ae(_status, createStatusIcon('value is empty'));
                return;
            }

            $(_status).append(CreateAjaxLoader());

            new Ajax('?admin=siteconfig&action=update&key=' + id + '&val=' + escape(value), {
                method: 'get',
                onSuccess: function(xhr) {
                    $WH.ee(_status);
                    $WH.ae(_status, createStatusIcon(xhr.responseText));
                }
            });
        }

        function cfg_default(id, val)
        {
            var node = $WH.ge(id);
            if (!node)
                return;

            if (node.tagName == 'DIV')
            {
                // bitmask
                $(node).find('input[type="checkbox"]').each(function(idx, opt) { opt.checked = !!(val & (1 << opt.value)); });

                // boolean
                $(node).find('input[type="radio"]').each(function(idx, opt) { opt.checked = !!opt.value == !!val; });
            }
            else if (node.tagName == 'SELECT')                  // opt-list
                $(node).find('option').each(function(idx, opt) { opt.selected = opt.value == val; });
            else if (node.tagName == 'INPUT')                   // string or numeric
                node.value = node.type == 'text' ? val : eval(val);
        }

        function cfg_remove(id)
        {
            var
                _td = this.parentNode,
                _status = $(_td).find('.status')[0];

            // already performing action
            if (_status.lastChild && _status.lastChild.tagName == 'IMG')
                return;
            else if (_status.lastChild && _status.lastChild.tagName == 'A')
                $WH.ee(_status);

            if (!confirm('Confirm remove'))
                return;

            $(_status).append(CreateAjaxLoader());

            new Ajax('?admin=siteconfig&action=remove&key=' + id, {
                method: 'get',
                onSuccess: function(xhr) {
                    if (!xhr.responseText)
                        $WH.de(_td.parentNode);
                    else {
                        $WH.ee(_status);
                        $WH.ae(_status, createStatusIcon(xhr.responseText));
                    }

                }
            });
        }
    </script>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

    $this->brick('lvTabs');
?>
            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
