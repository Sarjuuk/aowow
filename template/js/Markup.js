var Markup = {
	MODE_COMMENT: 1,
	MODE_ARTICLE: 2,
	MODE_QUICKFACTS: 3,
	MODE_SIGNATURE: 4,
	simpleTags: {
		img: 1,
		pad: 1,
		item: 1,
		spell: 1,
		achievement: 1,
		npc: 1,
		skill: 1,
		pet: 1,
        'class': 1
	},
	_prepare: function(D, C) {
		Markup.tags = [];
		Markup.links = [];
		Markup.nTags = 0;
		Markup.nLinks = 0;
		D = D.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
		D = D.replace(/^(\s*)|\r|(\s*)$/g, "");
		D = D.replace(/\n(\s+)\n/g, "\n\n");
		var B = "b|i|u|s|small";
		var A = "b|i|u|s|small|url";
		switch (C) {
		case Markup.MODE_ARTICLE:
			Markup.maps = [];
			D = D.replace(/(.)?\[toggler\s*[\s+=:]\s*(.+?)\]/gi,
			function(F, E, H) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					var G = Markup._yank(H, [["id", "[a-z0-9]+"]]);
					Markup.tags.push({
						name: "toggler",
						close: false,
						other: G
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			});
			D = D.replace(/(.)?\[div\s*[\s+=:]\s*(.+?)\]/gi,
			function(F, E, H) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					var G = Markup._yank(H, [["id", "[a-z0-9]+"], ["hidden", ""]]);
					Markup.tags.push({
						name: "div",
						close: false,
						other: G
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			});
			D = D.replace(/(.)?\[img\s*[\s+=:]\s*(.+?)\]/gi,
			function(F, E, H) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					var G = Markup._yank(H, [["src", "\\S+"], ["width", "[0-9]+"], ["height", "[0-9]+"], ["float", "left|right"]]);
					Markup.tags.push({
						name: "img",
						close: false,
						other: G
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			});
			D = D.replace(/(.)?\[map\s*[\s+=:]\s*(.+?)\]/gi,
			function(F, E, H) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					var G = Markup._yank(H, [["zone", "[0-9]+[a-z]?"], ["source", "\\S+"]]);
					Markup.tags.push({
						name: "map",
						close: false,
						other: G
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			});
			D = D.replace(/(.)?\[pin\s*[\s+=:]\s*(.+?)\]/gi,
			function(F, E, H) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					var G = Markup._yank(H, [["x", "[0-9]{1,2}(\\.[0-9])?"], ["y", "[0-9]{1,2}(\\.[0-9])?"], ["url", "\\S+"], ["type", "[0-9]+"]]);
					Markup.tags.push({
						name: "pin",
						close: false,
						other: G
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			});
			B += "|h3|minibox";
			A += "|h3|minibox|toggler|div|map|pin";
		case Markup.MODE_QUICKFACTS:
			D = D.replace(/(.)?\[color\s*[\s+=:]\s*(aqua|black|blue|fuchsia|gray|green|lime|maroon|navy|olive|purple|red|silver|teal|white|yellow|c[0-9]{2}|q[0-8]?|#[a-f0-9]{6})\]/gi,
			function(F, E, G) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					Markup.tags.push({
						name: "color",
						color: false,
						other: (G).toLowerCase()
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			});
			D = D.replace(/(.)?\[(item|spell|achievement|class|npc|skill|pet)\s*[\s+=:]\s*(\d+?)\]/gi,
			function(F, E, I, H) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					var G = Markup.tags.push({
						name: (I).toLowerCase(),
						close: false,
						other: H
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			});
			D = D.replace(/(.)?\[icon\s*[\s+=:]\s*(.+?)\]/gi,
			function(F, E, H) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					var G = Markup._yank(H, [["name", "\\S+"], ["size", "tiny|small|medium|large"], ["float", "left|right"]]);
					Markup.tags.push({
						name: "icon",
						close: false,
						other: G
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			});
			B += "|pad|span";
			A += "|color|span|icon";
			case Markup.MODE_COMMENT:
			D = D.replace(/(.)?\[quote(\s*[\s+=:]\s*([^\]]+?))?\]/gi,
			function(F, E, I, H) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					var G = Markup.tags.push({
						name: "quote",
						close: false,
						other: H
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			});
			B += "|code|ul|ol|li";
			A += "|code|quote|ul|ol|li";
		default:
			D = D.replace(/(.)?\[url\s*[\s+=:]\s*([^\]]+?)\]/gi,
			function(F, E, G) {
				if (E == "\\") {
					return F.substr(1)
				} else {
					Markup.tags.push({
						name: "url",
						close: false,
						other: G
					});
					return (E || "") + "<t" + (Markup.nTags++) + ">"
				}
			})
		}
		D = D.replace(new RegExp("(.)?\\[(" + B + ")\\]", "gi"),
		function(F, E, G) {
			if (E == "\\") {
				return F.substr(1)
			} else {
				Markup.tags.push({
					name: (G).toLowerCase(),
					close: false,
					other: ""
				});
				return (E || "") + "<t" + (Markup.nTags++) + ">"
			}
		});
		D = D.replace(new RegExp("(.)?\\[/(" + A + ")\\]", "gi"),
		function(F, E, G) {
			if (E == "\\") {
				return F.substr(1)
			} else {
				Markup.tags.push({
					name: (G).toLowerCase(),
					close: true,
					other: ""
				});
				return (E || "") + "<t" + (Markup.nTags++) + ">"
			}
		});
		D = D.replace(/(https?:\/\/|www\.)([\/_a-z0-9\%\?#@\-\+~&=;:'\(\)]|\.\S|,\S)+/gi,
		function(E) {
			Markup.links.push(E);
			return "<l" + (Markup.nLinks++) + ">"
		});
		return D
	},
	_parseCode: function(G, K, E) {
		var B = [],
		I = [],
		D = "",
		C;
		var F = G.length;
		while (E < F) {
			if (G.charAt(E) == "<" && G.charAt(E + 1) == "t") {
				if (D.length) {
					if (K == "ol" || K == "ul") {
						if (trim(D).length) {
							I.push(["<text>", D])
						}
					} else {
						if (K != "map") {
							B.push(["<text>", D])
						}
					}
					D = ""
				}
				E += 2;
				C = "";
				while (G.charAt(E) != ">") {
					C += G.charAt(E); ++E
				}++E;
				var L = Markup.tags[parseInt(C)];
				if (L.close) {
					if (L.name == K) {
						break
					}
				} else {
					var H = Markup.simpleTags[L.name] != null;
					var J = [];
					if (!H) {
						var A = Markup._parseCode(G, L.name, E);
						J = A[0];
						E = A[1]
					}
					if (J.length || H) {
						if (L.name == "url" && !Markup._isUrlSafe(L.other)) {
							L.name = "<node>"
						}
						if (K == "ol" || K == "ul") {
							if (L.name == "li") {
								I = Markup._cleanNodes(I);
								if (I.length) {
									B.push(["li", I]);
									I = []
								}
								B.push(["li", J])
							} else {
								I.push([L.name, J, L.other])
							}
						} else {
							if (K == "map") {
								if (L.name == "pin") {
									B.push(["pin", J, L.other])
								}
							} else {
								if (L.name == "li") {
									B.push(["<node>", J, L.other])
								} else {
									if (L.name != "pin") {
										B.push([L.name, J, L.other])
									}
								}
							}
						}
					}
				}
			} else {
				D += G.charAt(E); ++E
			}
		}
		if (D.length) {
			if (K == "ol" || K == "ul") {
				if (trim(D).length) {
					I.push(["<text>", D])
				}
			} else {
				if (K != "map") {
					B.push(["<text>", D])
				}
			}
		}
		I = Markup._cleanNodes(I);
		if (I.length) {
			B.push(["li", I]);
			I = []
		}
		return [B, E]
	},
	_cleanNodes: function(B) {
		var D = [];
		for (var C = 0, A = B.length; C < A; ++C) {
			if (B[C][0] == "<text>") {
				if (C == 0) {
					B[C][1] = B[C][1].replace(/^\n+/g, "")
				}
				if (C == B.length - 1) {
					B[C][1] = B[C][1].replace(/\n+$/g, "")
				}
				if (B[C][1] != "") {
					D.push(B[C])
				}
			} else {
				D.push(B[C])
			}
		}
		return D
	},
	_yank: function(D, C) {
		var E = {};
		D = " " + D + " ";
		for (var B = 0, A = C.length; B < A; ++B) {
			if (C[B][1] == "") {
				D = D.replace(new RegExp("\\s" + C[B][0] + "\\s", "ig"),
				function(F) {
					E[C[B][0]] = true;
					return " "
				})
			} else {
				D = D.replace(new RegExp("\\s" + C[B][0] + "\\s*[\\s=:]\\s*(" + C[B][1] + ")\\s", "ig"),
				function(G, F) {
					E[C[B][0]] = F;
					return " "
				})
			}
		}
		return E
	},
	_isUrlSafe: function(A) {
		if (!A) {
			return true
		}
		return ! A.match(/^[a-z0-9]+?:/i) || A.match(/^https?:/i)
	},
	_htmlmize: function(B) {
		var G = "";
		for (var D = 0, A = B.length; D < A; ++D) {
			switch (B[D][0]) {
			case "<text>":
				G += B[D][1];
				break;
			case "<node>":
				G += Markup._htmlmize(B[D][1]);
				break;
			case "minibox":
				G += '<div class="minibox">' + Markup._htmlmize(B[D][1]) + "</div>";
				break;
			case "code":
				G += '<pre class="code">' + Markup._htmlmize(B[D][1]) + "</pre>";
				break;
			case "quote":
				G += '<div class="quote">';
				if (B[D][2]) {
					var H = trim(B[D][2]);
					if (H.length > 0) {
						G += "<small><b>";
						if (H.match(/[^a-z0-9]/i) == null && H.length >= 4 && H.length <= 16) {
							G += '<a href="?user=' + H + '">' + H + "</a>"
						} else {
							G += H
						}
						G += "</b> " + LANG.markup_said + '</small><div class="pad"></div>'
					}
				}
				G += Markup._htmlmize(B[D][1]) + "</div>";
				break;
			case "url":
				G += '<a href="' + B[D][2] + '"';
				if (B[D][2].indexOf("wowhead.com") == -1 && B[D][2].substr(0, 5) == "http:") {
					G += ' target="_blank"'
				}
				G += ">" + Markup._htmlmize(B[D][1]) + "</a>";
				break;
			case "li":
				G += "<li><div>" + Markup._htmlmize(B[D][1]) + "</div></li>";
				break;
			case "u":
				G += "<ins>" + Markup._htmlmize(B[D][1]) + "</ins>";
				break;
			case "s":
				G += "<del>" + Markup._htmlmize(B[D][1]) + "</del>";
				break;
			case "span":
				G += "<span>" + Markup._htmlmize(B[D][1]) + "</span>";
				break;
			case "pad":
				G += '<div class="pad">' + Markup._htmlmize(B[D][1]) + "</div>";
				break;
			case "color":
				G += "<span " + ((B[D][2].charAt(0) == "q" || B[D][2].charAt(0) == "c") ? 'class="': 'style="color: ') + B[D][2] + '">' + Markup._htmlmize(B[D][1]) + "</span>";
				break;
			case "toggler":
				var F = B[D][2];
				if (F.id != null) {
					G += '<a href="javascript:;" class="disclosure-' + (F.hidden ? "on": "off") + '" onmousedown="return false" onclick="this.className = \'disclosure-\' + (g_toggleDisplay(ge(\'ov4io23-' + F.id + "')) ? 'on' : 'off')\">" + Markup._htmlmize(B[D][1]) + "</a>"
				}
				break;
			case "div":
				var F = B[D][2];
				if (F.id != null) {
					G += '<div id="ov4io23-' + F.id + '"';
					if (F.hidden) {
						G += ' style="display: none"'
					}
					G += ">" + Markup._htmlmize(B[D][1]) + "</div>"
				}
				break;
			case "icon":
				var F = B[D][2];
				F.size = F.size || 'tiny';
				if (F.name != "" && Markup._isUrlSafe(F.name)) {
					G += '<span class="icon' + F.size + '"';
					G += ' style="background-image: url(images/icons/' + F.size + '/' + F.name + (F.size == 'tiny' ? '.gif' : '.jpg') + ');';
					if (F["float"]) {
						G += " float: " + F["float"]
					}
					G += '" />' + Markup._htmlmize(B[D][1]) + '</span>'
				}
				break;
			case "img":
				var F = B[D][2];
				if (F.src != "" && Markup._isUrlSafe(F.src)) {
					G += '<img src="' + F.src + '" alt="" class="border"';
					if (F.width) {
						G += ' width="' + F.width + '"'
					}
					if (F.height) {
						G += ' height="' + F.height + '"'
					}
					G += ' style="margin: 8px;';
					if (F["float"]) {
						G += " float: " + F["float"]
					}
					G += '" />'
				}
				break;
			case "achievement":
				if (g_achievements[B[D][2]]) {
					G += "<a href=\"?achievement=" + B[D][2] + "\" class=\"icontiny\">";
					G += "<img src=\"images/icons/tiny/" + g_achievements[B[D][2]].icon + ".gif\" align=\"absmiddle\">";
					G += "<span>" + g_achievements[B[D][2]]["name_" + g_locale.name] + "</span></a>";
				}
				else
					G += '<a href="?achievement=' + B[D][2] + '">(Achievement #' + B[D][2] + ")</a>";
				break;
			case "class":
				if (g_classes[B[D][2]]) {
					G += "<a href=\"?class=" + B[D][2] + "\" class=\"icontiny c" + B[D][2] + "\">";
					G += "<img src=\"images/icons/tiny/class_" + g_file_classes[B[D][2]] + ".gif\" align=\"absmiddle\">";
					G += "<span>" + g_classes[B[D][2]]["name_" + g_locale.name] + "</span></a>";
				}
				else
					G += '<a href="?class=' + B[D][2] + '">(Class #' + B[D][2] + ")</a>";
				break;
			case "npc":
				if (g_npcs[B[D][2]]) {
					G += "<a href=\"?npc=" + B[D][2] + "\" class=\"icontiny\">";
					G += "<img src=\"images/icons/tiny/" + g_npcs[B[D][2]].icon + ".gif\" align=\"absmiddle\">";
					G += "<span>" + g_npcs[B[D][2]]["name_" + g_locale.name] + "</span></a>";
				}
				else
					G += '<a href="?npc=' + B[D][2] + '">(NPC #' + B[D][2] + ")</a>";
				break;
			case "skill":
				if (g_skills[B[D][2]]) {
					G += "<a href=\"?skill=" + B[D][2] + "\" class=\"icontiny\">";
					G += "<img src=\"images/icons/tiny/" + g_skills[B[D][2]].icon + ".gif\" align=\"absmiddle\">";
					G += "<span>" + g_skills[B[D][2]]["name_" + g_locale.name] + "</span></a>";
				}
				else
					G += '<a href="?skill=' + B[D][2] + '">(Skill #' + B[D][2] + ")</a>";
				break;
			case "pet":
				if (g_pets[B[D][2]]) {
					G += "<a href=\"?pet=" + B[D][2] + "\" class=\"icontiny\">";
					G += "<img src=\"images/icons/tiny/" + g_pets[B[D][2]].icon + ".gif\" align=\"absmiddle\">";
					G += "<span>" + g_pets[B[D][2]]["name_" + g_locale.name] + "</span></a>";
				}
				else
					G += '<a href="?pet=' + B[D][2] + '">(Pet #' + B[D][2] + ")</a>";
				break;
			case "item":
				if (g_items[B[D][2]]) {
					G += "<a href=\"?item=" + B[D][2] + "\" class=\"icontiny q" + B[D][2].quality + "\">";
					G += "<img src=\"images/icons/tiny/" + g_items[B[D][2]].icon + ".gif\" align=\"absmiddle\">";
					G += "<span>" + g_items[B[D][2]]["name_" + g_locale.name] + "</span></a>";
				}
				else
					G += '<a href="?item=' + B[D][2] + '">(Item #' + B[D][2] + ")</a>";
				break;
			case "spell":
				if (g_spells[B[D][2]]) {
					G += "<a href=\"?spell=" + B[D][2] + "\" class=\"icontiny\">";
					G += "<img src=\"images/icons/tiny/" + g_spells[B[D][2]].icon + ".gif\" align=\"absmiddle\">";
					G += "<span>" + g_spells[B[D][2]]["name_" + g_locale.name] + "</span></a>";
				}
				else
					G += '<a href="?spell=' + B[D][2] + '">(Spell #' + B[D][2] + ")</a>";
				break;
			case "map":
				var F = B[D][2];
				if (F.zone != null) {
					var E = Markup._htmlmize(B[D][1]);
					G += '<a href="javascript:;" onclick="Markup.mapperPreview(' + Markup.maps.length + ')">(Map: Zone #' + parseInt(F.zone) + ", " + E.length + " pin" + (E.length == 1 ? "": "s") + ")</a>";
					Markup.maps.push([F.zone, E])
				}
				break;
			case "pin":
				var F = B[D][2];
				if (!Markup._isUrlSafe(F.url)) {
					F.url = ""
				}
				if (G == "") {
					G = []
				}
				var C = Markup._htmlmize(B[D][1]).replace(/\n/g, "<br />");
				if (F.url && F.url.indexOf("?npc=") != -1) {
					C = '<b class="q">' + C + '</b><br/ ><span class="q2">Click to view this NPC</span>'
				}
				G.push([parseFloat(F.x || 0), parseFloat(F.y || 0), {
					label: C,
					url: F.url,
					type: F.type
				}]);
				break;
            default:
				G += "<" + B[D][0] + ">" + Markup._htmlmize(B[D][1]) + "</" + B[D][0] + ">"
			}
		}
		return G
	},
	_tagless: function(B) {
		var E = "";
		for (var C = 0, A = B.length; C < A; ++C) {
			switch (B[C][0]) {
			case "<text>":
				E += B[C][1];
				break;
			case "img":
				var D = B[C][2];
				if (D.src != "" && Markup._isUrlSafe(D.src)) {
					E += "(Image: " + D.src + ")"
				}
				break;
			case "npc":
				E += "(NPC #" + B[C][2] + ")";
				break;
			case "achievement":
				E += "(Achievement #" + B[C][2] + ")";
				break;
			case "class":
				E += "(Class #" + B[C][2] + ")";
				break;
			case "skill":
				E += "(Skill #" + B[C][2] + ")";
				break;
			case "pet":
				E += "(Pet #" + B[C][2] + ")";
				break;
			case "item":
				E += "(Item #" + B[C][2] + ")";
				break;
			case "spell":
				E += "(Spell #" + B[C][2] + ")";
				break;
			case "map":
				E += "(Map)";
				break;
			case "pin":
				break;
			default:
				E += Markup._tagless(B[C][1])
			}
		}
		return E
	},
	toHtml: function(D, C) {
		C = C || {};
		C.root = C.root || "";
		if (C.mode != null && C.mode != Markup.MODE_SIGNATURE) {
			D = D.replace(/\s*\[(ol|ul|h3)\]/ig,
			function(F, E) {
				return "[" + E + "]"
			});
			D = D.replace(/\[\/(ol|ul|h3|minibox|code|quote)\]\s*/ig,
			function(F, E) {
				return "[/" + E + "]"
			});
			D = D.replace(/\s*\[(pad)\]\s*/ig,
			function(F, E) {
				return "[" + E + "]"
			})
		}
		D = Markup._prepare(D, C.mode);
		var A = Markup._parseCode(D, C.root, 0)[0];
		var B = Markup._htmlmize(A);
		B = B.replace(/<l([0-9]+)>/g,
		function(G, E) {
			var F = Markup.links[parseInt(E)];
			return '<a href="' + F.replace(/^www/, "http://www") + (F.indexOf("wowhead.com") == -1 ? '" target="_blank">': '">') + (F.length > 100 ? F.substr(0, 97) + "...": F) + "</a>"
		});
		B = B.replace(/\n/g, "<br />");
		return B
	},
	removeTags: function(D, C) {
		C = C || {};
		C.root = C.root || "";
		if (C.mode != null && C.mode != Markup.MODE_SIGNATURE) {
			D = D.replace(/\s*\[(ol|ul|h3)\]\s?/ig,
			function(F, E) {
				return " [" + E + "]"
			});
			D = D.replace(/\s?\[\/(ol|ul|h3|minibox|code|quote)\]\s*/ig,
			function(F, E) {
				return "[/" + E + "]"
			});
			D = D.replace(/\s*\[(pad)\]\s*/ig,
			function(F, E) {
				return "[" + E + "]"
			})
		}
		D = Markup._prepare(D, C.mode);
		var A = Markup._parseCode(D, C.root, 0)[0];
		var B = Markup._tagless(A);
		B = B.replace(/<l([0-9]+)>/g,
		function(G, E) {
			var F = Markup.links[parseInt(E)];
			return F
		});
		B = B.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, '"');
		return B
	},
	mapperPreview: function(C) {
		try {
			window.mapper = Markup.maps[C];
			var B = window.open("?edit=mapperpreview", "mapperpreview", "toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=no,resizable=no,width=800,height=540");
			B.focus()
		} catch(A) {}
	},
	printHtml: function (c, d, b) {
		d = ge(d);
		var a = Markup.toHtml(c, b);
		d.innerHTML = a;
	}
};
