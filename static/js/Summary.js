var g_summaries = {};
var fi_type = 'items';

function Summary(opt) {
    $WH.cO(this, opt);

    if (this.id) {
        var divId = this.id;
        if (this.parent) {
            var d = $WH.ce('div');
            d.id = divId;
            $WH.ae($WH.ge(this.parent), d);
            this.container = d;
        }
        else {
            this.container = $WH.ge(divId);
        }
    }
    else {
        return;
    }

    if (this.template) {
        this.templateName = this.template;
        this.template = Summary.templates[this.template];
    }
    else {
        return;
    }

    g_summaries[this.id] = this;

    if (this.editable == null) {
        if (this.template.editable != null) {
            this.editable = this.template.editable;
        }
        else {
            this.editable = 1;
        }
    }

    if (this.draggable == null) {
        if (this.template.draggable != null) {
            this.draggable = this.template.draggable;
        }
        else {
            this.draggable = this.editable;
        }
    }

    if (this.searchable == null) {
        if (this.template.searchable != null) {
            this.searchable = this.template.searchable;
        }
        else {
            this.searchable = (this.editable && this.draggable);
        }
    }

    if (this.weightable == null) {
        if (this.template.weightable != null) {
            this.weightable = this.template.weightable;
        }
        else {
            this.weightable = this.editable;
        }
    }

    if (this.textable == null) {
        if (this.template.textable != null) {
            this.textable = this.template.textable;
        }
        else {
            this.textable = 0;
        }
    }

    if (this.enhanceable == null) {
        if (this.template.enhanceable != null) {
            this.enhanceable = this.template.enhanceable;
        }
        else {
            this.enhanceable = this.editable;
        }
    }

    if (this.level == null) {
        if (this.template.level != null) {
            this.level = this.template.level;
        }
        else {
            this.level = 80;
        }
    }

    var GET = $WH.g_getGets();
    /* aowow: GET.compare => GET.items, when using flat urls */

    this.autoSave = 1;
    if (GET.compare) {
        this.autoSave = 0;
    }

    if (GET.l && !isNaN(GET.l)) {
        this.level = parseInt(GET.l);
    }
    else {
        var l = g_getWowheadCookie('compare_level');
        if (l) {
            this.level = l;
        }
    }

    if (this.groups == null) {
        this.groups = [];

        if (GET.compare) {
            this.readGroups(GET.compare);
        }
        else {
            this.readGroups(g_getWowheadCookie('compare_groups'));
        }
    }

    if (this.weights == null) {
        this.weights = [];

        if (this.weightable) {
            if (GET.compare) {
                this.readWeights(GET.weights);
            }
            else {
                this.readWeights(g_getWowheadCookie('compare_weights'));
            }
        }
    }

    this.showTotal = 0;

    if (this.template.total != null) {
        this.total = this.template.total;
        this.showTotal = this.template.columns[this.total].hidden;
    }

    if (this.template.clone != null) {
        this.clone = {};
        $WH.cO(this.clone, this.template.columns[this.template.clone]);
        this.clone.i = this.template.clone;
    }

    if (GET.compare && GET.focus != null) {
        var f = parseInt(GET.focus) | 0;
        if (f >= 0 && f < this.groups.length) {
            this.selected = this.clone.i + f;
        }
    }

    // aowow - data=item-scaling load is delayed from basic.js but required for heirlooms
    // this.initialize();
    setTimeout(this.initialize.bind(this), 250);
}

Summary.prototype = {
    initialize: function() {
        this.controls = $WH.ce('div');
        this.controls.style.display = 'none';
        this.controls.className = 'summary-controls';

        this.textNames = $WH.ce('div');
        this.textNames.style.display = 'none';
        this.textNames.className = 'summary-textnames';

        var result;
        if (this.onBeforeCreate != null) {
            result = this.onBeforeCreate();
        }

        this.updateGroups();
        this.updateTraits();
        this.updateColumns();
        this.updateVisibility();

        if (this.traits.length == 0) {
            this.visibility.shift();
            this.visibility.shift();
        }

        this.noData = $WH.ce('div');
        this.noData.className = 'listview-nodata text';

        this.table = $WH.ce('table');
        this.thead = $WH.ce('thead');
        this.tbody = $WH.ce('tbody');

        this.table.id = 'su_table';
        this.table.className = 'grid';

        this.refreshHeader();

        $WH.ae(this.table, this.thead);
        $WH.ae(this.table, this.tbody);

        this.refreshRows();

        $WH.ae(this.container, this.controls);
        $WH.ae(this.container, this.textNames);
        $WH.ae(this.container, this.table);

        this.itemTip = $WH.ce('div');
        this.itemTip.className = 'summary-tip';
        this.itemTip.style.display = 'none';
        var span = $WH.ce('span');
        span.innerHTML = LANG.su_itemtip;
        $WH.ae(this.itemTip, span);
        $WH.ae(this.container, this.itemTip);

        this.refreshLink();

        if (this.onAfterCreate != null) {
            this.onAfterCreate(result);
        }

        if (this.templateName == 'compare') {
            ModelViewer.addExtraPound(this.viewIn3dFromPound.bind(this));
        }
    },

    updateGroups: function() {
        for (var i = 0, len = this.groups.length; i < len; ++i) {
            for (var j = 0, len2 = this.groups[i].length; j < len2; ++j) {
                if (this.groups[i][j][0] == 0 || (len2 > 1 && this.groups[i][j][0] == -1) || !g_items[this.groups[i][j][0]]) {
                    this.groups[i].splice(j, 1);
                    --j;
                    --len2;
                }
            }

            if (len2 == 0) { // Remove empty group
                if (this.selected != null) { // Deselect if selected
                    if (this.__selected.i == i) {
                        this.selected = null;
                    }
                }

                this.groups.splice(i, 1);
                --i;
                --len;
            }
        }

        if (this.editable) {
            this.updateControls();
        }
    },

    updateTraits: function() {
        this.traits = [];

        var
            setBonus,
            sep = false,
            dataz = [];

        // Get the items, enchants, and gems used by the summary
        for (var i = 0, len = this.groups.length; i < len; ++i) {
            for (var j = 0, len2 = this.groups[i].length; j < len2; ++j) {
                var item = this.groups[i][j];

                if (g_items[item[0]]) {
                    dataz.push(g_items[item[0]].jsonequip);

                    if (!this.enhanceable) {
                        continue;
                    }

                    // Subitems
                    if (g_items[item[0]].jsonequip.subitems && g_items[item[0]].jsonequip.subitems[item[1]]) {
                        dataz.push(g_items[item[0]].jsonequip.subitems[item[1]].jsonequip);
                    }

                    // Perm Enchant
                    if (g_enchants[item[2]]) {
                        dataz.push(g_enchants[item[2]].jsonequip);
                    }

                    // Temp Enchant
                    if (g_enchants[item[3]]) {
                        dataz.push(g_enchants[item[3]].jsonequip);
                    }

                    // Gems
                    for (var k = 4; k < 8; ++k) {
                        if (g_gems[item[k]]) {
                            dataz.push(g_gems[item[k]].jsonequip);
                        }
                    }

                    // Extra sockets
                    if (this.hasExtraSocket(item)) {
                        dataz.push({ nsockets: 1 });
                    }

                    // Socket bonuses
                    if (this.hasSocketBonus(item)) {
                        dataz.push(g_items[item[0]].jsonequip.socketbonusstat);
                    }
                }
            }

            if (setBonus = this.getSetBonuses(this.groups[i])) {
                dataz.push(setBonus);
            }
        }

        // Find the traits used by those items
        for (var i = 0, len = this.template.traits.length; i < len; ++i) {
            var trait = this.template.traits[i];

            if (trait.type == 'sep') {
                sep = true;
                continue;
            }

            var _ = [trait.id];
            if (trait.json) {
                var _ = trait.json;
            }

            var found = false;

            for (var j = 0, len2 = dataz.length; j < len2; ++j) {
                for (var k = 0, len3 = _.length; k < len3; ++k) {
                    if (dataz[j] && dataz[j][_[k]]
                        && (trait.id != 'gains' || (this.editable && this.groups.length > 1)) // Special case for 'Gains'
                        && (trait.id != 'score' || this.weights.length)) { // Special case for 'Score'
                        if (sep) {
                            this.traits.push({});
                        }
                        sep = false;

                        this.traits.push(trait);
                        found = true;
                        break;
                    }
                }

                if (found) {
                    break;
                }
            }
        }
    },

    updateColumns: function() {
        this.columns = this.template.columns.slice(0);
        if (this.template.newgroup != null) {
            this.newgroup = this.template.newgroup;
        }

        if (this.total != null) {
            this.__total = this.columns[this.total];
        }
        else {
            this.__total = {};
        }

        this.__total.total = {};
        this.minValue = {};
        this.maxValue = {};
        this.ratings = {};

        if (this.clone != null) {
            this.columns.splice(this.clone.i, 1);

            var k = 0;
            for (var i = 0, len = this.groups.length; i < len; ++i) {
                if (this.groups[i].length < 1) {
                    continue;
                }

                if (k > 0 && this.newgroup != null) {
                    this.newgroup++;
                }

                var _ = $WH.dO(this.clone);

                _.id    = 'group' + i;
                _.group = this.groups[i];
                _.i     = i;

                this.columns.splice(this.clone.i + i, 0, _);

                k++;
            }

            for (var i = 0, len = this.columns.length; i < len; ++i) {
                this.calcTraitTotals(this.columns[i], this.__total, this.level);
            }

            if (this.selected != null) {
                this.__selected = this.columns[this.selected];
                for (var i = 0, len = this.groups.length; i < len; ++i) {
                    if (this.clone.i + i == this.selected) {
                        continue;
                    }

                    this.calcTraitDifference(this.columns[this.clone.i + i], this.__selected);
                }
            }
            else {
                this.__selected = {};
            }

            if (this.total != null && (k == 1 || this.selected != null)) {
                this.__total.hidden = 1;
            }
            else {
                this.__total.hidden = this.showTotal;
            }
        }
    },

    updateVisibility: function() {
        this.visibility = [];

        var
            visibleCols = [],
            hiddenCols = [];

        if (this.visibleCols != null) {
            $WH.array_walk(this.visibleCols, function(x) { visibleCols[x] = 1; });
        }

        if (this.hiddenCols != null) {
            $WH.array_walk(this.hiddenCols, function(x) { hiddenCols[x] = 1; });
        }

        for (var i = 0, len = this.columns.length; i < len; ++i) {
            var col = this.columns[i];
            if (visibleCols[col.id] != null || (!col.hidden && hiddenCols[col.id] == null)) {
                this.visibility.push(i);
            }
        }

        if (this.groups.length > 1 && this.textable) {
            this.showTextNames();
        }
        else {
            this.textNames.style.display = 'none';
        }
    },

    updateDraggable: function() {
        for (var i = 0, len = this.dragHeaders.length; i < len; ++i) {
            var _ = this.dragHeaders[i];

            _._targets = this.dragTargets;
            Draggable.init(_, {
                container: this.table,
                onDrag: Summary.dragGroup.bind(this),
                onDrop: Summary.moveGroup.bind(this)
            });
        }

        for (var i = 0, len = this.dragIcons.length; i < len; ++i) {
            var _ = this.dragIcons[i];

            _._targets = this.dragTargets;
            Draggable.init(_, {
                container: this.table,
                onDrop: Summary.moveGroupItem.bind(this)
            });
        }
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

    showDetails: function(a) {
        var foo = $WH.ge('su_weight');

        g_toggleDisplay(foo);

        foo = foo.nextSibling;

        a.firstChild.nodeValue = g_toggleDisplay(foo) ? LANG.fihidedetails : LANG.fishowdetails;
    },

    saveScale: function() {
        if (!g_user.id) {
            return;
        }

        var
            opts = $WH.gE($WH.ge('su_presets'), 'option'),
            o;

        for (i in opts) {
            if (opts[i].selected) {
                o = opts[i];
                break;
            }
        }

        var
            name = $WH.ge('su_scale').value,
            id    = ((o._weights ? o._weights.id : 0) | 0),
            scale = { id: id, name: name },
            _     = $WH.ge('su_weight'),
            n     = 0;

        for (i = 0; i < _.childNodes.length; ++i) {
            var
                w    = fi_Lookup($WH.gE(_.childNodes[i], 'select')[0].value),
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

        if (!name || n < 1 || (!(o._weights && o._weights.id) && g_user.weightscales && g_user.weightscales.length >= 5)) {
            return alert(LANG.message_weightscalesaveerror);
        }

        var data = [ 'save=1', 'name=' + $WH.urlencode(name) ];

        if (id) {
            data.push('id=' + id);
        }

        scale.name = name;
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

        new Ajax('?account=weightscales', {
            method: 'post',
            params: data.join('&'),
            onSuccess: function(xhr, opt) {
                var response = parseInt(xhr.responseText);

                if (response > 0) {
                    if (g_user.weightscales == null) {
                        g_user.weightscales = [];
                    }

                    if (scale.id) {
                        g_user.weightscales = $WH.array_filter(g_user.weightscales, function(x) {
                            return x.id != scale.id;
                        });
                    }

                    scale.id = response;
                    g_user.weightscales.push(scale);

                    var
                        s    = $WH.ge('su_classes'),
                        opts = $WH.gE(s, 'option'),
                        c;

                    for (i in opts) {
                        if (opts[i].value == -1) {
                            c = opts[i];
                            break;
                        }
                    }

                    if (!c) {
                        c = $WH.ce('option');
                        c.value = -1;
                        $WH.ae(c, $WH.ct(LANG.ficustom));
                        $WH.aef(s, c);
                        $WH.aef(s, $WH.gE(s, 'option')[1]);
                    }

                    c._presets = { custom: {} };
                    for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                        c._presets.custom[g_user.weightscales[i].id] = g_user.weightscales[i];
                    }

                    c.selected = true;
                    c.parentNode.onchange();

                    var
                        opts = $WH.gE($WH.ge('su_presets'), 'option'),
                        o;

                    for (i in opts) {
                        if (opts[i].value == scale.id) {
                            o = opts[i];
                            break;
                        }
                    }

                    if (o) {
                        o.text = scale.name;
                        o.selected = true;
                        o.parentNode.onchange();
                    }

                    alert(LANG.message_saveok);
                }
                else {
                    alert(LANG.message_weightscalesaveerror);
                }
            }
        });
    },

    deleteScale: function() {
        if (!g_user.id) {
            return;
        }

        var
            s    = $WH.ge('su_classes'),
            opts = $WH.gE(s, 'option'),
            c;

        for (i in opts) {
            if (opts[i].selected) {
                c = opts[i];
                break;
            }
        }

        if (c.value == -1) {
            var
                opts = $WH.gE($WH.ge('su_presets'), 'option'),
                o;

            for (i in opts) {
                if (opts[i].selected) {
                    o = opts[i];
                    break;
                }
            }

            if (o.value && confirm(LANG.confirm_deleteweightscale)) {
                new Ajax('?account=weightscales', {
                    method: 'post',
                    params: 'delete=1&id=' + o.value
                });

                g_user.weightscales = $WH.array_filter(g_user.weightscales, function(x) {
                    return x.id != o.value;
                });

                if (g_user.weightscales.length) {
                    c._presets = { custom: {} };
                    for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                        c._presets.custom[g_user.weightscales[i].id] = g_user.weightscales[i];
                    }
                }
                else {
                    $WH.de(c);
                }

                $WH.de(o);

                $('#su_classes, #su_presets').change();
            }
        }
    },

    calcTraitTotals: function(col, tot, level) {
        if (!col.group) {
            return;
        }

        col.total      = {};
        col.difference = {};

        var traitFuncs = {
            sum:    function(x, y) { return (!isNaN(x) ? x : 0) + y; },
            min:    function(x, y) { return Math.min((!isNaN(x) ? x : y), y); },
            max:    function(x, y) { return Math.max((!isNaN(x) ? x : 0), y); },
            avg:    function(x, y, n) { return (!isNaN(x) ? x : 0) + (y / n); },
            text:   function(x, y, n, e, f, t, s) {
                if (f) {
                    f = f.bind(this);
                    y = f(y, t, e, s);
                }

                return ($WH.in_array(x, y) < 0 ? y : null);
            },
            custom: function(x, y, n, e, f, t, s) {
                if (f) {
                    f = f.bind(this);
                    f(y, x, t, e, s);
                }

                return x;
            }
        };

        var checkTrait = function(json, tj, trait, col, f, c, e, s) {
            var _json = $WH.dO(json);
            if (_json.scadist && _json.scaflags) {
                $WH.g_setJsonItemLevel(_json, level);
            }

            var
                v = (trait.type == 'text' || trait.type == 'custom' ? _json : (_json[tj] && typeof _json[tj] != 'object' ? _json[tj] : 0));

            if (tj == 'nsockets' && s) {
                v += s;
            }

            if (!v) {
                return;
            }

            var
                cT = f(col.total[trait.id], v, col.group.length, e, c, 0, s),
                tT = f(tot.total[trait.id], v, col.group.length, e, c, 1, s);

            if (trait.type == 'text') {
                if (cT) {
                    col.total[trait.id].push(cT);
                }
                if (tT) {
                    tot.total[trait.id].push(tT);
                }
            }
            else {
                col.total[trait.id] = cT;
                tot.total[trait.id] = tT;
            }
        };

        for (var trait in this.traits) {
            trait = this.traits[trait];

            if (trait.id && trait.type) {
                var
                    setBonus,
                    f = traitFuncs[trait.type].bind(this),
                    c = trait.calcTotal;

                var _ = [trait.id];
                if (trait.json) {
                    _ = trait.json;
                }

                if (col.total[trait.id] == null) {
                    if (trait.type == 'text') {
                        col.total[trait.id] = [];
                    }
                    else if (trait.type == 'custom') {
                        col.total[trait.id] = {};
                    }
                }

                if (tot.total[trait.id] == null) {
                    if (trait.type == 'text') {
                        tot.total[trait.id] = [];
                    }
                    else if (trait.type == 'custom') {
                        tot.total[trait.id] = {};
                    }
                }

                for (var i = 0, len = col.group.length; i < len; ++i) {
                    var extraSocket = this.hasExtraSocket(col.group[i]);

                    for (var j = 0, len2 = _.length; j < len2; ++j) {
                        if (g_items[col.group[i][0]]) {
                            if (g_items[col.group[i][0]]) {
                                checkTrait(g_items[col.group[i][0]].jsonequip, _[j], trait, col, f, c, col.group[i], extraSocket);

                                if (!this.enhanceable || trait.itemonly) {
                                    continue;
                                }

                                if (g_items[col.group[i][0]].jsonequip.subitems && g_items[col.group[i][0]].jsonequip.subitems[col.group[i][1]]) {
                                    checkTrait(g_items[col.group[i][0]].jsonequip.subitems[col.group[i][1]].jsonequip, _[j], trait, col, f, c, col.group[i]);
                                }

                                if (g_enchants[col.group[i][2]]) {
                                    checkTrait(g_enchants[col.group[i][2]].jsonequip, _[j], trait, col, f, c, col.group[i]);
                                }

                                if (g_enchants[col.group[i][3]]) {
                                    checkTrait(g_enchants[col.group[i][3]].jsonequip, _[j], trait, col, f, c, col.group[i]);
                                }

                                for (var k = 4; k < 8; ++k) {
                                    if (g_gems[col.group[i][k]]) {
                                        checkTrait(g_gems[col.group[i][k]].jsonequip, _[j], trait, col, f, c, col.group[i]);
                                    }
                                }

                                if (this.hasSocketBonus(col.group[i])) {
                                    checkTrait(g_items[col.group[i][0]].jsonequip.socketbonusstat, _[j], trait, col, f, c, col.group[i]);
                                }
                            }
                        }
                    }
                }

                if (setBonus = this.getSetBonuses(col.group)) {
                    for (var j = 0, len2 = _.length; j < len2; ++j) {
                        checkTrait(setBonus, _[j], trait, col, f, c, []);
                    }
                }

                if (trait.rating) {
                    this.ratings[trait.id] = trait.rating;
                }

                if (trait.type == 'sum') {
                    if (col.total[trait.id] == null) {
                        this.minValue[trait.id] = 0;
                    }
                    else if (this.minValue[trait.id] == null || this.minValue[trait.id] > col.total[trait.id]) {
                            this.minValue[trait.id] = col.total[trait.id];
                    }
                    if (this.maxValue[trait.id] == null || this.maxValue[trait.id] < col.total[trait.id]) {
                        this.maxValue[trait.id] = col.total[trait.id];
                    }
                }
                else if (trait.type == 'custom') {
                    if (this.minValue[trait.id] == null) {
                        this.minValue[trait.id] = {};
                    }
                    if (this.maxValue[trait.id] == null) {
                        this.maxValue[trait.id] = {};
                    }

                    for (var t in col.total[trait.id]) {
                        if (this.minValue[trait.id][t] == null || this.minValue[trait.id][t] > col.total[trait.id][t]) {
                            this.minValue[trait.id][t] = col.total[trait.id][t];
                        }
                        if (this.maxValue[trait.id][t] == null || this.maxValue[trait.id][t] < col.total[trait.id][t]) {
                            this.maxValue[trait.id][t] = col.total[trait.id][t];
                        }
                    }
                }
            }
        }
    },

    calcTraitDifference: function(col, sel) {
        col.difference = {};

        var traitFuncs = {
            sum:    function(x, y)    { return (!isNaN(y) ? y : 0) - (!isNaN(x) ? x : 0); },
            min:    function(x, y)    { return (!isNaN(y) ? y : 0) - (!isNaN(x) ? x : 0); },
            max:    function(x, y)    { return (!isNaN(y) ? y : 0) - (!isNaN(x) ? x : 0); },
            avg:    function(x, y)    { return (!isNaN(y) ? y : 0) - (!isNaN(x) ? x : 0); },
            text:   function(x, y)    {
                var a = [];
                for (var i = 0, len = y.length; i < len; ++i) {
                    if ($WH.in_array(x, y[i]) == -1) {
                        var _ = $WH.ce('span');
                        _.className = 'q2';
                        $WH.ae(_, $WH.ct('+' + y[i]));
                        a.push(_);
                    }
                }
                for (var i = 0, len = x.length; i < len; ++i) {
                    if ($WH.in_array(y, x[i]) == -1) {
                        var _ = $WH.ce('span');

                        _.className = 'q10';
                        $WH.ae(_, $WH.ct('-' + x[i]));
                        a.push(_);
                    }
                }
                return a;
            },
            custom: function(x, y, f) {
                if (f) {
                    f = f.bind(this);
                    return f(y, x);
                }

                return {};
            }
        };

        for (var trait in this.traits) {
            trait = this.traits[trait];

            if (trait.id && trait.type) {
                var
                    f = traitFuncs[trait.type].bind(this),
                    c = trait.calcDifference;

                col.difference[trait.id] = f(sel.total[trait.id], col.total[trait.id], c);
            }
        }
    },

    resetAll: function() {
        this.groups = [];
        this.weights = [];
        this.refreshAll();
    },

    refreshAll: function() {
        this.updateGroups();
        this.updateTraits();
        this.updateColumns();
        this.updateVisibility();
        this.refreshHeader();
        this.refreshRows();
        this.refreshLink();
        this.refreshSort();

        if (this.autoSave) {
            this.saveComparison(0);
        }
        else if (this.editable && !$WH.Browser.ie) {
            window.onbeforeunload = function(e) { return LANG.message_savebeforeexit };
        }
    },

    refreshHeader: function() {
        if (this.groups.length && this.thead.nodeName.toLowerCase() != 'thead') {
            this.thead = $WH.ce('thead');
            $WH.ae(this.table, this.thead);
        }

        while (this.thead.firstChild) {
            this.thead.removeChild(this.thead.firstChild);
        }

        this.dragHeaders = [];
        this.dragIcons = [];
        this.dragTargets = [];

        this.selectLevel = $WH.ce('div');
        this.selectLevel.style.display = 'none';

        var tr = $WH.ce('tr');

        if (!this.groups.length) {
            var th = $WH.ce('th');
            $WH.ae(tr, th);
            $WH.ae(this.thead, tr);
            this.showNoData(th);
            return;
        }

        var groupi = 0;
        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var reali = this.visibility[i];
            var col = this.columns[reali];

            var th = $WH.ce('th');
            if (col.id == 'total') {
                th.style.verticalAlign = 'middle';
            }
            else if (col.id == 'name') {
                th.style.verticalAlign = 'bottom';
            }
            else {
                    th.style.verticalAlign = 'top';
            }

            col.__th = th;
            th.__col = col;

            if (col.width != null) {
                th.style.width = col.width;
            }
            if (col.align != null) {
                th.style.textAlign = col.align;
            }
            if (col.span != null) {
                th.colSpan = col.span;
            }

            if (this.selected != null && this.__selected.id == col.id) {
                th.className = 'checked';
            }

            if (col.id == 'name') {
                $WH.ae(th, this.selectLevel);

                var s = $WH.ce('select');
                s.onchange = (function(s) {
                    this.level = s.options[s.selectedIndex].value;
                    this.refreshAll();
                }).bind(this, s);

                for (var i = 80; i > 0; --i) {
                    var o = $WH.ce('option');
                    if (i == this.level) {
                        o.selected = true;
                    }
                    $WH.st(o, i);
                    $WH.ae(s, o);
                }

                $WH.ae(this.selectLevel, $WH.ct(LANG.su_level + ' '));
                $WH.ae(this.selectLevel, s);
            }

            if (col.group) {
                this.dragTargets.push(th);

                if (this.editable) {
                    var _ = $WH.ce('div');
                    _.className = 'summary-group';

                    div = $WH.ce('div');
                    div.className = 'summary-group-controls';

                    var a = $WH.ce('a');
                    a.href = 'javascript:;';
                    a.className = 'summary-group-dropdown';
                    $WH.ae(a, $WH.ce('span'));
                    a.menu = [
                        [0, LANG.su_export, '?compare=' + this.getGroupData(groupi), null, {newWindow: true}]
                    ];

                    if (this.viewableIn3d(col)) {
                        a.menu.push([0, LANG.su_viewin3d, this.viewIn3d.bind(this, col)]);
                    }

                    if (col.group.length > 1) {
                        a.menu.push([0, LANG.su_split, this.splitColumn.bind(this, col)]);
                    }

                    if (this.enhanceable && this.hasEnhancements(col.group)) {
                        a.menu.push([0, LANG.pr_menu_clearenh, this.clearEnhancements.bind(this, col)]);
                    }

                    Menu.add(a, a.menu, { showAtCursor: true });
                    $WH.ae(div, a);

                    if (this.groups.length > 1) {
                        a = $WH.ce('a');
                        a.href = 'javascript:;';
                        a.className = 'summary-group-focus';
                        var s = $WH.ce('span');
                        a.onclick = this.selectColumn.bind(this, col);
                        if (this.selected && this.__selected.id == col.id) {
                            a.onmouseover = function() {
                                $WH.Tooltip.show(this, LANG.tooltip_removefocus, 0, 0, 'q');
                            };
                            s.className = 'selected';
                        }
                        else {
                            a.onmouseover = function() {
                                $WH.Tooltip.show(this, LANG.tooltip_setfocus, 0, 0, 'q');
                            };
                        }
                        a.onmousemove = $WH.Tooltip.cursorUpdate;
                        a.onmouseout = $WH.Tooltip.hide;
                        $WH.ae(a, s);
                        $WH.ae(div, a);
                    }

                    a = $WH.ce('a');
                    a.href = 'javascript:;';
                    a.className = 'summary-group-delete';
                    a.onclick = this.deleteColumn.bind(this, col);
                    $WH.ae(a, $WH.ce('span'));
                    $WH.ae(div, a);

                    if (this.draggable) {
                        a = $WH.ce('a');
                        a.href = 'javascript:;';
                        a.className = 'summary-group-drag';
                        $WH.ae(a, $WH.ce('span'));
                        $WH.ae(div, a);

                        _.__col = col;
                        _._handle = a;
                        this.dragHeaders.push(_);
                    }

                    $WH.ae(_, div);
                    $WH.ae(th, _);

                    if (this.draggable && this.groups.length > 1) {
                        // Spacer needed to fix the wrapping issues in the bar
                        _.style.minWidth = '80px';
                    }
                }

                var
                    len2 = col.group.length,
                    iconSize = 1,
                    iconPerRow = 3,
                    iconWidth = 44;

                if (len2 > 3) {
                    iconSize = 0;
                    iconPerRow = 5;
                    iconWidth = 26;
                }

                var _ = $WH.ce('div');
                _.style.margin = '0 auto';
                _.style.clear = 'both';

                var
                    btm = $WH.ce('div'),
                    div = _.cloneNode(true);

                if (this.editable) {
                    btm.className = 'summary-group-bottom';
                }

                for (var j = 0; j < len2; ++j) {
                    if (j % iconPerRow == 0) {
                        div.style.width = (iconWidth * iconPerRow) + 'px';
                        div = _.cloneNode(true);
                        $WH.ae(btm, div);
                    }

                    var icon = g_items.createIcon(col.group[j][0], iconSize);

                    icon.__col = col;
                    icon.i = j;

                    if (this.enhanceable) {
                        this.refreshItem(col, j, Icon.getLink(icon));
                    }

                    this.dragIcons.push(icon);

                    $WH.ae(div, icon);
                }

                if (col.group.length) {
                    div.style.width = (iconWidth * (len2 % iconPerRow == 0 ? iconPerRow : (len2 % iconPerRow))) + 'px';
                }
                $WH.ae(btm, div);

                if (this.editable) {
                    th.style.padding = '0';

                    div = $WH.ce('div');
                    div.className = 'clear';
                    $WH.ae(btm, div);

                    div = $WH.ce('div');
                    div.className = 'clear';
                    $WH.ae(th.firstChild, div);

                    $WH.ae(th.firstChild, btm);
                }
                else {
                    $WH.ae(th, btm);
                }

                groupi++;
            }
            else if (col.name) {
                var b = $WH.ce('b');
                $WH.ae(b, $WH.ct(col.name));
                $WH.ae(th, b);
            }

            if (this.editable && reali == this.newgroup) {
                this.dragTargets.push(th);
            }

            $WH.ae(tr, th);
        }
        $WH.ae(this.thead, tr);

        if (this.draggable) {
            this.updateDraggable();
        }
    },

    refreshItem: function(col, i, a) {
        var
            item = col.group[i],
            itemId = item[0],
            menu = [],

            extraSocket = this.hasExtraSocket(item);

        // Random Enchant
        if (g_items[itemId].jsonequip.subitems != null) {
            menu.push([0, (item[1] ? LANG.pr_menu_repsubitem : LANG.pr_menu_addsubitem), this.openSubitemPicker.bind(this, col, i)]);
        }

        // Enchant
        if (this.canBeEnchanted(g_items[itemId].jsonequip.slotbak, g_items[itemId].jsonequip.subclass)) {
            var _ = [0, (item[2] ? LANG.pr_menu_repenchant : LANG.pr_menu_addenchant), this.openEnchantPicker.bind(this, col, i), null, {}];

            if (item[2] && g_enchants[item[2]]) {
                _[4].tinyIcon = g_enchants[item[2]].icon;
            }

            menu.push(_);
        }

        // Gems
        if (g_items[itemId].jsonequip.nsockets || extraSocket) {
            for (var k = 0, len3 = (g_items[itemId].jsonequip.nsockets | 0) + extraSocket; k < len3; ++k) {
                var
                    gemId = (item[k + 4] > 0 ? item[k + 4] : 0),
                    c     = (extraSocket && k == len3 - 1 ? 14 : g_items[itemId].jsonequip['socket' + (k + 1)]),
                    _     = [0, (gemId ? LANG.pr_menu_repgem : LANG.pr_menu_addgem), this.openGemPicker.bind(this, col, i, 4 + k, c), null, {}];

                if (gemId) {
                    _[4].tinyIcon = g_gems[gemId].icon;
                }
                else {
                    _[4].socketColor = c;
                }

                menu.push(_);
            }
        }

        // Extra Socket
        if (this.canBeSocketed(g_items[itemId].jsonequip.slotbak)) {
            var _ = [0, LANG.pr_menu_extrasock, Summary.toggleExtraSocket.bind(this, col, i, a)];
            _.checked = extraSocket;

            menu.push(_);
        }

        if (this.hasEnhancements([item])) {
            menu.push([0, LANG.pr_menu_clearenh, this.clearEnhancements.bind(this, col, i)]);
        }

        // Remove item
        menu.push([0, LANG.pr_menu_remove, this.removeItem.bind(this, col.group, i)]);

        a.rel = this.getItemRel(item, col);
        a.oncontextmenu = $WH.rf;
        a.onclick = this.onMouseClick.bind(0, a);
        a.onmouseup = this.onMouseUp;
        a.menu = menu;
    },

    removeItem: function(group, i) {
        group.splice(i, 1);
        this.refreshAll();
    },

    refreshRows: function() {
        if (this.groups.length) {
            $WH.ae(this.table, this.tbody);
        }

        while (this.tbody.firstChild) {
            this.tbody.removeChild(this.tbody.firstChild);
        }

        this.selectLevel.style.display = 'none';

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var reali = this.visibility[i];
            var col = this.columns[reali];

            if (!col.group)
                continue;

            for (var j = 0, len2 = col.group.length; j < len2; ++j)
            {
                var itemId = col.group[j][0];

                if (g_items[itemId].jsonequip.scadist && g_items[itemId].jsonequip.scaflags)
                {
                    this.selectLevel.style.display = '';
                    break;
                }
            }
        }

        var i = 0;
        for (trait in this.traits) {
            if (this.traits[trait].id) {
                this.traits[trait].i = ++i;
            }
            $WH.ae(this.tbody, this.getRow(trait, i));
        }

        if (!i) {
            if (!this.groups.length && this.tbody.parentNode) {
                $WH.de(this.tbody);
            }
            var tr = $WH.ce('tr');
            var td = $WH.ce('td');
            var span = $WH.ce('span');
            if (this.visibility && this.visibility.length) {
                td.colSpan = this.visibility.length;
            }
            td.style.textAlign = 'center';
            $WH.ae(span, $WH.ct('The items in this set don\'t have any comparable stats.'));
            $WH.ae(td, span);
            $WH.ae(tr, td);
            $WH.ae(this.tbody, tr);
        }
    },

    getLink: function(groups, weights, level, focus) {
        var href ='?compare';

        if (groups) {
            href += '=' + groups;                           // aowow - with fixed urls: href += '?items=' + groups;

            if (level != 80) {
                href += '&l=' + level;
            }

            if (weights[1].length && weights[2].length) {
                href += '&weights=' + weights.join(';');
            }

            if (focus !== undefined) {
                href += '&focus=' + focus;
            }
        }

        return href;
    },

    refreshLink: function() {
        var
            a = $WH.ge('su_link'),
            _ = this.getGroupData(),
            c = this.getWeightData();

        if (this.editable && _) {
            this.itemTip.style.display = '';
        }
        else {
            this.itemTip.style.display = 'none';
        }

        if (a) {
            var href = this.getLink(_, c, this.level, (this.selected ? this.__selected.i : undefined));

            a.href   = href;
            a.target = '_blank';
        }
    },

    refreshSort: function(lv) {
        if (!this.sortWeighted) {
            var sm = $WH.ce('small');
            sm.style.display = 'none';

            var sp = $WH.ce('span');
            sp.style.padding = '0 8px';
            sp.style.color = 'white';
            $WH.ae(sp, $WH.ct('|'));
            $WH.ae(sm, sp);

            $WH.ae(sm, $WH.ct(LANG.pr_note_sort + ' '));

            var s = $WH.ce('select');
            $WH.ae(sm, s);

            this.sortWeighted = sm;
        }

        var
            s = $WH.gE(this.sortWeighted, 'select')[0],
            o = $WH.ce('option');

        $WH.ee(s);
        $WH.ae(s, o);

        if (this.weights && this.weights.length) {
            for (var i = 0, len = this.weights.length; i < len; ++i) {
                o = $WH.ce('option');
                o.scale = this.weights[i];
                o.selected = (o.scale == this.currentScale);
                $WH.st(o, this.weights[i].name);
                $WH.ae(s, o);
            }
        }

        this.sortWeighted.style.display = s.childNodes.length > 1 ? '' : 'none';

        if (lv) {
            $WH.ae($WH.ce('div'), this.sortWeighted); // Remove from current parent
            $WH.gE(this.sortWeighted, 'span')[0].style.display = ($WH.gE(lv.getNoteTopDiv(), 'small').length ? '' : 'none');

            $WH.ae(lv.getNoteTopDiv(), this.sortWeighted);
            s.onchange = this.sortPickerWindow.bind(this, lv, 1);
            s.onchange();
        }
    },

    refreshClasses: function(sel) {
        var
            c = sel.options[sel.selectedIndex],
            _ = $WH.ge('su_presets');

        while (_.firstChild) {
            $WH.de(_.firstChild);
        }
        $WH.ae(_, $WH.ce('option'));

        if (sel.selectedIndex > 0) {
            for (var _group in c._presets) {
                var weights = c._presets[_group];

                if (LANG.presets[_group] != null) {
                    var group = $WH.ce('optgroup');
                    group.label = LANG.presets[_group];
                }
                else {
                    group = _;
                }

                for (var p in weights) {
                    var o = $WH.ce('option');
                    o.value = p;
                    o._weights = weights[p];
                    $WH.ae(o, $WH.ct(weights[p].name ? weights[p].name : LANG.presets[p]));
                    $WH.ae(group, o);
                }

                if (LANG.presets[_group] != null && group && group.childNodes.length > 0) {
                    $WH.ae(_, group);
                }
            }

            if (_.childNodes.length > 1) {
                _.parentNode.style.display = '';
            }
        }
        else {
            _.parentNode.style.display = 'none';
        }

        this.resetWeights();
    },

    refreshPresets: function(sel) {
        this.resetWeights();

        var _ = $WH.ge('su_classes');

        var o = sel.options[sel.selectedIndex];
        if (sel.selectedIndex > 0) {
            this.updateWeights(o._weights);
            $WH.ge('su_scale').value = (_.options[_.selectedIndex].value != -1 ? _.options[_.selectedIndex].text + LANG.hyphen : '') + o.text;
        }

        if (g_user.id > 0) {
            var a = $WH.ge('su_remscale');
            a.style.display = (o._weights && o._weights.name ? '' : 'none');
        }
    },

    resetWeights: function() {
        var _ = $WH.ge('su_weight');

        $WH.ge('su_scale').value = '';

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

    saveComparison: function(refresh) {
        window.onbeforeunload = null;

        g_setWowheadCookie('compare_groups', this.getGroupData(), true);
        g_setWowheadCookie('compare_weights', $WH.rtrim(this.getWeightData(1).join(';'), ';'), true);
        g_setWowheadCookie('compare_level', this.level, true);

        if (refresh) {
            document.location.href = '?compare'
        }
    },

    viewSavedComparison: function(refresh) {
        window.onbeforeunload = null;

        document.location.href = '?compare';
    },

    getGroupData: function(n) {
        var
            start = 0,
            len = this.groups.length;

        if (!isNaN(n)) {
            start = n;
            len = n + 1;
        }

        var
            _ = '',
            j = 0;

        for (var i = start; i < len; ++i) {
            if (this.groups[i].length < 1) {
                continue;
            }

            if (j++ > 0) {
                _ += ';';
            }

            for (var j = 0, len2 = this.groups[i].length; j < len2; ++j) {
                if (j > 0) {
                    _ += ':';
                }

                _ += this.writeItem(this.groups[i][j]);
            }
        }
        return _;
    },

    getWeightData: function(plain) {
        var
            n = '',
            wt = '',
            wtv = '',
            filter;

        for (var i = 0, len = this.weights.length; i < len; ++i) {
            if (i > 0) {
                wt  += ';';
                wtv += ';';
                n   += ';';
            }
            n += (plain ? this.weights[i].name : $WH.urlencode(this.weights[i].name));

            var j = 0;
            for (var w in this.weights[i]) {
                if (!LANG.traits[w]) {
                    continue;
                }

                filter = fi_Lookup(w, 'items');
                if (!filter) {
                    continue;
                }

                if (j++ > 0) {
                    wt  += ':';
                    wtv += ':';
                }

                wt += filter.id;
                wtv += this.weights[i][w];
            }
        }
        return [n, wt, wtv];
    },

    showNoData: function(container) {
        var div = this.textNames;
        while (div.firstChild) {
            $WH.de(div.firstChild);
        }
        div.style.display = 'none';

        div = this.noData;
        while (div.firstChild) {
            $WH.de(div.firstChild);
        }
        $WH.ae(container, div);

        var result = -1;
        if (this.template.onNoData) {
            result = (this.template.onNoData.bind(this, div))();
        }
        if (result == -1) {
            $WH.ae(div, $WH.ct(LANG.su_additems || LANG.lvnodata));
        }
    },

    showTextNames: function() {
        var div = this.textNames;
        div.style.display = '';
        div.style.paddingTop = '10px';

        while (div.firstChild) {
            $WH.de(div.firstChild);
        }

        $WH.ae(div, $WH.ct(LANG.su_comparing));

        var n = 0;
        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var reali = this.visibility[i];
            var col = this.columns[reali];

            if (!col.group || !col.group.length) {
                continue;
            }

            if (n++ > 0) {
                $WH.ae(div, $WH.ct(LANG.su_comparewith));
            }

            if (col.group.length == 1) {
                var item = g_items[col.group[0][0]].jsonequip;

                var a = $WH.ce('a');
                a.className = 'q' + (7 - parseInt(item.name.charAt(0)));
                a.href = '?item=' + item.id;
                a.rel = this.getItemRel(col.group[0], col);
                $WH.ae(a, $WH.ct('[' + this.getItemName(col.group[0]) + ']'));
                $WH.ae(div, a);
            }
            else {
                var sp = $WH.ce('span');
                sp.onmouseover = Summary.groupOver.bind(a, col.group);
                sp.onmousemove = $WH.Tooltip.cursorUpdate;
                sp.onmouseout = $WH.Tooltip.hide;
                sp.className = 'tip';
                $WH.ae(sp, $WH.ct('[' + col.group.length + ' ' + LANG.types[3][3] + ']'));
                $WH.ae(div, sp);
            }
        }
    },

    updateControls: function() {
        if (this.__lastCtrlUpdate != null && (this.__lastCtrlUpdate == this.groups.length || this.groups.length > 1)) {
            return;
        }
        this.__lastCtrlUpdate = this.groups.length;

        var
            div = this.controls,
            a;

        div.style.display = '';

        while (div.firstChild) {
            $WH.de(div.firstChild);
        }

        var div2 = $WH.ce('div');
        div2.className = 'summary-controls-right';
        $WH.ae(div, div2);

        a = this.createControl(LANG.su_help, null, 'icon-help', null, '?help=item-comparison');
        a.target = '_blank';
        $WH.ae(div2, a);

        if (this.searchable) {
            a = this.createControl(LANG.su_addset, 'su_addset', 'icon-add', this.openItemPicker.bind(this, 4));
            $WH.ae(div2, a);

            a = this.createControl(LANG.su_additem, 'su_additem', 'icon-add', this.openItemPicker.bind(this, 3));
            $WH.ae(div2, a);
        }

        if (this.groups.length) {
            if (this.weightable) {
                a = this.createControl(LANG.su_addscale, 'su_addscale', 'icon-add', this.toggleOptions.bind(this, 'su_weights', 'su_addscale'));
                $WH.ae(div2, a);
            }
        }

        $WH.aE(document, 'click', this.toggleOptions.bind(this, null, null));

        var _ = $WH.ce('div');
        _.className = 'clear';
        $WH.ae(div2, _);

        if (this.weightable) {
            var
                d2 = $WH.ce('div'),
                d = $WH.ce('div');

            d2.style.display = 'none';
            d2.id = 'su_weights';
            d.className = 'summary-weights-inner';
            $WH.ae(d2, d);
            $WH.ae(div2, d2);

            var
                t = $WH.ce('table'),
                tbody = $WH.ce('tbody'),
                tr = $WH.ce('tr'),
                td = $WH.ce('td');

            $WH.ae(t, tbody);
            $WH.ae(tbody, tr);
            $WH.ae(tr, td);

            $WH.ae(td, $WH.ct(LANG.su_preset));

            td = $WH.ce('td');
            $WH.ae(tr, td);

            var s = $WH.ce('select');
            s.id = 'su_classes';
            s.onchange = s.onkeyup = this.refreshClasses.bind(this, s);
            $WH.ae(s, $WH.ce('option'));
            $WH.ae(td, s);

            if (g_user.weightscales != null && g_user.weightscales.length) {
                var o = $WH.ce('option');
                o.value = -1;
                o._presets = { custom: {} };
                $WH.ae(o, $WH.ct(LANG.ficustom));
                $WH.ae(s, o);

                for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                    o._presets.custom[g_user.weightscales[i].id] = g_user.weightscales[i];
                }
            }

            var temp = [];
            for (var c in wt_presets) {
                temp.push(c);
            }
            temp.sort(function(a, b) {
                return $WH.strcmp(g_chr_classes[a], g_chr_classes[b]);
            });

            for (var i = 0, len = temp.length; i < len; ++i) {
                var
                    c = temp[i],
                    o = $WH.ce('option');

                o.value = c;
                o._presets = wt_presets[c];
                $WH.ae(o, $WH.ct(g_chr_classes[c]));
                $WH.ae(s, o);
            }

            _ = $WH.ce('span');
            _.style.display = 'none';
            $WH.ae(_, $WH.ct(' '));
            $WH.ae(td, _);

            s = $WH.ce('select');
            s.id = 'su_presets';
            s.onchange = s.onkeyup = this.refreshPresets.bind(this, s);
            $WH.ae(s, $WH.ce('option'));
            $WH.ae(_, s);

            $WH.ae(td, $WH.ct(' '));
            a = $WH.ce('a');
            a.href = 'javascript:;';
            $WH.ae(a, $WH.ct(LANG.fishowdetails));
            a.onclick = this.showDetails.bind(this, a);
            $WH.ae(td, a);

            if (g_user.id > 0) {
                $WH.ae(td, $WH.ct(' '));
                a = $WH.ce('a');
                a.href = 'javascript:;';
                a.className = 'icon-save';
                a.appendChild($WH.ct(LANG.fisavescale));
                a.onclick = this.saveScale;
                a.onmousedown = $WH.rf;
                $WH.ae(td, a);

                $WH.ae(td, $WH.ct(' '));
                a = $WH.ce('a');
                a.href = 'javascript:;';
                a.id = 'su_remscale';
                a.className = 'icon-delete';
                a.style.display = 'none';
                a.appendChild($WH.ct(LANG.fideletescale));
                a.onclick = this.deleteScale;
                a.onmousedown = $WH.rf;
                $WH.ae(td, a);
            }

            tr = $WH.ce('tr');
            td = $WH.ce('td');
            $WH.ae(tbody, tr);
            $WH.ae(tr, td);

            $WH.ae(td, $WH.ct(LANG.su_name));

            td = $WH.ce('td');
            $WH.ae(tr, td);

            var i = $WH.ce('input');
            i.type = 'text';
            i.id = 'su_scale';
            $WH.ae(td, i);
            $WH.ae(td, $WH.ct(' '));

            $WH.ae(d, t);

            var w = $WH.ce('div');
            w.style.display = 'none';
            w.id = 'su_weight';
            $WH.ae(d, w);
            _ = $WH.ce('div');
            $WH.ae(w, _);

            s = $WH.ce('select');
            s.onchange = s.onkeyup = this.refreshWeights.bind(this, s);
            $WH.ae(s, $WH.ce('option'));
            $WH.ae(_, s);

            _ = false;

            for (var i = 0, len = this.template.traits.length; i < len; ++i) {
                var p = this.template.traits[i];

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
            _.style.display = 'none';
            a = this.createControl(LANG.su_addweight, 'su_addweight', '', this.addWeight.bind(this));
            $WH.ae(_, a);
            $WH.ae(d, _);

            _ = $WH.ce('div');
            _.className = 'summary-weights-buttons';

            a = $WH.ce('a');
            a.className = 'icon-help';
            a.href = '?help=stat-weighting';
            a.target = '_blank';
            a.style.border = 'none';
            a.style.cssFloat = a.style.styleFloat = 'right';
            $WH.ae(a, $WH.ct(LANG.su_help));
            $WH.ae(_, a);

            i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.su_applyweight;
            i.onclick = Summary.addWeightScale.bind(this);
            $WH.ae(_, i);

            $WH.ae(_, $WH.ct(' '));

            i = $WH.ce('input');
            i.type = 'button';
            i.value = LANG.su_resetweight;
            i.onclick = Summary.resetScale.bind(this);
            $WH.ae(_, i);

            $WH.ae(d, _);
        }

        if (this.autoSave) {
            a = this.createControl(LANG.su_autosaving, null, 'icon-refresh selected');
        }
        else {
            if (g_getWowheadCookie('compare_groups')) {
                a = this.createControl(LANG.su_viewsaved, 'su_viewsaved', 'icon-save', this.viewSavedComparison.bind(this, 1));
                $WH.ae(div, a);
            }

            a = this.createControl(LANG.su_savecompare, 'su_save', 'icon-save', this.saveComparison.bind(this, 1));
        }
        $WH.ae(div, a);

        if (this.groups.length) {
            a = this.createControl(LANG.su_linkcompare, 'su_link', 'icon-link');
            $WH.ae(div, a);

            a = this.createControl(LANG.su_clear, null, 'icon-delete', this.resetAll.bind(this));
            $WH.ae(div, a);
        }
    },

    getRow: function(trait) {
        var row = this.traits[trait];
        this.createRow(row);
        return row.__tr
    },

    createRow: function(row) {
        var tr = $WH.ce('tr');
        row.__tr = tr;

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var reali = this.visibility[i];
            var col = this.columns[reali];

            var
                td = $WH.ce('td'),
                _ = td;

            if (col.align != null) {
                td.style.textAlign = col.align;
            }

            if (this.total != null && this.__total.id == col.id || this.selected != null && this.__selected.id == col.id) {
                if (row.id != 'gains') { // Special case for 'Gains'
                    td.style.fontWeight = 'bold';
                    td.className += 'q1';

                    if (this.selected != null && this.__selected.id == col.id) {
                        td.className += ' checked';
                    }
                }
            }

            if (!row.type) {
                _.colSpan = len;
                _.style.borderBottomWidth = '2px';
                _.style.padding = '0';
                $WH.ae(tr, td);
                break;
            }

            var
                result = null,
                sign = '';

            if (col.compute) {
                result = (col.compute.bind(this, row, _, tr, reali))();
            }
            else if (row.compute) {
                result = (row.compute.bind(this, col, _, tr, reali))();
            }
            else if (col.total) {
                if (this.selected != null && this.__selected.id != col.id && col.difference) {
                    result = col.difference[row.id];
                    if (result > 0) {
                        _.className += ' q2';
                    }
                    else if (result < 0) {
                        _.className += ' q10';
                    }
                    else if (result == 0) {
                        result = null;
                    }
                }
                else {
                    result = col.total[row.id];
                }

                if (result != null) {
                    if (result.join) {
                        for (var j = 0, len2 = result.length; j < len2; ++j) {
                            if (j > 0) {
                                $WH.ae(_, $WH.ct(', '));
                            }
                            if (result[j].appendChild) {
                                $WH.ae(_, result[j]);
                            }
                            else {
                                $WH.ae(_, $WH.ct(result[j]));
                            }
                        }
                        result = null;
                    }
                    else if (!isNaN(result)) {
                        var n = Math.pow(10, (row.digits != null ? row.digits : 4));
                        result = Math.round(n * result) / n;

                        if (result > 0 && this.selected != null && this.__selected.id != col.id) {
                            sign = '+';
                        }

                        if (!row.rating) {
                            result = (result < 0 ? '-' : '') + $WH.number_format(Math.abs(result));
                        }
                        else {
                            this.selectLevel.style.display = '';
                        }
                    }
                }
            }

            // Is maximum value?
            if (this.groups.length > 1 && !this.selected && col.total && col.total[row.id] == this.maxValue[row.id] && this.maxValue[row.id] != this.minValue[row.id] && col.id != 'total') {
                _.style.fontWeight = 'bold';
                _.className += ' q2';
            }

            if (result != null) {
                if (row.rating && !isNaN(result)) {
                    var percent = (result < 0 ? -1 : 1) * $WH.g_convertRatingToPercent(this.level, row.rating, Math.abs(result));
                    percent = $WH.number_format((Math.round(percent * 100) / 100));

                    if (row.rating != 12 && row.rating != 37) { // Neither Defense, nor Expertise
                        percent += '%';
                    }

                    var a = $WH.ce('a');

                    a.className = (_.className ? _.className : 'q1');
                    a.style.borderBottom = '1px dotted #808080';
                    a.onmouseover = (function(rating, percent, level, text) {
                        $WH.Tooltip.show(this, rating + ' ' + text + ' (' + $WH.sprintf(LANG.tooltip_combatrating, percent, level) + ')<br /><span class="q2">' + LANG.su_toggle + '</span>', 0, 0, 'q');
                    }).bind(a, result, percent, this.level, LANG.traits[row.id][0]);
                    a.onmousemove = $WH.Tooltip.cursorUpdate;
                    a.onmouseout = $WH.Tooltip.hide;
                    a.onclick = this.toggleRatings.bind(this);

                    $WH.ae(a, $WH.ct(sign + (this.ratingMode ? percent : result)));

                    $WH.aef(_, a);
                }
                else {
                    $WH.aef(_, $WH.ct(sign + result));
                }
            }

            if (!row.hidden) {
                $WH.ae(tr, td);
            }
        }
    },

    toggleOptions: function(popupId, link, e) {
        e = $WH.$E(e);

        if (e && e._button >= 2) {
            return false;
        }

        var
            popups = [$WH.ge('su_weights')],
            links  = [$WH.ge('su_addscale')],
            popup  = null;

        if ($WH.Browser.ie && e && !popupId && $WH.in_array(links, e._target) != -1) {
            return;
        }

        if (link) {
            link = $WH.ge(link);
        }
        if (popupId && link) { // Params provided -> a link was clicked
            if (link.className.indexOf('selected') != -1) { // Already selected
                link = null;
            }
            else {
                popup = $WH.ge(popupId);
            }
        }
        else if (e) {
            var p = e._target;

            if (!p.parentNode) {
                return;
            }

            while (p.parentNode) {
                if ($WH.in_array(popups, p) != -1) {
                    return false;
                }

                p = p.parentNode;
            }
        }

        for (var i = 0, len = popups.length; i < len; ++i) {
            if (popups[i] && popups[i] != popup) {
                popups[i].style.display = 'none';
            }
        }
        for (var i = 0, len = links.length; i < len; ++i) {
            if (links[i] && links[i] != link) {
                links[i].className = links[i].className.replace('selected', '');
            }
        }

        if (link) {
            link.className += ' selected';

            if (popup) {
                popup.style.display = '';
            }
        }

        if (e && popupId && link) {
            $WH.sp(e);
        }
    },

    toggleWeights: function() {
        if (++this.scoreMode > 2) {
            this.scoreMode = 0;
        }
        this.refreshAll();
    },

    toggleRatings: function() {
        this.ratingMode = !this.ratingMode;

        this.refreshAll();
    },

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

    createColumn: function() {
        this.groups.push([this.readItem(-1)]);
        this.refreshAll();
    },

    selectColumn: function(col) {
        $WH.Tooltip.hide();

        this.selected = (this.__selected.id == col.id ? null : this.clone.i + col.i);
        this.refreshAll();
    },

    deleteColumn: function(col, e) {
        $WH.Tooltip.hide();

        e = $WH.$E(e);

        if (!e.shiftKey) {
            this.groups.splice(col.i, 1);
        }
        else {
            this.groups = [this.groups[col.i]];
        }
        if (!this.editable || this.groups.length <= 1 || this.__selected.id == col.id) {
            this.selected = null;
        }
        else if (this.selected) {
            this.selected += (this.clone.i + col.i > this.selected ? 0 : -1);
        }
        this.refreshAll();

        return false;
    },

    createWeightScale: function() {
        var
            _ = $WH.ge('su_weight'),
            s = $WH.ge('su_scale'),
            scale = {},
            found = false;

        scale.name = s.value;
        scale.name = $WH.trim(scale.name);
        scale.name = scale.name.replace(/'/g, '');

        for (var i = 0, len = _.childNodes.length; i < len; ++i) {
            if (_.childNodes[i].childNodes.length == 5) {
                if (_.childNodes[i].childNodes[0].tagName == 'SELECT' && _.childNodes[i].childNodes[2].tagName == 'INPUT') {
                    scale[_.childNodes[i].childNodes[0].options[_.childNodes[i].childNodes[0].selectedIndex].value] = Number(_.childNodes[i].childNodes[2].value);

                    found = true;
                }
            }
        }

        if (found) {
            return scale;
        }

        return null;
    },

    deleteWeightScale: function(i) {
        $WH.Tooltip.hide();

        this.weights.splice(i, 1);

        if (!this.weights.length) {
            var a = $WH.ge('enchants-default-sort');
            if (a) {
                a.onclick();
            }
        }

        this.refreshAll();
    },

    calcScores: function(data, scale) {
        for (var i = 0, len = data.length; i < len; ++i) {
            var row = data[i];

            row.__tr  = null;
            row.score = (scale ? 0 : null);

            if (row.jsonequip && scale) {
                for (var w in scale) {
                    if (!LANG.traits[w]) {
                        continue;
                    }

                    if (!isNaN(row.jsonequip[w])) {
                        row.score += row.jsonequip[w] * scale[w] / (!this.scoreMode ? scale.f : (this.scoreMode == 2 ? scale.m * 0.01 : 1));
                    }
                }

                if (!this.scoreMode) {
                    row.score = row.score.toFixed(2);
                }
                else if (this.scoreMode == 2) {
                    row.score = row.score.toFixed(1) + '%';
                }
            }
        }

        return data;
    },

    sortPickerWindow: function(lv, search) {
        if (!this.sortWeighted) {
            return;
        }

        if ($('option:selected', this.sortWeighted)[0]) {
            this.currentScale = $('option:selected', this.sortWeighted)[0].scale;
        }

        lv.setSort([1], true, false);
        lv.setData(this.calcScores(lv.data, this.currentScale));

        if (this.currentScale) {
            lv.setSort([2], true, false);
        }

        if (search && lv.id == 'items') {
            this.onSearchKeyUp(0);
        }
    },

    openItemPicker: function(type) {
        this.searchType = type;

        Lightbox.show('compare', { onShow: this.onItemPickerShow.bind(this) });
    },

    createItemPickerNote: function(div) {
        var
            sm = $WH.ce('small'),
            sp = $WH.ce('span'),
            a;

        this.currentSearch = '';
        this.searchTimer = 0;

        $WH.ae(sm, $WH.ct(LANG.su_note_name));

        this.searchName = $WH.ce('input');
        this.searchName.type = 'text';

        $WH.aE(this.searchName, 'keyup', this.onSearchKeyUp.bind(this, 333));
        $WH.aE(this.searchName, 'keydown', this.onSearchKeyDown.bind(this));
        $WH.ae(sm, this.searchName);

        this.searchMsg = sp;
        this.searchMsg.style.fontWeight = 'bold';
        $WH.ae(sm, this.searchMsg);

        $WH.ae(div, sm);
    },

    getSetBonuses: function(group) {
        var
            setbonuses = {},
            itemsetpcs = {},
            itemsetbak = {};

        for (var i = 0, len = group.length; i < len; ++i) {
            var p = group[i][0];
            if (g_items[p]) {
                var
                    s = g_items[p].jsonequip.itemset,
                    _ = g_itemsets[s];

                if (_) {
                    // Use the real set id for set bonus tally
                    s = _.idbak;
                    if (itemsetpcs[s] == null) {
                        itemsetpcs[s] = {};
                    }
                    itemsetpcs[s][p] = 1;
                    itemsetbak[s] = _;
                }
            }
        }

        for (var s in itemsetpcs) {
            var
                itemset = itemsetbak[s],
                nItems = 0;

            if (!itemset.setbonus) {
                continue;
            }

            for (var p in itemsetpcs[s]) {
                nItems++;
            }

            for (var n in itemset.setbonus) {
                if (n > nItems) {
                    break;
                }

                for (var j in itemset.setbonus[n]) {
                    if (setbonuses[j] == null) {
                        setbonuses[j] = 0;
                    }

                    setbonuses[j] += itemset.setbonus[n][j];
                }
            }
        }

        return setbonuses;
    },

    onItemPickerShow: function(dest, first, opt) {
        Lightbox.setSize(800, 564);

        var lv;

        if (first) {
            dest.className = 'summary-picker listview';

            var
                d     = $WH.ce('div'),
                a     = $WH.ce('a'),
                clear = $WH.ce('div');

            d.className = 'listview';
            $WH.ae(dest, d);

            a = $WH.ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(dest, a);

            clear.className = 'clear';
            $WH.ae(dest, clear);

            lv = new Listview({
                template: 'compare',
                id: 'items',
                parent: d,
                data: [],
                clip: { w: 780, h: 478 },
                createNote: this.createItemPickerNote.bind(this)
            });

            if ($WH.Browser.firefox) {
                $WH.aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
            }
            else {
                lv.getClipDiv().onmousewheel = g_pickerWheel;
            }
        }
        else {
            lv = g_listviews.items;
        }

        var
            searchItems = this.searchItems.bind(this),
            searchName = this.searchName;

            this.refreshSort(lv);

        setTimeout(function() {
            searchItems('', this.searchType);
            searchName.value = '';
            searchName.focus();
        }, 1);
    },

    openSubitemPicker: function(col, i) {
        this.currentItem = { col: col, i: i, item: col.group[i] };

        Lightbox.show('subitempicker', { onShow: this.onSubitemPickerShow.bind(this) });
    },

    onSubitemPickerShow: function(dest, first, opt) {
        Lightbox.setSize(800, 564);

        var
            lv,
            dataz = [],
            itemId = this.currentItem.item[0];

        for (var subitemId in g_items[itemId].jsonequip.subitems) {
            var subitem = g_items[itemId].jsonequip.subitems[subitemId];

            subitem.id = subitemId;
            subitem.item = itemId;
            subitem._summary = this;

            dataz.push(subitem);
        }

        if (first) {
            dest.className = 'summary-picker listview';

            var
                d     = $WH.ce('div'),
                a     = $WH.ce('a'),
                clear = $WH.ce('div');

                d.className = 'listview';
            $WH.ae(dest, d);

            a = $WH.ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(dest, a);

            clear.className = 'clear';
            $WH.ae(dest, clear);

            lv = new Listview({
                template: 'subitempicker',
                id: 'subitems',
                parent: d,
                data: dataz
            });

            if ($WH.Browser.firefox) {
                $WH.aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
            }
            else {
                lv.getClipDiv().onmousewheel = g_pickerWheel;
            }
        }
        else {
            lv = g_listviews.subitems;

            lv.setData(dataz);
            lv.clearSearch();
            lv.updateFilters(true);
        }

        this.refreshSort(lv);

        setTimeout(function() {
            lv.focusSearch();
        }, 1);
    },

    openEnchantPicker: function(col, i) {
        this.currentItem = { col: col,i: i, item: col.group[i] };

        Lightbox.show('enchantpicker', { onShow: this.onEnchantPickerShow.bind(this) });
    },

    createEnchantPickerNote: function(div) {
        var
            sm = $WH.ce('small'),
            a;

        $WH.ae(sm, $WH.ct(LANG.pr_note_source));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 0, null);
        $WH.ae(a, $WH.ct(LANG.pr_note_all));

        g_setSelectedLink(a, 'enchants0');

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 0, 1);
        $WH.ae(a, $WH.ct(LANG.pr_note_items));

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 0, 2);
        $WH.ae(a, $WH.ct(LANG.pr_note_profs));

        $WH.ae(sm, a);
        $WH.ae(div, sm);

        sm = $WH.ce('small');
        var sp = $WH.ce('span');
        sp.style.padding = '0 8px';
        sp.style.color = 'white';
        $WH.ae(sp, $WH.ct('|'));
        $WH.ae(sm, sp);
        $WH.ae(sm, $WH.ct(LANG.su_note_stats + ': '));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 1, null);
        $WH.ae(a, $WH.ct(LANG.pr_note_all));

        g_setSelectedLink(a, 'enchants1');

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma))

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 1, 'sta');
        $WH.ae(a, $WH.ct(LANG.traits.sta[0]));
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 1, 'str')
        $WH.ae(a, $WH.ct(LANG.traits.str[0]));
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 1, 'agi');
        $WH.ae(a, $WH.ct(LANG.traits.agi[0]))
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 1, 'int');
        $WH.ae(a, $WH.ct(LANG.traits['int'][0]));
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 1, 'spi');
        $WH.ae(a, $WH.ct(LANG.traits['spi'][0]));
        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 1, -1);
        $WH.ae(a, $WH.ct(LANG.su_note_other));

        this.enchantStat = null;
        $WH.ae(sm, a);
        $WH.ae(div, sm);
    },

    canBeEnchanted: function(slot, subclass) {
        return ((slot ==  1 || slot ==  3 || slot == 16 || slot ==  5 || slot == 20 ||  // Head, Shoulder, Back, Chest, Robe
                 slot ==  9 || slot == 10 || slot ==  6 || slot ==  7 || slot ==  8 ||  // Wrists, Hands, Waist, Legs, Feet
                 slot == 11 || slot == 21 || slot == 13 || slot == 17 || slot == 22 ||  // Finger, Main Hand, One Hand, Two Hand, Off Hand
                 slot == 14 || slot == 15 || slot == 26 || slot == 23)              &&  // Shield, Ranged, Held in Off Hand
                (subclass != 19))                                                       // No Wands
    },

    isValidEnchant: function(enchant) {
        if (enchant.none) {
            return true;
        }

        var slotMask = 1 << (g_items[this.currentItem.item[0]].jsonequip.slot - 1);

        return (
            // Slot
            (enchant.slots & slotMask) &&

            // Source
            (
                this.enchantSource == null ||
                (this.enchantSource == 1 && enchant.source < 0) ||
                (this.enchantSource == 2 && enchant.source > 0)
            ) &&

            // StatType
            (
                this.enchantStat == null ||
                (
                    this.enchantStat == -1 &&
                    !enchant.jsonequip.hasOwnProperty('sta') &&
                    !enchant.jsonequip.hasOwnProperty('str') &&
                    !enchant.jsonequip.hasOwnProperty('agi') &&
                    !enchant.jsonequip.hasOwnProperty('int') &&
                    !enchant.jsonequip.hasOwnProperty('spi')
                ) ||
                (
                    enchant.jsonequip.hasOwnProperty(this.enchantStat)
                )
            )
        );
    },

    filterEnchants: function(a, wut, value) {
        switch (wut) {
        case 0:
            this.enchantSource = value;
            break;
        case 1:
            this.enchantStat = value;
            break;
        default:
            return;
        }

        if (a && a.nodeName == 'A') {
            g_setSelectedLink(a, 'enchants' + wut);
        }

        g_listviews.enchants.updateFilters(true);

        return false;
    },

    onEnchantPickerShow: function(dest, first, opt) {
        Lightbox.setSize(800, 564);

        var lv;

        if (first) {
            dest.className = 'summary-picker listview';

            var
                dataz = [],
                d = $WH.ce('div'),
                a = $WH.ce('a'),
                clear = $WH.ce('div');

            dataz.push({ none: 1, __alwaysvisible: 1, _summary: this });

            for (var enchantId in g_enchants) {
                var enchant = { id: enchantId, _summary: this };

                $WH.cO(enchant, g_enchants[enchantId]);

                if (typeof enchant.name == 'string') {
                    dataz.push(enchant);
                }
                else {
                    for (var i = 0, len = g_enchants[enchantId].name.length; i < len; ++i) {
                        var row = $WH.dO(enchant);

                        row.name   = enchant.name[i];
                        row.source = enchant.source[i];
                        row.slots  = enchant.slots[i];

                        dataz.push(row);
                    }
                }
            }

            d.className = 'listview';
            $WH.ae(dest, d);

            a = $WH.ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(dest, a);

            clear.className = 'clear';
            $WH.ae(dest, clear);

            lv = new Listview({
                template: 'enchantpicker',
                id: 'enchants',
                parent: d,
                data: dataz,
                createNote: this.createEnchantPickerNote.bind(this),
                customFilter: this.isValidEnchant.bind(this)
            });

            if ($WH.Browser.firefox) {
                $WH.aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
            }
            else {
                lv.getClipDiv().onmousewheel = g_pickerWheel;
            }
        }
        else {
            lv = g_listviews.enchants;

            lv.clearSearch();
            lv.updateFilters(true);
        }

        this.refreshSort(lv);

        setTimeout(function() {
            lv.focusSearch();
        }, 1);
    },

    openGemPicker: function(col, i, socket, color) {
        this.currentItem = {
            col: col,
            i: i,
            item: col.group[i],
            socket: socket,
            color: color
        };

        Lightbox.show('gempicker', { onShow: this.onGemPickerShow.bind(this) });
    },

    createGemPickerNote: function(div) {
        var
            sm = $WH.ce('small'),
            a;

        $WH.ae(sm, $WH.ct(LANG.pr_note_source));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 0, null);
        $WH.ae(a, $WH.ct(LANG.pr_note_all));

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 0, 1);
        $WH.ae(a, $WH.ct(LANG.pr_note_bc));

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 0, 2);
        $WH.ae(a, $WH.ct(LANG.pr_note_wotlk));
        this.gemSource = 2;
        g_setSelectedLink(a, 'gems0');

        $WH.ae(sm, a);
        $WH.ae(div, sm);

        sm = $WH.ce('small');

        var sp = $WH.ce('span');
        sp.style.padding = '0 8px';
        sp.style.color = 'white';
        $WH.ae(sp, $WH.ct('|'));
        $WH.ae(sm, sp);

        $WH.ae(sm, $WH.ct(LANG.pr_note_color));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 1, null);
        $WH.ae(a, $WH.ct(LANG.pr_note_all));

        $WH.ae(sm, a);
        $WH.ae(sm, $WH.ct(LANG.comma));

        a = $WH.ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 1, 1);
        $WH.ae(a, $WH.ct(LANG.pr_note_match));

        this.gemColor = 1;
        g_setSelectedLink(a, 'gems1');

        $WH.ae(sm, a);
        $WH.ae(div, sm);
    },

    matchGemSocket: function(gemColor, socketColor) {
        for (var i = 1; i <= 8; i *= 2) {
            if ((socketColor & i) && (gemColor & i)) {
                return true;
            }
        }

        return false;
    },

    canBeSocketed: function(slot) {
        return (slot== 9 || slot == 10 || slot == 6); // Wrists, Hands, and Waist
    },

    hasExtraSocket: function(item) {
        return this.enhanceable && (item[this.getExtraSocketPos(item[0])] ? 1 : 0);
    },

    getExtraSocketPos: function(itemId) {
        if (!itemId || !g_items[itemId]) {
            return 4;
        }

        return 4 + (g_items[itemId].jsonequip.nsockets | 0);
    },

    hasSocketBonus: function(item) {
        var nMatches = 0;

        if (!g_items[item[0]] || !g_items[item[0]].jsonequip.nsockets) {
            return false;
        }

        for (var j = 0; j < 3; ++j) {
            if (item[j + 4] && g_gems[item[j + 4]]) {
                if (this.matchGemSocket(g_gems[item[j + 4]].colors, g_items[item[0]].jsonequip['socket' + (j + 1)])) {
                    ++nMatches;
                }
            }
        }

        return (nMatches == g_items[item[0]].jsonequip.nsockets);
    },

    isValidGem: function(gem) {
        if (gem.none) {
            return true;
        }

        return (
            // Source
            (this.gemSource == null || gem.expansion == this.gemSource) &&

            // Color
            (
                ((this.currentItem.color != 1 && gem.colors != 1) && // Always match meta gems and sockets
                this.gemColor == null) ||
                this.matchGemSocket(gem.colors, this.currentItem.color)
            )
        );
    },

    filterGems: function(a, wut, value) {
        switch (wut) {
        case 0:
            this.gemSource = value;
            break;
        case 1:
            this.gemColor = value;
            break;
        default:
            return;
        }

        if (a && a.nodeName == 'A') {
            g_setSelectedLink(a, 'gems' + wut);
        }

        g_listviews.gems.updateFilters(true);

        return false;
    },

    onGemPickerShow: function(dest, first, opt) {
        Lightbox.setSize(800, 564);

        var lv;

        if (first) {
            dest.className = 'summary-picker listview';

                var
                dataz = [],
                d     = $WH.ce('div'),
                a     = $WH.ce('a'),
                clear = $WH.ce('div');

            dataz.push({ none: 1, __alwaysvisible: 1, _summary: this });

            for (var gemId in g_gems) {
                var gem = { id: gemId, _summary: this };

                $WH.cO(gem, g_gems[gemId]);

                dataz.push(gem);
            }

            d.className = 'listview';
            $WH.ae(dest, d);

            a = $WH.ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(dest, a);

            clear.className = 'clear';
            $WH.ae(dest, clear);

            lv = new Listview({
                template: 'gempicker',
                id: 'gems',
                parent: d,
                data: dataz,
                createNote: this.createGemPickerNote.bind(this),
                customFilter: this.isValidGem.bind(this)
            });

            if ($WH.Browser.firefox) {
                $WH.aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
            }
            else {
                lv.getClipDiv().onmousewheel = g_pickerWheel;
            }
        }
        else {
            lv = g_listviews.gems;

            lv.clearSearch();
            lv.updateFilters(true);
        }

        var
            div = lv.getNoteTopDiv(),
            sms = $WH.gE(div, 'small'),
            as = $WH.gE(div, 'a');

        if (this.currentItem.color == 1 || this.currentItem.color == 14) { // Meta, Prismatic
            sms[1].style.display = 'none'; // Hide 'Color' filter
        }
        else {
            sms[1].style.display = ''; // Show 'Color' filter
        }

        this.refreshSort(lv);

        setTimeout(function() {
            lv.focusSearch();
        }, 1);
    },

    onMouseClick: function(a, e) {
        e = $WH.$E(e);

        if (e._button == 3 || e.shiftKey || e.ctrlKey) {
            return false;
        }
    },

    onMouseUp: function(event) {
        event = $WH.$E(event);
        if (event._button == 3 || event.shiftKey || event.ctrlKey) { // Right click
            var pos = $WH.g_getCursorPos(event);
            setTimeout(Menu.showAtXY.bind(null, this.menu, pos.x, pos.y), 1); // Timeout needed for the context menu to be disabled

            $WH.Tooltip.hide();
        }

        return false;
    },

    onSearchKeyUp: function(delay) {
        var search = $WH.trim(this.searchName.value.replace(/\s+/g, ' '));

        if (search == this.currentSearch && isNaN(delay)) {
            return;
        }
        this.currentSearch = search;

        this.prepareSearch(search, delay);
    },

    onSearchKeyDown: function(e) {
        e = $WH.$E(e);

        switch (e.keyCode) {
        case 13: // Enter
            g_listviews.items.submitSearch(e);
            break;
/*  aowow todo: internal misconception ... hide() and cycle(dir) are members of Livesearch and cant be reused here without further adaptation
        case 27: // Escape
            hide();
            break;
        case 38: // Up
            cycle(0);
            break;
        case 40: // Down
            cycle(1);
            break;
*/
        }
    },

    prepareSearch: function(search, delay) {
        if (isNaN(delay)) {
            delay = 1000;
        }

        if (this.searchTimer > 0) {
            clearTimeout(this.searchTimer);
            this.searchTimer = 0;
        }

        if (search) {
            $WH.st(this.searchMsg, $WH.sprintf(LANG['su_searching' + this.searchType], search));
        }
        this.searchMsg.className = '';

        this.searchTimer = setTimeout(this.searchItems.bind(this, search, this.searchType), delay);
    },

    searchItems: function(search, type) {
        var
            lv    = g_listviews.items,
            _this = this,
            searchResults = [{ none: 1 }];

        lv.searchable = false;
        lv.setData(searchResults);
        lv.clearSearch();
        lv.updateFilters(true);
        this.searchMsg.className = '';

        if (!search && !this.currentScale) {
            $WH.st(this.searchMsg, LANG['su_specifyitem' + this.searchType]);
            return;
        }
        $WH.st(this.searchMsg, $WH.sprintf(LANG['su_searching' + this.searchType], search));

        new Ajax('?search=' + $WH.urlencode(search) + '&json&type=' + type + pr_getScaleFilter(this.currentScale, 1), {
            method: 'POST',
            search: search,
            onSuccess: function (xhr, opt) {
                var text = xhr.responseText;
                if (text.charAt(0) != '[' || text.charAt(text.length - 1) != ']') {
                    return;
                }

                var a = eval(text);
                if (search == opt.search && a.length == 3 && a[1].length) {
                    for (var i = 0, len = a[1].length; i < len; ++i) {
                        var row       = {};
                        row.id        = a[1][i].id;
                        row.name      = row['name_' + Locale.getName()] = a[1][i].name.substring(1);
                        row.quality   = 7 - a[1][i].name.charAt(0);
                        row.icon      = a[1][i].icon;
                        row.level     = a[1][i].level;
                        row.classs    = a[1][i].classs;
                        row.subclass  = a[1][i].subclass;
                        row.jsonequip = $WH.dO(a[1][i]);

                        row._type = 3;
                        row._summary = _this;

                        g_items.add(a[1][i].id, row);

                        if (_this.searchType == row._type) {
                            searchResults.push(row);
                        }
                    }

                    for (var i = 0, len = a[2].length; i < len; ++i) {
                        var row = {};
                        row.id        = a[2][i].id;
                        row.name      = row['name_' + Locale.getName()] = a[2][i].name.substring(1);
                        row.quality   = 7 - a[2][i].name.charAt(0);
                        row.minlevel  = a[2][i].minlevel;
                        row.maxlevel  = a[2][i].maxlevel;
                        row.type      = a[2][i].type;
                        row.pieces    = a[2][i].pieces;
                        row.jsonequip = {};

                        for (var j = 0, len2 = row.pieces.length; j < len2; ++j) {
                            if (g_items[row.pieces[j]]) {
                                var item = g_items[row.pieces[j]];
                                for (var k in item.jsonequip) {
                                    if (LANG.traits[k] == null) {
                                        continue;
                                    }
                                    if (!row.jsonequip[k]) {
                                        row.jsonequip[k] = 0;
                                    }
                                    row.jsonequip[k] += item.jsonequip[k];
                                }
                            }
                        }

                        row._type = 4;
                        row._summary = _this;

                        if (_this.searchType == row._type) {
                            searchResults.push(row);
                        }
                    }
                    lv.searchable = true;
                    $WH.ee(_this.searchMsg)
                }
                else {
                    $WH.st(_this.searchMsg, $WH.sprintf(LANG.su_noresults, opt.search));
                    _this.searchMsg.className = 'q10';
                }

                lv.setData(searchResults);
                lv.clearSearch();
                lv.updateFilters(true);

                _this.sortPickerWindow(lv);
            }
        });
    },

    // Item slots to ignore when checking if the column can be viewed in 3D
    ignoredSlots: {
         2: 1, // Neck
        11: 1, // Finger
        12: 1, // Trinket
        18: 1, // Bag
        24: 1, // Projectile
        28: 1  // Relic
    },

    viewableIn3d: function(col) {
        for (var i = 0, len = col.group.length; i < len; ++i) {
            var d = g_items[col.group[i][0]].jsonequip;
            if (d.slotbak > 0 && d.displayid > 0 && !this.ignoredSlots[d.slotbak]) {
                return true;
            }
        }

        return false;
    },

    viewIn3dFromPound: function(pound) {
        var p = parseInt(pound) | 0;
        if (p >= 0 && p < this.groups.length) {
            this.viewIn3d(this.columns[this.clone.i + p]);
        }
    },

    viewIn3d: function(col) {
        var stuff = [];

        for (var i = 0, len = col.group.length; i < len; ++i) {
            var d = g_items[col.group[i][0]].jsonequip;
            if (d.slotbak > 0 && d.displayid > 0 && !this.ignoredSlots[d.slotbak]) {
                stuff.push(d.slotbak);
                stuff.push(d.displayid);
            }
        }

        ModelViewer.show({
            type: 4,
            typeId: 9999,
            equipList: stuff,
            extraPound: col.i,
            noPound: (this.autoSave ? 1 : null)
        });
    },

    splitColumn: function(col) {
        this.groups.splice(col.i, 1);

        for (var i = 0, len = col.group.length; i < len; ++i) {
            this.groups.splice(col.i, 0, [col.group[i]]);
        }

        this.refreshAll();
    },

    hasEnhancements: function(group) {
        for (var i = 0, len = group.length; i < len; ++i) {
            for (var j = 1; j < 8; ++j) {
                if (group[i][j] != 0) {
                    return true;
                }
            }
        }

        return false;
    },

    clearEnhancements: function(col, n) {
        var
            start = 0,
            len = col.group.length;

        if (!isNaN(n)) {
            start = n;
            len = n + 1;
        }

        for (var i = start; i < len; ++i) {
            col.group[i] = this.readItem(col.group[i][0] + '.' + col.group[i][1]);
        }

        this.refreshAll();
    },

    readGroups: function(txt) { // Used to read either the 'compare' GET value or the 'compare_groups' cookie.
        if (!txt) {
            return;
        }

        var parts = txt.split(';');
        for (var i = 0, len = parts.length; i < len; ++i) {
            var
                group = [],
                itemIds = parts[i].split(':');

            for (var j = 0, len2 = itemIds.length; j < len2; ++j) {
                var item = this.readItem(itemIds[j]);

                if (g_items[item[0]]) {
                    group.push(item);
                }
            }

            if (group.length) {
                this.groups.push(group);
            }
        }
    },

    readItem: function(txt) {
        txt += ''; // Force string type

        var item = txt.split('.', 8);
        while (item.length < 8) {
            item.push(0);
        }

        for (var i = 0; i < 8; ++i) {
            item[i] = parseInt(item[i]);
        }

        return item;
    },

    writeItem: function(item) {
        return item[0] ? item.join('.').replace(/(\.0)+$/, "") : '';
    },

    getItemName: function(item) {
        if (!g_items[item[0]]) {
            return;
        }

        var name = g_items[item[0]].jsonequip.name.substr(1);

        if (g_items[item[0]].jsonequip.subitems && g_items[item[0]].jsonequip.subitems[item[1]]) {
            name += ' ' + g_items[item[0]].jsonequip.subitems[item[1]].name;
        }

        return name;
    },

    getItemRel: function(item, col) {
        if (!g_items[item[0]]) {
            return;
        }

        var
            extraSocket = this.hasExtraSocket(item),
            rel         = [],
            gems        = [],
            pcs         = [];

        if (item[1]) {
            rel.push('rand=' + item[1]);
        }

        if (item[2]) {
            rel.push('ench=' + item[2]);
        }

        for (var i = 0, len = (g_items[item[0]].jsonequip.nsockets | 0) + extraSocket; i < len; ++i) {
            gems.push(item[4 + i] > 0 ? item[4 + i] : 0);
        }

        if (gems.length) {
            rel.push('gems=' + gems.join(':'));
        }

        if (extraSocket) {
            rel.push('sock');
        }

        if (g_items[item[0]].jsonequip.itemset) {
            for (var i = 0, len = col.group.length; i < len; ++i) {
                var p = col.group[i][0];
                if (g_items[p] && g_items[p].jsonequip.itemset) {
                    pcs.push(p);
                }
            }
            rel.push('pcs=' + pcs.join(':'));
        }

        if (this.level < 80)
            rel.push('lvl=' + this.level);

        var result = rel.join('&');

        if (result) {
            result = '&' + result;
        }

        return result;
    },

    readWeights: function(txt) { // Used to read either the 'weights' GET value or the 'compare_weights' cookie.
        if (!txt) {
            return;
        }

        var
            parts = txt.split(';'),
            nScales = parseInt(parts.length / 3); // Name, Ids, Values, Name, Ids, Values, ...

        if (nScales > 0) {
            for (var i = 0; i < nScales; ++i) {
                var
                    name   = parts[i],
                    ids    = parts[i + nScales].split(':'),
                    values = parts[i + nScales * 2].split(':');

                if (ids.length > 0 && ids.length == values.length) {
                    var
                        weight = { name: name },
                        found = false;

                        for (var j = 0, len = ids.length; j < len; ++j) {
                        var filter = fi_Lookup(ids[j], 'items');
                        if (filter && filter.type == 'num') {

                            weight[filter.name] = parseInt(values[j]);
                            found = true;
                        }
                    }

                    if (found) {
                        this.weights.push(weight);
                    }
                }
            }
        }
    }
};

Summary.dragGroup = function(e, obj, orig) {
    obj.style.width = orig.offsetWidth + 'px';
};

Summary.moveGroup = function(e, obj, targ, cursor) {
    if (!obj.__col.group.length) {
        return;
    }

    var
        group = [],
        pos;

    $WH.cO(group, this.groups[obj.__col.i]);

    if (!targ) {
        if (confirm(LANG.message_deletegroup)) {
            this.deleteColumn(obj.__col, e);
        }

        return;
    }
    else if (cursor && this.newgroup != null && targ.__col.id == this.columns[this.newgroup].id) {
        pos = this.groups.length - 1;
    }
    else if (cursor && targ.__col.id != obj.__col.id) {
        pos = targ.__col.i;
    }
    else {
        return;
    }

    if (!e.shiftKey) {
        if (this.selected != null && this.clone.i + obj.__col.i <= this.selected) {
            this.selected--;
        }

        this.groups.splice(obj.__col.i, 1);
    }

    if (this.__selected.id == obj.__col.id) {
        this.selected = this.clone.i + pos;
    }
    else if (this.selected != null && this.clone.i + pos <= this.selected) {
        this.selected++;
    }

    this.groups.splice(pos, 0, group);
    this.refreshAll();
};

Summary.moveGroupItem = function(e, obj, targ) {
    if (!obj.__col.group.length) {
        return;
    }

    var item = [];
    $WH.cO(item, obj.__col.group[obj.i]);

    if (!targ || (targ.__col.group && !targ.__col.group.length)) {
        if (confirm($WH.sprintf(LANG.message_deleteitem, g_items[obj.__col.group[obj.i][0]].jsonequip.name.substring(1)))) {
            obj.__col.group.splice(obj.i, 1);
            this.refreshAll();
        }
        return;
    }
    else if (this.newgroup != null && targ.__col.id == this.columns[this.newgroup].id) {
        this.groups.push([item]);
    }
    else if (targ.__col.id != obj.__col.id) {
        targ.__col.group.push(item);
    }
    else if (e.shiftKey) {
        obj.__col.group.push(item);
    }
    else {
        return;
    }

    if (!e.shiftKey) {
        obj.__col.group.splice(obj.i, 1);
    }
    this.refreshAll();
};

Summary.addGroupItem = function(type, item) {
    if (type == 3) {
        this.groups.push([this.readItem(item.id)]);
    }
    else if (type == 4) {
        var _ = [];
        for (var i in g_items) {
            if (g_items[i].jsonequip && g_items[i].jsonequip.itemset == item.id) {
                _.push(this.readItem(i));
            }
        }
        this.groups.push(_);
    }

    this.refreshAll();
    Lightbox.hide();
    if (type == 3 && g_items[item.id].jsonequip.subitems) {

        var lastGroup;

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var reali = this.visibility[i];
            var col = this.columns[reali];
            if (col.group && col.group[0][0] == item.id) {
                lastGroup = col;
            }
        }

        if (lastGroup) {
            this.openSubitemPicker(lastGroup, 0);
        }
    }

    return false;
};

Summary.groupOver = function(group, e) {
    var
        buff = '',
        count = {};

    for (var i = 0, len = group.length; i < len; ++i) {
        count[group[i][0]] = (count[group[i][0]] | 0) + 1;
    }

    for (var i = 0, len = group.length; i < len; ++i) {
        var itemId = group[i][0];

        if (g_items[itemId]) {
            buff += '<tr>';
            buff += '<td style="text-align: right">x' + count[itemId] + '</td>';
            buff += '<td><div class="indent q' + g_items[itemId].quality + '">';
            buff += '<span class="icontiny" style="background: url(' + g_staticUrl + '/images/wow/icons/tiny/' + g_items[itemId].icon.toLowerCase() + '.gif) left center no-repeat">' + g_items[itemId]['name_' + Locale.getName()] + '</span>';
            buff += '</div></td>';
            buff += '<td><div class="indent q1"><small><em>' + LANG.level + ' ' + g_items[itemId].jsonequip.level + '</em></small></div></td>';
            buff += '</tr>';
        }
    }

    if (buff) {
        $WH.Tooltip.showAtCursor(e, '<table style="white-space: nowrap">' + buff + '</table>');
    }
};

Summary.addWeightScale = function(scale) {
    scale = this.createWeightScale();

    if (scale) {
        this.weights.push(scale);
    }

    this.toggleOptions();
    this.refreshAll();
};

Summary.resetScale = function() {
    var _ = $WH.ge('su_classes');

    _.selectedIndex = 0;
    _.onchange();
};

Summary.weightOver = function(weight, e) {
    var
        buff = '<b class="q">' + weight.name + '</b>',
        leftTd = '',
        rightTd = '',
        i,
        found = false;

    i = 0;
    for (var w in weight) {
        if (LANG.traits[w] == null) {
            continue;
        }

        if (i++>0) {
            leftTd += '<br />';
        }

        leftTd += weight[w];

        found = true;
    }

    if (found) {
        i = 0;
        for (var w in weight) {
            if (LANG.traits[w] == null) {
                continue;
            }

            if (i++>0) {
                rightTd += '<br />';
            }

            rightTd += LANG.traits[w][1];
        }
        buff += '<table style="white-space: nowrap"><tr><td style="text-align: right">' + leftTd + '</td><td><div class="indent">' + rightTd + '</td></tr></table><span class="q2">' + LANG.su_toggle + '</span>';
    }

    $WH.Tooltip.showAtCursor(e, buff);
};

Summary.socketOver = function(gems, e) {
    var buff = '';

    for (var i in gems) {
        if (g_gems[i]) {
            buff += '<tr>';
            buff += '<td style="text-align: right"><div class="gem' + g_gems[i].colors + '">x' + gems[i] + '</div></td>';
            buff += '<td><div class="indent q' + g_gems[i].quality + '">';
            buff += '<span class="icontiny" style="background: url(' + g_staticUrl + '/images/wow/icons/tiny/' + g_gems[i].icon.toLowerCase() + '.gif) left center no-repeat">' + g_gems[i].name + '</span>';
            buff += '</div></td>';
            buff += '<td><div class="indent q1"><small><em>' + g_gems[i].enchantment + '</em></small></div></td>';
            buff += '</tr>';
        }
    }

    if (buff) {
        $WH.Tooltip.showAtCursor(e, '<table style="white-space: nowrap">' + buff + '</table>');
    }
};

Summary.addItemSubitem = function(subitemId) {
    var
        col = this.currentItem.col,
        i   = this.currentItem.i;

    col.group[i][1] = subitemId;

    this.refreshAll();
    Lightbox.hide();
};

Summary.addItemEnchant = function(enchantId) {
    var
        col = this.currentItem.col,
        i   = this.currentItem.i;

    col.group[i][2] = enchantId;

    this.refreshAll();
    Lightbox.hide();
};

Summary.addItemGem = function(gemId) {
    var
        col = this.currentItem.col,
        i   = this.currentItem.i,
        s   = this.currentItem.socket;

    col.group[i][s] = gemId;

    this.refreshAll();
    Lightbox.hide();
};

Summary.toggleExtraSocket = function(col, i, a) {
    var
        item   = col.group[i],
        socket = this.getExtraSocketPos(item[0]);

    item[socket] = (this.hasExtraSocket(item) ? 0 : -1);

    this.refreshAll();
};

Summary.funcBox = {
    createSockets: function(sockets, td, selected, minSockets, maxSockets, gems) {
        if (!sockets) {
            return;
        }

        var gem_colors = {
             1 : 'meta',
             2 : 'red',
             4 : 'yellow',
             8 : 'blue',
            14 : 'prismatic'
        };

        var
            color,
            j = 0;

        for (color in gem_colors) {
            if (sockets[color] != null && sockets[color] != 0) {
                ++j;
            }
        }

        if (j == 0) {
            return;
        }

        var div = $WH.ce('div');
        div.style.paddingBottom = '3px';
        $WH.ae(td, div);

        var
            i = 0,
            j = 0;

        for (color in gem_colors) {
            if (sockets[color] != null && sockets[color] != 0) {
                if (j > 0) {
                    if (j % 2 == 0) {
                        var div = $WH.ce('div');
                        div.style.paddingBottom = '3px';
                        $WH.ae(td, div);
                    }
                    else {
                        $WH.ae(div, $WH.ct(String.fromCharCode(160, 160)));
                    }
                }

                var a = $WH.ce('a');
                a.href = '?items=3&filter=' + (color == 14 ? 'gb=1;cr=81:81:81;crs=2:3:4;crv=0:0:0;ma=1' : 'cr=81;crs=' + (i + 1) + ';crv=0');
                a.className = 'moneysocket' + gem_colors[color];

                if (gems && gems[color]) {
                    for (var x in gems[color]) {
                        if (g_gems[x]) {
                            a.className += ' tip';
                            a.style.borderBottom = '1px dotted #808080';
                            break;
                        }
                    }

                    a.onmouseover = Summary.socketOver.bind(a, gems[color]);
                    a.onmousemove = $WH.Tooltip.cursorUpdate;
                    a.onmouseout = $WH.Tooltip.hide;
                }

                if (selected) {
                    a.className += ' ' + (sockets[color] > 0 ? 'q2' : 'q10');
                    if (sockets[color] > 0) {
                        $WH.ae(a, $WH.ct('+'));
                    }
                }
                else if (minSockets && maxSockets && minSockets[color] != maxSockets[color] && sockets[color] == maxSockets[color]) {
                    a.style.fontWeight = 'bold';
                    a.className += ' q2';
                }

                $WH.ae(a, $WH.ct(sockets[color]));
                $WH.ae(div, a);

                ++j;
            }

            ++i;
        }
    }
};

Summary.templates = {
    compare: {
        total: 1,
        clone: 2,
        newgroup: 3,
        textable: 1,
        enhanceable: 1,

        columns: [
            {
                id: 'name',
                align: 'left',
                compute: function(trait, td, tr, i) {
                    if (!this.__total.total[trait.id]) {
                        tr.style.display = 'none';
                    }

                    td.style.whiteSpace = 'nowrap';
                    td.style.paddingLeft = '4px';
                    td.style.paddingRight = '20px';

                    var res = (LANG.traits[trait.id] ? LANG.traits[trait.id][0] : trait.name);
                    if (trait.id == 'gains') {
                        var sp = $WH.ce('span');
                        sp.className = 'tip';
                        sp.onmouseover = function() {
                            $WH.Tooltip.show(this, LANG.tooltip_gains, 0, 0, 'q');
                        };
                        sp.onmousemove = $WH.Tooltip.cursorUpdate;
                        sp.onmouseout = $WH.Tooltip.hide;
                        $WH.ae(sp, $WH.ct(res));
                        $WH.ae(td, sp);
                    }
                    else {
                        return res;
                    }
                }
            },
            { id: 'total', name: 'Total', align: 'center', hidden: 1 },
            { align: 'center' },
            { id: 'newgroup', align: 'center', width: '100%' }
        ],

        traits: [
            { id: 'sepgeneral', type: 'sep' },
            {
                id: 'score',
                name: LANG.score,
                type: 'custom',
                json: ['id'],
                calcTotal: function(item, total, isTotal) {
                    if (this.scoreMode == null) {
                        this.scoreMode = 0;
                    }

                    for (var i = 0, len = this.weights.length; i < len; ++i) {
                        var
                            score = 0,
                            f = 0,

                        weights = this.weights[i];

                        for (var w in weights) {
                            if (!LANG.traits[w]) {
                                continue;
                            }

                            f += weights[w];
                            if (!isNaN(item[w])) {
                                score += item[w] * weights[w];
                            }
                        }
                        total[i] = (total[i] | 0) + score;
                        this.weights[i].f = f;

                        if (!isTotal && (this.weights[i].m == null || total[i] > this.weights[i].m)) {
                            this.weights[i].m = total[i];
                        }
                    }
                },
                calcDifference: function(a, b) {
                    var diff = {};
                    for (var i = 0, len = this.weights.length; i < len; ++i) {
                        diff[i] = a[i] - b[i];
                    }

                    return diff;
                },
                compute: function(col, td) {
                    if (!col.total || !col.difference) {
                        return;
                    }

                    var n = 0,
                    _ = (this.selected != null && this.__selected.id != col.id);

                    td.style.whiteSpace = 'nowrap';
                    td.style.padding = '4px';

                    for (var i = 0, len = this.weights.length; i < len; ++i) {
                        var weight = this.weights[i];
                        var
                            score = (_ ? col.difference.score[i] : col.total.score[i]),
                            name  = weight.name;

                        if (score != null) {
                            if (!weight.name) {
                                weight.name = $WH.sprintf(LANG.su_customscale, ++n);
                            }

                            switch (this.scoreMode) {
                            case 0:
                                score = (weight.f != 0 ? score / weight.f : 0).toFixed(2);
                                break;
                            case 2:
                                score = (weight.m ? 100 * score / weight.m : 0).toFixed(1) + '%';
                                break;
                            }

                            var a = $WH.ce('a');
                            a.className = 'summary-score-remove';
                            a.href = 'javascript:;';
                            a.onclick = this.deleteWeightScale.bind(this, i);
                            $WH.ae(a, $WH.ct('x'));
                            $WH.ae(td, a);

                            var d = $WH.ce('div');
                            d.className = 'summary-score-row' + (i % 2);
                            $WH.ae(td, d);

                            var a = $WH.ce('a');
                            a.className = (_ ? (score > 0 ? 'q2' : 'q10') : 'q1');
                            a.style.borderBottom = '1px dotted #808080';
                            a.href = 'javascript:;';
                            a.onclick = this.toggleWeights.bind(this);
                            a.onmouseover = Summary.weightOver.bind(a, weight);
                            a.onmousemove = $WH.Tooltip.cursorUpdate;
                            a.onmouseout = $WH.Tooltip.hide;
                            $WH.ae(a, $WH.ct(score));
                            $WH.ae(d, a);
                        }
                    }
                }
            },

            { id: 'sepgeneral', type: 'sep' },
            { id: 'level', type: 'avg', digits: 1, itemonly: 1 },
            { id: 'reqlevel', type: 'max' },

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
            {
                id: 'gems',
                name: LANG.gems,
                type: 'custom',
                json: ['id'],
                itemonly: 1,
                calcTotal: function(item, total, isTotal, equipInfo, extraSocket) {
                    for (var i = 1; i <= 8; i *= 2) {
                        if (total[i] == null) {
                            total[i] = {};
                        }
                    }

                    if (total[14] == null) {
                        total[14] = {};
                    }

                    for (var i = 1; i < 4; ++i) {
                        if ((s = item['socket' + i]) && equipInfo[i + 3]) {
                            total[s][equipInfo[i + 3]] = (total[s][equipInfo[i + 3]] | 0) + 1;
                        }
                    }

                    i = (item.nsockets | 0) + 4;
                    if (extraSocket && equipInfo[i]) {
                        total[14][equipInfo[i]] = (total[14][i] | 0) + 1;
                    }
                },
                hidden: 1
            },
            {
                id: 'sockets',
                name: LANG.sockets,
                type: 'custom',
                json: ['socket1'],
                calcTotal: function(item, total, isTotal, equipInfo, extraSocket) {
                    for (var i = 1; i <= 8; i *= 2) {
                        total[i] = (total[i] | 0);
                    }

                    total[14] = (total[14] | 0);

                    for (var i = 1; i < 4; ++i) {
                        if (s = item['socket' + i]) {
                            total[s]++;
                        }
                    }

                    if (extraSocket) {
                        total[14]++;
                    }
                },
                calcDifference: function(a, b) {
                    var diff = {};
                    for (var i = 1; i <= 8; i *= 2) {
                        if (a[i] != null || b[i] != null) {
                            diff[i] = (a[i] | 0) - (b[i] | 0);
                        }
                    }
                    return diff;
                },
                compute: function(col, td) {
                    var _ = (this.selected != null && this.__selected.id != col.id);
                    var total = _ ? col.difference : col.total;

                    if (!total) {
                        return;
                    }

                    if (this.groups.length > 1) {
                        Summary.funcBox.createSockets(total.sockets, td, _, this.minValue['sockets'], this.maxValue['sockets'], total.gems);
                    }
                    else {
                        Summary.funcBox.createSockets(total.sockets, td, _, null, null, total.gems);
                    }
                }
            },
            { id: 'nsockets', type: 'sum' },
            { id: '', type: 'sep' },
            {
                id: 'gains',
                name: LANG.gains,
                type: 'custom',
                json: ['id'],
                compute: function(col, td) {
                    if (col.id == this.columns[this.total].id || !col.total) {
                        return;
                    }

                    td.style.whiteSpace = 'nowrap';
                    td.style.textAlign = 'left';

                    var i = 0;
                    for (var trait in this.minValue) {
                        if (col.total[trait] > this.minValue[trait]) {
                            if (i++ > 0) {
                                $WH.ae(td, $WH.ce('br'));
                            }

                            var value = (Math.round(1000 * (col.total[trait] - this.minValue[trait])) / 1000);

                            $WH.ae(td, $WH.ct(value + ' ' + LANG.traits[trait][1]));

                            if (this.ratings[trait] != null) {
                                var percent = $WH.g_convertRatingToPercent(this.level, this.ratings[trait], value);

                                percent = $WH.number_format((Math.round(percent * 100) / 100));

                                if (this.ratings[trait] != 12 && this.ratings[trait] != 37) { // Neither Defense, nor Expertise
                                    percent += '%';
                                }

                                var s = $WH.ce('small');
                                $WH.ae(s, $WH.ct(' (' + percent + ')'));
                                $WH.ae(td, s);
                            }
                        }
                    }
                }
            }
        ]
    },

    itemset: {
        total: 1,
        clone: 2,
        editable: 0,

        columns: [
            {
                id: 'name',
                align: 'left',
                compute: function(trait, td, tr, i) {
                    if (!this.__total.total[trait.id]) {
                        tr.style.display = 'none';
                    }

                    return (LANG.traits[trait.id] ? LANG.traits[trait.id][0] : trait.name);
                }
            },
            { id: 'total', name: 'Total', align: 'center' },
            { align: 'center' }
        ],

        traits: [
            { id: 'str', type: 'sum' },
            { id: 'agi', type: 'sum' },
            { id: 'sta', type: 'sum' },
            { id: 'int', type: 'sum' },
            { id: 'spi', type: 'sum' },
            { id: 'health', type: 'sum' },
            { id: 'mana', type: 'sum' },
            { id: 'healthrgn', type: 'sum' },
            { id: 'manargn', type: 'sum' },

            { id: 'armor', type: 'sum' },
            { id: 'block', type: 'sum' },
            { id: 'defrtng', type: 'sum', rating: 12 },
            { id: 'dodgertng', type: 'sum', rating: 13 },
            { id: 'parryrtng', type: 'sum', rating: 14 },
            { id: 'resirtng', type: 'sum', rating: 35 },
            { id: 'blockrtng', type: 'sum', rating: 15 },
            { id: 'atkpwr', type: 'sum' },
            { id: 'feratkpwr', type: 'sum' },
            { id: 'armorpenrtng', type: 'sum', rating: 44 },
            { id: 'critstrkrtng', type: 'sum', rating: 32 },
            { id: 'exprtng', type: 'sum', rating: 37 },
            { id: 'hastertng', type: 'sum', rating: 36 },
            { id: 'hitrtng', type: 'sum', rating: 31 },
            { id: 'splpen', type: 'sum' },
            { id: 'splpwr', type: 'sum' },
            { id: 'arcsplpwr', type: 'sum' },
            { id: 'firsplpwr', type: 'sum' },
            { id: 'frosplpwr', type: 'sum' },
            { id: 'holsplpwr', type: 'sum' },
            { id: 'natsplpwr', type: 'sum' },
            { id: 'shasplpwr', type: 'sum' },

            { id: 'dps', type: 'sum' },
            { id: 'dmg', type: 'sum' },

            { id: 'arcres', type: 'sum' },
            { id: 'firres', type: 'sum' },
            { id: 'frores', type: 'sum' },
            { id: 'holres', type: 'sum' },
            { id: 'natres', type: 'sum' },
            { id: 'shares', type: 'sum' },

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

            {
                id: 'sockets',
                name: LANG.sockets,
                type: 'custom',
                json: ['socket1'],
                calcTotal: function(item, total) {
                    for (var i = 1; i <= 8; i *= 2) {
                        total[i] = (total[i] | 0);
                    }

                    for (var i = 1; i < 4; ++i) {
                        if (s = item['socket' + i]) {
                            total[s]++;
                        }
                    }
                },
                calcDifference: function(a, b) {
                    var diff = {};
                    for (var i = 1; i <= 8; i *= 2) {
                        if (a[i] != null || b[i] != null) {
                            diff[i] = (a[i] | 0) - (b[i] | 0);
                        }
                    }
                    return diff;
                },
                compute: function(col, td) {
                    var _ = (this.selected != null && this.__selected.id != col.id);
                    var total = _ ? col.difference : col.total;

                    if (!total) {
                        return;
                    }

                    if (this.groups.length > 1) {
                        Summary.funcBox.createSockets(total.sockets, td, _, this.minValue['sockets'], this.maxValue['sockets']);
                    }
                    else {
                        Summary.funcBox.createSockets(total.sockets, td, _);
                    }
                }
            }
        ]
    }
};

Listview.templates.compare = {
    sort: [1],
    nItemsPerPage: -1,
    hideBands: 2,
    hideNav: 1 | 2,
    hideHeader: 1,
    searchable: 1,
    searchDelay: 100,
    poundable: 0,
    filtrable: 0,
    clip: {
        w: 780,
        h: 486
    },

    onBeforeCreate: function() {
        this.applySort();
    },

    onSearchSubmit: function(item) {
        if (this.nRowsVisible != 1) {
            return;
        }

        (Summary.addGroupItem.bind(item._summary, item._type, item))();
    },

    columns: [
        {
            id: 'item',
            type: 'text',
            align: 'left',
            value: 'name',
            compute: function(item, td, tr) {
                if (item.none) {
                    tr.style.display = 'none';
                    return;
                }

                var a = $WH.ce('a');
                a.className = 'q' + item.quality;
                a.style.fontFamily = 'Verdana, sans-serif';

                switch (item._type) {
                    case 3:
                        this.template.columns[0].span = 2;

                        var i = $WH.ce('td');
                        i.style.width = '1px';
                        i.style.padding = '0';
                        i.style.borderRight = 'none';

                        var
                            icon = Icon.create(item.icon, 0, null, '?item=' + item.id),
                            link = Icon.getLink(icon);

                        $WH.ae(i, icon);
                        $WH.ae(tr, i);
                        td.style.borderLeft = 'none';

                        link.onclick = $WH.rf;

                        a.href = '?item=' + item.id;
                        break;
                    case 4:
                        a.href = '?itemset=' + item.id;
                        break;
                }

                $WH.ae(a, $WH.ct(item.name));

                $WH.nw(td);
                $WH.ae(td, a);

                tr.onclick = function(e) {
                    e = $WH.$E(e);
                    if (e._button != 2 || e._target != a) {
                        e.preventDefault();
                        (Summary.addGroupItem.bind(item._summary, item._type, item))();
                    }
                };
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.level | a.minlevel, b.level | b.minlevel) ||
                    -$WH.strcmp(a.quality, b.quality) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'level',
            name: LANG.level,
            type: 'range',
            getMinValue: function(item) {
                return item.minlevel | item.level;
            },
            getMaxValue: function(item) {
                return item.maxlevel | item.level;
            },
            compute: function(item, td, tr) {
                if (item.none) {
                    return;
                }

                switch (item._type) {
                    case 3:
                        return item.level;
                        break;
                    case 4:
                        if (item.minlevel > 0 && item.maxlevel > 0) {
                            if (item.minlevel != item.maxlevel) {
                                return item.minlevel + LANG.hyphen + item.maxlevel;
                            }
                            else {
                                return item.minlevel;
                            }
                        }
                        else {
                            return -1;
                        }
                        break;
                }
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.score, b.score) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'pieces',
            name: LANG.pieces,
            getValue: function(item) {
                return item.pieces ? item.pieces.length : null;
            },
            compute: function(item, td) {
                if (item.pieces) {
                    td.style.padding = '0';
                    Listview.funcBox.createCenteredIcons(item.pieces, td);
                }
                else {
                    td.style.display = 'none';
                }
            }
        },
        {
            id: 'type',
            name: LANG.type,
            type: 'text',
            compute: function(item, td, tr) {
                td.className = 'small q1';
                $WH.nw(td);
                var a = $WH.ce('a');

                switch (item._type) {
                    case 3:
                        var it = Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass);
                        a.href = it.url;
                        $WH.ae(a, $WH.ct(it.text));
                        break;
                    case 4:
                        $WH.ae(a, $WH.ct(g_itemset_types[item.type]));
                        break;
                }

                $WH.ae(td, a);
            },
            getVisibleText: function(item) {
                return Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass).text;
            }
        },
        {
            id: 'score',
            align: 'center',
            compute: function(item, td, tr) {
                if (item.score != null) {
                    var
                        n = parseFloat(item.score),
                        s = $WH.ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    $WH.st(s, (n ? (n > 0 ? '+' : '-') + item.score : 0));
                    $WH.ae(td, s);
                }
                else {
                    td.style.display = 'none';
                }
            }
        }
    ]
};

Listview.templates.subitempicker = {
    sort: [1],
    nItemsPerPage: -1,
    hideBands: 2,
    hideNav: 1 | 2,
    hideHeader: 1,
    searchable: 1,
    searchDelay: 100,
    poundable: 0,
    filtrable: 0,
    clip: {
        w: 780,
        h: 486
    },

    onBeforeCreate: function() {
        this.applySort();
    },

    onSearchSubmit: function(subitem) {
        if (this.nRowsVisible != 2 || !subitem.id) { // None
            return;
        }

        (Summary.addItemSubitem.bind(subitem._summary, subitem.id))();
    },

    columns: [
        {
            id: 'subitem',
            type: 'text',
            align: 'left',
            value: 'name',
            compute: function(subitem, td, tr) {
                if (subitem.none) {
                    return;
                }

                var
                    url = '?item=' + subitem.item,
                    item = g_items[subitem.item];

                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(item.icon, 0, null, url),
                    link = Icon.getLink(icon);

                $WH.ae(i, icon);
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = $WH.rf;
                link.rel = 'rand=' + subitem.id;

                var a = $WH.ce('a');

                if (subitem.quality != -1) {
                    a.className = 'q' + item.quality;
                }

                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = url;
                a.rel = 'rand=' + subitem.id;

                $WH.ae(a, $WH.ct(item['name_' + Locale.getName()] + ' ' + subitem.name));

                $WH.nw(td);
                $WH.ae(td, a);

                tr.onclick = function(e) {
                    e = $WH.$E(e);
                    if (e._button != 2 || e._target != a) {
                        e.preventDefault();
                        (Summary.addItemSubitem.bind(subitem._summary, subitem.id))();
                    }
                };
            }
        },
        {
            id: 'enchantment',
            type: 'text',
            align: 'left',
            value: 'enchantment',
            compute: function(subitem, td, tr) {
                if (subitem.none) {
                    return;
                }

                var d = $WH.ce('div');
                d.className = 'small crop';
                td.title = subitem.enchantment;
                $WH.ae(d, $WH.ct(subitem.enchantment));
                $WH.ae(td, d);
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.score, b.score) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'score',
            align: 'center',
            compute: function(subitem, td, tr) {
                if (subitem.none) {
                    return;
                }

                if (subitem.score != null) {
                    var
                        n = parseFloat(subitem.score),
                        s = $WH.ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    $WH.st(s, (n ? (n > 0 ? '+' : '-') + subitem.score : 0));
                    $WH.ae(td, s);
                }
            }
        }
    ]
};

Listview.templates.enchantpicker = {
    sort: [1],
    nItemsPerPage: -1,
    hideBands: 2,
    hideNav: 1 | 2,
    hideHeader: 1,
    searchable: 1,
    searchDelay: 100,
    poundable: 0,
    filtrable: 0,
    clip: {
        w: 780,
        h: 486
    },

    onBeforeCreate: function() {
        this.applySort();
    },

    onSearchSubmit: function(enchant, n) {
        if (this.nRowsVisible != 2 || !enchant.id) { // None
            return;
        }

        (Summary.addItemEnchant.bind(enchant._summary, enchant.id))();
    },

    columns: [
        {
            id: 'enchant',
            type: 'text',
            align: 'left',
            value: 'name',
            span: 2,
            compute: function(enchant, td, tr) {
                if (enchant.none) {
                    return;
                }

                var url = (enchant.source > 0 ? '?spell=' : '?item=') + Math.abs(enchant.source);

                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(enchant.icon, 0, null, url),
                    link = Icon.getLink(icon);

                $WH.ae(i, icon);
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = $WH.rf;

                var a = $WH.ce('a');

                if (enchant.quality != -1) {
                    a.className = 'q' + enchant.quality;
                }

                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = url;

                $WH.ae(a, $WH.ct(enchant.name));

                $WH.nw(td);
                $WH.ae(td, a);

                tr.onclick = function(e) {
                    e = $WH.$E(e);
                    if (e._button != 2 || e._target != a) {
                        e.preventDefault();
                        (Summary.addItemEnchant.bind(enchant._summary, enchant.id))();
                    }
                };
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.quality, b.quality) ||
                    $WH.strcmp(a.type, b.type) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'enchantment',
            type: 'text',
            align: 'left',
            value: 'enchantment',
            compute: function(enchant, td, tr) {
                if (enchant.none) {
                    return;
                }

                var d = $WH.ce('div');
                d.className = 'small crop';
                td.title = enchant.enchantment;
                $WH.ae(d, $WH.ct(enchant.enchantment));
                $WH.ae(td, d);
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.score, b.score) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'skill',
            type: 'text',
            compute: function(enchant, td, tr) {
                if (enchant.none) {
                    return;
                }

                td.className = 'small q0';
                $WH.nw(td);

                if (enchant.skill > 0) {
                    return g_spell_skills[enchant.skill];
                }
                else {
                    return LANG.types[3][0];
                }
            },
            getVisibleText: function(enchant) {
                if (enchant.none) {
                    return;
                }

                if (enchant.skill > 0) {
                    return g_spell_skills[enchant.skill];
                }
                else {
                    return LANG.types[3][0];
                }
            }
        },
        {
            id: 'score',
            align: 'center',
            compute: function(enchant, td, tr) {
                if (enchant.none) {
                    $WH.ee(tr);

                    tr.onclick = Summary.addItemEnchant.bind(enchant._summary, 0);
                    td.colSpan          = 5;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign  = 'center';
                    return LANG.dash + LANG.pr_noneenchant + LANG.dash;
                }

                if (enchant.score != null) {
                    var
                        n = parseFloat(enchant.score),
                        s = $WH.ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    $WH.st(s, (n ? (n > 0 ? '+' : '-') + enchant.score : 0));
                    $WH.ae(td, s);
                }
            }
        }
    ]
};

Listview.templates.gempicker = {
    sort: [1],
    nItemsPerPage: -1,
    hideBands: 2,
    hideNav: 1 | 2,
    hideHeader: 1,
    searchable: 1,
    searchDelay: 100,
    poundable: 0,
    filtrable: 0,
    clip: {
        w: 780,
        h: 486
    },

    onBeforeCreate: function() {
        this.applySort();
    },

    onSearchSubmit: function(gem) {
        if (this.nRowsVisible != 2 || !gem.id) { // None
            return;
        }

        (Summary.addItemGem.bind(gem._summary, gem.id))();
    },

    columns: [
        {
            id: 'gem',
            type: 'text',
            align: 'left',
            value: 'name',
            span: 2,
            compute: function(gem, td, tr) {
                if (gem.none) {
                    return;
                }

                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(gem.icon, 0, null, '?item=' + gem.id),
                    link = Icon.getLink(icon);

                $WH.ae(i, icon);
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = $WH.rf;

                var a = $WH.ce('a');
                a.className = 'q' + gem.quality;
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + gem.id;

                $WH.ae(a, $WH.ct(gem.name));

                $WH.nw(td);
                $WH.ae(td, a);

                tr.onclick = function(e) {
                    e = $WH.$E(e);
                    if (e._button != 2 || e._target != a) {
                        e.preventDefault();
                        (Summary.addItemGem.bind(gem._summary, gem.id))();
                    }
                };
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.quality, b.quality) ||
                    $WH.strcmp(a.colors, b.colors) ||
                    $WH.strcmp(a.icon, b.icon) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'enchantment',
            type: 'text',
            align: 'left',
            value: 'enchantment',
            compute: function(gem, td, tr) {
                if (gem.none) {
                    return;
                }

                var d = $WH.ce('div');
                d.className = 'small crop';
                td.title = gem.enchantment;
                $WH.ae(d, $WH.ct(gem.enchantment));
                $WH.ae(td, d);
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -$WH.strcmp(a.score, b.score) ||
                    $WH.strcmp(a.name, b.name)
                );
            }
        },
        {
            id: 'colors',
            compute: function(gem, td, tr) {
                if (gem.none) {
                    return;
                }

                td.className = 'small gem' + gem.colors;
                $WH.nw(td);

                return g_gem_colors[gem.colors];
            },
            getVisibleText: function(gem) {
                if (gem.none) {
                    return;
                }

                return g_gem_colors[gem.colors];
            }
        },
        {
            id: 'score',
            align: 'center',
            compute: function(gem, td, tr) {
                if (gem.none) {
                    $WH.ee(tr);

                    tr.onclick = Summary.addItemGem.bind(gem._summary, 0);
                    td.colSpan          = 5;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign  = 'center';

                    return LANG.dash + LANG.pr_nonegem + LANG.dash;
                }

                if (gem.score != null) {
                    var
                        n = parseFloat(gem.score),
                        s = $WH.ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    $WH.st(s, (n ? (n > 0 ? '+' : '-') + gem.score : 0));
                    $WH.ae(td, s);
                }
            }
        }
    ]
};
