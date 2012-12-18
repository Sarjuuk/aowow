var tc_loaded = false,
tc_object, tc_classId = -1,
tc_classIcons = {},
tc_build = "",
tc_glyphs = "";

function tc_init() {
	var c;
	g_initPath([1, 0]);
	ge("tc-classes").className = "choose";
	var e = g_sortJsonArray(g_chr_classes, g_chr_classes);
	c = ge("tc-classes-inner");
	for (var d = 0, b = e.length; d < b; ++d) {
		var h = e[d],
		f = Icon.create("class_" + g_file_classes[h], 1, null, "javascript:;"),
		g = Icon.getLink(f);
		tc_classIcons[h] = f;
		if (Browser.ie6) {
			g.onfocus = tb
		}
		g.onclick = tc_classClick.bind(g, h);
		g.onmouseover = tc_classOver.bind(g, h);
		g.onmouseout = Tooltip.hide;
		ae(c, f)
	}
	var a = ce("div");
	a.className = "clear";
	ae(c, a);
	tc_object = new TalentCalc();
	tc_object.initialize("tc-itself", {
		onChange: tc_onChange
	});
	tc_readPound();
	setInterval(tc_readPound, 1000)
}

function tc_classClick(a) {
	if (tc_object.setClass(a)) {
		Tooltip.hide()
	}
	return false
}

function tc_classOver(a) {
	Tooltip.show(this, "<b>" + g_chr_classes[a] + "</b>", 0, 0, "c" + a)
}

function tc_onChange(a, e, d) {
	var c;
	if (e.classId != tc_classId) {
		if (!tc_loaded) {
			var c = ge("tc-classes");
			de(gE(c, "p")[0]);
			c.className = "";
			tc_loaded = true
		}
		if (tc_classId != -1) {
			c = tc_classIcons[tc_classId];
			c.className = tc_classIcons[tc_classId].className.replace("iconmedium-gold", "")
		}
		tc_classId = e.classId;
		c = tc_classIcons[tc_classId];
		c.className += " iconmedium-gold";
		g_initPath([1, 0, tc_classId])
	}
	tc_build = a.getWhBuild();
	tc_glyphs = a.getWhGlyphs();
	var b = "#" + tc_build;
	if (tc_glyphs != "") {
		b += ":" + tc_glyphs
	}
	location.replace(b);
	var g = document.title;
	if (g.indexOf("/") != -1) {
		var f = g.indexOf("- ");
		if (f != -1) {
			g = g.substring(f + 2)
		}
	}
	document.title = g_chr_classes[tc_classId] + " (" + d[0].k + "/" + d[1].k + "/" + d[2].k + ") - " + g
}

function tc_readPound() {
	if (location.hash) {
		if (location.hash.indexOf("-") != -1) {
			var d = location.hash.substr(1).split("-"),
			a = d[0] || "",
			f = d[1] || "",
			c = -1;
			for (var g in g_file_classes) {
				if (g_file_classes[g] == a) {
					c = g;
					break
				}
			}
			if (c != -1) {
				tc_object.setBlizzBuild(c, f)
			}
		} else {
			var d = location.hash.substr(1).split(":"),
			e = d[0] || "",
			b = d[1] || "";
			if (tc_build != e) {
				tc_build = e;
				tc_object.setWhBuild(tc_build)
			}
			if (tc_glyphs != b) {
				tc_glyphs = b;
				tc_object.setWhGlyphs(tc_glyphs)
			}
		}
	}
};