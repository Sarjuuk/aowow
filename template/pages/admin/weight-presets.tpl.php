<?php $this->brick('header'); ?>

    <script type="text/javascript">
        var SummaryAdmin = {
            createControl: function(text, id, _class, onclick, href) {
                var a = $WH.ce('a');

                if (href) {
                    a.href = href;
                    a.target = '_blank';
                }
                else {
                    a.href = 'javascript:;';
                }

                if (id) {
                    a.id = id;
                }

                if (_class) {
                    a.className = _class;
                }

                if (onclick) {
                    a.onclick = onclick;
                }

                $WH.ae(a, $WH.ct(text));

                return a;
            },

            updateControls: function() {
                var
                    div = _suDiv,
                    a,
                    d2 = $WH.ce('div'),
                    d = $WH.ce('div');

                var div2 = $WH.ce('div');
                div2.className = 'summary-controls-right';
                $WH.ae(div, div2);

                d2.id = 'su_weights';
                d.className = 'summary-weights-inner';
                $WH.ae(d2, d);
                $WH.ae(div2, d2);

                var w = $WH.ce('div');
                w.id = 'su_weight';
                $WH.ae(d, w);
                _ = $WH.ce('div');
                $WH.ae(w, _);

                s = $WH.ce('select');
                s.onchange = s.onkeyup = this.refreshWeights.bind(this, s);
                $WH.ae(s, $WH.ce('option'));
                $WH.ae(_, s);

                a = $WH.ce('a');
                a.href = 'javascript:;';
                a.className = 'icon-refresh';
                a.appendChild($WH.ct(LANG.tc_restore));
                a.onclick = this.restorScale;
                a.onmousedown = $WH.rf;
                $WH.aef(_ctrlDiv, a);

                $WH.aef(_ctrlDiv, $WH.ce('br'));

                a = $WH.ce('a');
                a.href = 'javascript:;';
                a.className = 'icon-save';
                a.appendChild($WH.ct(LANG.fisavescale));
                a.onclick = this.saveScale;
                a.onmousedown = $WH.rf;
                $WH.aef(_ctrlDiv, a);

                _ = false;

                for (var i = 0, len = this.traits.length; i < len; ++i) {
                    var p = this.traits[i];

                    if (p.type == 'sep') {
                        if (_ && _.childNodes.length > 0) {
                            $WH.ae(s, _);
                        }
                        _ = $WH.ce('optgroup');
                        _.label = (LANG.traits[p.id] ? LANG.traits[p.id] : p.name);
                    }
                    else if (p.type != 'custom') {
                        var o = $WH.ce('option');
                        o.value = p.id;
                        $WH.ae(o, $WH.ct((p.indent ? '- ' : '') + (LANG.traits[p.id] ? LANG.traits[p.id][0] : p.name)));
                        $WH.ae(_, o);
                    }
                }
                if (_ && _.childNodes.length > 0) {
                    $WH.ae(s, _);
                }

                _ = $WH.ce('div');
                a = this.createControl(LANG.su_addweight, 'su_addweight', '', this.addWeight.bind(this));
                $WH.ae(_, a);
                $WH.ae(d, _);

                _ = $WH.ce('div');
                _.className = 'summary-weights-buttons';
            },

            updateWeights: function(weights) {
                var _ = $WH.ge('su_weight');
                var c = _.childNodes[0].childNodes[0];
                var i = 0;

                for (var w in weights) {
                    if (!LANG.traits[w]) {
                        continue;
                    }

                    if (i++ > 0) {
                        c = this.addWeight();
                    }

                    var opts = c.getElementsByTagName('option');

                    for (var j = 0, len = opts.length; j < len; ++j) {
                        if (opts[j].value == w) {
                            opts[j].selected = true;
                            break;
                        }
                    }

                    this.refreshWeights(c, weights[w]);
                }
            },

            addWeight: function(e) {
                var _ = $WH.ge('su_weight');
                var a = $WH.ge('su_addweight');

                if (_.childNodes.length >= 14) {
                    a.style.display = 'none';
                }

                a = _.childNodes[0].lastChild;
                if (a.nodeName != 'A') {
                    $WH.ae(_.childNodes[0], $WH.ct(String.fromCharCode(160, 160)));
                    $WH.ae(_.childNodes[0], this.createControl(LANG.firemove, '', '', this.deleteWeight.bind(this, _.childNodes[0].firstChild)));
                }
                else {
                    a.firstChild.nodeValue = LANG.firemove;
                    a.onmouseup = this.deleteWeight.bind(this, _.childNodes[0].firstChild);
                }

                var
                    d = $WH.ce('div'),
                    c = _.childNodes[0].childNodes[0].cloneNode(true);

                $WH.ae(_, d);

                c.onchange = c.onkeyup = this.refreshWeights.bind(this, c);
                $WH.ae(d, c);

                $WH.ae(d, $WH.ct(String.fromCharCode(160, 160)));
                $WH.ae(d, this.createControl(LANG.firemove, '', '', this.deleteWeight.bind(this, c)));

                if (e) {
                    $WH.sp($WH.$E(e));
                }

                return c;
            },

            resetWeights: function() {
                var _ = $WH.ge('su_weight');

                while (_.childNodes.length >= 2) {
                    _.removeChild(_.childNodes[1]);
                }

                var d = _.childNodes[0];
                while (d.childNodes.length > 1) {
                    d.removeChild(d.childNodes[1]);
                }
                d.firstChild.selectedIndex = 0;

                var a = $WH.ge('su_addweight');
                if (_.childNodes.length < 15) {
                    a.style.display = '';
                }
            },

            deleteWeight: function(sel, e) {
                var
                    d = sel.parentNode,
                    c = d.parentNode;

                $WH.de(d);

                if (c.childNodes.length == 1) {
                    var _ = c.firstChild;
                    if (_.firstChild.selectedIndex > 0) {
                        var a = _.lastChild;
                        a.firstChild.nodeValue = LANG.ficlear;
                        a.onmouseup = this.resetWeights.bind(this);
                    }
                    else {
                        while (_.childNodes.length > 1) {
                            $WH.de(_.childNodes[1]);
                        }
                    }
                }

                var a = $WH.ge('su_addweight');
                if (c.childNodes.length < 15) {
                    a.style.display = '';
                }

                if (e) {
                    $WH.sp($WH.$E(e));
                }
            },

            refreshWeights: function(sel, value) {
                var d = sel.parentNode;

                while (d.childNodes.length > 1) {
                    $WH.de(d.childNodes[1]);
                }

                if (sel.selectedIndex > 0) {
                    $WH.ae(d, $WH.ct(' '));
                    var _ = $WH.ce('input');
                    _.type = 'text';
                    _.value = (value | 0);
                    _.maxLength = 7;
                    _.style.textAlign = 'center';
                    _.style.width = '4.5em';
                    _.setAttribute('autocomplete', 'off');
                    _.onchange = this.sortWeights.bind(this, _);
                    $WH.ae(d, _);
                    this.sortWeights(_);
                }

                if (d.parentNode.childNodes.length == 1) {
                    if (sel.selectedIndex > 0) {
                        $WH.ae(d, $WH.ct(String.fromCharCode(160, 160)));
                        $WH.ae(d, this.createControl(LANG.ficlear, '', '', this.resetWeights.bind(this)));
                    }
                }
                else if (d.parentNode.childNodes.length > 1) {
                    $WH.ae(d, $WH.ct(String.fromCharCode(160, 160)));
                    $WH.ae(d, this.createControl(LANG.firemove, '', '', this.deleteWeight.bind(this, sel)));
                }
            },

            sortWeights: function(input) {
                var
                    _ = $WH.ge('su_weight'),
                    v = Number(input.value),
                    c = input.parentNode;

                var n = 0;
                for (var i = 0, len = _.childNodes.length; i < len; ++i) {
                    var d = _.childNodes[i];
                    if (d.childNodes.length == 5) {
                        if (d.childNodes[0].tagName == 'SELECT' && d.childNodes[2].tagName == 'INPUT') {
                            if (v > Number(d.childNodes[2].value)) {
                                _.insertBefore(c, d);
                                return;
                            }
                            ++n;
                        }
                    }
                }

                if (n < len) {
                    _.insertBefore(c, _.childNodes[n]);
                }
                else {
                    $WH.ae(_, c);
                }
            },

            saveScale: function() {
                if (!_curWT) {
                    createStatusIcon('no scale selected');
                    return;
                }


                var
                    id    = _curWT,
                    scale = {},
                    _     = $WH.ge('su_weight'),
                    n     = 0;

                for (i = 0; i < _.childNodes.length; ++i) {
                    var
                        w    = fi_Lookup($WH.gE(_.childNodes[i], 'select')[0].value, 'items'),
                        inps = $WH.gE(_.childNodes[i], 'input'),
                        v;

                    for (j in inps) {
                        if (inps[j]) {
                            v = inps[j].value;
                            break;
                        }
                    }

                    if (w && v && v != 0) {
                        scale[w.name] = v;
                        ++n;
                    }
                }

                var _ = _iconDiv.firstChild.firstChild.style;
                if (_.backgroundImage.length && (_.backgroundImage.indexOf(g_staticUrl) >= 4 || g_staticUrl == '')) {
                    var
                        start = _.backgroundImage.lastIndexOf('/'),
                        end   = _.backgroundImage.indexOf('.jpg');

                    if (start != -1 && end != -1) {
                        __icon = _.backgroundImage.substring(start + 1, end);
                    }
                }

                wt_presets[id] = scale;

                var data = [
                    'id=' + id,
                    '__icon=' + (__icon || '')
                ];

                n = 0;
                var scaStr = ''
                for (var w in scale) {
                    if (!LANG.traits[w]) {
                        continue;
                    }

                    if (n++ > 0) {
                        scaStr += ',';
                    }

                    scaStr += w + ':' + scale[w];
                }
                data.push('scale=' + scaStr);

                $('#status-ic').append(CreateAjaxLoader());

                new Ajax('?admin=weight-presets&action=save', {
                    method: 'post',
                    params: data.join('&'),
                    onSuccess: function(xhr, opt) {
                        switch (parseInt(xhr.responseText)) {
                            case 0:
                                createStatusIcon();
                                break;
                            case 1:
                                createStatusIcon('could not write to DB');
                                break;
                            case 2:
                                createStatusIcon('could not create file: datasets/weight-presets');
                                break;
                            default:
                                createStatusIcon('an unknown error occured');
                        }
                    }
                });
            },

            restorScale: function() {
                if (!_curWT)
                {
                    createStatusIcon('no scale selected');
                    return;
                }

                loadScale(_curWT, true);
            }
        };

        SummaryAdmin.traits = [
            { id: 'sepbasestats', type: 'sep' },
            { id: 'agi', type: 'sum' },
            { id: 'int', type: 'sum' },
            { id: 'sta', type: 'sum' },
            { id: 'spi', type: 'sum' },
            { id: 'str', type: 'sum' },
            { id: 'health', type: 'sum' },
            { id: 'mana', type: 'sum' },
            { id: 'healthrgn', type: 'sum' },
            { id: 'manargn', type: 'sum' },

            { id: 'sepdefensivestats', type: 'sep' },
            { id: 'armor', type: 'sum' },
            { id: 'blockrtng', type: 'sum', rating: 15 },
            { id: 'block', type: 'sum' },
            { id: 'defrtng', type: 'sum', rating: 12 },
            { id: 'dodgertng', type: 'sum', rating: 13 },
            { id: 'parryrtng', type: 'sum', rating: 14 },
            { id: 'resirtng', type: 'sum', rating: 35 },

            { id: 'sepoffensivestats', type: 'sep' },
            { id: 'atkpwr', type: 'sum' },
            { id: 'feratkpwr', type: 'sum', indent: 1 },
            { id: 'armorpenrtng', type: 'sum', rating: 44 },
            { id: 'critstrkrtng', type: 'sum', rating: 32 },
            { id: 'exprtng', type: 'sum', rating: 37 },
            { id: 'hastertng', type: 'sum', rating: 36 },
            { id: 'hitrtng', type: 'sum', rating: 31 },
            { id: 'splpen', type: 'sum' },
            { id: 'splpwr', type: 'sum' },
            { id: 'arcsplpwr', type: 'sum', indent: 1 },
            { id: 'firsplpwr', type: 'sum', indent: 1 },
            { id: 'frosplpwr', type: 'sum', indent: 1 },
            { id: 'holsplpwr', type: 'sum', indent: 1 },
            { id: 'natsplpwr', type: 'sum', indent: 1 },
            { id: 'shasplpwr', type: 'sum', indent: 1 },

            { id: 'sepweaponstats', type: 'sep' },

            { id: 'dmg', type: 'sum' },
            { id: 'mledps', type: 'sum' },
            { id: 'rgddps', type: 'sum' },

            { id: 'mledmgmin', type: 'sum' },
            { id: 'rgddmgmin', type: 'sum' },

            { id: 'mledmgmax', type: 'sum' },
            { id: 'rgddmgmax', type: 'sum' },

            { id: 'mlespeed', type: 'avg' },
            { id: 'rgdspeed', type: 'avg' },

            { id: 'sepresistances', type: 'sep' },
            { id: 'arcres', type: 'sum' },
            { id: 'firres', type: 'sum' },
            { id: 'frores', type: 'sum' },
            { id: 'holres', type: 'sum' },
            { id: 'natres', type: 'sum' },
            { id: 'shares', type: 'sum' },

            { id: 'sepindividualstats', type: 'sep' },
            { id: 'mleatkpwr', type: 'sum' },
            { id: 'mlecritstrkrtng', type: 'sum', rating: 19 },
            { id: 'mlehastertng', type: 'sum', rating: 28 },
            { id: 'mlehitrtng', type: 'sum', rating: 16 },
            { id: 'rgdatkpwr', type: 'sum' },
            { id: 'rgdcritstrkrtng', type: 'sum', rating: 20 },
            { id: 'rgdhastertng', type: 'sum', rating: 29 },
            { id: 'rgdhitrtng', type: 'sum', rating: 17 },
            { id: 'splcritstrkrtng', type: 'sum', rating: 21 },
            { id: 'splhastertng', type: 'sum', rating: 30 },
            { id: 'splhitrtng', type: 'sum', rating: 18 },
            { id: 'spldmg', type: 'sum' },
            { id: 'splheal', type: 'sum' },

            { id: '', name: LANG.sockets, type: 'sep' },
            { id: 'nsockets', type: 'sum' },
        ];

        function createStatusIcon(errTxt)
        {
            _status = $WH.ge('status-ic');

            $WH.ee(_status);

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
            setTimeout(fadeout.bind(a), 5000);

            $WH.ae(_status, a);
        }

        function loadScale(wtId, restore)
        {
            if (!wtId)
                return;

            src = restore ? wt_backup : wt_presets;

            if (!restore)
                _nameSp.innerHTML = LANG.colon + this.innerHTML;

            _iconInp.value = src[wtId].__icon;

            _curWT = wtId;

            SummaryAdmin.resetWeights();
            SummaryAdmin.updateWeights(src[wtId]);
            updateIcon();
        }

        function updateIcon()
        {
            Icon.setTexture(_iconDiv.firstChild, 2, _iconInp.value.trim());
        }

        var
            _iconDiv,
            _iconInp,
            _nameSp,
            _suDiv,
            _ctrlDiv,
            _curWT    = null,
            wt_backup = {};

        $(document).ready(function()
        {
            _iconDiv = $WH.ge('ic-container');
            _iconInp = $WH.ge('wt-icon');
            _nameSp  = $WH.ge('wt-name');
            _suDiv   = $WH.ge('su-container');
            _ctrlDiv = $WH.ge('su-controls');

            _iconDiv.appendChild(Icon.create('inv_misc_questionmark', 2));

            $WH.cO(wt_backup, wt_presets);

            $('#text-generic td a').each(function(_, el) {
                el.innerHTML = LANG.presets[el.text];
            });

            _iconInp.onchange = _iconInp.onkeyup = updateIcon.bind();

            SummaryAdmin.updateControls();
        });
    </script>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate');
?>
            <div class="text">
                <h1><?=$this->name;?></h1>

<?php
    $this->brick('article');

    if (isset($this->extraText)):
?>
                <div id="text-generic" class="left"></div>
                <script type="text/javascript">//<![CDATA[
                    Markup.printHtml("<?=Util::jsEscape($this->extraText);?>", "text-generic", {
                        allow: Markup.CLASS_ADMIN,
                        dbpage: true
                    });
                //]]></script>

                <div class="pad2"></div>
<?php
    endif;

    if (isset($this->extraHTML)):
        echo $this->extraHTML;
    endif;
?>
                <h2>Edit<span id="wt-name"></span><span id="status-ic" style="float:right;"></span></h2>
                <div class="wt-edit">
                    <div style="display:inline-block; vertical-align:top;"><div class="pad2" style="color: white; font-size: 15px; font-weight: bold;">Icon</div><input type="text" id="wt-icon" size="30" /></div>
                    <div id="ic-container" style="display: inline-block; clear: left;"></div>
                </div>
                <div class="wt-edit">
                    <div class="pad2" style="color: white; font-size: 15px; font-weight: bold;">Scale</div>
                    <div id="su-container"></div>
                </div>
                <div id="su-controls" class="wt-edit" style="width:auto;"></div>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
