if (typeof $WH == "undefined") {
	$WH = window
}
$WH.$ = function (c) {
	if (arguments.length > 1) {
		var b = [];
		var a;
		for (var d = 0, a = arguments.length; d < a; ++d) {
			b.push($WH.$(arguments[d]))
		}
		return b
	}
	if (typeof c == "string") {
		c = $WH.ge(c)
	}
	return c
};

$WH.$E = function (e) {
	if (!e) {
		if (typeof event != 'undefined') {
			e = event;
		}
		else {
			return null;
		}
	}

	// Netscape standard (1 = Left, 2 = Middle, 3 = Right)
	if (e.which) {
		e._button = e.which;
	}
	else {
		e._button = e.button;
		// IE8 doesnt have a button set, so add 1 to at least register as a left click
		if ($WH.Browser.ie6789 && e._button) { // 5.0
		// if ($WH.Browser.ie) { // 3.x
			if (e._button & 4) {
				e._button = 2; // Middle
			}
			else if (e._button & 2) {
				e._button = 3; // Right
			}
		}
		else {
			e._button = e.button + 1;
		}
	}

	e._target = e.target ? e.target : e.srcElement;

	e._wheelDelta = e.wheelDelta ? e.wheelDelta : -e.detail;

	return e;
};

$WH.$A = function (a) {
	var r = [];
	for (var i = 0, len = c.length; i < len; ++i) {
		r.push(a[i]);
	}

	return r;
};

$WH.bindfunc = function(){
	args = $A(arguments);
	var __method = args.shift();
	var object = args.shift();

	return function() {
		return __method.apply(object, args.concat($A(arguments)));
	}
};

if (!Function.prototype.bind) {
	Function.prototype.bind = function () {
		var
			__method = this,
			args	 = $WH.$A(arguments),
			object   = args.shift();

		return function () {
			return __method.apply(object, args.concat($A(arguments)));
		};
	}
}
if (!String.prototype.ltrim) {
	String.prototype.ltrim = function () {
		return this.replace(/^\s*/, "")
	}
}
if (!String.prototype.rtrim) {
	String.prototype.rtrim = function () {
		return this.replace(/\s*$/, "")
	}
}
if (!String.prototype.trim) {
	String.prototype.trim = function () {
		return this.ltrim().rtrim()
	}
}
if (!String.prototype.removeAllWhitespace) {
	String.prototype.removeAllWhitespace = function () {
		return this.replace("/s+/g", "")
	}
}
$WH.strcmp = function (d, c) {
	if (d == c) {
		return 0
	}
	if (d == null) {
		return -1
	}
	if (c == null) {
		return 1
	}
	var f = parseFloat(d),
	e = parseFloat(c);
	if (!isNaN(f) && !isNaN(e) && f != e) {
		return f < e ? -1 : 1
	}
	return d < c ? -1 : 1
};
$WH.trim = function (a) {
	return a.replace(/(^\s*|\s*$)/g, "")
};
$WH.rtrim = function (c, d) {
	var b = c.length;
	while (--b > 0 && c.charAt(b) == d) {}
	c = c.substring(0, b + 1);
	if (c == d) {
		c = ""
	}
	return c
};
$WH.sprintf = function (b) {
	var a;
	for (a = 1, len = arguments.length; a < len; ++a) {
		b = b.replace("$" + a, arguments[a])
	}
	return b
};
$WH.sprintfa = function (b) {
	var a;
	for (a = 1, len = arguments.length; a < len; ++a) {
		b = b.replace(new RegExp("\\$" + a, "g"), arguments[a])
	}
	return b
};
$WH.sprintfo = function (c) {
	if (typeof c == "object" && c.length) {
		var a = c;
		c = a[0];
		var b;
		for (b = 1; b < a.length; ++b) {
			c = c.replace("$" + b, a[b])
		}
		return c
	}
};
$WH.str_replace = function (e, d, c) {
	while (e.indexOf(d) != -1) {
		e = e.replace(d, c)
	}
	return e
};
$WH.urlencode = function (a) {
	a = encodeURIComponent(a);
	a = str_replace(a, "+", "+");
	return a
};
$WH.urlencode2 = function (a) {
	a = encodeURIComponent(a);
	a = str_replace(a, " ", "+");
	return a
};
$WH.number_format = function (a) {
	x = ("" + parseFloat(a)).split(".");
	a = x[0];
	x = x.length > 1 ? "." + x[1] : "";
	if (a.length <= 3) {
		return a + x
	}
	return $WH.number_format(a.substr(0, a.length - 3)) + "," + a.substr(a.length - 3) + x
};
$WH.is_array = function (b) {
	return !! (b && b.constructor == Array)
};
$WH.in_array = function (c, g, h, e) {
	if (c == null) {
		return -1
	}
	if (h) {
		return $WH.in_arrayf(c, g, h, e)
	}
	for (var d = e || 0, b = c.length; d < b; ++d) {
		if (c[d] == g) {
			return d
		}
	}
	return -1
};
$WH.in_arrayf = function (c, g, h, e) {
	for (var d = e || 0, b = c.length; d < b; ++d) {
		if (h(c[d]) == g) {
			return d
		}
	}
	return -1
};
$WH.rs = function () {
	var e = $WH.rs.random;
	var b = "";
	for (var a = 0; a < 16; a++) {
		var d = Math.floor(Math.random() * e.length);
		if (a == 0 && d < 11) {
			d += 10
		}
		b += e.substring(d, d + 1)
	}
	return b
};
$WH.rs.random = "0123456789abcdefghiklmnopqrstuvwxyz";
$WH.isset = function (a) {
	return typeof window[a] != "undefined"
};
$WH.array_walk = function (d, h, c) {
	var g;
	for (var e = 0, b = d.length; e < b; ++e) {
		g = h(d[e], c, d, e);
		if (g != null) {
			d[e] = g
		}
	}
};
$WH.array_apply = function (d, h, c) {
	var g;
	for (var e = 0, b = d.length; e < b; ++e) {
		h(d[e], c, d, e)
	}
};
$WH.ge = function (a) {
	return document.getElementById(a)
};
$WH.gE = function (a, b) {
	return a.getElementsByTagName(b)
};
$WH.ce = function (d, b, e) {
	var a = document.createElement(d);
	if (b) {
		$WH.cOr(a, b)
	}
	if (e) {
		$WH.ae(a, e)
	}
	return a
};
$WH.de = function (a) {
	a.parentNode.removeChild(a)
};
$WH.ae = function (a, b) {
	if ($WH.is_array(b)) {
		$WH.array_apply(b, a.appendChild.bind(a));
		return b
	} else {
		return a.appendChild(b)
	}
};
$WH.aef = function (a, b) {
	return a.insertBefore(b, a.firstChild)
};
$WH.ee = function (a, b) {
	if (!b) {
		b = 0
	}
	while (a.childNodes[b]) {
		a.removeChild(a.childNodes[b])
	}
};
$WH.ct = function (a) {
	return document.createTextNode(a)
};
$WH.st = function (a, b) {
	if (a.firstChild && a.firstChild.nodeType == 3) {
		a.firstChild.nodeValue = b
	} else {
		$WH.aef(a, ct(b))
	}
};
$WH.nw = function (a) {
	a.style.whiteSpace = "nowrap"
};
$WH.rf = function () {
	return false
};
$WH.rf2 = function (a) {
	a = $WH.$E(a);
	if (a.ctrlKey || a.shiftKey || a.altKey || a.metaKey) {
		return
	}
	return false
};
$WH.tb = function () {
	this.blur()
};
$WH.aE = function (b, c, a) {
	if ($WH.Browser.ie) {
		b.attachEvent("on" + c, a)
	} else {
		b.addEventListener(c, a, false)
	}
};
$WH.dE = function (b, c, a) {
	if ($WH.Browser.ie) {
		b.detachEvent("on" + c, a)
	} else {
		b.removeEventListener(c, a, false)
	}
};
$WH.sp = function (a) {
	if (!a) {
		a = event
	}
	if ($WH.Browser.ie) {
		a.cancelBubble = true
	} else {
		a.stopPropagation()
	}
};
$WH.sc = function (h, i, d, f, g) {
	var e = new Date();
	var c = h + "=" + escape(d) + "; ";
	e.setDate(e.getDate() + i);
	c += "expires=" + e.toUTCString() + "; ";
	if (f) {
		c += "path=" + f + "; "
	}
	if (g) {
		c += "domain=" + g + "; "
	}
	document.cookie = c;
	$WH.gc.C[h] = d
};
$WH.dc = function (a) {
	$WH.sc(a, -1);
	$WH.gc.C[a] = null
};
$WH.gc = function (f) {
	if ($WH.gc.I == null) {
		var e = unescape(document.cookie).split("; ");
		$WH.gc.C = {};
		for (var c = 0, a = e.length; c < a; ++c) {
			var g = e[c].indexOf("="),
			b,
			d;
			if (g != -1) {
				b = e[c].substr(0, g);
				d = e[c].substr(g + 1)
			} else {
				b = e[c];
				d = ""
			}
			$WH.gc.C[b] = d
		}
		$WH.gc.I = 1
	}
	if (!f) {
		return $WH.gc.C
	} else {
		return $WH.gc.C[f]
	}
};
$WH.ns = function (a) {
	if ($WH.Browser.ie) {
		a.onfocus = tb;
		a.onmousedown = a.onselectstart = a.ondragstart = $WH.rf
	}
};
$WH.eO = function (b) {
	for (var a in b) {
		delete b[a]
	}
};
$WH.cO = function (c, a) {
	for (var b in a) {
		if (a[b] !== null && typeof a[b] == "object" && a[b].length) {
			c[b] = a[b].slice(0)
		} else {
			c[b] = a[b]
		}
	}
};
$WH.cOr = function (c, a) {
	for (var b in a) {
		if (typeof a[b] == "object") {
			if (a[b].length) {
				c[b] = a[b].slice(0)
			} else {
				if (!c[b]) {
					c[b] = {}
				}
				$WH.cOr(c[b], a[b])
			}
		} else {
			c[b] = a[b]
		}
	}
};

$WH.Browser = {
	ie:	  !!(window.attachEvent && !window.opera),
	opera:   !!window.opera,
	safari:  navigator.userAgent.indexOf('Safari') != -1,
	firefox: navigator.userAgent.indexOf('Firefox') != -1,
	chrome:  navigator.userAgent.indexOf('Chrome') != -1
};
$WH.Browser.ie9   = $WH.Browser.ie && navigator.userAgent.indexOf('MSIE 9.0') != -1;
$WH.Browser.ie8   = $WH.Browser.ie && navigator.userAgent.indexOf('MSIE 8.0') != -1&& !$WH.Browser.ie9;
$WH.Browser.ie7   = $WH.Browser.ie && navigator.userAgent.indexOf('MSIE 7.0') != -1 && !$WH.Browser.ie8;
$WH.Browser.ie6   = $WH.Browser.ie && navigator.userAgent.indexOf('MSIE 6.0') != -1 && !$WH.Browser.ie7;

$WH.Browser.ie67   = $WH.Browser.ie6   || $WH.Browser.ie7;
$WH.Browser.ie678  = $WH.Browser.ie67  || $WH.Browser.ie8;
$WH.Browser.ie6789 = $WH.Browser.ie678 || $WH.Browser.ie9;

navigator.userAgent.match(/Gecko\/([0-9]+)/);
$WH.Browser.geckoVersion = parseInt(RegExp.$1) | 0;

$WH.OS = {
	windows: navigator.appVersion.indexOf('Windows')   != -1,
	mac:	 navigator.appVersion.indexOf('Macintosh') != -1,
	linux:   navigator.appVersion.indexOf('Linux')	 != -1
};

$WH.g_getWindowSize = function () {
	var a = 0,
	b = 0;
	if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
		a = document.documentElement.clientWidth;
		b = document.documentElement.clientHeight
	} else {
		if (document.body && (document.body.clientWidth || document.body.clientHeight)) {
			a = document.body.clientWidth;
			b = document.body.clientHeight
		} else {
			if (typeof window.innerWidth == "number") {
				a = window.innerWidth;
				b = window.innerHeight
			}
		}
	}
	return {
		w: a,
		h: b
	};
};

$WH.g_getScroll = function() {
	var
		x = 0,
		y = 0;

	if (typeof(window.pageYOffset) == "number") {
		// Netscape compliant
		x = window.pageXOffset;
		y = window.pageYOffset
	}
	else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
		// DOM compliant
		x = document.body.scrollLeft;
		y = document.body.scrollTop
	}
	else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
		// IE6 standards compliant mode
		x = document.documentElement.scrollLeft;
		y = document.documentElement.scrollTop
	}

	return {
		x: x,
		y: y
	};
};

$WH.g_getCursorPos = function(e) {
	var
		x,
		y;

	if (window.innerHeight) {

        // ok, something of a workaround here... MS9+ sends a MSEventObj istead of mouseEvent . whatever
        // but the properties for that are client[X|Y] DIAF!

        if (!e.pageX || !e.pageY) {
            x = e.clientX;
            y = e.clientY
        }
        else {
            x = e.pageX;
            y = e.pageY
        }
	}
	else {
		var scroll = $WH.g_getScroll();

		x = e.clientX + scroll.x;
		y = e.clientY + scroll.y
	}

	return {
		x: x,
		y: y
	};
};

$WH.ac = function (c, d) {
	var a = 0,
	g = 0,
	b;
	while (c) {
		a += c.offsetLeft;
		g += c.offsetTop;
		b = c.parentNode;
		while (b && b != c.offsetParent && b.offsetParent) {
			if (b.scrollLeft || b.scrollTop) {
				a -= (b.scrollLeft | 0);
				g -= (b.scrollTop | 0);
				break
			}
			b = b.parentNode
		}
		c = c.offsetParent
	}
	if ($WH.isset("Lightbox") && Lightbox.isVisible()) {
		d = true
	}
	if (d && !$WH.Browser.ie6) {
		var f = $WH.g_getScroll();
		a += f.x;
		g += f.y
	}
	var e = [a, g];
	e.x = a;
	e.y = g;
	return e
};
$WH.g_scrollTo = function (c, b) {
	var l, k = $WH.g_getWindowSize(),
	m = $WH.g_getScroll(),
	i = k.w,
	e = k.h,
	g = m.x,
	d = m.y;
	c = $WH.$(c);
	if (b == null) {
		b = []
	} else {
		if (typeof b == "number") {
			b = [b]
		}
	}
	l = b.length;
	if (l == 0) {
		b[0] = b[1] = b[2] = b[3] = 0
	} else {
		if (l == 1) {
			b[1] = b[2] = b[3] = b[0]
		} else {
			if (l == 2) {
				b[2] = b[0];
				b[3] = b[1]
			} else {
				if (l == 3) {
					b[3] = b[1]
				}
			}
		}
	}
	l = $WH.ac(c);
	var a = l[0] - b[3],
	h = l[1] - b[0],
	j = l[0] + c.offsetWidth + b[1],
	f = l[1] + c.offsetHeight + b[2];
	if (j - a > i || a < g) {
		g = a
	} else {
		if (j - i > g) {
			g = j - i
		}
	}
	if (f - h > e || h < d) {
		d = h
	} else {
		if (f - e > d) {
			d = f - e
		}
	}
	scrollTo(g, d)
};
$WH.g_createReverseLookupJson = function (b) {
	var c = {};
	for (var a in b) {
		c[b[a]] = a
	}
	return c
};
$WH.g_getLocaleFromDomain = function (a) {
	var c = $WH.g_getLocaleFromDomain.L;
	if (a) {
		var b = a.indexOf(".");
		if (b != -1) {
			a = a.substring(0, b)
		}
	}
	return (c[a] ? c[a] : 0)
};
$WH.g_getLocaleFromDomain.L = {
	fr: 2,
	de: 3,
	es: 6,
	ru: 8
};
$WH.g_getDomainFromLocale = function (a) {
	var b;
	if ($WH.g_getDomainFromLocale.L) {
		b = $WH.g_getDomainFromLocale.L
	} else {
		b = $WH.g_getDomainFromLocale.L = $WH.g_createReverseLookupJson($WH.g_getLocaleFromDomain.L)
	}
	return (b[a] ? b[a] : "www")
};
$WH.g_getIdFromTypeName = function (a) {
	var b = $WH.g_getIdFromTypeName.L;
	return (b[a] ? b[a] : -1)
};
$WH.g_getIdFromTypeName.L = {
	npc: 1,
	object: 2,
	item: 3,
	itemset: 4,
	quest: 5,
	spell: 6,
	zone: 7,
	faction: 8,
	pet: 9,
	achievement: 10,
	title: 11,
	profile: 100
};
$WH.g_ajaxIshRequest = function (b) {
	var c = document.getElementsByTagName("head")[0],
	a = $WH.g_getGets();
	if (a.refresh != null) {
		b += "&refresh"
	}
	$WH.ae(c, $WH.ce("script", {
		type: "text/javascript",
		src: b
	}))
};
$WH.g_getGets = function () {
	if ($WH.g_getGets.C != null) {
		return $WH.g_getGets.C
	}
	var e = {};
	if (location.search) {
		var f = decodeURIComponent(location.search.substr(1)).split("&");
		for (var c = 0, a = f.length; c < a; ++c) {
			var g = f[c].indexOf("="),
			b,
			d;
			if (g != -1) {
				b = f[c].substr(0, g);
				d = f[c].substr(g + 1)
			} else {
				b = f[c];
				d = ""
			}
			e[b] = d
		}
	}
	$WH.g_getGets.C = e;
	return e
};
$WH.g_createRect = function (d, c, a, b) {
	return {
		l: d,
		t: c,
		r: d + a,
		b: c + b
	}
};
$WH.g_intersectRect = function (d, c) {
	return ! (d.l >= c.r || c.l >= d.r || d.t >= c.b || c.t >= d.b)
};
$WH.g_convertRatingToPercent = function (g, b, f, d) {
	var e = $WH.g_convertRatingToPercent.RB;
	if (g < 0) {
		g = 1
	} else {
		if (g > 80) {
			g = 80
		}
	}
	if ((b == 14 || b == 12 || b == 15) && g < 34) {
		g = 34
	}
	if ((b == 28 || b == 36) && (d == 2 || d == 6 || d == 7 || d == 11)) {
		e[b] /= 1.3
	}
	if (f < 0) {
		f = 0
	}
	var a;
	if (e[b] == null) {
		a = 0
	} else {
		var c;
		if (g > 70) {
			c = (82 / 52) * Math.pow((131 / 63), ((g - 70) / 10))
		} else {
			if (g > 60) {
				c = (82 / (262 - 3 * g))
			} else {
				if (g > 10) {
					c = ((g - 8) / 52)
				} else {
					c = 2 / 52
				}
			}
		}
		a = f / e[b] / c
	}
	return a
};
$WH.g_convertRatingToPercent.RB = {
	12 : 1.5,
	13 : 12,
	14 : 15,
	15 : 5,
	16 : 10,
	17 : 10,
	18 : 8,
	19 : 14,
	20 : 14,
	21 : 14,
	22 : 10,
	23 : 10,
	24 : 0,
	25 : 0,
	26 : 0,
	27 : 0,
	28 : 10,
	29 : 10,
	30 : 10,
	31 : 10,
	32 : 14,
	33 : 0,
	34 : 0,
	35 : 25,
	36 : 10,
	37 : 2.5,
	44 : 4.69512176513672 / 1.1
};
$WH.g_statToJson = {
	1 : "health",
	2 : "mana",
	3 : "agi",
	4 : "str",
	5 : "int",
	6 : "spi",
	7 : "sta",
	12 : "defrtng",
	13 : "dodgertng",
	14 : "parryrtng",
	15 : "blockrtng",
	16 : "mlehitrtng",
	17 : "rgdhitrtng",
	18 : "splhitrtng",
	19 : "mlecritstrkrtng",
	20 : "rgdcritstrkrtng",
	21 : "splcritstrkrtng",
	22 : "_mlehitrtng",
	23 : "_rgdhitrtng",
	24 : "_splhitrtng",
	25 : "_mlecritstrkrtng",
	26 : "_rgdcritstrkrtng",
	27 : "_splcritstrkrtng",
	28 : "mlehastertng",
	29 : "rgdhastertng",
	30 : "splhastertng",
	31 : "hitrtng",
	32 : "critstrkrtng",
	33 : "_hitrtng",
	34 : "_critstrkrtng",
	35 : "resirtng",
	36 : "hastertng",
	37 : "exprtng",
	38 : "atkpwr",
	43 : "manargn",
	44 : "armorpenrtng",
	45 : "splpwr"
};
$WH.g_convertScalingFactor = function (c, b, g, d, j) {
	var f = $WH.g_convertScalingFactor.SV;
	var e = $WH.g_convertScalingFactor.SD;
	var i = {},
	h = f[c],
	a = e[g];
	if (!a || !(d >= 0 && d <= 9)) {
		i.v = h[b]
	} else {
		i.n = $WH.g_statToJson[a[d]];
		i.s = a[d];
		i.v = Math.floor(h[b] * a[d + 10] / 10000)
	}
	return (j ? i: i.v)
};
$WH.g_convertScalingFactor.SV = {
	1 : [2, 2, 1, 3, 1, 10, 32, 32, 64, 6, 8, 6, 8, 6, 8, 1, 0, 0, 1, 7, 13, 43, 43, 85],
	2 : [3, 3, 1, 3, 1, 12, 35, 35, 70, 6, 9, 6, 9, 7, 9, 2, 0, 0, 2, 8, 16, 47, 47, 93],
	3 : [3, 3, 1, 4, 1, 13, 39, 39, 76, 7, 9, 7, 9, 7, 10, 3, 0, 0, 2, 9, 17, 52, 52, 101],
	4 : [3, 3, 1, 4, 1, 14, 42, 42, 83, 7, 10, 7, 10, 8, 11, 3, 0, 0, 2, 9, 19, 56, 56, 111],
	5 : [4, 4, 2, 5, 2, 16, 45, 45, 89, 8, 10, 8, 10, 8, 12, 4, 0, 0, 3, 11, 21, 60, 60, 119],
	6 : [4, 4, 2, 5, 2, 17, 48, 48, 95, 8, 11, 8, 11, 9, 12, 5, 0, 0, 3, 11, 23, 64, 64, 127],
	7 : [4, 4, 3, 6, 2, 19, 51, 51, 101, 9, 11, 9, 11, 9, 13, 6, 0, 0, 3, 13, 25, 68, 68, 135],
	8 : [5, 5, 3, 7, 2, 20, 54, 54, 107, 9, 12, 9, 12, 9, 14, 7, 0, 0, 4, 13, 27, 72, 72, 143],
	9 : [5, 5, 3, 7, 2, 22, 56, 56, 113, 10, 13, 10, 13, 10, 15, 7, 0, 0, 4, 15, 29, 75, 75, 151],
	10 : [6, 6, 3, 8, 2, 23, 57, 57, 119, 10, 13, 10, 13, 10, 16, 8, 0, 0, 4, 15, 31, 76, 76, 159],
	11 : [6, 6, 4, 8, 3, 24, 59, 59, 125, 11, 14, 11, 14, 11, 16, 9, 0, 0, 5, 16, 32, 79, 79, 167],
	12 : [7, 7, 4, 9, 3, 25, 60, 60, 131, 11, 15, 11, 15, 12, 17, 10, 0, 0, 5, 17, 33, 80, 80, 175],
	13 : [7, 7, 4, 9, 3, 26, 61, 61, 133, 12, 16, 12, 16, 12, 18, 10, 0, 0, 5, 17, 35, 81, 81, 177],
	14 : [8, 8, 4, 10, 3, 27, 63, 63, 136, 12, 16, 12, 16, 13, 19, 11, 0, 0, 6, 18, 36, 84, 84, 181],
	15 : [8, 8, 5, 11, 3, 28, 64, 64, 138, 13, 17, 13, 17, 13, 20, 12, 0, 0, 6, 19, 37, 85, 85, 184],
	16 : [8, 8, 5, 11, 4, 29, 65, 65, 141, 14, 18, 14, 18, 14, 21, 13, 0, 0, 6, 19, 39, 87, 87, 188],
	17 : [9, 9, 5, 12, 4, 30, 67, 67, 143, 14, 19, 14, 19, 14, 22, 14, 0, 0, 7, 20, 40, 89, 89, 191],
	18 : [9, 9, 5, 12, 4, 30, 68, 68, 146, 15, 19, 15, 19, 15, 23, 14, 0, 0, 7, 20, 40, 91, 91, 195],
	19 : [10, 10, 6, 13, 4, 31, 69, 69, 148, 15, 20, 15, 20, 15, 24, 15, 0, 0, 7, 21, 41, 92, 92, 197],
	20 : [10, 10, 6, 14, 4, 32, 71, 71, 151, 16, 21, 16, 21, 16, 25, 16, 0, 0, 8, 21, 43, 95, 95, 201],
	21 : [11, 11, 6, 14, 5, 33, 72, 72, 153, 16, 21, 16, 21, 16, 26, 17, 0, 0, 8, 22, 44, 96, 96, 204],
	22 : [11, 11, 6, 15, 5, 34, 73, 73, 156, 17, 22, 17, 22, 17, 27, 18, 0, 0, 8, 23, 45, 97, 97, 208],
	23 : [12, 12, 7, 15, 5, 34, 75, 75, 158, 17, 23, 17, 23, 17, 28, 18, 0, 0, 9, 23, 45, 100, 100, 211],
	24 : [12, 12, 7, 16, 5, 35, 76, 76, 161, 18, 23, 18, 23, 18, 29, 19, 0, 0, 9, 23, 47, 101, 101, 215],
	25 : [12, 12, 7, 17, 5, 35, 77, 77, 163, 19, 24, 19, 24, 19, 29, 20, 0, 0, 9, 23, 47, 103, 103, 217],
	26 : [13, 13, 7, 17, 5, 36, 78, 78, 166, 19, 25, 19, 25, 19, 30, 21, 0, 0, 10, 24, 48, 104, 104, 221],
	27 : [13, 13, 8, 18, 6, 37, 80, 80, 168, 20, 26, 20, 26, 20, 31, 22, 0, 0, 10, 25, 49, 107, 107, 224],
	28 : [14, 14, 8, 18, 6, 37, 81, 81, 171, 21, 27, 21, 27, 21, 32, 22, 0, 0, 10, 25, 49, 108, 108, 228],
	29 : [14, 14, 8, 19, 6, 38, 82, 82, 173, 22, 29, 22, 29, 22, 32, 23, 0, 0, 11, 25, 51, 109, 109, 231],
	30 : [15, 15, 8, 20, 6, 38, 84, 84, 176, 23, 30, 23, 30, 22, 33, 24, 0, 0, 11, 25, 51, 112, 112, 235],
	31 : [15, 15, 9, 20, 6, 39, 85, 85, 178, 24, 31, 24, 31, 23, 34, 25, 0, 0, 11, 26, 52, 113, 113, 237],
	32 : [16, 16, 9, 21, 7, 40, 86, 86, 181, 25, 32, 25, 32, 24, 34, 25, 0, 0, 12, 27, 53, 115, 115, 241],
	33 : [16, 16, 9, 21, 7, 41, 88, 88, 184, 25, 33, 25, 33, 25, 35, 26, 0, 0, 12, 27, 55, 117, 117, 245],
	34 : [16, 16, 9, 22, 7, 43, 90, 90, 187, 26, 34, 26, 34, 26, 36, 27, 0, 0, 12, 29, 57, 120, 120, 249],
	35 : [17, 17, 10, 23, 7, 44, 91, 91, 190, 27, 35, 27, 35, 26, 37, 28, 0, 0, 13, 29, 59, 121, 121, 253],
	36 : [17, 17, 10, 23, 7, 44, 93, 93, 193, 28, 36, 28, 36, 27, 38, 29, 0, 0, 13, 29, 59, 124, 124, 257],
	37 : [18, 18, 10, 24, 8, 46, 95, 95, 196, 28, 37, 28, 37, 28, 39, 29, 0, 0, 13, 31, 61, 127, 127, 261],
	38 : [18, 18, 10, 24, 8, 47, 97, 97, 199, 29, 38, 29, 38, 28, 40, 30, 0, 0, 14, 31, 63, 129, 129, 265],
	39 : [19, 19, 11, 25, 8, 48, 99, 99, 202, 29, 38, 29, 38, 29, 41, 31, 0, 0, 14, 32, 64, 132, 132, 269],
	40 : [19, 19, 11, 26, 8, 49, 100, 205, 360, 30, 39, 30, 39, 30, 42, 32, 0, 0, 14, 33, 65, 133, 273, 480],
	41 : [20, 20, 11, 26, 8, 50, 102, 209, 368, 31, 40, 31, 40, 30, 43, 33, 0, 0, 15, 33, 67, 136, 279, 491],
	42 : [20, 20, 11, 27, 8, 51, 104, 213, 375, 31, 41, 31, 41, 31, 44, 33, 0, 0, 15, 34, 68, 139, 284, 500],
	43 : [20, 20, 12, 27, 9, 52, 106, 217, 382, 32, 41, 32, 41, 31, 45, 34, 0, 0, 15, 35, 69, 141, 289, 509],
	44 : [21, 21, 12, 28, 9, 53, 108, 221, 389, 32, 42, 32, 42, 32, 47, 35, 0, 0, 16, 35, 71, 144, 295, 519],
	45 : [21, 21, 12, 28, 9, 54, 109, 225, 396, 33, 43, 33, 43, 33, 48, 36, 0, 0, 16, 36, 72, 145, 300, 528],
	46 : [22, 22, 12, 29, 9, 55, 111, 229, 403, 34, 44, 34, 44, 33, 50, 37, 0, 0, 16, 37, 73, 148, 305, 537],
	47 : [22, 22, 13, 30, 9, 56, 113, 233, 410, 34, 45, 34, 45, 34, 51, 37, 0, 0, 17, 37, 75, 151, 311, 547],
	48 : [23, 23, 13, 30, 10, 57, 115, 238, 418, 35, 45, 35, 45, 34, 52, 38, 0, 0, 17, 38, 76, 153, 317, 557],
	49 : [23, 23, 13, 31, 10, 58, 117, 242, 425, 35, 46, 35, 46, 35, 53, 39, 0, 0, 17, 39, 77, 156, 323, 567],
	50 : [24, 24, 13, 31, 10, 59, 118, 246, 433, 36, 47, 36, 47, 36, 54, 40, 0, 0, 18, 39, 79, 157, 328, 577],
	51 : [24, 24, 14, 32, 10, 60, 120, 250, 440, 37, 48, 37, 48, 36, 54, 40, 0, 0, 18, 40, 80, 160, 333, 587],
	52 : [24, 24, 14, 33, 10, 61, 122, 254, 448, 37, 49, 37, 49, 37, 55, 41, 0, 0, 18, 41, 81, 163, 339, 597],
	53 : [25, 25, 14, 33, 11, 62, 124, 258, 455, 38, 49, 38, 49, 38, 57, 42, 0, 0, 19, 41, 83, 165, 344, 607],
	54 : [25, 25, 14, 34, 11, 63, 126, 262, 463, 38, 50, 38, 50, 38, 58, 43, 0, 0, 19, 42, 84, 168, 349, 617],
	55 : [26, 26, 15, 34, 11, 64, 127, 266, 470, 39, 51, 39, 51, 39, 59, 44, 0, 0, 19, 43, 85, 169, 355, 627],
	56 : [26, 26, 15, 35, 11, 65, 129, 270, 478, 40, 52, 40, 52, 39, 61, 44, 0, 0, 20, 43, 87, 172, 360, 637],
	57 : [27, 27, 15, 36, 11, 66, 131, 274, 485, 40, 53, 40, 53, 40, 62, 45, 0, 0, 20, 44, 88, 175, 365, 647],
	58 : [34, 34, 19, 46, 14, 83, 159, 344, 611, 50, 65, 46, 60, 48, 85, 46, 0, 0, 25, 55, 111, 212, 459, 815],
	59 : [36, 36, 20, 47, 15, 86, 164, 356, 633, 51, 67, 47, 61, 50, 88, 52, 0, 0, 27, 57, 115, 219, 475, 844],
	60 : [37, 37, 21, 49, 16, 88, 169, 368, 655, 53, 68, 47, 62, 51, 91, 57, 0, 0, 28, 59, 117, 225, 491, 873],
	61 : [39, 39, 22, 51, 16, 91, 174, 380, 677, 54, 70, 48, 63, 52, 94, 63, 0, 0, 29, 61, 121, 232, 507, 903],
	62 : [40, 40, 22, 53, 17, 94, 178, 392, 699, 55, 72, 49, 64, 53, 97, 68, 0, 0, 30, 63, 125, 237, 523, 932],
	63 : [41, 41, 23, 54, 17, 97, 183, 404, 721, 56, 73, 50, 65, 54, 100, 73, 0, 0, 31, 65, 129, 244, 539, 961],
	64 : [42, 42, 24, 55, 18, 100, 187, 416, 742, 58, 75, 50, 66, 55, 104, 80, 0, 0, 31, 67, 133, 249, 555, 989],
	65 : [43, 43, 24, 57, 18, 102, 192, 428, 764, 60, 78, 52, 67, 57, 109, 89, 0, 0, 32, 68, 136, 256, 571, 1019],
	66 : [44, 44, 25, 58, 18, 105, 197, 440, 786, 62, 81, 53, 69, 59, 113, 98, 0, 0, 33, 70, 140, 263, 587, 1048],
	67 : [45, 45, 25, 59, 19, 108, 203, 452, 808, 65, 84, 54, 71, 61, 117, 107, 0, 0, 34, 72, 144, 271, 603, 1077],
	68 : [62, 62, 36, 84, 26, 157, 294, 654, 1169, 91, 119, 70, 91, 82, 168, 220, 0, 0, 47, 105, 209, 392, 872, 1559],
	69 : [64, 64, 37, 87, 27, 161, 303, 675, 1206, 93, 121, 71, 92, 84, 172, 228, 0, 0, 48, 107, 215, 404, 900, 1608],
	70 : [67, 67, 39, 90, 28, 166, 312, 695, 1242, 95, 124, 72, 94, 86, 175, 238, 0, 0, 50, 111, 221, 416, 927, 1656],
	71 : [69, 69, 40, 93, 29, 171, 321, 715, 1278, 97, 127, 73, 95, 88, 179, 246, 0, 0, 52, 114, 228, 428, 953, 1704],
	72 : [72, 72, 42, 97, 30, 176, 331, 736, 1315, 99, 129, 75, 97, 90, 183, 255, 0, 0, 54, 117, 235, 441, 981, 1753],
	73 : [75, 75, 43, 101, 32, 181, 340, 756, 1351, 102, 132, 76, 99, 92, 187, 265, 0, 0, 56, 121, 241, 453, 1008, 1801],
	74 : [78, 78, 45, 104, 33, 186, 349, 776, 1387, 104, 135, 77, 100, 94, 191, 275, 0, 0, 58, 124, 248, 465, 1035, 1849],
	75 : [81, 81, 46, 108, 34, 191, 358, 797, 1423, 106, 138, 79, 102, 96, 196, 285, 0, 0, 60, 127, 255, 477, 1063, 1897],
	76 : [84, 84, 48, 113, 35, 196, 367, 817, 1460, 109, 141, 80, 104, 98, 200, 296, 0, 0, 63, 131, 261, 489, 1089, 1947],
	77 : [87, 87, 50, 117, 37, 200, 376, 837, 1496, 111, 145, 81, 106, 101, 205, 307, 0, 0, 65, 133, 267, 501, 1116, 1995],
	78 : [90, 90, 52, 121, 38, 205, 386, 858, 1532, 114, 148, 83, 108, 103, 210, 319, 0, 0, 68, 137, 273, 515, 1144, 2043],
	79 : [94, 94, 54, 126, 40, 208, 390, 868, 1551, 117, 152, 85, 110, 105, 215, 331, 0, 0, 71, 139, 277, 520, 1157, 2068],
	80 : [97, 97, 56, 131, 41, 210, 395, 878, 1570, 120, 156, 86, 112, 108, 220, 343, 0, 0, 73, 140, 280, 527, 1171, 2093]
};
$WH.g_convertScalingFactor.SD = {
	1 : [4, 7, 32, -1, -1, -1, -1, -1, -1, -1, 5259, 7888, 5259, 0, 0, 0, 0, 0, 0, 0, 80],
	2 : [38, 3, 31, -1, -1, -1, -1, -1, -1, -1, 14532, 4106, 3193, 0, 0, 0, 0, 0, 0, 0, 80],
	3 : [38, 7, 32, -1, -1, -1, -1, -1, -1, -1, 5068, 5065, 6666, 0, 0, 0, 0, 0, 0, 0, 80],
	4 : [38, 32, 31, -1, -1, -1, -1, -1, -1, -1, 13332, 4767, 3900, 0, 0, 0, 0, 0, 0, 0, 80],
	5 : [7, 5, 32, -1, -1, -1, -1, -1, -1, -1, 7150, 5850, 4766, 0, 0, 0, 0, 0, 0, 0, 80],
	6 : [7, 5, 43, -1, -1, -1, -1, -1, -1, -1, 5067, 7601, 1350, 0, 0, 0, 0, 0, 0, 0, 80],
	7 : [4, 7, 32, -1, -1, -1, -1, -1, -1, -1, 6666, 6666, 4445, 0, 0, 0, 0, 0, 0, 0, 80],
	8 : [38, 3, 7, 5, -1, -1, -1, -1, -1, -1, 10518, 5258, 5641, 3076, 0, 0, 0, 0, 0, 0, 80],
	9 : [45, 7, 5, 43, -1, -1, -1, -1, -1, -1, 5201, 6666, 4444, 1778, 0, 0, 0, 0, 0, 0, 80],
	10 : [38, 31, 7, -1, -1, -1, -1, -1, -1, -1, 14532, 4106, 4789, 0, 0, 0, 0, 0, 0, 0, 80],
	11 : [45, 7, 5, 6, -1, -1, -1, -1, -1, -1, 6153, 3996, 3997, 5258, 0, 0, 0, 0, 0, 0, 80],
	12 : [0, 5, 0, 0, 0, 0, 0, 0, 0, 0, 5000, 5000, 0, 0, 0, 0, 0, 0, 0, 0, 10],
	13 : [42, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10],
	14 : [38, 39, 40, 41, 42, 43, 0, 0, 0, 0, 6500, 6500, 10000, 10000, 10000, 10000, 0, 0, 0, 0, 15],
	15 : [40, 41, 42, 0, 0, 0, 0, 0, 0, 0, 4200, 5200, 6200, 0, 0, 0, 0, 0, 0, 0, 10],
	16 : [45, 7, 5, 6, -1, -1, -1, -1, -1, -1, 6153, 3996, 3997, 5258, 0, 0, 0, 0, 0, 0, 80],
	21 : [12, 13, 14, 15, 16, 0, 0, 0, 0, 0, 5000, 6000, 7000, 8000, 9000, 0, 0, 0, 0, 0, 10],
	41 : [3, 4, 5, 6, 7, 12, 13, 14, 15, 16, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10],
	42 : [17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10],
	43 : [27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10000, 10],
	102 : [44, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 15],
	103 : [3, -1, -1, -1, -1, -1, -1, -1, -1, -1, 10000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
	104 : [32, -1, -1, -1, -1, -1, -1, -1, -1, -1, 10000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 80],
	105 : [13, -1, -1, -1, -1, -1, -1, -1, -1, -1, 10000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
	221 : [4, 7, 32, 36, -1, -1, -1, -1, -1, -1, 4844, 7266, 4106, 3193, 0, 0, 0, 0, 0, 0, 80],
	222 : [3, 44, 7, -1, -1, -1, -1, -1, -1, -1, 5259, 3506, 5259, 0, 0, 0, 0, 0, 0, 0, 80],
	223 : [7, 5, 32, -1, -1, -1, -1, -1, -1, -1, 4859, 5732, 2519, 0, 0, 0, 0, 0, 0, 0, 80],
	224 : [38, 3, 31, 7, -1, -1, -1, -1, -1, -1, 9688, 4844, 3193, 6159, 0, 0, 0, 0, 0, 0, 80],
	241 : [45, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10000, 0, 0, 0, 0, 0, 0, 0, 0, 0, 80],
	251 : [36, -1, -1, -1, -1, -1, -1, -1, -1, -1, 6666, 0, 0, 0, 0, 0, 0, 0, 0, 0, 80],
	271 : [45, -1, -1, -1, -1, -1, -1, -1, -1, -1, 7800, 0, 0, 0, 0, 0, 0, 0, 0, 0, 80],
	291 : [38, -1, -1, -1, -1, -1, -1, -1, -1, -1, 23252, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
	292 : [38, 7, 35, -1, -1, -1, -1, -1, -1, -1, 10518, 7888, 5258, 0, 0, 0, 0, 0, 0, 0, 80],
	293 : [4, 7, 35, -1, -1, -1, -1, -1, -1, -1, 7266, 4789, 4106, 0, 0, 0, 0, 0, 0, 0, 80],
	294 : [38, 32, 35, -1, -1, -1, -1, -1, -1, -1, 8212, 7266, 3193, 0, 0, 0, 0, 0, 0, 0, 80],
	295 : [7, 35, 43, -1, -1, -1, -1, -1, -1, -1, 6666, 6666, 1777, 0, 0, 0, 0, 0, 0, 0, 80],
	296 : [7, 31, 35, -1, -1, -1, -1, -1, -1, -1, 7888, 5259, 5258, 0, 0, 0, 0, 0, 0, 0, 80],
	297 : [35, -1, -1, -1, -1, -1, -1, -1, -1, -1, 5259, 0, 0, 0, 0, 0, 0, 0, 0, 0, 80],
	298 : [35, -1, -1, -1, -1, -1, -1, -1, -1, -1, 6667, 0, 0, 0, 0, 0, 0, 0, 0, 0, 80],
	299 : [35, -1, -1, -1, -1, -1, -1, -1, -1, -1, 6667, 0, 0, 0, 0, 0, 0, 0, 0, 0, 80],
	300 : [4, 7, 35, -1, -1, -1, -1, -1, -1, -1, 5259, 7888, 5258, 0, 0, 0, 0, 0, 0, 0, 80],
	301 : [45, 7, 43, 35, -1, -1, -1, -1, -1, -1, 5200, 6666, 1776, 4444, 0, 0, 0, 0, 0, 0, 80],
	302 : [38, 3, 7, 35, -1, -1, -1, -1, -1, -1, 8888, 4444, 6668, 4444, 0, 0, 0, 0, 0, 0, 80],
	303 : [45, 7, 5, 35, -1, -1, -1, -1, -1, -1, 6153, 5259, 3506, 5258, 0, 0, 0, 0, 0, 0, 80],
	304 : [38, 3, 7, 35, -1, -1, -1, -1, -1, -1, 8888, 3899, 6666, 4767, 0, 0, 0, 0, 0, 0, 80],
	305 : [45, 7, 6, 35, -1, -1, -1, -1, -1, -1, 6153, 5259, 3506, 5258, 0, 0, 0, 0, 0, 0, 80],
	306 : [45, 7, 32, 35, -1, -1, -1, -1, -1, -1, 6153, 5259, 3506, 5258, 0, 0, 0, 0, 0, 0, 80],
	311 : [ -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
	331 : [38, 3, 7, 5, -1, -1, -1, -1, -1, -1, 10518, 5258, 5259, 3506, 0, 0, 0, 0, 0, 0, 80],
	332 : [45, 7, 5, 43, -1, -1, -1, -1, -1, -1, 6153, 3997, 3997, 2629, 0, 0, 0, 0, 0, 0, 80],
	333 : [4, 7, 32, -1, -1, -1, -1, -1, -1, -1, 5996, 5996, 5258, 0, 0, 0, 0, 0, 0, 0, 80],
	334 : [45, 7, 5, 6, -1, -1, -1, -1, -1, -1, 6153, 3997, 3997, 5259, 0, 0, 0, 0, 0, 0, 80],
	335 : [38, 7, 31, -1, -1, -1, -1, -1, -1, -1, 10518, 7888, 5259, 0, 0, 0, 0, 0, 0, 0, 80],
	336 : [45, 7, 5, 6, -1, -1, -1, -1, -1, -1, 6153, 3997, 3997, 5259, 0, 0, 0, 0, 0, 0, 80],
	351 : [3, 38, 7, 32, -1, -1, -1, -1, -1, -1, 5259, 7012, 7889, 3506, 0, 0, 0, 0, 0, 0, 80],
	352 : [3, 44, 7, 38, -1, -1, -1, -1, -1, -1, 5259, 3506, 7889, 7012, 0, 0, 0, 0, 0, 0, 80],
	371 : [32, 31, 7, -1, -1, -1, -1, -1, -1, -1, 7266, 4106, 4789, 0, 0, 0, 0, 0, 0, 0, 80]
};
$WH.g_setJsonItemLevel = function (s, a) {
	if (!s.scadist || !s.scaflags) {
		return
	}
	s.bonuses = s.bonuses || {};
	var c = -1,
	r = -1,
	k = -1,
	p = -1,
	f = 262175,
	o = 16253408,
	d = 32256,
	g = 32768,
	b = 5120;
	for (var h = 0; h < 24; ++h) {
		var l = 1 << h;
		if (l & s.scaflags) {
			if (l & f && c < 0) {
				c = h
			} else {
				if (l & o && r < 0) {
					r = h
				} else {
					if (l & d && k < 0) {
						k = h
					} else {
						if (l & g && p < 0) {
							p = h
						}
					}
				}
			}
		}
	}
	if (c >= 0) {
		for (var h = 0; h < 10; ++h) {
			var q = $WH.g_convertScalingFactor(a, c, s.scadist, h, 1);
			if (q.n) {
				s[q.n] = q.v
			}
			s.bonuses[q.s] = q.v
		}
	}
	if (r >= 0) {
		s.armor = $WH.g_convertScalingFactor(a, r)
	}
	if (k >= 0) {
		var j = (s.scaflags & b ? 0.2 : 0.3),
		m = (s.mledps ? "mle": "rgd");
		s.speed /= s.speed > 1000 ? 1000 : 1;			   // summary expects speed in sec; different version of script is different
		s.dps = s[m + "dps"] = g_convertScalingFactor(a, k);
		s.dmgmin = s[m + "dmgmin"] = Math.floor(s.dps * s.speed * (1 - j));
		s.dmgmax = s[m + "dmgmax"] = Math.floor(s.dps * s.speed * (1 + j))

		if (s.feratkpwr) {								  // yes thats custom too..
			s.feratkpwr = Math.max(0, Math.floor((s.dps - 54.8) * 14));
		}
	}
	if (p >= 0) {
		s.splpwr = s.bonuses[45] = $WH.g_convertScalingFactor(a, p)
	}
	if (s.gearscore != null) {
		if (s._gearscore == null) {
			s._gearscore = s.gearscore
		}
		var e = Math.min(80, a + 1);
		if (e >= 70) {
			n = ((e - 70) * 9.5) + 105
		} else {
			if (e >= 60) {
				n = ((e - 60) * 4.5) + 60
			} else {
				n = e + 5
			}
		}
		s.gearscore = (s._gearscore * n) / 1.8
	}
};
$WH.g_setTooltipItemLevel = function (a, g) {
	var d = typeof a;
	if (d == "number") {
		if ($WH.isset("g_items") && g_items[a] && g_items[a]["tooltip_" + g_locale.name]) {
			a = g_items[a]["tooltip_" + g_locale.name]
		} else {
			return a
		}
	} else {
		if (d != "string") {
			return a
		}
	}
	d = a.match(/<!--\?\d+:\d+:\d+:\d+(:(\d+):(\d+))?-->/);
	if (!d) {
		return a
	}
	var b = d[2] || 0,
	c = d[3] || 0;
	if (b && c) {
		var f = a.match(/<!--spd-->(\d\.\d+)/);
		if (f) {
			f = Math.floor(parseFloat(f[1]) * 1000)
		}
		var e = {
			scadist: b,
			scaflags: c,
			speed: f || 0
		};
		$WH.g_setJsonItemLevel(e, g);
		a = a.replace(/(<!--asc(\d+)-->)([^<]+)/, function (j, h, i) {
			d = i;
			if (g < 40 && (i == 3 || i == 4)) {--d
			}
			return h + g_itemset_types[d]
		});
		a = a.replace(/(<!--dmg-->)\d+(\D+)\d+/, function (j, h, i) {
			return h + e.dmgmin + i + e.dmgmax
		});
		a = a.replace(/(<!--dps-->\D*?)(\d+\.\d)/, function (i, h) {
			return h + e.dps.toFixed(1)
		});
		a = a.replace(/<span class="c11"><!--fap-->(\D*?)(\d+)(\D*?)<\/span>(<br \/>)?/i, function (l, h, i, m, j) {
			var k;
			i = Math.floor((e.dps - 54.8) * 14);
			if (e.dps > 54.8 && i > 0) {
				k = "";
				j = (j ? "<br />": "")
			} else {
				i = 0;
				k = ' style="display: none"';
				j = (j ? "<!--br-->": "")
			}
			return '<span class="c11"' + k + "><!--fap-->" + h + i + m + "</span>" + j
		});
		a = a.replace(/(<!--amr-->)\d+/, function (i, h) {
			return h + e.armor
		});
		a = a.replace(/<span><!--stat(\d+)-->[-+]\d+(\D*?)<\/span>(<!--e-->)?(<!--ps-->)?(<br ?\/?>)?/gi, function (l, i, h, o, p, j) {
			var k, m = e.bonuses[i];
			if (m) {
				m = (m > 0 ? "+": "-") + m;
				k = "";
				j = (j ? "<br />": "")
			} else {
				m = "+0";
				k = ' style="display: none"';
				j = (j ? "<!--br-->": "")
			}
			return "<span" + k + "><!--stat" + i + "-->" + m + h + "</span>" + (o || "") + (p || "") + j
		});
		a = a.replace(/<span class="q2">(.*?)<!--rtg(\d+)-->\d+(.*?)<\/span>(<br \/>)?/gi, function (h, k, m, p, l, i, q) {
			var j, o = e.bonuses[m];
			if (o) {
				j = "";
				q = (q ? "<br />": "")
			} else {
				j = ' style="display: none"';
				q = (q ? "<!--br-->": "")
			}
			return '<span class="q2"' + j + ">" + k + "<!--rtg" + m + "-->" + o + p + "</span>" + q
		})
	}
	a = a.replace(/(<!--rtg%(\d+)-->)([\.0-9]+)/g, function (j, h, k, i) {
		d = a.match(new RegExp("<!--rtg" + k + "-->(\\d+)"));
		if (!d) {
			return j
		}
		return h + Math.round($WH.g_convertRatingToPercent(g, k, d[1]) * 100) / 100
	});
	a = a.replace(/(<!--\?\d+:\d+:\d+:)\d+((:\d+:\d+)?-->)/, "$1" + g + "$2");
	a = a.replace(/<!--lvl-->\d+/g, "<!--lvl-->" + g);
	return a
};
$WH.g_enhanceTooltip = function (a, e, d) {
	var c = typeof a;
	if (c == "number") {
		if ($WH.isset("g_items") && g_items[a] && g_items[a]["tooltip_" + g_locale.name]) {
			a = g_items[a]["tooltip_" + g_locale.name]
		} else {
			return a
		}
	} else {
		if (c != "string") {
			return a
		}
	}
	if (d) {
		var b = $WH.g_getGets();
		if (b.lvl) {
			a = $WH.g_setTooltipItemLevel(a, b.lvl)
		}
	}
	if (e) {
		a = a.replace(/<span class="q2"><!--addamr(\d+)--><span>.*?<\/span><\/span>/i, function (f, g) {
			return '<span class="q2 tip" onmouseover="Tooltip.showAtCursor(event, sprintf(LANG.tooltip_armorbonus, ' + g + '), 0, 0, \'q\')" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">' + f + "</span>"
		});
		a = a.replace(/\(([^\)]*?<!--lvl-->[^\(]*?)\)/gi, function (g, f) {
			return '(<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="g_staticTooltipLevelClick(this)">' + f + "</a>)"
		})
	}
	return a
};
$WH.g_staticTooltipLevelClick = function (g, f) {
	while (g.className.indexOf("tooltip") == -1) {
		g = g.parentNode
	}
	var c = g.innerHTML;
	c = c.match(/<!--\?(\d+):(\d+):(\d+):(\d+)/);
	if (!c) {
		return
	}
	var e = parseInt(c[1]),
	d = parseInt(c[2]),
	b = parseInt(c[3]),
	a = parseInt(c[4]);
	if (!f) {
		f = prompt($WH.sprintf(LANG.prompt_ratinglevel, d, b), a)
	}
	f = parseInt(f);
	if (isNaN(f)) {
		return
	}
	if (f == a || f < d || f > b) {
		return
	}
	c = g_setTooltipItemLevel(g_items[e]["tooltip_" + g_locale.name], f);
	c = g_enhanceTooltip(c, true);
	g.innerHTML = "<table><tr><td>" + c + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
	Tooltip.fixSafe(g, 1, 1)
};
$WH.Tooltip = {
	create: function (i) {
		var g = $WH.ce("div"),
		l = $WH.ce("table"),
		b = $WH.ce("tbody"),
		f = $WH.ce("tr"),
		c = $WH.ce("tr"),
		a = $WH.ce("td"),
		k = $WH.ce("th"),
		j = $WH.ce("th"),
		h = $WH.ce("th");
		g.className = "wowhead-tooltip";
		k.style.backgroundPosition = "top right";
		j.style.backgroundPosition = "bottom left";
		h.style.backgroundPosition = "bottom right";
		if (i) {
			a.innerHTML = i
		}
		$WH.ae(f, a);
		$WH.ae(f, k);
		$WH.ae(b, f);
		$WH.ae(c, j);
		$WH.ae(c, h);
		$WH.ae(b, c);
		$WH.ae(l, b);
		$WH.Tooltip.icon = $WH.ce("p");
		$WH.Tooltip.icon.style.visibility = "hidden";
		$WH.ae($WH.Tooltip.icon, $WH.ce("div"));
		$WH.ae(g, $WH.Tooltip.icon);
		$WH.ae(g, l);
		var e = $WH.ce("div");
		e.className = "wowhead-tooltip-powered";
		$WH.ae(g, e);
		$WH.Tooltip.logo = e;
		return g
	},
	fix: function (d, b, f) {
		var e = $WH.gE(d, "table")[0],
		h = $WH.gE(e, "td")[0],
		g = h.childNodes;
		if (g.length >= 2 && g[0].nodeName == "TABLE" && g[1].nodeName == "TABLE") {
			g[0].style.whiteSpace = "nowrap";
			var a;
			if (g[1].offsetWidth > 300) {
				a = Math.max(300, g[0].offsetWidth) + 20
			} else {
				a = Math.max(g[0].offsetWidth, g[1].offsetWidth) + 20
			}
			if (a > 20) {
				d.style.width = a + "px";
				g[0].style.width = g[1].style.width = "100%";
				if (!b && d.offsetHeight > document.body.clientHeight) {
					e.className = "shrink"
				}
			}
		}
		if (f) {
			d.style.visibility = "visible"
		}
	},
	fixSafe: function (c, b, a) {
		if ($WH.Browser.ie) {
			setTimeout($WH.Tooltip.fix.bind(this, c, b, a), 1)
		} else {
			$WH.Tooltip.fix(c, b, a)
		}
	},
	append: function (c, b) {
		var c = $WH.$(c);
		var a = $WH.Tooltip.create(b);
		$WH.ae(c, a);
		$WH.Tooltip.fixSafe(a, 1, 1)
	},
	prepare: function () {
		if ($WH.Tooltip.tooltip) {
			return
		}
		var b = $WH.Tooltip.create();
		b.style.position = "absolute";
		b.style.left = b.style.top = "-2323px";
		var a = $WH.ge("layers");
		if (!a) {
			a = $WH.ce("div");
			a.id = "layers";
			$WH.aef(document.body, a)
		}
		$WH.ae(a, b);
		$WH.Tooltip.tooltip = b;
		$WH.Tooltip.tooltipTable = $WH.gE(b, "table")[0];
		$WH.Tooltip.tooltipTd = $WH.gE(b, "td")[0];
		if ($WH.Browser.ie6) {
			b = ce("iframe");
			b.src = "javascript:0;";
			b.frameBorder = 0;
			if (a) {
				$WH.ae(a, b)
			} else {
				$WH.ae(document.body, b)
			}
			$WH.Tooltip.iframe = b
		}
	},
	set: function (b) {
		var a = $WH.Tooltip.tooltip;
		a.style.width = "550px";
		a.style.left = "-2323px";
		a.style.top = "-2323px";
		$WH.Tooltip.tooltipTd.innerHTML = b;
		a.style.display = "";
		$WH.Tooltip.fix(a, 0, 0)
	},
	moveTests: [[null, null], [null, false], [false, null], [false, false]],
	move: function (m, l, d, o, c, a) {
		if (!$WH.Tooltip.tooltipTable) {
			return
		}
		var k = $WH.Tooltip.tooltip,
		g = $WH.Tooltip.tooltipTable.offsetWidth,
		b = $WH.Tooltip.tooltipTable.offsetHeight,
		p;
		k.style.width = g + "px";
		var j, e;
		for (var f = 0, h = $WH.Tooltip.moveTests.length; f < h; ++f) {
			p = $WH.Tooltip.moveTests[f];
			j = $WH.Tooltip.moveTest(m, l, d, o, c, a, p[0], p[1]);
			if ($WH.isset("Ads") && !Ads.intersect(j)) {
				e = true;
				break
			} else {
				if (!$WH.isset("Ads")) {
					break
				}
			}
		}
		if ($WH.isset("Ads") && !e) {
			$WH.Tooltip.hiddenAd = Ads.intersect(j, true)
		}
		k.style.left = j.l + "px";
		k.style.top = j.t + "px";
		k.style.visibility = "visible";
		if ($WH.Browser.ie6 && $WH.Tooltip.iframe) {
			var p = $WH.Tooltip.iframe;
			p.style.left = j.l + "px";
			p.style.top = j.t + "px";
			p.style.width = g + "px";
			p.style.height = b + "px";
			p.style.display = "";
			p.style.visibility = "visible"
		}
	},
	moveTest: function (e, l, o, y, c, a, m, b) {
		var k = e,
		w = l,
		f = $WH.Tooltip.tooltip,
		i = $WH.Tooltip.tooltipTable.offsetWidth,
		q = $WH.Tooltip.tooltipTable.offsetHeight,
		g = $WH.g_getWindowSize(),
		j = $WH.g_getScroll(),
		h = g.w,
		p = g.h,
		d = j.x,
		v = j.y,
		u = d,
		t = v,
		s = d + h,
		r = v + p;
		if (m == null) {
			m = (e + o + i <= s)
		}
		if (b == null) {
			b = (l - q >= t)
		}
		if (m) {
			e += o + c
		} else {
			e = Math.max(e - i, u) - c
		}
		if (b) {
			l -= q + a
		} else {
			l += y + a
		}
		if (e < u) {
			e = u
		} else {
			if (e + i > s) {
				e = s - i
			}
		}
		if (l < t) {
			l = t
		} else {
			if (l + q > r) {
				l = Math.max(v, r - q)
			}
		}
		if ($WH.Tooltip.iconVisible) {
			if (k >= e - 48 && k <= e && w >= l - 4 && w <= l + 48) {
				l -= 48 - (w - l)
			}
		}
		return $WH.g_createRect(e, l, i, q)
	},
	show: function (f, e, d, b, c) {
		if ($WH.Tooltip.disabled) {
			return
		}
		if (!d || d < 1) {
			d = 1
		}
		if (!b || b < 1) {
			b = 1
		}
		if (c) {
			e = '<span class="' + c + '">' + e + "</span>"
		}
		var a = $WH.ac(f);
		$WH.Tooltip.prepare();
		$WH.Tooltip.set(e);
		$WH.Tooltip.move(a.x, a.y, f.offsetWidth, f.offsetHeight, d, b)
	},
	showAtCursor: function (d, f, c, a, b) {
		if ($WH.Tooltip.disabled) {
			return
		}
		if (!c || c < 10) {
			c = 10
		}
		if (!a || a < 10) {
			a = 10
		}
		if (b) {
			f = '<span class="' + b + '">' + f + "</span>"
		}
		d = $WH.$E(d);
		var g = $WH.g_getCursorPos(d);
		$WH.Tooltip.prepare();
		$WH.Tooltip.set(f);
		$WH.Tooltip.move(g.x, g.y, 0, 0, c, a)
	},
	showAtXY: function (d, a, e, c, b) {
		if ($WH.Tooltip.disabled) {
			return
		}
		$WH.Tooltip.prepare();
		$WH.Tooltip.set(d);
		$WH.Tooltip.move(a, e, 0, 0, c, b)
	},
	cursorUpdate: function (b, a, d) {
		if ($WH.Tooltip.disabled || !$WH.Tooltip.tooltip) {
			return
		}
		b = $WH.$E(b);
		if (!a || a < 10) {
			a = 10
		}
		if (!d || d < 10) {
			d = 10
		}
		var c = $WH.g_getCursorPos(b);
		$WH.Tooltip.move(c.x, c.y, 0, 0, a, d)
	},
	hide: function () {
		if ($WH.Tooltip.tooltip) {
			$WH.Tooltip.tooltip.style.display = "none";
			$WH.Tooltip.tooltip.visibility = "hidden";
			$WH.Tooltip.tooltipTable.className = "";
			if ($WH.Browser.ie6) {
				$WH.Tooltip.iframe.style.display = "none"
			}
			$WH.Tooltip.setIcon(null);
			if ($WH.isset("Ads") && $WH.Tooltip.hiddenAd) {
				Ads.reveal(Tooltip.hiddenAd);
				$WH.Tooltip.hiddenAd = false
			}
		}
	},
	setIcon: function (a) {
		$WH.Tooltip.prepare();
		if (a) {
			$WH.Tooltip.icon.style.backgroundImage = "url(" + g_staticUrl + "/images/icons/medium/" + a.toLowerCase() + ".jpg)";
			$WH.Tooltip.icon.style.visibility = "visible"
		} else {
			$WH.Tooltip.icon.style.backgroundImage = "none";
			$WH.Tooltip.icon.style.visibility = "hidden"
		}
		$WH.Tooltip.iconVisible = a ? 1 : 0
	}
};
if ($WH.isset("$WowheadPower")) {
	$WowheadPower.init()
};

$WH.g_getProfileIcon = function(raceId, classId, gender, level, icon, size) {
	var raceXclass = {
		10: {6:1,3:1,8:1,2:1,5:1,4:1,9:1},                  // bloodelf
		11: {6:1,3:1,8:1,2:1,5:1,7:1,1:1},                  // draenei
		 3: {6:1,3:1,2:1,5:1,4:1,1:1},                      // dwarf
		 7: {6:1,8:1,4:1,9:1,1:1},                          // gnome
		 1: {6:1,8:1,2:1,5:1,4:1,9:1,1:1},                  // human
		 4: {6:1,11:1,3:1,5:1,4:1,1:1},                     // nightelf
		 2: {6:1,3:1,4:1,7:1,9:1,1:1},                      // orc
		 6: {6:1,11:1,3:1,7:1,1:1},                         // tauren
		 8: {6:1,3:1,8:1,5:1,4:1,7:1,1:1},                  // troll
		 5: {6:1,8:1,5:1,4:1,9:1,1:1}                       // scourge
	};

	if( icon) {
		return isNaN(icon) ? icon : '?profile=avatar' + (size ? '&size=' + size : '') + '&id=' + icon + (size == 'tiny' ? '.gif' : '.jpg');
	}

	if (!g_file_races[raceId] || !g_file_classes[classId] || !g_file_genders[gender] ||
	   !raceXclass[raceId] || !raceXclass[raceId][classId] || (classId == 6 && level < 55)) {
		return 'inv_misc_questionmark';
    }

	return 'chr_' + g_file_races[raceId] + '_' + g_file_genders[gender] + '_' + g_file_classes[classId] + '0' + (level > 59 ? (Math.floor((level - 60) / 10) + 2) : 1);
}
