/*
public static const MT_ITEM:int = 1;
public static const MT_HELM:int = 2;
public static const MT_SHOULDER:int = 4;
public static const MT_NPC:int = 8;
public static const MT_CHAR:int = 16;
public static const MT_HUMAN:int = 32;  // .sis file
public static const MT_OBJECT:int = 64;
public static const MT_ARMOR:int = 128;

public static const IT_HEAD:int = 1;
public static const IT_SHOULDER:int = 3;
public static const IT_SHIRT:int = 4;
public static const IT_CHEST:int = 5;
public static const IT_BELT:int = 6;
public static const IT_PANTS:int = 7;
public static const IT_BOOTS:int = 8;
public static const IT_BRACERS:int = 9;
public static const IT_GLOVES:int = 10;
public static const IT_ONEHAND:int = 13;    //14
public static const IT_SHIELD:int = 14;        //13
public static const IT_BOW:int = 15;        //14
public static const IT_CAPE:int = 16;
public static const IT_TWOHAND:int = 17;    //14
public static const IT_TABARD:int = 19;
public static const IT_ROBE:int = 20;        //5
public static const IT_RIGHTHAND:int = 21;    //14
public static const IT_LEFTHAND:int = 22;    //13
public static const IT_OFFHAND:int = 23;    //13
public static const IT_THROWN:int = 25;        //14
*/

var ModelViewer = new function()
{
    this.validSlots = [1, 3, 4, 5, 6, 7, 8, 9, 10, 13, 14, 15, 16, 17, 19, 20, 21, 22, 23, 25, 26];
    this.slotMap = {1: 1, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9, 10: 10, 13: 21, 14: 22, 15: 22, 16: 16, 17: 21, 19: 19, 20: 5, 21: 21, 22: 22, 23: 22, 25: 21, 26: 21};
    var model,
        modelType,
        equipList = [],
        displayLink,
        displayLabel,

        optBak,

        flashDiv,
        modelDiv,
        screenDiv,
        animDiv,
        raceSel1,
        raceSel2,
        sexSel,

        oldHash,
        mode,
        opts,

        readExtraPound,
        animsLoaded = false,

    races = [
        { id: 10, name: g_chr_races[10], model: 'bloodelf' },
        { id: 11, name: g_chr_races[11], model: 'draenei' },
        { id:  3, name: g_chr_races[ 3], model: 'dwarf' },
        { id:  7, name: g_chr_races[ 7], model: 'gnome' },
        { id:  1, name: g_chr_races[ 1], model: 'human' },
        { id:  4, name: g_chr_races[ 4], model: 'nightelf' },
        { id:  2, name: g_chr_races[ 2], model: 'orc' },
        { id:  6, name: g_chr_races[ 6], model: 'tauren' },
        { id:  8, name: g_chr_races[ 8], model: 'troll' },
        { id:  5, name: g_chr_races[ 5], model: 'scourge' }
    ],

    sexes = [
        { id: 0, name: LANG.male,   model: 'male' },
        { id: 1, name: LANG.female, model: 'female' }
    ];

    function clear()
    {

    }

    function getRaceSex()
    {
        var race,
            sex;

        if (raceSel1.is(':visible'))
            race = (raceSel1[0].selectedIndex >= 0 ? raceSel1.val() : '');
        else
            race = (raceSel2[0].selectedIndex >= 0 ? raceSel2.val() : '');

        sex = (sexSel[0].selectedIndex >= 0 ? sexSel.val() : 0);

        return { r: race, s: sex };
    }

    function isRaceSexValid(race, sex)
    {
        return (!isNaN(race) && race > 0 && $WH.in_array(races, race, function(x) { return x.id; }) != -1 &&
                !isNaN(sex)  && sex >= 0 && sex <= 1);
    }

    function render()
    {
        screenDiv.css('width', '600px');
        var flashVars = {
            model: model,
            modelType: modelType,
         // contentPath: 'http://static.wowhead.com/modelviewer/'
            contentPath: g_staticUrl + '/modelviewer/'
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

        if (modelType == 16 && equipList.length)
            flashVars.equipList = equipList.join(',');
        if (displayLink)
            flashVars.link = displayLink;
        if (displayLabel)
            flashVars.label = displayLabel;

     // swfobject.embedSWF('http://static.wowhead.com/modelviewer/ZAMviewerfp11.swf', 'modelviewer-generic', '600', '400', "11.0.0", 'http://static.wowhead.com/modelviewer/expressInstall.swf', flashVars, params, attributes);
        swfobject.embedSWF(g_staticUrl + '/modelviewer/ZAMviewerfp11.swf', 'modelviewer-generic', '600', '400', "11.0.0", g_staticUrl + '/modelviewer/expressInstall.swf', flashVars, params, attributes);

        var foo  = getRaceSex(),
            race = foo.r,
            sex  = foo.s;

        if (!optBak.noPound)
        {
            var url = '#modelviewer';
            var foo = $WH.ge('view3D-button');
            if (!foo)
            {
                switch (optBak.type)
                {
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
            if (race && sex)
                url += ':' + race + '+' + sex;
            else
                url += ':';

            if (optBak.extraPound != null)
                url += ':' + optBak.extraPound;

            animsLoaded = false;

            location.replace($WH.rtrim(url, ':'));
        }
    }

    function onSelChange()
    {
        var foo  = getRaceSex(),
            race = foo.r,
            sex  = foo.s;

        if (!race)
        {
            if (!sexSel.is(':visible'))
                return;

            sexSel.hide();

            model = equipList[1];
            switch (optBak.slot)
            {
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
        else
        {
            if (!sexSel.is(':visible'))
                sexSel.show();

            var foo = function(x) { return x.id; };
            var raceIndex = $WH.in_array(races, race, foo);
            var sexIndex  = $WH.in_array(sexes, sex,  foo);

            if (raceIndex != -1 && sexIndex != -1)
            {
                model = races[raceIndex].model + sexes[sexIndex].model;
                modelType = 16;
            }

            g_setWowheadCookie('temp_default_3dmodel', race + ',' + sex);
        }

        clear();
        render();
    }

    function onAnimationChange()
    {
        var viewer = $('#modelviewer-generic');
        if (viewer.length == 0)
            return;
        viewer = viewer[0];

        var animList = $('select', animDiv);
        if (animList.val() && viewer.isLoaded && viewer.isLoaded())
            viewer.setAnimation(animList.val());
    }

    function onAnimationMouseover()
    {
        if (animsLoaded)
            return;

        var viewer = $('#modelviewer-generic');
        if (viewer.length == 0)
            return;
        viewer = viewer[0];

        var animList = $('select', animDiv);
        animList.empty();
        if (!viewer.isLoaded || !viewer.isLoaded())
        {
            animList.append($('<option/>', { text: LANG.tooltip_loading, val: 0 }));
            return;
        }

        var anims = {};
        var numAnims = viewer.getNumAnimations();
        for (var i = 0; i < numAnims; ++i)
        {
            var a = viewer.getAnimation(i);
            if (a && a != 'EmoteUseStanding')
                anims[a] = 1;
        }

        var animArray = [];
        for (var a in anims)
            animArray.push(a);
        animArray.sort();

        for (var i = 0; i < animArray.length; ++i)
            animList.append($('<option/>', { text: animArray[i], val: animArray[i] }));

        animsLoaded = true;
    }

    function initRaceSex(allowNoRace, opt)
    {
        var race = -1,
            sex  = -1,

            sel,
            offset;

        if (opt.race != null && opt.sex != null)
        {
            race = opt.race;
            sex  = opt.sex;

            modelDiv.hide();
            allowNoRace = 0;
        }
        else
            modelDiv.show();

        if (race == -1 && sex == -1)
        {
            if (location.hash)
            {
                var matches = location.hash.match(/modelviewer:.*?([0-9]+)\+([0-9]+)/);
                if (matches != null)
                {
                    if (isRaceSexValid(matches[1], matches[2]))
                    {
                        race = matches[1];
                        sex  = matches[2];
                        sexSel.show();
                    }
                }
            }
        }

        if (allowNoRace)
        {
            sel    = raceSel1;
            offset = 1;

            raceSel1.show();
            raceSel1[0].selectedIndex = -1;
            raceSel2.hide();
            if (sex == -1)
                sexSel.hide();
        }
        else
        {
            if (race == -1 && sex == -1)
            {
                var cooRace = 1,
                    cooSex  = 0;

                if (g_user && g_user.cookies['default_3dmodel'])
                {
                    var sp = g_user.cookies['default_3dmodel'].split(',');
                    if (sp.length == 2)
                    {
                        cooRace = sp[0];
                        cooSex  = sp[1] - 1;
                    }
                }
                else
                {
                    var cookie = g_getWowheadCookie('temp_default_3dmodel');
                    if (cookie)
                    {
                        var sp = cookie.split(',');
                        if (sp.length == 2)
                        {
                            cooRace = sp[0];
                            cooSex  = sp[1];
                        }
                    }
                }

                if (isRaceSexValid(cooRace, cooSex))
                {
                    race = cooRace;
                    sex  = cooSex;
                }
                else
                {
                    // Default
                    race = 1; // Human
                    sex  = 0; // Male
                }
            }

            sel    = raceSel2;
            offset = 0;

            raceSel1.hide();
            raceSel2.show();
            sexSel.show();
        }

        if (sex != -1)
            sexSel[0].selectedIndex = sex;

        if (race != -1 && sex != -1)
        {
            var foo = function(x) { return x.id; };
            var raceIndex = $WH.in_array(races, race, foo);
            var sexIndex  = $WH.in_array(sexes, sex,  foo);

            if (raceIndex != -1 && sexIndex != -1)
            {
                model = races[raceIndex].model + sexes[sexIndex].model;
                modelType = 16;

                raceIndex += offset;

                sel[0].selectedIndex = raceIndex;
                sexSel[0].selectedIndex = sexIndex;
            }
        }
    }

    function onHide()
    {
        if (!optBak.noPound)
        {
            if (!optBak.fromTag && oldHash && oldHash.indexOf('modelviewer') == -1)
                location.replace(oldHash);
            else
                location.replace('#.');
        }

        if (optBak.onHide)
            optBak.onHide();
    }

    function onShow(dest, first, opt)
    {
        var a1,
            a2;
        opts = opt;

        Lightbox.setSize(620, 452);

        if (first)
        {
            dest = $(dest);
            dest.addClass('modelviewer');

            var screen = $('<div/>', { 'class': 'modelviewer-screen' });
            flashDiv = $('<div/>');
            flashDiv.append($('<div/>', { id: 'modelviewer-generic' }));

            screen.append(flashDiv);

            var screenbg = $('<div/>', { css: { 'background-color': '#181818', margin: '0' } });
            screenbg.append(screen);
            dest.append(screenbg);

            screenDiv = screen;

            var rightDiv = $('<div/>', { css: { 'float': 'right' } });
            var leftDiv  = $('<div/>', { css: { 'float': 'left' } });

            animDiv = $('<div/>', { 'class': 'modelviewer-animation' });
            var v = $('<var/>', { text: LANG.animation });
            animDiv.append(v);

            var select = $('<select/>', { change: onAnimationChange, mouseenter: onAnimationMouseover });
            select.append($('<option/>', { text: LANG.dialog_mouseovertoload }));
            animDiv.append(select);

            rightDiv.append(animDiv);

            var a1 = $('<a/>', { 'class': 'modelviewer-help', href: '?help=modelviewer', target: '_blank'/* , text: LANG.help  */ }),
                a2 = $('<a/>', { 'class': 'modelviewer-close', href: 'javascript:;', click: Lightbox.hide/* , text: LANG.close */ });

            a1.append($('<span/>'));
            a2.append($('<span/>'));

            rightDiv.append(a2);
            rightDiv.append(a1);

            dest.append(rightDiv);

            var sp = $('<span/>');
            sp.append('<small>Drag to rotate<br />Control (Windows) / Cmd (Mac) + drag to pan</small>');
            leftDiv.append(sp);

            modelDiv = $('<div/>', { 'class': 'modelviewer-model' });

            var foo = function(a, b) { return $WH.strcmp(a.name, b.name); };
            races.sort(foo);
            sexes.sort(foo);

            raceSel1 = $('<select/>', { change: onSelChange });
            raceSel2 = $('<select/>', { change: onSelChange });
            sexSel   = $('<select/>', { change: onSelChange });

            raceSel1.append($('<option/>'));
            for (var i = 0, len = races.length; i < len; ++i)
            {
                var o = $('<option/>', { val: races[i].id, text: races[i].name });
                raceSel1.append(o);
            }
            for (var i = 0, len = races.length; i < len; ++i)
            {
                var o = $('<option/>', { val: races[i].id, text: races[i].name });
                raceSel2.append(o);
            }

            for (var i = 0, len = sexes.length; i < len; ++i)
            {
                var o = $('<option/>', { val: sexes[i].id, text: sexes[i].name });
                sexSel.append(o);
            }
            sexSel.hide();

            var v = $('<var/>', { text: LANG.model });
            modelDiv.append(v);
            modelDiv.append(raceSel1);
            modelDiv.append(raceSel2);
            modelDiv.append(sexSel);
            leftDiv.append(modelDiv);

            dest.append(leftDiv);

            d = $('<div/>', { 'class': 'clear' });
            dest.append(d);

            d = $('<div/>', { id: 'modelviewer-msg', 'class': 'sub', css: { display: 'none', 'margin-top': '-6px', color: '#ccc', 'font-size': '11px' } });
            dest.append(d);

        }

        switch (opt.type)
        {
            case 1: // NPC
                modelDiv.hide();
                if (opt.humanoid)
                    modelType = 32; // Humanoid NPC
                else
                    modelType = 8; // NPC
                model = opt.displayId;
                break;

            case 2: // Object
                modelDiv.hide();
                modelType = 64; // Object
                model = opt.displayId;
                break;

            case 3: // Item
            case 4: // Item Set
                if (opt.type == 3)
                    equipList = [opt.slot, opt.displayId];
                else
                    equipList = opt.equipList;

                if ($WH.in_array([4, 5, 6, 7, 8, 9, 10, 16, 19, 20], equipList[0]) != -1)
                {
                    initRaceSex(0, opt);
                }
                else
                {
                    switch (equipList[0])
                    {
                        case 1:
                            modelType = 2; // Helm
                            break;

                        case 3:
                            modelType = 4; // Shoulder
                            break;

                        default:
                            modelType = 1; // Item
                    }

                    model = equipList[1];

                    initRaceSex(1, opt);
                }
                break;
        }

        clear();
        setTimeout(render, 1);

        var msg = $('#modelviewer-msg');
        if (opt.message && msg.length > 0)
        {
            msg.html(opt.message);
            msg.show();
        }
        else
            msg.hide();

        if (opt.link)
            displayLink = opt.link;
        if (opt.label)
            displayLabel = opt.label;

        var trackCode = '';
        if (opt.fromTag)
        {
            trackCode += 'Custom ';
            switch (opt.type)
            {
                case 1: // npc
                    trackCode += 'NPC ' + opt.displayId + (opt.humanoid ? ' humanoid' : ''); break;
                case 2: // object
                    trackCode += 'Object ' + opt.displayId; break;
                case 3: // item
                    trackCode += 'Item ' + opt.displayId + ' Slot ' + (opt.slot | 0); break;
                case 4: // item set
                    trackCode += 'Item set ' + equipList.join('.'); break;
            }
        }
        else
        {
            switch (opt.type)
            {
                case 1: // npc
                    trackCode += 'NPC ' + (opt.typeId ? opt.typeId : ' DisplayID ' + opt.displayId); break;
                case 2: // object
                    trackCode += 'Object ' + opt.typeId; break;
                case 3: // item
                    trackCode += 'Item ' + opt.typeId; break;
                case 4: // item set
                    trackCode += 'Item set ' + equipList.join('.'); break;
            }
        }

        $WH.Track.interactiveEvent({
            category: 'Model Viewer',
            action: 'Show',
            label: g_urlize(trackCode)                      // WH.Strings.slug(trackCode)
        });

        oldHash = location.hash;
    }

    this.checkPound = function()
    {
        if (location.hash && location.hash.indexOf('#modelviewer') == 0)
        {
            var parts = location.hash.split(':');
            if (parts.length >= 3)
            {
                parts.shift(); // - #modelviewer
                var type = parseInt(parts.shift());
                var opt = { type: type };
                switch (type)
                {
                    case 1: // npc
                        opt.displayId = parseInt(parts.shift());
                        var humanoid = parseInt(parts.shift());
                        if (humanoid == 1)
                            opt.humanoid = 1;
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

                if (readExtraPound != null)
                {
                    if (parts.length > 0 && parts[parts.length - 1])
                        readExtraPound(parts[parts.length - 1]);
                }
            }
            else if (readExtraPound != null && parts.length == 2 && parts[1])
                readExtraPound(parts[1]);
            else
            {
                var foo = $WH.ge('view3D-button');
                if (foo)
                    foo.onclick();
            }
        }
    }

    this.addExtraPound = function(func)
    {
        readExtraPound = func;
    }

    this.show = function(opt)
    {
        optBak = opt;

        Lightbox.show('modelviewer', {
            onShow: onShow,
            onHide: onHide
        }, opt);
    }

    $(document).ready(this.checkPound);
};
