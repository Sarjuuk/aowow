// Needed for IE because it's dumb
'abbr article aside audio canvas details figcaption figure footer header hgroup mark menu meter nav output progress section summary time video'.replace(/\w+/g,function(n){document.createElement(n)})

/*********/
/* ROLES */
/*********/

var U_GROUP_TESTER     = 0x1;
var U_GROUP_ADMIN      = 0x2;
var U_GROUP_EDITOR     = 0x4;
var U_GROUP_MOD        = 0x8;
var U_GROUP_BUREAU     = 0x10;
var U_GROUP_DEV        = 0x20;
var U_GROUP_VIP        = 0x40;
var U_GROUP_BLOGGER    = 0x80;
var U_GROUP_PREMIUM    = 0x100;
var U_GROUP_LOCALIZER  = 0x200;
var U_GROUP_SALESAGENT = 0x400;
var U_GROUP_SCREENSHOT = 0x800;
var U_GROUP_VIDEO      = 0x1000;
var U_GROUP_APIONLY    = 0x2000;
var U_GROUP_PENDING    = 0x4000;

/******************/
/* ROLE SHORTCUTS */
/******************/

var U_GROUP_STAFF               = U_GROUP_ADMIN   | U_GROUP_EDITOR    | U_GROUP_MOD | U_GROUP_BUREAU | U_GROUP_DEV | U_GROUP_BLOGGER | U_GROUP_LOCALIZER | U_GROUP_SALESAGENT;
var U_GROUP_EMPLOYEE            = U_GROUP_ADMIN   | U_GROUP_BUREAU    | U_GROUP_DEV;
var U_GROUP_GREEN_TEXT          = U_GROUP_MOD     | U_GROUP_BUREAU    | U_GROUP_DEV;
var U_GROUP_MODERATOR           = U_GROUP_ADMIN   | U_GROUP_MOD       | U_GROUP_BUREAU;
var U_GROUP_COMMENTS_MODERATOR  = U_GROUP_BUREAU  | U_GROUP_MODERATOR | U_GROUP_LOCALIZER;
var U_GROUP_PREMIUM_PERMISSIONS = U_GROUP_PREMIUM | U_GROUP_STAFF     | U_GROUP_VIP;

var g_users = {};

function g_isUsernameValid(username) {
	return (username.match(/[^a-z0-9]/i) == null && username.length >= 4 && username.length <= 16);
}

var User = new function() {
	var self = this;

	/**********/
	/* PUBLIC */
	/**********/

	self.hasPermissions = function(roles) {
		if (!roles) {
			return true;
		}

		return !!(g_user.roles & roles);
	}

	/**********/
	/* PRIVATE */
	/**********/

};

function Ajax(url, opt) {
	if (!url) {
		return;
	}

	var _;

	try { _ = new XMLHttpRequest() }
    catch(e) {
		try { _ = new ActiveXObject("Msxml2.XMLHTTP") }
        catch(e) {
			try { _ = new ActiveXObject("Microsoft.XMLHTTP") }
            catch(e) {
				if (window.createRequest) {
					_ = window.createRequest();
				}
                else {
					alert(LANG.message_ajaxnotsupported);
					return;
				}
			}
		}
	}

	this.request = _;

	$WH.cO(this, opt);
	this.method = this.method || (this.params && 'POST') || 'GET';

	_.open(this.method, url, this.async == null ? true: this.async);
	_.onreadystatechange = Ajax.onReadyStateChange.bind(this);

	if (this.method.toUpperCase() == 'POST') {
		_.setRequestHeader('Content-Type', (this.contentType || 'application/x-www-form-urlencoded') + '; charset=' + (this.encoding || 'UTF-8'));
	}

	_.send(this.params);
}

Ajax.onReadyStateChange = function() {
	if (this.request.readyState == 4) {
		if (this.request.status == 0 || (this.request.status >= 200 && this.request.status < 300)) {
			this.onSuccess != null && this.onSuccess(this.request, this);
		}
        else {
			this.onFailure != null && this.onFailure(this.request, this);
		}

		if (this.onComplete != null) {
			this.onComplete(this.request, this);
		}
	}
};

var DomContentLoaded = new function() {
    var _now = [];
    var _del = [];

    this.now = function() {
        $WH.array_apply(_now, function(f) {
            f();
        });
    };

    this.delayed = function() {
        $WH.array_apply(_del, function(f) {
            f();
        });

        DomContentLoaded = null;
    };

    this.addEvent = function(f) {
        _now.push(f);
    };

    this.addDelayedEvent = function(f) {
        _del.push(f);
    }
};

function g_addCss(b) {
	var c = $WH.ce("style");
	c.type = "text/css";
	if (c.styleSheet) {
		c.styleSheet.cssText = b
	} else {
		$WH.ae(c, $WH.ct(b))
	}
	var a = $WH.gE(document, "head")[0];
	$WH.ae(a, c)
}
function g_setTextNodes(c, b) {
	if (c.nodeType == 3) {
		c.nodeValue = b
	} else {
		for (var a = 0; a < c.childNodes.length; ++a) {
			g_setTextNodes(c.childNodes[a], b)
		}
	}
}
function g_setInnerHtml(d, c, a) {
	if (d.nodeName.toLowerCase() == a) {
		d.innerHTML = c
	} else {
		for (var b = 0; b < d.childNodes.length; ++b) {
			g_setInnerHtml(d.childNodes[b], c, a)
		}
	}
}
function g_getTextContent(c) {
	var a = "";
	for (var b = 0; b < c.childNodes.length; ++b) {
		if (c.childNodes[b].nodeValue) {
			a += c.childNodes[b].nodeValue
		} else {
			if (c.childNodes[b].nodeName == "BR") {
				if ($WH.Browser.ie67) {
					a += "\r"
				} else {
					a += "\n"
				}
			}
		}
		a += g_getTextContent(c.childNodes[b])
	}
	return a
}

function g_pickerWheel(evt)
{
	evt = $WH.$E(evt);

	if (evt._wheelDelta < 0)
		this.scrollTop += 27;
	else
		this.scrollTop -= 27;
}

function g_setSelectedLink(c, b) {
	if (!g_setSelectedLink.groups) {
		g_setSelectedLink.groups = {}
	}
	var a = g_setSelectedLink.groups;
	if (a[b]) {
		a[b].className = a[b].className.replace("selected", "")
	}
	c.className += " selected";
	a[b] = c
}
function g_setCheckedRow(c, b) {
	if (!g_setCheckedRow.groups) {
		g_setCheckedRow.groups = {}
	}
	var a = g_setCheckedRow.groups;
	if (a[b]) {
		a[b].className = a[b].className.replace("checked", "")
	}
	c.className += " checked";
	a[b] = c
}
function g_toggleDisplay(a) {
	if (a.style.display == "none") {
		a.style.display = "";
		return true
	} else {
		a.style.display = "none";
		return false
	}
}
function g_enableScroll(a) {
	if (!a) {
		$WH.aE(document, "mousewheel", g_enableScroll.F);
		$WH.aE(window, "DOMMouseScroll", g_enableScroll.F)
	} else {
		$WH.dE(document, "mousewheel", g_enableScroll.F);
		$WH.dE(window, "DOMMouseScroll", g_enableScroll.F)
	}
}
g_enableScroll.F = function(a) {
	if (a.stopPropagation) {
		a.stopPropagation()
	}
	if (a.preventDefault) {
		a.preventDefault()
	}
	a.returnValue = false;
	a.cancelBubble = true;
	return false
};
function g_createRange(c, a) {
	range = {};
	for (var b = c; b <= a; ++b) {
		range[b] = b
	}
	return range
}
function g_sortIdArray(a, b, c) {
	a.sort(c ?
	function(e, d) {
		return $WH.strcmp(b[e][c], b[d][c])
	}: function(e, d) {
		return $WH.strcmp(b[e], b[d])
	})
}
function g_sortJsonArray(e, d, f, a) {
	var c = [];
	for (var b in e) {
		if (d[b] && (a == null || a(d[b]))) {
			c.push(b)
		}
	}
	if (f != null) {
		c.sort(f)
	} else {
		g_sortIdArray(c, d)
	}
	return c
}

function g_urlize(str, allowLocales, profile) {
	var ta = $WH.ce('textarea');
	ta.innerHTML = str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
	str = ta.value;

	str = $WH.str_replace(str, ' / ', '-');
	str = $WH.str_replace(str, "'", '');

	if (profile) {
		str = $WH.str_replace(str, '(', '');
		str = $WH.str_replace(str, ')', '');
		var accents = {
			"ß": "ss",
			"á": "a", "ä": "a", "à": "a", "â": "a",
			"è": "e", "ê": "e", "é": "e", "ë": "e",
			"í": "i", "î": "i", "ì": "i", "ï": "i",
			"ñ": "n",
			"ò": "o", "ó": "o", "ö": "o", "ô": "o",
			"ú": "u", "ü": "u", "û": "u", "ù": "u",
			"œ": "oe",
			"Á": "A", "Ä": "A", "À": "A", "Â": "A",
			"È": "E", "Ê": "E", "É": "E", "Ë": "E",
			"Í": "I", "Î": "I", "Ì": "I", "Ï": "I",
			"Ñ": "N",
			"Ò": "O", "Ó": "O", "Ö": "O", "Ô": "O",
			"Ú": "U", "Ü": "U", "Û": "U", "Ù": "U",
			"œ": "Oe"
		};
		for (var character in accents) {
			str = str.replace(new RegExp(character, "g"), accents[character]);
		}
	}

	str = $WH.trim(str);
	if (allowLocales) {
		str = $WH.str_replace(str, ' ', '-');
	}
	else {
		str = str.replace(/[^a-z0-9]/ig, '-');
	}

	str = $WH.str_replace(str, '--', '-');
	str = $WH.str_replace(str, '--', '-');
	str = $WH.rtrim(str, '-');
	str = str.replace(/[A-Z]/g, function(x) {
        return x.toLowerCase();
    });

	return str;
}

function g_createHeader(c) {
	var k = $WH.ce("dl"),
	p = (c == 5);
	for (var j = 0, l = mn_path.length; j < l; ++j) {
		var f = $WH.ce("dt");
		var q = $WH.ce("a");
		var m = $WH.ce("ins");
		var g = $WH.ce("big");
		var e = $WH.ce("span");
		var o = mn_path[j][0];
		var h = (o == c);
		var d = (!h && mn_path[j][3]);
		if (p && o == 5) {
			d = true;
			mn_path[j][3] = mn_profiles
		}
		if (d) {
            Menu.add(q, mn_path[j][3]);
		} else {
			q.onmouseover = Menu._hide
		}
		if (mn_path[j][2]) {
			q.href = mn_path[j][2]
		} else {
			q.href = "javascript:;";
			$WH.ns(q);
			q.style.cursor = "default"
		}
		if (h) {
			q.className = "selected"
		}
		$WH.ae(g, $WH.ct(mn_path[j][1].charAt(0)));
		$WH.ae(m, g);
		$WH.ae(m, $WH.ct(mn_path[j][1].substr(1)));
		$WH.ae(q, m);
		$WH.ae(q, e);
		$WH.ae(f, q);
		$WH.ae(k, f)
	}
	$WH.ae($WH.ge("toptabs-generic"), k);
    var b = $WH.ge("topbar-generic");
    if (c != null && c >= 0 && c < mn_path.length) {
        c = parseInt(c);
        switch (c) {
            case 0: // Database
                Menu.addButtons(b, [
                    [0, LANG.menu_browse, null, mn_database],         // Browse
                    Menu.findItem(mn_tools, [8]),                     // Utilities
                    Menu.findItem(mn_tools, [8, 4])                   // Random Page
                   ]);
                break;
            case 1: // Tools
                Menu.addButtons(b, [
                    [0, LANG.calculators, null, mn_tools.slice(0,4)], // Calculators
                    Menu.findItem(mn_tools, [1]),                     // Maps
                    Menu.findItem(mn_tools, [8]),                     // Utilities
                    Menu.findItem(mn_tools, [6]),                     // Guides
                ]);
                break;
            case 2:
                Menu.addButtons(b, Menu.explode(mn_more));
                break;
            case 5:
                pr_initTopBarSearch();
                break
        }
    }
    else {
        $WH.ae(b, $WH.ct(String.fromCharCode(160)));
	}
}
function g_updateHeader(a) {
	$WH.ee($WH.ge("toptabs-generic"));
	$WH.ee($WH.ge("topbar-generic"));
	g_createHeader(a)
}
function g_initHeader(a) {
	g_createHeader(a);
	var d = $WH.ge("livesearch-generic");
	var b = d.previousSibling;
	var c = d.parentNode;
	$WH.ns(b);
	b.onclick = function() {
		this.parentNode.onsubmit()
	};
	if ($WH.Browser.ie) {
		setTimeout(function() {
			d.value = ""
		},
		1)
	}
	if (d.value == "") {
		d.className = "search-database"
	}
	d.onmouseover = function() {
		if ($WH.trim(this.value) != "") {
			this.className = ""
		}
	};
	d.onfocus = function() {
		this.className = ""
	};
	d.onblur = function() {
		if ($WH.trim(this.value) == "") {
			this.className = "search-database";
			this.value = ""
		}
	};
	c.onsubmit = function() {
		var e = this.elements[0].value;
		if ($WH.trim(e) == "") {
			return false
		}
		this.submit()
	}
}
function g_initHeaderMenus() {
	var c = $WH.ge("toptabs-menu-user");
	if (c) {
		menu = [[0, LANG.userpage, "?user=" + g_user.name], [0, LANG.settings, "?account"], [0, LANG.signout, "?account=signout"]];
		if (location.href.match(new RegExp("/?user=" + g_user.name + "$", "i"))) {
			menu[0].checked = 1
		} else {
			if (location.href.indexOf("?account") != -1) {
				menu[1].checked = 1
			}
		}
        Menu.add(c, menu);
		c.href = "?user=" + g_user.name
	}
	c = $WH.ge("toptabs-menu-profiles");
	if (c) {
		c.menu = [];
		if (g_user.characters) {
			c.menu.push([, LANG.tab_characters]);
			for (var f = 0, b = g_user.characters.length; f < b; ++f) {
				var h = g_user.characters[f],
				e = [0, h.name + " (" + h.realmname + LANG.hyphen + h.region.toUpperCase() + ")", "?profile=" + h.region + "." + h.realm + "." + g_cleanCharacterName(h.name)];
				e.smallIcon = h.icon ? h.icon: "chr_" + g_file_races[h.race] + "_" + g_file_genders[h.gender] + "_" + g_file_classes[h.classs] + "0" + (h.level > 59 ? (Math.floor((h.level - 60) / 10) + 2) : 1);
				c.menu.push(e)
			}
		}
		c.menu.push([, LANG.tab_profiles]);
		if (g_user.profiles) {
			for (var f = 0, b = g_user.profiles.length; f < b; ++f) {
				var h = g_user.profiles[f],
				e = [0, h.name, "?profile=" + h.id];
				e.smallIcon = h.icon ? h.icon: "chr_" + g_file_races[h.race] + "_" + g_file_genders[h.gender] + "_" + g_file_classes[h.classs] + "0" + (h.level > 59 ? (Math.floor((h.level - 60) / 10) + 2) : 1);
				c.menu.push(e)
			}
		}
		var e = [0, "(" + LANG.button_new + ")", "?profile&new"];
		e.smallIcon = "inv_misc_questionmark";
		c.menu.push(e);
		c.menu.rightAligned = 1;
        Menu.add(c, c.menu);
        c.href = "?user=" + g_user.name + (g_user.profiles ? "#profiles": (g_user.characters ? "#characters": ""))
	}
	c = $WH.ge("toptabs-menu-language");
	if (c) {
		var g = "www",
		d = location.href,
		j = location.hostname.indexOf(".");
		if (j != -1 && j <= 5) {
			g = location.hostname.substr(0, j)
		}
		j = d.indexOf("#");
		if (j != -1) {
			d = d.substr(0, j)
		}
		//menu = [[0, "Deutsch", (g_locale.id != 3 ? d.replace(g, "de") : null)], [0, "English", (g_locale.id != 0 ? d.replace(g, "www") : null)], [0, "Espa" + String.fromCharCode(241) + "ol", (g_locale.id != 6 ? d.replace(g, "es") : null)], [0, "Fran" + String.fromCharCode(231) + "ais", (g_locale.id != 2 ? d.replace(g, "fr") : null)], [0, String.fromCharCode(1056, 1091, 1089, 1089, 1082, 1080, 1081), (g_locale.id != 8 ? d.replace(g, "ru") : null)]];

        var rel = d.match(/()\?((item|quest|spell|achievement|npc|object)=([0-9]+))/);
        rel = (rel && rel[2]) ? rel[2] : "";

		menu = [
			[0, "Deutsch", (g_locale.id != 3 ? "?locale=3" : null), , {rel: rel + " domain=de"}],
			[0, "English", (g_locale.id != 0 ? "?locale=0" : null), , {rel: rel + " domain=en"}],
			[0, "Espa" + String.fromCharCode(241) + "ol", (g_locale.id != 6 ? "?locale=6" : null), , {rel: rel + " domain=es"}],
			[0, "Fran" + String.fromCharCode(231) + "ais", (g_locale.id != 2 ? "?locale=2" : null), , {rel: rel + " domain=fr"}],
			[0, String.fromCharCode(1056, 1091, 1089, 1089, 1082, 1080, 1081), (g_locale.id != 8 ? "?locale=8" : null), , {rel: rel + " domain=ru"}]
        ];
		menu.rightAligned = 1;
		if (g_locale.id != 25) {
			menu[{
				0 : 1,
				2 : 3,
				3 : 0,
				6 : 2,
				8 : 4
			} [g_locale.id]].checked = 1
		}
        Menu.add(c, menu);
	}
}
function g_initPath(q, f) {
	var h = mn_path,
	c = null,
	k = null,
	p = 0,
	l = $WH.ge("main-precontents"),
	o = $WH.ce("div");
	$WH.ee(l);
	if (g_initPath.lastIt) {
		g_initPath.lastIt.checked = null
	}
	o.className = "path";
	if (f != null) {
		var m = $WH.ce("div");
		m.className = "path-right";
		var r = $WH.ce("a");
		r.href = "javascript:;";
		r.id = "fi_toggle";
		$WH.ns(r);
		r.onclick = fi_toggle;
		if (f) {
			r.className = "disclosure-on";
			$WH.ae(r, $WH.ct(LANG.fihide))
		} else {
			r.className = "disclosure-off";
			$WH.ae(r, $WH.ct(LANG.fishow))
		}
		$WH.ae(m, r);
		$WH.ae(l, m)
	}
	for (var g = 0; g < q.length; ++g) {
		var r, b, t = 0;
		for (var e = 0; e < h.length; ++e) {
			if (h[e][0] == q[g]) {
				t = 1;
				h = h[e];
				h.checked = 1;
				break
			}
		}
		if (!t) {
			p = 1;
			break
		}
		r = $WH.ce("a");
		b = $WH.ce("span");
		if (h[2]) {
			r.href = h[2]
		} else {
			r.href = "javascript:;";
			$WH.ns(r);
			r.style.textDecoration = "none";
			r.style.color = "white";
			r.style.cursor = "default"
		}
		if (g < q.length - 1 && h[3]) {
			b.className = "menuarrow"
		}
		//$WH.ae(r, $WH.ct(h[4] == null ? h[1] : h[4]));
		$WH.ae(r, $WH.ct(h[1]));
		if (g == 0) {
			Menu.add(r, mn_path);
		} else {
			Menu.add(r, c[3]);
		}

		$WH.ae(b, r);
		$WH.ae(o, b);
		k = b;
		c = h;
		h = h[3];
		if (!h) {
			p = 1;
			break
		}
	}
	if (p && k) {
		k.className = ""
	} else {
		if (c && c[3]) {
			k.className = "menuarrow";
			r = $WH.ce("a");
			b = $WH.ce("span");
			r.href = "javascript:;";
			$WH.ns(r);
			r.style.textDecoration = "none";
			r.style.paddingRight = "16px";
			r.style.color = "white";
			r.style.cursor = "default";
			$WH.ae(r, $WH.ct("..."));
            Menu.add(r, c[3]);
			$WH.ae(b, r);
			$WH.ae(o, b)
		}
	}
    // not really usefull
	// var m = $WH.ce("div");
	// m.className = "clear";
	// $WH.ae(o, m);
	$WH.ae(l, o);
	g_initPath.lastIt = c
}
function g_addTooltip(b, c, a) {
	if (!a && c.indexOf("<table>") == -1) {
		a = "q"
	}
	b.onmouseover = function(d) {
		$WH.Tooltip.showAtCursor(d, c, 0, 0, a)
	};
	b.onmousemove = $WH.Tooltip.cursorUpdate;
	b.onmouseout = $WH.Tooltip.hide
}
function g_addStaticTooltip(b, c, a) {
	if (!a && c.indexOf("<table>") == -1) {
		a = "q"
	}
	b.onmouseover = function(d) {
		$WH.Tooltip.show(b, c, 0, 0, a)
	};
	b.onmouseout = $WH.Tooltip.hide
}

function g_formatTimeElapsed(delay) {
    function OMG(value, unit, abbrv) {
        if (abbrv && LANG.timeunitsab[unit] == '') {
            abbrv = 0;
        }

        if (abbrv) {
            return value + ' ' + LANG.timeunitsab[unit];
        }
        else {
            return value + ' ' + (value == 1 ? LANG.timeunitssg[unit] : LANG.timeunitspl[unit]);
        }
    }

    var
        range   = [31557600, 2629800, 604800, 86400, 3600, 60, 1],
        subunit = [1, 3, 3, -1, 5, -1, -1];

    delay = Math.max(delay, 1);

    for (var i = 3, len = range.length; i < len; ++i) {
        if (delay >= range[i]) {
            var i1 = i;
            var v1 = Math.floor(delay / range[i1]);

            if (subunit[i1] != -1) {
                var i2 = subunit[i1];
                delay %= range[i1];

                var v2 = Math.floor(delay / range[i2]);

                if (v2 > 0) {
                    return OMG(v1, i1, 1) + ' ' + OMG(v2, i2, 1);
                }
            }

            return OMG(v1, i1, 0);
        }
    }

    return '(n/a)';
}

function g_GetStaffColorFromRoles(roles) {
    if (roles & U_GROUP_ADMIN) {
        return 'comment-blue';
    }
    if (roles & U_GROUP_GREEN_TEXT) { // Mod, Bureau, Dev
        return 'comment-green';
    }
    if (roles & U_GROUP_VIP) { // VIP
        return 'comment-gold';
    }

    return '';
}

function g_formatDate(sp, elapsed, theDate, time, alone) {
    var today = new Date();
    var event_day = new Date();
    event_day.setTime(today.getTime() - (1000 * elapsed));
    var txt;
    var event_day_midnight = new Date(event_day.getYear(), event_day.getMonth(), event_day.getDate());
    var today_midnight = new Date(today.getYear(), today.getMonth(), today.getDate());
    var delta = (today_midnight.getTime() - event_day_midnight.getTime());
    delta /= 1000;
    delta /= 86400;
    delta = Math.round(delta);

    if (elapsed >= 2592000) { /* More than a month ago */
        txt = LANG.date_on + g_formatDateSimple(theDate, time);
    }
    else if (delta > 1) {
        txt = $WH.sprintf(LANG.ddaysago, delta);

        if (sp) {
            var _ = new Date();
            _.setTime(theDate.getTime() + (g_localTime - g_serverTime));
            sp.className += ' tip';
            sp.title = _.toLocaleString();
        }
    }
    else if (elapsed >= 43200) {
        if (today.getDay() == event_day.getDay()) {
            txt = LANG.today;
        }
        else {
            txt = LANG.yesterday;
        }

        txt = g_formatTimeSimple(event_day, txt);

        if (sp) {
            var _ = new Date();
            _.setTime(theDate.getTime() + (g_localTime - g_serverTime));
            sp.className += ' tip';
            sp.title = _.toLocaleString();
        }
    }
    else { /* Less than 12 hours ago */
        var txt = $WH.sprintf(LANG.date_ago, g_formatTimeElapsed(elapsed));

        if (sp) {
            var _ = new Date();
            _.setTime(theDate.getTime() + (g_localTime - g_serverTime));
            sp.className += ' tip';
            sp.title = _.toLocaleString();
        }
    }

    if (alone == 1) {
        txt = txt.substr(0, 1).toUpperCase() + txt.substr(1);
    }

    if (sp) {
        $WH.ae(sp, $WH.ct(txt));
    }
    else {
        return txt;
    }
}

function g_formatDateSimple(d, time) {
    function __twoDigits(n) {
        return (n < 10 ? '0' + n : n);
    }

    var
        b     = "",
        day   = d.getDate(),
        month = d.getMonth() + 1,
        year  = d.getFullYear();

    if (year <= 1970) {
        b += LANG.unknowndate_stc;
    }
    else {
        b += $WH.sprintf(LANG.date_simple, __twoDigits(day), __twoDigits(month), year);
    }

    if (time != null) {
        b = g_formatTimeSimple(d, b);
    }

    return b;
}

function g_formatTimeSimple(d, txt, noPrefix) {
    function __twoDigits(n) {
        return (n < 10 ? '0' + n : n);
    }

    var
        hours   = d.getHours(),
        minutes = d.getMinutes();

    if (txt == null) {
        txt = '';
    }

    txt += (noPrefix ? ' ' : LANG.date_at);

    if (hours == 12) {
        txt += LANG.noon;
    }
    else if (hours == 0) {
        txt += LANG.midnight;
    }
    else if (hours > 12) {
        txt += (hours - 12) + ':' + __twoDigits(minutes) + ' ' + LANG.pm;
    }
    else {
        txt += hours + ':' + __twoDigits(minutes) + ' ' + LANG.am;
    }

    return txt;
}

function g_cleanCharacterName(e) {
	var d = "";
	for (var c = 0, a = e.length; c < a; ++c) {
		var b = e.charAt(c).toLowerCase();
		if (b >= "a" && b <= "z") {
			d += b
		} else {
			d += e.charAt(c)
		}
	}
	return d
}
function g_createGlow(a, h) {
	var e = $WH.ce("span");
	for (var c = -1; c <= 1; ++c) {
		for (var b = -1; b <= 1; ++b) {
			var g = $WH.ce("div");
			g.style.position = "absolute";
			g.style.whiteSpace = "nowrap";
			g.style.left = c + "px";
			g.style.top = b + "px";
			if (c == 0 && b == 0) {
				g.style.zIndex = 4
			} else {
				g.style.color = "black";
				g.style.zIndex = 2
			}
			g.innerHTML = a;
			$WH.ae(e, g)
		}
	}
	e.style.position = "relative";
	e.className = "glow" + (h != null ? " " + h: "");
	var f = $WH.ce("span");
	f.style.visibility = "hidden";
	$WH.ae(f, $WH.ct(a));
	$WH.ae(e, f);
	return e
}
function g_createProgressBar(c) {
	if (c == null) {
		c = {}
	}
	if (!c.text) {
		c.text = " "
	}
	if (c.color == null) {
		c.color = "rep0"
	}
	if (c.width == null || c.width > 100) {
		c.width = 100
	}
	var d, e;
	if (c.hoverText) {
		d = $WH.ce("a");
		d.href = "javascript:;"
	} else {
		d = $WH.ce("span")
	}
	d.className = "progressbar";
	if (c.text || c.hoverText) {
		e = $WH.ce("div");
		e.className = "progressbar-text";
		if (c.text) {
			var a = $WH.ce("del");
			$WH.ae(a, $WH.ct(c.text));
			$WH.ae(e, a)
		}
		if (c.hoverText) {
			var b = $WH.ce("ins");
			$WH.ae(b, $WH.ct(c.hoverText));
			$WH.ae(e, b)
		}
		$WH.ae(d, e)
	}
	e = $WH.ce("div");
	e.className = "progressbar-" + c.color;
	e.style.width = c.width + "%";
	$WH.ae(e, $WH.ct(String.fromCharCode(160)));
	$WH.ae(d, e);
	return d
}
function g_createReputationBar(g) {
	var f = g_createReputationBar.P;
	if (!g) {
		g = 0
	}
	g += 42000;
	if (g < 0) {
		g = 0
	} else {
		if (g > 84999) {
			g = 84999
		}
	}
	var e = g,
	h, b = 0;
	for (var d = 0, a = f.length; d < a; ++d) {
		if (f[d] > e) {
			break
		}
		if (d < a - 1) {
			e -= f[d];
			b = d + 1
		}
	}
	h = f[b];
	var c = {
		text: g_reputation_standings[b],
		hoverText: e + " / " + h,
		color: "rep" + b,
		width: parseInt(e / h * 100)
	};
	return g_createProgressBar(c)
}
g_createReputationBar.P = [36000, 3000, 3000, 3000, 6000, 12000, 21000, 999];
function g_createAchievementBar(b, d, a) {
	if (!b) {
		b = 0
	}
	var c = {
		text: b + (d > 0 ? " / " + d: ""),
		color: (a ? "rep7": "ach" + (d > 0 ? 0 : 1)),
		width: (d > 0 ? parseInt(b / d * 100) : 100)
	};
	return g_createProgressBar(c)
}

function g_getMoneyHtml(c) {
	var b = 0,
	a = "";
	if (c >= 10000) {
		b = 1;
		a += '<span class="moneygold">' + Math.floor(c / 10000) + "</span>";
		c %= 10000
	}
	if (c >= 100) {
		if (b) {
			a += " "
		} else {
			b = 1
		}
		a += '<span class="moneysilver">' + Math.floor(c / 100) + "</span>";
		c %= 100
	}
	if (c >= 1) {
		if (b) {
			a += " "
		} else {
			b = 1
		}
		a += '<span class="moneycopper">' + c + "</span>"
	}
	return a
}
function g_getMoneyHtml2(f, c, b, a) {
	var e = g_getMoneyHtml(f);
	if (c !== undefined && c !== null && c != 0) {
		if (e.length > 0) {
			e += " "
		}
		e += '<span class="money' + (c < 0 ? "horde": "alliance") + ' tip" onmouseover="Listview.funcBox.moneyHonorOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">' + g_numberFormat(Math.abs(c)) + "</span>"
	}
	if (b !== undefined && b !== null && b > 0) {
		if (e.length > 0) {
			e += " "
		}
		e += '<span class="moneyarena tip" onmouseover="Listview.funcBox.moneyArenaOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">' + g_numberFormat(b) + "</span>"
	}
	if (a !== undefined && a !== null && a.length > 0) {
		for (var d = 0; d < a.length; ++d) {
			if (e.length > 0) {
				e += " "
			}
			var h = a[d][0];
			var g = a[d][1];
			e += '<a href="?item=' + h + '" class="moneyitem" style="background-image: url(images/icons/tiny/' + (g_items[h] && g_items[h]["icon"] ? g_items[h]["icon"] : "inv_misc_questionmark").toLowerCase() + '.gif)">' + g + "</a>"
		}
	}
	return e
}
function g_numberFormat(f, b, l, h) {
	var c = f,
	a = b;
	var e = function(r, q) {
		var i = Math.pow(10, q);
		return (Math.round(r * i) / i).toString()
	};
	c = !isFinite( + c) ? 0 : +c;
	a = !isFinite( + a) ? 0 : Math.abs(a);
	var p = (typeof h === "undefined") ? ",": h;
	var d = (typeof l === "undefined") ? ".": l;
	var o = (a > 0) ? e(c, a) : e(Math.round(c), a);
	var m = e(Math.abs(c), a);
	var k, g;
	if (m >= 1000) {
		k = m.split(/\D/);
		g = k[0].length % 3 || 3;
		k[0] = o.slice(0, g + (c < 0)) + k[0].slice(g).replace(/(\d{3})/g, p + "$1");
		o = k.join(d)
	} else {
		o = o.replace(".", d)
	}
	var j = o.indexOf(d);
	if (a >= 1 && j !== -1 && (o.length - j - 1) < a) {
		o += new Array(a - (o.length - j - 1)).join(0) + "0"
	} else {
		if (a >= 1 && j === -1) {
			o += d + new Array(a).join(0) + "0"
		}
	}
	return o
}
function g_expandSite() {
	$WH.ge("wrapper").className = "nosidebar";
	var a = $WH.ge("topbar-expand");
	if (a) {
		$WH.de(a)
	}
	a = $WH.ge("sidebar");
	if (a) {
		$WH.de(a)
	}
}
function g_insertTag(d, a, i, j) {
	var b = $(d);
	b.focus();
	if (b.selectionStart != null) {
		var l = b.selectionStart,
		h = b.selectionEnd,
		k = b.scrollLeft,
		c = b.scrollTop;
		var g = b.value.substring(l, h);
		if (typeof j == "function") {
			g = j(g)
		}
		b.value = b.value.substr(0, l) + a + g + i + b.value.substr(h);
		b.selectionStart = b.selectionEnd = h + a.length;
		b.scrollLeft = k;
		b.scrollTop = c
	} else {
		if (document.selection && document.selection.createRange) {
			var f = document.selection.createRange();
			if (f.parentElement() != b) {
				return
			}
			var g = f.text;
			if (typeof j == "function") {
				g = j(g)
			}
			f.text = a + g + i
		}
	}
	if (b.onkeyup) {
		b.onkeyup()
	}
}

function g_isDateValid(date) {
	var match = /^(20[0-2]\d)-([01]\d)-([0-3]\d) ([0-2]\d):([0-5]\d):([0-5]\d)$/.exec(date);
	return match;
}

function g_isIpAddress(str) {
	return /[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/.test(str);
}

function g_isEmailValid(email) {
	return email.match(/^([a-z0-9._-]+)(\+[a-z0-9._-]+)?(@[a-z0-9.-]+\.[a-z]{2,4})$/i) != null;
}

function g_getCurrentDomain() {
	if (g_getCurrentDomain.CACHE) {
        return g_getCurrentDomain.CACHE;
	}

	var hostname = location.hostname;

	if (!g_isIpAddress(hostname)) {
		// Only keep the last 2 parts
		var parts = hostname.split('.');
		if(parts.length > 2) {
			parts.splice(0, parts.length - 2);
		}
		hostname = parts.join('.');
	}

	g_getCurrentDomain.CACHE = hostname;

	return hostname;
}

function g_onAfterTyping(a, d, c) {
	var e;
	var b = function() {
		if (e) {
			clearTimeout(e);
			e = null
		}
		e = setTimeout(d, c)
	};
	a.onkeyup = b
}
function g_onClick(c, d) {
	var b = 0;
	function a(e) {
		if (b) {
			if (b != e) {
				return
			}
		} else {
			b = e
		}
		d(true)
	}
	c.oncontextmenu = function() {
		a(1);
		return false
	};
	c.onmouseup = function(f) {
		f = $WH.$E(f);
		if (f._button == 3 || f.shiftKey || f.ctrlKey) {
			a(2)
		} else {
			if (f._button == 1) {
				d(false)
			}
		}
		return false
	}
}
function g_isLeftClick(a) {
	a = $WH.$E(a);
	return (a && a._button == 1)
}

function g_isExternalUrl(url) {
	if(!url) {
        return false;
	}

	if (url.indexOf('http') != 0 && url.indexOf('//') != 0) {
		return false;
	}
	else if (url.indexOf(g_getCurrentDomain()) != -1) {
		return false;
	}

	return true;
}

function g_createOrRegex(search, negativeGroup) {
	search = search.replace(/(\(|\)|\|\+|\*|\?|\$|\^)/g, '\\$1');
	var
        parts = search.split(' '),
        strRegex= '';

    for (var j = 0, len = parts.length; j < len; ++j) {
		if (j > 0) {
			strRegex += '|';
		}
		strRegex += parts[j];
	}

	// The additional group is necessary so we dont replace %s
	return new RegExp((negativeGroup != null ? '(' + negativeGroup + ')?' : '') + '(' + strRegex + ')', 'gi');
}

function g_getHash() {
	return '#' + decodeURIComponent(location.href.split('#')[1] || '');
}

// Lets you add/remove/edit the query parameters in the passed URL
function g_modifyUrl(url, params, opt) {
	if (!opt) {
        opt = $.noop;
	}

  	// Preserve existing hash
	var hash = '';
	if (url.match(/(#.+)$/)) {
		hash = RegExp.$1;
		url = url.replace(hash, '');
	}

	$.each(params, function(paramName, newValue) {
		var needle;
		var paramPrefix;
		var paramValue;

		var matches = url.match(new RegExp('(&|\\?)?' + paramName + '=?([^&]+)?'));
		if (matches != null) {
			needle      = matches[0];
			paramPrefix = matches[1];
			paramValue  = decodeURIComponent(matches[2]);
		}

		// Remove
		if (newValue == null) {
			if (!needle) {
                return; // If param wasn't there, no need to remove anything
			}
			paramValue = null;
		}
		// Append
		else if (newValue.substr(0, 2) == '+=') {
			if (paramValue && opt.onAppendCollision) {
 				paramValue = opt.onAppendCollision(paramValue, newValue.substr(2), opt.menuUrl);
			}
			else if (!paramValue && opt.onAppendEmpty) {
 				paramValue = opt.onAppendEmpty(newValue.substr(2), opt.menuUrl);
			}
			else {
				if (!paramValue) {
                    paramValue = '';
				}
				paramValue += $.trim(newValue.substr(2));
			}
		}
		// Set
		else {
			paramValue = newValue;
		}

		// Replace existing param
		if (needle) {
			var replacement = '';
			if (paramPrefix) { // Preserve existing prefix
				replacement += paramPrefix;
			}
			if (paramValue != null) {
				replacement += paramName;
				if (paramValue) {
					replacement += '=' + $WH.urlencode2(paramValue);
				}
			}

			url = url.replace(needle, replacement);
		}
		// Add new param
		else if (paramValue || newValue == null || newValue.substr(0,2) != '+=') {
			url += (url.indexOf('?') == -1 ? '?' : '&') + paramName;
			if (paramValue) {
				url += '=' + $WH.urlencode2(paramValue);
			}
		}
	});

	// Polish
	url = url.replace('?&', '?');
	url = url.replace(/&&/g, '&');
	url = url.replace(/\/\?/g, '/');
	url = url.replace(/(&|\?)+$/, ''); // Remove trailing & and ? characters

	return url + hash;
}

DomContentLoaded.addEvent(function () {
    $WH.array_apply($WH.gE(document, 'dfn'), function(x){
        var text = x.title;
        x.title = '';
        x.className += ' tip';

        if (text.indexOf('LANG.') == 0) {                   // custom for less redundant texts
            text = eval(text);
        }

        g_addTooltip(x, text, 'q');
    });

// if i understand this right, this code binds an onCopy eventHandler to every child node of class="text"-nodes with the attribute unselectable="on"
// causing the text to disappear for 1ms, causing the empty node to be copied ...  w/e, i'm not going to use this
/*
    $('.text').bind('copy', function() {
        $('*[unselectable]', this).each(function(i, v) {
            var txt = $(v).text();
            $(v).text('');
            setTimeout(function() { $(v).text(txt) }, 1);
        });
    });
*/
});

function g_GetExpansionClassName(expansion) {
	switch (expansion) {
		case 0:
			return null;
		case 1:
			return "bc-icon-right";
		case 2:
			return "wotlk-icon-right";
	}

	return null;
}

function g_addPages(l, b) {
	function p(r, d) {
		var i;
		if (r == b.page) {
			i = $WH.ce("span");
			i.className = "selected"
		} else {
			i = $WH.ce("a");
			i.href = (r > 1 ? b.url + b.sep + r + b.pound: b.url + b.pound)
		}
		$WH.ae(i, $WH.ct(d != null ? d: r));
		return i
	}
	if (!b.pound) {
		b.pound = ""
	}
	if (!b.sep) {
		b.sep = "."
	}
	if (b.allOrNothing && b.nPages <= 1) {
		return
	}
	var c = (b.align && b.align == "left");
	var e = $WH.ce("div"),
	k,
	q = $WH.ce("var");
	e.className = "pages";
	if (c) {
		e.className += " pages-left"
	}
	if (b.nPages > 1) {
		k = $WH.ce("div");
		k.className = "pages-numbers";
		var o = Math.max(2, b.page - 3);
		var h = Math.min(b.nPages - 1, b.page + 3);
		var m = [];
		if (b.page != b.nPages) {
			m.push(p(b.page + 1, LANG.lvpage_next + String.fromCharCode(8250)))
		}
		m.push(p(b.nPages));
		if (h < b.nPages - 1) {
			var a = $WH.ce("span");
			$WH.ae(a, $WH.ct("..."));
			m.push(a)
		}
		for (var g = h; g >= o; --g) {
			m.push(p(g))
		}
		if (o > 2) {
			var a = $WH.ce("span");
			$WH.ae(a, $WH.ct("..."));
			m.push(a)
		}
		m.push(p(1));
		if (b.page != 1) {
			m.push(p(b.page - 1, String.fromCharCode(8249) + LANG.lvpage_previous))
		}
		if (c) {
			m.reverse()
		}
		for (var g = 0, j = m.length; g < j; ++g) {
			$WH.ae(k, m[g])
		}
		k.firstChild.style.marginRight = "0";
		k.lastChild.style.marginLeft = "0"
	}
	var q = $WH.ce("var");
	$WH.ae(q, $WH.ct($WH.sprintf(LANG[b.wording[b.nItems == 1 ? 0 : 1]], b.nItems)));
	if (b.nPages > 1) {
		var a = $WH.ce("span");
		$WH.ae(a, $WH.ct(String.fromCharCode(8211)));
		$WH.ae(q, a);
		var f = $WH.ce("a");
		f.className = "gotopage";
		f.href = "javascript:;";
		$WH.ns(f);
		if ($WH.Browser.ie) {
			$WH.ae(f, $WH.ct(" "))
		}
		f.onclick = function() {
			var d = prompt($WH.sprintf(LANG.prompt_gotopage, 1, b.nPages), b.page);
			if (d != null) {
				d |= 0;
				if (d != b.page && d >= 1 && d <= b.nPages) {
					document.location.href = (d > 1 ? b.url + b.sep + d + b.pound: b.url + b.pound)
				}
			}
		};
		f.onmouseover = function(d) {
			$WH.Tooltip.showAtCursor(d, LANG.tooltip_gotopage, 0, 0, "q")
		};
		f.onmousemove = $WH.Tooltip.cursorUpdate;
		f.onmouseout = $WH.Tooltip.hide;
		$WH.ae(q, f)
	}
	if (c) {
		$WH.ae(e, q);
		if (k) {
			$WH.ae(e, k)
		}
	} else {
		if (k) {
			$WH.ae(e, k)
		}
		$WH.ae(e, q)
	}
	$WH.ae(l, e)
}
function g_disclose(a, b) {
	b.className = "disclosure-" + (g_toggleDisplay(a) ? "on": "off");
	return false
}
function co_addYourComment() {
	tabsContribute.focus(0);
	var ta = $WH.gE(document.forms['addcomment'], "textarea")[0];
	ta.focus()
}
function co_cancelReply() {
	$WH.ge("replybox-generic").style.display = "none";
	document.forms.addcomment.elements.replyto.value = ""
}
function co_validateForm(f) {
	var ta = $WH.gE(f, "textarea")[0];

    if (g_user.permissions & 1) {
        return true;
    }

    if (Listview.funcBox.coValidate(ta)) {
        return true;
	}
	return false
}
function ss_submitAScreenshot() {
	tabsContribute.focus(1)
}
function ss_validateForm(f) {
	if (!f.elements.screenshotfile.value.length) {
		alert(LANG.message_noscreenshot);
		return false;
	}
	return true;
}
function ss_appendSticky() {
	var _ = $WH.ge("infobox-sticky-ss");
	var type = g_pageInfo.type;
	var typeId = g_pageInfo.typeId;
	var pos = $WH.in_array(lv_screenshots, 1, function(a) {
		return a.sticky;
	});

	if (pos != -1) {
		var screenshot = lv_screenshots[pos];

		var a = $WH.ce("a");
		a.href = "#screenshots:id=" + screenshot.id;
		a.onclick = function(a) {
			ScreenshotViewer.show({
				screenshots: lv_screenshots,
				pos: pos
			});
			return $WH.rf2(a);
		};

		var size = (lv_videos && lv_videos.length ? [120, 90] : [150, 150]);
		var
            img = $WH.ce("img"),
            scale = Math.min(size[0] / screenshot.width, size[1] / screenshot.height);

		img.src    = g_staticUrl + "/uploads/screenshots/thumb/" + screenshot.id + ".jpg";
		img.width  = Math.round(scale * screenshot.width);
		img.height = Math.round(scale * screenshot.height);
		img.className = "border";
		$WH.ae(a, img);
		$WH.ae(_, a);

		var th = $WH.ge('infobox-screenshots');
        var a = $WH.ce("a");
        $WH.ae(a, $WH.ct(th.innerText + " (" + lv_screenshots.length + ")"));
        a.href = "#screenshots"
        a.title = $WH.sprintf(LANG.infobox_showall, lv_screenshots.length);
		a.onclick = function() {
			tabsRelated.focus((lv_videos && lv_videos.length) || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)) ? -2 : -1);
			return false;
		};
        $WH.ee(th);
        $WH.ae(th, a);
	}
    else {
		var a;
		if (g_user.id > 0) {
			a = '<a href="javascript:;" onclick="ss_submitAScreenshot(); return false">';
		}
        else {
			a = '<a href="?account=signin">';
		}
		_.innerHTML = $WH.sprintf(LANG.infobox_noneyet, a + LANG.infobox_submitone + "</a>")
	}
}
var vi_thumbnails = {
	1 : "http://i3.ytimg.com/vi/$1/default.jpg"
};
var vi_siteurls = {
	1 : "http://www.youtube.com/watch?v=$1"
};
var vi_sitevalidation = {
	1 : /^http:\/\/www\.youtube\.com\/watch\?v=([^& ]{11})/
};
function vi_submitAVideo() {
	tabsContribute.focus(2)
}
function vi_validateForm(f) {
	if (!f.elements['videourl'].value.length) {
		alert(LANG.message_novideo);
		return false;
	}

	var urlmatch = false;
	for (var i in vi_sitevalidation) {
		if (f.elements['videourl'].value.match(vi_sitevalidation[i])) {
			urlmatch = true;
			break
		}
	}

	if (!urlmatch) {
		alert(LANG.message_novideo);
		return false;
	}
	return true;
}
function vi_appendSticky() {
	var _ = $WH.ge("infobox-sticky-vi");
	var type = g_pageInfo.type;
	var typeId = g_pageInfo.typeId;
	var pos = $WH.in_array(lv_videos, 1, function(a) {
		return a.sticky
	});

	if (pos != -1) {
		var video = lv_videos[pos];

		var a = $WH.ce("a");
		a.href = "#videos:id=" + video.id;
		a.onclick = function(e) {
			VideoViewer.show({
				videos: lv_videos,
				pos: pos
			});
			return $WH.rf2(e)
		};

		var img = $WH.ce("img");
		img.src = $WH.sprintf(vi_thumbnails[video.videoType], video.videoId);
		img.className = "border";
		$WH.ae(a, img);
		$WH.ae(_, a);

		var th = $WH.ge('infobox-videos');
        var a = $WH.ce("a");
        $WH.ae(a, $WH.ct(th.innerText + " (" + lv_videos.length + ")"));
        a.href = "#videos"
        a.title = $WH.sprintf(LANG.infobox_showall, lv_videos.length);
		a.onclick = function() {
			tabsRelated.focus(-1);
			return false;
		};
        $WH.ee(th);
        $WH.ae(th, a);
	}
    else {
        var a;
        if (g_user.id > 0) {
            a = '<a href="javascript:;" onclick="vi_submitAVideo(); return false">'
        }
        else {
            a = '<a href="?account=signin">'
        }

        _.innerHTML = $WH.sprintf(LANG.infobox_noneyet, a + LANG.infobox_suggestone + "</a>")
	}
}
var g_videos = [];
var VideoViewer = new function() {
	var
        videos,
        pos,
        imgWidth,
        imgHeight,
        scale,
        oldHash,
        mode = 0,
        collectionId,
        pageTitle, // IE flash embed fix
        container,
        screen,
        imgDiv,
        aPrev,
        aNext,
        aCover,
        aOriginal,
        divFrom,
        divCaption;

	function computeDimensions() {
		var video = videos[pos];

		var
            captionExtraHeight = Math.max(divCaption.offsetHeight - 18, 0),
            availHeight = Math.max(50, Math.min(520, $WH.g_getWindowSize().h - 72 - captionExtraHeight)),
            scale = Math.min(1, availHeight / 520);

		imgWidth = Math.round(scale * 880);
		imgHeight = Math.round(scale * 520);

		aPrev.style.height = aNext.style.height = aCover.style.height = (imgHeight - 95) + 'px';
		Lightbox.setSize(Math.max(480, imgWidth) + 20, imgHeight + 52 + captionExtraHeight);
	}

	function getPound(pos) {
		var
            video = videos[pos],
            buff = '#videos:';

        if (mode == 0) {
			buff += 'id=' + video.id;
		}
        else {
			buff += collectionId + ':' + (pos + 1);
		}

		return buff;
	}

	function render(resizing) {
		if (resizing && (scale == 1) && $WH.g_getWindowSize().h > container.offsetHeight) {
			return;
		}

		container.style.visibility = 'hidden';

		var video = videos[pos];

		computeDimensions();

		if (!resizing) {
			if (video.videoType == 1) {
				imgDiv.innerHTML = Markup.toHtml('[youtube=' + video.videoId + ' width=' + imgWidth + ' height=' + imgHeight + ' autoplay=true]', {mode:Markup.MODE_ARTICLE});
			}

			aOriginal.href = $WH.sprintf(vi_siteurls[video.videoType], video.videoId);

			if (!video.user && typeof g_pageInfo == 'object') {
				video.user = g_pageInfo.username;
			}

			var
                hasFrom1 = (video.date && video.user),
                hasFrom2 = (videos.length > 1);

			if (hasFrom1) {
				var
                    postedOn = new Date(video.date),
                    elapsed = (g_serverTime - postedOn) / 1000;

				var a = divFrom.firstChild.childNodes[1];
				a.href = '?user=' + video.user;
				a.innerHTML = video.user;

				var s = divFrom.firstChild.childNodes[3];
				$WH.ee(s);
				Listview.funcBox.coFormatDate(s, elapsed, postedOn);
				divFrom.firstChild.style.display = '';
			}
            else {
				divFrom.firstChild.style.display = 'none';
			}

			var s = divFrom.childNodes[1];
			$WH.ee(s);

			if (video.user)
			{
				if (hasFrom1) {
					$WH.ae(s, $WH.ct(' ' + LANG.dash + ' '));
                }
				var a = $WH.ce('a');
				a.href = 'javascript:;';
				a.onclick = ContactTool.show.bind(ContactTool, { mode: 5, video: video });
				a.className = 'icon-report';
				g_addTooltip(a, LANG.report_tooltip, 'q2');
				$WH.ae(a, $WH.ct(LANG.report));
				$WH.ae(s, a);
			}

			s = divFrom.childNodes[2];

			if (hasFrom2) {
				var buff = '';
				if (video.user) {
					buff = LANG.dash;
				}
				buff += (pos + 1) + LANG.lvpage_of + videos.length;

				s.innerHTML = buff;
				s.style.display = '';
			}
            else {
				s.style.display = 'none';
			}

			divFrom.style.display = (hasFrom1 || hasFrom2 ? '': 'none');

			var hasCaption = (video.caption != null && video.caption.length);
			var hasSubject = (video.subject != null && video.subject.length && video.type && video.typeId);

			if (hasCaption || hasSubject) {
				var html = '';

				if (hasSubject) {
					html += LANG.types[video.type][0] + LANG.colon;
					html += '<a href="?' + g_types[video.type] + '=' + video.typeId + '">';
					html += video.subject;
					html += '</a>';
				}

				if (hasCaption) {
					if (hasSubject) {
						html += LANG.dash;
					}

					html += (video.noMarkup ? video.caption: Markup.toHtml(video.caption, {
						mode: Markup.MODE_SIGNATURE
					}));
				}

				divCaption.innerHTML = html;
				divCaption.style.display = '';
			}
            else {
				divCaption.style.display = 'none';
			}

			if (videos.length > 1) {
				aPrev.href = getPound(peekPos(-1));
				aNext.href = getPound(peekPos(1));
				aPrev.style.display = aNext.style.display = '';
				aCover.style.display = 'none';
			}
            else {
				aPrev.style.display = aNext.style.display = 'none';
				aCover.style.display = '';
			}

			location.replace(getPound(pos));
		}

		Lightbox.reveal();

		container.style.visibility = 'visible';

		setTimeout(fixTitle, 1);
	}

	function peekPos(change) {
		var foo = pos;
		foo += change;

		if (foo < 0) {
			foo = videos.length - 1;
		}
        else if (foo >= videos.length) {
            foo = 0;
		}

		return foo;
	}

	function prevVideo() {
		pos = peekPos(-1);
		render();

		return false;
	}

	function nextVideo() {
		pos = peekPos(1);
		render();

		return false;
	}

	function fixTitle() {
		if (pageTitle) {
			document.title = pageTitle;
		}
	}

	function onKeyUp(e) {
		e = $WH.$E(e);

		switch (e.keyCode) {
		case 37: // Left
			prevVideo();
			break;
		case 39: // Right
			nextVideo();
			break;
		}
	}

	function onResize() {
		render(1);
	}

	function onHide() {
		$WH.ee(imgDiv);

		if (videos.length > 1) {
			$WH.dE(document, 'keyup', onKeyUp) ;
		}

		if (oldHash && mode == 0) {
			if (oldHash.indexOf(':id=') != -1) {
				oldHash = '#videos';
			}
			location.replace(oldHash);
		}
        else {
			location.replace('#.');
		}

		fixTitle();
	}

	function onShow(dest, first, opt) {
		if (typeof opt.videos == 'string') {
			videos = g_videos[opt.videos];
			mode = 1;
			collectionId = opt.videos;
		}
        else {
			videos = opt.videos;
			mode = 0;
			collectionId = null;
		}
		container = dest;

		pos = 0;
		if (opt.pos && opt.pos >= 0 && opt.pos < videos.length) {
			pos = opt.pos;
		}

		if (first) {
			dest.className = 'screenshotviewer';
			screen = $WH.ce('div');
			screen.className = 'screenshotviewer-screen';

			aPrev = $WH.ce('a');
			aNext = $WH.ce('a');
			aPrev.className = 'screenshotviewer-prev';
			aNext.className = 'screenshotviewer-next';
			aPrev.href = 'javascript:;';
			aNext.href = 'javascript:;';

			var foo = $WH.ce('span');
			var b = $WH.ce('b');
			// $WH.ae(b, $WH.ct(LANG.previous));
			$WH.ae(foo, b);
			$WH.ae(aPrev, foo);
			var foo = $WH.ce('span');
			var b = $WH.ce('b');
			// $WH.ae(b, $WH.ct(LANG.next));
			$WH.ae(foo, b);
			$WH.ae(aNext, foo);

			aPrev.onclick = prevVideo;
			aNext.onclick = nextVideo;

			aCover = $WH.ce('a');
			aCover.className = 'screenshotviewer-cover';
			aCover.href = 'javascript:;';
			aCover.onclick = Lightbox.hide;
			var foo = $WH.ce('span');
			var b = $WH.ce('b');
			$WH.ae(b, $WH.ct(LANG.close));
			$WH.ae(foo, b);
			$WH.ae(aCover, foo);

			$WH.ae(screen, aPrev);
			$WH.ae(screen, aNext);
			$WH.ae(screen, aCover);

			imgDiv = $WH.ce('div');
			$WH.ae(screen, imgDiv);

			$WH.ae(dest, screen);

			var aClose = $WH.ce('a');
			// aClose.className = 'dialog-x';
			aClose.className = 'screenshotviewer-close';
			aClose.href = 'javascript:;';
			aClose.onclick = Lightbox.hide;
			// $WH.ae(aClose, $WH.ct(LANG.close));
            $WH.ae(aClose, $WH.ce('span'));
			$WH.ae(dest, aClose);

			aOriginal = $WH.ce('a');
			// aOriginal.className = 'dialog-arrow';
			aOriginal.className = 'screenshotviewer-original';
			aOriginal.href = 'javascript:;';
			aOriginal.target = '_blank';
			// $WH.ae(aOriginal, $WH.ct(LANG.original));
            $WH.ae(aOriginal, $WH.ce('span'));
			$WH.ae(dest, aOriginal);

			divFrom = $WH.ce('div');
			divFrom.className = 'screenshotviewer-from';
			var sp = $WH.ce('span');
			$WH.ae(sp, $WH.ct(LANG.lvscreenshot_from));
			$WH.ae(sp, $WH.ce('a'));
			$WH.ae(sp, $WH.ct(' '));
			$WH.ae(sp, $WH.ce('span'));
			$WH.ae(divFrom, sp);
			$WH.ae(divFrom, $WH.ce('span'));
			$WH.ae(divFrom, $WH.ce('span'));
			$WH.ae(dest, divFrom);

			divCaption = $WH.ce('div');
			divCaption.className = 'screenshotviewer-caption';
			$WH.ae(dest, divCaption);

			var d = $WH.ce('div');
			d.className = 'clear';
			$WH.ae(dest, d);
		}

		oldHash = location.hash;

		if (videos.length > 1) {
			$WH.aE(document, 'keyup', onKeyUp);
		}
		render();
	}

	this.checkPound = function() {
		pageTitle = $WH.gE(document, 'title').innerHTML;
		if (location.hash && location.hash.indexOf('#videos') == 0) {
			if (!g_listviews['videos']) { // Standalone video viewer
				var parts = location.hash.split(':');
				if (parts.length == 3) {
					var collection = g_videos[parts[1]],
					p = parseInt(parts[2]);

					if (collection && p >= 1 && p <= collection.length) {
						VideoViewer.show({
							videos: parts[1],
							pos: p - 1
						});
					}
				}
			}
		}
	}

	this.show = function(opt) {
		Lightbox.show('videoviewer', {
			onShow: onShow,
			onHide: onHide,
			onResize: onResize
		},opt);
		return false;
	}

	DomContentLoaded.addEvent(this.checkPound)
};

var Slider = new function() {
    var
        start,
        handleObj,
        timer;

    function onMouseDown(e) {
        e = $WH.$E(e);

        handleObj = this;
        start     = $WH.g_getCursorPos(e);

        $WH.aE(document, 'mousemove', onMouseMove);
        $WH.aE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onMouseMove(e) {
        e = $WH.$E(e);

        if (!start || !handleObj) {
            return;
        }

        var
            cursor  = $WH.g_getCursorPos(e),
            delta   = cursor[handleObj._dir] - start[handleObj._dir],
            outside = setPosition(handleObj, handleObj._pos + delta),
            current = getCurrentPosition(handleObj);

        if (current && handleObj.input) {
            handleObj.input.value = current.value;
        }

        if (!outside) {
            start = cursor;
        }

        if (handleObj.onMove) {
            handleObj.onMove(e, handleObj, current);
        }
    }

    function onMouseUp(e) {
        e = $WH.$E(e);

        if (handleObj.onMove) {
            handleObj.onMove(e, handleObj, getCurrentPosition(handleObj));
        }

        handleObj = null;
        start     = null;

        $WH.dE(document, 'mousemove', onMouseMove);
        $WH.dE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onClick(obj, e) {
        e = $WH.$E(e);

        handleObj = obj;
        start     = $WH.g_getCursorPos(e);

        var
            offset = $WH.ac(handleObj.parentNode),
            center = Math.floor(getHandleWidth(handleObj) / 2);

        setPosition(handleObj, start[handleObj._dir] - offset[handleObj._dir] - center);

        var current = getCurrentPosition(handleObj);

        if (current && handleObj.input) {
            handleObj.input.value = current.value;
        }

        if (handleObj.onMove) {
            handleObj.onMove(e, handleObj, current);
        }

        $WH.aE(document, 'mousemove', onMouseMove);
        $WH.aE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onKeyPress(obj, e) {
        if (timer) {
            clearTimeout(timer);
        }

        if (e.type == 'change' || e.type == 'keypress' && e.which == 13) {
            onInput(obj, e);
        }
        else {
            timer = setTimeout(onInput.bind(0, obj, e), 1000);
        }
    }

    function onInput(obj, e) {
        var
            value   = obj.input.value,
            current = getCurrentPosition(obj);

        if (isNaN(value)) {
            value = current.value;
        }
        if (value > obj._max) {
            value = obj._max;
        }
        if (value < obj._min) {
            value = obj._min;
        }

        Slider.setValue(obj, value);

        if (obj.onMove) {
            obj.onMove(e, obj, getCurrentPosition(obj));
        }
    }

    function setPosition(obj, offset) {
        var outside = false;

        if (offset < 0) {
            offset  = 0;
            outside = true;
        }
        else if (offset > getMaxPosition(obj)) {
            offset  = getMaxPosition(obj);
            outside = true;
        }

        obj.style[(obj._dir == 'y' ? 'top' : 'left')] = offset + 'px';
        obj._pos = offset;

        return outside;
    }

    function getMaxPosition(obj) {
        return getTrackerWidth(obj) - getHandleWidth(obj) + 2;
    }

    function getCurrentPosition(obj) {
        var
            percent = obj._pos / getMaxPosition(obj),
            value   = Math.round((percent * (obj._max - obj._min)) + obj._min),
            result  = [percent, value];

        result.percent = percent;
        result.value   = value;
        return result;
    }

    function getTrackerWidth(obj) {
        if (obj._tsz > 0) {
            return obj._tsz;
        }

        if (obj._dir == 'y') {
            return obj.parentNode.offsetHeight;
        }

        return obj.parentNode.offsetWidth;
    }

    function getHandleWidth(obj) {
        if (obj._hsz > 0) {
            return obj._hsz;
        }

        if (obj._dir == 'y') {
            return obj.offsetHeight;
        }

        return obj.offsetWidth;
    }

    this.setPercent = function(obj, percent) {
        setPosition(obj, parseInt(percent * getMaxPosition(obj)));
    };

    this.setValue = function(obj, value) {
        if (value < obj._min) {
            value = obj._min;
        }
        else if (value > obj._max) {
            value = obj._max;
        }

        if (obj.input) {
            obj.input.value = value;
        }

        this.setPercent(obj, (value - obj._min) / (obj._max - obj._min));
    };

    this.setSize = function(obj, length) {
        var
            current = getCurrentPosition(obj),
            resized = getMaxPosition(obj);

        obj.parentNode.style[(obj._dir == 'y' ? 'height' : 'width')] = length + 'px';

        if (resized != getMaxPosition(obj)) {
            this.setValue(obj, current.value);
        }
    };

    this.init = function(container, opt) {
        var obj = $WH.ce('a');
        obj.href = 'javascript:;';
        obj.onmousedown = onMouseDown;
        obj.className = 'handle';

        var track = $WH.ce('a');
        track.href = 'javascript:;';
        track.onmousedown = onClick.bind(0, obj);
        track.className = 'track';

        $WH.ae(container, $WH.ce('span'));
        $WH.ae(container, track);
        $WH.ae(container, obj);

        obj._dir = 'x';
        obj._min = 1;
        obj._max = 100;
        obj._pos = 0;
        obj._tsz = 0;
        obj._hsz = 0;

        if (opt != null) {
            // Orientation
            if (opt.direction == 'y') {
                obj._dir = 'y';
            }

            // Values
            if (opt.minValue) {
                obj._min = opt.minValue;
            }
            if (opt.maxValue) {
                obj._max = opt.maxValue;
            }

            // Functions
            if (opt.onMove) {
                obj.onMove = opt.onMove;
            }

            if (opt.trackSize) {
                obj._tsz = opt.trackSize;
            }

            if (opt.handleSize) {
                obj._hsz = opt.handleSize;
            }

            // Labels
            if (opt.showLabels !== false) {
                var label = $WH.ce('div');

                label.innerHTML = obj._min;
                label.className = 'label min';
                $WH.ae(container, label);

                label = $WH.ce('div');
                label.innerHTML = obj._max;
                label.className = 'label max';
                $WH.ae(container, label);

                obj.input = $WH.ce('input');
                $(obj.input).attr({ value: obj._max, type: 'text' }
                ).bind('click', function () { this.select(); }
                ).keypress(function (e) {
                    var allowed = [];
                    var usedKey = e.which;
                    for (i = 48; i < 58; i++) {
                        allowed.push(i);
                    }
                    if (!($WH.in_array(allowed, usedKey) >= 0) && usedKey != 13) {
                        e.preventDefault();
                    }
                }).bind('keyup keydown change', onKeyPress.bind(0, obj));

                obj.input.className = 'input';
                $WH.ae(container, obj.input);
            }
        }

        container.className = 'slider-' + obj._dir + (opt == null || opt.showLabels !== false ? ' has-labels' : '');

        return obj;
    }
};

var suDialog;
function su_addToSaved(c, d, a, e) {
	if (!c) {
		return
	}
	if (!Dialog.templates.docompare) {
		Dialog.templates.docompare = {
			title: LANG.dialog_compare,
			width: 400,
			height: 138,
            buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],
			fields: [{
				id: "selecteditems",
				type: "caption",
				compute: function(h, g, f, i) {
					i.innerHTML = $WH.sprintf((g == 1 ? LANG.dialog_selecteditem: LANG.dialog_selecteditems), g)
				}
			},
			{
				id: "action",
				type: "radio",
				label: "",
				value: 3,
				submitOnDblClick: 1,
				options: {
					1 : LANG.dialog_nosaveandview,
					2 : LANG.dialog_saveandview,
					3 : LANG.dialog_saveforlater
				}
			}]
		}
	}
	if (!suDialog) {
		suDialog = new Dialog()
	}
	var b = function(h) {
		var g = $WH.gc("compare_groups"),
		f = "?compare";
		if (h.action > 1) {
			if (g) {
				c = g + ";" + c
			}
			$WH.sc("compare_groups", 20, c, "/", location.hostname);
			// $WH.sc("compare_groups", 20, c, "/", ".wowhead.com");
			if (e) {
				$WH.sc("compare_level", 20, e, "/", location.hostname)
				// $WH.sc("compare_level", 20, e, "/", ".wowhead.com")
			}
		} else {
			f += "=" + c + (e ? "&l=" + e: "")
		}
		if (h.action < 3) {
			if (a) {
				window.open(f)
			} else {
				location.href = f
			}
		}
	};
	suDialog.show("docompare", {
		data: {
			selecteditems: d,
			action: 1
		},
		onSubmit: b
	})
}

function Tabs(opt) {
    $WH.cO(this, opt);

    if (this.parent) {
        this.parent = $WH.ge(this.parent);
    }
    else {
        return;
    }

    this.selectedTab = -1;

    this.uls = [];

    this.tabs = [];
    this.nShows = 0;
    if (this.poundable == null) {
        this.poundable = 1;
    }
    this.poundedTab = null;

    if (this.onLoad == null) {
        this.onLoad = Tabs.onLoad.bind(this);
    }

    if (this.onShow == null) {
        this.onShow = Tabs.onShow.bind(this);
    }

    if (this.onHide) {
        this.onHide = this.onHide.bind(this);
    }
}

Tabs.prototype = {
    add: function(caption, opt) {
        var
            _,
            index = this.tabs.length;

        _ = {
            caption: caption,
            index: index,
            owner: this
        };
        $WH.cO(_, opt);

        this.tabs.push(_);

        return index;
    },

    hide: function(index, visible) {
        if (this.tabs[index]) {
            var selectedTab = this.selectedTab;

            if (index == 0 && selectedTab == -1) {
                this.poundedTab = this.selectedTab = selectedTab = 0;
            }

            if (index != this.poundedTab) {
                this.selectedTab = -1;
            }

            this.tabs[index].hidden = !visible;
            this.flush();

            if (!visible && index == selectedTab) {
                this.selectedTab = selectedTab;
                for (var i = 0, len = this.tabs.length; i < len; ++i) {
                    if (i != index && !this.tabs[i].hidden) {
                        return this.show(i, 1);
                    }
                }
            }
        }
    },

    unlock: function(index, locked) {
        if (this.tabs[index]) {
            this.tabs[index].locked = locked;
            _ = $WH.gE(this.uls[0], 'a');

            $('.icon-lock', _[index]).remove();

            if (locked) {
                $('div, b', _[index]).prepend('<span class="icon-lock" />');
            }

            var _ = location.hash.substr(1).split(':')[0];
            if (this.tabs[index].id == _) {
                this.show(index, 1);
            }
        }
    },

    focus: function(index) {
        if (index < 0) {
            index = this.tabs.length + index;
        }
        this.forceScroll = 1;
        $WH.gE(this.uls[0], 'a')[index].onclick({}, true);
        this.forceScroll = null;
    },

    show: function(index, forceClick) {
        var _;

        if (isNaN(index) || index < 0) {
            index = 0;
        }
        else if (index >= this.tabs.length) {
            index = this.tabs.length - 1;
        }

        if ((forceClick == null && index == this.selectedTab) || this.tabs[index].hidden) {
            return;
        }

        if(this.tabs[index].locked) {
            return this.onShow(this.tabs[index], this.tabs[this.selectedTab]);
        }

        if (this.selectedTab != -1) {
            _ = this.tabs[this.selectedTab];

            if (this.onHide && !this.onHide(_)) {
                return;
            }

            if (_.onHide && !_.onHide()) {
                return;
            }
        }

        ++this.nShows;

        _ = $WH.gE(this.uls[0], 'a');
        if (this.selectedTab != -1) {
            _[this.selectedTab].className = '';
        }
        _[index].className = 'selected';

        _ = this.tabs[index];
        if (_.onLoad) {
            _.onLoad();
            _.onLoad = null;
        }

        this.onShow(this.tabs[index], this.tabs[this.selectedTab]);

        if (_.onShow) {
            _.onShow(this.tabs[this.selectedTab]);
        }

        this.selectedTab = index;
    },

    flush: function(defaultTab) {
        var _, l, a, b, d, d2;

        var container = $WH.ce('div');
        container.className = 'tabs-container';

        this.uls[0] = $WH.ce('ul');
        this.uls[0].className = 'tabs';

        d = $WH.ce('div');
        d.className = 'tabs-levels';

        $WH.ae(container, this.uls[0]);

        for (var i = 0; i < this.tabs.length; ++i) {
            var tab = this.tabs[i];

            l = $WH.ce('li');
            a = $WH.ce('a');
            b = $WH.ce('b');

            if (tab.hidden) {
                l.style.display = 'none';
            }

            if (this.poundable) {
                a.href = '#' + tab.id;
            }
            else {
                a.href = 'javascript:;';
            }

            $WH.ns(a);
            a.onclick = Tabs.onClick.bind(tab, a);

            d = $WH.ce('div');

            if (tab.locked) {
                s = $WH.ce('span');
                s.className = 'icon-lock';
                $WH.ae(d, s);
            }
            else if (tab.icon) {
                s = $WH.ce('span');
                s.className = 'icontiny';
                s.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + tab.icon.toLowerCase() + '.gif)';
                $WH.ae(d, s);
            }

            if(tab.tooltip) {
                a.onmouseover = (function(tooltip, e) { $WH.Tooltip.showAtCursor(e, tooltip, 0, 0, 'q'); }).bind(a, tab.tooltip);
                a.onmousemove = $WH.Tooltip.cursorUpdate;
                a.onmouseout  = $WH.Tooltip.hide;
            }

            if (tab['class']) {
                d.className = tab['class'];
            }

            $WH.ae(d, $WH.ct(tab.caption));
            $WH.ae(a, d);

            if (tab.locked) {
                s = $WH.ce('span');
                s.className = 'icon-lock';
                $WH.ae(b, s);
            }
            else if (tab.icon) {
                s = $WH.ce('span');
                s.className = 'icontiny';
                s.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + tab.icon.toLowerCase() + '.gif)';
                $WH.ae(b, s);
            }

            $WH.ae(b, $WH.ct(tab.caption));
            $WH.ae(a, b);
            $WH.ae(l, a);
            $WH.ae(this.uls[0], l);
        }

        $WH.ee(this.parent);
        $WH.ae(this.parent, container);

        if (this.onLoad) {
            _ = this.onLoad();
            if (_ != null) {
                this.poundedTab = defaultTab = _;
            }
        }

        this.show(defaultTab);
    },

    setTabName: function(index, name) {
        this.tabs[index].caption = name;

        var _ = $WH.gE(this.uls[0], 'a');
        g_setTextNodes(_[index], name);
    },

    setTabPound: function(index, pound) {
        if (!this.poundable) {
            return;
        }

        var _ = $WH.gE(this.uls[0], 'a');
        _[index].href = '#' + this.tabs[index].id + (pound ? ':' + pound : '');
    },

    setTabTooltip: function(index, text) {
        this.tabs[index].tooltip = text;

        var _ = $WH.gE(this.uls[0], 'a');
        if(text == null) {
            _[index].onmouseover = _[index].onmousemove = _[index].onmouseout = null;
        }
        else {
            _[index].onmouseover = function(e) { $WH.Tooltip.showAtCursor(e, text, 0, 0, 'q2'); };
            _[index].onmousemove = $WH.Tooltip.cursorUpdate;
            _[index].onmouseout  = $WH.Tooltip.hide;
        }
    },

    getSelectedTab: function() {
        return this.selectedTab;
    }
};

Tabs.onClick = function(a, e, forceClick) {
    if (forceClick == null && this.index == this.owner.selectedTab) {
        return;
    }

    var res = $WH.rf2(e);
    if (res == null) {
        return;
    }

    this.owner.show(this.index, forceClick);

    if (this.owner.poundable && !this.locked) {
        var _ = a.href.indexOf('#');
        _ != -1 && location.replace(a.href.substr(_));
    }

    return res;
};

Tabs.onLoad = function() {
    if (!this.poundable || !location.hash.length) {
        return;
    }

    var _ = location.hash.substr(1).split(':')[0];
    if (_) {
        return $WH.in_array(this.tabs, _, function(x) {
            if(!x.locked) {
                return x.id;
            }
        });
    }
};

Tabs.onShow = function(newTab, oldTab) {
    var _;

    if (newTab.hidden || newTab.locked) {
        return;
    }

    if (oldTab) {
        $WH.ge('tab-' + oldTab.id).style.display = 'none';
    }

    _ = $WH.ge('tab-' + newTab.id);
    _.style.display = '';

    if (((this.nShows == 1 && this.poundedTab != null && this.poundedTab >= 0) || this.forceScroll) && !this.noScroll) {
        var el, padd;
        if (this.__st) {
            el = this.__st;
            padd = 15;
        }
        else {
            el = _;
            padd = this.parent.offsetHeight + 15;
        }

        setTimeout($WH.g_scrollTo.bind(null, el, padd), 10);
    }
};

var g_listviews = {};

function Listview(opt) {
    $WH.cO(this, opt);

    if (this.id) {
        var divId = (this.tabs ? 'tab-' : 'lv-') + this.id;
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

    var get = $WH.g_getGets();
    if ((get.debug != null || g_user.debug) && g_user.roles & U_GROUP_MODERATOR) {
        this.debug = true;
    }

    if (this.template && Listview.templates[this.template]) {
        this.template = Listview.templates[this.template];
    }
    else {
        return;
    }

    g_listviews[this.id] = this;

    if (this.data == null) {
        this.data = [];
    }

    if (this.poundable == null) {
        if (this.template.poundable != null) {
            this.poundable = this.template.poundable;
        }
        else {
            this.poundable = true;
        }
    }

    if (this.searchable == null) {
        if (this.template.searchable != null) {
            this.searchable = this.template.searchable;
        }
        else {
            this.searchable = false;
        }
    }

    if (this.filtrable == null) {
        if (this.template.filtrable != null) {
            this.filtrable = this.template.filtrable;
        }
        else {
            this.filtrable = false;
        }
    }

    if (this.sortable == null) {
        if (this.template.sortable != null) {
            this.sortable = this.template.sortable;
        }
        else {
            this.sortable = true;
        }
    }

    if (this.customPound == null) {
        if (this.template.customPound != null) {
            this.customPound = this.template.customPound;
        }
        else {
            this.customPound = false;
        }
    }

    if (this.data.length == 1) {
        this.filtrable = false;
        this.searchable = false;
    }

    if (this.searchable && this.searchDelay == null) {
        if (this.template.searchDelay != null) {
            this.searchDelay = this.template.searchDelay;
        }
        else {
            this.searchDelay = 333;
        }
    }

    if (this.clickable == null) {
        if (this.template.clickable != null) {
            this.clickable = this.template.clickable;
        }
        else {
            this.clickable = true;
        }
    }

    if (this.hideBands == null) {
        this.hideBands = this.template.hideBands;
    }

    if (this.hideNav == null) {
        this.hideNav = this.template.hideNav;
    }

    if (this.hideHeader == null) {
        this.hideHeader = this.template.hideHeader;
    }

    if (this.hideCount == null) {
        this.hideCount = this.template.hideCount;
    }

    if (this.computeDataFunc == null && this.template.computeDataFunc != null) {
        this.computeDataFunc = this.template.computeDataFunc;
    }

    if (this.createCbControls == null && this.template.createCbControls != null) {
        this.createCbControls = this.template.createCbControls;
    }

    if (this.template.onBeforeCreate != null) {
        if (this.onBeforeCreate == null) {
            this.onBeforeCreate = this.template.onBeforeCreate;
        }
        else {
            this.onBeforeCreate = [this.template.onBeforeCreate, this.onBeforeCreate];
        }
    }

    if (this.onAfterCreate == null && this.template.onAfterCreate != null) {
        this.onAfterCreate = this.template.onAfterCreate;
    }

    if (this.onNoData == null && this.template.onNoData != null) {
        this.onNoData = this.template.onNoData;
    }

    if (this.createNote == null && this.template.createNote != null) {
        this.createNote = this.template.createNote;
    }

    if (this.customFilter == null && this.template.customFilter != null) {
        this.customFilter = this.template.customFilter;
    }

    if (this.onSearchSubmit == null && this.template.onSearchSubmit != null) {
        this.onSearchSubmit = this.template.onSearchSubmit;
    }

    if (this.getItemLink == null && this.template.getItemLink != null) {
        this.getItemLink = this.template.getItemLink;
    }

    if (this.clip == null && this.template.clip != null) {
        this.clip = this.template.clip;
    }

    if (this.clip || this.template.compute || this.id == 'topics' || this.id == 'recipes') {
        this.debug = false; // Don't add columns to picker windows
    }

    if (this.mode == null) {
        this.mode = this.template.mode;
    }

    if (this.template.noStyle != null) {
        this.noStyle = this.template.noStyle;
    }

    if (this.nItemsPerPage == null) {
        if (this.template.nItemsPerPage != null) {
            this.nItemsPerPage = this.template.nItemsPerPage;
        }
        else {
            this.nItemsPerPage = 50;
        }
    }
    this.nItemsPerPage |= 0;
    if (this.nItemsPerPage <= 0) {
        this.nItemsPerPage = 0;
    }

    this.nFilters = 0;
    this.resetRowVisibility();

    if (this.mode == Listview.MODE_TILED) {
        if (this.nItemsPerRow == null) {
            var ipr = this.template.nItemsPerRow;
            this.nItemsPerRow = (ipr != null ? ipr : 4);
        }
        this.nItemsPerRow |= 0;
        if (this.nItemsPerRow <= 1) {
            this.nItemsPerRow = 1;
        }
    }
    else if (this.mode == Listview.MODE_CALENDAR) {
        this.dates = [];
        this.nItemsPerRow  = 7; // Days per row
        this.nItemsPerPage = 1; // Months per page
        this.nDaysPerMonth = [];

        if (this.template.startOnMonth != null) {
            this.startOnMonth = this.template.startOnMonth;
        }
        else {
            this.startOnMonth = new Date();
        }

        this.startOnMonth.setDate(1);
        this.startOnMonth.setHours(0, 0, 0, 0);

        if (this.nMonthsToDisplay == null) {
            if (this.template.nMonthsToDisplay != null) {
                this.nMonthsToDisplay = this.template.nMonthsToDisplay;
            }
            else {
                this.nMonthsToDisplay = 1;
            }
        }

        var
            y = this.startOnMonth.getFullYear(),
            m = this.startOnMonth.getMonth();

        for (var j = 0; j < this.nMonthsToDisplay; ++j) {
            var date = new Date(y, m + j, 32);
            this.nDaysPerMonth[j] = 32 - date.getDate();
            for (var i = 1; i <= this.nDaysPerMonth[j]; ++i) {
                this.dates.push({ date: new Date(y, m + j, i) });
            }
        }

        if (this.template.rowOffset != null) {
            this.rowOffset = this.template.rowOffset;
        }
    }
    else {
        this.nItemsPerRow = 1;
    }

    this.columns = [];
    for (var i = 0, len = this.template.columns.length; i < len; ++i) {
        var
            ori = this.template.columns[i],
            cpy = {};

            $WH.cO(cpy, ori);

        this.columns.push(cpy);
    }

    // ************************
    // Extra Columns

    if (this.extraCols != null) {
        for (var i = 0, len = this.extraCols.length; i < len; ++i) {
            var pos = null;
            var col = this.extraCols[i];

            if (col.after || col.before) {
                var index = $WH.in_array(this.columns, (col.after ? col.after: col.before), function(x) {
                    return x.id;
                });

                if (index != -1) {
                    pos = (col.after ? index + 1 : index);
                }
            }

            if (pos == null) {
                pos = this.columns.length;
            }

            if (col.id == 'debug-id') {
                this.columns.splice(0, 0, col);
            }
            else {
                this.columns.splice(pos, 0, col);
            }
        }
    }

    // ************************
    // Visibility

    this.visibility = [];

    var
        visibleCols = [],
        hiddenCols  = [];

    if (this.visibleCols != null) {
        $WH.array_walk(this.visibleCols, function(x) {
            visibleCols[x] = 1;
        });
    }

    if (this.hiddenCols != null) {
        $WH.array_walk(this.hiddenCols, function(x) {
            hiddenCols[x] = 1;
        });
    }

    for (var i = 0, len = this.columns.length; i < len; ++i) {
        var col = this.columns[i];
        if (visibleCols[col.id] != null || (!col.hidden && hiddenCols[col.id] == null)) {
            this.visibility.push(i);
        }
    }

    // ************************
    // Sort

    if (this.sort == null && this.template.sort) {
        this.sort = this.template.sort.slice(0);
    }

    if (this.sort != null) {
        var sortParam = this.sort;
        this.sort = [];
        for (var i = 0, len = sortParam.length; i < len; ++i) {
            var col = parseInt(sortParam[i]);
            if (isNaN(col)) {
                var desc = 0;
                if (sortParam[i].charAt(0) == '-') {
                    desc = 1;
                    sortParam[i] = sortParam[i].substring(1);
                }
                var index = $WH.in_array(this.columns, sortParam[i], function(x) {
                    return x.id;
                });
                if (index != -1) {
                    if (desc) {
                        this.sort.push( - (index + 1));
                    }
                    else {
                        this.sort.push(index + 1);
                    }
                }
            }
            else {
                this.sort.push(col);
            }
        }
    }
    else {
        this.sort = [];
    }

    if (this.debug || g_user.debug) {
        this.columns.splice(0, 0, {
            id: 'debug-id',
            compute: function(data, td) {
                if (data.id) {
                    $WH.ae(td, $WH.ct(data.id));
                }
            },
            getVisibleText: function(data) {
                if (data.id) {
                    return data.id;
                }
                else {
                    return '';
                }
            },
            getValue: function(data) {
                if (data.id) {
                    return data.id;
                }
                else {
                    return 0;
                }
            },
            sortFunc: function(a, b, col) {
                if (a.id == null) {
                    return -1;
                }
                else if (b.id == null) {
                    return 1;
                }

                return $WH.strcmp(a.id, b.id);
            },
            name: 'ID',
            width: '5%',
            tooltip: 'ID'
        });
        this.visibility.splice(0, 0, -1);

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            this.visibility[i] = this.visibility[i] + 1;
        }

        for (var i = 0, len = this.sort.length; i < len; ++i) {
            if (this.sort[i] < 0) {
                this.sort[i] = this.sort[i] - 1;
            }
            else {
                this.sort[i] = this.sort[i] + 1;
            }
        }
    }

    if (this.tabs) {
        this.tabIndex = this.tabs.add(this.getTabName(), {
            id: this.id,
            onLoad: this.initialize.bind(this)
        });
    }
    else {
        this.initialize();
    }
}

Listview.MODE_DEFAULT  = 0;
Listview.MODE_CHECKBOX = 1;
Listview.MODE_DIV      = 2;
Listview.MODE_TILED    = 3;
Listview.MODE_CALENDAR = 4;

Listview.prototype = {
    initialize: function() {
        if (this.data.length) {
            if (this.computeDataFunc != null) {
                for (var i = 0, len = this.data.length; i < len; ++i) {
                    this.computeDataFunc(this.data[i]);
                }
            }
        }

        if (this.tabs) {
            this.pounded = (this.tabs.poundedTab == this.tabIndex);
            if (this.pounded) {
                this.readPound();
            }
        }
        else {
            this.readPound();
        }

        this.applySort();

        var obcResult;
        if (this.onBeforeCreate != null) {
            if (typeof this.onBeforeCreate == 'function') {
                obcResult = this.onBeforeCreate();
            }
            else {
                for (var i = 0; i < this.onBeforeCreate.length; ++i) {
                    (this.onBeforeCreate[i].bind(this))();
                }
            }
        }

        this.noData = $WH.ce('div');
        this.noData.className = 'listview-nodata text';

        if (this.mode == Listview.MODE_DIV) {
            this.mainContainer = this.mainDiv = $WH.ce('div');
            if(!this.noStyle) {
                this.mainContainer.className = 'listview-mode-div';
            }
        }
        else {
            this.mainContainer = this.table = $WH.ce('table');
            this.thead = $WH.ce('thead');
            this.tbody = $WH.ce('tbody');

            if (this.clickable) {
                this.tbody.className = 'clickable';
            }

            if (this.mode == Listview.MODE_TILED || this.mode == Listview.MODE_CALENDAR) {
                if(!this.noStyle)
                    this.table.className = 'listview-mode-' + (this.mode == Listview.MODE_TILED ? 'tiled' : 'calendar');

                var
                    width = (100 / this.nItemsPerRow) + '%',
                    colGroup = $WH.ce('colgroup'),
                    col;

                for (var i = 0; i < this.nItemsPerRow; ++i) {
                    col = $WH.ce('col');
                    col.style.width = width;
                    $WH.ae(colGroup, col);
                }

                $WH.ae(this.mainContainer, colGroup);
            }
            else {
                if (!this.noStyle) {
                    this.table.className = 'listview-mode-default';
                }

                this.createHeader();
                this.updateSortArrow();
            }

            $WH.ae(this.table, this.thead);
            $WH.ae(this.table, this.tbody);
        }

        this.createBands();

        if (this.customFilter != null) {
            this.updateFilters();
        }

        this.updateNav();
        this.refreshRows();

        if (this.onAfterCreate != null) {
            this.onAfterCreate(obcResult);
        }
    },

    createHeader: function() {
        var tr = $WH.ce('tr');

        if (this.mode == Listview.MODE_CHECKBOX) {
            var
                th  = $WH.ce('th'),
                div = $WH.ce('div'),
                a   = $WH.ce('a');

            th.style.width = '33px';

            a.href = 'javascript:;';
            a.className = 'listview-cb';
            $WH.ns(a);
            $WH.ae(a, $WH.ct(String.fromCharCode(160)));

            $WH.ae(div, a);
            $WH.ae(th, div);
            $WH.ae(tr, th);
        }

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var
                reali = this.visibility[i],
                col = this.columns[reali],
                th = $WH.ce('th');

            div = $WH.ce('div'),
            a = $WH.ce('a'),
            outerSpan = $WH.ce('span'),
            innerSpan = $WH.ce('span');

            col.__th = th;

            if (this.filtrable && (col.filtrable == null || col.filtrable)) {
                a.onmouseup = Listview.headerClick.bind(this, col, reali);
                a.onclick = a.oncontextmenu = $WH.rf;
            }
            else if (this.sortable) {
                a.href = 'javascript:;';
                a.onclick = this.sortBy.bind(this, reali + 1);
            }

            if (a.onclick) {
                a.onmouseover = Listview.headerOver.bind(this, a, col);
                a.onmouseout = $WH.Tooltip.hide;
                $WH.ns(a);
            }
            else {
                a.className = 'static';
            }

            if (col.width != null) {
                th.style.width = col.width;
            }

            if (col.align != null) {
                th.style.textAlign = col.align;
            }

            if (col.span != null) {
                th.colSpan = col.span;
            }

            $WH.ae(innerSpan, $WH.ct(col.name));
            $WH.ae(outerSpan, innerSpan);

            $WH.ae(a, outerSpan);
            $WH.ae(div, a);
            $WH.ae(th, div);

            $WH.ae(tr, th);
        }

        if (this.hideHeader) {
            this.thead.style.display = 'none';
        }

        $WH.ae(this.thead, tr);
    },

    createBands: function() {
        var
            bandTop = $WH.ce('div'),
            bandBot = $WH.ce('div'),
            noteTop = $WH.ce('div'),
            noteBot = $WH.ce('div');

        this.bandTop = bandTop;
        this.bandBot = bandBot;
        this.noteTop = noteTop;
        this.noteBot = noteBot;

        bandTop.className = 'listview-band-top';
        bandBot.className = 'listview-band-bottom';

        this.navTop = this.createNav(true);
        this.navBot = this.createNav(false);

        noteTop.className = noteBot.className = 'listview-note';

        if (this.note) {
            noteTop.innerHTML = this.note;
            var e = $WH.g_getGets();
            if (this.note.indexOf('fi_toggle()') > -1 && !e.filter) {
                fi_toggle();
            }
        }
        else if (this.createNote) {
            this.createNote(noteTop, noteBot);
        }

        if (this.debug) {
            $WH.ae(noteTop, $WH.ct(" ("));
            var ids = $WH.ce('a');
            ids.onclick = this.getList.bind(this);
            $WH.ae(ids, $WH.ct("CSV"));
            $WH.ae(noteTop, ids);
            $WH.ae(noteTop, $WH.ct(")"));
        }

        if (this._errors) {
            var
                sp = $WH.ce('small'),
                b  = $WH.ce('b');

            b.className = 'q10 report-icon';
            if (noteTop.innerHTML) {
                b.style.marginLeft = '10px';
            }

            g_addTooltip(sp, LANG.lvnote_witherrors, 'q');

            $WH.st(b, LANG.error);
            $WH.ae(sp, b);
            $WH.ae(noteTop, sp);
        }

        if (!noteTop.firstChild && !(this.createCbControls || this.mode == Listview.MODE_CHECKBOX)) {
            $WH.ae(noteTop, $WH.ct(String.fromCharCode(160)));
        }
        if (!(this.createCbControls || this.mode == Listview.MODE_CHECKBOX)) {
            $WH.ae(noteBot, $WH.ct(String.fromCharCode(160)));
        }

        $WH.ae(bandTop, this.navTop);
        if (this.searchable) {
            var
                FI_FUNC  = this.updateFilters.bind(this, true),
                FI_CLASS = (this._truncated ? 'search-within-results2' : 'search-within-results'),
                sp       = $WH.ce('span'),
                em       = $WH.ce('em'),
                a        = $WH.ce('a'),
                input    = $WH.ce('input');

            sp.className = 'listview-quicksearch';

            if (this.tabClick) {
                $(sp).click(this.tabClick);
            }

            $WH.ae(sp, em);

            a.href = 'javascript:;';
            a.onclick = function() {
                var foo = this.nextSibling;
                foo.value = '';
                foo.className = FI_CLASS;
                FI_FUNC();
            };
            a.style.display = 'none';
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(sp, a);
            $WH.ns(a);

            input.setAttribute('type', 'text');
            input.className = FI_CLASS;
            input.style.width = (this._truncated ? '19em': '15em');
            g_onAfterTyping(input, FI_FUNC, this.searchDelay);

            input.onmouseover = function() {
                if ($WH.trim(this.value) != '') {
                    this.className = '';
                }
            };

            input.onfocus = function() {
                this.className = '';
            };

            input.onblur = function() {
                if ($WH.trim(this.value) == '') {
                    this.className = FI_CLASS;
                    this.value = '';
                }
            };

            input.onkeypress = this.submitSearch.bind(this);

            $WH.ae(sp, input);

            this.quickSearchBox = input;
            this.quickSearchGlass = em;
            this.quickSearchClear = a;

            $WH.ae(bandTop, sp);
        }
        $WH.ae(bandTop, noteTop);

        $WH.ae(bandBot, this.navBot);
        $WH.ae(bandBot, noteBot);

        if (this.createCbControls || this.mode == Listview.MODE_CHECKBOX) {
            if (this.note) {
                noteTop.style.paddingBottom = '5px';
            }

            this.cbBarTop = this.createCbBar(true);
            this.cbBarBot = this.createCbBar(false);

            $WH.ae(bandTop, this.cbBarTop);
            $WH.ae(bandBot, this.cbBarBot);

            if (!this.noteTop.firstChild && !this.cbBarTop.firstChild) {
                this.noteTop.innerHTML = '&nbsp;';
            }

            if (!this.noteBot.firstChild && !this.cbBarBot.firstChild) {
                this.noteBot.innerHTML = '&nbsp;';
            }

            if (this.noteTop.firstChild && this.cbBarTop.firstChild) {
                this.noteTop.style.paddingBottom = '6px';
            }

            if (this.noteBot.firstChild && this.cbBarBot.firstChild) {
                this.noteBot.style.paddingBottom = '6px';
            }
        }

        if (this.hideBands & 1) {
            bandTop.style.display = 'none';
        }
        if (this.hideBands & 2) {
            bandBot.style.display = 'none';
        }

        $WH.ae(this.container, this.bandTop);
        if (this.clip) {
            var clipDiv = $WH.ce('div');
            clipDiv.className = 'listview-clip';
            clipDiv.style.width = this.clip.w + 'px';
            clipDiv.style.height = this.clip.h + 'px';
            this.clipDiv = clipDiv;

            $WH.ae(clipDiv, this.mainContainer);
            $WH.ae(clipDiv, this.noData);
            $WH.ae(this.container, clipDiv);
        }
        else {
            $WH.ae(this.container, this.mainContainer);
            $WH.ae(this.container, this.noData);
        }
        $WH.ae(this.container, this.bandBot);
    },

    createNav: function(top) {
        var
            div = $WH.ce('div'),
            a1 = $WH.ce('a'),
            a2 = $WH.ce('a'),
            a3 = $WH.ce('a'),
            a4 = $WH.ce('a'),
            span = $WH.ce('span'),
            b1 = $WH.ce('b'),
            b2 = $WH.ce('b'),
            b3 = $WH.ce('b');

        div.className = 'listview-nav';

        a1.href = a2.href = a3.href = a4.href = 'javascript:;';

        $WH.ae(a1, $WH.ct(String.fromCharCode(171) + LANG.lvpage_first));
        $WH.ae(a2, $WH.ct(String.fromCharCode(8249) + LANG.lvpage_previous));
        $WH.ae(a3, $WH.ct(LANG.lvpage_next + String.fromCharCode(8250)));
        $WH.ae(a4, $WH.ct(LANG.lvpage_last + String.fromCharCode(187)));

        $WH.ns(a1);
        $WH.ns(a2);
        $WH.ns(a3);
        $WH.ns(a4);

        a1.onclick = this.firstPage.bind(this);
        a2.onclick = this.previousPage.bind(this);
        a3.onclick = this.nextPage.bind(this);
        a4.onclick = this.lastPage.bind(this);

        if (this.mode == Listview.MODE_CALENDAR) {
            $WH.ae(b1, $WH.ct('a'));
            $WH.ae(span, b1);
        }
        else {
            $WH.ae(b1, $WH.ct('a'));
            $WH.ae(b2, $WH.ct('a'));
            $WH.ae(b3, $WH.ct('a'));
            $WH.ae(span, b1);
            $WH.ae(span, $WH.ct(LANG.hyphen));
            $WH.ae(span, b2);
            $WH.ae(span, $WH.ct(LANG.lvpage_of));
            $WH.ae(span, b3);
        }

        $WH.ae(div, a1);
        $WH.ae(div, a2);
        $WH.ae(div, span);
        $WH.ae(div, a3);
        $WH.ae(div, a4);

        if (top) {
            if (this.hideNav & 1) {
                div.style.display = 'none';
            }
        }
        else {
            if (this.hideNav & 2) {
                div.style.display = 'none';
            }
        }

        if (this.tabClick) {
            $('a', div).click(this.tabClick);
        }

        return div;
    },

    createCbBar: function(topBar) {
        var    div = $WH.ce('div');

        if (this.createCbControls) {
            this.createCbControls(div, topBar);
        }

        if(div.firstChild) { // Not empty
            div.className = 'listview-withselected' + (topBar ? '' : '2');
        }

        return div;
    },

    refreshRows: function() {
        var target = (this.mode == Listview.MODE_DIV ? this.mainContainer: this.tbody);
        $WH.ee(target);

        if (this.nRowsVisible == 0) {
            if (!this.filtered) {
                this.bandTop.style.display = this.bandBot.style.display = 'none';

                this.mainContainer.style.display = 'none';
            }

            this.noData.style.display = '';
            this.showNoData();

            return;
        }

        var
            starti,
            endi,
            func;

        if (! (this.hideBands & 1)) {
            this.bandTop.style.display = '';
        }
        if (! (this.hideBands & 2)) {
            this.bandBot.style.display = '';
        }

        if (this.nDaysPerMonth && this.nDaysPerMonth.length) {
            starti = 0;
            for(var i = 0; i < this.rowOffset; ++i) {
                starti += this.nDaysPerMonth[i];
            }
            endi = starti + this.nDaysPerMonth[i];
        }
        else if (this.nItemsPerPage > 0) {
            starti = this.rowOffset;
            endi = Math.min(starti + this.nRowsVisible, starti + this.nItemsPerPage);

            if (this.filtered && this.rowOffset > 0) { // Adjusts start and end position when listview is filtered
                for (var i = 0, count = 0; i < this.data.length && count < this.rowOffset; ++i) {
                    var row = this.data[i];

                    if (row.__hidden || row.__deleted) {
                        ++starti;
                    }
                    else {
                        ++count;
                    }
                }
                endi += (starti - this.rowOffset);
            }
        }
        else {
            starti = 0;
            endi = this.nRowsVisible;
        }

        var nItemsToDisplay = endi - starti;

        if (this.mode == Listview.MODE_DIV) {
            for (var j = 0; j < nItemsToDisplay; ++j) {
                var
                    i = starti + j,
                    row = this.data[i];

                if (!row) {
                    break;
                }

                if (row.__hidden || row.__deleted) {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(this.mainDiv, this.getDiv(i));
            }
        }
        else if (this.mode == Listview.MODE_TILED) {
            var
                k  = 0,
                tr = $WH.ce('tr');

            for (var j = 0; j < nItemsToDisplay; ++j) {
                var
                    i = starti + j,
                row = this.data[i];

                if (!row) {
                    break;
                }

                if (row.__hidden || row.__deleted) {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(tr, this.getCell(i));

                if (++k == this.nItemsPerRow) {
                    $WH.ae(this.tbody, tr);
                    if (j + 1 < nItemsToDisplay) {
                        tr = $WH.ce('tr');
                    }
                    k = 0;
                }
            }

            if (k != 0) {
                for (; k < 4; ++k) {
                    var foo = $WH.ce('td');
                    foo.className = 'empty-cell';
                    $WH.ae(tr, foo);
                }
                $WH.ae(this.tbody, tr);
            }
        }
        else if (this.mode == Listview.MODE_CALENDAR) {
            var tr = $WH.ce('tr');

            for(var i = 0; i < 7; ++i) {
                var th = $WH.ce('th');
                $WH.st(th, LANG.date_days[i]);
                $WH.ae(tr, th);
            }

            $WH.ae(this.tbody, tr);
            tr = $WH.ce('tr');

            for (var k = 0; k < this.dates[starti].date.getDay(); ++k) {
                var foo = $WH.ce('td');
                foo.className = 'empty-cell';
                $WH.ae(tr, foo);
            }

            for (var j = starti; j < endi; ++j) {
                $WH.ae(tr, this.getEvent(j));

                if (++k == 7) {
                    $WH.ae(this.tbody, tr);
                    tr = $WH.ce('tr');
                    k  = 0;
                }
            }

            if (k != 0) {
                for(; k < 7; ++k) {
                    var foo = $WH.ce('td');
                    foo.className = 'empty-cell';
                    $WH.ae(tr, foo);
                }
                $WH.ae(this.tbody, tr);
            }
        }
        else { // DEFAULT || CHECKBOX
            for (var j = 0; j < nItemsToDisplay; ++j) {
                var
                i = starti + j,
                row = this.data[i];

                if (!row) {
                    break;
                }

                if (row.__hidden || row.__deleted) {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(this.tbody, this.getRow(i));
            }
        }

        this.mainContainer.style.display = '';
        this.noData.style.display = 'none';
    },

    showNoData: function() {
        var div = this.noData;
        $WH.ee(div);

        var result = -1;
        if (this.onNoData) {
            result = (this.onNoData.bind(this, div))();
        }

        if (result == -1) {
            $WH.ae(this.noData, $WH.ct(this.filtered ? LANG.lvnodata2: LANG.lvnodata));
        }
    },

    getDiv: function(i) {
        var row = this.data[i];

        if (row.__div == null || this.minPatchVersion != row.__minPatch) {
            this.createDiv(row, i);
        }

        return row.__div;
    },

    createDiv: function(row, i) {
        var div = $WH.ce('div');
        row.__div = div;
        if (this.minPatchVersion) {
            row.__minPatch = this.minPatchVersion;
        }

        (this.template.compute.bind(this, row, div, i))();
    },

    getCell: function(i) {
        var row = this.data[i];

        if (row.__td == null) {
            this.createCell(row, i);
        }

        return row.__td;
    },

    createCell: function(row, i) {
        var td = $WH.ce('td');
        row.__td = td;

        (this.template.compute.bind(this, row, td, i))();
    },

    getEvent: function(i) {
        var row = this.dates[i];

        if (row.__td == null) {
            this.createEvent(row, i);
        }

        return row.__td;
    },

    createEvent: function(row, i) {
        row.events = $WH.array_filter(this.data, function(holiday) {
            if (holiday.__hidden || holiday.__deleted) {
                return false;
            }

            var dates = Listview.funcBox.getEventNextDates(holiday.startDate, holiday.endDate, holiday.rec || 0, row.date);
            if (dates[0] && dates[1]) {
                dates[0].setHours(0, 0, 0, 0);
                dates[1].setHours(0, 0, 0, 0);
                return dates[0] <= row.date && dates[1] >= row.date;
            }

            return false;
        });

        var td = $WH.ce('td');
        row.__td = td;

        if (row.date.getFullYear() == g_serverTime.getFullYear() && row.date.getMonth() == g_serverTime.getMonth() && row.date.getDate() == g_serverTime.getDate()) {
            td.className = 'calendar-today';
        }

        var div = $WH.ce('div');
        div.className = 'calendar-date';
        $WH.st(div, row.date.getDate());
        $WH.ae(td, div);

        div = $WH.ce('div');
        div.className = 'calendar-event';
        $WH.ae(td, div);

        (this.template.compute.bind(this, row, div, i))();

        if (this.getItemLink) {
            td.onclick = this.itemClick.bind(this, row);
        }
    },

    getRow: function(i) {
        var row = this.data[i];

        if (row.__tr == null) {
            this.createRow(row);
        }

        return row.__tr;
    },

    setRow: function(newRow) {
        if (this.data[newRow.pos]) {
            this.data[newRow.pos] = newRow;
            this.data[newRow.pos].__tr = newRow.__tr;
            this.createRow(this.data[newRow.pos]);
            this.refreshRows();
        }
    },

    createRow: function(row) {
        var tr = $WH.ce('tr');
        row.__tr = tr;

        if (this.mode == Listview.MODE_CHECKBOX) {
            var td = $WH.ce('td');

            if (!row.__nochk) {
                td.className = 'listview-cb';
                td.onclick = Listview.cbCellClick;

                var cb = $WH.ce('input');
                $WH.ns(cb);
                cb.type = 'checkbox';
                cb.onclick = Listview.cbClick;

                if (row.__chk) {
                    cb.checked = true;
                }

                row.__cb = cb;

                $WH.ae(td, cb);
            }

            $WH.ae(tr, td);
        }

        for (var i = 0, len = this.visibility.length; i < len; ++i) {
            var
                reali = this.visibility[i],
                col = this.columns[reali],
                td = $WH.ce('td'),
                result;

            if (col.align != null) {
                td.style.textAlign = col.align;
            }

            if (col.compute) {
                result = (col.compute.bind(this, row, td, tr, reali))();
            }
            else {
                if (row[col.value] != null) {
                    result = row[col.value];
                }
                else {
                    result = -1;
                }
            }

            if (result != -1 && result != null) {
                td.insertBefore($WH.ct(result), td.firstChild);
            }

            $WH.ae(tr, td);
        }

        if (this.mode == Listview.MODE_CHECKBOX && row.__chk) {
            tr.className = 'checked';
        }

        if (row.frommerge == 1) {
            tr.className += ' mergerow';
        }

        if (this.getItemLink) {
            tr.onclick = this.itemClick.bind(this, row);
        }
    },

    itemClick: function(row, e) {
        e = $WH.$E(e);

        var
            i = 0,
            el = e._target;

        while (el && i < 3) {
            if (el.nodeName == 'A') {
                return;
            }
            el = el.parentNode;
        }

        location.href = this.getItemLink(row);
    },

    submitSearch: function(e) {
        e = $WH.$E(e);

        if (!this.onSearchSubmit || e.keyCode != 13) {
            return;
        }

        for (var i = 0, len = this.data.length; i < len; ++i) {
            if (this.data[i].__hidden) {
                continue;
            }

            (this.onSearchSubmit.bind(this, this.data[i]))();
        }
    },

    validatePage: function() {
        var
            rpp = this.nItemsPerPage,
            ro  = this.rowOffset,
            len = this.nRowsVisible;

        if (ro < 0) {
            this.rowOffset = 0;
        }
        else if (this.mode == Listview.MODE_CALENDAR) {
            this.rowOffset = Math.min(ro, this.nDaysPerMonth.length - 1);
        }
        else {
            this.rowOffset = this.getRowOffset(ro + rpp > len ? len - 1 : ro);
        }
    },

    getRowOffset: function(rowNo) {
        var rpp = this.nItemsPerPage;

        return (rpp > 0 && rowNo > 0 ? Math.floor(rowNo / rpp) * rpp: 0);
    },

    resetRowVisibility: function() {
        for (var i = 0, len = this.data.length; i < len; ++i) {
            this.data[i].__hidden = false;
        }

        this.filtered = false;
        this.rowOffset = 0;
        this.nRowsVisible = this.data.length;
    },

    getColText: function(row, col) {
        var text = '';
        if (this.template.getVisibleText) {
            text = $WH.trim(this.template.getVisibleText(row) + ' ');
        }

        if (col.getVisibleText) {
            return text + col.getVisibleText(row)
        }

        if (col.getValue) {
            return text + col.getValue(row)
        }

        if (col.value) {
            return text + row[col.value]
        }

        if (col.compute) {
            return text + col.compute(row, $WH.ce('td'), $WH.ce('tr'));
        }

        return ""
    },

    resetFilters: function() {
        for (var j = 0, len2 = this.visibility.length; j < len2; ++j) {
            var realj = this.visibility[j];
            var col = this.columns[realj];

            if (col.__filter) {
                col.__th.firstChild.firstChild.className = '';
                col.__filter = null;
                --(this.nFilters);
            }
        }
    },

    updateFilters: function(refresh) {
        $WH.Tooltip.hide();
        this.resetRowVisibility();

        var
            searchText,
            parts,
            nParts;

        if (this.searchable) {
            this.quickSearchBox.parentNode.style.display = '';

            searchText = $WH.trim(this.quickSearchBox.value);

            if (searchText) {
                this.quickSearchGlass.style.display = 'none';
                this.quickSearchClear.style.display = '';

                searchText = searchText.toLowerCase().replace(/\s+/g, ' ');

                parts = searchText.split(' ');
                nParts = parts.length;
            }
            else {
                this.quickSearchGlass.style.display = '';
                this.quickSearchClear.style.display = 'none';
            }
        }
        else if (this.quickSearchBox) {
            this.quickSearchBox.parentNode.style.display = 'none';
        }

        if (!searchText && this.nFilters == 0 && this.customFilter == null) {
            if (refresh) {
                this.updateNav();
                this.refreshRows();
            }

            return;
        }

        // Numerical
        var filterFuncs = {
            1: function(x, y) {
                return x > y;
            },
            2: function(x, y) {
                return x == y;
            },
            3: function(x, y) {
                return x < y;
            },
            4: function(x, y) {
                return x >= y;
            },
            5: function(x, y) {
                return x <= y;
            },
            6: function(x, y, z) {
                return y <= x && x <= z;
            }
        };

        // Range
        var filterFuncs2 = {
            1: function(min, max, y) {
                return max > y;
            },
            2: function(min, max, y) {
                return min <= y && y <= max;
            },
            3: function(min, max, y) {
                return min < y;
            },
            4: function(min, max, y) {
                return max >= y;
            },
            5: function(min, max, y) {
                return min <= y;
            },
            6: function(min, max, y, z) {
                return y <= max && min <= z;
            }
        };

        var nRowsVisible = 0;

        for (var i = 0, len = this.data.length; i < len; ++i) {
            var
                row = this.data[i],
                nFilterMatches = 0,
                nSearchMatches = 0,
                matches = [];

            row.__hidden = true;

            if (this.customFilter && !this.customFilter(row, i)) {
                continue;
            }

            for (var j = 0, len2 = this.visibility.length; j < len2; ++j) {
                var realj = this.visibility[j];
                var col = this.columns[realj];

                if (col.__filter) {
                    var
                        filter = col.__filter,
                        result = false;

                    if (col.type != null && col.type == 'range') {
                        var
                            minValue = col.getMinValue(row),
                            maxValue = col.getMaxValue(row);

                        result = (filterFuncs2[filter.type])(minValue, maxValue, filter.value, filter.value2);
                    }
                    else if (col.type == null || col.type == 'num' || filter.type > 0) {
                        var value = null;

                        if (col.getValue) {
                            value = col.getValue(row);
                        }
                        else if (col.value) {
                            value = parseFloat(row[col.value]);
                        }

                        if (!value) {
                            value = 0;
                        }

                        result = (filterFuncs[filter.type])(value, filter.value, filter.value2);
                    }
                    else {
                        var text = this.getColText(row, col);

                        if (text) {
                            text = text.toString().toLowerCase();

                            if (filter.invert) {
                                result = text.match(filter.regex) != null;
                            }
                            else {
                                var foo = 0;

                                for (var k = 0, len3 = filter.words.length; k < len3; ++k) {
                                    if (text.indexOf(filter.words[k]) != -1) {
                                        ++foo;
                                    }
                                    else {
                                        break;
                                    }
                                }

                                result = (foo == filter.words.length);
                            }
                        }
                    }

                    if (filter.invert) {
                        result = !result;
                    }

                    if (result) {
                        ++nFilterMatches;
                    }
                    else {
                        break;
                    }
                }

                if (searchText) {
                    var text = this.getColText(row, col);
                    if (text) {
                        text = text.toString().toLowerCase();

                        for (var k = 0, len3 = parts.length; k < len3; ++k) {
                            if (!matches[k]) {
                                if (text.indexOf(parts[k]) != -1) {
                                    matches[k] = 1;
                                    ++nSearchMatches;
                                }
                            }
                        }
                    }
                }
            }

            if (row.__alwaysvisible ||
               ((this.nFilters == 0 || nFilterMatches == this.nFilters) &&
               (!searchText || nSearchMatches == nParts))) {
                row.__hidden = false;
                ++nRowsVisible;
            }
        }

        this.filtered = (nRowsVisible < this.data.length);
        this.nRowsVisible = nRowsVisible;

        if (refresh) {
            this.updateNav();
            this.refreshRows();
        }
    },

    changePage: function() {
        this.validatePage();

        this.refreshRows();
        this.updateNav();
        this.updatePound();

        var
            scroll = $WH.g_getScroll(),
            c = $WH.ac(this.container);

        if (scroll.y > c[1]) {
            scrollTo(scroll.x, c[1]);
        }
    },

    firstPage: function() {
        this.rowOffset = 0;
        this.changePage();

        return false;
    },

    previousPage: function() {
        this.rowOffset -= this.nItemsPerPage;
        this.changePage();

        return false;
    },

    nextPage: function() {
        this.rowOffset += this.nItemsPerPage;
        this.changePage();

        return false;
    },

    lastPage: function() {
        this.rowOffset = 99999999;
        this.changePage();

        return false;
    },

    addSort: function(arr, colNo) {
        var i = $WH.in_array(arr, Math.abs(colNo), function(x) {
            return Math.abs(x);
        });

        if (i != -1) {
            colNo = arr[i];
            arr.splice(i, 1);
        }

        arr.splice(0, 0, colNo);
    },

    sortBy: function(colNo) {
        if (colNo <= 0 || colNo > this.columns.length) {
            return;
        }

        if (Math.abs(this.sort[0]) == colNo) {
            this.sort[0] = -this.sort[0];
        }
        else {
            var defaultSort = -1;
            if (this.columns[colNo-1].type == 'text') {
                defaultSort = 1;
            }

            this.addSort(this.sort, defaultSort * colNo);
        }

        this.applySort();
        this.refreshRows();
        this.updateSortArrow();
        this.updatePound();
    },

    applySort: function() {
        if (this.sort.length == 0) {
            return;
        }

        Listview.sort = this.sort;
        Listview.columns = this.columns;

        if (this.indexCreated) {
            this.data.sort(Listview.sortIndexedRows.bind(this));
        }
        else {
            this.data.sort(Listview.sortRows.bind(this));
        }

        this.updateSortIndex();
    },

    setSort: function(sort, refresh, updatePound) {
        if (this.sort.toString() != sort.toString()) {
            this.sort = sort;
            this.applySort();

            if (refresh) {
                this.refreshRows();
            }

            if (updatePound) {
                this.updatePound();
            }
        }
    },

    readPound: function() {
        if (!this.poundable || !location.hash.length) {
            return false;
        }

        var _ = location.hash.substr(1);
        if (this.tabs) {
            var n = _.lastIndexOf(':');

            if (n == -1) {
                return false;
            }

            _ = _.substr(n + 1);
        }

        var num = parseInt(_);
        if (!isNaN(num)) {
            this.rowOffset = num;
            this.validatePage();

            if (this.poundable != 2) {
                var sort = [];
                var matches= _.match(/(\+|\-)[0-9]+/g);
                if (matches != null) {
                    for (var i = matches.length - 1; i >= 0; --i) {
                        var colNo = parseInt(matches[i]) | 0;
                        var _ = Math.abs(colNo);
                        if (_ <= 0 || _ > this.columns.length) {
                            break;
                        }
                        this.addSort(sort, colNo);
                    }

                    this.setSort(sort, false, false);
                }
            }

            if (this.tabs) {
                this.tabs.setTabPound(this.tabIndex, this.getTabPound());
            }
        }
    },

    updateSortArrow: function() {
        if (!this.sort.length || !this.thead || this.mode == Listview.MODE_TILED || this.mode == Listview.MODE_CALENDAR) {
            return;
        }

        var i = $WH.in_array(this.visibility, Math.abs(this.sort[0]) - 1);

        if (i == -1) {
            return;
        }

        if (this.mode == Listview.MODE_CHECKBOX && i < this.thead.firstChild.childNodes.length - 1) {
            i += 1;
        }

        var span = this.thead.firstChild.childNodes[i].firstChild.firstChild.firstChild;

        if (this.lsa && this.lsa != span) { // lastSortArrow
            this.lsa.className = '';
        }

        span.className = (this.sort[0] < 0 ? 'sortdesc': 'sortasc');
        this.lsa = span;
    },

    updateSortIndex: function() {
        var _ = this.data;

        for (var i = 0, len = _.length; i < len; ++i) {
            _[i].__si = i; // sortIndex
        }

        this.indexCreated = true;
    },

    updateTabName: function() {
        if (this.tabs && this.tabIndex != null) {
            this.tabs.setTabName(this.tabIndex, this.getTabName());
        }
    },

    updatePound: function(useCurrentSort) {
        if (!this.poundable) {
            return;
        }

        var
            _  = '',
            id = '';

        if (useCurrentSort) {
            if (location.hash.length && this.tabs) {
                var n = location.hash.lastIndexOf(':');
                if (n != -1 && !isNaN(parseInt(location.hash.substr(n + 1)))) {
                    _ = location.hash.substr(n + 1);
                }
            }
        }
        else {
            _ = this.getTabPound();
        }

        if (this.customPound) {
            id = this.customPound;
        }
        else if (this.tabs) {
            id = this.id;
        }

        if (_ && this.tabs) {
            this.tabs.setTabPound(this.tabIndex, _);
        }

        location.replace('#' + id + (id && _ ? ':' : '') + _);
    },

    updateNav: function() {
        var
            arr      = [this.navTop, this.navBot],
            _        = this.nItemsPerPage,
            __       = this.rowOffset,
            ___      = this.nRowsVisible,
            first    = 0,
            previous = 0,
            next     = 0,
            last     = 0,
            date     = new Date();

        if (___ > 0) {
            if (! (this.hideNav & 1)) {
                arr[0].style.display = '';
            }
            if (! (this.hideNav & 2)) {
                arr[1].style.display = '';
            }
        }
        else {
            arr[0].style.display = arr[1].style.display = 'none';
        }

        if (this.mode == Listview.MODE_CALENDAR) {
            for (var i = 0; i < this.nDaysPerMonth.length; ++i) {
                if (i == __)  { // Selected month
                    if (i > 0)
                        previous = 1;
                    if (i > 1)
                        first = 1;
                    if (i < this.nDaysPerMonth.length - 1)
                        next = 1;
                    if (i < this.nDaysPerMonth.length - 2)
                        last = 1;
                }
            }

            date.setTime(this.startOnMonth.valueOf());
            date.setMonth(date.getMonth() + __);
        }
        else {
            if (_) {
                if (__ > 0) {
                    previous = 1;
                    if (__ >= _ + _) {
                        first = 1;
                    }
                }
                if (__ + _ < ___) {
                    next = 1;
                    if (__ + _ + _ < ___) {
                        last = 1;
                    }
                }
            }
        }

        for (var i = 0; i < 2; ++i) {
            var childs = arr[i].childNodes;

            childs[0].style.display = (first ? '': 'none');
            childs[1].style.display = (previous ? '': 'none');
            childs[3].style.display = (next ? '': 'none');
            childs[4].style.display = (last ? '': 'none');
            childs = childs[2].childNodes;

            if (this.mode == Listview.MODE_CALENDAR) {
                childs[0].firstChild.nodeValue = LANG.date_months[date.getMonth()] + ' ' + date.getFullYear();
            }
            else {
                childs[0].firstChild.nodeValue = __ + 1;
                childs[2].firstChild.nodeValue = _ ? Math.min(__ + _, ___) : ___;
                childs[4].firstChild.nodeValue = ___;
            }
        }
    },

    getTabName: function() {
        var
            name = this.name,
            n = this.data.length;

        for (var i = 0, len = this.data.length; i < len; ++i) {
            if (this.data[i].__hidden || this.data[i].__deleted) {
                --n;
            }
        }

        if (n > 0 && !this.hideCount) {
            name += $WH.sprintf(LANG.qty, n);
        }

        return name;
    },

    getTabPound: function() {
        var buffer = '';

        buffer += this.rowOffset;

        if (this.poundable != 2 && this.sort.length) {
            buffer += ('+' + this.sort.join('+')).replace(/\+\-/g, '-');
        }

        return buffer;
    },

    getCheckedRows: function() {
        var checkedRows = [];

        for (var i = 0, len = this.data.length; i < len; ++i) {
            var _ = this.data[i];

            if ((_.__cb && _.__cb.checked) || (!_.__cb && _.__chk)) {
                checkedRows.push(_);
            }
        }

        return checkedRows;
    },

    resetCheckedRows: function() {
        for (var i = 0, len = this.data.length; i < len; ++i) {
            var _ = this.data[i];

            if (_.__cb) {
                _.__cb.checked = false;
            }
            else if (_.__chk) {
                _.__chk = null;
            }

            if (_.__tr) {
                _.__tr.className = _.__tr.className.replace('checked', '');
            }
        }
    },

    deleteRows: function(rows) {
        if (!rows || !rows.length) {
            return;
        }

        for (var i = 0, len = rows.length; i < len; ++i) {
            var row = rows[i];

            if (!row.__hidden && !row.__hidden) {
                this.nRowsVisible -= 1;
            }

            row.__deleted = true;
        }

        this.updateTabName();

        if (this.rowOffset >= this.nRowsVisible) {
            this.previousPage();
        }
        else {
            this.refreshRows();
            this.updateNav();
        }
    },

    setData: function(data) {
        this.data = data;
        this.indexCreated = false;

        this.resetCheckedRows();
        this.resetRowVisibility();

        if (this.tabs) {
            this.pounded = (this.tabs.poundedTab == this.tabIndex);
            if (this.pounded) {
                this.readPound();
            }
        }
        else {
            this.readPound();
        }

        this.applySort();
        this.updateSortArrow();

        if (this.customFilter != null) {
            this.updateFilters();
        }

        this.updateNav();
        this.refreshRows();
    },

    getClipDiv: function() {
        return this.clipDiv;
    },

    getNoteTopDiv: function() {
        return this.noteTop;
    },

    focusSearch: function() {
        this.quickSearchBox.focus();
    },

    clearSearch: function() {
        this.quickSearchBox.value = '';
    },

    getList: function() {
        if (!this.debug) {
            return;
        }
        var str = '';
        for (var i = 0; i < this.data.length; i++) {
            if (!this.data[i].__hidden) {
                str += this.data[i].id + ', ';
            }
        }

        listviewIdList.show(str);
    },

    createIndicator: function(v, f, c) {
        if (!this.noteIndicators) {
            this.noteIndicators = $WH.ce('div');
            this.noteIndicators.className = 'listview-indicators';
            $(this.noteIndicators).insertBefore($(this.noteTop));
        }

        var t = this.tabClick;
        $(this.noteIndicators).append(
            $('<span class="indicator"></span>')
            .html(v)
            .append(!f ? '' :
                $('<a class="indicator-x" style="outline: none">[x]</a>')
                .attr('href', (typeof f == 'function' ? 'javascript:;' : f))
                .click(function () { if (t) t(); if (typeof f == 'function') f(); })
            )
            .css('cursor', (typeof c == 'function' ? 'pointer' : null))
            .click(function () { if (t) t(); if (typeof c == 'function') c(); })
        );

        $(this.noteTop).css('padding-top', '7px');
    },

    removeIndicators: function() {
        if (this.noteIndicators) {
            $(this.noteIndicators).remove();
            this.noteIndicators = null;
        }

        $(this.noteTop).css('padding-top', '');
    }
};

Listview.sortRows = function(a, b) {
    var
        sort = Listview.sort,
        cols = Listview.columns;

    for (var i = 0, len = sort.length; i < len; ++i) {
        var
            res,
            _ = cols[Math.abs(sort[i]) - 1];

        if (!_) {
            _ = this.template;
        }

        if (_.sortFunc) {
            res = _.sortFunc(a, b, sort[i]);
        }
        else {
            res = $WH.strcmp(a[_.value], b[_.value]);
        }

        if (res != 0) {
            return res * sort[i];
        }
    }

    return 0;
},

Listview.sortIndexedRows = function(a, b) {
    var
        sort = Listview.sort,
        cols = Listview.columns,
        res;

    for (var idx in sort) {
        _ = cols[Math.abs(sort[idx]) - 1];

        if(!_) {
            _ = this.template;
        }

        if (_.sortFunc) {
            res = _.sortFunc(a, b, sort[0]);
        }
        else {
            res = $WH.strcmp(a[_.value], b[_.value]);
        }

        if (res != 0) {
            return res * sort[idx];
        }
    }

    return (a.__si - b.__si);
},

Listview.cbSelect = function(v) {
    for (var i = 0, len = this.data.length; i < len; ++i) {
        var _ = this.data[i];
        var v2 = v;

        if (_.__hidden) {
            continue;
        }

        if (!_.__nochk && _.__cb) {
            var
                cb = _.__cb,
                tr = cb.parentNode.parentNode;

            if (v2 == null) {
                v2 = !cb.checked;
            }

            if (cb.checked != v2) {
                cb.checked = v2;
                tr.className = (cb.checked ? tr.className + ' checked' : tr.className.replace('checked', ''));
            }
        }
        else if (v2 == null) {
            v2 = true;
        }

        _.__chk = v2;
    }
};

Listview.cbClick = function(e) {
    setTimeout(Listview.cbUpdate.bind(0, 0, this, this.parentNode.parentNode), 1);
    $WH.sp(e);
};

Listview.cbCellClick = function(e) {
    setTimeout(Listview.cbUpdate.bind(0, 1, this.firstChild, this.parentNode), 1);
    $WH.sp(e);
};

Listview.cbUpdate = function(toggle, cb, tr) {
    if (toggle) {
        cb.checked = !cb.checked;
    }

    tr.className = (cb.checked ? tr.className + ' checked' : tr.className.replace('checked', ''));
};

Listview.headerClick = function(col, i, e) {
    e = $WH.$E(e);

    if (this.tabClick) {
        this.tabClick();
    }

    if (e._button == 3 || e.shiftKey || e.ctrlKey) {
        $WH.Tooltip.hide();
        setTimeout(Listview.headerFilter.bind(this, col, null), 1);
    }
    else {
        this.sortBy(i + 1);
    }

    return false;
};

Listview.headerFilter = function(col, res) {
    var prefilled = '';
    if (col.__filter) {
        if (col.__filter.invert) {
            prefilled += '!';
        }

        prefilled += col.__filter.text;
    }

    if (res == null) {
        var res = prompt($WH.sprintf(LANG.prompt_colfilter1 + (col.type == 'text' ? LANG.prompt_colfilter2: LANG.prompt_colfilter3), col.name), prefilled);
    }

    if (res != null) {
        var filter = {text: '', type: -1};

        res = $WH.trim(res.replace(/\s+/g, ' '));

        if (!res && this.onEmptyFilter) {
            this.onEmptyFilter(col);
        }
        else if (res) {
            if (res.charAt(0) == '!' || res.charAt(0) == '-') {
                filter.invert = 1;
                res = res.substr(1);
            }

            if (col.type == 'text') {
                filter.type = 0;
                filter.text = res;

                if (filter.invert) {
                    filter.regex = g_createOrRegex(res);
                }
                else {
                    filter.words = res.toLowerCase().split(' ');
                }
            }
            var
                value,
                value2;

            if (res.match(/(>|=|<|>=|<=)\s*([0-9\.]+)/)) {
                value = parseFloat(RegExp.$2);
                if (!isNaN(value)) {
                    switch (RegExp.$1) {
                    case '>':
                        filter.type = 1;
                        break;
                    case '=':
                        filter.type = 2;
                        break;
                    case '<':
                        filter.type = 3;
                        break;
                    case '>=':
                        filter.type = 4;
                        break;
                    case '<=':
                        filter.type = 5;
                        break;
                    }
                    filter.value = value;
                    filter.text = RegExp.$1 + ' ' + value;
                }
            }
            else if (res.match(/([0-9\.]+)\s*\-\s*([0-9\.]+)/)) {
                value = parseFloat(RegExp.$1);
                value2 = parseFloat(RegExp.$2);
                if (!isNaN(value) && !isNaN(value2)) {
                    if (value > value2) {
                        var foo = value;
                        value = value2;
                        value2 = foo;
                    }

                    if (value == value2) {
                        filter.type = 2;
                        filter.value = value;
                        filter.text = '= ' + value;
                    }
                    else {
                        filter.type = 6;
                        filter.value = value;
                        filter.value2 = value2;
                        filter.text = value + ' - ' + value2;
                    }
                }
            }
            else {
                var parts = res.toLowerCase().split(' ');
                if (!col.allText && parts.length == 1 && !isNaN(value = parseFloat(parts[0]))) {
                    filter.type = 2;
                    filter.value = value;
                    filter.text = '= ' + value;
                }
                else if (col.type == 'text') {
                    filter.type = 0;
                    filter.text = res;

                    if (filter.invert) {
                        filter.regex = g_createOrRegex(res);
                    }
                    else {
                        filter.words = parts;
                    }
                }
            }

            if (filter.type == -1) {
                alert(LANG.message_invalidfilter);
                return;
            }
        }

        if (!col.__filter || filter.text != col.__filter.text || filter.invert != col.__filter.invert) {
            var a = col.__th.firstChild.firstChild;

            if (res && filter.text) {
                if (!col.__filter) {
                    a.className = 'q5';
                    ++(this.nFilters);
                }

                col.__filter = filter;
            }
            else {
                if (col.__filter) {
                    a.className = '';
                    --(this.nFilters);
                }

                col.__filter = null;
            }

            this.updateFilters(1);
        }
    }
};

Listview.headerOver = function(a, col, e) {
    var buffer = '';

    buffer += '<b class="q1">' + (col.tooltip ? col.tooltip: col.name) + '</b>';

    if (col.__filter) {
        buffer += '<br />' + $WH.sprintf((col.__filter.invert ? LANG.tooltip_colfilter2: LANG.tooltip_colfilter1), col.__filter.text);
    }

    buffer += '<br /><span class="q2">' + LANG.tooltip_lvheader1 + '</span>';
    if (this.filtrable && (col.filtrable == null || col.filtrable)) {
        buffer += '<br /><span class="q2">' + ($WH.Browser.opera ? LANG.tooltip_lvheader3: LANG.tooltip_lvheader2) + '</span>';
    }

    $WH.Tooltip.show(a, buffer, 0, 0, 'q');
};

Listview.extraCols = {
    id: {
        id: 'id',
        name: 'ID',
        width: '5%',
        value: 'id',
        compute: function(data, td) {
            if (data.id) {
                $WH.ae(td, $WH.ct(data.id));
            }
        }
    },

    date: {
        id: 'obj-date',
        name: LANG.added,
        compute: function(data, td) {
            if (data.date) {
                if (data.date <= 86400) {
                    $WH.ae(td, $WH.ct('???'));
                }
                else {
                    var added   = new Date(data.date * 1000);
                    var elapsed = (g_serverTime - added) / 1000;

                    return g_formatDate(td, elapsed, added, null, true);
                }
            }
        },
        sortFunc: function(a, b, col) {
            if (a.date == b.date) {
                return 0;
            }
            else if (a.date < b.date) {
                return -1;
            }
            else {
                return 1;
            }
        }
    },

    cost: {
        id: 'cost',
        name: LANG.cost,
        getValue: function(row) {
            if (row.cost) {
                return (row.cost[3] && row.cost[3][0] ? row.cost[3][0][1] : 0) || (row.cost[2] || row.cost[1] || row.cost[0])
    // 5.0      return (row.cost[2] && row.cost[2][0] ? row.cost[2][0][1] : 0) || (row.cost[1] && row.cost[1][0] ? row.cost[1][0][1] : 0) || row.cost[0];
            }
        },
        compute: function(row, td) {
            if (row.cost) {
                var money = row.cost[0];
                var side = null;
                var items = row.cost[2];
                var currency = row.cost[1];
                var achievementPoints = 0;

                if (row.side != null) {
                    side = row.side;
                }
                else  if (row.react != null) {
                    if (row.react[0] == 1 && row.react[1] == -1) { // Alliance only
                        side = 1;
                    }
                    else if (row.react[0] == -1 && row.react[1] == 1) { // Horde only
                        side = 2;
                    }
                }

                Listview.funcBox.appendMoney(td, money, side, currency, items, row.cost[3]/*achievementPoints*/)
    // 5.0      Listview.funcBox.appendMoney(td, money, side, items, currency, achievementPoints);

            }
        },
        sortFunc: function(a, b, col) {
            if (a.cost == null) {
                return -1;
            }
            else if (b.cost == null) {
                return 1;
            }

            var
                lena = 0,
                lenb = 0,
                lenc = 0,
                lend = 0;

            if (a.cost[2] != null) {
                $WH.array_walk(a.cost[2], function(x, _, __, i) {
                    lena += Math.pow(10, i) + x[1];
                });
            }
            if (b.cost[2] != null) {
                $WH.array_walk(b.cost[2], function(x, _, __, i) {
                    lenb += Math.pow(10, i) + x[1];
                });
            }
            if (a.cost[1] != null) {
                $WH.array_walk(a.cost[1], function(x, _, __, i) {
                    lenc += Math.pow(10, i) + x[1];
                });
            }
            if (b.cost[1] != null) {
                $WH.array_walk(b.cost[1], function(x, _, __, i) {
                    lend += Math.pow(10, i) + x[1];
                });
            }

            return $WH.strcmp(lena, lenb) || $WH.strcmp(lenc, lend) || $WH.strcmp(a.cost[0], b.cost[0]);
        }
    },

    count: {
        id: 'count',
        name: LANG.count,
        value: 'count',
        compute: function(row, td) {
            if (!(this._totalCount > 0 || row.outof > 0)) {
                return;
            }

            if (row.outof) {
                var d = $WH.ce('div');
                d.className = 'small q0';
                $WH.ae(d, $WH.ct($WH.sprintf(LANG.lvdrop_outof, row.outof)));
                $WH.ae(td, d);
            }
            return row.count;
        },
        getVisibleText: function(row) {
            var buff = row.count;
            if (row.outof) {
                buff += ' ' + row.outof;
            }

            return buff;
        },
        sortFunc: function(a, b, col) {
            if (a.count == null) {
                return -1;
            }
            else if (b.count == null) {
                return 1;
            }

            return $WH.strcmp(a.count, b.count);
        }
    },

    percent: {
        id: 'percent',
        name: '%',
        value: 'percent',
        compute: function(row, td) {
            if (row.count <= 0) {
                return '??';
            }

            if (row.pctstack) {
                var text = '';
                var data = eval('(' + row.pctstack + ')');

                for (var amt in data) {
                    var pct = (data[amt] * row.percent) / 100;

                    if (pct >= 1.95) {
                        pct = parseFloat(pct.toFixed(0));
                    }
                    else if (pct >= 0.195) {
                        pct = parseFloat(pct.toFixed(1));
                    }
                    else {
                        pct = parseFloat(pct.toFixed(2));
                    }

                    text += $WH.sprintf(LANG.stackof_format, amt, pct) + '<br />';
                }

                td.className += ' tip';
                g_addTooltip(td, text);
            }

            var value = parseFloat(row.percent.toFixed(row.percent >= 1.95 ? 0 : (row.percent >= 0.195 ? 1 : 2)));

            if (row.pctstack) {
                var sp = $WH.ce('span');
                sp.className += ' tip';
                $WH.ae(sp, $WH.ct(value));
                $WH.ae(td, sp);
            }
            else {
                return value;
            }
        },
        getVisibleText: function(row) {
            if (row.count <= 0) {
                return '??';
            }

            if (row.percent >= 1.95) {
                return row.percent.toFixed(0);
            }
            else if (row.percent >= 0.195) {
                return parseFloat(row.percent.toFixed(1));
            }
            else {
                return parseFloat(row.percent.toFixed(2));
            }
        },
        sortFunc: function(a, b, col) {
            if (a.count == null) {
                return -1;
            }
            else if (b.count == null) {
                return 1;
            }

            if (a.percent >= 1.95) {
                var acmp = a.percent.toFixed(0);
            }
            else if (a.percent >= 0.195) {
                acmp = parseFloat(a.percent.toFixed(1));
            }
            else {
                acmp = parseFloat(a.percent.toFixed(2));
            }

            if (b.percent >= 1.95) {
                var bcmp = b.percent.toFixed(0);
            }
            else if (b.percent >= 0.195) {
                bcmp = parseFloat(b.percent.toFixed(1));
            }
            else {
                bcmp = parseFloat(b.percent.toFixed(2));
            }

            return $WH.strcmp(acmp, bcmp);
        }
    },

    stock: {
        id: 'stock',
        name: LANG.stock,
        width: '10%',
        value: 'stock',
        compute: function(row, td) {
            if (row.stock > 0) {
                return row.stock;
            }
            else {
                td.style.fontFamily = 'Verdana, sans-serif';
                return String.fromCharCode(8734);
            }
        },
        getVisibleText: function(row) {
            if (row.stock > 0) {
                return row.stock;
            }
            else {
                return String.fromCharCode(8734) + ' infinity';
            }
        }
    },

    currency: {
        id: 'currency',
        name: LANG.currency,
        getValue: function(row) {
            if (row.currency) {
                return (row.currency[0] ? row.currency[0][1] : 0);
            }
        },
        compute: function(row, td) {
            if (row.currency) {
                var side = null;
                if (row.side != null) {
                    side = row.side;
                }
                else if (row.react != null) {
                    if (row.react[0] == 1 && row.react[1] == -1) { // Alliance only
                        side = 1;
                    }
                    else if (row.react[0] == -1 && row.react[1] == 1) { // Horde only
                        side = 2;
                    }
                }

                Listview.funcBox.appendMoney(td, null, side, null, row.currency);
            }
        },
        sortFunc: function(a, b, col) {
            if (a.currency == null) {
                return -1;
            }
            else if (b.currency == null) {
                return 1;
            }

            var
                lena = 0,
                lenb = 0;

            $WH.array_walk(a.currency, function(x, _, __, i) {
                lena += Math.pow(10, i) + x[1];
            });
            $WH.array_walk(b.currency, function(x, _, __, i) {
                lenb += Math.pow(10, i) + x[1];
            });

            return $WH.strcmp(lena, lenb);
        }
    },

    mode: {
        id: 'mode',
        name: 'Mode',
        after: 'name',
        type: 'text',
        compute: function(row, td) {
            if (row.modes && row.modes.mode) {
                if ((row.modes.mode & 120) == 120 || (row.modes.mode & 3) == 3) {
                    return LANG.pr_note_all;
                }

                return Listview.extraCols.mode.getVisibleText(row);
            }
        },
        getVisibleText: function(row) {
            // TODO: Remove magic numbers.
            var modeNormal = !!(row.modes.mode & 26);
            var modeHeroic = !!(row.modes.mode & 97);
            var player10 = !!(row.modes.mode & 40);
            var player25 = !!(row.modes.mode & 80);

            var specificPlayers;
            if (player10 && !player25) {
                specificPlayers = 10;
            }
            else if (player25 && !player10) {
                specificPlayers = 25;
            }

            var specificMode;
            if (modeNormal && !modeHeroic) {
                specificMode = 'normal';
            }
            else if (modeHeroic && !modeNormal) {
                specificMode = 'heroic';
            }

            if (specificMode) {
                if (specificPlayers) {
                    return $WH.sprintf(LANG['tab_' + specificMode + 'X'], specificPlayers); // e.g. "Heroic 25"
                }
                else {
                    return LANG['tab_' + specificMode]; // e.g. "Heroic"
                }
            }

            if (specificPlayers) {
                return $WH.sprintf(LANG.lvzone_xman, specificPlayers); // e.g. "25-player"
            }

            return LANG.pr_note_all;
        },
        sortFunc: function(a, b, col) {
            if (a.modes && b.modes) {
                return -$WH.strcmp(a.modes.mode, b.modes.mode);
            }
        }
    },

    requires: {
        id: 'requires',
        name: LANG.requires,
        type: 'text',
        compute: function(item, td) {
            if (item.achievement && g_achievements[item.achievement]) {
                $WH.nw(td);
                td.className = 'small';
                td.style.lineHeight = '18px';

                var a = $WH.ce('a');
                a.href = '?achievement=' + item.achievement;
                a.className = 'icontiny tinyspecial';
                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + g_achievements[item.achievement].icon.toLowerCase() + '.gif)';
                a.style.whiteSpace = 'nowrap';

                $WH.st(a, g_achievements[item.achievement]['name_' + g_locale.name]);
                $WH.ae(td, a);
            }
        },
        getVisibleText: function(item) {
            if (item.achievement && g_achievements[item.achievement]) {
                return g_achievements[item.achievement].name;
            }
        },
        sortFunc: function(a, b, col) {
            return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
        }
    },

    reqskill: {
        id: 'reqskill',
        name: LANG.skill,
        width: '10%',
        value: 'reqskill',
        before: 'yield'
    },

    yield: {
        id: 'yield',
        name: LANG.yields,
        type: 'text',
        align: 'left',
        span: 2,
        value: 'name',
        compute: function(row, td, tr) {
            if (row.yield && g_items[row.yield]) {
                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                $WH.ae(i, g_items.createIcon(row.yield, 1));
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                var wrapper = $WH.ce('div');

                var a = $WH.ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + row.yield;
                a.className = 'q' + g_items[row.yield].quality;
                $WH.ae(a, $WH.ct(g_items[row.yield]['name_' + g_locale.name]));
                $WH.ae(wrapper, a);
                $WH.ae(td, wrapper);
            }
        },
        getVisibleText: function(row) {
            if (row.yield && g_items[row.yield]) {
                return g_items[row.yield]['name_' + g_locale.name];
            }
        },
        sortFunc: function(a, b, col) {
            if (!a.yield || !g_items[a.yield] || !b.yield || !g_items[b.yield]) {
                return (a.yield && g_items[a.yield] ? 1 : (b.yield && g_items[b.yield] ? -1 : 0));
            }
            return -$WH.strcmp(g_items[a.yield].quality, g_items[b.yield].quality) ||
                    $WH.strcmp(g_items[a.yield]['name_' + g_locale.name], g_items[b.yield]['name_' + g_locale.name]);
        }
    },

    condition: {
        /*
            condition.status: [
                0: missing
                1: active
                2: done / obtained
            ]

            LANG.completed
            LANG.earned
            LANG.progress

            probably also events, zones, skill, faction, ..
        */

        id: 'condition',
        name: LANG.requires,
        compute: function(row, td) {
            if (!row.condition || !row.condition.type || !row.condition.typeId) {
                return '';
            }

            var cnd = Listview.extraCols.condition.getState(row.condition);
            if (!cnd) {
                return;
            }

            td.className = 'small';
            td.style.lineHeight = '18px';

            var span = $WH.ce('span');
            span.className = cnd.color;
            $WH.ae(span, cnd.state);
            $WH.ae(td, span);
            $WH.ae(td, $WH.ce('br'));

            var a = $WH.ce('a');
            a.href = cnd.url;
            a.className = 'icontiny tinyspecial';
            if (cnd.quality) {
                a.className += ' q' + cnd.quality;
            }
            a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + cnd.icon + '.gif)';
            a.style.whiteSpace = 'nowrap';
            $WH.st(a, cnd.name);
            $WH.ae(td, a);
        },
        getVisibleText: function(row) {
            var buff = '';

            if (!row.condition || !row.condition.type || !row.condition.typeId) {
                return buff;
            }

            var cnd = Listview.extraCols.condition.getState(row.condition);
            if (!cnd) {
                return buff;
            }

            buff += cnd.name + ' ' + cnd.state;

            return buff;
        },
        getState: function(cond) {
            switch (g_types[cond.type]) {
                case 'spell':
                    return Listview.extraCols.condition.getSpellState(cond);
                case 'item':
                    return Listview.extraCols.condition.getItemtate(cond);
                case 'achievement':
                    return Listview.extraCols.condition.getAchievementState(cond);
                case 'quest':
                    return Listview.extraCols.condition.getQuestState(cond);
                default:
                    return {};
            }
        },
        getSpellState: function(cond) {
            if (!cond.typeId || !g_spells[cond.typeId]) {
                return;
            }

            var
                cnd  = {},
                item = g_spells[cond.typeId];

            cnd.icon  = item.icon.toLowerCase();
            cnd.state = cond.status ? $WH.ct(LANG.pr_note_known) : $WH.ct(LANG.pr_note_missing);
            cnd.color = cond.status ? 'q2' : 'q10';
            cnd.name  = item['name_' + g_locale.name];
            cnd.url   = '?spell=' + cond.typeId;

            return cnd;
        },
        getItemState: function(cond) {
            if (!cond.typeId || !g_items[cond.typeId]) {
                return;
            }

            var
                cnd  = {},
                item = g_items[cond.typeId];

            cnd.icon    = item.icon.toLowerCase();
            cnd.state   = cond.status ? $WH.ct(LANG.pr_note_earned) : $WH.ct(LANG.pr_note_missing);
            cnd.color   = cond.status ? 'q2' : 'q10';
            cnd.name    = item['name_' + g_locale.name];
            cnd.url     = '?item=' + cond.typeId;
            cnd.quality = item.quality;

            return cnd;
        },
        getAchievementState: function(cond) {
            if (!cond.typeId || !g_achievements[cond.typeId]) {
                return;
            }

            var
                cnd  = {},
                item = g_achievements[cond.typeId];

            cnd.icon  = item.icon.toLowerCase();
            cnd.state = cond.status ? $WH.ct(LANG.pr_note_earned) : $WH.ct(LANG.pr_note_incomplete);
            cnd.color = cond.status ? 'q2' : 'q10';
            cnd.name  = item['name_' + g_locale.name];
            cnd.url   = '?achievement=' + cond.typeId;

            return cnd;
        },
        getQuestState: function(cond) {
            if (!cond.typeId || !g_quests[cond.typeId]) {
                return;
            }

            var
                cnd  = {},
                item = g_quests[cond.typeId];

            cnd.icon  = item.icon.toLowerCase();
            cnd.state = cond.status == 1 ? $WH.ct(LANG.progress) : cond.status == 2 ? $WH.ct(LANG.pr_note_complete) : $WH.ct(LANG.pr_note_incomplete);
            cnd.color = cond.status == 1 ? 'q1' : cond.status == 2 ? 'q2' : 'q10';
            cnd.name  = item['name_' + g_locale.name];
            cnd.url   = '?quest=' + cond.typeId;

            return cnd;
        },
        sortFunc: function(a, b, col) {
            if (a.condition.status && b.condition.status) {
                return $WH.strcmp(a.condition.status, b.condition.status);
            }
        }
    },
};

Listview.funcBox = {
    createSimpleCol: function(i, n, w, v) {
        return {
            id:    i,
            name:  (LANG[n] !== undefined ? LANG[n] : n),
            width: w,
            value: v
        };
    },

    initLootTable: function(row) {
        var divider;

        if (this._totalCount != null) {
            divider = this._totalCount;
        }
        else {
            divider = row.outof;
        }

        if (divider == 0) {
            if (row.count != -1) {
                row.percent = row.count;
            }
            else {
                row.percent = 0;
            }
        }
        else {
            row.percent = row.count / divider * 100;
        }

        (Listview.funcBox.initModeFilter.bind(this, row))();
    },

// // subtabs here //

    initModeFilter: function(row)
    {
        if(this._lootModes == null)
            this._lootModes = { 99: 0 };

        if(this._distinctModes == null)
            this._distinctModes = { 99: 0 };

        if((!row.modes || row.modes.mode == 4) && row.classs != 12 && row.commondrop)
        {
            this._lootModes[99]++; // Trash
            this._distinctModes[99]++;
        }
        else if(row.modes)
        {
            for(var i = -2; i <= 5; ++i)
            {
                if(this._lootModes[i] == null)
                    this._lootModes[i] = 0;

                if(row.modes.mode & 1 << parseInt(i) + 2)
                    this._lootModes[i]++;
            }

            if(this._distinctModes[row.modes.mode] == null)
                this._distinctModes[row.modes.mode] = 0;
            this._distinctModes[row.modes.mode]++;
        }
    },

    addModeIndicator: function()
    {
        var nModes = 0;
        for(var i in this._distinctModes)
        {
            if(this._distinctModes[i])
                nModes++;
        }

        if(nModes < 2)
            return;

        var    pm      = location.hash.match(/:mode=([^:]+)/),
            order   = [0,-1,-2,1,3,2,4,5,99],
            langref = {
                "-2": LANG.tab_heroic,
                "-1": LANG.tab_normal,
                   0: LANG.tab_noteworthy,
                   1: $WH.sprintf(LANG.tab_normalX, 10),
                   2: $WH.sprintf(LANG.tab_normalX, 25),
                   3: $WH.sprintf(LANG.tab_heroicX, 10),
                   4: $WH.sprintf(LANG.tab_heroicX, 25),
                   5: LANG.tab_raidfinder,
                  99: '' // No indicator for trash
            };

        var f = function(mode, dm, updatePound)
        {
            g_setSelectedLink(this, 'lootmode');

            lv.customPound  = lv.id + (dm != null ? ':mode=' + g_urlize(langref[dm].replace(' ', '')) : '');
            lv.customFilter = function(item) { return Listview.funcBox.filterMode(item, lv._totalCount, mode) };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if(updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            modes = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, null, 0);
        firstCallback();

        modes.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for(var j = 0, len = order.length; j < len; ++j)
        {
            var i = order[j];
            if(!this._lootModes[i])
                continue;

            a = $('<a><span>' + langref[i] + '</span> (' + this._lootModes[i] + ')</a>');
            a[0].f = f.bind(a[0], 1 << i + 2, i, 1);
            a.click(a[0].f);

            if(i == 0)
                firstCallback = f.bind(a[0], 1 << i + 2, i, 0);

            if((i < -1 || i > 2) && i != 5)
                a.addClass('icon-heroic');

            modes.push($('<span class="indicator-mode"></span>').append(a).append($('<b' + (i < -1 || i > 2 ? ' class="icon-heroic"' : '') + '>' + langref[i] + ' (' + this._lootModes[i] + ')</b>'))); // jQuery is dumb

            if(pm && pm[1] == g_urlize(langref[i].replace(' ', '')))
                (a[0].f)();
        }

        var showNoteworthy = false;
        for(var i = 0, len = modes.length; i < len; ++i)
        {
            a = $('a', modes[i]);
            if(!$('span', a).html() && modes.length == 3)
                showNoteworthy = true;
            else
                this.createIndicator(modes[i], null, a[0].f);
        }

        if(showNoteworthy)
            firstCallback();

        $(this.noteTop).append($('<div class="clear"></div>'));
    },

    filterMode: function(row, total, mode)
    {
        if(total != null && row.count != null)
        {
            if(row._count == null)
                row._count = row.count;

            var count = row._count;

            if(mode != null && row.modes[mode])
            {
                count = row.modes[mode].count;
                total = row.modes[mode].outof;
            }

            row.__tr    = null;
            row.count   = count;
            row.outof   = total;
            if(total)
                row.percent = count / total * 100;
            else
                row.percent = count;
        }

        return (mode != null ? ((!row.modes || row.modes.mode == 4) && row.classs != 12 && row.commondrop ? (mode == 32) : (row.modes && (row.modes.mode & mode))) : true);
    },

    initSubclassFilter: function(row)
    {
        var i = row.classs || 0;
        if(this._itemClasses == null)
            this._itemClasses = {};
        if(this._itemClasses[i] == null)
            this._itemClasses[i] = 0;
        this._itemClasses[i]++;
    },

    addSubclassIndicator: function()
    {
        var it = location.hash.match(/:type=([^:]+)/),
            order = [];

        for(var i in g_item_classes)
            order.push({ i: i, n: g_item_classes[i] });
        order.sort(function(a, b) { return $WH.strcmp(a.n, b.n) });

        var f = function(itemClass, updatePound)
        {
            g_setSelectedLink(this, 'itemclass');

            lv.customPound  = lv.id + (itemClass != null ? ':type=' + itemClass : '');
            lv.customFilter = function(item) { return itemClass == null || itemClass == item.classs };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if(updatePound)
                lv.updatePound(1);
        }

        var lv = this,
            classes = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();

        classes.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for(var j = 0, len = order.length; j < len; ++j)
        {
            var i = order[j].i;
            if(!this._itemClasses[i])
                continue;

            a = $('<a><span>' + g_item_classes[i] + '</span> (' + this._itemClasses[i] + ')</a>');
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            classes.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + g_item_classes[i] + ' (' + this._itemClasses[i] + ')</b>')));

            if(it && it[1] == g_urlize(i))
                (a[0].f)();
        }

        if(classes.length > 2)
        {
            for(var i = 0, len = classes.length; i < len; ++i)
                this.createIndicator(classes[i], null, $('a', classes[i])[0].f);

            $(this.noteTop).css('padding-bottom', '12px');
            $(this.noteIndicators).append($('<div class="clear"></div>')).insertAfter($(this.navTop));
        }
    },

    initStatisticFilter: function(row)
    {
        if(this._achievTypes == null)
            this._achievTypes = {};
        if(this._achievTypes[row.type] == null)
            this._achievTypes[row.type] = 0;
        this._achievTypes[row.type]++;
    },

    addStatisticIndicator: function()
    {
        var it = location.hash.match(/:type=([^:]+)/),
            order = [];

        for(var i in g_achievement_types)
            order.push({ i: i, n: g_achievement_types[i] });
        order.sort(function(a, b) { return $WH.strcmp(a.n, b.n) });

        var f = function(achievType, updatePound)
        {
            g_setSelectedLink(this, 'achievType');

            lv.customPound  = lv.id + (achievType != null ? ':type=' + achievType : '');
            lv.customFilter = function(achievement) { return achievType == null || achievType == achievement.type };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if(updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            types = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();

        types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for(var j = 0, len = order.length; j < len; ++j)
        {
            var i = order[j].i;
            if(!this._achievTypes[i])
                continue;

            a = $('<a><span>' + g_achievement_types[i] + '</span> (' + this._achievTypes[i] + ')</a>');
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + g_achievement_types[i] + ' (' + this._achievTypes[i] + ')</b>')));

            if(it && it[1] == i)
                (a[0].f)();
        }

        if(types.length > 2)
        {
            for(var i = 0, len = types.length; i < len; ++i)
                this.createIndicator(types[i], null, $('a', types[i])[0].f);

            $(this.noteTop).append($('<div class="clear"></div>'));
        }
    },

    initQuestFilter: function(row)
    {
        if(this._questTypes == null)
            this._questTypes = {};

        for(var i = 1; i <= 4; ++i)
        {
            if(this._questTypes[i] == null)
                this._questTypes[i] = 0;

            if(row._type && (row._type & 1 << i - 1))
                this._questTypes[i]++;
        }
    },

    addQuestIndicator: function()
    {
        var it = location.hash.match(/:type=([^:]+)/);

        var f = function(questType, updatePound)
        {
            g_setSelectedLink(this, 'questType');

            lv.customPound  = lv.id + (questType != null ? ':type=' + questType : '');
            lv.customFilter = function(quest) { return questType == null || (quest._type & 1 << questType - 1) };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if(updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            types = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();

        types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for(var i = 1; i <= 4; ++i)
        {
            if(!this._questTypes[i])
                continue;

            a = $('<a><span>' + g_quest_indicators[i] + '</span> (' + this._questTypes[i] + ')</a>');
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + g_quest_indicators[i] + ' (' + this._questTypes[i] + ')</b>')));

            if(it && it[1] == i)
                (a[0].f)();
        }

        if(types.length > 2)
        {
            for(var i = 0, len = types.length; i < len; ++i)
                this.createIndicator(types[i], null, $('a', types[i])[0].f);

            $(this.noteTop).css('padding-bottom', '12px');
            $(this.noteIndicators).append($('<div class="clear"></div>')).insertAfter($(this.navTop));
        }
    },

// \\ subtabs here \\

    assocArrCmp: function(a, b, arr) {
        if (a == null) {
            return -1;
        }
        else if (b == null) {
            return 1;
        }

        var n = Math.max(a.length, b.length);
        for (var i = 0; i < n; ++i) {
            if (a[i] == null) {
                return -1;
            }
            else if (b[i] == null) {
                return 1;
            }

            var res = $WH.strcmp(arr[a[i]], arr[b[i]]);
            if (res != 0) {
                return res;
            }
        }

        return 0
    },

    assocBinFlags: function(f, arr) {
        var res = [];
        for (var i in arr) {
            if (!isNaN(i) && (f & 1 << i - 1)) {
                res.push(i);
            }
        }
        res.sort(function(a, b) {
            return $WH.strcmp(arr[a], arr[b]);
        });

        return res;
    },

    location: function(row, td) {
        if (row.location == null) {
            return -1;
        }

        for (var i = 0, len = row.location.length; i < len; ++i) {
            if (i > 0) {
                $WH.ae(td, $WH.ct(LANG.comma));
            }

            var zoneId = row.location[i];
            if (zoneId == -1) {
                $WH.ae(td, $WH.ct(LANG.ellipsis));
            }
            else {
                var a = $WH.ce('a');
                a.className = 'q1';
                a.href = '?zone=' + zoneId;
                $WH.ae(a, $WH.ct(g_zones[zoneId]));
                $WH.ae(td, a);
            }
        }
    },

    arrayText: function(arr, lookup) {
        if (arr == null) {
            return;
        }
        else if (!$WH.is_array(arr)) {
            return lookup[arr];
        }
        var buff = '';
        for (var i = 0, len = arr.length; i < len; ++i) {
            if (i > 0) {
                buff += ' ';
            }
            if (!lookup[arr[i]]) {
                continue;
            }
            buff += lookup[arr[i]];
        }
        return buff;
    },

    createCenteredIcons: function(arr, td, text, type) {
        if (arr != null) {
            var
                d = $WH.ce('div'),
                d2 = $WH.ce('div');

            $WH.ae(document.body, d);

            if (text && (arr.length != 1 || type != 2)) {
                var bibi = $WH.ce('div');
                bibi.style.position = 'relative';
                bibi.style.width = '1px';
                var bibi2 = $WH.ce('div');
                bibi2.className = 'q0';
                bibi2.style.position = 'absolute';
                bibi2.style.right = '2px';
                bibi2.style.lineHeight = '26px';
                bibi2.style.fontSize = '11px';
                bibi2.style.whiteSpace = 'nowrap';
                $WH.ae(bibi2, $WH.ct(text));
                $WH.ae(bibi, bibi2);
                $WH.ae(d, bibi);

                d.style.paddingLeft = bibi2.offsetWidth + 'px';
            }

            var iconPool = g_items;
            if (type == 1) {
                iconPool = g_spells;
            }

            for (var i = 0, len = arr.length; i < len; ++i) {
                var icon;
                if (arr[i] == null) {
                    icon = $WH.ce('div');
                    icon.style.width = icon.style.height = '26px';
                }
                else {
                    var
                        id,
                        num;

                        if (typeof arr[i] == 'object') {
                        id = arr[i][0];
                        num = arr[i][1];
                    }
                    else {
                        id = arr[i];
                    }

                    if (id) {
                        icon = iconPool.createIcon(id, 0, num);
                    }
                    else {
                        icon = Icon.create('inventoryslot_empty', 0, null, 'javascript:;');
                    }
                }

                if (arr.length == 1 && type == 2) { // Tiny text display
                    if (id && g_items[id]) {
                        $WH.ee(d);
                        var
                            item = g_items[id],
                            a = $WH.ce('a'),
                            sp = $WH.ce('span');

                            sp.style.paddingTop = '4px';

                        a.href = '?item=' + id;
                        a.className = 'q' + item.quality + ' icontiny tinyspecial';
                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                        a.style.whiteSpace = 'nowrap';

                        $WH.st(a, item['name_' + g_locale.name]);
                        $WH.ae(sp, a);

                        if (num > 1) {
                            $WH.ae(sp, $WH.ct(' (' + num + ')'));
                        }

                        if (text) {
                            var bibi = $WH.ce('span');
                            bibi.className = 'q0';
                            bibi.style.fontSize = '11px';
                            bibi.style.whiteSpace = 'nowrap';
                            $WH.ae(bibi, $WH.ct(text));
                            $WH.ae(d, bibi);
                            if ($(bibi2).length > 0) {
                                sp.style.paddingLeft = $(bibi2).width() + 'px';
                            }
                        }

                        $WH.ae(d, sp);
                    }
                }
                else {
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    $WH.ae(d, icon);

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';

                    d.style.width = (26 * arr.length) + 'px';
                }
            }

            d2.className = 'clear';

            $WH.ae(td, d);
            $WH.ae(td, d2);

            return true;
        }
    },

    createSocketedIcons: function(sockets, td, gems, match, text) {
        var
            nMatch = 0,
            d      = $WH.ce('div'),
            d2     = $WH.ce('div');

        for (var i = 0, len = sockets.length; i < len; ++i) {
            var
                icon,
                gemId = gems[i];

                if (g_items && g_items[gemId]) {
                    icon = g_items.createIcon(gemId, 0);
                }
                else if ($WH.isset('g_gems') && g_gems && g_gems[gemId]) {
                    icon = Icon.create(g_gems[gemId].icon, 0, null, '?item=' + gemId);
                }
                else {
                    icon = Icon.create(null, 0, null, 'javascript:;');
                }

            icon.className += ' iconsmall-socket-' + g_file_gems[sockets[i]] + (!gems || !gemId ? '-empty': '');
            icon.style.cssFloat = icon.style.styleFloat = 'left';

            if (match && match[i]) {
                icon.insertBefore($WH.ce('var'), icon.childNodes[1]);
                ++nMatch;
            }

            $WH.ae(d, icon);
        }

        d.style.margin = '0 auto';
        d.style.textAlign = 'left';

        d.style.width = (26 * sockets.length) + 'px';
        d2.className = 'clear';

        $WH.ae(td, d);
        $WH.ae(td, d2);

        if (text && nMatch == sockets.length) {
            d = $WH.ce('div');
            d.style.paddingTop = '4px';
            $WH.ae(d, $WH.ct(text));
            $WH.ae(td, d);
        }
    },

    getItemType: function(itemClass, itemSubclass, itemSubsubclass) {
        if (itemSubsubclass != null && g_item_subsubclasses[itemClass] != null && g_item_subsubclasses[itemClass][itemSubclass] != null) {
            return {
                url: '?items=' + itemClass + '.' + itemSubclass + '.' + itemSubsubclass,
                text: g_item_subsubclasses[itemClass][itemSubclass][itemSubsubclass]
            };
        }
        else if (itemSubclass != null &&g_item_subclasses[itemClass] != null) {
            return {
                url: '?items=' + itemClass + '.' + itemSubclass,
                text: g_item_subclasses[itemClass][itemSubclass]
            };
        }
        else {
            return {
                url: '?items=' + itemClass,
                text: g_item_classes[itemClass]
            };
        }
    },

    getQuestCategory: function(category) {
        return g_quest_sorts[category];
    },

    getQuestReputation: function(faction, quest) {
        if (quest.reprewards) {
            for (var c = 0, a = quest.reprewards.length; c < a; ++c) {
                if (quest.reprewards[c][0] == faction) {
                    return quest.reprewards[c][1];
                }
            }
        }
    },

    getFactionCategory: function(category, category2) {
        if (category) {
            return g_faction_categories[category];
        }
        else {
            return g_faction_categories[category2];
        }
    },

    getEventNextDates: function(startDate, endDate, recurrence, fromWhen) {
        if (typeof startDate != 'string' || typeof endDate != 'string') {
            return [null, null];
        }

        startDate = new Date(startDate.replace(/-/g, '/'));
        endDate   = new Date(endDate.replace(/-/g, '/'));

        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
            return [null, null];
        }

        if (fromWhen == null) {
            fromWhen = g_serverTime;
        }

        var offset = 0;
        if (recurrence == -1) { // Once by month using day of the week of startDate
            var nextEvent = new Date(fromWhen.getFullYear(), fromWhen.getMonth(), 1, startDate.getHours(), startDate.getMinutes(), startDate.getSeconds()); // counts as next until it ends
            for (var i = 0; i < 2; ++i) {
                nextEvent.setDate(1);
                nextEvent.setMonth(nextEvent.getMonth() + i); // + 0 = try current month, else try next month
                var day = nextEvent.getDay();
                var tolerance = 1;
                if (nextEvent.getYear() == 2009) {
                    tolerance = 0;
                }
                if (day > tolerance) {
                    nextEvent.setDate(nextEvent.getDate() + (7 - day)); // first sunday
                }

                var eventEnd = new Date(nextEvent);
                eventEnd.setDate(eventEnd.getDate() + (7 - tolerance)); // 2010, length of 6. tolerance is 1 if it isnt 2009
                if (fromWhen.getTime() < eventEnd.getTime()) {
                    break; // event hasnt ended yet, so this is still the current one
                }
            }

            offset = nextEvent.getTime() - startDate.getTime();
        }
        else if (recurrence > 0) {
            recurrence *= 1000; // sec -> ms
            offset = Math.ceil((fromWhen.getTime() - endDate.getTime()) / recurrence) * recurrence;
        }

        startDate.setTime(startDate.getTime() + offset);
        endDate.setTime(endDate.getTime() + offset);

        return [startDate, endDate];
    },

    createTextRange: function(min, max) {
        min |= 0;
        max |= 0;
        if (min > 1 || max > 1) {
            if (min != max && max > 0) {
                return min + '-' + max;
            }
            else {
                return min + '';
            }
        }

        return null;
    },

    coReport: function(d, b, f) {
        if (!g_user.id || !g_report_reasons[f]) {
            return
        }
        var a = "";
        if (f == 4 || f == 7) {
            a = prompt(LANG.prompt_details, "")
        } else {
            if (d == 2) {
                if (!confirm((f == 5 ? LANG.confirm_report3: LANG.confirm_report4))) {
                    return
                }
            } else {
                if (!confirm($WH.sprintf((d == 0 ? LANG.confirm_report: LANG.confirm_report2), g_report_reasons[f]))) {
                    return
                }
            }
        }
        if (a != null) {
            var e = "?report&type=" + d + "&typeid=" + b + "&reason=" + f;
            if (a) {
                e += "&reasonmore=" + $WH.urlencode(a)
            }
            new Ajax(e);
            var c = $WH.ce("span");
            $WH.ae(c, $WH.ct(LANG.lvcomment_reported));
            this.parentNode.replaceChild(c, this)
        }
    },
    coReportClick: function(b, a, c) {
        this.menu = [
            [2, g_report_reasons[2], Listview.funcBox.coReport.bind(this, a, b.id, 2)],
            [1, g_report_reasons[1], Listview.funcBox.coReport.bind(this, a, b.id, 1)],
            [3, g_report_reasons[3], Listview.funcBox.coReport.bind(this, a, b.id, 3)],
            [4, g_report_reasons[4], Listview.funcBox.coReport.bind(this, a, b.id, 4)]
        ];
        if (a == 1 && b.op && typeof g_pageInfo != "undefined" && !g_pageInfo.sticky) {
            this.menu.splice(3, 0, [0, g_report_reasons[0], Listview.funcBox.coReport.bind(this, a, b.id, 0)])
        }
        if (a == 1 && g_users[b.user].avatar == 2) {
            this.menu.push([5, g_report_reasons[5], Listview.funcBox.coReport.bind(this, 2, g_users[b.user].avatarmore, 5)])
        } (Menu.showAtCursor.bind(this, c))()
    },
    coGetColor: function(c, a, d) {
        switch (a) {
        case -1 :
            var b = null;
            if (!d) {

                b = c.divPost.childNodes[1].className.match(/comment-([a-z]+)/);
            } else {
                b = c.divBody[0].className.match(/comment-([a-z]+)/)
            }
            if (b != null) {
                return " comment-" + b[1]
            }
            break;
        case 3:
        case 4:
            if (c.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU)) {
                return " comment-blue"
            }
            if (c.roles & U_GROUP_GREEN_TEXT) {
                return " comment-green"
            } else {
                if (c.roles & U_GROUP_VIP) {
                    return " comment-gold"
                }
            }
            break
        }
        if (c.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU)) {
            return " comment-blue"
        } else {
            if (c.rating >= 10) {
                return " comment-green"
            } else {
                if (c.rating < 0) {
                    return " comment-bt"
                }
            }
        }
        return ""
    },
    coToggleVis: function(b) {
        var c = g_toggleDisplay(b.divBody);
        this.firstChild.nodeValue = (c ? LANG.lvcomment_hide: LANG.lvcomment_show);
        b.__div.className = $WH.trim(b.__div.className.replace("comment-collapsed", "")) + (c ? "": " comment-collapsed");
        var a = b.divHeader.firstChild.lastChild;
        if (b.ratable) {
            a.style.display = ""
        } else {
            if (b.deleted || b.purged) {
                a.style.fontWeight = "normal";
                a.className = "q10";
                a.innerHTML = (b.deleted ? LANG.lvcomment_deleted: LANG.lvcomment_purged);
                a.style.display = ""
            }
        }
        g_toggleDisplay(b.divLinks);
        if (b.lastEdit != null) {
            g_toggleDisplay(b.divLastEdit)
        }
    },
    coDisplayRating: function(d, c) {
        if (typeof(d._ratingMode) == "undefined") {
            d._ratingMode = 0
        }
        if (typeof(Listview._ratings) == "undefined") {
            Listview._ratings = {}
        }
        var a = c;
        var e = d._ratingMode;
        if (e == 0) {
            if (d.rating < 0) {
                a.innerHTML = d.rating
            } else {
                a.innerHTML = "+" + d.rating
            }
        }
        if (e == 1) {
            if (Listview._ratings[d.id] !== undefined) {
                var b = Listview._ratings[d.id];
                a.innerHTML = "+" + b.up + " / -" + b.down
            } else {
                new Ajax("?comment=rating&id=" + d.id, {
                    method: "get",
                    onSuccess: function(i, g) {
                        var f = JSON.parse(g.responseText);
                        if (f.success) {
                            Listview._ratings[i] = f;
                            this.innerHTML = "+" + f.up + " / -" + f.down
                        } else {
                            this.innerHTML = "Error!"
                        }
                    }.bind(a, d.id)
                });
                a.innerHTML = '<img src="' + g_staticUrl + '/template/images/ajax.gif" />';
            }
        }
    },
    coToggleRating: function(b, a) {
        if (typeof(b._ratingMode) == "undefined") {
            b._ratingMode = 0
        }
        if (++b._ratingMode > 1) {
            b._ratingMode = 0
        }
        Listview.funcBox.coDisplayRating(b, a)
    },
    coRate: function(e, a) {
        if (a == 0) {
            var c = 5;
            if (g_user.roles & U_GROUP_ADMIN) {
                c = 25
            } else {
                if (g_user.roles & U_GROUP_BUREAU) {
                    c = 15
                }
            }
            var d = prompt($WH.sprintf(LANG.prompt_customrating, c, c), 0);
            if (d == null) {
                return
            } else {
                d |= 0;
                if (d != 0 && Math.abs(d) <= c) {
                    a = d
                }
            }
            if (a == 0) {
                return
            }
        } else {
            if (g_user.roles & U_GROUP_COMMENTS_MODERATOR) {
                a *= 5
            }
        }
        e.rating += a;
        e.raters.push([g_user.id, a]);
        var b = e.divHeader.firstChild;
        $WH.Tooltip.hide();
        b = b.childNodes[b.childNodes.length - 3];
        var f = $WH.ge("commentrating" + e.id);
        Listview.funcBox.coDisplayRating(e, f);
        $WH.de(b.nextSibling);
        $WH.de(b.nextSibling);
        new Ajax("?comment=rate&id=" + e.id + "&rating=" + a, {
            method: "get",
            onSuccess: function(e) {
                if (e.responseText == "0") {} else {
                    if (e.responseText == "1") {
                        b.innerHTML = LANG.tooltip_banned_rating;
                    } else {
                        if (e.responseText == "3") {
                            b.innerHTML = LANG.tooltip_too_many_votes;
                        } else {
                            b.innerHTML = LANG.genericerror;
                        }
                    }
                }
            }
        });
    },
    coDelete: function(a) {
        if (a.purged) {
            alert(LANG.message_cantdeletecomment)
        } else {
            if (confirm(LANG.confirm_deletecomment)) {
                new Ajax("?comment=delete&id=" + a.id);
                this.deleteRows([a])
            }
        }
    },
    coDetach: function(a) {
        if (a.replyTo == 0) {
            alert(LANG.message_cantdetachcomment)
        } else {
            if (confirm(LANG.confirm_detachcomment)) {
                new Ajax("?comment=detach&id=" + a.id);
                a.replyTo = 0;
                alert(LANG.message_commentdetached)
            }
        }
    },
    coEdit: function(g, e, c) {
        if (!c) {
            g.divBody.style.display = "none";
            g.divResponse.style.display = "none";
            g.divLinks.firstChild.style.display = "none"
        } else {
            g.divBody.hide();
            g.divResponse.hide()
        }
        var f = $WH.ce("div");
        f.className = "comment-edit";
        g.divEdit = f;
        if (e == -1) {
            if (g_users[g.user] != null) {
                g.roles = g_users[g.user].roles
            }
        }
        var a = Listview.funcBox.coEditAppend(f, g, e, c);
        var b = $WH.ce("div");
        b.className = "comment-edit-buttons";
        var d = $WH.ce("input");
        d.type = "button";
        d.value = LANG.compose_save;
        d.onclick = Listview.funcBox.coEditButton.bind(d, g, true, e, c);
        $WH.ae(b, d);
        $WH.ae(b, $WH.ct(" "));
        d = $WH.ce("input");
        d.type = "button";
        d.value = LANG.compose_cancel;
        d.onclick = Listview.funcBox.coEditButton.bind(d, g, false, e, c);
        $WH.ae(b, d);
        $WH.ae(f, b);
        var c = f;
        if ($WH.Browser.ie6) {
            c = $WH.ce("div");
            c.style.width = "99%";
            $WH.ae(c, f)
        }
        $WH.ae(g.divBody.parentNode, f)
        a.focus()
    },
    coEditAppend: function(m, b, l, X, x) {
        var f = Listview.funcBox.coGetCharLimit(l);
        if (l == 1 || l == 3 || l == 4) {
            b.user = g_user.name;
            b.roles = g_user.roles;
            b.rating = 1
        } else {
            if (l == 2) {
                b.roles = g_user.roles;
                b.rating = 1
            }
        }
        if (x) {
            b.roles &= ~U_GROUP_PENDING
        }
        if (l == -1 || l == 0) {
            var j = $WH.ce("div");
            j.className = "comment-edit-modes";
            $WH.ae(j, $WH.ct(LANG.compose_mode));
            var p = $WH.ce("a");
            p.className = "selected";
            p.onclick = Listview.funcBox.coModeLink.bind(p, 1, l, b);
            p.href = "javascript:;";
            $WH.ae(p, $WH.ct(LANG.compose_edit));
            $WH.ae(j, p);
            $WH.ae(j, $WH.ct("|"));
            var w = $WH.ce("a");
            w.onclick = Listview.funcBox.coModeLink.bind(w, 2, l, b);
            w.href = "javascript:;";
            $WH.ae(w, $WH.ct(LANG.compose_preview));
            $WH.ae(j, w);
            $WH.ae(m, j)
        }
        var a = $WH.ce("div");
        a.style.display = "none";
        a.className = "comment-body" + Listview.funcBox.coGetColor(b, l);
        $WH.ae(m, a);
        var h = $WH.ce("div");
        h.className = "comment-edit-body";
        var e = $WH.ce("div");
        e.className = "toolbar";
        e.style.cssFloat = "left";
        var i = $WH.ce("div");
        i.className = "menu-buttons";
        i.style.cssFloat = "left";
        var g = $WH.ce("textarea");
        g.className = "comment-editbox";
        g.rows = 10;
        g.style.clear = "left";
        g.value = b.body;
        switch (l) {
        case 1:
            g.name = "commentbody";
            break;
        case 2:
            g.name = "desc";
            g.originalValue = b.body;
            break;
        case 3:
            g.name = "body";
            break;
        case 4:
            g.name = "sig";
            g.originalValue = b.body;
            g.rows = ($WH.Browser.firefox ? 2 : 3);
            g.style.height = "auto";
            break
        }
        if (l != -1 && l != 0) {
            var d = $WH.ce("h3"),
            y = $WH.ce("a"),
            v = $WH.ce("div"),
            u = $WH.ce("div");
            var c = Listview.funcBox.coLivePreview.bind(g, b, l, v);
            if (b.body) {
                y.className = "disclosure-off";
                v.style.display = "none"
            } else {
                y.className = "disclosure-on"
            }
            $WH.ae(y, $WH.ct(LANG.compose_livepreview));
            $WH.ae(d, y);
            y.href = "javascript:;";
            y.onclick = function() {
                c(1);
                y.className = "disclosure-" + (g_toggleDisplay(v) ? "on": "off")
            };
            $WH.ns(y);
            d.className = "first";
            u.className = "pad";
            $WH.ae(a, d);
            $WH.ae(a, v);
            $WH.ae(a, u);
            g_onAfterTyping(g, c, 50);
            $WH.aE(g, "focus", function() {
                c();
                a.style.display = "";
                if (l != 4) {
                    g.style.height = "22em"
                }
            })
        } else {
            if (l != 4) {
                $WH.aE(g, "focus", function() {
                    g.style.height = "22em"
                })
            }
        }
        var t = [{
            id: "b",
            title: LANG.markup_b,
            pre: "[b]",
            post: "[/b]"
        },
        {
            id: "i",
            title: LANG.markup_i,
            pre: "[i]",
            post: "[/i]"
        },
        {
            id: "u",
            title: LANG.markup_u,
            pre: "[u]",
            post: "[/u]"
        },
        {
            id: "s",
            title: LANG.markup_s,
            pre: "[s]",
            post: "[/s]"
        },
        {
            id: "small",
            title: LANG.markup_small,
            pre: "[small]",
            post: "[/small]"
        },
        {
            id: "url",
            title: LANG.markup_url,
            onclick: function() {
                var i = prompt(LANG.prompt_linkurl, "http://");
                if (i) {
                    g_insertTag(g, "[url=" + i + "]", "[/url]")
                }
            }
        },
        {
            id: "quote",
            title: LANG.markup_quote,
            pre: "[quote]",
            post: "[/quote]"
        },
        {
            id: "code",
            title: LANG.markup_code,
            pre: "[code]",
            post: "[/code]"
        },
        {
            id: "ul",
            title: LANG.markup_ul,
            pre: "[ul]\n[li]",
            post: "[/li]\n[/ul]",
            rep: function(i) {
                return i.replace(/\n/g, "[/li]\n[li]")
            }
        },
        {
            id: "ol",
            title: LANG.markup_ol,
            pre: "[ol]\n[li]",
            post: "[/li]\n[/ol]",
            rep: function(i) {
                return i.replace(/\n/g, "[/li]\n[li]")
            }
        },
        {
            id: "li",
            title: LANG.markup_li,
            pre: "[li]",
            post: "[/li]"
        }];
        if (!X) {
            for (var q = 0, r = t.length; q < r; ++q) {
                var k = t[q];
                if (l == 4 && k.id == "quote") {
                    break
                }
                if ((g_user.roles & U_GROUP_PENDING) && k.nopending) {
                    continue
                }
                var o = $WH.ce("button");
                o.setAttribute("type", "button");
                o.title = k.title;
                if (k.onclick != null) {
                    o.onclick = k.onclick
                } else {
                    o.onclick = g_insertTag.bind(0, g, k.pre, k.post, k.rep)
                }

                var z = $WH.ce("img");
                z.src = "template/images/pixel.gif";
                z.className = "toolbar-" + k.id;
                $WH.ae(o, z);
                $WH.ae(e, o)
            }
        } else {
            for (var B = 0, C = t.length; B < C; ++B) {
                var q = t[B];
                if ((g_user.rolls & U_GROUP_PENDING) && q.nopending) {
                    continue
                }
                var H = "tb-" + q.id;
                var V = $WH.ce('button');
                V.onclick = function(i, L) {
                    L.preventDefault();
                    (i.onclick != null ? i.onclick: g_insertTag.bind(0, g, i.pre, i.post, i.rep))()
                };
                V.bind(null, q);
                V.className = H;
                V.title = q.title;
                V[0].setAttribute("type", "button");
                $WH.ae(V, $WH.ce('ins'));
                $WH.ae(e, V);
            }
            e.className += " formatting button sm";
        }
        var r = function(L, i) {
            var M = prompt($WH.sprintf(LANG.markup_prompt, L), "");
            if (M != null) {
                g_insertTag(g, "[" + i + "=" + (parseInt(M) || 0) + "]", "")
            }
        };
        var A = [[0, LANG.markup_links, , [
                [9, LANG.types[10][0] + "...", r.bind(null, LANG.types[10][1], "achievement")],
                [11, LANG.types[13][0] + "...", r.bind(null, LANG.types[13][1], "class")],
                [7, LANG.types[8][0] + "...", r.bind(null, LANG.types[8][1], "faction")],
                [0, LANG.types[3][0] + "...", r.bind(null, LANG.types[3][1], "item")],
                [1, LANG.types[4][0] + "...", r.bind(null, LANG.types[4][1], "itemset")],
                [2, LANG.types[1][0] + "...", r.bind(null, LANG.types[1][1], "npc")],
                [3, LANG.types[2][0] + "...", r.bind(null, LANG.types[2][1], "object")],
                [8, LANG.types[9][0] + "...", r.bind(null, LANG.types[9][1], "pet")],
                [4, LANG.types[5][0] + "...", r.bind(null, LANG.types[5][1], "quest")],
                [12, LANG.types[14][0] + "...", r.bind(null, LANG.types[14][1], "race")],
                [13, LANG.types[15][0] + "...", r.bind(null, LANG.types[15][1], "skill")],
                [5, LANG.types[6][0] + "...", r.bind(null, LANG.types[6][1], "spell")],
                [6, LANG.types[7][0] + "...", r.bind(null, LANG.types[7][1], "zone")]]
        ]];
        var di = $WH.ce('div');
        $WH.ae(di, e);
        $WH.ae(di, i);
        $WH.ae(h, di);
          $WH.ae(h, $WH.ce("div"));
        $WH.ae(h, g);
        $WH.ae(h, $WH.ce("br"));
        Menu.addButtons(i, A);
        if (l == 4) {
            $WH.ae(h, $WH.ct($WH.sprintf(LANG.compose_limit2, f, 3)))
        } else {
            $WH.ae(h, $WH.ct($WH.sprintf(LANG.compose_limit, f)))
        }
        var A = $WH.ce('span');
        A.className = "comment-remaining";
        $WH.ae(A, $WH.ct($WH.sprintf(LANG.compose_remaining, l - b.body.length)));
        $WH.ae(h, A);
        g.onkeyup = Listview.funcBox.coUpdateCharLimit.bind(0, g, A, f);
        g.onkeydown = Listview.funcBox.coUpdateCharLimit.bind(0, g, A, f);
        if ((l == -1 || l == 0) && g_user.roles & U_GROUP_MODERATOR) {
            var B = $WH.ce("div");
            B.classname = "pad";
            var W = $WH.ce("div");
            $WH.ae(W, $WH.ct((g_user.roles & U_GROUP_ADMIN ? "Admin": "Moderator") + " response"));
            var p = $WH.ce("textarea");
            p.value = b.response;
            p.rows = 3;
            p.style.height = "6em";
            $WH.ae(h, B);
            $WH.ae(h, w);
            $WH.ae(h, p)
        }
        $WH.ae(m, h);
        $WH.ae(m, $WH.ce('div'));
        $WH.ae(m, a);

        return g
    },
/*
    coEditAppend: function(m, b, l) {
        var f = Listview.funcBox.coGetCharLimit(l);
        if (l == 1 || l == 3 || l == 4) {
            b.user = g_user.name;
            b.roles = g_user.roles;
            b.rating = 1
        } else {
            if (l == 2) {
                b.roles = g_user.roles;
                b.rating = 1
            }
        }
        if (l == -1 || l == 0) {
            var j = $WH.ce("div");
            j.className = "comment-edit-modes";
            $WH.ae(j, $WH.ct(LANG.compose_mode));
            var p = $WH.ce("a");
            p.className = "selected";
            p.onclick = Listview.funcBox.coModeLink.bind(p, 1, l, b);
            p.href = "javascript:;";
            $WH.ae(p, $WH.ct(LANG.compose_edit));
            $WH.ae(j, p);
            $WH.ae(j, $WH.ct("|"));
            var w = $WH.ce("a");
            w.onclick = Listview.funcBox.coModeLink.bind(w, 2, l, b);
            w.href = "javascript:;";
            $WH.ae(w, $WH.ct(LANG.compose_preview));
            $WH.ae(j, w);
            $WH.ae(m, j)
        }
        var a = $WH.ce("div");
        a.style.display = "none";
        a.className = "comment-body" + Listview.funcBox.coGetColor(b, l);
        $WH.ae(m, a);
        var h = $WH.ce("div");
        h.className = "comment-edit-body";
        var e = $WH.ce("div");
        e.className = "toolbar";
        var g = $WH.ce("textarea");
        g.className = "comment-editbox";
        g.rows = 10;
        g.value = b.body;
        switch (l) {
        case 1:
            g.name = "commentbody";
            g.onfocus = g_revealCaptcha;
            break;
        case 2:
            g.name = "desc";
            g.originalValue = b.body;
            break;
        case 3:
            g.name = "body";
            g.onfocus = g_revealCaptcha;
            break;
        case 4:
            g.name = "sig";
            g.originalValue = b.body;
            g.rows = ($WH.Browser(gecko ? 2 : 3);
            g.style.height = "auto";
            break
        }
        if (l != -1 && l != 0) {
            var d = $WH.ce("h3"),
            y = $WH.ce("a"),
            v = $WH.ce("div"),
            u = $WH.ce("div");
            var c = Listview.funcBox.coLivePreview.bind(g, b, l, v);
            if (b.body) {
                y.className = "disclosure-off";
                v.style.display = "none"
            } else {
                y.className = "disclosure-on"
            }
            $WH.ae(y, $WH.ct(LANG.compose_livepreview));
            $WH.ae(d, y);
            y.href = "javascript:;";
            y.onclick = function() {
                c(1);
                y.className = "disclosure-" + (g_toggleDisplay(v) ? "on": "off")
            };
            $WH.ns(y);
            d.className = "first";
            u.className = "pad";
            $WH.ae(a, d);
            $WH.ae(a, v);
            $WH.ae(a, u);
            g_onAfterTyping(g, c, 50);
            $WH.aE(g, "focus", function() {
                c();
                a.style.display = "";
                if (l != 4) {
                    g.style.height = "22em"
                }
            })
        } else {
            if (l != 4) {
                $WH.aE(g, "focus", function() {
                    g.style.height = "22em"
                })
            }
        }
        var t = [{
            id: "b",
            title: LANG.markup_b,
            pre: "[b]",
            post: "[/b]"
        },
        {
            id: "i",
            title: LANG.markup_i,
            pre: "[i]",
            post: "[/i]"
        },
        {
            id: "u",
            title: LANG.markup_u,
            pre: "[u]",
            post: "[/u]"
        },
        {
            id: "s",
            title: LANG.markup_s,
            pre: "[s]",
            post: "[/s]"
        },
        {
            id: "small",
            title: LANG.markup_small,
            pre: "[small]",
            post: "[/small]"
        },
        {
            id: "url",
            title: LANG.markup_url,
            onclick: function() {
                var i = prompt(LANG.prompt_linkurl, "http://");
                if (i) {
                    g_insertTag(g, "[url=" + i + "]", "[/url]")
                }
            }
        },
        {
            id: "quote",
            title: LANG.markup_quote,
            pre: "[quote]",
            post: "[/quote]"
        },
        {
            id: "code",
            title: LANG.markup_code,
            pre: "[code]",
            post: "[/code]"
        },
        {
            id: "ul",
            title: LANG.markup_ul,
            pre: "[ul]\n[li]",
            post: "[/li]\n[/ul]",
            rep: function(i) {
                return i.replace(/\n/g, "[/li]\n[li]")
            }
        },
        {
            id: "ol",
            title: LANG.markup_ol,
            pre: "[ol]\n[li]",
            post: "[/li]\n[/ol]",
            rep: function(i) {
                return i.replace(/\n/g, "[/li]\n[li]")
            }
        },
        {
            id: "li",
            title: LANG.markup_li,
            pre: "[li]",
            post: "[/li]"
        }];
        for (var q = 0, r = t.length; q < r; ++q) {
            var k = t[q];
            if (l == 4 && k.id == "quote") {
                break
            }
            var o = $WH.ce("button");
            var z = $WH.ce("img");
            o.setAttribute("type", "button");
            o.title = k.title;
            if (k.onclick != null) {
                o.onclick = k.onclick
            } else {
                o.onclick = g_insertTag.bind(0, g, k.pre, k.post, k.rep)
            }
            z.src = "template/images/pixel.gif";
            z.className = "toolbar-" + k.id;
            $WH.ae(o, z);
            $WH.ae(e, o)
        }
        $WH.ae(h, e);
        $WH.ae(h, g);
        $WH.ae(h, $WH.ce("br"));
        if (l == 4) {
            $WH.ae(h, $WH.ct($WH.sprintf(LANG.compose_limit2, f, 3)))
        } else {
            $WH.ae(h, $WH.ct($WH.sprintf(LANG.compose_limit, f)))
        }
        $WH.ae(m, h);
        return g
    },
*/
    coLivePreview: function(f, e, a, b) {
        if (b != 1 && a.style.display == "none") {
            return
        }
        var c = this,
        i = Listview.funcBox.coGetCharLimit(e),
        g = (c.value.length > i ? c.value.substring(0, i) : c.value);
        if (e == 4) {
            var h;
            if ((h = g.indexOf("\n")) != -1 && (h = g.indexOf("\n", h + 1)) != -1 && (h = g.indexOf("\n", h + 1)) != -1) {
                g = g.substring(0, h)
            }
        }
        var d = Markup.toHtml(g, {
            mode: Markup.MODE_COMMENT,
            roles: f.roles
        });
        if (d) {
            a.innerHTML = d
        } else {
            a.innerHTML = '<span class="q6">...</span>'
        }
    },
    coEditButton: function(f, d, e, k) {
        if (d) {
            var a = $WH.gE(f.divEdit, "textarea");
            var g = a[0];
            if (!Listview.funcBox.coValidate(a, e)) {
                return
            }
            if (g.value != f.body || (a[1] && a[1].value != f.response)) {
                var c = 0;
                if (f.lastEdit != null) {
                    c = f.lastEdit[1]
                }++c;
                f.lastEdit = [g_serverTime, c, g_user.name];
                Listview.funcBox.coUpdateLastEdit(f);
                var b = Listview.funcBox.coGetCharLimit(e);
                var i = Markup.toHtml((g.value.length > b ? g.value.substring(0, b) : g.value), {
                    mode: Markup.MODE_COMMENT,
                    roles: f.roles
                });
                var h = ((a[1] && a[1].value.length > 0) ? Markup.toHtml("[div][/div][wowheadresponse=" + g_user.name + " roles=" + g_user.roles + "]" + a[1].value + "[/wowheadresponse]", {
                    mode: Markup.MODE_COMMENT,
                    roles: g_user.roles
                }) : "");
                if (!k) {
                    f.divBody.innerHTML = i;
                    f.divResponse.innerHTML = h
                } else {
                    f.divBody.html(i);
                    f.divResponse.html(h)
                }
                f.body = g.value;
                if (g_user.roles & U_GROUP_MODERATOR && e[1]) {
                    f.response = e[1].value
                }
                var j = "body=" + $WH.urlencode(f.body);
                if (f.response !== undefined) {
                    j += "&response=" + $WH.urlencode(f.response)
                }
                if (e == -1) {
                    new Ajax("?forums=editpost&id=" + f.id, {
                        method: "POST",
                        params: j
                    })
                } else {
                    new Ajax("?comment=edit&id=" + f.id, {
                        method: "POST",
                        params: j
                    })
                }
            }
        }
        if (!k) {
            f.divBody.style.display = "";
            f.divResponse.style.display = "";
            f.divLinks.firstChild.style.display = "";
        } else {
            f.divBody.show();
            f.divResponse.show()
        }
        $WH.de(f.divEdit);
        f.divEdit = null
    },
    coGetCharLimit: function(a) {
        if (a == 2) {
            return 7500
        }
        if (a == 4) {
            return 250
        }
        if (g_user.roles & U_GROUP_STAFF) {
            return 16000000
        }
        var b = 1;
        if (g_user.premium) {
            b = 3
        }
        switch (a) {
        case 0:
        case 1:
            return 7500 * b;
        case -1 : case 3:
            return 15000 * b
        }
    },
    coUpdateCharLimit: function(a, b, c) {
        var d = a.value;
        if (d.length > c) {
            a.value = d.substring(0, c);
        } else {
            b.innerHTML = (" " + $WH.sprintf(LANG.compose_remaining, c - d.length))
            b.className.replace(/(?:^|\s)q10(?!\S)/g , '');
            if (d.length == c) {
                b.className += " q10";
            }
        }
    },
    coModeLink: function(e, b, f) {
        var j = Listview.funcBox.coGetCharLimit(b);
        var c = Markup.MODE_COMMENT;
        $WH.array_walk($WH.gE(this.parentNode, "a"), function(k) {
            k.className = ""
        });
        this.className = "selected";
        var d = $WH.gE(this.parentNode.parentNode, "textarea"),
        k = d[0],
        i = k,
        a = i.previousSibling;
        if (b == 4) {
            c = Markup.MODE_SIGNATURE
        }
        switch (e) {
        case 1:
            i.style.display = "";
            a.style.display = "none";
            a.previousSibling.style.display = "";
            i.focus();
            break;
        case 2:
            i.style.display = "none";
            var g = (k.value.length > j ? k.value.substring(0, j) : k.value);
            if (b == 4) {
                var h;
                if ((h = g.indexOf("\n")) != -1 && (h = g.indexOf("\n", h + 1)) != -1 && (h = g.indexOf("\n", h + 1)) != -1) {
                    g = g.substring(0, h)
                }
            }
            var l = Markup.toHtml(g, {
                mode: c,
                roles: f.roles
            });
            if (d[1] && d[1].value.length > 0) {
                l += Markup.toHtml("[div][/div][wowheadresponse=" + g_user.name + " roles=" + g_user.roles + "]" + f[1].value + "[/wowheadresponse]", {
                    mode: c,
                    roles: g_user.roles
                })
            }
            a.innerHTML = l;
            a.style.display = "";
            a.previousSibling.style.display = "none";
            break
        }
    },
    coReply: function(b) {
        document.forms.addcomment.elements.replyto.value = b.replyTo;
        var a = $WH.ge("replybox-generic");
        $WH.gE(a, "span")[0].innerHTML = b.user;
        a.style.display = "";
        co_addYourComment()
    },
    coValidate: function(a, c) {
        c |= 0;
        if (c == 1 || c == -1) {
            if ($WH.trim(a.value).length < 1) {
                alert(LANG.message_forumposttooshort);
                return false
            }
        } else {
            if ($WH.trim(a.value).length < 10) {
                alert(LANG.message_commenttooshort);
                return false
            }
        }
        var b = Listview.funcBox.coGetCharLimit(c);
        if (a.value.length > b) {
            if (!confirm($WH.sprintf(c == 1 ? LANG.confirm_forumposttoolong: LANG.confirm_commenttoolong, b, a.value.substring(b - 30, b)))) {
                return false
            }
        }
        return true
    },
    coCustomRatingOver: function(a) {
        $WH.Tooltip.showAtCursor(a, LANG.tooltip_customrating, 0, 0, "q")
    },
    coPlusRatingOver: function(a) {
        $WH.Tooltip.showAtCursor(a, LANG.tooltip_uprate, 0, 0, "q2")
    },
    coMinusRatingOver: function(a) {
        $WH.Tooltip.showAtCursor(a, LANG.tooltip_downrate, 0, 0, "q10")
    },
    coSortDate: function(a) {
        a.nextSibling.nextSibling.className = "";
        a.className = "selected";
        this.mainDiv.className += " listview-aci";
        this.setSort([1], true, false)
        $WH.sc("temp_comment_sort", 1)
    },
    coSortHighestRatedFirst: function(a) {
        a.previousSibling.previousSibling.className = "";
        a.className = "selected";
        this.mainDiv.className = this.mainDiv.className.replace("listview-aci", "");
        this.setSort([ - 3, 2], true, false)
        $WH.sc("temp_comment_sort", 2)
    },
    coFilterByPatchVersion: function(a) {
        this.minPatchVersion = a.value;
        this.refreshRows()
    },
    coUpdateLastEdit: function(f) {
        var b = f.divLastEdit;
        if (!b) {
            return
        }
        if (f.lastEdit != null) {
            var e = f.lastEdit;
            b.childNodes[1].firstChild.nodeValue = e[2];
            b.childNodes[1].href = "?user=" + e[2];
            var c = new Date(e[0]);
            var d = (g_serverTime - c) / 1000;
            if (b.childNodes[3].firstChild) {
                $WH.de(b.childNodes[3].firstChild)
            }
            Listview.funcBox.coFormatDate(b.childNodes[3], d, c);
            var a = "";
            if (f.rating != null) {
                a += $WH.ct($WH.sprintf(LANG.lvcomment_patch, g_getPatchVersion(c)))
            }
            if (e[1] > 1) {
                a += LANG.dash + $WH.sprintf(LANG.lvcomment_nedits, e[1])
            }
            b.childNodes[4].nodeValue = a;
            b.style.display = ""
        } else {
            b.style.display = "none"
        }
    },
    coFormatDate: function(f, e, b, g, h) {
        var d;
        if (e < 2592000) {
            var a = $WH.sprintf(LANG.date_ago, g_formatTimeElapsed(e));
            var c = new Date();
            c.setTime(b.getTime() + (g_localTime - g_serverTime));
            f.style.cursor = "help";
            f.title = c.toLocaleString()
        } else {
            a = LANG.date_on + g_formatDateSimple(b, g)
        }
        if (h == 1) {
            a = a.substr(0, 1).toUpperCase() + a.substr(1)
        }
        d = $WH.ct(a);
        $WH.ae(f, d)
    },
    coFormatFileSize: function(c) {
        var b = -1;
        var a = "KMGTPEZY";
        while (c >= 1024 && b < 7) {
            c /= 1024; ++b
        }
        if (b < 0) {
            return c + " byte" + (c > 1 ? "s": "")
        } else {
            return c.toFixed(1) + " " + a[b] + "B"
        }
    },

    dateEventOver: function(date, event, e) {
        var dates = Listview.funcBox.getEventNextDates(event.startDate, event.endDate, event.rec || 0, date),
            buff  = '';

        if (dates[0] && dates[1]) {
            var
                t1 = new Date(event.startDate.replace(/-/g, '/')),
                t2 = new Date(event.endDate.replace(/-/g, '/')),
                first,
                last;

            t1.setFullYear(date.getFullYear(), date.getMonth(), date.getDate());
            t2.setFullYear(date.getFullYear(), date.getMonth(), date.getDate());

            if (date.getFullYear() == dates[0].getFullYear() && date.getMonth() == dates[0].getMonth() && date.getDate() == dates[0].getDate()) {
                first = true;
            }

            if (date.getFullYear() == dates[1].getFullYear() && date.getMonth() == dates[1].getMonth() && date.getDate() == dates[1].getDate()) {
                last = true;
            }

            if (first && last)
                buff = g_formatTimeSimple(t1, LANG.lvscreenshot_from, 1) + ' ' + g_formatTimeSimple(t2, LANG.date_to, 1);
            else if (first)
                buff = g_formatTimeSimple(t1, LANG.tab_starts);
            else if (last)
                buff = g_formatTimeSimple(t2, LANG.tab_ends);
            else
                buff = LANG.allday;
        }

        $WH.Tooltip.showAtCursor(e, '<span class="q1">' + event.name + '</span><br />' + buff, 0, 0, 'q');
    },

    ssCellOver: function() {
        this.className = 'screenshot-caption-over';
    },

    ssCellOut: function() {
        this.className = 'screenshot-caption';
    },

    ssCellClick: function(i, e) {
        e = $WH.$E(e);

        if (e.shiftKey || e.ctrlKey) {
            return;
        }

        var
            j = 0,
            el = e._target;

        while (el && j < 3) {
            if (el.nodeName == 'A') {
                return;
            }
            if (el.nodeName == 'IMG') {
                break;
            }
            el = el.parentNode;
        }

        ScreenshotViewer.show({
            screenshots: this.data,
            pos: i
        });
    },

    ssCreateCb: function(td, row) {
        if (row.__nochk) {
            return;
        }

        var div = $WH.ce('div');
        div.className = 'listview-cb';
        div.onclick   = Listview.cbCellClick;

        var cb = $WH.ce('input');
        cb.type = 'checkbox';
        cb.onclick = Listview.cbClick;
        $WH.ns(cb);

        if (row.__chk) {
            cb.checked = true;
        }

        row.__cb = cb;

        $WH.ae(div, cb);
        $WH.ae(td, div);
    },

    viCellClick: function(i, e) {
        e = $WH.$E(e);

        if (e.shiftKey || e.ctrlKey) {
            return;
        }

        var
            j = 0,
            el = e._target;

        while (el && j < 3) {
            if (el.nodeName == 'A') {
                return;
            }
            if (el.nodeName == 'IMG') {
                break;
            }
            el = el.parentNode;
        }

        VideoViewer.show({
            videos: this.data,
            pos: i
        })
    },

    moneyHonorOver: function(e) {
        $WH.Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_honorpoints + '</b>', 0, 0, 'q1');
    },

    moneyArenaOver: function(e) {
        $WH.Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_arenapoints + '</b>', 0, 0, 'q1');
    },

    moneyAchievementOver: function(e) {
        $WH.Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_achievementpoints + '</b>', 0, 0, 'q1');
    },

    moneyCurrencyOver: function(currencyId, count, e) {
        var buff = g_gatheredcurrencies[currencyId]['name_' + Locale.getName()];

        // justice / valor points handling removed

        $WH.Tooltip.showAtCursor(e, buff, 0, 0, 'q1');
    },

    appendMoney: function(g, a, f, m, j, c, l) {        // todo: understand and adapt
        var k, h = 0;
        if (a >= 10000) {
            h = 1;
            k = $WH.ce("span");
            k.className = "moneygold";
            $WH.ae(k, $WH.ct(Math.floor(a / 10000)));
            $WH.ae(g, k);
            a %= 10000
        }
        if (a >= 100) {
            if (h) {
                $WH.ae(g, $WH.ct(" "))
            } else {
                h = 1
            }
            k = $WH.ce("span");
            k.className = "moneysilver";
            $WH.ae(k, $WH.ct(Math.floor(a / 100)));
            $WH.ae(g, k);
            a %= 100
        }
        if (a >= 1 || f != null) {
            if (h) {
                $WH.ae(g, $WH.ct(" "))
            } else {
                h = 1
            }
            k = $WH.ce("span");
            k.className = "moneycopper";
            $WH.ae(k, $WH.ct(a));
            $WH.ae(g, k)
        }
        if (m != null && m != 0) {
            if (h) {
                $WH.ae(g, $WH.ct(" "))
            } else {
                h = 1
            }
            k = $WH.ce("span");
            k.className = "money" + (m < 0 ? "horde": "alliance") + " tip";
            k.onmouseover = Listview.funcBox.moneyHonorOver;
            k.onmousemove = $WH.Tooltip.cursorUpdate;
            k.onmouseout = $WH.Tooltip.hide;
            $WH.ae(k, $WH.ct($WH.number_format(Math.abs(m))));
            $WH.ae(g, k)
        }
        if (j >= 1) {
            if (h) {
                $WH.ae(g, $WH.ct(" "))
            } else {
                h = 1
            }
            k = $WH.ce("span");
            k.className = "moneyarena tip";
            k.onmouseover = Listview.funcBox.moneyArenaOver;
            k.onmousemove = $WH.Tooltip.cursorUpdate;
            k.onmouseout = $WH.Tooltip.hide;
            $WH.ae(k, $WH.ct($WH.number_format(j)));
            $WH.ae(g, k)
        }
        if (c != null) {
            for (var b = 0; b < c.length; ++b) {
                if (h) {
                    $WH.ae(g, $WH.ct(" "))
                } else {
                    h = 1
                }
                var o = c[b][0];
                var e = c[b][1];
                k = $WH.ce("a");
                k.href = "?item=" + o;
                k.className = "moneyitem";
                k.style.backgroundImage = "url(images/icons/tiny/" + g_items.getIcon(o).toLowerCase() + ".gif)";
                $WH.ae(k, $WH.ct(e));
                $WH.ae(g, k)
            }
        }
        if (l != null) {
            if (h) {
                $WH.ae(g, $WH.ct(" "))
            } else {
                h = 1
            }
            k = $WH.ce("span");
            k.className = "moneyachievement tip";
            k.onmouseover = Listview.funcBox.moneyAchievementOver;
            k.onmousemove = $WH.Tooltip.cursorUpdate;
            k.onmouseout = $WH.Tooltip.hide;
            $WH.ae(k, $WH.ct($WH.number_format(l)));
            $WH.ae(g, k)
        }
    },

    getUpperSource: function(source, sm) {
        switch (source) {
            case 2: // Drop
                if (sm.bd) {
                    return LANG.source_bossdrop;
                }
                if (sm.z) {
                    return LANG.source_zonedrop;
                }
                break;
            case 4: // Quest
                return LANG.source_quests;
            case 5: // Vendor
                return LANG.source_vendors;
        }

        return g_sources[source];
    },

    getLowerSource: function(source, sm, type) {
        switch (source) {
            case 3: // PvP
                if (sm.p && g_sources_pvp[sm.p]) {
                    return { text: g_sources_pvp[sm.p] };
                }
                break;
        }
        switch (type) {
            case 0: // None
            case 1: // NPC
            case 2: // Object
                if (sm.z) {
                    var res = {
                        url: '?zone=' + sm.z,
                        text: g_zones[sm.z]
                    };

                    if (sm.t && source == 5) {
                        res.pretext = LANG.lvitem_vendorin;
                    }

                    if (sm.dd && sm.dd != 99) {
                        if (sm.dd < 0) { // Dungeon
                            res.posttext = $WH.sprintf(LANG.lvitem_dd, "", (sm.dd < -1 ? LANG.lvitem_heroic : LANG.lvitem_normal));
                        }
                        else { // Raid
                            res.posttext = $WH.sprintf(LANG.lvitem_dd, (sm.dd & 1 ? LANG.lvitem_raid10 : LANG.lvitem_raid25), (sm.dd > 2 ? LANG.lvitem_heroic : LANG.lvitem_normal));
                        }
                    }

                    return res;
                }
                break;
            case 5: // Quest
                return {
                    url: '?quests=' + sm.c2 + '.' + sm.c,
                    text: Listview.funcBox.getQuestCategory(sm.c)
                };
                break;
            case 6: // Spell
                if (sm.c && sm.s) {
                    return {
                        url: '?spells=' + sm.c + '.' + sm.s,
                        text: g_spell_skills[sm.s]
                    };
                }
                else {
                    return {
                        url: '?spells=0',
                        text: '??'
                    };
                }
                break;
        }
    },

    getExpansionText: function(line) {
        var str = '';

        if (line.expansion == 1) {
            str += ' bc';
        }
        else if (line.expansion == 2) {
            str += ' wotlk wrath';
        }

        return str;
    }
};

Listview.templates = {
    faction: {
        sort: [1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(faction, td) {
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(faction);
                    $WH.ae(a, $WH.ct(faction.name));
                    if (faction.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(faction.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(td, sp);
                    }
                    else {
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(faction) {
                    var buff = faction.name + Listview.funcBox.getExpansionText(faction);

                    return buff;
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(item, td) {
                    if (item.side && item.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (item.side == 1 ? 'alliance-icon' : 'horde-icon');
                        g_addTooltip(sp, g_sides[item.side]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(item) {
                    if (item.side) {
                        return g_sides[item.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'standing',
                name: LANG.reputation,
                value: 'standing',
                compute: function(faction, td) {
                    td.style.padding = 0;
                    $WH.ae(td, g_createReputationBar(faction.standing));
                },
                hidden: 1
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '16%',
                compute: function(faction, td) {
                    if (faction.category2 != null) {
                        td.className = 'small q1';
                        var
                            a = $WH.ce('a'),
                            href = '?factions=' + faction.category2;

                            if (faction.category) {
                            href += '.' + faction.category;
                        }
                        a.href = href;
                        $WH.ae(a, $WH.ct(Listview.funcBox.getFactionCategory(faction.category, faction.category2)));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(faction) {
                    return Listview.funcBox.getFactionCategory(faction.category, faction.category2);
                },
                sortFunc: function(a, b, col) {
                    var _ = Listview.funcBox.getFactionCategory;
                    return $WH.strcmp(_(a.category, a.category2), _(b.category, b.category2));
                }
            }
        ],

        getItemLink: function(faction) {
            return '?faction=' + faction.id;
        }
    },

    item: {
        sort: [-2],
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(item, td, tr) {
                    if (item.upgraded) {
                        tr.className = 'upgraded';
                    }

                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    var
                        num = null,
                        qty = null;

                    if (item.stack != null) {
                        num = Listview.funcBox.createTextRange(item.stack[0], item.stack[1]);
                    }
                    if (item.avail != null) {
                        qty = item.avail;
                    }

                    if (item.id) {
                        $WH.ae(i, g_items.createIcon(item.id, (this.iconSize == null ? 1 : this.iconSize), num, qty));
                    }
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var a = $WH.ce('a');
                    a.className = 'q' + (7 - parseInt(item.name.charAt(0)));
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(item);

                    if (item.rel) {
                        Icon.getLink(i.firstChild).rel = item.rel;
                        a.rel = item.rel;
                    }

                    $WH.ae(a, $WH.ct(item.name.substring(1)));

                    var wrapper = $WH.ce('div');
                    $WH.ae(wrapper, a);

                    if (item.reqclass) {
                        var d = $WH.ce('div');
                        d.className = 'small2';

                        var classes = Listview.funcBox.assocBinFlags(item.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(d, $WH.ct(', '));
                            }
                            var a = $WH.ce('a');
                            a.href = '?class=' + classes[i];
                            a.className = 'c' + classes[i];
                            $WH.st(a, g_chr_classes[classes[i]]);
                            $WH.ae(d, a);
                        }

                        $WH.ae(wrapper, d);
                    }

                    if (typeof fi_nExtraCols == 'number' && fi_nExtraCols >= 5) {
                        if (item.source != null && item.source.length == 1) {
                            if (item.reqclass) {
                                $WH.ae(d, $WH.ct(LANG.dash));
                            }
                            else {
                                var d = $WH.ce('div');
                                d.className = 'small2';
                            }

                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;

                                var a = $WH.ce('a');
                                if (sm.q != null) {
                                    a.className = 'q' + sm.q;
                                }
                                else {
                                    a.className = 'q1';
                                }
                                a.href = '?' + g_types[sm.t] + '=' + sm.ti;

                                if (sm.n.length <= 30) {
                                    $WH.ae(a, $WH.ct(sm.n));
                                }
                                else {
                                    a.title = sm.n;
                                    $WH.ae(a, $WH.ct($WH.trim(sm.n.substr(0, 27)) + '...'));
                                }

                                $WH.ae(d, a);
                            }
                            else {
                                $WH.ae(d, $WH.ct(Listview.funcBox.getUpperSource(item.source[0], sm)));
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (ls != null) {
                                $WH.ae(d, $WH.ct(LANG.hyphen));

                                if (ls.pretext) {
                                    $WH.ae(d, $WH.ct(ls.pretext));
                                }

                                if (ls.url) {
                                    var a = $WH.ce('a');
                                    a.className = 'q1';
                                    a.href = ls.url;
                                    $WH.ae(a, $WH.ct(ls.text));
                                    $WH.ae(d, a);
                                }
                                else {
                                    $WH.ae(d, $WH.ct(ls.text));
                                }

                                if (ls.posttext) {
                                    $WH.ae(d, $WH.ct(ls.posttext));
                                }
                            }

                            $WH.ae(wrapper, d);
                        }
                    }

                    if (item.heroic || item.reqrace) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = d.style.bottom = '3px';

                        if (item.heroic) {
                            var s = $WH.ce('span');
                            s.className = 'q2';
                            $WH.ae(s, $WH.ct(LANG.lvitem_heroicitem));
                            $WH.ae(d, s);
                        }

                        if (item.reqrace) {
                            if ((item.reqrace & 1791) != 1101 && (item.reqrace & 1791) != 690) {
                                if (item.heroic) {
                                    $WH.ae(d, $WH.ce('br'));
                                    d.style.bottom = '-6px';
                                }

                                var races = Listview.funcBox.assocBinFlags(item.reqrace, g_chr_races);

                                for (var i = 0, len = races.length; i < len; ++i) {
                                    if (i > 0) {
                                        $WH.ae(d, $WH.ct(', '));
                                    }
                                    var a = $WH.ce('a');
                                    a.href = '?race=' + races[i];
                                    $WH.st(a, g_chr_races[races[i]]);
                                    $WH.ae(d, a);
                                }

                                d.className += ' q1';
                            }
                        }

                        $WH.ae(wrapper, d);
                    }

                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(item) {
                    var buff = item.name.substring(1);

                    if (item.heroic) {
                        buff += ' ' + LANG.lvitem_heroicitem;
                    }

                    if (item.reqrace) {
                        buff += ' ' + Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(item.reqrace, g_chr_races), g_chr_races);
                    }

                    if (item.reqclass) {
                        buff += ' ' + Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(item.reqclass, g_chr_classes), g_chr_classes);
                    }

                    if (typeof fi_nExtraCols == 'number' && fi_nExtraCols >= 5) {
                        if (item.source != null && item.source.length == 1) {
                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;
                                buff += ' ' + sm.n;
                            }
                            else {
                                buff += ' ' + Listview.funcBox.getUpperSource(item.source[0], sm);
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (ls != null) {
                                if (ls.pretext) {
                                    buff += ' ' + ls.pretext;
                                }

                                buff += ' ' + ls.text;

                                if (ls.posttext) {
                                    buff += ' ' + ls.posttext;
                                }
                            }
                        }
                    }

                    return buff;
                }
            },
            {
                id: 'level',
                name: LANG.level,
                value: 'level',
                type: 'range',
                getMinValue: function(item) {
                    return item.minlevel ? item.minlevel: item.level;
                },
                getMaxValue: function(item) {
                    return item.maxlevel ? item.maxlevel: item.level;
                },
                compute: function(item, td) {
                    if (item.minlevel && item.maxlevel) {
                        if (item.minlevel != item.maxlevel) {
                            return item.minlevel + LANG.hyphen + item.maxlevel;
                        }
                        else {
                            return item.minlevel;
                        }
                    }
                    else {
                        return item.level;
                    }
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.level, b.level);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.level, b.level);
                    }
                }
            },
            {
                id: 'reqlevel',
                name: LANG.req,
                tooltip: LANG.tooltip_reqlevel,
                value: 'reqlevel',
                compute: function(item, td) {
                    if (item.reqlevel > 1) {
                        return item.reqlevel;
                    }
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(item, td) {
                    if (item.side && item.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (item.side == 1 ? 'alliance-icon': 'horde-icon');
                        g_addTooltip(sp, g_sides[item.side]);
                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(item) {
                    if (item.side) {
                        return g_sides[item.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'dps',
                name: LANG.dps,
                value: 'dps',
                compute: function(item, td) {
                    return (item.dps || 0).toFixed(1);
                },
                hidden: true
            },
            {
                id: 'speed',
                name: LANG.speed,
                value: 'speed',
                compute: function(item, td) {
                    return (item.speed || 0).toFixed(2);
                },
                hidden: true
            },
            {
                id: 'armor',
                name: LANG.armor,
                value: 'armor',
                compute: function(item, td) {
                    if (item.armor > 0) {
                        return item.armor;
                    }
                },
                hidden: true
            },
            {
                id: 'slot',
                name: LANG.slot,
                type: 'text',
                compute: function(item, td) {
                    $WH.nw(td);

                    return g_item_slots[item.slot];
                },
                getVisibleText: function(item) {
                    return g_item_slots[item.slot];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_item_slots[a.slot], g_item_slots[b.slot]);
                }
            },
            {
                id: 'slots',
                name: LANG.slots,
                value: 'nslots',
                hidden: true
            },
            {
                id: 'skill',
                name: LANG.skill,
                value: 'skill',
                hidden: true
            },
            {
                id: 'glyph',
                name: LANG.glyphtype,
                type: 'text',
                value: 'glyph',
                compute: function(item, td) {
                    if (item.glyph) {
                        return g_item_glyphs[item.glyph];
                    }
                },
                getVisibleText: function(item) {
                    return g_item_glyphs[item.glyph];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_item_glyphs[a.glyph], g_item_glyphs[b.glyph]);
                },
                hidden: true
            },
            {
                id: 'source',
                name: LANG.source,
                type: 'text',
                compute: function(item, td) {
                    if (this.iconSize == 0) {
                        td.className = 'small';
                    }
                    if (item.source != null) {
                        if (item.source.length == 1) {
                            $WH.nw(td);

                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;

                                var a = $WH.ce('a');
                                if (sm.q != null) {
                                    a.className = 'q' + sm.q;
                                }
                                else {
                                    a.className = 'q1';
                                }
                                a.href = '?' + g_types[sm.t] + '=' + sm.ti;
                                a.style.whiteSpace = 'nowrap';

                                if (sm.icon) {
                                    a.className += ' icontiny tinyspecial';
                                    a.style.backgroundImage = 'url("' + g_staticUrl + '/images/icons/tiny/' + sm.icon.toLowerCase() + '.gif")';
                                }

                                $WH.ae(a, $WH.ct(sm.n));
                                $WH.ae(td, a);
                            }
                            else {
                                $WH.ae(td, $WH.ct(Listview.funcBox.getUpperSource(item.source[0], sm)));
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (this.iconSize != 0 && ls != null) {
                                var div = $WH.ce('div');
                                div.className = 'small2';

                                if (ls.pretext) {
                                    $WH.ae(div, $WH.ct(ls.pretext));
                                }

                                if (ls.url) {
                                    var a = $WH.ce('a');
                                    a.className = 'q1';
                                    a.href = ls.url;
                                    $WH.ae(a, $WH.ct(ls.text));
                                    $WH.ae(div, a);
                                }
                                else {
                                    $WH.ae(div, $WH.ct(ls.text));
                                }

                                if (ls.posttext) {
                                    $WH.ae(div, $WH.ct(ls.posttext));
                                }

                                $WH.ae(td, div);
                            }
                        }
                        else {
                            var buff = '';
                            for (var i = 0, len = item.source.length; i < len; ++i) {
                                if (i > 0) {
                                    buff += LANG.comma;
                                }
                                buff += g_sources[item.source[i]];
                            }
                            return buff;
                        }
                    }
                },
                getVisibleText: function(item) {
                    if (item.source != null) {
                        if (item.source.length == 1) {
                            var buff = '';

                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;
                                buff += ' ' + sm.n;
                            }
                            else {
                                buff += ' ' + Listview.funcBox.getUpperSource(item.source[0], sm);
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (ls != null) {
                                if (ls.pretext) {
                                    buff += ' ' + ls.pretext;
                                }

                                buff += ' ' + ls.text;

                                if (ls.posttext) {
                                    buff += ' ' + ls.posttext;
                                }
                            }

                            return buff;
                        }
                        else {
                            return Listview.funcBox.arrayText(item.source, g_sources);
                        }
                    }
                },
                sortFunc: function(a, b) {
                    var res = Listview.funcBox.assocArrCmp(a.source, b.source, g_sources);
                    if (res != 0) {
                        return res;
                    }

                    var
                        na = (a.sourcemore && a.source.length == 1 ? a.sourcemore[0].n: null),
                        nb = (b.sourcemore && b.source.length == 1 ? b.sourcemore[0].n: null);

                    return $WH.strcmp(na, nb);
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                compute: function(item, td) {
                    if (item.classs == null) {
                        return;
                    }

                    td.className = 'small q1';
                    $WH.nw(td);
                    var a = $WH.ce('a');

                    var it = Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass);

                    a.href = it.url;
                    $WH.ae(a, $WH.ct(it.text));
                    $WH.ae(td, a);
                },
                getVisibleText: function(item) {
                    return Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass).text;
                },
                sortFunc: function(a, b, col) {
                    var _ = Listview.funcBox.getItemType;
                    return $WH.strcmp(_(a.classs, a.subclass, a.subsubclass).text, _(b.classs, b.subclass, b.subsubclass).text);
                }
            }
        ],

        getItemLink: function(item) {
            return item.id > 0 ? '?item=' + item.id : 'javascript:;';
        },

        onBeforeCreate: function() {
            var nComparable = false;

            for (var i = 0, len = this.data.length; i < len; ++i) {
                var item = this.data[i];

                if ((item.slot > 0 && item.slot != 18) || (item.classs == 3 && item.subclass != 7) || ($WH.in_array(ModelViewer.validSlots, item.slotbak) >= 0 && item.displayid > 0) || item.modelviewer) { // Equippable, and not a bag, or has a model
                    ++nComparable;
                }
                else {
                    item.__nochk = 1;
                }
            }

            if (nComparable > 0) {
                this.mode = Listview.MODE_CHECKBOX;
                this._nComparable = nComparable;
            }
        },

        createCbControls: function(div, topBar) {
            if (!topBar && this._nComparable < 15) {
                return;
            }

            if (this.mode != Listview.MODE_CHECKBOX) {
                return;
            }

            var
                iCompare  = $WH.ce('input'),
                iViewIn3d = $WH.ce('input'),
                iEquip    = $WH.ce('input'),
                iDeselect = $WH.ce('input'),
                pinnedChr = g_user.characters ? $WH.array_filter(g_user.characters, function(row) {
                    return row.pinned;
                }) : false;

            iCompare.type = iViewIn3d.type = iEquip.type = iDeselect.type = 'button';

            iCompare.value  = LANG.button_compare;
            iViewIn3d.value = LANG.button_viewin3d;
            iEquip.value    = LANG.button_equip;
            iDeselect.value = LANG.button_deselect;

            iCompare.onclick  = this.template.compareItems.bind(this);
            iViewIn3d.onclick = this.template.viewIn3d.bind(this);
            iDeselect.onclick = Listview.cbSelect.bind(this, false);

            if (this._nComparable == 0 || typeof this._nComparable == 'undefined') {
                iCompare.disabled  = 'disabled';
                iViewIn3d.disabled = 'disabled';
                iEquip.disabled    = 'disabled';
                iDeselect.disabled = 'disabled';
                pinnedChr = false;
            }

            $WH.ae(div, iCompare);
            $WH.ae(div, iViewIn3d);

            if (pinnedChr && pinnedChr.length) {
                iEquip.onclick = this.template.equipItems.bind(this, pinnedChr[0]);
                $WH.ae(div, iEquip);
            }

            $WH.ae(div, iDeselect);
        },

        compareItems: function() {
            var rows = this.getCheckedRows();
            if (!rows.length) {
                return;
            }

            var data = '';
            $WH.array_walk(rows, function(x) {
                if (x.slot == 0 || x.slot == 18) {
                    return;
                }

                data += x.id + ';';
            });
            su_addToSaved($WH.rtrim(data, ';'), rows.length);
        },

        viewIn3d: function() {
            var rows = this.getCheckedRows();
            if (!rows.length) {
                return;
            }

            var
                hasData = false,
                repeatData = false,
                badData = false;
            var data = {};
            var model = null;
            $WH.array_walk(rows, function(x) {
                if ($WH.in_array(ModelViewer.validSlots, x.slotbak) >= 0 && x.displayid > 0) {
                    var slot = ModelViewer.slotMap[x.slotbak];
                    if (data[slot]) {
                        repeatData = true;
                    }
                    data[slot] = x.displayid;
                    hasData = true;
                }
                else if (x.modelviewer) {
                    model = x.modelviewer;
                }
                else {
                    badData = true;
                }
            });

            var message = null;
            if (model) {
                if (hasData || badData) {
                    message = LANG.dialog_cantdisplay;
                }
                ModelViewer.show({
                    type: model.type,
                    displayId: model.displayid,
                    slot: model.slot,
                    message: message
                });
            }
            else {
                if (repeatData || badData) {
                    message = LANG.dialog_cantdisplay;
                }
                var equipList = [];
                for (var i in data) {
                    equipList.push(parseInt(i));
                    equipList.push(data[i]);
                }
                if (equipList.length > 0) {
                    ModelViewer.show({
                        type: 4,
                        equipList: equipList,
                        message: message
                    });
                }
                else {
                    alert(LANG.message_nothingtoviewin3d);
                }
            }
        },

        equipItems: function(character) {
            var rows = this.getCheckedRows();
            if (!rows.length) {
                return;
            }

            var data = '';
            $WH.array_walk(rows, function(x) {
                if (x.slot == 0 || x.slot == 18) {
                    return;
                }
                data += x.id + ':';
            });
            location.href = g_getProfileUrl(character) + '&items=' + $WH.rtrim(data, ':');
        }
    },

    itemset: {
        sort: [1],
        nItemsPerPage: 75,
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(itemSet, td) {
                    var a = $WH.ce('a');
                    a.className = 'q' + (7 - parseInt(itemSet.name.charAt(0)));
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(itemSet);
                    $WH.ae(a, $WH.ct(itemSet.name.substring(1)));

                    var div = $WH.ce('div');
                    div.style.position = 'relative';
                    $WH.ae(div, a);

                    if (itemSet.heroic) {
                        var d = $WH.ce('div');
                        d.className = 'small q2';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '3px';
                        $WH.ae(d, $WH.ct(LANG.lvitem_heroicitem));
                        $WH.ae(div, d);
                    }

                    $WH.ae(td, div);

                    if (itemSet.note) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct(g_itemset_notes[itemSet.note]));
                        $WH.ae(td, d);
                    }
                },
                getVisibleText: function(itemSet) {
                    var buff = itemSet.name.substring(1);
                    if (itemSet.note) {
                        buff += ' ' + g_itemset_notes[itemSet.note];
                    }
                    return buff;
                }
            },
            {
                id: 'level',
                name: LANG.level,
                type: 'range',
                getMinValue: function(itemSet) {
                    return itemSet.minlevel;
                },
                getMaxValue: function(itemSet) {
                    return itemSet.maxlevel;
                },
                compute: function(itemSet, td) {
                    if (itemSet.minlevel > 0 && itemSet.maxlevel > 0) {
                        if (itemSet.minlevel != itemSet.maxlevel) {
                            return itemSet.minlevel + LANG.hyphen + itemSet.maxlevel;
                        }
                        else {
                            return itemSet.minlevel;
                        }
                    }
                    else {
                        return - 1;
                    }
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel);
                    }
                }
            },
            {
                id: 'pieces',
                name: LANG.pieces,
                getValue: function(itemSet) {
                    return itemSet.pieces.length;
                },
                compute: function(itemSet, td) {
                    td.style.padding = '0';
                    Listview.funcBox.createCenteredIcons(itemSet.pieces, td);
                },
                sortFunc: function(a, b) {
                    var lena = (a.pieces != null ? a.pieces.length: 0);
                    var lenb = (b.pieces != null ? b.pieces.length: 0);
                    return $WH.strcmp(lena, lenb);
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                compute: function(itemSet, td) {
                    return g_itemset_types[itemSet.type];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_itemset_types[a.type], g_itemset_types[b.type]);
                }
            },
            {
                id: 'classes',
                name: LANG.classes,
                type: 'text',
                width: '20%',
                getVisibleText: function(itemSet) {
                    var str = '';
                    if (itemSet.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(itemSet.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                str += LANG.comma;
                            }
                            str += g_chr_classes[classes[i]];
                        }
                    }
                    return str;
                },
                compute: function(itemSet, td) {
                    if (itemSet.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(itemSet.reqclass, g_chr_classes);

                        var d = $WH.ce('div');
                        d.style.width = (26 * classes.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            var icon = Icon.create('class_' + g_file_classes[classes[i]], 0, null, '?class=' + classes[i]);
                            icon.style.cssFloat = icon.style.styleFloat = 'left';
                            g_addTooltip(icon, g_chr_classes[classes[i]], 'c' + classes[i]);

                            $WH.ae(d, icon);
                        }

                        $WH.ae(td, d);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.reqclass, g_chr_classes), Listview.funcBox.assocBinFlags(b.reqclass, g_chr_classes), g_chr_classes);
                }
            }
        ],

        getItemLink: function(itemSet) {
            return '?itemset=' + itemSet.id;
        }
    },

    npc: {
        sort: [1],
        nItemsPerPage: 100,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(npc, td) {
                    if (npc.boss) {
                        td.className = 'boss-icon-padded';
                    }

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(npc);
                    $WH.ae(a, $WH.ct(npc.name));
                    $WH.ae(td, a);

                    if (npc.tag != null) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct('<' + npc.tag + '>'));
                        $WH.ae(td, d);
                    }
                },
                getVisibleText: function(npc) {
                    var buff = npc.name;
                    if (npc.tag) {
                        buff += ' <' + npc.tag + '>';
                    }
                    if (npc.boss) {
                        buff += ' boss skull';
                    }
                    return buff;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(b.boss, a.boss) || $WH.strcmp(a.name, b.name);
                }
            },
            {
                id: 'level',
                name: LANG.level,
                type: 'range',
                width: '10%',
                getMinValue: function(npc) {
                    return npc.minlevel;
                },
                getMaxValue: function(npc) {
                    return npc.maxlevel;
                },
                compute: function(npc, td) {
                    if (npc.classification) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct(g_npc_classifications[npc.classification]));
                        $WH.ae(td, d);
                    }

                    if (npc.classification == 3 || npc.maxlevel == 9999) {
                        return '??';
                    }

                    if (npc.minlevel > 0 && npc.maxlevel > 0) {
                        if (npc.minlevel != npc.maxlevel) {
                            return npc.minlevel + LANG.hyphen + npc.maxlevel;
                        }
                        else {
                            return npc.minlevel;
                        }
                    }

                    return -1;
                },
                getVisibleText: function(npc) {
                    var buff = '';

                    if (npc.classification) {
                        buff += ' ' + g_npc_classifications[npc.classification];
                    }

                    if (npc.minlevel > 0 && npc.maxlevel > 0) {
                        buff += ' ';
                        if(npc.maxlevel == 9999) {
                            buff += '??';
                        }
                        else if (npc.minlevel != npc.maxlevel) {
                            buff += npc.minlevel + LANG.hyphen + npc.maxlevel;
                        }
                        else {
                            buff += npc.minlevel;
                        }
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.classification, b.classification);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.classification, b.classification);
                    }
                }
            },
            {
                id: 'location',
                name: LANG.location,
                type: 'text',
                compute: function(npc, td) {
                    return Listview.funcBox.location(npc, td);
                },
                getVisibleText: function(npc) {
                    return Listview.funcBox.arrayText(npc.location, g_zones);
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(a.location, b.location, g_zones);
                }
            },
            {
                id: 'react',
                name: LANG.react,
                type: 'text',
                width: '10%',
                value: 'react',
                filtrable: 0,
                compute: function(npc, td) {
                    if (npc.react == null) {
                        return -1;
                    }

                    var sides = [LANG.lvnpc_alliance, LANG.lvnpc_horde];
                    var c = 0;
                    for (var k = 0; k < 2; ++k) {
                        if (npc.react[k] != null) {
                            if (c++ > 0) {
                                $WH.ae(td, $WH.ct(' '));
                            }
                            var sp = $WH.ce('span');

                            sp.className = (npc.react[k] < 0 ? 'q10': (npc.react[k] > 0 ? 'q2': 'q'));

                            $WH.ae(sp, $WH.ct(sides[k]));
                            $WH.ae(td, sp);
                        }
                    }
                }
            },
            {
                id: 'skin',
                name: LANG.skin,
                type: 'text',
                value: 'skin',
                compute: function(npc, td) {
                    if (npc.skin) {
                        var a = $WH.ce('a');
                        a.className = 'q1';
                        a.href = '?npcs&filter=cr=35;crs=0;crv=' + npc.skin;
                        $WH.ae(a, $WH.ct(npc.skin));
                        $WH.ae(td, a);
                    }
                },
                hidden: 1
            },
            {
                id: 'petfamily',
                name: LANG.petfamily,
                type: 'text',
                width: '12%',
                compute: function(npc, td) {
                    td.className = 'q1';

                    var a = $WH.ce('a');
                    a.href = '?pet=' + npc.family;
                    $WH.ae(a, $WH.ct(g_pet_families[npc.family]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(npc) {
                    return g_pet_families[npc.family];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_pet_families[a.family], g_pet_families[b.family]);
                },
                hidden: 1
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                width: '12%',
                compute: function(npc, td) {
                    td.className = 'small q1';

                    var a = $WH.ce('a');
                    a.href = '?npcs=' + npc.type;
                    $WH.ae(a, $WH.ct(g_npc_types[npc.type]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(npc) {
                    return g_npc_types[npc.type];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_npc_types[a.type], g_npc_types[b.type]);
                }
            }
        ],

        getItemLink: function(npc) {
            return '?npc=' + npc.id;
        }
    },

    object: {
        sort: [1],
        nItemsPerPage: 100,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(object, td) {
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(object);
                    $WH.ae(a, $WH.ct(object.name));
                    $WH.ae(td, a);
                }
            },
            {
                id: 'location',
                name: LANG.location,
                type: 'text',
                compute: function(object, td) {
                    return Listview.funcBox.location(object, td);
                },
                getVisibleText: function(object) {
                    return Listview.funcBox.arrayText(object.location, g_zones);
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(a.location, b.location, g_zones);
                }
            },
            {
                id: 'skill',
                name: LANG.skill,
                width: '10%',
                value: 'skill',
                hidden: true
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                width: '12%',
                compute: function(object, td) {
                    td.className = 'small q1';
                    var a = $WH.ce('a');
                    a.href = '?objects=' + object.type;
                    $WH.ae(a, $WH.ct(g_object_types[object.type]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(object) {
                    return g_object_types[object.type];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_object_types[a.type], g_object_types[b.type]);
                }
            }
        ],

        getItemLink: function(object) {
            return '?object=' + object.id;
        }
    },

    quest: {
        sort: [1,2],
        nItemsPerPage: 100,
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(quest, td) {
                    var wrapper = $WH.ce('div');
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(quest);
                    $WH.ae(a, $WH.ct(quest.name));
                    $WH.ae(wrapper, a);

                    if (quest.reqclass) {
                        var d = $WH.ce('div');
                        d.className += ' small2';

                        var classes = Listview.funcBox.assocBinFlags(quest.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(d, $WH.ct(LANG.comma));
                            }

                            var a = $WH.ce('a');
                            a.href = '?class=' + classes[i];
                            a.className += 'c' + classes[i];
                            $WH.st(a, g_chr_classes[classes[i]]);
                            $WH.ae(d, a);
                        }

                        $WH.ae(wrapper, d);
                    }

                    if (quest.wflags & 1 || (quest.wflags & 32) || (quest.reqrace && quest.reqrace != -1)) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '3px';
                        d.style.textAlign = 'right';
                        if (quest.wflags & 1) {
                            var s = $WH.ce('span');
                            s.style.color = 'red';
                            $WH.ae(s, $WH.ct(LANG.lvquest_disabled));
                            // $WH.ae(s, $WH.ct(LANG.lvquest_removed));
                            $WH.ae(d, s);
                        }

                        if (quest.wflags & 32) {
                            if (quest.wflags & 1) {
                                $WH.ae(d, $WH.ce('br'));
                                wrapper.style.height = '33px';
                            }

                            var
                                s = $WH.ce('span'),
                                o = LANG.lvquest_autoaccept;
                            if (quest.wflags & 64) {
                                s.style.color = 'red';
                                o += ' ' + LANG.lvquest_hostile;
                            }
                            $WH.ae(s, $WH.ct(o));
                            $WH.ae(d, s);
                        }

                        if (quest.reqrace && quest.reqrace != -1) {
                            var races = Listview.funcBox.assocBinFlags(quest.reqrace, g_chr_races);

                            if (races.length && (quest.wflags & 1 || (quest.wflags & 32))) {
                                $WH.ae(d, $WH.ce('br'));
                                wrapper.style.height = '33px';
                            }

                            for (var i = 0, len = races.length; i < len; ++i) {
                                if (i > 0) {
                                    $WH.ae(d, $WH.ct(LANG.comma));
                                }

                                var l = $WH.ce('a');
                                l.href = '?race=' + races[i];
                                l.className += 'q1';
                                $WH.st(l, g_chr_races[races[i]]);
                                $WH.ae(d, l);
                            }
                        }

                        $WH.ae(wrapper, d);
                    }

                    $WH.ae(td, wrapper);
                }
            },
            {
                id: 'level',
                name: LANG.level,
                value: 'level',
                compute: function(quest, td) {
                    if (quest.type || quest.daily || quest.weekly) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.nw(d);

                        if (quest.daily) {
                            if (quest.type) {
                                $WH.ae(d, $WH.ct($WH.sprintf(LANG.lvquest_daily, g_quest_types[quest.type])));
                            }
                            else {
                                $WH.ae(d, $WH.ct(LANG.daily));
                            }
                        }
                        else if (quest.weekly) {
                            if (quest.type) {
                                $WH.ae(d, $WH.ct($WH.sprintf(LANG.lvquest_weekly, g_quest_types[quest.type])));
                            }
                            else {
                                $WH.ae(d, $WH.ct(LANG.weekly));
                            }
                        }
                        else if (quest.type) {
                            $WH.ae(d, $WH.ct(g_quest_types[quest.type]));
                        }

                        $WH.ae(td, d);
                    }

                    return quest.level;
                },
                getVisibleText: function(quest) {
                    var buff = '';

                    if (quest.type) {
                        buff += ' ' + g_quest_types[quest.type];
                    }

                    if (quest.daily) {
                        buff += ' ' + LANG.daily;
                    }
                    else if (quest.weekly) {
                        buff += ' ' + LANG.weekly;
                    }

                    if (quest.level) {
                        buff += ' ' + quest.level;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(a.level, b.level) || $WH.strcmp(a.type, b.type);
                }
            },
            {
                id: 'reqlevel',
                name: LANG.req,
                tooltip: LANG.tooltip_reqlevel,
                value: 'reqlevel'
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(quest, td) {
                    if (quest.side && quest.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (quest.side == 1 ? 'alliance-icon': 'horde-icon');
                        g_addTooltip(sp, g_sides[quest.side]);

                        $WH.ae(td, sp);
                    }
                    else if (!quest.side) {
                        $WH.ae(td, $WH.ct('??'));
                    }
                },
                getVisibleText: function(quest) {
                    if (quest.side) {
                        return g_sides[quest.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'rewards',
                name: LANG.rewards,
                compute: function(quest, td) {
                    var hasIcons = (quest.itemchoices != null || quest.itemrewards != null);
                    if (hasIcons) {
                        var
                            choiceText,
                            rewardText;
                        if (quest.itemchoices && quest.itemchoices.length > 1) {
                            choiceText = LANG.lvquest_pickone;
                            if (quest.itemrewards && quest.itemrewards.length > 0) {
                                rewardText = LANG.lvquest_alsoget;
                            }
                        }

                        Listview.funcBox.createCenteredIcons(quest.itemchoices, td, choiceText, 2);
                        Listview.funcBox.createCenteredIcons(quest.itemrewards, td, rewardText, 2);
                    }

                    if (quest.titlereward && g_titles[quest.titlereward]) {
                        var title = g_titles[quest.titlereward]['name_' + g_locale.name];
                        title = title.replace('%s', '<span class="q0">&lt;' + LANG.name + '&gt;</span>');
                        var span = $WH.ce('a');
                        span.className = 'q1';
                        span.href = '?title=' + quest.titlereward;
                        span.innerHTML = title;
                        $WH.ae(td, span);
                        $WH.ae(td, $WH.ce('br'));
                    }
                },
                getVisibleText: function(quest) {
                    var buff = '';

                    if (quest.itemchoices && quest.itemchoices.length) {
                        buff += ' ' + LANG.lvquest_pickone;
                        if (quest.itemrewards && quest.itemrewards.length) {
                            buff += ' ' + LANG.lvquest_alsoget;
                        }
                    }

                    if (quest.titlereward && g_titles[quest.titlereward]) {
                        buff += ' ' + g_titles[quest.titlereward]['name_' + g_locale.name];
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var lenA = (a.itemchoices != null ? a.itemchoices.length : 0) + (a.itemrewards != null ? a.itemrewards.length : 0);
                    var lenB = (b.itemchoices != null ? b.itemchoices.length : 0) + (b.itemrewards != null ? b.itemrewards.length : 0);
                    var titleA = (a.titlereward && g_titles[a.titlereward] ? g_titles[a.titlereward]['name_' + g_locale.name] : '');
                    var titleB = (b.titlereward && g_titles[b.titlereward] ? g_titles[b.titlereward]['name_' + g_locale.name] : '');
                    return $WH.strcmp(lenA, lenB) || $WH.strcmp(titleA, titleB);
                }
            },
            {
                id: 'experience',
                name: LANG.exp,
                value: 'xp'
            },
            {
                id: 'money',
                name: LANG.money,
                compute: function(quest, td) {
                    if (quest.money > 0 || quest.currencyrewards != null) {
                        if (quest.money > 0) {
                            Listview.funcBox.appendMoney(td, quest.money);
                            if (quest.currencyrewards != null) {
                                $WH.ae(td, $WH.ct(' + '));
                            }
                        }

                        if (quest.currencyrewards != null) {
                            Listview.funcBox.appendMoney(td, null, null, quest.side, null, quest.currencyrewards);    // todo: update appendMoney ..!important!
                        }
                    }
                },
                getVisibleText: function(quest) {
                    var buff = '';
                    for (var i = 0; quest.currencyrewards && i < quest.currencyrewards.length; ++i) {
                        if (g_gatheredcurrencies[quest.currencyrewards[i][0]]) {
                            buff += ' ' + g_gatheredcurrencies[quest.currencyrewards[i][0]]['name_' + g_locale.name];
                        }
                    }
                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var
                        lenA = 0,
                        lenB = 0;

                    if (a.currencyrewards && a.currencyrewards.length) {
                        for (i in a.currencyrewards) {
                            var c = (a.currencyrewards)[i];
                            lenA += c[1];
                        }
                    }
                    if (b.currencyrewards && b.currencyrewards.length) {
                        for (a in b.currencyrewards) {
                            var c = (b.currencyrewards)[i];
                            lenB += c[1];
                        }
                    }
                    return $WH.strcmp(lenA, lenB) || $WH.strcmp(a.money, b.money);
                }
            },
            {
                id: 'reputation',
                name: LANG.reputation,
                width: '14%',
                value: 'id',
                hidden: true
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                compute: function(quest, td) {
                    if (quest.category != 0) {
                        td.className = 'small q1';
                        var a = $WH.ce('a');
                        a.href = '?quests=' + quest.category2 + '.' + quest.category;
                        $WH.ae(a, $WH.ct(Listview.funcBox.getQuestCategory(quest.category)));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(quest) {
                    return Listview.funcBox.getQuestCategory(quest.category);
                },
                sortFunc: function(a, b, col) {
                    var _ = Listview.funcBox.getQuestCategory;
                    return $WH.strcmp(_(a.category), _(b.category));
                }
            }
        ],

        getItemLink: function(quest) {
            return '?quest=' + quest.id;
        }
    },

    skill: {
        sort: [1],
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                span: 2,
                compute: function(skill, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, Icon.create(skill.icon, 0, null, this.getItemLink(skill)));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(skill);
                    $WH.ae(a, $WH.ct(skill.name));

                    if (skill.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(skill.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(wrapper, sp);
                    }
                    else {
                        $WH.ae(wrapper, a);
                    }

                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(skill) {
                    var buff = skill.name + Listview.funcBox.getExpansionText(skill);

                    return buff;
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '16%',
                compute: function(skill, td) {
                    if (skill.category != 0) {
                        td.className = 'small q1';
                        var a = $WH.ce('a');
                        a.href = '?skills=' + skill.category;
                        $WH.ae(a, $WH.ct(g_skill_categories[skill.category]));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(skill) {
                    return g_skill_categories[skill.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_skill_categories[a.category], g_skill_categories[b.category]);
                }
            }
        ],

        getItemLink: function(skill) {
            return '?skill=' + skill.id;
        }
    },

    spell: {
        sort: ['name', 'skill', 'level'],
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(spell, td, tr) {
                    var
                        i = $WH.ce('td'),
                        _;

                    i.style.width = '44px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    if (spell.creates != null) {
                        _ = g_items.createIcon(spell.creates[0], 1, Listview.funcBox.createTextRange(spell.creates[1], spell.creates[2]));
                    }
                    else {
                        _ = g_spells.createIcon(spell.id, 1);
                    }

                    _.style.cssFloat = _.style.styleFloat = 'left';
                    $WH.ae(i, _);

                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    var c = spell.name.charAt(0);
                    if (c != '@') {
                        a.className = 'q' + (7 - parseInt(c));
                    }
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(spell);

                    $WH.ae(a, $WH.ct(spell.name.substring(1)));

                    $WH.ae(wrapper, a);

                    if (spell.rank) {
                        var d = $WH.ce('div');
                        d.className = 'small2';

                        $WH.ae(d, $WH.ct(spell.rank));
                        $WH.ae(wrapper, d);
                    }

                    if (this.showRecipeClass && spell.reqclass) {
                        var d = $WH.ce('div');
                        d.className = 'small2';

                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(d, $WH.ct(', '));
                            }
                            var a = $WH.ce('a');
                            a.href = '?class=' + classes[i];
                            a.className = 'c' + classes[i];
                            $WH.st(a, g_chr_classes[classes[i]]);
                            $WH.ae(d, a);
                        }

                        $WH.ae(wrapper, d);
                    }

                    if (spell.reqrace) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = d.style.bottom = '3px';
                        if ((spell.reqrace & 1791) == 1101) {  // Alliance
                            $WH.ae(d, $WH.ct(g_sides[1]));
                        }
                        else if ((spell.reqrace & 1791) == 690) { // Horde
                            $WH.ae(d, $WH.ct(g_sides[2]));
                        }
                        else {
                            var races = Listview.funcBox.assocBinFlags(spell.reqrace, g_chr_races);
                            d.className += ' q1';

                            for (var i = 0, len = races.length; i < len; ++i) {
                                if (i > 0) {
                                    $WH.ae(d, $WH.ct(LANG.comma)) ;
                                }
                                var a = $WH.ce('a');
                                a.href = '?race=' + races[i];
                                $WH.st(a, g_chr_races[races[i]]);
                                $WH.ae(d, a);
                            }
                        }
                        $WH.ae(wrapper, d);
                    }
                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(spell) {
                    var buff = spell.name;

                    if (spell.rank) {
                        buff += ' ' + spell.rank;
                    }

                    if (spell.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                buff += LANG.comma;
                            }
                            buff += g_chr_classes[classes[i]];
                        }
                    }

                    if (spell.reqrace) {
                        buff += ' ' + Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(spell.reqrace, g_chr_races), g_chr_races);
                    }

                    return buff;
                }
            },
            {
                id: 'tier',
                name: LANG.tier,
                width: '10%',
                value: 'level',
                compute: function(spell, td) {
                    if (spell.level > 0) {
                        var
                            baseLevel  = (!this._petTalents ? 10 : 20),
                            tierPoints = (!this._petTalents ?  5 : 12);

                        return Math.floor((spell.level - baseLevel) / tierPoints) + 1;  // + 3 ?
                    }
                    else {
                        return 1;
                    }
                },
                hidden: true
            },
            {
                id: 'level',
                name: LANG.level,
                width: '10%',
                value: 'level',
                compute: function(spell, td) {
                    if (spell.level > 0) {
                        return spell.level;
                    }
                },
                hidden: true
            },
            {
                id: 'trainingcost',
                name: LANG.cost,
                width: '10%',
                hidden: true,
                getValue: function(row) {
                    if (row.trainingcost) {
                        return row.trainingcost;
                    }
                },
                compute: function(row, td) {
                    if (row.trainingcost) {
                        Listview.funcBox.appendMoney(td, row.trainingcost);
                    }
                },
                sortFunc: function(a, b, col) {
                    if (a.trainingcost == null) {
                        return -1;
                    }
                    else if (b.trainingcost == null) {
                        return 1;
                    }

                    if (a.trainingcost < b.trainingcost) {
                        return -1;
                    }
                    else if (a.trainingcost > b.trainingcost) {
                        return 1;
                    }
                    return 0;
                }
            },
            {
                id: 'classes',
                name: LANG.classes,
                type: 'text',
                hidden: true,
                width: '20%',
                getVisibleText: function(spell) {
                    var str = '';
                    if (spell.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                str += LANG.comma;
                            }
                            str += g_chr_classes[classes[i]];
                        }
                    }
                    return str;
                },
                compute: function(spell, td) {
                    if (spell.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);

                        var d = $WH.ce('div');
                        d.style.width = (26 * classes.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            var icon = Icon.create('class_' + g_file_classes[classes[i]], 0, null, '?class=' + classes[i]);
                            icon.style.cssFloat = icon.style.styleFloat = 'left';

                            g_addTooltip(icon, g_chr_classes[classes[i]], 'c' + classes[i]);

                            $WH.ae(d, icon);
                        }

                        $WH.ae(td, d);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.reqclass, g_chr_classes), Listview.funcBox.assocBinFlags(b.reqclass, g_chr_classes), g_chr_classes);
                }
            },
            {
                /* NOTE: To be used only if one single class is to be displayed */
                id: 'singleclass',
                name: LANG.classs,
                type: 'text',
                hidden: true,
                width: '15%',
                compute: function(spell, td) {
                    if (spell.reqclass) {
                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);
                        var classId = classes[0];

                        var text = $WH.ce('a');
                        text.style.backgroundImage = 'url("' + g_staticUrl + '/images/icons/tiny/class_' + g_file_classes[classId] + '.gif")';
                        text.className = 'icontiny tinyspecial c' + classId;
                        text.href = '?class=' + classId;
                        $WH.ae(text, $WH.ct(g_chr_classes[classId]));

                        $WH.ae(td, text);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.reqclass, g_chr_classes), Listview.funcBox.assocBinFlags(b.reqclass, g_chr_classes), g_chr_classes);
                }
            },
            {
                id: 'glyphtype',
                name: LANG.glyphtype,
                type: 'text',
                hidden: true,
                width: '10%',
                compute: function(spell, td) {
                    if (spell.glyphtype) {
                        return g_item_glyphs[spell.glyphtype];
                    }
                }
            },
            {
                id: 'schools',
                name: LANG.school,
                type: 'text',
                width: '10%',
                hidden: true,
                compute: function(spell, td) {
                    var text = '';
                    var schools = spell.schools ? spell.schools: spell.school;

                    for (var i = 0; i < 32; ++i) {
                        if (!(schools & (1 << i))) {
                            continue;
                        }

                        if (text != '') {
                            text += ', ';
                        }

                        text += g_spell_resistances[i];
                    }

                    return text;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.compute(a), this.compute(b));
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                width: '14%',
                hidden: true,
                compute: function (spell, td) {
                    var d = $WH.ce('div');
                    d.className = 'small';
                    if ($WH.in_array([7, -2, -7, -12, -11, -13], spell.cat) != -1) {
                        var a1 = $WH.ce('a');

                        a1.className = 'q0';
                        a1.href = '?spells=' + spell.cat;

                        $WH.ae(a1, $WH.ct(g_spell_categories[spell.cat]));
                        $WH.ae(d, a1);
                        $WH.ae(d, $WH.ce('br'));

                        if (spell.talentspec) {
                            for (var i = 0, len = spell.talentspec.length; i< len; ++i) {
                                if (i > 0) {
                                    $WH.ae(d, $WH.ct(LANG.comma));
                                }

                                var a2 = $WH.ce('a');
                                a2.className = 'q1';
                                a2.href = '?spells=' + spell.cat + '.' + (1 + Math.log(spell.reqclass) / Math.LN2) + '.' + spell.talentspec[i];
                                $WH.ae(a2, $WH.ct(g_chr_specs[spell.talentspec[i]]));
                                $WH.ae(d, a2);
                            }
                        }
                        else if (g_spell_types[spell.cat]) {
                            if (spell.cat == -11 && (spell.type == 1 || spell.type == 2)) {
                                var a2 = $WH.ce('a');
                                a2.className = 'q1';
                                // a2.href = '?spells=' + spell.cat + '&filter=cr=22;crs=' + spell.type + ';crv=0';
                                a2.href = '?spells=' + spell.cat + '.' + (spell.type == 2 ? '8' : '6');
                                $WH.ae(a2, $WH.ct(g_spell_types[spell.cat][spell.type]));
                                $WH.ae(d, a2);
                            }
                            else {
                                $WH.ae(d, $WH.ct(g_spell_types[spell.cat][spell.type]));
                            }
                        }
                        else if (spell.reqclass) {
                            var a2 = $WH.ce('a');
                            a2.className = 'q1';
                            a2.href = '?spells=' + spell.cat + '.' + (1 + Math.log(spell.reqclass) / Math.LN2) + (g_item_glyphs[spell.glyphtype] ? '&filter=gl=' + spell.glyphtype : '');
                            $WH.ae(a2, $WH.ct((g_item_glyphs[spell.glyphtype] ? g_item_glyphs[spell.glyphtype] + ' ' : '') + g_chr_classes[(1 + Math.log(spell.reqclass) / Math.LN2)]));
                            $WH.ae(d, a2);
                        }
                    }
                    else if (g_spell_categories[spell.cat]) {
                        var a1 = $WH.ce('a');
                        a1.className = 'q1';
                        a1.href = '?spells=' + spell.cat;
                        $WH.ae(a1, $WH.ct(g_spell_categories[spell.cat]));
                        $WH.ae(d, a1)
                    }
                    $WH.ae(td, d);
                },
                getVisibleText: function (spell) {
                    var buff = g_spell_categories[spell.cat];

                    if ($WH.in_array([7, -2, -12, -11, -13], spell.cat) != -1) {
                        if (spell.talentspec) {
                            buff += ' ' + Listview.funcBox.arrayText(spell.talentspec, g_chr_specs);
                        }
                        else if (g_spell_types[spell.cat]) {
                            buff += ' ' + g_spell_types[spell.cat][spell.type];
                        }
                        else {
                            if (g_item_glyphs[spell.glyphtype]) {
                                buff += ' ' + g_item_glyphs[spell.glyphtype];
                            }

                            buff += ' ' + g_chr_classes[(1 + Math.log(spell.reqclass) / Math.LN2)];
                        }
                    }
                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var
                        typeA = (g_spell_types[a.cat] ? g_spell_types[a.cat][a.type] : a.type),
                        typeB = (g_spell_types[b.cat] ? g_spell_types[b.cat][b.type] : a.type);

                    return $WH.strcmp(a.cat, b.cat) || $WH.strcmp(typeA, typeB);
                }
            },
            {
                id: 'reagents',
                name: LANG.reagents,
                width: '9%',
                hidden: true,
                getValue: function(spell) {
                    return (spell.reagents ? spell.reagents.length: 0);
                },
                compute: function(spell, td) {
                    var hasIcons = (spell.reagents != null);
                    if (hasIcons) {
                        td.style.padding = '0';

                        var d = $WH.ce('div');
                        var items = spell.reagents;

                        d.style.width = (44 * items.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = items.length; i < len; ++i) {
                            var id   = items[i][0];
                            var num  = items[i][1];
                            var icon = g_items.createIcon(id, 1, num);
                            icon.style.cssFloat = icon.style.styleFloat = 'left';
                            $WH.ae(d, icon);
                        }

                        $WH.ae(td, d);
                    }
                },
                sortFunc: function(a, b) {
                    var lena = (a.reagents != null ? a.reagents.length: 0);
                    var lenb = (b.reagents != null ? b.reagents.length: 0);
                    if (lena > 0 && lena == lenb) {
                        return $WH.strcmp(a.reagents.toString(), b.reagents.toString());
                    }
                    else {
                        return $WH.strcmp(lena, lenb);
                    }
                }
            },
            {
                id: 'tp',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.tp,
                tooltip: LANG.tooltip_trainingpoints,
                width: '7%',
                hidden: true,
                value: 'tp',
                compute: function (spell, col) {
                    if (spell.tp > 0) {
                        return spell.tp;
                    }
                }
            },
            {
                id: 'source',
                name: LANG.source,
                type: 'text',
                width: '12%',
                hidden: true,
                compute: function(spell, td) {
                    if (spell.source != null) {
                        var buff = '';
                        for (var i = 0, len = spell.source.length; i < len; ++i) {
                            if (i > 0) {
                                buff += LANG.comma;
                            }
                            buff += g_sources[spell.source[i]];
                        }
                        return buff;
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(a.source, b.source, g_sources);
                }
            },
            {
                id: 'skill',
                name: LANG.skill,
                type: 'text',
                width: '16%',
                getValue: function(spell) {
                    return spell.learnedat;
                },
                compute: function(spell, td, tr, colNo) {
                    if (spell.skill != null) {
                        this.skillsColumn = colNo;
                        var div = $WH.ce('div');
                        div.className = 'small';

                        if (spell.cat == -7 && spell.pettype != null) {
                            spell.skill = [];
                            var petTalents = {
                                0: 410,
                                1: 409,
                                2: 411
                            };
                            for (var i = 0, len = spell.pettype.length; i < len; ++i) {
                                spell.skill.push(petTalents[spell.pettype[i]]);
                            }
                        }

                        for (var i = 0, len = spell.skill.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(div, $WH.ct(LANG.comma));
                            }

                            if (spell.skill[i] == -1) {
                                $WH.ae(div, $WH.ct(LANG.ellipsis));
                            }
                            else {
                                if ($WH.in_array([7, -2, -3, -5, -6, -7, 11, 9], spell.cat) != -1) {
                                    var a = $WH.ce('a');
                                    a.className = 'q1';
                                    if ($WH.in_array([-5, -6], spell.cat) != -1) {
                                        a.href = '?spells=' + spell.cat;
                                    }
                                    else {
                                        a.href = '?spells=' + spell.cat + '.' + ((spell.reqclass && (spell.cat == 7 || spell.cat == -2)) ? (1 + Math.log(spell.reqclass) / Math.LN2) + '.' : '') + spell.skill[i];
                                    }

                                    var get = $WH.g_getGets();
                                    var spellCat = (get.spells ? get.spells.split('.') : [false, false]);
                                    if (spell.reqclass && (spell.cat == 7 || spell.cat == -2)) {
                                        if (i < 1 && ((1 + Math.log(spell.reqclass) / Math.LN2) != spellCat[1])) {
                                            var a2 = $WH.ce('a');
                                            a2.className = 'q0';
                                            a2.href = '?spells=' + spell.cat + '.' + (1 + Math.log(spell.reqclass) / Math.LN2);
                                            $WH.ae(a2, $WH.ct(g_chr_classes[(1 + Math.log(spell.reqclass) / Math.LN2)]));
                                            $WH.ae(div, a2);
                                            $WH.ae(div, $WH.ce('br'));
                                        }
                                    }
                                    $WH.ae(a, $WH.ct(spell.cat == -7 && spell.pettype != null ? g_pet_types[spell.pettype[i]] : g_spell_skills[spell.skill[i]]));
                                    $WH.ae(div, a);
                                }
                                else {
                                    $WH.ae(div, $WH.ct(g_spell_skills[spell.skill[i]]));
                                }
                            }
                        }

                        if (spell.learnedat > 0) {
                            $WH.ae(div, $WH.ct(' ('));
                            var spLearnedAt = $WH.ce('span');
                            if (spell.learnedat == 9999) {
                                spLearnedAt.className = 'q0';
                                $WH.ae(spLearnedAt, $WH.ct('??'));
                            }
                            else if (spell.learnedat > 0) {
                                $WH.ae(spLearnedAt, $WH.ct(spell.learnedat));
                                spLearnedAt.style.fontWeight = 'bold';
                            }
                            $WH.ae(div, spLearnedAt);
                            $WH.ae(div, $WH.ct(')'));
                        }

                        $WH.ae(td, div);

                        if (spell.colors != null) {
                            this.template.columns[colNo].type = null;

                            var
                                colors = spell.colors,
                                nColors = 0;

                            for (var i = 0; i < colors.length; ++i) {
                                if (colors[i] > 0) {
                                    ++nColors;
                                    break;
                                }
                            }
                            if (nColors > 0) {
                                nColors = 0;
                                div = $WH.ce('div');
                                div.className = 'small';
                                div.style.fontWeight = 'bold';
                                for (var i = 0; i < colors.length; ++i) {
                                    if (colors[i] > 0) {
                                        if (nColors++ > 0) {
                                            $WH.ae(div, $WH.ct(' '));
                                        }
                                        var spColor = $WH.ce('span');
                                        spColor.className = 'r' + (i + 1);
                                        $WH.ae(spColor, $WH.ct(colors[i]));
                                        $WH.ae(div, spColor);
                                    }
                                }
                                $WH.ae(td, div);
                            }
                        }
                    }
                },
                getVisibleText: function(spell) {
                    var buff = Listview.funcBox.arrayText(spell.skill, g_spell_skills);

                    if (spell.learnedat > 0) {
                        buff += ' ' + (spell.learnedat == 9999 ? '??' : spell.learnedat);
                    }

                    return buff;
                },
                sortFunc: function(a, b) {
                /*  sarjuuk: behaves unpredictable if reqrace not set or 0
                    if (a.reqclass && b.reqclass) {
                        var reqClass = $WH.strcmp(g_chr_classes[(1 + Math.log(a.reqclass) / Math.LN2)], g_chr_classes[(1 + Math.log(b.reqclass) / Math.LN2)]);
                        if (reqClass) {
                            return reqClass;
                        }
                    }
                */

                    var skill = [a.learnedat, b.learnedat];
                    for (var s = 0; s < 2; ++s) {
                        var sp = (s == 0 ? a: b);
                        if (skill[s] == 9999 && sp.colors != null) {
                            var i = 0;
                            while (sp.colors[i] == 0 && i < sp.colors.length) {
                                i++;
                            }
                            if (i < sp.colors.length) {
                                skill[s] = sp.colors[i];
                            }
                        }
                    }
                    var foo = $WH.strcmp(skill[0], skill[1]);
                    if (foo != 0) {
                        return foo;
                    }

                    if (a.colors != null && b.colors != null) {
                        for (var i = 0; i < 4; ++i) {
                            foo = $WH.strcmp(a.colors[i], b.colors[i]);
                            if (foo != 0) {
                                return foo;
                            }
                        }
                    }

                    if (a.pettype != null & b.pettype != null) {
                        return Listview.funcBox.assocArrCmp(a.pettype, b.pettype, g_pet_types);
                    }

                    return Listview.funcBox.assocArrCmp(a.skill, b.skill, g_spell_skills);
                }
            },
    /* sarjuuk
        todo: localize he next three cols
    */
            {
                id: 'stackRules',
                name: 'Behaviour',
                type: 'text',
                width: '20%',
                hidden: true,
                compute: function(spell, td) {
                    if (!spell.stackRule) {
                        return;
                    }

                    var
                        buff  = '',
                        buff2 = '';

                    switch (spell.stackRule) {
                        case 3:
                            buff2 = '(strongest effect is applied)';
                        case 0:
                            buff  = 'coexist';              // without condition
                            break;
                        case 2:
                            buff2 = '(from same caster)';
                        case 1:
                            buff  = 'exclusive';            // without condition
                            break;
                    }

                    td.className = 'small';
                    td.style.lineHeight = '18px';

                    var span = $WH.ce('span');
                    span.className = !spell.stackRule || spell.stackRule == 3 ? 'q2' : 'q10';
                    $WH.ae(span, $WH.ct(buff));
                    $WH.ae(td, span);

                    if (buff2) {
                        var sp2 = $WH.ce('span');
                        sp2.style.whiteSpace = 'nowrap';
                        sp2.className = 'q0';
                        $WH.ae(td, $WH.ce('br'));
                        $WH.st(sp2, buff2);
                        $WH.ae(td, sp2);
                    }
                },
                getVisibleText: function (spell) {
                    if (!spell.stackRule) {
                        return;
                    }

                    var buff = '';
                    switch (spell.stackRule) {
                        case 3:
                            buff += '(strongest effect is applied)';
                        case 0:
                            buff += ' coexist';
                            break;
                        case 2:
                            buff += '(from same caster)';
                        case 1:
                            buff += ' exclusive';
                            break;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(a.stackRule, b.stackRule);
                }
            },
            {
                id: 'linkedTrigger',
                name: 'Triggers',
                type: 'text',
                width: '50%',
                hidden: true,
                compute: function(spell, td) {
                    if (!spell.linked) {
                        return;
                    }

                    var
                        trigger = spell.linked[0],
                        buff    = '';

                    switch (spell.linked[2]) {
                        case 0:
                            buff = trigger > 0 ? 'When Spell is casted' : 'When Aura is removed';
                            break;
                        case 1:
                            buff = 'When Spell hits the target(s)';
                            break;
                        case 2:
                            buff = 'When Aura is applied and removed';
                            break;
                    }

                    td.className = 'small';
                    td.style.lineHeight = '18px';

                    var a = $WH.ce('a');
                    a.style.whiteSpace = 'nowrap';
                    a.href = '?spell=' + Math.abs(trigger);
                    if (g_pageInfo.typeId == Math.abs(trigger)) { // ponts to self
                        a.className = 'q1';
                        $WH.st(a, 'This');
                    }
                    else {
                        var item = g_spells[Math.abs(trigger)];
                        a.className = 'icontiny tinyspecial';
                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item['icon'] + '.gif)';
                        $WH.st(a, item['name_' + g_locale.name]);
                    }
                    $WH.ae(td, a);

                    var span = $WH.ce('span');
                    span.className = 'q0';
                    $WH.st(span, buff);
                    $WH.ae(td, $WH.ce('br'));
                    $WH.ae(td, span);

                },
                getVisibleText: function (spell) {
                    if (!spell.linked) {
                        return;
                    }

                    var
                        trigger = spell.linked[0],
                        buff    = '';

                    if (g_pageInfo.typeId == Math.abs(trigger)) {
                        buff += 'This';
                    }
                    else {
                        buff += g_spells[Math.abs(trigger)]['name_' + g_locale.name];
                    }

                    switch (spell.linked[2]) {
                        case 0:
                            buff += trigger > 0 ? ' When Spell is casted' : ' When Aura is removed';
                            break;
                        case 1:
                            buff += ' When Spell hits the target(s)';
                            break;
                        case 2:
                            buff += ' When Aura is applied and removed';
                            break;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var
                        trA = a.linked[0],
                        trB = b.linked[0];

                    if (trA > 0 && trB < 0) {
                        return -1;
                    }
                    else if (trA < 0 && trB > 0) {
                        return 1;
                    }

                    if (g_pageInfo.typeId == Math.abs(trA)) {
                        return 1;
                    }
                    else if (g_pageInfo.typeId == Math.abs(trB)) {
                        return -1;
                    }
                    else if (trA != trB) {
                        return $WH.strcmp(g_spells[Math.abs(trA)]['name_' + g_locale.name],  g_spells[Math.abs(trB)]['name_' + g_locale.name]);
                    }

                    return 0;
                }
            },
            {
                id: 'linkedEffect',
                name: 'Effects',
                type: 'text',
                width: '50%',
                hidden: true,
                compute: function(spell, td) {
                    if (!spell.linked) {
                        return;
                    }

                    var
                        effect = spell.linked[1],
                        buff   = '';

                    switch (spell.linked[2]) {
                        case 0:
                        case 1:
                            buff = effect > 0 ? 'Spell is triggered' : 'Spells Auras are removed';
                            break;
                        case 2:
                            buff = effect > 0 ? 'Spells Auras are applied or removed' : 'Immunity against Spell is applied or cleared ';
                            break;
                    }

                    td.className = 'small';
                    td.style.lineHeight = '18px';

                    var a = $WH.ce('a');
                    a.style.whiteSpace = 'nowrap';
                    a.href = '?spell=' + Math.abs(effect);
                    if (g_pageInfo.typeId == Math.abs(effect)) { // ponts to self
                        a.className = 'q1';
                        $WH.st(a, 'This');
                    }
                    else {
                        var item = g_spells[Math.abs(effect)];
                        a.className = 'icontiny tinyspecial';
                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item['icon'] + '.gif)';
                        $WH.st(a, item['name_' + g_locale.name]);
                    }
                    $WH.ae(td, a);

                    var span = $WH.ce('span');
                    span.className = 'q0';
                    $WH.st(span, buff);
                    $WH.ae(td, $WH.ce('br'));
                    $WH.ae(td, span);

                },
                getVisibleText: function (spell) {
                    if (!spell.linked) {
                        return;
                    }

                    var
                        effect = spell.linked[1],
                        buff   = '';

                    if (g_pageInfo.typeId == Math.abs(effect)) {
                        buff += 'This';
                    }
                    else {
                        buff += g_spells[Math.abs(effect)]['name_' + g_locale.name];
                    }

                    switch (spell.linked[2]) {
                        case 0:
                        case 1:
                            buff += effect > 0 ? ' Spell is triggered' : ' Spells Auras are removed';
                            break;
                        case 2:
                            buff += effect > 0 ? ' Spells Auras are applied or removed' : ' Immunity against Spell is applied or cleared ';
                            break;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var
                        effA = a.linked[1],
                        effB = b.linked[1];

                    if (effA > 0 && effB < 0) {
                        return -1;
                    }
                    else if (effA < 0 && effB > 0) {
                        return 1;
                    }

                    if (g_pageInfo.typeId == Math.abs(effA)) {
                        return 1;
                    }
                    else if (g_pageInfo.typeId == Math.abs(effB)) {
                        return -1;
                    }
                    else if (effA != effB) {
                        return $WH.strcmp(g_spells[Math.abs(effA)]['name_' + g_locale.name],  g_spells[Math.abs(effB)]['name_' + g_locale.name]);
                    }

                    return 0;
                }
            }
        ],

        getItemLink: function(spell) {
            return '?spell=' + spell.id;
        }
    },

    zone: {
        sort: [1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(zone, td) {
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(zone);
                    $WH.ae(a, $WH.ct(zone.name));
                    if (zone.expansion) {
                        var sp = $WH.ce('span');
                            sp.className = g_GetExpansionClassName(zone.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(td, sp);
                    }
                    else {
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(zone) {
                    var buff = zone.name + Listview.funcBox.getExpansionText(zone);

                    if (zone.instance == 5 || zone.instance == 8) {
                        buff += ' heroic';
                    }

                    return buff;
                }
            },
            {
                id: 'level',
                name: LANG.level,
                type: 'range',
                width: '10%',
                getMinValue: function(zone) {
                    return zone.minlevel;
                },
                getMaxValue: function(zone) {
                    return zone.maxlevel;
                },
                compute: function(zone, td) {
                    if (zone.minlevel > 0 && zone.maxlevel > 0) {
                        if (zone.minlevel != zone.maxlevel) {
                            return zone.minlevel + LANG.hyphen + zone.maxlevel;
                        }
                        else {
                            return zone.minlevel;
                        }
                    }
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel);
                    }
                }
            },
            {
                id: 'players',
                name: LANG.players,
                type: 'text',
                hidden: true,
                compute: function(zone, td) {
                    if (zone.instance > 0) {
                        var sp = $WH.ce('span');

                        if (zone.nplayers == -2) {
                            zone.nplayers = '10/25';
                        }

                        var buff = '';
                        if (zone.nplayers) {
                            if (zone.instance == 4) {
                                buff += $WH.sprintf(LANG.lvzone_xvx, zone.nplayers, zone.nplayers);
                            }
                            else {
                                buff += $WH.sprintf(LANG.lvzone_xman, zone.nplayers);
                            }
                        }

                        $WH.ae(sp, $WH.ct(buff));
                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(zone) {
                    if (zone.instance > 0) {
                        if (zone.nplayers == -2) {
                            zone.nplayers = '10/25';
                        }

                        var buff = '';
                        if (zone.nplayers && ((zone.instance != 2 && zone.instance != 5) || zone.nplayers > 5)) {
                            if (zone.instance == 4) {
                                buff += $WH.sprintf(LANG.lvzone_xvx, zone.nplayers, zone.nplayers);
                            }
                            else {
                                buff += $WH.sprintf(LANG.lvzone_xman, zone.nplayers);
                            }
                        }
                        return buff;
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(a.nplayers, b.nplayers);
                }
            },
            {
                id: 'territory',
                name: LANG.territory,
                type: 'text',
                width: '13%',
                compute: function(zone, td) {
                    var sp = $WH.ce('span');
                    switch (zone.territory) {
                    case 0:
                        sp.className = 'alliance-icon';
                        break;
                    case 1:
                        sp.className = 'horde-icon';
                        break;
                    case 4:
                        sp.className = 'ffapvp-icon';
                        break;
                    }

                    $WH.ae(sp, $WH.ct(g_zone_territories[zone.territory]));
                    $WH.ae(td, sp);
                },
                getVisibleText: function(zone) {
                    return g_zone_territories[zone.territory];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_zone_territories[a.territory], g_zone_territories[b.territory]);
                }
            },
            {
                id: 'instancetype',
                name: LANG.instancetype,
                type: 'text',
                compute: function(zone, td) {
                    if (zone.instance > 0) {
                        var sp = $WH.ce('span');
                        if ((zone.instance >= 1 && zone.instance <= 5) || zone.instance == 7 || zone.instance == 8) {
                            sp.className = 'instance-icon' + zone.instance;
                        }

                        var buff = g_zone_instancetypes[zone.instance];

                        if (zone.heroicLevel) {
                            var skull = $WH.ce('span');
                            skull.className = 'heroic-icon';
                            g_addTooltip(skull, LANG.tooltip_heroicmodeavailable + LANG.qty.replace('$1', zone.heroicLevel));
                            $WH.ae(td, skull);
                        }

                        $WH.ae(sp, $WH.ct(buff));
                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(zone) {
                    if (zone.instance > 0) {
                        var buff = g_zone_instancetypes[zone.instance];

                        if (zone.nplayers && ((zone.instance != 2 && zone.instance != 5) || zone.nplayers > 5)) {
                            if (zone.instance == 4) {
                                buff += ' ' + $WH.sprintf(LANG.lvzone_xvx, zone.nplayers, zone.nplayers);
                            }
                            else {
                                buff += ' ' + $WH.sprintf(LANG.lvzone_xman, zone.nplayers);
                            }
                        }
                        if (zone.instance == 5 || zone.instance == 8) {
                            buff += ' heroic';
                        }

                        return buff;
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_zone_instancetypes[a.instance], g_zone_instancetypes[b.instance]) || $WH.strcmp(a.instance, b.instance) || $WH.strcmp(a.nplayers, b.nplayers);
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(zone, td) {
                    td.className = 'small q1';
                    var a = $WH.ce('a');
                    a.href = '?zones=' + zone.category;
                    $WH.ae(a, $WH.ct(g_zone_categories[zone.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(zone) {
                    return g_zone_categories[zone.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_zone_categories[a.category], g_zone_categories[b.category]);
                }
            }
        ],

        getItemLink: function(zone) {
            return '?zone=' + zone.id;
        }
    },

    holiday: {
        sort: [2, 1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                span: 2,
                compute: function(holiday, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, g_holidays.createIcon(holiday.id, 0));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(holiday);
                    $WH.ae(a, $WH.ct(holiday.name));
                    $WH.ae(td, a);
                },
                getVisibleText: function(holiday) {
                    return holiday.name;
                }
            },
            {
                id: 'date',
                name: LANG.date,
                type: 'text',
                width: '16%',
                allText: true,
                compute: function(holiday, td, tr) {
                    if (holiday.startDate && holiday.endDate) {
                        var dates = Listview.funcBox.getEventNextDates(holiday.startDate, holiday.endDate, holiday.rec || 0);

                        if (dates[0] && dates[1]) {
                            var
                                start = g_formatDateSimple(dates[0]),
                                end = g_formatDateSimple(dates[1]),
                                sp = $WH.ce('span');

                            if (start != end) {
                                $WH.st(sp, start + LANG.hyphen + end);
                            }
                            else {
                                $WH.st(sp, start);
                            }
                            $WH.ae(td, sp);

                            if (dates[0] <= g_serverTime && dates[1] >= g_serverTime) {
                                tr.className = 'checked';
                                sp.className = 'q2 tip';
                                g_addTooltip(sp, LANG.tooltip_activeholiday, 'q');
                            }
                        }
                    }
                },
                getVisibleText: function(holiday) {
                    if (holiday.startDate && holiday.endDate) {
                        var dates = Listview.funcBox.getEventNextDates(holiday.startDate, holiday.endDate, holiday.rec || 0);

                        if (dates[0] && dates[1]) {
                            var
                                start = g_formatDateSimple(dates[0]),
                                end = g_formatDateSimple(dates[1]);

                            if (start != end) {
                                return start + LANG.hyphen + end;
                            }
                            else {
                                return start;
                            }
                        }
                    }

                    return'';
                },
                sortFunc: function(a, b, col) {
                    if (a.startDate && b.startDate) {
                        var datesA = Listview.funcBox.getEventNextDates(a.startDate, a.endDate, a.rec || 0);
                        var datesB = Listview.funcBox.getEventNextDates(b.startDate, b.endDate, b.rec || 0);

                        if (datesA[0] && datesB[0]) {
                            return datesA[0] - datesB[0];
                        }
                    }
                    else if (a.startDate) {
                        return -1;
                    }
                    else if (b.startDate) {
                        return 1;
                    }

                    return 0;
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '16%',
                compute: function(holiday, td) {
                    td.className = 'small q1';
                    var
                        a = $WH.ce('a'),
                        href = '?events=' + holiday.category;

                    a.href = href;
                    $WH.ae(a, $WH.ct(g_holiday_categories[holiday.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(holiday) {
                    return g_holiday_categories[holiday.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_holiday_categories[a.category], g_holiday_categories[b.category]);
                }
            }
        ],

        getItemLink: function(holiday) {
            return '?event=' + holiday.id;
        }
    },

    holidaycal: {
        sort: [1],
        mode: 4, // Calendar
        startOnMonth: new Date(g_serverTime.getFullYear(), 0, 1),
        nMonthsToDisplay: 12,
        rowOffset: g_serverTime.getMonth(),
        poundable: 2, // Yes but w/o sort

        columns: [],

        compute: function(holiday, div, i) {
            if (!holiday.events || !holiday.events.length) {
                return;
            }

            for (var i = 0; i < holiday.events.length; ++i) {
                var icon = g_holidays.createIcon(holiday.events[i].id, 1);
                icon.onmouseover = Listview.funcBox.dateEventOver.bind(icon, holiday.date, holiday.events[i]);
                icon.onmousemove = $WH.Tooltip.cursorUpdate;
                icon.onmouseout  = $WH.Tooltip.hide;
                icon.style.cssFloat = icon.style.styleFloat = 'left';
                $WH.ae(div, icon);
            }
        },
        sortFunc: function(a, b) {
            if (a.startDate && b.startDate) {
                var datesA = Listview.funcBox.getEventNextDates(a.startDate, a.endDate, a.rec || 0);
                var datesB = Listview.funcBox.getEventNextDates(b.startDate, b.endDate, b.rec || 0);

                for (var i = 0; i < 2; ++i) {
                    var
                        dA = datesA[i],
                        dB = datesB[i];

                    if (dA.getFullYear() == dB.getFullYear() && dA.getMonth() == dB.getMonth() && dA.getDate() == dB.getDate()) {
                        return dA - dB;
                    }
                }
            }

            return $WH.strcmp(a.name, b.name);
        }
    },

    comment: {          // todo: reformat
        sort: [1],
        mode: 2,
        nItemsPerPage: 40,
        poundable: 2,
        columns: [{
            value: "number"
        },
        {
            value: "id"
        },
        {
            value: "rating"
        }],
        compute: function(J, ac, ab) {
            var ag, I = new Date(J.date),
            Y = (g_serverTime - I) / 1000,
            h = (g_user.roles & U_GROUP_COMMENTS_MODERATOR) != 0,
            ad = J.rating < 0 || J.purged || J.deleted || (J.__minPatch && g_getPatchVersion.T[J.__minPatch] > I),
            U = h || (J.user.toLowerCase() == g_user.name.toLowerCase() && !g_user.commentban),
            L = U && J.deleted == 0,
            d = U && J.replyTo != J.id,
            af = true,
            W = J.purged == 0 && J.deleted == 0 && g_user.id && J.user.toLowerCase() != g_user.name.toLowerCase() && $WH.in_array(J.raters, g_user.id, function(i) {
                return i[0]
            }) == -1 && !g_user.ratingban,
            p = J.rating >= 0 && (g_user.id == 0 || W || g_user.ratingban),
            G = g_users[J.user];
            J.ratable = W;
            var aa = ac;
            var N = $WH.ce("div");
            var z = $WH.ce("div");
            var t = $WH.ce("em");
            J.divHeader = N;
            J.divBody = z;
            J.divLinks = t;
            aa.className = "comment-wrapper";
            if (J.indent) {
                aa.className += " comment-indent"
            }
            if (ad) {
                aa.className += " comment-collapsed"
            }
            ac = $WH.ce("div");
            ac.className = "comment comment" + (ab % 2);
            $WH.ae(aa, ac);
            N.className = "comment-header";
            $WH.ae(ac, N);
            var n = $WH.ce("em");
            n.className = "comment-rating";
            if (ad) {
                var D = $WH.ce("a");
                D.href = "javascript:;";
                D.onclick = Listview.funcBox.coToggleVis.bind(D, J);
                $WH.ae(D, $WH.ct(LANG.lvcomment_show));
                $WH.ae(n, D);
                $WH.ae(n, $WH.ct(" " + String.fromCharCode(160) + " "))
            }
            var A = $WH.ce("b");
            var v = $WH.ce("a");
            v.href = "javascript:;";
            $WH.ae(v, $WH.ct(LANG.lvcomment_rating));
            var E = $WH.ce("span");
            E.id = "commentrating" + J.id;
            Listview.funcBox.coDisplayRating(J, E);
            v.onclick = Listview.funcBox.coToggleRating.bind(this, J, E);
            $WH.ae(v, E);
            $WH.ae(A, v);
            $WH.ae(n, A);
            $WH.ae(n, $WH.ct(" "));
            var S = $WH.ce("span");
            var q = $WH.ce("a"),
            af = $WH.ce("a");
            if (W) {
                q.href = af.href = "javascript:;";
                q.onclick = Listview.funcBox.coRate.bind(q, J, 1);
                af.onclick = Listview.funcBox.coRate.bind(af, J, -1);
                if (h) {
                    var R = $WH.ce("a");
                    R.href = "javascript:;";
                    R.onclick = Listview.funcBox.coRate.bind(R, J, 0);
                    R.onmouseover = Listview.funcBox.coCustomRatingOver;
                    R.onmousemove = $WH.Tooltip.cursorUpdate;
                    R.onmouseout = $WH.Tooltip.hide;
                    $WH.ae(R, $WH.ct("[~]"))
                    $WH.ae(S, R);
                    $WH.ae(S, $WH.ct(" "))
                }
            } else {
                if (g_user.ratingban) {
                    q.href = af.href = "javascript:;"
                } else {
                    q.href = af.href = "?account=signin"
                }
            }
            $WH.ae(q, $WH.ct("[+]"))
            if (!g_user.ratingban) {
                q.onmouseover = Listview.funcBox.coPlusRatingOver;
                af.onmouseover = Listview.funcBox.coMinusRatingOver;
                q.onmousemove = af.onmousemove = $WH.Tooltip.cursorUpdate;
                q.onmouseout = af.onmouseout = $WH.Tooltip.hide
            } else {
                g_addTooltip(q, LANG.tooltip_banned_rating, "q");
                g_addTooltip(af, LANG.tooltip_banned_rating, "q")
            }
            $WH.ae(af, $WH.ct("[-]"))
            $WH.ae(S, af);
            $WH.ae(S, $WH.ct(" "));
            $WH.ae(S, q);
            $WH.ae(n, S);
            if (!p) {
                S.style.display = "none"
            }
            $WH.ae(N, n);
            t.className = "comment-links";
            var c = false;
            if (U) {
                var b = $WH.ce("span");
                var Q = $WH.ce("a");
                $WH.ae(Q, $WH.ct(LANG.lvcomment_edit));
                Q.onclick = Listview.funcBox.coEdit.bind(this, J, 0, false);
                $WH.ns(Q);
                Q.href = "javascript:;";
                $WH.ae(b, Q);
                c = true;
                $WH.ae(t, b)
            }
            if (L) {
                var u = $WH.ce("span");
                var F = $WH.ce("a");
                $WH.ae(F, $WH.ct(LANG.lvcomment_delete));
                F.onclick = Listview.funcBox.coDelete.bind(this, J);
                $WH.ns(F);
                F.href = "javascript:;";
                if (c) {
                    var e = $WH.ce("span");
                    e.style.color = "white";
                    $WH.ae(e, $WH.ct("|"));
                    $WH.ae(t, e)
                }
                $WH.ae(u, F);
                $WH.ae(t, u)
                c = true;
            }
            if (d) {
                var P = $WH.ce("span");
                var k = $WH.ce("a");
                $WH.ae(k, $WH.ct(LANG.lvcomment_detach));
                k.onclick = Listview.funcBox.coDetach.bind(this, J);
                $WH.ns(k);
                k.href = "javascript:;";
                if (c) {
                    var e = $WH.ce("span");
                    e.style.color = "white";
                    $WH.ae(e, $WH.ct("|"));
                    $WH.ae(t, e)
                }
                $WH.ae(P, k);
                $WH.ae(t, P)
                c = true;
            }
            if (af) {
                var K = $WH.ce("span");
                var m = $WH.ce("a");
                $WH.ae(m, $WH.ct(LANG.lvcomment_report));
                m.onclick = ContactTool.show.bind(ContactTool, {
                    mode: 1,
                    comment: J
                });
                m.className = "report-icon";
                m.href = "javascript:;";
                g_addTooltip(m, LANG.report_tooltip, "q2");
                if (c) {
                    var e = $WH.ce("span");
                    e.style.color = "white";
                    $WH.ae(e, $WH.ct("|"));
                    $WH.ae(t, e)
                }
                $WH.ae(K, m);
                $WH.ae(t, K)
                c = true;
            }
            if (!g_user.commentban) {
                var l = $WH.ce("span");
                var o = $WH.ce("a");
                $WH.ae(o, $WH.ct(LANG.lvcomment_reply));
                if (g_user.id > 0) {
                    o.onclick = Listview.funcBox.coReply.bind(this, J);
                    o.href = "javascript:;"
                } else {
                    o.href = "?account=signin"
                }
                if (c) {
                    var e = $WH.ce("span");
                    e.style.color = "white";
                    $WH.ae(e, $WH.ct("|"));
                    $WH.ae(t, e)
                }
                $WH.ae(l, o);
                $WH.ae(t, l)
                c = true;
            }
            if (ad) {
                z.style.display = "none";
                t.style.display = "none"
            }
            $WH.ae(N, t);
            var C = $WH.ce("var");
            $WH.ae(C, $WH.ct(LANG.lvcomment_by));
            aUser = $WH.ce("a");
            aUser.href = "?user=" + J.user;
            $WH.ae(aUser, $WH.ct(J.user));
            $WH.ae(C, aUser);
            $WH.ae(C, $WH.ct(" "));
            var a = $WH.ce("a");
            a.className = "q0";
            a.id = "comments:id=" + J.id;
            a.href = "#" + a.id;
            g_formatDate(a, Y, I);
            $WH.ae(C, a);
            $WH.ae(C, $WH.ct($WH.sprintf(LANG.lvcomment_patch, g_getPatchVersion(I))));
            if (G != null && G.avatar) {
                var j = Icon.createUser(G.avatar, G.avatarmore, 0, null, ((G.roles & U_GROUP_PREMIUM) ? (G.border ? 2 : 1) : 0));
                j.style.marginRight = "3px";
                j.style.cssFloat = j.style.styleFloat = "left";
                $WH.ae(N, j);
                C.style.lineHeight = "26px"
            }
            $WH.ae(N, C);
            z.className = "text comment-body" + Listview.funcBox.coGetColor(J);
            if (J.indent) {
                z.className += " comment-body-indent"
            }
            B = (this.id == "english-comments" ? "www": "");
            z.innerHTML = Markup.toHtml(J.body, {
                mode: Markup.MODE_COMMENT,
                roles: J.roles,
                locale: B
            });
            $WH.ae(ac, z);
            var H = $WH.ce("div");
            H.className = "text comment-body";
            if (J.indent) {
                H.className += " comment-body-indent"
            }
            if (J.response) {
                H.innerHTML = Markup.toHtml("[div][/div][wowheadresponse=" + J.responseuser + " roles=" + J.responseroles + "]" + J.response + "[/wowheadresponse]", {
                    allow: Markup.CLASS_STAFF,
                    roles: J.responseroles,
                    uid: "resp-" + J.id
                })
            }
            $WH.ae(ac, H);
            J.divResponse = H;
            if ((J.roles & U_GROUP_COMMENTS_MODERATOR) == 0 || g_user.roles & U_GROUP_COMMENTS_MODERATOR) {
                var X = $WH.ce("div");
                J.divLastEdit = X;
                X.className = "comment-lastedit";
                $WH.ae(X, $WH.ct(LANG.lvcomment_lastedit));
                var w = $WH.ce("a");
                $WH.ae(w, $WH.ct(" "));
                $WH.ae(X, w);
                $WH.ae(X, $WH.ct(" "));
                var O = $WH.ce("span");
                $WH.ae(X, O);
                $WH.ae(X, $WH.ct(" "));
                Listview.funcBox.coUpdateLastEdit(J);
                if (ad) {
                    X.style.display = "none"
                }
                $WH.ae(ac, X)
            }
        },
/* no idea what the new one does exactly.. so saved this old compute
        compute: function(v, K) {
            var O, u = new Date(v.date),
            I = (g_serverTime - u) / 1000,
            d = (g_user.roles & 26) != 0,
            L = v.rating < 0 || v.purged || v.deleted || (v.__minPatch && g_getPatchVersion.T[v.__minPatch] > u),
            F = d || v.user.toLowerCase() == g_user.name.toLowerCase(),
            y = F && v.deleted == 0,
            c = F && v.replyTo != v.id,
            M = ((v.roles & 26) == 0),
            G = v.purged == 0 && v.deleted == 0 && g_user.id && v.user.toLowerCase() != g_user.name.toLowerCase() && $WH.in_array(v.raters, g_user.id, function(P) {
                return P[0]
            }) == -1,
            i = v.rating >= 0 && (g_user.id == 0 || G);
            v.ratable = G;
            K.className = "comment";
            if (v.indent) {
                K.className += " comment-indent"
            }
            var z = $WH.ce("div");
            var m = $WH.ce("div");
            var k = $WH.ce("div");
            v.divHeader = z;
            v.divBody = m;
            v.divLinks = k;
            z.className = (L ? "comment-header-bt": "comment-header");
            var g = $WH.ce("div");
            g.className = "comment-rating";
            if (L) {
                var q = $WH.ce("a");
                q.href = "javascript:;";
                q.onclick = Listview.funcBox.coToggleVis.bind(q, v);
                $WH.ae(q, $WH.ct(LANG.lvcomment_show));
                $WH.ae(g, q);
                $WH.ae(g, $WH.ct(" " + String.fromCharCode(160) + " "))
            }
            var o = $WH.ce("b");
            $WH.ae(o, $WH.ct(LANG.lvcomment_rating));
            var r = $WH.ce("span");
            $WH.ae(r, $WH.ct((v.rating > 0 ? "+": "") + v.rating));
            $WH.ae(o, r);
            $WH.ae(g, o);
            $WH.ae(g, $WH.ct(" "));
            var E = $WH.ce("span");
            var j = $WH.ce("a"),
            N = $WH.ce("a");
            if (G) {
                j.href = N.href = "javascript:;";
                j.onclick = Listview.funcBox.coRate.bind(j, v, 1);
                N.onclick = Listview.funcBox.coRate.bind(N, v, -1);
                if (d) {
                    var D = $WH.ce("a");
                    D.href = "javascript:;";
                    D.onclick = Listview.funcBox.coRate.bind(D, v, 0);
                    D.onmouseover = Listview.funcBox.coCustomRatingOver;
                    D.onmousemove = $WH.Tooltip.cursorUpdate;
                    D.onmouseout = $WH.Tooltip.hide;
                    $WH.ae(D, $WH.ct("[~]"));
                    $WH.ae(E, D);
                    $WH.ae(E, $WH.ct(" "))
                }
            } else {
                j.href = N.href = "?account=signin"
            }
            $WH.ae(j, $WH.ct("[+]"));
            j.onmouseover = Listview.funcBox.coPlusRatingOver;
            N.onmouseover = Listview.funcBox.coMinusRatingOver;
            j.onmousemove = N.onmousemove = $WH.Tooltip.cursorUpdate;
            j.onmouseout = N.onmouseout = $WH.Tooltip.hide;
            $WH.ae(N, $WH.ct("[-]"));
            $WH.ae(E, N);
            $WH.ae(E, $WH.ct(" "));
            $WH.ae(E, j);
            $WH.ae(g, E);
            if (!i) {
                E.style.display = "none"
            }
            $WH.ae(z, g);
            $WH.ae(z, $WH.ct(LANG.lvcomment_by));
            var J = $WH.ce("a");
            J.href = "?user=" + v.user;
            $WH.ae(J, $WH.ct(v.user));
            $WH.ae(z, J);
            $WH.ae(z, $WH.ct(" "));
            var a = $WH.ce("a");
            a.className = "q0";
            a.id = "comments:id=" + v.id;
            a.href = "#" + a.id;
            Listview.funcBox.coFormatDate(a, I, u);
            a.style.cursor = "pointer";
            $WH.ae(z, a);
            $WH.ae(z, $WH.ct($WH.sprintf(LANG.lvcomment_patch, g_getPatchVersion(u))));
            $WH.ae(K, z);
            m.className = "comment-body" + Listview.funcBox.coGetColor(v);
            if (v.indent) {
                m.className += " comment-body-indent"
            }
            m.innerHTML = Markup.toHtml(v.body, {
                mode: Markup.MODE_COMMENT,
                roles: v.roles
            });
            $WH.ae(K, m);
            if ((v.roles & 26) == 0 || g_user.roles & 26) {
                var H = $WH.ce("div");
                v.divLastEdit = H;
                H.className = "comment-lastedit";
                $WH.ae(H, $WH.ct(LANG.lvcomment_lastedit));
                var p = $WH.ce("a");
                $WH.ae(p, $WH.ct(" "));
                $WH.ae(H, p);
                $WH.ae(H, $WH.ct(" "));
                var C = $WH.ce("span");
                $WH.ae(H, C);
                $WH.ae(H, $WH.ct(" "));
                Listview.funcBox.coUpdateLastEdit(v);
                if (L) {
                    H.style.display = "none"
                }
                $WH.ae(K, H)
            }
            k.className = "comment-links";
            if (F) {
                var b = $WH.ce("span");
                var B = $WH.ce("a");
                $WH.ae(B, $WH.ct(LANG.lvcomment_edit));
                B.onclick = Listview.funcBox.coEdit.bind(this, v, 0);
                $WH.ns(B);
                B.href = "javascript:;";
                $WH.ae(b, B);
                $WH.ae(b, $WH.ct("|"));
                $WH.ae(k, b)
            }
            if (y) {
                var l = $WH.ce("span");
                var t = $WH.ce("a");
                $WH.ae(t, $WH.ct(LANG.lvcomment_delete));
                t.onclick = Listview.funcBox.coDelete.bind(this, v);
                $WH.ns(t);
                t.href = "javascript:;";
                $WH.ae(l, t);
                $WH.ae(l, $WH.ct("|"));
                $WH.ae(k, l)
            }
            if (c) {
                var A = $WH.ce("span");
                var e = $WH.ce("a");
                $WH.ae(e, $WH.ct(LANG.lvcomment_detach));
                e.onclick = Listview.funcBox.coDetach.bind(this, v);
                $WH.ns(e);
                e.href = "javascript:;";
                $WH.ae(A, e);
                $WH.ae(A, $WH.ct("|"));
                $WH.ae(k, A)
            }
            if (M) {
                var w = $WH.ce("span");
                var f = $WH.ce("a");
                $WH.ae(f, $WH.ct(LANG.lvcomment_report));
                if (g_user.id > 0) {
                    f.onclick = Listview.funcBox.coReportClick.bind(f, v, 0);
                    f.href = "javascript:;"
                } else {
                    f.href = "?account=signin"
                }
                $WH.ae(w, f);
                $WH.ae(w, $WH.ct("|"));
                $WH.ae(k, w)
            }
            var h = $WH.ce("a");
            $WH.ae(h, $WH.ct(LANG.lvcomment_reply));
            if (g_user.id > 0) {
                h.onclick = Listview.funcBox.coReply.bind(this, v);
                h.href = "javascript:;"
            } else {
                h.href = "?account=signin"
            }
            $WH.ae(k, h);
            if (L) {
                m.style.display = "none";
                k.style.display = "none"
            }
            $WH.ae(K, k)
        }, */
        createNote: function(b) {
            var g = $WH.ce("small");
            if (!g_user.commentban) {
                var l = $WH.ce("a");
                if (g_user.id > 0) {
                    l.href = "javascript:;";
                    l.onclick = co_addYourComment
                } else {
                    l.href = "?account=signin"
                }
                $WH.ae(l, $WH.ct(LANG.lvcomment_add));
                $WH.ae(g, l);
                var e = $WH.ce("span");
                e.style.padding = "0 5px";
                e.style.color = "white";
                $WH.ae(e, $WH.ct("|"));
                $WH.ae(g, e);
            }
            $WH.ae(g, $WH.ct(LANG.lvcomment_sort));
            var m = $WH.ce("a");
            m.href = "javascript:;";
            $WH.ae(m, $WH.ct(LANG.lvcomment_sortdate));
            m.onclick = Listview.funcBox.coSortDate.bind(this, m);
            $WH.ae(g, m);
            $WH.ae(g, $WH.ct(LANG.comma));
            var o = $WH.ce("a");
            o.href = "javascript:;";
            $WH.ae(o, $WH.ct(LANG.lvcomment_sortrating));
            o.onclick = Listview.funcBox.coSortHighestRatedFirst.bind(this, o);
            $WH.ae(g, o);
            var h = $WH.gc("temp_comment_sort") || 1;
            if (h == "2") {
                o.onclick()
            } else {
                m.onclick()
            }
            var e = $WH.ce("span");
            e.style.padding = "0 5px";
            e.style.color = "white";
            $WH.ae(e, $WH.ct("|"));
            $WH.ae(g, e);
            var q = $WH.ce("select");
            var f = $WH.ce("option");
            f.value = 0;
            f.selected = "selected";
            $WH.ae(q, f);
            var k = {};
            for (var i = 0; i < this.data.length; ++i) {
                var h = new Date(this.data[i].date).getTime();
                k[g_getPatchVersionIndex(h)] = true
            }
            var j = [];
            for (var c in k) {
                j.push(c)
            }
            j.sort(function(p, d) {
                return d - p
            });
            for (var c = 0; c < j.length; ++c) {
                var f = $WH.ce("option");
                f.value = j[c];
                $WH.ae(f, $WH.ct(g_getPatchVersion.V[j[c]]));
                $WH.ae(q, f)
            }
            q.onchange = Listview.funcBox.coFilterByPatchVersion.bind(this, q);
            $WH.ae(g, $WH.ct(LANG.lvcomment_patchfilter));
            $WH.ae(g, q);
            $WH.ae(b, g);
        },
        onNoData: function(c) {
            var a = "<b>" + LANG.lvnodata_co1 + '</b><div class="pad2"></div>';
            if (g_user.id > 0) {
                var b = LANG.lvnodata_co2;
                b = b.replace("<a>", '<a href="javascript:;" onclick="co_addYourComment()" onmousedown="return false">');
                a += b
            } else {
                var b = LANG.lvnodata_co3;
                b = b.replace("<a>", '<a href="?account=signin">');
                b = b.replace("<a>", '<a href="?account=signup">');
                a += b
            }
            c.style.padding = "1.5em 0";
            c.innerHTML = a
        },
        onBeforeCreate: function() {
            if (location.hash && location.hash.match(/:id=([0-9]+)/) != null) {
                var a = $WH.in_array(this.data, parseInt(RegExp.$1), function(b) {
                    return b.id
                });
                this.rowOffset = this.getRowOffset(a);
                return this.data[a]
            }
        },
        onAfterCreate: function(a) {
            if (a != null) {
                var b = a.__div;
                this.tabs.__st = b;
                b.firstChild.style.border = "1px solid #505050"
            }
        }
    },

    commentpreview: {
        sort: [4],
        nItemsPerPage: 75,

        columns: [
            {
                id: 'subject',
                name: LANG.subject,
                align: 'left',
                value: 'subject',
                compute: function(comment, td) {
                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(comment);
                    $WH.ae(a, $WH.ct(comment.subject));
                    $WH.ae(td, a);

                    if (LANG.types[comment.type]) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct(LANG.types[comment.type][0]));
                        $WH.ae(td, d);
                    }
                }
            },
            {
                id: 'preview',
                name: LANG.preview,
                align: 'left',
                width: '50%',
                value: 'preview',
                compute: function(comment, td, tr) {
                    var d = $WH.ce('div');
                    d.className = 'crop';
                    if (comment.rating >= 10) {
                        d.className += ' comment-green';
                    }

                    $WH.ae(d, $WH.ct(
                        Markup.removeTags(comment.preview, {mode: Markup.MODE_ARTICLE})
                    ));
                    $WH.ae(td, d);

                    if (comment.rating || comment.deleted) {
                        d = $WH.ce('div');
                        d.className = 'small3';

                        if (comment.rating) {
                            $WH.ae(d, $WH.ct(LANG.lvcomment_rating + (comment.rating > 0 ? '+': '') + comment.rating));
                        }

                        var
                            s = $WH.ce('span'),
                            foo = '';

                        s.className = 'q10';
                        if (comment.deleted) {
                            foo = LANG.lvcomment_deleted;
                        }

                        $WH.ae(s, $WH.ct(foo));
                        $WH.ae(d, s);

                        tr.__status = s;

                        $WH.ae(td, d);
                    }
                }
            },
            {
                id: 'author',
                name: LANG.author,
                value: 'user',
                compute: function(comment, td) {
                    td.className = 'q1';

                    var a = $WH.ce('a');
                    a.href = '?user=' + comment.user;
                    $WH.ae(a, $WH.ct(comment.user));
                    $WH.ae(td, a);
                }
            },
            {
                id: 'posted',
                name: LANG.posted,
                width: '16%',
                value: 'elapsed',
                compute: function(comment, td) {
                    var
                        postedOn = new Date(comment.date),
                        elapsed  = (g_serverTime - postedOn) / 1000;

                        var s = $WH.ce('span');
                    g_formatDate(s, elapsed, postedOn, 0, 1);
                    $WH.ae(td, s);
                }
            }
        ],

        getItemLink: function(comment) {
            return '?' + g_types[comment.type] + '=' + comment.typeId + (comment.id != null ? '#comments:id=' + comment.id: '')
        }
    },

    screenshot: {
        sort: [],
        mode: 3, // Grid mode
        nItemsPerPage: 40,
        nItemsPerRow: 4,
        poundable: 2, // Yes but w/o sort

        columns: [],

        compute: function(screenshot, td, i) {
            var
                _,
                postedOn = new Date(screenshot.date),
                elapsed  = (g_serverTime - postedOn) / 1000;

            td.className = 'screenshot-cell';
            td.vAlign = 'bottom';

            var a = $WH.ce('a');
            a.href = '#screenshots:id=' + screenshot.id;
            a.onclick = $WH.rf2;

            var
                img = $WH.ce('img'),
                scale = Math.min(150 / screenshot.width, 150 / screenshot.height);

            img.src = g_staticUrl + '/uploads/screenshots/thumb/' + screenshot.id + '.jpg';
            //img.width  = Math.round(scale * screenshot.width);
            //img.height = Math.round(scale * screenshot.height);
            $WH.ae(a, img);

            $WH.ae(td, a);

            var d = $WH.ce('div');
            d.className = 'screenshot-cell-user';

            var hasUser = (screenshot.user != null && screenshot.user.length);

            if (hasUser) {
                a = $WH.ce('a');
                a.href = '?user=' + screenshot.user;
                $WH.ae(a, $WH.ct(screenshot.user));
                $WH.ae(d, $WH.ct(LANG.lvscreenshot_from));
                $WH.ae(d, a);
                $WH.ae(d, $WH.ct(' '));
            }

            var s = $WH.ce('span');
            if (hasUser) {
                g_formatDate(s, elapsed, postedOn);
            }
            else {
                g_formatDate(s, elapsed, postedOn, 0, 1);
            }
            $WH.ae(d, s);

            $WH.ae(d, $WH.ct(' ' + LANG.dash + ' '));
            var a = $WH.ce('a');
            a.href = 'javascript:;';
            a.onclick = ContactTool.show.bind(ContactTool, {
                mode: 3,
                screenshot: screenshot
            });

            a.className = 'report-icon';

            g_addTooltip(a, LANG.report_tooltip, 'q2');
            $WH.ae(a, $WH.ct(LANG.report));
            $WH.ae(d, a);

            $WH.ae(td, d);

            d = $WH.ce('div');
            d.style.position = 'relative';
            d.style.height = '1em';

            var hasCaption = (screenshot.caption != null && screenshot.caption.length);
            var hasSubject = (screenshot.subject != null && screenshot.subject.length);

            if (hasCaption || hasSubject) {
                var d2 = $WH.ce('div');
                d2.className = 'screenshot-caption';

                if (hasSubject) {
                    var foo1 = $WH.ce('small');
                    $WH.ae(foo1, $WH.ct(LANG.types[screenshot.type][0] + LANG.colon));
                    var foo2 = $WH.ce('a');
                    $WH.ae(foo2, $WH.ct(screenshot.subject));
                    foo2.href = '?' + g_types[screenshot.type] + '=' + screenshot.typeId;
                    $WH.ae(foo1, foo2);
                    $WH.ae(d2, foo1);

                    if (hasCaption && screenshot.caption.length) {
                        $WH.ae(foo1, $WH.ct(' (...)'));
                    }
                    $WH.ae(foo1, $WH.ce('br'));
                }

                if (hasCaption) {
                    $WH.aE(td, 'mouseover', Listview.funcBox.ssCellOver.bind(d2));
                    $WH.aE(td, 'mouseout', Listview.funcBox.ssCellOut.bind(d2));

                    var foo = $WH.ce('span');
                    foo.innerHTML = Markup.toHtml(screenshot.caption, {
                        mode: Markup.MODE_SIGNATURE
                    });
                    $WH.ae(d2, foo);
                }

                $WH.ae(d, d2);
            }

            $WH.aE(td, 'click', Listview.funcBox.ssCellClick.bind(this, i));

            $WH.ae(td, d);
        },

        createNote: function(div) {
            if (typeof g_pageInfo == 'object' && g_pageInfo.type > 0) {
                var sm = $WH.ce('small');

                var a = $WH.ce('a');
                if (g_user.id > 0) {
                    a.href = 'javascript:;';
                    a.onclick = ss_submitAScreenshot;
                }
                else {
                    a.href = '?account=signin';
                }
                $WH.ae(a, $WH.ct(LANG.lvscreenshot_submit));
                $WH.ae(sm, a);

                $WH.ae(div, sm);
            }
        },

        onNoData: function(div) {
            if (typeof g_pageInfo == 'object' && g_pageInfo.type > 0) {
                var s = '<b>' + LANG.lvnodata_ss1 + '</b><div class="pad2"></div>';

                if (g_user.id > 0) {
                    var foo = LANG.lvnodata_ss2;
                    foo = foo.replace('<a>', '<a href="javascript:;" onclick="ss_submitAScreenshot()" onmousedown="return false">');
                    s += foo;
                }
                else {
                    var foo = LANG.lvnodata_ss3;
                    foo = foo.replace('<a>', '<a href="?account=signin">');
                    foo = foo.replace('<a>', '<a href="?account=signup">');
                    s += foo;
                }

                div.style.padding = '1.5em 0';
                div.innerHTML = s;
            }
            else {
                return -1;
            }
        },

        onBeforeCreate: function() {
            if (location.hash && location.hash.match(/:id=([0-9]+)/) != null) {
                var rowNo = $WH.in_array(this.data, parseInt(RegExp.$1), function(x) {
                    return x.id;
                });
                this.rowOffset = this.getRowOffset(rowNo);
                return rowNo;
            }
        },

        onAfterCreate: function(rowNo) {
            if (rowNo != null) {
                setTimeout((function() {
                    ScreenshotViewer.show({
                        screenshots: this.data,
                        pos: rowNo
                    })
                }).bind(this), 1);
            }
        }
    },

    video: {
        sort: [],
        mode: 3, // Grid mode
        nItemsPerPage: 40,
        nItemsPerRow: 4,
        poundable: 2, // Yes but w/o sort

        columns: [],

        compute: function(video, td, i) {
            var
                _,
                postedOn = new Date(video.date),
                elapsed = (g_serverTime - postedOn) / 1000;

                td.className = 'screenshot-cell';
            td.vAlign = 'bottom';

            var a = $WH.ce('a');
            a.href = '#videos:id=' + video.id;
            a.onclick = $WH.rf2;

            var img = $WH.ce('img');
            img.src = $WH.sprintf(vi_thumbnails[video.videoType], video.videoId);
            $WH.ae(a, img);

            $WH.ae(td, a);

            var d = $WH.ce('div');
            d.className = 'screenshot-cell-user';

            var hasUser = (video.user != null && video.user.length);

            if (hasUser) {
                a = $WH.ce('a');
                a.href = '?user=' + video.user;
                $WH.ae(a, $WH.ct(video.user));
                $WH.ae(d, $WH.ct(LANG.lvvideo_from));
                $WH.ae(d, a);
                $WH.ae(d, $WH.ct(' '));
            }

            var s = $WH.ce('span');
            if (hasUser) {
                g_formatDate(s, elapsed, postedOn);
            }
            else {
                g_formatDate(s, elapsed, postedOn, 0, 1);
            }
            $WH.ae(d, s);

            $WH.ae(d, $WH.ct(' ' + LANG.dash + ' '));
            var a = $WH.ce('a');
            a.href = 'javascript:;';
            a.onclick = ContactTool.show.bind(ContactTool, {mode: 5, video: video});
            a.className = 'icon-report';
            g_addTooltip(a, LANG.report_tooltip, 'q2');
            $WH.ae(a, $WH.ct(LANG.report));
            $WH.ae(d, a);

            $WH.ae(td, d);

            d = $WH.ce('div');
            d.style.position = 'relative';
            d.style.height = '1em';
            var hasCaption = (video.caption != null && video.caption.length);
            var hasSubject = (video.subject != null && video.subject.length);

            if (hasCaption || hasSubject) {
                var d2 = $WH.ce('div');
                d2.className = 'screenshot-caption';

                if (hasSubject) {
                    var foo1 = $WH.ce('small');
                    $WH.ae(foo1, $WH.ct(LANG.types[video.type][0] + LANG.colon));
                    var foo2 = $WH.ce('a');
                    $WH.ae(foo2, $WH.ct(video.subject));
                    foo2.href = '?' + g_types[video.type] + '=' + video.typeId;
                    $WH.ae(foo1, foo2);
                    $WH.ae(d2, foo1);

                    if (hasCaption && video.caption.length) {
                        $WH.ae(foo1, $WH.ct(' (...)'));
                    }
                    $WH.ae(foo1, $WH.ce('br'));
                }

                if (hasCaption) {
                    $WH.aE(td, 'mouseover', Listview.funcBox.ssCellOver.bind(d2));
                    $WH.aE(td, 'mouseout', Listview.funcBox.ssCellOut.bind(d2));

                    var foo = $WH.ce('span');
                    foo.innerHTML = Markup.toHtml(video.caption, {
                        mode: Markup.MODE_SIGNATURE
                    });
                    $WH.ae(d2, foo);
                }

                $WH.ae(d, d2);
            }

            $WH.aE(td, 'click', Listview.funcBox.viCellClick.bind(this, i));

            $WH.ae(td, d);
        },

        createNote: function(div) {
            if (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)) {
                if (typeof g_pageInfo == 'object' && g_pageInfo.type > 0) {
                    var sm = $WH.ce('small');

                    var a = $WH.ce('a');
                    if (g_user.id > 0) {
                        a.href = 'javascript:;';
                        a.onclick = vi_submitAVideo;
                    }
                    else {
                        a.href = '?account=signin';
                    }

                    $WH.ae(a, $WH.ct(LANG.lvvideo_suggest));
                    $WH.ae(sm, a);

                    $WH.ae(div, sm);
                }
            }
        },

        onNoData: function(div) {
            if (typeof g_pageInfo == 'object' && g_pageInfo.type > 0) {
                var s = '<b>' + LANG.lvnodata_vi1 + '</b><div class="pad2"></div>';

                if (g_user.id > 0) {
                    var foo = LANG.lvnodata_vi2;
                    foo = foo.replace('<a>', '<a href="javascript:;" onclick="vi_submitAVideo()" onmousedown="return false">');
                    s += foo;
                }
                else {
                    var foo = LANG.lvnodata_vi3;
                    foo = foo.replace('<a>', '<a href="?account=signin">');
                    foo = foo.replace('<a>', '<a href="?account=signup">');
                    s += foo;
                }

                div.style.padding = '1.5em 0';
                div.innerHTML = s;
            }
            else {
                return -1;
            }
        },

        onBeforeCreate: function() {
            if (location.hash && location.hash.match(/:id=([0-9]+)/) != null) {
                var rowNo = $WH.in_array(this.data, parseInt(RegExp.$1), function(x) {
                    return x.id;
                });
                this.rowOffset = this.getRowOffset(rowNo);
                return rowNo;
            }
        },

        onAfterCreate: function(rowNo) {
            if (rowNo != null) {
                setTimeout((function() {
                    VideoViewer.show({
                        videos: this.data,
                        pos: rowNo
                    })
                }).bind(this), 1);
            }
        }
    },

    pet: {
        sort: [1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                span: 2,
                compute: function(pet, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, Icon.create(pet.icon, 0));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(pet);
                    $WH.ae(a, $WH.ct(pet.name));

                    if (pet.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(pet.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(wrapper, sp);
                    }
                    else {
                        $WH.ae(wrapper, a);
                    }

                    if (pet.exotic) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small q1';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '0px';
                        var a = $WH.ce('a');
                        a.href = '?spell=53270';
                        $WH.ae(a, $WH.ct(LANG.lvpet_exotic));
                        $WH.ae(d, a);
                        $WH.ae(wrapper, d);
                    }
                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(pet) {
                    var buff = pet.name + Listview.funcBox.getExpansionText(pet);

                    if (pet.exotic) {
                        buff += ' ' + LANG.lvpet_exotic;
                    }

                    return buff;
                }
            },
            {
                id: 'level',
                name: LANG.level,
                type: 'range',
                getMinValue: function(pet) {
                    return pet.minlevel;
                },
                getMaxValue: function(pet) {
                    return pet.maxlevel;
                },
                compute: function(pet, td) {
                    if (pet.minlevel > 0 && pet.maxlevel > 0) {
                        if (pet.minlevel != pet.maxlevel) {
                            return pet.minlevel + LANG.hyphen + pet.maxlevel;
                        }
                        else {
                            return pet.minlevel;
                        }
                    }
                    else {
                        return -1;
                    }
                },
                sortFunc: function(a, b, col) {
                    if (col > 0) {
                        return $WH.strcmp(a.minlevel, b.minlevel) || $WH.strcmp(a.maxlevel, b.maxlevel);
                    }
                    else {
                        return $WH.strcmp(a.maxlevel, b.maxlevel) || $WH.strcmp(a.minlevel, b.minlevel);
                    }
                }
            },
            {
                id: 'damage',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.damage,
                value: 'damage',
                hidden: 1,
                compute: function (pet, col) {
                    $WH.ae(col, this.template.getStatPct(pet.damage))
                }
            },
            {
                id: 'armor',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.armor,
                value: 'armor',
                hidden: 1,
                compute: function (pet, col) {
                    $WH.ae(col, this.template.getStatPct(pet.armor))
                }
            },
            {
                id: 'health',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.health,
                value: 'health',
                hidden: 1,
                compute: function (pet, col) {
                    $WH.ae(col, this.template.getStatPct(pet.health))
                }
            },
            {
                id: 'abilities',
                name: LANG.abilities,
                type: 'text',
                getValue: function(pet) {
                    if (!pet.spells) {
                        return '';
                    }

                    if (pet.spells.length > 0) {
                        var spells = '';
                        for (var i = 0, len = pet.spells.length; i < len; ++i) {
                            if (pet.spells[i]) {
                                spells += g_spells[pet.spells[i]]['name_' + g_locale.name];
                            }
                        }
                        return spells;
                    }
                },
                compute: function(pet, td) {
                    if (!pet.spells) {
                        return '';
                    }

                    if (pet.spells.length > 0) {
                        td.style.padding = '0';
                        Listview.funcBox.createCenteredIcons(pet.spells, td, '', 1);
                    }
                },
                sortFunc: function(a, b) {
                    if (!a.spells || !b.spells) {
                        return 0;
                    }

                    return $WH.strcmp(a.spellCount, b.spellCount) || $WH.strcmp(a.spells, b.spells);
                },
                hidden: true
            },
            {
                id: 'diet',
                name: LANG.diet,
                type: 'text',
                compute: function(pet, td) {
                    if (td) {
                        td.className = 'small';
                    }

                    var
                        i = 0,
                        foods = '';

                    for (var food in g_pet_foods) {
                        if (pet.diet & food) {
                            if (i++ > 0) {
                                foods += LANG.comma;
                            }
                            foods += g_pet_foods[food];
                        }
                    }
                    return foods;
                },
                sortFunc: function(a, b) {
                    return $WH.strcmp(b.foodCount, a.foodCount) || Listview.funcBox.assocArrCmp(a.diet, b.diet, g_pet_foods);
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                compute: function(pet, td) {
                    if (pet.type != null) {
                        td.className = 'small q1';
                        var a = $WH.ce('a');
                        a.href = '?pets=' + pet.type;
                        $WH.ae(a, $WH.ct(g_pet_types[pet.type]));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function(pet) {
                    if (pet.type != null) {
                        return g_pet_types[pet.type];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_pet_types[a.type], g_pet_types[b.type]);
                }
            }
        ],

        getItemLink: function(pet) {
            return '?pet=' + pet.id;
        },

        getStatPct: function(modifier) {
            var _ = $WH.ce('span');
            if (!isNaN(modifier) && modifier > 0) {
                _.className = 'q2';
                $WH.ae(_, $WH.ct('+' + modifier + '%'));
            }
            else if (!isNaN(modifier) && modifier < 0) {
                _.className = 'q10';
                $WH.ae(_, $WH.ct(modifier + '%'));
            }

            return _;
        }
    },

    achievement: {
        sort: [1, 2],
        nItemsPerPage: 100,
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                span: 2,
                compute: function(achievement, td, tr) {
                    var rel = null;
                    if (achievement.who && achievement.completed) {
                        rel = 'who=' + achievement.who + '&when=' + achievement.completed.getTime();
                    }

                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, g_achievements.createIcon(achievement.id, 1));

                    Icon.getLink(i.firstChild).href = this.getItemLink(achievement);
                    Icon.getLink(i.firstChild).rel = rel;

                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(achievement);
                    a.rel = rel;
                    $WH.ae(a, $WH.ct(achievement.name));
                    $WH.ae(td, a);

                    if (achievement.description != null) {
                        var d = $WH.ce('div');
                        d.className = 'small';
                        $WH.ae(d, $WH.ct(achievement.description));
                        $WH.ae(td, d);
                    }
                },
                getVisibleText: function(achievement) {
                    var buff = achievement.name;
                    if (achievement.description) {
                        buff += ' ' + achievement.description;
                    }
                    return buff;
                }
            },
            {
                id: 'location',
                name: LANG.location,
                type: 'text',
                width: '15%',
                hidden: 1,
                compute: function (achievement, td) {
                    if (achievement.zone) {
                        var a = $WH.ce('a');
                        a.className = 'q1';
                        a.href = '?zones=' + achievement.zone;
                        $WH.ae(a, $WH.ct(g_zones[achievement.zone]));
                        $WH.ae(td, a);
                    }
                },
                getVisibleText: function (achievement) {
                    return Listview.funcBox.arrayText(achievement.zone, g_zones);
                },
                sortFunc: function (a, b, col) {
                    return Listview.funcBox.assocArrCmp(a.zone, b.zone, g_zones);
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(achievement, td) {
                    if (achievement.side && achievement.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (achievement.side == 1 ? 'alliance-icon': 'horde-icon');
                        g_addTooltip(sp, g_sides[achievement.side]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(achievement) {
                    if (achievement.side) {
                        return g_sides[achievement.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'points',
                name: LANG.points,
                type: 'number',
                width: '10%',
                value: 'points',
                compute: function(achievement, td) {
                    if (achievement.points) {
                        Listview.funcBox.appendMoney(td, 0, null, 0, 0, 0, achievement.points);
                    }
                }
            },
            {
                id: 'rewards',
                name: LANG.rewards,
                type: 'text',
                width: '20%',
                compute: function(achievement, td) {
                    if (achievement.rewards) {
                        var itemrewards = [];
                        var spellrewards = []; // not used in 3.3.5
                        var titlerewards = [];
                        for (var i = 0; i < achievement.rewards.length; i++) {
                            if (achievement.rewards[i][0] == 11) {
                                titlerewards.push(achievement.rewards[i][1]);
                            }
                            else  if (achievement.rewards[i][0] == 3) {
                                itemrewards.push(achievement.rewards[i][1]);
                            }
                            else if (achievement.rewards[i][0] == 6) {
                                spellrewards.push(achievement.rewards[i][1]);
                            }
                        }
                        if (itemrewards.length > 0) {
                            for (var i = 0; i < itemrewards.length; i++) {
                                if (!g_items[itemrewards[i]]) {
                                    return;
                                }

                                var item = g_items[itemrewards[i]];
                                var a = $WH.ce('a');
                                a.href = '?item=' + itemrewards[i];
                                a.className = 'q' + item.quality + ' icontiny tinyspecial';
                                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                                $WH.ae(a, $WH.ct(item['name_' + g_locale.name]));
                                var span = $WH.ce('span');
                                $WH.ae(span, a);
                                $WH.ae(td, span);
                                $WH.ae(td, $WH.ce('br'));
                            }
                        }
                        if (spellrewards.length > 0) {
                            for (var i = 0; i < spellrewards.length; i++) {
                                if (!g_spells[spellrewards[i]]) {
                                    return;
                                }

                                var item = g_spells[spellrewards[i]];
                                var a = $WH.ce('a');
                                a.href = '?spell=' + spellrewards[i];
                                a.className = 'q8 icontiny tinyspecial';
                                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                                $WH.ae(a, $WH.ct(item['name_' + g_locale.name]));
                                var span = $WH.ce('span');
                                $WH.ae(span, a);
                                $WH.ae(td, span);
                                $WH.ae(td, $WH.ce('br'));
                            }
                        }
                        if (titlerewards.length > 0) {
                            for (var i = 0; i < titlerewards.length; i++) {
                                if (!g_titles[titlerewards[i]]) {
                                    return;
                                }

                                var title = g_titles[titlerewards[i]]['name_' + g_locale.name];
                                title = title.replace('%s', '<span class="q0">&lt;' + LANG.name + '&gt;</span>');
                                var span = $WH.ce('a');
                                span.className = 'q1';
                                span.href = '?title=' + titlerewards[i];
                                span.innerHTML = title;
                                $WH.ae(td, span);
                                $WH.ae(td, $WH.ce('br'));
                            }
                        }
                    }
                    else if (achievement.reward) {
                        var span = $WH.ce('span');
                        span.className = 'q1';
                        $WH.ae(span, $WH.ct(achievement.reward));
                        $WH.ae(td, span);
                    }
                },
                getVisibleText: function(achievement) {
                    var buff = '';
                    if (achievement.rewards) {
                        for (var i = 0; i < achievement.rewards.length; i++) {
                            if (achievement.rewards[i][0] == 11) {
                                buff += ' ' + g_titles[achievement.rewards[i][1]]['name_' + g_locale.name].replace('%s', '<' + LANG.name + '>');
                            }
                            else if (achievement.rewards[i][0] == 3) {
                                buff += ' ' + g_items[achievement.rewards[i][1]]['name_' + g_locale.name];
                            }
                            else if (achievement.rewards[i][0] == 6) {
                                buff += ' ' + g_spells[achievement.rewards[i][1]]['name_' + g_locale.name];
                            }
                        }
                    }
                    else if (achievement.reward) {
                        buff += ' ' + achievement.reward;
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    var text1 = this.getVisibleText(a);
                    var text2 = this.getVisibleText(b);

                    if (text1 != '' && text2 == '') {
                        return -1;
                    }
                    if (text2 != '' && text1 == '') {
                        return 1;
                    }

                    return $WH.strcmp(text1, text2);
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(achievement, td) {
                    statistic = achievement.type ? achievement.type + '.' : '';
                    td.className = 'small';
                    path = '?achievements=' + statistic;
                    if (achievement.category != -1 && achievement.parentcat != -1) {
                        var a2 = $WH.ce('a');
                        a2.className = 'q0';
                        a2.href = '?achievements=' + statistic + achievement.parentcat;
                        $WH.ae(a2, $WH.ct(g_achievement_categories[achievement.parentcat]));
                        $WH.ae(td, a2);
                        $WH.ae(td, $WH.ce('br'));
                        path = a2.href + '.';
                    }
                    var a = $WH.ce('a');
                    a.className = 'q1';
                    a.href = path + achievement.category;
                    $WH.ae(a, $WH.ct(g_achievement_categories[achievement.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(achievement) {
                    return g_achievement_categories[achievement.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_achievement_categories[a.category], g_achievement_categories[b.category]);
                },
                hidden: true
            }
        ],

        getItemLink: function(achievement) {
            return '?achievement=' + achievement.id;
        }
    },

    title: {
        sort: [1],
        nItemsPerPage: -1,
        searchable: 1,
        filtrable: 1,

        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                value: 'name',
                compute: function(title, td, tr) {
                    var
                        sp = $WH.ce('a'),
                        n = $WH.ce('span'),
                        t = $WH.ct($WH.str_replace(title.name, '%s', ''));

                    td.style.fontFamily = 'Verdana, sans-serif';

                    // $WH.nw(td)

                    sp.href = this.getItemLink(title);

                    if (title.who) {
                        $WH.ae(n, $WH.ct(title.who));
                    }
                    else {
                        $WH.ae(n, $WH.ct('<' + LANG.name + '>'));
                        n.className = 'q0';
                    }

                    if (title.name.indexOf('%s') > 0) { // Prefix title
                        $WH.ae(sp, t);
                        $WH.ae(sp, n);
                    }
                    else if (title.name.indexOf('%s') == 0) { // Suffix title
                        $WH.ae(sp, n);
                        $WH.ae(sp, t);
                    }

                    if (title.expansion) {
                        var i = $WH.ce('span');
                        i.className = (title.expansion == 1 ? 'bc-icon': 'wotlk-icon');
                        $WH.ae(i, sp);
                        $WH.ae(td, i);
                    }
                    else {
                        $WH.ae(td, sp);
                    }
                },
                sortFunc: function(a, b, col) {
                    var
                        aName = $WH.trim(a.name.replace('%s', '').replace(/^[\s,]*(,|the |of the |of )/i, ''));
                        bName = $WH.trim(b.name.replace('%s', '').replace(/^[\s,]*(,|the |of the |of )/i, ''));

                    return $WH.strcmp(aName, bName);
                },
                getVisibleText: function(title) {
                    var buff = title.name + Listview.funcBox.getExpansionText(title);

                    return buff;
                }
            },
            {
                id: 'gender',
                name: LANG.gender,
                type: 'text',
                value: 'gender',
                compute: function(title, td) {
                    if (title.gender && title.gender != 3) {
                        var gender = g_file_genders[title.gender - 1];
                        var sp = $WH.ce('span');
                        sp.className = gender + '-icon';
                        g_addTooltip(sp, LANG[gender]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(title) {
                    if (title.gender && title.gender != 3) {
                        return LANG[g_file_genders[title.gender - 1]];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(a.gender, b.gender);
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(title, td) {
                    if (title.side && title.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (title.side == 1 ? 'alliance-icon': 'horde-icon');
                        g_addTooltip(sp, g_sides[title.side]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(title) {
                    if (title.side) {
                        return g_sides[title.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'source',
                name: LANG.source,
                type: 'text',
                compute: function(title, td) {
                    if (title.source) {
                        $WH.nw(td);
                        td.className = 'small';
                        td.style.lineHeight = '18px';
                        var n = 0;

                        for (var s in title.source) {
                            title.source[s].sort(function(a, b) {
                                return b.s - a.s;
                            });

                            for (var i = 0, len = title.source[s].length; i < len; ++i) {
                                var sm = title.source[s][i];
                                var type = 0;

                                if (title.faction && typeof sm != 'string' && sm.s !== undefined && sm.s != -1 && sm.s != 2 - title.faction) {
                                    continue;
                                }

                                if (n++ > 0) {
                                    $WH.ae(td, $WH.ce('br'));
                                }

                                if (typeof sm == 'string') {
                                    $WH.ae(td, $WH.ct(sm));
                                }
                                else  if (sm.t) {
                                    type = sm.t;
                                    var a = $WH.ce('a');
                                    // o.style.fontFamily = 'Verdana, sans-serif';
                                    a.href = '?' + g_types[sm.t] + '=' + sm.ti;
                                    a.className = 'q1';

                                    if (sm.s == 1) {
                                        a.className += ' alliance-icon'
                                    }
                                    if (sm.s == 0) {
                                        a.className += ' horde-icon'
                                    }
                                    if (sm.t == 5) { // Quests
                                        a.className += ' icontiny tinyspecial';
                                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/quest_start.gif)';
                                    }

                                    $WH.ae(a, $WH.ct(sm.n));
                                    $WH.ae(td, a);
                                }
                            }
                        }
                    }
                },
                getVisibleText: function(title) {
                    var buff = '';

                    if (title.source) {
                        for (var s in title.source) {
                            for (var i = 0, len = title.source[s].length; i < len; ++i) {
                                var sm = title.source[s][i];
                                if (typeof sm == 'string') {
                                    buff += ' ' + sm;
                                }
                                else if (sm.t){
                                    buff += ' ' + sm.n;
                                }
                            }
                        }
                    }

                    return buff;
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(title, td) {
                    $WH.nw(td);
                    td.className = 'small q1';
                    var a = $WH.ce('a');
                    a.href = '?titles=' + title.category;
                    $WH.ae(a, $WH.ct(g_title_categories[title.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(title) {
                    return g_title_categories[title.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_title_categories[a.category], g_title_categories[b.category]);
                },
                hidden: true
            }
        ],

        getItemLink: function(title) {
            return '?title=' + title.id;
        }
    },

    profile: {
        sort: [],
        nItemsPerPage: 50,
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                value: 'name',
                type: 'text',
                align: 'left',
                span: 2,
                compute: function(profile, td, tr) {
                    if (profile.level) {
                        var i = $WH.ce('td');
                        i.style.width = '1px';
                        i.style.padding = '0';
                        i.style.borderRight = 'none';

                        $WH.ae(i, Icon.create($WH.g_getProfileIcon(profile.race, profile.classs, profile.gender, profile.level, profile.icon ? profile.icon : profile.id, 'medium'), 1, null, this.getItemLink(profile)));
                        $WH.ae(tr, i);

                        td.style.borderLeft = 'none';
                    }
                    else {
                        td.colSpan = 2;
                    }

                    var wrapper = $WH.ce('div');
                    wrapper.style.position = 'relative';

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(profile);
                    if (profile.pinned) {
                        a.className = 'star-icon-right';
                    }
                    $WH.ae(a, $WH.ct(profile.name));
                    $WH.ae(wrapper, a);

                    var d = $WH.ce('div');
                    d.className = 'small';
                    d.style.marginRight = '20px';

                    if (profile.guild && typeof(profile.guild) != 'number') {
                        var a = $WH.ce('a');
                        a.className = 'q1';
                        a.href = '?guild=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.guild);
                        $WH.ae(a, $WH.ct(profile.guild));
                        $WH.ae(d, $WH.ct('<'));
                        $WH.ae(d, a);
                        $WH.ae(d, $WH.ct('>'));
                    }
                    else if (profile.description) {
                        $WH.ae(d, $WH.ct(profile.description));
                    }

                    var
                        s = $WH.ce('span'),
                        foo = '';

                    s.className = 'q10';
                    if (profile.deleted) {
                        foo = LANG.lvcomment_deleted;
                    }
                    $WH.ae(s, $WH.ct(foo));
                    $WH.ae(d, s);
                    $WH.ae(wrapper, d);

                    var d = $WH.ce('div');
                    d.className = 'small';
                    d.style.fontStyle = 'italic';
                    d.style.position = 'absolute';
                    d.style.right = '3px';
                    d.style.bottom = '0px';
                    tr.__status = d;    // todo: wtf is that for..?

                    if (profile.published === 0) {
                        $WH.ae(d, $WH.ct(LANG.privateprofile));
                    }

                    $WH.ae(wrapper, d);
                    $WH.ae(td, wrapper);
                },
                getVisibleText: function(profile) {
                    var buff = profile.name;
                    if (profile.guild && typeof(profile.guild) != 'number') {
                        buff += ' ' + profile.guild;
                    }
                    return buff;
                }
            },
            {
                id: 'faction',
                name: LANG.faction,
                type: 'text',
                compute: function(profile, td) {
                    if (!profile.size && profile.members === undefined && !profile.level) {
                        return;
                    }

                    var
                        d = $WH.ce('div'),
                        d2 = $WH.ce('div'),
                        icon;

                    icon = Icon.create('faction_' + g_file_factions[parseInt(profile.faction) + 1], 0);
                    icon.style.cssFloat = icon.style.syleFloat = 'left';
                    g_addTooltip(icon, g_sides[parseInt(profile.faction) + 1]);

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';
                    d.style.width = '26px';
                    d2.className = 'clear';

                    $WH.ae(d, icon);
                    $WH.ae(td, d);
                    $WH.ae(td, d2);
                },
                getVisibleText: function(profile) {
                    return g_sides[profile.faction + 1];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
                }
            },
            {
                id: 'members',
                name: LANG.members,
                value: 'members',
                hidden: 1
            },
            {
                id: 'size',
                name: 'Size',
                value: 'size',
                hidden: 1
            },
            {
                id: 'rank',
                name: 'Rank',
                value: 'rank',
                hidden: 1
            },
            {
                id: 'race',
                name: LANG.race,
                type: 'text',
                compute: function(profile, td) {
                    if (profile.race) {
                        var
                            d = $WH.ce('div'),
                            d2 = $WH.ce('div'),
                            icon;

                        icon = Icon.create('race_' + g_file_races[profile.race] + '_' + g_file_genders[profile.gender], 0, null, '?race=' + profile.race);
                        icon.style.cssFloat = icon.style.syleFloat = 'left';
                        g_addTooltip(icon, g_chr_races[profile.race]);

                        d.style.margin = '0 auto';
                        d.style.textAlign = 'left';
                        d.style.width = '26px';
                        d2.className = 'clear';

                        $WH.ae(d, icon);
                        $WH.ae(td, d);
                        $WH.ae(td, d2);
                    }
                },
                getVisibleText: function(profile) {
                    return g_file_genders[profile.gender] + ' ' + g_chr_races[profile.race];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_chr_races[a.race], g_chr_races[b.race]);
                },
                hidden: 1
            },
            {
                id: 'classs',
                name: LANG.classs,
                type: 'text',
                compute: function(profile, td) {
                    if (profile.classs) {
                        var
                            d  = $WH.ce('div'),
                            d2 = $WH.ce('div'),
                            icon;

                        icon = Icon.create('class_' + g_file_classes[profile.classs], 0, null, '?class=' + profile.classs);
                        icon.style.cssFloat = icon.style.syleFloat = 'left';

                        g_addTooltip(icon, g_chr_classes[profile.classs], 'c' + profile.classs);

                        d.style.margin = '0 auto';
                        d.style.textAlign = 'left';
                        d.style.width = '26px';
                        d2.className = 'clear';

                        $WH.ae(d, icon);
                        $WH.ae(td, d);
                        $WH.ae(td, d2);
                    }
                    else {
                        return -1;
                    }
                },
                getVisibleText: function(profile) {
                    if (profile.classs) {
                        return g_chr_classes[profile.classs];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
                },
                hidden: 1
            },
            {
                id: 'level',
                name: LANG.level,
                value: 'level',
                hidden: 1
            },
            {
                id: 'talents',
                name: LANG.talents,
                type: 'text',
                compute: function(profile, td) {
                    if (!profile.level) {
                        return;
                    }

                    var spent = [profile.talenttree1, profile.talenttree2, profile.talenttree3];
                    var specData = pr_getSpecFromTalents(profile.classs, spent);
                    var a = $WH.ce('a');

                    a.className = 'icontiny tinyspecial tip q1';
                    a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + specData.icon.toLowerCase() + '.gif)';
                    a.rel = 'np';
                    a.href = this.getItemLink(profile) + '#talents';
                    g_addTooltip(a, specData.name);

                    $WH.ae(a, $WH.ct(profile.talenttree1 + ' / ' + profile.talenttree2 + ' / ' + profile.talenttree3));

                    $WH.ae(td, a);
                },
                getVisibleText: function(profile) {
                    if (profile.talenttree1 || profile.talenttree2 || profile.talenttree3) {
                        if (profile.talentspec > 0) {
                            return g_chr_specs[profile.classs][profile.talentspec - 1];
                        }
                        else {
                            return g_chr_specs[0];
                        }
                    }
                    else {
                        return g_chr_specs['-1'];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
                },
                hidden: 1
            },
            {
                id: 'achievementpoints',
                name: LANG.points,
                value: 'achievementpoints',
                tooltip: LANG.tooltip_achievementpoints,
                compute: function(profile, td) {
                    if (profile.achievementpoints) {
                        Listview.funcBox.appendMoney(td, 0, null, 0, 0, 0, profile.achievementpoints);
                    }
                },
                hidden: 1
            },
            {
                id: 'wins',
                name: LANG.wins,
                value: 'wins',
                hidden: 1
            },
            {
                id: 'losses',
                name: LANG.losses,
                compute: function(profile, td) {
                    return profile.games - profile.wins;
                },
                hidden: 1
            },
            {
                id: 'guildrank',
                name: LANG.guildrank,
                value: 'guildrank',
                compute: function(profile, td) {
                    if (profile.guildrank > 0) {
                        return $WH.sprintf(LANG.rankno, profile.guildrank);
                    }
                    else if (profile.guildrank == 0) {
                        var b = $WH.ce('b');
                        $WH.ae(b, $WH.ct(LANG.guildleader));
                        $WH.ae(td, b);
                    }
                },
                getVisibleText: function(profile) {
                    if (profile.guildrank > 0) {
                        return $WH.sprintf(LANG.rankno, profile.guildrank);
                    }
                    else if (profile.guildrank == 0) {
                        return LANG.guildleader;
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp((a.guildrank >= 0 ? a.guildrank : 11), (b.guildrank >= 0 ? b.guildrank : 11));
                },
                hidden: 1
            },
            {
                id: 'rating',
                name: LANG.rating,
                value: 'rating',
                compute: function(profile, td) {
                    if (profile.roster) {
                        return profile.arenateam[profile.roster].rating;
                    }

                    return profile.rating;
                },
                sortFunc: function(a, b, col) {
                    if (a.roster && b.roster) {
                        return $WH.strcmp(a.arenateam[a.roster].rating, b.arenateam[b.roster].rating);
                    }

                    return $WH.strcmp(a.rating, b.rating);
                },
                hidden: 1
            },
            {
                id: 'location',
                name: LANG.location,
                type: 'text',
                compute: function(profile, td) {
                    var a;

                    if (profile.region) {
                        if (profile.realm) {
                            a = $WH.ce('a');
                            a.className = 'q1';
                            a.href = '?profiles=' + profile.region + '.' + profile.realm;
                            $WH.ae(a, $WH.ct(profile.realmname));
                            $WH.ae(td, a);
                            $WH.ae(td, $WH.ce('br'));
                        }

                        var s = $WH.ce('small');
                        a = $WH.ce('a');
                        a.className = 'q1';
                        a.href = '?profiles=' + profile.region;
                        $WH.ae(a, $WH.ct(profile.region.toUpperCase()));
                        $WH.ae(s, a);

                        if (profile.subregion) {
                            $WH.ae(s, $WH.ct(LANG.hyphen));

                            a = $WH.ce('a');
                            a.className = 'q1';
                            a.href = '?profiles=' + profile.region + '.' + profile.subregion;
                            $WH.ae(a, $WH.ct(profile.subregionname));
                            $WH.ae(s, a);
                        }

                        $WH.ae(td, s);
                    }
                },
                getVisibleText: function(profile) {
                    var buff = '';
                    if (profile.region) {
                        buff += ' ' + profile.region;
                    }
                    if (profile.subregion) {
                        buff += ' ' + profile.subregion;
                    }
                    if (profile.realm) {
                        buff += ' ' + profile.realm;
                    }

                    return $WH.trim(buff);
                },
                sortFunc: function(a, b, col) {
                    if (a.region != b.region) {
                        return $WH.strcmp(a.region, b.region);
                    }
                    if (a.subregion != b.subregion) {
                        return $WH.strcmp(a.subregion, b.subregion);
                    }
                    return $WH.strcmp(a.realm, b.realm);
                }
            },
            {
                id: 'guild',
                name: LANG.guild,
                value: 'guild',
                type: 'text',
                compute: function(profile, td) {
                    if (!profile.region || !profile.realm || !profile.guild || typeof(profile.guild) == 'number') {
                        return;
                    }

                    var a = $WH.ce('a');
                    a.className = 'q1';
                    a.href = '?guild=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.guild);
                    $WH.ae(a, $WH.ct(profile.guild));
                    $WH.ae(td, a);
                }
            }
        ],

        getItemLink: function(profile) {
            if (profile.size !== undefined) {
                return '?arena-team=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.name);
            }
            else if (profile.members !== undefined) {
                return '?guild=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.name);
            }
            else {
                return g_getProfileUrl(profile);
            }
        }
    },

    model: {
        sort: [],
        mode: 3, // Grid mode
        nItemsPerPage: 40,
        nItemsPerRow: 4,
        poundable: 2, // Yes but w/o sort

        columns: [],

        compute: function(model, td, i) {
            td.className = 'screenshot-cell';
            td.vAlign = 'bottom';

            var a = $WH.ce('a');
            a.href = 'javascript:;';
            // a.className = 'pet-zoom';   // reference only
            a.onclick = this.template.modelShow.bind(this.template, model.npcId, model.displayId, false);

            var img = $WH.ce('img');
            img.src = g_staticUrl + '/modelviewer/thumbs/npc/' + model.displayId + '.png';
            $WH.ae(a, img);

            $WH.ae(td, a);

            var d = $WH.ce('div');
            d.className = 'screenshot-cell-user';

            a = $WH.ce('a');
            a.href = '?npcs=1&filter=' + (model.family ? 'fa=' + model.family + ';' : '') + 'minle=1;cr=35;crs=0;crv=' + model.skin;
            $WH.ae(a, $WH.ct(model.skin));
            $WH.ae(d, a);

            $WH.ae(d, $WH.ct(' (' + model.count + ')'));
            $WH.ae(td, d);

            d = $WH.ce('div');
            d.style.position = 'relative';
            d.style.height = '1em';

            var d2 = $WH.ce('div');
            d2.className = 'screenshot-caption';

            var s = $WH.ce('small');
            $WH.ae(s, $WH.ct(LANG.level + ': '));
            $WH.ae(s, $WH.ct((model.minLevel == 9999 ? '??' : model.minLevel) + (model.minLevel == model.maxLevel ? '' : LANG.hyphen + (model.maxLevel == 9999 ? '??' : model.maxLevel))));
            $WH.ae(s, $WH.ce('br'));
            $WH.ae(d2, s);
            $WH.ae(d, d2);
            $WH.ae(td, d);

            $WH.aE(td, 'click', this.template.modelShow.bind(this.template, model.npcId, model.displayId, true));
        },

        modelShow: function(npcId, displayId, sp, e) {
            if (sp) {
                e = $WH.$E(e);

                if (e.shiftKey || e.ctrlKey) {
                    return;
                }

                var
                    j = 0,
                    el = e._target;
                while (el && j < 3) {
                    if (el.nodeName == 'A') {
                        return;
                    }
                    if (el.nodeName == 'IMG') {
                        break;
                    }
                    el = el.parentNode;
                }
            }

            ModelViewer.show({type: 1, typeId: npcId, displayId: displayId, noPound: 1});
        }
    },

    currency: {
        sort: [1],
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(currency, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, Icon.create(currency.icon, 0, null, this.getItemLink(currency)));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(currency);
                    $WH.ae(a, $WH.ct(currency.name));

                    $WH.ae(wrapper, a);

                    $WH.ae(td, wrapper);
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(currency, td) {
                    td.className = 'small';

                    var a = $WH.ce('a');
                    a.className = 'q1';
                    a.href = '?currencies=' + currency.category;
                    $WH.ae(a, $WH.ct(g_currency_categories[currency.category]));
                    $WH.ae(td, a);
                },
                getVisibleText: function(currency) {
                    return g_currency_categories[currency.category];
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_currency_categories[a.category], g_currency_categories[b.category]);
                }
            }
        ],

        getItemLink: function(currency) {
            return '?currency=' + currency.id;
        }
    },

    classs: {
        sort: [1],
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(classs, td, tr) {
                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, Icon.create('class_' + g_file_classes[classs.id], 0, null, this.getItemLink(classs)));
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    // force minimum width to fix overlap display bug
                    td.style.width = '225px';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.className = 'c' + classs.id;
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(classs);
                    $WH.ae(a, $WH.ct(classs.name));

                    if (classs.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(classs.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(wrapper, sp);
                    }
                    else {
                        $WH.ae(wrapper, a);
                    }

                    if (classs.hero) {
                        wrapper.style.position = 'relative';
                        var d = $WH.ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '0px';
                        $WH.ae(d, $WH.ct(LANG.lvclass_hero));
                        $WH.ae(wrapper, d);
                    }
                    $WH.ae(td, wrapper);
                }
            },
            {
                id: 'races',
                name: LANG.races,
                type: 'text',
                compute: function(classs, td) {
                    if (classs.races) {
                        var races = Listview.funcBox.assocBinFlags(classs.races, g_chr_races);

                        td.className = 'q1';
                        for (var i = 0, len = races.length; i < len; ++i) {
                            if (i > 0) {
                                $WH.ae(td, $WH.ct(LANG.comma));
                            }
                            var a = $WH.ce('a');
                            a.href = '?race=' + races[i];
                            $WH.ae(a, $WH.ct(g_chr_races[races[i]]));
                            $WH.ae(td, a);
                        }
                    }
                },
                getVisibleText: function(classs) {
                    if (classs.races) {
                        return Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(classs.races, g_chr_races), g_chr_races);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.races, g_chr_races), Listview.funcBox.assocBinFlags(b.races, g_chr_races), g_chr_races);
                }
            }
        ],

        getItemLink: function(classs) {
            return '?class=' + classs.id;
        }
    },

    race: {
        sort: [1],
        searchable: 1,
        filtrable: 1,
        columns: [
            {
                id: 'name',
                name: LANG.name,
                type: 'text',
                align: 'left',
                span: 2,
                value: 'name',
                compute: function(race, td, tr) {
                    var
                        d = $WH.ce('div'),
                        icon;

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';
                    d.style.width = '52px';

                    icon = Icon.create('race_' + g_file_races[race.id] + '_' + g_file_genders[0], 0, null, this.getItemLink(race));
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    $WH.ae(d, icon);

                    icon = Icon.create('race_' + g_file_races[race.id] + '_' + g_file_genders[1], 0, null, this.getItemLink(race));
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    $WH.ae(d, icon);

                    var i = $WH.ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    $WH.ae(i, d);
                    $WH.ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = $WH.ce('div');

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.getItemLink(race);
                    $WH.ae(a, $WH.ct(race.name));

                    if (race.expansion) {
                        var sp = $WH.ce('span');
                        sp.className = g_GetExpansionClassName(race.expansion);
                        $WH.ae(sp, a);
                        $WH.ae(wrapper, sp);
                    }
                    else {
                        $WH.ae(wrapper, a);
                    }
                    $WH.ae(td, wrapper);
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(race, td) {
                    if (race.side && race.side != 3) {
                        var sp = $WH.ce('span');
                        sp.className = (race.side == 1 ? 'alliance-icon' : 'horde-icon');
                        g_addTooltip(sp, g_sides[race.side]);

                        $WH.ae(td, sp);
                    }
                },
                getVisibleText: function(race) {
                    if (race.side) {
                        return g_sides[race.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return $WH.strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'classes',
                name: LANG.classes,
                type: 'text',
                compute: function(race, td) {
                    if (race.classes) {
                        var classes = Listview.funcBox.assocBinFlags(race.classes, g_chr_classes);

                        var d = $WH.ce('div');
                        d.style.width = (26 * classes.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            var icon = Icon.create('class_' + g_file_classes[classes[i]], 0, null, '?class=' + classes[i]);

                            g_addTooltip(icon, g_chr_classes[classes[i]], 'c' + classes[i]);

                            icon.style.cssFloat = icon.style.styleFloat = 'left';
                            $WH.ae(d, icon);
                        }

                        $WH.ae(td, d);
                    }
                },
                getVisibleText: function(race) {
                    if (race.classes) {
                        return Listview.funcBox.arrayText(Listview.funcBox.assocBinFlags(race.classes, g_chr_classes), g_chr_classes);
                    }
                },
                sortFunc: function(a, b, col) {
                    return Listview.funcBox.assocArrCmp(Listview.funcBox.assocBinFlags(a.classes, g_chr_classes), Listview.funcBox.assocBinFlags(b.classes, g_chr_classes), g_chr_classes);
                }
            }
        ],

        getItemLink: function(race) {
            return '?race=' + race.id;
        }
    }
};

/*
Menu class
*/

var MENU_IDX_ID   = 0; //      ID: A number or string; null makes the menu item a separator
var MENU_IDX_NAME = 1; //    Name: A string
var MENU_IDX_URL  = 2; //     URL: A string for the URL, or a function to call when the menu item is clicked
var MENU_IDX_SUB  = 3; // Submenu: Child menu
var MENU_IDX_OPT  = 4; // Options: JSON array with additional options

var Menu = new function()
{
	var self = this;

	/***********/
	/* PUBLIC */
	/**********/

	self.add = function(node, menu, opt) // Add a menu to a DOM element
	{
		if(!opt) opt = $.noop;

		var $node = $(node);

		$node.data('menu', menu);

		if(opt.showAtCursor)
		{
			$node.click(rootNodeClick);
		}
		else
		{
			$node
			.mouseover(rootNodeOver)
			.mouseout(rootNodeOut);
		}
	};

	self.remove = function(node) // Remove menu from DOM element
	{
		$(node)
		.data('menu', null)
		.unbind('click',     rootNodeClick)
		.unbind('mouseover', rootNodeOver)
		.unbind('mouseout',  rootNodeOut);
	};

	self.show = function(menu, node)
	{
		var $node = $(node);

		show(menu, $node);
	}

	self.showAtCursor = function(menu, event) // {event} must have been normalized by jQuery
	{
		showAtXY(menu, event.pageX, event.pageY);
	}

	self.showAtXY = function(menu, x, y)
	{
		showAtXY(menu, x, y);
	}

	self.hide = function()
	{
		rootNodeOut();
	}

	self.addButtons = function(dest, menu) // Create a set of menu buttons (as seen on the homepage or in the top bar)
	{
		var $dest = $(dest);
		if(!$dest.length)
			return;

		var $buttons = $('<span class="menu-buttons"></span>');

		$.each(menu, function(idx, menuItem)
		{
			if(isSeparator(menuItem)) return;

			var $a = $('<a></a>')
			var $span = $('<span></span>', {
				text: menuItem[MENU_IDX_NAME]
			}).appendTo($a);

			self.linkifyItem(menuItem, $a);

			if(hasSubmenu(menuItem))
			{
				$span.addClass('hassubmenu');
				self.add($a, menuItem[MENU_IDX_SUB]);
			}

			$buttons.append($a);
		});

		$dest.append($buttons);
	};

	self.linkifyItem = function(menuItem, $a) // Also used by PageTemplate when generating the top tabs
	{
		var opt = self.getItemOpt(menuItem);

		if(!menuItem[MENU_IDX_URL]) // Unlinked
		{
			$a.attr('href', 'javascript:;');
			$a.addClass('unlinked');

			return;
		}

		if(typeof menuItem[MENU_IDX_URL] == 'function') // Function
		{
			$a.attr('href', 'javascript:;');
			$a.click(hide);
			$a.click(menuItem[MENU_IDX_URL]);
		}
		else // URL
		{
			var url = self.getItemUrl(menuItem);
			$a.attr('href', url);

			if(opt.newWindow || g_isExternalUrl(url))
				$a.attr('target', '_blank');

			if(opt.rel)
				$a.attr('rel', opt.rel);
		}
		if(typeof menuItem[MENU_IDX_OPT] == 'object' && menuItem[MENU_IDX_OPT].className)
			$a.addClass(menuItem[MENU_IDX_OPT].className);
	};

	self.updateItem = function(menuItem) // Also used by PageTemplate when the breadcrumb changes
	{
		var $a = menuItem.$a;
		if(!$a)
			return;

		var opt = self.getItemOpt(menuItem);

		$a.removeClass('checked tinyicon icon');
		$a.css('background-image', '');

		if(menuItem.checked)
		{
			$a.addClass('checked');
		}
		else if(opt.tinyIcon)
		{
			$a.addClass('tinyicon');
			$a.css('background-image', 'url(' + (opt.tinyIcon.indexOf('/') != -1 ? opt.tinyIcon : g_staticUrl + '/images/icons/tiny/' + opt.tinyIcon.toLowerCase() + '.gif') + ')');
		}
		else if(opt.icon)
		{
			$a.addClass('icon');
			$a.css('background-image', 'url(' + opt.icon + ')');
		}
		else if(opt.socketColor && g_file_gems[opt.socketColor])
		{
			$a.addClass('socket-' + g_file_gems[opt.socketColor]);
		}

		var className = (opt['class'] || opt.className);
		if(className)
			$a.addClass(className);
	};

	// MENU UTILITY FUNCTIONS

	self.hasMenu = function(node)
	{
		var $node = $(node);

		return $node.data('menu') != null;
	}

	self.modifyUrl = function(menuItem, params, opt)
	{
		var json = { params: params, opt: opt };

		traverseMenuItem(menuItem, function(x)
		{
			x.modifyUrl = json;
		});

		// uncomment with breadcrumbs PageTemplate.updateBreadcrumb();
	};

	self.fixUrls = function(menu, url, opt)
	{
		opt = opt || {};

		opt.hash = (opt.hash ? '#' + opt.hash : '');

		fixUrls(menu, url, opt, 0);
	};

	self.sort = function(menu) // Sort a specific menu
	{
		if(hasSeparators(menu))
			sortSeparatedMenu(menu);
		else
			sortMenu(menu);
	};

	self.sortSubmenus = function(menu, paths) // Sort the submenus of {menu} specified by {paths}
	{
		$.each(paths, function(idx, path)
		{
			var submenu = self.findItem(menu, path);
			if(submenu && submenu[MENU_IDX_SUB])
				self.sort(submenu[MENU_IDX_SUB]);
		});
	};

	self.implode = function(menu, opt) // Return a new menu where items preceded by a heading are merged into a single item w/ a submenu
	{
		if(!opt) opt = $.noop;

		var result = [];
		var groupedMenu;

		if(opt.createHeadinglessGroup)
		{
			groupedMenu = [];
			result.push([0, '', null, groupedMenu]);
		}

		$.each(menu, function(idx, menuItem)
		{
			if(isSeparator(menuItem))
			{
				groupedMenu = [];
				result.push([0, menuItem[MENU_IDX_NAME], null, groupedMenu]);
			}
			else
			{
				if(groupedMenu)
					groupedMenu.push(menuItem);
				else
					result.push(menuItem);
			}
		});

		return result;
	};

	self.findItem = function(menu, path) // Return the menu item specified by {path} in {menu}
	{
		return self.getFullPath(menu, path).pop();
	};

	self.getFullPath = function(menu, path) // Return an array with all the menu items specified by {path} in {menu}
	{
		var result = [];

		for(var i = 0; i < path.length; ++i)
		{
			var pos = findItemPosById(menu, path[i]);

			if(pos != -1)
			{
				var menuItem = menu[pos];
				menuItem.parentMenu = menu;
				menu = menuItem[MENU_IDX_SUB];

				result.push(menuItem);
			}
		}

		return result;
	};

	self.getItemUrl = function(menuItem)
	{
		var url = menuItem[MENU_IDX_URL];
		if(!url)
			return null;

		var opt = self.getItemOpt(menuItem);

		if(menuItem.modifyUrl)
			url = g_modifyUrl(url, menuItem.modifyUrl.params, menuItem.modifyUrl.opt);

		return url;
	};

	self.getItemOpt = function(menuItem)
	{
		if(!menuItem[MENU_IDX_OPT])
			menuItem[MENU_IDX_OPT] = {};

		return menuItem[MENU_IDX_OPT];
	};

	self.removeItemById = function(menu, id)
	{
		var pos = findItemPosById(menu, id);
		if(pos != -1)
			menu.splice(pos, 1);
	};

	/***********/
	/* PRIVATE */
	/***********/

	var DELAY_BEFORESHOW  = 25;
	var DELAY_BEFOREHIDE  = 333;

	var MAX_COLUMNS = 4;

	// Should match the styles used in Menu.css
	var SIZE_SHADOW_WIDTH    = 6;
	var SIZE_SHADOW_HEIGHT   = 6;
	var SIZE_BORDER_HEIGHT   = 3;
	var SIZE_MENUITEM_HEIGHT = 26;

	var inited = false;
	var timer;
	var $rootNode;
	var visibleMenus = {};
	var openLinks = {};
	var divCache = {};
	var menuItemsCache = {};
	var nextUniqueId = 0;

	function init()
	{
		if(inited) return;
		inited = true;

		// Run a test to get the height of a menu item (in case it's different in some browser/OS combos)
		var $div = $('<div class="menu"><a href="#"><span>ohai</span></a></div>').css({left: '-1000px', top: '-1000px'}).appendTo(document.body);
		var height = $div.children('a').outerHeight();
		$div.remove();
		if(height > 15) // Safety
			SIZE_MENUITEM_HEIGHT = height;
	}

	function show(menu, $node)
	{
		if($rootNode)
			$rootNode.removeClass('open');

		$rootNode = $node;
		$rootNode.addClass('open');

		displayFirstMenu(menu);
	}

	function hide()
	{
		if($rootNode)
		{
			$rootNode.removeClass('open');
			$rootNode = null;
		}

		hideMenus(0);
	}

	function showAtXY(menu, x, y)
	{
		clearTimeout(timer);

		displayFirstMenu(menu, x, y);
	}

	function displayFirstMenu(menu, x, y)
	{
		closeLinks(0);
		displayMenu(menu, 0, x, y);
		hideMenus(1);
	}

	function displayMenu(menu, depth, x, y)
	{
		init();

		beforeShowMenu(menu);

		var $div       = createDiv(depth);
		var $menuItems = createMenuItems(menu);
		var $menu      = createMenu($menuItems, depth);

		$div.append($menu);

		var animated = !isMenuVisible(depth);
		visibleMenus[depth] = $div;

		var pos = getMenuPosition($div, depth, x, y);
		$div.css({
			left: pos.x + 'px',
			top:  pos.y + 'px'
		});

		// Hide ads that intersect with the menu
		// var menuRect = $WH.g_createRect(pos.x, pos.y, $div.width(), $div.height());
		// Ads.intersect(menuRect, true);

		revealDiv($div, animated);
	}

	function createDiv(depth)
	{
		if(divCache[depth])
		{
			var $div = divCache[depth];
			$div.children().detach(); // Similar to $div.empty(), except custom data is preserved.
			return $div;
		}

		var $div = $('<div class="menu"></div>')
		.mouseover(menuDivOver)
		.mouseleave(menuDivOut)
		.delegate('a', 'mouseenter', { depth: depth }, menuItemOver)
		.delegate('a', 'click', menuItemClick);

		if($WH.isset('g_thottbot') && g_thottbot)
			$div.hide();

		$div.appendTo(document.body);

		divCache[depth] = $div;
		return $div;
	}

	function createMenuItems(menu)
	{
		var uid = getUniqueId(menu);

		if(menuItemsCache[uid])
			return menuItemsCache[uid];

		var nextSeparator; // Used to make sure a separator is always followed by a visible item
		var menuItems = [];

		$.each(menu, function(idx, menuItem)
		{
			if(!isItemVisible(menuItem)) return;

			$a = createMenuItem(menuItem);

			if(isSeparator(menuItem))
			{
				nextSeparator = $a;
				return;
			}

			if(nextSeparator)
			{
				menuItems.push(nextSeparator);
				nextSeparator = null;
			}

			menuItems.push($a);
		});

		var $menuItems = $(menuItems);

		menuItemsCache[menu] = $menuItems;
		return $menuItems;
	}

	function createMenuItem(menuItem)
	{
		beforeCreateMenuItem(menuItem);

		var $a = $('<a></a>');

		menuItem.$a = $a;
		$a.data('menuItem', menuItem);

		self.linkifyItem(menuItem, $a);
		self.updateItem(menuItem);

		// Separator
		if(isSeparator(menuItem))
		{
			$a.addClass('separator');
			$a.text(menuItem[MENU_IDX_NAME]);

			return $a;
		}

		var $span = $('<span></span>');
		$span.text(menuItem[MENU_IDX_NAME]);
		$span.appendTo($a);

		// Submenu
		if(hasSubmenu(menuItem))
			$span.addClass('hassubmenu');

		return $a;
	}

	function createMenu($menuItems, depth)
	{
		var $a = $rootNode;
		var $w = $(window);
		var nItems = $menuItems.length;
		var availableHeight = $w.height() - (SIZE_BORDER_HEIGHT * 2) - SIZE_SHADOW_HEIGHT;
		var nItemsThatCanFit = Math.floor(Math.max(0, availableHeight) / SIZE_MENUITEM_HEIGHT);

		// 1 column
		if(nItemsThatCanFit >= nItems)
		{
			var $outerDiv = $('<div class="menu-outer"></div>');
			var $innerDiv = $('<div class="menu-inner"></div>');

			$menuItems.appendTo($innerDiv);
			$outerDiv.append($innerDiv);

			return $outerDiv;
		}

		// Multiple columns
		var nColumns = Math.min(MAX_COLUMNS, Math.ceil(nItems / nItemsThatCanFit));
		var nItemsPerColumn = Math.ceil(nItems / nColumns);
		var nItemsAdded = 0;
		var nItemsRemaining = nItems;
		var $holder = $('<div></div>');

		while(nItemsRemaining > 0)
		{
			var $outerDiv = $('<div class="menu-outer"></div>');
			var $innerDiv = $('<div class="menu-inner"></div>');

			var nItemsToAdd = Math.min(nItemsRemaining, nItemsPerColumn);
			var start = nItemsAdded;
			var end   = start + nItemsToAdd;

			$menuItems.slice(start, end).appendTo($innerDiv);
			$outerDiv.append($innerDiv);
			$holder.append($outerDiv);

			nItemsAdded     += nItemsToAdd;
			nItemsRemaining -= nItemsToAdd;
		}

		return $holder;
	}

	function getMenuPosition($div, depth, x, y)
	{
		if(depth == 0)
			return getFirstMenuPosition($div, depth, x, y);

		return getSubmenuPosition($div, depth);
	}

	function getFirstMenuPosition($div, depth, x, y)
	{
		var viewport   = g_getViewport();
		var menuWidth  = $div.width();
		var menuHeight = $div.height();
		var menuWidthShadow  = menuWidth  + SIZE_SHADOW_WIDTH;
		var menuHeightShadow = menuHeight + SIZE_SHADOW_HEIGHT;

		var showingAtCursor = (x != null && y != null);
		if(showingAtCursor)
		{
			if(y + menuHeightShadow > viewport.b) // Make sure menu doesn't overflow vertically
				y = Math.max(viewport.t, viewport.b - menuHeightShadow);  // Place along the bottom edge if it does
		}
		else
		{
			var $a = $rootNode;
			var offset = $a.offset();

			// Place underneath the root node by default
			x = offset.left
			y = offset.top + $a.outerHeight();

			// Show menu upwards if it's going to be outside the viewport otherwise (only if there's room)
			if(y + menuHeightShadow > viewport.b && offset.top >= menuHeightShadow)
				y = offset.top - menuHeightShadow;
		}

		if(x + menuWidthShadow > viewport.r) // Make sure menu doesn't overflow horizontally
			x = Math.max(viewport.l, viewport.r - menuWidthShadow); // Place along the right edge if it does

		return {x: x, y: y};
	}

	function getSubmenuPosition($div, depth)
	{
		var viewport   = g_getViewport();
		var menuWidth  = $div.width();
		var menuHeight = $div.height();
		var menuWidthShadow  = menuWidth  + SIZE_SHADOW_WIDTH;
		var menuHeightShadow = menuHeight + SIZE_SHADOW_HEIGHT;

		var $a = openLinks[depth - 1];
		var offset = $a.offset();
		var openToTheLeft = false;

		// Show to the right of the menu item
		x = offset.left + $a.outerWidth() - 5;
		y = offset.top - 2;

		if(x + menuWidthShadow > viewport.r) // Make sure menu doesn't overflow horizontally
			openToTheLeft = true;

		if(openToTheLeft)
			x = Math.max(viewport.l, offset.left - menuWidth);

		if(y + menuHeightShadow > viewport.b) // Make sure menu doesn't overflow vertically
			y = Math.max(viewport.t, viewport.b - menuHeightShadow);  // Place along the bottom edge if it does

		return {x: x, y: y};
	}

	function revealDiv($div, animated)
	{
		if(animated)
		{
			$div.css({
				opacity: '0'
			})
			.show()
			.animate({
				opacity: '1'
			}, 'fast', null, doneRevealing);
		}
		else
			$div.show();
	}

	function doneRevealing(a)
	{
		$(this).css('opacity', ''); // Remove opacity once animation is over to prevent a bug in IE8
	}

	function hideMenus(depth)
	{
		while(visibleMenus[depth])
		{
			visibleMenus[depth].stop().hide();
			visibleMenus[depth] = null;

			++depth;
		}
	}

	function closeLinks(depth)
	{
		while(openLinks[depth])
		{
			openLinks[depth].removeClass('open');
			openLinks[depth] = null;

			++depth;
		}
	}

	function isMenuVisible(depth)
	{
		return visibleMenus[depth || 0] != null;
	}

	// MENU UTILITY FUNCTIONS

	function getItemId(menuItem)
	{
		return menuItem[MENU_IDX_ID];
	}

	function isSeparator(menuItem)
	{
		return menuItem[MENU_IDX_ID] == null; // Separators don't have an ID
	}

	function hasSeparators(menu)
	{
		return $WH.in_array(menu, true, isSeparator) != -1;
	}

	function hasSubmenu(menuItem)
	{
		return menuItem[MENU_IDX_SUB] != null;
	}

	function findItemPosById(menu, id)
	{
		return $WH.in_array(menu, id, getItemId);
	}

	function hasPermissions(menuItem)
	{
		var opt = self.getItemOpt(menuItem);

		if(opt.requiredAccess && !User.hasPermissions(opt.requiredAccess))
			return false;

		return true;
	}

	function isItemVisible(menuItem)
	{
		if(!hasPermissions(menuItem))
			return false;

		if(hasSubmenu(menuItem))
		{
			if(!hasVisibleItems(menuItem[MENU_IDX_SUB]))
				return false;
		}

		return true;
	}

	function hasVisibleItems(menu)
	{
		return $WH.in_array(menu, true, isSubitemVisible) != -1;
	}

	function isSubitemVisible(menuItem)
	{
		return !isSeparator(menuItem) && hasPermissions(menuItem);
	}

	function getUniqueId(menu)
	{
		if(menu.uniqueId == null)
			menu.uniqueId = nextUniqueId++;

		return menu.uniqueId;
	}

	function traverseMenu(menu, func)
	{
		$.each(menu, function(idx, menuItem)
		{
			traverseMenuItem(menuItem, func);
		});
	}

	function traverseMenuItem(menuItem, func)
	{
		func(menuItem);
		if(hasSubmenu(menuItem))
			traverseMenu(menuItem[MENU_IDX_SUB], func);
	}

	function fixUrls(menu, url, opt, depth)
	{
		$.each(menu, function(idx, menuItem)
		{
			if(menuItem === undefined) return;
			if(isSeparator(menuItem)) return;

			if(menuItem[MENU_IDX_URL] == null) // Don't override if already set
				menuItem[MENU_IDX_URL] = url + menuItem[MENU_IDX_ID] + opt.hash;

			if(hasSubmenu(menuItem))
			{
				var accumulate = true;

				if(opt.useSimpleIds)
					accumulate = false;
				else if(opt.useSimpleIdsAfter != null && depth >= opt.useSimpleIdsAfter)
					accumulate = false;

				var nextUrl = url;
				if(accumulate)
					nextUrl += menuItem[MENU_IDX_ID] + '.';
				fixUrls(menuItem[MENU_IDX_SUB], nextUrl, opt, depth + 1);
			}
		});
	}

	function sortMenu(menu)
	{
		menu.sort(function(a, b)
		{
			return $WH.strcmp(a[MENU_IDX_NAME], b[MENU_IDX_NAME]);
		});
	}

	function sortSeparatedMenu(menu)
	{
		// Sort each group separately so headings stay where they are.

		var implodedMenu = self.implode(menu, { createHeadinglessGroup: true });

		$.each(implodedMenu, function(idx, submenu)
		{
			sortMenu(submenu[MENU_IDX_SUB]);
		});

		explodeInto(menu, implodedMenu);
	};

	function explodeInto(menu, implodedMenu) // Reverse of implode
	{
		menu.splice(0, menu.length); // Clear menu

		$.each(implodedMenu, function(idx, menuItem)
		{
			if(menuItem[MENU_IDX_NAME])
				menu.push([, menuItem[MENU_IDX_NAME]]); // Heading

			$.each(menuItem[MENU_IDX_SUB], function(idx, menuItem)
			{
				menu.push(menuItem);
			});
		});
	}

	// EVENTS

	function beforeCreateMenuItem(menuItem)
	{
		var opt = self.getItemOpt(menuItem);

		if(opt.checkedUrl && location.href.match(opt.checkedUrl))
			menuItem.checked = true;
	}

	function beforeShowMenu(menu)
	{
		if(menu.onBeforeShow)
			menu.onBeforeShow(menu);

		$.each(menu, function(idx, menuItem)
		{
			var opt = self.getItemOpt(menuItem);

			if(opt.onBeforeShow)
				opt.onBeforeShow(menuItem);
		});
	}

	function rootNodeOver(event)
	{
		clearTimeout(timer);

		var $node = $(this);

		if(!isMenuVisible())
		{
			// Use a small delay the 1st time to prevent undesired appearances
			timer = setTimeout(show.bind(null, $node.data('menu'), $node), DELAY_BEFORESHOW);
			return;
		}

		show($node.data('menu'), $node);
	}

	function rootNodeOut(event)
	{
		clearTimeout(timer);

		if(isMenuVisible())
			timer = setTimeout(hide, DELAY_BEFOREHIDE);
	}

	function rootNodeClick(event)
	{
		clearTimeout(timer);

		self.showAtCursor($(this).data('menu'), event);
	}

	function menuDivOver(event)
	{
		clearTimeout(timer);
	}

	function menuDivOut(event)
	{
		clearTimeout(timer);
		timer = setTimeout(hide, DELAY_BEFOREHIDE);
	}

	function menuItemOver(event)
	{
		clearTimeout(timer);

		var $a = $(this);
		var depth = event.data.depth;

		closeLinks(depth);

		var menuItem = $a.data('menuItem');

		var deepestDepth = depth;
		if(menuItem && hasSubmenu(menuItem))
		{
			$a.addClass('open');
			openLinks[depth] = $a;

			displayMenu(menuItem[MENU_IDX_SUB], depth + 1);

			++deepestDepth;
		}

		hideMenus(deepestDepth + 1);
	}

	function menuItemClick(event)
	{
		var $a = $(this);
		var menuItem = $a.data('menuItem');

		if(!menuItem)
			return;

		var opt = self.getItemOpt(menuItem);

		if(opt.onClick)
			opt.onClick();
	}
};

Menu.fixUrls(mn_achievements, '?achievements=');
Menu.fixUrls(mn_classes, '?class=');
Menu.fixUrls(mn_currencies, '?currencies=');
Menu.fixUrls(mn_factions, '?factions=');
Menu.fixUrls(mn_items, '?items=');
Menu.fixUrls(mn_itemSets, '?itemsets&filter=cl=', { hash: '0-2+1' });
Menu.fixUrls(mn_npcs, '?npcs=');
Menu.fixUrls(mn_objects, '?objects=');
Menu.fixUrls(mn_petCalc,  '?petcalc=');
Menu.fixUrls(mn_pets, '?pets=');
Menu.fixUrls(mn_quests, '?quests=');
Menu.fixUrls(mn_races, '?race=');
Menu.fixUrls(mn_spells, '?spells=');
Menu.fixUrls(mn_titles, '?titles=');
Menu.fixUrls(mn_zones, '?zones=');

$(document).ready(function() // Locale is only known later
{
	// if(Locale.getId() == LOCALE_ENUS)
		// return;

	Menu.sort(mn_classes);
	Menu.sort(mn_database);
	Menu.sortSubmenus(mn_items,
	[
		[4, 1], // Armor > Cloth
		[4, 2], // Armor > Leather
		[4, 3], // Armor > Mail
		[4, 4], // Armor > Plate
		[1],    // Containers
		[0],    // Consumables
		[16],   // Glyphs
		[7],    // Trade Goods
		[6],    // Projectiles
		[9]     // Recipes
	]);
	Menu.sort(mn_itemSets);
	Menu.sort(mn_npcs);
	Menu.sort(mn_objects);
	Menu.sort(mn_talentCalc);
	Menu.sort(mn_petCalc);
	Menu.sort(mn_pets);
	Menu.sort(mn_races);
	Menu.sort(mn_skills);
	Menu.sortSubmenus(mn_spells,
	[
		[7],  // Abilities
		[-2], // Talents
		[-3], // Pet Abilities
		[11], // Professions
		[9]   // Secondary Skills
	]);
});

var g_dev = false;
var g_locale = {
	id: 0,
	name: "enus"
};
var g_localTime = new Date();
var g_user = {
	id: 0,
	name: "",
	roles: 0
};

var g_locales = {
	0: 'enus',
	2: 'frfr',
	3: 'dede',
	6: 'eses',
	8: 'ruru'
};

/*
Global Profiler-related functions
*/

function g_cleanCharacterName(name) {
	return (name.match && name.match(/^[A-Z]/) ? name.charAt(0).toLowerCase() + name.substr(1) : name);
}

function g_getProfileUrl(profile) {
	if (profile.region) { // Armory character
		return '?profile=' + profile.region + '.' + profile.realm + '.' + g_cleanCharacterName(profile.name);
	}
	else { // Custom profile
		return '?profile=' + profile.id;
	}
}

function g_getProfileRealmUrl(profile) {
	return '?profiles=' + profile.region + '.' + profile.realm;
}

var Icon = {
    sizes: ['small', 'medium', 'large'],
    sizes2: [18, 36, 56],
    premiumOffsets: [[-56, -36], [-56, 0], [0, 0]],

    create: function(name, size, UNUSED, url, num, qty, noBorder) {
        var
            icon  = $WH.ce('div'),
            image = $WH.ce('ins'),
            tile  = $WH.ce('del');

        if (size == null) {
            size = 1;
        }

        icon.className = 'icon' + Icon.sizes[size];

        $WH.ae(icon, image);

        if (!noBorder) {
            $WH.ae(icon, tile);
        }

        Icon.setTexture(icon, size, name);

        if (url) {
            var a = $WH.ce('a');
            a.href = url;
            if (url.indexOf('wowhead.com') == -1 && url.substr(0, 5) == 'http:') {
                a.target = "_blank";
            }
            $WH.ae(icon, a);
        }
        else if (name) {
            var _ = icon.firstChild.style;
            var avatarIcon = (_.backgroundImage.indexOf('/avatars/') != -1);

            if (!avatarIcon) {
                icon.onclick = Icon.onClick;

                var a = $WH.ce('a');
                a.href = "javascript:;";
                $WH.ae(icon, a);
            }
        }

        Icon.setNumQty(icon, num, qty);

        return icon;
    },

    createUser: function(avatar, avatarMore, size, url, isPremium, noBorder) {
        if (avatar == 2) {
            avatarMore = g_staticUrl + '/uploads/avatars/' + avatarMore + '.jpg';
        }

        var icon = Icon.create(avatarMore, size, null, url, null, null, noBorder);

        if (isPremium) {
            icon.className += ' ' + icon.className + (isPremium == 2 ? '-gold' : '-premium');
        }

        if (avatar == 2) {
            Icon.moveTexture(icon, size, Icon.premiumOffsets[size][0], Icon.premiumOffsets[size][1], true);
        }

        return icon;
    },

    setTexture: function(icon, size, name) {
        if (!name) {
            return;
        }

        var _ = icon.firstChild.style;

        if (name.indexOf('/') != -1 || name.indexOf('?') != -1) {
            _.backgroundImage = 'url(' + name + ')';
        }
        else {
            _.backgroundImage = 'url(' + g_staticUrl + '/images/icons/' + Icon.sizes[size] + '/' + escape(name.toLowerCase()) + '.jpg)';
        }

        Icon.moveTexture(icon, size, 0, 0);
    },

    moveTexture: function(icon, size, x, y, exact) {
        var _ = icon.firstChild.style;

        if (x || y) {
            if (exact) {
                _.backgroundPosition = x + 'px ' + y + 'px';
            }
            else {
                _.backgroundPosition = (-x * Icon.sizes2[size]) + 'px ' + ( -y * Icon.sizes2[size]) + 'px';
            }
        }
        else if (_.backgroundPosition) {
            _.backgroundPosition = '';
        }
    },

    setNumQty: function(icon, num, qty) {
        var _ = $WH.gE(icon, 'span');

        for (var i = 0, len = _.length; i < len; ++i) {
            if (_[i]) {
                $WH.de(_[i]);
            }
        }
        if (num != null && ((num > 1 && num < 2147483647) || num.length)) {
            _ = g_createGlow(num, 'q1');
            _.style.right = '0';
            _.style.bottom = '0';
            _.style.position = 'absolute';
            $WH.ae(icon, _);
        }

        if (qty != null && qty > 0) {
            _ = g_createGlow('(' + qty + ')', 'q');
            _.style.left = '0';
            _.style.top = '0';
            _.style.position = 'absolute';
            $WH.ae(icon, _);
        }
    },

    getLink: function(icon) {
        return $WH.gE(icon, 'a')[0];
    },

    showIconName: function(x) {
        if (x.firstChild) {
            var _ = x.firstChild.style;
            if (_.backgroundImage.length && (_.backgroundImage.indexOf(g_staticUrl) >= 4 || g_staticUrl == '')) {
                var
                    start = _.backgroundImage.lastIndexOf('/'),
                    end   = _.backgroundImage.indexOf('.jpg');

                if (start != -1 && end != -1) {
                    Icon.displayIcon(_.backgroundImage.substring(start + 1, end));
                }
            }
        }
    },

    onClick: function() {
        Icon.showIconName(this);
    },

    displayIcon: function(icon) {
        if (!Dialog.templates.icondisplay) {
            var w = 364;
            switch (g_locale.id) {
                case 6:
                    w = 380;
                    break;

                case 8:
                    w = 384;
                    break;
            }

            Dialog.templates.icondisplay = {
                title: LANG.icon,
                width: w,
                buttons: [['arrow', LANG.original], ['cancel', LANG.close]],
                fields:
                [
                    {
                        id: 'icon',
                        label: LANG.dialog_imagename,
                        required: 1,
                        type: 'text',
                        labelAlign: 'left',
                        compute: function(field, value, form, td) {
                            var wrapper = $WH.ce('div');
                            td.style.width = '300px';
                            wrapper.style.position = 'relative';
                            wrapper.style.cssFloat = 'left';
                            wrapper.style.paddingRight = '6px';
                            field.style.width = '200px';

                            var divIcon = this.iconDiv = $WH.ce('div');
                            divIcon.style.position = 'absolute';
                            divIcon.style.top = '-12px';
                            divIcon.style.right = '-70px';

                            divIcon.update = function() {
                                setTimeout(function() {
                                    field.focus();
                                    field.select();
                                }, 10);
                                $WH.ee(divIcon);
                                $WH.ae(divIcon, Icon.create(field.value, 2));
                            };

                            $WH.ae(divIcon, Icon.create(value, 2));
                            $WH.ae(wrapper, divIcon);
                            $WH.ae(wrapper, field);
                            $WH.ae(td, wrapper);
                        }
                    },
                    {
                        id: 'location',
                        label: " ",
                        required: 1,
                        type: 'caption',
                        compute: function(field, value, form, th, tr) {
                            $WH.ee(th);
                            th.style.padding = '3px 3px 0 3px';
                            th.style.lineHeight = '17px';
                            th.style.whiteSpace = 'normal';
                            var wrapper = $WH.ce('div');
                            wrapper.style.position = 'relative';
                            wrapper.style.width = '250px';

                            var span = $WH.ce('span');

                            var text = LANG.dialog_seeallusingicon;
                            text = text.replace('$1', '<a href="?items&filter=cr=142;crs=0;crv=' + this.data.icon + '">' + LANG.types[3][3] + '</a>');
                            text = text.replace('$2', '<a href="?spells&filter=cr=15;crs=0;crv=' + this.data.icon + '">' + LANG.types[6][3] + '</a>');
                            text = text.replace('$3', '<a href="?achievements&filter=cr=10;crs=0;crv=' + this.data.icon + '">' + LANG.types[10][3] + '</a>');

                            span.innerHTML = text;
                            $WH.ae(wrapper, span);
                            $WH.ae(th, wrapper);
                        }
                    }
                ],

                onInit: function(form) {
                    this.updateIcon = this.template.updateIcon.bind(this, form);
                },

                onShow: function(form) {
                    this.updateIcon();
                    if (location.hash && location.hash.indexOf('#icon') == -1) {
                        this.oldHash = location.hash;
                    }
                    else {
                        this.oldHash = '';
                    }

                    var hash = '#icon';

                    // Add icon name on all pages but item, spell and achievement pages (where the name is already available).
                    var nameDisabled = ($WH.isset('g_pageInfo') && g_pageInfo.type && $WH.in_array([3, 6, 10], g_pageInfo.type) == -1);
                    if (!nameDisabled) {
                        hash += ':' + this.data.icon;
                    }

                    location.hash = hash;
                },

                onHide: function(form) {
                    if (this.oldHash) {
                        location.hash = this.oldHash;
                    }
                    else {
                        location.hash = '#.';
                    }
                },

                updateIcon: function(form) {
                    this.iconDiv.update();
                },

                onSubmit: function(unused, data, button, form) {
                    if (button == 'arrow') {
                        var win = window.open(g_staticUrl + '/images/icons/large/' + data.icon.toLowerCase() + '.jpg', '_blank');
                        win.focus();
                        return false;
                    }

                    return true;
                }
            };
        }

        if (!Icon.icDialog) {
            Icon.icDialog = new Dialog();
        }

        Icon.icDialog.show('icondisplay', {data: {icon: icon}});
    },

    checkPound: function() {
        if (location.hash && location.hash.indexOf('#icon') == 0) {
            var parts = location.hash.split(':');
            var icon = false;
            if (parts.length == 2) {
                icon = parts[1];
            }
            else if (parts.length == 1 && $WH.isset('g_pageInfo')) {
                switch (g_pageInfo.type) {
                    case 3: // Item
                        icon = g_items[g_pageInfo.typeId].icon.toLowerCase();
                        break;
                    case 6: // Spell
                        icon = g_spells[g_pageInfo.typeId].icon.toLowerCase();
                        break;
                    case 10: // Achievement
                        icon = g_achievements[g_pageInfo.typeId].icon.toLowerCase();
                        break;
                }
            }

            if (icon) {
                Icon.displayIcon(icon);
            }
        }
    }
};
DomContentLoaded.addEvent(Icon.checkPound);

function Rectangle(left, top, width, height) {
	this.l = left;
	this.t = top;
	this.r = left + width;
	this.b = top + height;
}

function g_getViewport()
{
	var win = $(window);

	return new Rectangle(win.scrollLeft(), win.scrollTop(), win.width(), win.height());
}

Rectangle.prototype = {

	intersectWith: function(rect) {
		var result = !(
			this.l >= rect.r || rect.l >= this.r ||
			this.t >= rect.b || rect.t >= this.b
		);

		return result;
	},

	contains: function(rect) {
		var result = (
			this.l <= rect.l && this.t <= rect.t &&
			this.r >= rect.r &&	this.b >= rect.b
		);

		return result;
	},

	containedIn: function(rect) {
		return rect.contains(this);
	}

};

var RedButton = {
    create: function(text, enabled, func) {
        var
            a    = $WH.ce('a'),
            em   = $WH.ce('em'),
            b    = $WH.ce('b'),
            i    = $WH.ce('i'),
            span = $WH.ce('span');

        a.href = 'javascript:;';
        a.className = 'button-red';

        $WH.ae(b, i);
        $WH.ae(em, b);
        $WH.ae(em, span);
        $WH.ae(a, em);

        RedButton.setText(a, text);
        RedButton.enable(a, enabled);
        RedButton.setFunc(a, func);

        return a;
    },

    setText: function(button, text) {
        $WH.st(button.firstChild.childNodes[0].firstChild, text); // em, b, i
        $WH.st(button.firstChild.childNodes[1], text); // em, span
    },

    enable: function(button, enabled) {
        if (enabled || enabled == null) {
            button.className = button.className.replace('button-red-disabled', '');
        }
        else if (button.className.indexOf('button-red-disabled') == -1) {
            button.className += ' button-red-disabled';
        }
    },

    setFunc: function(button, func) {
        button.onclick = (func ? func: null);
    }
};

var LiveSearch = new function() {
    var
        currentTextbox,
        lastSearch = {},
        lastDiv,
        timer,
        prepared,
        container,
        cancelNext,
        hasData,
        summary,
        selection,
        LIVESEARCH_DELAY = 500;

    function setText(textbox, txt) {
        textbox.value = txt;
        textbox.selectionStart = textbox.selectionEnd = txt.length;
    }

    function colorDiv(div, fromOver) {
        if (lastDiv) {
            lastDiv.className = lastDiv.className.replace("live-search-selected", "");
        }

        lastDiv = div;
        lastDiv.className += " live-search-selected";
        selection = div.i;

        if (!fromOver) {
            show();
            setTimeout(setText.bind(0, currentTextbox, g_getTextContent(div.firstChild.firstChild.childNodes[1])), 1);
            cancelNext = 1;
        }
    }

    function aOver() {
        colorDiv(this.parentNode.parentNode, 1);
    }

    function isVisible() {
        if (!container) {
            return false;
        }

        return container.style.display != "none";
    }

    function adjust(fromResize) {
        if (fromResize == 1 && !isVisible()) {
            return;
        }

        if (currentTextbox == null) {
            return;
        }

        var c = $WH.ac(currentTextbox);
        container.style.left  = (c[0] - 2) + "px";
        container.style.top   = (c[1] + currentTextbox.offsetHeight + 1) + "px";
        container.style.width = currentTextbox.offsetWidth + "px";
    }

    function prepare() {
        if (prepared) {
            return;
        }

        prepared = 1;

        container = $WH.ce("div");
        container.className = "live-search";
        container.style.display = "none";

        $WH.ae($WH.ge("layers"), container);
        $WH.aE(window, "resize", adjust.bind(0, 1));
        $WH.aE(document, "click", hide);
    }

    function show() {
        if (container && !isVisible()) {
            adjust();
            container.style.display = "";
        }
    }

    function hide() {
        if (container) {
            container.style.display = "none";
        }
    }

    function highlight(match) {
        return "<b><u>" + match + "</u></b>";
    }

    function display(textbox, search, suggz, dataz) {
        prepare();
        show();
        lastA = null;
        hasData = 1;
        selection = null;

        while (container.firstChild) {
            $WH.de(container.firstChild);
        }

        if (!$WH.Browser.ie6) {
            $WH.ae(container, $WH.ce("em"));
            $WH.ae(container, $WH.ce("var"));
            $WH.ae(container, $WH.ce("strong"));
        }

        search = search.replace(/[^a-z0-9\-]/i, " ");
        search = $WH.trim(search.replace(/\s+/g, " "));

        var regex = g_createOrRegex(search);

        for (var i = 0, len = suggz.length; i < len; ++i) {
            var pos = suggz[i].lastIndexOf("(");
            if (pos != -1) {
                suggz[i] = suggz[i].substr(0, pos - 1);
            }

            var
                type   = dataz[i][0],
                typeId = dataz[i][1],
                param1 = dataz[i][2],
                param2 = dataz[i][3],
                a      = $WH.ce("a"),
                sp     = $WH.ce("i"),
                sp2    = $WH.ce("span"),
                div    = $WH.ce("div"),
                div2   = $WH.ce("div");
                div.i  = i;

            a.onmouseover = aOver;
            a.href = "?" + g_types[type] + "=" + typeId;

            if (textbox._append) {
                a.rel += textbox._append;
            }

            if (type == 1 && param1 != null) {
                div.className += ' live-search-icon-boss';
            }
            if (type == 3 && param2 != null) {
                a.className += " q" + param2;
            }
            else if (type == 4 && param1 != null) {
                a.className += " q" + param1;
            }
            else if (type == 13) {
                a.className += ' c' + typeId;
            }

            if ((type == 3 || type == 6 || type == 9 || type == 10 || type == 13 || type == 14 || type == 15 || type == 17) && param1) {
                div.className += " live-search-icon";
                div.style.backgroundImage = "url(" + g_staticUrl + "/images/icons/small/" + param1.toLowerCase() + ".jpg)";
            }
            else if ((type == 5 || type == 11) && param1 >= 1 && param1 <= 2) {
                div.className += " live-search-icon-quest-" + (param1 == 1 ? "alliance" : "horde");
            }

            $WH.ae(sp, $WH.ct(LANG.types[type][0]));
            $WH.ae(a, sp);

            var buffer = suggz[i];
            buffer = buffer.replace(regex, highlight);

            if (type == 11) {
                buffer = buffer.replace('%s', '<span class="q0">&lt;'+ LANG.name + '&gt;</span>');
            }

            sp2.innerHTML = buffer;
            $WH.ae(a, sp2);

            if (type == 6 && param2) {
                $WH.ae(a, $WH.ct(" (" + param2 + ")"));
            }

            $WH.ae(div2, a);
            $WH.ae(div, div2);
            $WH.ae(container, div);
        }
    }

    function receive(xhr, opt) {
        var text = xhr.responseText;
        if (text.charAt(0) != "[" || text.charAt(text.length - 1) != "]") {
            return;
        }

        var a = eval(text);
        var search = a[0];

        if (search == opt.search) {
            if (a.length == 8) {
                display(opt.textbox, search, a[1], a[7]);
            }
            else {
                hide();
            }
        }
    }

    function fetch(textbox, search) {
        var url = "?search=" + $WH.urlencode(search);

        if (textbox._type) {
            url += "&json&type=" + textbox._type;
        }
        else {
            url += "&opensearch";
        }

        new Ajax(url, {
            onSuccess: receive,
            textbox: textbox,
            search: search
        })
    }

    function preFetch(textbox, search) {
        if (cancelNext) {
            cancelNext = 0;
            return;
        }
        hasData = 0;
        if (timer > 0) {
            clearTimeout(timer);
            timer = 0;
        }
        timer = setTimeout(fetch.bind(0, textbox, search), LIVESEARCH_DELAY);
    }

    function cycle(dir) {
        if (!isVisible()) {
            if (hasData) {
                show();
            }
            return;
        }

        var firstNode = (container.childNodes[0].nodeName == "EM" ? container.childNodes[3] : container.firstChild);
        var bakDiv = dir ? firstNode : container.lastChild;

        if (lastDiv == null) {
            colorDiv(bakDiv);
        }
        else {
            var div = dir ? lastDiv.nextSibling: lastDiv.previousSibling;
            if (div) {
                if (div.nodeName == "STRONG") {
                    div = container.lastChild;
                }
                colorDiv(div);
            }
            else {
                colorDiv(bakDiv);
            }
        }
    }

    function onKeyUp(e) {
        e = $WH.$E(e);
        var textbox = e._target;

        switch (e.keyCode) {
            case 48:
            case 96:
            case 107:
            case 109:
                if ($WH.Browser.firefox && e.ctrlKey) {
                    adjust(textbox);
                    break;
                }
                break;
        }

        var search = $WH.trim(textbox.value.replace(/\s+/g, " "));
        if (search == lastSearch[textbox.id]) {
            return;
        }

        lastSearch[textbox.id] = search;
        if (search.length) {
            preFetch(textbox, search);
        }
        else {
            hide();
        }
    }

    function onKeyDown(e) {
        e = $WH.$E(e);
        var textbox = e._target;

        switch (e.keyCode) {
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
    }

    function onFocus(e) {
        e = $WH.$E(e);
        var textbox = e._target;

        if (textbox != document) {
            currentTextbox = textbox;
        }
    }

    this.attach = function(textbox) {
        if (textbox.getAttribute("autocomplete") == "off") {
            return;
        }
        textbox.setAttribute("autocomplete", "off");

        $WH.aE(textbox, "focus", onFocus);
        $WH.aE(textbox, "keyup", onKeyUp);
        $WH.aE(textbox, "keydown", onKeyDown);
    };

    this.reset = function(textbox) {
        lastSearch[textbox.id] = null;
        textbox.value = "";
        hasData = 0;
        hide();
    };

    this.hide = function() {
        hide();
    }
};

var Lightbox = new function() {
    var
        overlay,
        outer,
        inner,
        divs = {},

        funcs = {},

        prepared,
        lastId;

    function hookEvents() {
        $WH.aE(overlay, 'click', hide);
        $WH.aE(document, 'keydown', onKeyDown);
        $WH.aE(window, 'resize', onResize);
    }

    function unhookEvents() {
        $WH.dE(overlay, 'click', hide);
        $WH.dE(document, 'keydown', onKeyDown);
        $WH.dE(window, 'resize', onResize);
    }

    function prepare() {
        if (prepared) {
            return;
        }

        prepared = 1;

        var dest = document.body;

        overlay = $WH.ce('div');
        overlay.className = 'lightbox-overlay';

        outer = $WH.ce('div');
        outer.className = 'lightbox-outer';

        inner = $WH.ce('div');
        inner.className = 'lightbox-inner';

        overlay.style.display = outer.style.display = 'none';

        $WH.ae(dest, overlay);
        $WH.ae(outer, inner);
        $WH.ae(dest, outer);
    }

    function onKeyDown(e) {
        e = $WH.$E(e);
        switch (e.keyCode) {
        case 27: // Escape
            hide();
            break;
        }
    }

    function onResize(fake) {
        if (fake != 1234) {
            if (funcs.onResize) {
                funcs.onResize();
            }
        }

        overlay.style.height = document.body.offsetHeight + 'px';
    }

    function hide() {
        if (!prepared) {
            return;
        }

        unhookEvents();

        if (funcs.onHide) {
            funcs.onHide();
        }

        overlay.style.display = outer.style.display = 'none';

        g_enableScroll(true);
    }

    function reveal() {
        overlay.style.display = outer.style.display = divs[lastId].style.display = '';
        Lightbox.setSize(inner.offsetWidth, inner.offsetHeight, 1);
    }

    this.setSize = function(w, h, auto) {
        if (!$WH.Browser.ie) {
            inner.style.visibility = 'hidden';
        }

        if (!auto) {
            inner.style.width = w + 'px';
            if (h) {
                inner.style.height = h + 'px';
            }
        }

        inner.style.left = -parseInt(w / 2) + 'px';
        if (h) {
            inner.style.top    = -parseInt(h / 2) + 'px';
        }
        inner.style.visibility = 'visible';
    };

    this.show = function(id, _funcs, opt) {
        funcs = _funcs || {};

        prepare();

        hookEvents();

        if (lastId != id && divs[lastId] != null) {
            divs[lastId].style.display = 'none';
        }

        lastId = id;

        var
            first = 0,
            d;

        if (divs[id] == null) {
            first = 1;
            d = $WH.ce('div');
            $WH.ae(inner, d);
            divs[id] = d;
        }
        else {
            d = divs[id];
        }

        if (funcs.onShow) {
            funcs.onShow(d, first, opt);
        }

        onResize(1234);
        reveal();

        g_enableScroll(false);
    };

    this.reveal = function() {
        reveal();
    };

    this.hide = function() {
        hide();
    };

    this.isVisible = function() {
        return (overlay && overlay.style.display != 'none');
    }
};

var ModelViewer = new function() {
	this.validSlots = [1,3,4,5,6,7,8,9,10,13,14,15,16,17,19,20,21,22,23,25,26];
	this.slotMap = {1: 1, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9, 10: 10, 13: 21, 14: 22, 15: 22, 16: 16, 17: 21, 19: 19, 20: 5, 21: 21, 22: 22, 23: 22, 25: 21, 26: 21};
    var
        model,
        modelType,
        equipList = [],
        optBak,
        _w,
        _o,
        _z,
        modelDiv,
        raceSel1,
        raceSel2,
        sexSel,
        oldHash,
        mode,
        readExtraPound,

    races = [
        {id: 10, name: g_chr_races[10], model: 'bloodelf' },
        {id: 11, name: g_chr_races[11], model: 'draenei' },
        {id: 3,  name: g_chr_races[3],  model: 'dwarf' },
        {id: 7,  name: g_chr_races[7],  model: 'gnome' },
        {id: 1,  name: g_chr_races[1],  model: 'human' },
        {id: 4,  name: g_chr_races[4],  model: 'nightelf' },
        {id: 2,  name: g_chr_races[2],  model: 'orc' },
        {id: 6,  name: g_chr_races[6],  model: 'tauren' },
        {id: 8,  name: g_chr_races[8],  model: 'troll' },
        {id: 5,  name: g_chr_races[5],  model: 'scourge' }
    ],

    sexes = [
        {id: 0, name: LANG.male,   model: 'male' },
        {id: 1, name: LANG.female, model: 'female' }
    ];

    function clear() {
        _w.style.display = 'none';
        _o.style.display = 'none';
        _z.style.display = 'none';
    }

    function getRaceSex() {
        var
            race,
            sex;

        if (raceSel1.style.display == '') {
            race = (raceSel1.selectedIndex >= 0 ? raceSel1.options[raceSel1.selectedIndex].value : '');
        }
        else {
            race = (raceSel2.selectedIndex >= 0 ? raceSel2.options[raceSel2.selectedIndex].value : '');
        }

        sex = (sexSel.selectedIndex >= 0 ? sexSel.options[sexSel.selectedIndex].value : 0);

        return { r: race, s: sex };
    }

    function isRaceSexValid(race, sex) {
        return (!isNaN(race) && race > 0 && $WH.in_array(races, race, function(x) {
            return x.id;
        }) != -1 && !isNaN(sex) && sex >= 0 && sex <= 1);
    }

    function render() {
        if (mode == 2 && !f()) {
            mode = 0;
        }
        if (mode == 2) {
            var G = '<object id="3dviewer-plugin" type="application/x-zam-wowmodel" width="600" height="400"><param name="model" value="' + model + '" /><param name="modelType" value="' + modelType + '" /><param name="contentPath" value="http://static.wowhead.com/modelviewer/" />';
            if (modelType == 16 && equipList.length) {
                G += '<param name="equipList" value="' + equipList.join(',') + '" />';
            }
            G += '<param name="bgColor" value="#181818" /></object>';
            _z.innerHTML = G;
            _z.style.display = '';
        }
        else {
            if (mode == 1) {
                var G = '<applet id="3dviewer-java" code="org.jdesktop.applet.util.JNLPAppletLauncher" width="600" height="400" archive="http://static.wowhead.com/modelviewer/applet-launcher.jar,http://download.java.net/media/jogl/builds/archive/jsr-231-webstart-current/jogl.jar,http://download.java.net/media/gluegen/webstart/gluegen-rt.jar,http://download.java.net/media/java3d/webstart/release/vecmath/latest/vecmath.jar,http://static.wowhead.com/modelviewer/ModelView510.jar"><param name="jnlp_href" value="http://static.wowhead.com/modelviewer/ModelView.jnlp"><param name="codebase_lookup" value="false"><param name="cache_option" value="no"><param name="subapplet.classname" value="modelview.ModelViewerApplet"><param name="subapplet.displayname" value="Model Viewer Applet"><param name="progressbar" value="true"><param name="jnlpNumExtensions" value="1"><param name="jnlpExtension1" value="http://download.java.net/media/jogl/builds/archive/jsr-231-webstart-current/jogl.jnlp"><param name="contentPath" value="http://static.wowhead.com/modelviewer/"><param name="model" value="' + model + '"><param name="modelType" value="' + modelType + '">';
                if (modelType == 16 && equipList.length) {
                    G += '<param name="equipList" value="' + equipList.join(',') + '">';
                }
                G += '<param name="bgColor" value="#181818"></applet>';
                _o.innerHTML = G;
                _o.style.display = '';
            }
            else {
                var flashVars = {
                    model: model,
                    modelType: modelType,
                    contentPath: 'http://static.wowhead.com/modelviewer/'
                    // contentPath: g_staticUrl + '/modelviewer/'
                };

                var params = {
                    quality: 'high',
                    allowscriptaccess: 'always',
                    allowfullscreen: true,
                    menu: false,
                    bgcolor: '#181818',
                    wmode: 'direct'
                };

                var attributes = { };

                if (modelType == 16 && equipList.length) {
                    flashVars.equipList = equipList.join(',');
                }

                // swfobject.embedSWF(g_staticUrl + '/modelviewer/ZAMviewerfp11.swf', 'modelviewer-generic', '600', '400', "11.0.0", g_staticUrl + '/modelviewer/expressInstall.swf', flashVars, params, attributes);
                swfobject.embedSWF('http://static.wowhead.com/modelviewer/ZAMviewerfp11.swf', 'modelviewer-generic', '600', '400', '10.0.0', 'http://static.wowhead.com/modelviewer/expressInstall.swf', flashVars, params, attributes);
                _w.style.display = '';
            }
        }
        var
            foo  = getRaceSex(),
            race = foo.r,
            sex  = foo.s;

        if (!optBak.noPound) {
            var url = '#modelviewer';
			var foo = $WH.ge('view3D-button');
			if (!foo) {
                switch (optBak.type) {
                    case 1: // npc
                        url += ':1:' + optBak.displayId + ':' + (optBak.humanoid | 0);
                        break;
                    case 2: // object
                        url += ':2:' + optBak.displayId;
                        break;
                    case 3: // item
                        url += ':3:' + optBak.displayId + ':' + (optBak.slot | 0);
                        break;
                    case 4: // item set
                        url += ':4:' + equipList.join(';');
                        break;
                }
            }
            if (race && sex) {
                url += ':' + race + '+' + sex;
            }
            else {
                url += ':';
            }

            if (optBak.extraPound != null) {
                url += ':' + optBak.extraPound;
            }
            location.replace($WH.rtrim(url, ':'));
        }
    }

    function onSelChange() {
        var
            foo  = getRaceSex(),
            race = foo.r,
            sex  = foo.s;

        if (!race) {
            if (sexSel.style.display == 'none') {
                return;
            }

            sexSel.style.display = 'none';

            model = equipList[1];
            switch (optBak.slot) {
                case 1:
                    modelType = 2; // Helm
                    break;
                case 3:
                    modelType = 4; // Shoulder
                    break;
                default:
                    modelType = 1; // Item
            }
        }
        else {
            if (sexSel.style.display == 'none') {
                sexSel.style.display = '';
            }

            var foo = function(x) {
                return x.id;
            };
            var raceIndex = $WH.in_array(races, race, foo);
            var sexIndex = $WH.in_array(sexes, sex, foo);

            if (raceIndex != -1 && sexIndex != -1) {
                model = races[raceIndex].model + sexes[sexIndex].model;
                modelType = 16;
            }
        }

        clear();
        render();
    }

    function j(newMode) {
        if (newMode == mode) {
            return;
        }

        g_setSelectedLink(this, 'modelviewer-mode');

        clear();

        if (mode == null) {
            mode = newMode;
            setTimeout(render, 50);
        }
        else {
            mode = newMode;
            $WH.sc('modelviewer_mode', 7, newMode, '/', location.hostname);
            // $WH.sc('modelviewer_mode', 7, newMode, '/', '.wowhead.com');
            render();
        }
    }

    function initRaceSex(allowNoRace, opt) {
        var
            race = -1,
            sex  = -1,

            sel,
            offset;

        if (opt.race != null && opt.sex != null) {
            race = opt.race;
            sex = opt.sex;

            modelDiv.style.display = 'none';
            allowNoRace = 0;
        }
        else {
            modelDiv.style.display = '';
        }

        if (race == -1 && sex == -1) {
            if (location.hash) {
                var matches = location.hash.match(/modelviewer:.*?([0-9]+)\+([0-9]+)/);
                if (matches != null) {
                    if (isRaceSexValid(matches[1], matches[2])) {
                        race = matches[1];
                        sex  = matches[2];
                        sexSel.style.display = '';
                    }
                }
            }
        }

        if (allowNoRace) {
            sel    = raceSel1;
            offset = 1;

            raceSel1.style.display = '';
            raceSel1.selectedIndex = -1;
            raceSel2.style.display = 'none';
            if (sex == -1) {
                sexSel.style.display = 'none';
            }
        }
        else {
            if (race == -1 && sex == -1) {
                var
                    cooRace = (g_user && g_user.settings ? g_user.settings.modelrace: 1),
                    cooSex  = (g_user && g_user.settings ? g_user.settings.modelgender - 1 : 0);

                if (isRaceSexValid(cooRace, cooSex)) {
                    race = cooRace;
                    sex  = cooSex;
                }
                else {
                    // Default
                    race = 1; // Human
                    sex  = 0; // Male
                }
            }

            sel    = raceSel2;
            offset = 0;

            raceSel1.style.display = 'none';
            raceSel2.style.display = '';
            sexSel.style.display   = '';
        }

        if (sex != -1) {
            sexSel.selectedIndex = sex;
        }

        if (race != -1 && sex != -1) {
            var foo = function(x) {
                return x.id;
            };

            var raceIndex = $WH.in_array(races, race, foo);
            var sexIndex  = $WH.in_array(sexes, sex, foo);

            if (raceIndex != -1 && sexIndex != -1) {
                model = races[raceIndex].model + sexes[sexIndex].model;
                modelType = 16;

                raceIndex += offset;

                sel.selectedIndex = raceIndex;
                sexSel.selectedIndex = sexIndex;
            }
        }
    }

    function f() {
        var E = navigator.mimeTypes['application/x-zam-wowmodel'];
        if (E) {
            var D = E.enabledPlugin;
            if (D) {
                return true
            }
        }
        return false
    }

    function onHide() {
        if (!optBak.noPound) {
            if (!optBak.fromTag && oldHash && oldHash.indexOf('modelviewer') == -1) {
                location.replace(oldHash);
            }
            else {
                location.replace('#.');
            }
        }

        if (optBak.onHide) {
            optBak.onHide();
        }
    }

    function onShow(dest, first, opt) {
        var
            G,
            E;

        if (!opt.displayAd || g_user.premium) {
            Lightbox.setSize(620, 452);
        }
        else {
            Lightbox.setSize(749, 546);
        }

        if (first) {
            dest.className = 'modelviewer';
            var screen = $WH.ce('div');
            _w = $WH.ce('div');
            _o = $WH.ce('div');
            _z = $WH.ce('div');
            var flashDiv = $WH.ce('div');
            flashDiv.id = 'modelviewer-generic';
            $WH.ae(_w, flashDiv);
            screen.className = 'modelviewer-screen';
            _w.style.display = _o.style.display = _z.style.display = 'none';
            $WH.ae(screen, _w);
            $WH.ae(screen, _o);
            $WH.ae(screen, _z);
            var screenbg = $WH.ce('div');
            screenbg.style.backgroundColor = '#181818';
            screenbg.style.margin = '0';
            $WH.ae(screenbg, screen);
            $WH.ae(dest, screenbg);
            G = $WH.ce('a'),
            E = $WH.ce('a');
            G.className = 'modelviewer-help';
            G.href = '?help=modelviewer';
            G.target = '_blank';
            $WH.ae(G, $WH.ce('span'));
            E.className = 'modelviewer-close';
            E.href = 'javascript:;';
            E.onclick = Lightbox.hide;
            $WH.ae(E, $WH.ce('span'));
            $WH.ae(dest, E);
            $WH.ae(dest, G);
            var N = $WH.ce('div'),
            F = $WH.ce('span'),
            G = $WH.ce('a'),
            E = $WH.ce('a');
            N.className = 'modelviewer-quality';
            G.href = E.href = 'javascript:;';
            $WH.ae(G, $WH.ct('Flash'));
            $WH.ae(E, $WH.ct('Java'));
            G.onclick = j.bind(G, 0);
            E.onclick = j.bind(E, 1);
            $WH.ae(F, G);
            $WH.ae(F, $WH.ct(' ' + String.fromCharCode(160)));
            $WH.ae(F, E);
            if (f()) {
                var D = $WH.ce('a');
                D.href = 'javascript:;';
                $WH.ae(D, $WH.ct('Plugin'));
                D.onclick = j.bind(D, 2);
                $WH.ae(F, $WH.ct(' ' + String.fromCharCode(160)));
                $WH.ae(F, D)
            }
            $WH.ae(N, $WH.ce('div'));
            $WH.ae(N, F);
            $WH.ae(dest, N);

            modelDiv = $WH.ce('div');
            modelDiv.className = 'modelviewer-model';

            var foo = function(a, b) {
                return $WH.strcmp(a.name, b.name);
            };

            races.sort(foo);
            sexes.sort(foo);

            raceSel1 = $WH.ce('select');
            raceSel2 = $WH.ce('select');
            sexSel   = $WH.ce('select');
            raceSel1.onchange = raceSel2.onchange = sexSel.onchange = onSelChange;

            $WH.ae(raceSel1, $WH.ce('option'));
            for (var i = 0, len = races.length; i < len; ++i) {
                var o = $WH.ce('option');
                o.value = races[i].id;
                $WH.ae(o, $WH.ct(races[i].name));
                $WH.ae(raceSel1, o);
            }

            for (var i = 0, len = races.length; i < len; ++i) {
                var o = $WH.ce('option');
                o.value = races[i].id;
                $WH.ae(o, $WH.ct(races[i].name));
                $WH.ae(raceSel2, o);
            }

            for (var i = 0, len = sexes.length; i < len; ++i) {
                var o = $WH.ce('option');
                o.value = sexes[i].id;
                $WH.ae(o, $WH.ct(sexes[i].name));
                $WH.ae(sexSel, o);
            }
            sexSel.style.display = 'none';
            $WH.ae(modelDiv, $WH.ce('div'));
            $WH.ae(modelDiv, raceSel1);
            $WH.ae(modelDiv, raceSel2);
            $WH.ae(modelDiv, sexSel);
            $WH.ae(dest, modelDiv);
            d = $WH.ce('div');
            d.className = 'clear';
            $WH.ae(dest, d);
        }

        switch (opt.type) {
        case 1: // NPC
            modelDiv.style.display = 'none';
            if (opt.humanoid) {
                modelType = 32; // Humanoid NPC
            }
            else {
                modelType = 8; // NPC
            }
            model = opt.displayId;
            break;
        case 2: // Object
            modelDiv.style.display = 'none';
            modelType = 64; // Object
            model = opt.displayId;
            break;
        case 3: // Item
            equipList = [opt.slot, opt.displayId];
            if ($WH.in_array([4, 5, 6, 7, 8, 9, 10, 16, 19, 20], opt.slot) != -1) {
                initRaceSex(0, opt)
            }
            else {
                switch (opt.slot) {
                case 1:
                    modelType = 2; // Helm
                    break;
                case 3:
                    modelType = 4; // Shoulder
                    break;
                default:
                    modelType = 1; // Item
                }

                model = opt.displayId;

                initRaceSex(1, opt);
            }
            break;
        case 4: // Item Set
            equipList = opt.equipList;
            initRaceSex(0, opt)
        }

        if (first) {
            if ($WH.gc('modelviewer_mode') == '2' && f()) {
                D.onclick()
            } else {
                if ($WH.gc('modelviewer_mode') == '1') {
                    E.onclick()
                } else {
                    G.onclick()
                }
            }
        }
        else {

            clear();
            setTimeout(render, 1);
        }

        oldHash = location.hash;
    }

    this.checkPound = function() {
        if (location.hash && location.hash.indexOf('#modelviewer') == 0) {
            var parts = location.hash.split(':');
            if (parts.length >= 3) {
                parts.shift(); // - #modelviewer
                var type = parseInt(parts.shift());
                var opt = { type: type };
                switch (type) {
                    case 1: // npc
                        opt.displayId = parseInt(parts.shift());
                        var humanoid = parseInt(parts.shift());
                        if (humanoid == 1) {
                            opt.humanoid = 1;
                        }
                        break;
                    case 2: // object
                        opt.displayId = parseInt(parts.shift());
                        break;
                    case 3: // item
                        opt.displayId = parseInt(parts.shift());
                        opt.slot = parseInt(parts.shift());
                        break;
                    case 4: // item set
                        var list = parts.shift();
                        opt.equipList = list.split(';');
                        break;
                }
                if (opt.displayId || opt.equipList) {
                    ModelViewer.show(opt);
                }

                if (readExtraPound != null) {
                    if (parts.length > 0 && parts[parts.length - 1]) {
                        readExtraPound(parts[parts.length - 1]);
                    }
                }
            }
            else if (readExtraPound != null && parts.length == 2 && parts[1]) {
                readExtraPound(parts[1]);
            }
            else {
                var foo = $WH.ge('view3D-button');
                if (foo) {
                    foo.onclick();
                }
            }
        }
    };

    this.addExtraPound = function(func) {
        readExtraPound = func;
    };

    this.show = function(opt) {
        optBak = opt;

        Lightbox.show('modelviewer', {
            onShow: onShow,
            onHide: onHide
        }, opt);
    };

    DomContentLoaded.addEvent(this.checkPound)
};

var g_screenshots = {};
var ScreenshotViewer = new function() {
    var
        screenshots,
        pos,
        imgWidth,
        imgHeight,
        scale,
        desiredScale,
        oldHash,
        mode = 0,
        collectionId,
        container,
        screen,
        imgDiv,
        aPrev,
        aNext,
        aCover,
        aOriginal,
        divFrom,
        divCaption,
        loadingImage,
        lightboxComponents;

    function computeDimensions(captionExtraHeight) {
        var screenshot = screenshots[pos];

        var availHeight = Math.max(50, Math.min(618, $WH.g_getWindowSize().h - 72 - captionExtraHeight));

        if (mode != 1 || screenshot.id || screenshot.resize) {
            desiredScale = Math.min(772 / screenshot.width, 618 / screenshot.height);
            scale        = Math.min(772 / screenshot.width, availHeight / screenshot.height);
        }
        else {
            desiredScale = scale = 1;
        }

        if (desiredScale > 1) {
            desiredScale = 1;
        }

        if (scale > 1) {
            scale = 1;
        }

        imgWidth = Math.round(scale * screenshot.width);
        imgHeight = Math.round(scale * screenshot.height);
        var lbWidth = Math.max(480, imgWidth);

        Lightbox.setSize(lbWidth + 20, imgHeight + 52 + captionExtraHeight);

        if ($WH.Browser.ie6) {
            screen.style.width = lbWidth + 'px';
            if (screenshots.length > 1) {
                aPrev.style.height = aNext.style.height = imgHeight + 'px'
            } else {
                aCover.style.height = imgHeight + 'px'
            }
        }
        if (captionExtraHeight) {
            imgDiv.firstChild.width = imgWidth;
            imgDiv.firstChild.height = imgHeight;
        }
    }

    function getPound(pos) {
        var
            screenshot = screenshots[pos],
            buff = '#screenshots:';

        if (mode == 0) {
            buff += 'id=' + screenshot.id;
        }
        else {
            buff += collectionId + ':' + (pos + 1);
        }
        return buff;
    }

    function render(resizing) {
        if (resizing && (scale == desiredScale) && $WH.g_getWindowSize().h > container.offsetHeight) {
            return;
        }
        container.style.visibility = 'hidden';
        var
            screenshot = screenshots[pos],
            resized = (screenshot.width > 772 || screenshot.height > 618);

        computeDimensions(0);

        var url = (screenshot.url ? screenshot.url: g_staticUrl + '/uploads/screenshots/' + (resized ? 'resized/': 'normal/') + screenshot.id + '.jpg');

        var html =
            '<img src="' + url + '"'
        + ' width="' + imgWidth + '"'
        + ' height="' + imgHeight + '"';
        if ($WH.Browser.ie6) {
            html += ' galleryimg="no"';
        }
        html += '>';

        imgDiv.innerHTML = html;

        if (!resizing) {
            if (screenshot.url) {
                aOriginal.href = url;
            }
            else {
                aOriginal.href = g_staticUrl + '/uploads/screenshots/normal/' + screenshot.id + '.jpg';
            }
            if (!screenshot.user && typeof g_pageInfo == 'object') {
                screenshot.user = g_pageInfo.username;
            }

            var
                hasFrom1 = (screenshot.date && screenshot.user),
                hasFrom2 = (screenshots.length > 1);

            if (hasFrom1) {
                var
                    postedOn = new Date(screenshot.date),
                    elapsed = (g_serverTime - postedOn) / 1000;
                var a = divFrom.firstChild.childNodes[1];
                a.href = '?user=' + screenshot.user;
                a.innerHTML = screenshot.user;

                var s = divFrom.firstChild.childNodes[3];
                $WH.ee(s);
                Listview.funcBox.coFormatDate(s, elapsed, postedOn);

                divFrom.firstChild.style.display = '';
            }
            else {
                divFrom.firstChild.style.display = 'none';
            }

            var s = divFrom.childNodes[1];
            $WH.ee(s);
            if (screenshot.user) {
                if (hasFrom1) {
                    $WH.ae(s, $WH.ct(' ' + LANG.dash + ' '));
                }
                var a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = ContactTool.show.bind(ContactTool, {
                    mode: 3,
                    screenshot: screenshot
                });
                a.className = 'report-icon'
                g_addTooltip(a, LANG.report_tooltip, 'q2');
                $WH.ae(a, $WH.ct(LANG.report));
                $WH.ae(s, a);
            }

            s = divFrom.childNodes[2];

            if (hasFrom2) {
                var buff = '';
                if (screenshot.user) {
                    buff = LANG.dash;
                }
                buff += (pos + 1) + LANG.lvpage_of + screenshots.length;

                s.innerHTML = buff;
                s.style.display = '';
            }
            else {
                s.style.display = 'none';
            }

            divFrom.style.display = (hasFrom1 || hasFrom2 ? '': 'none');

            var hasCaption = (screenshot.caption != null && screenshot.caption.length);
            var hasSubject = (screenshot.subject != null && screenshot.subject.length && screenshot.type && screenshot.typeId);

            if (hasCaption || hasSubject) {
                var html = '';

                if (hasSubject) {
                    html += LANG.types[screenshot.type][0] + LANG.colon;
                    html += '<a href="?' + g_types[screenshot.type] + '=' + screenshot.typeId + '">';
                    html += screenshot.subject;
                    html += '</a>';
                }

                if (hasCaption) {
                    if (hasSubject) {
                        html += LANG.dash;
                    }
                    html += (screenshot.noMarkup ? screenshot.caption : Markup.toHtml(screenshot.caption, { mode: Markup.MODE_SIGNATURE }));
                }

                divCaption.innerHTML = html;
                divCaption.style.display = '';
            }
            else {
                divCaption.style.display = 'none';
            }

            if (screenshots.length > 1) {
                aPrev.href = getPound(peekPos(-1));
                aNext.href = getPound(peekPos(1));

                aPrev.style.display = aNext.style.display = '';
                aCover.style.display = 'none';
            }
            else {
                aPrev.style.display = aNext.style.display = 'none';
                aCover.style.display = '';
            }

            location.replace(getPound(pos));
        }

        Lightbox.reveal();

        if (divCaption.offsetHeight > 18) {
            computeDimensions(divCaption.offsetHeight - 18);
        }
        container.style.visibility = 'visible';
    }

    function peekPos(change) {
        var foo = pos;
        foo += change;

        if (foo < 0) {
            foo = screenshots.length - 1;
        }
        else if (foo >= screenshots.length) {
            foo = 0;
        }

        return foo;
    }

    function prevScreenshot() {
        pos = peekPos(-1);
        onRender();

        return false;
    }

    function nextScreenshot() {
        pos = peekPos(1);
        onRender();

        return false;
    }

    function onKeyUp(e) {
        e = $WH.$E(e);
        switch (e.keyCode) {
            case 37: // Left
                prevScreenshot();
                break;
            case 39: // Right
                nextScreenshot();
                break;
        }
    }

    function onResize() {
        render(1);
    }

    function onHide() {
        cancelImageLoading();

        if (screenshots.length > 1) {
            $WH.dE(document, 'keyup', onKeyUp);
        }

        if (oldHash && mode == 0) {
            if (oldHash.indexOf(':id=') != -1) {
                oldHash = '#screenshots';
            }
            location.replace(oldHash);
        }
        else {
            location.replace('#.');
        }
    }

    function onShow(dest, first, opt) {
        if (typeof opt.screenshots == 'string') {
            screenshots = g_screenshots[opt.screenshots];
            mode = 1;
            collectionId = opt.screenshots;
        }
        else {
            screenshots = opt.screenshots;
            mode = 0;
            collectionId = null;
        }
        container = dest;

        pos = 0;
        if (opt.pos && opt.pos >= 0 && opt.pos < screenshots.length) {
            pos = opt.pos;
        }

        if (first) {
            dest.className = 'screenshotviewer';

            screen = $WH.ce('div');

            screen.className = 'screenshotviewer-screen';

            aPrev = $WH.ce('a');
            aNext = $WH.ce('a');
            aPrev.className = 'screenshotviewer-prev';
            aNext.className = 'screenshotviewer-next';
            aPrev.href = 'javascript:;';
            aNext.href = 'javascript:;';

            var foo = $WH.ce('span');
            $WH.ae(foo, $WH.ce('b'));
            // var b = $WH.ce('b');
            // $WH.ae(b, $WH.ct(LANG.previous));
            // $WH.ae(foo, b);
            $WH.ae(aPrev, foo);
            var foo = $WH.ce('span');
            $WH.ae(foo, $WH.ce('b'));
            // var b = $WH.ce('b');
            // $WH.ae(b, $WH.ct(LANG.next));
            // $WH.ae(foo, b);
            $WH.ae(aNext, foo);

            aPrev.onclick = prevScreenshot;
            aNext.onclick = nextScreenshot;

            aCover = $WH.ce('a');
            aCover.className = 'screenshotviewer-cover';
            aCover.href = 'javascript:;';
            aCover.onclick = Lightbox.hide;
            var foo = $WH.ce('span');
            $WH.ae(foo, $WH.ce('b'));
            // var b = $WH.ce('b');
            // $WH.ae(b, $WH.ct(LANG.close));
            // $WH.ae(foo, b);
            $WH.ae(aCover, foo);
            if ($WH.Browser.ie6) {
                $WH.ns(aPrev);
                $WH.ns(aNext);
                aPrev.onmouseover = aNext.onmouseover = aCover.onmouseover = function() {
                    this.firstChild.style.display = 'block';
                };
                aPrev.onmouseout = aNext.onmouseout = aCover.onmouseout = function() {
                    this.firstChild.style.display = '';
                };

            }
            $WH.ae(screen, aPrev);
            $WH.ae(screen, aNext);
            $WH.ae(screen, aCover);

            imgDiv = $WH.ce('div');
            $WH.ae(screen, imgDiv);

            $WH.ae(dest, screen);

            var aClose = $WH.ce('a');
            aClose.className = 'screenshotviewer-close';
            // aClose.className = 'dialog-x';
            aClose.href = 'javascript:;';
            aClose.onclick = Lightbox.hide;
            $WH.ae(aClose, $WH.ce('span'));
            // $WH.ae(aClose, $WH.ct(LANG.close));
            $WH.ae(dest, aClose);

            aOriginal = $WH.ce('a');
            aOriginal.className = 'screenshotviewer-original';
            // aOriginal.className = 'dialog-arrow';
            aOriginal.href = 'javascript:;';
            aOriginal.target = '_blank';
            $WH.ae(aOriginal, $WH.ce('span'));
            // $WH.ae(aOriginal, $WH.ct(LANG.original));
            $WH.ae(dest, aOriginal);

            divFrom = $WH.ce('div');
            divFrom.className = 'screenshotviewer-from';
            var sp = $WH.ce('span');
            $WH.ae(sp, $WH.ct(LANG.lvscreenshot_from));
            $WH.ae(sp, $WH.ce('a'));
            $WH.ae(sp, $WH.ct(' '));
            $WH.ae(sp, $WH.ce('span'));
            $WH.ae(divFrom, sp);
            $WH.ae(divFrom, $WH.ce('span'));
            $WH.ae(divFrom, $WH.ce('span'));
            $WH.ae(dest, divFrom);

            divCaption = $WH.ce('div');
            divCaption.className = 'screenshotviewer-caption';
            $WH.ae(dest, divCaption);
            var d = $WH.ce('div');
            d.className = 'clear';
            $WH.ae(dest, d);
        }

        oldHash = location.hash;

        if (screenshots.length > 1) {
            $WH.aE(document, 'keyup', onKeyUp);
        }

        onRender();
    }

    function onRender() {
        var screenshot = screenshots[pos];
        if (!screenshot.width || !screenshot.height) {
            if (loadingImage) {
                loadingImage.onload = null;
                loadingImage.onerror = null;
            }
            else {
                container.className = '';
                lightboxComponents = [];
                while (container.firstChild) {
                    lightboxComponents.push(container.firstChild);
                    $WH.de(container.firstChild);
                }
            }

            var lightboxTimer = setTimeout(function() {
                screenshot.width = 126;
                screenshot.height = 22;
                computeDimensions(0);
                screenshot.width = null;
                screenshot.height = null;

                var div = $WH.ce('div');
                div.style.margin = '0 auto';
                div.style.width = '126px';
                var img = $WH.ce('img');
                img.src = g_staticUrl + '/template/images/progress-anim.gif';
                img.width = 126;
                img.height = 22;
                $WH.ae(div, img);
                $WH.ae(container, div);

                Lightbox.reveal();
                container.style.visiblity = 'visible';
            }, 150);

            loadingImage = new Ima$WH.ge();
            loadingImage.onload = (function(screen, timer) {
                clearTimeout(timer);
                screen.width = this.width;
                screen.height = this.height;
                loadingImage = null;
                restoreLightbox();
                render();
            }).bind(loadingImage, screenshot, lightboxTimer);
            loadingImage.onerror = (function(timer) {
                clearTimeout(timer);
                loadingImage = null;
                Lightbox.hide();
                restoreLightbox();
            }).bind(loadingImage, lightboxTimer);
            loadingImage.src = (screenshot.url ? screenshot.url : g_staticUrl + '/uploads/screenshots/normal/' + screenshot.id + '.jpg');
        }
        else {
            render();
        }
    }

    function cancelImageLoading() {
        if (!loadingImage) {
            return;
        }

        loadingImage.onload = null;
        loadingImage.onerror = null;
        loadingImage = null;

        restoreLightbox();
    }

    function restoreLightbox() {
        if (!lightboxComponents) {
            return;
        }

        $WH.ee(container);
        container.className = 'screenshotviewer';
        for (var i = 0; i < lightboxComponents.length; ++i)
            $WH.ae(container, lightboxComponents[i]);
        lightboxComponents = null;
    }

    this.checkPound = function() {
        if (location.hash && location.hash.indexOf('#screenshots') == 0) {
            if (!g_listviews['screenshots']) { // Standalone screenshot viewer
                var parts = location.hash.split(':');
                if (parts.length == 3) {
                    var
                        collection = g_screenshots[parts[1]],
                        p = parseInt(parts[2]);

                    if (collection && p >= 1 && p <= collection.length) {
                        ScreenshotViewer.show({
                            screenshots: parts[1],
                            pos: p - 1
                        });
                    }
                }
            }
        }
    }

    this.show = function(opt) {
        Lightbox.show('screenshotviewer', {
            onShow: onShow,
            onHide: onHide,
            onResize: onResize
        }, opt);
    }

    DomContentLoaded.addEvent(this.checkPound)
};

var Dialog = function() {
var
    _self = this,
    _template,
    _onSubmit = null,
    _templateName,

    _funcs = {},
    _data,

    _inited = false,
    _form = $WH.ce('form'),
    _elements = {};

    _form.onsubmit = function() {
        _processForm();
        return false
    };

    this.show = function(template, opt) {
        if (template) {
            _templateName = template;
            _template = Dialog.templates[_templateName];
            _self.template = _template;
        }
        else {
            return;
        }

        if (_template.onInit && !_inited) {
            (_template.onInit.bind(_self, _form, opt))();
        }

        if (opt.onBeforeShow) {
            _funcs.onBeforeShow = opt.onBeforeShow.bind(_self, _form);
        }

        if (_template.onBeforeShow) {
            _template.onBeforeShow = _template.onBeforeShow.bind(_self, _form);
        }

        if (opt.onShow) {
            _funcs.onShow = opt.onShow.bind(_self, _form);
        }

        if (_template.onShow) {
            _template.onShow = _template.onShow.bind(_self, _form);
        }

        if (opt.onHide) {
            _funcs.onHide = opt.onHide.bind(_self, _form);
        }

        if (_template.onHide) {
            _template.onHide = _template.onHide.bind(_self, _form);
        }

        if (opt.onSubmit) {
            _funcs.onSubmit = opt.onSubmit;
        }

        if (_template.onSubmit)
            _onSubmit = _template.onSubmit.bind(_self, _form);

        if (opt.data) {
            _inited = false;
            _data = {};
            $WH.cO(_data, opt.data);
        }
        _self.data = _data;

        Lightbox.show('dialog-' + _templateName, {
            onShow: _onShow,
            onHide: _onHide
        });
    };

    this.getValue = function(id) {
        return _getValue(id);
    };

    this.setValue = function(id, value) {
        _setValue(id, value);
    };

    this.getSelectedValue = function(id) {
        return _getSelectedValue(id);
    };

    this.getCheckedValue = function(id) {
        return _getCheckedValue(id);
    };

    function _onShow(dest, first) {
        if (first || !_inited) {
            _initForm(dest);
        }

        if (_template.onBeforeShow) {
            _template.onBeforeShow();
        }

        if (_funcs.onBeforeShow) {
            _funcs.onBeforeShow();
        }

        Lightbox.setSize(_template.width, _template.height);
        dest.className = 'dialog';

        _updateForm();

        if (_template.onShow) {
            _template.onShow();
        }

        if (_funcs.onShow) {
            _funcs.onShow();
        }
    }

    function _initForm(dest) {
        $WH.ee(dest);
        $WH.ee(_form);

        var container = $WH.ce('div');
        container.className = 'text';
        $WH.ae(dest, container);
        $WH.ae(container, _form);
        if (_template.title) {
            var h = $WH.ce('h1');
            $WH.ae(h, $WH.ct(_template.title));
            $WH.ae(_form, h);
        }

        var
            t         = $WH.ce('table'),
            tb        = $WH.ce('tbody'),
            mergeCell = false;

        $WH.ae(t, tb);
        $WH.ae(_form, t);

        for (var i = 0, len = _template.fields.length; i < len; ++i) {
            var
                field = _template.fields[i],
                element;

            if (!mergeCell) {
                tr = $WH.ce('tr');
                th = $WH.ce('th');
                td = $WH.ce('td');
            }

            field.__tr = tr;

            if (_data[field.id] == null) {
                _data[field.id] = (field.value ? field.value: '');
            }

            var options;
            if (field.options) {
                options = [];

                if (field.optorder) {
                    $WH.cO(options, field.optorder);
                }
                else {
                    for (var j in field.options) {
                        options.push(j);
                    }
                }

                if (field.sort) {
                    options.sort(function(a, b) {
                        return field.sort * $WH.strcmp(field.options[a], field.options[b]);
                    });
                }
            }

            switch (field.type) {
                case 'caption':
                    th.colSpan = 2;
                    th.style.textAlign = 'left';
                    th.style.padding = 0;

                    if (field.compute) {
                        (field.compute.bind(_self, null, _data[field.id], _form, th, tr))();
                    }
                    else if (field.label) {
                        $WH.ae(th, $WH.ct(field.label));
                    }

                    $WH.ae(tr, th);
                    $WH.ae(tb, tr);

                    continue;
                    break;
                case 'textarea':
                    var f = element = $WH.ce('textarea');

                    f.name = field.id;

                    if (field.disabled) {
                        f.disabled = true;
                    }

                    f.rows = field.size[0];
                    f.cols = field.size[1];
                    td.colSpan = 2;

                    if (field.label) {
                        th.colSpan = 2;
                        th.style.textAlign = 'left';
                        th.style.padding = 0;
                        td.style.padding = 0;

                        $WH.ae(th, $WH.ct(field.label));
                        $WH.ae(tr, th);
                        $WH.ae(tb, tr);

                        tr = $WH.ce('tr');
                    }
                    $WH.ae(td, f);

                    break;
                case 'select':

                    var f = element = $WH.ce('select');

                    f.name = field.id;

                    if (field.size) {
                        f.size = field.size;
                    }

                    if (field.disabled) {
                        f.disabled = true;
                    }

                    if (field.multiple) {
                        f.multiple = true;
                    }

                    for (var j = 0, len2 = options.length; j < len2; ++j) {
                        var o = $WH.ce('option');

                        o.value = options[j];

                        $WH.ae(o, $WH.ct(field.options[options[j]]));
                        $WH.ae(f, o)
                    }

                    $WH.ae(td, f);

                    break;
                case 'dynamic':
                    td.colSpan = 2;
                    td.style.textAlign = 'left';
                    td.style.padding = 0;

                    if (field.compute)
                        (field.compute.bind(_self, null, _data[field.id], _form, td, tr))();

                    $WH.ae(tr, td);
                    $WH.ae(tb, tr);

                    element = td;

                    break;
                case 'checkbox':
                case 'radio':
                    var k = 0;
                    element = [];
                    for (var j = 0, len2 = options.length; j < len2; ++j) {
                        var
                            s = $WH.ce('span'),
                            f,
                            l,
                            uniqueId = 'sdfler46' + field.id + '-' + options[j];

                        if (j > 0 && !field.noInputBr) {
                            $WH.ae(td, $WH.ce('br'));
                        }
                        if ($WH.Browser.ie6 && field.type == 'radio') {
                            l = $WH.ce("<label for='' + uniqueId + '' onselectstart='return false' />");
                            f = $WH.ce("<input type='' + field.type + '' name='' + field.id + '' />");
                        }
                        else {
                            l = $WH.ce('label');
                            l.setAttribute('for', uniqueId);
                            l.onmousedown = $WH.rf;

                            f = $WH.ce('input');
                            f.setAttribute('type', field.type);
                            f.name = field.id;
                        }
                        f.value = options[j];
                        f.id = uniqueId;

                        if (field.disabled) {
                            f.disabled = true;
                        }
                        if (field.submitOnDblClick) {
                            l.ondblclick = f.ondblclick = function(e) {
                                _processForm();
                            };
                        }

                        if (field.compute) {
                            (field.compute.bind(_self, f, _data[field.id], _form, td, tr))();
                        }

                        $WH.ae(l, f);
                        $WH.ae(l, $WH.ct(field.options[options[j]]));
                        $WH.ae(td, l);
                        element.push(f);
                    }
                    break;
                default: // Textbox
                    var f = element = $WH.ce('input');
                    f.name = field.id;

                    if (field.size) {
                        f.size = field.size;
                    }

                    if (field.disabled) {
                        f.disabled = true ;
                    }

                    if (field.submitOnEnter) {
                        f.onkeypress = function(e) {
                            e = $WH.$E(e);
                            if (e.keyCode == 13) {
                                _processForm();
                            }
                        }
                    }
                    f.setAttribute('type', field.type);
                    $WH.ae(td, f);
                    break;
            }

            if (field.label) {
                if (field.type == 'textarea') {
                    if (field.labelAlign) {
                        td.style.textAlign = field.labelAlign;
                    }
                    td.colSpan = 2;
                }
                else {
                    if (field.labelAlign) {
                        th.style.textAlign = field.labelAlign;
                    }
                    $WH.ae(th, $WH.ct(field.label));
                    $WH.ae(tr, th);
                }
            }

            if (field.type != 'checkbox' && field.type != 'radio') {
                if (field.width) {
                    f.style.width = field.width;
                }

                if (field.compute  && field.type != 'caption' && field.type != 'dynamic') {
                    (field.compute.bind(_self, f, _data[field.id], _form, td, tr))();
                }
            }

            if (field.caption) {
                var s = $WH.ce('small');
                if (field.type != 'textarea')
                    s.style.paddingLeft = '2px';
                s.className = 'q0'; // commented in 5.0?
                $WH.ae(s, $WH.ct(field.caption));
                $WH.ae(td, s);
            }

            $WH.ae(tr, td);
            $WH.ae(tb, tr);

            mergeCell = field.mergeCell;
            _elements[field.id] = element;
        }

        for (var i = _template.buttons.length; i > 0; --i) {
            var
                button = _template.buttons[i - 1],
                a      = $WH.ce('a');

            a.href = 'javascript:;';
            a.onclick = _processForm.bind(a, button[0]);
            a.className = 'dialog-' + button[0];
            $WH.ae(a, $WH.ct(button[1]));
            $WH.ae(dest, a);
        }

        var _ = $WH.ce('div');
        _.className = 'clear';
        $WH.ae(dest, _);

        _inited = true;
    }

    function _updateForm() {
        for (var i = 0, len = _template.fields.length; i < len; ++i) {
            var
                field = _template.fields[i],
                f     = _elements[field.id];

            switch (field.type) {
                case 'caption': // Do nothing
                    break;
                case 'select':
                    for (var j = 0, len2 = f.options.length; j < len2; j++) {
                        f.options[j].selected = (f.options[j].value == _data[field.id] || $WH.in_array(_data[field.id], f.options[j].value) != -1);
                    }
                    break;
                case 'checkbox':
                case 'radio':
                    for (var j = 0, len2 = f.length; j < len2; j++) {
                        f[j].checked = (f[j].value == _data[field.id] || $WH.in_array(_data[field.id], f[j].value) != -1);
                    }
                    break;

                default:
                    f.value = _data[field.id];
                    break;
            }

            if (field.update) {
                (field.update.bind(_self, null, _data[field.id], _form, f))();
            }
        }
    }

    function _onHide() {
        if (_template.onHide) {
            _template.onHide();
        }
        if (_funcs.onHide) {
            _funcs.onHide();
        }
    }

    function _processForm(button) {
        // if (button == 'x') { // Special case
        if (button == 'cancel') { // Special case
            return Lightbox.hide();
        }

        for (var i = 0, len = _template.fields.length; i < len; ++i) {
            var
                field = _template.fields[i],
                newValue;

            switch (field.type) {
                case 'caption': // Do nothing
                    continue;
                case 'select':
                    newValue = _getSelectedValue(field.id);
                    break;
                case 'checkbox':
                case 'radio':
                    newValue = _getCheckedValue(field.id);
                    break;
                case 'dynamic':
                    if (field.getValue) {
                        newValue = field.getValue(field, _data, _form);
                        break;
                    }
                default:
                    newValue = _getValue(field.id);
                    break;
            }
            if (field.validate) {
                if (!field.validate(newValue, _data, _form)) {
                    return;
                }
            }

            if (newValue && typeof newValue == 'string') {
                newValue = $WH.trim(newValue);
            }
            _data[field.id] = newValue;
        }

        _submitData(button);
    }

    function _submitData(button) {
        var ret;

        if (_onSubmit) {
            ret = _onSubmit(_data, button, _form);
        }

        if (_funcs.onSubmit) {
            ret = _funcs.onSubmit(_data, button, _form);
        }

        if (ret === undefined || ret)
            Lightbox.hide();

            return false;
    }

    function _getValue(id) {
        return _elements[id].value;
    }

    function _setValue(id, value) {
        _elements[id].value = value;
    }

    function _getSelectedValue(id) {
        var
            result = [],
            f = _elements[id];

            for (var i = 0, len = f.options.length; i < len; i++) {
            if (f.options[i].selected) {
                result.push(parseInt(f.options[i].value) == f.options[i].value ? parseInt(f.options[i].value) : f.options[i].value);
            }
        }
        if (result.length == 1) {
            result = result[0];
        }
        return result;
    }

    function _getCheckedValue(id) {
        var
            result = [],
            f      = _elements[id];

        for (var i = 0, len = f.length; i < len; i++) {
            if (f[i].checked) {
                result.push(parseInt(f[i].value) == f[i].value ? parseInt(f[i].value) : f[i].value);
            }
        }

        return result;
    }
};
Dialog.templates = {};

var ContactTool = new function() {
    this.general    = 0;
    this.comment    = 1;
    this.post       = 2;
    this.screenshot = 3;
    this.character  = 4;
    this.video      = 5;
    this.guide      = 6;

    var _dialog;

    var contexts = {
        0: [ // general
            [1, true], // General feedback
            [2, true], // Bug report
            [8, true], // Article misinformation
            [3, true], // Typo/mistranslation
            [4, true], // Advertise with us
            [5, true], // Partnership opportunities
            [6, true], // Press inquiry
            [7, true]  // Other
        ],
        1: [ // comment
            [15, function(post) {
                return ((post.roles & U_GROUP_MODERATOR) == 0);
            }], // Advertising
            [16, true], // Inaccurate
            [17, true], // Out of date
            [18, function(post) {
                return ((post.roles & U_GROUP_MODERATOR) == 0);
            }], // Spam
            [19, function(post) {
                return ((post.roles & U_GROUP_MODERATOR) == 0);
            }], // Vulgar/inappropriate
            [20, function(post) {
                return ((post.roles & U_GROUP_MODERATOR) == 0);
            }] // Other
        ],
        2: [ // forum post
            [30, function(post) {
                return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0);
            }], // Advertising
            [37, function(post) {
                return ((post.roles & U_GROUP_MODERATOR) == 0 && g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0 && g_users[post.user].avatar == 2);
            }], // Avatar
            [31, true], // Inaccurate
            [32, true], // Out of date
            [33, function(post) {
                return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0);
            }], // Spam
            [34, function(post) {
                return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0 && post.op && !post.sticky);
            }], // Sticky request
            [35, function(post) {
                return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0);
            }], // Vulgar/inappropriate
            [36, function(post) {
                return (g_users && g_users[post.user] && (g_users[post.user].roles & U_GROUP_MODERATOR) == 0);
            }]  // Other
        ],
        3: [ // screenshot
            [45, true], // Inaccurate,
            [46, true], // Out of date,
            [47, function(screen) {
                return (g_users && g_users[screen.user] && (g_users[screen.user].roles & U_GROUP_MODERATOR) == 0);
            }], // Vulgar/inappropriate
            [48, function(screen) {
                return (g_users && g_users[screen.user] && (g_users[screen.user].roles & U_GROUP_MODERATOR) == 0);
            }]  // Other
        ],
        4: [ // character
            [60, true], // Inaccurate completion data
            [61, true]  // Other
        ],
        5: [ // video
            [45, true], // Inaccurate,
            [46, true], // Out of date,
            [47, function(video) {
                return (g_users && g_users[video.user] && (g_users[video.user].roles & U_GROUP_MODERATOR) == 0);
            }], // Vulgar/inappropriate
            [48, function(video) {
                return (g_users && g_users[video.user] && (g_users[video.user].roles & U_GROUP_MODERATOR) == 0);
            }]  // Other
        ],
        6: [ // Guide
            [45, true], // Inaccurate,
            [46, true], // Out of date,
            [48, true]  // Other
        ]
    };

    var errors = {
        1 : LANG.ct_resp_error1,
        2 : LANG.ct_resp_error2,
        3 : LANG.ct_resp_error3,
        7 : LANG.ct_resp_error7
    };

    var oldHash = null;

    this.displayError = function(field, message) {
        alert(message);
    };

    this.onShow = function() {
        if (location.hash && location.hash != '#contact') {
            oldHash = location.hash;
        }
        if (this.data.mode == 0) {
            location.replace('#contact');
        }
    };

    this.onHide = function() {
        if (oldHash && (oldHash.indexOf('screenshots:') == -1 || oldHash.indexOf('videos:') == -1)) {
            location.replace(oldHash);
        }
        else {
            location.replace('#.');
        }
    };

    this.onSubmit = function(data, button, form) {
        if (data.submitting) {
            return false;
        }

        for (var i = 0; i < form.elements.length; ++i) {
            form.elements[i].disabled = true;
        }

        var params = [
            'contact=1',
            'mode=' + $WH.urlencode(data.mode),
            'reason=' + $WH.urlencode(data.reason),
            'desc=' + $WH.urlencode(data.description),
            'ua=' + $WH.urlencode(navigator.userAgent),
            'appname=' + $WH.urlencode(navigator.appName),
            'page=' + $WH.urlencode(data.currenturl)
        ];

        if (data.mode == 0) { // contact us
            if (data.relatedurl) {
                params.push('relatedurl=' + $WH.urlencode(data.relatedurl));
            }
            if (data.email) {
                params.push('email=' + $WH.urlencode(data.email));
            }
        }
        else if (data.mode == 1) { // comment
            params.push('id=' + $WH.urlencode(data.comment.id));
        }
        else if (data.mode == 2) { // forum post
            params.push('id=' + $WH.urlencode(data.post.id));
        }
        else if (data.mode == 3) { // screenshot
            params.push('id=' + $WH.urlencode(data.screenshot.id));
        }
        else if (data.mode == 4) { // character
            params.push('id=' + $WH.urlencode(data.profile.source));
        }
        else if (data.mode == 5) { // video
            params.push('id=' + $WH.urlencode(data.video.id));
        }
        else if (data.mode == 6) { // guide
            params.push('id=' + $WH.urlencode(data.guide.id));
        }

        data.submitting = true;
        var url = '?contactus';
        new Ajax(url, {
            method: 'POST',
            params: params.join('&'),
            onSuccess: function(xhr, opt) {
                var resp = xhr.responseText;
                if (resp == 0) {
                    if (g_user.name) {
                        alert($WH.sprintf(LANG.ct_dialog_thanks_user, g_user.name));
                    }
                    else {
                        alert(LANG.ct_dialog_thanks);
                    }
                    Lightbox.hide();
                }
                else {
                    if (errors[resp]) {
                        alert(errors[resp]);
                    }
                    else {
                        alert('Error: ' + resp);
                    }
                }
            },
            onFailure: function(xhr, opt) {
                alert('Failure submitting contact request: ' + xhr.statusText);
            },
            onComplete: function(xhr, opt) {
                for (var i = 0; i < form.elements.length; ++i) {
                    form.elements[i].disabled = false;
                }
                data.submitting = false;
            }
        });
        return false;
    };

    this.show = function(opt) {
        if (!opt) {
            opt = {};
        }
        var data = { mode: 0 };
        $WH.cO(data, opt);
        data.reasons = contexts[data.mode];
        if (location.href.indexOf('#contact') != -1) {
            data.currenturl = location.href.substr(0, location.href.indexOf('#contact'));
        }
        else {
            data.currenturl = location.href;
        }
        var form = 'contactus';
        if (data.mode != 0) {
            form = 'reportform';
        }

        if (!_dialog) {
            this.init();
        }

        _dialog.show(form, {
            data: data,
            onShow: this.onShow,
            onHide: this.onHide,
            onSubmit: this.onSubmit
        })
    };

    this.checkPound = function() {
        if (location.hash && location.hash == '#contact') {
            ContactTool.show();
        }
    };

    var dialog_contacttitle = LANG.ct_dialog_contactwowhead;

    this.init = function() {
        _dialog = new Dialog();

        Dialog.templates.contactus = {
            title: dialog_contacttitle,
            width: 550,
            buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],

            fields: [
                {
                    id: 'reason',
                    type: 'select',
                    label: LANG.ct_dialog_reason,
                    required: 1,
                    options: [],
                    compute: function(field, value, form, td) {
                        $WH.ee(field);

                        for (var i = 0; i < this.data.reasons.length; ++i) {
                            var id = this.data.reasons[i][0];
                            var check = this.data.reasons[i][1];
                            var valid = false;
                            if (typeof check == 'function') {
                                valid = check(this.extra);
                            }
                            else {
                                valid = check;
                            }

                            if (!valid) {
                                continue;
                            }

                            var o = $WH.ce('option');
                            o.value = id;
                            if (value && value == id) {
                                o.selected = true;
                            }
                            $WH.ae(o, $WH.ct(g_contact_reasons[id]));
                            $WH.ae(field, o);
                        }

                        field.onchange = function() {
                            if (this.value == 1 || this.value == 2 || this.value == 3) {
                                form.currenturl.parentNode.parentNode.style.display = '';
                                form.relatedurl.parentNode.parentNode.style.display = '';
                            }
                            else {
                                form.currenturl.parentNode.parentNode.style.display = 'none';
                                form.relatedurl.parentNode.parentNode.style.display = 'none';
                            }
                        }.bind(field);

                        td.style.width = '98%';
                    },
                    validate: function(newValue, data, form) {
                        var error = '';
                        if (!newValue || newValue.length == 0) {
                            error = LANG.ct_dialog_error_reason;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.reason, error);
                        form.reason.focus();
                        return false;
                    }
                },
                {
                    id: 'currenturl',
                    type: 'text',
                    disabled: true,
                    label: LANG.ct_dialog_currenturl,
                    size: 40
                },
                {
                    id: 'relatedurl',
                    type: 'text',
                    label: LANG.ct_dialog_relatedurl,
                    caption: LANG.ct_dialog_optional,
                    size: 40,
                    validate: function(newValue, data, form) {
                        var error = '';
                        var urlRe = /^(http(s?)\:\/\/|\/)?([\w]+:\w+@)?([a-zA-Z]{1}([\w\-]+\.)+([\w]{2,5}))(:[\d]{1,5})?((\/?\w+\/)+|\/?)(\w+\.[\w]{3,4})?((\?\w+=\w+)?(&\w+=\w+)*)?/;
                        newValue = newValue.trim();
                        if (newValue.length >= 250) {
                            error = LANG.ct_dialog_error_relatedurl;
                        }
                        else if (newValue.length > 0 && !urlRe.test(newValue)) {
                            error = LANG.ct_dialog_error_invalidurl;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.relatedurl, error);
                        form.relatedurl.focus();
                        return false;
                    }
                },
                {
                    id: 'email',
                    type: 'text',
                    label: LANG.ct_dialog_email,
                    caption: LANG.ct_dialog_email_caption,
                    compute: function(field, value, form, td, tr) {
                        if (g_user.email) {
                            this.data.email = g_user.email;
                            tr.style.display = 'none';
                        }
                        else {
                            var func = function() {
                                $WH.ge('contact-emailwarn').style.display = g_isEmailValid($WH.ge(form.email).value) ? 'none' : '';
                                Lightbox.reveal();
                            };

                            $WH.ge(field).onkeyup = func;
                            $WH.ge(field).onblur = func;
                        }
                    },
                    validate: function(newValue, data, form) {
                        var error = '';
                        newValue = newValue.trim();
                        if (newValue.length >= 100) {
                            error = LANG.ct_dialog_error_emaillen;
                        }
                        else if (newValue.length > 0 && !g_isEmailValid(newValue)) {
                            error = LANG.ct_dialog_error_email;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.email, error);
                        form.email.focus();
                        return false;
                    }
                },
                {
                    id: 'description',
                    type: 'textarea',
                    caption: LANG.ct_dialog_desc_caption,
                    width: '98%',
                    required: 1,
                    size: [10, 30],
                    validate: function(newValue, data, form) {
                        var error = '';
                        newValue = newValue.trim();
                        if (newValue.length == 0 || newValue.length > 10000) {
                            error = LANG.ct_dialog_error_desc;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.description, error);
                        form.description.focus();
                        return false;
                    }
                },
                {
                    id: 'noemailwarning',
                    type: 'caption',
                    compute: function(field, value, form, td) {
                        var td = $WH.ge(td);
                        td.innerHTML = '<span id="contact-emailwarn" class="q10"' + (g_user.email ? ' style="display: none"' : '') + '>' + LANG.ct_dialog_noemailwarning + '</span>';
                        td.style.whiteSpace = 'normal';
                        td.style.padding = '0 4px';
                    }
                }
            ],

            onInit: function(form) { },

            onShow: function(form) {
                if (this.data.focus && form[this.data.focus]) {
                    setTimeout(g_setCaretPosition.bind(null, form[this.data.focus], form[this.data.focus].value.length), 100);
                }
                else if (form['reason'] && !form.reason.value) {
                    setTimeout($WH.bindfunc(form.reason.focus, form.reason), 10);
                }
                else if (form['relatedurl'] && !form.relatedurl.value) {
                    setTimeout($WH.bindfunc(form.relatedurl.focus, form.relatedurl), 10);
                }
                else if (form['email'] && !form.email.value) {
                    setTimeout($WH.bindfunc(form.email.focus, form.email), 10);
                }
                else if (form['description'] && !form.description.value) {
                    setTimeout($WH.bindfunc(form.description.focus, form.description), 10);
                }

                setTimeout(Lightbox.reveal, 250);
            }
        };

        Dialog.templates.reportform = {
            title: LANG.ct_dialog_report,
            width: 550,
            // height: 360,
            buttons: [['okay', LANG.ok], ['cancel', LANG.cancel]],
            fields: [
                {
                    id: 'reason',
                    type: 'select',
                    label: LANG.ct_dialog_reason,
                    options: [],
                    compute: function(field, value, form, td) {
                        switch (this.data.mode) {
                            case 1: // comment
                                form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reportcomment, '<a href="?user=' + this.data.comment.user + '">' + this.data.comment.user + '</a>');
                                break;
                            case 2: // forum post
                                var rep = '<a href="?user=' + this.data.post.user + '">' + this.data.post.user + '</a>';
                                if (this.data.post.op) {
                                    form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reporttopic, rep);
                                }
                                else {
                                    form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reportpost, rep);
                                }
                                break;
                            case 3: // screenshot
                                form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reportscreen, '<a href="?user=' + this.data.screenshot.user + '">' + this.data.screenshot.user + '</a>');
                                break;
                            case 4: // character
                                $WH.ee(form.firstChild);
                                $WH.ae(form.firstChild, $WH.ct(LANG.ct_dialog_reportchar));
                                break;
                            case 5: // video
                                form.firstChild.innerHTML = $WH.sprintf(LANG.ct_dialog_reportvideo, '<a href="?user=' + this.data.video.user + '">' + this.data.video.user + '</a>');
                                break;
                            case 6: // guide
                                form.firstChild.innerHTML = 'Report guide';
                                break;
                        }
                        form.firstChild.setAttribute('style', '');

                        $WH.ee(field);

                        var extra;
                        if (this.data.mode == 1) {
                            extra = this.data.comment;
                        }
                        else if (this.data.mode == 2) {
                            extra = this.data.post;
                        }
                        else if (this.data.mode == 3) {
                            extra = this.data.screenshot;
                        }
                        else if (this.data.mode == 4) {
                            extra = this.data.profile;
                        }
                        else if (this.data.mode == 5) {
                            extra = this.data.video;
                        }
                        else if (this.data.mode == 6) {
                            extra = this.data.guide;
                        }

                        $WH.ae(field, $WH.ce('option', { selected: (!value), value: -1 }));

                        for (var i = 0; i < this.data.reasons.length; ++i) {
                            var id = this.data.reasons[i][0];
                            var check = this.data.reasons[i][1];
                            var valid = false;
                            if (typeof check == 'function') {
                                valid = check(extra);
                            }
                            else {
                                valid = check;
                            }

                            if (!valid) {
                                continue;
                            }

                            var o = $WH.ce('option');
                            o.value = id;
                            if (value && value == id) {
                                o.selected = true;
                            }

                            $WH.ae(o, $WH.ct(g_contact_reasons[id]));
                            $WH.ae(field, o);
                        }

                        td.style.width = '98%';
                    },
                    validate: function(newValue, data, form) {
                        var error = '';
                        if (!newValue || newValue == -1 || newValue.length == 0) {
                            error = LANG.ct_dialog_error_reason;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.reason, error);
                        form.reason.focus();
                        return false;
                    }
                },
                {
                    id: 'description',
                    type: 'textarea',
                    caption: LANG.ct_dialog_desc_caption,
                    width: '98%',
                    required: 1,
                    size: [10, 30],
                    validate: function(newValue, data, form) {
                        var error = '';
                        newValue = newValue.trim();
                        if (newValue.length == 0 || newValue.length > 10000) {
                            error = LANG.ct_dialog_error_desc;
                        }

                        if (error == '') {
                            return true;
                        }

                        ContactTool.displayError(form.description, error);
                        form.description.focus();
                        return false;
                    }
                }
            ],

            onInit: function(form) {},

            onShow: function(form) {
                /* Work-around for IE7 */
                var reason = $WH.gE(form, 'select')[0];
                var description = $WH.gE(form, 'textarea')[0];

                if (this.data.focus && form[this.data.focus]) {
                    setTimeout(g_setCaretPosition.bind(null, form[this.data.focus], form[this.data.focus].value.length), 100);
                }
                else if (!reason.value) {
                    setTimeout($WH.bindfunc(reason.focus, reason), 10);
                }
                else if (!description.value) {
                    setTimeout($WH.bindfunc(description.focus, description), 10);
                }
            }
        }
    };

    DomContentLoaded.addEvent(this.checkPound);
};

function Line(x1, y1, x2, y2, type) {
	var left   = Math.min(x1, x2),
		right  = Math.max(x1, x2),
		top    = Math.min(y1, y2),
		bottom = Math.max(y1, y2),

		width  = (right - left),
		height = (bottom - top),
		length = Math.sqrt(Math.pow(width, 2) + Math.pow(height, 2)),

		radian   = Math.atan2(height, width),
		sinTheta = Math.sin(radian),
		cosTheta = Math.cos(radian);

	var $line = $WH.ce('span');
    $line.className = 'line';
    $line.style.top    = top.toFixed(2) + 'px';
	$line.style.left   = left.toFixed(2) + 'px';
	$line.style.width  = width.toFixed(2) + 'px';
	$line.style.height = height.toFixed(2) + 'px';

    var v = $WH.ce('var');
	v.style.width = length.toFixed(2) + 'px';
    v.style.OTransform = 'rotate(' + radian + 'rad)';
    v.style.MozTransform = 'rotate(' + radian + 'rad)';
    v.style.webkitTransform = 'rotate(' + radian + 'rad)';
    v.style.filter = "progid:DXImageTransform.Microsoft.Matrix(sizingMethod='auto expand', M11=" + cosTheta + ', M12=' + (-1 * sinTheta) + ', M21=' + sinTheta + ', M22=' + cosTheta + ')';
    $WH.ae($line, v);

	if (!(x1 == left && y1 == top) && !(x2 == left && y2 == top)) {
		$line.className += ' flipped';
	}

	if (type != null) {
		$line.className += ' line-' + type;
	}

	return $line;
}

var Links = new function() {
    var dialog  = null;
    var oldHash = null;

    var validArmoryTypes = {
        item: 1
    };

    this.onShow = function() {
        if (location.hash && location.hash != '#links') {
            oldHash = location.hash;
        }

        location.replace('#links');
    }

    this.onHide = function() {
        if (oldHash && (oldHash.indexOf('screenshots:') == -1 || oldHash.indexOf('videos:') == -1)) {
            location.replace(oldHash);
        }
        else {
            location.replace('#.');
        }
    }

    this.show = function(opt) {
        if (!opt || !opt.type || !opt.typeId) {
            return;
        }

        var type = g_types[opt.type];

        if (!dialog) {
            this.init();
        }

        if (validArmoryTypes[type] && Dialog.templates.links.fields[1].id != 'armoryurl') {
            Dialog.templates.links.fields.splice(1, 0, {
                id: 'armoryurl',
                type: 'text',
                label: 'Armory URL',
                size: 40
            });
        }

        var link = '';
        if (opt.linkColor && opt.linkId && opt.linkName) {
            link = g_getIngameLink(opt.linkColor, opt.linkId, opt.linkName);

            if (Dialog.templates.links.fields[Dialog.templates.links.fields.length - 2].id != 'ingamelink') {
                Dialog.templates.links.fields.splice(Dialog.templates.links.fields.length - 1, 0, {
                    id: 'ingamelink',
                    type: 'text',
                    label: 'Ingame Link',
                    size: 40
                });
            }
        }

        var data = {
            'wowheadurl': g_staticUrl +'?' + type + '=' + opt.typeId,
            'armoryurl': 'http://us.battle.net/wow/en/' + type + '/' + opt.typeId,
            'ingamelink': link,
            'markuptag': '[' + type + '=' + opt.typeId + ']'
        };

        dialog.show('links', {
            data: data,
            onShow: this.onShow,
            onHide: this.onHide,
            onSubmit: function() {
                return false;
            }
        });
    }

    this.checkPound = function() {
        if (location.hash && location.hash == '#links') {
            $WH.ge('open-links-button').click();
        }
    }

    this.init = function() {
        dialog = new Dialog();

        Dialog.templates.links = {
            title: LANG.pr_menu_links || 'Links',
            width: 425,
            buttons: [['cancel', LANG.close]],

            fields:
                [
                    {
                        id: 'wowheadurl',
                        type: 'text',
                        label: 'Aowow URL',
                        size: 40
                    },
                    {
                        id: 'markuptag',
                        type: 'text',
                        label: 'Markup Tag',
                        size: 40
                    }
                ],

            onInit: function(form) {

            },

            onShow: function(form) {
                setTimeout(function() {
                    document.getElementsByName('ingamelink')[0].select();
                }, 50);
                setTimeout(Lightbox.reveal, 100);
            }
        };
    };

    DomContentLoaded.addEvent(this.checkPound)
};

var Announcement = function(opt) {
    if (!opt) {
        opt = {};
    }

    $WH.cO(this, opt);

    if (this.parent) {
        this.parentDiv = $WH.ge(this.parent);
    }
    else {
        return;
    }

    if (g_user.id == 0 && $WH.gc('announcement-' + this.id) == 'closed') {
        return;
    }

    this.initialize();
};

Announcement.prototype = {
    initialize: function() {
        // this.parentDiv.style.display = 'none';
        this.parentDiv.style.opacity = '0';
        // this.parentDiv.style.height = '0px';
        /* replaced with..*/

        if (this.mode === undefined || this.mode == 1)
            this.parentDiv.className = 'announcement announcement-contenttop';
        else
            this.parentDiv.className = 'announcement announcement-pagetop';

        var div = this.innerDiv = $WH.ce('div');
        div.className = 'announcement-inner text';
        this.setStyle(this.style);

        var a = null;
        var id = parseInt(this.id);

        if (g_user && (g_user.roles & (U_GROUP_ADMIN|U_GROUP_BUREAU)) > 0 && Math.abs(id) > 0) {
            if (id < 0) {
                a = $WH.ce('a');
                a.style.cssFloat = a.style.styleFloat = 'right';
                a.href = '?admin=announcements&id=' + Math.abs(id) + '&status=2';
                a.onclick = function() {
                    return confirm('Are you sure you want to delete ' + this.name + '?');
                };
                $WH.ae(a, $WH.ct('Delete'));
                var small = $WH.ce('small');
                $WH.ae(small, a);
                $WH.ae(div, small);

                a = $WH.ce('a');
                a.style.cssFloat = a.style.styleFloat = 'right';
                a.style.marginRight = '10px';
                a.href = '?admin=announcements&id=' + Math.abs(id) + '&status=' + (this.status == 1 ? 0 : 1);
                a.onclick = function() {
                    return confirm('Are you sure you want to delete ' + this.name + '?');
                };
                $WH.ae(a, $WH.ct((this.status == 1 ? 'Disable' : 'Enable')));
                var small = $WH.ce('small');
                $WH.ae(small, a);
                $WH.ae(div, small);
            }

            a = $WH.ce('a');
            a.style.cssFloat = a.style.styleFloat = 'right';
            a.style.marginRight = '22px';
            a.href = '?admin=announcements&id=' + Math.abs(id) + '&edit';
            $WH.ae(a, $WH.ct('Edit announcement'));
            var small = $WH.ce('small');
            $WH.ae(small, a);
            $WH.ae(div, small);
        }

        var markupDiv = $WH.ce('div');
        markupDiv.id = this.parent + '-markup';
        $WH.ae(div, markupDiv);

        if (id >= 0) {
            a = $WH.ce('a');

            a.id = 'closeannouncement';
            a.href = 'javascript:;';
            a.className = 'announcement-close';
            if (this.nocookie) {
                a.onclick = this.hide.bind(this);
            }
            else {
                a.onclick = this.markRead.bind(this);
            }
            $WH.ae(div, a);
            g_addTooltip(a, LANG.close);
        }

        $WH.ae(div, $WH.ce('div', { style: { clear: 'both' } }));

        $WH.ae(this.parentDiv, div);

        this.setText(this.text);

        setTimeout(this.show.bind(this), 500); // Delay to avoid visual lag
    },

    show: function() {
        // $(this.parentDiv).animate({
            // opacity: 'show',
            // height: 'show'
        // },{
            // duration: 333
        // });
        // this.parentDiv.style.display = 'block';

        // todo: iron out the quirks
        this.parentDiv.style.opacity = '100';
        this.parentDiv.style.height = (this.parentDiv.offsetHeight + 10) + 'px'; // + margin-bottom of child
    },

    hide: function() {
        // $(this.parentDiv).animate({
            // opacity: 'hide',
            // height: 'hide'
        // },{
            // duration: 200
        // });
        // this.parentDiv.style.display = 'none';

        // todo: iron out the quirks
        this.parentDiv.style.opacity = '0';
        this.parentDiv.style.height = '0px';
        setTimeout(function() {
            this.parentDiv.style.display = 'none';
        }.bind(this), 400);
    },

    markRead: function() {
        // g_setWowheadCookie('announcement-' + this.id, 'closed');
        $WH.sc('announcement-' + this.id, 20, 'closed', "/", location.hostname);
        this.hide();
    },

    setStyle: function(style) {
        this.style = style;
        this.innerDiv.setAttribute('style', style);
    },

    setText: function(text) {
        this.text = text;
        // Markup.printHtml(this.text, this.parent + '-markup');
        $WH.ge(this.parent + '-markup').innerHTML = this.text;
    }
};


/*
Global WoW data
*/

var g_file_races = {
     1: 'human',
     2: 'orc',
     3: 'dwarf',
     4: 'nightelf',
     5: 'scourge',
     6: 'tauren',
     7: 'gnome',
     8: 'troll',
    10: 'bloodelf',
    11: 'draenei'
};

var g_file_classes = {
     1: 'warrior',
     2: 'paladin',
     3: 'hunter',
     4: 'rogue',
     5: 'priest',
     6: 'deathknight',
     7: 'shaman',
     8: 'mage',
     9: 'warlock',
    11: 'druid'
};

var g_file_genders = {
    0: 'male',
    1: 'female'
};

var g_file_factions = {
    1: 'alliance',
    2: 'horde'
};

var g_file_gems = {
     1: 'meta',
     2: 'red',
     4: 'yellow',
     6: 'orange',
     8: 'blue',
    10: 'purple',
    12: 'green',
    14: 'prismatic'
};

/*
Source:
http://www.wowwiki.com/Patches
http://www.wowwiki.com/Patches/1.x
*/

function g_getPatchVersionIndex(timestamp) {
    var _ = g_getPatchVersion;
    var l = 0, u = _.T.length - 2, m;

    while (u > l) {
        m = Math.floor((u + l) / 2);

        if (timestamp >= _.T[m] && timestamp < _.T[m + 1]) {
            return m;
        }

        if (timestamp >= _.T[m]) {
            l = m + 1;
        }
        else {
            u = m - 1;
        }
    }
    m = Math.ceil((u + l) / 2);

    return m;
}

function g_getPatchVersion(timestamp) {
    var m = g_getPatchVersionIndex(timestamp);
    return g_getPatchVersion.V[m];
}
g_getPatchVersion.V = [
    '1.12.0',
    '1.12.1',
    '1.12.2',

    '2.0.1',
    '2.0.3',
    '2.0.4',
    '2.0.5',
    '2.0.6',
    '2.0.7',
    '2.0.8',
    '2.0.10',
    '2.0.12',

    '2.1.0',
    '2.1.1',
    '2.1.2',
    '2.1.3',

    '2.2.0',
    '2.2.2',
    '2.2.3',

    '2.3.0',
    '2.3.2',
    '2.3.3',

    '2.4.0',
    '2.4.1',
    '2.4.2',
    '2.4.3',

    '3.0.2',
    '3.0.3',
    '3.0.8',
    '3.0.9',

    '3.1.0',
    '3.1.1',
    '3.1.2',
    '3.1.3',

    '3.2.0',
    '3.2.2',

    '3.3.0',
    '3.3.2',
    '3.3.3',
    '3.3.5',

    '?????'
];

g_getPatchVersion.T = [
    // 1.12: Drums of War
    1153540800000, // 1.12.0    22 August 2006
    1159243200000, // 1.12.1    26 September 2006
    1160712000000, // 1.12.2    13 October 2006

    // 2.0: Before the Storm (The Burning Crusade)
    1165294800000, // 2.0.1     5 December 2006
    1168318800000, // 2.0.3     9 January 2007
    1168578000000, // 2.0.4     12 January 2007
    1168750800000, // 2.0.5     14 January 2007
    1169528400000, // 2.0.6     23 January 2007
    1171342800000, // 2.0.7     13 February 2007
    1171602000000, // 2.0.8     16 February 2007
    1173157200000, // 2.0.10    6 March 2007
    1175572800000, // 2.0.12    3 April 2007

    // 2.1: The Black Temple
    1179806400000, // 2.1.0     22 May 2007
    1181016000000, // 2.1.1     5 June 2007
    1182225600000, // 2.1.2     19 June 2007
    1184040000000, // 2.1.3     10 July 2007

    // 2.2: Voice Chat!
    1190692800000, // 2.2.0     25 September 2007
    1191297600000, // 2.2.2     2 October 2007
    1191902400000, // 2.2.3     9 October 2007

    // 2.3: The Gods of Zul'Aman
    1194930000000, // 2.3.0     13 November 2007
    1199768400000, // 2.3.2     08 January 2008
    1200978000000, // 2.3.3     22 January 2008

    // 2.4: Fury of the Sunwell
    1206417600000, // 2.4.0     25 March 2008
    1207022400000, // 2.4.1     1 April 2008
    1210651200000, // 2.4.2     13 May 2008
    1216094400000, // 2.4.3     15 July 2008

    // 3.0: Echoes of Doom
    1223956800000, // 3.0.2     October 14 2008
    1225774800000, // 3.0.3     November 4 2008
    1232427600000, // 3.0.8     January 20 2009
    1234242000000, // 3.0.9     February 10 2009

    // 3.1: Secrets of Ulduar
    1239681600000, // 3.1.0     April 14 2009
    1240286400000, // 3.1.1     April 21 2009
    1242705600000, // 3.1.2     19 May 2009
    1243915200000, // 3.1.3     2 June 2009

    // 3.2: Call of the Crusader
    1249358400000, // 3.2.0     4 August 2009
    1253595600000, // 3.2.2     22 September 2009

    // 3.3: Fall of the Lich King
    1260266400000, // 3.3.0     8 December 2009
    1265104800000, // 3.3.2     2 February 2010
    1269320400000, // 3.3.3     23 March 2010
    1277182800000, // 3.3.5     22 June 2010

    9999999999999
];

/*
Global stuff related to WoW database entries
*/

var
    g_npcs               = {},
    g_objects            = {},
    g_items              = {},
    g_itemsets           = {},
    g_quests             = {},
    g_spells             = {},
    g_gatheredzones      = {},
    g_factions           = {},
    g_pets               = {},
    g_achievements       = {},
    g_titles             = {},
    g_holidays           = {},
    g_classes            = {},
    g_races              = {},
    g_skills             = {},
    g_gatheredcurrencies = {};

var g_types = {
     1: 'npc',
     2: 'object',
     3: 'item',
     4: 'itemset',
     5: 'quest',
     6: 'spell',
     7: 'zone',
     8: 'faction',
     9: 'pet',
    10: 'achievement',
    11: 'title',
    12: 'event',
    13: 'class',
    14: 'race',
    15: 'skill',
    17: 'currency'
};

// Items
$WH.cO(g_items, {
    add: function(id, json) {
        if (g_items[id] != null) {
            $WH.cO(g_items[id], json);
        }
        else {
            g_items[id] = json;
        }
    },
    getIcon: function(id) {
        if (g_items[id] != null && g_items[id].icon) {
            return g_items[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_items.getIcon(id), size, null, '?item=' + id, num, qty);
    }
});

// Spells
$WH.cO(g_spells, {
    add: function(id, json) {
        if (g_spells[id] != null) {
            $WH.cO(g_spells[id], json);
        }
        else {
            g_spells[id] = json;
        }
    },
    getIcon: function(id) {
        if (g_spells[id] != null && g_spells[id].icon) {
            return g_spells[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_spells.getIcon(id), size, null, '?spell=' + id, num, qty);
    }
});

// Achievements
$WH.cO(g_achievements, {
    getIcon: function(id) {
        if (g_achievements[id] != null && g_achievements[id].icon) {
            return g_achievements[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_achievements.getIcon(id), size, null, '?achievement=' + id, num, qty);
    }
});

// Classes
$WH.cO(g_classes, {
    getIcon: function(id) {
        if (g_file_classes[id]) {
            return 'class_' + g_file_classes[id];
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_classes.getIcon(id), size, null, '?class=' + id, num, qty);
    }
});

// Races
$WH.cO(g_races, {
    getIcon: function(id, gender) {
        if (gender === undefined) {
            gender = 0;
        }
        if (g_file_races[id] && g_file_genders[gender]) {
            return 'race_' + g_file_races[id] + '_' + g_file_genders[gender];
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_races.getIcon(id), size, null, '?race=' + id, num, qty);
    }
});

// Skills
$WH.cO(g_skills, {
    getIcon: function(id) {
        if (g_skills[id] != null && g_skills[id].icon) {
            return g_skills[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_skills.getIcon(id), size, null, '?skill=' + id, num, qty);
    }
});

// Currencies
$WH.cO(g_gatheredcurrencies, {
    getIcon: function(id, side) {
        if (g_gatheredcurrencies[id] != null && g_gatheredcurrencies[id].icon) {
            if ($WH.is_array(g_gatheredcurrencies[id].icon) && !isNaN(side)) {
                return g_gatheredcurrencies[id].icon[side];
            }
            return g_gatheredcurrencies[id].icon;
        }
        else
            return 'inv_misc_questionmark';
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_gatheredcurrencies.getIcon(id, (num > 0 ? 0 : 1)), size, null, null, Math.abs(num), qty);
    }
});

// Holidays
$WH.cO(g_holidays, {
    getIcon: function(id) {
        if (g_holidays[id] != null && g_holidays[id].icon) {
            return g_holidays[id].icon;
        }
        else {
            return 'inv_misc_questionmark';
        }
    },
    createIcon: function(id, size, num, qty) {
        return Icon.create(g_holidays.getIcon(id), size, null, '?event=' + id, num, qty);
    }
});

function g_getIngameLink(color, id, name) {
    // prompt(LANG.prompt_ingamelink, '/script DEFAULT_CHAT_FRAME:AddMessage("\\124c' + a + "\\124H" + c + "\\124h[" + b + ']\\124h\\124r");')
    return '/script DEFAULT_CHAT_FRAME:AddMessage("\\124c' + color + '\\124H' + id + '\\124h[' + name + ']\\124h\\124r");';
}

/*
 * Wowhead Site Achievements (WSA)
 * which i intend to ignore
*/
