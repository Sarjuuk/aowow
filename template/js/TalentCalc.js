var $WowheadTalentCalculator;

function TalentCalc() {
	var
        MODE_DEFAULT = 0,
        MODE_PET = 1,

        MIN_LEVEL = 10,
        MAX_LEVEL = 80,

        _versionBuild = 85,
        _self = this,
        _opt,
        _data = {},
        _glyphLookup = {},
        _build,
        _firstBuild,
        _firstGlyphs,
        _glyphs,
        _currentClass = -1,
        _oldClass = -1,
        _nClassChanges = 0,
        _currentGlyphSlot,
        _filteredGlyphs = false,
        _locked = false,
        _mode,
        _nTrees,
        _nTiers,
        _nGlyphs,
        _glyphSlots,
        _glyphOrder = [1,2],
        _glyphLevels = {
            1: [], // Major
            2: []  // Minor
        },
        _pointsPerTier,
        _basePoints,
        _bonusPoints = 0,
        _pointsFromBonus,
        _maxLevel = MAX_LEVEL,
        _pointsToSpend,
        _referenceArray,

        _container,
        _divSidebar,
            _controlsDiv,
            _lnkLock,
            _lnkSummary,
            _lnkExport,
            _lnkRestore,
            _glyphDiv,
            _glyphIcons = {},
            _glyphLinks = {},
        _divWrapper,
        _divUpper,
            _lblClass,
            _lblSpec,
            _lblReqLevel,
            _lnkMaxLevel,
            _lblPtsLeft,
            _lnkBonusPoints,
        _divLower,
            _lblTreePoints = [],
        _divMain,
        _divModel,
            swfModel,


        _encoding = '0zMcmVokRsaqbdrfwihuGINALpTjnyxtgevElBCDFHJKOPQSUWXYZ123456789',
        _blizzEncoding = 'aZbYcXdWeVfUgThSiRjQkPlOmNnMoLpKqJrIsHtGuFvEwDxCyBzA0123456789_=+-.',
        _newTree = 'Z',

        _onChangeCallback,
        _info = {};

// *************************************************************
// *************************************************************
//                         Public Methods
// *************************************************************
// *************************************************************

    this.getTalentTrees = function() {
        return _data[_currentClass];
    };

	this.addGlyph = function (itemId) {
		if (itemId) {
			_addGlyph(_currentGlyphSlot, itemId);
		}
        else {
			_removeGlyph(_currentGlyphSlot);
		}

		Lightbox.hide()
	};

	this.getBlizzBuild = function () {
		if (_currentClass == -1) {
			return;
		}

		var
            c     = _data[_currentClass],
            blizz = '';

		for (var tree = 0; tree < _nTrees; ++tree) {
			for (var i = 0; i < c[tree].t.length; ++i) {
				blizz += c[tree].t[i].k;
			}
		}

		blizz = rtrim(blizz, '0');

		return blizz;
	};

	this.getBlizzGlyphs = function () {
		if (_currentClass == -1) {
			return;
		}

		var
            c     = _data[_currentClass],
            blizz = '';

		for (var slot = 0; slot < _nGlyphs; ++slot) {
			if (slot > 0) {
				blizz += ':';
			}

			if (c.glyphs[slot]) {
				blizz += c.glyphs[slot];
			}
            else {
				blizz += '0';
			}
		}

		return blizz;
	};

	this.getGlyphs = function () {
		var res = [];

		if (_currentClass != -1) {
			var c = _data[_currentClass];

			if (c) {
				for (var slot = 0; slot < _nGlyphs; ++slot) {
					if (c.glyphs[slot]) {
						res.push(g_glyphs[c.glyphs[slot]]);
					}
				}
			}
		}

		return res;
	};

	this.getSpentFromBlizzBuild = function (build, classId) {
		var
            c     = _data[classId],
            spent = [];

    	for (var i = 0; i < _nTrees; ++i) {
            spent.push(0);
    	}

		if (c) {
			var
                tree = 0,
                talent = 0;

            // Fix for extra talent digit in blizzard mage builds
            if (classId == 8 && build.length == 62) {
                build = build.substr(0, 15) + build.substr(16);
            }

            for (var i = 0; i < build.length; ++i) {
				var n = Math.min(parseInt(build.charAt(i)), c[tree].t[talent].m);
				if (isNaN(n)) {
					continue;
				}

				for (var k = 0; k < n; ++k) {
                    ++spent[tree];
				}

				if (++talent > c[tree].t.length - 1) {
					talent = 0;
					if (++tree > _nTrees - 1) {
						break;
					}
				}
			}
		}

		return spent;
	};

	this.getTalents = function () {
		var res = [];

		if (_currentClass != -1) {
			var c = _data[_currentClass];
			if (c) {
				for (var tree = 0; tree < _nTrees; ++tree) {
					for (i = 0; i < c[tree].t.length; ++i) {
						if (c[tree].t[i].k) {
							res.push(c[tree].t[i]);
						}
					}
				}
			}
		}

		return res;
	};

    this.getTalentRanks = function(talentId) {
        if (_currentClass == -1) {
            return;
        }

        if (_talentLookup[talentId]) {
            return _talentLookup[talentId].k;
        }

        return 0;
    };

	this.getWhBuild = function () {
		if (_currentClass == -1) {
			return;
		}

		var
            c     = _data[_currentClass],
            wh    = '',
            blizz,
            i;

		for (var tree = 0; tree < _nTrees; ++tree) {
			blizz = '';
			for (i = 0; i < c[tree].t.length; ++i) {
				blizz += c[tree].t[i].k;
			}

			blizz = rtrim(blizz, '0');

			wh += _convertBlizzToWh(blizz);

			i = blizz.length;
			if (i % 2 == 1) {
                ++i;
			}

			if (i < c[tree].t.length) {
				wh += _newTree;
			}
		}

		var res;

		if (_mode == MODE_PET) {
			res = _encoding.charAt(Math.floor(_currentClass / 10)) + _encoding.charAt((2 * (_currentClass % 10)) + (_bonusPoints ? 1 : 0));
		}
        else {
			res = _encoding.charAt(_getOldIdFromClassId(_currentClass) * 3);
		}

		res += rtrim(wh, _newTree);

		return res;
	};

	this.getWhGlyphs = function () {
		if (_currentClass == -1) {
			return;
		}

		var
            c      = _data[_currentClass],
            glyphs = {};

		for (var slot = 0; slot < _nGlyphs; ++slot) {
            var glyphType = _getGlyphTypeFromSlot(slot);
            if (glyphs[glyphType] == null) {
                glyphs[glyphType] = '';
            }

			if (c.glyphs[slot]) {
				glyphs[glyphType] += _encoding.charAt(g_glyphs[c.glyphs[slot]].index);
			}
		}

        var res = '', encodeLen = 0;
        for (var i = 0, len = _glyphOrder.length; i < len; ++i) {
            var glyphType = _glyphOrder[i];
            encodeLen += _glyphLevels[glyphType].length;

            if (glyphs[glyphType]) {
                res += glyphs[glyphType];
            }

            if (res.length < encodeLen) {
                res += _newTree;
            }
        }

        res = rtrim(res, _newTree);

        return res;
	};

	this.initialize = function (container, opt) {
		if (_container) {
			return;
		}

		container = $(container);
		if (!container) {
			return;
		}

		_container = container;
		_container.className = "talentcalc";
		if (opt == null) {
			opt = {};
		}

		_opt = opt;

		if (_opt.onChange) {
			_onChangeCallback = _opt.onChange;
		}

		if (_opt.mode == MODE_PET) {
			_mode            = MODE_PET;
			_nTrees          = 1;
			_nTiers          = 6;
			_pointsPerTier   = 3;
			_basePoints      = 16;
            _pointsFromBonus = 4
			_referenceArray  = g_pet_families;

			_container.className += " talentcalc-pet";

            _setGlyphSlots(0);
		}
        else {
			_mode            = MODE_DEFAULT;
			_nTrees          = 3;
			_nTiers          = 11;
			_glyphLevels     = {
				1 : [15, 30, 80],  // Major
				2 : [15, 50, 70]   // Minor
			};
			_pointsPerTier   = 5;
			_basePoints      = 71;
            _pointsFromBonus = 0;
			_referenceArray  = g_chr_classes;

			_container.className += " talentcalc-default";

			$WowheadTalentCalculator = _self;

            _setGlyphSlots(MAX_LEVEL);
			_initGlyphData();
		}

		_pointsToSpend = _basePoints + _bonusPoints;
        _divWrapper = ce('div');
        _divWrapper.className = 'talentcalc-wrapper';
        ae(_container, _divWrapper);

		_createSidebar();
		_createUpper();
		_createMain();
		_createModel();
		_createLower();

    /*
    	_createSidebar();
        _createUpper();
        _createTreeNames();
        _createMasteries();
        _createModel();
        _createMain();
        _createLower();
    */

		if (_opt.whBuild) {
			_setWhBuild(_opt.whBuild);
		}
        else if (_opt.classId > 0 && _referenceArray[_opt.classId]) {
            if (_opt.blizzBuild) {
                _setBlizzBuild(_opt.classId, _opt.blizzBuild);
            }
            else {
                _setClass(_opt.classId);
            }
		}

		if (_opt.whGlyphs) {
			_setWhGlyphs(_opt.whGlyphs);
		}
        else if (_opt.blizzGlyphs) {
            _setBlizzGlyphs(_opt.blizzGlyphs);
		}
	};

	this.promptBlizzBuild = function () {
		if (_mode == MODE_PET) {
			return;
		}

		var
            classId,
            url = prompt(LANG.prompt_importblizz, "");

		if (!url) {
			return;
		}

        if (url.match(/talent-calculator#([\w=+-.])c[0-9]{2}(![\w=+-.]*![\w=+-.]*![\w=+-.]*!?[\w=+-.]*)/)) { // Bnet Url
            classId = _blizzEncoding.indexOf(RegExp.$1);
			_setBlizzBuild(classId, RegExp.$2, true);

			return;
		}
        else if (url.match(/\?cid=([0-9]+)&tal=([0-9]+)/)) { // old style
			classId = parseInt(RegExp.$1);
			_setBlizzBuild(classId, RegExp.$2);

            return;
        }
        else {
			var idx = url.indexOf("?tal=");
			if (idx != -1) {
				for (var i in g_file_classes) {
					if (url.indexOf(g_file_classes[i]) != -1) {
						classId = parseInt(i);

						break;
					}
				}
				if (classId) {
					_setBlizzBuild(classId, url.substring(idx + 5));

					return;
				}
			}
		}

		alert(LANG.alert_invalidurl);
	};

	this.promptWhBuild = function () {
		var url = prompt(LANG.prompt_importwh, '');

		if (!url) {
			return;
		}

		var pos = url.indexOf('=');
        if(pos != -1) {
            var hash = url.substr(pos);

            var
                parts  = hash.substr(1).split(':'),
                build  = parts[0] || '',
                glyphs = parts[1] || '';

            _setWhBuild(build);
            _setWhGlyphs(glyphs);
        }
	};

	this.registerClass = function (classId, data) {
		_registerClass(classId, data)
	};

	this.reset = function (tree) {
		if (_currentClass == -1) {
			return;
		}

		if (tree > _nTrees - 1) {
			return;
		}

		_locked = false;
		_resetTree(tree, _currentClass, true)
	};

	this.resetAll = function () {
		if (!_data[_currentClass]) {
			return;
		}

		_locked = false;
		_resetAll(_currentClass);
		_onChange();
	};

	this.resetBuild = function () {
		if (!_data[_currentClass]) {
			return;
		}

		_locked = false;

		_resetBuild(_currentClass);
		_refreshAll(_currentClass);
		_onChange();
	};

	this.resetGlyphs = function () {
		_resetGlyphs();
		_onChange();
	};

	this.restore = function () {
		_restore();
	};

	this.setBlizzBuild = function (classId, build) {
		_setBlizzBuild(classId, build);
	};

	this.setBlizzGlyphs = function (glyphs) {
		if (_currentClass == -1) {
			return;
		}

		_setBlizzGlyphs(glyphs);
	};

	this.setBonusPoints = function () {
		if (_mode != MODE_PET) {
			return;
		}

		_setPoints(-1, _pointsFromBonus);
	};

	this.setClass = function (classId) {
		return _setClass(classId);
	};

	this.setLevelCap = function (lvl) {
    	_setLevelCap(lvl);
    };

	this.setLock = function (locked) {
		if (_currentClass == -1) {
			return;
		}

		_setLock(locked);
	};

	this.setWhBuild = function (build) {
		return _setWhBuild(build);
	};

	this.setWhGlyphs = function (glyphs) {
		if (_currentClass == -1) {
			return;
		}

		_setWhGlyphs(glyphs);
	};

    this.setPetModel = function(npcId) {
        _updateModel(_currentClass, npcId); // NYI!
    }

	this.showSummary = function (mode) {
		if (_currentClass == -1) {
			return;
		}

		var
            c = _data[_currentClass],
            bg = window.open('?talent=summary', '', 'toolbar=no,menubar=yes,status=yes,scrollbars=yes,resizable=yes'),
            i,
            j,
            k,
            buffer = '<html><head><title>' + document.title + '</title></head><body style="font-family: Arial, sans-serif; font-size: 13px">';

		bg.document.open();

		if (mode) { // Printable version
			buffer += '<h2>';

			if (_mode == MODE_PET) {
				buffer += sprintf(LANG.tc_printh, _getRequiredLevel(), g_pet_families[c.n]);
			}
            else {
				buffer += sprintf(LANG.tc_printh, _getRequiredLevel(), g_chr_classes[c.n]) + ' (' + c[0].k + '/' + c[1].k + '/' + c[2].k + ')';
			}

			buffer += '</h2>';
			buffer += '<p></p>';

			for (i = 0; i < _nTrees; ++i) {
				buffer += '<h3>' + c[i].n + ' (' + c[i].k + ' ' + LANG[c[i].k == 1 ? 'tc_point': 'tc_points'] + ')</h3>';
				buffer += '<blockquote>';
				k = 0;

				for (j = 0; j < c[i].t.length; ++j) {
					if (c[i].t[j].k) {
						if (k) {
							buffer += '<br /><br />';
						}

						buffer += '<b>' + c[i].t[j].n + '</b>' + LANG.hyphen + sprintf(LANG.tc_rank, c[i].t[j].k, c[i].t[j].m) + '<br />';
						buffer += _getTalentDescription(c[i].t[j]);
                        ++k;
					}
				}

				if (k == 0) {
					buffer += LANG.tc_none;
				}

				buffer += '</blockquote>'
			}

			buffer += '<h3>' + LANG.tc_glyphs + '</h3>';
			buffer += '<blockquote>';
			glyphCount = 0;

			for (i = 0; i < _nGlyphs; ++i) {
				glyph = g_glyphs[c.glyphs[i]];
				if (glyph) {
					if (glyphCount) {
						buffer += '<br /><br />';
					}

					buffer += '<b>' + glyph.name + '</b> ';
					if (glyph.type == 1) {
						buffer += '(' + LANG.tc_majgly + ')<br />';
					}
                    else {
						buffer += '(' + LANG.tc_mingly + ')<br />';
					}

					buffer += glyph.description;

					glyphCount++
				}
			}

			if (glyphCount == 0) {
				buffer += LANG.tc_none;
			}

			buffer += '</blockquote>';
		}
        else {  // Summary
			buffer += '<pre>';
			for (i = 0; i < _nTrees; ++i) {
				buffer += '<b>' + c[i].n + ' (' + c[i].k + ' ' + LANG[c[i].k == 1 ? 'tc_point': 'tc_points'] + ')</b>\n\n';
				k = 0;
				for (j = 0; j < c[i].t.length; ++j) {
					if (c[i].t[j].k) {
						buffer += '&nbsp;&nbsp;&nbsp;&nbsp;' + c[i].t[j].k + '/' + c[i].t[j].m + ' ' + c[i].t[j].n + '\n';
                        ++k;
					}
				}
				if (k == 0) {
					buffer += '&nbsp;&nbsp;&nbsp;&nbsp;' + LANG.tc_none + '\n';
				}
				buffer += '\n';
			}
			buffer += '</pre>';
		}

		buffer += '</body></html>';

		bg.document.write(buffer);
		bg.document.close()
	};

	this.simplifyGlyphName = function (name) {
		return _simplifyGlyphName(name);
	};

	this.toggleLock = function () {
		if (_currentClass == -1) {
			return;
		}

		_toggleLock();
	};

// *************************************************************
// *************************************************************
//                         Private Methods
// *************************************************************
// *************************************************************

	function _addGlyph(slot, itemId, stealth) {
		var
            cls   = _data[_currentClass];
            glyph = g_glyphs[itemId];

		if (glyph && _isValidGlyph(slot, glyph)) {
			if (cls.glyphs[slot]) {
				cls.glyphItems[cls.glyphs[slot]] = 0;
			}

			cls.glyphs[slot] = itemId;
			cls.glyphItems[itemId] = 1;

			if (!stealth) {
				_updateGlyph(slot);
				_onChange();
			}
		}
	}

	function _afterCapDecrease() {
		var c = _data[_currentClass];

		if (c.k > _pointsToSpend) {
			for (var tree = _nTrees - 1; tree >= 0; --tree) {
				for (var i = c[tree].t.length - 1; i >= 0; --i) {
					var k = c[tree].t[i].k;

					for (var j = 0; j < k; ++j) {
						_decrementTalent(c[tree].t[i]);

						if (c.k <= _pointsToSpend) {
							return;
						}
					}
				}
			}
		}
	}

	function _bonusPointsOnClick(_this, e) {
        _setPoints(-1, (_bonusPoints ? 0 : _pointsFromBonus));
		_bonusPointsOnMouseOver(_this, e);
	}

	function _bonusPointsOnMouseOver(_this, e) {
        if(_mode == MODE_PET) {
            Tooltip.showAtCursor(e, LANG[_bonusPoints ? "tc_rembon": "tc_addbon"], null, null, "q");
        }
	}

    function _setLevelCapOnClick(_this, e) {
        _setLevelCap(prompt($WH.sprintf(LANG.prompt_ratinglevel, MIN_LEVEL, MAX_LEVEL), _maxLevel));
        _setLevelCapOnMouseOver(_this, e);
        _onChange();
    }

    function _setLevelCapOnMouseOver(_this, e) {
        Tooltip.showAtCursor(e, LANG.tooltip_changelevel, null, null, 'q');
    }

	function _createArrow(bc, bf, bg) {
		var bh = ce("div"),
		be,
		bd;
		bh.className = "talentcalc-arrow";
		switch (bc) {
		case 0:
			bf = 15;
			be = _createTable(1, 2);
			be.className = "talentcalc-arrow-down";
			bd = be.firstChild.childNodes[0].childNodes[0].style;
			bd.width = "15px";
			bd.height = "4px";
			bd = be.firstChild.childNodes[1].childNodes[0].style;
			bd.backgroundPosition = "bottom";
			bd.height = (bg - 4) + "px";
			break;
		case 1:
			be = _createTable(2, 2, true);
			be.className = "talentcalc-arrow-leftdown";
			bd = be.firstChild.childNodes[0].childNodes[0].style;
			bd.backgroundPosition = "left";
			bd.width = (bf - 4) + "px";
			bd.height = "11px";
			bd = be.firstChild.childNodes[0].childNodes[1].style;
			bd.backgroundPosition = "right";
			bd.width = "4px";
			bd = be.firstChild.childNodes[1].childNodes[0].style;
			bd.backgroundPosition = "bottom left";
			bd.backgroundRepeat = "no-repeat";
			bd.height = (bg - 11) + "px";
			break;
		case 2:
			be = _createTable(2, 2, true);
			be.className = "talentcalc-arrow-rightdown";
			bd = be.firstChild.childNodes[0].childNodes[0].style;
			bd.backgroundPosition = "left";
			bd.width = "4px";
			bd = be.firstChild.childNodes[0].childNodes[1].style;
			bd.backgroundPosition = "right";
			bd.width = (bf - 4) + "px";
			bd.height = "11px";
			bd = be.firstChild.childNodes[1].childNodes[0].style;
			bd.backgroundPosition = "bottom right";
			bd.backgroundRepeat = "no-repeat";
			bd.height = (bg - 11) + "px";
			break;
		case 3:
			bg = 15;
			be = _createTable(2, 1);
			be.className = "talentcalc-arrow-right";
			bd = be.firstChild.childNodes[0].childNodes[0].style;
			bd.backgroundPosition = "left";
			bd.width = "4px";
			bd = be.firstChild.childNodes[0].childNodes[1].style;
			bd.backgroundPosition = "right";
			bd.width = (bf - 4) + "px";
			break;
		case 4:
			bg = 15;
			be = _createTable(2, 1);
			be.className = "talentcalc-arrow-left";
			bd = be.firstChild.childNodes[0].childNodes[0].style;
			bd.backgroundPosition = "left";
			bd.width = (bf - 4) + "px";
			bd = be.firstChild.childNodes[0].childNodes[1].style;
			bd.backgroundPosition = "right";
			bd.width = "4px";
			break
		}
		bh.style.width = bf + "px";
		bh.style.height = bg + "px";
		ae(bh, be);
		return bh
	}

	function _createLower() {
		var
            be,
            bf,
            bd;

		_divLower = ce("div");
		_divLower.className = "talentcalc-lower";
		_divLower.style.display = "none";

		for (var bc = 0; bc < _nTrees; ++bc) {
			be = _lblTreePoints[bc] = ce("div");
			be.className = "talentcalc-lower-tree" + (bc + 1);
			bf = ce("p");
			bf.className = "rcorners";
			ae(bf, ce("b"));
			ae(bf, ce("span"));
			bd = ce("a");
			bd.href = "javascript:;";
			bd.onclick = _self.reset.bind(null, bc);
            g_addTooltip(bd, LANG.tc_resettree);
			ae(bf, bd);
			ae(be, bf);
			ae(_divLower, be)
		}
		// ae(_container, _divLower)
		ae(_divWrapper, _divLower)
	}

	function _createMain() {
		_divMain = ce("div");
		_divMain.className = "talentcalc-main";

		var clear = ce("div");
		clear.className = "clear";
		ae(_divMain, clear);

		// ae(_container, _divMain);
		ae(_divWrapper, _divMain);
	}

    function _createModel() {
        if (_mode != MODE_PET) {
            return;
        }

        _divModel = ce('div');
        _divModel.className = 'talentcalc-model';

        _divModel.style.display = "none";

        _swfModel = ce('div');
        _swfModel.id = 'shg09yrhlnk';
        ae(_divModel, _swfModel);

        var clear = ce('div');
        clear.className = 'clear';
        ae(_divModel, clear);

        // ae(_container, _divModel);
        ae(_divWrapper, _divModel);
    }

	function _createPetTalents(classId) {
		var
            data = [{}],
            talent,
            _;

		for (var i = 0, len = g_pet_talents.length; i < len; ++i) {
			var talents = g_pet_talents[i];

			if (in_array(talents.f, classId) >= 0) {
				data[0].n    = talents.n;
				data[0].t    = [];
				data[0].i    = i;
                data[0].icon = talents.icon;

				for (var j = 0, bf = talents.t.length; j < bf; ++j) {
					talent = talents.t[j];

					_ = data[0].t[j] = {};
					cO(_, talent);

                    if (talent.f && in_array(talent.f, classId) == -1)
                        _.hidden = true;
				}
				break;
			}
		}
		return data;
	}

	function _createSidebar() {
		var
            sidebarDivInner,
            _,
            __,
            ___;

		_divSidebar = ce("div");
		_divSidebar.className = "talentcalc-sidebar rcorners";
		sidebarDivInner = ce("div");
		sidebarDivInner.className = "talentcalc-sidebar-inner";
		_ = ce("a");
		_.className = "talentcalc-button-help";
		_.href = (_mode == MODE_PET ? "http://petopia.brashendeavors.net/html/patch30/patch30faq_talents.php": "?help=talent-calculator");
		_.target = "_blank";
		ae(_, ct(LANG.tc_help));
		ae(sidebarDivInner, _);
		_controlsDiv = ce("div");
		_controlsDiv.className = "talentcalc-sidebar-controls";
		_controlsDiv.style.display = "none";
		_ = ce("a");
		_.className = "talentcalc-button-reset";
		_.href = "javascript:;";
		_.onclick = _self.resetAll;
		ae(_, ct(LANG.tc_resetall));
		ae(_controlsDiv, _);
		_ = _lnkLock = ce("a");
		_.className = "talentcalc-button-lock";
		_.href = "javascript:;";
		_.onclick = _toggleLock;
		ae(_, ct(LANG.tc_lock));
		ae(_controlsDiv, _);
		_ = ce("div");
		_.className = "clear";
		ae(_controlsDiv, _);
		ae(sidebarDivInner, _controlsDiv);
		_ = ce("div");
		_.className = "talentcalc-sidebar-controls2";
		__ = ce("a");
		__.className = "talentcalc-button-import";
		__.href = "javascript:;";
		__.onclick = _self.promptBlizzBuild;
		ae(__, ct(LANG.tc_import));
		ae(_, __);

		__ = _lnkSummary = ce("a");
		__.className = "talentcalc-button-summary";
		__.style.display = "none";
		__.href = "javascript:;";
		__.onclick = _self.showSummary.bind(null, 1);
		ae(__, ct(LANG.tc_summary));
		ae(_, __);

		__ = _lnkRestore = ce("a");
		__.className = "talentcalc-button-restore";
		__.style.display = "none";
		__.href = "javascript:;";
		__.onclick = _restore;
		ae(__, ct(LANG.tc_restore));
		ae(_, __);

        __ = _lnkExport = ce("a");
        __.className = "talentcalc-button-export";
        __.style.display = "none";
        __.href = "javascript:;";
        __.target = "_blank";
        ae(__, ct(LANG.tc_link));
        ae(_, __)

        __ = ce("div");
		__.className = "clear";
		ae(_, __);
		ae(sidebarDivInner, _);
		_ = ce("div");
		ae(sidebarDivInner, _);
		if (_mode == MODE_DEFAULT) {
			_glyphDiv = ce("div");
			_glyphDiv.style.display = "none";
			_ = ce("h3");
			ae(_, ct(LANG.tc_glyphs));
			ae(_glyphDiv, _);
			__ = ce("a");
			__.href = "javascript:;";
			__.onclick = _self.resetGlyphs;
       		g_addTooltip(__, LANG.tc_resetglyphs);
			ae(__, ct("[x]"));
			ae(_, __);
			_ = ce("div");
			_.className = "talentcalc-sidebar-majorglyphs q9";
			__ = ce("b");
			ae(__, ct(g_item_glyphs[1]));
			ae(_, __);
			ae(_glyphDiv, _);
			_ = ce("div");
			_.className = "talentcalc-sidebar-minorglyphs q9";
			__ = ce("b");
			ae(__, ct(g_item_glyphs[2]));
			ae(_, __);
			ae(_glyphDiv, _);
			_ = ce("div");
			_.className = "clear";
			ae(_glyphDiv, _);
			var bq = ce("table"),
			bf = ce("tbody"),
			bh,
			bd,
			be,
			bk,
			bj,
			bm;
			bq.className = "icontab";
			for (var bi = 0; bi < 3; ++bi) {
				bh = ce("tr");
				for (var bl = 0; bl < 2; ++bl) {
					var bn = (bl * 3) + bi;
					bd = ce("th");
					bk = Icon.create("inventoryslot_empty", 1, null, "javascript:;");
					bj = Icon.getLink(bk);
					_glyphIcons[bn] = bk;
					ae(bd, bk);
					ae(bh, bd);
					be = ce("td");
					bm = ce("a");
					_glyphLinks[bn] = bm;
					ae(be, bm);
					ae(bh, be);
					bm.target = bj.target = "_blank";
					bm.rel = bj.rel = "np";
					bm.onmousedown = bj.onmousedown = rf;
					bm.onclick = bj.onclick = rf;
					g_onClick(bm, _glyphClick.bind(bm, bn));
					bm.onmouseover = _showGlyphTooltip.bind(null, bm, bn);
					bm.onmousemove = Tooltip.cursorUpdate;
					bm.onmouseout = Tooltip.hide;
					g_onClick(bj, _glyphClick.bind(bj, bn));
					bj.onmouseover = _showGlyphTooltip.bind(null, bj, bn);
					bj.onmouseout = Tooltip.hide;
					be.oncontextmenu = rf
				}
				ae(bf, bh)
			}
			ae(bq, bf);
			ae(_glyphDiv, bq);
			ae(sidebarDivInner, _glyphDiv)
		}
		ae(_divSidebar, sidebarDivInner);
        _ = ce("div");
		_.className = "talentcalc-sidebar-anchor";
		ae(_, _divSidebar);
		ae(_container, _);
	}

	function _createTable(be, bi, bc) {
		var bk = ce("table"),
		bd = ce("tbody"),
		bf,
		bj;
		for (var bg = 0; bg < bi; ++bg) {
			bf = ce("tr");
			for (var bh = 0; bh < be; ++bh) {
				if (bc && bg > 0) {
					bj = ce("th");
					bj.colSpan = 2;
					ae(bf, bj);
					break
				} else {
					ae(bf, ce("td"))
				}
			}
			ae(bd, bf)
		}
		ae(bk, bd);
		return bk
	}

	function _createTrees(bo) {
		var bw = _data[bo],
		bA;
		bw.k = 0;
		bw.div = ce("div");
		bw.div.style.display = "none";
		aef(_divMain, bw.div);
		for (var bm = 0; bm < _nTrees; ++bm) {
			bw[bm].k = 0;
			var bv = ce("div");
			d2 = ce("div");
			bv.style.backgroundRepeat = "no-repeat";
			bv.style.cssFloat = bv.style.styleFloat = "left";
			if (bm > 0) {
				bv.style.borderLeft = "1px solid #404040"
			}
			d2.style.overflow = "hidden";
			d2.style.width = (_mode == MODE_DEFAULT ? "204px": "244px");
			ae(d2, _createTable(4, _nTiers));
			ae(bv, d2);
			ae(bw.div, bv);
			var br = gE(bv, "td"),
			by,
			bx = "";
			if (!Browser.ie6) {
				bx = "?" + _versionBuild
			}
			if (_mode == MODE_PET) {
				bv.style.backgroundImage = "url(images/talent/pets/bg_" + (bw[0].i + 1) + ".jpg" + bx + ")";
				by = "images/talent/pets/icons_" + (bw[0].i + 1) + ".jpg" + bx
			} else {
				bv.style.backgroundImage = "url(images/talent/classes/backgrounds/" + g_file_classes[bo] + "_" + (bm + 1) + ".jpg" + bx + ")";
				by = "images/talent/classes/icons/" + g_file_classes[bo] + "_" + (bm + 1) + ".jpg" + bx;
			}
			for (var bq = bw[bm].t.length - 1; bq >= 0; --bq) {
				var bj = bw[bm].t[bq],
				bu = Icon.create(by, 1, null, "javascript:;"),
				bf = Icon.getLink(bu),
				bn = br[(bj.y * 4 + bj.x + 1) - 1];
				if (Browser.ie6) {
					bf.onfocus = tb
				}
				bf.rel = "np";
				bf.target = "_blank";
				bf.onmousedown = rf;
				bf.onclick = rf;
				g_onClick(bf, _iconClick.bind(bf, bj));
				bf.onmouseover = _showTooltip.bind(null, bf, bj);
				bf.onmouseout = Tooltip.hide;
				var bt = ce("div"),
				bz = ce("div");
				ae(bz, ct("0"));
				bt.className = "icon-border";
				bz.className = "icon-bubble";
				ae(bu, bt);
				ae(bu, bz);
				bj.k = 0;
				bj.i = bq;
				bj.tree = bm;
				bj.classId = bo;
				bj.icon = bu;
				bj.link = bf;
				bj.border = bt;
				bj.bubble = bz;
				if (!bj.hidden) {
					ae(bn, bu)
				}
				if (bj.r) {
					var bh = bw[bm].t[bj.r[0]],
					be = bj.x - bh.x,
					bd = bj.y - bh.y,
					bp,
					bk,
					bi,
					bs,
					bg = -1;
					if (bh.links == null) {
						bh.links = [bq]
					} else {
						bh.links.push(bq)
					}
					if (bd > 0) {
						if (be == 0) {
							bg = 0
						} else {
							if (be < 0) {
								bg = 1
							} else {
								bg = 2
							}
						}
					} else {
						if (bd == 0) {
							if (be > 0) {
								bg = 3
							} else {
								if (be < 0) {
									bg = 4
								}
							}
						}
					}
					if (_mode == MODE_PET) {
						bi = (Math.abs(be) - 1) * 60;
						bs = (Math.abs(bd) - 1) * 60
					} else {
						bi = (Math.abs(be) - 1) * 50;
						bs = (Math.abs(bd) - 1) * 50
					}
					if (_mode == MODE_PET) {
						switch (bg) {
						case 0:
							bs += 27;
							bp = 21;
							bk = 6 - bs;
							break
						}
					} else {
						switch (bg) {
						case 0:
							bs += 17;
							bp = 16;
							bk = 6 - bs;
							break;
						case 1:
							bi += 36;
							bs += 42;
							bp = 16;
							bk = 6 - bs;
							break;
						case 2:
							bi += 37;
							bs += 42;
							bp = -6;
							bk = 6 - bs;
							break;
						case 3:
							bi += 15;
							bp = -6;
							bk = 12;
							break;
						case 4:
							bi += 15;
							bp = 37;
							bk = 12;
							break
						}
					}
					var bc = _createArrow(bg, bi, bs);
					bc.style.left = bp + "px";
					bc.style.top = bk + "px";
					var bl = ce("div");
					bl.className = "talentcalc-arrow-anchor";
					ae(bl, bc);
					if (!bj.hidden) {
						bn.insertBefore(bl, bn.firstChild)
					}
					bj.arrow = bc
				}
			}
		}
	}

	function _createUpper() {
		var bf, bd, be;
		_divUpper = ce("div");
		_divUpper.className = "talentcalc-upper rcorners";
		_divUpper.style.display = "none";
		bf = ce("div");
		bf.className = "talentcalc-upper-inner";
		bd = ce("span");
		bd.className = "talentcalc-upper-class";
		be = _lblClass = ce("a");
        be.target = '_blank';
        be.style.fontWeight = 'bold';
		ae(bd, be);
		ae(bd, ct(" "));
		_lblSpec = ce("b");
		ae(bd, _lblSpec);
		ae(bf, bd);
		bd = ce("span");
		bd.className = "talentcalc-upper-ptsleft";
		ae(bd, ct(LANG.tc_ptsleft));
		_lblPtsLeft = ce("b");
		ae(bd, _lblPtsLeft);
		ae(bf, bd);
		if (_mode == MODE_PET) {
			be = _lnkBonusPoints = ce("a");
			be.href = "javascript:;";
			be.onclick = _bonusPointsOnClick.bind(null, be);
			be.onmouseover = _bonusPointsOnMouseOver.bind(null, be);
			be.onmousemove = Tooltip.cursorUpdate;
			be.onmouseout = Tooltip.hide;
			ae(bd, be)
		}
		bd = ce("span");
		bd.className = "talentcalc-upper-reqlevel";
		ae(bd, ct(LANG.tc_reqlevel));
		_lblReqLevel = ce("b");
		ae(bd, _lblReqLevel);

        be = _lnkMaxLevel = ce('a');
        be.className = 'q1';
        be.href = 'javascript:;';
        be.onclick = _setLevelCapOnClick.bind(null, be);
        be.onmouseover = _setLevelCapOnMouseOver.bind(null, be);
        be.onmousemove = Tooltip.cursorUpdate;
        be.onmouseout  = Tooltip.hide;

        if(!_opt.profiler)
        {
            $WH.ae(bd, $WH.ct(' ('));
            $WH.ae(bd, be);
            $WH.ae(bd, $WH.ct(')'));
        }

		ae(bf, bd);
		bd = ce("div");
		bd.className = "clear";
		ae(bf, bd);
		ae(_divUpper, bf);

		// ae(_container, _divUpper)
		ae(_divWrapper, _divUpper)
	}

	function _convertBlizzToWh(bg) {
		var bc = "";
		var bf = [];
		for (var be = 0; be < bg.length; be += 2) {
			for (var bd = 0; bd < 2; ++bd) {
				bf[bd] = parseInt(bg.substring(be + bd, be + bd + 1));
				if (isNaN(bf[bd])) {
					bf[bd] = 0
				}
			}
			bc += _encoding.charAt(bf[0] * 6 + bf[1])
		}
		return bc
	}

	function _decrementTalent(bj, bf, bi) {
		var bh = _data[bj.classId];
		if (bj.k > 0) {
			if (bj.links) {
				for (bd = 0; bd < bj.links.length; ++bd) {
					if (bh[bj.tree].t[bj.links[bd]].k) {
						return
					}
				}
			}
			var be = 0;
			bj.k--;
			for (var bd = 0; bd < bh[bj.tree].t.length; ++bd) {
				var bg = bh[bj.tree].t[bd];
				if (bg.k && bj.y != bg.y) {
					if (be < bg.y * _pointsPerTier) {
						bj.k++;
						return
					}
				}
				be += bg.k
			}
			bh[bj.tree].k--;
			bd = bh.k--;
			_updateTree(bj.tree, bf, null, bj.classId);
			if (bf) {
				_showTooltip(bi, bj);
				if (bd >= _pointsToSpend) {
					for (var bc = 0; bc < _nTrees; ++bc) {
						_updateTree(bc, true, null, bj.classId)
					}
				}
				_onChange();
			}
		}
	}

	function _getClassIdFromOldId(oldId) {
		var L = _getClassIdFromOldId.L;

		if (L == null) {
			L = _getClassIdFromOldId.L = {};
			for (var classId in _getOldIdFromClassId.L) {
				L[_getOldIdFromClassId.L[classId]] = classId;
			}
		}

		return L[oldId];
	}

	function _getOldIdFromClassId(classId) {
		return _getOldIdFromClassId.L[classId];
	}
	_getOldIdFromClassId.L = {
         6: 9, // Death Knight
        11: 0, // Druid
         3: 1, // Hunter
         8: 2, // Mage
         2: 3, // Paladin
         5: 4, // Priest
         4: 5, // Rogue
         7: 6, // Shaman
         9: 7, // Warlock
         1: 8  // Warrior
	};

	function _getGlyphTypeFromSlot(slot) {
        var skip = 0;
        for (var i = 0, len = _glyphOrder.length; i < len; ++i) {
            var glyphType = _glyphOrder[i];

            if (slot >= skip && slot < skip + _glyphLevels[glyphType].length) {
                return glyphType;
            }

            skip += _glyphLevels[glyphType].length;
        }
	}

    function _isGlyphLocked(slot) {
        var notFound = (in_array(_glyphSlots[_getGlyphTypeFromSlot(slot)], slot) == -1);
        return (notFound || _getGlyphSlotLevel(slot) > _maxLevel);
    }

    function _getGlyphSlotLevel(slot) {
        var levelSlot = 0;
        for (var i in _glyphLevels) {
            for (var j = 0, len = _glyphLevels[i].length; j < len; ++j) {
                if (levelSlot == slot) {
                    return _glyphLevels[i][j];
                }

                ++levelSlot;
            }
        }

        return 0;
    }

	function _getRequiredLevel() {
		var
            c = _data[_currentClass],
            r;

		if (_mode == MODE_PET) {
			r = Math.max(_bonusPoints ? 60 : 0, c.k > 0 ? (c.k - _bonusPoints) * 4 + 16 : 0);
		}
        else {
			r = (c.k ? c.k + 9 : 0);
            // r = (c.k > 0 ? Math.max(0, 2 - c.k) + Math.min(36, c.k - 1) * 2 + Math.max(0, c.k - 37) + 9 : 0);
		}

        for (var slot = 0; slot < _nGlyphs; ++slot) {
            if (g_glyphs[c.glyphs[slot]]) {
                r = Math.max(r, _getGlyphSlotLevel(slot));
                r = Math.max(r, g_glyphs[c.glyphs[slot]].level);
            }
        }

        return r;
	}

	function _getTalentDescription(talent, next) {
		var description = talent.d;
		var rank = Math.max(0, talent.k - 1) + (next ? 1 : 0);

		return talent.d[rank];
	}

	function _glyphClick(slot, right) {
		if (!_locked) {
			if (right) {
				if (_removeGlyph(slot)) {
					_showGlyphTooltip(this, slot);
				}
			}
            else {
				_currentGlyphSlot = slot;
				Lightbox.show(
                    "glyphpicker", {
                        onShow: _glyphPickerOnShow
                    }
                )
			}
		}
	}

	function _glyphPickerOnShow(dest, first, opt) {
		Lightbox.setSize(800, 564);

		var
            lv,
        	_filteredGlyphs = false;


		if (first) {
			dest.className = "talentcalc-glyphpicker listview";

			var
                dataz = [],
                div   = ce("div"),
                anch  = ce("a"),
                clear = ce("div");

			dataz.push({ none: 1 });

			for (var itemId in g_glyphs) {
				dataz.push(g_glyphs[itemId]);
			}

			div.className = "listview";
			ae(dest, div);

			anch.className = "screenshotviewer-close";
			anch.href = "javascript:;";
			anch.onclick = Lightbox.hide;
			ae(anch, ce("span"));
			ae(dest, anch);

			clear.className = "clear";
			ae(dest, clear);

			lv = new Listview({
				template: "glyph",
				id: "glyphs",
				parent: div,
				data: dataz,
				customFilter: _isValidGlyph.bind(0, null),
                createNote: _createGlyphPickerNote
			});

			if (Browser.firefox) {
				aE(lv.getClipDiv(), "DOMMouseScroll", g_pickerWheel);
			}
            else {
				lv.getClipDiv().onmousewheel = g_pickerWheel;
			}
		}
        else {
			lv = g_listviews.glyphs;

			lv.clearSearch();
			lv.updateFilters(true)
		}

		setTimeout(function () {
            lv.createNote(lv.noteTop, lv.noteBot);
			lv.focusSearch()
		},
		1)
	}

	function _iconClick(talent, right) {
		if (_locked) {
			return;
		}

		if (right) {
			_decrementTalent(talent, true, this);
		}
        else {
			_incrementTalent(talent, true, this);
		}
	}

	function _incrementTalent(bg, bd, bf) {
		var be = _data[bg.classId];
		if (be.k < _pointsToSpend) {
			if (bg.enabled && bg.k < bg.m) {
				be.k++;
				be[bg.tree].k++;
				bg.k++;
				_updateTree(bg.tree, bd, bg, bg.classId);
				if (bd) {
					_showTooltip(bf, bg);
					if (be.k == _pointsToSpend) {
						for (var bc = 0; bc < _nTrees; ++bc) {
							if (bc != bg.tree) {
								_updateTree(bc, bd, null, bg.classId)
							}
						}
					}
					_onChange();
				}
			}
		}
        else {
			if (_mode == MODE_PET && be.k == _pointsToSpend && !bd) {
				_setPoints(-1, 4, true);
				_incrementTalent(bg, bd, bf)
			}
		}
	}

	function _initGlyphData() {
		var
            glyph,
            c,
            t,
            ids = [];

		for (var itemId in g_glyphs) {
			ids.push(itemId);
		}

		ids.sort();

		for (var i = 0, len = ids.length; i < len; ++i) {
			var itemId = ids[i];

			glyph = g_glyphs[itemId];
			c = glyph.classs;
			t = glyph.type;

			if (!_glyphLookup[c]) {
                _glyphLookup[c] = {};
                for(var n in _glyphLevels) {
                    _glyphLookup[c][n] = [];
                }
			}

            if(!_glyphLookup[c][t])
                continue;

			glyph.id    = itemId;
			glyph.index = _glyphLookup[c][t].length;

			_glyphLookup[c][t].push(glyph.id);
		}
	}

	function _isValidGlyph(slot, glyph) {
		if (glyph.none) {
			return true
		}

		var cls = _data[_currentClass];

		var valid = (
            // Class
            glyph.classs == _currentClass &&

            // Type
            glyph.type == _getGlyphTypeFromSlot(slot != null ? slot : _currentGlyphSlot) &&

            // Uniqueness
            !cls.glyphItems[glyph.id]
        );

        if (valid && glyph.level > _maxLevel) {
            _filteredGlyphs = true;
        }

        return valid && (glyph.level <= _maxLevel);
	}

    function _createGlyphPickerNote(div) {
        div.innerHTML = '';
        if (_filteredGlyphs) {
            div.innerHTML += '<small><b class="q10">' + LANG.tc_glyphnote + '</b></small>';
        }
    }

	function _onChange() {
		var c = _data[_currentClass];
		if (!c) {
			return;
		}

		_info.mode          = _mode;
		_info.classId       = _currentClass;
		_info.locked        = _locked;
		_info.requiredLevel = _getRequiredLevel();
		_info.pointsLeft    = _pointsToSpend - c.k;
		_info.pointsSpent   = (_mode == MODE_PET ? c[0].k: [c[0].k, c[1].k, c[2].k]);
		_info.bonusPoints   = _bonusPoints;

		st(_lblSpec,     '(' + (_mode == MODE_PET ? c.k: _info.pointsSpent.join('/')) + ')');
		st(_lblReqLevel, _info.requiredLevel ? _info.requiredLevel: '-');
        st(_lnkMaxLevel, _maxLevel);
        st(_lblPtsLeft,  _info.pointsLeft);

		if (_locked) {
			st(_lnkLock, LANG.tc_unlock);
			_lnkLock.className = 'talentcalc-button-unlock';
		}
        else {
			st(_lnkLock, LANG.tc_lock);
			_lnkLock.className = 'talentcalc-button-lock';
		}

		if (_mode == MODE_PET) {
			if (_bonusPoints) {
				st(_lnkBonusPoints, '[-]');
				_lnkBonusPoints.className = 'q10'
			} else {
				st(_lnkBonusPoints, '[+]');
				_lnkBonusPoints.className = 'q2'
			}
		}

        if(_lnkExport) {
            _lnkExport.href = (_mode == MODE_PET ? '?petcalc#' : '?talent#') + _self.getWhBuild() + (_mode == MODE_PET ? '' : ':' + _self.getWhGlyphs());
        }

		for (var tree = 0; tree < _nTrees; ++tree) {
			var bd = _lblTreePoints[tree].firstChild.childNodes[1];
			st(bd, ' (' + c[tree].k + ')')
		}

		if (_onChangeCallback) {
			_onChangeCallback(_self, _info, c)
		}
	}

	function _onClassChange() {
		st(_lblClass, _referenceArray[_currentClass]);
		if (_mode == MODE_PET) {
			_lblClass.href = "?pet=" + _currentClass;
            _updateModel(_currentClass);

            _divModel.style.display = "";
		}
        else {
            _lblClass.href = '?class=' + _currentClass;
            _lblClass.className = "c" + _currentClass;
		}

		if (_nClassChanges == 0) {  // First time
			_controlsDiv.style.display = "";
			_lnkSummary.style.display  = "";

			if (_lnkExport) {
				_lnkExport.style.display = "";
			}

			if (_glyphDiv) {
				_glyphDiv.style.display = "";
			}

			_divUpper.style.display   = "";
			_divLower.style.display   = "";
		}

		var c = _data[_currentClass];

		for (var tree = 0; tree < _nTrees; ++tree) {
			var span = _lblTreePoints[tree].firstChild.childNodes[0];
			if (_mode == MODE_DEFAULT) {
				span.style.backgroundImage = "url(images/talent/classes/trees/" + g_file_classes[_currentClass] + "_" + (tree + 1) + ".gif)";
			}
			st(span, c[tree].n);
		}

		_afterCapDecrease();
		_refreshAll(_currentClass);
		_onChange();

        ++_nClassChanges;
	}

	function _parseBlizzBuild(bk, bi) {
		var bj = _data[bi];
		var bl = 0,
		bd = 0;
		var bf = null,
		bc;
		for (var bh = 0; bh < bk.length; ++bh) {
			var be = Math.min(parseInt(bk.charAt(bh)), bj[bl].t[bd].m);
			if (isNaN(be)) {
				continue
			}
			for (var bg = 0; bg < be; ++bg) {
				_incrementTalent(bj[bl].t[bd])
			}
			if (bf) {
				for (var bg = 0; bg < bc; ++bg) {
					_incrementTalent(bf)
				}
				bf = null
			}
			if (bj[bl].t[bd].k < be) {
				bf = bj[bl].t[bd];
				bc = be - bj[bl].t[bd].k
			}
			if (++bd > bj[bl].t.length - 1) {
				bd = 0;
				if (++bl > _nTrees - 1) {
					break
				}
			}
		}
	}

	function _parseBlizzGlyphs(bk) {
		var be = ("" + bk).split(":", _nGlyphs),
		bh = 0,
		bd = 0;
		for (var bf = 0, bg = be.length; bf < bg && bf < _nGlyphs; ++bf) {
			var bc = be[bf],
			bi = g_glyphs[bc];
			if (bi) {
				var bj = -1;
				if (bi.type == 1) {
					if (bh < _glyphLevels[1].length) {
						bj = _glyphLevels[1][bh]; ++bh
					}
				} else {
					if (bd < _glyphLevels[2].length) {
						bj = _glyphLevels[2][bd]; ++bd
					}
				}
				if (bj != -1) {
					_addGlyph(bj, bc, true)
				}
			} else {
				if (_getGlyphTypeFromSlot(bf) == 1) {++bh
				} else {++bd
				}
			}
		}
	}

	function _parseWhBuild(bn, bk) {
		var bl = _data[bk];
		var bo = 0,
		be = 0;
		var bm = [];
		var bg = null,
		bd;
		for (var bj = 0; bj < bn.length; ++bj) {
			var bc = bn.charAt(bj);
			if (bc != _newTree) {
				var bf = _encoding.indexOf(bc);
				if (bf < 0) {
					continue
				}
				bm[1] = bf % 6;
				bm[0] = (bf - bm[1]) / 6;
				for (var bi = 0; bi < 2; ++bi) {
					bf = Math.min(bm[bi], bl[bo].t[be].m);
					for (var bh = 0; bh < bf; ++bh) {
						_incrementTalent(bl[bo].t[be])
					}
					if (bg) {
						for (var bh = 0; bh < bd; ++bh) {
							_incrementTalent(bg)
						}
						bg = null
					}
					if (bl[bo].t[be].k < bf) {
						bg = bl[bo].t[be];
						bd = bf - bl[bo].t[be].k
					}
					if (++be >= bl[bo].t.length) {
						break
					}
				}
			}
			if (be >= bl[bo].t.length || bc == _newTree) {
				be = 0;
				if (++bo > _nTrees - 1) {
					return
				}
			}
		}
	}

	function _parseWhGlyphs(bd) {
		var bg = 0;
		for (var be = 0, bc = bd.length; be < bc && be < _nGlyphs; ++be) {
			var bf = bd.charAt(be);
			if (bf == "Z") {
				bg = 3;
				continue;
			}
			_addGlyph(bg, _glyphLookup[_currentClass][_getGlyphTypeFromSlot(bg)][_encoding.indexOf(bf)], true);
            ++bg;
		}
	}

	function _refreshAll(classId) {
		_refreshGlyphs();

		for (var tree = 0; tree < _nTrees; ++tree) {
			_updateTree(tree, true, null, classId);
		}
	}

	function _refreshGlyphs() {
		if (_mode != MODE_DEFAULT) {
			return;
		}

		var nGlyphs = 0;
		for (var bd = 0; bd < _nGlyphs; ++bd) {
			if (_updateGlyph(bd)) {
                ++nGlyphs;
			}
		}

		_glyphDiv.style.display = (nGlyphs == 0 && _locked && _opt.profiler ? "none": "")
	}

	function _registerClass(be, bd) {
		if (_data[be] == null) {
			bd.n = be;
			_data[be] = bd;
			var bf = _data[be];
			bf.glyphs = [];
			bf.glyphItems = {};
			_createTrees(be);
			if (_build && _build.classId == be) {
				for (var bc = 0; bc < _nTrees; ++bc) {
					_updateTree(bc, false, null, be)
				}
				if (_build.wh || _build.blizz) {
					_locked = true;
					if (_build.wh) {
						_parseWhBuild(_build.wh, be)
					} else {
						_parseBlizzBuild(_build.blizz, be)
					}
				}
			} else {
				_locked = false
			}
			_build = null;
			if (_glyphs && _glyphs.classId == be) {
				if (_glyphs.wh) {
					_parseWhGlyphs(_glyphs.wh)
				} else {
					_parseBlizzGlyphs(_glyphs.blizz)
				}
			}
			_glyphs = null;
			if (be == _currentClass) {
				_onClassChange();
				bf.div.style.display = "";
				for (var bc = 0; bc < _nTrees; ++bc) {
					_updateTree(bc, true, null, be)
				}
			}
		}
	}

	function _removeGlyph(be, bc) {
		var bd = _data[_currentClass];
		if (bd.glyphs[be]) {
			bd.glyphItems[bd.glyphs[be]] = 0;
			bd.glyphs[be] = 0;
			if (!bc) {
				_updateGlyph(be);
				_onChange();
			}
			return true;
		}
	}

	function _resetAll(bc) {
		_resetBuild(bc);
		_resetGlyphs();
		_refreshAll(bc);
	}

	function _resetBuild(bd) {
		if (_mode == MODE_PET) {
			_setPoints(-1, 0, true)
		}
		for (var bc = 0; bc < _nTrees; ++bc) {
			_resetTree(bc, bd, false);
		}
	}

	function _resetGlyphs(bc) {
		var be = _data[_currentClass];
		if (!be) {
			return;
		}
		for (var bd = 0; bd < _nGlyphs; ++bd) {
			_removeGlyph(bd, !bc);
		}
		_refreshGlyphs();
	}

	function _resetTree(bc, bf, be) {
		var bg = _data[bf];
		var bd;
		for (bd = 0; bd < bg[bc].t.length; ++bd) {
			bg[bc].t[bd].k = 0
		}
		bd = (bg.k < _pointsToSpend);
		bg.k -= bg[bc].k;
		bg[bc].k = 0;
		if (be) {
			if (bd) {
				_updateTree(bc, true, null, bf)
			} else {
				for (bc = 0; bc < _nTrees; ++bc) {
					_updateTree(bc, true, null, bf)
				}
			}
			_onChange();
		}
	}

    function _restore() {
		if (_firstBuild) {
			if (_firstBuild.wh) {
				_setWhBuild(_firstBuild.wh);
			}
            else {
				_setBlizzBuild(_firstBuild.classId, _firstBuild.blizz);
			}
		}
		if (_firstGlyphs && _firstGlyphs.wh) {
            _setWhGlyphs(_firstGlyphs.wh);
		}
	}

	function _setBlizzBuild(bc, bd) {
		if (_referenceArray[bc] == null) {
			return
		}
		if (!bd) {
			return
		}
		_locked = true;
		if (!_firstBuild) {
			_firstBuild = {
				classId: bc,
				blizz: bd
			};
			_lnkRestore.style.display = ""
		}
		if (_data[bc]) {
			_resetBuild(bc);
			_refreshAll(bc);
			_parseBlizzBuild(bd, bc);
			_refreshAll(bc)
		} else {
			_build = {
				classId: bc,
				blizz: bd
			}
		}
		if (!_setClass(bc)) {
			_onChange();
		}
	}

	function _setBlizzGlyphs(bc) {
		if (!bc) {
			return
		}
		if (_data[_currentClass]) {
			_resetGlyphs();
			_parseBlizzGlyphs(bc);
			_refreshGlyphs();
			_onChange();
		} else {
			_glyphs = {
				classId: _currentClass,
				blizz: bc
			}
		}
	}

	function _setClass(classId) {
		if (_referenceArray[classId] == null) {
			return;
		}

		if (classId != _currentClass) {
			_oldClass     = _currentClass;
			_currentClass = classId;

			if (_mode == MODE_PET && _data[classId] == null) {
				_registerClass(classId, _createPetTalents(classId));
			}
            else {
				if (_data[classId]) {
					_onClassChange();

					var c = _data[classId];
					c.div.style.display = "";
				}
                else {
					g_ajaxIshRequest("?data=talents&class=" + classId + "&" + _versionBuild);
				}
			}

			if (_data[_oldClass]) {
				_data[_oldClass].div.style.display = "none";
			}

			return true;
		}
	}

    function _setLevelCap(lvl) {
        lvl = parseInt(lvl);

		if (isNaN(lvl) || lvl < MIN_LEVEL || lvl > MAX_LEVEL) {
			return;
		}

        _maxLevel = lvl;

        var basePoints;

		if (_mode == MODE_PET) {
			basePoints = Math.max(0, Math.floor((lvl - 16) / 4));
		}
        else {
			basePoints = Math.max(0, lvl - 9);
            // basePoints = Math.max(0, Math.min(10, lvl) - 9) + Math.max(0, Math.floor((Math.min(80, lvl) - 9) / 2)) + Math.max(0, lvl - 80);
		}

        _setPoints(basePoints, -1);
        _setGlyphSlots(lvl);
        _refreshGlyphs();
	};

	function _setLock(locked) {
		if (_locked != locked) {
			_locked = locked;

			_refreshAll(_currentClass);
			_onChange(1);
		}
	}

    function _setPoints(base, bonus, stealth) {
        var bak = _pointsToSpend;

        if (base == -1) {
            base = _basePoints;
        }

        if (bonus == -1) {
            bonus = _bonusPoints;
        }

        _basePoints    = base;
        _bonusPoints   = bonus;
        _pointsToSpend = base + bonus;

        if (_currentClass != -1) {
            if (_pointsToSpend < bak) {
                _afterCapDecrease();
            }

            _refreshAll(_currentClass);

            if (!stealth) {
                _onChange();
            }
        }
    }

    function _setGlyphSlots(level) {
        _nGlyphs = 0;
        _glyphSlots = {};

        var slot = 0;
        for (var i = 0, len = _glyphOrder.length; i < len; ++i) {
            var glyphType = _glyphOrder[i];
            _glyphSlots[glyphType] = [];

            for(var j = 0, len2 = _glyphLevels[glyphType].length; j < len2; ++j) {
                if(level >= _glyphLevels[glyphType][j]) {
                    _glyphSlots[glyphType].push(slot);
                    _nGlyphs++;
                }

                ++slot;
            }
        }
    }

    function _setWhBuild(bg) {
		if (!bg) {
			return;
		}
		var bc = bg,
		bd = false,
		be;
		if (_mode == MODE_PET) {
			var bh = _encoding.indexOf(bg.charAt(0));
			if (bh >= 0 && bh <= 4) {
				var bf = _encoding.indexOf(bg.charAt(1));
				if (bf % 2 == 1) {
					_setPoints(-1, 4, true);
                    --bf
				} else {
					_setPoints(-1, 0, true)
				}
				be = bh * 10 + (bf / 2);
				if (g_pet_families[be] != null) {
					bg = bg.substr(2);
					bd = true
				}
			}
		} else {
			var bh = _encoding.indexOf(bg.charAt(0));
			if (bh >= 0 && bh <= 27) {
				var bf = bh % 3,
				be = (bh - bf) / 3;
				be = _getClassIdFromOldId(be);
				if (be != null) {
					bg = bg.substr(1);
					bd = true
				}
			}
		}
		if (bd) {
			if (bg.length) {
				_locked = true;
				if (!_firstBuild) {
					_firstBuild = {
						wh: bc
					};
					_lnkRestore.style.display = ""
				}
			}
			if (_data[be]) {
				_resetBuild(be);
				_parseWhBuild(bg, be);
				_refreshAll(be)
			} else {
				_build = {
					classId: be,
					wh: bg
				}
			}
			if (!_setClass(be)) {
				_onChange();
			}
			return be;
		}
	}

	function _setWhGlyphs(bc) {
		if (!bc) {
			return;
		}
		if (!_firstGlyphs) {
			_firstGlyphs = {
				wh: bc
			}
		}
		if (_data[_currentClass]) {
			_resetGlyphs();
			_parseWhGlyphs(bc);
			_refreshGlyphs();
			_onChange();
		} else {
			_glyphs = {
				classId: _currentClass,
				wh: bc
			}
		}
	}

	function _showGlyphTooltip(_this, slot) {
		var
            c      = _data[_currentClass];
            upper  = "",
            lower  = "",
            glyph  = g_glyphs[c.glyphs[slot]],
            locked = _isGlyphLocked(slot);

		if (glyph && !locked) {
			upper += "<b>" + glyph.name + "</b>";
			upper += '<br /><span class="q9">' + LANG[slot <= 2 ? "tc_majgly": "tc_mingly"] + "</span>";

			lower += '<span class="q">' + glyph.description + "</span>";
			if (!_locked) { // If glyphs are locked, do not show instructions on how to click to remove them.
				lower += '<br /><span class="q10">' + LANG["tc_remgly"] + "</span>";
			}
		}
        else if (!locked) {
			upper += '<b class="q0">' + LANG.tc_empty + "</b>";
			upper += '<br /><span class="q9">' + LANG[slot <= 2 ? "tc_majgly": "tc_mingly"] + "</span>";

			if (!_locked) { // If glyphs are locked, do not show instructions on how to click to add them.
				lower += '<span class="q2">' + LANG.tc_addgly + "</span>";
			}
		}
        else {
            upper += '<b class="q0">' + LANG.tc_locked + '</b>';
            upper += '<br /><span class="q9">' + LANG[slot <= 2 ? "tc_majgly": "tc_mingly"] + '</span>';
            lower += '<span class="q10">' + sprintf(LANG.tc_lockgly, _getGlyphSlotLevel(slot)) + '</span>';
        }

		if (glyph && _this.parentNode.className.indexOf("icon") != 0) {
			Tooltip.setIcon(glyph.icon);
		}
        else {
			Tooltip.setIcon(null);
		}

		Tooltip.show(_this, "<table><tr><td>" + upper + "</td></tr></table><table><tr><td>" + lower + "</td></tr></table>")
	}

	function _showTooltip(bf, be) {
		var bd = _data[be.classId],
		bc = "<table><tr><td><b>";
		if (be.z) {
			bc += '<span style="float: right" class="q0">' + be.z + "</span>"
		}
		bc += be.n + "</b><br />" + sprintf(LANG.tc_rank, be.k, be.m) + "<br />";
		if (be.r) {
			if (bd[be.tree].t[be.r[0]].k < be.r[1]) {
				bc += '<span class="q10">';
				bc += sprintf(LANG[be.r[1] == 1 ? "tc_prereq": "tc_prereqpl"], be.r[1], bd[be.tree].t[be.r[0]].n);
				bc += "</span><br />"
			}
		}
		if (bd[be.tree].k < be.y * _pointsPerTier) {
			bc += '<span class="q10">' + sprintf(LANG.tc_tier, (be.y * _pointsPerTier), bd[be.tree].n) + "</span><br />"
		}
		if (be.t && be.t.length >= 1) {
			bc += be.t[0]
		}
		bc += "</td></tr></table><table><tr><td>";
		if (be.t && be.t.length > 1) {
			bc += be.t[1] + "<br />"
		}
		bc += '<span class="q">' + _getTalentDescription(be) + "</span><br />";
		if (_locked) {} else {
			if (be.enabled) {
				if (!be.k) {
					bc += '<span class="q2">' + LANG.tc_learn + "</span><br />"
				} else {
					if (be.k == be.m) {
						bc += '<span class="q10">' + LANG["tc_unlearn"] + "</span><br />"
					}
				}
				if (be.k && be.k < be.m) {
					bc += "<br />" + LANG.tc_nextrank + '<br /><span class="q">' + _getTalentDescription(be, 1) + "</span><br />"
				}
			}
		}
		bc += "</td></tr></table>";
		Tooltip.show(bf, bc)
	}

	function _simplifyGlyphName(name) {
        var str;

		switch (g_locale.id) {
            case 0:
                str = name.replace(/^Glyph of (the )?/i, '');
                break;
            case 2:
                str = name.replace(/^Glyphe (d'|des |de )?/i, '');
                break;
            case 3:
                str = name.match(/^Glyphe '(.*)'/i);
                str = str && str[1] ? str[1] : name;
                break;
            case 6:
                str = name.replace(/^Glifo (de )?/i, '');
                break;
            case 8:
                str = name.replace(/^Символ /i, '');
                break;
            default:
                return name;
        }

        // ucFirst
        return str.charAt(0).toUpperCase() + str.substr(1);
	}

	function _toggleLock() {
		_locked = !_locked;
		_refreshAll(_currentClass);
		_onChange();

		return _locked;
	}

	function _updateGlyph(slot) {
		var
            c      = _data[_currentClass],
            icon   = _glyphIcons[slot],
            link   = Icon.getLink(icon),
            locked = _isGlyphLocked(slot),
            a      = _glyphLinks[slot],
            glyph;

		if (c.glyphs[slot]) {
			glyph = g_glyphs[c.glyphs[slot]];

            if (locked || glyph.level > _maxLevel) {
                _removeGlyph(slot);
            }
        }

        if (glyph && !locked) {

			Icon.setTexture(icon, 1, glyph.icon);
			a.href = link.href = '?item=' + glyph.id;

			st(a, _simplifyGlyphName(glyph.name));
			a.className = 'q1';

			return true;
		}
        else {
			Icon.setTexture(icon, 1, 'inventoryslot_empty');
			a.href = link.href = 'javascript:;';

			st(a, (!locked ? LANG.tc_empty : LANG.tc_locked));
			a.className = 'q0';

			return false;
		}
	}

	function _updateTree(bl, bh, bd, bi) {
		var bj = _data[bi];
		var bg;
		var bc;
		if (!bd || bj.k == _pointsToSpend) {
			bc = 0;
			bg = _pointsToSpend - 21
		} else {
			bc = bd.i;
			bg = Math.floor(bj[bl].k / 5) * 5 + 5
		}
		if (bd != null && bd.links != null) {
			for (var be = 0, bf = bd.links.length; be < bf; ++be) {
				if (bc > bd.links[be]) {
					bc = bd.links[be]
				}
			}
		}
		for (var be = bc; be < bj[bl].t.length; ++be) {
			bd = bj[bl].t[be];
			if (bj.k == _pointsToSpend && !bd.k) {
				bd.enabled = 0
			} else {
				if (bj[bl].k >= bd.y * _pointsPerTier) {
					if (bd.r) {
						if (bj[bl].t[bd.r[0]].k >= bd.r[1]) {
							bd.enabled = 1
						} else {
							bd.enabled = 0
						}
					} else {
						bd.enabled = 1
					}
				} else {
					bd.enabled = 0
				}
			}
			if (bh) {
				if (bd.enabled && (!_locked || bd.k)) {
					if ((bd.k == bd.m)) {
						bd.border.style.backgroundPosition = "-42px 0";
						bd.bubble.style.color = "#E7BA00"
					} else {
						bd.border.style.backgroundPosition = "-84px 0";
						bd.bubble.style.color = "#17FD17"
					}
					Icon.moveTexture(bd.icon, 1, be, 0);
					bd.link.className = "bubbly";
					bd.bubble.style.visibility = "visible";
					if (bd.r) {
						var bk = bd.arrow.firstChild;
						if (bk.className.charAt(bk.className.length - 1) != "2") {
							bk.className += "2"
						}
					}
				} else {
					bd.border.style.backgroundPosition = "0 0";
					Icon.moveTexture(bd.icon, 1, be, 1);
					bd.link.className = "";
					bd.bubble.style.visibility = "hidden";
					if (bd.r) {
						var bk = bd.arrow.firstChild;
						if (bk.className.charAt(bk.className.length - 1) == "2") {
							bk.className = bk.className.substr(0, bk.className.length - 1)
						}
					}
				}
				bd.bubble.firstChild.nodeValue = bd.k;
				bd.link.href = "?spell=" + bd.s[Math.max(0, bd.k - 1)]
			}
		}
	}

    function _updateModel(classId, npcId) {

        var swfUrl = "http://static.wowhead.com"; // g_staticUrl

        if (_mode != MODE_PET){
            return;
        }

        var c = _data[classId];

        if (!c) {
            return;
        }

        if (g_pets[npcId] && g_pets[npcId].family == classId) {
            c.npcId = npcId;
        }

        if(!g_pets[c.npcId] || g_pets[c.npcId].family != classId) {
            var models = [];

            for(var i in g_pets) {
                if(g_pets[i].family == classId) {
                    models.push(g_pets[i].id);
                }
            }

            if(!models.length) {
                return;
            }

            c.npcId = models[Math.floor(Math.random() * models.length)];
        }

        var flashVars = {
            model: g_pets[c.npcId].displayId,
            modelType: 8,
            contentPath: swfUrl + '/modelviewer/',
            blur: (OS.mac ? '0' : '1')
        };

        var params = {
            quality: 'high',
            allowscriptaccess: 'always',
            allowfullscreen: true,
            menu: false,
            bgcolor: '#181818',
            wmode: 'opaque'
        };

        var attributes = {};

        swfobject.embedSWF(
            swfUrl + '/modelviewer/ModelView.swf',
            _swfModel.id,
            '100%',
            '100%',
            '10.0.0',
            swfUrl + '/modelviewer/expressInstall.swf',
            flashVars,
            params,
            attributes
        );
    }
}

TalentCalc.MODE_DEFAULT = 0;
TalentCalc.MODE_PET     = 1;

Listview.templates.glyph = {
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
	},

	onSearchSubmit: function(glyph) {
		if (this.nRowsVisible != 1) {
			return;
        }

		$WowheadTalentCalculator.addGlyph(glyph.id);
	},

	columns: [
        {
            id: 'glyph',
            type: 'text',
            align: 'left',
            value: 'name',
			span: 2,
            compute: function (glyph, td, tr) {
                if (glyph.none) {
                    return;
                }

                var i = ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(glyph.icon, 0, null, '?item=' + glyph.id),
                    link = Icon.getLink(icon);
                ae(i, icon);
                ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = rf;

                var a = ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + glyph.id;

                ae(a, ct($WowheadTalentCalculator.simplifyGlyphName(glyph.name)));

                td.style.whiteSpace = 'nowrap';
                ae(td, a);

                tr.onclick = function(e) {
                    if (e.which != 2 || e.target != a) {
                        e.preventDefault();
                        ($WowheadTalentCalculator.addGlyph.bind(null, glyph.id))();
                    }
                };
            },
            sortFunc: function (a, b, col) {
                if (a.none) {
                    return -1;
                }

                return strcmp(a.name, b.name);
            }
        },
        {
            id: 'description',
            type: 'text',
            align: 'left',
            value: 'description',
            compute: function (glyph, td) {
                if (glyph.none) {
                    return;
                }

                var d = ce('div');
                d.className = 'small crop';
                td.title = glyph.description;
                ae(d, ct(glyph.description));
                ae(td, d);
            }
        },
        {
			id: 'level',
			type: 'text',
			align: 'center',
			value: 'level',
			compute: function(glyph, td) {
				if(glyph.none) {
                    return;
                }

				td.className = 'small q0';
				td.style.whiteSpace = 'nowrap';
				return LANG.tc_level.replace('%s', glyph.level);
			}
		},
		{
        id: 'skill',
            type: 'text',
            align: 'center',
            getValue: function (glyph) {
                if (glyph.none) {
                    return;
                }

                return g_spell_skills[glyph.skill];
            },
            compute: function (glyph, td, tr) {
                if (glyph.none) {
                    ee(tr);

                    tr.onclick = $WowheadTalentCalculator.addGlyph.bind(null, 0);
					td.colSpan          = 5;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign  = 'center';

                    return LANG.dash + LANG.tc_nonegly + LANG.dash;
                }

                if (glyph.skill) {
                    td.className = 'small q1';
                    td.style.whiteSpace = 'nowrap';

                    var a = ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = '?skill=' + glyph.skill;

                    ae(a, ct(g_spell_skills[glyph.skill]));
                    ae(td, a);
                }
            }
        }
    ]
};