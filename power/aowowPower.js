if (typeof $WH == "undefined") {
	$WH = {
		wowheadRemote: true
	}

	/* custom */
	for (i in document.scripts) {
		if (!document.scripts[i].src)
			continue;

		var match = document.scripts[i].src.match(/(.*)\/power\/aowowPower.js/i);
		if (match) {
			var g_staticUrl = match[1];
			break;
		}
	}
}

if (typeof $WowheadPower == "undefined") {
	var $WowheadPower = new function () {
		var isRemote = $WH.wowheadRemote;
		var
            opt = {
                applyto: 3
            },
            head = document.getElementsByTagName("head")[0],
            currentType,
            currentId,
            currentLocale,
            currentDomain,
            currentParams,
            currentA,
            cursorX,
            cursorY,
            mode = 0,
            t, y, ag, A, Z, ab = 1,
            eventAttached = false,
            npcs = {},
            objects = {},
            items = {},
            quests = {},
            spells = {},
            achievements = {},
            profiles = {},
            showLogo = 1,
            STATUS_NONE = 0,
            STATUS_QUERYING = 1,
            STATUS_ERROR = 2,
            STATUS_NOTFOUND = 3,
            STATUS_OK = 4,
            TYPE_NPC = 1,
            TYPE_OBJECT = 2,
            TYPE_ITEM = 3,
            TYPE_QUEST = 5,
            TYPE_SPELL = 6,
            TYPE_ACHIEVEMENT = 10,
            TYPE_PROFILE = 100,
            CURSOR_HSPACE = 15,
            CURSOR_VSPACE = 15,
            _LANG = {
                loading: "Loading...",
                noresponse: "No response from server :(",
                achievementcomplete: "Achievement earned by $1 on $2/$3/$4"
            },
            LOOKUPS = {
                1 : [npcs, "npc", "NPC"],
                2 : [objects, "object", "Object"],
                3 : [items, "item", "Item"],
                5 : [quests, "quest", "Quest"],
                6 : [spells, "spell", "Spell"],
                10 : [achievements, "achievement", "Achievement"],
                100 : [profiles, "profile", "Profile"]
            },
            LOCALES = {
                0 : "enus",
                2 : "frfr",
                3 : "dede",
                6 : "eses",
                8 : "ruru",
                25 : "ptr"
            },
            REDIRECTS = {
                wotlk: "www",
                ptr: "www"
            };

		if (isRemote) {
			var Locale = {
				id: 0,
				name: "enus"
			};
		}

		function init() {
			if (isRemote) {
				var script = document.createElement("script");
				// script.src = "http://static.wowhead.com/js/basic.js?4";
				script.src = g_staticUrl + "/template/js/basic.js";
				head.appendChild(script);
			}
            else {
				attachEvent();
			}
		}

		function attachEvent() {
			if (eventAttached) {
				return;
			}

			eventAttached = true;
			$WH.aE(document, "mouseover", onMouseOver);
		}

		this.init = function () {
			if (isRemote) {
				$WH.ae(head, $WH.ce("link", {
					type: "text/css",
					// href: "http://static.wowhead.com/css/basic.css?4",
					href: g_staticUrl + "/template/css/basic.css",
					rel:  "stylesheet"
				}));

				if ($WH.Browser.ie67) {
					$WH.ae(head, $WH.ce("link", {
						type: "text/css",
						// href: "http://static.wowhead.com/css/basic_ie67.css?4",
						href: g_staticUrl + "/template/css/global_ie67.css",
						rel:  "stylesheet"
					}));

					if ($WH.Browser.ie6) {
						$WH.ae(head, $WH.ce("link", {
							type: "text/css",
							// href: "http://static.wowhead.com/css/basic_ie6.css?4",
							href: g_staticUrl + "/template/css/global_ie6.css",
							rel:  "stylesheet"
						}));
					}
				}
			}
			attachEvent();
		};

		function updateCursorPos(e) {
			var pos = $WH.g_getCursorPos(e);
			cursorX = pos.x;
			cursorY = pos.y;
		}

		function scanElement(t, e) {
			if (t.nodeName != "A" && t.nodeName != "AREA") {
				return -2323;
			}

			if (!t.href.length) {
				return;
			}

			if (!t.href.length && !t.rel) {
				return;
			}

			if (t.rel && t.rel.indexOf("np") != -1) {
				return;
			}

			var
                i0,
                i1,
                i2,
                url,
                params = {};

			currentParams = params;

			var p = function (url, k, v) {
                if (k == "buff" || k == "sock") {
                    params[k] = true;
                }
                else  if (k == "rand" || k == "ench" || k == "lvl" || k == "c") {
                    params[k] = parseInt(v);
                }
                else if (k == "gems" || k == "pcs") {
                    params[k] = v.split(":");
                }
                else if (k == "who" || k == "domain") {
                    params[k] = v;
                }
                else if (k == "when") {
                    params[k] = new Date(parseInt(v));
                }
			};

			if (opt.applyto & 1) {
				i1 = 2;
				i2 = 3;
				if (t.href.indexOf("http://") == 0) {
					i0 = 1;
					// url = t.href.match(/^http:\/\/(.+?)?\.?wowhead\.com\/\?(item|quest|spell|achievement|npc|object)=([0-9]+)/);
					url = t.href.match(/^http:\/\/(.*)\/\??(item|quest|spell|achievement|npc|object)=([0-9]+)/);
					if (url == null) {
						// url = t.href.match(/^http:\/\/(.+?)?\.?wowhead\.com\/\?(profile)=([^&#]+)/)
						url = t.href.match(/^http:\/\/(.*)\/\??(profile)=([^&#]+)/);
					}

					showLogo = 0;
				}
                else {
					url = t.href.match(/()\?(item|quest|spell|achievement|npc|object)=([0-9]+)/);
					if (url == null) {
						url = t.href.match(/()\?(profile)=([^&#]+)/);
					}

					showLogo = 1;
				}
			}

			if (url == null && t.rel && (opt.applyto & 2)) {
				i0 = 0;
				i1 = 1;
				i2 = 2;
				url = t.rel.match(/(item|quest|spell|achievement|npc|object).?([0-9]+)/);
				if (url == null) {
					url = t.rel.match(/(profile).?([^&#]+)/);
				}
				showLogo = 1;
			}

			t.href.replace(/([a-zA-Z]+)=?([a-zA-Z0-9:-]*)/g, p);
			if (t.rel) {
				t.rel.replace(/([a-zA-Z]+)=?([a-zA-Z0-9:-]*)/g, p);
			}

			if (params.gems && params.gems.length > 0) {
				var i;
				for (i = Math.min(3, params.gems.length - 1); i >= 0; --i) {
					if (parseInt(params.gems[i])) {
						break;
					}
				}
                ++i;

				if (i == 0) {
					delete params.gems;
				}
                else {
					if (i < params.gems.length) {
						params.gems = params.gems.slice(0, i);
					}
				}
			}

			if (url) {
				var
                    locale,
                    domain = "www";

				currentA = t;
				if (params.domain) {
					domain = params.domain;
				}
                else if (i0 && url[i0]) {
                    domain = url[i0].split(".")[0];
                }

				if (REDIRECTS[domain]) {
					domain = REDIRECTS[domain];
				}

				locale = $WH.g_getLocaleFromDomain(domain);

				/* edit start */
				if ($WH.in_array(['fr', 'de', 'es', 'ru', 'en'], domain) == -1) {
					for (i in document.scripts) {
						if (!document.scripts[i].src)
							continue;

						var dmn = document.scripts[i].src.match(/power\/aowowPower.js\?(lang|locale)=(en|fr|de|es|ru)/i);
						if (dmn) {
							domain = dmn[2];
							locale = $WH.g_getLocaleFromDomain(dmn[2]);
							break;
						}
					}
				}
				/* end of edit */

				currentDomain = domain;
				if (t.href.indexOf("#") != -1 && document.location.href.indexOf(url[i1] + "=" + url[i2]) != -1) {
					return;
				}

				mode = ((t.parentNode.className.indexOf("icon") == 0 && t.parentNode.nodeName == "DIV") ? 1 : 0);
				if (!t.onmouseout) {
					if (mode == 0) {
						t.onmousemove = onMouseMove;
					}
					t.onmouseout = onMouseOut;
				}

				updateCursorPos(e);

				var
                    type = $WH.g_getIdFromTypeName(url[i1]),
                    typeId = url[i2];

				if (type == TYPE_PROFILE) {
					locale = 0;
				}

				display(type, typeId, locale, params);
			}
		}

		function onMouseOver(e) {
			e = $WH.$E(e);
			var t = e._target;
			var i = 0;
			while (t != null && i < 5 && scanElement(t, e) == -2323) {
				t = t.parentNode; ++i
			}
		}

		function onMouseMove(e) {
			e = $WH.$E(e);
			updateCursorPos(e);
			$WH.Tooltip.move(cursorX, cursorY, 0, 0, CURSOR_HSPACE, CURSOR_VSPACE);
		}

		function onMouseOut() {
			currentType = null;
			currentA = null;
			$WH.Tooltip.hide();
		}

		function getTooltipField(locale) {
			return (currentParams && currentParams.buff ? "buff_" : "tooltip_") + LOCALES[locale];
		}

		function initElement(type, id, locale) {
			var arr = LOOKUPS[type][0];

			if (arr[id] == null) {
				arr[id] = {};
			}

			if (arr[id].status == null) {
				arr[id].status = {};
			}

			if (arr[id].status[locale] == null) {
				arr[id].status[locale] = STATUS_NONE;
			}
		}

		function display(type, id, locale, params) {
			if (!params) {
				params = {};
			}

			var fullId    = getFullId(id, params);
			currentType   = type;
			currentId     = fullId;
			currentLocale = locale;
			currentParams = params;

			initElement(type, fullId, locale);

			var arr = LOOKUPS[type][0];
			if (arr[fullId].status[locale] == STATUS_OK || arr[fullId].status[locale] == STATUS_NOTFOUND) {
				showTooltip(arr[fullId][getTooltipField(locale)], arr[fullId].icon);
			}
            else if (arr[fullId].status[locale] == STATUS_QUERYING) {
                showTooltip(_LANG.loading);
            }
            else {
                request(type, id, locale, null, params);
            }
		}

		function request(type, id, locale, stealth, params) {
			var fullId = getFullId(id, params);
			var arr = LOOKUPS[type][0];

			if (arr[fullId].status[locale] != STATUS_NONE && arr[fullId].status[locale] != STATUS_ERROR) {
				return;
			}

			arr[fullId].status[locale] = STATUS_QUERYING;

            if (!stealth) {
				arr[fullId].timer = setTimeout(function () {
					showLoading.apply(this, [type, fullId, locale]);
				}, 333);
			}

			var p = "";
			for (var i in params) {
				if (i != "rand" && i != "ench" && i != "gems" && i != "sock") {
					continue;
				}

				if (typeof params[i] == "object") {
					p += "&" + i + "=" + params[i].join(":");
				}
                else if (i == "sock") {
                    p += "&sock";
                }
                else {
                    p += "&" + i + "=" + params[i];
				}
			}

			// var url = "http://" + $WH.g_getDomainFromLocale(locale) + ".wowhead.com"
			// url += "ajax.php?" + LOOKUPS[type][1] + "=" + id + "&power" + p;
			var url = g_staticUrl + "/";
			url += "?" + LOOKUPS[type][1] + "=" + id + "&domain=" + $WH.g_getDomainFromLocale(locale) + "&power" + p;
			$WH.g_ajaxIshRequest(url);
		}

		function showTooltip(html, icon) {
			if (currentA && currentA._fixTooltip) {
				html = currentA._fixTooltip(html, currentType, currentId, currentA);
			}

			var notFound = false;
			if (!html) {
				html = LOOKUPS[currentType][2] + " not found :(";
				icon = "inv_misc_questionmark";
				notFound = true;
			}
            else {
				if (currentParams != null) {
					if (currentParams.pcs && currentParams.pcs.length) {
						var n = 0;
						for (var i = 0, len = currentParams.pcs.length; i < len; ++i) {
							var ak;
							if (ak = html.match(new RegExp("<span><!--si([0-9]+:)*" + currentParams.pcs[i] + '(:[0-9]+)*--><a href="\\?item=(\\d+)">(.+?)</a></span>'))) {
								html = html.replace(ak[0], '<span class="q8"><!--si' + currentParams.pcs[i] + '--><a href="?item=' + ak[3] + '">' + (($WH.isset("g_items") && g_items[currentParams.pcs[i]]) ? g_items[currentParams.pcs[i]]["name_" + LOCALES[currentLocale]] : ak[4]) + "</a></span>");
                                ++n;
							}
						}

						if (n > 0) {
							html = html.replace("(0/", "(" + n + "/");
							html = html.replace(new RegExp("<span>\\(([0-" + n + "])\\)", "g"), '<span class="q2">($1)');
						}
					}

					if (currentParams.c) {
						html = html.replace(/<span class="c([0-9]+?)">(.+?)<\/span><br \/>/g, '<span class="c$1" style="display: none">$2</span>');
                        html = html.replace(new RegExp('<span class="c(' + currentParams.c + ')" style="display: none">(.+?)</span>', "g"), '<span class="c$1">$2</span><br />');
					}

					// custom start
					if ($WH.gc('compare_level') && window.location.href.match(/\?compare/i)) {
						html = $WH.g_setTooltipItemLevel(html, gc('compare_level'));
					}
					// custom end

					if (currentParams.lvl) {
						html = $WH.g_setTooltipItemLevel(html, currentParams.lvl);
					}

					if (currentParams.who && currentParams.when) {
						html = html.replace("<table><tr><td><br />", '<table><tr><td><br /><span class="q2">' + $WH.sprintf(_LANG.achievementcomplete, currentParams.who, currentParams.when.getMonth() + 1, currentParams.when.getDate(), currentParams.when.getFullYear()) + "</span><br /><br />");
						html = html.replace(/class="q0"/g, 'class="r3"');
					}
				}
			}

			if (mode == 1) {
				$WH.Tooltip.setIcon(null);
				$WH.Tooltip.show(currentA, html);
			}
            else {
				$WH.Tooltip.setIcon(icon);
				$WH.Tooltip.showAtXY(html, cursorX, cursorY, CURSOR_HSPACE, CURSOR_VSPACE);
			}

			if (isRemote && $WH.Tooltip.logo) {
				$WH.Tooltip.logo.style.display = (showLogo ? "block": "none");
			}
		}

		function showLoading(type, id, locale) {
			if (currentType == type && currentId == id && currentLocale == locale) {
				showTooltip(_LANG.loading);
				var arr = LOOKUPS[type][0];

				arr[id].timer = setTimeout(function () {
					notFound.apply(this, [type, id, locale]);
				}, 3850);
			}
		}

		function notFound(type, id, locale) {
			var arr = LOOKUPS[type][0];
			arr[id].status[locale] = STATUS_ERROR;

			if (currentType == type && currentId == id && currentLocale == locale) {
				showTooltip(_LANG.noresponse);
			}
		}

		function getFullId(id, params) {
			return id + (params.rand ? "r" + params.rand: "") + (params.ench ? "e" + params.ench: "") + (params.gems ? "g" + params.gems.join(",") : "") + (params.sock ? "s": "");
		}

		this.register = function (type, id, locale, json) {
			var arr = LOOKUPS[type][0];
			initElement(type, id, locale);

			if (arr[id].timer) {
				clearTimeout(arr[id].timer);
				arr[id].timer = null;
			}

			$WH.cO(arr[id], json);

			if (arr[id].status[locale] == STATUS_QUERYING) {
				if (arr[id][getTooltipField(locale)]) {
					arr[id].status[locale] = STATUS_OK;
				}
                else {
					arr[id].status[locale] = STATUS_NOTFOUND;
				}
			}

			if (currentType == type && id == currentId && currentLocale == locale) {
				showTooltip(arr[id][getTooltipField(locale)], arr[id].icon);
			}
		};

		this.registerNpc = function (id, locale, json) {
			this.register(TYPE_NPC, id, locale, json);
		};

		this.registerObject = function (id, locale, json) {
			this.register(TYPE_OBJECT, id, locale, json);
		};

		this.registerItem = function (id, locale, json) {
			this.register(TYPE_ITEM, id, locale, json);
		};

		this.registerQuest = function (id, locale, json) {
			this.register(TYPE_QUEST, id, locale, json);
		};

		this.registerSpell = function (id, locale, json) {
			this.register(TYPE_SPELL, id, locale, json);
		};

		this.registerAchievement = function (id, locale, json) {
			this.register(TYPE_ACHIEVEMENT, id, locale, json);
		};

		this.registerProfile = function (id, locale, json) {
			this.register(TYPE_PROFILE, id, locale, json);
		};

		this.request = function (type, id, locale, params) {
			if (!params) {
				params = {};
			}

			var fullId = getFullId(id, params);
			initElement(type, fullId, locale);

			request(type, id, locale, 1, params);
		};

		this.requestItem = function (id, params) {
			this.request(TYPE_ITEM, id, Locale.id, params);
		};

		this.requestSpell = function (id) {
			this.request(TYPE_SPELL, id, Locale.id);
		};

		this.getStatus = function (type, id, locale) {
			var arr = LOOKUPS[type][0];
			if (arr[id] != null) {
				return arr[id].status[locale];
			}
            else {
				return STATUS_NONE;
			}
		};

		this.getItemStatus = function (id, locale) {
			this.getStatus(TYPE_ITEM, id, locale);
		};

		this.getSpellStatus = function (id, locale) {
			this.getStatus(TYPE_SPELL, id, locale);
		};

		if (isRemote) {
			this.set = function (foo) {
				$WH.cO(opt, foo);
			};

			this.showTooltip = function (e, tooltip, icon) {
				updateCursorPos(e);
				showTooltip(tooltip, icon);
			};

			this.hideTooltip = function () {
				$WH.Tooltip.hide();
			};

			this.moveTooltip = function (e) {
				onMouseMove(e);
			}
		}

		init();
	}
};