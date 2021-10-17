
var ShowOnMap = function(data, mapper) {
    var self = this;
    self.data = data;

    if (mapper == null) {
        mapper = myMapper;
    }

    self.mapper = mapper;

    self._legend = null;
    self._legendLabel = null;
    self._legendContents = null;
    self._legendHorde = null;
    self._legendAlliance = null;
    self._menu = [];

    self.construct();
};

ShowOnMap.prototype.onExpand = function() {
    this.expanded = true;
    location.replace(this.pound + '.map');
};

ShowOnMap.prototype.onCollapse = function() {
    this.expanded = false;
    var hash = g_getHash();
    if (hash.indexOf('#show') == 0 && hash.indexOf('.map') > 0) {
        this.pound = hash.substr(0, hash.indexOf('.map'));
        location.replace(this.pound);
    }
}

ShowOnMap.prototype.construct = function() {
    if (!this.data) {
        return;
    }

    var d = $WH.ge('som-generic');

    var cmpFunc = function(a, b) {
        return $WH.strcmp(a[1], b[1]);
    };

    var allianceMenu = [];
    var hordeMenu = [];
    for (var i in this.data) {
        if (this.data[i].length === undefined) {
            var submenu = [];
            var allCoords = [];
            var allLegend = {};
            var group = this.data[i];
            for (var p in group) {
                var hasPaths = false;
                for (var n = 0, len = group[p].length; n < len; ++n) {
                    if (group[p][n].paths) {
                        hasPaths = true;
                        break;
                    }
                }

                var i2 = g_urlize(p);
                if (group[p][0].name_enus !== undefined) {
                    i2 = g_urlize(group[p][0].name_enus);
                }

                var coords = {};
                var legend = {};
                var entry = [i2];
                //pin.list.key = i;
                var combined = ShowOnMap.combinePins(group[p], false, hasPaths);
                var pins = combined[0];
                var nPins = combined[1];
                var nCoords = 0;
                for (var n = 0; n < pins.length; ++n) {
                    var pin = pins[n];
                    var tooltip = ShowOnMap.buildTooltip(pin.list);
                    var url = tooltip[2];
                    var m = null;
                    if (url == 'javascript:;') {
                        m = tooltip[3];
                    }

                    if (pin.list.length == 1) {
                        url = (g_types[pin.list[0].type] && pin.list[0].id ? '?' + g_types[pin.list[0].type] + '=' + pin.list[0].id : '');
                    }
                    if (i == 'rare' || i == 'herb' || i == 'vein' || i == 'pool') {
                        tooltip[1] = submenu.length + 1;
                    }
                    if (coords[pin.level] == undefined) {
                        coords[pin.level] = [];
                    }
                    coords[pin.level].push([
                        pin.coord[0],
                        pin.coord[1],
                        {
                            url:   url,
                            label: tooltip[0],
                            menu:  m,
                            type:  tooltip[1],
                            lines: pin.list[0].paths
                        }
                    ]);
                    nCoords++;
                }
                if (nCoords > 0) {
                    var url = (g_types[group[p][0].type] && group[p][0].id ? '?' + g_types[group[p][0].type] + '=' + group[p][0].id : '');
                    // legend[submenu.length+1] = [p, url]; aowow - switch to numeric groupIdx to support mail
                    // entry.push(p + $WH.sprintf(LANG.qty, nPins));
                    legend[submenu.length+1] = [group[p][0].name, url];
                    entry.push(group[p][0].name + $WH.sprintf(LANG.qty, nPins));
                    entry.push(this.showStuff.bind(this, coords, [i, i2], legend));
                    submenu.push(entry);
                    for (var l in coords) {
                        if (allCoords[l]) {
                            allCoords[l] = allCoords[l].concat(coords[l]);
                        }
                        else {
                            allCoords[l] = coords[l];
                        }
                    }
                    allLegend[submenu.length] = [group[p][0].name, url];
                }
            }
            if (submenu.length > 0) {
                submenu.sort(cmpFunc);
                var entry = [i, LANG.som[i], this.showStuff.bind(this, allCoords, [i], allLegend), submenu];
                this._menu.push(entry);
            }
        }
        else {
            if (this.data[i].length == 0) {
                continue;
            }

            var hasPaths = false;
            for (var n = 0, len = this.data[i].length; n < len; ++n) {
                if (this.data[i][n].paths) {
                    hasPaths = true;
                    break;
                }
            }

            var entry = [i];
            var coords = {};
            var hordeCoords = {}, hordeDailyCoords = {};
            var allianceCoords = {}, allianceDailyCoords = {};
            var legend = {};
            var hordeLegend = {};
            var allianceLegend = {};
            var combined = ShowOnMap.combinePins(this.data[i], false, hasPaths);
            var nPins = combined[1];
            var nCoords = 0, nHordeCoords = 0, nHordeDailyCoords = 0, nAllianceCoords = 0, nAllianceDailyCoords = 0;

            var processPins = function(id, pins) {
                for (var n = 0; n < pins.length; ++n) {
                    var pin = pins[n];
                    var idx = id;
                    pin.list.key = i;
                    var tooltip = ShowOnMap.buildTooltip(pin.list, (id == 'hordedailyquests' || id == 'alliancedailyquests') ? true : false);
                    var url = tooltip[2];
                    var m = null;
                    if (url == 'javascript:;') {
                        m = tooltip[3];
                    }

                    if (pin.list.length == 1) {
                        url = (g_types[pin.list[0].type] && pin.list[0].id ? '?' + g_types[pin.list[0].type] + '=' + pin.list[0].id : '');
                    }
                    var coord = [
                        pin.coord[0],
                        pin.coord[1],
                        {
                            url:   url,
                            label: tooltip[0],
                            menu:  m,
                            type:  tooltip[1],
                            lines: pin.list[0].paths
                        }
                    ];
                    if (coords[pin.level] == undefined) {
                        coords[pin.level] = [];
                        hordeCoords[pin.level] = [];
                        hordeDailyCoords[pin.level] = [];
                        allianceCoords[pin.level] = [];
                        allianceDailyCoords[pin.level] = [];
                    }

                    if (idx != 'rare' && idx != 'spirithealer' && idx != 'book' && idx != 'forge' && idx != 'anvil' && idx != 'hordequests' && idx != 'alliancequests' && idx != 'hordedailyquests' && idx != 'alliancedailyquests' && idx != 'boss' && idx != 'areatrigger') { // aowow - areatrigger is custom
                        if (tooltip[1] == 2 || tooltip[1] == 0) {
                            if (tooltip[1] == 2) {
                                hordeLegend[2] = [LANG.som_legend_horde, null];
                            }
                            else {
                                hordeLegend[0] = [LANG.som_legend_neutral, null];
                            }
                            hordeCoords[pin.level].push(coord);
                            nHordeCoords++;
                        }
                        if (tooltip[1] == 3 || tooltip[1] == 0) {
                            if (tooltip[1] == 3) {
                                allianceLegend[3] = [LANG.som_legend_alliance, null];
                            }
                            else {
                                allianceLegend[0] = [LANG.som_legend_neutral, null];
                            }
                            allianceCoords[pin.level].push(coord);
                            nAllianceCoords++;
                        }
                    }
                    else if (idx == 'hordequests') {
                        idx = 'quest';
                        if (tooltip[1] == 2) {
                            hordeLegend[2] = [LANG.som_legend_horde, null];
                        }
                        else {
                            hordeLegend[0] = [LANG.som_legend_neutral, null];
                        }
                        hordeCoords[pin.level].push(coord);
                        nHordeCoords++;
                    }
                    else if (idx == 'hordedailyquests') {
                        idx = 'daily';
                        if (tooltip[1] == 2) {
                            hordeLegend[2] = [LANG.som_legend_horde, null];
                        }
                        else {
                            hordeLegend[0] = [LANG.som_legend_neutral, null];
                        }
                        hordeDailyCoords[pin.level].push(coord);
                        nHordeDailyCoords++;
                    }
                    else if  (idx == 'alliancequests') {
                        idx = 'quest';
                        if (tooltip[1] == 3) {
                            allianceLegend[3] = [LANG.som_legend_alliance, null];
                        }
                        else {
                            allianceLegend[0] = [LANG.som_legend_neutral, null];
                        }
                        allianceCoords[pin.level].push(coord);
                        nAllianceCoords++;
                    }
                    else if (idx == 'alliancedailyquests') {
                        idx = 'daily';
                        if (tooltip[1] == 3) {
                            allianceLegend[3] = [LANG.som_legend_alliance, null];
                        }
                        else {
                            allianceLegend[0] = [LANG.som_legend_neutral, null];
                        }
                        allianceDailyCoords[pin.level].push(coord);
                        nAllianceDailyCoords++;
                    }
                    else {
                        coords[pin.level].push(coord);
                        nCoords++;
                    }
                }
                return idx;
            };
            var idx = processPins(i, combined[0]);
            if (i == 'alliancequests') {
                var allianceDailyPins = ShowOnMap.combinePins(this.data[i], true);
                if (allianceDailyPins[1] > 0) {
                    processPins('alliancedailyquests', allianceDailyPins[0]);
                }
            }
            else if (i == 'hordequests') {
                var hordeDailyPins = ShowOnMap.combinePins(this.data[i], true);
                if (hordeDailyPins[1] > 0) {
                    processPins('hordedailyquests', hordeDailyPins[0]);
                }
            }

            if (idx == 'rare') {
                nPins = this.data[idx].length;
            }
            else if (idx == 'forge' || idx == 'anvil') {
                nPins = combined[0].length;
            }

            if (nCoords > 0) {
                var entry = [idx, LANG.som[idx] + $WH.sprintf(LANG.qty, nPins), this.showStuff.bind(this, coords, [idx], legend)];
                this._menu.push(entry);
            }
            if (nHordeCoords > 0) {
                var entry = [idx, LANG.som[idx] + $WH.sprintf(LANG.qty, nHordeCoords), this.showStuff.bind(this, hordeCoords, ['horde', idx], hordeLegend), null];
                if (idx == 'quest') {
                    entry.push({ tinyIcon: 'quest_start' });

                    if (nHordeDailyCoords > 0) {
                        hordeMenu.push(entry);
                        entry = ['daily', LANG.som.daily + $WH.sprintf(LANG.qty, nHordeDailyCoords), this.showStuff.bind(this, hordeDailyCoords, ['horde', 'daily'], hordeLegend), null, { tinyIcon: 'quest_start_daily' }];
                    }
                }
                hordeMenu.push(entry);
            }
            if (nAllianceCoords > 0) {
                var entry = [idx, LANG.som[idx] + $WH.sprintf(LANG.qty, nAllianceCoords), this.showStuff.bind(this, allianceCoords, ['alliance', idx], allianceLegend), null];
                if (idx == 'quest') {
                    entry.push({ tinyIcon: 'quest_start' });

                    if (nAllianceDailyCoords > 0) {
                        allianceMenu.push(entry);
                        entry = ['daily', LANG.som.daily + $WH.sprintf(LANG.qty, nAllianceDailyCoords), this.showStuff.bind(this, allianceDailyCoords, ['alliance', 'daily'], allianceLegend), null, { tinyIcon: 'quest_start_daily' }];
                    }
                }
                allianceMenu.push(entry);
            }
        }
    }

    allianceMenu.sort(cmpFunc);
    hordeMenu.sort(cmpFunc);
    this._menu.sort(cmpFunc);

    if (hordeMenu.length > 0) {
        this._menu.unshift(['horde', LANG.som.horde, '', hordeMenu, { tinyIcon: 'side_horde' }]);
    }
    if (allianceMenu.length > 0) {
        this._menu.unshift(['alliance', LANG.som.alliance, '', allianceMenu, { tinyIcon: 'side_alliance' }]);
    }

    var nothing = [-1, LANG.som_nothing, this.showStuff.bind(this, [], [-1], {})];
    nothing.checked = true;
    this._menu.push(nothing);

    var redButton = RedButton.create(LANG.showonmap, true);
    redButton.className += ' mapper-som-button';
    Menu.add(redButton, this._menu, { showAtCursor: true });
    $WH.ae(d, redButton);

    var clear;
    if (!this._legend) {
        this._legend = $WH.ce('div');
        this._legend.className = 'mapper-legend';
        this._legend.style.display = 'none';

        var d2 = $WH.ce('div');
        d2.className = 'mapper-legend-container';

        this._legendLabel = $WH.ce('b')
        $WH.ae(this._legendLabel, $WH.ct(LANG.som_legend));
        $WH.ae(d2, this._legendLabel);

        this._legendContents = $WH.ce('div');
        this._legendContents.style.cssFloat = 'right';
        $WH.ae(d2, this._legendContents);

        var clear = $WH.ce('div');
        clear.style.clear = 'right';
        $WH.ae(d2, clear);

        $WH.ae(this._legend, d2);
    }
    $WH.ae(d, this._legend);

    var clear = $WH.ce('div');
    clear.style.clear = 'left';
    $WH.ae(d, clear);

    var components = [];
    var hash = g_getHash();
    if (hash.indexOf('#show:') != -1) {
        components = hash.split('.');
    }
    else if (this.data.instance) {
        components.push('#show:boss');
    }

    if (components.length > 0) {
        if (components.length == 2 && components[1] == 'map') {
            this.expanded = true;
            this.mapper.toggleZoom();
        }

        var path = components[0].split(':');
        if (path.length < 2) {
            return;
        }

        var menu = this._menu;
        for (var n = 1; n < path.length; ++n) {
            var maxToRecurse = path.length - 1;
            for (var p = 0; p < menu.length; ++p) {
                if (menu[p][0] == path[n]) {
                    if (menu[p][3] && n < maxToRecurse) {
                        menu = menu[p][3];
                    }
                    else {
                        menu = menu[p];
                    }
                    break;
                }
            }
        }
        if (menu && menu[2] && menu[2].constructor && menu[2].call && menu[2].apply) {      // aowow: check if menu[2] is function
            menu[2]();
        }
    }
};

ShowOnMap.prototype.setLegend = function(info) {
    $WH.ee(this._legendContents);

    var div = $WH.ce('div');
    var count = 0;
    for (var i in info) {
        var sp = $WH.ce('span');
        sp.className = 'mapper-pin mapper-pin-' + i + ' mapper-legend-pin';

        if (info[i][1]) {
            var a = $WH.ce('a');
            a.href = info[i][1];
            $WH.ae(a, $WH.ct(info[i][0]));
            $WH.ae(sp, a);
        }
        else {
            $WH.ae(sp, $WH.ct(info[i][0]));
        }

        $WH.ae(div, sp);
        if ((++count) % 4 == 0) {
            $WH.ae(div, $WH.ce('br'));
        }
    }

    $WH.ae(this._legendContents, div);
};

ShowOnMap.prototype.showStuff = function(coords, path, legendInfo) {
    this.mapper.update({zone: g_pageInfo.id, coords: coords, preservelevel: true});
    this.setLegend(legendInfo);
    this.checkMenu(path);

    if (path.length == 1 && path[0] == -1) {
        this.pound = '';
        location.replace('#.');
        return;
    }

    this.pound = '#show:' + path.join(':');
    if (this.pound != '#show:boss') {
        location.replace(this.pound + (this.expanded ? '.map' : ''));
    }
};

ShowOnMap.prototype.clearChecks = function(menu) {
    for (var i = 0; i < menu.length; ++i) {
        menu[i].checked = false;
        if (menu[i][3] && menu[i][3].length > 0) {
            this.clearChecks(menu[i][3]);
        }
    }
    this._legend.style.display = 'none';
};

ShowOnMap.prototype.checkMenu = function(path) {
    this.clearChecks(this._menu);
    if (path[0] != -1) {
        this._legend.style.display = 'block';
    }
    else {
        this._legend.style.display = 'none';
    }

    var menu = this._menu;
    var label = [];
    for (var i = 0; i < path.length; ++i) {
        for (var j = 0; j < menu.length; ++j) {
            if (menu[j][0] == path[i]) {
                menu[j].checked = true;
                label.push([menu[j][0], menu[j][1]]);
                menu = menu[j][3];
                break;
            }
        }
    }

    var maxLabel = label.length - 1;
    var str = '';
    var singlePinOptions = { 'rare': true, 'herb': true, 'vein': true, 'pool': true };
    for (var i = 0; i < label.length; ++i) {
        if (i > 0 && singlePinOptions[label[0][0]]) {
            var span = $('span', this._legendContents);
            span.removeClass('mapper-legend-pin');
            span.append($('<b/>', { text: ' ' + label[i][1].substr(label[i][1].lastIndexOf('(')) }));
        }
        else {
            if (i == maxLabel) {
                str += '<span>';
            }
            else {
                str += '<span class="breadcrumb-arrow">';
            }
            str += label[i][1] + '</span>';
        }
    }
    this._legendLabel.innerHTML = str;
};

ShowOnMap.combinePins = function(pins, dailyOnly, hasPaths) {
    var combined = {};
    var coord = null, tmpCoord = null;
    var x, y;
    var nPins = 0;

    var getCoord = function(coord, tight) {
        var x = Math.floor(coord[0]);
        var y = Math.floor(coord[1]);
        if (!tight) {
            if (x % 2 == 1) {
                x += 1;
            }
            if (y % 2 == 1) {
                y += 1;
            }
        }

        if (combined[x] === undefined) {
            combined[x] = {};
        }
        if (combined[x][y] === undefined) {
            combined[x][y] = [];
        }

        return [x, y];
    }

    for (var p = 0; p < pins.length; ++p) {
        var pin = pins[p];
        if (dailyOnly) {
            if (!pin.quests) {
                continue;
            }
            var noDaily = true;
            for (var n = 0; n < pin.quests.length; ++n) {
                if (pin.quests[n].daily) {
                    noDaily = false;
                    break;
                }
            }
            if (noDaily) {
                continue;
            }
        }
        else if (hasPaths) {
            // Don't combine pins that have paths
            coord = getCoord([pin.id, 0], true);
            x = coord[0]; y = coord[1];
            var newPin = $WH.dO(pin);
            newPin.coord = pin.coords[0];

            combined[x][y].push(newPin);
            nPins++;

            continue;
        }

        if (pin.point == 'start' || pin.point == 'end') {
            coord = getCoord(pin.coord);
            x = coord[0]; y = coord[1];
            if (combined[x][y].length > 3) {
                var temp = combined[x][y];
                combined[x][y] = [];
                for (var i = 0; i < temp.length; ++i) {
                    tmpCoord = getCoord(temp[i].coord, true);
                    combined[tmpCoord[0]][tmpCoord[1]].push(temp[i]);
                }
            }

            combined[x][y].push(pin);
            nPins++;
        }
        else {
            for (var c = 0; c < pin.coords.length; ++c) {
                coord = getCoord(pin.coords[c]);
                x = coord[0]; y = coord[1];
                var newPin = $WH.dO(pin);
                newPin.coord = pin.coords[c];

                if (combined[x][y].length > 3) {
                    var temp = combined[x][y];
                    combined[x][y] = [];
                    for (var i = 0; i < temp.length; ++i) {
                        tmpCoord = getCoord(temp[i].coord, true);
                        combined[tmpCoord[0]][tmpCoord[1]].push(temp[i]);
                    }
                }

                combined[x][y].push(newPin);
                nPins++;
            }
        }
    }

    var complete = [];
    for (x in combined) {
        for (y in combined[x]) {
            complete.push({
                coord: [combined[x][y][0].coord[0], combined[x][y][0].coord[1]],
                level: combined[x][y][0].level,
                list:  combined[x][y]
            });
        }
    }

    return [complete, nPins];
};

ShowOnMap.buildTooltip = function(list, dailyOnly) {
    var str = '';
    var url = '';
    var menu = [];
    var pinType = -1;
    var uniques = {};
    var nUniques = 0;
    var pointTypes = {};
    var objType = 1;

    for (var n = 0; n < list.length; ++n) {
        var obj = list[n];
        url = (g_types[obj.type] && obj.id ? '?' + g_types[obj.type] + '=' + obj.id : '');
        var key = url + obj.item;
        var pointKey = url + obj.point;
        if (!uniques[key]) {
            uniques[key] = {
                url:    url,
                obj:    obj,
                coords: [obj.coord],
                all:    [obj]
            };
            nUniques++;
        }
        else {
            if (!pointTypes[pointKey]) {
                uniques[key].all.push(obj);
            }
            uniques[key].coords.push(obj.coord);
        }
        pointTypes[pointKey] = 1;
    }

    var n = 0;
    for (var key in uniques) {
        var url = uniques[key].url;
        var all = uniques[key].all;
        var obj = uniques[key].obj;
        var coords = uniques[key].coords;
        if (n > 0) {
            str += '<br />';
        }
        menu.push([n++, obj.name, url]);
        objType = obj.type;

        if (!obj.point) {
            if ((obj.reacthorde == 1 && obj.reactalliance < 1) || obj.side == 2) {
                if (pinType == 2 || pinType == -1) {
                    pinType = 2;
                }
                else {
                    pinType = 0;
                }
            }
            else if ((obj.reactalliance == 1 && obj.reacthorde < 1) || obj.side == 1) {
                if (pinType == 3 || pinType == -1) {
                    pinType = 3;
                }
                else {
                    pinType = 0;
                }
            }
            else {
                pinType = 0;
            }
        }

        str += '<b class="q' + (pinType == 2 ? ' icon-horde' : '') + (pinType == 3 ? ' icon-alliance' : '') + '">' + obj.name + '</b>';
        if (coords.length > 0) {
            str += ' (' + coords[0][0] + ', ' + coords[0][1] + ')<br />';
        }

        if (obj.quests) {
            if (obj.quests.length > 1) {
                str += LANG.som_startsquestpl;
            }
            else {
                str += LANG.som_startsquest;
            }
            str += '<div class="indent">';
            for (var q = 0; q < obj.quests.length; ++q) {
                var quest = obj.quests[q];
                if (dailyOnly && !quest.daily) {
                    continue;
                }
                str += '<span class="q0">[' + quest.level + ']</span> ' + quest.name + ((quest.series && !quest.first) ? '<sup style="font-size: 8px">(*)</sup>' : '') + ((quest.category < 0 && g_quest_sorts[quest.category]) ? ' <i class="q0">' + g_quest_sorts[quest.category] + '</i>' : '') + '<br />';
            }
            str += '</div>';
        }
        else if (obj.description) {
            str += obj.description + '<br />';
        }
        else if (obj.point) {
            for (var n = 0; n < all.length; ++n) {
                var o = all[n];
                switch (o.point) {
                    case 'start':
                        str += LANG.mapper_startsquest + '<br />';
                        if (pinType == 'end') {
                            pinType = 'startend';
                        }
                        else if (pinType != 'startend') {
                            pinType = 'start';
                        }
                        break;
                    case 'end':
                        str += LANG.mapper_endsquest + '<br />';
                        if (pinType == 'start') {
                            pinType = 'startend';
                        }
                        else if (pinType != 'startend') {
                            pinType = 'end';
                        }
                        break;
                    case 'sourcestart':
                        str += LANG.mapper_sourcestart + '<br />';
                        str += '<div class="indent">' + o.item + '</div>';
                        if (pinType == 'end') {
                            pinType = 'startend';
                        }
                        else if (pinType != 'startend') {
                            pinType = 'start';
                        }
                        break;
                    case 'sourceend':
                        str += LANG.mapper_sourceend + '<br />';
                        str += '<div class="indent">' + o.item + '</div>';
                        if (pinType == 'start') {
                            pinType = 'startend';
                        }
                        else if (pinType != 'startend') {
                            pinType = 'end';
                        }
                        break;
                    case 'requirement':
                        str += LANG.mapper_requiredquest + '<br />';
                        if (pinType == -1) {
                            pinType = o.objective;
                        }
                        break;
                    case 'sourcerequirement':
                        str += LANG.mapper_sourcereq + '<br />';
                        str += '<div class="indent">' + o.item + '</div>';
                        if (pinType == -1) {
                            pinType = o.objective;
                        }
                        break;
                }
            }
        }
    }

    str += '<div class="q2">';
    if (list.length == 1) {
        str += (list[0].type == 1 ? LANG.som_viewnpc : (list[0].type == 2 ? LANG.som_viewobj : ''));
    }
    else if (nUniques == 1) {
        str += (objType == 1 ? LANG.som_viewnpc : (objType == 2 ? LANG.som_viewobj : ''));
    }
    else {
        str += '<br />' + LANG.som_view;
    }
    str += '</div>';

    var ret = [];
    ret.push(str);
    ret.push(pinType);
    if (list.length == 1 || nUniques == 1) {
        ret.push(url);
        ret.push(null);
    }
    else {
        ret.push('javascript:;');
        ret.push(menu);
    }

    return ret;
};
