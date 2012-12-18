var fi_type = null;
var fi_weights = null;
var fi_weightsFactor;
var fi_nExtraCols;
var fi_gemScores;
var fi_upgradeId;
var fi_filters = {
	items: [
		{id: 1, name: "sepgeneral"},
		{id: 131, name: "addedinwotlk", type: "yn"},
		{id: 110, name: "addedinbc", type: "yn"},
		{id: 82, name: "addedinp24", type: "yn"},
		{id: 2, name: "bindonpickup", type: "yn"},
		{id: 3, name: "bindonequip", type: "yn"},
		{id: 4, name: "bindonuse", type: "yn"},
		{id: 133, name: "bindtoaccount", type: "yn"},
		{id: 146, name: "heroicitem", type: "yn"},
		{id: 107, name: "effecttext", type: "str"},
		{id: 81, name: "fitsgemslot", type: "gem"},
		{id: 132, name: "glyphtype", type: "glyphtype"},
		{id: 80, name: "hassockets", type: "gem"},
		{id: 100, name: "nsockets", type: "num"},
		{id: 124, name: "randomenchants", type: "str"},
		{id: 125, name: "reqarenartng", type: "num"},
		{id: 111, name: "reqskillrank", type: "num"},
		{id: 99, name: "requiresprof", type: "profession"},
		{id: 66, name: "requiresprofspec", type: "profession"},
		{id: 17, name: "requiresrepwith", type: "faction-any+none"},
		{id: 15, name: "unique", type: "yn"},
		{id: 83, name: "uniqueequipped", type: "yn"},
		{id: 152, name: "classspecific", type: "classs"},
		{id: 153, name: "racespecific", type: "race"},
		{id: 19, name: "sepbasestats"},
		{id: 21, name: "agi", type: "num"},
		{id: 23, name: "int", type: "num"},
		{id: 22, name: "sta", type: "num"},
		{id: 24, name: "spi", type: "num"},
		{id: 20, name: "str", type: "num"},
		{id: 115, name: "health", type: "num"},
		{id: 116, name: "mana", type: "num"},
		{id: 60, name: "healthrgn", type: "num"},
		{id: 61, name: "manargn", type: "num"},
		{id: 120, name: "sepdefensivestats"},
		{id: 41, name: "armor", type: "num"},
		{id: 44, name: "blockrtng", type: "num"},
		{id: 43, name: "block", type: "num"},
		{id: 42, name: "defrtng", type: "num"},
		{id: 45, name: "dodgertng", type: "num"},
		{id: 46, name: "parryrtng", type: "num"},
		{id: 79, name: "resirtng", type: "num"},
		{id: 31, name: "sepoffensivestats"},
		{id: 77, name: "atkpwr", type: "num"},
		{id: 97, name: "feratkpwr", type: "num", indent: 1},
		{id: 114, name: "armorpenrtng", type: "num"},
		{id: 96, name: "critstrkrtng", type: "num"},
		{id: 117, name: "exprtng", type: "num"},
		{id: 103, name: "hastertng", type: "num"},
		{id: 119, name: "hitrtng", type: "num"},
		{id: 94, name: "splpen", type: "num"},
		{id: 123, name: "splpwr", type: "num"},
		{id: 52, name: "arcsplpwr", type: "num", indent: 1},
		{id: 53, name: "firsplpwr", type: "num", indent: 1},
		{id: 54, name: "frosplpwr", type: "num", indent: 1},
		{id: 55, name: "holsplpwr", type: "num", indent: 1},
		{id: 56, name: "natsplpwr", type: "num", indent: 1},
		{id: 57, name: "shasplpwr", type: "num", indent: 1},
		{id: 122, name: "sepweaponstats"},
		{id: 32, name: "dps", type: "num"},
		{id: 35, name: "damagetype", type: "resistance"},
		{id: 33, name: "dmgmin1", type: "num"},
		{id: 34, name: "dmgmax1", type: "num"},
		{id: 36, name: "speed", type: "num"},
		{id: 134, name: "mledps", type: "num"},
		{id: 135, name: "mledmgmin", type: "num"},
		{id: 136, name: "mledmgmax", type: "num"},
		{id: 137, name: "mlespeed", type: "num"},
		{id: 138, name: "rgddps", type: "num"},
		{id: 139, name: "rgddmgmin", type: "num"},
		{id: 140, name: "rgddmgmax", type: "num"},
		{id: 141, name: "rgdspeed", type: "num"},
		{id: 121, name: "sepresistances"},
		{id: 25, name: "arcres", type: "num"},
		{id: 26, name: "firres", type: "num"},
		{id: 28, name: "frores", type: "num"},
		{id: 30, name: "holres", type: "num"},
		{id: 27, name: "natres", type: "num"},
		{id: 29, name: "shares", type: "num"},
		{id: 67, name: "sepsource"},
		{id: 86, name: "craftedprof", type: "profession"},
		{id: 16, name: "dropsin", type: "zone"},
		{id: 105, name: "dropsinnormal", type: "heroicdungeon-any"},
		{id: 106, name: "dropsinheroic", type: "heroicdungeon-any"},
		{id: 147, name: "dropsinnormal10", type: "multimoderaid-any"},
		{id: 148, name: "dropsinnormal25", type: "multimoderaid-any"},
		{id: 149, name: "dropsinheroic10", type: "heroicraid-any"},
		{id: 150, name: "dropsinheroic25", type: "heroicraid-any"},
		{id: 68, name: "otdisenchanting", type: "yn"},
		{id: 69, name: "otfishing", type: "yn"},
		{id: 70, name: "otherbgathering", type: "yn"},
		{id: 71, name: "otitemopening", type: "yn"},
		{id: 72, name: "otlooting", type: "yn"},
		{id: 143, name: "otmilling", type: "yn"},
		{id: 73, name: "otmining", type: "yn"},
		{id: 74, name: "otobjectopening", type: "yn"},
		{id: 75, name: "otpickpocketing", type: "yn"},
		{id: 88, name: "otprospecting", type: "yn"},
		{id: 93, name: "otpvp", type: "pvp"},
		{id: 76, name: "otskinning", type: "yn"},
		{id: 118, name: "purchasablewith", type: "currency-any"},
		{id: 144, name: "purchasablewithhonor", type: "yn"},
		{id: 145, name: "purchasablewitharena", type: "yn"},
		{id: 18, name: "rewardedbyfactionquest", type: "side"},
		{id: 126, name: "rewardedbyquestin", type: "zone-any"},
		{id: 92, name: "soldbyvendor", type: "yn"},
		{id: 129, name: "soldbynpc", type: "str-small"},
		{id: 128, name: "sepsource", type: "itemsource"},
		{id: 47, name: "sepindividualstats"},
		{id: 37, name: "mleatkpwr", type: "num"},
		{id: 84, name: "mlecritstrkrtng", type: "num"},
		{id: 78, name: "mlehastertng", type: "num"},
		{id: 95, name: "mlehitrtng", type: "num"},
		{id: 38, name: "rgdatkpwr", type: "num"},
		{id: 40, name: "rgdcritstrkrtng", type: "num"},
		{id: 101, name: "rgdhastertng", type: "num"},
		{id: 39, name: "rgdhitrtng", type: "num"},
		{id: 49, name: "splcritstrkrtng", type: "num"},
		{id: 102, name: "splhastertng", type: "num"},
		{id: 48, name: "splhitrtng", type: "num"},
		{id: 51, name: "spldmg", type: "num"},
		{id: 50, name: "splheal", type: "num"},
		{id: 58, name: "sepmisc"},
		{id: 109, name: "armorbonus", type: "num"},
		{id: 90, name: "avgbuyout", type: "num"},
		{id: 65, name: "avgmoney", type: "num"},
		{id: 63, name: "buyprice", type: "num"},
		{id: 9, name: "conjureditem", type: "yn"},
		{id: 62, name: "cooldown", type: "num"},
		{id: 8, name: "disenchantable", type: "yn"},
		{id: 59, name: "dura", type: "num"},
		{id: 104, name: "flavortext", type: "str"},
		{id: 7, name: "hasflavortext", type: "yn"},
		{id: 142, name: "icon", type: "str"},
		{id: 10, name: "locked", type: "yn"},
		{id: 127, name: "notavailable", type: "yn"},
		{id: 85, name: "objectivequest", type: "side"},
		{id: 11, name: "openable", type: "yn"},
		{id: 12, name: "partofset", type: "yn"},
		{id: 98, name: "partyloot", type: "yn"},
		{id: 89, name: "prospectable", type: "yn"},
		{id: 5, name: "questitem", type: "yn"},
		{id: 13, name: "randomlyenchanted", type: "yn"},
		{id: 14, name: "readable", type: "yn"},
		{id: 87, name: "reagentforability", type: "profession"},
		{id: 64, name: "sellprice", type: "num"},
		{id: 6, name: "startsquest", type: "side"},
		{id: 91, name: "tool", type: "totemcategory"},
		{id: 151, name: "id", type: "num", before: "name"},
		{id: 112, name: "sepcommunity"},
		{id: 130, name: "hascomments", type: "yn"},
		{id: 113, name: "hasscreenshots", type: "yn"}
	],
	npcs: [
		{id: 4, name: "sepgeneral"},
		{id: 36, name: "addedinwotlk", type: "yn"},
		{id: 26, name: "addedinbc", type: "yn"},
		{id: 13, name: "addedinp24", type: "yn"},
		{id: 5, name: "canrepair", type: "yn"},
		{id: 3, name: "faction", type: "faction"},
		{id: 6, name: "foundin", type: "zone"},
		{id: 1, name: "health", type: "num"},
		{id: 2, name: "mana", type: "num"},
		{id: 32, name: "instanceboss", type: "yn"},
		{id: 7, name: "startsquest", type: "side"},
		{id: 8, name: "endsquest", type: "side"},
		{id: 34, name: "usemodel", type: "str-small"},
		{id: 35, name: "useskin", type: "str"},
		{id: 37, name: "id", type: "num"},
		{id: 14, name: "seploot"},
		{id: 12, name: "averagemoneydropped", type: "num"},
		{id: 15, name: "gatherable", type: "yn"},
		{id: 9, name: "lootable", type: "yn"},
		{id: 16, name: "minable", type: "yn"},
		{id: 11, name: "pickpocketable", type: "yn"},
		{id: 10, name: "skinnable", type: "yn"},
		{id: 17, name: "sepgossipoptions"},
		{id: 18, name: "auctioneer", type: "yn"},
		{id: 19, name: "banker", type: "yn"},
		{id: 20, name: "battlemaster", type: "yn"},
		{id: 21, name: "flightmaster", type: "yn"},
		{id: 22, name: "guildmaster", type: "yn"},
		{id: 23, name: "innkeeper", type: "yn"},
		{id: 24, name: "talentunlearner", type: "yn"},
		{id: 25, name: "tabardvendor", type: "yn"},
		{id: 27, name: "stablemaster", type: "yn"},
		{id: 28, name: "trainer", type: "yn"},
		{id: 29, name: "vendor", type: "yn"},
		{id: 30, name: "sepcommunity"},
		{id: 33, name: "hascomments", type: "yn"},
		{id: 31, name: "hasscreenshots", type: "yn"}
	],
	objects: [
		{id: 8, name: "sepgeneral"},
		{id: 14, name: "addedinwotlk", type: "yn"},
		{id: 12, name: "addedinbc", type: "yn"},
		{id: 6, name: "addedinp24", type: "yn"},
		{id: 1, name: "foundin", type: "zone"},
		{id: 7, name: "requiredskilllevel", type: "num"},
		{id: 2, name: "startsquest", type: "side"},
		{id: 3, name: "endsquest", type: "side"},
		{id: 15, name: "id", type: "num"},
		{id: 9, name: "seploot"},
		{id: 5, name: "averagemoneycontained", type: "num"},
		{id: 4, name: "openable", type: "yn"},
		{id: 10, name: "sepcommunity"},
		{id: 13, name: "hascomments", type: "yn"},
		{id: 11, name: "hasscreenshots", type: "yn"}
	],
	quests: [
		{id: 12, name: "sepgeneral"},
		{id: 26, name: "addedinwotlk", type: "yn"},
		{id: 20, name: "addedinbc", type: "yn"},
		{id: 8, name: "addedinp24", type: "yn"},
		{id: 27, name: "daily", type: "yn"},
		{id: 28, name: "weekly", type: "yn"},
		{id: 29, name: "repeatable", type: "yn"},
		{id: 9, name: "objectiveearnrepwith", type: "faction-any+none"},
		{id: 5, name: "sharable", type: "yn"},
		{id: 19, name: "startsfrom", type: "queststart"},
		{id: 21, name: "endsat", type: "questend"},
		{id: 11, name: "suggestedplayers", type: "num"},
		{id: 6, name: "timer", type: "num"},
		{id: 30, name: "id", type: "num"},
		{id: 13, name: "sepgainsrewards"},
		{id: 2, name: "experiencegained", type: "num"},
		{id: 23, name: "itemchoices", type: "num"},
		{id: 22, name: "itemrewards", type: "num"},
		{id: 3, name: "moneyrewarded", type: "num"},
		{id: 4, name: "spellrewarded", type: "yn"},
		{id: 1, name: "increasesrepwith", type: "faction"},
		{id: 10, name: "decreasesrepwith", type: "faction"},
		{id: 14, name: "sepseries"},
		{id: 7, name: "firstquestseries", type: "yn"},
		{id: 15, name: "lastquestseries", type: "yn"},
		{id: 16, name: "partseries", type: "yn"},
		{id: 17, name: "sepcommunity"},
		{id: 25, name: "hascomments", type: "yn"},
		{id: 18, name: "hasscreenshots", type: "yn"},
		{id: 24, name: "lacksstartend", type: "yn"}
	],
	spells: [
		{id: 6, name: "sepgeneral"},
		{id: 2, name: "prcntbasemanarequired", type: "num"},
		{id: 19, name: "scaling", type: "yn"},
		{id: 10, name: "firstrank", type: "yn"},
		{id: 12, name: "lastrank", type: "yn"},
		{id: 13, name: "rankno", type: "num"},
		{id: 1, name: "manaenergyragecost", type: "num"},
		{id: 3, name: "requiresnearbyobject", type: "yn"},
		{id: 5, name: "requiresprofspec", type: "yn"},
		{id: 9, name: "source", type: "spellsource"},
		{id: 4, name: "trainingcost", type: "num"},
		{id: 14, name: "id", type: "num"},
		{id: 15, name: "icon", type: "str"},
		{id: 7, name: "sepcommunity"},
		{id: 11, name: "hascomments", type: "yn"},
		{id: 8, name: "hasscreenshots", type: "yn"}
	],
	achievements: [
		{id: 1, name: "sepgeneral"},
		{id: 2, name: "givesreward", type: "yn"},
		{id: 3, name: "rewardtext", type: "str"},
		{id: 4, name: "location", type: "zone"},
		{id: 9, name: "id", type: "num"},
		{id: 8, name: "sepseries"},
		{id: 5, name: "firstseries", type: "yn"},
		{id: 6, name: "lastseries", type: "yn"},
		{id: 7, name: "partseries", type: "yn"}
	],
	profiles: [
		{id: 1, name: "sepgeneral"},
		{id: 2, name: "gearscore", type: "num"},
		{id: 3, name: "achievementpoints", type: "num"},
		{id: 21, name: "wearingitem", type: "str-small"},
		{id: 23, name: "completedachievement", type: "str-small"},
		{id: 24, name: "sepprofession"},
		{id: 25, name: "alchemy", type: "str-small"},
		{id: 26, name: "blacksmithing", type: "str-small"},
		{id: 27, name: "enchanting", type: "str-small"},
		{id: 28, name: "engineering", type: "str-small"},
		{id: 29, name: "herbalism", type: "str-small"},
		{id: 30, name: "inscription", type: "str-small"},
		{id: 31, name: "jewelcrafting", type: "str-small"},
		{id: 32, name: "leatherworking", type: "str-small"},
		{id: 33, name: "mining", type: "str-small"},
		{id: 34, name: "skinning", type: "str-small"},
		{id: 35, name: "tailoring", type: "str-small"},
		{id: 4, name: "septalent"},
		{id: 5, name: "talenttree1", type: "num"},
		{id: 6, name: "talenttree2", type: "num"},
		{id: 7, name: "talenttree3", type: "num"},
		{id: 8, name: "sepguild"},
		{id: 36, name: "hasguild", type: "yn"},
		{id: 9, name: "guildname", type: "str"},
		{id: 10, name: "guildrank", type: "num"},
		{id: 11, name: "separenateam"},
		{id: 12, name: "teamname2v2", type: "str"},
		{id: 13, name: "teamrtng2v2", type: "num"},
		{id: 14, name: "teamcontrib2v2", type: "num"},
		{id: 15, name: "teamname3v3", type: "str"},
		{id: 16, name: "teamrtng3v3", type: "num"},
		{id: 17, name: "teamcontrib3v3", type: "num"},
		{id: 18, name: "teamname5v5", type: "str"},
		{id: 19, name: "teamrtng5v5", type: "num"},
		{id: 20, name: "teamcontrib5v5", type: "num"}
	]
};
function fi_toggle() {
	var c = ge("fi");
	var b = g_toggleDisplay(c),
	e;
	var d = ge("fi_toggle");
	if (b) {
        d.firstChild.nodeValue = LANG.fihide;
		c = (c.parentNode.tagName == "FORM" ? c.parentNode: gE(c, "form")[0]);
		c = c.elements.na ? c.elements.na: c.elements.ti;
		c.focus();
		c.select()
	}
    else {
        d.firstChild.nodeValue = LANG.fishow
	}
	d.className = "disclosure-" + (b ? "on": "off");
	return false
}
function fi_submit(d) {
	var c = 0;
	var a = d.elements;
	for (var b = 0; b < a.length; ++b) {switch (a[b].nodeName) {
        case "INPUT":
			switch (a[b].type) {
                case "text":
                    if (trim(a[b].value).length > 0) {
                        ++c
                    }
                    break;
                case "checkbox":
                    if ((a[b].value == "ja" || a[b].name == "gb") && a[b].checked) {
                        ++c
                    }
                    break
			}
			break;
		case "SELECT":
			if (a[b].name != "cr[]" && a[b].name != "gm" && a[b].selectedIndex != -1 && a[b].options[a[b].selectedIndex].value) {
                ++c
			}
			break
		}
	}
	if (c == 0) {
        alert(LANG.message_fillsomecriteria);
		return false
	}
	return true
}
function fi_initWeightedListview() {
	this._scoreMode = 0;
	if (this.sort[0] == -this.columns.length) {
        this.applySort()
	}
	if (this._minScore) {setTimeout(Listview.headerFilter.bind(this, this.columns[this.columns.length - 1], ">=" + this._minScore), 1);
		this._maxScore = this._minScore
	}
}
function fi_reset(c) {
	fi_resetCriterion(ge("fi_criteria"));
	fi_resetCriterion(ge("fi_weight"));
	var a = ge("sdkgnsdkn436");
	if (a) {
        a.parentNode.style.display = "none";
		while (a.firstChild) {
            de(a.firstChild)
		}
		ae(a, ce("option"))
	}
	a = c.elements;
	for (var b = 0; b < a.length; ++b) {
        switch (a[b].nodeName) {
            case "INPUT":
                if (a[b].type == "text") {
                    a[b].value = ""
                }
                else {
                    if (a[b].type == "checkbox") {
                        a[b].checked = false
                    }
                    else {
                        if (a[b].type == "radio" && a[b].value.length == 0) {
                            a[b].checked = true
                        }
                    }
                }
			break;
		case "SELECT":
			a[b].selectedIndex = a[b].multiple ? -1 : 0;
			if (a[b].i) {
                a[b].i = a[b].selectedIndex
			}
			break
		}
	}
	return false
}
function fi_resetCriterion(b) {
	if (b != null) {var a;
		while (b.childNodes.length > 1) {
            a = b.childNodes[1];
			while (a.childNodes.length > 1) {
                a.removeChild(a.childNodes[1])
			}
			b.removeChild(a)
		}
		a = b.childNodes[0];
		while (a.childNodes.length > 1) {
            a.removeChild(a.childNodes[1])
		}
		a.firstChild.i = null;
		a.firstChild.selectedIndex = 0;
		if (b.nextSibling.firstChild) {
            b.nextSibling.firstChild.style.display = b.style.display
		}
	}
}
function fi_addCriterion(l, h) {
	var e = ge(l.id.replace("add", ""));
	if (e.childNodes.length >= 19 || (l.id.indexOf("criteria") > 0 && e.childNodes.length >= 4)) {
        l.style.display = "none"
	}
	var b = e.childNodes[0].lastChild;
	if (b.nodeName != "A") {
        fi_appendRemoveLink(e.childNodes[0])
	} else {
        b.firstChild.nodeValue = LANG.firemove;
		b.onmouseup = fi_removeCriterion
	}
	var j = ce("div"),
	k = e.childNodes[0].childNodes[0].cloneNode(true);
	k.onchange = k.onkeyup = fi_criterionChange.bind(0, k);
	k.i = null;
	if (h != null) {
        var g = k.getElementsByTagName("option");
		for (var f = 0; f < g.length; ++f) {
            if (g[f].value == h) {
                g[f].selected = true;
				break
			}
		}
	} else {
        k.firstChild.selected = true
	}
	j.appendChild(k);
	fi_appendRemoveLink(j);
	e.appendChild(j);
	return k
}
function fi_removeCriterion() {
	var e, f = this.parentNode,
	h = f.parentNode,
	g = (f.firstChild.name == "wt[]");
	h.removeChild(f);
	if (h.childNodes.length == 1) {
        e = h.firstChild;
		if (e.firstChild.selectedIndex > 0) {
            var b = e.lastChild;
			b.firstChild.nodeValue = LANG.ficlear;
			b.onmouseup = fi_clearCriterion
		}
        else {
            e.removeChild(e.lastChild);
			e.removeChild(e.lastChild)
		}
	}
	if (h.nextSibling.firstChild) {
        h.nextSibling.firstChild.style.display = ""
	}
	if (g) {
        e = ge("sdkgnsdkn436");
		e.selectedIndex = 0;
		e.i = 0;
		fi_presetMatch()
	}
}
function fi_clearCriterion() {
	var a = this.parentNode;
	a.firstChild.selectedIndex = 0;
	fi_criterionChange(a.firstChild)
}
function fi_appendRemoveLink(c) {
	c.appendChild(ct(String.fromCharCode(160, 160)));
	var b = ce("a");
	b.href = "javascript:;";
	b.appendChild(ct(LANG.firemove));
	b.onmouseup = fi_removeCriterion;
	b.onmousedown = b.onclick = rf;
	c.appendChild(b)
}
function fi_appendClearLink(c) {
	c.appendChild(ct(String.fromCharCode(160, 160)));
	var b = ce("a");
	b.href = "javascript:;";
	b.appendChild(ct(LANG.ficlear));
	b.onmouseup = fi_clearCriterion;
	b.onmousedown = b.onclick = rf;
	c.appendChild(b)
}
function fi_Lookup(h, d) {
	var j;
	if (d == null) {
        d = fi_type
	}
	if (fi_Lookup.cache == null) {
        fi_Lookup.cache = {}
	}
	if (fi_Lookup.cache[d] == null) {j = {};
		for (var b = 0, a = fi_filters[d].length; b < a; ++b) {
            var g = fi_filters[d][b];
			j[g.id] = g;
			j[g.name] = g
		}
		fi_Lookup.cache[d] = j
	}
    else {
        j = fi_Lookup.cache[d]
	}
	if (h && typeof h == "string") {
        var e = h.charCodeAt(0);
		if (e >= "0".charCodeAt(0) && e <= "9".charCodeAt(0)) {
            h = parseInt(h)
		}
	}
	return j[h]
}
function fi_criterionChange(l, w, s) {
	var x;
	if (l.selectedIndex != l.i) {
        var e = l.options[l.selectedIndex],
		q = l.parentNode;
		if (q.childNodes.length > 1) {
            if (l.selectedIndex > 0 && l.i > 0) {
                var u = fi_Lookup(e.value);
				var r = fi_Lookup(l.options[l.i].value);
				if (u.type == r.type) {
                    return
				}
			}
			while (q.childNodes.length > 1) {
                q.removeChild(q.childNodes[1])
			}
		}
		if (l.selectedIndex > 0) {
            var c = fi_Lookup(e.value);
			var j = c.type.split("-");
			var t = j[0];
			var h = j[1] || "";
			if (LANG.fidropdowns[t] != null) {
                if (l.name == "cr[]") {
                    var m = LANG.fidropdowns[t];
					x = ce("select");
					x.name = "crs[]";
					var v = x;
					if (h.indexOf("any") != -1) {
                        var g = ce("option");
						g.value = "-2323";
						g.appendChild(ct(LANG.fiany));
						ae(v, g);
						if (w != null && w == "-2323") {
                            g.selected = true
						}
					}
					for (var k = 0; k < m.length; ++k) {
                        if (m[k][0]) {
                            var g = ce("option");
							g.value = m[k][0];
							g.appendChild(ct(m[k][1]));
							ae(v, g);
							if (w != null && w == m[k][0]) {
                                g.selected = true
							}
						}
                        else {
                            var v = ce("optgroup");
							v.label = m[k][1];
							ae(x, v)
						}
					}
					if (h.indexOf("none") != -1) {
                        var g = ce("option");
						g.value = "-2324";
						g.appendChild(ct(LANG.finone));
						ae(v, g);
						if (w != null && w == "-2324") {
                            g.selected = true
						}
					}
					q.appendChild(ct(" "));
					q.appendChild(x)
				}
				var f = (t == "num");
				if (f) {
                    q.appendChild(ct(" "))
				}
				x = ce("input");
				x.type = "text";
				if (s != null) {
                    x.value = s.toString()
				}
                else {
                    x.value = "0"
				}
				if (l.name == "cr[]") {
                    x.name = "crv[]"
				}
                else {
                    x.name = "wtv[]";
					x.onchange = fi_changeWeight.bind(0, x)
				}
				if (f) {
                    x.maxLength = 7;
					x.style.textAlign = "center";
					x.style.width = "4.5em"
				}
                else {
                    x.type = "hidden"
				}
				x.setAttribute("autocomplete", "off");
				q.appendChild(x);
				if (l.name == "wt[]") {
                    fi_sortWeight(x)
				}
			}
            else {
                if (t == "str") {
                    x = ce("input");
					x.name = "crs[]";
					x.type = "hidden";
					x.value = "0";
					q.appendChild(x);
					x = ce("input");
					x.type = "text";
					if (h.indexOf("small") != -1) {
                        x.maxLength = 7;
						x.style.textAlign = "center";
						x.style.width = "4.5em"
					}
                    else {
                        x.maxLength = 50;
						x.style.width = "9em"
					}
					x.name = "crv[]";
					if (s != null) {
                        x.value = s
					}
					q.appendChild(ct(" "));
					q.appendChild(x)
				}
			}
		}
		if (q.parentNode.childNodes.length == 1) {
            if (l.selectedIndex > 0) {
                fi_appendClearLink(q)
			}
		}
        else {
            if (q.parentNode.childNodes.length > 1) {
                fi_appendRemoveLink(q)
			}
		}
		l.i = l.selectedIndex
	}
}
function fi_setCriteria(g, d, j) {
	var e = ge("fi_criteria");
	var f, h = e.childNodes[0].childNodes[0];
	e = h.getElementsByTagName("option");
	for (f = 0; f < e.length; ++f) {
        if (e[f].value == g[0]) {
            e[f].selected = true;
			break
		}
	}
	fi_criterionChange(h, d[0], j[0]);
	var b = ge("fi_addcriteria");
	for (f = 1; f < g.length && f < 5; ++f) {
        fi_criterionChange(fi_addCriterion(b, g[f]), d[f], j[f])
	}
}
function fi_setWeights(q, m, d, e) {
	if (d) {var k = q[0],
		g = q[1],
		q = {};
		for (var h = 0; h < k.length; ++h) {
            var o = fi_Lookup(k[h]);
			if (o.type == "num" && LANG.traits[o.name]) {
                q[o.name] = g[h]
			}
		}
	}
	var s = ge("fi_weight");
	if (fi_weights == null) {fi_weights = {};
		cO(fi_weights, q)
	}
	var o = ge("fi_addweight"),
	l = s.childNodes[0].childNodes[0];
	var h = 0;
	for (var r in q) {
        if (!LANG.traits[r]) {
            continue
		}
		if (h++>0) {
            l = fi_addCriterion(o, r)
		}
		var b = l.getElementsByTagName("option");
		for (var f = 0; f < b.length; ++f) {
            if (b[f].value && r == fi_Lookup(b[f].value).name) {
                b[f].selected = true;
				break
			}
		}
		fi_criterionChange(l, 0, q[r])
	}
	fi_weightsFactor = fi_convertWeights(q, true);
	ge("fi_weight_toggle").className = "disclosure-on";
	ge("fi_weight").parentNode.style.display = "";
	if (!m) {
        if (!fi_presetMatch(q, e)) {
            fi_presetDetails()
		}
	}
}
function fi_changeWeight(b) {
	var a = ge("sdkgnsdkn436");
	a.selectedIndex = 0;
	a.i = 0;
	fi_sortWeight(b);
	fi_presetMatch()
}
function fi_sortWeight(e) {
	var d, c = ge("fi_weight"),
	b = Number(e.value);
	e = e.parentNode;
	n = 0;
	for (d = 0; d < c.childNodes.length; ++d) {var a = c.childNodes[d];
		if (a.childNodes.length == 5) {	n++;
			if (a.childNodes[2].nodeName == "INPUT" && b > Number(a.childNodes[2].value)) {
                c.insertBefore(e, a);
				return
			}
		}
	}
	c.insertBefore(e, c.childNodes[n])
}
function fi_convertWeights(c, g) {
	var e = 0,
	a = 0;
	for (var b in c) {
        if (!LANG.traits[b]) {
            continue
		}
		e += Math.abs(c[b]);
		if (Number(c[b]) > a) {
            a = Number(c[b])
		}
	}
	if (g) {
        return e
	}
	var d = {};
	for (var b in c) {
        d[b] = (LANG.traits[b] ? Math.round(1000 * c[b] / e) / 1000 : c[b])
	}
	return d
}
function fi_convertScore(b, a, c) {
	if (a == 1) {
        return parseInt(b * fi_weightsFactor)
	}
    else {
        if (a == 2) {
            return ((b / c) * 100).toFixed(1) + "%"
		}
        else {
            return b.toFixed(2)
		}
	}
}
function fi_updateScores() {
	if (++this._scoreMode > 2) {
        this._scoreMode = 0
	}
	for (var b = 0; b < this.data.length; ++b) {
        if (this.data[b].__tr) {
            var a = this.data[b].__tr.lastChild;
			a.firstChild.firstChild.nodeValue = fi_convertScore(this.data[b].score, this._scoreMode, this._maxScore)
		}
	}
}
function fi_presetClass(g, b) {
	if (g.selectedIndex != g.i) {
        var f, j, k = g.options[g.selectedIndex],
		h = g.parentNode,
		l = LANG.presets;
		fi_resetCriterion(r);
		var r = ge("sdkgnsdkn436");
		r.parentNode.style.display = "none";
		if (!b && (g.form.ub.selectedIndex == 0 || g.form.ub.selectedIndex == g.i)) {
            g.form.ub.selectedIndex = g.selectedIndex
		}
		while (r.firstChild) {
            de(r.firstChild)
		}
		ae(r, ce("option"));
		if (g.selectedIndex > 0) {
            for (j in k._presets) {
                var m = k._presets[j];
				var q = ce("optgroup");
				q.label = l[j];
				for (var a in m) {
                    var e = ce("option");
					e.value = a;
					e._weights = m[a];
					ae(e, ct(l[a]));
					ae(q, e)
				}
				if (q && q.childNodes.length > 0) {
                    ae(r, q)
				}
			}
			if (r.childNodes.length > 1) {
                r.parentNode.style.display = ""
			}
		}
		fi_presetChange(r);
		g.i = g.selectedIndex
	}
}
function fi_presetChange(b) {
	if (b.selectedIndex != b.i) {fi_resetCriterion(ge("fi_weight"));
		var a = b.options[b.selectedIndex];
		if (b.selectedIndex > 0) {
            if (b.form.elements.gm.selectedIndex == 0) {
                b.form.elements.gm.selectedIndex = 2
			}
			fi_resetCriterion(ge("fi_weight"));
			fi_setWeights(a._weights, 1, 0)
		}
		b.i = b.selectedIndex
	}
}
function fi_presetDetails() {
	var c = ge("fi_weight"),
	e = ge("fi_detail"),
	d = ge("fi_addweight");
	var b = g_toggleDisplay(c);
	d.style.display = "none";
	if (b) {
        e.firstChild.nodeValue = LANG.fihidedetails;
		if (c.childNodes.length < 19) {
            d.style.display = ""
		}
	}
    else {
        e.firstChild.nodeValue = LANG.fishowdetails
	}
	return false
}
function fi_presetMatch(m, a) {
	if (!m) {m = {};
		var r = ge("fi_weight");
		for (var f = 0; f < r.childNodes.length; ++f) {
            if (r.childNodes[f].childNodes.length == 5) {
                if (r.childNodes[f].childNodes[0].nodeName == "SELECT" && r.childNodes[f].childNodes[2].nodeName == "INPUT") {
                    var d = r.childNodes[f].childNodes[0].options[r.childNodes[f].childNodes[0].selectedIndex];
					if (d.value) {
                        m[fi_Lookup(d.value)] = Number(r.childNodes[f].childNodes[2].value)
					}
				}
			}
		}
	}
	var r = ge("fi_presets");
	var t = r.getElementsByTagName("select");
	if (t.length != 2) {
        return false
	}
	var b = fi_convertWeights(m);
	for (var l in wt_presets) {
        for (var k in wt_presets[l]) {
            for (var q in wt_presets[l][k]) {
                p = fi_convertWeights(wt_presets[l][k][q]);
				var h = true;
				for (var o in p) {
                    if (!LANG.traits[o]) {
                        continue
					}
					if (!b[o] || p[o] != b[o]) {
                        h = false;
						break
					}
				}
				if (h) {
                    for (var e = 0; e < t[0].options.length; ++e) {
                        if (l == t[0].options[e].value) {
                            t[0].options[e].selected = true;
							fi_presetClass(t[0], a);
							break
						}
					}
					for (e = 0; e < t[1].options.length; ++e) {
                        if (q == t[1].options[e].value) {
                            t[1].options[e].selected = true;
							fi_presetChange(t[1]);
							break
						}
					}
					return true
				}
			}
		}
	}
	return false
}
function fi_scoreSockets(r) {
	if (fi_gemScores != null) {
        var g = 0,
		d = 0,
		a = 0;
		var b = [],
		v = [];
		var k = [],
		h = [];
		var o = {},
		m = {};
		var l = false;
		for (var f = 1; f <= 3; ++f) {
            var c = r["socket" + f],
			u = (c == 1 ? 1 : 0);
			if (c && fi_gemScores[c]) {
                for (var e = 0; e < fi_gemScores[c].length; ++e) {
                    var q = fi_gemScores[c][e];
					if (q.uniqEquip == 0 || in_array(k, q.id) < 0) {
                        g += q.score;
						b.push(q.id);
						o[f - 1] = 1;
						if (q.uniqEquip == 1) {
                            k.push(q.id)
						}
						break
					}
				}
				for (var e = 0; e < fi_gemScores[u].length; ++e) {
                    var q = fi_gemScores[u][e];
					if (q.uniqEquip == 0 || in_array(h, q.id) < 0) {
                        d += q.score;
						v.push(q.id);
						m[f - 1] = (q.colors & c);
						if (q.uniqEquip == 1) {
                            h.push(q.id)
						}
						break
					}
				}
			}
            else {
                if (c) {
                    l = true
				}
			}
		}
		if (r.socketbonusstat && fi_weights && fi_weightsFactor != 0) {
            for (var f in fi_weights) {
                if (r.socketbonusstat[f]) {
                    a += r.socketbonusstat[f] * fi_weights[f] / fi_weightsFactor
				}
			}
		}
		r.scoreBest = d;
		r.scoreMatch = g + a;
		r.scoreSocket = a;
		if (r.scoreMatch >= r.scoreBest && !l) {
            r.matchSockets = o;
			r.gemGain = r.scoreMatch;
			r.gems = b
		}
        else {
            r.matchSockets = m;
			r.gemGain = r.scoreBest;
			r.gems = v
		}
		r.score += r.gemGain
	}
	if (r.score > this._maxScore || !this._maxScore) {
        this._maxScore = r.score
	}
	if (fi_upgradeId && r.id == fi_upgradeId) {
        this._minScore = r.score
	}
}
function fi_dropdownSync(a) {
	if (a.selectedIndex >= 0) {
        a.className = a.options[a.selectedIndex].className
	}
}
function fi_init(b) {
	fi_type = b;
	var a = ge("fi_subcat");
	if (g_initPath.lastIt && g_initPath.lastIt[3]) {
        if (a) {
            a.menu = g_initPath.lastIt[3];
			a.menuappend = "&filter";
			a.onmouseover = Menu.show;
			a.onmouseout = Menu.hide
		}
	}
	else if (a) {
        de(a.parentNode);
	}

	fi_initCriterion(ge("fi_criteria"), "cr[]", b);
	if (b == "items") {
        var d = ge("fi_presets");
		if (d) {
            fi_initPresets(ge("fi_presets"));
			fi_initCriterion(ge("fi_weight"), "wt[]", b)
		}
	}
	var c = ge("ma-0");
	if (c.getAttribute("checked")) {
        c.checked = true
	}
}
function fi_initCriterion(h, a, j) {
	var b = h.firstChild;
	var m = ce("select");
	m.name = a;
	m.onchange = m.onkeyup = fi_criterionChange.bind(0, m);
	ae(m, ce("option"));
	var l = null;
	var k = LANG["fi" + j];
	for (var f = 0, g = fi_filters[j].length; f < g; ++f) {
        var c = fi_filters[j][f];
		if (!c.type) {
            if (l && l.childNodes.length > 0) {
                ae(m, l)
			}
			l = ce("optgroup");
			l.label = (LANG.traits[c.name] ? LANG.traits[c.name] : k[c.name])
		}
        else {
            if (a != "wt[]" || c.type == "num") {
                var d = ce("option");
				d.value = c.id;
				var e = LANG.traits[c.name] ? LANG.traits[c.name][0] : k[c.name];
				if (c.indent) {
                    e = "- " + e
				}
				ae(d, ct(e));
				ae(l, d)
			}
		}
	}
	if (l && l.childNodes.length > 0) {
        ae(m, l)
	}
	ae(b, m)
}
function fi_initPresets(g) {
	var d, m = ce("select");
	m.onchange = m.onkeyup = fi_presetClass.bind(0, m, 0);
	ae(m, ce("option"));
	var l = [];
	for (var h in wt_presets) {
        l.push(h)
	}
	l.sort(function (i, c) {
        return strcmp(g_chr_classes[i], g_chr_classes[c])
	});
	for (var e = 0, f = l.length; e < f; ++e) {
        var h = l[e],
		b = ce("option");
		b.value = h;
		b._presets = wt_presets[h];
		ae(b, ct(g_chr_classes[h]));
		ae(m, b)
	}
	ae(g, m);
	var k = ce("span");
	k.style.display = "none";
	var m = ce("select");
	m.id = "sdkgnsdkn436";
	m.onchange = m.onkeyup = fi_presetChange.bind(0, m);
	ae(m, ce("option"));
	ae(k, ct(" "));
	ae(k, m);
	ae(g, k);
	ae(g, ct(String.fromCharCode(160, 160)));
	var j = ce("a");
	j.href = "javascript:;";
	j.id = "fi_detail";
	j.appendChild(ct(LANG.fishowdetails));
	j.onclick = fi_presetDetails;
	j.onmousedown = rf;
	ae(g, j)
}
function fi_getExtraCols(f, e, l) {
	if (!f.length) {
        return
	}
	var g = [],
	j = LANG.fiitems;
	var c = (fi_weightsFactor ? (e ? 8 : 9) : 10) - (l ? 1 : 0);
	for (var d = 0; d < f.length && d < c; ++d) {
        var k = fi_Lookup(f[d]);
		if (k && k.name && k.type == "num") {
            var h = {
                id: k.name,
				value: k.name,
				name: (LANG.traits[k.name] ? LANG.traits[k.name][2] : j[k.name]),
				tooltip: (LANG.traits[k.name] ? LANG.traits[k.name][0] : j[k.name]),
				before: (k.before ? k.before: "source")
			};
			if (k.name.indexOf("dps") != -1 || k.name.indexOf("speed") != -1) {
                h.compute = function (a, b) {
                    return (a[k.name] || 0).toFixed(2)
				}
			}
			g.push(h)
		}
	}
	if (fi_weightsFactor) {
        if (e) {
            g.push({
                id: "gems",
				name: LANG.gems,
				getValue: function (a) {
                    return a.gems.length
				},
				compute: function (q, r) {
                    if (!q.nsockets || !q.gems) {
                        return
					}
					var b = [];
					for (var m = 0; m < q.nsockets; m++) {
                        b.push(q["socket" + (m + 1)])
					}
					var a = "",
					m = 0;
					for (var o in q.socketbonusstat) {
                        if (LANG.traits[o]) {
                            if (m++>0) {
                                a += ", "
							}
							a += "+" + q.socketbonusstat[o] + " " + LANG.traits[o][2]
						}
					}
					Listview.funcBox.createSocketedIcons(b, r, q.gems, q.matchSockets, a)
				},
				sortFunc: function (m, i, o) {
                    return strcmp((m.gems ? m.gems.length: 0), (i.gems ? i.gems.length: 0))
				}
			})
		}
		g.push({
            id: "score",
			name: LANG.score,
			width: "7%",
			value: "score",
			compute: function (i, m) {
                var b = ce("a");
				b.href = "javascript:;";
				b.onclick = fi_updateScores.bind(this);
				b.className = (i.gemGain > 0 ? "q2": "q1");
				ae(b, ct(fi_convertScore(i.score, this._scoreMode, this._maxScore)));
				ae(m, b)
			}
		})
	}
	if (l) {
        g.push(Listview.extraCols.cost)
	}
	fi_nExtraCols = g.length;
	return g
}
function fi_GetReputationCols(factions) {
	var res = [];
	for (var i = 0, len = factions.length; i < len; ++i) {
        var name = factions[i][1];
		if (name.length > 15) {
            var words = factions[i][1].split(" ");
			for (var j = 0, len2 = words.length; j < len2; ++j) {
                if (words[j].length > 3) {
                    name = (words[j].length > 15 ? words[j].substring(0, 12) + "...": words[j]);
                    break
				}
			}
		}
		var col = {	id: "faction-" + factions[i][0],
			name: name,
			tooltip: factions[i][1],
			type: "num",
			before: "category"
		};
		eval("col.getValue = function(quest) { return Listview.funcBox.getQuestReputation(" + factions[i][0] + ", quest) }");
		eval("col.compute = function(quest, td) { return Listview.funcBox.getQuestReputation(" + factions[i][0] + ", quest) }");
		eval("col.sortFunc = function(a, b, col) { var _ = Listview.funcBox.getQuestReputation; return strcmp(_(" + factions[i][0] + ", a), _(" + factions[i][0] + ", b)) }");
		res.push(col)
	}
	return res
}
