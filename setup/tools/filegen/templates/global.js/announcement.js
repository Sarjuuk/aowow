var Announcement = function(opt)
{
    if (!opt)
        opt = {};

    $WH.cO(this, opt);

    if (this.parent)
        this.parentDiv = $WH.ge(this.parent);
    else
        return;

    if (g_user.id > 0 && (!g_cookiesEnabled() || g_getWowheadCookie('announcement-' + this.id) == 'closed'))
        return;

    this.initialize();
};

Announcement.prototype = {
    initialize: function()
    {
     // aowow - animation fix
     // this.parentDiv.style.display = 'none';
        this.parentDiv.style.opacity = '0';

        if (this.mode === undefined || this.mode == 1)
            this.parentDiv.className = 'announcement announcement-contenttop';
        else
            this.parentDiv.className = 'announcement announcement-pagetop';

        var div = this.innerDiv = $WH.ce('div');
        div.className = 'announcement-inner text';
        this.setStyle(this.style);

        var a = null;
        var id = parseInt(this.id);

        if (g_user && (g_user.roles & (U_GROUP_ADMIN|U_GROUP_BUREAU)) > 0 && Math.abs(id) > 0)
        {
            if (id < 0)
            {
                a = $WH.ce('a');
                a.style.cssFloat = a.style.styleFloat = 'right';
                a.href = '?admin=announcements&id=' + Math.abs(id) + '&status=2';
                a.onclick = function() { return confirm('Are you sure you want to delete ' + this.name + '?'); };
                $WH.ae(a, $WH.ct('Delete'));
                var small = $WH.ce('small');
                $WH.ae(small, a);
                $WH.ae(div, small);

                a = $WH.ce('a');
                a.style.cssFloat = a.style.styleFloat = 'right';
                a.style.marginRight = '10px';
                a.href = '?admin=announcements&id=' + Math.abs(id) + '&status=' + (this.status == 1 ? 0 : 1);
                a.onclick = function() { return confirm('Are you sure you want to delete ' + this.name + '?'); };
                $WH.ae(a, $WH.ct((this.status == 1 ? 'Disable' : 'Enable')));
                var small = $WH.ce('small');
                $WH.ae(small, a);
                $WH.ae(div, small);
            }

            a = $WH.ce('a');
            a.style.cssFloat = a.style.styleFloat = 'right';
            a.style.marginRight = '22px';
            a.href = '?admin=announcements&id=' + Math.abs(id) + '&edit';
            $WH.ae(a, $WH.ct('Edit announcement'));
            var small = $WH.ce('small');
            $WH.ae(small, a);
            $WH.ae(div, small);
        }

        var markupDiv = $WH.ce('div');
        markupDiv.id = this.parent + '-markup';
        $WH.ae(div, markupDiv);

        if (id >= 0)
        {
            a = $WH.ce('a');

            a.id = 'closeannouncement';
            a.href = 'javascript:;';
            a.className = 'announcement-close';
            if (this.nocookie)
                a.onclick = this.hide.bind(this);
            else
                a.onclick = this.markRead.bind(this);

            $WH.ae(div, a);
            g_addTooltip(a, LANG.close);
        }

        $WH.ae(div, $WH.ce('div', { style: { clear: 'both' } }));

        $WH.ae(this.parentDiv, div);

        this.setText(this.text);

        setTimeout(this.show.bind(this), 500); // Delay to avoid visual lag
    },

    show: function()
    {
        // $(this.parentDiv).animate({
            // opacity: 'show',
            // height: 'show'
        // },{
            // duration: 333
        // });

        // aowow - animation fix - jQuery.animate hard snaps into place after half the time passed
        this.parentDiv.style.opacity = '100';
        this.parentDiv.style.height = (this.parentDiv.offsetHeight + 10) + 'px';

        $WH.Track.nonInteractiveEvent({
            category: 'Announcements',
            action:   'Show',
            label:    '' + this.name
        });
    },

    hide: function()
    {
        // $(this.parentDiv).animate({
            // opacity: 'hide',
            // height: 'hide'
        // },{
            // duration: 200
        // });

        // aowow - animation fix - jQuery.animate hard snaps into place after half the time passed
        this.parentDiv.style.opacity = '0';
        this.parentDiv.style.height = '0px';
        setTimeout(function() {
            this.parentDiv.style.display = 'none';
        }.bind(this), 400);
    },

    markRead: function()
    {
        $WH.Track.interactiveEvent({
            category: 'Announcements',
            action:   'Close',
            label:    '' + this.name
        });
        g_setWowheadCookie('announcement-' + this.id, 'closed');
        this.hide();
    },

    setStyle: function(style)
    {
        this.style = style;
        this.innerDiv.setAttribute('style', style);
    },

    setText: function(text)
    {
        this.text = text;
        Markup.printHtml(this.text, this.parent + '-markup');

        let parent = $WH.ge(this.parent + '-markup');
        $WH.qsa('a', parent).forEach(link => {
            $WH.aE(link, 'click', () => {
                let label = 'unknown';
                let txt   = g_getFirstTextContent(link);
                if (txt)
                    label = g_urlize(txt).substr(0, 80);
                else if (link.title)
                    label = g_urlize(link.title).substr(0, 80);
                else if (link.id)
                    label = g_urlize(link.id).substr(0, 80);

                label = `${ this.id || 0 }-${ label }`;
                $WH.Track.linkClick(link, { category: 'Announcements', label: label });
            });
        });
    }
};
