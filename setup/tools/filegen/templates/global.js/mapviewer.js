var MapViewer = new function()
{
    var imgWidth,
        imgHeight,
        scale,
        desiredScale,

        mapper,
        oldOnClick,
        oldOnUpdate,
        oldParent,
        oldSibling,
        tempParent,
        placeholder,

        container,
        screen,
        imgDiv,
        aCover;

    function computeDimensions()
    {
        var availHeight = Math.max(50, Math.min(618, $WH.g_getWindowSize().h - 72));

        desiredScale = 1;
        scale        = 1;//Math.min(1, availHeight / 515);
        // no scaling because it doesnt work with background images

        if (desiredScale > 1)
            desiredScale = 1;
        if (scale > 1)
            scale = 1;

        imgWidth  = Math.round(scale * 772);
        imgHeight = Math.round(scale * 515);

        var lbWidth = Math.max(480, imgWidth);

        Lightbox.setSize(lbWidth + 20, imgHeight + 52);
    }

    function getPound(extra)
    {
        var extraBits = function(map, s)
        {
            s += ':' + map.zone;
            if (map.level)
                s += '.' + map.level;
            return s;
        };
        var buff = '#map';

        if (tempParent)
            buff += '=' + mapper.getLink();
        else if (Mapper.zoneDefaultLevel[mapper.zone])
        {
            if (Mapper.zoneDefaultLevel[mapper.zone] != mapper.level)
                buff = extraBits(mapper, buff);
        }
        else if (mapper.level != 0)
            buff = extraBits(mapper, buff);
        else if ((!$WH.isset('g_mapperData') || !g_mapperData[mapper.zone]) && (!$WH.isset('g_mapper_data') || !g_mapper_data[mapper.zone]))
            buff = extraBits(mapper, buff);

        return buff;
    }

    function onUpdate()
    {
        if (oldOnUpdate)
            oldOnUpdate(mapper);
        location.replace(getPound(true));
    }

    function render(resizing)
    {
        if (resizing && (scale == desiredScale) && $WH.g_getWindowSize().h > container.offsetHeight)
            return;

        container.style.visibility = 'hidden';

        computeDimensions(0);

        if (!resizing)
        {
            if (!placeholder)
            {
                placeholder = $WH.ce('div');
                placeholder.style.height = '325px';
                placeholder.style.padding = '3px';
                placeholder.style.marginTop = '10px';
            }

            mapper.parent.style.borderWidth = '0px';
            mapper.parent.style.marginTop = '0px';
            mapper.span.style.cursor = 'pointer';
            if (mapper.span.onclick)
                oldOnClick = mapper.span.onclick;
            mapper.span.onclick = Lightbox.hide;
            mapper.span.onmouseover = function() { aCover.style.display = 'block'; };
            mapper.span.onmouseout = function() { setTimeout(function() { if (!aCover.hasMouse) aCover.style.display = 'none'; }, 10); };
            if (mapper.onMapUpdate)
                oldOnUpdate = mapper.onMapUpdate;
            mapper.onMapUpdate = onUpdate;

            if (!tempParent)
            {
                oldParent = mapper.parent.parentNode;
                oldSibling = mapper.parent.nextSibling;
                oldParent.insertBefore(placeholder, mapper.parent);
                $WH.de(mapper.parent);
                $WH.ae(mapDiv, mapper.parent);
            }
            else
            {
                $WH.de(tempParent);
                $WH.ae(mapDiv, tempParent);
            }

            if (location.hash.indexOf('#show') == -1)
                location.replace(getPound(false));
            else if ($WH.isset('mapShower'))
                mapShower.onExpand();
        }

        Lightbox.reveal();

        container.style.visibility = 'visible';
    }

    function onResize()
    {
        render(1);
    }

    function onHide()
    {
        if (oldOnClick)
            mapper.span.onclick = oldOnClick;
        else
            mapper.span.onclick = null;
        oldOnClick = null;
        if (oldOnUpdate)
            mapper.onMapUpdate = oldOnUpdate
        else
            mapper.onMapUpdate = null;
        oldOnUpdate = null;
        mapper.span.style.cursor = '';

        mapper.span.onmouseover = null;
        mapper.span.onmouseout = null;

        if (!tempParent)
        {
            $WH.de(placeholder);
            $WH.de(mapper.parent);
            mapper.parent.style.borderWidth = '';
            mapper.parent.style.marginTop = '';
            if (oldSibling)
                oldParent.insertBefore(mapper.parent, oldSibling);
            else
                $WH.ae(oldParent, mapper.parent);
            oldParent = oldSibling = null;
        }
        else
        {
            $WH.de(tempParent);
            tempParent = null;
        }

        mapper.toggleZoom();

        if (location.hash.indexOf('#show') == -1)
            location.replace('#.');
        else if ($WH.isset('mapShower'))
            mapShower.onCollapse();
    }

    function onShow(dest, first, opt)
    {
        mapper = opt.mapper;
        container = dest;

        if (first)
        {
            dest.className = 'mapviewer';

            screen = $WH.ce('div');
            screen.style.width = '772px';
            screen.style.height = '515px';

            screen.className = 'mapviewer-screen';

            aCover = $WH.ce('a');
            aCover.className = 'mapviewer-cover';
            aCover.href = 'javascript:;';
            aCover.onclick = Lightbox.hide;
            aCover.onmouseover = function() { aCover.hasMouse = true; };
            aCover.onmouseout = function() { aCover.hasMouse = false; };
            var foo = $WH.ce('span');
            var b = $WH.ce('b');
            $WH.ae(b, $WH.ct(LANG.close));
            $WH.ae(foo, b);
            $WH.ae(aCover, foo);
            $WH.ae(screen, aCover);

            mapDiv = $WH.ce('div');
            $WH.ae(screen, mapDiv);

            $WH.ae(dest, screen);

            var aClose = $WH.ce('a');
         // aClose.className = 'dialog-x'; aowow - button not yet renamed
            aClose.className = 'dialog-cancel';
            aClose.href = 'javascript:;';
            aClose.onclick = Lightbox.hide;
            $WH.ae(aClose, $WH.ct(LANG.close));
            $WH.ae(dest, aClose);

            var d = $WH.ce('div');
            d.className = 'clear';
            $WH.ae(dest, d);
        }

        onRender();
    }

    function onRender()
    {
        render();
    }

    this.checkPound = function()
    {
        if (location.hash && location.hash.indexOf('#map') == 0)
        {
            var parts = location.hash.split('=');
            if (parts.length == 2)
            {
                var link = parts[1];

                if (link)
                {
                    /*tempParent = $WH.ce('div');
                    tempParent.id = 'fewuiojfdksl';
                    $WH.ae(document.body, tempParent);
                    var map = new Mapper({ parent: tempParent.id });
                    map.setLink(link, true);
                    map.toggleZoom();*/
                    MapViewer.show({ link: link });
                }
            }
            else
            {
                parts = location.hash.split(':');

                var map = $WH.ge('sjdhfkljawelis');
                if (map)
                    map.onclick();

                if (parts.length == 2)
                {
                    if (!map)
                        MapViewer.show({ link: parts[1]});
                    var subparts = parts[1].split('.');
                    var opts = { zone: subparts[0] };
                    if (subparts.length == 2)
                        opts.level = parseInt(subparts[1])+1;
                    mapper.update(opts);
                    //if (Mapper.multiLevelZones[mapper.zone])
                    //    mapper.setMap(Mapper.multiLevelZones[mapper.zone][floor], floor, true);
                }
            }
        }
    }

    this.show = function(opt)
    {
        $WH.Track.interactiveEvent({
            category: "Zone Maps",
            action: "Show",
            label: opt.link ? opt.link : "General"
        });

        if (opt.link)
        {
            tempParent = $WH.ce('div');
            tempParent.id = 'fewuiojfdksl';
            $WH.aef(document.body, tempParent);             // aowow - aef() insteead of ae() - rather scroll page to top instead of bottom
            var map = new Mapper({ parent: tempParent.id });
            map.setLink(opt.link, true);
            map.toggleZoom();
        }
        else
            Lightbox.show('mapviewer', { onShow: onShow, onHide: onHide, onResize: onResize }, opt);
    }

    $(document).ready(this.checkPound);
};
