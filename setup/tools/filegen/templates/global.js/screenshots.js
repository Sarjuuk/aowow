/*
Global screenshot-related functions
*/
function ss_submitAScreenshot()
{
    tabsContribute.focus(1);
}

function ss_validateForm(f)
{
    if (!f.elements['screenshotfile'].value.length)
    {
        alert(LANG.message_noscreenshot);
        return false;
    }

    return true;
}

function ss_appendSticky()
{
    var _ = $WH.ge('infobox-sticky-ss');

    var type   = g_pageInfo.type;
    var typeId = g_pageInfo.typeId;

    var pos = $WH.in_array(lv_screenshots, 1, function(x) { return x.sticky; });

    if (pos != -1)
    {
        var screenshot = lv_screenshots[pos];

        var a = $WH.ce('a');
        a.href = '#screenshots:id=' + screenshot.id;
        a.onclick = function(e) {
            ScreenshotViewer.show({ screenshots: lv_screenshots, pos: pos });
            return $WH.rf2(e);
        };

        var size = (lv_videos && lv_videos.length ? [120, 90] : [150, 150]);
        var img   = $WH.ce('img'),
            scale = Math.min(size[0] / screenshot.width, size[1] / screenshot.height);

        img.src       = g_staticUrl + '/uploads/screenshots/thumb/' + screenshot.id + '.jpg';
        img.width     = Math.round(scale * screenshot.width);
        img.height    = Math.round(scale * screenshot.height);
        img.className = 'border';
        $WH.ae(a, img);

        $WH.ae(_, a);

        var th = $WH.ge('infobox-screenshots');
        var a = $WH.ce('a');

        if (!th)
        {
            var sections = $('th', _.parentNode);
            th = sections[sections.length - (lv_videos && lv_videos.length ? 2 : 1)];
        }

        $WH.ae(a, $WH.ct(th.textContent + ' (' + lv_screenshots.length + ')'));
        a.href = '#screenshots'
        a.title = $WH.sprintf(LANG.infobox_showall, lv_screenshots.length);
        a.onclick = function() {
            tabsRelated.focus((lv_videos && lv_videos.length) || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)) ? -2 : -1);
            return false;
        };
        $WH.ee(th);
        $WH.ae(th, a);
    }
    else
    {
        var a;

        if (g_user.id > 0)
            a = '<a href="javascript:;" onclick="ss_submitAScreenshot(); return false">';
        else
            a = '<a href="?account=signin">';

        _.innerHTML = $WH.sprintf(LANG.infobox_noneyet, a + LANG.infobox_submitone + '</a>');
    }
}

var g_screenshots = {};

var ScreenshotViewer = new function()
{
    var screenshots,
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

    function computeDimensions(captionExtraHeight)
    {
        var screenshot = screenshots[pos];

        var availHeight = Math.max(50, Math.min(618, $WH.g_getWindowSize().h - 72 - captionExtraHeight));

        if (mode != 1 || screenshot.id || screenshot.resize)
        {
            desiredScale = Math.min(772 / screenshot.width, 618 / screenshot.height);
            scale        = Math.min(772 / screenshot.width, availHeight / screenshot.height);
        }
        else
        {
            desiredScale = scale = 1;
        }

        if (desiredScale > 1)
            desiredScale = 1;

        if (scale > 1)
            scale = 1;

        imgWidth  = Math.round(scale * screenshot.width);
        imgHeight = Math.round(scale * screenshot.height);

        var lbWidth = Math.max(480, imgWidth);

        Lightbox.setSize(lbWidth + 20, imgHeight + 52 + captionExtraHeight);

        if (captionExtraHeight)
        {
            imgDiv.firstChild.width  = imgWidth;
            imgDiv.firstChild.height = imgHeight;
        }
    }

    function getPound(pos)
    {
        var screenshot = screenshots[pos],
            buff = '#screenshots:';

        if (mode == 0)
            buff += 'id=' + screenshot.id;
        else
            buff += collectionId + ':' + (pos + 1);

        return buff;
    }

    function render(resizing)
    {
        if (resizing && (scale == desiredScale) && $WH.g_getWindowSize().h > container.offsetHeight)
            return;

        container.style.visibility = 'hidden';

        var screenshot = screenshots[pos],
            resized = (screenshot.width > 772 || screenshot.height > 618);

        computeDimensions(0);

        var url = (screenshot.url ? screenshot.url : g_staticUrl + '/uploads/screenshots/' + (resized ? 'resized/' : 'normal/') + screenshot.id + '.jpg');

        var html =
            '<img src="' + url + '"'
        + ' width="'  + imgWidth + '"'
        + ' height="' + imgHeight + '"'
        + '>';

        imgDiv.innerHTML = html;

        if (!resizing)
        {
            $WH.Track.interactiveEvent({
                category: 'Screenshots',
                action: 'Show',
                label: screenshot.id + (screenshot.caption && screenshot.caption.length ? ` (${ screenshot.caption })` : '')
            });

            // ORIGINAL

            if (screenshot.url)
                aOriginal.href = url;
            else
                aOriginal.href = g_staticUrl + '/uploads/screenshots/normal/' + screenshot.id + '.jpg';

            // FROM

            if (!screenshot.user && typeof g_pageInfo == 'object')
                screenshot.user = g_pageInfo.username;

            var hasFrom1 = (screenshot.date && screenshot.user),
                hasFrom2 = (screenshots.length > 1);

            if (hasFrom1)
            {
                var postedOn = new Date(screenshot.date),
                    elapsed  = (g_serverTime - postedOn) / 1000;

                var a = divFrom.firstChild.childNodes[1];
                a.href = '?user=' + screenshot.user;
                a.innerHTML = screenshot.user;

                var s = divFrom.firstChild.childNodes[3];

                $WH.ee(s);
                g_formatDate(s, elapsed, postedOn);

                divFrom.firstChild.style.display = '';
            }
            else
                divFrom.firstChild.style.display = 'none';

            var s = divFrom.childNodes[1];
            $WH.ee(s);
            if (screenshot.user)
            {
                if (hasFrom1)
                    $WH.ae(s, $WH.ct(' ' + LANG.dash + ' '));

                var a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = ContactTool.show.bind(ContactTool, {
                    mode: 3,
                    screenshot: screenshot
                });
                a.className = 'icon-report';
                g_addTooltip(a, LANG.report_tooltip, 'q2');
                $WH.ae(a, $WH.ct(LANG.report));
                $WH.ae(s, a);
            }

            s = divFrom.childNodes[2];

            if (hasFrom2)
            {
                var buff = '';
                if (screenshot.user)
                    buff = LANG.dash;

                buff += (pos + 1) + LANG.lvpage_of + screenshots.length;

                s.innerHTML = buff;
                s.style.display = '';
            }
            else
                s.style.display = 'none';

            divFrom.style.display = (hasFrom1 || hasFrom2 ? '' : 'none');

            // CAPTION

         // aowow - be locale agnostic
         // if (Locale.getId() != LOCALE_ENUS && screenshot.caption)
         //     screenshot.caption = '';

            var hasCaption = (screenshot.caption != null && screenshot.caption.length);
            var hasSubject = (screenshot.subject != null && screenshot.subject.length && screenshot.type && screenshot.typeId);

            if (hasCaption || hasSubject)
            {
                var html = '';

                if (hasSubject)
                {
                    html += LANG.types[screenshot.type][0] + LANG.colon;
                    html += '<a href="?' + g_types[screenshot.type] + '=' + screenshot.typeId + '">';
                    html += screenshot.subject;
                    html += '</a>';
                }

                if (hasCaption)
                {
                    if (hasSubject)
                        html += LANG.dash;

                    html += (screenshot.noMarkup ? screenshot.caption : Markup.toHtml(screenshot.caption, { mode: Markup.MODE_SIGNATURE }));
                }

                divCaption.innerHTML = html;
                divCaption.style.display = '';
            }
            else
                divCaption.style.display = 'none';

            // URLS

            if (screenshots.length > 1)
            {
                aPrev.href = getPound(peekPos(-1));
                aNext.href = getPound(peekPos( 1));

                aPrev.style.display = aNext.style.display = '';
                aCover.style.display = 'none';
            }
            else
            {
                aPrev.style.display = aNext.style.display = 'none';
                aCover.style.display = '';
            }

            location.replace(getPound(pos));
        }

        Lightbox.reveal();

        if (divCaption.offsetHeight > 18)
            computeDimensions(divCaption.offsetHeight - 18);

        container.style.visibility = 'visible';
    }

    function peekPos(change)
    {
        var foo = pos;
        foo += change;

        if (foo < 0)
            foo = screenshots.length - 1;
        else if (foo >= screenshots.length)
            foo = 0;

        return foo;
    }

    function prevScreenshot()
    {
        pos = peekPos(-1);
        onRender();

        return false;
    }

    function nextScreenshot()
    {
        pos = peekPos(1);
        onRender();

        return false;
    }

    function onKeyUp(e)
    {
        e = $WH.$E(e);

        switch (e.keyCode)
        {
            case 37: // Left
                prevScreenshot();
                break;

            case 39: // Right
                nextScreenshot();
                break;
        }
    }

    function onResize()
    {
        render(1);
    }

    function onHide()
    {
        cancelImageLoading();

        if (screenshots.length > 1)
            $WH.dE(document, 'keyup', onKeyUp);

        if (oldHash && mode == 0)
        {
            if (oldHash.indexOf(':id=') != -1)
                oldHash = '#screenshots';

            location.replace(oldHash);
        }
        else
            location.replace('#.');
    }

    function onShow(dest, first, opt)
    {
        if (typeof opt.screenshots == 'string')
        {
            screenshots = g_screenshots[opt.screenshots];
            mode = 1;
            collectionId = opt.screenshots;
        }
        else
        {
            screenshots = opt.screenshots;
            mode = 0;
            collectionId = null;
        }
        container = dest;

        pos = 0;
        if (opt.pos && opt.pos >= 0 && opt.pos < screenshots.length)
            pos = opt.pos;

        if (first)
        {
            dest.className = 'screenshotviewer';

            screen = $WH.ce('div');

            screen.className = 'screenshotviewer-screen';

            aPrev = $WH.ce('a');
            aNext = $WH.ce('a');
            aPrev.className = 'screenshotviewer-prev';
            aNext.className = 'screenshotviewer-next';
            aPrev.href = 'javascript:;';
            aNext.href = 'javascript:;';

            var foo = $WH.ce('span');
            var b = $WH.ce('b');
         // $WH.ae(b, $WH.ct(LANG.previous));
            $WH.ae(foo, b);
            $WH.ae(aPrev, foo);
            var foo = $WH.ce('span');
            var b = $WH.ce('b');
         // $WH.ae(b, $WH.ct(LANG.next));
            $WH.ae(foo, b);
            $WH.ae(aNext, foo);

            aPrev.onclick = prevScreenshot;
            aNext.onclick = nextScreenshot;

            aCover = $WH.ce('a');
            aCover.className = 'screenshotviewer-cover';
            aCover.href = 'javascript:;';
            aCover.onclick = Lightbox.hide;
            var foo = $WH.ce('span');
            var b = $WH.ce('b');
         // $WH.ae(b, $WH.ct(LANG.close));
            $WH.ae(foo, b);
            $WH.ae(aCover, foo);

            $WH.ae(screen, aPrev);
            $WH.ae(screen, aNext);
            $WH.ae(screen, aCover);

            imgDiv = $WH.ce('div');
            $WH.ae(screen, imgDiv);

            $WH.ae(dest, screen);

            var aClose = $WH.ce('a');
            aClose.className = 'screenshotviewer-close';
         // aClose.className = 'dialog-x';
            aClose.href = 'javascript:;';
            aClose.onclick = Lightbox.hide;
            $WH.ae(aClose, $WH.ce('span')); // aowow - 'close' from texture atlas
         // $WH.ae(aClose, $WH.ct(LANG.close));
            $WH.ae(dest, aClose);

            aOriginal = $WH.ce('a');
            aOriginal.className = 'screenshotviewer-original';
         // aOriginal.className = 'dialog-arrow';
            aOriginal.href = 'javascript:;';
            aOriginal.target = '_blank';
            $WH.ae(aOriginal, $WH.ce('span')); // aowow - 'original' from texture atlas
         // $WH.ae(aOriginal, $WH.ct(LANG.original));
            $WH.ae(dest, aOriginal);

            divFrom = $WH.ce('div');
            divFrom.className = 'screenshotviewer-from';
            var sp = $WH.ce('span');
            $WH.ae(sp, $WH.ct(LANG.lvscreenshot_from));
            $WH.ae(sp, $WH.ce('a'));
            $WH.ae(sp, $WH.ct(' '));
            $WH.ae(sp, $WH.ce('span'));
            $WH.ae(divFrom, sp);
            $WH.ae(divFrom, $WH.ce('span'));
            $WH.ae(divFrom, $WH.ce('span'));
            $WH.ae(dest, divFrom);

            divCaption = $WH.ce('div');
            divCaption.className = 'screenshotviewer-caption';
            $WH.ae(dest, divCaption);

            var d = $WH.ce('div');
            d.className = 'clear';
            $WH.ae(dest, d);
        }

        oldHash = location.hash;

        if (screenshots.length > 1)
            $WH.aE(document, 'keyup', onKeyUp);

        onRender();
    }

    function onRender()
    {
        var screenshot = screenshots[pos];
        if (!screenshot.width || !screenshot.height)
        {
            if (loadingImage)
            {
                loadingImage.onload = null;
                loadingImage.onerror = null;
            }
            else
            {
                container.className = '';
                lightboxComponents = [];
                while (container.firstChild)
                {
                    lightboxComponents.push(container.firstChild);
                    $WH.de(container.firstChild);
                }
            }

            var lightboxTimer = setTimeout(function() {
                screenshot.width = 126;
                screenshot.height = 22;
                computeDimensions(0);
                screenshot.width = null;
                screenshot.height = null;

                var div = $WH.ce('div');
                div.style.margin = '0 auto';
                div.style.width = '126px';
                var img = $WH.ce('img');
                img.src = g_staticUrl + '/images/ui/misc/progress-anim.gif';
                img.width = 126;
                img.height = 22;
                $WH.ae(div, img);
                $WH.ae(container, div);

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
        else
            render();
    }

    function cancelImageLoading()
    {
        if (!loadingImage)
            return;

        loadingImage.onload = null;
        loadingImage.onerror = null;
        loadingImage = null;

        restoreLightbox();
    }

    function restoreLightbox()
    {
        if (!lightboxComponents)
            return;

        $WH.ee(container);
        container.className = 'screenshotviewer';
        for (var i = 0; i < lightboxComponents.length; ++i)
            $WH.ae(container, lightboxComponents[i]);
        lightboxComponents = null;
    }

    this.checkPound = function()
    {
        if (location.hash && location.hash.indexOf('#screenshots') == 0)
        {
            if (!g_listviews['screenshots']) // Standalone screenshot viewer
            {
                var parts = location.hash.split(':');
                if (parts.length == 3)
                {
                    var collection = g_screenshots[parts[1]],
                        p = parseInt(parts[2]);

                    if (collection && p >= 1 && p <= collection.length)
                    {
                        ScreenshotViewer.show({
                            screenshots: parts[1],
                            pos: p - 1
                        });
                    }
                }
            }
        }
    }

    this.show = function(opt)
    {
        Lightbox.show('screenshotviewer', {
            onShow: onShow,
            onHide: onHide,
            onResize: onResize
        }, opt);
    }

    $(document).ready(this.checkPound);
};
