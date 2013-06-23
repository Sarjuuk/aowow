var g_summaries = {};
var fi_type = 'items';

function Summary(opt) {
    cO(this, opt);

    if (this.id) {
        var divId = this.id;
        if (this.parent) {
            var d = ce('div');
            d.id = divId;
            ae($(this.parent), d);
            this.container = d;
        }
        else {
            this.container = ge(divId);
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

    var GET = g_getGets();
    /* sarjuuk: GET.compare => GET.items, when using flat urls */

    this.autoSave = 1;
    if (GET.compare) {
        this.autoSave = 0;
    }

    if (GET.l && !isNaN(GET.l)) {
        this.level = parseInt(GET.l);
    }
    else {
        var l = gc('compare_level');
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
            this.readGroups(gc('compare_groups'));
        }
    }

    if (this.weights == null) {
        this.weights = [];

        if (this.weightable) {
            if (GET.compare) {
                this.readWeights(GET.weights);
            }
            else {
                this.readWeights(gc('compare_weights'));
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
        cO(this.clone, this.template.columns[this.template.clone]);
        this.clone.i = this.template.clone;
    }

    if (GET.compare && GET.focus != null) {
        var f = parseInt(GET.focus) | 0;
        if (f >= 0 && f < this.groups.length) {
            this.selected = this.clone.i + f;
        }
    }

    this.initialize();
}

Summary.prototype = {
    initialize: function() {
        this.controls = ce('div');
        this.controls.style.display = 'none';
        this.controls.className = 'summary-controls';

        this.textNames = ce('div');
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

        this.noData = ce('div');
        this.noData.className = 'listview-nodata text';

        this.table = ce('table');
        this.thead = ce('thead');
        this.tbody = ce('tbody');

        this.table.id = 'su_table';
        this.table.className = 'grid';

        this.refreshHeader();

        ae(this.table, this.thead);
        ae(this.table, this.tbody);

        this.refreshRows();

        ae(this.container, this.controls);
        ae(this.container, this.textNames);
        ae(this.container, this.table);

        this.itemTip = ce('div');
        this.itemTip.className = 'summary-tip';
        this.itemTip.style.display = 'none';
        var span = ce('span');
        span.innerHTML = LANG.su_itemtip;
        ae(this.itemTip, span);
        ae(this.container, this.itemTip);

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

                var _ = dO(this.clone);

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
            array_walk(this.visibleCols, function(x) { visibleCols[x] = 1; });
        }

        if (this.hiddenCols != null) {
            array_walk(this.hiddenCols, function(x) { hiddenCols[x] = 1; });
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
        var _ = ge('su_weight');
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
        var _ = ge('su_weight');
        var a = ge('su_addweight');

        if (_.childNodes.length >= 14) {
            a.style.display = 'none';
        }

        a = _.childNodes[0].lastChild;
        if (a.nodeName != 'A') {
            ae(_.childNodes[0], ct(String.fromCharCode(160, 160)));
            ae(_.childNodes[0], this.createControl(LANG.firemove, '', '', this.deleteWeight.bind(this, _.childNodes[0].firstChild)));
        }
        else {
            a.firstChild.nodeValue = LANG.firemove;
            a.onmouseup = this.deleteWeight.bind(this, _.childNodes[0].firstChild);
        }

        var
            d = ce('div'),
            c = _.childNodes[0].childNodes[0].cloneNode(true);

        ae(_, d);

        c.onchange = c.onkeyup = this.refreshWeights.bind(this, c);
        ae(d, c);

        ae(d, ct(String.fromCharCode(160, 160)));
        ae(d, this.createControl(LANG.firemove, '', '', this.deleteWeight.bind(this, c)));

        if (e) {
            sp($E(e));
        }

        return c;
    },

    deleteWeight: function(sel, e) {
        var
            d = sel.parentNode,
            c = d.parentNode;

        de(d);

        if (c.childNodes.length == 1) {
            var _ = c.firstChild;
            if (_.firstChild.selectedIndex > 0) {
                var a = _.lastChild;
                a.firstChild.nodeValue = LANG.ficlear;
                a.onmouseup = this.resetWeights.bind(this);
            }
            else {
                while (_.childNodes.length > 1) {
                    de(_.childNodes[1]);
                }
            }
        }

        var a = ge('su_addweight');
        if (c.childNodes.length < 15) {
            a.style.display = '';
        }

        if (e) {
            sp($E(e));
        }
    },

    showDetails: function(a) {
        var foo = ge('su_weight');

        g_toggleDisplay(foo);

        foo = foo.nextSibling;

        a.firstChild.nodeValue = g_toggleDisplay(foo) ? LANG.fihidedetails : LANG.fishowdetails;
    },

    saveScale: function() {
        if (!g_user.id) {
            return;
        }

        var
            opts = gE(ge('su_presets'), 'option'),
            o;

        for (i in opts) {
            if (opts[i].selected) {
                o = opts[i];
                break;
            }
        }

        var
            name = ge('su_scale').value,
            id    = ((o._weights ? o._weights.id : 0) | 0),
            scale = { id: id, name: name },
            _     = ge('su_weight'),
            n     = 0;

        for (i = 0; i < _.childNodes.length; ++i) {
            var
                w    = fi_Lookup(gE(_.childNodes[i], 'select')[0].value),
                inps = gE(_.childNodes[i], 'input'),
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

        var data = [ 'save=1', 'name=' + urlencode(name) ];

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
                        g_user.weightscales = array_filter(g_user.weightscales, function(x) {
                            return x.id != scale.id;
                        });
                    }

                    scale.id = response;
                    g_user.weightscales.push(scale);

                    var
                        s    = ge('su_classes'),
                        opts = gE(s, 'option'),
                        c;

                    for (i in opts) {
                        if (opts[i].value == -1) {
                            c = opts[i];
                            break;
                        }
                    }

                    if (!c) {
                        c = ce('option');
                        c.value = -1;
                        ae(c, ct(LANG.ficustom));
                        aef(s, c);
                        aef(s, gE(s, 'option')[1]);
                    }

                    c._presets = { custom: {} };
                    for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                        c._presets.custom[g_user.weightscales[i].id] = g_user.weightscales[i];
                    }

                    c.selected = true;
                    c.parentNode.onchange();

                    var
                        opts = gE(ge('su_presets'), 'option'),
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
            s    = ge('su_classes'),
            opts = gE(s, 'option'),
            c;

        for (i in opts) {
            if (opts[i].selected) {
                c = opts[i];
                break;
            }
        }

        if (c.value == -1) {
            var
                opts = gE(ge('su_presets'), 'option'),
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

                g_user.weightscales = array_filter(g_user.weightscales, function(x) {
                    return x.id != o.value;
                });

                if (g_user.weightscales.length) {
                    c._presets = { custom: {} };
                    for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                        c._presets.custom[g_user.weightscales[i].id] = g_user.weightscales[i];
                    }
                }
                else {
                    de(c);
                }

                de(o);

                // $('#su_classes, #su_presets').change();
                ge('su_classes').onchange();
                ge('su_presets').onchange();
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

                return (in_array(x, y) < 0 ? y : null);
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
            var _json = dO(json);
            if (_json.scadist && _json.scaflags) {
                g_setJsonItemLevel(_json, level);
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
                    if (in_array(x, y[i]) == -1) {
                        var _ = ce('span');
                        _.className = 'q2';
                        ae(_, ct('+' + y[i]));
                        a.push(_);
                    }
                }
                for (var i = 0, len = x.length; i < len; ++i) {
                    if (in_array(y, x[i]) == -1) {
                        var _ = ce('span');

                        _.className = 'q10';
                        ae(_, ct('-' + x[i]));
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
        else if (this.editable && !Browser.ie) {
            window.onbeforeunload = function(e) { return LANG.message_savebeforeexit };
        }
    },

    refreshHeader: function() {
        if (this.groups.length && this.thead.nodeName.toLowerCase() != 'thead') {
            this.thead = ce('thead');
            ae(this.table, this.thead);
        }

        while (this.thead.firstChild) {
            this.thead.removeChild(this.thead.firstChild);
        }

        this.dragHeaders = [];
        this.dragIcons = [];
        this.dragTargets = [];

        this.selectLevel = ce('div');
        this.selectLevel.style.display = 'none';

        var tr = ce('tr');

        if (!this.groups.length) {
            var th = ce('th');
            ae(tr, th);
            ae(this.thead, tr);
            this.showNoData(th);
            return;
        }

        var groupi = 0;
        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var reali = this.visibility[i];
            var col = this.columns[reali];

            var th = ce('th');
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
                ae(th, this.selectLevel);

                var s = ce('select');
                s.onchange = (function(s) {
                    this.level = s.options[s.selectedIndex].value;
                    this.refreshAll();
                }).bind(this, s);

                for (var i = 80; i > 0; --i) {
                    var o = ce('option');
                    if (i == this.level) {
                        o.selected = true;
                    }
                    st(o, i);
                    ae(s, o);
                }

                ae(this.selectLevel, ct(LANG.su_level + ' '));
                ae(this.selectLevel, s);
            }

            if (col.group) {
                this.dragTargets.push(th);

                if (this.editable) {
                    var _ = ce('div');
                    _.className = 'summary-group';

                    div = ce('div');
                    div.className = 'summary-group-controls';

                    var a = ce('a');
                    a.href = 'javascript:;';
                    a.className = 'summary-group-dropdown';
                    ae(a, ce('span'));
                    a.menu = [
                        [0, LANG.su_export, '?compare=' + this.getGroupData(groupi)]
                    ];
                    a.menu[0].newWindow = 1;

                    if (this.viewableIn3d(col)) {
                        a.menu.push([0, LANG.su_viewin3d, this.viewIn3d.bind(this, col)]);
                    }

                    if (col.group.length > 1) {
                        a.menu.push([0, LANG.su_split, this.splitColumn.bind(this, col)]);
                    }

                    if (this.enhanceable && this.hasEnhancements(col.group)) {
                        a.menu.push([0, LANG.pr_menu_clearenh, this.clearEnhancements.bind(this, col)]);
                    }

                    a.onclick = Menu.showAtCursor;
                    ae(div, a);

                    if (this.groups.length > 1) {
                        a = ce('a');
                        a.href = 'javascript:;';
                        a.className = 'summary-group-focus';
                        var s = ce('span');
                        a.onclick = this.selectColumn.bind(this, col);
                        if (this.selected && this.__selected.id == col.id) {
                            a.onmouseover = function() {
                                Tooltip.show(this, LANG.tooltip_removefocus, 0, 0, 'q');
                            };
                            s.className = 'selected';
                        }
                        else {
                            a.onmouseover = function() {
                                Tooltip.show(this, LANG.tooltip_setfocus, 0, 0, 'q');
                            };
                        }
                        a.onmousemove = Tooltip.cursorUpdate;
                        a.onmouseout = Tooltip.hide;
                        ae(a, s);
                        ae(div, a);
                    }

                    a = ce('a');
                    a.href = 'javascript:;';
                    a.className = 'summary-group-delete';
                    a.onclick = this.deleteColumn.bind(this, col);
                    ae(a, ce('span'));
                    ae(div, a);

                    if (this.draggable) {
                        a = ce('a');
                        a.href = 'javascript:;';
                        a.className = 'summary-group-drag';
                        ae(a, ce('span'));
                        ae(div, a);

                        _.__col = col;
                        _._handle = a;
                        this.dragHeaders.push(_);
                    }

                    ae(_, div);
                    ae(th, _);

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

                var _ = ce('div');
                _.style.margin = '0 auto';
                _.style.clear = 'both';

                var
                    btm = ce('div'),
                    div = _.cloneNode(true);

                if (this.editable) {
                    btm.className = 'summary-group-bottom';
                }

                for (var j = 0; j < len2; ++j) {
                    if (j % iconPerRow == 0) {
                        div.style.width = (iconWidth * iconPerRow) + 'px';
                        div = _.cloneNode(true);
                        ae(btm, div);
                    }

                    var icon = g_items.createIcon(col.group[j][0], iconSize);

                    icon.__col = col;
                    icon.i = j;

                    if (this.enhanceable) {
                        this.refreshItem(col, j, Icon.getLink(icon));
                    }

                    this.dragIcons.push(icon);

                    ae(div, icon);
                }

                if (col.group.length) {
                    div.style.width = (iconWidth * (len2 % iconPerRow == 0 ? iconPerRow : (len2 % iconPerRow))) + 'px';
                }
                ae(btm, div);

                if (this.editable) {
                    th.style.padding = '0';

                    div = ce('div');
                    div.className = 'clear';
                    ae(btm, div);

                    div = ce('div');
                    div.className = 'clear';
                    ae(th.firstChild, div);

                    ae(th.firstChild, btm);
                }
                else {
                    ae(th, btm);
                }

                groupi++;
            }
            else if (col.name) {
                var b = ce('b');
                ae(b, ct(col.name));
                ae(th, b);
            }

            if (this.editable && reali == this.newgroup) {
                this.dragTargets.push(th);
            }

            ae(tr, th);
        }
        ae(this.thead, tr);

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
            var _ = [0, (item[2] ? LANG.pr_menu_repenchant : LANG.pr_menu_addenchant), this.openEnchantPicker.bind(this, col, i)];

            if (item[2] && g_enchants[item[2]]) {
                _.tinyIcon = g_enchants[item[2]].icon;
            }

            menu.push(_);
        }

        // Gems
        if (g_items[itemId].jsonequip.nsockets || extraSocket) {
            for (var k = 0, len3 = (g_items[itemId].jsonequip.nsockets | 0) + extraSocket; k < len3; ++k) {
                var
                    gemId = (item[k + 4] > 0 ? item[k + 4] : 0),
                    c     = (extraSocket && k == len3 - 1 ? 14 : g_items[itemId].jsonequip['socket' + (k + 1)]),
                    _     = [0, (gemId ? LANG.pr_menu_repgem : LANG.pr_menu_addgem), this.openGemPicker.bind(this, col, i, 4 + k, c)];

                if (gemId) {
                    _.tinyIcon = g_gems[gemId].icon;
                }
                else {
                    _.socketColor = c;
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
        a.oncontextmenu = rf;
        a.onclick = this.onMouseClick.bind(0, a);
        a.onmouseup = this.onMouseUp.bind(0, a);
        a.menu = menu;
    },

    removeItem: function(group, i) {
        group.splice(i, 1);
        this.refreshAll();
    },

    refreshRows: function() {
        if (this.groups.length) {
            ae(this.table, this.tbody);
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
            ae(this.tbody, this.getRow(trait, i));
        }

        if (!i) {
            if (!this.groups.length && this.tbody.parentNode) {
                de(this.tbody);
            }
            var tr = ce('tr');
            var td = ce('td');
            var span = ce('span');
            if (this.visibility && this.visibility.length) {
                td.colSpan = this.visibility.length;
            }
            td.style.textAlign = 'center';
            ae(span, ct('The items in this set don\'t have any comparable stats.'));
            ae(td, span);
            ae(tr, td);
            ae(this.tbody, tr);
        }
    },

    getLink: function(groups, weights, level, focus) {
        var href ='?compare';

        if (groups) {
            href += '=' + groups;                           // sarjuuk - with fixed urls: href += '?items=' + groups;

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
            a = ge('su_link'),
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
            var sm = ce('small');
            sm.style.display = 'none';

            var sp = ce('span');
            sp.style.padding = '0 8px';
            sp.style.color = 'white';
            ae(sp, ct('|'));
            ae(sm, sp);

            ae(sm, ct(LANG.pr_note_sort + ' '));

            var s = ce('select');
            ae(sm, s);

            this.sortWeighted = sm;
        }

        var
            s = gE(this.sortWeighted, 'select')[0],
            o = ce('option');

        ee(s);
        ae(s, o);

        if (this.weights && this.weights.length) {
            for (var i = 0, len = this.weights.length; i < len; ++i) {
                o = ce('option');
                o.scale = this.weights[i];
                o.selected = (o.scale == this.currentScale);
                st(o, this.weights[i].name);
                ae(s, o);
            }
        }

        this.sortWeighted.style.display = s.childNodes.length > 1 ? '' : 'none';

        if (lv) {
            ae(ce('div'), this.sortWeighted); // Remove from current parent
            gE(this.sortWeighted, 'span')[0].style.display = (gE(lv.getNoteTopDiv(), 'small').length ? '' : 'none');

            ae(lv.getNoteTopDiv(), this.sortWeighted);
            s.onchange = this.sortPickerWindow.bind(this, lv, 1);
            s.onchange();
        }
    },

    refreshClasses: function(sel) {
        var
            c = sel.options[sel.selectedIndex],
            _ = ge('su_presets');

        while (_.firstChild) {
            de(_.firstChild);
        }
        ae(_, ce('option'));

        if (sel.selectedIndex > 0) {
            for (var _group in c._presets) {
                var weights = c._presets[_group];

                if (LANG.presets[_group] != null) {
                    var group = ce('optgroup');
                    group.label = LANG.presets[_group];
                }
                else {
                    group = _;
                }

                for (var p in weights) {
                    var o = ce('option');
                    o.value = p;
                    o._weights = weights[p];
                    ae(o, ct(weights[p].name ? weights[p].name :LANG.presets[p]));
                    ae(group, o);
                }

                if (LANG.presets[_group] != null && group && group.childNodes.length > 0) {
                    ae(_, group);
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

        var _ = ge('su_classes');

        var o = sel.options[sel.selectedIndex];
        if (sel.selectedIndex > 0) {
            this.updateWeights(o._weights);
            ge('su_scale').value = (_.options[_.selectedIndex].value != -1 ? _.options[_.selectedIndex].text + LANG.hyphen : '') + o.text;
        }

        if (g_user.id > 0) {
            var a = ge('su_remscale');
            a.style.display = (o._weights && o._weights.name ? '' : 'none');
        }
    },

    resetWeights: function() {
        var _ = ge('su_weight');

        ge('su_scale').value = '';

        while (_.childNodes.length >= 2) {
            _.removeChild(_.childNodes[1]);
        }

        var d = _.childNodes[0];
        while (d.childNodes.length > 1) {
            d.removeChild(d.childNodes[1]);
        }
        d.firstChild.selectedIndex = 0;

        var a = ge('su_addweight');
        if (_.childNodes.length < 15) {
            a.style.display = '';
        }
    },

    refreshWeights: function(sel, value) {
        var d = sel.parentNode;

        while (d.childNodes.length > 1) {
            de(d.childNodes[1]);
        }

        if (sel.selectedIndex > 0) {
            ae(d, ct(' '));
            var _ = ce('input');
            _.type = 'text';
            _.value = (value | 0);
            _.maxLength = 7;
            _.style.textAlign = 'center';
            _.style.width = '4.5em';
            _.setAttribute('autocomplete', 'off');
            _.onchange = this.sortWeights.bind(this, _);
            ae(d, _);
            this.sortWeights(_);
        }

        if (d.parentNode.childNodes.length == 1) {
            if (sel.selectedIndex > 0) {
                ae(d, ct(String.fromCharCode(160, 160)));
                ae(d, this.createControl(LANG.ficlear, '', '', this.resetWeights.bind(this)));
            }
        }
        else if (d.parentNode.childNodes.length > 1) {
            ae(d, ct(String.fromCharCode(160, 160)));
            ae(d, this.createControl(LANG.firemove, '', '', this.deleteWeight.bind(this, sel)));
        }
    },

    sortWeights: function(input) {
        var
            _ = ge('su_weight'),
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
            ae(_, c);
        }
    },

    saveComparison: function(refresh) {
        window.onbeforeunload = null;

        // g_setWowheadCookie('compare_groups', this.getGroupData(), true);
        sc('compare_groups', 20, this.getGroupData(), '/', location.hostname);
        // g_setWowheadCookie('compare_weights', rtrim(this.getWeightData(1).join(';'), ';'), true);
        sc('compare_weights', 20, rtrim(this.getWeightData(1).join(';'), ';'), '/', location.hostname);
        // g_setWowheadCookie('compare_level', this.level, true);
        sc('compare_level', 20, this.level, '/', location.hostname);

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
            n += (plain ? this.weights[i].name : urlencode(this.weights[i].name));

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
            de(div.firstChild);
        }
        div.style.display = 'none';

        div = this.noData;
        while (div.firstChild) {
            de(div.firstChild);
        }
        ae(container, div);

        var result = -1;
        if (this.template.onNoData) {
            result = (this.template.onNoData.bind(this, div))();
        }
        if (result == -1) {
            ae(div, ct(LANG.su_additems || LANG.lvnodata));
        }
    },

    showTextNames: function() {
        var div = this.textNames;
        div.style.display = '';
        div.style.paddingTop = '10px';

        while (div.firstChild) {
            de(div.firstChild);
        }

        ae(div, ct(LANG.su_comparing));

        var n = 0;
        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var reali = this.visibility[i];
            var col = this.columns[reali];

            if (!col.group || !col.group.length) {
                continue;
            }

            if (n++ > 0) {
                ae(div, ct(LANG.su_comparewith));
            }

            if (col.group.length == 1) {
                var item = g_items[col.group[0][0]].jsonequip;

                var a = ce('a');
                a.className = 'q' + (7 - parseInt(item.name.charAt(0)));
                a.href = '?item=' + item.id;
                a.rel = this.getItemRel(col.group[0], col);
                ae(a, ct('[' + this.getItemName(col.group[0]) + ']'));
                ae(div, a);
            }
            else {
                var sp = ce('span');
                sp.onmouseover = Summary.groupOver.bind(a, col.group);
                sp.onmousemove = Tooltip.cursorUpdate;
                sp.onmouseout = Tooltip.hide;
                sp.className = 'tip';
                ae(sp, ct('[' + col.group.length + ' ' + LANG.types[3][3] + ']'));
                ae(div, sp);
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
            de(div.firstChild);
        }

        var div2 = ce('div');
        div2.className = 'summary-controls-right';
        ae(div, div2);

        a = this.createControl(LANG.su_help, null, 'help-icon', null, '?help=item-comparison');
        a.target = '_blank';
        ae(div2, a);

        if (this.searchable) {
            a = this.createControl(LANG.su_addset, 'su_addset', 'additem-icon', this.openItemPicker.bind(this, 4));
            ae(div2, a);

            a = this.createControl(LANG.su_additem, 'su_additem', 'additem-icon', this.openItemPicker.bind(this, 3));
            ae(div2, a);
        }

        if (this.groups.length) {
            if (this.weightable) {
                a = this.createControl(LANG.su_addscale, 'su_addscale', 'additem-icon', this.toggleOptions.bind(this, 'su_weights', 'su_addscale'));
                ae(div2, a);
            }
        }

        aE(document, 'click', this.toggleOptions.bind(this, null, null));

        var _ = ce('div');
        _.className = 'clear';
        ae(div2, _);

        if (this.weightable) {
            var
                d2 = ce('div'),
                d = ce('div');

            d2.style.display = 'none';
            d2.id = 'su_weights';
            d.className = 'summary-weights-inner';
            ae(d2, d);
            ae(div2, d2);

            var
                t = ce('table'),
                tbody = ce('tbody'),
                tr = ce('tr'),
                td = ce('td');

            ae(t, tbody);
            ae(tbody, tr);
            ae(tr, td);

            ae(td, ct(LANG.su_preset));

            td = ce('td');
            ae(tr, td);

            var s = ce('select');
            s.id = 'su_classes';
            s.onchange = s.onkeyup = this.refreshClasses.bind(this, s);
            ae(s, ce('option'));
            ae(td, s);

            if (g_user.weightscales != null && g_user.weightscales.length) {
                var o = ce('option');
                o.value = -1;
                o._presets = { custom: {} };
                ae(o, ct(LANG.ficustom));
                ae(s, o);

                for (var i = 0, len = g_user.weightscales.length; i < len; ++i) {
                    o._presets.custom[g_user.weightscales[i].id] = g_user.weightscales[i];
                }
            }

            var temp = [];
            for (var c in wt_presets) {
                temp.push(c);
            }
            temp.sort(function(a, b) {
                return strcmp(g_chr_classes[a], g_chr_classes[b]);
            });

            for (var i = 0, len = temp.length; i < len; ++i) {
                var
                    c = temp[i],
                    o = ce('option');

                o.value = c;
                o._presets = wt_presets[c];
                ae(o, ct(g_chr_classes[c]));
                ae(s, o);
            }

            _ = ce('span');
            _.style.display = 'none';
            ae(_, ct(' '));
            ae(td, _);

            s = ce('select');
            s.id = 'su_presets';
            s.onchange = s.onkeyup = this.refreshPresets.bind(this, s);
            ae(s, ce('option'));
            ae(_, s);

            ae(td, ct(' '));
            a = ce('a');
            a.href = 'javascript:;';
            ae(a, ct(LANG.fishowdetails));
            a.onclick = this.showDetails.bind(this, a);
            ae(td, a);

            if (g_user.id > 0) {
                ae(td, ct(' '));
                a = ce('a');
                a.href = 'javascript:;';
                a.className = 'save-icon';
                a.appendChild(ct(LANG.fisavescale));
                a.onclick = this.saveScale;
                a.onmousedown = rf;
                ae(td, a);

                ae(td, ct(' '));
                a = ce('a');
                a.href = 'javascript:;';
                a.id = 'su_remscale';
                a.className = 'clear-icon';
                a.style.display = 'none';
                a.appendChild(ct(LANG.fideletescale));
                a.onclick = this.deleteScale;
                a.onmousedown = rf;
                ae(td, a);
            }

            tr = ce('tr');
            td = ce('td');
            ae(tbody, tr);
            ae(tr, td);

            ae(td, ct(LANG.su_name));

            td = ce('td');
            ae(tr, td);

            var i = ce('input');
            i.type = 'text';
            i.id = 'su_scale';
            ae(td, i);
            ae(td, ct(' '));

            ae(d, t);

            var w = ce('div');
            w.style.display = 'none';
            w.id = 'su_weight';
            ae(d, w);
            _ = ce('div');
            ae(w, _);

            s = ce('select');
            s.onchange = s.onkeyup = this.refreshWeights.bind(this, s);
            ae(s, ce('option'));
            ae(_, s);

            _ = false;

            for (var i = 0, len = this.template.traits.length; i < len; ++i) {
                var p = this.template.traits[i];

                if (p.type == 'sep') {
                    if (_ && _.childNodes.length > 0) {
                        ae(s, _);
                    }
                    _ = ce('optgroup');
                    _.label = (LANG.traits[p.id] ? LANG.traits[p.id] : p.name);
                }
                else if (p.type != 'custom') {
                    var o = ce('option');
                    o.value = p.id;
                    ae(o, ct((p.indent ? '- ' : '') + (LANG.traits[p.id] ? LANG.traits[p.id][0] : p.name)));
                    ae(_, o);
                }
            }
            if (_ && _.childNodes.length > 0) {
                ae(s, _);
            }

            _ = ce('div');
            _.style.display = 'none';
            a = this.createControl(LANG.su_addweight, 'su_addweight', '', this.addWeight.bind(this));
            ae(_, a);
            ae(d, _);

            _ = ce('div');
            _.className = 'summary-weights-buttons';

            a = ce('a');
            a.className = 'help-icon';
            a.href = '?help=stat-weighting';
            a.target = '_blank';
            a.style.border = 'none';
            a.style.cssFloat = a.style.styleFloat = 'right';
            ae(a, ct(LANG.su_help));
            ae(_, a);

            i = ce('input');
            i.type = 'button';
            i.value = LANG.su_applyweight;
            i.onclick = Summary.addWeightScale.bind(this);
            ae(_, i);

            ae(_, ct(' '));

            i = ce('input');
            i.type = 'button';
            i.value = LANG.su_resetweight;
            i.onclick = Summary.resetScale.bind(this);
            ae(_, i);

            ae(d, _);
        }

        if (this.autoSave) {
            a = this.createControl(LANG.su_autosaving, null, 'autosave-icon selected');
        }
        else {
            if (gc('compare_groups')) {
                a = this.createControl(LANG.su_viewsaved, 'su_viewsaved', 'save-icon', this.viewSavedComparison.bind(this, 1));
                ae(div, a);
            }

            a = this.createControl(LANG.su_savecompare, 'su_save', 'save-icon', this.saveComparison.bind(this, 1));
        }
        ae(div, a);

        if (this.groups.length) {
            a = this.createControl(LANG.su_linkcompare, 'su_link', 'link-icon');
            ae(div, a);

            a = this.createControl(LANG.su_clear, null, 'clear-icon', this.resetAll.bind(this));
            ae(div, a);
        }
    },

    getRow: function(trait) {
        var row = this.traits[trait];
        this.createRow(row);
        return row.__tr
    },

    createRow: function(row) {
        var tr = ce('tr');
        row.__tr = tr;

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var reali = this.visibility[i];
            var col = this.columns[reali];

            var
                td = ce('td'),
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
                ae(tr, td);
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
                                ae(_, ct(', '));
                            }
                            if (result[j].appendChild) {
                                ae(_, result[j]);
                            }
                            else {
                                ae(_, ct(result[j]));
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
                            result = (result < 0 ? '-' : '') + number_format(Math.abs(result));
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
                    var percent = (result < 0 ? -1 : 1) * g_convertRatingToPercent(this.level, row.rating, Math.abs(result));
                    percent = number_format((Math.round(percent * 100) / 100));

                    if (row.rating != 12 && row.rating != 37) { // Neither Defense, nor Expertise
                        percent += '%';
                    }

                    var a = ce('a');

                    a.className = (_.className ? _.className : 'q1');
                    a.style.borderBottom = '1px dotted #808080';
                    a.onmouseover = (function(rating, percent, level, text) {
                        Tooltip.show(this, rating + ' ' + text + ' (' + sprintf(LANG.tooltip_combatrating, percent, level) + ')<br /><span class="q2">' + LANG.su_toggle + '</span>', 0, 0, 'q');
                    }).bind(a, result, percent, this.level, LANG.traits[row.id][0]);
                    a.onmousemove = Tooltip.cursorUpdate;
                    a.onmouseout = Tooltip.hide;
                    a.onclick = this.toggleRatings.bind(this);

                    ae(a, ct(sign + (this.ratingMode ? percent : result)));

                    aef(_, a);
                }
                else {
                    aef(_, ct(sign + result));
                }
            }

            if (!row.hidden) {
                ae(tr, td);
            }
        }
    },

    toggleOptions: function(popupId, link, e) {
        e = $E(e);

        if (e && e._button >= 2) {
            return false;
        }

        var
            popups = [ge('su_weights')],
            links  = [ge('su_addscale')],
            popup  = null;

        if (Browser.ie && e && !popupId && in_array(links, e._target) != -1) {
            return;
        }

        if (link) {
            link = ge(link);
        }
        if (popupId && link) { // Params provided -> a link was clicked
            if (link.className.indexOf('selected') != -1) { // Already selected
                link = null;
            }
            else {
                popup = ge(popupId);
            }
        }
        else if (e) {
            var p = e._target;

            if (!p.parentNode) {
                return;
            }

            while (p.parentNode) {
                if (in_array(popups, p) != -1) {
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
            sp(e);
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
        var a = ce('a');

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

        ae(a, ct(text));

        return a;
    },

    createColumn: function() {
        this.groups.push([this.readItem(-1)]);
        this.refreshAll();
    },

    selectColumn: function(col) {
        Tooltip.hide();

        this.selected = (this.__selected.id == col.id ? null : this.clone.i + col.i);
        this.refreshAll();
    },

    deleteColumn: function(col, e) {
        Tooltip.hide();

        e = $E(e);

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
            _ = ge('su_weight'),
            s = ge('su_scale'),
            scale = {},
            found = false;

        scale.name = s.value;
        scale.name = trim(scale.name);
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
        Tooltip.hide();

        this.weights.splice(i, 1);

        if (!this.weights.length) {
            var a = ge('enchants-default-sort');
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

    /* original
        if ($('option:selected', this.sortWeighted)[0])
            this.currentScale = $('option:selected', this.sortWeighted)[0].scale;
    * replacement */

        var opts = gE(this.sortWeighted, 'option');
        for (i in opts) {
            if (opts[i].selected) {
                this.currentScale = opts[i].scale;
                break;
            }
        }
    /* replace end */

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
            sm = ce('small'),
            sp = ce('span'),
            a;

        this.currentSearch = '';
        this.searchTimer = 0;

        ae(sm, ct(LANG.su_note_name));

        this.searchName = ce('input');
        this.searchName.type = 'text';

        aE(this.searchName, 'keyup', this.onSearchKeyUp.bind(this, 333));
        aE(this.searchName, 'keydown', this.onSearchKeyDown.bind(this));

        ae(sm, this.searchName);
        this.searchMsg = sp;
        this.searchMsg.style.fontWeight = 'bold';
        ae(sm, this.searchMsg);
        ae(div, sm);
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
                d     = ce('div'),
                a     = ce('a'),
                clear = ce('div');

            d.className = 'listview';
            ae(dest, d);

            a = ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            ae(a, ce('span'));
            ae(dest, a);

            clear.className = 'clear';
            ae(dest, clear);

            lv = new Listview({
                template: 'compare',
                id: 'items',
                parent: d,
                data: [],
                clip: { w: 780, h: 478 },
                createNote: this.createItemPickerNote.bind(this)
            });

            if (Browser.firefox) {
                aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
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
                d     = ce('div'),
                a     = ce('a'),
                clear = ce('div');

                d.className = 'listview';
            ae(dest, d);

            a = ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            ae(a, ce('span'));
            ae(dest, a);

            clear.className = 'clear';
            ae(dest, clear);

            lv = new Listview({
                template: 'subitempicker',
                id: 'subitems',
                parent: d,
                data: dataz
            });

            if (Browser.firefox) {
                aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
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
            sm = ce('small'),
            a;

        ae(sm, ct(LANG.pr_note_source));

        a = ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 0, null);
        ae(a, ct(LANG.pr_note_all));

        g_setSelectedLink(a, 'enchants0');

        ae(sm, a);
        ae(sm, ct(LANG.comma));

        a = ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 0, 1);
        ae(a, ct(LANG.pr_note_items));

        ae(sm, a);
        ae(sm, ct(LANG.comma));

        a = ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterEnchants.bind(this, a, 0, 2);
        ae(a, ct(LANG.pr_note_profs));

        ae(sm, a);
        ae(div, sm);
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
            )
        );
    },

    filterEnchants: function(a, wut, value) {
        switch (wut) {
        case 0:
            this.enchantSource = value;
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
                d = ce('div'),
                a = ce('a'),
                clear = ce('div');

            dataz.push({ none: 1, __alwaysvisible: 1, _summary: this });

            for (var enchantId in g_enchants) {
                var enchant = { id: enchantId, _summary: this };

                cO(enchant, g_enchants[enchantId]);

                if (typeof enchant.name == 'string') {
                    dataz.push(enchant);
                }
                else {
                    for (var i = 0, len = g_enchants[enchantId].name.length; i < len; ++i) {
                        var row = dO(enchant);

                        row.name   = enchant.name[i];
                        row.source = enchant.source[i];
                        row.slots  = enchant.slots[i];

                        dataz.push(row);
                    }
                }
            }

            d.className = 'listview';
            ae(dest, d);

            a = ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            ae(a, ce('span'));
            ae(dest, a);

            clear.className = 'clear';
            ae(dest, clear);

            lv = new Listview({
                template: 'enchantpicker',
                id: 'enchants',
                parent: d,
                data: dataz,
                createNote: this.createEnchantPickerNote.bind(this),
                customFilter: this.isValidEnchant.bind(this)
            });

            if (Browser.firefox) {
                aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
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
            sm = ce('small'),
            a;

        ae(sm, ct(LANG.pr_note_source));

        a = ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 0, null);
        ae(a, ct(LANG.pr_note_all));

        ae(sm, a);
        ae(sm, ct(LANG.comma));

        a = ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 0, 1);
        ae(a, ct(LANG.pr_note_bc));

        ae(sm, a);
        ae(sm, ct(LANG.comma));

        a = ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 0, 2);
        ae(a, ct(LANG.pr_note_wotlk));
        this.gemSource = 2;
        g_setSelectedLink(a, 'gems0');

        ae(sm, a);
        ae(div, sm);

        sm = ce('small');

        var sp = ce('span');
        sp.style.padding = '0 8px';
        sp.style.color = 'white';
        ae(sp, ct('|'));
        ae(sm, sp);

        ae(sm, ct(LANG.pr_note_color));

        a = ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 1, null);
        ae(a, ct(LANG.pr_note_all));

        ae(sm, a);
        ae(sm, ct(LANG.comma));

        a = ce('a');
        a.href = 'javascript:;';
        a.onclick = this.filterGems.bind(this, a, 1, 1);
        ae(a, ct(LANG.pr_note_match));

        this.gemColor = 1;
        g_setSelectedLink(a, 'gems1');

        ae(sm, a);
        ae(div, sm);
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
                d     = ce('div'),
                a     = ce('a'),
                clear = ce('div');

            dataz.push({ none: 1, __alwaysvisible: 1, _summary: this });

            for (var gemId in g_gems) {
                var gem = { id: gemId, _summary: this };

                cO(gem, g_gems[gemId]);

                dataz.push(gem);
            }

            d.className = 'listview';
            ae(dest, d);

            a = ce('a');
            a.className = 'screenshotviewer-close';
            a.href = 'javascript:;';
            a.onclick = Lightbox.hide;
            ae(a, ce('span'));
            ae(dest, a);

            clear.className = 'clear';
            ae(dest, clear);

            lv = new Listview({
                template: 'gempicker',
                id: 'gems',
                parent: d,
                data: dataz,
                createNote: this.createGemPickerNote.bind(this),
                customFilter: this.isValidGem.bind(this)
            });

            if (Browser.firefox) {
                aE(lv.getClipDiv(), 'DOMMouseScroll', g_pickerWheel);
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
            sms = gE(div, 'small'),
            as = gE(div, 'a');

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
        e = $E(e);

        if (e._button == 3 || e.shiftKey || e.ctrlKey) {
            return false;
        }
    },

    onMouseUp: function(a, e) {
        e = $E(e);
        if (e._button == 3 || e.shiftKey || e.ctrlKey) { // Right click
            var f = Menu.getDiv(0, a.menu);

            var pos = g_getCursorPos(e);
            setTimeout(Menu.showAtCursor.bind(a, null, pos.x, pos.y), 1); // Timeout needed for the context menu to be disabled
            // setTimeout(Menu.showAtXY.bind(null, this.menu, pos.x, pos.y), 1); // 5.x

            Tooltip.hide();
        }

        return false;
    },

    onSearchKeyUp: function(delay) {
        var search = trim(this.searchName.value.replace(/\s+/g, ' '));

        if (search == this.currentSearch && isNaN(delay)) {
            return;
        }
        this.currentSearch = search;

        this.prepareSearch(search, delay);
    },

    onSearchKeyDown: function(e) {
        e = $E(e);

        switch (e.keyCode) {
        case 13: // Enter
            g_listviews.items.submitSearch(e);
            break;
/*  sarjuuk todo: internal misconception ... hide() and cycle(dir) are members of Livesearch and cant be reused here without further adaptation
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
            st(this.searchMsg, sprintf(LANG['su_searching' + this.searchType], search));
        }
        this.searchMsg.className = '';

        this.searchTimer = setTimeout(this.searchItems.bind(this, search, this.searchType), delay);
    },

    searchItems: function(search, type) {
        var
            lv = g_listviews.items,
            _this = this,
            searchResults = [{ none: 1 }];

        lv.searchable = false;
        lv.setData(searchResults);
        lv.clearSearch();
        lv.updateFilters(true);
        this.searchMsg.className = '';

        if (!search && !this.currentScale) {
            st(this.searchMsg, LANG['su_specifyitem' + this.searchType]);
            return;
        }
        st(this.searchMsg, sprintf(LANG['su_searching' + this.searchType], search));

        new Ajax('?search=' + urlencode(search) + '&json&type=' + type + pr_getScaleFilter(this.currentScale, 1), {
            method: 'POST',
            search: search,
            onSuccess: function(xhr, opt) {
                var text = xhr.responseText;
                if (text.charAt(0) != '[' || text.charAt(text.length - 1) != ']') {
                    return;
                }

                var a = eval(text);
                if (search == opt.search && a.length == 3 && a[1].length) {
                    for (var i = 0, len = a[1].length; i < len; ++i) {
                        var row = {};
                        row.id        = a[1][i].id;
                        row.name      = row['name_' + g_locale.name] = a[1][i].name.substring(1);
                        row.quality   = 7 - a[1][i].name.charAt(0);
                        row.icon      = a[1][i].icon;
                        row.level     = a[1][i].level;
                        row.classs    = a[1][i].classs;
                        row.subclass  = a[1][i].subclass;
                        row.jsonequip = dO(a[1][i]);

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
                        row.name      = row['name_' + g_locale.name] = a[2][i].name.substring(1);
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
                    ee(_this.searchMsg)
                }
                else {
                    st(_this.searchMsg, sprintf(LANG.su_noresults, opt.search));
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

    cO(group, this.groups[obj.__col.i]);

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
    cO(item, obj.__col.group[obj.i]);

    if (!targ || (targ.__col.group && !targ.__col.group.length)) {
        if (confirm(sprintf(LANG.message_deleteitem, g_items[obj.__col.group[obj.i][0]].jsonequip.name.substring(1)))) {
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
            buff += '<span class="icontiny" style="background: url(' + g_staticUrl + '/images/icons/tiny/' + g_items[itemId].icon.toLowerCase() + '.gif) left center no-repeat">' + g_items[itemId]['name_' + g_locale.name] + '</span>';
            buff += '</div></td>';
            buff += '<td><div class="indent q1"><small><em>' + LANG.level + ' ' + g_items[itemId].jsonequip.level + '</em></small></div></td>';
            buff += '</tr>';
        }
    }

    if (buff) {
        Tooltip.showAtCursor(e, '<table style="white-space: nowrap">' + buff + '</table>');
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
    var _ = ge('su_classes');

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

    Tooltip.showAtCursor(e, buff);
};

Summary.socketOver = function(gems, e) {
    var buff = '';

    for (var i in gems) {
        if (g_gems[i]) {
            buff += '<tr>';
            buff += '<td style="text-align: right"><div class="gem' + g_gems[i].colors + '">x' + gems[i] + '</div></td>';
            buff += '<td><div class="indent q' + g_gems[i].quality + '">';
            buff += '<span class="icontiny" style="background: url(' + g_staticUrl + '/images/icons/tiny/' + g_gems[i].icon.toLowerCase() + '.gif) left center no-repeat">' + g_gems[i].name + '</span>';
            buff += '</div></td>';
            buff += '<td><div class="indent q1"><small><em>' + g_gems[i].enchantment + '</em></small></div></td>';
            buff += '</tr>';
        }
    }

    if (buff) {
        Tooltip.showAtCursor(e, '<table style="white-space: nowrap">' + buff + '</table>');
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

        var div = ce('div');
        div.style.paddingBottom = '3px';
        ae(td, div);

        var
            i = 0,
            j = 0;

        for (color in gem_colors) {
            if (sockets[color] != null && sockets[color] != 0) {
                if (j > 0) {
                    if (j % 2 == 0) {
                        var div = ce('div');
                        div.style.paddingBottom = '3px';
                        ae(td, div);
                    }
                    else {
                        ae(div, ct(String.fromCharCode(160, 160)));
                    }
                }

                var a = ce('a');
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
                    a.onmousemove = Tooltip.cursorUpdate;
                    a.onmouseout = Tooltip.hide;
                }

                if (selected) {
                    a.className += ' ' + (sockets[color] > 0 ? 'q2' : 'q10');
                    if (sockets[color] > 0) {
                        ae(a, ct('+'));
                    }
                }
                else if (minSockets && maxSockets && minSockets[color] != maxSockets[color] && sockets[color] == maxSockets[color]) {
                    a.style.fontWeight = 'bold';
                    a.className += ' q2';
                }

                ae(a, ct(sockets[color]));
                ae(div, a);

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
                        var sp = ce('span');
                        sp.className = 'tip';
                        sp.onmouseover = function() {
                            Tooltip.show(this, LANG.tooltip_gains, 0, 0, 'q');
                        };
                        sp.onmousemove = Tooltip.cursorUpdate;
                        sp.onmouseout = Tooltip.hide;
                        ae(sp, ct(res));
                        ae(td, sp);
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
                                weight.name = sprintf(LANG.su_customscale, ++n);
                            }

                            switch (this.scoreMode) {
                            case 0:
                                score = (weight.f != 0 ? score / weight.f : 0).toFixed(2);
                                break;
                            case 2:
                                score = (weight.m ? 100 * score / weight.m : 0).toFixed(1) + '%';
                                break;
                            }

                            var a = ce('a');
                            a.className = 'summary-score-remove';
                            a.href = 'javascript:;';
                            a.onclick = this.deleteWeightScale.bind(this, i);
                            ae(a, ct('x'));
                            ae(td, a);

                            var d = ce('div');
                            d.className = 'summary-score-row' + (i % 2);
                            ae(td, d);

                            var a = ce('a');
                            a.className = (_ ? (score > 0 ? 'q2' : 'q10') : 'q1');
                            a.style.borderBottom = '1px dotted #808080';
                            a.href = 'javascript:;';
                            a.onclick = this.toggleWeights.bind(this);
                            a.onmouseover = Summary.weightOver.bind(a, weight);
                            a.onmousemove = Tooltip.cursorUpdate;
                            a.onmouseout = Tooltip.hide;
                            ae(a, ct(score));
                            ae(d, a);
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
                                ae(td, ce('br'));
                            }

                            var value = (Math.round(1000 * (col.total[trait] - this.minValue[trait])) / 1000);

                            ae(td, ct(value + ' ' + LANG.traits[trait][1]));

                            if (this.ratings[trait] != null) {
                                var percent = g_convertRatingToPercent(this.level, this.ratings[trait], value);

                                percent = number_format((Math.round(percent * 100) / 100));

                                if (this.ratings[trait] != 12 && this.ratings[trait] != 37) { // Neither Defense, nor Expertise
                                    percent += '%';
                                }

                                var s = ce('small');
                                ae(s, ct(' (' + percent + ')'));
                                ae(td, s);
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

                var a = ce('a');
                a.className = 'q' + item.quality;
                a.style.fontFamily = 'Verdana, sans-serif';

                switch (item._type) {
                    case 3:
                        this.template.columns[0].span = 2;

                        var i = ce('td');
                        i.style.width = '1px';
                        i.style.padding = '0';
                        i.style.borderRight = 'none';

                        var
                            icon = Icon.create(item.icon, 0, null, '?item=' + item.id),
                            link = Icon.getLink(icon);

                        ae(i, icon);
                        ae(tr, i);
                        td.style.borderLeft = 'none';

                        link.onclick = rf;

                        a.href = '?item=' + item.id;
                        break;
                    case 4:
                        a.href = '?itemset=' + item.id;
                        break;
                }

                ae(a, ct(item.name));

                nw(td);
                ae(td, a);

                tr.onclick = function(e) {
                    e = $E(e);
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
                    -strcmp(a.level | a.minlevel, b.level | b.minlevel) ||
                    -strcmp(a.quality, b.quality) ||
                    strcmp(a.name, b.name)
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
                    -strcmp(a.score, b.score) ||
                    strcmp(a.name, b.name)
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
                nw(td);
                var a = ce('a');

                switch (item._type) {
                    case 3:
                        var it = Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass);
                        a.href = it.url;
                        ae(a, ct(it.text));
                        break;
                    case 4:
                        ae(a, ct(g_itemset_types[item.type]));
                        break;
                }

                ae(td, a);
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
						s = ce('div');

					s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
					st(s, (n ? (n > 0 ? '+' : '-') + item.score : 0));
					ae(td, s);
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

                var i = ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(item.icon, 0, null, url),
                    link = Icon.getLink(icon);

                ae(i, icon);
                ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = rf;
                link.rel = 'rand=' + subitem.id;

                var a = ce('a');

                if (subitem.quality != -1) {
                    a.className = 'q' + item.quality;
                }

                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = url;
                a.rel = 'rand=' + subitem.id;

                ae(a, ct(item['name_' + g_locale.name] + ' ' + subitem.name));

                nw(td);
                ae(td, a);

                tr.onclick = function(e) {
                    e = $E(e);
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

                var d = ce('div');
                d.className = 'small crop';
                td.title = subitem.enchantment;
                ae(d, ct(subitem.enchantment));
                ae(td, d);
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -strcmp(a.score, b.score) ||
                    strcmp(a.name, b.name)
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
                        s = ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    st(s, (n ? (n > 0 ? '+' : '-') + subitem.score : 0));
                    ae(td, s);
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

                var i = ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(enchant.icon, 0, null, url),
                    link = Icon.getLink(icon);

                ae(i, icon);
                ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = rf;

                var a = ce('a');

                if (enchant.quality != -1) {
                    a.className = 'q' + enchant.quality;
                }

                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = url;

                ae(a, ct(enchant.name));

                nw(td);
                ae(td, a);

                tr.onclick = function(e) {
                    e = $E(e);
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
                    -strcmp(a.quality, b.quality) ||
                    strcmp(a.type, b.type) ||
                    strcmp(a.name, b.name)
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

                var d = ce('div');
                d.className = 'small crop';
                td.title = enchant.enchantment;
                ae(d, ct(enchant.enchantment));
                ae(td, d);
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -strcmp(a.score, b.score) ||
                    strcmp(a.name, b.name)
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
                nw(td);

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
                    ee(tr);

                    tr.onclick = Summary.addItemEnchant.bind(enchant._summary, 0);
                    td.colSpan          = 5;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign  = 'center';
                    return LANG.dash + LANG.pr_noneenchant + LANG.dash;
                }

                if (enchant.score != null) {
                    var
                        n = parseFloat(enchant.score),
                        s = ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    st(s, (n ? (n > 0 ? '+' : '-') + enchant.score : 0));
                    ae(td, s);
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

                var i = ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(gem.icon, 0, null, '?item=' + gem.id),
                    link = Icon.getLink(icon);

                ae(i, icon);
                ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = rf;

                var a = ce('a');
                a.className = 'q' + gem.quality;
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + gem.id;

                ae(a, ct(gem.name));

                nw(td);
                ae(td, a);

                tr.onclick = function(e) {
                    e = $E(e);
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
                    -strcmp(a.quality, b.quality) ||
                    strcmp(a.colors, b.colors) ||
                    strcmp(a.icon, b.icon) ||
                    strcmp(a.name, b.name)
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

                var d = ce('div');
                d.className = 'small crop';
                td.title = gem.enchantment;
                ae(d, ct(gem.enchantment));
                ae(td, d);
            },
            sortFunc: function(a, b, col) {
                if (a.none) {
                    return -1;
                }
                if (b.none) {
                    return 1;
                }

                return (
                    -strcmp(a.score, b.score) ||
                    strcmp(a.name, b.name)
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
                nw(td);

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
                    ee(tr);

                    tr.onclick = Summary.addItemGem.bind(gem._summary, 0);
                    td.colSpan          = 5;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign  = 'center';

                    return LANG.dash + LANG.pr_nonegem + LANG.dash;
                }

                if (gem.score != null) {
                    var
                        n = parseFloat(gem.score),
                        s = ce('div');

                    s.className = 'small q' + (n ? (n > 0 ? 2 : 10) : 0);
                    st(s, (n ? (n > 0 ? '+' : '-') + gem.score : 0));
                    ae(td, s);
                }
            }
        }
    ]
};

/* sarjuuk: bandaid .. belongs to profile.js */
function pr_getScaleFilter(scale, noFilter) {
    var temp = [];

    if (scale) {
        for (var i = 0, len = fi_filters.items.length; i < len; ++i) {
            var f = fi_filters.items[i];

            if (LANG.traits[f.name] && scale[f.name]) {
                temp.push([f.id, scale[f.name]]);
            }
        }
    }

    temp.sort(function(a, b) {
        return -strcmp(a[1], b[1]);
    });

    var wt = [], wtv = [];
    for (var i = 0, len = temp.length; i < len; ++i) {
        wt.push(temp[i][0]);
        wtv.push(temp[i][1]);
    }

    if (wt.length && wtv.length) {
        return (noFilter ? '&' : ';gm=3;rf=1;') + 'wt=' + wt.join(':') + (noFilter ? '&' : ';') + 'wtv=' + wtv.join(':');
    }

    return '';
}

