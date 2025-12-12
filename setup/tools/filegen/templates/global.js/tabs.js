function Tabs(opt)
{
    $WH.cO(this, opt);

    if (this.parent)
        this.parent = $WH.ge(this.parent);
    else
        return;

    this.selectedTab = -1;

    // spiffyjr - This could eventually be removed. Refactored to remove multiple ul/li's.
    this.uls = [];

    this.tabs = [];
    this.nShows = 0;
    if (this.poundable == null)
        this.poundable = 1;

    this.poundedTab = null;

    if (this.onLoad == null)
        this.onLoad = Tabs.onLoad.bind(this);

    if (this.onShow == null)
        this.onShow = Tabs.onShow.bind(this);

    if (this.onHide)
        this.onHide = this.onHide.bind(this);

    this.trackClick = Tabs.trackClick.bind(this);
}

Tabs.prototype = {
    add: function(caption, opt)
    {
        var _,
            index = this.tabs.length;

        _ = {
            caption: caption,
            index: index,
            owner: this
        };
        $WH.cO(_, opt);

        this.tabs.push(_);

        return index;
    },

    hide: function(index, visible)
    {
        if (this.tabs[index])
        {
            var selectedTab = this.selectedTab;

            if (index == 0 && selectedTab == -1)
                this.poundedTab = this.selectedTab = selectedTab = 0;

            if (index != this.poundedTab)
                this.selectedTab = -1;

            this.tabs[index].hidden = !visible;
            this.flush();

            if (!visible && index == selectedTab)
            {
                this.selectedTab = selectedTab;
                for (var i = 0, len = this.tabs.length; i < len; ++i)
                {
                    if (i != index && !this.tabs[i].hidden)
                        return this.show(i, 1);
                }
            }
        }
    },

    unlock: function(index, locked)
    {
        if (this.tabs[index])
        {
            this.tabs[index].locked = locked;
            _ = $WH.gE(this.uls[0], 'a');

            $('.icon-lock', _[index]).remove();

            if (locked)
                $('div, b', _[index]).prepend('<span class="icon-lock" />');

            var _ = location.hash.substr(1).split(':')[0];
            if (this.tabs[index].id == _)
                this.show(index, 1);
        }
    },

    focus: function(index)
    {
        if (index < 0)
            index = this.tabs.length + index;

        this.forceScroll = 1;
        $WH.gE(this.uls[0], 'a')[index].onclick({}, true);
        this.forceScroll = null;
    },

    show: function(index, forceClick)
    {
        var _;

        if (isNaN(index) || index < 0)
            index = 0;
        else if (index >= this.tabs.length)
            index = this.tabs.length - 1;

        if ((forceClick == null && index == this.selectedTab) || this.tabs[index].hidden)
            return;

        if (this.tabs[index].locked)
            return this.onShow(this.tabs[index], this.tabs[this.selectedTab]);

        if (this.selectedTab != -1)
        {
            _ = this.tabs[this.selectedTab];

            if (this.onHide && !this.onHide(_))
                return;

            if (_.onHide && !_.onHide())
                return;
        }

        ++this.nShows;

        _ = $WH.gE(this.uls[0], 'a');
        if (this.selectedTab != -1)
            _[this.selectedTab].className = '';

        _[index].className = 'selected';

        _ = this.tabs[index];
        if (_.onLoad)
        {
            _.onLoad();
            _.onLoad = null;
        }

        this.onShow(this.tabs[index], this.tabs[this.selectedTab]);

        if (_.onShow)
            _.onShow(this.tabs[this.selectedTab]);

        this.selectedTab = index;
    },

    flush: function(defaultTab)
    {
        var _, l, a, b, d, d2;

        var container = $WH.ce('div');
        container.className = 'tabs-container';

        this.uls[0] = $WH.ce('ul');
        this.uls[0].className = 'tabs';

        d = $WH.ce('div');
        d.className = 'tabs-levels';

        $WH.ae(container, this.uls[0]);

        for (var i = 0; i < this.tabs.length; ++i)
        {
            var tab = this.tabs[i];

            l = $WH.ce('li');
            b = $WH.ce('b');
            a = $WH.ce('a');

            if (tab.hidden)
                l.style.display = 'none';

            if (this.poundable)
                a.href = '#' + tab.id;
            else
                a.href = 'javascript:;';

            $WH.ns(a);
            a.onclick = Tabs.onClick.bind(tab, a);

            d = $WH.ce('div');

            if (tab.locked)
            {
                s = $WH.ce('span');
                s.className = 'icon-lock';
                $WH.ae(d, s);
            }
            else if (tab.icon)
            {
                s = $WH.ce('span');
                s.className = 'icontiny';
                s.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + tab.icon.toLowerCase() + '.gif)';
                $WH.ae(d, s);
            }

            if (tab.tooltip)
            {
                a.onmouseover = (function(tooltip, e) { $WH.Tooltip.showAtCursor(e, tooltip, 0, 0, 'q'); }).bind(a, tab.tooltip);
                a.onmousemove = $WH.Tooltip.cursorUpdate;
                a.onmouseout  = $WH.Tooltip.hide;
            }

            if (tab['class'])
                d.className = tab['class'];

            $WH.ae(d, $WH.ct(tab.caption));
            $WH.ae(a, d);

            if (tab.locked)
            {
                s = $WH.ce('span');
                s.className = 'icon-lock';
                $WH.ae(b, s);
            }
            else if (tab.icon)
            {
                s = $WH.ce('span');
                s.className = 'icontiny';
                s.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + tab.icon.toLowerCase() + '.gif)';
                $WH.ae(b, s);
            }

            $WH.ae(b, $WH.ct(tab.caption));
            $WH.ae(a, b);
            $WH.ae(l, a);
            $WH.ae(this.uls[0], l);
        }

        $WH.ee(this.parent);
        $WH.ae(this.parent, container);

        if (this.onLoad)
        {
            _ = this.onLoad();
            if (_ != null)
                this.poundedTab = defaultTab = _;
        }

        this.show(defaultTab);
    },

    setTabName: function(index, name)
    {
        this.tabs[index].caption = name;

        var _ = $WH.gE(this.uls[0], 'a');
        g_setTextNodes(_[index], name);
    },

    setTabPound: function(index, pound)
    {
        if (!this.poundable)
            return;

        var _ = $WH.gE(this.uls[0], 'a');
        _[index].href = '#' + this.tabs[index].id + (pound ? ':' + pound : '');
    },

    setTabTooltip: function(index, text)
    {
        this.tabs[index].tooltip = text;

        var _ = $WH.gE(this.uls[0], 'a');
        if (text == null)
            _[index].onmouseover = _[index].onmousemove = _[index].onmouseout = null;
        else
        {
            _[index].onmouseover = function(e) { $WH.Tooltip.showAtCursor(e, text, 0, 0, 'q2'); };
            _[index].onmousemove = $WH.Tooltip.cursorUpdate;
            _[index].onmouseout  = $WH.Tooltip.hide;
        }
    },

    getSelectedTab: function()
    {
        return this.selectedTab;
    }
};

Tabs.onClick = function(a, e, forceClick)
{
    if (forceClick == null && this.index == this.owner.selectedTab)
        return;

    var res = $WH.rf2(e);
    if (res == null)
        return;

    this.owner.show(this.index, forceClick);

    if (this.owner.poundable && !this.locked)
    {
        var _ = a.href.indexOf('#');
        _ != -1 && location.replace(a.href.substr(_));
    }

    return res;
};

Tabs.onLoad = function()
{
    if (!this.poundable || !location.hash.length)
        return;

    var _ = location.hash.substr(1).split(':')[0];
    if (_)
        return $WH.in_array(this.tabs, _, function(x) {
            if (!x.locked)
                return x.id;
        });
};

Tabs.onShow = function(newTab, oldTab)
{
    var _;

    if (newTab.hidden || newTab.locked)
        return;

    if (oldTab)
        $WH.ge('tab-' + oldTab.id).style.display = 'none';

    if (this.poundedTab != null || oldTab)
        this.trackClick(newTab);

    _ = $WH.ge('tab-' + newTab.id);
    _.style.display = '';

    if (((this.nShows == 1 && this.poundedTab != null && this.poundedTab >= 0) || this.forceScroll) && !this.noScroll)
    {
        var el, padd;
        if (this.__st)
        {
            el = this.__st;
            padd = 15;
        }
        else
        {
            el = _;
            padd = this.parent.offsetHeight + 15;
        }

        setTimeout($WH.g_scrollTo.bind(null, el, padd), 10);
    }
};

Tabs.trackClick = function(tab)
{
    if (!this.trackable || tab.tracked)
        return;

    $WH.Track.interactiveEvent({
        category: 'Tab Click',
        action:   'Page: ' + this.trackable,
        label:    'Tab: ' + tab.id
    });

    tab.tracked = 1;
}
