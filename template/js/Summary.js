// FILE ARCHIVED ON Dez 29, 2009
var g_summaries = {};

// required for json parse
var dO = function(a){
    function b() {}
    b.prototype = a;
    return new b
};

function Summary(c) {
	cO(this, c);
	if (this.id) {
		var b = this.id;
		if (this.parent) {
			var g = ce("div");
			g.id = b;
			ae($(this.parent), g);
			this.container = g
		} else {
			this.container = ge(b)
		}
	} else {
		return
	}
	if (this.template) {
		this.templateName = this.template;
		this.template = Summary.templates[this.template]
	} else {
		return
	}
	g_summaries[this.id] = this;
	if (this.editable == null) {
		if (this.template.editable != null) {
			this.editable = this.template.editable
		} else {
			this.editable = 1
		}
	}
	if (this.draggable == null) {
		if (this.template.draggable != null) {
			this.draggable = this.template.draggable
		} else {
			this.draggable = this.editable
		}
	}
	if (this.searchable == null) {
		if (this.template.searchable != null) {
			this.searchable = this.template.searchable
		} else {
			this.searchable = (this.editable && this.draggable)
		}
	}
	if (this.weightable == null) {
		if (this.template.weightable != null) {
			this.weightable = this.template.weightable
		} else {
			this.weightable = this.editable
		}
	}
	if (this.textable == null) {
		if (this.template.textable != null) {
			this.textable = this.template.textable
		} else {
			this.textable = 0
		}
	}
	if (this.enhanceable == null) {
		if (this.template.enhanceable != null) {
			this.enhanceable = this.template.enhanceable
		} else {
			this.enhanceable = this.editable
		}
	}
	if (this.level == null) {
		if (this.template.level != null) {
			this.level = this.template.level
		} else {
			this.level = 80
		}
	}
	var a = g_getGets();
	this.autoSave = 1;
	if (a.compare) {
		this.autoSave = 0
	}
	if (a.l && !isNaN(a.l)) {
		this.level = parseInt(a.l)
	}
    else
	{
		var l = gc('compare_level');
		if (l)
			this.level = l;
	}
	if (this.groups == null) {
		this.groups = [];
		if (a.compare) {
			this.readGroups(a.compare)
		} else {
			this.readGroups(gc("compare_groups"))
		}
	}
	if (this.weights == null) {
		this.weights = [];
		if (this.weightable) {
			if (a.compare) {
				this.readWeights(a.weights)
			} else {
				this.readWeights(gc("compare_weights"))
			}
		}
	}
	this.showTotal = 0;
	if (this.template.total != null) {
		this.total = this.template.total;
		this.showTotal = this.template.columns[this.total].hidden
	}
	if (this.template.clone != null) {
		this.clone = {};
		cO(this.clone, this.template.columns[this.template.clone]);
		this.clone.i = this.template.clone
	}
	if (a.compare && a.focus != null) {
		var e = parseInt(a.focus) | 0;
		if (e >= 0 && e < this.groups.length) {
			this.selected = this.clone.i + e
		}
	}
	this.initialize()
}
Summary.prototype = {
	initialize: function () {
        // todo: move to announcements
	/*	if (this.templateName == "compare" && !gc("compare_first")) {
			var e = ce("div"),
			b = ce("span");
			e.onclick = function (d) {
				d = $E(d);
				if (d._target.nodeName == "A") {
					de(this);
					// sc("compare_first", 20, 1, "/", ".wowhead.com")
					sc("compare_first", 20, 1, "/", location.hostname)
				}
			};
			e.className = "summary-notice";
			b.innerHTML = LANG.su_notice;
			ae(e, b);
			ae(this.container, e)
		} */
		this.controls = ce("div");
		this.controls.style.display = "none";
		this.controls.className = "summary-controls";
		this.textNames = ce("div");
		this.textNames.style.display = "none";
		this.textNames.className = "summary-textnames";
		var c;
		if (this.onBeforeCreate != null) {
			c = this.onBeforeCreate()
		}
		this.updateGroups();
		this.updateTraits();
		this.updateColumns();
		this.updateVisibility();
		if (this.traits.length == 0) {
			this.visibility.shift();
			this.visibility.shift()
		}
		this.noData = ce("div");
		this.noData.className = "listview-nodata text";
		this.table = ce("table");
		this.thead = ce("thead");
		this.tbody = ce("tbody");
		this.table.id = "su_table";
		this.table.className = "grid";
		this.refreshHeader();
		ae(this.table, this.thead);
		ae(this.table, this.tbody);
		this.refreshRows();
		ae(this.container, this.controls);
		ae(this.container, this.textNames);
		ae(this.container, this.table);
		this.itemTip = ce("div");
		this.itemTip.className = "summary-tip";
		this.itemTip.style.display = "none";
		var a = ce("span");
		a.innerHTML = LANG.su_itemtip;
		ae(this.itemTip, a);
		ae(this.container, this.itemTip);
		this.refreshLink();
		if (this.onAfterCreate != null) {
			this.onAfterCreate(c)
		}
		if (this.templateName == "compare") {
			ModelViewer.addExtraPound(this.viewIn3dFromPound.bind(this))
		}
	},
	updateGroups: function () {
		for (var d = 0, a = this.groups.length; d < a; ++d) {
			for (var c = 0, b = this.groups[d].length; c < b; ++c) {
				if (this.groups[d][c][0] == 0 || (b > 1 && this.groups[d][c][0] == -1)) {
					this.groups[d].splice(c, 1); --c; --b
				}
			}
			if (b == 0) {
				if (this.selected != null) {
					if (this.__selected.i == d) {
						this.selected = null
					}
				}
				this.groups.splice(d, 1); --d; --a
			}
		}
		if (this.editable) {
			this.updateControls()
		}
	},
	updateTraits: function () {
		this.traits = [];
		var b, p = false,
		a = [];
		for (var g = 0, h = this.groups.length; g < h; ++g) {
			for (var f = 0, e = this.groups[g].length; f < e; ++f) {
				var n = this.groups[g][f];
				if (g_items[n[0]]) {
					a.push(g_items[n[0]].jsonequip);
					if (!this.enhanceable) {
						continue
					}
					if (g_items[n[0]].jsonequip.subitems && g_items[n[0]].jsonequip.subitems[n[1]]) {
						a.push(g_items[n[0]].jsonequip.subitems[n[1]].jsonequip)
					}
					if (g_enchants[n[2]]) {
						a.push(g_enchants[n[2]].jsonequip)
					}
					if (g_enchants[n[3]]) {
						a.push(g_enchants[n[3]].jsonequip)
					}
					for (var d = 4; d < 8; ++d) {
						if (g_gems[n[d]]) {
							a.push(g_gems[n[d]].jsonequip)
						}
					}
					if (this.hasExtraSocket(n)) {
						a.push({
							nsockets: 1
						})
					}
					if (this.hasSocketBonus(n)) {
						a.push(g_items[n[0]].jsonequip.socketbonusstat)
					}
				}
			}
			if (b = this.getSetBonuses(this.groups[g])) {
				a.push(b)
			}
		}
		for (var g = 0, h = this.template.traits.length; g < h; ++g) {
			var l = this.template.traits[g];
			if (l.type == "sep") {
				p = true;
				continue
			}
			var m = [l.id];
			if (l.json) {
				var m = l.json
			}
			var o = false;
			for (var f = 0, e = a.length; f < e; ++f) {
				for (var d = 0, c = m.length; d < c; ++d) {
					if (a[f][m[d]] && (l.id != "gains" || (this.editable && this.groups.length > 1)) && (l.id != "score" || this.weights.length)) {
						if (p) {
							this.traits.push({})
						}
						p = false;
						this.traits.push(l);
						o = true;
						break
					}
				}
				if (o) {
					break
				}
			}
		}
	},
	updateColumns: function () {
		this.columns = this.template.columns.slice(0);
		if (this.template.newgroup != null) {
			this.newgroup = this.template.newgroup
		}
		if (this.total != null) {
			this.__total = this.columns[this.total]
		} else {
			this.__total = {}
		}
		this.__total.total = {};
		this.minValue = {};
		this.maxValue = {};
		this.ratings = {};
		if (this.clone != null) {
			this.columns.splice(this.clone.i, 1);
			var b = 0;
			for (var d = 0, a = this.groups.length; d < a; ++d) {
				if (this.groups[d].length < 1) {
					continue
				}
				if (b > 0 && this.newgroup != null) {
					this.newgroup++
				}
				var c = {};
				cO(c, this.clone);
				c.id = "group" + d;
				c.group = this.groups[d];
				c.i = d;
				this.columns.splice(this.clone.i + d, 0, c);
				b++
			}
			for (var d = 0, a = this.columns.length; d < a; ++d) {
				this.calcTraitTotals(this.columns[d], this.__total, this.level)
			}
			if (this.selected != null) {
				this.__selected = this.columns[this.selected];
				for (var d = 0, a = this.groups.length; d < a; ++d) {
					if (this.clone.i + d == this.selected) {
						continue
					}
					this.calcTraitDifference(this.columns[this.clone.i + d], this.__selected)
				}
			} else {
				this.__selected = {}
			}
			if (this.total != null && (b == 1 || this.selected != null)) {
				this.__total.hidden = 1
			} else {
				this.__total.hidden = this.showTotal
			}
		}
	},
	updateVisibility: function () {
		this.visibility = [];
		var e = [],
		d = [];
		if (this.visibleCols != null) {
			array_walk(this.visibleCols, function (f) {
				e[f] = 1
			})
		}
		if (this.hiddenCols != null) {
			array_walk(this.hiddenCols, function (f) {
				d[f] = 1
			})
		}
		for (var c = 0, a = this.columns.length; c < a; ++c) {
			var b = this.columns[c];
			if (e[b.id] != null || (!b.hidden && d[b.id] == null)) {
				this.visibility.push(c)
			}
		}
		if (this.groups.length > 1 && this.textable) {
			this.showTextNames()
		} else {
			this.textNames.style.display = "none"
		}
	},

	updateDraggable: function () {
		for (var c = 0, len = this.dragHeaders.length; c < len; ++c) {
			var _ = this.dragHeaders[c];

			_._targets = this.dragTargets;
			Draggable.init(_, {
				container: this.table,
				onDrag: Summary.dragGroup.bind(this),
				onDrop: Summary.moveGroup.bind(this)
			});
            // alert(_.onmousedown);

		}
		for (var c = 0, len = this.dragIcons.length; c < len; ++c) {
			var _ = this.dragIcons[c];

			_._targets = this.dragTargets;
			Draggable.init(_, {
				container: this.table,
				onDrop: Summary.moveGroupItem.bind(this)
			});
		}
	},

	updateWeights: function (g) {
		var e = ge("su_weight");
		var k = e.childNodes[0].childNodes[0];
		var f = 0;
		for (var b in g) {
			if (!LANG.traits[b]) {
				continue
			}
			if (f++>0) {
				k = this.addWeight()
			}
			var h = k.getElementsByTagName("option");
			for (var d = 0, a = h.length; d < a; ++d) {
				if (h[d].value == b) {
					h[d].selected = true;
					break
				}
			}
			this.refreshWeights(k, g[b])
		}
	},
	addWeight: function (g) {
		var f = ge("su_weight");
		var b = ge("su_addweight");
		if (f.childNodes.length >= 14) {
			b.style.display = "none"
		}
		b = f.childNodes[0].lastChild;
		if (b.nodeName != "A") {
			ae(f.childNodes[0], ct(String.fromCharCode(160, 160)));
			ae(f.childNodes[0], this.createControl(LANG.firemove, "", "", this.deleteWeight.bind(this, f.childNodes[0].firstChild)))
		} else {
			b.firstChild.nodeValue = LANG.firemove;
			b.onmouseup = this.deleteWeight.bind(this, f.childNodes[0].firstChild)
		}
		var h = ce("div"),
		i = f.childNodes[0].childNodes[0].cloneNode(true);
		ae(f, h);
		i.onchange = i.onkeyup = this.refreshWeights.bind(this, i);
		ae(h, i);
		ae(h, ct(String.fromCharCode(160, 160)));
		ae(h, this.createControl(LANG.firemove, "", "", this.deleteWeight.bind(this, i)));
		if (g) {
			sp($E(g))
		}
		return i
	},
	deleteWeight: function (g, h) {
		var i = g.parentNode,
		j = i.parentNode;
		de(i);
		if (j.childNodes.length == 1) {
			var f = j.firstChild;
			if (f.firstChild.selectedIndex > 0) {
				var b = f.lastChild;
				b.firstChild.nodeValue = LANG.ficlear;
				b.onmouseup = this.resetWeights.bind(this)
			} else {
				while (f.childNodes.length > 1) {
					de(f.childNodes[1])
				}
			}
		}
		var b = ge("su_addweight");
		if (j.childNodes.length < 15) {
			b.style.display = ""
		}
		if (h) {
			sp($E(h))
		}
	},
	showDetails: function (b) {
		var c = ge("su_weight");
		g_toggleDisplay(c);
		c = c.nextSibling;
		b.firstChild.nodeValue = g_toggleDisplay(c) ? LANG.fihidedetails: LANG.fishowdetails
	},
	calcTraitTotals: function (d, o, level) {
		if (!d.group) {
			return
		}
		d.total = {};
		d.difference = {};
		var q = {
			sum: function (c, f) {
				return (!isNaN(c) ? c: 0) + f
			},
			min: function (c, f) {
				return Math.min((!isNaN(c) ? c: f), f)
			},
			max: function (c, f) {
				return Math.max((!isNaN(c) ? c: 0), f)
			},
			avg: function (c, i, f) {
				return (!isNaN(c) ? c: 0) + (i / f)
			},
			text: function (c, B, A, z, k, i, j) {
				if (k) {
					k = k.bind(this);
					B = k(B, i, z, j)
				}
				return (in_array(c, B) < 0 ? B: null)
			},
			custom: function (c, B, A, z, k, i, j) {
				if (k) {
					k = k.bind(this);
					k(B, c, i, z, j)
				}
				return c
			}
		};
		var e = function (C, i, y, j, k, z, t, E) {
            var x = {};
			cO(x, C);

            if(x.scadist && x.scaflags)
                $WH.g_setJsonItemLevel(x, level);

			var A = (y.type == "text" || y.type == "custom" ? x: (x[i] && typeof x[i] != "object" ? x[i] : 0));
			if (i == "nsockets" && E) {
				A += E
			}
			if (!A) {
				return
			}
			var B = k(j.total[y.id], A, j.group.length, t, z, 0, E),
			D = k(o.total[y.id], A, j.group.length, t, z, 1, E);
			if (y.type == "text") {
				if (B) {
					j.total[y.id].push(B)
				}
				if (D) {
					o.total[y.id].push(D)
				}
			} else {
				j.total[y.id] = B;
				o.total[y.id] = D
			}
		};
		for (var r in this.traits) {
			r = this.traits[r];
			if (r.id && r.type) {
				var b, p = q[r.type].bind(this),
				u = r.calcTotal;
				var v = [r.id];
				if (r.json) {
					v = r.json
				}
				if (d.total[r.id] == null) {
					if (r.type == "text") {
						d.total[r.id] = []
					} else {
						if (r.type == "custom") {
							d.total[r.id] = {}
						}
					}
				}
				if (o.total[r.id] == null) {
					if (r.type == "text") {
						o.total[r.id] = []
					} else {
						if (r.type == "custom") {
							o.total[r.id] = {}
						}
					}
				}
				for (var m = 0, n = d.group.length; m < n; ++m) {
					var a = this.hasExtraSocket(d.group[m]);
					for (var l = 0, h = v.length; l < h; ++l) {
						if (g_items[d.group[m][0]]) {
							if (g_items[d.group[m][0]]) {
								e(g_items[d.group[m][0]].jsonequip, v[l], r, d, p, u, d.group[m], a);
								if (!this.enhanceable || r.itemonly) {
									continue
								}
								if (g_items[d.group[m][0]].jsonequip.subitems && g_items[d.group[m][0]].jsonequip.subitems[d.group[m][1]]) {
									e(g_items[d.group[m][0]].jsonequip.subitems[d.group[m][1]].jsonequip, v[l], r, d, p, u, d.group[m])
								}
								if (g_enchants[d.group[m][2]]) {
									e(g_enchants[d.group[m][2]].jsonequip, v[l], r, d, p, u, d.group[m])
								}
								if (g_enchants[d.group[m][3]]) {
									e(g_enchants[d.group[m][3]].jsonequip, v[l], r, d, p, u, d.group[m])
								}
								for (var g = 4; g < 8; ++g) {
									if (g_gems[d.group[m][g]]) {
										e(g_gems[d.group[m][g]].jsonequip, v[l], r, d, p, u, d.group[m])
									}
								}
								if (this.hasSocketBonus(d.group[m])) {
									e(g_items[d.group[m][0]].jsonequip.socketbonusstat, v[l], r, d, p, u, d.group[m])
								}
							}
						}
					}
				}
				if (b = this.getSetBonuses(d.group)) {
					for (var l = 0, h = v.length; l < h; ++l) {
						e(b, v[l], r, d, p, u, [])
					}
				}
				if (r.rating) {
					this.ratings[r.id] = r.rating
				}
				if (r.type == "sum") {
					if (d.total[r.id] == null) {
						this.minValue[r.id] = 0
					} else {
						if (this.minValue[r.id] == null || this.minValue[r.id] > d.total[r.id]) {
							this.minValue[r.id] = d.total[r.id]
						}
					}
					if (this.maxValue[r.id] == null || this.maxValue[r.id] < d.total[r.id]) {
						this.maxValue[r.id] = d.total[r.id]
					}
				} else {
					if (r.type == "custom") {
						if (this.minValue[r.id] == null) {
							this.minValue[r.id] = {}
						}
						if (this.maxValue[r.id] == null) {
							this.maxValue[r.id] = {}
						}
						for (var w in d.total[r.id]) {
							if (this.minValue[r.id][w] == null || this.minValue[r.id][w] > d.total[r.id][w]) {
								this.minValue[r.id][w] = d.total[r.id][w]
							}
							if (this.maxValue[r.id][w] == null || this.maxValue[r.id][w] < d.total[r.id][w]) {
								this.maxValue[r.id][w] = d.total[r.id][w]
							}
						}
					}
				}
			}
		}
	},
	calcTraitDifference: function (b, g) {
		b.difference = {};
		var a = {
			sum: function (c, f) {
				return (!isNaN(f) ? f: 0) - (!isNaN(c) ? c: 0)
			},
			min: function (c, f) {
				return (!isNaN(f) ? f: 0) - (!isNaN(c) ? c: 0)
			},
			max: function (c, f) {
				return (!isNaN(f) ? f: 0) - (!isNaN(c) ? c: 0)
			},
			avg: function (c, f) {
				return (!isNaN(f) ? f: 0) - (!isNaN(c) ? c: 0)
			},
			text: function (f, m) {
				var j = [];
				for (var l = 0, c = m.length; l < c; ++l) {
					if (in_array(f, m[l]) == -1) {
						var k = ce("span");
						k.className = "q2";
						ae(k, ct("+" + m[l]));
						j.push(k)
					}
				}
				for (var l = 0, c = f.length; l < c; ++l) {
					if (in_array(m, f[l]) == -1) {
						var k = ce("span");
						k.className = "q10";
						ae(k, ct("-" + f[l]));
						j.push(k)
					}
				}
				return j
			},
			custom: function (c, j, i) {
				if (i) {
					i = i.bind(this);
					return i(j, c)
				}
				return {}
			}
		};
		for (var d in this.traits) {
			d = this.traits[d];
			if (d.id && d.type) {
				var e = a[d.type].bind(this),
				h = d.calcDifference;
				b.difference[d.id] = e(g.total[d.id], b.total[d.id], h)
			}
		}
	},
	resetAll: function () {
		this.groups = [];
		this.weights = [];
		this.refreshAll()
	},
	refreshAll: function () {
		this.updateGroups();
		this.updateTraits();
		this.updateColumns();
		this.updateVisibility();
		this.refreshHeader();
		this.refreshRows();
		this.refreshLink();
		if (this.autoSave) {
			this.saveComparison(0)
		}
	},
	refreshHeader: function () {
		if (this.groups.length && this.thead.nodeName.toLowerCase() != "thead") {
			this.thead = ce("thead");
			ae(this.table, this.thead)
		}
		while (this.thead.firstChild) {
			this.thead.removeChild(this.thead.firstChild)
		}
		this.dragHeaders = [];
		this.dragIcons = [];
		this.dragTargets = [];
		this.selectLevel = ce("div");
		this.selectLevel.style.display = "none";
		var d = ce("tr");
		if (!this.groups.length) {
			var f = ce("th");
			ae(d, f);
			ae(this.thead, d);
			this.showNoData(f);
			return
		}
		var l = 0;
		for (var u = 0, v = this.visibility.length; u < v; ++u) {
			var p = this.visibility[u];
			var e = this.columns[p];
			var f = ce("th");
			if (e.id == "total") {
				f.style.verticalAlign = "middle"
			} else {
				if (e.id == "name") {
					f.style.verticalAlign = "bottom"
				} else {
					f.style.verticalAlign = "top"
				}
			}
			e.__th = f;
			f.__col = e;
			if (e.width != null) {
				f.style.width = e.width
			}
			if (e.align != null) {
				f.style.textAlign = e.align
			}
			if (e.span != null) {
				f.colSpan = e.span
			}
			if (this.selected != null && this.__selected.id == e.id) {
				f.className = "checked"
			}
			if (e.id == "name") {
				ae(f, this.selectLevel);
				if (this.editable) {
					var n = ce("select");
					n.onchange = (function (a) {
						this.level = a.options[a.selectedIndex].value;
						this.refreshAll()
					}).bind(this, n);
					for (var u = 80; u > 0; --u) {
						var q = ce("option");
						if (u == this.level) {
							q.selected = true
						}
						st(q, u);
						ae(n, q)
					}
					ae(this.selectLevel, ct(LANG.pr_dialog_level + " "));
					ae(this.selectLevel, n)
				}
			}
			if (e.group) {
				this.dragTargets.push(f);
				if (this.editable) {
					var A = ce("div");
					A.className = "summary-group";
					m = ce("div");
					m.className = "summary-group-controls";
					var z = ce("a");
					z.href = "javascript:;";
					z.className = "summary-group-dropdown";
					ae(z, ce("span"));
					z.menu = [[0, LANG.su_export, "?compare=" + this.getGroupData(l)]];
					z.menu[0].newWindow = 1;
					if (this.viewableIn3d(e)) {
						z.menu.push([0, LANG.su_viewin3d, this.viewIn3d.bind(this, e)])
					}
					if (e.group.length > 1) {
						z.menu.push([0, LANG.su_split, this.splitColumn.bind(this, e)])
					}
					if (this.enhanceable && this.hasEnhancements(e.group)) {
						z.menu.push([0, LANG.pr_menu_clearenh, this.clearEnhancements.bind(this, e)])
					}
					z.onclick = Menu.showAtCursor;
					ae(m, z);
					if (this.groups.length > 1) {
						z = ce("a");
						z.href = "javascript:;";
						z.className = "summary-group-focus";
						var n = ce("span");
						z.onclick = this.selectColumn.bind(this, e);
						if (this.selected && this.__selected.id == e.id) {
							z.onmouseover = function () {
								Tooltip.show(this, LANG.tooltip_removefocus, 0, 0, "q")
							};
							n.className = "selected"
						} else {
							z.onmouseover = function () {
								Tooltip.show(this, LANG.tooltip_setfocus, 0, 0, "q")
							}
						}
						z.onmousemove = Tooltip.cursorUpdate;
						z.onmouseout = Tooltip.hide;
						ae(z, n);
						ae(m, z)
					}
					z = ce("a");
					z.href = "javascript:;";
					z.className = "summary-group-delete";
					z.onclick = this.deleteColumn.bind(this, e);
					ae(z, ce("span"));
					ae(m, z);
					if (this.draggable) {
						z = ce("a");
						z.href = "javascript:;";
						z.className = "summary-group-drag";
						ae(z, ce("span"));
						ae(m, z);
						A.__col = e;
						A._handle = z;
						this.dragHeaders.push(A)
					}
					ae(A, m);
					ae(f, A);
					if (this.draggable && this.groups.length > 1) {
						if (Browser.ie) {
							var x = ce("div");
							x.style.width = "80px";
							x.style.fontSize = "1px";
							x.style.height = "0";
							x.style.background = "transparent";
							ae(f, x)
						} else {
							A.style.minWidth = "80px"
						}
					}
				}
				var g = e.group.length,
				r = 1,
				c = 3,
				h = 44;
				if (g > 3) {
					r = 0;
					c = 5;
					h = 26
				}
				var A = ce("div");
				A.style.margin = "0 auto";
				A.style.clear = "both";
				var k = ce("div"),
				m = A.cloneNode(true);
				if (this.editable) {
					k.className = "summary-group-bottom"
				}
				for (var t = 0; t < g; ++t) {
					if (t % c == 0) {
						m.style.width = (h * c) + "px";
						m = A.cloneNode(true);
						ae(k, m)
					}
					var w = g_items.createIcon(e.group[t][0], r);
					w.__col = e;
					w.i = t;
					if (this.enhanceable) {
						this.refreshItem(e, t, Icon.getLink(w))
					}
					this.dragIcons.push(w);
					ae(m, w)
				}
				if (e.group.length) {
					m.style.width = (h * (g % c == 0 ? c: (g % c))) + "px"
				}
				ae(k, m);
				if (this.editable) {
					f.style.padding = "0";
					m = ce("div");
					m.className = "clear";
					ae(k, m);
					m = ce("div");
					m.className = "clear";
					ae(f.firstChild, m);
					ae(f.firstChild, k)
				} else {
					ae(f, k)
				}
				l++
			} else {
				if (e.name) {
					var y = ce("b");
					ae(y, ct(e.name));
					ae(f, y)
				}
			}
			if (this.editable && p == this.newgroup) {
				this.dragTargets.push(f)
			}
			ae(d, f)
		}
		ae(this.thead, d);
		if (this.draggable) {
			this.updateDraggable()
		}
	},
	refreshItem: function (e, h, m) {
		var o = e.group[h],
		p = o[0],
		b = [],
		d = this.hasExtraSocket(o);
		m.rel = this.getItemRel(o, e);
		if (!this.enhanceable) {
			return
		}
		m.oncontextmenu = rf;
		if (g_items[p].jsonequip.subitems != null) {
			b.push([0, (o[1] ? LANG.pr_menu_repsubitem: LANG.pr_menu_addsubitem), this.openSubitemPicker.bind(this, e, h)])
		}
		if (this.canBeEnchanted(g_items[p].jsonequip.slotbak)) {
			var n = [0, (o[2] ? LANG.pr_menu_repenchant: LANG.pr_menu_addenchant), this.openEnchantPicker.bind(this, e, h)];
			if (o[2] && g_enchants[o[2]]) {
				n.tinyIcon = g_enchants[o[2]].icon
			}
			b.push(n)
		}
		if (g_items[p].jsonequip.nsockets || d) {
			for (var g = 0, f = (g_items[p].jsonequip.nsockets | 0) + d; g < f; ++g) {
				var j = (o[g + 4] > 0 ? o[g + 4] : 0),
				l = (d && g == f - 1 ? 14 : g_items[p].jsonequip["socket" + (g + 1)]),
				n = [0, (j ? LANG.pr_menu_repgem: LANG.pr_menu_addgem), this.openGemPicker.bind(this, e, h, 4 + g, l)];
				if (j) {
					n.tinyIcon = g_gems[j].icon
				} else {
					n.socketColor = l
				}
				b.push(n)
			}
		}
		if (this.canBeSocketed(g_items[p].jsonequip.slotbak)) {
			var n = [0, LANG.pr_menu_extrasock, Summary.toggleExtraSocket.bind(this, e, h, m)];
			n.checked = d;
			b.push(n)
		}
		if (this.hasEnhancements([o])) {
			b.push([0, LANG.pr_menu_clearenh, this.clearEnhancements.bind(this, e, h)])
		}
		if (b.length) {
			m.oncontextmenu = rf;
			m.onclick = this.onMouseClick.bind(0, m);
			m.onmouseup = this.onMouseUp.bind(0, m);
			m.menu = b
		}
	},
	refreshRows: function () {
		if (this.groups.length) {
			ae(this.table, this.tbody)
		}
		while (this.tbody.firstChild) {
			this.tbody.removeChild(this.tbody.firstChild)
		}
 		this.selectLevel.style.display = "none";
/* start experimental */
		for (var itr = 0, len = this.visibility.length; itr < len; ++itr)
		{
			var reali = this.visibility[itr];
			var col = this.columns[reali];
 
			if(!col.group)
				continue;
 
			for(var jtr = 0, len2 = col.group.length; jtr < len2; ++jtr)
			{
				var itemId = col.group[jtr][0];
 
				if(g_items[itemId].jsonequip.scadist && g_items[itemId].jsonequip.scaflags)
				{
					this.selectLevel.style.display = '';
					break;
				}
			}
		}
/* end experimental */
		var a = 0;
		for (trait in this.traits) {
			if (this.traits[trait].id) {
				this.traits[trait].i = ++a
			}
			ae(this.tbody, this.getRow(trait, a))
		}
		if (!a) {
			if (!this.groups.length && this.tbody.parentNode) {
				de(this.tbody)
			}
			var c = ce("tr");
			var d = ce("td");
			var b = ce("span");
			if(this.visibility && this.visibility.length)
                d.colSpan = this.visibility.length;
			d.style.textAlign = "center";
			ae(b, ct("The items in this set don't have any comparable stats."));
			ae(d, b);
			ae(c, d);
			ae(this.tbody, c)
		}
	},
	refreshLink: function () {
		var b = ge("su_link"),
		e = this.getGroupData(),
		f = this.getWeightData();
		if (e) {
			this.itemTip.style.display = ""
		} else {
			this.itemTip.style.display = "none"
		}
		if (b) {
            sc("compare_level", 20, this.level, "/", location.hostname);      // custom
			var d = "?compare" + (e ? "=" + e: "") + (this.level != 80 ? "&l=" + this.level: "");
			if (f[1].length && f[2].length) {
				d += "&weights=" + f.join(";")
			}
			if (this.selected) {
				d += "&focus=" + this.__selected.i
			}
			b.href = d
		}
	},
	refreshClasses: function (e) {
		var i = e.options[e.selectedIndex],
		a = ge("su_presets");
		while (a.firstChild) {
			de(a.firstChild)
		}
		ae(a, ce("option"));
		if (e.selectedIndex > 0) {
			for (var b in i._presets) {
				var d = i._presets[b];
				var g = ce("optgroup");
				g.label = LANG.presets[b];
				for (var f in d) {
					var h = ce("option");
					h._weights = d[f];
					ae(h, ct(LANG.presets[f]));
					ae(g, h)
				}
				if (g && g.childNodes.length > 0) {
					ae(a, g)
				}
			}
			if (a.childNodes.length > 1) {
				a.parentNode.style.display = ""
			}
		} else {
			a.parentNode.style.display = "none"
		}
		this.resetWeights()
	},
	refreshPresets: function (b) {
		this.resetWeights();
		var a = ge("su_classes");
		var c = b.options[b.selectedIndex];
		if (b.selectedIndex > 0) {
			this.updateWeights(c._weights);
			ge("su_scale").value = a.options[a.selectedIndex].text + LANG.hyphen + c.text
		}
	},
	resetWeights: function () {
		var c = ge("su_weight");
		ge("su_scale").value = "";
		while (c.childNodes.length >= 2) {
			c.removeChild(c.childNodes[1])
		}
		var e = c.childNodes[0];
		while (e.childNodes.length > 1) {
			e.removeChild(e.childNodes[1])
		}
		e.firstChild.selectedIndex = 0;
		var b = ge("su_addweight");
		if (c.childNodes.length < 15) {
			b.style.display = ""
		}
	},
	refreshWeights: function (c, b) {
		var e = c.parentNode;
		while (e.childNodes.length > 1) {
			de(e.childNodes[1])
		}
		if (c.selectedIndex > 0) {
			ae(e, ct(" "));
			var a = ce("input");
			a.type = "text";
			a.value = (b | 0);
			a.maxLength = 7;
			a.style.textAlign = "center";
			a.style.width = "4.5em";
			a.setAttribute("autocomplete", "off");
			a.onchange = this.sortWeights.bind(this, a);
			ae(e, a);
			this.sortWeights(a)
		}
		if (e.parentNode.childNodes.length == 1) {
			if (c.selectedIndex > 0) {
				ae(e, ct(String.fromCharCode(160, 160)));
				ae(e, this.createControl(LANG.ficlear, "", "", this.resetWeights.bind(this)))
			}
		} else {
			if (e.parentNode.childNodes.length > 1) {
				ae(e, ct(String.fromCharCode(160, 160)));
				ae(e, this.createControl(LANG.firemove, "", "", this.deleteWeight.bind(this, c)))
			}
		}
	},
	sortWeights: function (e) {
		var f = ge("su_weight"),
		b = Number(e.value),
		k = e.parentNode;
		var j = 0;
		for (var g = 0, a = f.childNodes.length; g < a; ++g) {
			var h = f.childNodes[g];
			if (h.childNodes.length == 5) {
				if (h.childNodes[0].tagName == "SELECT" && h.childNodes[2].tagName == "INPUT") {
					if (b > Number(h.childNodes[2].value)) {
						f.insertBefore(k, h);
						return
					}++j
				}
			}
		}
		if (j < a) {
			f.insertBefore(k, f.childNodes[j])
		} else {
			ae(f, k)
		}
	},
	saveComparison: function (refresh) {
		// sc("compare_groups", 20, this.getGroupData(), "/", ".wowhead.com");
		sc("compare_groups", 20, this.getGroupData(), "/", location.hostname);
		// sc("compare_weights", 20, rtrim(this.getWeightData(1).join(";"), ";"), "/", wowhead.com);
		sc("compare_weights", 20, rtrim(this.getWeightData(1).join(";"), ";"), "/", location.hostname);
		sc("compare_level", 20, this.level, "/", location.hostname);

		if (refresh) {
			document.location.href = "?compare"
		}
	},
	getGroupData: function (g) {
		var f = 0,
		a = this.groups.length;
		if (!isNaN(g)) {
			f = g;
			a = g + 1
		}
		var d = "",
		c = 0;
		for (var e = f; e < a; ++e) {
			if (this.groups[e].length < 1) {
				continue
			}
			if (c++>0) {
				d += ";"
			}
			for (var c = 0, b = this.groups[e].length; c < b; ++c) {
				if (c > 0) {
					d += ":"
				}
				d += this.writeItem(this.groups[e][c])
			}
		}
		return d
	},
	getWeightData: function (k) {
		var a = "",
		f = "",
		b = "",
		g;
		for (var d = 0, e = this.weights.length; d < e; ++d) {
			if (d > 0) {
				f += ";";
				b += ";";
				a += ";"
			}
			a += (k ? this.weights[d].name: urlencode(this.weights[d].name));
			var c = 0;
			for (var h in this.weights[d]) {
				if (!LANG.traits[h]) {
					continue
				}
				g = fi_Lookup(h, "items");
				if (!g) {
					continue
				}
				if (c++>0) {
					f += ":";
					b += ":"
				}
				f += g.id;
				b += this.weights[d][h]
			}
		}
		return [a, f, b]
	},
	showNoData: function (a) {
		var c = this.textNames;
		while (c.firstChild) {
			de(c.firstChild)
		}
		c.style.display = "none";
		c = this.noData;
		while (c.firstChild) {
			de(c.firstChild)
		}
		ae(a, c);
		var b = -1;
		if (this.template.onNoData) {
			b = (this.template.onNoData.bind(this, c))()
		}
		if (b == -1) {
			ae(c, ct(LANG.su_additems || LANG.lvnodata))
		}
	},
	showTextNames: function () {
		var b = this.textNames;
		b.style.display = "";
		b.style.paddingTop = "10px";
		while (b.firstChild) {
			de(b.firstChild)
		}
		ae(b, ct(LANG.su_comparing));
		var e = 0;
		for (var f = 0, g = this.visibility.length; f < g; ++f) {
			var h = this.visibility[f];
			var d = this.columns[h];
			if (!d.group || !d.group.length) {
				continue
			}
			if (e++>0) {
				ae(b, ct(LANG.su_comparewith))
			}
			if (d.group.length == 1) {
				var k = g_items[d.group[0][0]].jsonequip;
				var j = ce("a");
				j.className = "q" + (7 - parseInt(k.name.charAt(0)));
				j.href = "?item=" + k.id;
				j.rel = this.getItemRel(d.group[0], d);
				ae(j, ct("[" + this.getItemName(d.group[0]) + "]"));
				ae(b, j)
			} else {
				var c = ce("span");
				c.onmouseover = Summary.groupOver.bind(j, d.group);
				c.onmousemove = Tooltip.cursorUpdate;
				c.onmouseout = Tooltip.hide;
				c.className = "tip";
				ae(c, ct("[" + d.group.length + " " + LANG.types[3][3] + "]"));
				ae(b, c)
			}
		}
	},
	updateControls: function () {
		if (this.__lastCtrlUpdate != null && (this.__lastCtrlUpdate == this.groups.length || this.groups.length > 1)) {
			return
		}
		this.__lastCtrlUpdate = this.groups.length;
		var k = this.controls,
		y;
		k.style.display = "";
		while (k.firstChild) {
			de(k.firstChild)
		}
		var f = ce("div");
		f.className = "summary-controls-right";
		ae(k, f);
		y = this.createControl(LANG.su_help, null, "help-icon", null, "?help=item-comparison");
		ae(f, y);
		if (this.searchable) {
			var l = ce("span");
			//l.className = "icon-additem";
			y = this.createControl(LANG.su_addset, "su_addset", "additem-icon", this.openItemPicker.bind(this, 4));
			ae(l, y);
			ae(f, l);
			l = ce("span");
			//l.className = "icon-additem";
			y = this.createControl(LANG.su_additem, "su_additem", null, this.openItemPicker.bind(this, 3));
			y.className = "additem-icon";
			ae(l, y);
			ae(f, l)
		}
		if (this.groups.length) {
			if (this.weightable) {
				var l = ce("span");
				//l.className = "icon-additem";
				y = this.createControl(LANG.su_addscale, "su_addscale", "additem-icon", this.toggleOptions.bind(this, "su_weights", "su_addscale"));
				ae(l, y);
				ae(f, l)
			}
		}
		aE(document, "click", this.toggleOptions.bind(this, null, null));
		var A = ce("div");
		A.className = "clear";
		ae(f, A);
		if (this.weightable) {
			var z = ce("div"),
			u = ce("div");
			z.style.display = "none";
			z.id = "su_weights";
			u.className = "summary-weights-inner";
			ae(z, u);
			ae(f, z);
			var j = ce("table"),
			b = ce("tbody"),
			e = ce("tr"),
			g = ce("td");
			ae(j, b);
			ae(b, e);
			ae(e, g);
			ae(g, ct(LANG.su_preset));
			g = ce("td");
			ae(e, g);
			var l = ce("select");
			l.id = "su_classes";
			l.onchange = l.onkeyup = this.refreshClasses.bind(this, l);
			ae(l, ce("option"));
			ae(g, l);
			var v = [];
			for (var x in wt_presets) {
				v.push(x)
			}
			v.sort(function (d, c) {
				return strcmp(g_chr_classes[d], g_chr_classes[c])
			});
			for (var q = 0, r = v.length; q < r; ++q) {
				var x = v[q],
				n = ce("option");
				n.value = x;
				n._presets = wt_presets[x];
				ae(n, ct(g_chr_classes[x]));
				ae(l, n)
			}
			A = ce("span");
			A.style.display = "none";
			ae(A, ct(" "));
			ae(g, A);
			l = ce("select");
			l.id = "su_presets";
			l.onchange = l.onkeyup = this.refreshPresets.bind(this, l);
			ae(l, ce("option"));
			ae(A, l);
			ae(g, ct(" "));
			y = ce("a");
			y.href = "javascript:;";
			ae(y, ct(LANG.fishowdetails));
			y.onclick = this.showDetails.bind(this, y);
			ae(g, y);
			e = ce("tr");
			g = ce("td");
			ae(b, e);
			ae(e, g);
			ae(g, ct(LANG.su_name));
			g = ce("td");
			ae(e, g);
			var q = ce("input");
			q.type = "text";
			q.id = "su_scale";
			ae(g, q);
			ae(g, ct(" "));
			ae(u, j);
			var h = ce("div");
			h.style.display = "none";
			h.id = "su_weight";
			ae(u, h);
			A = ce("div");
			ae(h, A);
			l = ce("select");
			l.onchange = l.onkeyup = this.refreshWeights.bind(this, l);
			ae(l, ce("option"));
			ae(A, l);
			A = false;
			for (var q = 0, r = this.template.traits.length; q < r; ++q) {
				var m = this.template.traits[q];
				if (m.type == "sep") {
					if (A && A.childNodes.length > 0) {
						ae(l, A)
					}
					A = ce("optgroup");
					A.label = (LANG.traits[m.id] ? LANG.traits[m.id] : m.name)
				} else {
					if (m.type != "custom") {
						var n = ce("option");
						n.value = m.id;
						ae(n, ct((m.indent ? "- ": "") + LANG.traits[m.id] ? LANG.traits[m.id][0] : m.name));
						ae(A, n)
					}
				}
			}
			if (A && A.childNodes.length > 0) {
				ae(l, A)
			}
			A = ce("div");
			A.style.display = "none";
			y = this.createControl(LANG.su_addweight, "su_addweight", "", this.addWeight.bind(this));
			ae(A, y);
			ae(u, A);
			A = ce("div");
			A.className = "summary-weights-buttons";
			y = ce("a");
			y.className = "help-icon";
			y.href = "?help=stat-weighting";
			y.target = "_blank";
			y.style.border = "none";
			y.style.cssFloat = y.style.styleFloat = "right";
			ae(y, ct(LANG.su_help));
			ae(A, y);
			q = ce("input");
			q.type = "button";
			q.value = LANG.su_applyweight;
			q.onclick = Summary.addWeightScale.bind(this);
			ae(A, q);
			ae(A, ct(" "));
			q = ce("input");
			q.type = "button";
			q.value = LANG.su_resetweight;
			q.onclick = Summary.resetScale.bind(this);
			ae(A, q);
			ae(u, A)
		}
		if (this.autoSave) {
			y = this.createControl(LANG.su_autosaving, null, "autosave-icon selected")
		} else {
			y = this.createControl(LANG.su_savecompare, "su_save", "save-icon", this.saveComparison.bind(this, 1))
		}
		ae(k, y);
		if (this.groups.length) {
			y = this.createControl(LANG.su_linkcompare, "su_link", "link-icon");
			ae(k, y);
			y = this.createControl(LANG.su_clear, null, "clear-icon", this.resetAll.bind(this));
			ae(k, y)
		}
	},
	getRow: function (a) {
		var b = this.traits[a];
		this.createRow(b);
		return b.__tr
	},
	createRow: function (r) {
		var m = ce("tr");
		r.__tr = m;
		for (var h = 0, k = this.visibility.length; h < k; ++h) {
			var l = this.visibility[h];
			var d = this.columns[l];
			var e = ce("td"),
			q = e;
			if (d.align != null) {
				e.style.textAlign = d.align
			}
			if (this.total != null && this.__total.id == d.id || this.selected != null && this.__selected.id == d.id) {
				if (r.id != "gains") {
					e.style.fontWeight = "bold";
					e.className += "q1";
					if (this.selected != null && this.__selected.id == d.id) {
						e.className += " checked"
					}
				}
			}
			if (!r.type) {
				q.colSpan = k;
				q.style.borderBottomWidth = (Browser.ie6 ? "3px": "2px");
				q.style.padding = "0";
				ae(m, e);
				break
			}
			var o = null;
			if (d.compute) {
				o = (d.compute.bind(this, r, q, m, l))()
			} else {
				if (r.compute) {
					o = (r.compute.bind(this, d, q, m, l))()
				} else {
					if (d.total) {
						if (this.selected != null && this.__selected.id != d.id && d.difference) {
							o = d.difference[r.id];
							if (o > 0) {
								q.className += " q2"
							} else {
								if (o < 0) {
									q.className += " q10"
								} else {
									if (o == 0) {
										o = null
									}
								}
							}
						} else {
							o = d.total[r.id]
						}
						if (o != null) {
							if (o.join) {
								for (var g = 0, f = o.length; g < f; ++g) {
									if (g > 0) {
										ae(q, ct(", "))
									}
									if (o[g].appendChild) {
										ae(q, o[g])
									} else {
										ae(q, ct(o[g]))
									}
								}
								o = null
							} else {
								if (!isNaN(o)) {
									var c = Math.pow(10, (r.digits != null ? r.digits: 4));
									o = Math.round(c * o) / c;
									if (!r.rating) {
										o = (o < 0 ? "-": "") + number_format(Math.abs(o))
									} else {
										this.selectLevel.style.display = ""
									}
									if (o > 0 && this.selected != null && this.__selected.id != d.id) {
										o = "+" + o
									}
								}
							}
						}
					}
				}
			}
			if (this.groups.length > 1 && !this.selected && d.total && d.total[r.id] == this.maxValue[r.id] && this.maxValue[r.id] != this.minValue[r.id] && d.id != "total") {
				q.style.fontWeight = "bold";
				q.className += " q2"
			}
			if (o != null) {
				if (r.rating && !isNaN(o)) {
					var b = (o < 0 ? -1 : 1) * g_convertRatingToPercent(this.level, r.rating, Math.abs(o));
					b = number_format((Math.round(b * 100) / 100));
					if (r.rating != 12 && r.rating != 37) {
						b += "%"
					}
					var p = ce("a");
					p.className = (q.className ? q.className: "q1");
					p.style.borderBottom = "1px dotted #808080";
					p.onmouseover = (function (i, a, n, j) {
						Tooltip.show(this, i + " " + j + " (" + sprintf(LANG.tooltip_combatrating, a, n) + ')<br /><span class="q2">' + LANG.su_toggle + "</span>", 0, 0, "q")
					}).bind(p, o, b, this.level, LANG.traits[r.id][0]);
					p.onmousemove = Tooltip.cursorUpdate;
					p.onmouseout = Tooltip.hide;
					p.onclick = this.toggleRatings.bind(this);
					ae(p, ct(this.ratingMode ? b: o));
					aef(q, p)
				} else {
					aef(q, ct(o))
				}
			}
			if (!r.hidden) {
				ae(m, e)
			}
		}
	},
	toggleOptions: function (g, h, f) {
		f = $E(f);
		if (f && f._button >= 2) {
			return false
		}
		var j = [ge("su_weights")],
		k = [ge("su_addscale")],
		a = null;
		if (Browser.ie && f && !g && in_array(k, f._target) != -1) {
			return
		}
		if (h) {
			h = ge(h)
		}
		if (g && h) {
			if (h.className.indexOf("selected") != -1) {
				h = null
			} else {
				a = ge(g)
			}
		} else {
			if (f) {
				var b = f._target;
				if (!b.parentNode) {
					return
				}
				while (b.parentNode) {
					if (in_array(j, b) != -1) {
						return false
					}
					b = b.parentNode
				}
			}
		}
		for (var c = 0, d = j.length; c < d; ++c) {
			if (j[c] && j[c] != a) {
				j[c].style.display = "none"
			}
		}
		for (var c = 0, d = k.length; c < d; ++c) {
			if (k[c] && k[c] != h) {
				k[c].className = k[c].className.replace("selected", "")
			}
		}
		if (h) {
			h.className += " selected";
			if (a) {
				a.style.display = ""
			}
		}
		if (f && g && h) {
			sp(f)
		}
	},
	toggleWeights: function () {
		if (++this.scoreMode > 2) {
			this.scoreMode = 0
		}
		this.refreshAll()
	},
	toggleRatings: function () {
		this.ratingMode = !this.ratingMode;
		this.refreshAll()
	},
	createControl: function (f, g, e, d, c) {
		var b = ce("a");
		if (c) {
			b.href = c;
			b.target = "_blank"
		} else {
			b.href = "javascript:;"
		}
		if (g) {
			b.id = g
		}
		if (e) {
			b.className = e
		}
		if (d) {
			if (Browser.ie) {
				b.onmouseup = d
			} else {
				b.onclick = d
			}
		}
		ae(b, ct(f));
		return b
	},
	createColumn: function () {
		this.groups.push([this.readItem( - 1)]);
		this.refreshAll()
	},
	selectColumn: function (a) {
		Tooltip.hide();
		this.selected = (this.__selected.id == a.id ? null: this.clone.i + a.i);
		this.refreshAll()
	},
	deleteColumn: function (a, b) {
		Tooltip.hide();
		b = $E(b);
		if (!b.shiftKey) {
			this.groups.splice(a.i, 1)
		} else {
			this.groups = [this.groups[a.i]]
		}
		if (!this.editable || this.groups.length <= 1 || this.__selected.id == a.id) {
			this.selected = null
		} else {
			if (this.selected) {
				this.selected += (this.clone.i + a.i > this.selected ? 0 : -1)
			}
		}
		this.refreshAll();
		return false
	},
	createWeightScale: function () {
		var b = ge("su_weight"),
		d = ge("su_scale"),
		f = {},
		e = false;
		f.name = d.value;
		f.name = trim(f.name);
		f.name = f.name.replace(/'/g, "");
		for (var c = 0, a = b.childNodes.length; c < a; ++c) {
			if (b.childNodes[c].childNodes.length == 5) {
				if (b.childNodes[c].childNodes[0].tagName == "SELECT" && b.childNodes[c].childNodes[2].tagName == "INPUT") {
					f[b.childNodes[c].childNodes[0].options[b.childNodes[c].childNodes[0].selectedIndex].value] = Number(b.childNodes[c].childNodes[2].value);
					e = true
				}
			}
		}
		if (e) {
			return f
		}
		return null
	},
	deleteWeightScale: function (a) {
		Tooltip.hide();
		this.weights.splice(a, 1);
		this.refreshAll()
	},
	openItemPicker: function (a) {
		this.searchType = a;
		Lightbox.show("compare", {
			onShow: this.onItemPickerShow.bind(this)
		})
	},
	createItemPickerNote: function (e) {
		var d = ce("small"),
		c = ce("span"),
		b;
		this.currentSearch = "";
		this.searchTimer = 0;
		ae(d, ct(LANG.su_note_name));
		this.searchName = ce("input");
		this.searchName.type = "text";
		aE(this.searchName, "keyup", this.onSearchKeyUp.bind(this));
		aE(this.searchName, Browser.opera ? "keypress": "keydown", this.onSearchKeyDown.bind(this));
		ae(d, this.searchName);
		this.searchMsg = ce("span");
		this.searchMsg.style.fontWeight = "bold";
		ae(d, this.searchMsg);
		ae(e, d)
	},
	getSetBonuses: function (l) {
		var b = {},
		e = {};
		for (var g = 0, k = l.length; g < k; ++g) {
			var c = l[g][0];
			if (g_items[c]) {
				var m = g_items[c].jsonequip.itemset;
				if (g_itemsets[m]) {
					if (e[m] == null) {
						e[m] = {}
					}
					e[m][c] = 1
				}
			}
		}
		for (var m in e) {
			var a = g_itemsets[m],
			h = 0;
			if (!a.setbonus) {
				continue
			}
			for (var c in e[m]) {
				h++
			}
			for (var d in a.setbonus) {
				if (d > h) {
					break
				}
				for (var f in a.setbonus[d]) {
					if (b[f] == null) {
						b[f] = 0
					}
					b[f] += a.setbonus[d][f]
				}
			}
		}
		return b
	},
	onItemPickerShow: function (j, f, b) {
		Lightbox.setSize(800, 564);
		var c;
		if (f) {
			j.className = "summary-picker listview";
			var h = ce("div"),
			i = ce("a"),
			e = ce("div");
			h.className = "listview";
			ae(j, h);
			i = ce("a");
			i.className = "screenshotviewer-close";
			i.href = "javascript:;";
			i.onclick = Lightbox.hide;
			ae(i, ce("span"));
			ae(j, i);
			e.className = "clear";
			ae(j, e);
			c = new Listview({
				template: "compare",
				id: "items",
				parent: h,
				data: [],
				clip: {
					w: 780,
					h: 478
				},
				createNote: this.createItemPickerNote.bind(this)
			});
			if (Browser.firefox) {
				aE(c.getClipDiv(), "DOMMouseScroll", this.onPickerWheel)
			} else {
				c.getClipDiv().onmousewheel = this.onPickerWheel
			}
		} else {
			c = g_listviews.items
		}
		var k = this.searchItems.bind(this),
		g = this.searchName;
		setTimeout(function () {
			k("", this.searchType);
			g.value = "";
			g.focus()
		},
		1)
	},
	openSubitemPicker: function (a, b) {
		this.currentItem = {
			col: a,
			i: b,
			item: a.group[b]
		};
		Lightbox.show("subitempicker", {
			onShow: this.onSubitemPickerShow.bind(this)
		})
	},
	onSubitemPickerShow: function (k, g, b) {
		Lightbox.setSize(800, 564);
		var e, c = [],
		m = this.currentItem.item[0];
		for (var i in g_items[m].jsonequip.subitems) {
			var l = g_items[m].jsonequip.subitems[i];
			l.id = i;
			l.item = m;
			l._summary = this;
			c.push(l)
		}
		if (g) {
			k.className = "summary-picker listview";
			var h = ce("div"),
			j = ce("a"),
			f = ce("div");
			h.className = "listview";
			ae(k, h);
			j = ce("a");
			j.className = "screenshotviewer-close";
			j.href = "javascript:;";
			j.onclick = Lightbox.hide;
			ae(j, ce("span"));
			ae(k, j);
			f.className = "clear";
			ae(k, f);
			e = new Listview({
				template: "subitempicker",
				id: "subitems",
				parent: h,
				data: c
			});
			if (Browser.firefox) {
				aE(e.getClipDiv(), "DOMMouseScroll", this.onPickerWheel)
			} else {
				e.getClipDiv().onmousewheel = this.onPickerWheel
			}
		} else {
			e = g_listviews.subitems;
			e.setData(c);
			e.clearSearch();
			e.updateFilters(true)
		}
		setTimeout(function () {
			e.focusSearch()
		},
		1)
	},
	openEnchantPicker: function (a, b) {
		this.currentItem = {
			col: a,
			i: b,
			item: a.group[b]
		};
		Lightbox.show("enchantpicker", {
			onShow: this.onEnchantPickerShow.bind(this)
		})
	},
	createEnchantPickerNote: function (d) {
		var c = ce("small"),
		b;
		ae(c, ct(LANG.pr_note_source));
		b = ce("a");
		b.href = "javascript:;";
		b.onclick = this.filterEnchants.bind(this, b, 0, null);
		ae(b, ct(LANG.pr_note_all));
		g_setSelectedLink(b, "enchants0");
		ae(c, b);
		ae(c, ct(LANG.comma));
		b = ce("a");
		b.href = "javascript:;";
		b.onclick = this.filterEnchants.bind(this, b, 0, 1);
		ae(b, ct(LANG.pr_note_items));
		ae(c, b);
		ae(c, ct(LANG.comma));
		b = ce("a");
		b.href = "javascript:;";
		b.onclick = this.filterEnchants.bind(this, b, 0, 2);
		ae(b, ct(LANG.pr_note_profs));
		ae(c, b);
		ae(d, c)
	},
	canBeEnchanted: function(slot) {
		return (slot ==  1 || slot ==  3 || slot == 16 || slot ==  5 || slot == 20 ||  // Head, Shoulder, Back, Chest, Robe
				slot ==  9 || slot == 10 || slot ==  6 || slot ==  7 || slot ==  8 ||  // Wrists, Hands, Waist, Legs, Feet
				slot == 11 || slot == 21 || slot == 13 || slot == 17 || slot == 22 ||  // Finger, Main Hand, One Hand, Two Hand, Off Hand
				slot == 14 || slot == 15 || slot == 26 || slot == 23)                  // Shield, Ranged, Held in Off Hand
	},
	isValidEnchant: function (b) {
		if (b.none) {
			return true
		}
		var a = 1 << (g_items[this.currentItem.item[0]].jsonequip.slot - 1);
		return ((b.slots & a) && (this.enchantSource == null || (this.enchantSource == 1 && b.source < 0) || (this.enchantSource == 2 && b.source > 0)))
	},
	filterEnchants: function (b, c, d) {
		switch (c) {
		case 0:
			this.enchantSource = d;
			break;
		default:
			return
		}
		if (b && b.nodeName == "A") {
			g_setSelectedLink(b, "enchants" + c)
		}
		g_listviews.enchants.updateFilters(true);
		return false
	},
	onEnchantPickerShow: function (n, j, c) {
		Lightbox.setSize(800, 564);
		var f;
		if (j) {
			n.className = "summary-picker listview";
			var e = [],
			l = ce("div"),
			m = ce("a"),
			h = ce("div");
			e.push({
				none: 1,
				__alwaysvisible: 1,
				_summary: this
			});
			for (var o in g_enchants) {
				var b = {
					id: o,
					_summary: this
				};
				cO(b, g_enchants[o]);
				if (typeof b.name == "string") {
					e.push(b)
				} else {
					for (var g = 0, k = g_enchants[o].name.length; g < k; ++g) {
						var p = {};
						cO(p, b);
						p.name = b.name[g];
						p.source = b.source[g];
						p.slots = b.slots[g];
						e.push(p)
					}
				}
			}
			l.className = "listview";
			ae(n, l);
			m = ce("a");
			m.className = "screenshotviewer-close";
			m.href = "javascript:;";
			m.onclick = Lightbox.hide;
			ae(m, ce("span"));
			ae(n, m);
			h.className = "clear";
			ae(n, h);
			f = new Listview({
				template: "enchantpicker",
				id: "enchants",
				parent: l,
				data: e,
				createNote: this.createEnchantPickerNote.bind(this),
				customFilter: this.isValidEnchant.bind(this)
			});
			if (Browser.firefox) {
				aE(f.getClipDiv(), "DOMMouseScroll", this.onPickerWheel)
			} else {
				f.getClipDiv().onmousewheel = this.onPickerWheel
			}
		} else {
			f = g_listviews.enchants;
			f.clearSearch();
			f.updateFilters(true)
		}
		setTimeout(function () {
			f.focusSearch()
		},
		1)
	},
	openGemPicker: function (b, c, d, a) {
		this.currentItem = {
			col: b,
			i: c,
			item: b.group[c],
			socket: d,
			color: a
		};
		Lightbox.show("gempicker", {
			onShow: this.onGemPickerShow.bind(this)
		})
	},
	createGemPickerNote: function (e) {
		var d = ce("small"),
		b;
		ae(d, ct(LANG.pr_note_source));
		b = ce("a");
		b.href = "javascript:;";
		b.onclick = this.filterGems.bind(this, b, 0, null);
		ae(b, ct(LANG.pr_note_all));
		ae(d, b);
		ae(d, ct(LANG.comma));
		b = ce("a");
		b.href = "javascript:;";
		b.onclick = this.filterGems.bind(this, b, 0, 1);
		ae(b, ct(LANG.pr_note_bc));
		ae(d, b);
		ae(d, ct(LANG.comma));
		b = ce("a");
		b.href = "javascript:;";
		b.onclick = this.filterGems.bind(this, b, 0, 2);
		ae(b, ct(LANG.pr_note_wotlk));
		this.gemSource = 2;
		g_setSelectedLink(b, "gems0");
		ae(d, b);
		ae(e, d);
		d = ce("small");
		var c = ce("span");
		c.style.padding = "0 8px";
		c.style.color = "white";
		ae(c, ct("|"));
		ae(d, c);
		ae(d, ct(LANG.pr_note_color));
		b = ce("a");
		b.href = "javascript:;";
		b.onclick = this.filterGems.bind(this, b, 1, null);
		ae(b, ct(LANG.pr_note_all));
		ae(d, b);
		ae(d, ct(LANG.comma));
		b = ce("a");
		b.href = "javascript:;";
		b.onclick = this.filterGems.bind(this, b, 1, 1);
		ae(b, ct(LANG.pr_note_match));
		this.gemColor = 1;
		g_setSelectedLink(b, "gems1");
		ae(d, b);
		ae(e, d)
	},
	matchGemSocket: function (b, c) {
		for (var a = 1; a <= 8; a *= 2) {
			if ((c & a) && (b & a)) {
				return true
			}
		}
		return false
	},
	canBeSocketed: function (a) {
		return (a == 9 || a == 10 || a == 6)
	},
	hasExtraSocket: function (a) {
		return this.enhanceable && (a[this.getExtraSocketPos(a[0])] ? 1 : 0)
	},
	getExtraSocketPos: function (a) {
		if (!a || !g_items[a]) {
			return 4
		}
		return 4 + (g_items[a].jsonequip.nsockets | 0)
	},
	hasSocketBonus: function (b) {
		var c = 0;
		if (!g_items[b[0]] || !g_items[b[0]].jsonequip.nsockets) {
			return false
		}
		for (var a = 0; a < 3; ++a) {
			if (b[a + 4] && g_gems[b[a + 4]]) {
				if (this.matchGemSocket(g_gems[b[a + 4]].colors, g_items[b[0]].jsonequip["socket" + (a + 1)])) {++c
				}
			}
		}
		return (c == g_items[b[0]].jsonequip.nsockets)
	},
	isValidGem: function (a) {
		if (a.none) {
			return true
		}
		return ((this.gemSource == null || a.expansion == this.gemSource) && ((this.gemColor == null && this.currentItem.color != 1 && a.colors != 1) || this.matchGemSocket(a.colors, this.currentItem.color)))
	},
	filterGems: function (b, c, d) {
		switch (c) {
		case 0:
			this.gemSource = d;
			break;
		case 1:
			this.gemColor = d;
			break;
		default:
			return
		}
		if (b && b.nodeName == "A") {
			g_setSelectedLink(b, "gems" + c)
		}
		g_listviews.gems.updateFilters(true);
		return false
	},
	onGemPickerShow: function (o, j, e) {
		Lightbox.setSize(800, 564);
		var h;
		if (j) {
			o.className = "summary-picker listview";
			var f = [],
			m = ce("div"),
			n = ce("a"),
			i = ce("div");
			f.push({
				none: 1,
				__alwaysvisible: 1,
				_summary: this
			});
			for (var l in g_gems) {
				var k = {
					id: l,
					_summary: this
				};
				cO(k, g_gems[l]);
				f.push(k)
			}
			m.className = "listview";
			ae(o, m);
			n = ce("a");
			n.className = "screenshotviewer-close";
			n.href = "javascript:;";
			n.onclick = Lightbox.hide;
			ae(n, ce("span"));
			ae(o, n);
			i.className = "clear";
			ae(o, i);
			h = new Listview({
				template: "gempicker",
				id: "gems",
				parent: m,
				data: f,
				createNote: this.createGemPickerNote.bind(this),
				customFilter: this.isValidGem.bind(this)
			});
			if (Browser.firefox) {
				aE(h.getClipDiv(), "DOMMouseScroll", this.onPickerWheel)
			} else {
				h.getClipDiv().onmousewheel = this.onPickerWheel
			}
		} else {
			h = g_listviews.gems;
			h.clearSearch();
			h.updateFilters(true)
		}
		var c = h.getNoteTopDiv(),
		b = gE(c, "small"),
		g = gE(c, "a");
		if (this.currentItem.color == 1 || this.currentItem.color == 14) {
			b[1].style.display = "none"
		} else {
			b[1].style.display = ""
		}
		setTimeout(function () {
			h.focusSearch()
		},
		1)
	},
	onPickerWheel: function (a) {
		a = $E(a);
		if (a._wheelDelta < 0) {
			this.scrollTop += 27
		} else {
			this.scrollTop -= 27
		}
	},
	onMouseClick: function (b, c) {
		c = $E(c);
		if (c._button == 3 || c.shiftKey || c.ctrlKey) {
			return false
		}
	},
	onMouseUp: function (b, c) {
		c = $E(c);
		if (c._button == 3 || c.shiftKey || c.ctrlKey) {
			var f = Menu.getDiv(0, b.menu);
			_cursorPos = g_getCursorPos(c);
			if (Browser.ie6) {
				_cursorPos.x -= 2;
				_cursorPos.y -= 2
			}
			var g = g_getCursorPos(c);
			setTimeout(Menu.showAtCursor.bind(b, null, g.x, g.y), 1);
			Tooltip.hide()
		}
		return false
	},
	onSearchKeyUp: function (b) {
		var a = trim(this.searchName.value.replace(/\s+/g, " "));
		if (a == this.currentSearch) {
			return
		}
		this.currentSearch = a;
		this.prepareSearch(a)
	},
	onSearchKeyDown: function (a) {
		a = $E(a);
		switch (a.keyCode) {
		case 13:
			g_listviews.items.submitSearch(a);
			break;
		case 27:
			hide();
			break;
		case 38:
			cycle(0);
			break;
		case 40:
			cycle(1);
			break
		}
	},
	prepareSearch: function (a) {
		if (this.searchTimer > 0) {
			clearTimeout(this.searchTimer);
			this.searchTimer = 0
		}
		if (a) {
			st(this.searchMsg, sprintf(LANG["su_searching" + this.searchType], a))
		}
		this.searchMsg.className = "";
		this.searchTimer = setTimeout(this.searchItems.bind(this, a, this.searchType), 333)
	},
	searchItems: function (search, type) {
		var lv = g_listviews.items,
		_this = this,
		searchResults = [{
			none: 1
		}];
		lv.searchable = false;
		lv.setData(searchResults);
		lv.clearSearch();
		lv.updateFilters(true);
		this.searchMsg.className = "";
		if (!search) {
			st(this.searchMsg, LANG["su_specifyitem" + this.searchType]);
			return
		}
		st(this.searchMsg, sprintf(LANG["su_searching" + this.searchType], search));
// todo: find propper format
		new Ajax("?search=" + urlencode(search) + "&json&type=" + type, {
			method: "POST",
			search: search,
			onSuccess: function (xhr, opt) {
				var text = xhr.responseText;
				if (text.charAt(0) != "[" || text.charAt(text.length - 1) != "]") {
					return
				}
				var a = eval(text);
				if (search == opt.search && a.length == 3) {
					for (var i = 0, len = a[1].length; i < len; ++i) {
						var row = {};
						row.id = a[1][i].id;
						row.name = row["name_" + g_locale.name] = a[1][i].name.substring(1);
						row.quality = 7 - a[1][i].name.charAt(0);
						row.icon = a[1][i].icon;
						row.level = a[1][i].level;
						row.classs = a[1][i].classs;
						row.subclass = a[1][i].subclass;
						row.jsonequip = dO(a[1][i]);
						row._type = 3;
						row._summary = _this;
						g_items.add(a[1][i].id, row);
						if (_this.searchType == row._type) {
							searchResults.push(row)
						}
/*  dropped in favour of code block from 2.5.2012 (see above)
                        var _ = {};
						cO(_, a[1][i]);
						_._type = _this.searchType;
						_._summary = _this;
						searchResults.push(_)
*/
					}
					for (var i = 0, len = a[2].length; i < len; ++i) {
						var row = {};
						row.id = a[2][i].id;
						row.name = row["name_" + g_locale.name] = a[2][i].name.substring(1);
						row.quality = 7 - a[2][i].name.charAt(0);
						row.minlevel = a[2][i].minlevel;
						row.maxlevel = a[2][i].maxlevel;
						row.type = a[2][i].type;
						row.pieces = a[2][i].pieces;
						row.jsonequip = {};
						for (var j = 0, len2 = row.pieces.length; j < len2; ++j) {
							if (g_items[row.pieces[j]]) {
								var item = g_items[row.pieces[j]];
								for (var k in item.jsonequip) {
									if (LANG.traits[k] == null) {
										continue
									}
									if (!row.jsonequip[k]) {
										row.jsonequip[k] = 0
									}
									row.jsonequip[k] += item.jsonequip[k]
								}
							}
						}
						row._type = 4;
						row._summary = _this;
						if (_this.searchType == row._type) {
							searchResults.push(row)
						}
/*  dropped in favour of code block from 2.5.2012 (see above)
						var _ = {};
						_["name_" + g_locale.name] = a[2][i].name.substring(1);
						cO(_, {
							quality: 7 - parseInt(a[2][i].name.charAt(0)),
							icon: a[2][i].icon,
							jsonequip: {}
						});
						cO(_.jsonequip, a[2][i]);
						g_items.add(a[2][i].id, _)
*/
					}
					lv.searchable = true;
					ee(_this.searchMsg)
				} else {
					st(_this.searchMsg, sprintf(LANG.su_noresults, opt.search));
					_this.searchMsg.className = "q10"
				}
				lv.setData(searchResults);
				lv.clearSearch();
				lv.updateFilters(true)
			}
		})
	},
	ignoredSlots: {
		2 : 1,
		11 : 1,
		12 : 1,
		18 : 1,
		24 : 1,
		28 : 1
	},
	viewableIn3d: function (b) {
		for (var c = 0, a = b.group.length; c < a; ++c) {
			var e = g_items[b.group[c][0]].jsonequip;
			if (e.slotbak > 0 && e.displayid > 0 && !this.ignoredSlots[e.slotbak]) {
				return true
			}
		}
		return false
	},
	viewIn3dFromPound: function (a) {
		var b = parseInt(a) | 0;
		if (b >= 0 && b < this.groups.length) {
			this.viewIn3d(this.columns[this.clone.i + b])
		}
	},
	viewIn3d: function (b) {
		var e = [];
		for (var c = 0, a = b.group.length; c < a; ++c) {
			var f = g_items[b.group[c][0]].jsonequip;
			if (f.slotbak > 0 && f.displayid > 0 && !this.ignoredSlots[f.slotbak]) {
				e.push(f.slotbak);
				e.push(f.displayid)
			}
		}
		ModelViewer.show({
			type: 4,
			typeId: 9999,
			equipList: e,
			extraPound: b.i,
			noPound: (this.autoSave ? 1 : null)
		})
	},
	splitColumn: function (b) {
		this.groups.splice(b.i, 1);
		for (var c = 0, a = b.group.length; c < a; ++c) {
			this.groups.splice(b.i, 0, [b.group[c]])
		}
		this.refreshAll()
	},
	hasEnhancements: function (d) {
		for (var c = 0, a = d.length; c < a; ++c) {
			for (var b = 1; b < 8; ++b) {
				if (d[c][b] != 0) {
					return true
				}
			}
		}
		return false
	},
	clearEnhancements: function (b, e) {
		var d = 0,
		a = b.group.length;
		if (!isNaN(e)) {
			d = e;
			a = e + 1
		}
		for (var c = d; c < a; ++c) {
			b.group[c] = this.readItem(b.group[c][0])
		}
		this.refreshAll()
	},
	readGroups: function (e) {
		if (!e) {
			return
		}
		var d = e.split(";");
		for (var f = 0, g = d.length; f < g; ++f) {
			var h = [],
			a = d[f].split(":");
			for (var c = 0, b = a.length; c < b; ++c) {
				var k = this.readItem(a[c]);
				if (g_items[k[0]]) {
					h.push(k)
				}
			}
			if (h.length) {
				this.groups.push(h)
			}
		}
	},
	readItem: function (a) {
		a += "";
		var c = a.split(".", 8);
		while (c.length < 8) {
			c.push(0)
		}
		for (var b = 0; b < 8; ++b) {
			c[b] = parseInt(c[b])
		}
		return c
	},
	writeItem: function (a) {
		return a[0] ? a.join(".").replace(/(\.0)+$/, "") : ""
	},
	getItemName: function (b) {
		if (!g_items[b[0]]) {
			return
		}
		var a = g_items[b[0]].jsonequip.name.substr(1);
		if (g_items[b[0]].jsonequip.subitems && g_items[b[0]].jsonequip.subitems[b[1]]) {
			a += " " + g_items[b[0]].jsonequip.subitems[b[1]].name
		}
		return a
	},
	getItemRel: function (k, d) {
		if (!g_items[k[0]]) {
			return
		}
		var c = this.hasExtraSocket(k),
		j = [],
		b = [],
		h = [];
		if (k[1]) {
			j.push("rand=" + k[1])
		}
		if (k[2]) {
			j.push("ench=" + k[2])
		}
		for (var e = 0, f = (g_items[k[0]].jsonequip.nsockets | 0) + c; e < f; ++e) {
			b.push(k[4 + e] > 0 ? k[4 + e] : 0)
		}
		if (b.length) {
			j.push("gems=" + b.join(":"))
		}
		if (c) {
			j.push("sock")
		}
		if (g_items[k[0]].jsonequip.itemset) {
			for (var e = 0, f = d.group.length; e < f; ++e) {
				var a = d.group[e][0];
				if (g_items[a] && g_items[a].jsonequip.itemset) {
					h.push(a)
				}
			}
			j.push("pcs=" + h.join(":"))
		}
		var g = j.join("&");
		if (g) {
			g = "&" + g
		}
		return g
	},
	readWeights: function (f) {
		if (!f) {
			return
		}
		var e = f.split(";"),
		h = parseInt(e.length / 3);
		if (h > 0) {
			for (var g = 0; g < h; ++g) {
				var b = e[g],
				a = e[g + h].split(":"),
				m = e[g + h * 2].split(":");
				if (a.length > 0 && a.length == m.length) {
					var c = {
						name: b
					},
					n = false;
					for (var d = 0, k = a.length; d < k; ++d) {
						var l = fi_Lookup(a[d], "items");
						if (l && l.type == "num") {
							c[l.name] = parseInt(m[d]);
							n = true
						}
					}
					if (n) {
						this.weights.push(c)
					}
				}
			}
		}
	}
};
Summary.dragGroup = function (b, a, c) {
	a.style.width = c.offsetWidth + "px"
};
Summary.moveGroup = function (d, c, a, f) {
	if (!c.__col.group.length) {
		return
	}
	var b = [],
	g;
	cO(b, this.groups[c.__col.i]);
	if (!a) {
		if (confirm(LANG.message_deletegroup)) {
			this.deleteColumn(c.__col, d)
		}
		return
	} else {
		if (f && this.newgroup != null && a.__col.id == this.columns[this.newgroup].id) {
			g = this.groups.length - 1
		} else {
			if (f && a.__col.id != c.__col.id) {
				g = a.__col.i
			} else {
				return
			}
		}
	}
	if (!d.shiftKey) {
		if (this.selected != null && this.clone.i + c.__col.i <= this.selected) {
			this.selected--
		}
		this.groups.splice(c.__col.i, 1)
	}
	if (this.__selected.id == c.__col.id) {
		this.selected = this.clone.i + g
	} else {
		if (this.selected != null && this.clone.i + g <= this.selected) {
			this.selected++
		}
	}
	this.groups.splice(g, 0, b);
	this.refreshAll()
};
Summary.moveGroupItem = function (d, c, a) {
	if (!c.__col.group.length) {
		return
	}
	var b = [];
	cO(b, c.__col.group[c.i]);
	if (!a || (a.__col.group && !a.__col.group.length)) {
		if (confirm(sprintf(LANG.message_deleteitem, g_items[c.__col.group[c.i][0]].jsonequip.name.substring(1)))) {
			c.__col.group.splice(c.i, 1);
			this.refreshAll()
		}
		return
	} else {
		if (this.newgroup != null && a.__col.id == this.columns[this.newgroup].id) {
			this.groups.push([b])
		} else {
			if (a.__col.id != c.__col.id) {
				a.__col.group.push(b)
			} else {
				if (d.shiftKey) {
					c.__col.group.push(b)
				} else {
					return
				}
			}
		}
	}
	if (!d.shiftKey) {
		c.__col.group.splice(c.i, 1)
	}
	this.refreshAll()
};
Summary.addGroupItem = function (g, h) {
	if (g == 3) {
		this.groups.push([this.readItem(h)])
	} else {
		if (g == 4) {
			var c = [];
			for (var f in g_items) {
				if (g_items[f].jsonequip && g_items[f].jsonequip.itemset == h) {
					c.push(this.readItem(f))
				}
			}
			this.groups.push(c)
		}
	}
	this.refreshAll();
	Lightbox.hide();
	if (g == 3 && g_items[h].jsonequip.subitems) {
		var e;
		for (var f = 0, a = this.visibility.length; f < a; ++f) {
			var d = this.visibility[f];
			var b = this.columns[d];
			if (b.group && b.group[0][0] == h) {
				e = b
			}
		}
		if (e) {
			this.openSubitemPicker(e, 0)
		}
	}
	return false
};
Summary.groupOver = function (f, d) {
	var h = "",
	c = {};
	for (var b = 0, a = f.length; b < a; ++b) {
		c[f[b][0]] = (c[f[b][0]] | 0) + 1
	}
	for (var b = 0, a = f.length; b < a; ++b) {
		var g = f[b][0];
		if (g_items[g]) {
			h += "<tr>";
			h += '<td style="text-align: right">x' + c[g] + "</td>";
			h += '<td><div class="indent q' + g_items[g].quality + '">';
			h += '<span class="icontiny" style="background: url(http://' + location.hostname + location.pathname + 'images/icons/tiny/' + g_items[g].icon.toLowerCase() + '.gif) left center no-repeat">' + g_items[g]["name_" + g_locale.name] + "</span>";
			h += "</div></td>";
			h += '<td><div class="indent q1"><small><em>' + LANG.level + " " + g_items[g].jsonequip.level + "</em></small></div></td>";
			h += "</tr>"
		}
	}
	if (h) {
		Tooltip.showAtCursor(d, '<table style="white-space: nowrap">' + h + "</table>")
	}
};
Summary.addWeightScale = function (a) {
	a = this.createWeightScale();
	if (a) {
		this.weights.push(a)
	}
	this.toggleOptions();
	this.refreshAll()
};
Summary.resetScale = function () {
	var a = ge("su_classes");
	a.selectedIndex = 0;
	a.onchange()
};
Summary.weightOver = function (a, h) {
	var j = '<b class="q">' + a.name + "</b>",
	c = "",
	g = "",
	d, f = false;
	d = 0;
	for (var b in a) {
		if (LANG.traits[b] == null) {
			continue
		}
		if (d++>0) {
			c += "<br />"
		}
		c += a[b];
		f = true
	}
	if (f) {
		d = 0;
		for (var b in a) {
			if (LANG.traits[b] == null) {
				continue
			}
			if (d++>0) {
				g += "<br />"
			}
			g += LANG.traits[b][1]
		}
		j += '<table style="white-space: nowrap"><tr><td style="text-align: right">' + c + '</td><td><div class="indent">' + g + '</td></tr></table><span class="q2">' + LANG.su_toggle + "</span>"
	}
	Tooltip.showAtCursor(h, j)
};
Summary.socketOver = function (c, d) {
	var f = "",
	a = {
		1 : "#9D9D9D",
		2 : "#e60c0b",
		4 : "#ffff35",
		6 : "#f48905",
		8 : "#295df1",
		10 : "#b957fc",
		12 : "#22c516",
		14 : "#FFFFFF"
	};
	for (var b in c) {
		if (g_gems[b]) {
			f += "<tr>";
			f += '<td style="text-align: right"><div style="color: ' + a[g_gems[b].colors] + '">x' + c[b] + "</div></td>";
			f += '<td><div class="indent q' + g_gems[b].quality + '">';
			f += '<span class="icontiny" style="background: url(http://' + location.hostname + location.pathname + 'images/icons/tiny/' + g_gems[b].icon.toLowerCase() + '.gif) left center no-repeat">' + g_gems[b].name + "</span>";
			f += "</div></td>";
			f += '<td><div class="indent q1"><small><em>' + g_gems[b].enchantment + "</em></small></div></td>";
			f += "</tr>"
		}
	}
	if (f) {
		Tooltip.showAtCursor(d, '<table style="white-space: nowrap">' + f + "</table>")
	}
};
Summary.addItemSubitem = function (c) {
	var a = this.currentItem.col,
	b = this.currentItem.i;
	a.group[b][1] = c;
	this.refreshAll();
	Lightbox.hide()
};
Summary.addItemEnchant = function (c) {
	var a = this.currentItem.col,
	b = this.currentItem.i;
	a.group[b][2] = c;
	this.refreshAll();
	Lightbox.hide()
};
Summary.addItemGem = function (a) {
	var b = this.currentItem.col,
	c = this.currentItem.i,
	d = this.currentItem.socket;
	b.group[c][d] = a;
	this.refreshAll();
	Lightbox.hide()
};
Summary.toggleExtraSocket = function (c, d, b) {
	var f = c.group[d],
	e = this.getExtraSocketPos(f[0]);
	f[e] = (this.hasExtraSocket(f) ? 0 : -1);
	this.refreshAll()
};
Summary.funcBox = {
	createSockets: function (c, e, h, m, k, d) {
        if (!c) {
			return
		}
		var p = {
			1 : "meta",
			2 : "red",
			4 : "yellow",
			8 : "blue",
			14 : "prismatic"
		};
		var f, g = 0;
		for (f in p) {
			if (c[f] != null && c[f] != 0) {++g
			}
		}
		if (g == 0) {
			return
		}
		var b = ce("div");
		b.style.paddingBottom = "3px";
		ae(e, b);
		var l = 0,
		g = 0;
		for (f in p) {
			if (c[f] != null && c[f] != 0) {
				if (g > 0) {
					if (g % 2 == 0) {
						var b = ce("div");
						b.style.paddingBottom = "3px";
						ae(e, b)
					} else {
						ae(b, ct(String.fromCharCode(160, 160)))
					}
				}
				var o = ce("a");
				o.href = "?items=3&filter=" + (f == 14 ? "gb=1;cr=81:81:81;crs=2:3:4;crv=0:0:0;ma=1": "cr=81;crs=" + (l + 1) + ";crv=0");
				o.className = "moneysocket" + p[f];
				if (d && d[f]) {
					for (var n in d[f]) {
						if (g_gems[n]) {
							o.className += " tip";
							o.style.borderBottom = "1px dotted #808080";
							break
						}
					}
					o.onmouseover = Summary.socketOver.bind(o, d[f]);
					o.onmousemove = Tooltip.cursorUpdate;
					o.onmouseout = Tooltip.hide
				}
				if (h) {
					o.className += " " + (c[f] > 0 ? "q2": "q10");
					if (c[f] > 0) {
						ae(o, ct("+"))
					}
				} else {
					if (m && k && m[f] != k[f] && c[f] == k[f]) {
						o.style.fontWeight = "bold";
						o.className += " q2"
					}
				}
				ae(o, ct(c[f]));
				ae(b, o); ++g
			}++l
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
                id: "name",
                align: "left",
                compute: function (c, f, e, b) {
                    if (!this.__total.total[c.id]) {
                        e.style.display = "none"
                    }
                    f.style.whiteSpace = "nowrap";
                    f.style.paddingLeft = "4px";
                    f.style.paddingRight = "20px";
                    var a = (LANG.traits[c.id] ? LANG.traits[c.id][0] : c.name);
                    if (c.id == "gains") {
                        var d = ce("span");
                        d.className = "tip";
                        d.onmouseover = function () {
                            Tooltip.show(this, LANG.tooltip_gains, 0, 0, "q")
                        };
                        d.onmousemove = Tooltip.cursorUpdate;
                        d.onmouseout = Tooltip.hide;
                        ae(d, ct(a));
                        ae(f, d)
                    } else {
                        return a
                    }
                }
            },
            { id: "total", name: "Total", align: "center", hidden: 1 },
            { align: "center" },
            { id: "newgroup", align: "center", width: "100%" }
        ],
		traits: [
            { id: "sepgeneral", type: "sep" },
            {
                id: "score",
                name: LANG.score,
                type: "custom",
                json: ["id"],
                calcTotal: function (k, g, e) {
                    if (this.scoreMode == null) {
                        this.scoreMode = 0
                    }
                    for (var b = 0, c = this.weights.length; b < c; ++b) {
                        var a = 0,
                        d = 0,
                        h = this.weights[b];
                        for (var j in h) {
                            if (!LANG.traits[j]) {
                                continue
                            }
                            d += h[j];
                            if (!isNaN(k[j])) {
                                a += k[j] * h[j]
                            }
                        }
                        g[b] = (g[b] | 0) + a;
                        this.weights[b].f = d;
                        if (!e && (this.weights[b].m == null || g[b] > this.weights[b].m)) {
                            this.weights[b].m = g[b]
                        }
                    }
                },
                calcDifference: function (e, d) {
                    var g = {};
                    for (var f = 0, c = this.weights.length; f < c; ++f) {
                        g[f] = e[f] - d[f]
                    }
                    return g
                },
                compute: function (c, h) {
                    if (!c.total || !c.difference) {
                        return
                    }
                    var e = 0,
                    o = (this.selected != null && this.__selected.id != c.id);
                    h.style.whiteSpace = "nowrap";
                    h.style.padding = "4px";
                    for (var j = 0, k = this.weights.length; j < k; ++j) {
                        var f = this.weights[j];
                        var g = (o ? c.difference.score[j] : c.total.score[j]),
                        b = f.name;
                        if (g != null) {
                            if (!f.name) {
                                f.name = sprintf(LANG.su_customscale, ++e)
                            }
                            switch (this.scoreMode) {
                            case 0:
                                g = (f.f != 0 ? g / f.f: 0).toFixed(2);
                                break;
                            case 2:
                                g = (f.m ? 100 * g / f.m: 0).toFixed(1) + "%";
                                break
                            }
                            var m = ce("a");
                            m.className = "summary-score-remove";
                            m.href = "javascript:;";
                            m.onclick = this.deleteWeightScale.bind(this, j);
                            ae(m, ct("x"));
                            ae(h, m);
                            var l = ce("div");
                            l.className = "summary-score-row" + (j % 2);
                            ae(h, l);
                            var m = ce("a");
                            m.className = (o ? (g > 0 ? "q2": "q10") : "q1");
                            m.style.borderBottom = "1px dotted #808080";
                            m.href = "javascript:;";
                            m.onclick = this.toggleWeights.bind(this);
                            m.onmouseover = Summary.weightOver.bind(m, f);
                            m.onmousemove = Tooltip.cursorUpdate;
                            m.onmouseout = Tooltip.hide;
                            ae(m, ct(g));
                            ae(l, m)
                        }
                    }
                }
            },

            { id: "sepgeneral", type: "sep" },
            { id: "level", type: "avg", digits: 1, itemonly: 1 },
            { id: "reqlevel", type: "max" },

            { id: "sepbasestats", type: "sep" },
            { id: "agi", type: "sum" },
            { id: "int", type: "sum" },
            { id: "sta", type: "sum" },
            { id: "spi", type: "sum" },
            { id: "str", type: "sum" },
            { id: "health", type: "sum" },
            { id: "mana", type: "sum" },
            { id: "healthrgn", type: "sum" },
            { id: "manargn", type: "sum" },

            { id: "sepdefensivestats", type: "sep" },
            { id: "armor", type: "sum" },
            { id: "blockrtng", type: "sum", rating: 15 },
            { id: "block", type: "sum" },
            { id: "defrtng", type: "sum", rating: 12 },
            { id: "dodgertng", type: "sum", rating: 13 },
            { id: "parryrtng", type: "sum", rating: 14 },
            { id: "resirtng", type: "sum", rating: 35 },

            { id: "sepoffensivestats", type: "sep" },
            { id: "atkpwr", type: "sum" },
            { id: "feratkpwr", type: "sum", indent: 1 },
            { id: "armorpenrtng", type: "sum", rating: 44 },
            { id: "critstrkrtng", type: "sum", rating: 32 },
            { id: "exprtng", type: "sum", rating: 37 },
            { id: "hastertng", type: "sum", rating: 36 },
            { id: "hitrtng", type: "sum", rating: 31 },
            { id: "splpen", type: "sum" },
            { id: "splpwr", type: "sum" },
            { id: "arcsplpwr", type: "sum", indent: 1 },
            { id: "firsplpwr", type: "sum", indent: 1 },
            { id: "frosplpwr", type: "sum", indent: 1 },
            { id: "holsplpwr", type: "sum", indent: 1 },
            { id: "natsplpwr", type: "sum", indent: 1 },
            { id: "shasplpwr", type: "sum", indent: 1 },

            { id: "sepweaponstats", type: "sep" },
            { id: "dmg", type: "sum" },
            { id: "mledps", type: "sum" },
            { id: "rgddps", type: "sum" },
            { id: "mledmgmin", type: "sum" },
            { id: "rgddmgmin", type: "sum" },
            { id: "mledmgmax", type: "sum" },
            { id: "rgddmgmax", type: "sum" },
            { id: "mlespeed", type: "sum" },
            { id: "rgdspeed", type: "sum" },

            { id: "sepresistances", type: "sep" },
            { id: "arcres", type: "sum" },
            { id: "firres", type: "sum" },
            { id: "frores", type: "sum" },
            { id: "holres", type: "sum" },
            { id: "natres", type: "sum" },
            { id: "shares", type: "sum" },

            { id: "sepindividualstats", type: "sep" },
            { id: "mleatkpwr", type: "sum" },
            { id: "mlecritstrkrtng", type: "sum", rating: 19 },
            { id: "mlehastertng", type: "sum", rating: 28 },
            { id: "mlehitrtng", type: "sum", rating: 16 },
            { id: "rgdatkpwr", type: "sum" },
            { id: "rgdcritstrkrtng", type: "sum", rating: 20 },
            { id: "rgdhastertng", type: "sum", rating: 29 },
            { id: "rgdhitrtng", type: "sum", rating: 17 },
            { id: "splcritstrkrtng", type: "sum", rating: 21 },
            { id: "splhastertng", type: "sum", rating: 30 },
            { id: "splhitrtng", type: "sum", rating: 18 },
            { id: "spldmg", type: "sum" },
            { id: "splheal", type: "sum" },

            { id: "", name: LANG.sockets, type: "sep" },
            {
                id: "gems",
                name: LANG.gems,
                type: "custom",
                json: ["id"],
                itemonly: 1,
                calcTotal: function (e, d, f, a, b) {
                    for (var c = 1; c <= 8; c *= 2) {
                        if (d[c] == null) {
                            d[c] = {}
                        }
                    }
                    if (d[14] == null) {
                        d[14] = {}
                    }
                    for (var c = 1; c < 4; ++c) {
                        if ((s = e["socket" + c]) && a[c + 3]) {
                            d[s][a[c + 3]] = (d[s][a[c + 3]] | 0) + 1
                        }
                    }
                    c = (e.nsockets | 0) + 4;
                    if (b && a[c]) {
                        d[14][a[c]] = (d[14][c] | 0) + 1
                    }
                },
                hidden: 1
            },
            {
                id: "sockets",
                name: LANG.sockets,
                type: "custom",
                json: ["socket1"],
                calcTotal: function (e, d, f, a, b) {
                    for (var c = 1; c <= 8; c *= 2) {
                        d[c] = (d[c] | 0)
                    }
                    d[14] = (d[14] | 0);
                    for (var c = 1; c < 4; ++c) {
                        if (s = e["socket" + c]) {
                            d[s]++
                        }
                    }
                    if (b) {
                        d[14]++
                    }
                },
                calcDifference: function (d, c) {
                    var f = {};
                    for (var e = 1; e <= 8; e *= 2) {
                        if (d[e] != null || c[e] != null) {
                            f[e] = (d[e] | 0) - (c[e] | 0)
                        }
                    }
                    return f
                },
                compute: function (b, d) {
                    var a = (this.selected != null && this.__selected.id != b.id);
                    var c = a ? b.difference: b.total;
                    if (!c) {
                        return
                    }
                    if (this.groups.length > 1) {
                        Summary.funcBox.createSockets(c.sockets, d, a, this.minValue.sockets, this.maxValue.sockets, c.gems)
                    } else {
                        Summary.funcBox.createSockets(c.sockets, d, a, null, null, c.gems)
                    }
                }
            },
            { id: "nsockets", type: "sum" },
            { id: "", type: "sep" },
            {
                id: "gains",
                name: LANG.gains,
                type: "custom",
                json: ["id"],
                compute: function (b, g) {
                    if (b.id == this.columns[this.total].id || !b.total) {
                        return
                    }
                    g.style.whiteSpace = "nowrap";
                    g.style.textAlign = "left";
                    var d = 0;
                    for (var c in this.minValue) {
                        if (b.total[c] > this.minValue[c]) {
                            if (d++>0) {
                                ae(g, ce("br"))
                            }
                            var f = (Math.round(1000 * (b.total[c] - this.minValue[c])) / 1000);
                            ae(g, ct(f + " " + LANG.traits[c][1]));
                            if (this.ratings[c] != null) {
                                var a = g_convertRatingToPercent(this.level, this.ratings[c], f);
                                a = number_format((Math.round(a * 100) / 100));
                                if (this.ratings[c] != 12 && this.ratings[c] != 37) {
                                    a += "%"
                                }
                                var e = ce("small");
                                ae(e, ct(" (" + a + ")"));
                                ae(g, e)
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
		columns: [{
			id: "name",
			align: "left",
			compute: function (b, d, c, a) {
				if (!this.__total.total[b.id]) {
					c.style.display = "none"
				}
				return (LANG.traits[b.id] ? LANG.traits[b.id][0] : b.name)
			}
		},
		{ id: "total", name: "Total", align: "center" },
		{ align: "center" }],
		traits: [
            { id: "str", type: "sum" },
            { id: "agi", type: "sum" },
            { id: "sta", type: "sum" },
            { id: "int", type: "sum" },
            { id: "spi", type: "sum" },
			{ id: "health", type: "sum" },
			{ id: "mana", type: "sum" },
			{ id: "healthrgn", type: "sum" },
			{ id: "manargn", type: "sum" },
			{ id: "armor", type: "sum" },
			{ id: "block", type: "sum" },
			{ id: "defrtng", type: "sum", rating: 12 },
			{ id: "dodgertng", type: "sum", rating: 13 },
			{ id: "parryrtng", type: "sum", rating: 14 },
			{ id: "resirtng", type: "sum", rating: 35 },
			{ id: "blockrtng", type: "sum", rating: 15 },
			{ id: "atkpwr", type: "sum" },
			{ id: "feratkpwr", type: "sum" },
			{ id: "armorpenrtng", type: "sum", rating: 44 },
			{ id: "critstrkrtng", type: "sum", rating: 32 },
			{ id: "exprtng", type: "sum", rating: 37 },
			{ id: "hastertng", type: "sum", rating: 36 },
			{ id: "hitrtng", type: "sum", rating: 31 },
			{ id: "splpen", type: "sum" },
			{ id: "splpwr", type: "sum" },
			{ id: "arcsplpwr", type: "sum" },
			{ id: "firsplpwr", type: "sum" },
			{ id: "frosplpwr", type: "sum" },
			{ id: "holsplpwr", type: "sum" },
			{ id: "natsplpwr", type: "sum" },
			{ id: "shasplpwr", type: "sum" },
			{ id: "dps", type: "sum" },
			{ id: "dmg", type: "sum" },
			{ id: "arcres", type: "sum" },
			{ id: "firres", type: "sum" },
			{ id: "frores", type: "sum" },
			{ id: "holres", type: "sum" },
			{ id: "natres", type: "sum" },
			{ id: "shares", type: "sum" },
			{ id: "mleatkpwr", type: "sum" },
			{ id: "mlecritstrkrtng", type: "sum", rating: 19 },
			{ id: "mlehastertng", type: "sum", rating: 28 },
			{ id: "mlehitrtng", type: "sum", rating: 16 },
			{ id: "rgdatkpwr", type: "sum" },
			{ id: "rgdcritstrkrtng", type: "sum", rating: 20 },
			{ id: "rgdhastertng", type: "sum", rating: 29 },
			{ id: "rgdhitrtng", type: "sum", rating: 17 },
			{ id: "splcritstrkrtng", type: "sum", rating: 21 },
			{ id: "splhastertng", type: "sum", rating: 30 },
			{ id: "splhitrtng", type: "sum", rating: 18 },
			{ id: "spldmg", type: "sum" },
			{ id: "splheal", type: "sum" },
            { id: "sockets",
                name: LANG.sockets,
                type: "custom",
                json: ["socket1"],
                calcTotal: function (c, b) {
                    for (var a = 1; a <= 8; a *= 2) {
                        b[a] = (b[a] | 0)
                    }
                    for (var a = 1; a < 4; ++a) {
                        if (s = c["socket" + a]) {
                            b[s]++
                        }
                    }
                },
                calcDifference: function (d, c) {
                    var f = {};
                    for (var e = 1; e <= 8; e *= 2) {
                        if (d[e] != null || c[e] != null) {
                            f[e] = (d[e] | 0) - (c[e] | 0)
                        }
                    }
                    return f
                },
                compute: function (b, d) {
                    var a = (this.selected != null && this.__selected.id != b.id);
                    var c = a ? b.difference: b.total;
                    if (!c) {
                        return
                    }
                    if (this.groups.length > 1) {
                        Summary.funcBox.createSockets(c.sockets, d, a, this.minValue.sockets, this.maxValue.sockets)
                    } else {
                        Summary.funcBox.createSockets(c.sockets, d, a)
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
	onBeforeCreate: function () {
		this.applySort()
	},
	onSearchSubmit: function (a) {
		if (this.nRowsVisible != 1) {
			return
		} (Summary.addGroupItem.bind(a._summary, a._type, a.id))()
	},
	columns: [{
		id: "item",
		type: "text",
		align: "left",
		value: "name",
		compute: function (f, h, g) {
			if (f.none) {
				g.style.display = "none";
				return
			}
			var b = ce("a");
			b.className = "q" + f.quality;
			b.style.fontFamily = "Verdana, sans-serif";
			b.onclick = rf;
			switch (f._type) {
			case 3:
				if (!Browser.ie67) {
					this.template.columns[0].span = 2;
					var c = ce("td");
					c.style.width = "1px";
					c.style.padding = "0";
					c.style.borderRight = "none";
					var d = Icon.create(f.icon, 0, null, "?item=" + f.id),
					e = Icon.getLink(d);
					ae(c, d);
					ae(g, c);
					h.style.borderLeft = "none";
					e.onclick = rf;
					b.href = "?item=" + f.id
				}
				break;
			case 4:
				b.href = "?itemset=" + f.id;
				break
			}
			ae(b, ct(f.name));
			nw(h);
			ae(h, b);
			g.onclick = Summary.addGroupItem.bind(f._summary, f._type, f.id)
		},
		sortFunc: function (d, c, e) {
			if (d.none) {
				return -1
			}
			if (c.none) {
				return 1
			}
			return ( -strcmp(d.quality, c.quality) || strcmp(d.name, c.name) || -strcmp(d.level, c.level))
		}
	},
	{
		id: "level",
		name: LANG.level,
		type: "range",
		getMinValue: function (a) {
			return a.minlevel | a.level
		},
		getMaxValue: function (a) {
			return a.maxlevel | a.level
		},
		compute: function (a, c, b) {
			if (a.none) {
				return
			}
			switch (a._type) {
			case 3:
				return a.level;
				break;
			case 4:
				if (a.minlevel > 0 && a.maxlevel > 0) {
					if (a.minlevel != a.maxlevel) {
						return a.minlevel + LANG.hyphen + a.maxlevel
					} else {
						return a.minlevel
					}
				} else {
					return -1
				}
				break
			}
		}
	},
	{
		id: "pieces",
		name: LANG.pieces,
		getValue: function (a) {
			return a.pieces ? a.pieces.length: null
		},
		compute: function (a, b) {
			b.style.padding = "0";
			Listview.funcBox.createCenteredIcons(a.pieces, b)
		}
	},
	{
		id: "type",
		name: LANG.type,
		type: "text",
		compute: function (d, f, e) {
			f.className = "small q1";
			nw(f);
			var b = ce("a");
			switch (d._type) {
			case 3:
				var c = Listview.funcBox.getItemType(d.classs, d.subclass, d.subsubclass);
				b.href = c.url;
				ae(b, ct(c.text));
				break;
			case 4:
				ae(b, ct(g_itemset_types[d.type]));
				break
			}
			ae(f, b)
		},
		getVisibleText: function (a) {
			return Listview.funcBox.getItemType(a.classs, a.subclass, a.subsubclass).text
		}
	}]
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
	onBeforeCreate: function () {
		this.applySort();
		if (!Browser.ie67) {
			this.template.columns[0].span = 2
		}
	},
	onSearchSubmit: function (a) {
		if (this.nRowsVisible != 2 || !a.id) {
			return
		} (Summary.addItemSubitem.bind(a._summary, a.id))()
	},
	columns: [{
		id: "subitem",
		type: "text",
		align: "left",
		value: "name",
		compute: function (j, c, e) {
			if (j.none) {
				return
			}
			var b = "?item=" + j.item,
			k = g_items[j.item];
			if (!Browser.ie67) {
				var d = ce("td");
				d.style.width = "1px";
				d.style.padding = "0";
				d.style.borderRight = "none";
				var f = Icon.create(k.icon, 0, null, b),
				g = Icon.getLink(f);
				ae(d, f);
				ae(e, d);
				c.style.borderLeft = "none";
				g.onclick = rf;
				g.rel = "rand=" + j.id
			}
			var h = ce("a");
			if (j.quality != -1) {
				h.className = "q" + k.quality
			}
			h.style.fontFamily = "Verdana, sans-serif";
			h.onclick = rf;
			h.href = b;
			h.rel = "rand=" + j.id;
			ae(h, ct(k["name_" + g_locale.name] + " " + j.name));
			nw(c);
			ae(c, h);
			e.onclick = Summary.addItemSubitem.bind(j._summary, j.id)
		}
	},
	{
		id: "enchantment",
		type: "text",
		align: "left",
		value: "enchantment",
		compute: function (a, e, b) {
			if (a.none) {
				return
			}
			var c = ce("div");
			c.className = "small crop";
			e.title = a.enchantment;
			ae(c, ct(a.enchantment));
			ae(e, c)
		}
	}]
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
	onBeforeCreate: function () {
		this.applySort();
		if (!Browser.ie67) {
			this.template.columns[0].span = 2
		}
	},
	onSearchSubmit: function (a, b) {
		if (this.nRowsVisible != 2 || !a.id) {
			return
		} (Summary.addItemEnchant.bind(a._summary, a.id))()
	},
	columns: [{
		id: "enchant",
		type: "text",
		align: "left",
		value: "name",
		compute: function (h, j, g) {
			if (h.none) {
				return
			}
			var c = (h.source > 0 ? "?spell=": "?item=") + Math.abs(h.source);
			if (!Browser.ie67) {
				var d = ce("td");
				d.style.width = "1px";
				d.style.padding = "0";
				d.style.borderRight = "none";
				var e = Icon.create(h.icon, 0, null, c),
				f = Icon.getLink(e);
				ae(d, e);
				ae(g, d);
				j.style.borderLeft = "none";
				f.onclick = rf
			}
			var b = ce("a");
			if (h.quality != -1) {
				b.className = "q" + h.quality
			}
			b.style.fontFamily = "Verdana, sans-serif";
			b.onclick = rf;
			b.href = c;
			ae(b, ct(h.name));
			nw(j);
			ae(j, b);
			g.onclick = Summary.addItemEnchant.bind(h._summary, h.id)
		},
		sortFunc: function (d, c, e) {
			if (d.none) {
				return -1
			}
			if (c.none) {
				return 1
			}
			return ( -strcmp(d.quality, c.quality) || strcmp(d.type, c.type) || strcmp(d.name, c.name))
		}
	},
	{
		id: "enchantment",
		type: "text",
		align: "left",
		value: "enchantment",
		compute: function (b, e, a) {
			if (b.none) {
				return
			}
			var c = ce("div");
			c.className = "small crop";
			e.title = b.enchantment;
			ae(c, ct(b.enchantment));
			ae(e, c)
		}
	},
	{
		id: "skill",
		type: "text",
		compute: function (b, c, a) {
			if (b.none) {
				ee(a);
				a.onclick = Summary.addItemEnchant.bind(b._summary, 0);
				c.colSpan = (Browser.ie67 ? 3 : 4);
				c.style.fontWeight = "bold";
				c.style.textAlign = "center";
				return LANG.dash + LANG.pr_noneenchant + LANG.dash
			}
			c.className = "small q0";
			nw(c);
			if (b.skill > 0) {
				return g_spell_skills[b.skill]
			} else {
				return LANG.types[3][0]
			}
		},
		getVisibleText: function (a) {
			if (a.none) {
				return
			}
			if (a.skill > 0) {
				return g_spell_skills[a.skill]
			} else {
				return LANG.types[3][0]
			}
		}
	}]
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
	onBeforeCreate: function () {
		this.applySort();
		if (!Browser.ie67) {
			this.template.columns[0].span = 2
		}
	},
	onSearchSubmit: function (a) {
		if (this.nRowsVisible != 2 || !a.id) {
			return
		} (Summary.addItemGem.bind(a._summary, a.id))()
	},
	sexyColors: {
		1 : "#9D9D9D",
		2 : "#e60c0b",
		4 : "#ffff35",
		6 : "#f48905",
		8 : "#295df1",
		10 : "#b957fc",
		12 : "#22c516",
		14 : "#FFFFFF"
	},
	columns: [{
		id: "gem",
		type: "text",
		align: "left",
		value: "name",
		compute: function (h, g, f) {
			if (h.none) {
				return
			}
			if (!Browser.ie67) {
				var c = ce("td");
				c.style.width = "1px";
				c.style.padding = "0";
				c.style.borderRight = "none";
				var d = Icon.create(h.icon, 0, null, "?item=" + h.id),
				e = Icon.getLink(d);
				ae(c, d);
				ae(f, c);
				g.style.borderLeft = "none";
				e.onclick = rf
			}
			var b = ce("a");
			b.className = "q" + h.quality;
			b.style.fontFamily = "Verdana, sans-serif";
			b.onclick = rf;
			b.href = "?item=" + h.id;
			ae(b, ct(h.name));
			nw(g);
			ae(g, b);
			f.onclick = Summary.addItemGem.bind(h._summary, h.id)
		},
		sortFunc: function (d, c, e) {
			if (d.none) {
				return -1
			}
			if (c.none) {
				return 1
			}
			return ( -strcmp(d.gearscore, c.gearscore) || -strcmp(d.quality, c.quality) || strcmp(d.colors, c.colors) || strcmp(d.icon, c.icon) || strcmp(d.name, c.name))
		}
	},
	{
		id: "enchantment",
		type: "text",
		align: "left",
		value: "enchantment",
		compute: function (e, c, a) {
			if (e.none) {
				return
			}
			var b = ce("div");
			b.className = "small crop";
			c.title = e.enchantment;
			ae(b, ct(e.enchantment));
			ae(c, b)
		}
	},
	{
		id: "colors",
		compute: function (c, b, a) {
			if (c.none) {
				ee(a);
				a.onclick = Summary.addItemGem.bind(c._summary, 0);
				b.colSpan = (Browser.ie67 ? 3 : 4);
				b.style.fontWeight = "bold";
				b.style.textAlign = "center";
				return LANG.dash + LANG.pr_nonegem + LANG.dash
			}
			b.className = "small";
			nw(b);
			b.style.color = this.template.sexyColors[c.colors];
			return g_gem_colors[c.colors]
		},
		getVisibleText: function (a) {
			if (a.none) {
				return
			}
			return g_gem_colors[a.colors]
		}
	}]
};