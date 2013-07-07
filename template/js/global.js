var U_GROUP_TESTER = 1;
var U_GROUP_ADMIN = 2;
var U_GROUP_EDITOR = 4;
var U_GROUP_MOD = 8;
var U_GROUP_BUREAU = 16;
var U_GROUP_DEV = 32;
var U_GROUP_VIP = 64;
var U_GROUP_BLOGGER = 128;
var U_GROUP_PREMIUM = 256;
var U_GROUP_LOCALIZER = 512;
var U_GROUP_SALESAGENT = 1024;
var U_GROUP_SCREENSHOT = 2048;
var U_GROUP_VIDEO = 4096;
var U_GROUP_APIONLY = 8192;
var U_GROUP_PENDING = 16384;
var U_GROUP_STAFF = U_GROUP_ADMIN | U_GROUP_EDITOR | U_GROUP_MOD | U_GROUP_BUREAU | U_GROUP_DEV | U_GROUP_BLOGGER | U_GROUP_LOCALIZER | U_GROUP_SALESAGENT;
var U_GROUP_EMPLOYEE = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_DEV;
var U_GROUP_GREEN_TEXT = U_GROUP_MOD | U_GROUP_BUREAU | U_GROUP_DEV;
var U_GROUP_MODERATOR = U_GROUP_ADMIN | U_GROUP_MOD | U_GROUP_BUREAU;
var U_GROUP_COMMENTS_MODERATOR = U_GROUP_MODERATOR | U_GROUP_LOCALIZER;
var U_GROUP_PREMIUM_PERMISSIONS = U_GROUP_PREMIUM | U_GROUP_STAFF | U_GROUP_VIP;

function $(z) {
    if (arguments.length > 1) {
        var el = [];
        var len;

        for (var i = 0, len = arguments.length; i < len; ++i) {
            el.push($(arguments[i]));
        }

        return el;
    }

    if (typeof z == 'string') {
        z = ge(z);
    }

    return z;
}

function $E(e) {
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
        if (Browser.ie6789 && e._button) {
            if (e._button & 4) {
                e._button = 2; // Middle
            }
            else if (e._button & 2) {
                e._button = 3; // Right
            }
        }
        else {
            e._button = e.button + 1
        }
    }

    e._target = e.target ? e.target : e.srcElement;

    e._wheelDelta = e.wheelDelta ? e.wheelDelta : -e.detail;

    return e;
}

function $A(a) {
    var r = [];
    for (var i = 0, len = a.length; i < len; ++i) {
        r.push(a[i]);
    }

    return r;
}

function bindfunc() {
    args = $A(arguments);
    var __method = args.shift();
    var object   = args.shift();

    return function() {
        return __method.apply(object, args.concat($A(arguments)));
    };
}

if (!Function.prototype.bind) {
    Function.prototype.bind = function() {
        var
            __method = this,
            args     = $A(arguments),
            object   = args.shift();

        return function() {
            return __method.apply(object, args.concat($A(arguments)));
        };
    };
}

if (!String.prototype.ltrim) {
    String.prototype.ltrim = function() {
        return this.replace(/^\s*/, '');
    }
}

if (!String.prototype.rtrim) {
    String.prototype.rtrim = function() {
        return this.replace(/\s*$/, '');
    }
}

if (!String.prototype.trim) {
    String.prototype.trim = function() {
        return this.ltrim().rtrim();
    }
}

if (!String.prototype.removeAllWhitespace) {
    String.prototype.removeAllWhitespace = function() {
        return this.replace('/s+/g', '');
    }
}

function strcmp(a, b) {
    if (a == b) {
        return 0;
    }

    if (a == null) {
        return -1;
    }

    if (b == null) {
        return 1;
    }

    // Natural sorting for strings starting with a number
    var
        _a = parseFloat(a),
        _b = parseFloat(b);
    if (!isNaN(_a) && !isNaN(_b) && _a != _b) {
        return _a < _b ? -1 : 1;
    }

    // String comparison done with a native JS function that supports accents and non-latin characters
    if (typeof a == 'string' && typeof b == 'string') {
        return a.localeCompare(b);
    }

    // Other
    return a < b ? -1 : 1;
}

function trim(str) {
    return str.replace(/(^\s*|\s*$)/g, '');
}

function rtrim(z, y) {
    var a = z.length;

    while (--a > 0 && z.charAt(a) == y) { }

    z = z.substring(0, a + 1);

    if (z == y) {
        z = '';
    }

    return z;
}

function sprintf(z) {
    var i;
    for (i = 1, len = arguments.length; i < len; ++i) {
        z = z.replace('$' + i, arguments[i]);
    }

    return z;
}

// This version supports multiple occurences of the same token.
function sprintfa(z) {
    var i;
    for (i = 1, len = arguments.length; i < len; ++i) {
        z = z.replace(new RegExp('\\$' + i, 'g'), arguments[i]);
    }

    return z;
}

// This version works with an array object as the paremeter.
function sprintfo(z) {
    if (typeof z == 'object' && z.length) {
        var args = z;
        z = args[0];

        var i;
        for (i = 1; i < args.length; ++i) {
            z = z.replace('$' + i, args[i]);
        }

        return z;
    }
}

function str_replace(z, a, b) {
    while (z.indexOf(a) != -1) {
        z = z.replace(a, b);
    }

    return z;
}

// Encode URL for internal use (e.g. Ajax)
function urlencode(z) {
    z = encodeURIComponent(z);
    z = str_replace(z, '+', '%2B');
    return z;
}

// Encode URL for visible use (e.g. href)
function urlencode2(z) {
    z = encodeURIComponent(z);
    z = str_replace(z, '%20', '+');
    z = str_replace(z, '%3D', '=');

    return z;
}

// Group digits (e.g. 1234 --> 1,234)
function number_format(z) {
    x = ('' + parseFloat(z)).split('.');
    z = x[0];
    x = x.length > 1 ? "." + x[1] : '';

    if (z.length <= 3) {
        return z + x;
    }

    return number_format(z.substr(0, z.length - 3)) + ',' + z.substr(z.length - 3) + x;
}

function is_array(arr) {
    return !!(arr && arr.constructor == Array);
}

function in_array(arr, val, func, idx) {
    if (arr == null) {
        return -1;
    }

    if (func) {
        return in_arrayf(arr, val, func, idx);
    }

    for (var i = idx || 0, len = arr.length; i < len; ++i) {
        if (arr[i] == val) {
            return i;
        }
    }

    return -1;
}

function in_arrayf(arr, val, func, idx) {
    for (var i = idx || 0, len = arr.length; i < len; ++i) {
        if (func(arr[i]) == val) {
            return i;
        }
    }
    return -1;
}

function rs() {
    var e = rs.random;
    var b = '';
    for (var a = 0; a < 16; a++) {
        var d = Math.floor(Math.random() * e.length);
        if (a == 0 && d < 11) {
            d += 10;
        }
        b += e.substring(d, d + 1);
    }
    return b;
}
rs.random = "0123456789abcdefghiklmnopqrstuvwxyz";

function isset(name) {
    return typeof window[name] != "undefined";
}

function array_filter(a, f) {
    var res = [];
    for (var i = 0, len = a.length; i < len; ++i) {
        if (f(a[i])) {
            res.push(a[i]);
        }
    }
    return res;
}

function array_walk(a, f, ud) {
    var res;
    for (var i = 0, len = a.length; i < len; ++i) {
        res = f(a[i], ud, a, i);
        if (res != null) {
            a[i] = res;
        }
    }
}

function array_apply(a, f, ud) {
    var res;
    for (var i = 0, len = a.length; i < len; ++i) {
        f(a[i], ud, a, i);
    }
}

// Get element
function ge(z) {
    if(typeof z != 'string') {
        return z;
    }

    return document.getElementById(z);
}

// Get elements by tag name
function gE(z, y) {
    return z.getElementsByTagName(y);
}

// Create element
function ce(z, p, c) {
    var a = document.createElement(z);

    if (p) {
        cOr(a, p);
    }

    if (c) {
        ae(a, c);
    }

    return a;
}

// Delete element
function de(z) {
    if (!z || !z.parentNode) {
        return;
    }

    z.parentNode.removeChild(z);
}

// Append element
function ae(z, y) {
    if (is_array(y)) {
        array_apply(y, z.appendChild.bind(z));

        return y;
    }
    else {
        return z.appendChild(y);
    }
}

// Prepend element
function aef(z, y) {
    return z.insertBefore(y, z.firstChild);
}

// Empty element
function ee(z, y) {
    if (!y) {
        y = 0;
    }
    while (z.childNodes[y]) {
        z.removeChild(z.childNodes[y]);
    }
}

// Create text element
function ct(z) {
    return document.createTextNode(z);
}

// Set element's text
function st(z, y) {
    if (z.firstChild && z.firstChild.nodeType == 3) {
        z.firstChild.nodeValue = y;
    }
    else {
        aef(z, ct(y));
    }
}

// Add "white-space: nowrap" style to element
function nw(z) {
    z.style.whiteSpace = "nowrap";
}

// Return false
function rf() {
    return false;
}

// Return false only if no control key is pressed
function rf2(e) {
    e = $E(e);

    if (e.ctrlKey || e.shiftKey || e.altKey || e.metaKey) {
        return;
    }

    return false;
}

// Remove focus from current element
function tb() {
    this.blur();
}

function ac(el, fixedPos) {
    var
        x = 0,
        y = 0,
        el2;

    while (el) {
        x += el.offsetLeft;
        y += el.offsetTop;

        el2 = el.parentNode;
        while (el2 && el2 != el.offsetParent && el2.offsetParent) { // Considers scroll position for elements inside an 'overflow: auto' div.
            if (el2.scrollLeft || el2.scrollTop) {
                x -= (el2.scrollLeft | 0);
                y -= (el2.scrollTop  | 0);
                break;
            }

            el2 = el2.parentNode;
        }

        el = el.offsetParent;
    }

    if (isset('Lightbox') && Lightbox.isVisible()) { // Assumes that calls made while the Lightbox is visible are on 'position: fixed' elements.
        fixedPos = true;
    }

    if (fixedPos) {
        var scroll = g_getScroll();
        x += scroll.x;
        y += scroll.y
    }

    var result = [x, y];
    result.x = x;
    result.y = y;
    return result;
}

// Attach event
function aE(z, y, x) {
    if (z.addEventListener) {
        z.addEventListener(y, x, false);
    }
    else if (z.attachEvent) {
        z.attachEvent('on' + y, x);
    }
}

// Detach event
function dE(z, y, x) {
    if (z.removeEventListener) {
        z.removeEventListener(y, x, false);
    }
    else if (z.detachEvent) {
        z.detachEvent('on' + y, x);
    }
}

// Stop propagation
function sp(z) {
    if (!z) {
        z = event;
    }

    if (Browser.ie6789) {
        z.cancelBubble = true
    }
    else {
        z.stopPropagation();
    }
}

// Set cookie
function sc(z, y, x, w, v) {
    var a = new Date();
    var b = z + "=" + escape(x) + "; ";

    a.setDate(a.getDate() + y);
    b += "expires=" + a.toUTCString() + "; ";

    if (w) {
        b += "path=" + w + "; ";
    }

    if (v) {
        b += "domain=" + v + "; ";
    }

    document.cookie = b;
    gc(z);
    gc.C[z] = x;
}

// Delete cookie
function dc(z) {
    sc(z, -1);
    gc.C[z] = null;
}

// Get all cookies (return value is cached)
function gc(z) {
    if (gc.I == null) { // Initialize cookie table
        var words = unescape(document.cookie).split("; ");

        gc.C = {};
        for (var i = 0, len = words.length; i < len; ++i) {
            var
                pos = words[i].indexOf("="),
                name,
                value;

            if (pos != -1) {
                name  = words[i].substr(0, pos);
                value = words[i].substr(pos + 1);
            }
            else {
                name  = words[i];
                value = "";
            }

            gc.C[name] = value;
        }

        gc.I = 1;
    }

    if (!z) {
        return gc.C;
    }
    else {
        return gc.C[z];
    }
}

// Prevent element from being selected/dragged (IE only)
function ns(a) {
    if (Browser.ie6789) {
        a.onfocus = tb;
        a.onmousedown = a.onselectstart = a.ondragstart = rf;
    }
}

// Empty object
function eO(z) {
    for (var p in z) {
        delete z[p];
    }
}

// Duplicate object
function dO(s) {
    function f(){};
    f.prototype = s;
    return new f;
}

// Copy object
function cO(d, s) {
    for (var p in s) {
        if (s[p] !== null && typeof s[p] == "object" && s[p].length) {
            d[p] = s[p].slice(0);
        }
        else {
            d[p] = s[p];
        }
    }

    return d;
}

// Copy object (recursive)
function cOr(d, s) {
    for (var p in s) {
        if (typeof s[p] == 'object') {
            if (s[p].length) {
                d[p] = s[p].slice(0);
            }
            else {
                if (!d[p]) {
                    d[p] = {};
                }

                cOr(d[p], s[p]);
            }
        }
        else {
            d[p] = s[p];
        }
    }

    return d;
}

Browser = {
    ie:      !!(window.attachEvent && !window.opera),
    opera:   !!window.opera,
    safari:  navigator.userAgent.indexOf('Safari') != -1,
    firefox: navigator.userAgent.indexOf('Firefox') != -1,
    chrome:  navigator.userAgent.indexOf('Chrome') != -1
};

Browser.ie9   = Browser.ie && navigator.userAgent.indexOf('MSIE 9.0') != -1;
Browser.ie8   = Browser.ie && navigator.userAgent.indexOf('MSIE 8.0') != -1&& !Browser.ie9;
Browser.ie7   = Browser.ie && navigator.userAgent.indexOf('MSIE 7.0') != -1 && !Browser.ie8;
Browser.ie6   = Browser.ie && navigator.userAgent.indexOf('MSIE 6.0') != -1 && !Browser.ie7;

Browser.ie67   = Browser.ie6   || Browser.ie7;
Browser.ie678  = Browser.ie67  || Browser.ie8;
Browser.ie6789 = Browser.ie678 || Browser.ie9;

navigator.userAgent.match(/Gecko\/([0-9]+)/);
Browser.geckoVersion = parseInt(RegExp.$1) | 0;

OS = {
    windows: navigator.appVersion.indexOf('Windows')   != -1,
    mac:     navigator.appVersion.indexOf('Macintosh') != -1,
    linux:   navigator.appVersion.indexOf('Linux')     != -1
};

var DomContentLoaded = new function() {
    var _now = [];
    var _del = [];

    this.now = function() {
        array_apply(_now, function(f) {
            f();
        });
    };

    this.delayed = function() {
        array_apply(_del, function(f) {
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

function g_getWindowSize() {
    var
        width  = 0,
        height = 0;

    if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
        // IE 6+ in 'standards compliant mode'
        width  = document.documentElement.clientWidth;
        height = document.documentElement.clientHeight;
    }
    else if (document.body && (document.body.clientWidth || document.body.clientHeight)) {
        // IE 4 compatible
        width  = document.body.clientWidth;
        height = document.body.clientHeight;
    }
    else if (typeof window.innerWidth == 'number') { // Non-IE
        width  = window.innerWidth;
        height = window.innerHeight;
    }

    return {
        w: width,
        h: height
    };
}

function g_getScroll() {
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
}

function g_getCursorPos(e) {
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
        var scroll = g_getScroll();

        x = e.clientX + scroll.x;
        y = e.clientY + scroll.y
    }

    return {
        x: x,
        y: y
    };
}

function g_scrollTo(n, p) {
    var
        _,
        windowSize = g_getWindowSize(),
        scroll = g_getScroll(),
        bcw = windowSize.w,
        bch = windowSize.h,
        bsl = scroll.x,
        bst = scroll.y;

    n = $(n);

    // Padding
    if (p == null) {
        p = [];
    }
    else if (typeof p == 'number') {
        p = [p];
    }

    _ = p.length;
    if (_ == 0) {
        p[0] = p[1] = p[2] = p[3] = 0;
    }
    else if (_ == 1) {
        p[1] = p[2] = p[3] = p[0];
    }
    else if (_ == 2) {
        p[2] = p[0];
        p[3] = p[1];
    }
    else if (_ == 3) {
        p[3] = p[1];
    }

    _ = ac(n);

    var
        nl = _[0] - p[3],
        nt = _[1] - p[0],
        nr = _[0] + n.offsetWidth + p[1],
        nb = _[1] + n.offsetHeight + p[2];

    if (nr - nl > bcw || nl < bsl) {
        bsl = nl;
    }
    else if (nr - bcw > bsl) {
        bsl = nr - bcw;
    }

    if (nb - nt > bch || nt < bst) {
        bst = nt;
    }
    else if (nb - bch > bst) {
        bst = nb - bch;
    }

    scrollTo(bsl, bst);
}

function g_addCss(b) {
	var c = ce("style");
	c.type = "text/css";
	if (c.styleSheet) {
		c.styleSheet.cssText = b
	} else {
		ae(c, ct(b))
	}
	var a = gE(document, "head")[0];
	ae(a, c)
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
				if (Browser.ie) {
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
	evt = $E(evt);

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
		aE(document, "mousewheel", g_enableScroll.F);
		aE(window, "DOMMouseScroll", g_enableScroll.F)
	} else {
		dE(document, "mousewheel", g_enableScroll.F);
		dE(window, "DOMMouseScroll", g_enableScroll.F)
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
function g_getGets() {
	if (g_getGets.C != null) {
		return g_getGets.C
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
	g_getGets.C = e;
	return e
}
function g_createRect(d, c, a, b) {
	return {
		l: d,
		t: c,
		r: d + a,
		b: c + b
	}
}
function g_intersectRect(d, c) {
	return ! (d.l >= c.r || c.l >= d.r || d.t >= c.b || c.t >= d.b)
}
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
		return strcmp(b[e][c], b[d][c])
	}: function(e, d) {
		return strcmp(b[e], b[d])
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
	var ta = ce('textarea');
	ta.innerHTML = str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
	str = ta.value;

	str = str_replace(str, ' / ', '-');
	str = str_replace(str, "'", '');

	if (profile) {
		str = str_replace(str, '(', '');
		str = str_replace(str, ')', '');
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

	str = trim(str);
	if (allowLocales) {
		str = str_replace(str, ' ', '-');
	}
	else {
		str = str.replace(/[^a-z0-9]/ig, '-');
	}

	str = str_replace(str, '--', '-');
	str = str_replace(str, '--', '-');
	str = rtrim(str, '-');
	str = str.replace(/[A-Z]/g, function(x) {
        return x.toLowerCase();
    });

	return str;
}

function g_getLocale(a) {
	if (a && g_locale.id == 25) {
		return 0
	}
	return g_locale.id
}
function g_createReverseLookupJson(b) {
	var c = {};
	for (var a in b) {
		c[b[a]] = a
	}
	return c
}
function g_isUsernameValid(a) {
	return (a.match(/[^a-z0-9]/i) == null && a.length >= 4 && a.length <= 16)
}
function g_createHeader(c) {
	var k = ce("dl"),
	p = (c == 5);
	for (var j = 0, l = mn_path.length; j < l; ++j) {
		var f = ce("dt");
		var q = ce("a");
		var m = ce("ins");
		var g = ce("big");
		var e = ce("span");
		var o = mn_path[j][0];
		var h = (o == c);
		var d = (!h && mn_path[j][3]);
		if (p && o == 5) {
			d = true;
			mn_path[j][3] = mn_profiles
		}
		if (d) {
			q.menu = mn_path[j][3];
			q.onmouseover = Menu.show;
			q.onmouseout = Menu.hide
		} else {
			q.onmouseover = Menu._hide
		}
		if (mn_path[j][2]) {
			q.href = mn_path[j][2]
		} else {
			q.href = "javascript:;";
			ns(q);
			q.style.cursor = "default"
		}
		if (h) {
			q.className = "selected"
		}
		ae(g, ct(mn_path[j][1].charAt(0)));
		ae(m, g);
		ae(m, ct(mn_path[j][1].substr(1)));
		ae(q, m);
		ae(q, e);
		ae(f, q);
		ae(k, f)
	}
	ae(ge("toptabs-generic"), k);
	var b = ge("topbar-generic");
	if (c != null && c >= 0 && c < mn_path.length) {
		c = parseInt(c);
        switch (c) {
            case 0: // Database
                Menu.addButtons(b, [
                    [0, LANG.menu_browse, null, mn_database],       // Browse
                    [1, mn_tools[7][1], null, mn_tools[7][3]],      // Utilities
                    [2, mn_tools[7][3][1][1], mn_tools[7][3][1][2]] // Random Page
                ]);
                break;
            case 1:
                Menu.addButtons(b, [
                    [0, LANG.calculators, null, mn_tools.slice(0,4)], // Calculators
                    [1, mn_tools[4][1], mn_tools[4][2]],              // Maps
                    [2, mn_tools[7][1], null, mn_tools[7][3]],        // Utilities
                    [3, mn_tools[6][1], null, mn_tools[6][3]]         // Guides
                ]);
                break;
            case 2:
                Menu.addButtons(b, Menu.explode(mn_more));
                break;
            case 3:
                Menu.addButtons(b, Menu.explode(mn_forums));
                break;
            case 5:
                pr_initTopBarSearch();
                break
        }
	} else {
		ae(b, ct(String.fromCharCode(160)))
	}
}
function g_updateHeader(a) {
	ee(ge("toptabs-generic"));
	ee(ge("topbar-generic"));
	g_createHeader(a)
}
function g_initHeader(a) {
	g_createHeader(a);
	var d = ge("livesearch-generic");
	var b = d.previousSibling;
	var c = d.parentNode;
	ns(b);
	b.onclick = function() {
		this.parentNode.onsubmit()
	};
	if (Browser.ie) {
		setTimeout(function() {
			d.value = ""
		},
		1)
	}
	if (d.value == "") {
		d.className = "search-database"
	}
	d.onmouseover = function() {
		if (trim(this.value) != "") {
			this.className = ""
		}
	};
	d.onfocus = function() {
		this.className = ""
	};
	d.onblur = function() {
		if (trim(this.value) == "") {
			this.className = "search-database";
			this.value = ""
		}
	};
	c.onsubmit = function() {
		var e = this.elements[0].value;
		if (trim(e) == "") {
			return false
		}
		this.submit()
	}
}
function g_initHeaderMenus() {
	var c = ge("toptabs-menu-user");
	if (c) {
		c.menu = [[0, LANG.userpage, "?user=" + g_user.name], [0, LANG.settings, "?account"], [0, LANG.signout, "?account=signout"]];
		if (location.href.match(new RegExp("/?user=" + g_user.name + "$", "i"))) {
			c.menu[0].checked = 1
		} else {
			if (location.href.indexOf("?account") != -1) {
				c.menu[1].checked = 1
			}
		}
		c.onmouseover = Menu.show;
		c.onmouseout = Menu.hide;
		c.href = "?user=" + g_user.name
	}
	c = ge("toptabs-menu-profiles");
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
		c.onmouseover = Menu.show;
		c.onmouseout = Menu.hide;
		c.href = "?user=" + g_user.name + (g_user.profiles ? "#profiles": (g_user.characters ? "#characters": ""))
	}
	c = ge("toptabs-menu-language");
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
		//c.menu = [[0, "Deutsch", (g_locale.id != 3 ? d.replace(g, "de") : null)], [0, "English", (g_locale.id != 0 ? d.replace(g, "www") : null)], [0, "Espa" + String.fromCharCode(241) + "ol", (g_locale.id != 6 ? d.replace(g, "es") : null)], [0, "Fran" + String.fromCharCode(231) + "ais", (g_locale.id != 2 ? d.replace(g, "fr") : null)], [0, String.fromCharCode(1056, 1091, 1089, 1089, 1082, 1080, 1081), (g_locale.id != 8 ? d.replace(g, "ru") : null)]];

        var rel = d.match(/()\?((item|quest|spell|achievement|npc|object)=([0-9]+))/);
        rel = (rel && rel[2]) ? rel[2] : "";

		c.menu = [
			[0, "Deutsch", (g_locale.id != 3 ? "?locale=3" : null), , {rel: rel + " domain=de"}],
			[0, "English", (g_locale.id != 0 ? "?locale=0" : null), , {rel: rel + " domain=en"}],
			[0, "Espa" + String.fromCharCode(241) + "ol", (g_locale.id != 6 ? "?locale=6" : null), , {rel: rel + " domain=es"}],
			[0, "Fran" + String.fromCharCode(231) + "ais", (g_locale.id != 2 ? "?locale=2" : null), , {rel: rel + " domain=fr"}],
			[0, String.fromCharCode(1056, 1091, 1089, 1089, 1082, 1080, 1081), (g_locale.id != 8 ? "?locale=8" : null), , {rel: rel + " domain=ru"}]
        ];
		c.menu.rightAligned = 1;
		if (g_locale.id != 25) {
			c.menu[{
				0 : 1,
				2 : 3,
				3 : 0,
				6 : 2,
				8 : 4
			} [g_locale.id]].checked = 1
		}
		c.onmouseover = Menu.show;
		c.onmouseout = Menu.hide
	}
}
function g_initPath(q, f) {
	var h = mn_path,
	c = null,
	k = null,
	p = 0,
	l = ge("main-precontents"),
	o = ce("div");
	ee(l);
	if (g_initPath.lastIt) {
		g_initPath.lastIt.checked = null
	}
	o.className = "path";
	if (f != null) {
		var m = ce("div");
		m.className = "path-right";
		var r = ce("a");
		r.href = "javascript:;";
		r.id = "fi_toggle";
		ns(r);
		r.onclick = fi_toggle;
		if (f) {
			r.className = "disclosure-on";
			ae(r, ct(LANG.fihide))
		} else {
			r.className = "disclosure-off";
			ae(r, ct(LANG.fishow))
		}
		ae(m, r);
		ae(l, m)
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
		r = ce("a");
		b = ce("span");
		if (h[2]) {
			r.href = h[2]
		} else {
			r.href = "javascript:;";
			ns(r);
			r.style.textDecoration = "none";
			r.style.color = "white";
			r.style.cursor = "default"
		}
		if (g < q.length - 1 && h[3]) {
			b.className = "menuarrow"
		}
		//ae(r, ct(h[4] == null ? h[1] : h[4]));
		ae(r, ct(h[1]));
		if (g == 0) {
			r.menu = mn_path
		} else {
			r.menu = c[3]
		}
		r.onmouseover = Menu.show;
		r.onmouseout = Menu.hide;
		ae(b, r);
		ae(o, b);
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
			r = ce("a");
			b = ce("span");
			r.href = "javascript:;";
			ns(r);
			r.style.textDecoration = "none";
			r.style.paddingRight = "16px";
			r.style.color = "white";
			r.style.cursor = "default";
			ae(r, ct("..."));
			r.menu = c[3];
			r.onmouseover = Menu.show;
			r.onmouseout = Menu.hide;
			ae(b, r);
			ae(o, b)
		}
	}
	var m = ce("div");
	m.className = "clear";
	ae(o, m);
	ae(l, o);
	g_initPath.lastIt = c
}
function g_addTooltip(b, c, a) {
	if (!a && c.indexOf("<table>") == -1) {
		a = "q"
	}
	b.onmouseover = function(d) {
		Tooltip.showAtCursor(d, c, 0, 0, a)
	};
	b.onmousemove = Tooltip.cursorUpdate;
	b.onmouseout = Tooltip.hide
}
function g_addStaticTooltip(b, c, a) {
	if (!a && c.indexOf("<table>") == -1) {
		a = "q"
	}
	b.onmouseover = function(d) {
		Tooltip.show(b, c, 0, 0, a)
	};
	b.onmouseout = Tooltip.hide
}
function g_formatTimeElapsed(e) {
	function c(m, l, i) {
		if (i && LANG.timeunitsab[l] == "") {
			i = 0
		}
		if (i) {
			return m + " " + LANG.timeunitsab[l]
		} else {
			return m + " " + (m == 1 ? LANG.timeunitssg[l] : LANG.timeunitspl[l])
		}
	}
	var g = [31557600, 2629800, 604800, 86400, 3600, 60, 1];
	var a = [1, 3, 3, -1, 5, -1, -1];
	e = Math.max(e, 1);
	for (var f = 3, h = g.length; f < h; ++f) {
		if (e >= g[f]) {
			var d = f;
			var k = Math.floor(e / g[d]);
			if (a[d] != -1) {
				var b = a[d];
				e %= g[d];
				var j = Math.floor(e / g[b]);
				if (j > 0) {
					return c(k, d, 1) + " " + c(j, b, 1)
				}
			}
			return c(k, d, 0)
		}
	}
	return "(n/a)"
}
function g_formatDate(c, j, a, d, k) {
	var f = new Date();
	var b = new Date();
	b.setTime(f.getTime() - (1000 * j));
	var e;
	var g = new Date(b.getYear(), b.getMonth(), b.getDate());
	var l = new Date(f.getYear(), f.getMonth(), f.getDate());
	var i = (l.getTime() - g.getTime());
	i /= 1000;
	i /= 86400;
	i = Math.round(i);
	if (j >= 2592000) {
		e = LANG.date_on + g_formatDateSimple(a, d)
	} else {
		if (i > 1) {
			e = sprintf(LANG.ddaysago, i);
			if (c) {
				var h = new Date();
				h.setTime(a.getTime() + (g_localTime - g_serverTime));
				c.className += " tip";
				c.title = h.toLocaleString()
			}
		} else {
			if (j >= 43200) {
				if (f.getDay() == b.getDay()) {
					e = LANG.today
				} else {
					e = LANG.yesterday
				}
				e = g_formatTimeSimple(b, e);
				if (c) {
					var h = new Date();
					h.setTime(a.getTime() + (g_localTime - g_serverTime));
					c.className += " tip";
					c.title = h.toLocaleString()
				}
			} else {
				var e = sprintf(LANG.date_ago, g_formatTimeElapsed(j));
				if (c) {
					var h = new Date();
					h.setTime(a.getTime() + (g_localTime - g_serverTime));
					c.className += " tip";
					c.title = h.toLocaleString()
				}
			}
		}
	}
	if (k == 1) {
		e = e.substr(0, 1).toUpperCase() + e.substr(1)
	}
	if (c) {
		ae(c, ct(e))
	} else {
		return e
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
        b += sprintf(LANG.date_simple, __twoDigits(day), __twoDigits(month), year);
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
	var e = ce("span");
	for (var c = -1; c <= 1; ++c) {
		for (var b = -1; b <= 1; ++b) {
			var g = ce("div");
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
			ae(e, g)
		}
	}
	e.style.position = "relative";
	e.className = "glow" + (h != null ? " " + h: "");
	var f = ce("span");
	f.style.visibility = "hidden";
	ae(f, ct(a));
	ae(e, f);
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
		d = ce("a");
		d.href = "javascript:;"
	} else {
		d = ce("span")
	}
	d.className = "progressbar";
	if (c.text || c.hoverText) {
		e = ce("div");
		e.className = "progressbar-text";
		if (c.text) {
			var a = ce("del");
			ae(a, ct(c.text));
			ae(e, a)
		}
		if (c.hoverText) {
			var b = ce("ins");
			ae(b, ct(c.hoverText));
			ae(e, b)
		}
		ae(d, e)
	}
	e = ce("div");
	e.className = "progressbar-" + c.color;
	e.style.width = c.width + "%";
	ae(e, ct(String.fromCharCode(160)));
	ae(d, e);
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
function g_setRatingLevel(f, e, b, c) {
	var d = prompt(sprintf(LANG.prompt_ratinglevel, 1, 80), e);
	if (d != null) {
		d |= 0;
		if (d != e && d >= 1 && d <= 80) {
			e = d;
			var a = g_convertRatingToPercent(e, b, c);
			a = (Math.round(a * 100) / 100);
			if (b != 12 && b != 37) {
				a += "%"
			}
			f.innerHTML = sprintf(LANG.tooltip_combatrating, a, e);
			f.onclick = g_setRatingLevel.bind(0, f, e, b, c)
		}
	}
}

function g_convertRatingToPercent(level, rating, value, classs) {
    var ratingBases = g_convertRatingToPercent.RB;

    if (level < 0) {
        level = 1;
    }
    else {
        if (level > 80) {
            level = 80;
        }
    }

    // Patch 2.4.3: Defense Skill, Dodge Rating, Parry Rating, Block Rating
    if ((rating == 14 || rating == 12 || rating == 15) && level < 34) {
        level = 34;
    }

    // Patch 3.1: Melee Haste for Death Knights, Druids, Paladins, and Shamans
    if ((rating == 28 || rating == 36) && (classs == 2 || classs == 6 || classs == 7 || classs == 11)) {
        ratingBases[rating] /= 1.3;
    }

    if (value < 0) {
        value = 0
    }

    var percent;
    if (!ratingBases || ratingBases[rating] == null) {
        percent = 0
    }
    else {
        var H;

        if (level > 70) {
            H = (82 / 52) * Math.pow((131 / 63), ((level - 70) / 10));
        }
        else if (level > 60) {
            H = (82 / (262 - 3 * level));
        }
        else if (level > 10) {
            H = ((level - 8) / 52);
        }
        else {
            H = 2 / 52;
        }

        percent = value / ratingBases[rating] / H;
    }

    return percent;
}

g_convertRatingToPercent.RB = {
    12: 1.5,
    13: 12,
    14: 15,
    15: 5,
    16: 10,
    17: 10,
    18: 8,
    19: 14,
    20: 14,
    21: 14,
    22: 10,
    23: 10,
    24: 0,
    25: 0,
    26: 0,
    27: 0,
    28: 10,
    29: 10,
    30: 10,
    31: 10,
    32: 14,
    33: 0,
    34: 0,
    35: 25,
    36: 10,
    37: 2.5,
    44: 3.756097412109376
};

var g_statToJson = {
    // Converts a numeric stat id into a json property
     1: 'health',
     2: 'mana',                                             // on idx 0 in 5.0
     3: 'agi',
     4: 'str',
     5: 'int',
     6: 'spi',
     7: 'sta',
     8: 'energy',
     9: 'rage',
    10: 'focus',
    12: 'defrtng',
    13: 'dodgertng',
    14: 'parryrtng',
    15: 'blockrtng',
    16: 'mlehitrtng',
    17: 'rgdhitrtng',
    18: 'splhitrtng',
    19: 'mlecritstrkrtng',
    20: 'rgdcritstrkrtng',
    21: 'splcritstrkrtng',
    22: '_mlehitrtng',
    23: '_rgdhitrtng',
    24: '_splhitrtng',
    25: '_mlecritstrkrtng',
    26: '_rgdcritstrkrtng',
    27: '_splcritstrkrtng',
    28: 'mlehastertng',
    29: 'rgdhastertng',
    30: 'splhastertng',
    31: 'hitrtng',
    32: 'critstrkrtng',
    33: '_hitrtng',
    34: '_critstrkrtng',
    35: 'resirtng',
    36: 'hastertng',
    37: 'exprtng',
    38: 'atkpwr',
    39: 'rgdatkpwr',
    41: 'splheal',
    42: 'spldmg',
    43: 'manargn',
    44: 'armorpenrtng',
    45: 'splpwr',
    46: 'healthrgn',
    47: 'splpen',
    49: 'mastrtng',
    50: 'armor',
    51: 'firres',
    52: 'frores',
    53: 'holres',
    54: 'shares',
    55: 'natres',
    56: 'arcres'
};

function g_convertScalingFactor(c, b, g, d, j) {
	var f = g_convertScalingFactor.SV;
	var e = g_convertScalingFactor.SD;
	var i = {},
	h = f[c],
	a = e[g];
	if (!a || !(d >= 0 && d <= 9)) {
		i.v = h[b]
	} else {
		i.n = g_statToJson[a[d]];
		i.s = a[d];
		i.v = Math.floor(h[b] * a[d + 10] / 10000)
	}
	return (j ? i: i.v)
}
g_convertScalingFactor.SV = {
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
g_convertScalingFactor.SD = {
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
	311 : [-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
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
function g_setJsonItemLevel(t, a) {
	if (!t.scadist || !t.scaflags) {
		return
	}
	t.bonuses = t.bonuses || {};
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
		if (l & t.scaflags) {
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
			var q = g_convertScalingFactor(a, c, t.scadist, h, 1);
			if (q.n) {
				t[q.n] = q.v
			}
			t.bonuses[q.s] = q.v
		}
	}
	if (r >= 0) {
		t.armor = g_convertScalingFactor(a, r)
	}
	if (k >= 0) {
		var j = (t.scaflags & b ? 0.2 : 0.3),
		m = (t.mledps ? "mle": "rgd");
        t.speed /= t.speed > 1000 ? 1000 : 1;               // summary expects speed in sec; different version of script is different
		t.dps = t[m + "dps"] = g_convertScalingFactor(a, k);
		t.dmgmin = t[m + "dmgmin"] = Math.floor(t.dps * t.speed * (1 - j));
		t.dmgmax = t[m + "dmgmax"] = Math.floor(t.dps * t.speed * (1 + j))

        if (t.feratkpwr) {                                  // yes thats custom too..
            t.feratkpwr = Math.max(0, Math.floor((t.dps - 54.8) * 14));
        }
	}
	if (p >= 0) {
		t.splpwr = t.bonuses[45] = g_convertScalingFactor(a, p)
	}
	if (t.gearscore != null) {
		if (t._gearscore == null) {
			t._gearscore = t.gearscore
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
		t.gearscore = (t._gearscore * n) / 1.8
	}
}
function g_setTooltipItemLevel(a, g) {
	var d = typeof a;
	if (d == "number") {
		if (isset("g_items") && g_items[a] && g_items[a]["tooltip_" + g_locale.name]) {
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
		g_setJsonItemLevel(e, g);
		a = a.replace(/(<!--asc(\d+)-->)([^<]+)/, function(j, h, i) {
			d = i;
			if (g < 40 && (i == 3 || i == 4)) {--d
			}
			return h + g_itemset_types[d]
		});
		a = a.replace(/(<!--dmg-->)\d+(\D+)\d+/, function(j, h, i) {
			return h + e.dmgmin + i + e.dmgmax
		});
		a = a.replace(/(<!--dps-->\D*?)(\d+\.\d)/, function(i, h) {
			return h + e.dps.toFixed(1)
		});
		a = a.replace(/<span class="c11"><!--fap-->(\D*?)(\d+)(\D*?)<\/span>(<br \/>)?/i, function(l, h, i, m, j) {
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
		a = a.replace(/(<!--amr-->)\d+/, function(i, h) {
			return h + e.armor
		});
		a = a.replace(/<span><!--stat(\d+)-->[-+]\d+(\D*?)<\/span>(<!--e-->)?(<!--ps-->)?(<br ?\/?>)?/gi, function(l, i, h, o, p, j) {
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
		a = a.replace(/<span class="q2">(.*?)<!--rtg(\d+)-->\d+(.*?)<\/span>(<br \/>)?/gi, function(h, k, m, p, l, i, q) {
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
	a = a.replace(/(<!--rtg%(\d+)-->)([\.0-9]+)/g, function(j, h, k, i) {
		d = a.match(new RegExp("<!--rtg" + k + "-->(\\d+)"));
		if (!d) {
			return j
		}
		return h + Math.round(g_convertRatingToPercent(g, k, d[1]) * 100) / 100
	});
	a = a.replace(/(<!--\?\d+:\d+:\d+:)\d+((:\d+:\d+)?-->)/, "$1" + g + "$2");
	a = a.replace(/<!--lvl-->\d+/g, "<!--lvl-->" + g);
	return a
}
function g_enhanceTooltip(a, c) {
	var b = typeof a;
	if (b == "number") {
		if (isset("g_items") && g_items[a] && g_items[a]["tooltip_" + g_locale.name]) {
			a = g_items[a]["tooltip_" + g_locale.name]
		} else {
			return a
		}
	} else {
		if (b != "string") {
			return a
		}
	}
	if (c) {
		a = a.replace(/<span class="q2"><!--addamr(\d+)--><span>.*?<\/span><\/span>/i, function(d, e) {
			return '<span class="q2 tip" onmouseover="Tooltip.showAtCursor(event, sprintf(LANG.tooltip_armorbonus, ' + e + '), 0, 0, \'q\')" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">' + d + "</span>"
		});
		a = a.replace(/\(([^\)]*?<!--lvl-->[^\(]*?)\)/gi, function(e, d) {
			return '(<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="g_staticTooltipLevelClick(this)">' + d + "</a>)"
		})
	}
	return a
}

function g_staticTooltipLevelClick(div, level) {
    while (div.className.indexOf('tooltip') == -1) {
        div = div.parentNode;
    }

    var _ = div.innerHTML;

    // Retrieve tooltip's global information
    _ = _.match(/<!--\?(\d+):(\d+):(\d+):(\d+)/);
    if (!_) {
        return; // Error
    }

    var
        itemId = parseInt(_[1]),
        minLevel = parseInt(_[2]),
        maxLevel = parseInt(_[3]),
        curLevel = parseInt(_[4]);

    if (!level) { // Prompt for level
        level = prompt(sprintf(LANG.prompt_ratinglevel, minLevel, maxLevel), curLevel);
    }

    level = parseInt(level);
    if (isNaN(level)) {
        return; // Invalid level
    }

    if (level == curLevel || level < minLevel || level > maxLevel) {
        return; // Level out of bound or no tooltip changes required
    }

    _ = g_setTooltipItemLevel(g_items[e]['tooltip_' + g_locale.name], level);
    _ = g_enhanceTooltip(_, true);

    div.innerHTML = '<table><tr><td>' + _ + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
    Tooltip.fixSafe(div, 1, 1);
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
		e += '<span class="money' + (c < 0 ? "horde": "alliance") + ' tip" onmouseover="Listview.funcBox.moneyHonorOver(event)" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">' + g_numberFormat(Math.abs(c)) + "</span>"
	}
	if (b !== undefined && b !== null && b > 0) {
		if (e.length > 0) {
			e += " "
		}
		e += '<span class="moneyarena tip" onmouseover="Listview.funcBox.moneyArenaOver(event)" onmousemove="Tooltip.cursorUpdate(event)" onmouseout="Tooltip.hide()">' + g_numberFormat(b) + "</span>"
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
	ge("wrapper").className = "nosidebar";
	var a = ge("topbar-expand");
	if (a) {
		de(a)
	}
	a = ge("sidebar");
	if (a) {
		de(a)
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
function g_getLocaleFromDomain(a) {
	var c = g_getLocaleFromDomain.L;
	if (a) {
		var b = a.indexOf(".");
		if (b != -1) {
			a = a.substring(0, b)
		}
	}
	return (c[a] ? c[a] : 0)
}
g_getLocaleFromDomain.L = {
   www: 0,
	fr: 2,
	de: 3,
	es: 6,
	ru: 8
};
function g_getDomainFromLocale(a) {
	var b;
	if (g_getDomainFromLocale.L) {
		b = g_getDomainFromLocale.L
	} else {
		b = g_getDomainFromLocale.L = g_createReverseLookupJson(g_getLocaleFromDomain.L)
	}
	return (b[a] ? b[a] : "www")
}
function g_getIdFromTypeName(a) {
	var b = g_getIdFromTypeName.L;
	return (b[a] ? b[a] : -1)
}
g_getIdFromTypeName.L = {
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
    event: 12,
    "class": 13,
    race: 14,
    skill: 15,
    currency: 17,
	profile: 100
};
function g_isEmailValid(a) {
	return a.match(/^([a-z0-9._-]+)(\+[a-z0-9._-]+)?(@[a-z0-9.-]+\.[a-z]{2,4})$/i) != null
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
		f = $E(f);
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
	a = $E(a);
	return (a && a._button == 1)
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

DomContentLoaded.addEvent(function () {
    array_apply(gE(document, 'dfn'), function(x){
        var text = x.title;
        x.title = '';
        x.className += ' tip';

        if (text.indexOf('LANG.') == 0) {                   // custom for less redundant texts
          text = eval(text);
        }

        g_addTooltip(x, text, 'q');
    });
/* old implementation below; new is above
    $("dfn").each(function() {
        var text = $(this).attr('title');
        // '<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_jconlygems, 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">'
        $(this).attr('title', '').addClass('tip').mouseover(function(event) {
            $WH.Tooltip.showAtCursor(event, text, 0, 0, 'q');
        }).mousemove(function(event) {
            $WH.Tooltip.cursorUpdate(event)
        }).mouseout(function() {
            $WH.Tooltip.hide()}
        );
    });
*/
// oohkay, if i understand this right, this code binds an onCopy eventHandler to every child node of class="text"-nodes with the attribute unselectable="on"
// causing the text to disappear for 1ms, causing the empty node to be copied ...  w/e, i'm not going to use this nonsense
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
			i = ce("span");
			i.className = "selected"
		} else {
			i = ce("a");
			i.href = (r > 1 ? b.url + b.sep + r + b.pound: b.url + b.pound)
		}
		ae(i, ct(d != null ? d: r));
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
	var e = ce("div"),
	k,
	q = ce("var");
	e.className = "pages";
	if (c) {
		e.className += " pages-left"
	}
	if (b.nPages > 1) {
		k = ce("div");
		k.className = "pages-numbers";
		var o = Math.max(2, b.page - 3);
		var h = Math.min(b.nPages - 1, b.page + 3);
		var m = [];
		if (b.page != b.nPages) {
			m.push(p(b.page + 1, LANG.lvpage_next + String.fromCharCode(8250)))
		}
		m.push(p(b.nPages));
		if (h < b.nPages - 1) {
			var a = ce("span");
			ae(a, ct("..."));
			m.push(a)
		}
		for (var g = h; g >= o; --g) {
			m.push(p(g))
		}
		if (o > 2) {
			var a = ce("span");
			ae(a, ct("..."));
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
			ae(k, m[g])
		}
		k.firstChild.style.marginRight = "0";
		k.lastChild.style.marginLeft = "0"
	}
	var q = ce("var");
	ae(q, ct(sprintf(LANG[b.wording[b.nItems == 1 ? 0 : 1]], b.nItems)));
	if (b.nPages > 1) {
		var a = ce("span");
		ae(a, ct(String.fromCharCode(8211)));
		ae(q, a);
		var f = ce("a");
		f.className = "gotopage";
		f.href = "javascript:;";
		ns(f);
		if (Browser.ie) {
			ae(f, ct(" "))
		}
		f.onclick = function() {
			var d = prompt(sprintf(LANG.prompt_gotopage, 1, b.nPages), b.page);
			if (d != null) {
				d |= 0;
				if (d != b.page && d >= 1 && d <= b.nPages) {
					document.location.href = (d > 1 ? b.url + b.sep + d + b.pound: b.url + b.pound)
				}
			}
		};
		f.onmouseover = function(d) {
			Tooltip.showAtCursor(d, LANG.tooltip_gotopage, 0, 0, "q")
		};
		f.onmousemove = Tooltip.cursorUpdate;
		f.onmouseout = Tooltip.hide;
		ae(q, f)
	}
	if (c) {
		ae(e, q);
		if (k) {
			ae(e, k)
		}
	} else {
		if (k) {
			ae(e, k)
		}
		ae(e, q)
	}
	ae(l, e)
}
function g_disclose(a, b) {
	b.className = "disclosure-" + (g_toggleDisplay(a) ? "on": "off");
	return false
}
function co_addYourComment() {
	tabsContribute.focus(0);
	var ta = gE(document.forms['addcomment'], "textarea")[0];
	ta.focus()
}
function co_cancelReply() {
	ge("replybox-generic").style.display = "none";
	document.forms.addcomment.elements.replyto.value = ""
}
function co_validateForm(f) {
	var ta = gE(f, "textarea")[0];

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
	var _ = ge("infobox-sticky-ss");
	var type = g_pageInfo.type;
	var typeId = g_pageInfo.typeId;
	var pos = in_array(lv_screenshots, 1, function(a) {
		return a.sticky;
	});

	if (pos != -1) {
		var screenshot = lv_screenshots[pos];

		var a = ce("a");
		a.href = "#screenshots:id=" + screenshot.id;
		a.onclick = function(a) {
			ScreenshotViewer.show({
				screenshots: lv_screenshots,
				pos: pos
			});
			return rf2(a);
		};

		var size = (lv_videos && lv_videos.length ? [120, 90] : [150, 150]);
		var
            img = ce("img"),
            scale = Math.min(size[0] / screenshot.width, size[1] / screenshot.height);

		img.src    = g_staticUrl + "/uploads/screenshots/thumb/" + screenshot.id + ".jpg";
		img.width  = Math.round(scale * screenshot.width);
		img.height = Math.round(scale * screenshot.height);
		img.className = "border";
		ae(a, img);
		ae(_, a);

		var th = ge('infobox-screenshots');
        var a = ce("a");
        ae(a, ct(th.innerText + " (" + lv_screenshots.length + ")"));
        a.href = "#screenshots"
        a.title = sprintf(LANG.infobox_showall, lv_screenshots.length);
		a.onclick = function() {
			tabsRelated.focus((lv_videos && lv_videos.length) || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)) ? -2 : -1);
			return false;
		};
        ee(th);
        ae(th, a);
	}
    else {
		var a;
		if (g_user.id > 0) {
			a = '<a href="javascript:;" onclick="ss_submitAScreenshot(); return false">';
		}
        else {
			a = '<a href="?account=signin">';
		}
		_.innerHTML = sprintf(LANG.infobox_noneyet, a + LANG.infobox_submitone + "</a>")
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
	var _ = ge("infobox-sticky-vi");
	var type = g_pageInfo.type;
	var typeId = g_pageInfo.typeId;
	var pos = in_array(lv_videos, 1, function(a) {
		return a.sticky
	});

	if (pos != -1) {
		var video = lv_videos[pos];

		var a = ce("a");
		a.href = "#videos:id=" + video.id;
		a.onclick = function(e) {
			VideoViewer.show({
				videos: lv_videos,
				pos: pos
			});
			return rf2(e)
		};

		var img = ce("img");
		img.src = sprintf(vi_thumbnails[video.videoType], video.videoId);
		img.className = "border";
		ae(a, img);
		ae(_, a);

		var th = ge('infobox-videos');
        var a = ce("a");
        ae(a, ct(th.innerText + " (" + lv_videos.length + ")"));
        a.href = "#videos"
        a.title = sprintf(LANG.infobox_showall, lv_videos.length);
		a.onclick = function() {
			tabsRelated.focus(-1);
			return false;
		};
        ee(th);
        ae(th, a);
	}
    else {
        var a;
        if (g_user.id > 0) {
            a = '<a href="javascript:;" onclick="vi_submitAVideo(); return false">'
        }
        else {
            a = '<a href="?account=signin">'
        }

        _.innerHTML = sprintf(LANG.infobox_noneyet, a + LANG.infobox_suggestone + "</a>")
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
            availHeight = Math.max(50, Math.min(520, g_getWindowSize().h - 72 - captionExtraHeight)),
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
		if (resizing && (scale == 1) && g_getWindowSize().h > container.offsetHeight) {
			return;
		}

		container.style.visibility = 'hidden';

		var video = videos[pos];

		computeDimensions();

		if (!resizing) {

			if (video.videoType == 1) {
				// imgDiv.innerHTML = Markup.toHtml('[youtube=' + video.videoId + ' width=' + imgWidth + ' height=' + imgHeight + ' autoplay=true]', {mode:Markup.MODE_ARTICLE});
                /* yes container hack .. fuck off */
                var m = 'http://www.youtube.com/collectionId/' + video.videoId + '&fs=1&rel=0&autoplay=1';
                var l = imgWidth ? imgWidth : 640;
                var n = imgHeight ? imgHeight : 385;
                imgDiv.innerHTML = '<object width="' + l + '" height="' + n + '"><param name="movie" value="' + m + '">';
                imgDiv.innerHTML += '<param name="allowfullscreen" value="true"></param>';
                imgDiv.innerHTML += '<param name="allowscriptaccess" value="always"></param>';
                imgDiv.innerHTML += '<param name="wmode" value="opaque"></param>';
                imgDiv.innerHTML += '<embed width="' + l + '" height="' + n + '" src="' + m + '" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" wmode="opaque"></embed>';
                imgDiv.innerHTML += '</object>';
                /* end of hack .. fuck off */
			}

			aOriginal.href = sprintf(vi_siteurls[video.videoType], video.videoId);

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
				ee(s);
				Listview.funcBox.coFormatDate(s, elapsed, postedOn);
				divFrom.firstChild.style.display = '';
			}
            else {
				divFrom.firstChild.style.display = 'none';
			}

			var s = divFrom.childNodes[1];
			ee(s);

			if (video.user)
			{
				if (hasFrom1) {
					ae(s, ct(' ' + LANG.dash + ' '));
                }
				var a = ce('a');
				a.href = 'javascript:;';
				a.onclick = ContactTool.show.bind(ContactTool, { mode: 5, video: video });
				a.className = 'icon-report';
				g_addTooltip(a, LANG.report_tooltip, 'q2');
				ae(a, ct(LANG.report));
				ae(s, a);
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
		e = $E(e);

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
		ee(imgDiv);

		if (videos.length > 1) {
			dE(document, 'keyup', onKeyUp) ;
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
			screen = ce('div');
			screen.className = 'screenshotviewer-screen';

			aPrev = ce('a');
			aNext = ce('a');
			aPrev.className = 'screenshotviewer-prev';
			aNext.className = 'screenshotviewer-next';
			aPrev.href = 'javascript:;';
			aNext.href = 'javascript:;';

			var foo = ce('span');
			var b = ce('b');
			// ae(b, ct(LANG.previous));
			ae(foo, b);
			ae(aPrev, foo);
			var foo = ce('span');
			var b = ce('b');
			// ae(b, ct(LANG.next));
			ae(foo, b);
			ae(aNext, foo);

			aPrev.onclick = prevVideo;
			aNext.onclick = nextVideo;

			aCover = ce('a');
			aCover.className = 'screenshotviewer-cover';
			aCover.href = 'javascript:;';
			aCover.onclick = Lightbox.hide;
			var foo = ce('span');
			var b = ce('b');
			ae(b, ct(LANG.close));
			ae(foo, b);
			ae(aCover, foo);

			ae(screen, aPrev);
			ae(screen, aNext);
			ae(screen, aCover);

			imgDiv = ce('div');
			ae(screen, imgDiv);

			ae(dest, screen);

			var aClose = ce('a');
			// aClose.className = 'dialog-x';
			aClose.className = 'screenshotviewer-close';
			aClose.href = 'javascript:;';
			aClose.onclick = Lightbox.hide;
			// ae(aClose, ct(LANG.close));
            ae(aClose, ce('span'));
			ae(dest, aClose);

			aOriginal = ce('a');
			// aOriginal.className = 'dialog-arrow';
			aOriginal.className = 'screenshotviewer-original';
			aOriginal.href = 'javascript:;';
			aOriginal.target = '_blank';
			// ae(aOriginal, ct(LANG.original));
            ae(aOriginal, ce('span'));
			ae(dest, aOriginal);

			divFrom = ce('div');
			divFrom.className = 'screenshotviewer-from';
			var sp = ce('span');
			ae(sp, ct(LANG.lvscreenshot_from));
			ae(sp, ce('a'));
			ae(sp, ct(' '));
			ae(sp, ce('span'));
			ae(divFrom, sp);
			ae(divFrom, ce('span'));
			ae(divFrom, ce('span'));
			ae(dest, divFrom);

			divCaption = ce('div');
			divCaption.className = 'screenshotviewer-caption';
			ae(dest, divCaption);

			var d = ce('div');
			d.className = 'clear';
			ae(dest, d);
		}

		oldHash = location.hash;

		if (videos.length > 1) {
			aE(document, 'keyup', onKeyUp);
		}
		render();
	}

	this.checkPound = function() {
		pageTitle = gE(document, 'title').innerHTML;
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
					i.innerHTML = sprintf((g == 1 ? LANG.dialog_selecteditem: LANG.dialog_selecteditems), g)
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
		var g = gc("compare_groups"),
		f = "?compare";
		if (h.action > 1) {
			if (g) {
				c = g + ";" + c
			}
			sc("compare_groups", 20, c, "/", location.hostname);
			// sc("compare_groups", 20, c, "/", ".wowhead.com");
			if (e) {
				sc("compare_level", 20, e, "/", location.hostname)
				// sc("compare_level", 20, e, "/", ".wowhead.com")
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
function Ajax(b, c) {
	if (!b) {
		return
	}
	var a;
	try {
		a = new XMLHttpRequest()
	} catch(d) {
		try {
			a = new ActiveXObject("Msxml2.XMLHTTP")
		} catch(d) {
			try {
				a = new ActiveXObject("Microsoft.XMLHTTP")
			} catch(d) {
				if (window.createRequest) {
					a = window.createRequest()
				} else {
					alert(LANG.message_ajaxnotsupported);
					return
				}
			}
		}
	}
	this.request = a;
	cO(this, c);
	this.method = this.method || (this.params && "POST") || "GET";
	a.open(this.method, b, this.async == null ? true: this.async);
	a.onreadystatechange = Ajax.onReadyStateChange.bind(this);
	if (this.method.toUpperCase() == "POST") {
		a.setRequestHeader("Content-Type", (this.contentType || "application/x-www-form-urlencoded") + "; charset=" + (this.encoding || "UTF-8"))
	}
	a.send(this.params)
}
Ajax.onReadyStateChange = function() {
	if (this.request.readyState == 4) {
		if (this.request.status == 0 || (this.request.status >= 200 && this.request.status < 300)) {
			this.onSuccess != null && this.onSuccess(this.request, this)
		} else {
			this.onFailure != null && this.onFailure(this.request, this)
		}
		if (this.onComplete != null) {
			this.onComplete(this.request, this)
		}
	}
};
function g_ajaxIshRequest(b) {
	var c = gE(document, "head")[0],
	a = g_getGets();
	if (a.refresh != null) {
		b += "&refresh"
	}
	ae(c, ce("script", {
		type: "text/javascript",
		src: b
	}))
}
var Menu = {
	iframes: [],
	divs: [],
	selection: [],
	show: function() {
		try {
			clearTimeout(Menu.timer);
			if (Menu.currentLink) {
				Menu._show(this)
			} else {
				if (this.className.indexOf("open") == -1) {
					this.className += " open"
				}
				Menu.timer = setTimeout(Menu._show.bind(0, this), 100)
			}
		} catch(a) {}
	},
	_show: function(b) {
		if (Menu.currentLink != b) {
			var a = ac(b);
			Menu._hide();
			Menu.selection = [-1];
			Menu.currentLink = b;
			Menu.showDepth(0, b.menu, a[0], a[1] + b.offsetHeight + 1, b.offsetHeight + 8, b.offsetWidth, a[1], false);
			if (b.className.indexOf("open") == -1) {
				b.className += " open"
			}
		} else {
			Menu.truncate(0);
			Menu.clean(0)
		}
	},
	showAtCursor: function(b, a, d) {
		clearTimeout(Menu.timer);
		Menu._hide();
		Menu.selection = [-1];
		Menu.currentLink = null;
		if (! (a && d)) {
			b = $E(b);
			var c = g_getCursorPos(b);
			a = c.x;
			d = c.y
		}
		if (Browser.ie6) {
			a -= 2;
			d -= 2
		}
		Menu.showDepth(0, this.menu, a, d, 0, 0, 0, true)
	},
	hide: function() {
		try {
			clearTimeout(Menu.timer);
			if (Menu.currentLink) {
				Menu.timer = setTimeout(Menu._hide, 333)
			} else {
				this.className = this.className.replace("open", "")
			}
		} catch(a) {}
	},
	_hide: function() {
		for (var b = 0, a = Menu.selection.length; b < a; ++b) {
			Menu.divs[b].style.display = "none";
			Menu.divs[b].style.visibility = "hidden";
			if (Browser.ie6) {
				Menu.iframes[b].style.display = "none"
			}
		}
		Menu.selection = [];
		if (Menu.currentLink) {
			Menu.currentLink.className = Menu.currentLink.className.replace("open", "")
		}
		Menu.currentLink = null
	},
	sepOver: function() {
		var b = this.d;
		var a = b.i;
		Menu.truncate(a);
		Menu.clean(a);
		Menu.selection[a] = -1
	},
	elemOver: function() {
		var g = this.d;
		var f = g.i;
		var e = this.i;
		var a = this.k;
		var b = this.firstChild.className == "menusub";
		Menu.truncate(f + b);
		if (b && a != Menu.selection[f]) {
			var h = ac(this);
			Menu.selection[f + 1] = -1;
			Menu.showDepth(f + 1, g.menuArray[e][3], h[0], h[1] - 2, this.offsetHeight, this.offsetWidth - 3, 0, false)
		}
		Menu.clean(f);
		Menu.selection[f] = a;
		if (this.className.length) {
			this.className += " open"
		} else {
			this.className = "open"
		}
	},
	elemClick: function(a) {
		Menu._hide();
		a()
	},
	getIframe: function(a) {
		var b;
		if (Menu.iframes[a] == null) {
			b = ce("iframe");
			b.src = "javascript:0;";
			b.frameBorder = 0;
			ae(ge("layers"), b);
			Menu.iframes[a] = b
		} else {
			b = Menu.iframes[a]
		}
		return b
	},
	getDiv: function(a, b) {
		var c;
		if (Menu.divs[a] == null) {
			c = ce("div");
			c.className = "menu";
			ae(ge("layers"), c);
			Menu.divs[a] = c
		} else {
			c = Menu.divs[a]
		}
		c.i = a;
		c.menuArray = b;
		return c
	},
	showDepth: function(N, c, D, C, O, G, A, z) {
		var X, U = Menu.getDiv(N, c);
		while (U.firstChild) {
			de(U.firstChild)
		}
		var v = ce("table"),
		B = ce("tbody"),
		S = ce("tr"),
		e = ce("td"),
		Q = ce("div"),
		K = ce("div");
		var J = 999;
		var b = g_getWindowSize(),
		l = g_getScroll(),
		f = b.w,
		o = b.h,
		W = l.x,
		P = l.y;
		if (O > 0 && (N > 0 || c.length > 20)) {
			if ((25 + 1) * c.length > o - 25 - A) {
				for (var M = 2; M < 4; ++M) {
					if (O / M * c.length + 30 < o - A) {
						break
					}
				}
				J = Math.floor(c.length / M)
			}
		}
		var t = 0;
		var L = 0;
		for (var M = 0, u = c.length; M < u; ++M) {
			var R = c[M];
			if (R[0] == null) {
				var r = ce("span");
				r.className = "separator";
				ns(r);
				ae(r, ct(R[1]));
				r.d = U;
				r.onmouseover = Menu.sepOver;
				ae(K, r)
			} else {
				var V = ce("a");
				V.d = U;
				V.k = L++;
				V.i = M;
				if (R[2]) {
					if (Menu.currentLink && Menu.currentLink.menuappend) {
						if (R[2].indexOf(Menu.currentLink.menuappend) == -1) {
							V.href = R[2] + Menu.currentLink.menuappend
						} else {
							V.href = R[2]
						}
					} else {
						if (typeof R[2] == "function") {
							V.href = "javascript:;";
							V.onclick = Menu.elemClick.bind(0, R[2]);
							ns(V)
						} else {
							V.href = R[2]
						}
					}
				} else {
					V.href = "javascript:;";
					V.style.cursor = "default";
					ns(V)
				}
				V.onmouseover = Menu.elemOver;
				var H = ce("span"),
				T = ce("span");
				if (R[3] != null) {
					H.className = "menusub"
				}
				if (R.newWindow) {
					V.target = "_blank"
				}
				if (R.className) {
					T.className += " "+R.className
				}
                if (R[4] != null && R[4].rel) {
                    V.rel = R[4].rel
                }
				if (R[4] != null && R[4].className) {
					T.className += " "+R[4].className
				}
				if (R.checked) {
					T.className += " menucheck"
				}
				else if (!R.checked && R[4] != null && R[4].tinyIcon) {
					if (R[4].tinyIcon.indexOf("/") != -1)
						T.style.background = "url(" + R[4].tinyIcon.toLowerCase() + ") left center no-repeat"
					else
						T.style.background = "url(images/icons/tiny/" + R[4].tinyIcon.toLowerCase() + ".gif) left center no-repeat"
				}
				else if (!R.checked && R.tinyIcon) {
					if (R.tinyIcon.indexOf("/") != -1)
						T.style.background = "url(" + R.tinyIcon.toLowerCase() + ") left center no-repeat"
					else
						T.style.background = "url(images/icons/tiny/" + R.tinyIcon.toLowerCase() + ".gif) left center no-repeat"
				} else {
					if (R[4] != null && R[4].socketColor) {
						T.className += " socket-" + g_file_gems[R[4].socketColor]
					}
					else if (R.socketColor) {
						T.className += " socket-" + g_file_gems[R.socketColor]
					} else {
						if (R.smallIcon) {
							V.style.padding = 0;
							T.style.padding = "4px 18px 4px 28px";
							T.style.background = "url(images/icon_border_small.png) left center no-repeat transparent";
							H.style.background = "url(images/icons/small/" + R.smallIcon.toLowerCase() + ".jpg) 4px 3px no-repeat transparent"
						} else {
							if (R.smallImage) {
								V.style.padding = 0;
								T.style.padding = "4px 18px 4px 28px";
								H.style.background = "url(images/icons/small/" + R.smallImage.toLowerCase() + ".jpg) 4px 3px no-repeat transparent"
							}
						}
					}
				}
				ae(T, ct(R[1]));
				ae(H, T);
				ae(V, H);
				ae(K, V)
			}
			if (t++==J) {
				Q.onmouseover = Menu.divOver;
				Q.onmouseout = Menu.divOut;
				ae(Q, K);
				if (!Browser.ie6) {
					var I = ce("p");
					ae(I, ce("em"));
					ae(I, ce("var"));
					ae(I, ce("strong"));
					ae(I, Q);
					ae(e, I)
				} else {
					ae(e, Q)
				}
				ae(S, e);
				e = ce("td");
				I = ce("p");
				Q = ce("div");
				K = ce("div");
				t = 0
			}
		}
		Q.onmouseover = Menu.divOver;
		Q.onmouseout = Menu.divOut;
		ae(Q, K);
		if (!Browser.ie6) {
			if (J != 999) {
				var I = ce("p");
				ae(I, ce("em"));
				ae(I, ce("var"));
				ae(I, ce("strong"));
				ae(I, Q);
				ae(e, I)
			} else {
				ae(U, ce("em"));
				ae(U, ce("var"));
				ae(U, ce("strong"));
				ae(e, Q)
			}
		} else {
			ae(e, Q)
		}
		ae(S, e);
		ae(B, S);
		ae(v, B);
		ae(U, v);
		U.style.left = U.style.top = "-2323px";
		U.style.display = "";
		var g = v.offsetWidth,
		q = v.offsetHeight,
		F = true,
		E = true;
		if (!Browser.ie6) {
			g += 5;
			q += 6
		}
		if (D + g > f + W || c.rightAligned) {
			F = false
		}
		if (F) {
			if (D + G + g > f) {
				D = Math.max(0, D - g)
			} else {
				if (N > 0) {
					D += G
				}
			}
		} else {
			D = D + G - g;
			if (Browser.ie) {
				D -= 3
			}
		}
		if ((N > 0 || z) && C + q > o + P) {
			C = Math.max(P + 5, o + P - q)
		}
		U.style.left = D + "px";
		U.style.top = C + "px";
		if (Browser.ie6) {
			X = Menu.getIframe(N);
			X.style.left = D + "px";
			X.style.top = C + "px";
			X.style.width = g + "px";
			X.style.height = q + "px";
			X.style.display = "";
			X.style.visibility = "visible"
		}
		U.style.visibility = "visible";
		if (Browser.opera) {
			U.style.display = "none";
			U.style.display = ""
		}
	},
	divOver: function() {
		clearTimeout(Menu.timer)
	},
	divOut: function() {
		clearTimeout(Menu.timer);
		Menu.timer = setTimeout(Menu._hide, 333)
	},
	truncate: function(b) {
		var c;
		while (Menu.selection.length - 1 > b) {
			c = Menu.selection.length - 1;
			Menu.divs[c].style.display = "none";
			Menu.divs[c].style.visibility = "hidden";
			if (Browser.ie6) {
				Menu.iframes[c].style.display = "none"
			}
			Menu.selection.pop()
		}
	},
	clean: function(b) {
		for (var c = b; c < Menu.selection.length; ++c) {
			if (Menu.selection[c] != -1) {
				var e = gE(Menu.divs[c], "a")[Menu.selection[c]];
				if (e.className.indexOf("sub") != -1) {
					e.className = "sub"
				} else {
					e.className = ""
				}
				Menu.selection[c] = -1
			}
		}
	},
	append: function(b, c) {
		b[2] += c;
		if (b[3] != null) {
			Menu._append(b[3], c)
		}
	},
	_append: function(b, d) {
		var e, g = 0;
		for (var c = 0; c < b.length; ++c) {
			var f = b[c][2].indexOf("&filter=");
			if (f != -1 && d.indexOf("&filter=") == 0) {
				d = Menu._fixCollision(b[c][2].substr(f), d)
			}
			b[c][2] += d;
			if (b[c][3]) {
				Menu._append(b[c][3], d)
			}
		}
	},
	_splitFilter: function(b) {
		var g = b.substr(8).split(";"),
		c = {};
		for (var e = 0, a = g.length; e < a; ++e) {
			var h = g[e].indexOf("="),
			d,
			f;
			if (h != -1) {
				d = g[e].substr(0, h);
				f = g[e].substr(h + 1)
			} else {
				d = g[e];
				f = ""
			}
			c[d] = f
		}
		return c
	},
	_fixCollision: function(d, a) {
		var b = Menu._splitFilter(d),
		c = Menu._splitFilter(a);
		a = "";
		for (var e in c) {
			if (!b[e] && e != "sl" && e != "cl") {
				a += ";";
				a += e + "=" + c[e]
			}
		}
		return a
	},
	fixUrls: function(menu, url, hash, opt, depth) {
		if (!depth) {
			depth = 0
		}
		for (var i = 0, len = menu.length; i < len; ++i) {
			if (menu[i][2] == null) {
				menu[i][2] = url + menu[i][0] + (hash ? hash : "")
			}
			if (menu[i][3]) {
				if (opt == true || (typeof opt == "object" && opt[depth] == true)) {
					Menu.fixUrls(menu[i][3], url, hash, opt, depth + 1)
				}
                else {
					Menu.fixUrls(menu[i][3], url + menu[i][0] + ".", hash, opt, depth + 1)
				}
			}
		}
	},
	addButtons: function(h, g) {
		for (var e = 0, b = g.length; e < b; ++e) {
			if (g[e][0] == null) {
				continue
			}
			var c = ce("a"),
			f = ce("span");
			if (g[e][2]) {
				c.href = g[e][2]
			} else {
				c.href = "javascript:;";
				c.style.cursor = "default";
				c.style.textDecoration = "none";
				ns(c)
			}
			if (g[e][3] != null) {
				f.className = "menuarrowd";
				c.menu = g[e][3];
				c.onmouseover = Menu.show;
				c.onmouseout = Menu.hide
			} else {
				c.onmouseover = Menu._hide
			}
			ae(f, ct(g[e][1]));
			ae(c, f);
			ae(h, c)
		}
	},
	explode: function(f) {
		var d = [],
		e = null,
		c;
		for (var b = 0, a = f.length; b < a; ++b) {
			if (f[b][0] != null) {
				if (e != null) {
					c.push(f[b])
				} else {
					d.push(f[b])
				}
			}
			if (e != null && (f[b][0] == null || b == a - 1)) {
				d.push([0, e[1], , c])
			}
			if (f[b][0] == null) {
				e = f[b];
				c = []
			}
		}
		return d
	}
};

function Tabs(opt) {
	cO(this, opt);

	if (this.parent) {
		this.parent = $(this.parent);
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
		cO(_, opt);

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
            _ = gE(this.uls[0], 'a');

    alert('whoops, forgot a ToDo!');
/*  fix this jquery-nonsens
            $('.icon-lock', _[index]).remove();

            if (locked)
                $('div, b', _[index]).prepend('<span class="icon-lock" />');
*/
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
		gE(this.uls[0], 'a')[index].onclick({}, true);
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

        _ = gE(this.uls[0], 'a');
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

        var container = ce('div');
        container.className = 'tabs-container';

        this.uls[0] = ce('ul');
        this.uls[0].className = 'tabs';

        d = ce('div');
        d.className = 'tabs-levels';

        ae(container, this.uls[0]);

        for (var i = 0; i < this.tabs.length; ++i) {
            var tab = this.tabs[i];

            l = ce('li');
            a = ce('a');
            b = ce('b');

            if (tab.hidden) {
                l.style.display = 'none';
            }

            if (this.poundable) {
                a.href = '#' + tab.id;
            }
            else {
                a.href = 'javascript:;';
            }

            ns(a);
            a.onclick = Tabs.onClick.bind(tab, a);

            d = ce('div');

            if(tab.locked)
            {
                s = ce('span');
                s.className = 'icon-lock';
                ae(d, s);
            }
            else if(tab.icon)
            {
                s = ce('span');
                s.className = 'icontiny';
                s.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + tab.icon.toLowerCase() + '.gif)';
                ae(d, s);
            }

            if(tab.tooltip)
            {
                a.onmouseover = (function(tooltip, e) { Tooltip.showAtCursor(e, tooltip, 0, 0, 'q'); }).bind(a, tab.tooltip);
                a.onmousemove = Tooltip.cursorUpdate;
                a.onmouseout  = Tooltip.hide;
            }

            if(tab['class'])
                d.className = tab['class'];

            ae(d, ct(tab.caption));
            ae(a, d);

            if(tab.locked) {
                s = ce('span');
                s.className = 'icon-lock';
                ae(b, s);
            }
            else if (tab.icon) {
                s = ce('span');
                s.className = 'icontiny';
                s.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + tab.icon.toLowerCase() + '.gif)';
                ae(b, s);
            }
            ae(b, ct(tab.caption));
            ae(a, b);
            ae(l, a);
            ae(this.uls[0], l);
        }

        ee(this.parent);
        ae(this.parent, container);

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

        var _ = gE(this.uls[0], 'a');
        g_setTextNodes(_[index], name);
    },

	setTabPound: function(index, pound) {
		if (!this.poundable) {
			return;
		}

        var _ = gE(this.uls[0], 'a');
        _[index].href = '#' + this.tabs[index].id + (pound ? ':' + pound : '');
	},

	setTabTooltip: function(index, text) {
		this.tabs[index].tooltip = text;

        var _ = gE(this.uls[0], 'a');
        if(text == null) {
            _[index].onmouseover = _[index].onmousemove = _[index].onmouseout = null;
        }
        else {
            _[index].onmouseover = function(e) { Tooltip.showAtCursor(e, text, 0, 0, 'q2'); };
            _[index].onmousemove = Tooltip.cursorUpdate;
            _[index].onmouseout  = Tooltip.hide;
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

	var res = rf2(e);
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
		return in_array(this.tabs, _, function(x) {
            if(!x.locked) {
                return x.id;
            }
		});
	}
};

Tabs.onShow = function(newTab, oldTab) {
	var _;

	if(newTab.hidden || newTab.locked) {
		return;
    }

	if (oldTab) {
		ge('tab-' + oldTab.id).style.display = 'none';
	}

	_ = ge('tab-' + newTab.id);
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

        setTimeout(g_scrollTo.bind(null, el, padd), 10);
	}
};

var g_listviews = {};
function Listview(a) {
	cO(this, a);
	if (this.id) {
		var o = (this.tabs ? "tab-": "lv-") + this.id;
		if (this.parent) {
			var l = ce("div");
			l.id = o;
			ae($(this.parent), l);
			this.container = l
		} else {
			this.container = ge(o)
		}
	} else {
		return
	}
	var c = g_getGets();
	if ((c.debug != null || g_user.debug) && g_user.roles & 26) {
		this.debug = true
	}
	if (this.template && Listview.templates[this.template]) {
		this.template = Listview.templates[this.template]
	} else {
		return
	}
	g_listviews[this.id] = this;
	if (this.data == null) {
		this.data = []
	}
	if (this.poundable == null) {
		if (this.template.poundable != null) {
			this.poundable = this.template.poundable
		} else {
			this.poundable = true
		}
	}
	if (this.searchable == null) {
		if (this.template.searchable != null) {
			this.searchable = this.template.searchable
		} else {
			this.searchable = false
		}
	}
	if (this.filtrable == null) {
		if (this.template.filtrable != null) {
			this.filtrable = this.template.filtrable
		} else {
			this.filtrable = false
		}
	}
	if (this.data.length == 1) {
		this.filtrable = false;
		this.searchable = false
	}
	if (this.searchable && this.searchDelay == null) {
		if (this.template.searchDelay != null) {
			this.searchDelay = this.template.searchDelay
		} else {
			this.searchDelay = 333
		}
	}
	if (this.clickable == null) {
		if (this.template.clickable != null) {
			this.clickable = this.template.clickable
		} else {
			this.clickable = true
		}
	}
	if (this.hideBands == null) {
		this.hideBands = this.template.hideBands
	}
	if (this.hideNav == null) {
		this.hideNav = this.template.hideNav
	}
	if (this.hideHeader == null) {
		this.hideHeader = this.template.hideHeader
	}
	if (this.hideCount == null) {
		this.hideCount = this.template.hideCount
	}
	if (this.computeDataFunc == null && this.template.computeDataFunc != null) {
		this.computeDataFunc = this.template.computeDataFunc
	}
	if (this.createCbControls == null && this.template.createCbControls != null) {
		this.createCbControls = this.template.createCbControls
	}
	if (this.template.onBeforeCreate != null) {
		if (this.onBeforeCreate == null) {
			this.onBeforeCreate = this.template.onBeforeCreate
		} else {
			this.onBeforeCreate = [this.template.onBeforeCreate, this.onBeforeCreate]
		}
	}
	if (this.onAfterCreate == null && this.template.onAfterCreate != null) {
		this.onAfterCreate = this.template.onAfterCreate
	}
	if (this.onNoData == null && this.template.onNoData != null) {
		this.onNoData = this.template.onNoData
	}
	if (this.createNote == null && this.template.createNote != null) {
		this.createNote = this.template.createNote
	}
	if (this.customFilter == null && this.template.customFilter != null) {
		this.customFilter = this.template.customFilter
	}
	if (this.onSearchSubmit == null && this.template.onSearchSubmit != null) {
		this.onSearchSubmit = this.template.onSearchSubmit
	}
	if (this.clip == null && this.template.clip != null) {
		this.clip = this.template.clip
	}
	if (this.mode == null) {
		this.mode = this.template.mode
	}
	if (this.nItemsPerPage == null) {
		if (this.template.nItemsPerPage != null) {
			this.nItemsPerPage = this.template.nItemsPerPage
		} else {
			this.nItemsPerPage = 50
		}
	}
	this.nItemsPerPage |= 0;
	if (this.nItemsPerPage <= 0) {
		this.nItemsPerPage = 0
	}
	this.nFilters = 0;
	this.resetRowVisibility();
	if (this.mode == Listview.MODE_TILED) {
		if (this.nItemsPerRow == null) {
			var t = this.template.nItemsPerRow;
			this.nItemsPerRow = (t != null ? t: 4)
		}
		this.nItemsPerRow |= 0;
		if (this.nItemsPerRow <= 1) {
			this.nItemsPerRow = 1
		}
	}
	else if (this.mode == Listview.MODE_CALENDAR) {
		this.dates = [];
		this.nItemsPerRow = 7; // Days per row
		this.nItemsPerPage = 1; // Months per page
		this.nDaysPerMonth = [];

		if (this.template.startOnMonth != null)
			this.startOnMonth = this.template.startOnMonth;
		else
			this.startOnMonth = new Date();
		this.startOnMonth.setDate(1);
		this.startOnMonth.setHours(0, 0, 0, 0);

		if (this.nMonthsToDisplay == null) {
			if(this.template.nMonthsToDisplay != null)
				this.nMonthsToDisplay = this.template.nMonthsToDisplay;
			else
				this.nMonthsToDisplay = 1;
		}

		var y = this.startOnMonth.getFullYear(),
			m = this.startOnMonth.getMonth();

		for (var j = 0; j < this.nMonthsToDisplay; ++j) {
			var date = new Date(y, m + j, 32);
			this.nDaysPerMonth[j] = 32 - date.getDate();
			for (var i = 1; i <= this.nDaysPerMonth[j]; ++i)
				this.dates.push({ date: new Date(y, m + j, i) });
		}

		if (this.template.rowOffset != null)
			this.rowOffset = this.template.rowOffset;
	}
    else {
		this.nItemsPerRow = 1
	}
	this.columns = [];
	for (var f = 0, k = this.template.columns.length; f < k; ++f) {
		var r = this.template.columns[f],
		e = {};
		cO(e, r);
		this.columns.push(e)
	}
	if (this.extraCols != null) {
		for (var f = 0, k = this.extraCols.length; f < k; ++f) {
			var m = null;
			var b = this.extraCols[f];
			if (b.after || b.before) {
				var j = in_array(this.columns, (b.after ? b.after: b.before), function(d) {
					return d.id
				});
				if (j != -1) {
					m = (b.after ? j + 1 : j - 1)
				}
			}
			if (m == null) {
				m = this.columns.length
			}
			if (b.id == "debug-id") {
				this.columns.splice(0, 0, b)
			} else {
				this.columns.splice(m, 0, b)
			}
		}
	}
	this.visibility = [];
	var p = [],
	q = [];
	if (this.visibleCols != null) {
		array_walk(this.visibleCols, function(d) {
			p[d] = 1
		})
	}
	if (this.hiddenCols != null) {
		array_walk(this.hiddenCols, function(d) {
			q[d] = 1
		})
	}
	for (var f = 0, k = this.columns.length; f < k; ++f) {
		var b = this.columns[f];
		if (p[b.id] != null || (!b.hidden && q[b.id] == null)) {
			this.visibility.push(f)
		}
	}
	if (this.sort == null && this.template.sort) {
		this.sort = this.template.sort.slice(0)
	}
	if (this.sort != null) {
		var h = this.sort;
		this.sort = [];
		for (var f = 0, k = h.length; f < k; ++f) {
			var b = parseInt(h[f]);
			if (isNaN(b)) {
				var g = 0;
				if (h[f].charAt(0) == "-") {
					g = 1;
					h[f] = h[f].substring(1)
				}
				var j = in_array(this.columns, h[f], function(d) {
					return d.id
				});
				if (j != -1) {
					if (g) {
						this.sort.push( - (j + 1))
					} else {
						this.sort.push(j + 1)
					}
				}
			} else {
				this.sort.push(b)
			}
		}
	} else {
		this.sort = []
	}
	if ((this.debug || g_user.debug) && this.id != "topics" && this.id != "recipes") {
		this.columns.splice(0, 0, {
			id: "debug-id",
			value: "id",
			name: "ID",
			width: "5%",
			tooltip: "ID"
		});
		this.visibility.splice(0, 0, -1);
		for (var f = 0, k = this.visibility.length; f < k; ++f) {
			this.visibility[f] = this.visibility[f] + 1
		}
		for (var f = 0, k = this.sort.length; f < k; ++f) {
			if (this.sort[f] < 0) {
				this.sort[f] = this.sort[f] - 1
			} else {
				this.sort[f] = this.sort[f] + 1
			}
		}
	}
	if (this.tabs) {
		this.tabIndex = this.tabs.add(this.getTabName(), {
			id: this.id,
			onLoad: this.initialize.bind(this)
		})
	} else {
		this.initialize()
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
				for (var d = 0, a = this.data.length; d < a; ++d) {
					this.computeDataFunc(this.data[d])
				}
			}
		}
		if (this.tabs) {
			this.pounded = (this.tabs.poundedTab == this.tabIndex);
			if (this.pounded) {
				this.readPound()
			}
		} else {
			this.readPound()
		}
		this.applySort();
		var b;
		if (this.onBeforeCreate != null) {
			if (typeof this.onBeforeCreate == "function") {
				b = this.onBeforeCreate()
			} else {
				for (var d = 0; d < this.onBeforeCreate.length; ++d) { (this.onBeforeCreate[d].bind(this))()
				}
			}
		}
		this.noData = ce("div");
		this.noData.className = "listview-nodata text";
		if (this.mode == Listview.MODE_DIV) {
			this.mainContainer = this.mainDiv = ce("div");
			this.mainContainer.className = "listview-mode-div"
		} else {
			this.mainContainer = this.table = ce("table");
			this.thead = ce("thead");
			this.tbody = ce("tbody");
			if (this.clickable) {
				this.tbody.className = "clickable"
			}
			if (this.mode == Listview.MODE_TILED) {
				if(!this.noStyle)
					this.table.className = 'listview-mode-' + (this.mode == Listview.MODE_TILED ? 'tiled' : 'calendar');

				var
                    e = (100 / this.nItemsPerRow) + "%",
                    f = ce("colgroup"),
                    c;

				for (var d = 0; d < this.nItemsPerRow; ++d) {
					c = ce("col");
					c.style.width = e;
					ae(f, c)
				}

				ae(this.mainContainer, f)
			} else {
				this.table.className = "listview-mode-default";
				this.createHeader();
				this.updateSortArrow()
			}
			ae(this.table, this.thead);
			ae(this.table, this.tbody);
			if (this.mode == Listview.MODE_CHECKBOX && Browser.ie) {
				setTimeout(Listview.cbIeFix.bind(this), 1)
			}
		}
		this.createBands();
		if (this.customFilter != null) {
			this.updateFilters()
		}
		this.updateNav();
		this.refreshRows();
		if (this.onAfterCreate != null) {
			this.onAfterCreate(b)
		}
	},
	createHeader: function() {
		var h = ce("tr");
		if (this.mode == Listview.MODE_CHECKBOX) {
			var g = ce("th"),
			j = ce("div"),
			c = ce("a");
			g.style.width = "33px";
			c.href = "javascript:;";
			c.className = "listview-cb";
			ns(c);
			ae(c, ct(String.fromCharCode(160)));
			ae(j, c);
			ae(g, j);
			ae(h, g)
		}
		for (var f = 0, b = this.visibility.length; f < b; ++f) {
			var e = this.visibility[f],
			d = this.columns[e],
			g = ce("th");
			j = ce("div"),
			c = ce("a"),
			outerSpan = ce("span"),
			innerSpan = ce("span");
			d.__th = g;
			c.href = "javascript:;";
			if (this.filtrable && (d.filtrable == null || d.filtrable)) {
				c.onmouseup = Listview.headerClick.bind(this, d, e);
				c.onclick = c.oncontextmenu = rf
			} else {
				c.onclick = this.sortBy.bind(this, e + 1)
			}
			c.onmouseover = Listview.headerOver.bind(this, c, d);
			c.onmouseout = Tooltip.hide;
			ns(c);
			if (d.width != null) {
				g.style.width = d.width
			}
			if (d.align != null) {
				g.style.textAlign = d.align
			}
			if (d.span != null) {
				g.colSpan = d.span
			}
			ae(innerSpan, ct(d.name));
			ae(outerSpan, innerSpan);
			ae(c, outerSpan);
			ae(j, c);
			ae(g, j);
			ae(h, g)
		}
		if (this.hideHeader) {
			this.thead.style.display = "none"
		}
		ae(this.thead, h)
	},
	createBands: function() {
		var j = ce("div"),
		l = ce("div"),
		m = ce("div"),
		k = ce("div");
		this.bandTop = j;
		this.bandBot = l;
		this.noteTop = m;
		this.noteBot = k;
		j.className = "listview-band-top";
		l.className = "listview-band-bottom";
		this.navTop = this.createNav(true);
		this.navBot = this.createNav(false);
		m.className = k.className = "listview-note";
		if (this.note) {
			m.innerHTML = this.note;
			var e = g_getGets();
			if (this.note.indexOf("fi_toggle()") > -1 && !e.filter) {
				fi_toggle()
			}
		} else {
			if (this.createNote) {
				this.createNote(m, k)
			}
		}
		if (this.debug && this.id != "topics") {
			ae(m, ct(" ("));
			var b = ce("a");
			b.onclick = this.getList.bind(this);
			ae(b, ct("CSV"));
			ae(m, b);
			ae(m, ct(")"))
		}

		if (this._errors) {
			var
				sp = ce('small'),
				b  = ce('b');

			b.className = 'q10 report-icon';
			if (m.innerHTML) {
				b.style.marginLeft = '10px';
			}

			g_addTooltip(sp, LANG.lvnote_witherrors, 'q')

			st(b, LANG.error);
			ae(sp, b);
			ae(m, sp);
		}

		if (!m.firstChild && this.mode != Listview.MODE_CHECKBOX) {
			ae(m, ct(String.fromCharCode(160)))
		}
		if (this.mode != Listview.MODE_CHECKBOX) {
			ae(k, ct(String.fromCharCode(160)))
		}
		ae(j, this.navTop);
		if (this.searchable) {
			var o = this.updateFilters.bind(this, true),
			f = (this._truncated ? "search-within-results2": "search-within-results"),
			d = ce("span"),
			c = ce("em"),
			i = ce("a"),
			h = ce("input");
			d.className = "listview-quicksearch";
			ae(d, c);
			i.href = "javascript:;";
			i.onclick = function() {
				var a = this.nextSibling;
				a.value = "";
				a.className = f;
				o()
			};
			i.style.display = "none";
			ae(i, ce("span"));
			ae(d, i);
			ns(i);
			h.setAttribute("type", "text");
			h.className = f;
			h.style.width = (this._truncated ? "19em": "15em");
			g_onAfterTyping(h, o, this.searchDelay);
			h.onmouseover = function() {
				if (trim(this.value) != "") {
					this.className = ""
				}
			};
			h.onfocus = function() {
				this.className = ""
			};
			h.onblur = function() {
				if (trim(this.value) == "") {
					this.className = f;
					this.value = ""
				}
			};
			h.onkeypress = this.submitSearch.bind(this);
			if (Browser.ie) {
				setTimeout(function() {
					h.value = ""
				},
				1)
			}
			ae(d, h);
			this.quickSearchBox = h;
			this.quickSearchGlass = c;
			this.quickSearchClear = i;
			ae(j, d)
		}
		ae(j, m);
		ae(l, this.navBot);
		ae(l, k);
		if (this.mode == Listview.MODE_CHECKBOX) {
			if (this.note) {
				m.style.paddingBottom = "5px"
			}
			this.cbBarTop = this.createCbBar(true);
			this.cbBarBot = this.createCbBar(false);
			ae(j, this.cbBarTop);
			ae(l, this.cbBarBot);
			if (!this.noteTop.firstChild && !this.cbBarTop.firstChild) {
				this.noteTop.innerHTML = "&nbsp;"
			}
			if (!this.noteBot.firstChild && !this.cbBarBot.firstChild) {
				this.noteBot.innerHTML = "&nbsp;"
			}
			if (this.noteTop.firstChild && this.cbBarTop.firstChild) {
				this.noteTop.style.paddingBottom = "6px"
			}
			if (this.noteBot.firstChild && this.cbBarBot.firstChild) {
				this.noteBot.style.paddingBottom = "6px"
			}
		}
		if (this.hideBands & 1) {
			j.style.display = "none"
		}
		if (this.hideBands & 2) {
			l.style.display = "none"
		}
		ae(this.container, this.bandTop);
		if (this.clip) {
			var g = ce("div");
			g.className = "listview-clip";
			g.style.width = this.clip.w + "px";
			g.style.height = this.clip.h + "px";
			this.clipDiv = g;
			ae(g, this.mainContainer);
			ae(g, this.noData);
			ae(this.container, g)
		} else {
			ae(this.container, this.mainContainer);
			ae(this.container, this.noData)
		}
		ae(this.container, this.bandBot)
	},
	createNav: function(g) {
		var c = ce("div"),
		d = ce("a"),
		b = ce("a"),
		a = ce("a"),
		j = ce("a"),
		i = ce("span"),
		h = ce("b"),
		f = ce("b"),
		e = ce("b");
		c.className = "listview-nav";
		d.href = b.href = a.href = j.href = "javascript:;";
		ae(d, ct(String.fromCharCode(171) + LANG.lvpage_first));
		ae(b, ct(String.fromCharCode(8249) + LANG.lvpage_previous));
		ae(a, ct(LANG.lvpage_next + String.fromCharCode(8250)));
		ae(j, ct(LANG.lvpage_last + String.fromCharCode(187)));
		ns(d);
		ns(b);
		ns(a);
		ns(j);
		d.onclick = this.firstPage.bind(this);
		b.onclick = this.previousPage.bind(this);
		a.onclick = this.nextPage.bind(this);
		j.onclick = this.lastPage.bind(this);

		if (this.mode == Listview.MODE_CALENDAR) {
			ae(h, ct('a'));
			ae(i, h);
		}
		else {
            ae(h, ct("a"));
            ae(f, ct("a"));
            ae(e, ct("a"));
            ae(i, h);
            ae(i, ct(LANG.hyphen));
            ae(i, f);
            ae(i, ct(LANG.lvpage_of));
            ae(i, e);
        }

		ae(c, d);
		ae(c, b);
		ae(c, i);
		ae(c, a);
		ae(c, j);
		if (g) {
			if (this.hideNav & 1) {
				c.style.display = "none"
			}
		} else {
			if (this.hideNav & 2) {
				c.style.display = "none"
			}
		}
		return c
	},
    createCbBar: function(a) {
		var b = ce("div");
		if (this.createCbControls) {
			this.createCbControls(b, a)
		}
		if (b.firstChild) {
			b.className = "listview-withselected" + (a ? "": "2")
		}
		return b
	},
	refreshRows: function() {
		var a = (this.mode == Listview.MODE_DIV ? this.mainContainer: this.tbody);
		ee(a);
		if (this.nRowsVisible == 0) {
			if (!this.filtered) {
				this.bandTop.style.display = this.bandBot.style.display = "none";
				this.mainContainer.style.display = "none"
			}
			this.noData.style.display = "";
			this.showNoData();
			return
		}
		var o, b, c;
		if (! (this.hideBands & 1)) {
			this.bandTop.style.display = ""
		}
		if (! (this.hideBands & 2)) {
			this.bandBot.style.display = ""
		}
		if (this.nItemsPerPage > 0) {
			o = this.rowOffset;
			b = Math.min(o + this.nRowsVisible, o + this.nItemsPerPage);
			if (this.filtered && this.rowOffset > 0) {
				for (var f = 0, g = 0; f < this.data.length && g < this.rowOffset; ++f) {
					var p = this.data[f];
					if (p.__hidden || p.__deleted) {++o
					} else {++g
					}
				}
				b += (o - this.rowOffset)
			}
		} else {
			o = 0;
			b = this.nRowsVisible
		}
		var h = b - o;
		if (this.mode == Listview.MODE_DIV) {
			for (var e = 0; e < h; ++e) {
				var f = o + e,
				p = this.data[f];
				if (!p) {
					break
				}
				if (p.__hidden || p.__deleted) {++h;
					continue
				}
				ae(this.mainDiv, this.getDiv(f))
			}
		} else {
			if (this.mode == Listview.MODE_TILED) {
				var d = 0,
				l = ce("tr");
				for (var e = 0; e < h; ++e) {
					var f = o + e,
					p = this.data[f];
					if (!p) {
						break
					}
					if (p.__hidden || p.__deleted) {++h;
						continue
					}
					ae(l, this.getCell(f));
					if (++d == this.nItemsPerRow) {
						ae(this.tbody, l);
						if (e + 1 < h) {
							l = ce("tr")
						}
						d = 0
					}
				}
				if (d != 0) {
					for (; d < 4; ++d) {
						var m = ce("td");
						m.className = "empty-cell";
						ae(l, m)
					}
					ae(this.tbody, l)
				}
			}
            else if (this.mode == Listview.MODE_CALENDAR) {
                var tr = ce('tr');

                for(var i = 0; i < 7; ++i) {
                    var th = ce('th');
                    st(th, LANG.date_days[i]);
                    ae(tr, th);
                }

                ae(this.tbody, tr);
                tr = ce('tr');

                for (var k = 0; k < this.dates[o].date.getDay(); ++k) {
                    var foo = ce('td');
                    foo.className = 'empty-cell';
                    ae(tr, foo);
                }

                for (var j = o; j < b; ++j) {
                    ae(tr, this.getEvent(j));

                    if(++k == 7) {
                        ae(this.tbody, tr);
                        tr = ce('tr');
                        k  = 0;
                    }
                }

                if (k != 0) {
                    for(; k < 7; ++k) {
                        var foo = ce('td');
                        foo.className = 'empty-cell';
                        ae(tr, foo);
                    }
                    ae(this.tbody, tr);
                }
            }
            else {
				for (var e = 0; e < h; ++e) {
					var f = o + e,
					p = this.data[f];
					if (!p) {
						break
					}
					if (p.__hidden || p.__deleted) {++h;
						continue
					}
					ae(this.tbody, this.getRow(f))
				}
			}
		}
		this.mainContainer.style.display = "";
		this.noData.style.display = "none"
	},
	showNoData: function() {
		var b = this.noData;
		ee(b);
		var a = -1;
		if (this.onNoData) {
			a = (this.onNoData.bind(this, b))()
		}
		if (a == -1) {
			ae(this.noData, ct(this.filtered ? LANG.lvnodata2: LANG.lvnodata))
		}
	},
	getDiv: function(a) {
		var b = this.data[a];
		if (b.__div == null || this.minPatchVersion != b.__minPatch) {
			this.createDiv(b, a)
		}
		return b.__div
	},
	createDiv: function(b, a) {
		var c = ce("div");
		b.__div = c;
		if (this.minPatchVersion) {
			b.__minPatch = this.minPatchVersion
		} (this.template.compute.bind(this, b, c, a))()
	},
	getCell: function(a) {
		var b = this.data[a];
		if (b.__div == null) {
			this.createCell(b, a)
		}
		return b.__td
	},
	createCell: function(b, a) {
		var c = ce("td");
		b.__td = c;
		(this.template.compute.bind(this, b, c, a))();
		if (this.template.getItemLink) {
			c.onclick = this.itemClick.bind(this, b)
		}
		if (Browser.ie6) {
			c.onmouseover = Listview.itemOver;
			c.onmouseout = Listview.itemOut
		}
	},

	getEvent: function(i) {
		var row = this.dates[i];

		if (row.__td == null) {
			this.createEvent(row, i);
		}

		return row.__td;
	},

	createEvent: function(row, i) {
		row.events = array_filter(this.data, function(holiday) {
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

		var td = ce('td');
		row.__td = td;

		if (row.date.getFullYear() == g_serverTime.getFullYear() && row.date.getMonth() == g_serverTime.getMonth() && row.date.getDate() == g_serverTime.getDate()) {
			td.className = 'calendar-today';
		}

		var div = ce('div');
		div.className = 'calendar-date';
		st(div, row.date.getDate());
		ae(td, div);

		div = ce('div');
		div.className = 'calendar-event';
		ae(td, div);

		(this.template.compute.bind(this, row, div, i))();

		if (this.getItemLink) {
			td.onclick = this.itemClick.bind(this, row);
		}
	},

	getRow: function(a) {
		var b = this.data[a];
		if (b.__tr == null) {
			this.createRow(b)
		}
		return b.__tr
	},
	setRow: function(a) {
		if (this.data[a.pos]) {
			this.data[a.pos] = a;
			this.data[a.pos].__tr = a.__tr;
			this.createRow(this.data[a.pos]);
			this.refreshRows()
		}
	},
	createRow: function(j) {
		var g = ce("tr");
		j.__tr = g;
		if (this.mode == Listview.MODE_CHECKBOX) {
			var c = ce("td");
			if (!j.__nochk) {
				c.className = "listview-cb";
				c.onclick = Listview.cbCellClick;
				var b = ce("input");
				ns(b);
				b.type = "checkbox";
				b.onclick = Listview.cbClick;
				if (j.__chk) {
					b.checked = true;
					if (Browser.ie) {
						b.defaultChecked = true
					}
				}
				j.__cb = b;
				ae(c, b)
			}
			ae(g, c)
		}
		for (var d = 0, e = this.visibility.length; d < e; ++d) {
			var f = this.visibility[d],
			a = this.columns[f],
			c = ce("td"),
			h;
			if (a.align != null) {
				c.style.textAlign = a.align
			}
			if (a.compute) {
				h = (a.compute.bind(this, j, c, g, f))()
			} else {
				if (j[a.value] != null) {
					h = j[a.value]
				} else {
					h = -1
				}
			}
			if (h != -1 && h != null) {
				c.insertBefore(ct(h), c.firstChild)
			}
			ae(g, c)
		}
		if (this.mode == Listview.MODE_CHECKBOX && j.__chk) {
			g.className = "checked"
		}
		if (this.template.getItemLink) {
			g.onclick = this.itemClick.bind(this, j)
		}
		if (Browser.ie6) {
			g.onmouseover = Listview.itemOver;
			g.onmouseout = Listview.itemOut
		}
	},
	itemClick: function(d, c) {
		c = $E(c);
		var a = 0,
		b = c._target;
		while (b && a < 3) {
			if (b.nodeName == "A") {
				return
			}
			b = b.parentNode
		}
		location.href = this.template.getItemLink(d)
	},
	submitSearch: function(c) {
		c = $E(c);
		if (!this.onSearchSubmit || c.keyCode != 13) {
			return
		}
		for (var b = 0, a = this.data.length; b < a; ++b) {
			if (this.data[b].__hidden) {
				continue
			} (this.onSearchSubmit.bind(this, this.data[b]))()
		}
	},
	validatePage: function() {
		var
            c = this.nItemsPerPage,
            b = this.rowOffset,
            a = this.nRowsVisible;

		if (b < 0) {
			this.rowOffset = 0
		}
		else if(this.mode == Listview.MODE_CALENDAR)
			this.rowOffset = Math.min(b, this.nDaysPerMonth.length - 1);
        else {
			this.rowOffset = this.getRowOffset(b + c > a ? a - 1 : b)
		}
	},
	getRowOffset: function(b) {
		var a = this.nItemsPerPage;
		return (a > 0 && b > 0 ? Math.floor(b / a) * a: 0)
	},
	resetRowVisibility: function() {
		for (var b = 0, a = this.data.length; b < a; ++b) {
			this.data[b].__hidden = false
		}
		this.filtered = false;
		this.rowOffset = 0;
		this.nRowsVisible = this.data.length
	},

	getColText: function(row, col) {
		var text = '';
		if (this.template.getVisibleText) {
			text = trim(this.template.getVisibleText(row) + ' ');
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
			return text + col.compute(row, ce('td'), ce('tr'));
		}

		return ""
	},

	updateFilters: function(d) {
		Tooltip.hide();
		this.resetRowVisibility();
		var z, r, c;
		if (this.searchable) {
			this.quickSearchBox.parentNode.style.display = "";
			z = trim(this.quickSearchBox.value);
			if (z) {
				this.quickSearchGlass.style.display = "none";
				this.quickSearchClear.style.display = "";
				z = z.toLowerCase().replace(/\s+/g, " ");
				r = z.split(" ");
				c = r.length
			} else {
				this.quickSearchGlass.style.display = "";
				this.quickSearchClear.style.display = "none"
			}
		} else {
			if (this.quickSearchBox) {
				this.quickSearchBox.parentNode.style.display = "none"
			}
		}
		if (!z && this.nFilters == 0 && this.customFilter == null) {
			if (d) {
				this.updateNav();
				this.refreshRows()
			}
			return
		}
		var C = {
			1 : function(i, j) {
				return i > j
			},
			2 : function(i, j) {
				return i == j
			},
			3 : function(i, j) {
				return i < j
			},
			4 : function(i, j) {
				return i >= j
			},
			5 : function(i, j) {
				return i <= j
			},
			6 : function(i, k, j) {
				return k <= i && i <= j
			}
		};
		var q = {
			1 : function(j, i, k) {
				return i > k
			},
			2 : function(j, i, k) {
				return j <= k && k <= i
			},
			3 : function(j, i, k) {
				return j < k
			},
			4 : function(j, i, k) {
				return i >= k
			},
			5 : function(j, i, k) {
				return j <= k
			},
			6 : function(j, i, E, k) {
				return E <= i && j <= k
			}
		};
		var p = 0;
		for (var w = 0, y = this.data.length; w < y; ++w) {
			var g = this.data[w],
			m = 0;
			nSearchMatches = 0,
			matches = [];
			g.__hidden = true;
			if (this.customFilter && !this.customFilter(g, w)) {
				continue
			}
			for (var v = 0, h = this.visibility.length; v < h; ++v) {
				var o = this.visibility[v];
				var e = this.columns[o];
				if (e.__filter) {
					var a = e.__filter,
					b = false;
					if (e.type == null || e.type == "num" || a.type > 0) {
						var t = null;
						if (e.getValue) {
							t = e.getValue(g)
						} else {
							if (e.value) {
								t = parseFloat(g[e.value])
							}
						}
						if (!t) {
							t = 0
						}
						b = (C[a.type])(t, a.value, a.value2)
					} else {
						if (e.type == "range") {
							var D = e.getMinValue(g),
							B = e.getMaxValue(g);
							b = (q[a.type])(D, B, a.value, a.value2)
						} else {
							var l = this.getColText(g, e);
							if (l) {
								l = l.toString().toLowerCase();
								if (a.invert) {
									b = l.match(a.regex) != null
								} else {
									var A = 0;
									for (var u = 0, f = a.words.length; u < f; ++u) {
										if (l.indexOf(a.words[u]) != -1) {++A
										} else {
											break
										}
									}
									b = (A == a.words.length)
								}
							}
						}
					}
					if (a.invert) {
						b = !b
					}
					if (b) {++m
					} else {
						break
					}
				}
				if (z) {
					var l = this.getColText(g, e);
					if (l) {
						l = l.toString().toLowerCase();
						for (var u = 0, f = r.length; u < f; ++u) {
							if (!matches[u]) {
								if (l.indexOf(r[u]) != -1) {
									matches[u] = 1; ++nSearchMatches
								}
							}
						}
					}
				}
			}
			if (g.__alwaysvisible || ((this.nFilters == 0 || m == this.nFilters) && (!z || nSearchMatches == c))) {
				g.__hidden = false; ++p
			}
		}
		this.filtered = (p < this.data.length);
		this.nRowsVisible = p;
		if (d) {
			this.updateNav();
			this.refreshRows()
		}
	},
	changePage: function() {
		this.validatePage();
		this.refreshRows();
		this.updateNav();
		this.updatePound();
		var a = g_getScroll(),
		b = ac(this.container);
		if (a.y > b[1]) {
			scrollTo(a.x, b[1])
		}
	},
	firstPage: function() {
		this.rowOffset = 0;
		this.changePage();
		return false
	},
	previousPage: function() {
		this.rowOffset -= this.nItemsPerPage;
		this.changePage();
		return false
	},
	nextPage: function() {
		this.rowOffset += this.nItemsPerPage;
		this.changePage();
		return false
	},
	lastPage: function() {
		this.rowOffset = 99999999;
		this.changePage();
		return false
	},
	addSort: function(a, c) {
		var b = in_array(a, Math.abs(c), function(d) {
			return Math.abs(d)
		});
		if (b != -1) {
			c = a[b];
			a.splice(b, 1)
		}
		a.splice(0, 0, c)
	},
	sortBy: function(a) {
		if (a <= 0 || a > this.columns.length) {
			return
		}
		if (Math.abs(this.sort[0]) == a) {
			this.sort[0] = -this.sort[0]
		} else {
			var b = -1;
			if (this.columns[a-1].type == "text") {
				b = 1
			}
			this.addSort(this.sort, b * a)
		}
		this.applySort();
		this.refreshRows();
		this.updateSortArrow();
		this.updatePound()
	},
	applySort: function() {
		if (this.sort.length == 0) {
			return
		}
		Listview.sort = this.sort;
		Listview.columns = this.columns;
		if (this.indexCreated) {
			this.data.sort(Listview.sortIndexedRows.bind(this))
		} else {
			this.data.sort(Listview.sortRows.bind(this))
		}
		this.updateSortIndex()
	},
	setSort: function(b, c, a) {
		if (this.sort.toString() != b.toString()) {
			this.sort = b;
			this.applySort();
			if (c) {
				this.refreshRows()
			}
			if (a) {
				this.updatePound()
			}
		}
	},
	readPound: function() {
		if (!this.poundable || !location.hash.length) {
			return false
		}
		var b = location.hash.substr(1);
		if (this.tabs) {
			var g = b.indexOf(":");
			if (g == -1) {
				return false
			}
			b = b.substr(g + 1)
		}
		var a = parseInt(b);
		if (!isNaN(a)) {
			this.rowOffset = a;
			this.validatePage();
			if (this.poundable != 2) {
				var d = [];
				var f = b.match(/(\+|\-)[0-9]+/g);
				if (f != null) {
					for (var c = f.length - 1; c >= 0; --c) {
						var e = parseInt(f[c]) | 0;
						var b = Math.abs(e);
						if (b <= 0 || b > this.columns.length) {
							break
						}
						this.addSort(d, e)
					}
					this.setSort(d, false, false)
				}
			}
			if (this.tabs) {
				this.tabs.setTabPound(this.tabIndex, this.getTabPound())
			}
		}
	},
	updateSortArrow: function() {
		if (!this.sort.length || !this.thead || this.mode == Listview.MODE_TILED || this.mode == Listview.MODE_CALENDAR) {
			return
		}
		var a = in_array(this.visibility, Math.abs(this.sort[0]) - 1);
		if (a == -1) {
			return
		}
		if (this.mode == Listview.MODE_CHECKBOX) {
			a += 1
		}
		var b = this.thead.firstChild.childNodes[a].firstChild.firstChild.firstChild;
		if (this.lsa && this.lsa != b) {
			this.lsa.className = ""
		}
		b.className = (this.sort[0] < 0 ? "sortdesc": "sortasc");
		this.lsa = b
	},
	updateSortIndex: function() {
		var b = this.data;
		for (var c = 0, a = b.length; c < a; ++c) {
			b[c].__si = c
		}
		this.indexCreated = true
	},
	updateTabName: function() {
		if (this.tabs && this.tabIndex != null) {
			this.tabs.setTabName(this.tabIndex, this.getTabName())
		}
	},
	updatePound: function() {
		if (!this.poundable) {
			return
		}
		var a = this.getTabPound();
		if (this.tabs) {
			this.tabs.setTabPound(this.tabIndex, a);
			location.replace("#" + this.id + ":" + a)
		} else {
			location.replace("#" + a)
		}
	},
	updateNav: function() {
		var e = [this.navTop, this.navBot],
		j = this.nItemsPerPage,
		h = this.rowOffset,
		d = this.nRowsVisible,
		g = 0,
		b = 0,
		f = 0,
		k = 0
        date = new Date();

		if (d > 0) {
			if (! (this.hideNav & 1)) {
				e[0].style.display = ""
			}
			if (! (this.hideNav & 2)) {
				e[1].style.display = ""
			}
		} else {
			e[0].style.display = e[1].style.display = "none"
		}

		if (this.mode == Listview.MODE_CALENDAR) {
			for (var i = 0; i < this.nDaysPerMonth.length; ++i) {
				if (i == h)  { // Selected month
					if (i > 0)
						b = 1;
					if (i > 1)
						g = 1;
					if (i < this.nDaysPerMonth.length - 1)
						f = 1;
					if (i < this.nDaysPerMonth.length - 2)
						k = 1;
				}
			}

			date.setTime(this.startOnMonth.valueOf());
			date.setMonth(date.getMonth() + h);
		}
		else
		{
            if (j) {
                if (h > 0) {
                    b = 1;
                    if (h >= j + j) {
                        g = 1
                    }
                }
                if (h + j < d) {
                    f = 1;
                    if (h + j + j < d) {
                        k = 1
                    }
                }
            }
		}

		for (var c = 0; c < 2; ++c) {
			var a = e[c].childNodes;
			a[0].style.display = (g ? "": "none");
			a[1].style.display = (b ? "": "none");
			a[3].style.display = (f ? "": "none");
			a[4].style.display = (k ? "": "none");
			a = a[2].childNodes;

			if (this.mode == Listview.MODE_CALENDAR)
				a[0].firstChild.nodeValue = LANG.date_months[date.getMonth()] + ' ' + date.getFullYear();
			else {
                a[0].firstChild.nodeValue = h + 1;
                a[2].firstChild.nodeValue = j ? Math.min(h + j, d) : d;
                a[4].firstChild.nodeValue = d;
            }
		}
	},
	getTabName: function() {
		var b = this.name,
		d = this.data.length;
		for (var c = 0, a = this.data.length; c < a; ++c) {
			if (this.data[c].__hidden || this.data[c].__deleted) {--d
			}
		}
		if (d > 0 && !this.hideCount) {
			b += sprintf(LANG.qty, d)
		}
		return b
	},
	getTabPound: function() {
		var a = "";
		a += this.rowOffset;
		if (this.poundable != 2 && this.sort.length) {
			a += ("+" + this.sort.join("+")).replace(/\+\-/g, "-")
		}
		return a
	},
	getCheckedRows: function() {
		var d = [];
		for (var c = 0, a = this.data.length; c < a; ++c) {
			var b = this.data[c];
			if ((b.__cb && b.__cb.checked) || (!b.__cb && b.__chk)) {
				d.push(b)
			}
		}
		return d
	},
	deleteRows: function(c) {
		if (!c || !c.length) {
			return
		}
		for (var b = 0, a = c.length; b < a; ++b) {
			var d = c[b];
			if (!d.__hidden && !d.__hidden) {
				this.nRowsVisible -= 1
			}
			d.__deleted = true
		}
		this.updateTabName();
		if (this.rowOffset >= this.nRowsVisible) {
			this.previousPage()
		} else {
			this.refreshRows();
			this.updateNav()
		}
	},
	setData: function(a) {
		this.data = a;
		this.indexCreated = false;
		this.resetRowVisibility();
		if (this.tabs) {
			this.pounded = (this.tabs.poundedTab == this.tabIndex);
			if (this.pounded) {
				this.readPound()
			}
		} else {
			this.readPound()
		}
		this.applySort();
		this.updateSortArrow();
		if (this.customFilter != null) {
			this.updateFilters()
		}
		this.updateNav();
		this.refreshRows()
	},
	getClipDiv: function() {
		return this.clipDiv
	},
	getNoteTopDiv: function() {
		return this.noteTop
	},
	focusSearch: function() {
		this.quickSearchBox.focus()
	},
	clearSearch: function() {
		this.quickSearchBox.value = ""
	},
	getList: function() {
		if (!this.debug) {
			return
		}
		var b = "";
		for (var a = 0; a < this.data.length; a++) {
			if (!this.data[a].__hidden) {
				b += this.data[a].id + ", "
			}
		}
		prompt("", b)
	}
};
Listview.sortRows = function(e, d) {
	var j = Listview.sort,
	k = Listview.columns;
	for (var h = 0, c = j.length; h < c; ++h) {
		var g, f = k[Math.abs(j[h]) - 1];
        if (!f)
			f = this.template;
        if (f.sortFunc) {
			g = f.sortFunc(e, d, j[h])
		} else {
			g = strcmp(e[f.value], d[f.value])
		}
		if (g != 0) {
			return g * j[h]
		}
	}
	return 0
},
Listview.sortIndexedRows = function(d, c) {
	var g = Listview.sort,
	h = Listview.columns,
	e = h[Math.abs(g[0]) - 1],
	f;
	if (e.sortFunc) {
		f = e.sortFunc(d, c, g[0])
	} else {
		f = strcmp(d[e.value], c[e.value])
	}
	if (f != 0) {
		return f * g[0]
	}
	return (d.__si - c.__si)
},
Listview.cbSelect = function(b) {
	for (var d = 0, a = this.data.length; d < a; ++d) {
		var c = this.data[d];
		var f = b;
		if (!c.__nochk && c.__tr) {
			var e = c.__tr.firstChild.firstChild;
			if (f == null) {
				f = !e.checked
			}
			if (e.checked != f) {
				e.checked = f;
				c.__tr.className = (e.checked ? "checked": "");
				if (Browser.ie) {
					e.defaultChecked = f;
					if (Browser.ie6) { (Listview.itemOut.bind(c.__tr))()
					}
				}
			}
		} else {
			if (f == null) {
				f = true
			}
		}
		c.__chk = f
	}
};
Listview.cbClick = function(a) {
	setTimeout(Listview.cbUpdate.bind(0, 0, this, this.parentNode.parentNode), 1);
	sp(a)
};
Listview.cbCellClick = function(a) {
	setTimeout(Listview.cbUpdate.bind(0, 1, this.firstChild, this.parentNode), 1);
	sp(a)
};
Listview.cbIeFix = function() {
	var d = gE(this.tbody, "tr");
	for (var c = 0, a = d.length; c < a; ++c) {
		var b = d[c].firstChild.firstChild;
		if (b) {
			b.checked = b.defaultChecked = false
		}
	}
};
Listview.cbUpdate = function(c, a, b) {
	if (c) {
		a.checked = !a.checked
	}
	b.className = (a.checked ? "checked": "");
	if (Browser.ie) {
		a.defaultChecked = a.checked;
		if (Browser.ie6) { (Listview.itemOver.bind(b))()
		}
	}
};
Listview.itemOver = function() {
	this.style.backgroundColor = (this.className == "checked" ? "#2C2C2C": "#202020")
};
Listview.itemOut = function() {
	this.style.backgroundColor = (this.className == "checked" ? "#242424": "transparent")
};
Listview.headerClick = function(a, b, c) {
	c = $E(c);
	if (c._button == 3 || c.shiftKey || c.ctrlKey) {
		Tooltip.hide();
		setTimeout(Listview.headerFilter.bind(this, a, null), 1)
	} else {
		this.sortBy(b + 1)
	}
	return false
};
Listview.headerFilter = function(c, f) {
	var j = "";
	if (c.__filter) {
		if (c.__filter.invert) {
			j += "!"
		}
		j += c.__filter.text
	}
	if (f == null) {
		var f = prompt(sprintf(LANG.prompt_colfilter1 + (c.type == "text" ? LANG.prompt_colfilter2: LANG.prompt_colfilter3), c.name), j)
	}
	if (f != null) {
		var e = {
			text: "",
			type: -1
		};
		f = trim(f.replace(/\s+/g, " "));
		if (f) {
			if (f.charAt(0) == "!" || f.charAt(0) == "-") {
				e.invert = 1;
				f = f.substr(1)
			}
			if (c.type == "text") {
				e.type = 0;
				e.text = f;
				if (e.invert) {
					e.regex = g_createOrRegex(f)
				} else {
					e.words = f.toLowerCase().split(" ")
				}
			}
			var i, b;
			if (f.match(/(>|=|<|>=|<=)\s*([0-9\.]+)/)) {
				i = parseFloat(RegExp.$2);
				if (!isNaN(i)) {
					switch (RegExp.$1) {
					case ">":
						e.type = 1;
						break;
					case "=":
						e.type = 2;
						break;
					case "<":
						e.type = 3;
						break;
					case ">=":
						e.type = 4;
						break;
					case "<=":
						e.type = 5;
						break
					}
					e.value = i;
					e.text = RegExp.$1 + " " + i
				}
			} else {
				if (f.match(/([0-9\.]+)\s*\-\s*([0-9\.]+)/)) {
					i = parseFloat(RegExp.$1);
					b = parseFloat(RegExp.$2);
					if (!isNaN(i) && !isNaN(b)) {
						if (i > b) {
							var g = i;
							i = b;
							b = g
						}
						if (i == b) {
							e.type = 2;
							e.value = i;
							e.text = "= " + i
						} else {
							e.type = 6;
							e.value = i;
							e.value2 = b;
							e.text = i + " - " + b
						}
					}
				} else {
					var d = f.toLowerCase().split(" ");
					if (d.length == 1 && !isNaN(i = parseFloat(d[0]))) {
						e.type = 2;
						e.value = i;
						e.text = "= " + i
					} else {
						if (c.type == "text") {
							e.type = 0;
							e.text = f;
							if (e.invert) {
								e.regex = g_createOrRegex(f)
							} else {
								e.words = d
							}
						}
					}
				}
			}
			if (e.type == -1) {
				alert(LANG.message_invalidfilter);
				return
			}
		}
		if (!c.__filter || e.text != c.__filter.text || e.invert != c.__filter.invert) {
			var h = c.__th.firstChild.firstChild;
			if (f && e.text) {
				if (!c.__filter) {
					h.className = "q5"; ++(this.nFilters)
				}
				c.__filter = e
			} else {
				if (c.__filter) {
					h.className = ""; --(this.nFilters)
				}
				c.__filter = null
			}
			this.updateFilters(1)
		}
	}
};
Listview.headerOver = function(b, c, f) {
	var d = "";
	d += '<b class="q1">' + (c.tooltip ? c.tooltip: c.name) + "</b>";
	if (c.__filter) {
		d += "<br />" + sprintf((c.__filter.invert ? LANG.tooltip_colfilter2: LANG.tooltip_colfilter1), c.__filter.text)
	}
	d += '<br /><span class="q2">' + LANG.tooltip_lvheader1 + "</span>";
	if (this.filtrable && (c.filtrable == null || c.filtrable)) {
		d += '<br /><span class="q2">' + (Browser.opera ? LANG.tooltip_lvheader3: LANG.tooltip_lvheader2) + "</span>"
	}
	Tooltip.show(b, d, 0, 0, "q")
};
Listview.extraCols = {
    id: {
        id: 'id',
        name: 'ID',
        width: '5%',
        value: 'id',
        compute: function(data, td) {
            if (data.id) {
                ae(td, ct(data.id));
            }
        }
    },

    date: {
        id: 'obj-date',
        name: LANG.added,
        compute: function(data, td) {
            if (data.date) {
                if (data.date <= 86400) {
                    ae(td, ct('???'));
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
                array_walk(a.cost[2], function(x, _, __, i) {
                    lena += Math.pow(10, i) + x[1];
                });
            }
            if (b.cost[2] != null) {
                array_walk(b.cost[2], function(x, _, __, i) {
                    lenb += Math.pow(10, i) + x[1];
                });
            }
            if (a.cost[1] != null) {
                array_walk(a.cost[1], function(x, _, __, i) {
                    lenc += Math.pow(10, i) + x[1];
                });
            }
            if (b.cost[1] != null) {
                array_walk(b.cost[1], function(x, _, __, i) {
                    lend += Math.pow(10, i) + x[1];
                });
            }

            return strcmp(lena, lenb) || strcmp(lenc, lend) || strcmp(a.cost[0], b.cost[0]);
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
                var d = ce('div');
                d.className = 'small q0';
                ae(d, ct(sprintf(LANG.lvdrop_outof, row.outof)));
                ae(td, d);
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

            return strcmp(a.count, b.count);
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

                    text += sprintf(LANG.stackof_format, amt, pct) + '<br />';
                }

                td.className += ' tip';
                g_addTooltip(td, text);
            }

            var value = parseFloat(row.percent.toFixed(row.percent >= 1.95 ? 0 : (row.percent >= 0.195 ? 1 : 2)));

            if (row.pctstack) {
                var sp = ce('span');
                sp.className += ' tip';
                ae(sp, ct(value));
                ae(b, sp);
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

            return strcmp(acmp, bcmp);
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

            array_walk(a.currency, function(x, _, __, i) {
                lena += Math.pow(10, i) + x[1];
            });
            array_walk(b.currency, function(x, _, __, i) {
                lenb += Math.pow(10, i) + x[1];
            });

            return strcmp(lena, lenb);
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
                    return sprintf(LANG['tab_' + specificMode + 'X'], specificPlayers); // e.g. "Heroic 25"
                }
                else {
                    return LANG['tab_' + specificMode]; // e.g. "Heroic"
                }
            }

            if (specificPlayers) {
                return sprintf(LANG.lvzone_xman, specificPlayers); // e.g. "25-player"
            }

            return LANG.pr_note_all;
        },
        sortFunc: function(a, b, col) {
            if (a.modes && b.modes) {
                return -strcmp(a.modes.mode, b.modes.mode);
            }
        }
    },

    requires: {
        id: 'requires',
        name: LANG.requires,
        type: 'text',
        compute: function(item, td) {
            if (item.achievement && g_achievements[item.achievement]) {
                nw(td);
                td.className = 'small';
                td.style.lineHeight = '18px';

                var a = ce('a');
                a.href = '?achievement=' + item.achievement;
                a.className = 'icontiny tinyspecial';
                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + g_achievements[item.achievement].icon.toLowerCase() + '.gif)';
                a.style.whiteSpace = 'nowrap';

                st(a, g_achievements[item.achievement]['name_' + g_locale.name]);
                ae(td, a);
            }
        },
        getVisibleText: function(item) {
            if (item.achievement && g_achievements[item.achievement]) {
                return g_achievements[item.achievement].name;
            }
        },
        sortFunc: function(a, b, col) {
            return strcmp(this.getVisibleText(a), this.getVisibleText(b));
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
                var i = ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                ae(i, g_items.createIcon(row.yield, 1));
                ae(tr, i);
                td.style.borderLeft = 'none';

                var wrapper = ce('div');

                var a = ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + row.yield;
                a.className = 'q' + g_items[row.yield].quality;
                ae(a, ct(g_items[row.yield]['name_' + g_locale.name]));
                ae(wrapper, a);
                ae(td, wrapper);
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
            return -strcmp(g_items[a.yield].quality, g_items[b.yield].quality) ||
                    strcmp(g_items[a.yield]['name_' + g_locale.name], g_items[b.yield]['name_' + g_locale.name]);
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
            td.className = 'small';
            td.style.lineHeight = '18px';

            if (row.condition) {
                switch(g_types[row.condition.type]) {
                    case 'spell':
                        return Listview.extraCols.condition.getSpellText(row.condition, td);
                    case 'item':
                        return Listview.extraCols.condition.getItemText(row.condition, td);
                    case 'achievement':
                        return Listview.extraCols.condition.getAchievementText(row.condition, td);
                    case 'quest':
                        return Listview.extraCols.condition.getQuestText(row.condition, td);
                    default:
                        return 'unhandled condition';
                }
            }
        },
        getSpellText: function(cond, td) {
            if (!cond.typeId || !g_spells[cond.typeId]) {
                return;
            }

            var item = g_spells[cond.typeId];
            var span = ce('span');
            span.className = cond.status ? 'q2' : 'q10';
            ae(span, cond.status ? ct(LANG.pr_note_known) : ct(LANG.pr_note_missing));
            ae(td, span);
            ae(td, ce('br'));

            var a = ce('a');
            a.href = '?spell=' + cond.typeId;
            a.className = 'icontiny tinyspecial';
            a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
            a.style.whiteSpace = 'nowrap';
            ae(a, ct(item['name_' + g_locale.name]));
            ae(td, a);
        },
        getItemText: function(cond, td) {
            if (!cond.typeId || !g_items[cond.typeId]) {
                return;
            }

            var item = g_items[cond.typeId];
            var span = ce('span');
            span.className = cond.status ? 'q2' : 'q10';
            ae(span, cond.status ? ct(LANG.pr_note_earned) : ct(LANG.pr_note_missing));
            ae(td, span);
            ae(td, ce('br'));

            var a = ce('a');
            a.href = '?item=' + cond.typeId;
            a.className = 'icontiny tinyspecial';
            a.className += ' q' + item.quality;
            a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
            a.style.whiteSpace = 'nowrap';
            ae(a, ct(item['name_' + g_locale.name]));
            ae(td, a);
        },
        getAchievementText: function(cond, td) {
            if (!cond.typeId || !g_achievements[cond.typeId]) {
                return;
            }

            var item = g_achievements[cond.typeId];
            var span = ce('span');
            span.className = cond.status ? 'q2' : 'q10';
            ae(span, cond.status ? ct(LANG.pr_note_earned) : ct(LANG.pr_note_incomplete));
            ae(td, span);
            ae(td, ce('br'));

            var a = ce('a');
            a.href = '?achievement=' + cond.typeId;
            a.className = 'icontiny tinyspecial';
            a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
            a.style.whiteSpace = 'nowrap';
            st(a, item['name_' + g_locale.name]);
            ae(td, a);
        },
        getQuestText: function(cond, td) {
            if (!cond.typeId || !g_quests[cond.typeId]) {
                return;
            }

            var item = g_quests[cond.typeId];
            var span = ce('span');
            span.className = cond.status == 1 ? 'q1' : cond.status == 2 ? 'q2' : 'q10';
            ae(span, cond.status == 1 ? ct(LANG.progress) : cond.status == 2 ? ct(LANG.pr_note_complete) : ct(LANG.pr_note_incomplete));
            ae(td, span);
            ae(td, ce('br'));

            var a = ce('a');
            a.href = '?quest=' + cond.typeId;
            a.style.whiteSpace = 'nowrap';
            st(a, item['name_' + g_locale.name]);
            ae(td, a);
        },
        sortFunc: function(a, b, col) {
            if (a.condition.status && b.condition.status) {
                return strcmp(a.condition.status, b.condition.status);
            }
        }
    },
};

Listview.funcBox = {
    assocBinFlags: function(f, arr) {
        var res = [];
        for (var i in arr) {
            if (!isNaN(i) && (f & 1 << i - 1)) {
                res.push(i);
            }
        }
        res.sort(function(a, b) {
            return strcmp(arr[a], arr[b]);
        });

        return res;
    },

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

            var res = strcmp(arr[a[i]], arr[b[i]]);
            if (res != 0) {
                return res;
            }
        }

        return 0
    },

	location: function(row, td) {
		if (row.location == null) {
			return -1;
		}

		for (var i = 0, len = row.location.length; i < len; ++i) {
			if (i > 0) {
				ae(td, ct(LANG.comma));
			}

			var zoneId = row.location[i];
			if (zoneId == -1) {
				ae(td, ct(LANG.ellipsis));
			}
            else {
				var a = ce('a');
				a.className = 'q1';
				a.href = '?zone=' + zoneId;
				ae(a, ct(g_zones[zoneId]));
				ae(td, a);
			}
		}
	},

    arrayText: function(arr, lookup) {
        if (arr == null) {
            return;
        }
        else if (!is_array(arr)) {
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
                d = ce('div'),
                d2 = ce('div');

            ae(document.body, d);

            if (text && (arr.length != 1 || type != 2)) {
                var bibi = ce('div');
                bibi.style.position = 'relative';
                bibi.style.width = '1px';
                var bibi2 = ce('div');
                bibi2.className = 'q0';
                bibi2.style.position = 'absolute';
                bibi2.style.right = '2px';
                bibi2.style.lineHeight = '26px';
                bibi2.style.fontSize = '11px';
                bibi2.style.whiteSpace = 'nowrap';
                ae(bibi2, ct(text));
                ae(bibi, bibi2);
                ae(d, bibi);

                d.style.paddingLeft = bibi2.offsetWidth + 'px';
            }

            var iconPool = g_items;
            if (type == 1) {
                iconPool = g_spells;
            }

            for (var i = 0, len = arr.length; i < len; ++i) {
                var icon;
                if (arr[i] == null) {
                    icon = ce('div');
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
                        ee(d);
                        var
                            item = g_items[id],
                            a = ce('a'),
                            sp = ce('span');

                            sp.style.paddingTop = '4px';

                        a.href = '?item=' + id;
                        a.className = 'q' + item.quality + ' icontiny tinyspecial';
                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                        a.style.whiteSpace = 'nowrap';

                        st(a, item['name_' + g_locale.name]);
                        ae(sp, a);

                        if (num > 1) {
                            ae(sp, ct(' (' + num + ')'));
                        }

                        if (text) {
                            var bibi = ce('span');
                            bibi.className = 'q0';
                            bibi.style.fontSize = '11px';
                            bibi.style.whiteSpace = 'nowrap';
                            ae(bibi, ct(text));
                            ae(d, bibi);
                            sp.style.paddingLeft = bibi.offsetWidth + 'px';
                        }

                        ae(d, sp);
                    }
                }
                else {
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    ae(d, icon);

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';

                    d.style.width = (26 * arr.length) + 'px';
                }
            }

            d2.className = 'clear';

            ae(td, d);
            ae(td, d2);

            return true;
        }
    },

    createSocketedIcons: function(sockets, td, gems, match, text) {
		var
            nMatch = 0,
            d      = ce('div'),
            d2     = ce('div');

        for (var i = 0, len = sockets.length; i < len; ++i) {
			var
                icon,
                gemId = gems[i];

				if (g_items && g_items[gemId]) {
					icon = g_items.createIcon(gemId, 0);
				}
                else if (isset('g_gems') && g_gems && g_gems[gemId]) {
					icon = Icon.create(g_gems[gemId].icon, 0, null, '?item=' + gemId);
                }
                else {
                    icon = Icon.create(null, 0, null, 'javascript:;');
				}

            icon.className += ' iconsmall-socket-' + g_file_gems[sockets[i]] + (!gems || !gemId ? '-empty': '');
			icon.style.cssFloat = icon.style.styleFloat = 'left';

			if (match && match[i]) {
				icon.insertBefore(ce('var'), icon.childNodes[1]);
                ++nMatch;
			}

			ae(d, icon);
		}

		d.style.margin = '0 auto';
		d.style.textAlign = 'left';

		d.style.width = (26 * sockets.length) + 'px';
		d2.className = 'clear';

		ae(td, d);
		ae(td, d2);

		if (text && nMatch == sockets.length) {
			d = ce('div');
			d.style.paddingTop = '4px';
			ae(d, ct(text));
			ae(td, d);
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

	getQuestReputation: function(d, b) {
		if (b.reprewards) {
			for (var c = 0, a = b.reprewards.length; c < a; ++c) {
				if (b.reprewards[c][0] == d) {
					return b.reprewards[c][1]
				}
			}
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

    getFactionCategory: function(category, category2) {
        if (category) {
            return g_faction_categories[category];
        }
        else {
            return g_faction_categories[category2];
        }
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
				if (!confirm(sprintf((d == 0 ? LANG.confirm_report: LANG.confirm_report2), g_report_reasons[f]))) {
					return
				}
			}
		}
		if (a != null) {
			var e = "?report&type=" + d + "&typeid=" + b + "&reason=" + f;
			if (a) {
				e += "&reasonmore=" + urlencode(a)
			}
			new Ajax(e);
			var c = ce("span");
			ae(c, ct(LANG.lvcomment_reported));
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
		b.__div.className = trim(b.__div.className.replace("comment-collapsed", "")) + (c ? "": " comment-collapsed");
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
			var d = prompt(sprintf(LANG.prompt_customrating, c, c), 0);
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
		Tooltip.hide();
		b = b.childNodes[b.childNodes.length - 3];
        var f = ge("commentrating" + e.id);
        Listview.funcBox.coDisplayRating(e, f);
		de(b.nextSibling);
		de(b.nextSibling);
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
		var f = ce("div");
		f.className = "comment-edit";
		g.divEdit = f;
		if (e == -1) {
			if (g_users[g.user] != null) {
				g.roles = g_users[g.user].roles
			}
		}
		var a = Listview.funcBox.coEditAppend(f, g, e, c);
		var b = ce("div");
		b.className = "comment-edit-buttons";
		var d = ce("input");
		d.type = "button";
		d.value = LANG.compose_save;
		d.onclick = Listview.funcBox.coEditButton.bind(d, g, true, e, c);
		ae(b, d);
		ae(b, ct(" "));
		d = ce("input");
		d.type = "button";
		d.value = LANG.compose_cancel;
		d.onclick = Listview.funcBox.coEditButton.bind(d, g, false, e, c);
		ae(b, d);
		ae(f, b);
		var c = f;
		if (Browser.ie6) {
			c = ce("div");
			c.style.width = "99%";
			ae(c, f)
		}
        ae(g.divBody.parentNode, f)
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
			var j = ce("div");
			j.className = "comment-edit-modes";
			ae(j, ct(LANG.compose_mode));
			var p = ce("a");
			p.className = "selected";
			p.onclick = Listview.funcBox.coModeLink.bind(p, 1, l, b);
			p.href = "javascript:;";
			ae(p, ct(LANG.compose_edit));
			ae(j, p);
			ae(j, ct("|"));
			var w = ce("a");
			w.onclick = Listview.funcBox.coModeLink.bind(w, 2, l, b);
			w.href = "javascript:;";
			ae(w, ct(LANG.compose_preview));
			ae(j, w);
			ae(m, j)
		}
		var a = ce("div");
		a.style.display = "none";
		a.className = "comment-body" + Listview.funcBox.coGetColor(b, l);
		ae(m, a);
		var h = ce("div");
		h.className = "comment-edit-body";
		var e = ce("div");
		e.className = "toolbar";
        e.style.cssFloat = "left";
		var i = ce("div");
		i.className = "menu-buttons";
        i.style.cssFloat = "left";
		var g = ce("textarea");
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
			g.rows = (Browser.firefox ? 2 : 3);
			g.style.height = "auto";
			break
		}
		if (l != -1 && l != 0) {
			var d = ce("h3"),
			y = ce("a"),
			v = ce("div"),
			u = ce("div");
			var c = Listview.funcBox.coLivePreview.bind(g, b, l, v);
			if (b.body) {
				y.className = "disclosure-off";
				v.style.display = "none"
			} else {
				y.className = "disclosure-on"
			}
			ae(y, ct(LANG.compose_livepreview));
			ae(d, y);
			y.href = "javascript:;";
			y.onclick = function() {
				c(1);
				y.className = "disclosure-" + (g_toggleDisplay(v) ? "on": "off")
			};
			ns(y);
			d.className = "first";
			u.className = "pad";
			ae(a, d);
			ae(a, v);
			ae(a, u);
			g_onAfterTyping(g, c, 50);
			aE(g, "focus", function() {
				c();
				a.style.display = "";
				if (l != 4) {
					g.style.height = "22em"
				}
			})
		} else {
			if (l != 4) {
				aE(g, "focus", function() {
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
                var o = ce("button");
                o.setAttribute("type", "button");
                o.title = k.title;
                if (k.onclick != null) {
                    o.onclick = k.onclick
                } else {
                    o.onclick = g_insertTag.bind(0, g, k.pre, k.post, k.rep)
                }

                var z = ce("img");
                z.src = "template/images/pixel.gif";
                z.className = "toolbar-" + k.id;
                ae(o, z);
                ae(e, o)
            }
        } else {
			for (var B = 0, C = t.length; B < C; ++B) {
				var q = t[B];
				if ((g_user.rolls & U_GROUP_PENDING) && q.nopending) {
					continue
				}
				var H = "tb-" + q.id;
				var V = ce('button');
				V.onclick = function(i, L) {
                    L.preventDefault();
                    (i.onclick != null ? i.onclick: g_insertTag.bind(0, g, i.pre, i.post, i.rep))()
                };
                V.bind(null, q);
				V.className = H;
				V.title = q.title;
				V[0].setAttribute("type", "button");
				ae(V, ce('ins'));
				ae(e, V);
			}
			e.className += " formatting button sm";
		}
		var r = function(L, i) {
			var M = prompt(sprintf(LANG.markup_prompt, L), "");
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
        var di = ce('div');
        ae(di, e);
        ae(di, i);
        ae(h, di);
  		ae(h, ce("div"));
		ae(h, g);
		ae(h, ce("br"));
		Menu.addButtons(i, A);
		if (l == 4) {
			ae(h, ct(sprintf(LANG.compose_limit2, f, 3)))
		} else {
			ae(h, ct(sprintf(LANG.compose_limit, f)))
		}
        var A = ce('span');
        A.className = "comment-remaining";
        ae(A, ct(sprintf(LANG.compose_remaining, l - b.body.length)));
        ae(h, A);
		g.onkeyup = Listview.funcBox.coUpdateCharLimit.bind(0, g, A, f);
		g.onkeydown = Listview.funcBox.coUpdateCharLimit.bind(0, g, A, f);
		if ((l == -1 || l == 0) && g_user.roles & U_GROUP_MODERATOR) {
			var B = ce("div");
            B.classname = "pad";
			var W = ce("div");
			ae(W, ct((g_user.roles & U_GROUP_ADMIN ? "Admin": "Moderator") + " response"));
			var p = ce("textarea");
            p.value = b.response;
            p.rows = 3;
			p.style.height = "6em";
			ae(h, B);
			ae(h, w);
			ae(h, p)
		}
		ae(m, h);
		ae(m, ce('div'));
		ae(m, a);

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
			var j = ce("div");
			j.className = "comment-edit-modes";
			ae(j, ct(LANG.compose_mode));
			var p = ce("a");
			p.className = "selected";
			p.onclick = Listview.funcBox.coModeLink.bind(p, 1, l, b);
			p.href = "javascript:;";
			ae(p, ct(LANG.compose_edit));
			ae(j, p);
			ae(j, ct("|"));
			var w = ce("a");
			w.onclick = Listview.funcBox.coModeLink.bind(w, 2, l, b);
			w.href = "javascript:;";
			ae(w, ct(LANG.compose_preview));
			ae(j, w);
			ae(m, j)
		}
		var a = ce("div");
		a.style.display = "none";
		a.className = "comment-body" + Listview.funcBox.coGetColor(b, l);
		ae(m, a);
		var h = ce("div");
		h.className = "comment-edit-body";
		var e = ce("div");
		e.className = "toolbar";
		var g = ce("textarea");
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
			g.rows = (Browser.gecko ? 2 : 3);
			g.style.height = "auto";
			break
		}
		if (l != -1 && l != 0) {
			var d = ce("h3"),
			y = ce("a"),
			v = ce("div"),
			u = ce("div");
			var c = Listview.funcBox.coLivePreview.bind(g, b, l, v);
			if (b.body) {
				y.className = "disclosure-off";
				v.style.display = "none"
			} else {
				y.className = "disclosure-on"
			}
			ae(y, ct(LANG.compose_livepreview));
			ae(d, y);
			y.href = "javascript:;";
			y.onclick = function() {
				c(1);
				y.className = "disclosure-" + (g_toggleDisplay(v) ? "on": "off")
			};
			ns(y);
			d.className = "first";
			u.className = "pad";
			ae(a, d);
			ae(a, v);
			ae(a, u);
			g_onAfterTyping(g, c, 50);
			aE(g, "focus", function() {
				c();
				a.style.display = "";
				if (l != 4) {
					g.style.height = "22em"
				}
			})
		} else {
			if (l != 4) {
				aE(g, "focus", function() {
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
			var o = ce("button");
			var z = ce("img");
			o.setAttribute("type", "button");
			o.title = k.title;
			if (k.onclick != null) {
				o.onclick = k.onclick
			} else {
				o.onclick = g_insertTag.bind(0, g, k.pre, k.post, k.rep)
			}
			z.src = "template/images/pixel.gif";
			z.className = "toolbar-" + k.id;
			ae(o, z);
			ae(e, o)
		}
		ae(h, e);
		ae(h, g);
		ae(h, ce("br"));
		if (l == 4) {
			ae(h, ct(sprintf(LANG.compose_limit2, f, 3)))
		} else {
			ae(h, ct(sprintf(LANG.compose_limit, f)))
		}
		ae(m, h);
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
			var a = gE(f.divEdit, "textarea");
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
				var j = "body=" + urlencode(f.body);
				if (f.response !== undefined) {
					j += "&response=" + urlencode(f.response)
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
		de(f.divEdit);
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
			b.innerHTML = (" " + sprintf(LANG.compose_remaining, c - d.length))
            b.className.replace(/(?:^|\s)q10(?!\S)/g , '');
			if (d.length == c) {
				b.className += " q10";
			}
		}
	},
	coModeLink: function(e, b, f) {
        var j = Listview.funcBox.coGetCharLimit(b);
		var c = Markup.MODE_COMMENT;
		array_walk(gE(this.parentNode, "a"), function(k) {
			k.className = ""
		});
		this.className = "selected";
		var d = gE(this.parentNode.parentNode, "textarea"),
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
		var a = ge("replybox-generic");
		gE(a, "span")[0].innerHTML = b.user;
		a.style.display = "";
		co_addYourComment()
	},
	coValidate: function(a, c) {
		c |= 0;
		if (c == 1 || c == -1) {
			if (trim(a.value).length < 1) {
				alert(LANG.message_forumposttooshort);
				return false
			}
		} else {
			if (trim(a.value).length < 10) {
				alert(LANG.message_commenttooshort);
				return false
			}
		}
		var b = Listview.funcBox.coGetCharLimit(c);
		if (a.value.length > b) {
			if (!confirm(sprintf(c == 1 ? LANG.confirm_forumposttoolong: LANG.confirm_commenttoolong, b, a.value.substring(b - 30, b)))) {
				return false
			}
		}
		return true
	},
	coCustomRatingOver: function(a) {
		Tooltip.showAtCursor(a, LANG.tooltip_customrating, 0, 0, "q")
	},
	coPlusRatingOver: function(a) {
		Tooltip.showAtCursor(a, LANG.tooltip_uprate, 0, 0, "q2")
	},
	coMinusRatingOver: function(a) {
		Tooltip.showAtCursor(a, LANG.tooltip_downrate, 0, 0, "q10")
	},
	coSortDate: function(a) {
		a.nextSibling.nextSibling.className = "";
		a.className = "selected";
		this.mainDiv.className += " listview-aci";
		this.setSort([1], true, false)
		sc("temp_comment_sort", 1)
	},
	coSortHighestRatedFirst: function(a) {
		a.previousSibling.previousSibling.className = "";
		a.className = "selected";
		this.mainDiv.className = this.mainDiv.className.replace("listview-aci", "");
		this.setSort([ - 3, 2], true, false)
		sc("temp_comment_sort", 2)
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
				de(b.childNodes[3].firstChild)
			}
			Listview.funcBox.coFormatDate(b.childNodes[3], d, c);
			var a = "";
			if (f.rating != null) {
				a += ct(sprintf(LANG.lvcomment_patch, g_getPatchVersion(c)))
			}
			if (e[1] > 1) {
				a += LANG.dash + sprintf(LANG.lvcomment_nedits, e[1])
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
			var a = sprintf(LANG.date_ago, g_formatTimeElapsed(e));
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
		d = ct(a);
		ae(f, d)
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

		Tooltip.showAtCursor(e, '<span class="q1">' + event.name + '</span><br />' + buff, 0, 0, 'q');
	},

	ssCellOver: function() {
		this.className = 'screenshot-caption-over';
	},

	ssCellOut: function() {
		this.className = 'screenshot-caption';
	},

	ssCellClick: function(i, e) {
		e = $E(e);

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

	viCellClick: function(i, e) {
		e = $E(e);

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
		Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_honorpoints + '</b>', 0, 0, 'q1');
	},

	moneyArenaOver: function(e) {
		Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_arenapoints + '</b>', 0, 0, 'q1');
	},

	moneyAchievementOver: function(e) {
		Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_achievementpoints + '</b>', 0, 0, 'q1');
	},

	appendMoney: function(g, a, f, m, j, c, l) {
		var k, h = 0;
		if (a >= 10000) {
			h = 1;
			k = ce("span");
			k.className = "moneygold";
			ae(k, ct(Math.floor(a / 10000)));
			ae(g, k);
			a %= 10000
		}
		if (a >= 100) {
			if (h) {
				ae(g, ct(" "))
			} else {
				h = 1
			}
			k = ce("span");
			k.className = "moneysilver";
			ae(k, ct(Math.floor(a / 100)));
			ae(g, k);
			a %= 100
		}
		if (a >= 1 || f != null) {
			if (h) {
				ae(g, ct(" "))
			} else {
				h = 1
			}
			k = ce("span");
			k.className = "moneycopper";
			ae(k, ct(a));
			ae(g, k)
		}
		if (m != null && m != 0) {
			if (h) {
				ae(g, ct(" "))
			} else {
				h = 1
			}
			k = ce("span");
			k.className = "money" + (m < 0 ? "horde": "alliance") + " tip";
			k.onmouseover = Listview.funcBox.moneyHonorOver;
			k.onmousemove = Tooltip.cursorUpdate;
			k.onmouseout = Tooltip.hide;
			ae(k, ct(number_format(Math.abs(m))));
			ae(g, k)
		}
		if (j >= 1) {
			if (h) {
				ae(g, ct(" "))
			} else {
				h = 1
			}
			k = ce("span");
			k.className = "moneyarena tip";
			k.onmouseover = Listview.funcBox.moneyArenaOver;
			k.onmousemove = Tooltip.cursorUpdate;
			k.onmouseout = Tooltip.hide;
			ae(k, ct(number_format(j)));
			ae(g, k)
		}
		if (c != null) {
			for (var b = 0; b < c.length; ++b) {
				if (h) {
					ae(g, ct(" "))
				} else {
					h = 1
				}
				var o = c[b][0];
				var e = c[b][1];
				k = ce("a");
				k.href = "?item=" + o;
				k.className = "moneyitem";
				k.style.backgroundImage = "url(images/icons/tiny/" + g_items.getIcon(o).toLowerCase() + ".gif)";
				ae(k, ct(e));
				ae(g, k)
			}
		}
		if (l != null) {
			if (h) {
				ae(g, ct(" "))
			} else {
				h = 1
			}
			k = ce("span");
			k.className = "moneyachievement tip";
			k.onmouseover = Listview.funcBox.moneyAchievementOver;
			k.onmousemove = Tooltip.cursorUpdate;
			k.onmouseout = Tooltip.hide;
			ae(k, ct(number_format(l)));
			ae(g, k)
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
                            res.posttext = sprintf(LANG.lvitem_dd, "", (sm.dd < -1 ? LANG.lvitem_heroic : LANG.lvitem_normal));
                        }
                        else { // Raid
                            res.posttext = sprintf(LANG.lvitem_dd, (sm.dd & 1 ? LANG.lvitem_raid10 : LANG.lvitem_raid25), (sm.dd > 2 ? LANG.lvitem_heroic : LANG.lvitem_normal));
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
                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(faction);
                    ae(a, ct(faction.name));
                    if (faction.expansion) {
                        var sp = ce('span');
                        sp.className = g_GetExpansionClassName(faction.expansion);
                        ae(sp, a);
                        ae(td, sp);
                    }
                    else {
                        ae(td, a);
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
                        var sp = ce('span');
                        sp.className = (item.side == 1 ? 'alliance-icon' : 'horde-icon');
                        g_addTooltip(sp, g_sides[item.side]);

                        ae(td, sp);
                    }
                },
                getVisibleText: function(item) {
                    if (item.side) {
                        return g_sides[item.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'standing',
                name: LANG.reputation,
                value: 'standing',
                compute: function(faction, td) {
                    td.style.padding = 0;
                    ae(td, g_createReputationBar(faction.standing));
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
                            a = ce('a'),
                            href = '?factions=' + faction.category2;

                            if (faction.category) {
                            href += '.' + faction.category;
                        }
                        a.href = href;
                        ae(a, ct(Listview.funcBox.getFactionCategory(faction.category, faction.category2)));
                        ae(td, a);
                    }
                },
                getVisibleText: function(faction) {
                    return Listview.funcBox.getFactionCategory(faction.category, faction.category2);
                },
                sortFunc: function(a, b, col) {
                    var _ = Listview.funcBox.getFactionCategory;
                    return strcmp(_(a.category, a.category2), _(b.category, b.category2));
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
                    var i = ce('td');
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
                        ae(i, g_items.createIcon(item.id, (this.iconSize == null ? 1 : this.iconSize), num, qty));
                    }
                    ae(tr, i);
                    td.style.borderLeft = 'none';

                    var a = ce('a');
                    a.className = 'q' + (7 - parseInt(item.name.charAt(0)));
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(item);

                    if (item.rel) {
                        Icon.getLink(i.firstChild).rel = item.rel;
                        a.rel = item.rel;
                    }

                    ae(a, ct(item.name.substring(1)));

                    var wrapper = ce('div');
                    ae(wrapper, a);

                    if (item.reqclass) {
                        var d = ce('div');
                        d.className = 'small2';

                        var classes = Listview.funcBox.assocBinFlags(item.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                ae(d, ct(', '));
                            }
                            var a = ce('a');
                            a.href = '?class=' + classes[i];
                            a.className = 'c' + classes[i];
                            st(a, g_chr_classes[classes[i]]);
                            ae(d, a);
                        }

                        ae(wrapper, d);
                    }

                    if (typeof fi_nExtraCols == 'number' && fi_nExtraCols >= 5) {
                        if (item.source != null && item.source.length == 1) {
                            if (item.reqclass) {
                                ae(d, ct(LANG.dash));
                            }
                            else {
                                var d = ce('div');
                                d.className = 'small2';
                            }

                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;

                                var a = ce('a');
                                if (sm.q != null) {
                                    a.className = 'q' + sm.q;
                                }
                                else {
                                    a.className = 'q1';
                                }
                                a.href = '?' + g_types[sm.t] + '=' + sm.ti;

                                if (sm.n.length <= 30) {
                                    ae(a, ct(sm.n));
                                }
                                else {
                                    a.title = sm.n;
                                    ae(a, ct(trim(sm.n.substr(0, 27)) + '...'));
                                }

                                ae(d, a);
                            }
                            else {
                                ae(d, ct(Listview.funcBox.getUpperSource(item.source[0], sm)));
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (ls != null) {
                                ae(d, ct(LANG.hyphen));

                                if (ls.pretext) {
                                    ae(d, ct(ls.pretext));
                                }

                                if (ls.url) {
                                    var a = ce('a');
                                    a.className = 'q1';
                                    a.href = ls.url;
                                    ae(a, ct(ls.text));
                                    ae(d, a);
                                }
                                else {
                                    ae(d, ct(ls.text));
                                }

                                if (ls.posttext) {
                                    ae(d, ct(ls.posttext));
                                }
                            }

                            ae(wrapper, d);
                        }
                    }

                    if (item.heroic || item.reqrace) {
                        wrapper.style.position = 'relative';
                        var d = ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = d.style.bottom = '3px';

                        if (item.heroic) {
                            var s = ce('span');
                            s.className = 'q2';
                            ae(s, ct(LANG.lvitem_heroicitem));
                            ae(d, s);
                        }

                        if (item.reqrace) {
                            if ((item.reqrace & 1791) != 1101 && (item.reqrace & 1791) != 690) {
                                if (item.heroic) {
                                    ae(d, ce('br'));
                                    d.style.bottom = '-6px';
                                }

                                var races = Listview.funcBox.assocBinFlags(item.reqrace, g_chr_races);

                                for (var i = 0, len = races.length; i < len; ++i) {
                                    if (i > 0) {
                                        ae(d, ct(', '));
                                    }
                                    var a = ce('a');
                                    a.href = '?race=' + races[i];
                                    st(a, g_chr_races[races[i]]);
                                    ae(d, a);
                                }

                                d.className += ' q1';
                            }
                        }

                        ae(wrapper, d);
                    }

                    ae(td, wrapper);
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
                        return strcmp(a.minlevel, b.minlevel) || strcmp(a.maxlevel, b.maxlevel) || strcmp(a.level, b.level);
                    }
                    else {
                        return strcmp(a.maxlevel, b.maxlevel) || strcmp(a.minlevel, b.minlevel) || strcmp(a.level, b.level);
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
                        var sp = ce('span');
                        sp.className = (item.side == 1 ? 'alliance-icon': 'horde-icon');
                        g_addTooltip(sp, g_sides[item.side]);
                        ae(td, sp);
                    }
                },
                getVisibleText: function(item) {
                    if (item.side) {
                        return g_sides[item.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_sides[a.side], g_sides[b.side]);
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
                    nw(td);

                    return g_item_slots[item.slot];
                },
                getVisibleText: function(item) {
                    return g_item_slots[item.slot];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_item_slots[a.slot], g_item_slots[b.slot]);
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
                    return strcmp(g_item_glyphs[a.glyph], g_item_glyphs[b.glyph]);
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
                            nw(td);

                            var sm = (item.sourcemore ? item.sourcemore[0] : {});
                            var type = 0;

                            if (sm.t) {
                                type = sm.t;

                                var a = ce('a');
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

                                ae(a, ct(sm.n));
                                ae(td, a);
                            }
                            else {
                                ae(td, ct(Listview.funcBox.getUpperSource(item.source[0], sm)));
                            }

                            var ls = Listview.funcBox.getLowerSource(item.source[0], sm, type);

                            if (this.iconSize != 0 && ls != null) {
                                var div = ce('div');
                                div.className = 'small2';

                                if (ls.pretext) {
                                    ae(div, ct(ls.pretext));
                                }

                                if (ls.url) {
                                    var a = ce('a');
                                    a.className = 'q1';
                                    a.href = ls.url;
                                    ae(a, ct(ls.text));
                                    ae(div, a);
                                }
                                else {
                                    ae(div, ct(ls.text));
                                }

                                if (ls.posttext) {
                                    ae(div, ct(ls.posttext));
                                }

                                ae(td, div);
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

                    return strcmp(na, nb);
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                compute: function(item, td) {
                    td.className = 'small q1';
                    nw(td);
                    var a = ce('a');

                    var it = Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass);

                    a.href = it.url;
                    ae(a, ct(it.text));
                    ae(td, a);
                },
                getVisibleText: function(item) {
                    return Listview.funcBox.getItemType(item.classs, item.subclass, item.subsubclass).text;
                },
                sortFunc: function(a, b, col) {
                    var _ = Listview.funcBox.getItemType;
                    return strcmp(_(a.classs, a.subclass, a.subsubclass).text, _(b.classs, b.subclass, b.subsubclass).text);
                }
            }
        ],

        getItemLink: function(item) {
            return '?item=' + item.id;
        },

        onBeforeCreate: function() {
            var nComparable = false;

            for (var i = 0, len = this.data.length; i < len; ++i) {
                var item = this.data[i];

                if ((item.slot > 0 && item.slot != 18) || (in_array(ModelViewer.validSlots, item.slotbak) >= 0 && item.displayid > 0) || item.modelviewer) { // Equippable, and not a bag, or has a model
                    ++nComparable;
                }
                else {
                    item.__nochk = 1;
                }
            }

            if (nComparable > 0) {
                this.mode = 1;
                this._nComparable = nComparable;
            }
        },

        createCbControls: function(div, topBar) {
            if (!topBar && this._nComparable < 15) {
                return;
            }

            var
                iCompare  = ce('input'),
                iViewIn3d = ce('input'),
                iEquip    = ce('input'),
                iDeselect = ce('input'),
                pinnedChr = g_user.characters ? array_filter(g_user.characters, function(row) {
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

            ae(div, iCompare);
            ae(div, iViewIn3d);

            if (pinnedChr && pinnedChr.length) {
                iEquip.onclick = this.template.equipItems.bind(this, pinnedChr[0]);
                ae(div, iEquip);
            }

            ae(div, iDeselect);
        },

        compareItems: function() {
            var rows = this.getCheckedRows();
            if (!rows.length) {
                return;
            }

            var data = '';
            array_walk(rows, function(x) {
                if (x.slot == 0 || x.slot == 18) {
                    return;
                }

                data += x.id + ';';
            });
            su_addToSaved(rtrim(data, ';'), rows.length);
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
            array_walk(rows, function(x) {
                if (in_array(ModelViewer.validSlots, x.slotbak) >= 0 && x.displayid > 0) {
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
            array_walk(rows, function(x) {
                if (x.slot == 0 || x.slot == 18) {
                    return;
                }
                data += x.id + ':';
            });
            location.href = g_getProfileUrl(character) + '&items=' + rtrim(data, ':');
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
                    var a = ce('a');
                    a.className = 'q' + (7 - parseInt(itemSet.name.charAt(0)));
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(itemSet);
                    ae(a, ct(itemSet.name.substring(1)));

                    var div = ce('div');
                    div.style.position = 'relative';
                    ae(div, a);

                    if (itemSet.heroic) {
                        var d = ce('div');
                        d.className = 'small q2';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '3px';
                        ae(d, ct(LANG.lvitem_heroicitem));
                        ae(div, d);
                    }

                    ae(td, div);

                    if (itemSet.note) {
                        var d = ce('div');
                        d.className = 'small';
                        ae(d, ct(g_itemset_notes[itemSet.note]));
                        ae(td, d);
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
                        return strcmp(a.minlevel, b.minlevel) || strcmp(a.maxlevel, b.maxlevel);
                    }
                    else {
                        return strcmp(a.maxlevel, b.maxlevel) || strcmp(a.minlevel, b.minlevel);
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
                    return strcmp(lena, lenb);
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
                    return strcmp(g_itemset_types[a.type], g_itemset_types[b.type]);
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

                        var d = ce('div');
                        d.style.width = (26 * classes.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            var icon = Icon.create('class_' + g_file_classes[classes[i]], 0, null, '?class=' + classes[i]);
                            icon.style.cssFloat = icon.style.styleFloat = 'left';
                            g_addTooltip(icon, g_chr_classes[classes[i]], 'c' + classes[i]);

                            ae(d, icon);
                        }

                        ae(td, d);
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

                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(npc);
                    ae(a, ct(npc.name));
                    ae(td, a);

                    if (npc.tag != null) {
                        var d = ce('div');
                        d.className = 'small';
                        ae(d, ct('<' + npc.tag + '>'));
                        ae(td, d);
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
                    return strcmp(b.boss, a.boss) || strcmp(a.name, b.name);
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
                        var d = ce('div');
                        d.className = 'small';
                        ae(d, ct(g_npc_classifications[npc.classification]));
                        ae(td, d);
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
                        return strcmp(a.minlevel, b.minlevel) || strcmp(a.maxlevel, b.maxlevel) || strcmp(a.classification, b.classification);
                    }
                    else {
                        return strcmp(a.maxlevel, b.maxlevel) || strcmp(a.minlevel, b.minlevel) || strcmp(a.classification, b.classification);
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
                                ae(td, ct(' '));
                            }
                            var sp = ce('span');

                            sp.className = (npc.react[k] < 0 ? 'q10': (npc.react[k] > 0 ? 'q2': 'q'));

                            ae(sp, ct(sides[k]));
                            ae(td, sp);
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
                        var a = ce('a');
                        a.className = 'q1';
                        a.href = '?npcs&filter=cr=35;crs=0;crv=' + npc.skin;
                        ae(a, ct(npc.skin));
                        ae(td, a);
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

                    var a = ce('a');
                    a.href = '?pet=' + npc.family;
                    ae(a, ct(g_pet_families[npc.family]));
                    ae(td, a);
                },
                getVisibleText: function(npc) {
                    return g_pet_families[npc.family];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_pet_families[a.family], g_pet_families[b.family]);
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

                    var a = ce('a');
                    a.href = '?npcs=' + npc.type;
                    ae(a, ct(g_npc_types[npc.type]));
                    ae(td, a);
                },
                getVisibleText: function(npc) {
                    return g_npc_types[npc.type];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_npc_types[a.type], g_npc_types[b.type]);
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
                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(object);
                    ae(a, ct(object.name));
                    ae(td, a);
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
                    var a = ce('a');
                    a.href = '?objects=' + object.type;
                    ae(a, ct(g_object_types[object.type]));
                    ae(td, a);
                },
                getVisibleText: function(object) {
                    return g_object_types[object.type];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_object_types[a.type], g_object_types[b.type]);
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
                    var wrapper = ce('div');
                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(quest);
                    ae(a, ct(quest.name));
                    ae(wrapper, a);

                    if (quest.reqclass) {
                        var d = ce('div');
                        d.className += ' small2';

                        var classes = Listview.funcBox.assocBinFlags(quest.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                ae(d, ct(LANG.comma));
                            }

                            var a = ce('a');
                            a.href = '?class=' + classes[i];
                            a.className += 'c' + classes[i];
                            st(a, g_chr_classes[classes[i]]);
                            ae(d, a);
                        }

                        ae(wrapper, d);
                    }

                    if (quest.wflags & 1 || (quest.wflags & 32) || (quest.reqrace && quest.reqrace != -1)) {
                        wrapper.style.position = 'relative';
                        var d = ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '3px';
                        d.style.textAlign = 'right';
                        if (quest.wflags & 1) {
                            var s = ce('span');
                            s.style.color = 'red';
                            ae(s, ct(LANG.lvquest_disabled));
                            // ae(s, ct(LANG.lvquest_removed));
                            ae(d, s);
                        }

                        if (quest.wflags & 32) {
                            if (quest.wflags & 1) {
                                ae(d, ce('br'));
                                wrapper.style.height = '33px';
                            }

                            var
                                s = ce('span'),
                                o = LANG.lvquest_autoaccept;
                            if (quest.wflags & 64) {
                                s.style.color = 'red';
                                o += ' ' + LANG.lvquest_hostile;
                            }
                            ae(s, ct(o));
                            ae(d, s);
                        }

                        if (quest.reqrace && quest.reqrace != -1) {
                            var races = Listview.funcBox.assocBinFlags(quest.reqrace, g_chr_races);

                            if (races.length && (quest.wflags & 1 || (quest.wflags & 32))) {
                                ae(d, ce('br'));
                                wrapper.style.height = '33px';
                            }

                            for (var i = 0, len = races.length; i < len; ++i) {
                                if (i > 0) {
                                    ae(d, ct(LANG.comma));
                                }

                                var l = ce('a');
                                l.href = '?race=' + races[i];
                                l.className += 'q1';
                                st(l, g_chr_races[races[i]]);
                                ae(d, l);
                            }
                        }

                        ae(wrapper, d);
                    }

                    ae(td, wrapper);
                }
            },
            {
                id: 'level',
                name: LANG.level,
                value: 'level',
                compute: function(quest, td) {
                    if (quest.type || quest.daily || quest.weekly) {
                        var d = ce('div');
                        d.className = 'small';
                        nw(d);

                        if (quest.daily) {
                            if (quest.type) {
                                ae(d, ct(sprintf(LANG.lvquest_daily, g_quest_types[quest.type])));
                            }
                            else {
                                ae(d, ct(LANG.daily));
                            }
                        }
                        else if (quest.weekly) {
                            if (quest.type) {
                                ae(d, ct(sprintf(LANG.lvquest_weekly, g_quest_types[quest.type])));
                            }
                            else {
                                ae(d, ct(LANG.weekly));
                            }
                        }
                        else if (quest.type) {
                            ae(d, ct(g_quest_types[quest.type]));
                        }

                        ae(td, d);
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
                    return strcmp(a.level, b.level) || strcmp(a.type, b.type);
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
                        var sp = ce('span');
                        sp.className = (quest.side == 1 ? 'alliance-icon': 'horde-icon');
                        g_addTooltip(sp, g_sides[quest.side]);

                        ae(td, sp);
                    }
                    else if (!quest.side) {
                        ae(td, ct('??'));
                    }
                },
                getVisibleText: function(quest) {
                    if (quest.side) {
                        return g_sides[quest.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_sides[a.side], g_sides[b.side]);
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
                        var span = ce('a');
                        span.className = 'q1';
                        span.href = '?title=' + quest.titlereward;
                        span.innerHTML = title;
                        ae(td, span);
                        ae(td, ce('br'));
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
                    return strcmp(lenA, lenB) || strcmp(titleA, titleB);
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
                                ae(td, ct(' + '));
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
                    return strcmp(lenA, lenB) || strcmp(a.money, b.money);
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
                        var a = ce('a');
                        a.href = '?quests=' + quest.category2 + '.' + quest.category;
                        ae(a, ct(Listview.funcBox.getQuestCategory(quest.category)));
                        ae(td, a);
                    }
                },
                getVisibleText: function(quest) {
                    return Listview.funcBox.getQuestCategory(quest.category);
                },
                sortFunc: function(a, b, col) {
                    var _ = Listview.funcBox.getQuestCategory;
                    return strcmp(_(a.category), _(b.category));
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
        columns: [{
            id: "name",
            name: LANG.name,
            type: "text",
            align: "left",
            value: "name",
            span: 2,
            compute: function(c, h, f) {
                var d = ce("td");
                d.style.width = "1px";
                d.style.padding = "0";
                d.style.borderRight = "none";
                ae(d, Icon.create(c.icon, 0, null, this.getItemLink(c)));
                ae(f, d);
                h.style.borderLeft = "none";
                var g = ce("div");
                var b = ce("a");
                b.style.fontFamily = "Verdana, sans-serif";
                b.href = this.getItemLink(c);
                ae(b, ct(c.name));
                if (c.expansion) {
                    var e = ce("span");
                    e.className = g_GetExpansionClassName(c.expansion);
                    ae(e, b);
                    ae(g, e)
                } else {
                    ae(g, b)
                }
                ae(h, g)
            },
            getVisibleText: function(a) {
                var b = a.name + Listview.funcBox.getExpansionText(a);
                return b
            }
        },
        {
            id: "category",
            name: LANG.category,
            type: "text",
            width: "16%",
            compute: function(c, d) {
                if (c.category != 0) {
                    d.className = "small q1";
                    var b = ce("a");
                    b.href = "?skills=" + c.category;
                    ae(b, ct(g_skill_categories[c.category]));
                    ae(d, b)
                }
            },
            getVisibleText: function(a) {
                return g_skill_categories[skill.category]
            },
            sortFunc: function(d, c, e) {
                return strcmp(g_skill_categories[d.category], g_skill_categories[c.category])
            }
        }],
        getItemLink: function(a) {
            return "?skill=" + a.id
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
                        i = ce('td'),
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
                    ae(i, _);

                    ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = ce('div');

                    var a = ce('a');
                    var c = spell.name.charAt(0);
                    if (c != '@') {
                        a.className = 'q' + (7 - parseInt(c));
                    }
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(spell);

                    ae(a, ct(spell.name.substring(1)));

                    ae(wrapper, a);

                    if (spell.rank) {
                        var d = ce('div');
                        d.className = 'small2';

                        ae(d, ct(spell.rank));
                        ae(wrapper, d);
                    }

                    if (this.showRecipeClass && spell.reqclass) {
                        var d = ce('div');
                        d.className = 'small2';

                        var classes = Listview.funcBox.assocBinFlags(spell.reqclass, g_chr_classes);

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            if (i > 0) {
                                ae(d, ct(', '));
                            }
                            var a = ce('a');
                            a.href = '?class=' + classes[i];
                            a.className = 'c' + classes[i];
                            st(a, g_chr_classes[classes[i]]);
                            ae(d, a);
                        }

                        ae(wrapper, d);
                    }

                    if (spell.reqrace) {
                        wrapper.style.position = 'relative';
                        var d = ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = d.style.bottom = '3px';
                        if ((spell.reqrace & 1791) == 1101) {  // Alliance
                            ae(d, ct(g_sides[1]));
                        }
                        else if ((spell.reqrace & 1791) == 690) { // Horde
                            ae(d, ct(g_sides[2]));
                        }
                        else {
                            var races = Listview.funcBox.assocBinFlags(spell.reqrace, g_chr_races);
                            d.className += ' q1';

                            for (var i = 0, len = races.length; i < len; ++i) {
                                if (i > 0) {
                                    ae(d, ct(LANG.comma)) ;
                                }
                                var a = ce('a');
                                a.href = '?race=' + races[i];
                                st(a, g_chr_races[races[i]]);
                                ae(d, a);
                            }
                        }
                        ae(wrapper, d);
                    }
                    ae(td, wrapper);
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

                        var d = ce('div');
                        d.style.width = (26 * classes.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            var icon = Icon.create('class_' + g_file_classes[classes[i]], 0, null, '?class=' + classes[i]);
                            icon.style.cssFloat = icon.style.styleFloat = 'left';

                            g_addTooltip(icon, g_chr_classes[classes[i]], 'c' + classes[i]);

                            ae(d, icon);
                        }

                        ae(td, d);
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

                        var text = ce('a');
                        text.style.backgroundImage = 'url("' + g_staticUrl + '/images/icons/tiny/class_' + g_file_classes[classId] + '.gif")';
                        text.className = 'icontiny tinyspecial c' + classId;
                        text.href = '?class=' + classId;
                        ae(text, ct(g_chr_classes[classId]));

                        ae(td, text);
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
                    return strcmp(this.compute(a), this.compute(b));
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                width: '10%',
                hidden: true,
                compute: function(spell, td) {
                    if (g_spell_types[spell.cat]) {
                        return g_spell_types[spell.cat][spell.type];
                    }
                    return spell.type;
                },
                sortFunc: function(a, b, col) {
                    var
                        typeA = (g_spell_types[a.cat] ? g_spell_types[a.cat][a.type] : a.type),
                        typeB = (g_spell_types[b.cat] ? g_spell_types[b.cat][b.type] : a.type);
                    return strcmp(a.cat, b.cat) || strcmp(typeA, typeB);
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

                        var d = ce('div');
                        var items = spell.reagents;

                        d.style.width = (44 * items.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = items.length; i < len; ++i) {
                            var id   = items[i][0];
                            var num  = items[i][1];
                            var icon = g_items.createIcon(id, 1, num);
                            icon.style.cssFloat = icon.style.styleFloat = 'left';
                            ae(d, icon);
                        }

                        ae(td, d);
                    }
                },
                sortFunc: function(a, b) {
                    var lena = (a.reagents != null ? a.reagents.length: 0);
                    var lenb = (b.reagents != null ? b.reagents.length: 0);
                    if (lena > 0 && lena == lenb) {
                        return strcmp(a.reagents.toString(), b.reagents.toString());
                    }
                    else {
                        return strcmp(lena, lenb);
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
                        var div = ce('div');
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
                                ae(div, ct(LANG.comma));
                            }

                            if (spell.skill[i] == -1) {
                                ae(div, ct(LANG.ellipsis));
                            }
                            else {
                                if (in_array([7, -2, -3, -5, -6, -7, 11, 9], spell.cat) != -1) {
                                    var a = ce('a');
                                    a.className = 'q1';
                                    if (in_array([-5, -6], spell.cat) != -1) {
                                        a.href = '?spells=' + spell.cat;
                                    }
                                    else {
                                        a.href = '?spells=' + spell.cat + '.' + ((spell.reqclass && (spell.cat == 7 || spell.cat == -2)) ? (1 + Math.log(spell.reqclass) / Math.LN2) + '.' : '') + spell.skill[i];
                                    }

                                    var get = g_getGets();
                                    var spellCat = (get.spells ? get.spells.split('.') : [false, false]);
                                    if (spell.reqclass && (spell.cat == 7 || spell.cat == -2)) {
                                        if (i < 1 && ((1 + Math.log(spell.reqclass) / Math.LN2) != spellCat[1])) {
                                            var a2 = ce('a');
                                            a2.className = 'q0';
                                            a2.href = '?spells=' + spell.cat + '.' + (1 + Math.log(spell.reqclass) / Math.LN2);
                                            ae(a2, ct(g_chr_classes[(1 + Math.log(spell.reqclass) / Math.LN2)]));
                                            ae(div, a2);
                                            ae(div, ce('br'));
                                        }
                                    }
                                    ae(a, ct(spell.cat == -7 && spell.pettype != null ? g_pet_types[spell.pettype[i]] : g_spell_skills[spell.skill[i]]));
                                    ae(div, a);
                                }
                                else {
                                    ae(div, ct(g_spell_skills[spell.skill[i]]));
                                }
                            }
                        }

                        if (spell.learnedat > 0) {
                            ae(div, ct(' ('));
                            var spLearnedAt = ce('span');
                            if (spell.learnedat == 9999) {
                                spLearnedAt.className = 'q0';
                                ae(spLearnedAt, ct('??'));
                            }
                            else if (spell.learnedat > 0) {
                                ae(spLearnedAt, ct(spell.learnedat));
                                spLearnedAt.style.fontWeight = 'bold';
                            }
                            ae(div, spLearnedAt);
                            ae(div, ct(')'));
                        }

                        ae(td, div);

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
                                div = ce('div');
                                div.className = 'small';
                                div.style.fontWeight = 'bold';
                                for (var i = 0; i < colors.length; ++i) {
                                    if (colors[i] > 0) {
                                        if (nColors++ > 0) {
                                            ae(div, ct(' '));
                                        }
                                        var spColor = ce('span');
                                        spColor.className = 'r' + (i + 1);
                                        ae(spColor, ct(colors[i]));
                                        ae(div, spColor);
                                    }
                                }
                                ae(td, div);
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
                        var reqClass = strcmp(g_chr_classes[(1 + Math.log(a.reqclass) / Math.LN2)], g_chr_classes[(1 + Math.log(b.reqclass) / Math.LN2)]);
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
                    var foo = strcmp(skill[0], skill[1]);
                    if (foo != 0) {
                        return foo;
                    }

                    if (a.colors != null && b.colors != null) {
                        for (var i = 0; i < 4; ++i) {
                            foo = strcmp(a.colors[i], b.colors[i]);
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
                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(zone);
                    ae(a, ct(zone.name));
                    if (zone.expansion) {
                        var sp = ce('span');
                            sp.className = g_GetExpansionClassName(zone.expansion);
                        ae(sp, a);
                        ae(td, sp);
                    }
                    else {
                        ae(td, a);
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
                        return strcmp(a.minlevel, b.minlevel) || strcmp(a.maxlevel, b.maxlevel);
                    }
                    else {
                        return strcmp(a.maxlevel, b.maxlevel) || strcmp(a.minlevel, b.minlevel);
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
                        var sp = ce('span');

                        if (zone.nplayers == -2) {
                            zone.nplayers = '10/25';
                        }

                        var buff = '';
                        if (zone.nplayers) {
                            if (zone.instance == 4) {
                                buff += sprintf(LANG.lvzone_xvx, zone.nplayers, zone.nplayers);
                            }
                            else {
                                buff += sprintf(LANG.lvzone_xman, zone.nplayers);
                            }
                        }

                        ae(sp, ct(buff));
                        ae(td, sp);
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
                                buff += sprintf(LANG.lvzone_xvx, zone.nplayers, zone.nplayers);
                            }
                            else {
                                buff += sprintf(LANG.lvzone_xman, zone.nplayers);
                            }
                        }
                        return buff;
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(a.nplayers, b.nplayers);
                }
            },
            {
                id: 'territory',
                name: LANG.territory,
                type: 'text',
                width: '13%',
                compute: function(zone, td) {
                    var sp = ce('span');
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

                    ae(sp, ct(g_zone_territories[zone.territory]));
                    ae(td, sp);
                },
                getVisibleText: function(zone) {
                    return g_zone_territories[zone.territory];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_zone_territories[a.territory], g_zone_territories[b.territory]);
                }
            },
            {
                id: 'instancetype',
                name: LANG.instancetype,
                type: 'text',
                compute: function(zone, td) {
                    if (zone.instance > 0) {
                        var sp = ce('span');
                        if ((zone.instance >= 1 && zone.instance <= 5) || zone.instance == 7 || zone.instance == 8) {
                            sp.className = 'instance-icon' + zone.instance;
                        }

                        var buff = g_zone_instancetypes[zone.instance];

                        if (zone.heroicLevel) {
                            var skull = ce('span');
                            skull.className = 'heroic-icon';
                            g_addTooltip(skull, LANG.tooltip_heroicmodeavailable + LANG.qty.replace('$1', zone.heroicLevel));
                            ae(td, skull);
                        }

                        ae(sp, ct(buff));
                        ae(td, sp);
                    }
                },
                getVisibleText: function(zone) {
                    if (zone.instance > 0) {
                        var buff = g_zone_instancetypes[zone.instance];

                        if (zone.nplayers && ((zone.instance != 2 && zone.instance != 5) || zone.nplayers > 5)) {
                            if (zone.instance == 4) {
                                buff += ' ' + sprintf(LANG.lvzone_xvx, zone.nplayers, zone.nplayers);
                            }
                            else {
                                buff += ' ' + sprintf(LANG.lvzone_xman, zone.nplayers);
                            }
                        }
                        if (zone.instance == 5 || zone.instance == 8) {
                            buff += ' heroic';
                        }

                        return buff;
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_zone_instancetypes[a.instance], g_zone_instancetypes[b.instance]) || strcmp(a.instance, b.instance) || strcmp(a.nplayers, b.nplayers);
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(zone, td) {
                    td.className = 'small q1';
                    var a = ce('a');
                    a.href = '?zones=' + zone.category;
                    ae(a, ct(g_zone_categories[zone.category]));
                    ae(td, a);
                },
                getVisibleText: function(zone) {
                    return g_zone_categories[zone.category];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_zone_categories[a.category], g_zone_categories[b.category]);
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
                    var i = ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    ae(i, g_holidays.createIcon(holiday.id, 0));
                    ae(tr, i);
                    td.style.borderLeft = 'none';

                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(holiday);
                    ae(a, ct(holiday.name));
                    ae(td, a);
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
                                sp = ce('span');

                            if (start != end) {
                                st(sp, start + LANG.hyphen + end);
                            }
                            else {
                                st(sp, start);
                            }
                            ae(td, sp);

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
                        a = ce('a'),
                        href = '?events=' + holiday.category;

                    a.href = href;
                    ae(a, ct(g_holiday_categories[holiday.category]));
                    ae(td, a);
                },
                getVisibleText: function(holiday) {
                    return g_holiday_categories[holiday.category];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_holiday_categories[a.category], g_holiday_categories[b.category]);
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
                icon.onmousemove = Tooltip.cursorUpdate;
                icon.onmouseout  = Tooltip.hide;
                icon.style.cssFloat = icon.style.styleFloat = 'left';
                ae(div, icon);
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

            return strcmp(a.name, b.name);
        }
    },

	comment: {
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
			W = J.purged == 0 && J.deleted == 0 && g_user.id && J.user.toLowerCase() != g_user.name.toLowerCase() && in_array(J.raters, g_user.id, function(i) {
				return i[0]
			}) == -1 && !g_user.ratingban,
			p = J.rating >= 0 && (g_user.id == 0 || W || g_user.ratingban),
			G = g_users[J.user];
            J.ratable = W;
			var aa = ac;
			var N = ce("div");
			var z = ce("div");
			var t = ce("em");
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
			ac = ce("div");
			ac.className = "comment comment" + (ab % 2);
			ae(aa, ac);
			N.className = "comment-header";
			ae(ac, N);
			var n = ce("em");
			n.className = "comment-rating";
			if (ad) {
				var D = ce("a");
				D.href = "javascript:;";
				D.onclick = Listview.funcBox.coToggleVis.bind(D, J);
				ae(D, ct(LANG.lvcomment_show));
				ae(n, D);
				ae(n, ct(" " + String.fromCharCode(160) + " "))
			}
			var A = ce("b");
			var v = ce("a");
			v.href = "javascript:;";
			ae(v, ct(LANG.lvcomment_rating));
			var E = ce("span");
			E.id = "commentrating" + J.id;
			Listview.funcBox.coDisplayRating(J, E);
			v.onclick = Listview.funcBox.coToggleRating.bind(this, J, E);
			ae(v, E);
			ae(A, v);
			ae(n, A);
			ae(n, ct(" "));
			var S = ce("span");
			var q = ce("a"),
			af = ce("a");
			if (W) {
				q.href = af.href = "javascript:;";
				q.onclick = Listview.funcBox.coRate.bind(q, J, 1);
				af.onclick = Listview.funcBox.coRate.bind(af, J, -1);
				if (h) {
					var R = ce("a");
					R.href = "javascript:;";
					R.onclick = Listview.funcBox.coRate.bind(R, J, 0);
					R.onmouseover = Listview.funcBox.coCustomRatingOver;
					R.onmousemove = Tooltip.cursorUpdate;
					R.onmouseout = Tooltip.hide;
					ae(R, ct("[~]"))
					ae(S, R);
					ae(S, ct(" "))
				}
			} else {
				if (g_user.ratingban) {
					q.href = af.href = "javascript:;"
				} else {
					q.href = af.href = "?account=signin"
				}
			}
			ae(q, ct("[+]"))
			if (!g_user.ratingban) {
				q.onmouseover = Listview.funcBox.coPlusRatingOver;
				af.onmouseover = Listview.funcBox.coMinusRatingOver;
				q.onmousemove = af.onmousemove = Tooltip.cursorUpdate;
				q.onmouseout = af.onmouseout = Tooltip.hide
			} else {
				g_addTooltip(q, LANG.tooltip_banned_rating, "q");
				g_addTooltip(af, LANG.tooltip_banned_rating, "q")
			}
			ae(af, ct("[-]"))
			ae(S, af);
			ae(S, ct(" "));
			ae(S, q);
			ae(n, S);
			if (!p) {
				S.style.display = "none"
			}
			ae(N, n);
			t.className = "comment-links";
			var c = false;
			if (U) {
				var b = ce("span");
				var Q = ce("a");
				ae(Q, ct(LANG.lvcomment_edit));
				Q.onclick = Listview.funcBox.coEdit.bind(this, J, 0, false);
				ns(Q);
				Q.href = "javascript:;";
				ae(b, Q);
				c = true;
				ae(t, b)
			}
			if (L) {
				var u = ce("span");
				var F = ce("a");
				ae(F, ct(LANG.lvcomment_delete));
				F.onclick = Listview.funcBox.coDelete.bind(this, J);
				ns(F);
				F.href = "javascript:;";
				if (c) {
                    var e = ce("span");
                    e.style.color = "white";
                    ae(e, ct("|"));
					ae(t, e)
				}
				ae(u, F);
				ae(t, u)
				c = true;
			}
			if (d) {
				var P = ce("span");
				var k = ce("a");
				ae(k, ct(LANG.lvcomment_detach));
				k.onclick = Listview.funcBox.coDetach.bind(this, J);
				ns(k);
				k.href = "javascript:;";
				if (c) {
                    var e = ce("span");
                    e.style.color = "white";
                    ae(e, ct("|"));
					ae(t, e)
				}
				ae(P, k);
				ae(t, P)
				c = true;
			}
			if (af) {
				var K = ce("span");
				var m = ce("a");
				ae(m, ct(LANG.lvcomment_report));
				m.onclick = ContactTool.show.bind(ContactTool, {
					mode: 1,
					comment: J
				});
				m.className = "report-icon";
				m.href = "javascript:;";
				g_addTooltip(m, LANG.report_tooltip, "q2");
				if (c) {
                    var e = ce("span");
                    e.style.color = "white";
                    ae(e, ct("|"));
					ae(t, e)
				}
				ae(K, m);
				ae(t, K)
				c = true;
			}
			if (!g_user.commentban) {
				var l = ce("span");
				var o = ce("a");
				ae(o, ct(LANG.lvcomment_reply));
				if (g_user.id > 0) {
					o.onclick = Listview.funcBox.coReply.bind(this, J);
					o.href = "javascript:;"
				} else {
					o.href = "?account=signin"
				}
				if (c) {
                    var e = ce("span");
                    e.style.color = "white";
                    ae(e, ct("|"));
					ae(t, e)
				}
				ae(l, o);
				ae(t, l)
				c = true;
			}
			if (ad) {
				z.style.display = "none";
				t.style.display = "none"
			}
			ae(N, t);
			var C = ce("var");
			ae(C, ct(LANG.lvcomment_by));
			aUser = ce("a");
			aUser.href = "?user=" + J.user;
			ae(aUser, ct(J.user));
			ae(C, aUser);
			ae(C, ct(" "));
			var a = ce("a");
			a.className = "q0";
			a.id = "comments:id=" + J.id;
			a.href = "#" + a.id;
			g_formatDate(a, Y, I);
			ae(C, a);
			ae(C, ct(sprintf(LANG.lvcomment_patch, g_getPatchVersion(I))));
			if (G != null && G.avatar) {
				var j = Icon.createUser(G.avatar, G.avatarmore, 0, null, ((G.roles & U_GROUP_PREMIUM) ? (G.border ? 2 : 1) : 0));
				j.style.marginRight = "3px";
				j.style.cssFloat = j.style.styleFloat = "left";
				ae(N, j);
				C.style.lineHeight = "26px"
			}
			ae(N, C);
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
			ae(ac, z);
			var H = ce("div");
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
			ae(ac, H);
			J.divResponse = H;
			if ((J.roles & U_GROUP_COMMENTS_MODERATOR) == 0 || g_user.roles & U_GROUP_COMMENTS_MODERATOR) {
				var X = ce("div");
				J.divLastEdit = X;
				X.className = "comment-lastedit";
				ae(X, ct(LANG.lvcomment_lastedit));
				var w = ce("a");
				ae(w, ct(" "));
				ae(X, w);
				ae(X, ct(" "));
				var O = ce("span");
				ae(X, O);
				ae(X, ct(" "));
				Listview.funcBox.coUpdateLastEdit(J);
				if (ad) {
					X.style.display = "none"
				}
				ae(ac, X)
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
			G = v.purged == 0 && v.deleted == 0 && g_user.id && v.user.toLowerCase() != g_user.name.toLowerCase() && in_array(v.raters, g_user.id, function(P) {
				return P[0]
			}) == -1,
			i = v.rating >= 0 && (g_user.id == 0 || G);
			v.ratable = G;
			K.className = "comment";
			if (v.indent) {
				K.className += " comment-indent"
			}
			var z = ce("div");
			var m = ce("div");
			var k = ce("div");
			v.divHeader = z;
			v.divBody = m;
			v.divLinks = k;
			z.className = (L ? "comment-header-bt": "comment-header");
			var g = ce("div");
			g.className = "comment-rating";
			if (L) {
				var q = ce("a");
				q.href = "javascript:;";
				q.onclick = Listview.funcBox.coToggleVis.bind(q, v);
				ae(q, ct(LANG.lvcomment_show));
				ae(g, q);
				ae(g, ct(" " + String.fromCharCode(160) + " "))
			}
			var o = ce("b");
			ae(o, ct(LANG.lvcomment_rating));
			var r = ce("span");
			ae(r, ct((v.rating > 0 ? "+": "") + v.rating));
			ae(o, r);
			ae(g, o);
			ae(g, ct(" "));
			var E = ce("span");
			var j = ce("a"),
			N = ce("a");
			if (G) {
				j.href = N.href = "javascript:;";
				j.onclick = Listview.funcBox.coRate.bind(j, v, 1);
				N.onclick = Listview.funcBox.coRate.bind(N, v, -1);
				if (d) {
					var D = ce("a");
					D.href = "javascript:;";
					D.onclick = Listview.funcBox.coRate.bind(D, v, 0);
					D.onmouseover = Listview.funcBox.coCustomRatingOver;
					D.onmousemove = Tooltip.cursorUpdate;
					D.onmouseout = Tooltip.hide;
					ae(D, ct("[~]"));
					ae(E, D);
					ae(E, ct(" "))
				}
			} else {
				j.href = N.href = "?account=signin"
			}
			ae(j, ct("[+]"));
			j.onmouseover = Listview.funcBox.coPlusRatingOver;
			N.onmouseover = Listview.funcBox.coMinusRatingOver;
			j.onmousemove = N.onmousemove = Tooltip.cursorUpdate;
			j.onmouseout = N.onmouseout = Tooltip.hide;
			ae(N, ct("[-]"));
			ae(E, N);
			ae(E, ct(" "));
			ae(E, j);
			ae(g, E);
			if (!i) {
				E.style.display = "none"
			}
			ae(z, g);
			ae(z, ct(LANG.lvcomment_by));
			var J = ce("a");
			J.href = "?user=" + v.user;
			ae(J, ct(v.user));
			ae(z, J);
			ae(z, ct(" "));
			var a = ce("a");
			a.className = "q0";
			a.id = "comments:id=" + v.id;
			a.href = "#" + a.id;
			Listview.funcBox.coFormatDate(a, I, u);
			a.style.cursor = "pointer";
			ae(z, a);
			ae(z, ct(sprintf(LANG.lvcomment_patch, g_getPatchVersion(u))));
			ae(K, z);
			m.className = "comment-body" + Listview.funcBox.coGetColor(v);
			if (v.indent) {
				m.className += " comment-body-indent"
			}
			m.innerHTML = Markup.toHtml(v.body, {
				mode: Markup.MODE_COMMENT,
				roles: v.roles
			});
			ae(K, m);
			if ((v.roles & 26) == 0 || g_user.roles & 26) {
				var H = ce("div");
				v.divLastEdit = H;
				H.className = "comment-lastedit";
				ae(H, ct(LANG.lvcomment_lastedit));
				var p = ce("a");
				ae(p, ct(" "));
				ae(H, p);
				ae(H, ct(" "));
				var C = ce("span");
				ae(H, C);
				ae(H, ct(" "));
				Listview.funcBox.coUpdateLastEdit(v);
				if (L) {
					H.style.display = "none"
				}
				ae(K, H)
			}
			k.className = "comment-links";
			if (F) {
				var b = ce("span");
				var B = ce("a");
				ae(B, ct(LANG.lvcomment_edit));
				B.onclick = Listview.funcBox.coEdit.bind(this, v, 0);
				ns(B);
				B.href = "javascript:;";
				ae(b, B);
				ae(b, ct("|"));
				ae(k, b)
			}
			if (y) {
				var l = ce("span");
				var t = ce("a");
				ae(t, ct(LANG.lvcomment_delete));
				t.onclick = Listview.funcBox.coDelete.bind(this, v);
				ns(t);
				t.href = "javascript:;";
				ae(l, t);
				ae(l, ct("|"));
				ae(k, l)
			}
			if (c) {
				var A = ce("span");
				var e = ce("a");
				ae(e, ct(LANG.lvcomment_detach));
				e.onclick = Listview.funcBox.coDetach.bind(this, v);
				ns(e);
				e.href = "javascript:;";
				ae(A, e);
				ae(A, ct("|"));
				ae(k, A)
			}
			if (M) {
				var w = ce("span");
				var f = ce("a");
				ae(f, ct(LANG.lvcomment_report));
				if (g_user.id > 0) {
					f.onclick = Listview.funcBox.coReportClick.bind(f, v, 0);
					f.href = "javascript:;"
				} else {
					f.href = "?account=signin"
				}
				ae(w, f);
				ae(w, ct("|"));
				ae(k, w)
			}
			var h = ce("a");
			ae(h, ct(LANG.lvcomment_reply));
			if (g_user.id > 0) {
				h.onclick = Listview.funcBox.coReply.bind(this, v);
				h.href = "javascript:;"
			} else {
				h.href = "?account=signin"
			}
			ae(k, h);
			if (L) {
				m.style.display = "none";
				k.style.display = "none"
			}
			ae(K, k)
		}, */
		createNote: function(b) {
			var g = ce("small");
			if (!g_user.commentban) {
				var l = ce("a");
				if (g_user.id > 0) {
					l.href = "javascript:;";
					l.onclick = co_addYourComment
				} else {
					l.href = "?account=signin"
				}
				ae(l, ct(LANG.lvcomment_add));
				ae(g, l);
				var e = ce("span");
				e.style.padding = "0 5px";
				e.style.color = "white";
				ae(e, ct("|"));
				ae(g, e);
			}
			ae(g, ct(LANG.lvcomment_sort));
			var m = ce("a");
			m.href = "javascript:;";
			ae(m, ct(LANG.lvcomment_sortdate));
			m.onclick = Listview.funcBox.coSortDate.bind(this, m);
			ae(g, m);
			ae(g, ct(LANG.comma));
			var o = ce("a");
			o.href = "javascript:;";
			ae(o, ct(LANG.lvcomment_sortrating));
			o.onclick = Listview.funcBox.coSortHighestRatedFirst.bind(this, o);
			ae(g, o);
			var h = gc("temp_comment_sort") || 1;
			if (h == "2") {
				o.onclick()
			} else {
				m.onclick()
			}
			var e = ce("span");
			e.style.padding = "0 5px";
			e.style.color = "white";
			ae(e, ct("|"));
			ae(g, e);
			var q = ce("select");
			var f = ce("option");
			f.value = 0;
			f.selected = "selected";
			ae(q, f);
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
				var f = ce("option");
				f.value = j[c];
				ae(f, ct(g_getPatchVersion.V[j[c]]));
				ae(q, f)
			}
			q.onchange = Listview.funcBox.coFilterByPatchVersion.bind(this, q);
			ae(g, ct(LANG.lvcomment_patchfilter));
			ae(g, q);
			ae(b, g);
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
				var a = in_array(this.data, parseInt(RegExp.$1), function(b) {
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
		columns: [{
			id: "subject",
			name: LANG.subject,
			align: "left",
			value: "subject",
			compute: function(f, e) {
				var b = ce("a");
				b.style.fontFamily = "Verdana, sans-serif";
				b.href = this.template.getItemLink(f);
				ae(b, ct(f.subject));
				ae(e, b);
				if (LANG.types[f.type]) {
                    var c = ce("div");
                    c.className = "small";
                    ae(c, ct(LANG.types[f.type][0]));
                    ae(e, c)
                }
			}
		},
		{
			id: "preview",
			name: LANG.preview,
			align: "left",
			width: "50%",
			value: "preview",
			compute: function(j, i, k) {
				var g = ce("div");
				g.className = "crop";
				if (j.rating >= 10) {
					g.className += " comment-green"
				}
				ae(g, ct(Markup.removeTags(j.preview, {
					mode: Markup.MODE_ARTICLE
				})));
				ae(i, g);
				var e = j.rating != null;
				var f = j.user != null;
				if (e || f ||j.purged) {
					g = ce("div");
					g.className = "small3";
					if (f) {
						ae(g, ct(LANG.lvcomment_by));
						var b = ce("a");
						b.href = "?user=" + j.user;
						ae(b, ct(j.user));
						ae(g, b);
						if (e) {
							ae(g, ct(LANG.hyphen))
						}
					}
					if (e) {
						ae(g, ct(LANG.lvcomment_rating + (j.rating > 0 ? "+": "") + j.rating));
						var c = ce("span"),
						h = "";
						c.className = "q10";
						if (j.deleted) {
							h = LANG.lvcomment_deleted
						} else {
							if (j.purged) {
								h = LANG.lvcomment_purged
							}
						}
						ae(c, ct(h));
						ae(g, c)
                        k.__status = c;
					}
					ae(i, g)
				}
            }
		},
		{
			id: "author",
			name: LANG.author,
			value: "user",
			compute: function(d, c) {
				c.className = "q1";
				var b = ce("a");
				b.href = "?user=" + d.user;
				ae(b, ct(d.user));
				ae(c, b)
			}
		},
		{
			id: "posted",
			name: LANG.posted,
			width: "16%",
			value: "elapsed",
			compute: function(e, d) {
				var a = new Date(e.date),
				c = (g_serverTime - a) / 1000;
				var b = ce("span");
				Listview.funcBox.coFormatDate(b, c, a, 0, 1);
				ae(d, b)
			}
		}],
		getItemLink: function(a) {
			return "?" + g_types[a.type] + "=" + a.typeId + (a.id != null ? "#comments:id=" + a.id: "")
		}
	},
	screenshot: {
		sort: [],
		mode: 3,
		nItemsPerPage: 40,
		nItemsPerRow: 4,
		poundable: 2,
		columns: [],
		compute: function(k, e, l) {
			var v, p = new Date(k.date),
			f = (g_serverTime - p) / 1000;
			e.className = "screenshot-cell";
			e.vAlign = "bottom";
			var r = ce("a");
			r.href = "#screenshots:id=" + k.id;
			r.onclick = rf2;
			var w = ce("img"),
			u = Math.min(150 / k.width, 150 / k.height);
			// w.src = "http://static.wowhead.com/uploads/screenshots/thumb/" + k.id + ".jpg";
			w.src = g_staticUrl + "/uploads/screenshots/thumb/" + k.id + ".jpg";
			ae(r, w);
			ae(e, r);
			var q = ce("div");
			q.className = "screenshot-cell-user";
			var m = (k.user != null && k.user.length);
			if (m) {
				r = ce("a");
				r.href = "?user=" + k.user;
				ae(r, ct(k.user));
				ae(q, ct(LANG.lvscreenshot_from));
				ae(q, r);
				ae(q, ct(" "))
			}
			var j = ce("span");
			if (m) {
				Listview.funcBox.coFormatDate(j, f, p)
			} else {
				Listview.funcBox.coFormatDate(j, f, p, 0, 1)
			}
			ae(q, j);

			ae(q, ct(" " + LANG.dash + " "));
			var w = ce("a");
			w.href = "javascript:;";
			w.onclick = ContactTool.show.bind(ContactTool, {
				mode: 3,
				screenshot: k
			});
            w.className = "report-icon"
			g_addTooltip(w, LANG.report_tooltip, "q2");
			ae(w, ct(LANG.report));
			ae(q, w);

			ae(e, q);
			q = ce("div");
			q.style.position = "relative";
			q.style.height = "1em";
			if (g_getLocale(true) != 0 && k.caption) {
				k.caption = ""
			}
			var h = (k.caption != null && k.caption.length);
			var g = (k.subject != null && k.subject.length);
			if (h || g) {
				var t = ce("div");
				t.className = "screenshot-caption";
				if (g) {
					var c = ce("small");
					ae(c, ct(LANG.types[k.type][0] + LANG.colon));
					var b = ce("a");
					ae(b, ct(k.subject));
					b.href = "?" + g_types[k.type] + "=" + k.typeId;
					ae(c, b);
					ae(t, c);
					if (h && k.caption.length) {
						ae(c, ct(" (...)"))
					}
					ae(c, ce("br"))
				}
				if (h) {
					aE(e, "mouseover", Listview.funcBox.ssCellOver.bind(t));
					aE(e, "mouseout", Listview.funcBox.ssCellOut.bind(t));
					var o = ce("span");
					o.innerHTML = Markup.toHtml(k.caption, {
						mode: Markup.MODE_SIGNATURE
					});
					ae(t, o)
				}
				ae(q, t)
			}
			aE(e, "click", Listview.funcBox.ssCellClick.bind(this, l));
			ae(e, q)
		},
		createNote: function(d) {
			if (typeof g_pageInfo == "object" && g_pageInfo.type > 0) {
				var c = ce("small");
				var b = ce("a");
				if (g_user.id > 0) {
					b.href = "javascript:;";
					b.onclick = ss_submitAScreenshot
				} else {
					b.href = "?account=signin"
				}
				ae(b, ct(LANG.lvscreenshot_submit));
				ae(c, b);
				ae(d, c)
			}
		},
		onNoData: function(c) {
			if (typeof g_pageInfo == "object" && g_pageInfo.type > 0) {
				var a = "<b>" + LANG.lvnodata_ss1 + '</b><div class="pad2"></div>';
				if (g_user.id > 0) {
					var b = LANG.lvnodata_ss2;
					b = b.replace("<a>", '<a href="javascript:;" onclick="ss_submitAScreenshot()" onmousedown="return false">');
					a += b
				} else {
					var b = LANG.lvnodata_ss3;
					b = b.replace("<a>", '<a href="?account=signin">');
					b = b.replace("<a>", '<a href="?account=signup">');
					a += b
				}
				c.style.padding = "1.5em 0";
				c.innerHTML = a
			} else {
				return -1
			}
		},
		onBeforeCreate: function() {
			if (location.hash && location.hash.match(/:id=([0-9]+)/) != null) {
				var a = in_array(this.data, parseInt(RegExp.$1), function(b) {
					return b.id
				});
				this.rowOffset = this.getRowOffset(a);
				return a
			}
		},
		onAfterCreate: function(a) {
			if (a != null) {
				setTimeout((function() {
					ScreenshotViewer.show({
						screenshots: this.data,
						pos: a
					})
				}).bind(this), 1)
			}
		}
	},
	video: {
		sort: [],
		mode: 3,
		nItemsPerPage: 40,
		nItemsPerRow: 4,
		poundable: 2,
		columns: [],
		compute: function(e, f, j) {
			var q, k = new Date(e.date),
			r = (g_serverTime - k) / 1000;
			f.className = "screenshot-cell";
			f.vAlign = "bottom";
			var p = ce("a");
			p.href = "#videos:id=" + e.id;
			p.onclick = rf2;
			var h = ce("img");
			h.src = sprintf(vi_thumbnails[e.videoType], e.videoId);
			ae(p, h);
			ae(f, p);
			var l = ce("div");
			l.className = "screenshot-cell-user";
			var t = (e.user != null && e.user.length);
			if (t) {
				p = ce("a");
				p.href = "?user=" + e.user;
				ae(p, ct(e.user));
				ae(l, ct(LANG.lvvideo_from));
				ae(l, p);
				ae(l, ct(" "))
			}
			var u = ce("span");
			if (t) {
				Listview.funcBox.coFormatDate(u, r, k)
			} else {
				Listview.funcBox.coFormatDate(u, r, k, 0, 1)
			}
			ae(l, u);
			ae(f, l);
			l = ce("div");
			l.style.position = "relative";
			l.style.height = "1em";
			if (g_locale.id != 0 && e.caption) {
				e.caption = ""
			}
			var c = (e.caption != null && e.caption.length);
			var g = (e.subject != null && e.subject.length);
			if (c || g) {
				var b = ce("div");
				b.className = "screenshot-caption";
				if (g) {
					var o = ce("small");
					ae(o, ct(LANG.types[e.type][0] + LANG.colon));
					var n = ce("a");
					ae(n, ct(e.subject));
					n.href = g_getCommentDomain(e.domain) + "/" + g_types[e.type] + "=" + e.typeId;
					ae(o, n);
					ae(b, o);
					if (c && e.caption.length) {
						ae(o, ct(" (...)"))
					}
					ae(o, ce("br"))
				}
				if (c) {
					aE(f, "mouseover", Listview.funcBox.ssCellOver.bind(b));
					aE(f, "mouseout", Listview.funcBox.ssCellOut.bind(b));
					var m = ce("span");
					m.innerHTML = Markup.toHtml(e.caption, {
						mode: Markup.MODE_SIGNATURE
					});
					ae(b, m)
				}
				ae(l, b)
			}
			aE(f, "click", Listview.funcBox.viCellClick.bind(this, j));
			ae(f, l)
		},
		createNote: function(d) {
			if (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)) {
				if (typeof g_pageInfo == "object" && g_pageInfo.type > 0) {
					var c = ce("small");
					var b = ce("a");
					if (g_user.id > 0) {
						b.href = "javascript:;";
						b.onclick = vi_submitAVideo
					} else {
						b.href = "?account=signin"
					}
					ae(b, ct(LANG.lvvideo_suggest));
					ae(c, b);
					ae(d, c)
				}
			}
		},
		onNoData: function(c) {
			if (typeof g_pageInfo == "object" && g_pageInfo.type > 0) {
				var a = "<b>" + LANG.lvnodata_vi1 + '</b><div class="pad2"></div>';
				if (g_user.id > 0) {
					var b = LANG.lvnodata_vi2;
					b = b.replace("<a>", '<a href="javascript:;" onclick="vi_submitAVideo()" onmousedown="return false">');
					a += b
				} else {
					var b = LANG.lvnodata_vi3;
					b = b.replace("<a>", '<a href="?account=signin">');
					b = b.replace("<a>", '<a href="?account=signup">');
					a += b
				}
				c.style.padding = "1.5em 0";
				c.innerHTML = a
			} else {
				return - 1
			}
		},
		onBeforeCreate: function() {
			if (location.hash && location.hash.match(/:id=([0-9]+)/) != null) {
				var a = in_array(this.data, parseInt(RegExp.$1), function(b) {
					return b.id
				});
				this.rowOffset = this.getRowOffset(a);
				return a
			}
		},
		onAfterCreate: function(a) {
			if (a != null) {
				setTimeout((function() {
					VideoViewer.show({
						videos: this.data,
						pos: a,
						displayAd: true
					})
				}).bind(this), 1)
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
                    var i = ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    ae(i, Icon.create(pet.icon, 0));
                    ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = ce('div');

                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(pet);
                    ae(a, ct(pet.name));

                    if (pet.expansion) {
                        var sp = ce('span');
                        sp.className = g_GetExpansionClassName(pet.expansion);
                        ae(sp, a);
                        ae(wrapper, sp);
                    }
                    else {
                        ae(wrapper, a);
                    }

                    if (pet.exotic) {
                        wrapper.style.position = 'relative';
                        var d = ce('div');
                        d.className = 'small q1';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '0px';
                        var a = ce('a');
                        a.href = '?spell=53270';
                        ae(a, ct(LANG.lvpet_exotic));
                        ae(d, a);
                        ae(wrapper, d);
                    }
                    ae(td, wrapper);
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
                        return strcmp(a.minlevel, b.minlevel) || strcmp(a.maxlevel, b.maxlevel);
                    }
                    else {
                        return strcmp(a.maxlevel, b.maxlevel) || strcmp(a.minlevel, b.minlevel);
                    }
                }
            },
            {
                id: 'damage',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.damage,
                value: 'damage',
                hidden: 1,
                compute: function (pet, col) {
                    ae(col, this.template.getStatPct(pet.damage))
                }
            },
            {
                id: 'armor',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.armor,
                value: 'armor',
                hidden: 1,
                compute: function (pet, col) {
                    ae(col, this.template.getStatPct(pet.armor))
                }
            },
            {
                id: 'health',                                   // essentially unused, but if someone wants to backport classic
                name: LANG.health,
                value: 'health',
                hidden: 1,
                compute: function (pet, col) {
                    ae(col, this.template.getStatPct(pet.health))
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

                    return strcmp(a.spellCount, b.spellCount) || strcmp(a.spells, b.spells);
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
                    return strcmp(b.foodCount, a.foodCount) || Listview.funcBox.assocArrCmp(a.diet, b.diet, g_pet_foods);
                }
            },
            {
                id: 'type',
                name: LANG.type,
                type: 'text',
                compute: function(pet, td) {
                    if (pet.type != null) {
                        td.className = 'small q1';
                        var a = ce('a');
                        a.href = '?pets=' + pet.type;
                        ae(a, ct(g_pet_types[pet.type]));
                        ae(td, a);
                    }
                },
                getVisibleText: function(pet) {
                    if (pet.type != null) {
                        return g_pet_types[pet.type];
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_pet_types[a.type], g_pet_types[b.type]);
                }
            }
        ],

        getItemLink: function(pet) {
            return '?pet=' + pet.id;
        },

        getStatPct: function(modifier) {
            var _ = ce('span');
            if (!isNaN(modifier) && modifier > 0) {
                _.className = 'q2';
                ae(_, ct('+' + modifier + '%'));
            }
            else if (!isNaN(modifier) && modifier < 0) {
                _.className = 'q10';
                ae(_, ct(modifier + '%'));
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

                    var i = ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    ae(i, g_achievements.createIcon(achievement.id, 1));

                    Icon.getLink(i.firstChild).href = this.template.getItemLink(achievement);
                    Icon.getLink(i.firstChild).rel = rel;

                    ae(tr, i);
                    td.style.borderLeft = 'none';

                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(achievement);
                    a.rel = rel;
                    ae(a, ct(achievement.name));
                    ae(td, a);

                    if (achievement.description != null) {
                        var d = ce('div');
                        d.className = 'small';
                        ae(d, ct(achievement.description));
                        ae(td, d);
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
                        var a = ce('a');
                        a.className = 'q1';
                        a.href = '?zones=' + achievement.zone;
                        ae(a, ct(g_zones[achievement.zone]));
                        ae(td, a);
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
                        var sp = ce('span');
                        sp.className = (achievement.side == 1 ? 'alliance-icon': 'horde-icon');
                        g_addTooltip(sp, g_sides[achievement.side]);

                        ae(td, sp);
                    }
                },
                getVisibleText: function(achievement) {
                    if (achievement.side) {
                        return g_sides[achievement.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_sides[a.side], g_sides[b.side]);
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
                                var a = ce('a');
                                a.href = '?item=' + itemrewards[i];
                                a.className = 'q' + item.quality + ' icontiny tinyspecial';
                                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                                ae(a, ct(item['name_' + g_locale.name]));
                                var span = ce('span');
                                ae(span, a);
                                ae(td, span);
                                ae(td, ce('br'));
                            }
                        }
                        if (spellrewards.length > 0) {
                            for (var i = 0; i < spellrewards.length; i++) {
                                if (!g_spells[spellrewards[i]]) {
                                    return;
                                }

                                var item = g_spells[spellrewards[i]];
                                var a = ce('a');
                                a.href = '?spell=' + spellrewards[i];
                                a.className = 'q8 icontiny tinyspecial';
                                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                                ae(a, ct(item['name_' + g_locale.name]));
                                var span = ce('span');
                                ae(span, a);
                                ae(td, span);
                                ae(td, ce('br'));
                            }
                        }
                        if (titlerewards.length > 0) {
                            for (var i = 0; i < titlerewards.length; i++) {
                                if (!g_titles[titlerewards[i]]) {
                                    return;
                                }

                                var title = g_titles[titlerewards[i]]['name_' + g_locale.name];
                                title = title.replace('%s', '<span class="q0">&lt;' + LANG.name + '&gt;</span>');
                                var span = ce('a');
                                span.className = 'q1';
                                span.href = '?title=' + titlerewards[i];
                                span.innerHTML = title;
                                ae(td, span);
                                ae(td, ce('br'));
                            }
                        }
                    }
                    else if (achievement.reward) {
                        var span = ce('span');
                        span.className = 'q1';
                        ae(span, ct(achievement.reward));
                        ae(td, span);
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

                    return strcmp(text1, text2);
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
                        var a2 = ce('a');
                        a2.className = 'q0';
                        a2.href = '?achievements=' + statistic + achievement.parentcat;
                        ae(a2, ct(g_achievement_categories[achievement.parentcat]));
                        ae(td, a2);
                        ae(td, ce('br'));
                        path = a2.href + '.';
                    }
                    var a = ce('a');
                    a.className = 'q1';
                    a.href = path + achievement.category;
                    ae(a, ct(g_achievement_categories[achievement.category]));
                    ae(td, a);
                },
                getVisibleText: function(achievement) {
                    return g_achievement_categories[achievement.category];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_achievement_categories[a.category], g_achievement_categories[b.category]);
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
                        sp = ce('a'),
                        n = ce('span'),
                        t = ct(str_replace(title.name, '%s', ''));

                    td.style.fontFamily = 'Verdana, sans-serif';

                    // nw(td)

                    sp.href = this.template.getItemLink(title);

                    if (title.who) {
                        ae(n, ct(title.who));
                    }
                    else {
                        ae(n, ct('<' + LANG.name + '>'));
                        n.className = 'q0';
                    }

                    if (title.name.indexOf('%s') > 0) { // Prefix title
                        ae(sp, t);
                        ae(sp, n);
                    }
                    else if (title.name.indexOf('%s') == 0) { // Suffix title
                        ae(sp, n);
                        ae(sp, t);
                    }

                    if (title.expansion) {
                        var i = ce('span');
                        i.className = (title.expansion == 1 ? 'bc-icon': 'wotlk-icon');
                        ae(i, sp);
                        ae(td, i);
                    }
                    else {
                        ae(td, sp);
                    }
                },
                sortFunc: function(a, b, col) {
                    var
                        aName = trim(a.name.replace('%s', '').replace(/^[\s,]*(,|the |of the |of )/i, ''));
                        bName = trim(b.name.replace('%s', '').replace(/^[\s,]*(,|the |of the |of )/i, ''));

                    return strcmp(aName, bName);
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
                        var sp = ce('span');
                        sp.className = gender + '-icon';
                        g_addTooltip(sp, LANG[gender]);

                        ae(td, sp);
                    }
                },
                getVisibleText: function(title) {
                    if (title.gender && title.gender != 3) {
                        return LANG[g_file_genders[title.gender - 1]];
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(a.gender, b.gender);
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(title, td) {
                    if (title.side && title.side != 3) {
                        var sp = ce('span');
                        sp.className = (title.side == 1 ? 'alliance-icon': 'horde-icon');
                        g_addTooltip(sp, g_sides[title.side]);

                        ae(td, sp);
                    }
                },
                getVisibleText: function(title) {
                    if (title.side) {
                        return g_sides[title.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'source',
                name: LANG.source,
                type: 'text',
                compute: function(title, td) {
                    if (title.source) {
                        nw(td);
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
                                    ae(td, ce('br'));
                                }

                                if (typeof sm == 'string') {
                                    ae(td, ct(sm));
                                }
                                else  if (sm.t) {
                                    type = sm.t;
                                    var a = ce('a');
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

                                    ae(a, ct(sm.n));
                                    ae(td, a);
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
                    return strcmp(this.getVisibleText(a), this.getVisibleText(b));
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(title, td) {
                    nw(td);
                    td.className = 'small q1';
                    var a = ce('a');
                    a.href = '?titles=' + title.category;
                    ae(a, ct(g_title_categories[title.category]));
                    ae(td, a);
                },
                getVisibleText: function(title) {
                    return g_title_categories[title.category];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_title_categories[a.category], g_title_categories[b.category]);
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
                        var i = ce('td');
                        i.style.width = '1px';
                        i.style.padding = '0';
                        i.style.borderRight = 'none';

                        ae(i, Icon.create($WH.g_getProfileIcon(profile.race, profile.classs, profile.gender, profile.level, profile.icon ? profile.icon : profile.id, 'medium'), 1, null, this.getItemLink(profile)));
                        ae(tr, i);

                        td.style.borderLeft = 'none';
                    }
                    else {
                        td.colSpan = 2;
                    }

                    var wrapper = ce('div');
                    wrapper.style.position = 'relative';

                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(profile);
                    if (profile.pinned) {
                        a.className = 'star-icon-right';
                    }
                    ae(a, ct(profile.name));
                    ae(wrapper, a);

                    var d = ce('div');
                    d.className = 'small';
                    d.style.marginRight = '20px';

                    if (profile.guild && typeof(profile.guild) != 'number') {
                        var a = ce('a');
                        a.className = 'q1';
                        a.href = '?guild=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.guild);
                        ae(a, ct(profile.guild));
                        ae(d, ct('<'));
                        ae(d, a);
                        ae(d, ct('>'));
                    }
                    else if (profile.description) {
                        ae(d, ct(profile.description));
                    }

                    var
                        s = ce('span'),
                        foo = '';

                    s.className = 'q10';
                    if (profile.deleted) {
                        foo = LANG.lvcomment_deleted;
                    }
                    ae(s, ct(foo));
                    ae(d, s);
                    ae(wrapper, d);

                    var d = ce('div');
                    d.className = 'small';
                    d.style.fontStyle = 'italic';
                    d.style.position = 'absolute';
                    d.style.right = '3px';
                    d.style.bottom = '0px';
                    tr.__status = d;    // todo: wtf is that for..?

                    if (profile.published === 0) {
                        ae(d, ct(LANG.privateprofile));
                    }

                    ae(wrapper, d);
                    ae(td, wrapper);
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
                        d = ce('div'),
                        d2 = ce('div'),
                        icon;

                    icon = Icon.create('faction_' + g_file_factions[parseInt(profile.faction) + 1], 0);
                    icon.style.cssFloat = icon.style.syleFloat = 'left';
                    g_addTooltip(icon, g_sides[parseInt(profile.faction) + 1]);

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';
                    d.style.width = '26px';
                    d2.className = 'clear';

                    ae(d, icon);
                    ae(td, d);
                    ae(td, d2);
                },
                getVisibleText: function(profile) {
                    return g_sides[profile.faction + 1];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(this.getVisibleText(a), this.getVisibleText(b));
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
                            d = ce('div'),
                            d2 = ce('div'),
                            icon;

                        icon = Icon.create('race_' + g_file_races[profile.race] + '_' + g_file_genders[profile.gender], 0, null, '?race=' + profile.race);
                        icon.style.cssFloat = icon.style.syleFloat = 'left';
                        g_addTooltip(icon, g_chr_races[profile.race]);

                        d.style.margin = '0 auto';
                        d.style.textAlign = 'left';
                        d.style.width = '26px';
                        d2.className = 'clear';

                        ae(d, icon);
                        ae(td, d);
                        ae(td, d2);
                    }
                },
                getVisibleText: function(profile) {
                    return g_file_genders[profile.gender] + ' ' + g_chr_races[profile.race];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_chr_races[a.race], g_chr_races[b.race]);
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
                            d  = ce('div'),
                            d2 = ce('div'),
                            icon;

                        icon = Icon.create('class_' + g_file_classes[profile.classs], 0, null, '?class=' + profile.classs);
                        icon.style.cssFloat = icon.style.syleFloat = 'left';

                        g_addTooltip(icon, g_chr_classes[profile.classs], 'c' + profile.classs);

                        d.style.margin = '0 auto';
                        d.style.textAlign = 'left';
                        d.style.width = '26px';
                        d2.className = 'clear';

                        ae(d, icon);
                        ae(td, d);
                        ae(td, d2);
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
                    return strcmp(this.getVisibleText(a), this.getVisibleText(b));
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
                    var a = ce('a');

                    a.className = 'icontiny tinyspecial tip q1';
                    a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + specData.icon.toLowerCase() + '.gif)';
                    a.rel = 'np';
                    a.href = this.template.getItemLink(profile) + '#talents';
                    g_addTooltip(a, specData.name);

                    ae(a, ct(profile.talenttree1 + ' / ' + profile.talenttree2 + ' / ' + profile.talenttree3));

                    ae(td, a);
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
                    return strcmp(this.getVisibleText(a), this.getVisibleText(b));
                },
                hidden: 1
            },
            {
                id: 'gearscore',
                name: LANG.gearscore,
                tooltip: LANG.gearscore_real,
                value: 'gearscore',
                compute: function(profile, td) {
                    var level = (profile.level ? profile.level: (profile.members !== undefined ? 80 : 0));

                    if (isNaN(profile.gearscore) || !level) {
                        return;
                    }

                    td.className = 'q' + pr_getGearScoreQuality(level, profile.gearscore, (in_array([2, 6, 7, 11], profile.classs) != -1));

                    return (profile.gearscore ? number_format(profile.gearscore) : 0);
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
                        return sprintf(LANG.rankno, profile.guildrank);
                    }
                    else if (profile.guildrank == 0) {
                        var b = ce('b');
                        ae(b, ct(LANG.guildleader));
                        ae(td, b);
                    }
                },
                getVisibleText: function(profile) {
                    if (profile.guildrank > 0) {
                        return sprintf(LANG.rankno, profile.guildrank);
                    }
                    else if (profile.guildrank == 0) {
                        return LANG.guildleader;
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp((a.guildrank >= 0 ? a.guildrank : 11), (b.guildrank >= 0 ? b.guildrank : 11));
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
                        return strcmp(a.arenateam[a.roster].rating, b.arenateam[b.roster].rating);
                    }

                    return strcmp(a.rating, b.rating);
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
                            a = ce('a');
                            a.className = 'q1';
                            a.href = '?profiles=' + profile.region + '.' + profile.realm;
                            ae(a, ct(profile.realmname));
                            ae(td, a);
                            ae(td, ce('br'));
                        }

                        var s = ce('small');
                        a = ce('a');
                        a.className = 'q1';
                        a.href = '?profiles=' + profile.region;
                        ae(a, ct(profile.region.toUpperCase()));
                        ae(s, a);

                        if (profile.subregion) {
                            ae(s, ct(LANG.hyphen));

                            a = ce('a');
                            a.className = 'q1';
                            a.href = '?profiles=' + profile.region + '.' + profile.subregion;
                            ae(a, ct(profile.subregionname));
                            ae(s, a);
                        }

                        ae(td, s);
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

                    return trim(buff);
                },
                sortFunc: function(a, b, col) {
                    if (a.region != b.region) {
                        return strcmp(a.region, b.region);
                    }
                    if (a.subregion != b.subregion) {
                        return strcmp(a.subregion, b.subregion);
                    }
                    return strcmp(a.realm, b.realm);
                }
            },
            {
                id: 'guild',
                name: LANG.guild,
                value: 'guild',
                type: 'text',
                compute: function(profile, td) {
                    if (!profile.region || !profile.subregion || !profile.realm || !profile.guild || typeof(profile.guild) == 'number') {
                        return;
                    }

                    var a = ce('a');
                    a.className = 'q1';
                    a.href = '?guild=' + profile.region + '.' + profile.realm + '.' + g_urlize(profile.guild);
                    ae(a, ct(profile.guild));
                    ae(td, a);
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

            var a = ce('a');
            a.href = 'javascript:;';
            // a.className = 'pet-zoom';   // reference only
            a.onclick = this.template.modelShow.bind(this.template, model.npcId, model.displayId, false);

            var img = ce('img');
            img.src = g_staticUrl + '/modelviewer/thumbs/npc/' + model.displayId + '.png';
            ae(a, img);

            ae(td, a);

            var d = ce('div');
            d.className = 'screenshot-cell-user';

            a = ce('a');
            a.href = '?npcs=1&filter=' + (model.family ? 'fa=' + model.family + ';' : '') + 'minle=1;cr=35;crs=0;crv=' + model.skin;
            ae(a, ct(model.skin));
            ae(d, a);

            ae(d, ct(' (' + model.count + ')'));
            ae(td, d);

            d = ce('div');
            d.style.position = 'relative';
            d.style.height = '1em';

            var d2 = ce('div');
            d2.className = 'screenshot-caption';

            var s = ce('small');
            ae(s, ct(LANG.level + ': '));
            ae(s, ct((model.minLevel == 9999 ? '??' : model.minLevel) + (model.minLevel == model.maxLevel ? '' : LANG.hyphen + (model.maxLevel == 9999 ? '??' : model.maxLevel))));
            ae(s, ce('br'));
            ae(d2, s);
            ae(d, d2);
            ae(td, d);

            aE(td, 'click', this.template.modelShow.bind(this.template, model.npcId, model.displayId, true));
        },

        modelShow: function(npcId, displayId, sp, e) {
            if (sp) {
                e = $E(e);

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
                    var i = ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    ae(i, Icon.create(currency.icon, 0, null, this.template.getItemLink(currency)));
                    ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = ce('div');

                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(currency);
                    ae(a, ct(currency.name));

                    ae(wrapper, a);

                    ae(td, wrapper);
                }
            },
            {
                id: 'category',
                name: LANG.category,
                type: 'text',
                width: '15%',
                compute: function(currency, td) {
                    td.className = 'small';

                    var a = ce('a');
                    a.className = 'q1';
                    a.href = '?currencies=' + currency.category;
                    ae(a, ct(g_currency_categories[currency.category]));
                    ae(td, a);
                },
                getVisibleText: function(currency) {
                    return g_currency_categories[currency.category];
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_currency_categories[a.category], g_currency_categories[b.category]);
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
                    var i = ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    ae(i, Icon.create('class_' + g_file_classes[classs.id], 0, null, this.template.getItemLink(classs)));
                    ae(tr, i);
                    td.style.borderLeft = 'none';

                    // force minimum width to fix overlap display bug
                    td.style.width = '225px';

                    var wrapper = ce('div');

                    var a = ce('a');
                    a.className = 'c' + classs.id;
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(classs);
                    ae(a, ct(classs.name));

                    if (classs.expansion) {
                        var sp = ce('span');
                        sp.className = g_GetExpansionClassName(classs.expansion);
                        ae(sp, a);
                        ae(wrapper, sp);
                    }
                    else {
                        ae(wrapper, a);
                    }

                    if (classs.hero) {
                        wrapper.style.position = 'relative';
                        var d = ce('div');
                        d.className = 'small';
                        d.style.fontStyle = 'italic';
                        d.style.position = 'absolute';
                        d.style.right = '3px';
                        d.style.bottom = '0px';
                        ae(d, ct(LANG.lvclass_hero));
                        ae(wrapper, d);
                    }
                    ae(td, wrapper);
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
                                ae(td, ct(LANG.comma));
                            }
                            var a = ce('a');
                            a.href = '?race=' + races[i];
                            ae(a, ct(g_chr_races[races[i]]));
                            ae(td, a);
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
                        d = ce('div'),
                        icon;

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';
                    d.style.width = '52px';

                    icon = Icon.create('race_' + g_file_races[race.id] + '_' + g_file_genders[0], 0, null, this.template.getItemLink(race));
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    ae(d, icon);

                    icon = Icon.create('race_' + g_file_races[race.id] + '_' + g_file_genders[1], 0, null, this.template.getItemLink(race));
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    ae(d, icon);

                    var i = ce('td');
                    i.style.width = '1px';
                    i.style.padding = '0';
                    i.style.borderRight = 'none';

                    ae(i, d);
                    ae(tr, i);
                    td.style.borderLeft = 'none';

                    var wrapper = ce('div');

                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = this.template.getItemLink(race);
                    ae(a, ct(race.name));

                    if (race.expansion) {
                        var sp = ce('span');
                        sp.className = g_GetExpansionClassName(race.expansion);
                        ae(sp, a);
                        ae(wrapper, sp);
                    }
                    else {
                        ae(wrapper, a);
                    }
                    ae(td, wrapper);
                }
            },
            {
                id: 'side',
                name: LANG.side,
                type: 'text',
                compute: function(race, td) {
                    if (race.side && race.side != 3) {
                        var sp = ce('span');
                        sp.className = (race.side == 1 ? 'alliance-icon' : 'horde-icon');
                        g_addTooltip(sp, g_sides[race.side]);

                        ae(td, sp);
                    }
                },
                getVisibleText: function(race) {
                    if (race.side) {
                        return g_sides[race.side];
                    }
                },
                sortFunc: function(a, b, col) {
                    return strcmp(g_sides[a.side], g_sides[b.side]);
                }
            },
            {
                id: 'classes',
                name: LANG.classes,
                type: 'text',
                compute: function(race, td) {
                    if (race.classes) {
                        var classes = Listview.funcBox.assocBinFlags(race.classes, g_chr_classes);

                        var d = ce('div');
                        d.style.width = (26 * classes.length) + 'px';
                        d.style.margin = '0 auto';

                        for (var i = 0, len = classes.length; i < len; ++i) {
                            var icon = Icon.create('class_' + g_file_classes[classes[i]], 0, null, '?class=' + classes[i]);

                            g_addTooltip(icon, g_chr_classes[classes[i]], 'c' + classes[i]);

                            icon.style.cssFloat = icon.style.styleFloat = 'left';
                            ae(d, icon);
                        }

                        ae(td, d);
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

Menu.fixUrls(mn_items, "?items=");
Menu.fixUrls(mn_itemSets, "?itemsets&filter=cl=", "#0-2+1");
Menu.fixUrls(mn_npcs, "?npcs=");
Menu.fixUrls(mn_objects, "?objects=");
Menu.fixUrls(mn_quests, "?quests=");
Menu.fixUrls(mn_spells, "?spells=");
Menu.fixUrls(mn_skills, "?skill=");
Menu.fixUrls(mn_classes, "?class=");
Menu.fixUrls(mn_races, "?race=");
Menu.fixUrls(mn_zones, "?zones=");
Menu.fixUrls(mn_pets, "?pets=");
Menu.fixUrls(mn_factions, "?factions=");
Menu.fixUrls(mn_achievements, "?achievements=");
Menu.fixUrls(mn_titles, "?titles=", null, true);
Menu.fixUrls(mn_petCalc, "?petcalc=");
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
            icon  = ce('div'),
            image = ce('ins'),
            tile  = ce('del');

        if (size == null) {
            size = 1;
        }

        icon.className = 'icon' + Icon.sizes[size];

        ae(icon, image);

        if (!noBorder) {
            ae(icon, tile);
        }

        Icon.setTexture(icon, size, name);

        if (url) {
            var a = ce('a');
            a.href = url;
            if (url.indexOf('wowhead.com') == -1 && url.substr(0, 5) == 'http:') {
                a.target = "_blank";
            }
            ae(icon, a);
        }
        else if (name) {
            var _ = icon.firstChild.style;
            var avatarIcon = (_.backgroundImage.indexOf('/avatars/') != -1);

            if (!avatarIcon) {
                icon.onclick = Icon.onClick;

                var a = ce('a');
                a.href = "javascript:;";
                ae(icon, a);
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
        var _ = gE(icon, 'span');

        for (var i = 0, len = _.length; i < len; ++i) {
            if (_[i]) {
                de(_[i]);
            }
        }
        if (num != null && ((num > 1 && num < 2147483647) || num.length)) {
            _ = g_createGlow(num, 'q1');
            _.style.right = '0';
            _.style.bottom = '0';
            _.style.position = 'absolute';
            ae(icon, _);
        }

        if (qty != null && qty > 0) {
            _ = g_createGlow('(' + qty + ')', 'q');
            _.style.left = '0';
            _.style.top = '0';
            _.style.position = 'absolute';
            ae(icon, _);
        }
    },

    getLink: function(icon) {
        return gE(icon, 'a')[0];
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
                            var wrapper = ce('div');
                            td.style.width = '300px';
                            wrapper.style.position = 'relative';
                            wrapper.style.cssFloat = 'left';
                            wrapper.style.paddingRight = '6px';
                            field.style.width = '200px';

                            var divIcon = this.iconDiv = ce('div');
                            divIcon.style.position = 'absolute';
                            divIcon.style.top = '-12px';
                            divIcon.style.right = '-70px';

                            divIcon.update = function() {
                                setTimeout(function() {
                                    field.focus();
                                    field.select();
                                }, 10);
                                ee(divIcon);
                                ae(divIcon, Icon.create(field.value, 2));
                            };

                            ae(divIcon, Icon.create(value, 2));
                            ae(wrapper, divIcon);
                            ae(wrapper, field);
                            ae(td, wrapper);
                        }
                    },
                    {
                        id: 'location',
                        label: " ",
                        required: 1,
                        type: 'caption',
                        compute: function(field, value, form, th, tr) {
                            ee(th);
                            th.style.padding = '3px 3px 0 3px';
                            th.style.lineHeight = '17px';
                            th.style.whiteSpace = 'normal';
                            var wrapper = ce('div');
                            wrapper.style.position = 'relative';
                            wrapper.style.width = '250px';

                            var span = ce('span');

                            var text = LANG.dialog_seeallusingicon;
                            text = text.replace('$1', '<a href="?items&filter=cr=142;crs=0;crv=' + this.data.icon + '">' + LANG.types[3][3] + '</a>');
                            text = text.replace('$2', '<a href="?spells&filter=cr=15;crs=0;crv=' + this.data.icon + '">' + LANG.types[6][3] + '</a>');
                            text = text.replace('$3', '<a href="?achievements&filter=cr=10;crs=0;crv=' + this.data.icon + '">' + LANG.types[10][3] + '</a>');

                            span.innerHTML = text;
                            ae(wrapper, span);
                            ae(th, wrapper);
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
                    var nameDisabled = (isset('g_pageInfo') && g_pageInfo.type && in_array([3, 6, 10], g_pageInfo.type) == -1);
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
            else if (parts.length == 1 && isset('g_pageInfo')) {
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

var RedButton = {
    create: function(text, enabled, func) {
        var
            a    = ce('a'),
            em   = ce('em'),
            b    = ce('b'),
            i    = ce('i'),
            span = ce('span');

        a.href = 'javascript:;';
        a.className = 'button-red';

        ae(b, i);
        ae(em, b);
        ae(em, span);
        ae(a, em);

        RedButton.setText(a, text);
        RedButton.enable(a, enabled);
        RedButton.setFunc(a, func);

        return a;
    },

    setText: function(button, text) {
        st(button.firstChild.childNodes[0].firstChild, text); // em, b, i
        st(button.firstChild.childNodes[1], text); // em, span
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

var Tooltip = {
    create: function(htmlTooltip, secondary) {
        var
            d   = ce('div'),
            t   = ce('table'),
            tb  = ce('tbody'),
            tr1 = ce('tr'),
            tr2 = ce('tr'),
            td  = ce('td'),
            th1 = ce('th'),
            th2 = ce('th'),
            th3 = ce('th');

        d.className = 'tooltip';

        th1.style.backgroundPosition = 'top right';
        th2.style.backgroundPosition = 'bottom left';
        th3.style.backgroundPosition = 'bottom right';

        if (htmlTooltip) {
            td.innerHTML = htmlTooltip;
        }

        ae(tr1, td);
        ae(tr1, th1);
        ae(tb, tr1);
        ae(tr2, th2);
        ae(tr2, th3);
        ae(tb, tr2);
        ae(t, tb);

        if (!secondary) {
            Tooltip.icon = ce('p');
            Tooltip.icon.style.visibility = 'hidden';
            ae(Tooltip.icon, ce('div'));
            ae(d, Tooltip.icon);
        }

        ae(d, t);

        if (!secondary) {
            var img = ce('div');
            img.className = 'tooltip-powered';
            ae(d, img);
            Tooltip.logo = img;
        }

        return d;
    },

    getMultiPartHtml: function(upper, lower) {
        return '<table><tr><td>' + upper + '</td></tr></table><table><tr><td>' + lower + '</td></tr></table>';
    },

    fix: function(tooltip, noShrink, visible) {
        var
        table     = gE(tooltip, 'table')[0],
        td        = gE(table, 'td')[0],
        c         = td.childNodes;

        if (c.length >= 2 && c[0].nodeName == 'TABLE' && c[1].nodeName == 'TABLE') {
            c[0].style.whiteSpace = 'nowrap';

            var m = parseInt(tooltip.style.width);
            if (!m) {
                if (c[1].offsetWidth > 300) {
                    m = Math.max(300, c[0].offsetWidth) + 20;
                }
                else {
                    m = Math.max(c[0].offsetWidth, c[1].offsetWidth) + 20;
                }
            }

            m = Math.min(320, m);
            if (m > 20) {
                tooltip.style.width = m + 'px';
                c[0].style.width = c[1].style.width = '100%';

                if (!noShrink && tooltip.offsetHeight > document.body.clientHeight) {
                    table.className = 'shrink';
                }
            }
        }

        if (visible) {
            tooltip.style.visibility = 'visible';
        }
    },

    fixSafe: function(p1, p2, p3) {
        Tooltip.fix(p1, p2, p3);
    },

    append: function(el, htmlTooltip) {
        var el = $(el);
        var tooltip = Tooltip.create(htmlTooltip);
        ae(el, tooltip);

        Tooltip.fixSafe(tooltip, 1, 1);
    },

    prepare: function() {
        if (Tooltip.tooltip) {
            return;
        }

        var _ = Tooltip.create();
        _.style.position = 'absolute';
        _.style.left = _.style.top = '-2323px';

        ae(document.body, _);

        Tooltip.tooltip = _;
        Tooltip.tooltipTable = gE(_, 'table')[0];
        Tooltip.tooltipTd = gE(_, 'td')[0];

        var _ = Tooltip.create(null, true);
        _.style.position = 'absolute';
        _.style.left = _.style.top = '-2323px';

        ae(document.body, _);

        Tooltip.tooltip2      = _;
        Tooltip.tooltipTable2 = gE(_, 'table')[0];
        Tooltip.tooltipTd2    = gE(_, 'td')[0];
    },

    set: function(text, text2) {
        var _ = Tooltip.tooltip;

        _.style.width = '550px';
        _.style.left = '-2323px';
        _.style.top = '-2323px';

        if (text.nodeName) {
            ee(Tooltip.tooltipTd);
            ae(Tooltip.tooltipTd, text);
        }
        else {
            Tooltip.tooltipTd.innerHTML = text;
        }

        _.style.display = '';

        Tooltip.fix(_, 0, 0);

        if (text2) {
            Tooltip.showSecondary = true;
            var _ = Tooltip.tooltip2;

            _.style.width = '550px';
            _.style.left  = '-2323px';
            _.style.top   = '-2323px';

            if (text2.nodeName) {
                ee(Tooltip.tooltipTd2);
                ae(Tooltip.tooltipTd2, text2);
            }
            else {
                Tooltip.tooltipTd2.innerHTML = text2;
            }

            _.style.display = '';

            Tooltip.fix(_, 0, 0);
        }
        else {
            Tooltip.showSecondary = false;
        }
    },

    moveTests: [
        [null, null],  // Top right
        [null, false], // Bottom right
        [false, null],  // Top left
        [false, false]  // Bottom left
    ],

    move: function(x, y, width, height, paddX, paddY) {
        if (!Tooltip.tooltipTable) {
            return;
        }

        var
            tooltip = Tooltip.tooltip,
            tow = Tooltip.tooltipTable.offsetWidth,
            toh = Tooltip.tooltipTable.offsetHeight,
            tt2     = Tooltip.tooltip2,
            tt2w    = Tooltip.showSecondary ? Tooltip.tooltipTable2.offsetWidth : 0,
            tt2h    = Tooltip.showSecondary ? Tooltip.tooltipTable2.offsetHeight : 0,
            _;

        tooltip.style.width = tow + 'px';
        tt2.style.width     = tt2w + 'px';

        var
            rect,
            safe;

        for (var i = 0, len = Tooltip.moveTests.length; i < len; ++i) {
            _ = Tooltip.moveTests[i];

            rect = Tooltip.moveTest(x, y, width, height, paddX, paddY, _[0], _[1]);
            break;
        }

        tooltip.style.left       = rect.l + 'px';
        tooltip.style.top        = rect.t + 'px';
        tooltip.style.visibility = 'visible';

        if (Tooltip.showSecondary) {
            tt2.style.left       = rect.l + tow + 'px';
            tt2.style.top        = rect.t + 'px';
            tt2.style.visibility = 'visible';
        }
    },

    moveTest: function(left, top, width, height, paddX, paddY, rightAligned, topAligned) {
        var
            bakLeft = left,
            bakTop  = top,
            tooltip = Tooltip.tooltip,
            tow     = Tooltip.tooltipTable.offsetWidth,
            toh     = Tooltip.tooltipTable.offsetHeight,
            tt2     = Tooltip.tooltip2,
            tt2w    = Tooltip.showSecondary ? Tooltip.tooltipTable2.offsetWidth : 0,
            tt2h    = Tooltip.showSecondary ? Tooltip.tooltipTable2.offsetHeight : 0,
            winSize = g_getWindowSize(),
            scroll  = g_getScroll(),
            bcw     = winSize.w,
            bch     = winSize.h,
            bsl     = scroll.x,
            bst     = scroll.y,
            minX    = bsl,
            minY    = bst,
            maxX    = bsl + bcw,
            maxY    = bst + bch;

            if (rightAligned == null) {
            rightAligned = (left + width + tow + tt2w <= maxX);
        }

        if (topAligned == null) {
            topAligned = (top - Math.max(toh, tt2h) >= minY);
        }

        if (rightAligned) {
            left += width + paddX;
        }
        else {
            left = Math.max(left - (tow + tt2w), minX) - paddX;
        }

        if (topAligned) {
            top -= Math.max(toh, tt2h) + paddY;
        }
        else {
            top += height + paddY;
        }

        if (left < minX) {
            left = minX;
        }
        else if (left + tow + tt2w > maxX) {
            left = maxX - (tow + tt2w);
        }

        if (top < minY) {
            top = minY;
        }
        else if (top + Math.max(toh, tt2h) > maxY) {
            top = Math.max(bst, maxY - Math.max(toh, tt2h));
        }

        if (Tooltip.iconVisible) {
            if (bakLeft >= left - 48 && bakLeft <= left && bakTop >= top - 4 && bakTop <= top + 48) {
                top -= 48 - (bakTop - top);
            }
        }

        return g_createRect(left, top, tow, toh);
    },

    show: function(_this, text, paddX, paddY, spanClass, text2) {
        if (Tooltip.disabled) {
            return;
        }

        if (!paddX || paddX < 1) {
            paddX = 1;
        }

        if (!paddY || paddY < 1) {
            paddY = 1;
        }

        if (spanClass) {
            text = '<span class="' + spanClass + '">' + text + '</span>';
        }

        var coords = ac(_this);

        Tooltip.prepare();
        Tooltip.set(text, text2);
        Tooltip.move(coords.x, coords.y, _this.offsetWidth, _this.offsetHeight, paddX, paddY);
    },

    showAtCursor: function(e, text, paddX, paddY, spanClass, text2) {
        if (Tooltip.disabled) {
            return;
        }

        if (!paddX || paddX < 10) {
            paddX = 10;
        }
        if (!paddY || paddY < 10) {
            paddY = 10;
        }

        if (spanClass) {
            text = '<span class="' + spanClass + '">' + text + '</span>';
            if (text2) {
                text2 = '<span class="' + spanClass + '">' + text2 + '</span>';
            }
        }

        e = $E(e);
        var pos = g_getCursorPos(e);

        Tooltip.prepare();
        Tooltip.set(text, text2);
        Tooltip.move(pos.x, pos.y, 0, 0, paddX, paddY);
    },

    showAtXY: function(text, x, y, paddX, paddY, text2) {
        if (Tooltip.disabled) {
            return;
        }

        Tooltip.prepare();
        Tooltip.set(text, text2);
        Tooltip.move(x, y, 0, 0, paddX, paddY);
    },

    cursorUpdate: function(e, x, y) { // Used along with showAtCursor
        if (Tooltip.disabled || !Tooltip.tooltip) {
            return;
        }

        e = $E(e);

        if (!x || x < 10) {
            x = 10;
        }
        if (!y || y < 10) {
            y = 10;
        }

        var pos = g_getCursorPos(e);
        Tooltip.move(pos.x, pos.y, 0, 0, x, y);
    },

    hide: function() {
        if (Tooltip.tooltip) {
            Tooltip.tooltip.style.display  = 'none';
            Tooltip.tooltip.visibility     = 'hidden';
            Tooltip.tooltipTable.className = '';

            Tooltip.setIcon(null);
        }

        if (Tooltip.tooltip2) {
            Tooltip.tooltip2.style.display  = 'none';
            Tooltip.tooltip2.visibility     = 'hidden';
            Tooltip.tooltipTable2.className = '';
        }
    },

    setIcon: function(icon) {
        Tooltip.prepare();

        if (icon) {
            Tooltip.icon.style.backgroundImage = 'url(images/icons/medium/' + icon.toLowerCase() + '.jpg)';
            Tooltip.icon.style.visibility      = 'visible';
        }
        else {
            Tooltip.icon.style.backgroundImage = 'none';
            Tooltip.icon.style.visibility      = 'hidden';
        }

        Tooltip.iconVisible = icon ? 1 : 0;
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

        var c = ac(currentTextbox);
        container.style.left  = (c[0] - 2) + "px";
        container.style.top   = (c[1] + currentTextbox.offsetHeight + 1) + "px";
        container.style.width = currentTextbox.offsetWidth + "px";
    }

    function prepare() {
        if (prepared) {
            return;
        }

        prepared = 1;

        container = ce("div");
        container.className = "live-search";
        container.style.display = "none";

        ae(ge("layers"), container);
        aE(window, "resize", adjust.bind(0, 1));
        aE(document, "click", hide);
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
            de(container.firstChild);
        }

        if (!Browser.ie6) {
            ae(container, ce("em"));
            ae(container, ce("var"));
            ae(container, ce("strong"));
        }

        search = search.replace(/[^a-z0-9\-]/i, " ");
        search = trim(search.replace(/\s+/g, " "));

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
                a      = ce("a"),
                sp     = ce("i"),
                sp2    = ce("span"),
                div    = ce("div"),
                div2   = ce("div");
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

            ae(sp, ct(LANG.types[type][0]));
            ae(a, sp);

            var buffer = suggz[i];
            buffer = buffer.replace(regex, highlight);

            if (type == 11) {
                buffer = buffer.replace('%s', '<span class="q0">&lt;'+ LANG.name + '&gt;</span>');
            }

            sp2.innerHTML = buffer;
            ae(a, sp2);

            if (type == 6 && param2) {
                ae(a, ct(" (" + param2 + ")"));
            }

            ae(div2, a);
            ae(div, div2);
            ae(container, div);
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
        var url = "?search=" + urlencode(search);

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
        e = $E(e);
        var textbox = e._target;

        switch (e.keyCode) {
            case 48:
            case 96:
            case 107:
            case 109:
                if (Browser.firefox && e.ctrlKey) {
                    adjust(textbox);
                    break;
                }
                break;
        }

        var search = trim(textbox.value.replace(/\s+/g, " "));
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
        e = $E(e);
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
        e = $E(e);
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

        aE(textbox, "focus", onFocus);
        aE(textbox, "keyup", onKeyUp);
        aE(textbox, "keydown", onKeyDown);
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
        aE(overlay, 'click', hide);
        aE(document, 'keydown', onKeyDown);
        aE(window, 'resize', onResize);
    }

    function unhookEvents() {
        dE(overlay, 'click', hide);
        dE(document, 'keydown', onKeyDown);
        dE(window, 'resize', onResize);
    }

    function prepare() {
        if (prepared) {
            return;
        }

        prepared = 1;

        var dest = document.body;

        overlay = ce('div');
        overlay.className = 'lightbox-overlay';

        outer = ce('div');
        outer.className = 'lightbox-outer';

        inner = ce('div');
        inner.className = 'lightbox-inner';

        overlay.style.display = outer.style.display = 'none';

        ae(dest, overlay);
        ae(outer, inner);
        ae(dest, outer);
    }

    function onKeyDown(e) {
        e = $E(e);
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
        if (!Browser.ie) {
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
            d = ce('div');
            ae(inner, d);
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
        return (!isNaN(race) && race > 0 && in_array(races, race, function(x) {
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
			var foo = ge('view3D-button');
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
            location.replace(rtrim(url, ':'));
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
            var raceIndex = in_array(races, race, foo);
            var sexIndex = in_array(sexes, sex, foo);

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
            sc('modelviewer_mode', 7, newMode, '/', location.hostname);
            // sc('modelviewer_mode', 7, newMode, '/', '.wowhead.com');
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

            var raceIndex = in_array(races, race, foo);
            var sexIndex  = in_array(sexes, sex, foo);

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
            var screen = ce('div');
            _w = ce('div');
            _o = ce('div');
            _z = ce('div');
            var flashDiv = ce('div');
            flashDiv.id = 'modelviewer-generic';
            ae(_w, flashDiv);
            screen.className = 'modelviewer-screen';
            _w.style.display = _o.style.display = _z.style.display = 'none';
            ae(screen, _w);
            ae(screen, _o);
            ae(screen, _z);
            var screenbg = ce('div');
            screenbg.style.backgroundColor = '#181818';
            screenbg.style.margin = '0';
            ae(screenbg, screen);
            ae(dest, screenbg);
            G = ce('a'),
            E = ce('a');
            G.className = 'modelviewer-help';
            G.href = '?help=modelviewer';
            G.target = '_blank';
            ae(G, ce('span'));
            E.className = 'modelviewer-close';
            E.href = 'javascript:;';
            E.onclick = Lightbox.hide;
            ae(E, ce('span'));
            ae(dest, E);
            ae(dest, G);
            var N = ce('div'),
            F = ce('span'),
            G = ce('a'),
            E = ce('a');
            N.className = 'modelviewer-quality';
            G.href = E.href = 'javascript:;';
            ae(G, ct('Flash'));
            ae(E, ct('Java'));
            G.onclick = j.bind(G, 0);
            E.onclick = j.bind(E, 1);
            ae(F, G);
            ae(F, ct(' ' + String.fromCharCode(160)));
            ae(F, E);
            if (f()) {
                var D = ce('a');
                D.href = 'javascript:;';
                ae(D, ct('Plugin'));
                D.onclick = j.bind(D, 2);
                ae(F, ct(' ' + String.fromCharCode(160)));
                ae(F, D)
            }
            ae(N, ce('div'));
            ae(N, F);
            ae(dest, N);

            modelDiv = ce('div');
            modelDiv.className = 'modelviewer-model';

            var foo = function(a, b) {
                return strcmp(a.name, b.name);
            };

            races.sort(foo);
            sexes.sort(foo);

            raceSel1 = ce('select');
            raceSel2 = ce('select');
            sexSel   = ce('select');
            raceSel1.onchange = raceSel2.onchange = sexSel.onchange = onSelChange;

            ae(raceSel1, ce('option'));
            for (var i = 0, len = races.length; i < len; ++i) {
                var o = ce('option');
                o.value = races[i].id;
                ae(o, ct(races[i].name));
                ae(raceSel1, o);
            }

            for (var i = 0, len = races.length; i < len; ++i) {
                var o = ce('option');
                o.value = races[i].id;
                ae(o, ct(races[i].name));
                ae(raceSel2, o);
            }

            for (var i = 0, len = sexes.length; i < len; ++i) {
                var o = ce('option');
                o.value = sexes[i].id;
                ae(o, ct(sexes[i].name));
                ae(sexSel, o);
            }
            sexSel.style.display = 'none';
            ae(modelDiv, ce('div'));
            ae(modelDiv, raceSel1);
            ae(modelDiv, raceSel2);
            ae(modelDiv, sexSel);
            ae(dest, modelDiv);
            d = ce('div');
            d.className = 'clear';
            ae(dest, d);
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
            if (in_array([4, 5, 6, 7, 8, 9, 10, 16, 19, 20], opt.slot) != -1) {
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
            if (gc('modelviewer_mode') == '2' && f()) {
                D.onclick()
            } else {
                if (gc('modelviewer_mode') == '1') {
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
                var foo = ge('view3D-button');
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

        var availHeight = Math.max(50, Math.min(618, g_getWindowSize().h - 72 - captionExtraHeight));

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

        if (Browser.ie6) {
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
        if (resizing && (scale == desiredScale) && g_getWindowSize().h > container.offsetHeight) {
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
        if (Browser.ie6) {
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
                ee(s);
                Listview.funcBox.coFormatDate(s, elapsed, postedOn);

                divFrom.firstChild.style.display = '';
            }
            else {
                divFrom.firstChild.style.display = 'none';
            }

            var s = divFrom.childNodes[1];
            ee(s);
            if (screenshot.user) {
                if (hasFrom1) {
                    ae(s, ct(' ' + LANG.dash + ' '));
                }
                var a = ce('a');
                a.href = 'javascript:;';
                a.onclick = ContactTool.show.bind(ContactTool, {
                    mode: 3,
                    screenshot: screenshot
                });
                a.className = 'report-icon'
                g_addTooltip(a, LANG.report_tooltip, 'q2');
                ae(a, ct(LANG.report));
                ae(s, a);
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

            if (g_getLocale(true) != 0 && screenshot.caption) {
                screenshot.caption = '';
            }

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
        e = $E(e);
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
            dE(document, 'keyup', onKeyUp);
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

            screen = ce('div');

            screen.className = 'screenshotviewer-screen';

            aPrev = ce('a');
            aNext = ce('a');
            aPrev.className = 'screenshotviewer-prev';
            aNext.className = 'screenshotviewer-next';
            aPrev.href = 'javascript:;';
            aNext.href = 'javascript:;';

            var foo = ce('span');
            ae(foo, ce('b'));
            // var b = ce('b');
            // ae(b, ct(LANG.previous));
            // ae(foo, b);
            ae(aPrev, foo);
            var foo = ce('span');
            ae(foo, ce('b'));
            // var b = ce('b');
            // ae(b, ct(LANG.next));
            // ae(foo, b);
            ae(aNext, foo);

            aPrev.onclick = prevScreenshot;
            aNext.onclick = nextScreenshot;

            aCover = ce('a');
            aCover.className = 'screenshotviewer-cover';
            aCover.href = 'javascript:;';
            aCover.onclick = Lightbox.hide;
            var foo = ce('span');
            ae(foo, ce('b'));
            // var b = ce('b');
            // ae(b, ct(LANG.close));
            // ae(foo, b);
            ae(aCover, foo);
            if (Browser.ie6) {
                ns(aPrev);
                ns(aNext);
                aPrev.onmouseover = aNext.onmouseover = aCover.onmouseover = function() {
                    this.firstChild.style.display = 'block';
                };
                aPrev.onmouseout = aNext.onmouseout = aCover.onmouseout = function() {
                    this.firstChild.style.display = '';
                };

            }
            ae(screen, aPrev);
            ae(screen, aNext);
            ae(screen, aCover);

            imgDiv = ce('div');
            ae(screen, imgDiv);

            ae(dest, screen);

            var aClose = ce('a');
            aClose.className = 'screenshotviewer-close';
            // aClose.className = 'dialog-x';
            aClose.href = 'javascript:;';
            aClose.onclick = Lightbox.hide;
            ae(aClose, ce('span'));
            // ae(aClose, ct(LANG.close));
            ae(dest, aClose);

            aOriginal = ce('a');
            aOriginal.className = 'screenshotviewer-original';
            // aOriginal.className = 'dialog-arrow';
            aOriginal.href = 'javascript:;';
            aOriginal.target = '_blank';
            ae(aOriginal, ce('span'));
            // ae(aOriginal, ct(LANG.original));
            ae(dest, aOriginal);

            divFrom = ce('div');
            divFrom.className = 'screenshotviewer-from';
            var sp = ce('span');
            ae(sp, ct(LANG.lvscreenshot_from));
            ae(sp, ce('a'));
            ae(sp, ct(' '));
            ae(sp, ce('span'));
            ae(divFrom, sp);
            ae(divFrom, ce('span'));
            ae(divFrom, ce('span'));
            ae(dest, divFrom);

            divCaption = ce('div');
            divCaption.className = 'screenshotviewer-caption';
            ae(dest, divCaption);
            var d = ce('div');
            d.className = 'clear';
            ae(dest, d);
        }

        oldHash = location.hash;

        if (screenshots.length > 1) {
            aE(document, 'keyup', onKeyUp);
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
                    de(container.firstChild);
                }
            }

            var lightboxTimer = setTimeout(function() {
                screenshot.width = 126;
                screenshot.height = 22;
                computeDimensions(0);
                screenshot.width = null;
                screenshot.height = null;

                var div = ce('div');
                div.style.margin = '0 auto';
                div.style.width = '126px';
                var img = ce('img');
                img.src = g_staticUrl + '/template/images/progress-anim.gif';
                img.width = 126;
                img.height = 22;
                ae(div, img);
                ae(container, div);

                Lightbox.reveal();
                container.style.visiblity = 'visible';
            }, 150);

            loadingImage = new Image();
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

        ee(container);
        container.className = 'screenshotviewer';
        for (var i = 0; i < lightboxComponents.length; ++i)
            ae(container, lightboxComponents[i]);
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
    _form = ce('form'),
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
            cO(_data, opt.data);
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
        ee(dest);
        ee(_form);

        var container = ce('div');
        container.className = 'text';
        ae(dest, container);
        ae(container, _form);
        if (_template.title) {
            var h = ce('h1');
            ae(h, ct(_template.title));
            ae(_form, h);
        }

        var
            t         = ce('table'),
            tb        = ce('tbody'),
            mergeCell = false;

        ae(t, tb);
        ae(_form, t);

        for (var i = 0, len = _template.fields.length; i < len; ++i) {
            var
                field = _template.fields[i],
                element;

            if (!mergeCell) {
                tr = ce('tr');
                th = ce('th');
                td = ce('td');
            }

            field.__tr = tr;

            if (_data[field.id] == null) {
                _data[field.id] = (field.value ? field.value: '');
            }

            var options;
            if (field.options) {
                options = [];

                if (field.optorder) {
                    cO(options, field.optorder);
                }
                else {
                    for (var j in field.options) {
                        options.push(j);
                    }
                }

                if (field.sort) {
                    options.sort(function(a, b) {
                        return field.sort * strcmp(field.options[a], field.options[b]);
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
                        ae(th, ct(field.label));
                    }

                    ae(tr, th);
                    ae(tb, tr);

                    continue;
                    break;
                case 'textarea':
                    var f = element = ce('textarea');

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

                        ae(th, ct(field.label));
                        ae(tr, th);
                        ae(tb, tr);

                        tr = ce('tr');
                    }
                    ae(td, f);

                    break;
                case 'select':

                    var f = element = ce('select');

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
                        var o = ce('option');

                        o.value = options[j];

                        ae(o, ct(field.options[options[j]]));
                        ae(f, o)
                    }

                    ae(td, f);

                    break;
                case 'dynamic':
                    td.colSpan = 2;
                    td.style.textAlign = 'left';
                    td.style.padding = 0;

                    if (field.compute)
                        (field.compute.bind(_self, null, _data[field.id], _form, td, tr))();

                    ae(tr, td);
                    ae(tb, tr);

                    element = td;

                    break;
                case 'checkbox':
                case 'radio':
                    var k = 0;
                    element = [];
                    for (var j = 0, len2 = options.length; j < len2; ++j) {
                        var
                            s = ce('span'),
                            f,
                            l,
                            uniqueId = 'sdfler46' + field.id + '-' + options[j];

                        if (j > 0 && !field.noInputBr) {
                            ae(td, ce('br'));
                        }
                        if (Browser.ie6 && field.type == 'radio') {
                            l = ce("<label for='' + uniqueId + '' onselectstart='return false' />");
                            f = ce("<input type='' + field.type + '' name='' + field.id + '' />");
                        }
                        else {
                            l = ce('label');
                            l.setAttribute('for', uniqueId);
                            l.onmousedown = rf;

                            f = ce('input');
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

                        ae(l, f);
                        ae(l, ct(field.options[options[j]]));
                        ae(td, l);
                        element.push(f);
                    }
                    break;
                default: // Textbox
                    var f = element = ce('input');
                    f.name = field.id;

                    if (field.size) {
                        f.size = field.size;
                    }

                    if (field.disabled) {
                        f.disabled = true ;
                    }

                    if (field.submitOnEnter) {
                        f.onkeypress = function(e) {
                            e = $E(e);
                            if (e.keyCode == 13) {
                                _processForm();
                            }
                        }
                    }
                    f.setAttribute('type', field.type);
                    ae(td, f);
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
                    ae(th, ct(field.label));
                    ae(tr, th);
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
                var s = ce('small');
                if (field.type != 'textarea')
                    s.style.paddingLeft = '2px';
                s.className = 'q0'; // commented in 5.0?
                ae(s, ct(field.caption));
                ae(td, s);
            }

            ae(tr, td);
            ae(tb, tr);

            mergeCell = field.mergeCell;
            _elements[field.id] = element;
        }

        for (var i = _template.buttons.length; i > 0; --i) {
            var
                button = _template.buttons[i - 1],
                a      = ce('a');

            a.href = 'javascript:;';
            a.onclick = _processForm.bind(a, button[0]);
            a.className = 'dialog-' + button[0];
            ae(a, ct(button[1]));
            ae(dest, a);
        }

        var _ = ce('div');
        _.className = 'clear';
        ae(dest, _);

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
                        f.options[j].selected = (f.options[j].value == _data[field.id] || in_array(_data[field.id], f.options[j].value) != -1);
                    }
                    break;
                case 'checkbox':
                case 'radio':
                    for (var j = 0, len2 = f.length; j < len2; j++) {
                        f[j].checked = (f[j].value == _data[field.id] || in_array(_data[field.id], f[j].value) != -1);
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
                newValue = trim(newValue);
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
            'mode=' + urlencode(data.mode),
            'reason=' + urlencode(data.reason),
            'desc=' + urlencode(data.description),
            'ua=' + urlencode(navigator.userAgent),
            'appname=' + urlencode(navigator.appName),
            'page=' + urlencode(data.currenturl)
        ];

        if (data.mode == 0) { // contact us
            if (data.relatedurl) {
                params.push('relatedurl=' + urlencode(data.relatedurl));
            }
            if (data.email) {
                params.push('email=' + urlencode(data.email));
            }
        }
        else if (data.mode == 1) { // comment
            params.push('id=' + urlencode(data.comment.id));
        }
        else if (data.mode == 2) { // forum post
            params.push('id=' + urlencode(data.post.id));
        }
        else if (data.mode == 3) { // screenshot
            params.push('id=' + urlencode(data.screenshot.id));
        }
        else if (data.mode == 4) { // character
            params.push('id=' + urlencode(data.profile.source));
        }
        else if (data.mode == 5) { // video
            params.push('id=' + urlencode(data.video.id));
        }
        else if (data.mode == 6) { // guide
            params.push('id=' + urlencode(data.guide.id));
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
                        alert(sprintf(LANG.ct_dialog_thanks_user, g_user.name));
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
        cO(data, opt);
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
                        ee(field);

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

                            var o = ce('option');
                            o.value = id;
                            if (value && value == id) {
                                o.selected = true;
                            }
                            ae(o, ct(g_contact_reasons[id]));
                            ae(field, o);
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
                                ge('contact-emailwarn').style.display = g_isEmailValid(ge(form.email).value) ? 'none' : '';
                                Lightbox.reveal();
                            };

                            ge(field).onkeyup = func;
                            ge(field).onblur = func;
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
                        var td = ge(td);
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
                    setTimeout(bindfunc(form.reason.focus, form.reason), 10);
                }
                else if (form['relatedurl'] && !form.relatedurl.value) {
                    setTimeout(bindfunc(form.relatedurl.focus, form.relatedurl), 10);
                }
                else if (form['email'] && !form.email.value) {
                    setTimeout(bindfunc(form.email.focus, form.email), 10);
                }
                else if (form['description'] && !form.description.value) {
                    setTimeout(bindfunc(form.description.focus, form.description), 10);
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
                                form.firstChild.innerHTML = sprintf(LANG.ct_dialog_reportcomment, '<a href="?user=' + this.data.comment.user + '">' + this.data.comment.user + '</a>');
                                break;
                            case 2: // forum post
                                var rep = '<a href="?user=' + this.data.post.user + '">' + this.data.post.user + '</a>';
                                if (this.data.post.op) {
                                    form.firstChild.innerHTML = sprintf(LANG.ct_dialog_reporttopic, rep);
                                }
                                else {
                                    form.firstChild.innerHTML = sprintf(LANG.ct_dialog_reportpost, rep);
                                }
                                break;
                            case 3: // screenshot
                                form.firstChild.innerHTML = sprintf(LANG.ct_dialog_reportscreen, '<a href="?user=' + this.data.screenshot.user + '">' + this.data.screenshot.user + '</a>');
                                break;
                            case 4: // character
                                ee(form.firstChild);
                                ae(form.firstChild, ct(LANG.ct_dialog_reportchar));
                                break;
                            case 5: // video
                                form.firstChild.innerHTML = sprintf(LANG.ct_dialog_reportvideo, '<a href="?user=' + this.data.video.user + '">' + this.data.video.user + '</a>');
                                break;
                            case 6: // guide
                                form.firstChild.innerHTML = 'Report guide';
                                break;
                        }
                        form.firstChild.setAttribute('style', '');

                        ee(field);

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

                        ae(field, ce('option', { selected: (!value), value: -1 }));

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

                            var o = ce('option');
                            o.value = id;
                            if (value && value == id) {
                                o.selected = true;
                            }

                            ae(o, ct(g_contact_reasons[id]));
                            ae(field, o);
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
                var reason = gE(form, 'select')[0];
                var description = gE(form, 'textarea')[0];

                if (this.data.focus && form[this.data.focus]) {
                    setTimeout(g_setCaretPosition.bind(null, form[this.data.focus], form[this.data.focus].value.length), 100);
                }
                else if (!reason.value) {
                    setTimeout(bindfunc(reason.focus, reason), 10);
                }
                else if (!description.value) {
                    setTimeout(bindfunc(description.focus, description), 10);
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

	var $line = ce('span');
    $line.className = 'line';
    $line.style.top    = top.toFixed(2) + 'px';
	$line.style.left   = left.toFixed(2) + 'px';
	$line.style.width  = width.toFixed(2) + 'px';
	$line.style.height = height.toFixed(2) + 'px';

    var v = ce('var');
	v.style.width = length.toFixed(2) + 'px';
    v.style.OTransform = 'rotate(' + radian + 'rad)';
    v.style.MozTransform = 'rotate(' + radian + 'rad)';
    v.style.webkitTransform = 'rotate(' + radian + 'rad)';
    v.style.filter = "progid:DXImageTransform.Microsoft.Matrix(sizingMethod='auto expand', M11=" + cosTheta + ', M12=' + (-1 * sinTheta) + ', M21=' + sinTheta + ', M22=' + cosTheta + ')';
    ae($line, v);

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
            ge('open-links-button').click();
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

    cO(this, opt);

    if (this.parent) {
        this.parentDiv = ge(this.parent);
    }
    else {
        return;
    }

    if (g_user.id == 0 && gc('announcement-' + this.id) == 'closed') {
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

        var div = this.innerDiv = ce('div');
        div.className = 'announcement-inner text';
        this.setStyle(this.style);

        var a = null;
        var id = parseInt(this.id);

        if (g_user && (g_user.roles & (U_GROUP_ADMIN|U_GROUP_BUREAU)) > 0 && Math.abs(id) > 0) {
            if (id < 0) {
                a = ce('a');
                a.style.cssFloat = a.style.styleFloat = 'right';
                a.href = '?admin=announcements&id=' + Math.abs(id) + '&status=2';
                a.onclick = function() {
                    return confirm('Are you sure you want to delete ' + this.name + '?');
                };
                ae(a, ct('Delete'));
                var small = ce('small');
                ae(small, a);
                ae(div, small);

                a = ce('a');
                a.style.cssFloat = a.style.styleFloat = 'right';
                a.style.marginRight = '10px';
                a.href = '?admin=announcements&id=' + Math.abs(id) + '&status=' + (this.status == 1 ? 0 : 1);
                a.onclick = function() {
                    return confirm('Are you sure you want to delete ' + this.name + '?');
                };
                ae(a, ct((this.status == 1 ? 'Disable' : 'Enable')));
                var small = ce('small');
                ae(small, a);
                ae(div, small);
            }

            a = ce('a');
            a.style.cssFloat = a.style.styleFloat = 'right';
            a.style.marginRight = '22px';
            a.href = '?admin=announcements&id=' + Math.abs(id) + '&edit';
            ae(a, ct('Edit announcement'));
            var small = ce('small');
            ae(small, a);
            ae(div, small);
        }

        var markupDiv = ce('div');
        markupDiv.id = this.parent + '-markup';
        ae(div, markupDiv);

        if (id >= 0) {
            a = ce('a');

            a.id = 'closeannouncement';
            a.href = 'javascript:;';
            a.className = 'announcement-close';
            if (this.nocookie) {
                a.onclick = this.hide.bind(this);
            }
            else {
                a.onclick = this.markRead.bind(this);
            }
            ae(div, a);
            g_addTooltip(a, LANG.close);
        }

        ae(div, ce('div', { style: { clear: 'both' } }));

        ae(this.parentDiv, div);

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
        sc('announcement-' + this.id, 20, 'closed', "/", location.hostname);
        this.hide();
    },

    setStyle: function(style) {
        this.style = style;
        this.innerDiv.setAttribute('style', style);
    },

    setText: function(text) {
        this.text = text;
        // Markup.printHtml(this.text, this.parent + '-markup');
        ge(this.parent + '-markup').innerHTML = this.text;
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
    g_gatheredcurrencies = {},
    g_users              = {};

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
cO(g_items, {
    add: function(id, json) {
        if (g_items[id] != null) {
            cO(g_items[id], json);
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
cO(g_spells, {
    add: function(id, json) {
        if (g_spells[id] != null) {
            cO(g_spells[id], json);
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
cO(g_achievements, {
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
cO(g_classes, {
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
cO(g_races, {
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
cO(g_skills, {
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
cO(g_gatheredcurrencies, {
    getIcon: function(id, side) {
        if (g_gatheredcurrencies[id] != null && g_gatheredcurrencies[id].icon) {
            if (is_array(g_gatheredcurrencies[id].icon) && !isNaN(side)) {
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
cO(g_holidays, {
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
