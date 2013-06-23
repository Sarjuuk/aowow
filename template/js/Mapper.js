function Mapper(opt, noScroll) {
    cO(this, opt);

    if(this.parent && !this.parent.nodeName) {
        this.parent = ge(this.parent);
    }
    else if(!this.parent) {
        return;
    }

    var _;

    this.mouseX = this.mouseY = 0;

    this.editable = this.editable || false;
    this.overlay  = this.overlay || false;

    if(this.editable) {
        this.zoomable = this.toggle = false;
        this.show     = this.mouse  = true;
    }
    else {
        this.zoomable = (this.zoomable == null ? true  : this.zoomable);
        this.toggle   = (this.toggle   == null ? true  : this.toggle);
        this.show     = (this.show     == null ? true  : this.show);
        this.mouse    = (this.mouse    == null ? false : this.mouse);
    }

    this.buttons = (this.buttons == null ? true : this.buttons);

    this.zoneLink = (this.zoneLink == null ? true : this.zoneLink);
    if(location.href.indexOf('zone=') != -1) {
        this.zoneLink = false;
    }

    this.zoom = (this.zoom == null ? 0 : this.zoom);
    this.zone = (this.zone == null ? 0 : this.zone);
    this.level = (this.level == null ? (Mapper.zoneDefaultLevel[this.zone] ? Mapper.zoneDefaultLevel[this.zone] : 0) : this.level);

    this.pins = [];
    this.nCoords = 0;

    this.tempWidth = null;
    this.tempHeight = null;

    this.parent.className = 'mapper';
    this.parent.appendChild(this.span = ce('span'));

    _ = this.span.style;
    _.display  = 'block';
    _.position = 'relative';

    ns(this.span);

    this.overlaySpan = _ = ce('div');
    _.style.display = 'block';
    _.style.width = '100%';
    _.style.height = '100%';
    this.span.appendChild(_);

    this.buttonDiv = _ = ce('div');
    _.style.position = 'absolute';
    _.style.top = _.style.right = '3px';

    if(this.buttons)
        this.parent.appendChild(_);

    if(this.editable) {
        this.span.onmouseup = this.addPin.bind(this);

        _ = g_createGlow(LANG.mapper_tippin);
        _.style.fontSize = '11px';
        _.style.position = 'absolute';
        _.style.bottom = _.style.right = '0';

        ns(_);
        this.parent.appendChild(_);
    }
    else {
        this.sToggle = _ = RedButton.create(LANG.mapper_hidepins, true, this.toggleShow.bind(this));

        _.style['float'] = 'right';

        _.style.display = 'none';

        ns(_);
        this.buttonDiv.appendChild(_);
    }

    if(this.zoomable) {
        this.span.onclick = this.toggleZoom.bind(this);
        this.span.id = 'sjdhfkljawelis' + (this.unique !== undefined ? this.unique : '');

        this.sZoom = _ = g_createGlow(LANG.mapper_tipzoom);
        _.style.fontSize = '11px';
        _.style.position = 'absolute';
        _.style.bottom = _.style.right = '0';

        ns(_);
        this.span.appendChild(_);
    }

    this.sZoneLink = _ = g_createGlow('');

    _.style.display = 'none';
    _.style.position = 'absolute';
    _.style.top = _.style.left = '0';

    this.parent.appendChild(_);

    if(this.mouse) {
        this.parent.onmouseout  = (function() {
            this.timeout = setTimeout((function() {
                this.sMouse.style.display = 'none';
            }).bind(this), 1)
        }).bind(this);
        this.parent.onmouseover = (function() {
            clearTimeout(this.timeout);
            this.sMouse.style.display = '';
        }).bind(this);

        this.span.onmousemove = this.span.onmousedown = this.getMousePos.bind(this);

        this.sMouse = _ = g_createGlow('(0.0, 0.0)');

        _.style.display = 'none';
        _.style.position = 'absolute';
        _.style.bottom = _.style.left = '0';

        _.onmouseup = sp;

        ns(_);
        this.span.appendChild(_);
    }

    this.floorPins = {};

    if(opt.coords != null) {
        this.setCoords(opt.coords);
    }
    else if(opt.link != null) {
        this.setLink(opt.link);
    }

    if(opt.objectives)
        this.setObjectives(opt.objectives);
    if(opt.zoneparent && opt.zones)
        this.setZones(opt.zoneparent, opt.zones);

    this.updateMap(noScroll);
};

Mapper.sizes = [
    [ 488, 325, 'normal'],
    [ 772, 515, 'zoom'],
    [1002, 668, 'original'],
    [ 224, 149, 'small']
];

Mapper.onlyOneFloor = {
    4120: true, // Nexus
    4264: true, // Halls of Stone
    4416: true, // Gundrak
    4415: true, // Violet Hold
    4493: true, // Obsidian Sanctum
    4500: true, // Eye of Eternity
    4603: true, // Vault of Archavon
    4723: true, // Trial of the Champion
    4809: true, // The Forge of Souls
    4813: true, // Pit of Saron
    4820: true  // Halls of Reflection
};

Mapper.zoneLevelOffset = {
    4273: 0 // Ulduar
};

Mapper.zoneDefaultLevel = {
    3456: 4, // Naxxramas
    4812: 4  // Icecrown Citadel
};

Mapper.remappedLevels = {
    4273: { 6: 5 }
};

Mapper.multiLevelZones = {};

Mapper.prototype = {
    getMap: function() { return this.parent },

    update: function(opt, noScroll) {
        if(opt.zoom != null) {
            this.zoom = opt.zoom;
        }

        if(opt.zone != null) {
            this.zone = opt.zone;
        }

        if(opt.show != null) {
            this.show = opt.show;
        }

        this.pins = [];
        this.nCoords = 0;
        for(var i in this.floorPins)
            if(this.floorPins[i].parentNode)
                de(this.floorPins[i]);
        this.floorPins = {};
        if(this.floorButton)
        {
            de(this.floorButton);
            this.floorButton = null;
        }

        var level = (opt.level === undefined ? 0 : this.fixLevel(parseInt(opt.level)));
        if(!opt.preservelevel)
            this.level = 0;
        else
            level = this.level;
        var mapperData = false;
        if(isset('g_mapperData'))
            mapperData = g_mapperData;
        else if(isset('g_mapper_data'))
            mapperData = g_mapper_data;
        if(mapperData && mapperData[this.zone] && !opt.coords)
        {
            var zone = mapperData[this.zone];
            var maxCount = -1;
            for(var i in zone)
            {
                i = parseInt(i);
                var iLevel = this.fixLevel(i);

                if(opt.level === undefined && zone[i].count > maxCount)
                {
                    level = parseInt(iLevel);
                    maxCount = zone[i].count;
                }

                if(zone[i].coords)
                    this.setCoords(zone[i].coords, iLevel);
            }
            this.level = level;
            if(this.floorPins[this.level])
                ae(this.span, this.floorPins[this.level]);
        }
        else if(opt.coords != null)
        {
            var lowestLevel = 999;
            for(var i in opt.coords)
            {
                i = parseInt(i);
                var iLevel = this.fixLevel(i);
                this.setCoords(opt.coords[i], iLevel);
                if(iLevel < lowestLevel)
                    lowestLevel = iLevel;
            }

            if(lowestLevel != 999 && !opt.preservelevel)
                this.level = lowestLevel;

            if(this.floorPins[this.level])
                ae(this.span, this.floorPins[this.level]);
        }
        //    this.setCoords(opt.coords);
        else if(opt.link != null)
            this.setLink(opt.link);

        this.updateMap(noScroll);
    },

    fixLevel: function(level)
    {
        if(Mapper.zoneLevelOffset[this.zone] !== undefined)
            level += Mapper.zoneLevelOffset[this.zone];
        else if(Mapper.multiLevelZones[this.zone] && level > 0)
            level += -1;
        else if(Mapper.multiLevelZones[this.zone] == undefined)
            level = 0;

        if(Mapper.remappedLevels[this.zone] && Mapper.remappedLevels[this.zone][level] !== undefined)
            level = Mapper.remappedLevels[this.zone][level];

        return level;
    },

    getZone: function() {
        return this.zone;
    },

    setZone: function(zone, level, noScroll) {
        this.pins = [];
        this.nCoords = 0;
        if(this.floorPins[this.level])
            de(this.floorPins[this.level]);
        this.floorPins = {};
        if(this.floorButton)
        {
            de(this.floorButton);
            this.floorButton = null;
        }

        this.zone = zone;
        this.level = level | 0;
        this.updateMap(noScroll);

        return true;
    },

    showFloors: function(event)
    {
        if(!Mapper.multiLevelZones[this.zone])
            return;

        this.menu = [];
        var _ = Mapper.multiLevelZones[this.zone];
        var src = g_zone_areas;

        for(var i = 0; i < _.length; ++i)
        {
            var menuItem;
            if(!src[this.zone])
                menuItem = [i, '[Level ' + (i + 1) + ']', this.setMap.bind(this, _[i], i, true)];
            else
                menuItem = [i, src[this.zone][i], this.setMap.bind(this, _[i], i, true)];

            if(i == this.level || (this.level === undefined && i == 0))
                menuItem.checked = true;

            this.menu.push(menuItem);
        }

        // Menu.showAtCursor(menu, event);
        (Menu.showAtCursor.bind(this, event))()
    },

    setMap: function(map, level, forceUpdate)
    {
        if(level != this.level)
        {
            if(this.floorPins[this.level])
                de(this.floorPins[this.level]);
            if(this.floorPins[level])
                ae(this.span, this.floorPins[level]);
            this.level = level;
        }

        var type = g_locale.name;

        if(isset('g_ptr') && g_ptr)
            type = 'ptr';
        else if(isset('g_beta') && g_beta)
            type = 'beta';
        else if(isset('g_old') && g_old)
            type = 'old';

        this.span.style.background = 'url(' + g_staticUrl + '/images/maps/' + type + '/' + Mapper.sizes[this.zoom][2] + '/' + map + '.jpg)';

        if(this.overlay)
            this.overlaySpan.style.background = 'url(' + g_staticUrl + '/images/maps/overlay/' + Mapper.sizes[this.zoom][2] + '/' + map + '.png)';

        if(this.sZoneLink)
        {
            var zoneText = '';
            var zoneId = parseInt(this.zone);
            var found = g_zones[zoneId] != null;
            var src = g_zone_areas;
            if(found)
            {
                if(this.zoneLink)
                    zoneText += '<a href="?zone=' + zoneId + '">' + g_zones[zoneId] + '</a>';
                if(Mapper.multiLevelZones[zoneId])
                {
                    if(this.zoneLink)
                        zoneText += ': ';
                    zoneText += (src[zoneId] ? src[zoneId][this.level] : 'Level ' + (this.level+1));
                }
                g_setInnerHtml(this.sZoneLink, zoneText, 'div');
                if(this.zoneLink)
                {
                    for(var i = 0; i < 9; ++i)
                    {
                        if(i == 4) continue;
                        this.sZoneLink.childNodes[i].firstChild.style.color = 'black';
                    }
                }
            }
            this.sZoneLink.style.display = found ? '' : 'none';
        }

        if(forceUpdate)
            this.onMapUpdate && this.onMapUpdate(this);
    },

    setObjectives: function(obj)
    {
        var startEnd = { start: 1, end: 1, startend: 1, sourcestart: 1, sourceend: 1 };
        for(var z in obj)
        {
            var zone = obj[z];
            if(g_mapperData[z] === undefined)
                g_mapperData[z] = {};
            var objectiveIndex = {};
            var nextIndex = 0;
            for(var l in zone.levels)
            {
                var level = zone.levels[l];
                var results = ShowOnMap.combinePins(level);
                var pins = results[0];
                g_mapperData[z][l] = { count: pins.length, coords: [] };
                for(var i = 0; i < pins.length; ++i)
                {
                    var tooltip = ShowOnMap.buildTooltip(pins[i].list);
                    g_mapperData[z][l].coords.push([pins[i].coord[0], pins[i].coord[1], { type: tooltip[1], url: tooltip[2], menu: tooltip[3], label: tooltip[0] }]);
                }
            }
        }
    },

    setZones: function(div, zones)
    {
        // div = $('#'+div);
        div = ge(div);
        if(!div || !zones || zones.length == 0 || !this.objectives)
            return;

        var zoneLinks = function(self, container, zoneList, zoneTypes)
        {
            var maxIdx = [false, -1];
            for(var i = 0; i < zoneList.length; ++i)
            {
                if(i > 0) ae(span, (i == zoneList.length-1 ? LANG.and : LANG.comma));
                var entry = null;
                if(self.objectives[zoneList[i][0]].mappable > 0)
                {
                    entry = ce('a');
                    entry.href = 'javascript:;';
                    ae(entry, ct(self.objectives[zoneList[i][0]].zone));
                    entry.onClick = function(link, zone) {
                        self.update({ zone: zone });
                        g_setSelectedLink(link, 'mapper');
                    }.bind(self, entry, zoneList[i][0]);
                    entry.isLink = true;
                }
                else
                {
                    entry = ce('a');
                    entry.href = '?zone=' + zoneList[i][0];
                    ae(entry, ct(self.objectives[zoneList[i][0]].zone));
                    g_addTooltip(entry, LANG.tooltip_zonelink);
                }

                if(zones.length > 1)
                {
                    var types = zoneTypes[zoneList[i][0]];
                    if(types.start && types.end)
                    {
                        entry.className += ' icontiny';
                        entry.style += ' background-image', 'url(' + g_staticUrl + '/images/icons/quest_startend.gif)';
                        entry.style += ' padding-left', '20px';
                    }
                    else if(types.start)
                    {
                        entry.className += ' icontiny';
                        entry.style += ' background-image', 'url(' + g_staticUrl + '/images/icons/quest_start.gif)';
                        entry.style += ' padding-left', '14px';
                    }
                    else if(types.end)
                    {
                        entry.className += ' icontiny';
                        entry.style += ' background-image', 'url(' + g_staticUrl + '/images/icons/quest_end.gif)';
                        entry.style += ' padding-left', '16px';
                    }
                }
                ae(container, entry);

                if(zoneList[i][1] > maxIdx[1])
                    maxIdx = [entry, zoneList[i][1]];
            }
            return maxIdx[0];
        };

        var getZoneList = function(zoneList, zoneTypes, type)
        {
            var ret = [];
            for(var i = 0; i < zoneList.length; ++i)
            {
                if(zoneTypes[zoneList[i][0]][type])
                    ret.push(zoneList[i]);
            }
            return ret;
        };

        var typesByZone = {};
        var types = { start: [], end: [], objective: [] };

        for(var zoneId in this.objectives)
        {
            if(typesByZone[zoneId] === undefined)
                typesByZone[zoneId] = {};
            var zone = this.objectives[zoneId];
            for(var levelNum in zone.levels)
            {
                var level = zone.levels[levelNum];
                for(var i = 0; i < level.length; ++i)
                {
                    if(level[i].point == 'start' || level[i].point == 'sourcestart')
                    {
                        types.start.push(zoneId);
                        typesByZone[zoneId].start = true;
                    }
                    else if(level[i].point == 'end' || level[i].point == 'sourceend')
                    {
                        types.end.push(zoneId);
                        typesByZone[zoneId].end = true;
                    }
                    else if(level[i].point == 'requirement' || level[i].point == 'sourcerequirement')
                    {
                        types.objective.push(zoneId);
                        typesByZone[zoneId].objective = true;
                    }
                }
            }
        }

        var h3 = ce('h3');
        ae(h3, ct(LANG.mapper_relevantlocs));
        ae(div, h3);
        if(zones.length == 1 && this.missing == 0)
        {
            var span = ce('span');
            span.innerHTML = LANG.mapper_entiretyinzone.replace('$$', '<b>' + this.objectives[zones[0][0]].zone + '</b>.');
            ae(div, span);
            this.update({ zone: zones[0][0] });
        }
        else if(this.missing > 0)
        {
            var span = ce('span');
            var primaryLink = false, secondaryLink = false, tertiaryLink = false;
            types.objective = array_unique(types.objective);
            types.start = array_unique(types.start);
            types.end = array_unique(types.end);
            var startEnd = types.start.length > 0 && array_compare(types.start, types.end);
            var startObj = types.start.length > 0 && array_compare(types.start, types.objective);
            var endObj = types.end.length > 0 && array_compare(types.end, types.objective);

            var objZones = getZoneList(zones, typesByZone, 'objective');
            var startZones = getZoneList(zones, typesByZone, 'start');
            var endZones = getZoneList(zones, typesByZone, 'end');

            if(startEnd && startObj) // everything in the same zones
            {
                var parts = LANG.mapper_happensin.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                ae(span, parts[1]);
            }
            else if(startEnd && types.objective.length == 0) // starts and ends in x
            {
                var parts = LANG.mapper_objectives.sex.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                ae(span, parts[1]);
            }
            else if(startEnd) // objectives in x, starts and ends in y
            {
                var parts = LANG.mapper_objectives.ox_sey.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                ae(span, parts[1]);
                secondaryLink = zoneLinks(this, span, objZones, typesByZone);
                ae(span, parts[2]);
            }
            else if(startObj && types.end.length == 0) // objectives and starts in x
            {
                var parts = LANG.mapper_objectives.osx.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                ae(span, parts[1]);
            }
            else if(startObj) // objectives and starts in x, ends in y
            {
                var parts = LANG.mapper_objectives.osx_ey.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, objZones, typesByZone);
                ae(span, parts[1]);
                secondaryLink = zoneLinks(this, span, endZones, typesByZone);
                ae(span, parts[2]);
            }
            else if(endObj && types.start.length == 0) // objectives and ends in x
            {
                var parts = LANG.mapper_objectives.oex.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                ae(span, parts[1]);
            }
            else if(endObj) // objectives and ends in x, starts in y
            {
                var parts = LANG.mapper_objectives.oex_sy.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                ae(span, parts[1]);
                secondaryLink = zoneLinks(this, span, objZones, typesByZone);
                ae(span, parts[1]);
            }
            else if(types.start.length > 0 && types.end.length > 0 && types.objective.length > 0) // objectives in x, starts in y, ends in z
            {
                var parts = LANG.mapper_objectives.ox_sy_ez.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                ae(span, parts[1]);
                secondaryLink = zoneLinks(this, span, objZones, typesByZone);
                ae(span, parts[1]);
                tertiaryLink = zoneLinks(this, span, endZones, typesByZone);
                ae(span, parts[3]);
            }
            else if(types.start.length > 0 && types.end.length > 0) // starts in x, ends in y
            {
                var parts = LANG.mapper_objectives.sx_ey.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                ae(span, parts[1]);
                secondaryLink = zoneLinks(this, span, endZones, typesByZone);
                ae(span, parts[2]);
            }
            else if(types.start.length > 0 && types.objective.length > 0) // objectives in x, starts in y
            {
                var parts = LANG.mapper_objectives.ox_sy.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, startZones, typesByZone);
                ae(span, parts[1]);
                secondaryLink = zoneLinks(this, span, objZones, typesByZone);
                ae(span, parts[2]);
            }
            else if(types.end.length > 0 && types.objective.length > 0) // objectives in x, ends in y
            {
                var parts = LANG.mapper_objectives.ox_ey.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, objZones, typesByZone);
                ae(span, parts[1]);
                secondaryLink = zoneLinks(this, span, endZones, typesByZone);
                ae(span, parts[2]);
            }
            else if(types.start.length > 0) // starts in x
            {
                var parts = LANG.mapper_objectives.sx.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                ae(span, parts[1]);
            }
            else if(types.end.length > 0) // ends in x
            {
                var parts = LANG.mapper_objectives.ex.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                ae(span, parts[1]);
            }
            else if(types.objective.length > 0) // objectives in x
            {
                var parts = LANG.mapper_objectives.ox.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                ae(span, parts[1]);
            }
            else // wat?
            {
                var parts = LANG.mapper_happensin.split('$$');
                ae(span, ct(parts[0]));
                primaryLink = zoneLinks(this, span, zones, typesByZone);
                ae(span, parts[1]);
            }
            ae(div, span);

            if(primaryLink && primaryLink.isLink)
                primaryLink.click();
            else if(secondaryLink && secondaryLink.isLink)
                secondaryLink.click();
            else if(tertiaryLink && tertiaryLink.isLink)
                tertiaryLink.click();
        }
        else
        {
            var parts = LANG.mapper_happensin.split('$$');
            var span = ce('span');
            ae(span, ct(parts[0]));
            var primaryLink = zoneLinks(this, span, zones, typesByZone);
            ae(span, parts[1]);
            ae(div, span);

            if(primaryLink && primaryLink.isLink)
                primaryLink.click();
        }
    },

    setSize: function(w, h) { this.tempWidth = w; this.tempHeight = h; this.updateMap(true); },

    getZoom: function() { return this.zoom; },
    setZoom: function(zoom, noScroll) { this.zoom = zoom; this.tempWidth = this.tempHeight = null; this.updateMap(noScroll); },

    toggleZoom: function(e)
    {
        this.zoom = 1 - this.zoom;
        this.updateMap(true);

        if(e)
            this.getMousePos(e);

        if(this.sZoom)
        {
            if(this.sZoom.style.display == 'none')
                this.sZoom.style.display = '';
            else
                this.sZoom.style.display = 'none';
        }

        if(this.zoom)
            MapViewer.show({ mapper: this });
    },

    getShow: function() { return this.show; },
    setShow: function(show)
    {
        this.show = show;

        var d = this.show ? '' : 'none';

        for(var i in this.floorPins)
            this.floorPins[i].style.display = d;
        RedButton.setText(this.sToggle, (this.show ? LANG.mapper_hidepins : LANG.mapper_showpins));
    },

    toggleShow: function()
    {
        this.setShow(!this.show);
    },

    getCoords: function()
    {
        var a = [];

        for(var i in this.pins)
            if(!this.pins[i].free)
                a.push([this.pins[i].x, this.pins[i].y]);

        return a;
    },

    clearPins: function()
    {
        for(var i in this.pins)
        {
            this.pins[i].style.display = 'none';
            this.pins[i].free = true;
        }
    },

    setCoords: function(coords, coordsLevel)
    {
        var _;

        var level, noLevel;
        if(coordsLevel === undefined)
        {
            this.clearPins();

            if(coords.length) // Backward compatibility
            {
                noLevel = true;
                level = 0;
            }
            else
            {
                for(var i in coords)
                {
                    level = i;
                    break;
                }

                if(level == null) return;

                coords = coords[level];
            }

            level = parseInt(level);

            if(!noLevel)
            {
                level = this.fixLevel(level);
                this.level = level;
            }
        }
        else
        {
            level = coordsLevel;
        }

        this.nCoords = coords.length;

        for(var i in coords)
        {
            var
                coord = coords[i],
                opt   = coord[2];

            if(coord[0] === undefined || coord[1] === undefined)
                continue;

            _ = this.getPin(level);
            _.x = coord[0];
            _.y = coord[1];
            _.style.left = _.x + '%';
            _.style.top = _.y + '%';

            if(this.editable)
                _.a.onmouseup = this.delPin.bind(this, _);
            else if(opt && opt.url)
            {
                _.a.href = Markup._fixUrl(opt.url);
                _.a.rel = "np";
                _.a.style.cursor = 'pointer';
            }

            if(opt && opt.tooltip)
            {
                _.a.tt = '';
                var printedFooter = false;
                for(var name in opt.tooltip)
                {
                    if(_.a.tt != '')
                        _.a.tt += '<br />';
                    _.a.tt += '<b class="q">' + name + '</b> ($)<br />';
                    for(var line in opt.tooltip[name].info)
                        _.a.tt += '<div>' + opt.tooltip[name].info[line] + '</div>';
                    if(!printedFooter && opt.tooltip[name].footer)
                    {
                        _.a.tt += opt.tooltip[name].footer + '<br />';
                        printedFooter = true;
                    }
                }
            }
            else if(opt && opt.label)
                _.a.tt = opt.label;
            else
                _.a.tt = '$';

            if(opt && opt.menu)
            {
                _.a.menu = opt.menu;
                //Menu.add(_.a, _.a.menu, { showAtCursor: true });
                _.a.onclick = function() {
                    (Menu.show.bind(this))();
                    Tooltip.hide();
                    this.onmouseout = function() {
                        Menu.hide();
                        this.onmouseout = Tooltip.hide();
                    }.bind(this);
                    sp();
                    return false;
                }.bind(_.a);
            }

            if(opt && opt.type)
                _.className += ' pin-' + opt.type;

            _.a.tt = str_replace(_.a.tt, '$', _.x.toFixed(1) + ', ' + _.y.toFixed(1));

            if(opt && opt.lines)
            {
                for(var p = 0, nPaths = opt.lines.length; p < nPaths; ++p)
                {
                    if(coord[0] == opt.lines[p][0] && coord[1] == opt.lines[p][1])
                        continue;

                    for(var s = 0, nSizes = Mapper.sizes.length; s < nSizes; ++s)
                    {
                        var size = Mapper.sizes[s];
                        _ = Line(coord[0] * size[0] / 100, coord[1] * size[1] / 100, opt.lines[p][0] * size[0] / 100, opt.lines[p][1] * size[1] / 100, opt.type);
                        _.className += ' ' + size[2];
                        ae(this.floorPins[level], _);
                    }
                }
            }
        }

        this.onPinUpdate && this.onPinUpdate(this);
    },

    getLink: function()
    {
        var s = '';

        for(var i in this.pins)
            if(!this.pins[i].free && this.pins[i].floor == this.level)
                s += (this.pins[i].x < 10 ? '0' : '') + (this.pins[i].x < 1 ? '0' : '') + (this.pins[i].x * 10).toFixed(0) + (this.pins[i].y < 10 ? '0' : '') + (this.pins[i].y < 1 ? '0' : '') + (this.pins[i].y * 10).toFixed(0);

        return (this.zone ? this.zone : '') + (Mapper.multiLevelZones[this.zone] && this.level != 0 ? '.' + this.level : '') + (s ? ':' + s : '');
    },

    setLink: function(link, noScroll)
    {
        var a = [];

        link = link.split(':');

        var zone = link[0];
        var level = 0;
        if(zone.indexOf('.') != -1)
        {
            var zoneLevel = zone.split('.');
            zone = zoneLevel[0];
            level = parseInt(zoneLevel[1]);
        }

        if(!this.setZone(zone, level, noScroll))
            return false;

        if(link.length == 2)
        {
            for(var i = 0; i < link[1].length; i += 6)
            {
                var x = link[1].substr(i, 3) / 10;
                var y = link[1].substr(i + 3, 3) / 10;
                if(isNaN(x) || isNaN(y)) break;
                a.push([x, y]);
            }
        }

        this.setCoords(a, level);

        return true;
    },

    updateMap: function(noScroll)
    {
        this.parent.style.width  = this.span.style.width  = (this.tempWidth ? this.tempWidth : Mapper.sizes[this.zoom][0]) + 'px';
        this.parent.style.height = this.span.style.height = (this.tempHeight ? this.tempHeight : Mapper.sizes[this.zoom][1]) + 'px';
        if(!this.editable)
            this.parent.style.cssFloat = this.parent.style.styleFloat = 'left';

        if(this.zone == '0')
            this.span.style.background = 'black';
        else
        {
            var level = this.level;

            if(level == 1 && Mapper.onlyOneFloor[this.zone])
                level = 0;

            var map = this.zone + (level ? '-' + level : '');
            if(Mapper.multiLevelZones[this.zone])
            {
                map = Mapper.multiLevelZones[this.zone][level];
            }
            this.setMap(map, level);

            if(!this.floorButton && Mapper.multiLevelZones[this.zone])
            {
                this.floorButton = _ = RedButton.create(LANG.mapper_floor, true, this.showFloors.bind(this));

                _.style['float'] = 'right';

                ns(_);
                this.buttonDiv.appendChild(_);
            }
            else if(this.floorButton)
                this.floorButton.style.display = Mapper.multiLevelZones[this.zone] ? '' : 'none';
        }

        if(this.sToggle)
            this.sToggle.style.display = (this.toggle && this.nCoords ? '' : 'none');

        if(!noScroll)
            g_scrollTo(this.parent, 3);

        // replacement start
        // $('.line', this.floorPins[level]).hide();
        // $('.line.' + Mapper.sizes[this.zoom][2], this.floorPins[level]).show();
        if (this.floorPins[level]) {
            var lines = this.floorPins[level].getElementsByClassName('line');
            for (i in lines) {
                if (i < lines.length) {
                    lines[i].style.display = 'none';
                }
            }

            lines = this.floorPins[level].getElementsByClassName('line ' + Mapper.sizes[this.zoom][2]);
            for (i in lines) {
                if (i < lines.length) {
                    lines[i].style.display = 'block';
                }
            }
        }
        // end of replacement

        this.onMapUpdate && this.onMapUpdate(this);
    },

    cleanPin: function(i, floor)
    {
        var _ = this.pins[i];
        _.style.display = '';
        _.free = false;
        _.className = 'pin';

        _.a.onmousedown = rf;
        _.a.onmouseup = rf;
        _.a.href = 'javascript:;';
        _.a.style.cursor = 'default';
        _.floor = floor;

        return _;
    },

    getPin: function(floor)
    {
        for(var i = 0; i < this.pins.length; ++i)
            if(this.pins[i].free)
                return this.cleanPin(i, floor);

        var _ = ce('div'), a = ce('a');
        _.className = 'pin';
        _.appendChild(a);
        _.a = a;
        _.floor = floor;
        a.onmouseover = this.pinOver;
        a.onmouseout = Tooltip.hide;
        a.onclick = sp;

        this.pins.push(_);
        this.cleanPin(this.pins.length - 1, floor);

        if(!this.floorPins[floor])
        {
            this.floorPins[floor] = ce('div');
            this.floorPins[floor].style.display = this.show ? '' : 'none';
            if(floor == this.level)
                ae(this.span, this.floorPins[floor]);
        }
        ae(this.floorPins[floor], _);
        return _;
    },

    addPin: function(e)
    {
        e = $E(e);
        if(e._button >= 2) return;

        this.getMousePos(e);
        var p = this.getPin(this.level);

        p.x = this.mouseX;
        p.y = this.mouseY;

        p.style.left = p.x.toFixed(1) + '%';
        p.style.top = p.y.toFixed(1) + '%';

        p.a.onmouseup = this.delPin.bind(this, p);
        p.a.tt = p.x.toFixed(1) + ', ' + p.y.toFixed(1);

        this.onPinUpdate && this.onPinUpdate(this);

        return false;
    },

    delPin: function(pin, e)
    {
        e = $E(e);

        pin.style.display = 'none';
        pin.free = true;
        sp(e);

        this.onPinUpdate && this.onPinUpdate(this);

        return;
    },

    pinOver: function()
    {
        Tooltip.show(this, this.tt, 4, 0);
    },

    getMousePos: function(e)
    {
        e = $E(e);

        var c = ac(this.parent);

        var scroll = g_getScroll();

        this.mouseX = Math.floor((e.clientX + scroll.x - c[0] - 3) / Mapper.sizes[this.zoom][0] * 1000) / 10;
        this.mouseY = Math.floor((e.clientY + scroll.y - c[1] - 3) / Mapper.sizes[this.zoom][1] * 1000) / 10;

        if(this.mouseX < 0) this.mouseX = 0;
        else if(this.mouseX > 100) this.mouseX = 100;

        if(this.mouseY < 0) this.mouseY = 0;
        else if(this.mouseY > 100) this.mouseY = 100;

        if(this.mouse)
            g_setTextNodes(this.sMouse, '(' + this.mouseX.toFixed(1) + ', ' + this.mouseY.toFixed(1) + ')');
    }
};
