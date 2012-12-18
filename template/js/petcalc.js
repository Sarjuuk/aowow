var pc_loaded = false,
pc_object, pc_classId = -1,
pc_classIcons = {},
pc_build = "";
function pc_init() {
	var j, d;
	g_initPath([1, 2]);
	ge("pc-classes").className = "choose";
	var b = g_sortJsonArray(g_pet_families, g_pet_families),
	a = ce("div");
	a.className = "pc-classes-inner-family";
	j = ge("pc-classes-inner");
	for (var c = 0, e = b.length; c < e; ++c) {
		var f = b[c],
		h = Icon.create(g_pet_icons[f], 1, null, "javascript:;"),
		g = Icon.getLink(h);
		pc_classIcons[f] = h;
		if (Browser.ie6) {
			g.onfocus = tb
		}
		g.onclick = pc_classClick.bind(g, f);
		g.onmouseover = pc_classOver.bind(g, f);
		g.onmouseout = Tooltip.hide;
		ae(a, h)
	}
	ae(j, a);
	d = ce("div");
	d.className = "clear";
	ae(a, d);
	d = ce("div");
	d.className = "clear";
	ae(j, d);
	pc_object = new TalentCalc();
	pc_object.initialize("pc-itself", {
		onChange: pc_onChange,
		mode: TalentCalc.MODE_PET
	});
	ge("pc-itself").className += " choose";
	pc_readPound();
	setInterval(pc_readPound, 1000)
}
function pc_classClick(a) {
	if (pc_object.setClass(a)) {
		Tooltip.hide()
	}
	return false
}
function pc_classOver(a) {
	Tooltip.show(this, "<b>" + g_pet_families[a] + "</b>")
}
function pc_onChange(a, d, c) {
	var b;
	if (d.classId != pc_classId) {
		if (!pc_loaded) {
			b = ge("pc-itself");
			b.className = b.className.replace("choose", "");
			b = ge("pc-classes");
			de(gE(b, "p")[0]);
			b.className = "";
			pc_loaded = true
		}
		if (pc_classId != -1) {
			b = pc_classIcons[pc_classId];
			b.className = pc_classIcons[pc_classId].className.replace("iconmedium-gold-selected", "")
		}
		pc_classId = d.classId;
		b = pc_classIcons[pc_classId];
		b.className += " iconmedium-gold-selected";
		g_initPath([1, 2, pc_classId])
	}
	pc_build = a.getWhBuild();
	location.replace("?petcalc#" + pc_build);
	var f = document.title;
	if (f.indexOf(" (") != -1) {
		var e = f.indexOf("- ");
		if (e != -1) {
			f = f.substring(e + 2)
		}
	}
	document.title = g_pet_families[pc_classId] + " (" + c.k + ") - " + f
}
function pc_readPound() {
	if (location.hash) {
		var a = location.hash.substr(1);
		if (pc_build != a) {
			pc_build = a;
			pc_object.setWhBuild(pc_build)
		}
	}
};