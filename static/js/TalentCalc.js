var $WowheadTalentCalculator;

function TalentCalc() {
    var
        MODE_DEFAULT       = 0,
        MODE_PET           = 1,

        MIN_LEVEL          = 10,
        MAX_LEVEL          = 80,

        _versionBuild      = 85,
        _self              = this,
        _opt,
        _data              = {},
        _glyphLookup       = {},
        _talentLookup      = {},
        _build,
        _firstBuild,
        _firstGlyphs,
        _glyphs,
        _currentClass      = -1,
        _oldClass          = -1,
        _nClassChanges     = 0,
        _currentGlyphSlot,
        _filteredGlyphs    = false,
        _locked            = false,
        _mode,
        _nTrees,
        _nTiers,
        _nGlyphs,
        _glyphSlots,
        _glyphOrder        = [1, 2],
        _glyphLevels       = { 1: [], 2: [] }, // Major, Minor
        _pointsPerTier,
        _basePoints,
        _bonusPoints       = 0,
        _pointsFromBonus,
        _maxLevel          = MAX_LEVEL,
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
            _glyphIcons    = {},
            _glyphLinks    = {},
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


        _encoding          = '0zMcmVokRsaqbdrfwihuGINALpTjnyxtgevElBCDFHJKOPQSUWXYZ123456789',
        _blizzEncoding     = 'aZbYcXdWeVfUgThSiRjQkPlOmNnMoLpKqJrIsHtGuFvEwDxCyBzA0123456789_=+-.',
        _newTree           = 'Z',

        _onChangeCallback,
        _info              = {};

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

        blizz = $WH.rtrim(blizz, '0');

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

            blizz = $WH.rtrim(blizz, '0');

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

        res += $WH.rtrim(wh, _newTree);

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

        res = $WH.rtrim(res, _newTree);

        return res;
    };

    this.initialize = function (container, opt) {
        if (_container) {
            return;
        }

        container = $WH.ge(container);
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
        _divWrapper = $WH.ce('div');
        _divWrapper.className = 'talentcalc-wrapper';
        $WH.ae(_container, _divWrapper);

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
        if (pos != -1) {
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

    this.getBonusPoints = function (bonusPoints) {
        return _bonusPoints;
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
                buffer += $WH.sprintf(LANG.tc_printh, _getRequiredLevel(), g_pet_families[c.n]);
            }
            else {
                buffer += $WH.sprintf(LANG.tc_printh, _getRequiredLevel(), g_chr_classes[c.n]) + ' (' + c[0].k + '/' + c[1].k + '/' + c[2].k + ')';
            }

            buffer += '</h2>';
            buffer += '<p></p>';

            // custom: deploy old armory-string
            if ($WowheadTalentCalculator !== undefined) {
                buffer += '<h3>' + LANG.tc_export + LANG.colon + '</h3>';
                buffer += '<blockquote style="font-family: \'Courier New\';">?cid=' + _currentClass + '&tal=' + $WowheadTalentCalculator.getBlizzBuild() + '</blockquote>';
                buffer += '<p></p>';
            }
            // custom end


            for (i = 0; i < _nTrees; ++i) {
                buffer += '<h3>' + c[i].n + ' (' + c[i].k + ' ' + LANG[c[i].k == 1 ? 'tc_point': 'tc_points'] + ')</h3>';
                buffer += '<blockquote>';
                k = 0;

                for (j = 0; j < c[i].t.length; ++j) {
                    if (c[i].t[j].k) {
                        if (k) {
                            buffer += '<br /><br />';
                        }

                        buffer += '<b>' + c[i].t[j].n + '</b>' + LANG.hyphen + $WH.sprintf(LANG.tc_rank, c[i].t[j].k, c[i].t[j].m) + '<br />';
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
        if (_mode == MODE_PET) {
            $WH.Tooltip.showAtCursor(e, LANG[_bonusPoints ? "tc_rembon": "tc_addbon"], null, null, "q");
        }
    }

    function _setLevelCapOnClick(_this, e) {
        _setLevelCap(prompt($WH.sprintf(LANG.prompt_ratinglevel, MIN_LEVEL, MAX_LEVEL), _maxLevel));
        _setLevelCapOnMouseOver(_this, e);
        _onChange();
    }

    function _setLevelCapOnMouseOver(_this, e) {
        $WH.Tooltip.showAtCursor(e, LANG.tooltip_changelevel, null, null, 'q');
    }

    function _createArrow(arrowType, width, height) {
        var
            d = $WH.ce('div'),
            t,
            _;

        d.className = 'talentcalc-arrow';

        switch(arrowType) {
            case 0: // Down
                width = 15;
                t = _createTable(1, 2);
                t.className = 'talentcalc-arrow-down';

                _ = t.firstChild.childNodes[0].childNodes[0].style;
                _.width = '15px';
                _.height = '4px';
                _ = t.firstChild.childNodes[1].childNodes[0].style;
                _.backgroundPosition = 'bottom';
                _.height = (height - 4) + 'px';
                break;
            case 1: // Leftdown
                t = _createTable(2, 2, true);
                t.className = 'talentcalc-arrow-leftdown';

                _ = t.firstChild.childNodes[0].childNodes[0].style;
                _.backgroundPosition = 'left';
                _.width = (width - 4) + 'px';
                _.height = '11px';
                _ = t.firstChild.childNodes[0].childNodes[1].style;
                _.backgroundPosition = 'right';
                _.width = '4px';
                _ = t.firstChild.childNodes[1].childNodes[0].style;
                _.backgroundPosition = 'bottom left';
                _.backgroundRepeat = 'no-repeat';
                _.height = (height - 11) + 'px';
                break;
            case 2: // Rightdown
                t = _createTable(2, 2, true);
                t.className = 'talentcalc-arrow-rightdown';

                _ = t.firstChild.childNodes[0].childNodes[0].style;
                _.backgroundPosition = 'left';
                _.width = '4px';
                _ = t.firstChild.childNodes[0].childNodes[1].style;
                _.backgroundPosition = 'right';
                _.width = (width - 4) + 'px';
                _.height = '11px';
                _ = t.firstChild.childNodes[1].childNodes[0].style;
                _.backgroundPosition = 'bottom right';
                _.backgroundRepeat = 'no-repeat';
                _.height = (height - 11) + 'px';
                break;
            case 3: // Right
                height = 15;
                t = _createTable(2, 1);
                t.className = 'talentcalc-arrow-right';

                _ = t.firstChild.childNodes[0].childNodes[0].style;
                _.backgroundPosition = 'left';
                _.width = '4px';
                _ = t.firstChild.childNodes[0].childNodes[1].style;
                _.backgroundPosition = 'right';
                _.width = (width - 4) + 'px';
                break;
            case 4: // Left
                height = 15;
                t = _createTable(2, 1);
                t.className = 'talentcalc-arrow-left';

                _ = t.firstChild.childNodes[0].childNodes[0].style;
                _.backgroundPosition = 'left';
                _.width = (width - 4) + 'px';
                _ = t.firstChild.childNodes[0].childNodes[1].style;
                _.backgroundPosition = 'right';
                _.width = '4px';
                break;
        }

        d.style.width = width + 'px';
        d.style.height = height + 'px';

        $WH.ae(d, t);
        return d;
    }

    function _createLower() {
        var
            _,
            __,
            ___;

        _divLower = $WH.ce('div');
        _divLower.className = 'talentcalc-lower';
        _divLower.style.display = 'none';

        for (var tree = 0; tree < _nTrees; ++tree) {
            _ = _lblTreePoints[tree] = $WH.ce('div');
            _.className = 'talentcalc-lower-tree' + (tree + 1);

            __ = $WH.ce('p');
            $WH.ae(__, $WH.ce('b'));
            $WH.ae(__, $WH.ce('span'));
            ___ = $WH.ce('a');
            ___.href = 'javascript:;';
            ___.onclick = _self.reset.bind(null, tree);
            g_addTooltip(___, LANG.tc_resettree);
            $WH.ae(__, ___);
            $WH.ae(_, __);
            $WH.ae(_divLower, _);
        }

        $WH.ae(_divWrapper, _divLower);
    }

    function _createMain() {
        _divMain = $WH.ce("div");
        _divMain.className = "talentcalc-main";

        var clear = $WH.ce("div");
        clear.className = "clear";
        $WH.ae(_divMain, clear);

        $WH.ae(_divWrapper, _divMain);
    }

    function _createModel() {
        if (_mode != MODE_PET) {
            return;
        }

        _divModel = $WH.ce('div');
        _divModel.className = 'talentcalc-model';

        _divModel.style.display = "none";

        _swfModel = $WH.ce('div');
        _swfModel.id = 'shg09yrhlnk';
        $WH.ae(_divModel, _swfModel);

        var clear = $WH.ce('div');
        clear.className = 'clear';
        $WH.ae(_divModel, clear);

        $WH.ae(_divWrapper, _divModel);
    }

    function _createPetTalents(classId) {
        var
            data = [{}],
            talent,
            _;

        for (var i = 0, len = g_pet_talents.length; i < len; ++i) {
            var talents = g_pet_talents[i];

            if ($WH.in_array(talents.f, classId) >= 0) {
                data[0].n    = talents.n;
                data[0].t    = [];
                data[0].i    = i;
                data[0].icon = talents.icon;

                for (var j = 0, bf = talents.t.length; j < bf; ++j) {
                    talent = talents.t[j];

                    _ = data[0].t[j] = {};
                    $WH.cO(_, talent);

                    if (talent.f && $WH.in_array(talent.f, classId) == -1)
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

        _divSidebar = $WH.ce('div');
        _divSidebar.className = 'talentcalc-sidebar';

        sidebarDivInner = $WH.ce('div');
        sidebarDivInner.className = 'talentcalc-sidebar-inner';

        _ = $WH.ce('a');
        _.className = 'talentcalc-button-help';
        _.href = (_mode == MODE_PET ? 'http://www.wow-petopia.com/talents/talents.html' : '?help=talent-calculator');
        _.target = '_blank';
        $WH.ae(_, $WH.ct(LANG.tc_help));
        $WH.ae(sidebarDivInner, _);

        _controlsDiv = $WH.ce('div');
        _controlsDiv.className = 'talentcalc-sidebar-controls';
        _controlsDiv.style.display = 'none';

        _ = $WH.ce('a');
        _.className = 'talentcalc-button-reset';
        _.href = 'javascript:;';
        _.onclick = _self.resetAll;
        $WH.ae(_, $WH.ct(LANG.tc_resetall));
        $WH.ae(_controlsDiv, _);

        _ = _lnkLock = $WH.ce('a');
        _.className = 'talentcalc-button-lock';
        _.href = 'javascript:;';
        _.onclick = _toggleLock;
        $WH.ae(_, $WH.ct(LANG.tc_lock));
        $WH.ae(_controlsDiv, _);

        _ = $WH.ce('div');
        _.className = 'clear';
        $WH.ae(_controlsDiv, _);

        $WH.ae(sidebarDivInner, _controlsDiv);

        _ = $WH.ce('div');
        _.className = 'talentcalc-sidebar-controls2';

        __ = $WH.ce('a');
        __.className = 'talentcalc-button-import';
        __.href = 'javascript:;';
        __.onclick = (_opt.profiler ? _self.promptWhBuild : _self.promptBlizzBuild);
        $WH.ae(__, $WH.ct(LANG.tc_import));
        $WH.ae(_, __);

        __ = _lnkSummary = $WH.ce('a');
        __.className = 'talentcalc-button-summary';
        __.style.display = 'none';
        __.href = 'javascript:;';
        __.onclick = _self.showSummary.bind(null, 1);
        $WH.ae(__, $WH.ct(LANG.tc_summary));
        $WH.ae(_, __);

        __ = _lnkRestore = $WH.ce('a');
        __.className = 'talentcalc-button-restore';
        __.style.display = 'none';
        __.href = 'javascript:;';
        __.onclick = _restore;
        $WH.ae(__, $WH.ct(LANG.tc_restore));
        $WH.ae(_, __);

        __ = _lnkExport = $WH.ce('a');
        __.className = 'talentcalc-button-export';
        __.style.display = 'none';
        __.href = 'javascript:;';
        __.target = '_blank';
        $WH.ae(__, $WH.ct(LANG.tc_link));
        $WH.ae(_, __);

        __ = $WH.ce('div');
        __.className = 'clear';
        $WH.ae(_, __);

        $WH.ae(sidebarDivInner, _);

        _ = $WH.ce('div');
        $WH.ae(sidebarDivInner, _);

        if (_mode == MODE_DEFAULT) {
            _glyphDiv = $WH.ce('div');
            _glyphDiv.style.display = 'none';

            _ = $WH.ce('h3');
            $WH.ae(_, $WH.ct(LANG.tc_glyphs));
            $WH.ae(_glyphDiv, _);

            __ = $WH.ce('a');
            __.href = 'javascript:;';
            __.onclick = _self.resetGlyphs;
               g_addTooltip(__, LANG.tc_resetglyphs);
            $WH.ae(__, $WH.ct('[x]'));
            $WH.ae(_, __);
            _ = $WH.ce('div');
            _.className = 'talentcalc-sidebar-majorglyphs q9';
            __ = $WH.ce('b');
            $WH.ae(__, $WH.ct(g_item_glyphs[1]));
            $WH.ae(_, __);
            $WH.ae(_glyphDiv, _);
            _ = $WH.ce('div');
            _.className = 'talentcalc-sidebar-minorglyphs q9';
            __ = $WH.ce('b');
            $WH.ae(__, $WH.ct(g_item_glyphs[2]));
            $WH.ae(_, __);
            $WH.ae(_glyphDiv, _);
            _ = $WH.ce('div');
            _.className = 'clear';
            $WH.ae(_glyphDiv, _);

            var
                table = $WH.ce('table'),
                tbody = $WH.ce('tbody'),
                tr,
                th,
                td,
                icon,
                link,
                a;

            table.className = 'icontab';

            for (var y = 0; y < 3; ++y) {
                tr = $WH.ce('tr');
                for (var z = 0; z < 2; ++z) {
                    var slot = (z * 3) + y;
                    th = $WH.ce('th');
                    icon = Icon.create('inventoryslot_empty', 1, null, 'javascript:;');
                    link = Icon.getLink(icon);

                    _glyphIcons[slot] = icon;
                    $WH.ae(th, icon);
                    $WH.ae(tr, th);
                    td = $WH.ce('td');

                    a = $WH.ce('a');
                    _glyphLinks[slot] = a;
                    $WH.ae(td, a);
                    $WH.ae(tr, td);

                    a.target = link.target = '_blank';
                    a.rel = link.rel = 'np';
                    a.onmousedown = link.onmousedown = $WH.rf;
                    a.onclick = link.onclick = $WH.rf;

                    g_onClick(a, _glyphClick.bind(a, slot));
                    a.onmouseover = _showGlyphTooltip.bind(null, a, slot);
                    a.onmousemove = $WH.Tooltip.cursorUpdate;
                    a.onmouseout = $WH.Tooltip.hide;

                    g_onClick(link, _glyphClick.bind(link, slot));
                    link.onmouseover = _showGlyphTooltip.bind(null, link, slot);
                    link.onmouseout = $WH.Tooltip.hide;

                    td.oncontextmenu = $WH.rf;
                }
                $WH.ae(tbody, tr);
            }
            $WH.ae(table, tbody);
            $WH.ae(_glyphDiv, table);
            $WH.ae(sidebarDivInner, _glyphDiv);
        }

        $WH.ae(_divSidebar, sidebarDivInner);

        _ = $WH.ce('div');
        _.className = 'talentcalc-sidebar-anchor';
        $WH.ae(_, _divSidebar);

        $WH.ae(_container, _);
    }

    function _createTable(nCols, nRows, arrowMaker) {
        var
            t = $WH.ce('table'),
            tb = $WH.ce('tbody'),
            tr,
            _;

        for (var y = 0; y < nRows; ++y) {
            tr = $WH.ce('tr');

            for (var x = 0; x < nCols; ++x) {
                if (arrowMaker && y > 0) {
                    _ = $WH.ce('th');
                    _.colSpan = 2;
                    $WH.ae(tr, _);
                    break;
                }
                else {
                    var td = $WH.ce('td');
                    td.className = 'talentcalc-main-cell';
                    $WH.ae(tr, td);
                }
            }
            $WH.ae(tb, tr);
        }
        $WH.ae(t, tb);

        return t;
    }

    function _createTrees(classId) {
        var
            c = _data[classId],
            _;

        c.k = 0;

        c.div = $WH.ce('div');
        c.div.style.display = 'none';
        $WH.aef(_divMain, c.div);

        for (var tree = 0; tree < _nTrees; ++tree) {
            c[tree].k = 0;

            var
                d  = $WH.ce('div'),
                d2 = $WH.ce('div');

            d.style.backgroundRepeat = 'no-repeat';
            d.style.cssFloat = d.style.styleFloat = 'left';
            if (tree > 0) {
                d.style.borderLeft = '1px solid #404040'; // Separator
            }

            d2.style.overflow = 'hidden';
            d2.style.width = (_mode == MODE_DEFAULT ? '204px' : '244px');

            $WH.ae(d2, _createTable(4, _nTiers));

            $WH.ae(d, d2);
            $WH.ae(c.div, d);

            var
                tds = $WH.gE(d, 'td'),
                iconBg,
                afterUrl = '?' + _versionBuild;

            if (_mode == MODE_PET) {
                d.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/hunterpettalents/bg_' + (c[0].i + 1) + '.jpg' + afterUrl + ')';
                iconBg = g_staticUrl + '/images/wow/hunterpettalents/icons_' + (c[0].i + 1) + '.jpg' + afterUrl
            }
            else {
                d.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/talents/backgrounds/' + g_file_classes[classId] + '_' + (tree + 1) + '.jpg' + afterUrl + ')';
                iconBg = g_staticUrl + '/images/wow/talents/icons/' + g_file_classes[classId] + '_' + (tree + 1) + '.jpg' + afterUrl;
            }

            for (var i = c[tree].t.length - 1; i >= 0; --i) {
                var
                    talent = c[tree].t[i],
                    icon = Icon.create(iconBg, 1, null, 'javascript:;'),
                    link = Icon.getLink(icon),
                    targetTd = tds[(talent.y * 4 + talent.x + 1) - 1];

                _talentLookup[c[tree].t[i].i] = talent;

                link.rel = 'np';
                link.target = '_blank';
                link.onmousedown = $WH.rf;
                link.onclick = $WH.rf;
                g_onClick(link, _iconClick.bind(link, talent));
                link.onmouseover = _showTooltip.bind(null, link, talent);
                link.onmouseout = $WH.Tooltip.hide;

                var
                    border = $WH.ce('div'),
                    bubble = $WH.ce('div');

                $WH.ae(bubble, $WH.ct('0'));

                border.className = 'icon-border';
                bubble.className = 'icon-bubble';

                $WH.ae(icon, border);
                $WH.ae(icon, bubble);

                talent.k = 0;
                talent.i = i;
                talent.tree = tree;
                talent.classId = classId;

                talent.icon = icon;
                talent.link = link;
                talent.border = border;
                talent.bubble = bubble;

                if (!talent.hidden) {
                    $WH.ae(targetTd, icon);
                }

                if (talent.r) {
                    var
                        requiredTalent = c[tree].t[talent.r[0]],
                    dX = talent.x - requiredTalent.x,
                    dY = talent.y - requiredTalent.y,
                    l,
                    t,
                    w,
                    h,
                    arrowType = -1;

                    if (requiredTalent.links == null) {
                        requiredTalent.links = [i];
                    }
                    else {
                        requiredTalent.links.push(i);
                    }

                    if (dY > 0) {
                        if (dX == 0) {
                            arrowType = 0; // Down
                        }
                        else if (dX < 0) {
                            arrowType = 1; // Leftdown
                        }
                        else {
                            arrowType = 2; // Rightdown
                        }
                    }
                    else if (dY == 0) {
                        if (dX > 0) {
                            arrowType = 3; // Right
                        }
                        else if (dX < 0) {
                            arrowType = 4; // Left
                        }
                    }

                    if (_mode == MODE_PET) {
                        w = (Math.abs(dX) - 1) * 60;
                        h = (Math.abs(dY) - 1) * 60;
                    }
                    else {
                        w = (Math.abs(dX) - 1) * 50;
                        h = (Math.abs(dY) - 1) * 50;
                    }

                    if (_mode == MODE_PET) {
                        switch (arrowType) {
                        case 0: // Down
                            h += 27;
                            l = 21;
                            t = 6 - h;
                            break;
                        }
                    }
                    else {
                        switch (arrowType) {
                        case 0: // Down
                            h += 17;
                            l = 16;
                            t = 6 - h;
                            break;
                        case 1: // Leftdown
                            w += 36;
                            h += 42;
                            l = 16;
                            t = 6 - h;
                            break;
                        case 2: // Rightdown
                            w += 37;
                            h += 42;
                            l = -6;
                            t = 6 - h;
                            break;
                        case 3: // Right
                            w += 15;
                            l = -6;
                            t = 12;
                            break;
                        case 4: // Left
                            w += 15;
                            l = 37;
                            t = 12;
                            break;
                        }
                    }

                    var arrow = _createArrow(arrowType, w, h);
                    arrow.style.left = l + 'px';
                    arrow.style.top = t + 'px';

                    var div = $WH.ce('div');
                    div.className = 'talentcalc-arrow-anchor';
                    $WH.ae(div, arrow);

                    if (!talent.hidden) {
                        targetTd.insertBefore(div, targetTd.firstChild);
                    }

                    talent.arrow = arrow;
                }
            }
        }
    }

    function _createUpper() {
        var
            _,
            __;

        _divUpper = $WH.ce('div');
        _divUpper.className = 'talentcalc-upper';
        _divUpper.style.display = 'none';

        _ = $WH.ce('span');
        _.className = 'talentcalc-upper-class';
        _lblClass = a = $WH.ce('a');
        a.target = '_blank';
        a.style.fontWeight = 'bold';
        $WH.ae(_, a);
        $WH.ae(_, $WH.ct(' '));
        _lblSpec = $WH.ce('b');
        $WH.ae(_, _lblSpec);
        $WH.ae(_divUpper, _);

        _ = $WH.ce('span');
        _.className = 'talentcalc-upper-ptsleft';
        $WH.ae(_, $WH.ct(LANG.tc_ptsleft));
        _lblPtsLeft = $WH.ce('b');
        $WH.ae(_, _lblPtsLeft);
        $WH.ae(_divUpper, _);

        if (_mode == MODE_PET) {
            __ = _lnkBonusPoints = $WH.ce('a');
            __.href = 'javascript:;';
            __.onclick = _bonusPointsOnClick.bind(null, __);
            __.onmouseover = _bonusPointsOnMouseOver.bind(null, __);
            __.onmousemove = $WH.Tooltip.cursorUpdate;
            __.onmouseout = $WH.Tooltip.hide;
            $WH.ae(_, __);
        }

        _ = $WH.ce('span');
        _.className = 'talentcalc-upper-reqlevel';
        $WH.ae(_, $WH.ct(LANG.tc_reqlevel));
        _lblReqLevel = $WH.ce('b');
        $WH.ae(_, _lblReqLevel);

        __ = _lnkMaxLevel = $WH.ce('a');
        __.className = 'q1';
        __.href = 'javascript:;';
        __.onclick = _setLevelCapOnClick.bind(null, __);
        __.onmouseover = _setLevelCapOnMouseOver.bind(null, __);
        __.onmousemove = $WH.Tooltip.cursorUpdate;
        __.onmouseout  = $WH.Tooltip.hide;

        if (!_opt.profiler) {
            $WH.ae(_, $WH.ct(' ('));
            $WH.ae(_, __);
            $WH.ae(_, $WH.ct(')'));
        }

        $WH.ae(_divUpper, _);

        _ = $WH.ce('div');
        _.className = 'clear';
        $WH.ae(_divUpper, _);

        $WH.ae(_divWrapper, _divUpper);
    }

    function _convertBlizzToWh(blizz) {
        var wh = '';
        var values = [];

        for (var i = 0; i < blizz.length; i += 2) {
            for (var j = 0; j < 2; ++j) {
                values[j] = parseInt(blizz.substring(i + j, i + j + 1));
                if (isNaN(values[j])) {
                    values[j] = 0;
                }
            }

            wh += _encoding.charAt(values[0] * 6 + values[1]);
        }

        return wh;
    }

    function _decrementTalent(talent, refresh, _this) {
        var c = _data[talent.classId];

        if (talent.k > 0) {
            // Test 1: Ensures that talent dependencies are respected.
            if (talent.links) {
                for (i = 0; i < talent.links.length; ++i) {
                    if (c[talent.tree].t[talent.links[i]].k) {
                        return;
                    }
                }
            }

            // Test 2: Simulate that a point was removed and make sure that everything is still fine.
            talent.k--;

            var
                sum = 0;

            for (var i = 0; i < c[talent.tree].t.length; ++i) {
                var fooTalent = c[talent.tree].t[i];
                if (fooTalent.k && talent.y != fooTalent.y) {
                    if (sum < fooTalent.y * _pointsPerTier) { // Damn it.
                        talent.k++; // Reset talent.
                        return;
                    }
                }
                sum += fooTalent.k;
            }

            c[talent.tree].k--;
            i = c.k--;

            _updateTree(talent.tree, refresh, null, talent.classId);
            if (refresh) {
                _showTooltip(_this, talent);

                if (i >= _pointsToSpend) {
                    for (var tree = 0; tree < _nTrees; ++tree) {
                        _updateTree(tree, true, null, talent.classId);
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
        var notFound = ($WH.in_array(_glyphSlots[_getGlyphTypeFromSlot(slot)], slot) == -1);
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
                div   = $WH.ce("div"),
                anch  = $WH.ce("a"),
                clear = $WH.ce("div");

            dataz.push({ none: 1 });

            for (var itemId in g_glyphs) {
                dataz.push(g_glyphs[itemId]);
            }

            div.className = "listview";
            $WH.ae(dest, div);

            anch.className = "screenshotviewer-close";
            anch.href = "javascript:;";
            anch.onclick = Lightbox.hide;
            $WH.ae(anch, $WH.ce("span"));
            $WH.ae(dest, anch);

            clear.className = "clear";
            $WH.ae(dest, clear);

            lv = new Listview({
                template: "glyph",
                id: "glyphs",
                parent: div,
                data: dataz,
                customFilter: _isValidGlyph.bind(0, null),
                createNote: _createGlyphPickerNote
            });

            if ($WH.Browser.firefox) {
                $WH.aE(lv.getClipDiv(), "DOMMouseScroll", g_pickerWheel);
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
        }, 1);
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

    function _incrementTalent(talent, refresh, _this) {
        var c = _data[talent.classId];

        if (c.k < _pointsToSpend) {
            if (talent.enabled && talent.k < talent.m) {
                c.k++;
                c[talent.tree].k++;
                talent.k++;

                _updateTree(talent.tree, refresh, talent, talent.classId);

                if (refresh) {
                    _showTooltip(_this, talent);

                    if (c.k == _pointsToSpend) {
                        for (var tree = 0; tree < _nTrees; ++tree) {
                            if (tree != talent.tree) {
                                _updateTree(tree, refresh, null, talent.classId);
                            }
                        }

                    }

                    _onChange();
                }
            }
        }
        else if (_mode == MODE_PET && c.k == _pointsToSpend && !refresh) {
            _setPoints(-1, _pointsFromBonus, true);
            _incrementTalent(talent, refresh, _this); // Try again
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
                for (var n in _glyphLevels) {
                    _glyphLookup[c][n] = [];
                }
            }

            if (!_glyphLookup[c][t])
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

        $WH.st(_lblSpec,     '(' + (_mode == MODE_PET ? c.k: _info.pointsSpent.join('/')) + ')');
        $WH.st(_lblReqLevel, _info.requiredLevel ? _info.requiredLevel: '-');
        $WH.st(_lnkMaxLevel, _maxLevel);
        $WH.st(_lblPtsLeft,  _info.pointsLeft);

        if (_locked) {
            $WH.st(_lnkLock, LANG.tc_unlock);
            _lnkLock.className = 'talentcalc-button-unlock';
        }
        else {
            $WH.st(_lnkLock, LANG.tc_lock);
            _lnkLock.className = 'talentcalc-button-lock';
        }

        if (_mode == MODE_PET) {
            if (_bonusPoints) {
                $WH.st(_lnkBonusPoints, '[-]');
                _lnkBonusPoints.className = 'q10';
            } else {
                $WH.st(_lnkBonusPoints, '[+]');
                _lnkBonusPoints.className = 'q2';
            }
        }

        if (_lnkExport) {
            _lnkExport.href = (_mode == MODE_PET ? '?petcalc#' : '?talent#') + _self.getWhBuild() + (_mode == MODE_PET ? '' : ':' + _self.getWhGlyphs());
        }

        for (var tree = 0; tree < _nTrees; ++tree) {
            var span = _lblTreePoints[tree].firstChild.childNodes[1];
            $WH.st(span, ' (' + c[tree].k + ')');
        }

        if (_onChangeCallback) {
            _onChangeCallback(_self, _info, c);
        }
    }

    function _onClassChange() {
        $WH.st(_lblClass, _referenceArray[_currentClass]);
        if (_mode == MODE_PET) {
            _lblClass.href = '?pet=' + _currentClass;
            _updateModel(_currentClass);

            _divModel.style.display = '';
        }
        else {
            _lblClass.href = '?class=' + _currentClass;
            _lblClass.className = 'c' + _currentClass;
        }

        if (_nClassChanges == 0) {  // First time
            _controlsDiv.style.display = '';
            _lnkSummary.style.display  = '';

            if (_lnkExport) {
                _lnkExport.style.display = '';
            }

            if (_glyphDiv) {
                _glyphDiv.style.display = '';
            }

            _divUpper.style.display   = '';
            _divLower.style.display   = '';
        }

        var c = _data[_currentClass];

        for (var tree = 0; tree < _nTrees; ++tree) {
            var span = _lblTreePoints[tree].firstChild.childNodes[0];
            if (_mode == MODE_DEFAULT) {
                span.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/talents/trees/' + g_file_classes[_currentClass] + '_' + (tree + 1) + '.gif)';
            }
            $WH.st(span, c[tree].n);
        }

        _afterCapDecrease();
        _refreshAll(_currentClass);
        _onChange();

        ++_nClassChanges;
    }

    function _parseBlizzBuild(build, classId) {
        var c = _data[classId];

        var
            tree   = 0,
            talent = 0;

        var fixTalent = [];

        // Fix for extra talent digit in blizzard mage builds
        if (classId == 8 && build.length == 62)
            build = build.substr(0,15) + build.substr(16);

        for (var i = 0; i < build.length; ++i) {
            var n = Math.min(parseInt(build.charAt(i)), c[tree].t[talent].m);
            if (isNaN(n)) {
                continue;
            }

            for (var k = 0; k < n; ++k) {
                _incrementTalent(c[tree].t[talent]);
            }

            for (var f = 0; f < fixTalent.length; ++f) {
                if (fixTalent[f][0].enabled && fixTalent[f][1] > 0) {
                    for (var k = 0; k < fixTalent[f][1]; ++k) {
                        _incrementTalent(fixTalent[f][0]);
                    }

                    fixTalent[f][1] = 0;
                }
            }

            if (c[tree].t[talent].k < n) {
                fixTalent.push([c[tree].t[talent], n - c[tree].t[talent].k]);
            }

            if (++talent > c[tree].t.length - 1) {
                talent = 0;
                if (++tree > _nTrees - 1) {
                    break;
                }
            }
        }
    }

    function _parseBlizzGlyphs(glyphs) {
        var
            glyphIds   = ('' + glyphs).split(':', _nGlyphs),
            nextOfType = {};

        for (var i = 0, len = glyphIds.length; i < len && i < _nGlyphs; ++i) {
            var
                glyphId = glyphIds[i],
                glyph   = g_glyphs[glyphId];

            if (glyph) {
                var slot = -1;

                if (nextOfType[glyph.type] == null) {
                    nextOfType[glyph.type] = 0;
                }

                if (nextOfType[glyph.type] < _glyphSlots[glyph.type].length) {
                    slot = _glyphSlots[glyph.type][nextOfType[glyph.type]];
                    ++nextOfType[glyph.type];
                }

                if (slot != -1) {
                    _addGlyph(slot, glyphId, true);
                }
            }
            else {
                ++nextOfType[_getGlyphTypeFromSlot(i)];
            }
        }
    }

    function _parseWhBuild(build, classId) {
        var c = _data[classId];

        var
            tree   = 0,
            talent = 0;

        var values = [];
        var fixTalent = [];

        for (var i = 0; i < build.length; ++i) {
            var ch = build.charAt(i);
            if (ch != _newTree) {
                var n = _encoding.indexOf(ch);
                if (n < 0) {
                    continue;
                }

                values[1] = n % 6;
                values[0] = (n - values[1]) / 6;

                for (var j = 0; j < 2; ++j) {
                    n = Math.min(values[j], c[tree].t[talent].m);

                    for (var k = 0; k < n; ++k) {
                        _incrementTalent(c[tree].t[talent]);
                    }

                    for (var f = 0; f < fixTalent.length; ++f) {
                        if (fixTalent[f][0].enabled && fixTalent[f][1] > 0) {
                            for (var k = 0; k < fixTalent[f][1]; ++k) {
                                _incrementTalent(fixTalent[f][0]);
                            }

                            fixTalent[f][1] = 0;
                        }
                    }

                    if (c[tree].t[talent].k < n) {
                        fixTalent.push([c[tree].t[talent], n - c[tree].t[talent].k]);
                    }

                    if (++talent >= c[tree].t.length) {
                        break;
                    }
                }
            }

            if (talent >= c[tree].t.length || ch == _newTree) {
                talent = 0;

                if (++tree > _nTrees - 1) {
                    return;
                }
            }
        }
    }

    function _parseWhGlyphs(glyphs) {
        var
            slot = 0;

        for (var i = 0, len = glyphs.length; i < len && i < _nGlyphs; ++i) {
            var c = glyphs.charAt(i);
            if (c == 'Z') {
                slot = 3;
                continue;
            }
            _addGlyph(slot, _glyphLookup[_currentClass][_getGlyphTypeFromSlot(slot)][_encoding.indexOf(c)], true);
            ++slot;
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

    function _registerClass(classId, data) {
        if (_data[classId] == null) {
            data.n = classId;
            _data[classId] = data;

            var c = _data[classId];

            c.glyphs = [];
            c.glyphItems = {};

            _createTrees(classId);

            if (_build && _build.classId == classId) {
                for (var tree = 0; tree < _nTrees; ++tree) {
                    _updateTree(tree, false, null, classId);
                }

                if (_build.wh || _build.blizz) {
                    _locked = true;

                    if (_build.wh) {
                        _parseWhBuild(_build.wh, classId);
                    }
                    else {
                        _parseBlizzBuild(_build.blizz, classId);
                    }
                }
            }
            else {
                _locked = false;
            }

            _build = null;

            if (_glyphs && _glyphs.classId == classId) {
                if (_glyphs.wh) {
                    _parseWhGlyphs(_glyphs.wh);
                }
                else {
                    _parseBlizzGlyphs(_glyphs.blizz);
                }
            }
            _glyphs = null;

            if (classId == _currentClass) {
                _onClassChange();

                c.div.style.display = '';

                for (var tree = 0; tree < _nTrees; ++tree) {
                    _updateTree(tree, true, null, classId);
                }
            }
        }
    }

    function _removeGlyph(slot, stealth) {
        var c = _data[_currentClass];

        if (c.glyphs[slot]) {
            c.glyphItems[c.glyphs[slot]] = 0;
            c.glyphs[slot] = 0;

            if (!stealth) {
                _updateGlyph(slot);
                _onChange();
            }

            return true;
        }
    }

    function _resetAll(classId) {
        _resetBuild(classId);
        _resetGlyphs();
        _refreshAll(classId);
    }

    function _resetBuild(classId) {
        if (_mode == MODE_PET) {
            _setPoints(-1, 0, true);
        }

        for (var tree = 0; tree < _nTrees; ++tree) {
            _resetTree(tree, classId, false);
        }
    }

    function _resetGlyphs(refresh) {
        var c = _data[_currentClass];
        if (!c) {
            return;
        }

        for (var slot = 0; slot < _nGlyphs; ++slot) {
            _removeGlyph(slot, !refresh);
        }

        _refreshGlyphs();
    }

    function _resetTree(tree, classId, refresh) {
        var c = _data[classId];
        var i;

        for (i = 0; i < c[tree].t.length; ++i) {
            c[tree].t[i].k = 0;
        }

        i = (c.k < _pointsToSpend);
        c.k -= c[tree].k;
        c[tree].k = 0;

        if (refresh) {
            if (i) {
                _updateTree(tree, true, null, classId)
            }
            else {
                for (tree = 0; tree < _nTrees; ++tree) {
                    _updateTree(tree, true, null, classId);
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

    function _setBlizzBuild(classId, build) {
        if (_referenceArray[classId] == null) {
            return;
        }

        if (!build) {
            return;
        }

        _locked = true;

        if (!_firstBuild) {
            _firstBuild = { classId: classId, blizz: build };
            _lnkRestore.style.display = "";
        }
        if (_data[classId]) {
            _resetBuild(classId);
            _refreshAll(classId);
            _parseBlizzBuild(build, classId);
            _refreshAll(classId);
        }
        else {
            _build = { classId: classId, blizz: build };
        }

        if (!_setClass(classId)) {
            _onChange();
        }
    }

    function _setBlizzGlyphs(glyphs) {
        if (!glyphs) {
            return;
        }

        if (_data[_currentClass]) {
            _resetGlyphs();
            _parseBlizzGlyphs(glyphs);
            _refreshGlyphs();
            _onChange();
        }
        else {
            _glyphs = { classId: _currentClass, blizz: glyphs };
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
                    $WH.g_ajaxIshRequest('?data=talents&class=' + classId + '&locale=' + Locale.getId() + '&t=' + g_dataKey + '&' + _versionBuild);
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

            for (var j = 0, len2 = _glyphLevels[glyphType].length; j < len2; ++j) {
                if (level >= _glyphLevels[glyphType][j]) {
                    _glyphSlots[glyphType].push(slot);
                    _nGlyphs++;
                }

                ++slot;
            }
        }
    }

    function _setWhBuild(build) {
        if (!build) {
            return;
        }

        var
            unalteredBuild = build,
            valid = false,
            classId;

            if (_mode == MODE_PET) {
            var n = _encoding.indexOf(build.charAt(0));
            if (n >= 0 && n <= 4) {
                var d = _encoding.indexOf(build.charAt(1));

                if (d % 2 == 1) {
                    _setPoints(-1, _pointsFromBonus, true);
                    --d;
                }
                else {
                    _setPoints(-1, 0, true);
                }

                classId = n * 10 + (d / 2);
                if (g_pet_families[classId] != null) {
                    build = build.substr(2);
                    valid = true;
                }
            }
        }
        else {
            var n = _encoding.indexOf(build.charAt(0));
            if (n >= 0 && n <= 27) {
                var
                    d = n % 3,
                    classId = (n - d) / 3;


                classId = _getClassIdFromOldId(classId);
                if (classId != null) {
                    build = build.substr(1);
                    valid = true;
                }
            }
        }

        if (valid) {
            if (build.length) {
                _locked = true;

                if (!_firstBuild) {
                    _firstBuild = { wh: unalteredBuild };
                    _lnkRestore.style.display = '';
                }
            }

            if (_data[classId]) {
                _resetBuild(classId);
                _parseWhBuild(build, classId);
                _refreshAll(classId);
            }
            else {
                _build = { classId: classId, wh: build };
            }

            if (!_setClass(classId)) {
                _onChange();
            }

            return classId;
        }
    }

    function _setWhGlyphs(glyphs) {
        if (!glyphs) {
            return;
        }

        if (!_firstGlyphs) {
            _firstGlyphs = { wh: glyphs };
        }

        if (_data[_currentClass]) {
            _resetGlyphs();
            _parseWhGlyphs(glyphs);
            _refreshGlyphs();
            _onChange();
        }
        else {
            _glyphs = { classId: _currentClass, wh: glyphs };
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
            lower += '<span class="q10">' + $WH.sprintf(LANG.tc_lockgly, _getGlyphSlotLevel(slot)) + '</span>';
        }

        if (glyph && _this.parentNode.className.indexOf("icon") != 0) {
            $WH.Tooltip.setIcon(glyph.icon);
        }
        else {
            $WH.Tooltip.setIcon(null);
        }

        $WH.Tooltip.show(_this, "<table><tr><td>" + upper + "</td></tr></table><table><tr><td>" + lower + "</td></tr></table>")
    }

    function _showTooltip(_this, talent) {
        var
            c = _data[talent.classId],
            buffer = '<table><tr><td><b>';

        if (talent.z) {
            buffer += '<span style="float: right" class="q0">' + talent.z + '</span>';
        }

        buffer += talent.n + '</b><br />' + $WH.sprintf(LANG.tc_rank, talent.k, talent.m) + '<br />';

        if (talent.r) {
            if (c[talent.tree].t[talent.r[0]].k < talent.r[1]) {
                buffer += '<span class="q10">';
                buffer += $WH.sprintf(LANG[talent.r[1] == 1 ? 'tc_prereq' : 'tc_prereqpl'], talent.r[1], c[talent.tree].t[talent.r[0]].n);
                buffer += '</span><br />';
            }
        }

        if (c[talent.tree].k < talent.y * _pointsPerTier) {
            buffer += '<span class="q10">' + $WH.sprintf(LANG.tc_tier, (talent.y * _pointsPerTier), c[talent.tree].n) + '</span><br />';
        }

        if (talent.t && talent.t.length >= 1) {
            buffer += talent.t[0];
        }

        buffer += '</td></tr></table><table><tr><td>';

        if (talent.t && talent.t.length > 1) {
            buffer += talent.t[1] + '<br />';
        }

        buffer += '<span class="q">' + _getTalentDescription(talent) + '</span><br />';

        if (_locked) {

        }
        else if (talent.enabled) {
            if (!talent.k) {
                buffer += '<span class="q2">' + LANG.tc_learn + '</span><br />';
            }
            else if (talent.k == talent.m) {
                buffer += '<span class="q10">' + LANG.tc_unlearn + '</span><br />';
            }

            if (talent.k && talent.k < talent.m) {
                buffer += '<br />' + LANG.tc_nextrank + '<br /><span class="q">' + _getTalentDescription(talent, 1) + '</span><br />';
            }
        }

        buffer += '</td></tr></table>';

        $WH.Tooltip.show(_this, $WH.g_setTooltipLevel(buffer, _getRequiredLevel()));
    }

    function _simplifyGlyphName(name) {
        var str;

        switch (Locale.getId()) {
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

            $WH.st(a, _simplifyGlyphName(glyph.name));
            a.className = 'q1';

            return true;
        }
        else {
            Icon.setTexture(icon, 1, 'inventoryslot_empty');
            a.href = link.href = 'javascript:;';

            $WH.st(a, (!locked ? LANG.tc_empty : LANG.tc_locked));
            a.className = 'q0';

            return false;
        }
    }

    function _updateTree(tree, refresh, talent, classId) {
        var c = _data[classId];

        var highestLevel;
        var bc;

        if (!talent || c.k == _pointsToSpend) {
            bc = 0;
            highestLevel = _pointsToSpend - 21;
        }
        else {
            bc = talent.i;
            highestLevel = Math.floor(c[tree].k / _pointsPerTier) * _pointsPerTier + 5;
        }
        if (talent != null && talent.links != null) {
            for (var i = 0, bf = talent.links.length; i < bf; ++i) {
                if (bc > talent.links[i]) {
                    bc = talent.links[i];
                }
            }
        }

        for (var i = bc; i < c[tree].t.length; ++i) {
            talent = c[tree].t[i];

            if (c.k == _pointsToSpend && !talent.k) {
                talent.enabled = 0;
            }
            else {
                if (c[tree].k >= talent.y * _pointsPerTier) {
                    if (talent.r) {
                        if (c[tree].t[talent.r[0]].k >= talent.r[1]) {
                            talent.enabled = 1;
                        }
                        else {
                            talent.enabled = 0;
                        }
                    }
                    else {
                        talent.enabled = 1;
                    }
                }
                else {
                    talent.enabled = 0;
                }
            }

            if (refresh) {
                if (talent.enabled && (!_locked || talent.k)) {
                    if (talent.k == talent.m) {
                        talent.border.style.backgroundPosition = '-42px 0';
                        talent.bubble.style.color = '#E7BA00';
                    }
                    else {
                        talent.border.style.backgroundPosition = '-84px 0';
                        talent.bubble.style.color = '#17FD17';
                    }

                    Icon.moveTexture(talent.icon, 1, i, 0);
                    talent.link.className = 'bubbly';
                    talent.bubble.style.visibility = 'visible';

                    if (talent.r) {
                        var _ = talent.arrow.firstChild;
                        if (_.className.charAt(_.className.length - 1) != '2') {
                            _.className += '2';
                        }

                        }
                }
                else {
                    talent.border.style.backgroundPosition = '0 0';
                    Icon.moveTexture(talent.icon, 1, i, 1);
                    talent.link.className = '';
                    talent.bubble.style.visibility = 'hidden';

                    if (talent.r) {
                        var _ = talent.arrow.firstChild;
                        if (_.className.charAt(_.className.length - 1) == '2') {
                            _.className = _.className.substr(0, _.className.length - 1);
                        }
                    }
                }

                talent.bubble.firstChild.nodeValue = talent.k;
                talent.link.href = '?spell=' + talent.s[Math.max(0, talent.k - 1)];
            }
        }
    }

    function _updateModel(classId, npcId) {
        var swfUrl = g_staticUrl;                           // "http://static.wowhead.com"

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

        if (!g_pets[c.npcId] || g_pets[c.npcId].family != classId) {
            var models = [];

            for (var i in g_pets) {
                if (g_pets[i].family == classId) {
                    models.push(g_pets[i].id);
                }
            }

            if (!models.length) {
                return;
            }

            c.npcId = models[Math.floor(Math.random() * models.length)];
        }

        var flashVars = {
            model: g_pets[c.npcId].displayId,
            modelType: 8,
            contentPath: swfUrl + '/modelviewer/',
            blur: ($WH.OS.mac ? '0' : '1')
        };

        var params = {
            quality: 'high',
            allowscriptaccess: 'always',
            allowfullscreen: true,
            menu: false,
            bgcolor: '#181818',
            wmode: 'direct'
        };

        var attributes = {};

        swfobject.embedSWF(
            swfUrl + '/modelviewer/ZAMviewerfp11.swf',
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

                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                var
                    icon = Icon.create(glyph.icon, 0, null, '?item=' + glyph.id),
                    link = Icon.getLink(icon);
                $WH.ae(i, icon);
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                link.onclick = $WH.rf;

                var a = $WH.ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + glyph.id;

                $WH.ae(a, $WH.ct($WowheadTalentCalculator.simplifyGlyphName(glyph.name)));

                td.style.whiteSpace = 'nowrap';
                $WH.ae(td, a);

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

                return $WH.strcmp(a.name, b.name);
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

                var d = $WH.ce('div');
                d.className = 'small crop';
                td.title = glyph.description;
                $WH.ae(d, $WH.ct(glyph.description));
                $WH.ae(td, d);
            }
        },
        {
            id: 'level',
            type: 'text',
            align: 'center',
            value: 'level',
            compute: function(glyph, td) {
                if (glyph.none) {
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
                    $WH.ee(tr);

                    tr.onclick = $WowheadTalentCalculator.addGlyph.bind(null, 0);
                    td.colSpan          = 5;
                    td.style.fontWeight = 'bold';
                    td.style.textAlign  = 'center';

                    return LANG.dash + LANG.tc_nonegly + LANG.dash;
                }

                if (glyph.skill) {
                    td.className = 'small q1';
                    td.style.whiteSpace = 'nowrap';

                    var a = $WH.ce('a');
                    a.style.fontFamily = 'Verdana, sans-serif';
                    a.href = '?skill=' + glyph.skill;

                    $WH.ae(a, $WH.ct(g_spell_skills[glyph.skill]));
                    $WH.ae(td, a);
                }
            }
        }
    ]
};
