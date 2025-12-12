/*
Global video-related functions
*/

var vi_thumbnails = {
    1: 'https://i3.ytimg.com/vi/$1/default.jpg' // YouTube
};

var vi_siteurls = {
    1: 'https://www.youtube.com/watch?v=$1' // YouTube
};

var vi_sitevalidation = [
    /https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([^& ]{11})/i,
    /https?:\/\/(?:www\.)?youtu\.be\/([^& ]{11})/i
];

function vi_submitAVideo()
{
    tabsContribute.focus(2);
}

function vi_validateForm(f)
{
    if (!f.elements['videourl'].value.length)
    {
        alert(LANG.message_novideo);
        return false;
    }

    var urlmatch = false;
    for (var i in vi_sitevalidation)
    {
        if (f.elements['videourl'].value.match(vi_sitevalidation[i]))
        {
            urlmatch = true;
            break;
        }
    }

    if (!urlmatch)
    {
        alert(LANG.message_novideo);
        return false;
    }

    return true;
}

function vi_appendSticky()
{
    var _ = $WH.ge('infobox-sticky-vi');

    var type   = g_pageInfo.type;
    var typeId = g_pageInfo.typeId;

    var pos = $WH.in_array(lv_videos, 1, function(x) { return x.sticky; });

    if (pos != -1)
    {
        var video = lv_videos[pos];

        var a = $WH.ce('a');
        a.href = '#videos:id=' + video.id;
        a.onclick = function(e) {
            VideoViewer.show({ videos: lv_videos, pos: pos });
            return $WH.rf2(e);
        };

        var img = $WH.ce('img');
        img.src = $WH.sprintf(vi_thumbnails[video.videoType].replace(/\/default\.jpg/, '/mqdefault.jpg'), video.videoId);
        img.className = 'border';
        $WH.ae(a, img);

        $WH.ae(_, a);

        var th = $WH.ge('infobox-videos');
        var a = $WH.ce('a');

        if (!th)
        {
            var sections = $('th', _.parentNode);
            th = sections[sections.length - (lv_videos && lv_videos.length ? 2 : 1)];
        }

        $WH.ae(a, $WH.ct(th.textContent + ' (' + lv_videos.length + ')'));
        a.href = '#videos'
        a.title = $WH.sprintf(LANG.infobox_showall, lv_videos.length);
        a.onclick = function() {
            tabsRelated.focus(-1);
            return false;
        };
        $WH.ee(th);
        $WH.ae(th, a);
    }
    // aowow - video submission opened for all
    else // if (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO))
    {
        var a;

        if (g_user.id > 0)
            a = '<a href="javascript:;" onclick="vi_submitAVideo(); return false">';
        else
            a = '<a href="?account=signin">';

        _.innerHTML = $WH.sprintf(LANG.infobox_noneyet, a + LANG.infobox_suggestone + '</a>');
    }
 // else
 //     $('#infobox-videos,#infobox-sticky-vi').closest('tr').css('display', 'none');
}

var g_videos = [];

var VideoViewer = new function()
{
    var videos,
        pos,
        imgWidth,
        imgHeight,
        scale,

        oldHash,
        mode = 0,
        collectionId,
        pageTitle, // IE flash embed fix

        container,
        screen,
        imgDiv,
        aPrev,
        aNext,
        aCover,
        aOriginal,
        divFrom,
        divCaption;

    function computeDimensions()
    {
        var video = videos[pos];

        var
            captionExtraHeight = Math.max(divCaption.offsetHeight - 18, 0),
            availHeight = Math.max(50, Math.min(520, $WH.g_getWindowSize().h - 72 - captionExtraHeight)),
            scale = Math.min(1, availHeight / 520);

        imgWidth  = Math.round(scale * 880);
        imgHeight = Math.round(scale * 520);

        aPrev.style.height = aNext.style.height = aCover.style.height = (imgHeight - 95) + 'px';
        Lightbox.setSize(Math.max(480, imgWidth) + 20, imgHeight + 52 + captionExtraHeight);
    }

    function getPound(pos)
    {
        var video = videos[pos],
            buff = '#videos:';

        if (mode == 0)
            buff += 'id=' + video.id;
        else
            buff += collectionId + ':' + (pos + 1);

        return buff;
    }

    function render(resizing)
    {
        if (resizing && (scale == 1) && $WH.g_getWindowSize().h > container.offsetHeight)
            return;

        container.style.visibility = 'hidden';

        var video = videos[pos];

        computeDimensions();

        if (!resizing)
        {
            var hasCaption = (video.caption != null && video.caption.length);

            $WH.Track.interactiveEvent({
                category: 'Videos',
                action: 'Show',
                label: video.id + (hasCaption ? ` (${ video.caption })` : '')
            });

            if (video.videoType == 1)
                imgDiv.innerHTML = Markup.toHtml('[youtube=' + video.videoId + ' width=' + imgWidth + ' height=' + imgHeight + ' autoplay=true]', {mode:Markup.MODE_ARTICLE});

            // ORIGINAL

            aOriginal.href = $WH.sprintf(vi_siteurls[video.videoType], video.videoId);

            // FROM

            if (!video.user && typeof g_pageInfo == 'object')
                video.user = g_pageInfo.username;

            var hasFrom1 = (video.date && video.user),
                hasFrom2 = (videos.length > 1);

            if (hasFrom1)
            {
                var postedOn = new Date(video.date),
                    elapsed  = (g_serverTime - postedOn) / 1000;

                var a = divFrom.firstChild.childNodes[1];
                a.href = '?user=' + video.user;
                a.innerHTML = video.user;

                var s = divFrom.firstChild.childNodes[3];

                $WH.ee(s);
                g_formatDate(s, elapsed, postedOn);

                divFrom.firstChild.style.display = '';
            }
            else
                divFrom.firstChild.style.display = 'none';

            var s = divFrom.childNodes[1];
            $WH.ee(s);

            if (video.user)
            {
                if (hasFrom1)
                    $WH.ae(s, $WH.ct(' ' + LANG.dash + ' '));

                var a = $WH.ce('a');
                a.href = 'javascript:;';
                a.onclick = ContactTool.show.bind(ContactTool, { mode: 5, video: video });
                a.className = 'icon-report';
                g_addTooltip(a, LANG.report_tooltip, 'q2');
                $WH.ae(a, $WH.ct(LANG.report));
                $WH.ae(s, a);
            }

            s = divFrom.childNodes[2];

            if (hasFrom2)
            {
                var buff = '';
                if (video.user)
                    buff = LANG.dash;

                buff += (pos + 1) + LANG.lvpage_of + videos.length;

                s.innerHTML = buff;
                s.style.display = '';
            }
            else
                s.style.display = 'none';

            divFrom.style.display = (hasFrom1 || hasFrom2 ? '' : 'none');

            // CAPTION

            var hasSubject = (video.subject != null && video.subject.length && video.type && video.typeId);

            if (hasCaption || hasSubject)
            {
                var html = '';

                if (hasSubject)
                {
                    html += LANG.types[video.type][0] + LANG.colon;
                    html += '<a href="?' + g_types[video.type] + '=' + video.typeId + '">';
                    html += video.subject;
                    html += '</a>';
                }

                if (hasCaption)
                {
                    if (hasSubject)
                        html += LANG.dash;

                    html += (video.noMarkup ? video.caption : Markup.toHtml(video.caption, { mode: Markup.MODE_SIGNATURE }));
                }

                divCaption.innerHTML = html;
                divCaption.style.display = '';
            }
            else
                divCaption.style.display = 'none';

            // URLS

            if (videos.length > 1)
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
        else
            $('object, embed', imgDiv).each(function() {
                this.width  = imgWidth;
                this.height = imgHeight
            });

        Lightbox.reveal();

        container.style.visibility = 'visible';

        setTimeout(fixTitle, 1);
    }

    function peekPos(change)
    {
        var foo = pos;
        foo += change;

        if (foo < 0)
            foo = videos.length - 1;
        else if (foo >= videos.length)
            foo = 0;

        return foo;
    }

    function prevVideo()
    {
        pos = peekPos(-1);
        render();

        return false;
    }

    function nextVideo()
    {
        pos = peekPos(1);
        render();

        return false;
    }

    function fixTitle()
    {
        if (pageTitle)
            document.title = pageTitle;
    }

    function onKeyUp(e)
    {
        e = $WH.$E(e);

        switch (e.keyCode)
        {
            case 37: // Left
                prevVideo();
                break;

            case 39: // Right
                nextVideo();
                break;
        }
    }

    function onResize()
    {
        render(1);
    }

    function onHide()
    {
        $WH.ee(imgDiv);

        if (videos.length > 1)
            $WH.dE(document, 'keyup', onKeyUp);

        if (oldHash && mode == 0)
        {
            if (oldHash.indexOf(':id=') != -1)
                oldHash = '#videos';

            location.replace(oldHash);
        }
        else
            location.replace('#.');

        fixTitle();
    }

    function onShow(dest, first, opt)
    {
        if (typeof opt.videos == 'string')
        {
            videos = g_videos[opt.videos];
            mode = 1;
            collectionId = opt.videos;
        }
        else
        {
            videos = opt.videos;
            mode = 0;
            collectionId = null;
        }
        container = dest;

        pos = 0;
        if (opt.pos && opt.pos >= 0 && opt.pos < videos.length)
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

            aPrev.onclick = prevVideo;
            aNext.onclick = nextVideo;

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
         // aClose.className = 'dialog-x';
            aClose.className = 'screenshotviewer-close';
            aClose.href = 'javascript:;';
            aClose.onclick = Lightbox.hide;
         // $WH.ae(aClose, $WH.ct(LANG.close));
            $WH.ae(aClose, $WH.ce('span'));
            $WH.ae(dest, aClose);

            aOriginal = $WH.ce('a');
         // aOriginal.className = 'dialog-arrow';
            aOriginal.className = 'screenshotviewer-original';
            aOriginal.href = 'javascript:;';
            aOriginal.target = '_blank';
         // $WH.ae(aOriginal, $WH.ct(LANG.original));
            $WH.ae(aOriginal, $WH.ce('span'));
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

        if (videos.length > 1)
            $WH.aE(document, 'keyup', onKeyUp);

        render();
    }

    this.checkPound = function()
    {
        pageTitle = $('title').html();
        if (location.hash && location.hash.indexOf('#videos') == 0)
        {
            if (!g_listviews['videos']) // Standalone video viewer
            {
                var parts = location.hash.split(':');
                if (parts.length == 3)
                {
                    var collection = g_videos[parts[1]],
                    p = parseInt(parts[2]);

                    if (collection && p >= 1 && p <= collection.length)
                    {
                        VideoViewer.show({
                            videos: parts[1],
                            pos: p - 1
                        });
                    }
                }
            }
        }
    }

    this.show = function(opt)
    {
        Lightbox.show('videoviewer', {
            onShow: onShow,
            onHide: onHide,
            onResize: onResize
        }, opt);
        return false;
    }

    $(document).ready(this.checkPound);
};
