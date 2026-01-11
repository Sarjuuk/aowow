var g_listviews = {};

function Listview(opt)
{
    $WH.cO(this, opt);

    if (this.id)
    {
        var divId = (this.tabs ? 'tab-' : 'lv-') + this.id;
        if (this.parent)
        {
            var d = $WH.ce('div');
            d.id = divId;
            $WH.ae($WH.ge(this.parent), d);
            this.container = d;
        }
        else
            this.container = $WH.ge(divId);
    }
    else
        return;

    var get = $WH.g_getGets();
    if ((get.debug != null || g_user.debug) && g_user.roles & U_GROUP_MODERATOR)
        this.debug = true;

    if (this.template && Listview.templates[this.template])
        this.template = Listview.templates[this.template];
    else
        return;

    g_listviews[this.id] = this;

    if (this.data == null)
        this.data = [];

    if (this.poundable == null)
    {
        if (this.template.poundable != null)
            this.poundable = this.template.poundable;
        else
            this.poundable = true;
    }

    if (this.searchable == null)
    {
        if (this.template.searchable != null)
            this.searchable = this.template.searchable;
        else
            this.searchable = false;
    }

    if (this.filtrable == null)
    {
        if (this.template.filtrable != null)
            this.filtrable = this.template.filtrable;
        else
            this.filtrable = false;
    }

    if (this.sortable == null)
    {
        if (this.template.sortable != null)
            this.sortable = this.template.sortable;
        else
            this.sortable = true;
    }

    if (this.customPound == null)
    {
        if (this.template.customPound != null)
            this.customPound = this.template.customPound;
        else
            this.customPound = false;
    }

    if (this.data.length == 1)
    {
        this.filtrable  = false;
        this.searchable = false;
    }

    if (this.searchable && this.searchDelay == null)
    {
        if (this.template.searchDelay != null)
            this.searchDelay = this.template.searchDelay;
        else
            this.searchDelay = 333;
    }

    if (this.clickable == null)
    {
        if (this.template.clickable != null)
            this.clickable = this.template.clickable;
        else
            this.clickable = true;
    }

    if (this.hideBands == null)
        this.hideBands = this.template.hideBands;

    if (this.hideNav == null)
        this.hideNav = this.template.hideNav;

    if (this.hideHeader == null)
        this.hideHeader = this.template.hideHeader;

    if (this.hideCount == null)
        this.hideCount = this.template.hideCount;

    if (this.computeDataFunc == null && this.template.computeDataFunc != null)
        this.computeDataFunc = this.template.computeDataFunc;

    if (this.createCbControls == null && this.template.createCbControls != null)
        this.createCbControls = this.template.createCbControls;

    if (this.template.onBeforeCreate != null)
    {
        if (this.onBeforeCreate == null)
            this.onBeforeCreate = this.template.onBeforeCreate;
        else
            this.onBeforeCreate = [this.template.onBeforeCreate, this.onBeforeCreate];
    }

    if (this.onAfterCreate == null && this.template.onAfterCreate != null)
        this.onAfterCreate = this.template.onAfterCreate;

    if (this.onNoData == null && this.template.onNoData != null)
        this.onNoData = this.template.onNoData;

    if (this.createNote == null && this.template.createNote != null)
        this.createNote = this.template.createNote;

    if (this.sortOptions == null && this.template.sortOptions != null)
        this.sortOptions = this.template.sortOptions;

    if (this.customFilter == null && this.template.customFilter != null)
        this.customFilter = this.template.customFilter;

    if (this.onSearchSubmit == null && this.template.onSearchSubmit != null)
        this.onSearchSubmit = this.template.onSearchSubmit;

    if (this.getItemLink == null && this.template.getItemLink != null)
        this.getItemLink = this.template.getItemLink;

    if (this.clip == null && this.template.clip != null)
        this.clip = this.template.clip;

    if (this.clip || this.template.compute || this.id == 'topics' || this.id == 'recipes')
        this.debug = false; // Don't add columns to picker windows

    if (this.mode == null)
        this.mode = this.template.mode;

    if (this.template.noStyle != null)
        this.noStyle = this.template.noStyle;

    if (this.nItemsPerPage == null)
    {
        if (this.template.nItemsPerPage != null)
            this.nItemsPerPage = this.template.nItemsPerPage;
        else
            this.nItemsPerPage = 50;
    }

    this.nItemsPerPage |= 0;
    if (this.nItemsPerPage <= 0)
        this.nItemsPerPage = 0;

    this.nFilters = 0;
    this.resetRowVisibility();

    if (this.mode == Listview.MODE_TILED)
    {
        if (this.nItemsPerRow == null)
        {
            var ipr = this.template.nItemsPerRow;
            this.nItemsPerRow = (ipr != null ? ipr : 4);
        }
        this.nItemsPerRow |= 0;
        if (this.nItemsPerRow <= 1)
            this.nItemsPerRow = 1;
    }
    else if (this.mode == Listview.MODE_CALENDAR)
    {
        this.dates         = [];
        this.nItemsPerRow  = 7; // Days per row
        this.nItemsPerPage = 1; // Months per page
        this.nDaysPerMonth = [];

        if (this.template.startOnMonth != null)
            this.startOnMonth = this.template.startOnMonth;
        else
            this.startOnMonth = new Date();

        this.startOnMonth.setDate(1);
        this.startOnMonth.setHours(0, 0, 0, 0);

        if (this.nMonthsToDisplay == null)
        {
            if (this.template.nMonthsToDisplay != null)
                this.nMonthsToDisplay = this.template.nMonthsToDisplay;
            else
                this.nMonthsToDisplay = 1;
        }

        var y = this.startOnMonth.getFullYear(),
            m = this.startOnMonth.getMonth();

        for (var j = 0; j < this.nMonthsToDisplay; ++j)
        {
            var date = new Date(y, m + j, 32);
            this.nDaysPerMonth[j] = 32 - date.getDate();
            for (var i = 1; i <= this.nDaysPerMonth[j]; ++i)
                this.dates.push({ date: new Date(y, m + j, i) });
        }

        if (this.template.rowOffset != null)
            this.rowOffset = this.template.rowOffset;
    }
    else
        this.nItemsPerRow = 1;

    this.columns = [];
    for (var i = 0, len = this.template.columns.length; i < len; ++i)
    {
        var
            ori = this.template.columns[i],
            cpy = {};

            $WH.cO(cpy, ori);

        this.columns.push(cpy);
    }

    // ************************
    // Extra Columns

    if (this.extraCols != null)
    {
        for (var i = 0, len = this.extraCols.length; i < len; ++i)
        {
            var pos = null;
            var col = this.extraCols[i];

            if (col.after || col.before)
            {
                var index = $WH.in_array(this.columns, (col.after ? col.after : col.before), function(x) { return x.id; } );

                if (index != -1)
                    pos = (col.after ? index + 1 : index);
            }

            if (pos == null)
                pos = this.columns.length;

            if (col.id == 'debug-id')
                this.columns.splice(0, 0, col);
            else
                this.columns.splice(pos, 0, col);
        }
    }

    // ************************
    // Visibility

    this.visibility = [];

    var visibleCols = [],
        hiddenCols  = [];

    if (this.visibleCols != null)
        $WH.array_walk(this.visibleCols, function(x) { visibleCols[x] = 1; });

    if (this.hiddenCols != null)
        $WH.array_walk(this.hiddenCols, function(x) { hiddenCols[x] = 1; });

    if ($.isArray(this.sortOptions))
    {
        for (var i = 0, len = this.sortOptions.length; i < len; ++i)
        {
            var sortOpt = this.sortOptions[i];
            if (visibleCols[sortOpt.id] != null || (!sortOpt.hidden && hiddenCols[sortOpt.id] == null))
                this.visibility.push(i);
        }
    }
    else
    {
        for (var i = 0, len = this.columns.length; i < len; ++i)
        {
            var col = this.columns[i];
            if (visibleCols[col.id] != null || (!col.hidden && hiddenCols[col.id] == null))
                this.visibility.push(i);
        }
    }

    // ************************
    // Sort

    if (this.sort == null && this.template.sort)
    {
        this.sort = this.template.sort.slice(0);
    }

    if (this.sort != null)
    {
        var sortParam = this.sort;
        this.sort = [];
        for (var i = 0, len = sortParam.length; i < len; ++i)
        {
            var col = parseInt(sortParam[i]);
            if (isNaN(col))
            {
                var desc = 0;
                if (sortParam[i].charAt(0) == '-')
                {
                    desc = 1;
                    sortParam[i] = sortParam[i].substring(1);
                }
                var index = $WH.in_array(this.columns, sortParam[i], function(x) { return x.id; } );
                if (index != -1)
                {
                    if (desc)
                        this.sort.push(-(index + 1));
                    else
                        this.sort.push(index + 1);
                }
            }
            else
                this.sort.push(col);
        }
    }
    else
        this.sort = [];

    if (this.debug)
    {
        this.columns.splice(0, 0, {
            id: 'debug-id',
            compute: function(data, td)
            {
                if (data.id)
                {
                    let pre = $WH.ce('pre', { style: { display: 'inline', margin: '0' }}, $WH.ct(data.id));
                    $WH.clickToCopy(pre);
                    $WH.ae(td, pre);
                }
            },
            getVisibleText: function(data)
            {
                return data.id || '';
            },
            getValue: function(data)
            {
                return data.id || 0;
            },
            sortFunc: function(a, b, col)
            {
                if (a.id == null)
                    return -1;
                else if (b.id == null)
                    return 1;

                return $WH.strcmp(a.id, b.id);
            },
            name: 'ID',
            width: '5%',
            tooltip: 'ID'
        });

        this.visibility.splice(0, 0, -1);

        for (var i = 0, len = this.visibility.length; i < len; ++i)
            this.visibility[i] = this.visibility[i] + 1;

        for (var i = 0, len = this.sort.length; i < len; ++i) {
            if (this.sort[i] < 0)
                this.sort[i] = this.sort[i] - 1;
            else
                this.sort[i] = this.sort[i] + 1;
        }
    }

    if (this.tabs)
    {
        this.tabIndex = this.tabs.add(this.getTabName(), {
            id:     this.id,
            onLoad: this.initialize.bind(this)
        });

        this.tabClick = Tabs.trackClick.bind(this.tabs, this.tabs.tabs[this.tabIndex]);
    }
    else
        this.initialize();
}

Listview.MODE_DEFAULT  = 0;
Listview.MODE_CHECKBOX = 1;
Listview.MODE_DIV      = 2;
Listview.MODE_TILED    = 3;
Listview.MODE_CALENDAR = 4;
Listview.MODE_FLEXGRID = 5;

Listview.prototype = {
    initialize: function()
    {
        if (this.data.length)
        {
            if (this.computeDataFunc != null)
            {
                for (var i = 0, len = this.data.length; i < len; ++i)
                    this.computeDataFunc(this.data[i]);
            }
        }

        if (this.tabs)
        {
            this.pounded = (this.tabs.poundedTab == this.tabIndex);
            if (this.pounded)
                this.readPound();
        }
        else
            this.readPound();

        this.applySort();

        var obcResult;
        if (this.onBeforeCreate != null)
        {
            if (typeof this.onBeforeCreate == 'function')
                obcResult = this.onBeforeCreate();
            else
            {
                for (var i = 0; i < this.onBeforeCreate.length; ++i)
                    (this.onBeforeCreate[i].bind(this))();
            }
        }

        this.noData = $WH.ce('div');
        this.noData.className = 'listview-nodata text';

        if (this.mode == Listview.MODE_DIV)
        {
            this.mainContainer = this.mainDiv = $WH.ce('div');
            if (!this.noStyle)
                this.mainContainer.className = 'listview-mode-div';
        }
        else if (this.mode == Listview.MODE_FLEXGRID)
        {
            this.mainContainer = this.mainDiv = $WH.ce('div', { className: 'listview-mode-flexgrid' });
            this.mainContainer.setAttribute('data-cell-min-width', this.template.cellMinWidth);
            if (this.clickable)
                this.mainContainer.className += ' clickable';

            var layout = $('.layout');
            var totalWidth = parseInt(layout.css('max-width')) - (parseInt(layout.css('padding-left')) || 0) - (parseInt(layout.css('padding-right')) || 0);
            var slots = Math.floor(totalWidth / this.template.cellMinWidth);
            var extraStyle = '.listview-mode-flexgrid[data-cell-min-width="' + this.template.cellMinWidth + '"] > div {min-width:' + this.template.cellMinWidth + "px;width:" + (100 / slots) + "%}";
            while (slots--)
            {
                if (slots)
                {
                    extraStyle += "\n@media screen and (max-width: " + (((slots + 1) * this.template.cellMinWidth) - 1 + 40) + "px) {";
                    extraStyle += '\n    .listview-mode-flexgrid[data-cell-min-width="' + this.template.cellMinWidth + '"] > div {width:' + (100 / slots) + "%}";
                    extraStyle += "\n}"
                }
            }

            $("<style/>").text(extraStyle).appendTo(document.head)
        }
        else
        {
            this.mainContainer = this.table = $WH.ce('table');
            this.thead = $WH.ce('thead');
            this.tbody = $WH.ce('tbody');

            if (this.clickable)
                this.tbody.className = 'clickable';

            if (this.mode == Listview.MODE_TILED || this.mode == Listview.MODE_CALENDAR)
            {
                if (!this.noStyle)
                    this.table.className = 'listview-mode-' + (this.mode == Listview.MODE_TILED ? 'tiled' : 'calendar');

                var
                    width = (100 / this.nItemsPerRow) + '%',
                    colGroup = $WH.ce('colgroup'),
                    col;

                for (var i = 0; i < this.nItemsPerRow; ++i)
                {
                    col = $WH.ce('col');
                    col.style.width = width;
                    $WH.ae(colGroup, col);
                }

                $WH.ae(this.mainContainer, colGroup);

                if (this.sortOptions)
                    setTimeout((function() { this.updateSortArrow() }).bind(this), 0);
            }
            else
            {
                if (!this.noStyle)
                    this.table.className = 'listview-mode-default';

                this.createHeader();
                this.updateSortArrow();
            }

            $WH.ae(this.table, this.thead);
            $WH.ae(this.table, this.tbody);
        }

        this.createBands();

        if (this.customFilter != null)
            this.updateFilters();

        this.updateNav();
        this.refreshRows();
        this.updateBrowseSession();

        if (this.onAfterCreate != null)
            this.onAfterCreate(obcResult);
    },

    createHeader: function()
    {
        var tr = $WH.ce('tr');

        if (this.mode == Listview.MODE_CHECKBOX)
        {
            var
                th  = $WH.ce('th'),
                div = $WH.ce('div'),
                a   = $WH.ce('a');

            th.style.width = '33px';

            a.href = 'javascript:;';
            a.className = 'listview-cb';
            $WH.ns(a);
            $WH.ae(a, $WH.ct(String.fromCharCode(160)));

            $WH.ae(div, a);
            $WH.ae(th, div);
            $WH.ae(tr, th);
        }

        for (var i = 0, len = this.visibility.length; i < len; ++i)
        {
            var
                reali = this.visibility[i],
                col = this.columns[reali],
                th = $WH.ce('th');
                div = $WH.ce('div'),
                a = $WH.ce('a'),
                outerSpan = $WH.ce('span'),
                innerSpan = $WH.ce('span');

            col.__th = th;

            if (this.filtrable && (col.filtrable == null || col.filtrable))
            {
                a.onmouseup = Listview.headerClick.bind(this, col, reali);
                a.onclick = a.oncontextmenu = $WH.rf;
            }
            else if (this.sortable)
            {
                a.href = 'javascript:;';
                a.onclick = this.sortBy.bind(this, reali + 1);
            }

            if (a.onclick)
            {
                a.onmouseover = Listview.headerOver.bind(this, a, col);
                a.onmouseout  = $WH.Tooltip.hide;
                $WH.ns(a);
            }
            else
                a.className = 'static';

            if (col.width != null)
                th.style.width = col.width;

            if (col.align != null)
                th.style.textAlign = col.align;

            if (col.span != null)
                th.colSpan = col.span;

            $WH.ae(innerSpan, $WH.ct(col.name));
            $WH.ae(outerSpan, innerSpan);

            $WH.ae(a, outerSpan);
            $WH.ae(div, a);
            $WH.ae(th, div);

            $WH.ae(tr, th);
        }

        if (this.hideHeader)
            this.thead.style.display = 'none';

        $WH.ae(this.thead, tr);
    },

    createSortOptions: function(parent)
    {
        if (!$.isArray(this.sortOptions))
            return;

        var div = $WH.ce('div');
        div.className = 'listview-sort-options';
        div.innerHTML = LANG.lvnote_sort;
        var sp = $WH.ce('span');
        sp.className = 'listview-sort-options-choices';
        var activeSort = null;
        if ($.isArray(this.sort))
            activeSort = this.sort[0];

        var a;
        var sorts = [];
        for (var i = 0; i < this.sortOptions.length; i++)
        {
            if (this.sortOptions[i].hidden)
                continue;

            a = $WH.ce('a');
            a.href = 'javascript:;';
            a.innerHTML = this.sortOptions[i].name;
            a.onclick = this.sortGallery.bind(this, a, i + 1);
            if (activeSort === i + 1)
                a.className = 'active';

            sorts.push(a);
        }

        for (i = 0; i < sorts.length; i++)
            $WH.ae(sp, sorts[i]);


        $WH.ae(div, sp);
        $WH.aef(parent, div);
    },

    sortGallery: function(el, colNo)
    {
        var btn = $(el);
        btn.siblings('a').removeClass('active');
        btn.addClass('active');
        this.sortBy(colNo);
    },

    createBands: function()
    {
        var
            bandTop = $WH.ce('div'),
            bandBot = $WH.ce('div'),
            noteTop = $WH.ce('div'),
            noteBot = $WH.ce('div');

        this.bandTop = bandTop;
        this.bandBot = bandBot;
        this.noteTop = noteTop;
        this.noteBot = noteBot;

        bandTop.className = 'listview-band-top';
        bandBot.className = 'listview-band-bottom';

        this.navTop = this.createNav(true);
        this.navBot = this.createNav(false);

        noteTop.className = noteBot.className = 'listview-note';

        if (this.note)
        {
            noteTop.innerHTML = this.note;
            var e = $WH.g_getGets();
            if (this.note.indexOf('fi_toggle()') > -1 && !e.filter)
                fi_toggle();
        }
        else if (this.createNote)
            this.createNote(noteTop, noteBot);

        this.createSortOptions(noteTop);

        if (this.debug)
        {
            $WH.ae(noteTop, $WH.ct(" ("));
            var ids = $WH.ce('a');
            ids.onclick = this.getList.bind(this);
            $WH.ae(ids, $WH.ct("CSV"));
            $WH.ae(noteTop, ids);
            $WH.ae(noteTop, $WH.ct(")"));
        }

        if (this._errors)
        {
            var sp = $WH.ce('small'),
                b  = $WH.ce('b');

            b.className = 'q10 icon-report';
            if (noteTop.innerHTML)
                b.style.marginLeft = '10px';

            g_addTooltip(sp, LANG.lvnote_witherrors, 'q');

            $WH.st(b, LANG.error);
            $WH.ae(sp, b);
            $WH.ae(noteTop, sp);
        }

        if (!noteTop.firstChild && !(this.createCbControls || this.mode == Listview.MODE_CHECKBOX))
            $WH.ae(noteTop, $WH.ct(String.fromCharCode(160)));
        if (!(this.createCbControls || this.mode == Listview.MODE_CHECKBOX))
            $WH.ae(noteBot, $WH.ct(String.fromCharCode(160)));

        $WH.ae(bandTop, this.navTop);
        if (this.searchable)
        {
            var
                FI_FUNC = this.updateFilters.bind(this, true),
                FI_PH   = (this._truncated ? LANG.lvsearchdisplayedresults : LANG.lvsearchresults),
                sp      = $WH.ce('span'),
                em      = $WH.ce('em'),
                a       = $WH.ce('a'),
                input   = $WH.ce('input');

            sp.className = 'listview-quicksearch';

            if (this.tabClick)
                $(sp).click(this.tabClick);

            $WH.ae(sp, em);

            a.href = 'javascript:;';
            a.onclick = function() {
                var foo = this.nextSibling;
                foo.value = '';
                foo.placeholder = FI_PH;
                FI_FUNC();
            };
            a.style.display = 'none';
            $WH.ae(a, $WH.ce('span'));
            $WH.ae(sp, a);
            $WH.ns(a);

            input.setAttribute('type', 'text');
            input.placeholder = FI_PH;
            input.style.width = (this._truncated ? '19em' : '15em');
            g_onAfterTyping(input, FI_FUNC, this.searchDelay);

            input.onmouseover = function()
            {
                if ($WH.trim(this.value) != '')
                    this.className = '';
            };

            input.onfocus = function()
            {
                this.className = '';
            };

            input.onblur = function()
            {
                if ($WH.trim(this.value) == '')
                {
                    this.value = '';
                }
            };

            input.onkeypress = this.submitSearch.bind(this);

            $WH.ae(sp, input);

            this.quickSearchBox   = input;
            this.quickSearchGlass = em;
            this.quickSearchClear = a;

            $WH.ae(bandTop, sp);
        }
        $WH.ae(bandTop, noteTop);

        $WH.ae(bandBot, this.navBot);
        $WH.ae(bandBot, noteBot);

        if (this.createCbControls || this.mode == Listview.MODE_CHECKBOX)
        {
            if (this.note)
                noteTop.style.paddingBottom = '5px';

            this.cbBarTop = this.createCbBar(true);
            this.cbBarBot = this.createCbBar(false);

            $WH.ae(bandTop, this.cbBarTop);
            $WH.ae(bandBot, this.cbBarBot);

            if (!this.noteTop.firstChild && !this.cbBarTop.firstChild)
                this.noteTop.innerHTML = '&nbsp;';
            if (!this.noteBot.firstChild && !this.cbBarBot.firstChild)
                this.noteBot.innerHTML = '&nbsp;';

            if (this.noteTop.firstChild && this.cbBarTop.firstChild)
                this.noteTop.style.paddingBottom = '6px';
            if (this.noteBot.firstChild && this.cbBarBot.firstChild)
                this.noteBot.style.paddingBottom = '6px';
        }

        if (this.hideBands & 1)
            bandTop.style.display = 'none';
        if (this.hideBands & 2)
            bandBot.style.display = 'none';

        $WH.ae(this.container, this.bandTop);
        if (this.clip)
        {
            var clipDiv = $WH.ce('div');
            clipDiv.className = 'listview-clip';
            clipDiv.style.width  = this.clip.w + 'px';
            clipDiv.style.height = this.clip.h + 'px';
            this.clipDiv = clipDiv;

            $WH.ae(clipDiv, this.mainContainer);
            $WH.ae(clipDiv, this.noData);
            $WH.ae(this.container, clipDiv);
        }
        else
        {
            $WH.ae(this.container, this.mainContainer);
            $WH.ae(this.container, this.noData);
        }
        $WH.ae(this.container, this.bandBot);
    },

    createNav: function(top)
    {
        var
            div = $WH.ce('div'),
            a1 = $WH.ce('a'),
            a2 = $WH.ce('a'),
            a3 = $WH.ce('a'),
            a4 = $WH.ce('a'),
            span = $WH.ce('span'),
            b1 = $WH.ce('b'),
            b2 = $WH.ce('b'),
            b3 = $WH.ce('b');

        div.className = 'listview-nav';

        a1.href = a2.href = a3.href = a4.href = 'javascript:;';

        $WH.ae(a1, $WH.ct(String.fromCharCode(171) + LANG.lvpage_first));
        $WH.ae(a2, $WH.ct(String.fromCharCode(8249) + LANG.lvpage_previous));
        $WH.ae(a3, $WH.ct(LANG.lvpage_next + String.fromCharCode(8250)));
        $WH.ae(a4, $WH.ct(LANG.lvpage_last + String.fromCharCode(187)));

        $WH.ns(a1);
        $WH.ns(a2);
        $WH.ns(a3);
        $WH.ns(a4);

        a1.onclick = this.firstPage.bind(this);
        a2.onclick = this.previousPage.bind(this);
        a3.onclick = this.nextPage.bind(this);
        a4.onclick = this.lastPage.bind(this);

        if (this.mode == Listview.MODE_CALENDAR)
        {
            $WH.ae(b1, $WH.ct('a'));
            $WH.ae(span, b1);
        }
        else
        {
            $WH.ae(b1, $WH.ct('a'));
            $WH.ae(b2, $WH.ct('a'));
            $WH.ae(b3, $WH.ct('a'));
            $WH.ae(span, b1);
            $WH.ae(span, $WH.ct(LANG.hyphen));
            $WH.ae(span, b2);
            $WH.ae(span, $WH.ct(LANG.lvpage_of));
            $WH.ae(span, b3);
        }

        $WH.ae(div, a1);
        $WH.ae(div, a2);
        $WH.ae(div, span);
        $WH.ae(div, a3);
        $WH.ae(div, a4);

        if (top)
        {
            if (this.hideNav & 1)
                div.style.display = 'none';
        }
        else
        {
            if (this.hideNav & 2)
                div.style.display = 'none';
        }

        if (this.tabClick)
            $('a', div).click(this.tabClick);

        return div;
    },

    createCbBar: function(topBar)
    {
        var div = $WH.ce('div');

        if (this.createCbControls)
            this.createCbControls(div, topBar);

        if (div.firstChild) // Not empty
        {
            div.className = 'listview-withselected' + (topBar ? '' : '2');
        }

        return div;
    },

    refreshRows: function()
    {
        var target = null;
        switch (this.mode)
        {
            case Listview.MODE_DIV:
                target = this.mainContainer;
                break;
            case Listview.MODE_FLEXGRID:
                target = this.mainDiv;
                break;
            default:
                target = this.tbody
        }
        if (!target)
            return;

        $WH.ee(target);

        if (this.nRowsVisible == 0)
        {
            if (!this.filtered)
            {
                this.bandTop.style.display = this.bandBot.style.display = 'none';

                this.mainContainer.style.display = 'none';
            }

            this.noData.style.display = '';
            this.showNoData();

            return;
        }

        var
            starti,
            endi,
            func;

        if (!(this.hideBands & 1))
            this.bandTop.style.display = '';
        if (!(this.hideBands & 2))
            this.bandBot.style.display = '';

        if (this.nDaysPerMonth && this.nDaysPerMonth.length)
        {
            starti = 0;
            for (var i = 0; i < this.rowOffset; ++i)
                starti += this.nDaysPerMonth[i];

            endi = starti + this.nDaysPerMonth[i];
        }
        else if (this.nItemsPerPage > 0)
        {
            starti = this.rowOffset;
            endi   = Math.min(starti + this.nRowsVisible, starti + this.nItemsPerPage);

            if (this.filtered && this.rowOffset > 0) // Adjusts start and end position when listview is filtered
            {
                for (var i = 0, count = 0; i < this.data.length && count < this.rowOffset; ++i)
                {
                    var row = this.data[i];

                    if (row.__hidden || row.__deleted)
                        ++starti;
                    else
                        ++count;
                }
                endi += (starti - this.rowOffset);
            }
        }
        else
        {
            starti = 0;
            endi   = this.nRowsVisible;
        }

        var nItemsToDisplay = endi - starti;

        if (this.mode == Listview.MODE_DIV)
        {
            for (var j = 0; j < nItemsToDisplay; ++j)
            {
                var
                    i   = starti + j,
                    row = this.data[i];

                if (!row)
                    break;

                if (row.__hidden || row.__deleted)
                {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(this.mainDiv, this.getDiv(i));
            }
        }
        else if (this.mode == Listview.MODE_FLEXGRID)
        {
            for (var j = 0; j < nItemsToDisplay; ++j)
            {
                var
                    i   = starti + j,
                    row = this.data[i];

                if (!row)
                    break;

                if (row.__hidden || row.__deleted)
                {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(this.mainDiv, this.getDiv(i));
            }
        }
        else if (this.mode == Listview.MODE_TILED)
        {
            var
                k  = 0,
                tr = $WH.ce('tr');

            for (var j = 0; j < nItemsToDisplay; ++j)
            {
                var
                    i   = starti + j,
                    row = this.data[i];

                if (!row)
                    break;

                if (row.__hidden || row.__deleted)
                {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(tr, this.getCell(i));

                if (++k == this.nItemsPerRow)
                {
                    $WH.ae(this.tbody, tr);
                    if (j + 1 < nItemsToDisplay)
                        tr = $WH.ce('tr');

                    k = 0;
                }
            }

            if (k != 0)
            {
                for (; k < 4; ++k)
                {
                    var foo = $WH.ce('td');
                    foo.className = 'empty-cell';
                    $WH.ae(tr, foo);
                }
                $WH.ae(this.tbody, tr);
            }
        }
        else if (this.mode == Listview.MODE_CALENDAR)
        {
            var tr = $WH.ce('tr');

            for (var i = 0; i < 7; ++i)
            {
                var th = $WH.ce('th');
                $WH.st(th, LANG.date_days[i]);
                $WH.ae(tr, th);
            }

            $WH.ae(this.tbody, tr);
            tr = $WH.ce('tr');

            for (var k = 0; k < this.dates[starti].date.getLocaleDay(); ++k)
            {
                var foo = $WH.ce('td');
                foo.className = 'empty-cell';
                $WH.ae(tr, foo);
            }

            for (var j = starti; j < endi; ++j)
            {
                $WH.ae(tr, this.getEvent(j));

                if (++k == 7)
                {
                    $WH.ae(this.tbody, tr);
                    tr = $WH.ce('tr');
                    k  = 0;
                }
            }

            if (k != 0)
            {
                for (; k < 7; ++k)
                {
                    var foo = $WH.ce('td');
                    foo.className = 'empty-cell';
                    $WH.ae(tr, foo);
                }
                $WH.ae(this.tbody, tr);
            }
        }
        else // DEFAULT || CHECKBOX
        {
            for (var j = 0; j < nItemsToDisplay; ++j)
            {
                var
                i   = starti + j,
                row = this.data[i];

                if (!row)
                    break;

                if (row.__hidden || row.__deleted)
                {
                    ++nItemsToDisplay;
                    continue;
                }

                $WH.ae(this.tbody, this.getRow(i));
            }
        }

        this.mainContainer.style.display = '';
        this.noData.style.display = 'none';
    },

    showNoData: function()
    {
        var div = this.noData;
        $WH.ee(div);

        var result = -1;
        if (this.onNoData)
            result = (this.onNoData.bind(this, div))();

        if (result == -1)
            $WH.ae(this.noData, $WH.ct(this.filtered ? LANG.lvnodata2 : LANG.lvnodata));
    },

    getDiv: function(i)
    {
        var row = this.data[i];

        if (row.__div == null || this.minPatchVersion != row.__minPatch)
            this.createDiv(row, i);

        return row.__div;
    },

    createDiv: function(row, i)
    {
        var div = $WH.ce('div');
        row.__div = div;
        if (this.minPatchVersion)
            row.__minPatch = this.minPatchVersion;

        (this.template.compute.bind(this, row, div, i))();
    },

    getCell: function(i)
    {
        var row = this.data[i];

        if (row.__td == null)
            this.createCell(row, i);

        return row.__td;
    },

    createCell: function(row, i)
    {
        var td = $WH.ce('td');
        row.__td = td;

        (this.template.compute.bind(this, row, td, i))();
    },

    getEvent: function(i)
    {
        var row = this.dates[i];

        if (row.__td == null)
            this.createEvent(row, i);

        return row.__td;
    },

    createEvent: function(row, i)
    {
        row.events = $WH.array_filter(this.data, function(holiday) {
            if (holiday.__hidden || holiday.__deleted)
                return false;

            var dates = Listview.funcBox.getEventNextDates(holiday.startDate, holiday.endDate, holiday.rec || 0, row.date);
            if (dates[0] && dates[1])
            {
                dates[0].setHours(0, 0, 0, 0);
                dates[1].setHours(0, 0, 0, 0);
                return dates[0] <= row.date && dates[1] >= row.date;
            }

            return false;
        });

        var td = $WH.ce('td');
        row.__td = td;

        if (row.date.getFullYear() == g_serverTime.getFullYear() && row.date.getMonth() == g_serverTime.getMonth() && row.date.getDate() == g_serverTime.getDate())
            td.className = 'calendar-today';

        var div = $WH.ce('div');
        div.className = 'calendar-date';
        $WH.st(div, row.date.getDate());
        $WH.ae(td, div);

        div = $WH.ce('div');
        div.className = 'calendar-event';
        $WH.ae(td, div);

        (this.template.compute.bind(this, row, div, i))();

        if (this.getItemLink)
            td.onclick = this.itemClick.bind(this, row);
    },

    getRow: function(i)
    {
        var row = this.data[i];

        if (row.__tr == null)
            this.createRow(row);

        return row.__tr;
    },

    setRow: function(newRow)
    {
        if (this.data[newRow.pos])
        {
            this.data[newRow.pos] = newRow;
            this.data[newRow.pos].__tr = newRow.__tr;
            this.createRow(this.data[newRow.pos]);
            this.refreshRows();
        }
    },

    createRow: function(row)
    {
        var tr = $WH.ce('tr');
        row.__tr = tr;

        if (this.mode == Listview.MODE_CHECKBOX)
        {
            var td = $WH.ce('td');

            if (!row.__nochk)
            {
                td.className = 'listview-cb';
                td.onclick   = Listview.cbCellClick;

                var cb = $WH.ce('input');
                $WH.ns(cb);
                cb.type = 'checkbox';
                cb.onclick = Listview.cbClick;

                if (row.__chk)
                {
                    cb.checked = true;
                }

                row.__cb = cb;

                $WH.ae(td, cb);
            }

            $WH.ae(tr, td);
        }

        for (var i = 0, len = this.visibility.length; i < len; ++i)
        {
            var
                reali = this.visibility[i],
                col = this.columns[reali],
                td = $WH.ce('td'),
                result;

            if (col.align != null)
                td.style.textAlign = col.align;

            if (col.compute)
                result = (col.compute.bind(this, row, td, tr, reali))();
            else
            {
                if (row[col.value] != null)
                    result = row[col.value];
                else
                    result = -1;
            }

            if (result != -1 && result != null)
                td.insertBefore($WH.ct(result), td.firstChild);

            $WH.ae(tr, td);
        }

        if (this.mode == Listview.MODE_CHECKBOX && row.__chk)
            tr.className = 'checked';

        if (row.frommerge == 1) {
            tr.className += ' mergerow';
        }

        if (this.getItemLink)
            tr.onclick = this.itemClick.bind(this, row);
    },

    itemClick: function(row, e)
    {
        e = $WH.$E(e);

        var i  = 0,
            el = e._target;

        while (el && i < 3)
        {
            if (el.nodeName == 'A')
                return;

            el = el.parentNode;
        }

        location.href = this.getItemLink(row);
    },

    submitSearch: function(e)
    {
        e = $WH.$E(e);

        if (!this.onSearchSubmit || e.keyCode != 13)
            return;

        for (var i = 0, len = this.data.length; i < len; ++i)
        {
            if (this.data[i].__hidden)
                continue;

            (this.onSearchSubmit.bind(this, this.data[i]))();
        }
    },

    validatePage: function()
    {
        var
            rpp = this.nItemsPerPage,
            ro  = this.rowOffset,
            len = this.nRowsVisible;

        if (ro < 0)
            this.rowOffset = 0;
        else if (this.mode == Listview.MODE_CALENDAR)
            this.rowOffset = Math.min(ro, this.nDaysPerMonth.length - 1);
        else
            this.rowOffset = this.getRowOffset(ro + rpp > len ? len - 1 : ro);
    },

    getRowOffset: function(rowNo)
    {
        var rpp = this.nItemsPerPage;

        return (rpp > 0 && rowNo > 0 ? Math.floor(rowNo / rpp) * rpp : 0);
    },

    resetRowVisibility: function()
    {
        for (var i = 0, len = this.data.length; i < len; ++i)
            this.data[i].__hidden = false;

        this.filtered     = false;
        this.rowOffset    = 0;
        this.nRowsVisible = this.data.length;
    },

    getColText: function(row, col)
    {
        var text = '';
        if (this.template.getVisibleText)
            text = $WH.trim(this.template.getVisibleText(row) + ' ');

        if (col.getVisibleText)
            return text + col.getVisibleText(row);

        if (col.getValue)
            return text + col.getValue(row);

        if (col.value)
            return text + row[col.value];

        if (col.compute)
            return text + col.compute(row, $WH.ce('td'), $WH.ce('tr'));

        return '';
    },

    resetFilters: function()
    {
        for (var j = 0, len2 = this.visibility.length; j < len2; ++j)
        {
            var realj = this.visibility[j];
            var col = this.columns[realj];

            if (col.__filter)
            {
                col.__th.firstChild.firstChild.className = '';
                col.__filter = null;
                --(this.nFilters);
            }
        }
    },

    updateFilters: function(refresh)
    {
        $WH.Tooltip.hide();
        this.resetRowVisibility();

        var
            searchText,
            parts,
            nParts;

        if (this.searchable)
        {
            this.quickSearchBox.parentNode.style.display = '';

            searchText = $WH.trim(this.quickSearchBox.value);

            if (searchText)
            {
                this.quickSearchGlass.style.display = 'none';
                this.quickSearchClear.style.display = '';

                searchText = searchText.toLowerCase().replace(/\s+/g, ' ');

                parts  = searchText.split(' ');
                nParts = parts.length;
            }
            else
            {
                this.quickSearchGlass.style.display = '';
                this.quickSearchClear.style.display = 'none';
            }
        }
        else if (this.quickSearchBox)
            this.quickSearchBox.parentNode.style.display = 'none';

        if (!searchText && this.nFilters == 0 && this.customFilter == null)
        {
            if (refresh)
            {
                this.updateNav();
                this.refreshRows();
                this.updateBrowseSession();
            }

            return;
        }

        // Numerical
        var filterFuncs = {
            1: function(x, y)    { return x  > y;           },
            2: function(x, y)    { return x == y;           },
            3: function(x, y)    { return x  < y;           },
            4: function(x, y)    { return x >= y;           },
            5: function(x, y)    { return x <= y;           },
            6: function(x, y, z) { return y <= x && x <= z; }
        };

        // Range
        var filterFuncs2 = {
            1: function(min, max, y)    { return max  > y;             },
            2: function(min, max, y)    { return min <= y && y <= max; },
            3: function(min, max, y)    { return min  < y;             },
            4: function(min, max, y)    { return max >= y;             },
            5: function(min, max, y)    { return min <= y;             },
            6: function(min, max, y, z) { return y <= max && min <= z; }
        };

        var nRowsVisible = 0;

        for (var i = 0, len = this.data.length; i < len; ++i)
        {
            var
                row = this.data[i],
                nFilterMatches = 0;
                nSearchMatches = 0,
                matches = [];

            row.__hidden = true;

            if (this.customFilter && !this.customFilter(row, i))
                continue;

            for (var j = 0, len2 = this.visibility.length; j < len2; ++j)
            {
                var realj = this.visibility[j];
                var col = this.columns[realj];

                if (col.__filter)
                {
                    var
                        filter = col.__filter,
                        result = false;

                    if (col.type != null && col.type == 'range')
                    {
                        var minValue = col.getMinValue(row),
                            maxValue = col.getMaxValue(row);

                        result = (filterFuncs2[filter.type])(minValue, maxValue, filter.value, filter.value2);
                    }
                    else if (col.type == null || col.type == 'num' || filter.type > 0)
                    {
                        var value = null;

                        if (col.getValue)
                            value = col.getValue(row);
                        else if (col.value)
                            value = parseFloat(row[col.value]);

                        if (!value)
                            value = 0;

                        result = (filterFuncs[filter.type])(value, filter.value, filter.value2);
                    }
                    else
                    {
                        var text = this.getColText(row, col);

                        if (text)
                        {
                            text = text.toString().toLowerCase();

                            if (filter.invert)
                                result = text.match(filter.regex) != null;
                            else
                            {
                                var foo = 0;

                                for (var k = 0, len3 = filter.words.length; k < len3; ++k)
                                {
                                    if (text.indexOf(filter.words[k]) != -1)
                                        ++foo;
                                    else
                                        break;
                                }

                                result = (foo == filter.words.length);
                            }
                        }
                    }

                    if (filter.invert)
                        result = !result;

                    if (result)
                        ++nFilterMatches;
                    else
                        break;
                }

                if (searchText)
                {
                    var text = this.getColText(row, col);
                    if (text)
                    {
                        text = text.toString().toLowerCase();

                        for (var k = 0, len3 = parts.length; k < len3; ++k)
                        {
                            if (!matches[k])
                            {
                                if (text.indexOf(parts[k]) != -1)
                                {
                                    matches[k] = 1;
                                    ++nSearchMatches;
                                }
                            }
                        }
                    }
                }
            }

            if (row.__alwaysvisible ||
               ((this.nFilters == 0 || nFilterMatches == this.nFilters) &&
               (!searchText || nSearchMatches == nParts)))
            {
                row.__hidden = false;
                ++nRowsVisible;
            }
        }

        this.filtered     = (nRowsVisible < this.data.length);
        this.nRowsVisible = nRowsVisible;

        if (refresh)
        {
            this.updateNav();
            this.refreshRows();
            this.updateBrowseSession();
        }
    },

    changePage: function()
    {
        this.validatePage();

        this.refreshRows();
        this.updateNav();
        this.updatePound();
        this.updateBrowseSession();

        var
            scroll = $WH.g_getScroll(),
            c = $WH.ac(this.container);

        if (scroll.y > c[1])
            scrollTo(scroll.x, c[1]);
    },

    firstPage: function()
    {
        this.rowOffset = 0;
        this.changePage();

        return false;
    },

    previousPage: function()
    {
        this.rowOffset -= this.nItemsPerPage;
        this.changePage();

        return false;
    },

    nextPage: function()
    {
        this.rowOffset += this.nItemsPerPage;
        this.changePage();

        return false;
    },

    lastPage: function()
    {
        this.rowOffset = 99999999;
        this.changePage();

        return false;
    },

    addSort: function(arr, colNo)
    {
        var i = $WH.in_array(arr, Math.abs(colNo), function(x) { return Math.abs(x); });

        if (i != -1)
        {
            colNo = arr[i];
            arr.splice(i, 1);
        }

        arr.splice(0, 0, colNo);
    },

    sortBy: function(colNo)
    {
        var sorts = this.sortOptions || this.columns;
        if (colNo <= 0 || colNo > sorts.length)
            return;

        if (Math.abs(this.sort[0]) == colNo)
            this.sort[0] = -this.sort[0];
        else
        {
            var defaultSort = -1;
            if (sorts[colNo - 1].type == 'text')
                defaultSort = 1;

            this.addSort(this.sort, defaultSort * colNo);
        }

        this.applySort();
        this.refreshRows();
        this.updateSortArrow();
        this.updatePound();
    },

    applySort: function()
    {
        if (this.sort.length == 0)
            return;

        Listview.sort        = this.sort;
        Listview.columns     = this.columns;
        Listview.sortOptions = this.sortOptions;

        if (this.indexCreated)
            this.data.sort(Listview.sortIndexedRows.bind(this));
        else
            this.data.sort(Listview.sortRows.bind(this));

        this.updateSortIndex();
        this.updateBrowseSession();
    },

    setSort: function(sort, refresh, updatePound)
    {
        if (this.sort.toString() != sort.toString())
        {
            this.sort = sort;
            this.applySort();

            if (refresh)
                this.refreshRows();

            if (updatePound)
                this.updatePound();
        }
    },

    readPound: function()
    {
        if (!this.poundable || !location.hash.length)
            return false;

        var _ = location.hash.substr(1);
        if (this.tabs)
        {
            var n = _.lastIndexOf(':');

            if (n == -1)
                return false;

            _ = _.substr(n + 1);
        }

        var num = parseInt(_);
        if (!isNaN(num))
        {
            this.rowOffset = num;
            this.validatePage();

            if (this.poundable != 2)
            {
                var sort = [];
                var matches = _.match(/(\+|\-)[0-9]+/g);
                if (matches != null)
                {
                    var sorts = this.sortOptions || this.columns;
                    for (var i = matches.length - 1; i >= 0; --i)
                    {
                        var colNo = parseInt(matches[i]) | 0;
                        var _ = Math.abs(colNo);
                        if (_ <= 0 || _ > sorts.length)
                            break;

                        this.addSort(sort, colNo);
                    }

                    this.setSort(sort, false, false);
                }
            }

            if (this.tabs)
                this.tabs.setTabPound(this.tabIndex, this.getTabPound());
        }
    },

    updateBrowseSession: function ()
    {
        if ((!window.JSON) || (!$WH.localStorage.isSupported()))
            return;

     // if ((typeof fi_filters == 'undefined') && (location.pathname != '/search')) { aowow - we still don't flatten our urls
        if ((typeof fi_filters == 'undefined') && !(/\?search/.test(location.search)))
            return;

        if (this.data.length < 3)
            return;

        if (typeof this.getItemLink != 'function')
            return;

        var max    = 5;
        var path   = location.pathname + location.search;
        var expire = (new Date(g_serverTime.getTime() - 1000 * 60 * 60 * 24 * 3)).getTime();
        var lv     = $WH.localStorage.get('lvBrowse');

        if (lv)
        {
            lv = window.JSON.parse(lv);
            for (var i = 0; i < lv.length; i++)
            {
                if ((lv[i].path == path) || (lv[i].when < expire))
                    lv.splice(i--, 1);
            }

            if (lv.length >= max)
                lv.splice(max - 1, lv.length - max - 1);
        }
        else
            lv = [];

        var urls = [],
            url,
            pattern = /^\?[-a-z]+=\d+/i;

        for (var i = 0; i < this.data.length; i++)
        {
            if (url = pattern.exec(this.getItemLink(this.data[i])))
                urls.push(url[0]);
        }

        if (urls.length < 3)
            return;

        lv.unshift({
            path: path,
            hash: location.hash,
            when: g_serverTime.getTime(),
            urls: urls
        });

        $WH.localStorage.set('lvBrowse', window.JSON.stringify(lv));
    },

    updateSortArrow: function()
    {
        if (!this.sort.length || !this.thead || this.mode == Listview.MODE_CALENDAR /* || this.searchSort */)
            return;

        var i = $WH.in_array(this.visibility, Math.abs(this.sort[0]) - 1);

        if (i == -1)
            return;

        if (this.mode == Listview.MODE_TILED)
        {
            if (!this.sortOptions)
                return;

            var a = $('.listview-sort-options a', this.noteTop).get(i);
            if (this.lsa && this.lsa != a)
                this.lsa.className = '';

            a.className = this.sort[0] < 0 ? 'active sortdesc' : 'active sortasc';
            this.lsa = a;
            return;
        }

        if (this.mode == Listview.MODE_CHECKBOX && i < this.thead.firstChild.childNodes.length - 1)
            i += 1;

        var span = this.thead.firstChild.childNodes[i].firstChild.firstChild.firstChild;

        if (this.lsa && this.lsa != span) // lastSortArrow
            this.lsa.className = '';

        span.className = (this.sort[0] < 0 ? 'sortdesc' : 'sortasc');
        this.lsa = span;
    },

    updateSortIndex: function()
    {
        var _ = this.data;

        for (var i = 0, len = _.length; i < len; ++i)
            _[i].__si = i; // sortIndex

        this.indexCreated = true;
    },

    updateTabName: function()
    {
        if (this.tabs && this.tabIndex != null)
            this.tabs.setTabName(this.tabIndex, this.getTabName());
    },

    updatePound: function(useCurrentSort)
    {
        if (!this.poundable)
            return;

        var _  = '',
            id = '';

        if (useCurrentSort)
        {
            if (location.hash.length && this.tabs)
            {
                var n = location.hash.lastIndexOf(':');
                if (n != -1 && !isNaN(parseInt(location.hash.substr(n + 1))))
                    _ = location.hash.substr(n + 1);
            }
        }
        else
            _ = this.getTabPound();

        if (this.customPound)
            id = this.customPound;
        else if (this.tabs)
            id = this.id;

        if (_ && this.tabs)
            this.tabs.setTabPound(this.tabIndex, _);

        location.replace('#' + id + (id && _ ? ':' : '') + _);
    },

    updateNav: function()
    {
        var
            arr      = [this.navTop, this.navBot],
            _        = this.nItemsPerPage,
            __       = this.rowOffset,
            ___      = this.nRowsVisible,
            first    = 0,
            previous = 0,
            next     = 0,
            last     = 0,
            date     = new Date();

        if (___ > 0)
        {
            if (!(this.hideNav & 1))
                arr[0].style.display = '';
            if (!(this.hideNav & 2))
                arr[1].style.display = '';
        }
        else
            arr[0].style.display = arr[1].style.display = 'none';

        if (this.mode == Listview.MODE_CALENDAR)
        {
            for (var i = 0; i < this.nDaysPerMonth.length; ++i)
            {
                if (i == __) // Selected month
                {
                    if (i > 0)
                        previous = 1;
                    if (i > 1)
                        first = 1;
                    if (i < this.nDaysPerMonth.length - 1)
                        next = 1;
                    if (i < this.nDaysPerMonth.length - 2)
                        last = 1;
                }
            }

            date.setTime(this.startOnMonth.valueOf());
            date.setMonth(date.getMonth() + __);
        }
        else
        {
            if (_)
            {
                if (__ > 0)
                {
                    previous = 1;
                    if (__ >= _ + _)
                        first = 1;
                }
                if (__ + _ < ___)
                {
                    next = 1;
                    if (__ + _ + _ < ___)
                        last = 1;
                }
            }
        }

        for (var i = 0; i < 2; ++i)
        {
            var childs = arr[i].childNodes;

            childs[0].style.display = (first ? '' : 'none');
            childs[1].style.display = (previous ? '' : 'none');
            childs[3].style.display = (next ? '' : 'none');
            childs[4].style.display = (last ? '' : 'none');

            childs = childs[2].childNodes;

            if (this.mode == Listview.MODE_CALENDAR)
                childs[0].firstChild.nodeValue = LANG.date_months[date.getMonth()] + ' ' + date.getFullYear();
            else
            {
                childs[0].firstChild.nodeValue = __ + 1;
                childs[2].firstChild.nodeValue = _ ? Math.min(__ + _, ___) : ___;
                childs[4].firstChild.nodeValue = ___;
            }
        }
    },

    getTabName: function()
    {
        var
            name = this.name,
            n = this.data.length;

        for (var i = 0, len = this.data.length; i < len; ++i)
            if (this.data[i].__hidden || this.data[i].__deleted)
                --n;

        if (n > 0 && !this.hideCount)
            name += $WH.sprintf(LANG.qty, n);

        return name;
    },

    getTabPound: function()
    {
        var buffer = '';

        buffer += this.rowOffset;

        if (this.poundable != 2 && this.sort.length)
            buffer += ('+' + this.sort.join('+')).replace(/\+\-/g, '-');

        return buffer;
    },

    getCheckedRows: function()
    {
        var checkedRows = [];

        for (var i = 0, len = this.data.length; i < len; ++i)
        {
            var _ = this.data[i];

            if ((_.__cb && _.__cb.checked) || (!_.__cb && _.__chk))
                checkedRows.push(_);
        }

        return checkedRows;
    },

    resetCheckedRows: function()
    {
        for (var i = 0, len = this.data.length; i < len; ++i)
        {
            var _ = this.data[i];

            if (_.__cb)
                _.__cb.checked = false;
            else if (_.__chk)
                _.__chk = null;

            if (_.__tr)
                _.__tr.className = _.__tr.className.replace('checked', '');
        }
    },

    deleteRows: function(rows)
    {
        if (!rows || !rows.length)
            return;

        for (var i = 0, len = rows.length; i < len; ++i)
        {
            var row = rows[i];

            if (!row.__hidden && !row.__hidden)
                this.nRowsVisible -= 1;

            row.__deleted = true;
        }

        this.updateTabName();

        if (this.rowOffset >= this.nRowsVisible)
            this.previousPage();
        else
        {
            this.refreshRows();
            this.updateNav();
            this.updateBrowseSession();
        }
    },

    setData: function(data)
    {
        this.data = data;
        this.indexCreated = false;

        this.resetCheckedRows();
        this.resetRowVisibility();

        if (this.tabs)
        {
            this.pounded = (this.tabs.poundedTab == this.tabIndex);
            if (this.pounded)
                this.readPound();
        }
        else
            this.readPound();

        this.applySort();
        this.updateSortArrow();

        if (this.customFilter != null)
            this.updateFilters();

        this.updateNav();
        this.refreshRows();
        this.updateBrowseSession();
    },

    getClipDiv: function()
    {
        return this.clipDiv;
    },

    getNoteTopDiv: function()
    {
        return this.noteTop;
    },

    focusSearch: function()
    {
        this.quickSearchBox.focus();
    },

    clearSearch: function()
    {
        this.quickSearchBox.value = '';
    },

    getList: function()
    {
        if (!this.debug)
            return;

        var str = '';
        for (var i = 0; i < this.data.length; i++)
            if (!this.data[i].__hidden)
                str += this.data[i].id + ', ';

        listviewIdList.show(str);
    },

    createIndicator: function(v, f, c)
    {
        if (!this.noteIndicators)
        {
            this.noteIndicators = $WH.ce('div');
            this.noteIndicators.className = 'listview-indicators';
            $(this.noteIndicators).insertBefore($(this.noteTop));
        }

        var t = this.tabClick;
        $(this.noteIndicators)
            .append(
                $('<span class="indicator"></span>')
                .html(v)
                .append(!f ? '' :
                    $('<a class="indicator-x" style="outline: none">[x]</a>')
                    .attr('href', (typeof f == 'function' ? 'javascript:;' : f))
                    .click(function () { if (t) t(); if (typeof f == 'function') f(); })
                )
                .css('cursor', (typeof c == 'function' ? 'pointer' : null))
                .click(function () { if (t) t(); if (typeof c == 'function') c(); })
            );

        $(this.noteTop).css('padding-top', '7px');
    },

    removeIndicators: function()
    {
        if (this.noteIndicators)
        {
            $(this.noteIndicators).remove();
            this.noteIndicators = null;
        }

        $(this.noteTop).css('padding-top', '');
    }
};

Listview.sortRows = function(a, b)
{
    var
        sort = Listview.sort,
        cols = Listview.columns;

    for (var i = 0, len = sort.length; i < len; ++i)
    {
        var
            res,
            _ = cols[Math.abs(sort[i]) - 1];

        if (!_)
            _ = this.template;

        if (_.sortFunc)
            res = _.sortFunc(a, b, sort[i]);
        else
            res = $WH.strcmp(a[_.value], b[_.value]);

        if (res != 0)
            return res * sort[i];
    }

    return 0;
},

Listview.sortIndexedRows = function(a, b)
{
    var
        sort = Listview.sort,
        cols = Listview.sortOptions || Listview.columns,
        res;

    for (var idx in sort)
    {
        _ = cols[Math.abs(sort[idx]) - 1];

        if (!_)
            _ = this.template;

        if (_.sortFunc)
            res = _.sortFunc(a, b, sort[0]);
        else
            res = $WH.strcmp(a[_.value], b[_.value]);

        if (res != 0)
            return res * sort[idx];
    }

    return (a.__si - b.__si);
},

Listview.cbSelect = function(v)
{
    for (var i = 0, len = this.data.length; i < len; ++i)
    {
        var _ = this.data[i];
        var v2 = v;

        if (_.__hidden)
            continue;

        if (!_.__nochk && _.__cb)
        {
            var cb = _.__cb,
                tr = cb.parentNode.parentNode;

            if (v2 == null)
                v2 = !cb.checked;

            if (cb.checked != v2)
            {
                cb.checked = v2;
                tr.className = (cb.checked ? tr.className + ' checked' : tr.className.replace('checked', ''));
            }
        }
        else if (v2 == null)
            v2 = true;

        _.__chk = v2;
    }
};

Listview.cbClick = function(e)
{
    setTimeout(Listview.cbUpdate.bind(0, 0, this, this.parentNode.parentNode), 1);
    $WH.sp(e);
};

Listview.cbCellClick = function(e)
{
    setTimeout(Listview.cbUpdate.bind(0, 1, this.firstChild, this.parentNode), 1);
    $WH.sp(e);
};

Listview.cbUpdate = function(toggle, cb, tr)
{
    if (toggle)
        cb.checked = !cb.checked;

    tr.className = (cb.checked ? tr.className + ' checked' : tr.className.replace('checked', ''));
};

Listview.headerClick = function(col, i, e)
{
    e = $WH.$E(e);

    if (this.tabClick)
        this.tabClick();

    if (e._button == 3 || e.shiftKey || e.ctrlKey)
    {
        $WH.Tooltip.hide();
        setTimeout(Listview.headerFilter.bind(this, col, null), 1);
    }
    else
    {
        this.sortBy(i + 1);
    }

    return false;
};

Listview.headerFilter = function(col, res)
{
    var prefilled = '';
    if (col.__filter)
    {
        if (col.__filter.invert)
            prefilled += '!';

        prefilled += col.__filter.text;
    }

    if (res == null)
        var res = prompt($WH.sprintf(LANG.prompt_colfilter1 + (col.type == 'text' ? LANG.prompt_colfilter2 : LANG.prompt_colfilter3), col.name), prefilled);

    if (res != null)
    {
        var filter = {text: '', type: -1};

        res = $WH.trim(res.replace(/\s+/g, ' '));

        if (!res && this.onEmptyFilter)
            this.onEmptyFilter(col);
        else if (res)
        {
            if (res.charAt(0) == '!' || res.charAt(0) == '-')
            {
                filter.invert = 1;
                res = res.substr(1);
            }

            if (col.type == 'text')
            {
                filter.type = 0;
                filter.text = res;

                if (filter.invert)
                    filter.regex = g_createOrRegex(res);
                else
                    filter.words = res.toLowerCase().split(' ');
            }
            var value,
                value2;

            if (res.match(/(>|=|<|>=|<=)\s*([0-9\.]+)/))
            {
                value = parseFloat(RegExp.$2);
                if (!isNaN(value))
                {
                    switch (RegExp.$1)
                    {
                        case '>':
                            filter.type = 1;
                            break;

                        case '=':
                            filter.type = 2;
                            break;

                        case '<':
                            filter.type = 3;
                            break;

                        case '>=':
                            filter.type = 4;
                            break;

                        case '<=':
                            filter.type = 5;
                            break;
                    }
                    filter.value = value;
                    filter.text  = RegExp.$1 + ' ' + value;
                }
            }
            else if (res.match(/([0-9\.]+)\s*\-\s*([0-9\.]+)/))
            {
                value  = parseFloat(RegExp.$1);
                value2 = parseFloat(RegExp.$2);
                if (!isNaN(value) && !isNaN(value2))
                {
                    if (value > value2)
                    {
                        var foo = value;
                        value   = value2;
                        value2  = foo;
                    }

                    if (value == value2)
                    {
                        filter.type  = 2;
                        filter.value = value;
                        filter.text  = '= ' + value;
                    }
                    else
                    {
                        filter.type   = 6;
                        filter.value  = value;
                        filter.value2 = value2;
                        filter.text   = value + ' - ' + value2;
                    }
                }
            }
            else
            {
                var parts = res.toLowerCase().split(' ');
                if (!col.allText && parts.length == 1 && !isNaN(value = parseFloat(parts[0])))
                {
                    filter.type = 2;
                    filter.value = value;
                    filter.text = '= ' + value;
                }
                else if (col.type == 'text')
                {
                    filter.type = 0;
                    filter.text = res;

                    if (filter.invert)
                        filter.regex = g_createOrRegex(res);
                    else
                        filter.words = parts;
                }
            }

            if (filter.type == -1)
            {
                alert(LANG.message_invalidfilter);
                return;
            }
        }

        if (!col.__filter || filter.text != col.__filter.text || filter.invert != col.__filter.invert)
        {
            var a = col.__th.firstChild.firstChild;

            if (res && filter.text)
            {
                if (!col.__filter)
                {
                    a.className = 'q5';
                    ++(this.nFilters);
                }

                col.__filter = filter;
            }
            else
            {
                if (col.__filter)
                {
                    a.className = '';
                    --(this.nFilters);
                }

                col.__filter = null;
            }

            this.updateFilters(1);
        }
    }
};

Listview.headerOver = function(a, col, e)
{
    var buffer = '';

    buffer += '<b class="q1">' + (col.tooltip ? col.tooltip : col.name) + '</b>';

    if (col.__filter)
        buffer += '<br />' + $WH.sprintf((col.__filter.invert ? LANG.tooltip_colfilter2 : LANG.tooltip_colfilter1), col.__filter.text);

    buffer += '<br /><span class="q2">' + LANG.tooltip_lvheader1 + '</span>';
    if (this.filtrable && (col.filtrable == null || col.filtrable))
        buffer += '<br /><span class="q2">' + LANG.tooltip_lvheader2 + '</span>';

    $WH.Tooltip.show(a, buffer, 0, 0, 'q');
};

Listview.extraCols = {
    id: {
        id: 'id',
        name: 'ID',
        width: '5%',
        value: 'id',
        compute: function(data, td)
        {
            if (data.id)
                $WH.ae(td, $WH.ct(data.id));
        }
    },

    date: {
        id: 'obj-date',
        name: LANG.added,
        compute: function(data, td)
        {
            if (data.date)
            {
                if (data.date <= 86400)
                    $WH.ae(td, $WH.ct('???'));
                else
                {
                    var added   = new Date(data.date * 1000);
                    var elapsed = (g_serverTime - added) / 1000;

                    return g_formatDate(td, elapsed, added, null, true);
                }
            }
        },
        sortFunc: function(a, b, col)
        {
            if (a.date == b.date)
                return 0;
            else if (a.date < b.date)
                return -1;
            else
                return 1;
        }
    },

    cost: {
        id: 'cost',
        name: LANG.cost,
        getValue: function(row)
        {
            if (row.cost)
                return (row.cost[2] && row.cost[2][0] ? row.cost[2][0][1] : 0) || (row.cost[1] && row.cost[1][0] ? row.cost[1][0][1] : 0) || row.cost[0];
        },
        compute: function(row, td)
        {
            if (row.cost)
            {
                var money = row.cost[0];
                var side = null;
                var items = row.cost[2];
                var currency = row.cost[1];

                if (row.side != null)
                    side = row.side;
                else if (row.react != null)
                {
                    if (row.react[0] == 1 && row.react[1] == -1) // Alliance only
                        side = 1;
                    else if (row.react[0] == -1 && row.react[1] == 1) // Horde only
                        side = 2;
                }

                Listview.funcBox.appendMoney(td, money, side, items, currency);
            }
        },
        sortFunc: function(a, b, col)
        {
            if (a.cost == null)
                return -1;
            else if (b.cost == null)
                return 1;

            var lena = 0,
                lenb = 0,
                lenc = 0,
                lend = 0;

            if (a.cost[2] != null)
                $WH.array_walk(a.cost[2], function(x, _, __, i) { lena += Math.pow(10, i) + x[1]; });
            if (b.cost[2] != null)
                $WH.array_walk(b.cost[2], function(x, _, __, i) { lenb += Math.pow(10, i) + x[1]; });
            if (a.cost[1] != null)
                $WH.array_walk(a.cost[1], function(x, _, __, i) { lenc += Math.pow(10, i) + x[1]; });
            if (b.cost[1] != null)
                $WH.array_walk(b.cost[1], function(x, _, __, i) { lend += Math.pow(10, i) + x[1]; });

            return $WH.strcmp(lena, lenb) || $WH.strcmp(lenc, lend) || $WH.strcmp(a.cost[0], b.cost[0]);
        }
    },

    count: {
        id: 'count',
        name: LANG.count,
        value: 'count',
        compute: function(row, td)
        {
            if (!(this._totalCount > 0 || row.outof > 0))
                return;

            if (row.outof)
            {
                var d = $WH.ce('div');
                d.className = 'small q0';
                $WH.ae(d, $WH.ct($WH.sprintf(LANG.lvdrop_outof, row.outof)));
                $WH.ae(td, d);
            }
            return row.count;
        },
        getVisibleText: function(row)
        {
            var buff = row.count;
            if (row.outof)
                buff += ' ' + row.outof;

            return buff;
        },
        sortFunc: function(a, b, col)
        {
            if (a.count == null)
                return -1;
            else if (b.count == null)
                return 1;

            return $WH.strcmp(a.count, b.count);
        }
    },

    percent: {
        id: 'percent',
        name: '%',
        value: 'percent',
        compute: function(row, td)
        {
            if (row.count <= 0)
                return '??';

            if (row.pctstack)
            {
                var text = '';
                var data = eval('(' + row.pctstack + ')');

                for (var amt in data)
                {
                    var pct = (data[amt] * row.percent) / 100;

                    if (pct >= 1.95)
                        pct = parseFloat(pct.toFixed(0));
                    else if (pct >= 0.195)
                        pct = parseFloat(pct.toFixed(1));
                    else
                        pct = parseFloat(pct.toFixed(2));

                    text += $WH.sprintf(LANG.stackof_format, amt, pct) + '<br />';
                }

                td.className += ' tip';
                g_addTooltip(td, text);
            }

         // var value = parseFloat(row.percent.toFixed(row.percent >= 1.95 ? 0 : (row.percent >= 0.195 ? 1 : 2)));
            var value = parseFloat(row.percent.toFixed(row.percent >= 1.95 ? 1 : 2)); // aowow: doesn't look as nice but i prefer accuracy

            if (row.pctstack)
                $(td).append($('<span>').addClass('tip').text(value));
            else
                return value;
        },
        getVisibleText: function(row)
        {
            if (row.count <= 0)
                return '??';

            if (row.percent >= 1.95)
                return row.percent.toFixed(1);
         //     return row.percent.toFixed(0);
         // else if (row.percent >= 0.195)
         //     return parseFloat(row.percent.toFixed(1));
            else
                return parseFloat(row.percent.toFixed(2));
        },
        sortFunc: function(a, b, col)
        {
            if (a.count == null)
                return -1;
            else if (b.count == null)
                return 1;

            if (a.percent >= 1.95)
                var acmp = a.percent.toFixed(1);
         //     var acmp = a.percent.toFixed(0);
         // else if (a.percent >= 0.195)
         //     acmp = parseFloat(a.percent.toFixed(1));
            else
                acmp = parseFloat(a.percent.toFixed(2));

            if (b.percent >= 1.95)
                var bcmp = b.percent.toFixed(1);
         //     var bcmp = b.percent.toFixed(0);
         // else if (b.percent >= 0.195)
         //     bcmp = parseFloat(b.percent.toFixed(1));
            else
                bcmp = parseFloat(b.percent.toFixed(2));

            return $WH.strcmp(acmp, bcmp);
        }
    },

    stock: {
        id: 'stock',
        name: LANG.stock,
        width: '10%',
        value: 'stock',
        compute: function(row, td)
        {
            if (row.stock > 0)
                return row.stock;
            else
            {
                td.style.fontFamily = 'Verdana, sans-serif';
                return String.fromCharCode(8734);
            }
        },
        getVisibleText: function(row)
        {
            if (row.stock > 0)
                return row.stock;
            else
                return String.fromCharCode(8734) + ' infinity';
        }
    },

    currency: {
        id: 'currency',
        name: LANG.currency,
        getValue: function(row)
        {
            if (row.currency)
                return (row.currency[0] ? row.currency[0][1] : 0);
        },
        compute: function(row, td)
        {
            if (row.currency)
            {
                var side = null;
                if (row.side != null)
                    side = row.side;
                else if (row.react != null)
                {
                    if (row.react[0] == 1 && row.react[1] == -1) // Alliance only
                        side = 1;
                    else if (row.react[0] == -1 && row.react[1] == 1) // Horde only
                        side = 2;
                }

                Listview.funcBox.appendMoney(td, null, side, null, row.currency);
            }
        },
        sortFunc: function(a, b, col)
        {
            if (a.currency == null)
                return -1;
            else if (b.currency == null)
                return 1;

            var lena = 0,
                lenb = 0;

            $WH.array_walk(a.currency, function(x, _, __, i) { lena += Math.pow(10, i) + x[1]; });
            $WH.array_walk(b.currency, function(x, _, __, i) { lenb += Math.pow(10, i) + x[1]; });

            return $WH.strcmp(lena, lenb);
        }
    },

    mode: {
        id: 'mode',
        name: 'Mode',
        after: 'name',
        type: 'text',
        compute: function(row, td)
        {
            if (row.modes && row.modes.mode)
            {
                if ((row.modes.mode & 120) == 120 || (row.modes.mode & 3) == 3)
                    return LANG.pr_note_all;

                return Listview.extraCols.mode.getVisibleText(row);
            }
        },
        getVisibleText: function(row)
        {
            // TODO: Remove magic numbers.
            var modeNormal = !!(row.modes.mode & 26);
            var modeHeroic = !!(row.modes.mode & 97);
            var player10   = !!(row.modes.mode & 40);
            var player25   = !!(row.modes.mode & 80);

            var specificPlayers;
            if (player10 && !player25)
                specificPlayers = 10;
            else if (player25 && !player10)
                specificPlayers = 25;

            var specificMode;
            if (modeNormal && !modeHeroic)
                specificMode = 'normal';
            else if (modeHeroic && !modeNormal)
                specificMode = 'heroic';

            if (specificMode)
            {
                if (specificPlayers)
                    return $WH.sprintf(LANG['tab_' + specificMode + 'X'], specificPlayers); // e.g. "Heroic 25"
                else
                    return LANG['tab_' + specificMode]; // e.g. "Heroic"
            }

            if (specificPlayers)
                return $WH.sprintf(LANG.lvzone_xman, specificPlayers); // e.g. "25-player"

            return LANG.pr_note_all;
        },
        sortFunc: function(a, b, col)
        {
            if (a.modes && b.modes)
                return -$WH.strcmp(a.modes.mode, b.modes.mode);
        }
    },

    requires:
    {
        id: 'requires',
        name: LANG.requires,
        type: 'text',
        compute: function(item, td)
        {
            if (item.achievement && g_achievements[item.achievement])
            {
                $WH.nw(td);
                td.className = 'small';
                td.style.lineHeight = '18px';

                var a = $WH.ce('a');
                a.href = '?achievement=' + item.achievement;
                a.className = 'icontiny tinyspecial';
                a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + g_achievements[item.achievement].icon.toLowerCase() + '.gif)';
                a.style.whiteSpace = 'nowrap';

                $WH.st(a, g_achievements[item.achievement]['name_' + Locale.getName()]);
                $WH.ae(td, a);
            }
        },
        getVisibleText: function(item)
        {
            if (item.achievement && g_achievements[item.achievement])
                return g_achievements[item.achievement].name;
        },
        sortFunc: function(a, b, col)
        {
            return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
        }
    },

    reqskill:
    {
        id: 'reqskill',
        name: LANG.skill,
        width: '10%',
        value: 'reqskill',
        before: 'yield'
    },

    yield:
    {
        id: 'yield',
        name: LANG.yields,
        type: 'text',
        align: 'left',
        span: 2,
        value: 'name',
        compute: function(row, td, tr)
        {
            if (row.yield && g_items[row.yield])
            {
                var i = $WH.ce('td');
                i.style.width = '1px';
                i.style.padding = '0';
                i.style.borderRight = 'none';

                $WH.ae(i, g_items.createIcon(row.yield, 1));
                $WH.ae(tr, i);
                td.style.borderLeft = 'none';

                var wrapper = $WH.ce('div');

                var a = $WH.ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = '?item=' + row.yield;
                a.className = 'q' + g_items[row.yield].quality;
                $WH.ae(a, $WH.ct(g_items[row.yield]['name_' + Locale.getName()]));
                $WH.ae(wrapper, a);
                $WH.ae(td, wrapper);
            }
        },
        getVisibleText: function(row)
        {
            if (row.yield && g_items[row.yield])
                return g_items[row.yield]['name_' + Locale.getName()];
        },
        sortFunc: function(a, b, col)
        {
            if (!a.yield || !g_items[a.yield] || !b.yield || !g_items[b.yield])
                return (a.yield && g_items[a.yield] ? 1 : (b.yield && g_items[b.yield] ? -1 : 0));

            return -$WH.strcmp(g_items[a.yield].quality, g_items[b.yield].quality) ||
                    $WH.strcmp(g_items[a.yield]['name_' + Locale.getName()], g_items[b.yield]['name_' + Locale.getName()]);
        }
    },

    condition:
    {
        id: 'condition',
        name: LANG.tab_conditions,
        type: 'text',
        compute: function(row, td)
        {
            if (!row.condition)
                return;

            td.className = 'small';
            td.style.lineHeight = '18px';
            td.style.textAlign  = 'left';
            td.style.whiteSpace = 'nowrap';

            // tiny links are hard to hit, hmkey?
            td.onclick = (e) => $WH.sp(e);

            var mText = ConditionList.createCell(row.condition);
            Markup.printHtml(mText, td);

            return;
        },
        getVisibleText: function(row)
        {
            var buff = '',
                mText;

            if (!row.condition)
                return buff;

            mText = ConditionList.createCell(row.condition);

            return Markup.removeTags(mText);
        },
        sortFunc: function(a, b, col)
        {
            var text1 = this.getVisibleText(a),
                text2 = this.getVisibleText(b);

            if (text1 != '' && text2 == '')
                return -1;

            if (text2 != '' && text1 == '')
                return 1;

            return $WH.strcmp(text1, text2);
        }
    }
};

Listview.funcBox = {
    createSimpleCol: function(i, n, w, v)
    {
        return {
            id:    i,
            name:  (LANG[n] !== undefined ? LANG[n] : n),
            width: w,
            value: v
        };
    },

    initLootTable: function(row)
    {
        var divider;

        if (this._totalCount != null)
            divider = this._totalCount;
        else
            divider = row.outof;

        if (divider == 0)
        {
            if (row.count != -1)
                row.percent = row.count;
            else
                row.percent = 0;
        }
        else
        {
            row.percent = row.count / divider * 100;
        }

        (Listview.funcBox.initModeFilter.bind(this, row))();
    },

// // subtabs here //

    initModeFilter: function(row)
    {
        if (this._lootModes == null)
            this._lootModes = { 99: 0 };

        if (this._distinctModes == null)
            this._distinctModes = { 99: 0 };

        if ((!row.modes || row.modes.mode == 4) && row.classs != 12 && row.commondrop)
        {
            this._lootModes[99]++; // Trash
            this._distinctModes[99]++;
        }
        else if (row.modes)
        {
            for (var i = -2; i <= 5; ++i)
            {
                if (this._lootModes[i] == null)
                    this._lootModes[i] = 0;

                if (row.modes.mode & 1 << parseInt(i) + 2)
                    this._lootModes[i]++;
            }

            if (this._distinctModes[row.modes.mode] == null)
                this._distinctModes[row.modes.mode] = 0;
            this._distinctModes[row.modes.mode]++;
        }
    },

    addModeIndicator: function()
    {
        var nModes = 0;
        for (var i in this._distinctModes)
        {
            if (this._distinctModes[i])
                nModes++;
        }

        if (nModes < 2)
            return;

        var    pm      = location.hash.match(/:mode=([^:]+)/),
            order   = [0,-1,-2,1,3,2,4,5,99],
            langref = {
                "-2": LANG.tab_heroic,
                "-1": LANG.tab_normal,
                   0: LANG.tab_noteworthy,
                   1: $WH.sprintf(LANG.tab_normalX, 10),
                   2: $WH.sprintf(LANG.tab_normalX, 25),
                   3: $WH.sprintf(LANG.tab_heroicX, 10),
                   4: $WH.sprintf(LANG.tab_heroicX, 25),
                   5: LANG.tab_raidfinder,
                  99: '' // No indicator for trash
            };

        var f = function(mode, dm, updatePound)
        {
            g_setSelectedLink(this, 'lootmode');

            lv.customPound  = lv.id + (dm != null ? ':mode=' + g_urlize(langref[dm].replace(' ', '')) : '');
            lv.customFilter = function(item) { return Listview.funcBox.filterMode(item, lv._totalCount, mode) };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if (updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            modes = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, null, 0);
        firstCallback();

        modes.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for (var j = 0, len = order.length; j < len; ++j)
        {
            var i = order[j];
            if (!this._lootModes[i])
                continue;

            a = $('<a><span>' + langref[i] + '</span> (' + this._lootModes[i] + ')</a>');
            a[0].f = f.bind(a[0], 1 << i + 2, i, 1);
            a.click(a[0].f);

            if (i == 0)
                firstCallback = f.bind(a[0], 1 << i + 2, i, 0);

            if ((i < -1 || i > 2) && i != 5)
                a.addClass('icon-heroic');

            modes.push($('<span class="indicator-mode"></span>').append(a).append($('<b' + (i < -1 || i > 2 ? ' class="icon-heroic"' : '') + '>' + langref[i] + ' (' + this._lootModes[i] + ')</b>'))); // jQuery is dumb

            if (pm && pm[1] == g_urlize(langref[i].replace(' ', '')))
                (a[0].f)();
        }

        var showNoteworthy = false;
        for (var i = 0, len = modes.length; i < len; ++i)
        {
            a = $('a', modes[i]);
            if (!$('span', a).html() && modes.length == 3)
                showNoteworthy = true;
            else
                this.createIndicator(modes[i], null, a[0].f);
        }

        if (showNoteworthy)
            firstCallback();

        $(this.noteTop).append($('<div class="clear"></div>'));
    },

    filterMode: function(row, total, mode)
    {
        if (total != null && row.count != null)
        {
            if (row._count == null)
                row._count = row.count;

            var count = row._count;

            if (mode != null && row.modes[mode])
            {
                count = row.modes[mode].count;
                total = row.modes[mode].outof;
            }

            row.__tr    = null;
            row.count   = count;
            row.outof   = total;
            if (total)
                row.percent = count / total * 100;
            else
                row.percent = count;
        }

        return (mode != null ? ((!row.modes || row.modes.mode == 4) && row.classs != 12 && row.commondrop ? (mode == 32) : (row.modes && (row.modes.mode & mode))) : true);
    },

    initSubclassFilter: function(row)
    {
        var i = row.classs || 0;
        if (this._itemClasses == null)
            this._itemClasses = {};
        if (this._itemClasses[i] == null)
            this._itemClasses[i] = 0;
        this._itemClasses[i]++;
    },

    addSubclassIndicator: function()
    {
        var it = location.hash.match(/:type=([^:]+)/),
            order = [];

        for (var i in g_item_classes)
            order.push({ i: i, n: g_item_classes[i] });
        order.sort(function(a, b) { return $WH.strcmp(a.n, b.n) });

        var f = function(itemClass, updatePound)
        {
            g_setSelectedLink(this, 'itemclass');

            lv.customPound  = lv.id + (itemClass != null ? ':type=' + itemClass : '');
            lv.customFilter = function(item) { return itemClass == null || itemClass == item.classs };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if (updatePound)
                lv.updatePound(1);
        }

        var lv = this,
            classes = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();

        classes.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for (var j = 0, len = order.length; j < len; ++j)
        {
            var i = order[j].i;
            if (!this._itemClasses[i])
                continue;

            a = $('<a><span>' + g_item_classes[i] + '</span> (' + this._itemClasses[i] + ')</a>');
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            classes.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + g_item_classes[i] + ' (' + this._itemClasses[i] + ')</b>')));

            if (it && it[1] == g_urlize(i))
                (a[0].f)();
        }

        if (classes.length > 2)
        {
            for (var i = 0, len = classes.length; i < len; ++i)
                this.createIndicator(classes[i], null, $('a', classes[i])[0].f);

            $(this.noteTop).css('padding-bottom', '12px');
            $(this.noteIndicators).append($('<div class="clear"></div>')).insertAfter($(this.navTop));
        }
    },

    initSpellFilter: function (row)
    {
        if (this._spellTypes == null)
            this._spellTypes = {};

        if (this._spellTypes[row.cat] == null)
            this._spellTypes[row.cat] = 0;

        this._spellTypes[row.cat]++;
    },

    addSpellIndicator: function ()
    {
        var it = location.hash.match(/:type=([^:]+)/);
        var fn = function (spellCat, updatePound)
        {
            g_setSelectedLink(this, "spellType");

            lv.customPound = lv.id + (spellCat != null ? ":type=" + spellCat : "");
            lv.customFilter = function (spell) { return spellCat == null || spell.cat == spellCat; };
            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if (updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            categories = [],
            a;

        a = $("<a><span>" + LANG.pr_note_all + "</span></a>");
        a[0].f = fn.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = fn.bind(a[0], null, 0);
        firstCallback();
        categories.push($('<span class="indicator-mode"></span>').append(a).append($("<b>" + LANG.pr_note_all + "</b>")));

        for (var i in g_spell_categories)
        {
            if (!this._spellTypes[i])
                continue;

            a = $("<a><span>" + g_spell_categories[i] + "</span> (" + this._spellTypes[i] + ")</a>");
            a[0].f = fn.bind(a[0], i, 1);
            a.click(a[0].f);

            categories.push($('<span class="indicator-mode"></span>').append(a).append($("<b>" + g_spell_categories[i] + " (" + this._spellTypes[i] + ")</b>")));

            if (it && it[1] == i)
                (a[0].f)();
        }

        if (categories.length > 2)
        {
            for (var i = 0, len = categories.length; i < len; ++i)
                this.createIndicator(categories[i], null, $("a", categories[i])[0].f);

            $(this.noteTop).css("padding-bottom", "12px");
            $(this.noteIndicators).append($('<div class="clear"></div>')).insertAfter($(this.navTop));
        }
    },

    initStatisticFilter: function(row)
    {
        if (this._achievTypes == null)
            this._achievTypes = {};
        if (this._achievTypes[row.type] == null)
            this._achievTypes[row.type] = 0;
        this._achievTypes[row.type]++;
    },

    addStatisticIndicator: function()
    {
        var it = location.hash.match(/:type=([^:]+)/),
            order = [];

        for (var i in g_achievement_types)
            order.push({ i: i, n: g_achievement_types[i] });
        order.sort(function(a, b) { return $WH.strcmp(a.n, b.n) });

        var f = function(achievType, updatePound)
        {
            g_setSelectedLink(this, 'achievType');

            lv.customPound  = lv.id + (achievType != null ? ':type=' + achievType : '');
            lv.customFilter = function(achievement) { return achievType == null || achievType == achievement.type };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if (updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            types = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();

        types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for (var j = 0, len = order.length; j < len; ++j)
        {
            var i = order[j].i;
            if (!this._achievTypes[i])
                continue;

            a = $('<a><span>' + g_achievement_types[i] + '</span> (' + this._achievTypes[i] + ')</a>');
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + g_achievement_types[i] + ' (' + this._achievTypes[i] + ')</b>')));

            if (it && it[1] == i)
                (a[0].f)();
        }

        if (types.length > 2)
        {
            for (var i = 0, len = types.length; i < len; ++i)
                this.createIndicator(types[i], null, $('a', types[i])[0].f);

            $(this.noteTop).append($('<div class="clear"></div>'));
        }
    },

    initQuestFilter: function(row)
    {
        if (this._questTypes == null)
            this._questTypes = {};

        for (var i = 1; i <= 4; ++i)
        {
            if (this._questTypes[i] == null)
                this._questTypes[i] = 0;

            if (row._type && (row._type & 1 << i - 1))
                this._questTypes[i]++;
        }
    },

    addQuestIndicator: function()
    {
        var it = location.hash.match(/:type=([^:]+)/);

        var f = function(questType, updatePound)
        {
            g_setSelectedLink(this, 'questType');

            lv.customPound  = lv.id + (questType != null ? ':type=' + questType : '');
            lv.customFilter = function(quest) { return questType == null || (quest._type & 1 << questType - 1) };

            lv.updateFilters(1);
            lv.applySort();
            lv.refreshRows();
            if (updatePound)
                lv.updatePound(1);
        };

        var lv = this,
            types = [],
            a;

        a = $('<a><span>' + LANG.pr_note_all + '</span></a>');
        a[0].f = f.bind(a[0], null, 1);
        a.click(a[0].f);
        var firstCallback = f.bind(a[0], null, 0);
        firstCallback();

        types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + LANG.pr_note_all + '</b>')));

        for (var i = 1; i <= 4; ++i)
        {
            if (!this._questTypes[i])
                continue;

            a = $('<a><span>' + g_quest_indicators[i] + '</span> (' + this._questTypes[i] + ')</a>');
            a[0].f = f.bind(a[0], i, 1);
            a.click(a[0].f);

            types.push($('<span class="indicator-mode"></span>').append(a).append($('<b>' + g_quest_indicators[i] + ' (' + this._questTypes[i] + ')</b>')));

            if (it && it[1] == i)
                (a[0].f)();
        }

        if (types.length > 2)
        {
            for (var i = 0, len = types.length; i < len; ++i)
                this.createIndicator(types[i], null, $('a', types[i])[0].f);

            $(this.noteTop).css('padding-bottom', '12px');
            $(this.noteIndicators).append($('<div class="clear"></div>')).insertAfter($(this.navTop));
        }
    },

// \\ subtabs here \\

    assocArrCmp: function(a, b, arr)
    {
        if (a == null)
            return -1;
        else if (b == null)
            return 1;

        var n = Math.max(a.length, b.length);
        for (var i = 0; i < n; ++i)
        {
            if (a[i] == null)
                return -1;
            else if (b[i] == null)
                return 1;

            var res = $WH.strcmp(arr[a[i]], arr[b[i]]);
            if (res != 0)
                return res;
        }

        return 0
    },

    assocBinFlags: function(f, arr)
    {
        var res = [];
        for (var i in arr)
        {
            if (!isNaN(i) && (f & 1 << i - 1))
                res.push(i);
        }
        res.sort(function(a, b) { return $WH.strcmp(arr[a], arr[b]); });

        return res;
    },

    location: function(row, td)
    {
        if (row.location == null)
            return -1;

        for (var i = 0, len = row.location.length; i < len; ++i)
        {
            if (i > 0)
                $WH.ae(td, $WH.ct(LANG.comma));

            var zoneId = row.location[i];
            if (zoneId == -1)
                $WH.ae(td, $WH.ct(LANG.ellipsis));
            else
            {
                var a = $WH.ce('a');
                a.className = 'q1';
                a.href = '?zone=' + zoneId;
                $WH.ae(a, $WH.ct(g_zones[zoneId]));
                $WH.ae(td, a);
            }
        }
    },

    arrayText: function(arr, lookup)
    {
        if (arr == null)
            return;
        else if (!$WH.is_array(arr))
            return lookup[arr];

        var buff = '';
        for (var i = 0, len = arr.length; i < len; ++i)
        {
            if (i > 0)
                buff += ' ';

            if (!lookup[arr[i]])
                continue;

            buff += lookup[arr[i]];
        }

        return buff;
    },

    createCenteredIcons: function(arr, td, text, type)
    {
        if (arr != null)
        {
            var d  = $WH.ce('div'),
                d2 = $WH.ce('div');

            $WH.ae(document.body, d);

            if (text && (arr.length != 1 || type != 2))
            {
                var bibi = $WH.ce('div');
                bibi.style.position = 'relative';
                bibi.style.width = '1px';
                var bibi2 = $WH.ce('div');
                bibi2.className = 'q0';
                bibi2.style.position = 'absolute';
                bibi2.style.right = '2px';
                bibi2.style.lineHeight = '26px';
                bibi2.style.fontSize = '11px';
                bibi2.style.whiteSpace = 'nowrap';
                $WH.ae(bibi2, $WH.ct(text));
                $WH.ae(bibi, bibi2);
                $WH.ae(d, bibi);

                d.style.paddingLeft = bibi2.offsetWidth + 'px';
            }

            var iconPool = g_items;
            if (type == 1)
                iconPool = g_spells;

            for (var i = 0, len = arr.length; i < len; ++i)
            {
                var icon;
                if (arr[i] == null)
                {
                    icon = $WH.ce('div');
                    icon.style.width = icon.style.height = '26px';
                }
                else
                {
                    var id,
                        num;

                    if (typeof arr[i] == 'object')
                    {
                        id  = arr[i][0];
                        num = arr[i][1];
                    }
                    else
                        id = arr[i];

                    if (id)
                        icon = iconPool.createIcon(id, 0, num);
                    else
                        icon = Icon.create('inventoryslot_empty', 0, null, 'javascript:;');
                }

                if (arr.length == 1 && type == 2) // Tiny text display
                {
                    if (id && g_items[id])
                    {
                        $WH.ee(d);
                        var item = g_items[id],
                            a    = $WH.ce('a'),
                            sp   = $WH.ce('span');

                            sp.style.paddingTop = '4px';

                        a.href = '?item=' + id;
                        a.className = 'q' + item.quality + ' icontiny tinyspecial';
                        a.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + item.icon.toLowerCase() + '.gif)';
                        a.style.whiteSpace = 'nowrap';

                        $WH.st(a, item['name_' + Locale.getName()]);
                        $WH.ae(sp, a);

                        if (num > 1)
                            $WH.ae(sp, $WH.ct(' (' + num + ')'));

                        if (text)
                        {
                            var bibi = $WH.ce('span');
                            bibi.className = 'q0';
                            bibi.style.fontSize = '11px';
                            bibi.style.whiteSpace = 'nowrap';
                            $WH.ae(bibi, $WH.ct(text));
                            $WH.ae(d, bibi);
                            if ($(bibi2).length > 0)
                                sp.style.paddingLeft = $(bibi2).width() + 'px';
                        }

                        $WH.ae(d, sp);
                    }
                }
                else
                {
                    icon.style.cssFloat = icon.style.styleFloat = 'left';
                    $WH.ae(d, icon);

                    d.style.margin = '0 auto';
                    d.style.textAlign = 'left';

                    d.style.width = (26 * arr.length) + 'px';
                }
            }

            d2.className = 'clear';

            $WH.ae(td, d);
            $WH.ae(td, d2);

            return true;
        }
    },

    createSocketedIcons: function(sockets, td, gems, match, text)
    {
        var nMatch = 0,
            d      = $WH.ce('div'),
            d2     = $WH.ce('div');

        for (var i = 0, len = sockets.length; i < len; ++i)
        {
            var
                icon,
                gemId = gems[i];

                if (g_items && g_items[gemId])
                    icon = g_items.createIcon(gemId, 0);
                else if ($WH.isset('g_gems') && g_gems && g_gems[gemId])
                    icon = Icon.create(g_gems[gemId].icon, 0, null, '?item=' + gemId);
                else
                    icon = Icon.create(null, 0, null, 'javascript:;');

            icon.className += ' iconsmall-socket-' + g_file_gems[sockets[i]] + (!gems || !gemId ? '-empty' : '');
            icon.style.cssFloat = icon.style.styleFloat = 'left';

            if (match && match[i])
            {
                icon.insertBefore($WH.ce('var'), icon.childNodes[1]);
                ++nMatch;
            }

            $WH.ae(d, icon);
        }

        d.style.margin = '0 auto';
        d.style.textAlign = 'left';

        d.style.width = (26 * sockets.length) + 'px';
        d2.className = 'clear';

        $WH.ae(td, d);
        $WH.ae(td, d2);

        if (text && nMatch == sockets.length)
        {
            d = $WH.ce('div');
            d.style.paddingTop = '4px';
            $WH.ae(d, $WH.ct(text));
            $WH.ae(td, d);
        }
    },

    getItemType: function(itemClass, itemSubclass, itemSubsubclass)
    {
        if (itemSubsubclass != null && g_item_subsubclasses[itemClass] != null && g_item_subsubclasses[itemClass][itemSubclass] != null)
            return {
                url: '?items=' + itemClass + '.' + itemSubclass + '.' + itemSubsubclass,
                text: g_item_subsubclasses[itemClass][itemSubclass][itemSubsubclass]
            };
        else if (itemSubclass != null && g_item_subclasses[itemClass] != null)
            return {
                url: '?items=' + itemClass + '.' + itemSubclass,
                text: g_item_subclasses[itemClass][itemSubclass]
            };
        else
            return {
                url: '?items=' + itemClass,
                text: g_item_classes[itemClass]
            };
    },

    getQuestCategory: function(category)
    {
        return g_quest_sorts[category];
    },

    getQuestReputation: function(faction, quest)
    {
        if (quest.reprewards)
            for (var i = 0, len = quest.reprewards.length; i < len; ++i)
                if (quest.reprewards[i][0] == faction)
                    return quest.reprewards[i][1];
    },

    getFactionCategory: function(category, category2)
    {
        if (category)
            return g_faction_categories[category];
        else
            return g_faction_categories[category2];
    },

    getEventNextDates: function(startDate, endDate, recurrence, fromWhen)
    {
        if (typeof startDate != 'string' || typeof endDate != 'string')
            return [null, null];

        startDate = new Date(startDate.replace(/-/g, '/'));
        endDate   = new Date(endDate.replace(/-/g, '/'));

        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime()))
            return [null, null];

        if (fromWhen == null)
            fromWhen = g_serverTime;

        var offset = 0;
        if (recurrence == -1) // Once by month using day of the week of startDate
        {
            // 2009: Monday .. Monday
            // 2010: Sunday .. Saturday, first full week of the month, unless only Sunday is in the previous month

            var nextEvent = new Date(fromWhen.getFullYear(), fromWhen.getMonth(), 1, startDate.getHours(), startDate.getMinutes(), startDate.getSeconds()); // counts as next until it ends
            for (var i = 0; i < 2; ++i)
            {
                nextEvent.setDate(1);
                nextEvent.setMonth(nextEvent.getMonth() + i); // + 0 = try current month, else try next month
                var day = nextEvent.getDay();
                var tolerance = 1;
                if (nextEvent.getYear() == 2009)
                    tolerance = 0;
                if (day > tolerance)
                {
                    nextEvent.setDate(nextEvent.getDate() + (7 - day)); // first sunday
                }

                var eventEnd = new Date(nextEvent);
                eventEnd.setDate(eventEnd.getDate() + (7 - tolerance)); // 2010, length of 6. tolerance is 1 if it isnt 2009
                if (fromWhen.getTime() < eventEnd.getTime())
                    break; // event hasnt ended yet, so this is still the current one
            }

            offset = nextEvent.getTime() - startDate.getTime();
        }
        else if (recurrence > 0)
        {
            recurrence *= 1000; // sec -> ms

            offset = Math.ceil((fromWhen.getTime() - endDate.getTime()) / recurrence) * recurrence;
        }

        startDate.setTime(startDate.getTime() + offset);
        endDate.setTime(endDate.getTime() + offset);

        return [startDate, endDate];
    },

    createTextRange: function(min, max)
    {
        min |= 0;
        max |= 0;
        if (min > 1 || max > 1)
        {
            if (min != max && max > 0)
                return min + '-' + max;
            else
                return min + '';
        }

        return null;
    },

    coGetColor: function(comment, mode, blog)
    {
        if (comment.user && g_customColors[comment.user])
            return ' comment-' + g_customColors[comment.user];

        switch (mode)
        {
            case -1: // Edit forum post
                var foo = null;
                if (!blog)
                    foo = comment.divPost.childNodes[1].className.match(/comment-([a-z]+)/);
                else
                    foo = comment.divBody[0].className.match(/comment-([a-z]+)/);
                if (foo != null)
                    return ' comment-' + foo[1];
                break;

            case 3: // Post reply (forums)
                if (comment.roles & U_GROUP_PREMIUMISH)
                    return ' comment-gold';
            case 4: // Signature (account settings)
                if (comment.roles & U_GROUP_ADMIN)
                    return ' comment-blue';
                if (comment.roles & U_GROUP_GREEN_TEXT) // Mod, Bureau, Dev
                    return ' comment-green';
                if (comment.roles & U_GROUP_VIP) // VIP
                    return ' comment-gold';
                if (comment.roles & U_GROUP_PREMIUMISH) // Premium, Editor
                    return ' comment-gold';
                break;
        }

        if (comment.roles & U_GROUP_ADMIN)
            return ' comment-blue';
        else if ((comment.commentv2 && comment.sticky) || (comment.roles & U_GROUP_GREEN_TEXT) || (comment.rating >= 10))
            return ' comment-green';
        else if (comment.rating < -2)
            return ' comment-bt';
        else if (comment.roles & U_GROUP_PREMIUMISH)
            return ' comment-gold';

        return '';
    },

    coFlagUpOfDate: function(comment)
    {
        var notificationDiv = comment.commentCell.find('.comment-notification');
        var god = comment.user == g_user.name || ((g_user.roles & (U_GROUP_ADMIN|U_GROUP_MOD)) != 0);

        if (!god) {
            return;
        }

        if (confirm('Mark comment ' + comment.id + ' as up to date?')) {
            comment.container.removeClass('comment-outofdate');
            $.post('?comment=out-of-date', { id: comment.id, remove: 1 }, function(data) {
                    if (data == 'ok')
                    {
                        comment.outofdate = false;
                        Listview.templates.comment.updateCommentCell(comment);
                        MessageBox(notificationDiv, LANG.lvcomment_uptodateresponse);
                    }
                    else
                        MessageBox(notificationDiv, 'Error: ' + data);
                }
            );
        }
        return;
    },

    coFlagOutOfDate: function(comment)
    {
        var notificationDiv = comment.commentCell.find('.comment-notification');
        var god = comment.user == g_user.name || ((g_user.roles & (U_GROUP_ADMIN|U_GROUP_BUREAU)) != 0);

        // SpiffyJr, God users don't require a reason
        if (god) {
            if (confirm('Mark comment ' + comment.id + ' as out of date?')) {
                comment.container.addClass('comment-outofdate');
                $.post('?comment=out-of-date', { id: comment.id }, function(data) {
                        if (data == 'ok')
                        {
                            comment.outofdate = true;
                            Listview.templates.comment.updateCommentCell(comment);
                            MessageBox(notificationDiv, LANG.lvcomment_outofdateresponse);
                        }
                        else
                            MessageBox(notificationDiv, 'Error: ' + data);
                    }
                );
            }
            return;
        }

        var reason = null;

        while (true) {
            reason = prompt(LANG.lvcomment_outofdate_tip);

            // null value indicates that the confirm box was cancelled
            if (reason == null || reason == false) {
                return;
            } else if (reason.toString().length > 0) {
                break;
            }

            alert(LANG.youmustprovideareason_stc);
        }

        $.post('?comment=out-of-date', { id: comment.id, reason: reason }, function(data) {
                if (data == 'ok')
                {
                    comment.outofdate = true;
                    Listview.templates.comment.updateCommentCell(comment);
                    MessageBox(notificationDiv, LANG.lvcomment_outofdateresponsequeued);
                }
                else
                    MessageBox(notificationDiv, 'Error: ' + data);
            }
        );
    },

    coDelete: function(comment)
    {
        var god = comment.user == g_user.name || ((g_user.roles & (U_GROUP_ADMIN|U_GROUP_MOD|U_GROUP_BUREAU)) != 0);
        var notificationDiv = comment.commentCell.find('.comment-notification');

        if (god)
        {
            if (!confirm(LANG.confirm_deletecomment))
                return;
            $.post('?comment=delete', { id: comment.id });

            if (!comment.commentv2)
            {
                this.deleteRows([comment]);
                return;
            }

            comment.container.addClass('comment-deleted');
            MessageBox(notificationDiv, LANG.commentdeleted_tip);
            comment.deletedInfo = [g_serverTime, g_user.name];
            comment.deleted = true;
            Listview.templates.comment.updateCommentCell(comment);

            // aowow: custom start
            comment.voteCell.hide();
            comment.repliesCell.hide();
            comment.commentBody.hide();
            comment.repliesControl.hide();
            Listview.templates.comment.updateRepliesControl(comment);
            Listview.templates.comment.updateCommentControls(comment, comment.commentControls);
            if (comment.rating >= -4 && !comment.headerCell.data('events'))
            {
                comment.headerCell.css('cursor', 'pointer');
                comment.headerCell.bind('click', function(e) {
                    if ($WH.$E(e)._target.nodeName == 'A')
                        return;

                    comment.voteCell.toggle();
                    comment.repliesCell.toggle();
                    comment.commentBody.toggle();
                    comment.repliesControl.toggle();
                });
            }
            // aowow: custom end

            return;
        }
    },

    coUndelete: function(comment)
    {
        var god = comment.user == g_user.name || ((g_user.roles & (U_GROUP_ADMIN|U_GROUP_MOD)) != 0);
        var notificationDiv = comment.commentCell.find('.comment-notification');

        if (confirm(LANG.votetoundelete_tip))
        {
            $.post('?comment=undelete', { id: comment.id });

            if (god)
            {
                MessageBox(notificationDiv, 'This comment has been restored.');

                if (comment.commentv2)
                {
                    comment.container.removeClass('comment-deleted');
                    comment.deletedInfo = null;
                    comment.deleted = false;
                    Listview.templates.comment.updateCommentCell(comment);

                    // aowow: custom start
                    comment.voteCell.show();
                    comment.repliesCell.show();
                    comment.commentBody.show();
                    comment.repliesControl.show();
                    Listview.templates.comment.updateRepliesControl(comment);
                    if (comment.rating >= -4)
                    {
                        comment.headerCell.css('cursor', 'auto');
                        comment.headerCell.unbind('click');
                    }
                    // aowow custom end
                }
            }
            else
                MessageBox(notificationDiv, LANG.votedtodelete_tip);
        }
    },

    coEdit: function(comment, mode, blog)
    {
        // aowow: custom
        if (comment.commentCell.find('.comment-edit')[0]) {
            return;
        }

        if (blog) {
            comment.divBody.hide();
            comment.divResponse.hide();
        }

        var divEdit = $('<div/>');
        divEdit.addClass('comment-edit');
        comment.divEdit = divEdit[0];

        if (mode == -1) // edit post (forums)
        {
            if (g_users[comment.user] != null)
                comment.roles = g_users[comment.user].roles;
        }

        var ta = Listview.funcBox.coEditAppend(divEdit, comment, mode, blog);

        var divButtons = $('<div/>');
        divButtons.addClass('comment-edit-buttons');

        var ip = $('<button/>', { text: LANG.compose_save });
        ip.click(Listview.funcBox.coEditButton.bind(ip[0], comment, true, mode, blog));
        divButtons.append(ip);

        divButtons.append($WH.ct(' '));

        ip = $('<button/>', { text: LANG.compose_cancel });
        ip.click(Listview.funcBox.coEditButton.bind(ip[0], comment, false, mode, blog));
        divButtons.append(ip);
        divEdit.append(divButtons);

        divEdit.insertAfter(comment.divBody);

        ta.focus();
    },

    coEditAppend: function(div, comment, mode, blog, noRestrictedMarkup)
    {
        var charLimit = Listview.funcBox.coGetCharLimit(mode);

        // Modes:
        // -1: Edit forum post
        //  0: Edit comment
        //  1: Post your comment
        //  2: Public description (Account settings)
        //  3: Post reply (forums)
        //  4: Signature (Account settings)

        if (mode == 1 || mode == 3 || mode == 4) // new comment/forum post/sig
        {
            comment.user = g_user.name;
            comment.roles = g_user.roles;
            comment.rating = 1;
        }
        else if (mode == 2) // public description
        {
            comment.roles = g_user.roles;
            comment.rating = 1;
        }

        if (noRestrictedMarkup)
            comment.roles &= ~U_GROUP_PENDING;

        if (mode == -1 || mode == 0)
        {
            var divMode = $('<div/>', { text: LANG.compose_mode });
            divMode.addClass('comment-edit-modes');

            var aEdit = $('<a/>', { href: 'javascript:;', text: LANG.compose_edit });
            aEdit.click(Listview.funcBox.coModeLink.bind(aEdit[0], 1, mode, comment));
            aEdit.addClass('selected');
            divMode.append(aEdit);

            divMode.append($WH.ct('|'));

            var aPreview = $('<a/>', { href: 'javascript:;', text: LANG.compose_preview });
            aPreview.click(Listview.funcBox.coModeLink.bind(aPreview[0], 2, mode, comment));
            divMode.append(aPreview);

            div.append(divMode);
        }

        var divPreview = $('<div/>', { css: { display: 'none' } });
        divPreview.addClass('text comment-body' + Listview.funcBox.coGetColor(comment, mode, blog));

        var divBody = $('<div/>');
        divBody.addClass('comment-edit-body');

        var tb = $('<div style="float: left" />');
        tb.addClass('toolbar');

        var tm = $('<div style="float: left" />');
        tm.addClass('menu-buttons');

        var ta = $('<textarea/>', { val: comment.body, rows: 10, css: { clear: 'left' } });
        ta.addClass('comment-editbox');
        switch (mode)
        {
            case 1:
                ta.attr('name', 'commentbody');
             // ta.focus(g_revealCaptcha.bind(null, 'klrbetkjerbt46', false, false, 'Listview commentbody'));
                break;

            case 2:
                ta.attr({ name: 'desc', originalValue: comment.body });
                break;

            case 3:
                ta.attr('name', 'body');
             // ta.focus(g_revealCaptcha.bind(null, 'klrbetkjerbt46', false, false, 'Listview body'));
                break;

            case 4:
                ta.attr({ name: 'sig', originalValue: comment.body, rows: 3 });
                ta.css('height', 'auto');
                break;
        }

        if (mode != -1 && mode != 0)
        {
            var h3 = $('<h3/>'),
                ah3 = $('<a/>'),
                preview = $('<div/>'),
                pad = $('<div/>'),
                mobile = screen.availWidth <= 480;

            var func = Listview.funcBox.coLivePreview.bind(ta[0], comment, mode, preview[0]);

            ah3.addClass('disclosure-' + (mobile ? 'off' : 'on'));

            ah3.text(LANG.compose_livepreview);
            h3.append(ah3);
            ah3.attr('href', 'javascript:;');
            ah3.click(function() { func(1); var on = g_toggleDisplay(preview); ah3.toggleClass('disclosure-on', on); ah3.toggleClass('disclosure-off', !on); });

            h3.addClass('first');
            pad.addClass('pad');

            divPreview.append(h3);
            divPreview.append(preview);
            divPreview.append(pad);

            g_onAfterTyping(ta[0], func, 50);

            ta.focus(function() { func(); divPreview.css('display', (mobile ? 'none' : '')); if (mode != 4) { ta.css('height', '22em'); } });
        }
        else if (mode != 4)
            ta.focus(function() { ta.css('height', '22em'); });

        var buttons = [
            {id: 'b',     title: LANG.markup_b,     pre: '[b]',        post: '[/b]'},
            {id: 'i',     title: LANG.markup_i,     pre: '[i]',        post: '[/i]'},
            {id: 'u',     title: LANG.markup_u,     pre: '[u]',        post: '[/u]'},
            {id: 's',     title: LANG.markup_s,     pre: '[s]',        post: '[/s]'},
            {id: 'small', title: LANG.markup_small, pre: '[small]',    post: '[/small]'},
            {id: 'url',   title: LANG.markup_url,   nopending: true, onclick: function() {var url = prompt(LANG.prompt_linkurl, 'http://');if (url) g_insertTag(ta[0], '[url=' + url + ']', '[/url]')}},
            {id: 'quote', title: LANG.markup_quote, pre: '[quote]',    post: '[/quote]'},
            {id: 'code',  title: LANG.markup_code,  pre: '[code]',     post: '[/code]'},
            {id: 'ul',    title: LANG.markup_ul,    pre: '[ul]\n[li]', post: '[/li]\n[/ul]', rep: function(txt) {return txt.replace(/\n/g, '[/li]\n[li]')}},
            {id: 'ol',    title: LANG.markup_ol,    pre: '[ol]\n[li]', post: '[/li]\n[/ol]', rep: function(txt) {return txt.replace(/\n/g, '[/li]\n[li]')}},
            {id: 'li',    title: LANG.markup_li,    pre: '[li]',       post: '[/li]'}
        ];

        if (!blog)
        {
            for (var i = 0, len = buttons.length; i < len; ++i)
            {
                var button = buttons[i];
                if (mode == 4 && button.id == 'quote')
                    break;
                if ((g_user.roles & U_GROUP_PENDING) && button.nopending)
                    continue;

                var but = $('<button/>', { click: function(button, event) { event.preventDefault(); (button.onclick != null ? button.onclick : g_insertTag.bind(0, ta[0], button.pre, button.post, button.rep))(); }.bind(null, button) });
                but[0].setAttribute('type', 'button');
                var img = $('<img/>');
                but.attr('title', button.title);

                img.attr('src', g_staticUrl + '/images/deprecated/pixel.gif');
                img.addClass('toolbar-' + button.id);

                but.append(img);
                tb.append(but);
            }
        }
        else
        {
            for (var i = 0, len = buttons.length; i < len; ++i)
            {
                var button = buttons[i];
                if ((g_user.rolls & U_GROUP_PENDING) && button.nopending)
                    continue;

                var buttonClass = 'tb-' + button.id;
                var but = $('<button/>', { click: function(button, event) { event.preventDefault(); (button.onclick != null ? button.onclick : g_insertTag.bind(0, ta[0], button.pre, button.post, button.rep))(); }.bind(null, button), 'class': buttonClass, title: button.title });
                but[0].setAttribute('type', 'button');
                but.append('<ins/>');

                tb.append(but);
            }

            tb.addClass('formatting button sm');
        }

        var promptId = function(name, tag)
        {
            var id = prompt($WH.sprintf(LANG.markup_prompt, name), '');
            if (id != null)
                g_insertTag(ta[0], '[' + tag + '=' + (parseInt(id) || 0) + ']', '');
        };

        var menu = [
            [0, LANG.markup_links,, [
                [ 9, LANG.types[10][0] + '...', promptId.bind(null, LANG.types[10][1], 'achievement')],
                [11, LANG.types[13][0] + '...', promptId.bind(null, LANG.types[13][1], 'class')],
                [ 7, LANG.types[8][0]  + '...', promptId.bind(null, LANG.types[8][1],  'faction')],
                [ 0, LANG.types[3][0]  + '...', promptId.bind(null, LANG.types[3][1],  'item')],
                [ 1, LANG.types[4][0]  + '...', promptId.bind(null, LANG.types[4][1],  'itemset')],
                [ 2, LANG.types[1][0]  + '...', promptId.bind(null, LANG.types[1][1],  'npc')],
                [ 3, LANG.types[2][0]  + '...', promptId.bind(null, LANG.types[2][1],  'object')],
                [ 8, LANG.types[9][0]  + '...', promptId.bind(null, LANG.types[9][1],  'pet')],
                [ 4, LANG.types[5][0]  + '...', promptId.bind(null, LANG.types[5][1],  'quest')],
                [12, LANG.types[14][0] + '...', promptId.bind(null, LANG.types[14][1], 'race')],
                [13, LANG.types[15][0] + '...', promptId.bind(null, LANG.types[15][1], 'skill')],
                [ 5, LANG.types[6][0]  + '...', promptId.bind(null, LANG.types[6][1],  'spell')],
                [ 6, LANG.types[7][0]  + '...', promptId.bind(null, LANG.types[7][1],  'zone')]
            ]]
        ];

        divBody.append(tb);
        divBody.append(tm);
        divBody.append($('<div style="clear: left" />'));
        divBody.append(ta);
        divBody.append($('<br/>'));

        Menu.addButtons(tm[0], menu);

        if (mode == 4)
            divBody.append($WH.ct($WH.sprintf(LANG.compose_limit2, charLimit, 3)));
        else
            divBody.append($WH.ct($WH.sprintf(LANG.compose_limit, charLimit)));

        var span = $('<span class="comment-remaining"> ' + $WH.sprintf(LANG.compose_remaining, charLimit - comment.body.length) + '</span>');
        divBody.append(span);

        ta.keyup(Listview.funcBox.coUpdateCharLimit.bind(0, ta, span, charLimit));
        ta.keydown(Listview.funcBox.coUpdateCharLimit.bind(0, ta, span, charLimit));

        if ((mode == -1 || mode == 0) && g_user.roles & U_GROUP_MODERATOR)
        {
            var spaceDiv = $('<div/>', { 'class': 'pad' });
            var responseDiv = $('<div/>', { text: (g_user.roles & U_GROUP_ADMIN ? 'Admin' : 'Moderator') + ' response' });
            var response = $('<textarea/>', { val: comment.response, rows: 3, css: { height: '6em' } });
            divBody.append(spaceDiv);
            divBody.append(responseDiv);
            divBody.append(response);
        }

        div.append(divBody);
        div.append($('<br/>'));
        div.append(divPreview);

        $('<div/>')
            .append('<div class="pad"/>')
            .append(
                $('<h3 class="first"/>')
                    .append(
                        $('<a class="disclosure-off"/>')
                            .text(LANG.compose_formattinghelp)
                            .click(function() { g_disclose(this.parentNode.nextSibling, this) })
                    )
            )
            .append(
                $('<div style="display: none"/>')
                    .append(Markup.toHtml('[markupdoc help=user]'))
            )
            .insertAfter(div.parent());

        return ta;
    },

    coLivePreview: function(comment, mode, div, force)
    {
        if (force != 1 && div.style.display == 'none')
            return;

        var ta        = this,
            charLimit = Listview.funcBox.coGetCharLimit(mode),
            str       = (ta.value.length > charLimit ? ta.value.substring(0, charLimit) : ta.value);

        if (mode == 4)
        {
            var foo;
            if ((foo = str.indexOf('\n')) != -1 && (foo = str.indexOf('\n', foo + 1)) != -1 && (foo = str.indexOf('\n', foo + 1)) != -1)
                str = str.substring(0, foo);
        }

        var allowed = Markup.rolesToClass(comment.roles);
        var html = Markup.toHtml(str, {allow: allowed, mode: Markup.MODE_COMMENT, roles: comment.roles});
        if (html)
            div.innerHTML = html;
        else
            div.innerHTML = '<span class="q6">...</span>';
    },

    coEditButton: function(comment, update, mode, blog)
    {
        if (update)
        {
            var tas = $WH.gE(comment.divEdit, 'textarea');
            var ta = tas[0];
            if (!Listview.funcBox.coValidate(ta, mode))
                return;

            if (ta.value != comment.body || (tas[1] && tas[1].value != comment.response))
            {
                var nEdits = 0;
                if (comment.lastEdit != null)
                    nEdits = comment.lastEdit[1];
                ++nEdits;
                comment.lastEdit = [g_serverTime, nEdits, g_user.name];

                if (!comment.commentv2)
                    Listview.funcBox.coUpdateLastEdit(comment);

                var charLimit = Listview.funcBox.coGetCharLimit(mode);

                var allowed = Markup.rolesToClass(comment.roles);
                var bodyHtml = Markup.toHtml((ta.value.length > charLimit ? ta.value.substring(0, charLimit) : ta.value), {allow: allowed, mode: Markup.MODE_COMMENT, roles: comment.roles});
                var respHtml = ((tas[1] && tas[1].value.length > 0) ? Markup.toHtml('[div][/div][wowheadresponse=' + g_user.name + ' roles=' + g_user.roles + ']' + tas[1].value + '[/wowheadresponse]', { allow: Markup.CLASS_STAFF, mode: Markup.MODE_COMMENT, roles: g_user.roles }) : '');

                if (comment.commentv2)
                {
                    comment.body = ta.value;

                    if (g_user.roles & U_GROUP_MODERATOR && tas[1])
                    {
                        comment.response      = tas[1].value;
                        comment.responseuser  = g_user.name;
                        comment.responseroles = g_user.roles;
                    }

                    Listview.templates.comment.updateCommentCell(comment);
                }
                else
                {
                    if (!blog) {
                        comment.divBody.innerHTML = bodyHtml;
                        comment.divResponse.innerHTML = respHtml;
                    }
                    else {
                        comment.divBody.html(bodyHtml);
                        comment.divResponse.html(respHtml);
                    }
                    comment.body = ta.value;

                    if (g_user.roles & U_GROUP_MODERATOR && tas[1])
                        comment.response = tas[1].value;
                }

                var params = 'body=' + $WH.urlencode(comment.body);
                if (comment.response !== undefined)
                    params += '&response=' + $WH.urlencode(comment.response);

                if (mode == -1)
                    new Ajax('?forums=editpost&id=' + comment.id, {method: 'POST', params: params});
                else
                    new Ajax('?comment=edit&id=' + comment.id, {method: 'POST', params: params});
            }
        }

        if (comment.commentv2)
        {
            Listview.templates.comment.updateCommentCell(comment);
        }
        else if (!blog) {
            comment.divBody.style.display = '';
            comment.divResponse.style.display = '';
            comment.divLinks.firstChild.style.display = '';
        }
        else {
            comment.divBody.show();
            comment.divResponse.show();
        }

        if (!comment.commentv2)
        {
            $WH.de(comment.divEdit);
            comment.divEdit = null;
        }
    },

    coGetCharLimit: function(mode)
    {
        if (mode == 2) // Public description (Account settings)
            return 7500;

        if (mode == 4) // Signature (Account settings)
            return 250;

        if (g_user.roles & U_GROUP_STAFF)
            return 16000000;

        var multiplier = 1;

        if (g_user.premium)
            multiplier = 3;

        switch (mode)
        {
            case 0: // Edit comment
            case 1: // Post comment
                return 7500 * multiplier;

            case -1: // Edit forum post
            case 3: // Post forum reply
                return 15000 * multiplier;
        }
    },

    coUpdateCharLimit: function(ta, span, charLimit)
    {
        var text = $(ta).val();
        if (text.length > charLimit)
            $(ta).val(text.substring(0, charLimit));
        else
        {
            $(span).html(' ' + $WH.sprintf(LANG.compose_remaining, charLimit - text.length)).removeClass('q10');
            if (text.length == charLimit)
                $(span).addClass('q10');
        }
    },

    coModeLink: function(mode, m, comment)
    {
        var charLimit = Listview.funcBox.coGetCharLimit(m);
        var markupMode = Markup.MODE_COMMENT;

        $WH.array_walk($WH.gE(this.parentNode, 'a'), function(x) {x.className = ''});
        this.className = 'selected';

        var
            tas = $WH.gE(this.parentNode.parentNode, 'textarea'),
            ta = tas[0],
            taContainer = ta.parentNode,
            preview = $('.comment-body', taContainer.parentNode)[0];

        if (m == 4)
            markupMode = Markup.MODE_SIGNATURE;

        switch (mode)
        {
            case 1:
                taContainer.style.display = '';
                preview.style.display = 'none';
                taContainer.firstChild.focus();
                break;

            case 2:
                taContainer.style.display = 'none';

                var str = (ta.value.length > charLimit ? ta.value.substring(0, charLimit) : ta.value);
                if (m == 4)
                {
                    var foo;
                    if ((foo = str.indexOf('\n')) != -1 && (foo = str.indexOf('\n', foo + 1)) != -1 && (foo = str.indexOf('\n', foo + 1)) != -1)
                        str = str.substring(0, foo);
                }

                var allowed = Markup.rolesToClass(comment.roles);
                var html = Markup.toHtml(str, {allow: allowed, mode: markupMode, roles: comment.roles});
                if (tas[1] && tas[1].value.length > 0)
                    html += Markup.toHtml('[div][/div][wowheadresponse=' + g_user.name + ' roles=' + g_user.roles + ']' + tas[1].value + '[/wowheadresponse]', { allow: Markup.CLASS_STAFF, mode: markupMode, roles: g_user.roles });
                preview.innerHTML = html;
                preview.style.display = '';
                break;
        }
    },

    coValidate: function(ta, mode)
    {
        mode |= 0;

        if (mode == 1 || mode == -1)
        {
            if ($WH.trim(ta.value).length < 10)
            {
                alert(LANG.message_forumposttooshort);
                return false;
            }
        }
        else
        {
            if ($WH.trim(ta.value).length < 10)
            {
                alert(LANG.message_commenttooshort);
                return false;
            }
        }

        var charLimit = Listview.funcBox.coGetCharLimit(mode);

        if (ta.value.length > charLimit)
        {
            if (!confirm(
                $WH.sprintf(mode == 1 ? LANG.confirm_forumposttoolong : LANG.confirm_commenttoolong, charLimit, ta.value.substring(charLimit - 30, charLimit)))
            )
                return false;
        }

        return true;
    },

    coSortNewestFirst: function(me)
    {
     // $WH.sc('comments_sort', 1000, '1', '/', '.wowhead.com');
        $WH.sc('comments_sort', 1000, '1', '/', location.hostname);

        $(me).parent().find('a.selected').removeClass('selected');
        me.className = 'selected';
        this.mainDiv.className += ' listview-aci';
        this.setSort([-5, 4, 6, -1, -2], true, false);
    },

    coSortOldestFirst: function(me)
    {
     // $WH.sc('comments_sort', 1000, '2', '/', '.wowhead.com');
        $WH.sc('comments_sort', 1000, '2', '/', location.hostname);

        $(me).parent().find('a.selected').removeClass('selected');
        me.className = 'selected';
        this.mainDiv.className += ' listview-aci';
        this.setSort([-5, 4, 6, 1, 2], true, false);
    },

    coSortHighestRatedFirst: function(me)
    {
     // $WH.sc('comments_sort', 1000, '3', '/', '.wowhead.com');
        $WH.sc('comments_sort', 1000, '3', '/', location.hostname);

        $(me).parent().find('a.selected').removeClass('selected');
        me.className = 'selected';
        this.mainDiv.className = this.mainDiv.className.replace('listview-aci', '');
        this.setSort([-5, 4, 6, -3, 2], true, false);
    },

    coFilterByPatchVersion: function(select)
    {
        this.minPatchVersion = select.value;
        this.refreshRows();
    },

    coUpdateLastEdit: function(comment)
    {
        var _ = comment.divLastEdit;
        if (!_) return;

        if (comment.lastEdit != null)
        {
            var __ = comment.lastEdit;
            _.childNodes[1].firstChild.nodeValue = __[2];
            _.childNodes[1].href = '?user=' + __[2];

            var editedOn = new Date(__[0]);
            var elapsed = (g_serverTime - editedOn) / 1000;

            if (_.childNodes[3].firstChild)
                $WH.de(_.childNodes[3].firstChild);

            g_formatDate(_.childNodes[3], elapsed, editedOn);

            var extraTxt = '';

            if (comment.rating != null) // Comments only
                extraTxt += $WH.sprintf(LANG.lvcomment_patch, g_getPatchVersion(editedOn));

            if (__[1] > 1)
                extraTxt += LANG.dash + $WH.sprintf(LANG.lvcomment_nedits, __[1]);

            _.childNodes[4].nodeValue = extraTxt;

            _.style.display = '';
        }
        else
            _.style.display = 'none';
    },

    coFormatFileSize: function(size)
    {
        var i = -1;
        var units = 'KMGTPEZY';

        while (size >= 1024 && i < 7)
        {
            size /= 1024;
            ++i;
        }

        if (i < 0)
            return size + ' byte' + (size > 1? 's': '');
        else
            return size.toFixed(1) + ' ' + units[i] + 'B';
    },

    dateEventOver: function(date, event, e)
    {
        var dates = Listview.funcBox.getEventNextDates(event.startDate, event.endDate, event.rec || 0, date),
            buff  = '';

        if (dates[0] && dates[1])
        {
            var t1 = new Date(event.startDate.replace(/-/g, '/')),
                t2 = new Date(event.endDate.replace(/-/g, '/')),
                first,
                last;

            t1.setFullYear(date.getFullYear(), date.getMonth(), date.getDate());
            t2.setFullYear(date.getFullYear(), date.getMonth(), date.getDate());

            if (date.getFullYear() == dates[0].getFullYear() && date.getMonth() == dates[0].getMonth() && date.getDate() == dates[0].getDate())
                first = true;
            if (date.getFullYear() == dates[1].getFullYear() && date.getMonth() == dates[1].getMonth() && date.getDate() == dates[1].getDate())
                last = true;

            if (first && last)
                buff = g_formatTimeSimple(t1, LANG.lvscreenshot_from, 1) + ' ' + g_formatTimeSimple(t2, LANG.date_to, 1);
            else if (first)
                buff = g_formatTimeSimple(t1, LANG.tab_starts);
            else if (last)
                buff = g_formatTimeSimple(t2, LANG.tab_ends);
            else
                buff = LANG.allday;
        }

        $WH.Tooltip.showAtCursor(e, '<span class="q1">' + event.name + '</span><br />' + buff, 0, 0, 'q');
    },

    ssCellOver: function()
    {
        this.className = 'screenshot-caption-over';
    },

    ssCellOut: function()
    {
        this.className = 'screenshot-caption';
    },

    ssCellClick: function(i, e)
    {
        e = $WH.$E(e);

        if (e.shiftKey || e.ctrlKey)
            return;

        var j  = 0,
            el = e._target;

        while (el && j < 3)
        {
            if (el.nodeName == 'A')
                return;
            if (el.nodeName == 'IMG')
                break;
            el = el.parentNode;
        }

        ScreenshotViewer.show({ screenshots: this.data, pos: i });
    },

    ssCreateCb: function(td, row)
    {
        if (row.__nochk)
            return;

        var div = $WH.ce('div');
        div.className = 'listview-cb';
        div.onclick   = Listview.cbCellClick;

        var cb = $WH.ce('input');
        cb.type = 'checkbox';
        cb.onclick = Listview.cbClick;
        $WH.ns(cb);

        if (row.__chk)
        {
            cb.checked = true;
        }

        row.__cb = cb;

        $WH.ae(div, cb);
        $WH.ae(td, div);
    },

    viCellClick: function(i, e)
    {
        e = $WH.$E(e);

        if (e.shiftKey || e.ctrlKey)
            return;

        var j  = 0,
            el = e._target;

        while (el && j < 3)
        {
            if (el.nodeName == 'A')
                return;
            if (el.nodeName == 'IMG')
                break;
            el = el.parentNode;
        }

        VideoViewer.show({ videos: this.data, pos: i });
    },

    moneyHonorOver: function(e)
    {
        $WH.Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_honorpoints + '</b>', 0, 0, 'q1');
    },

    moneyArenaOver: function(e)
    {
        $WH.Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_arenapoints + '</b>', 0, 0, 'q1');
    },

    moneyAchievementOver: function(e)
    {
        $WH.Tooltip.showAtCursor(e, '<b>' + LANG.tooltip_achievementpoints + '</b>', 0, 0, 'q1');
    },


    moneyCurrencyOver: function(currencyId, count, e)
    {
        var buff = g_gatheredcurrencies[currencyId]['name_' + Locale.getName()];

        // aowow: justice / valor points handling removed

        $WH.Tooltip.showAtCursor(e, buff, 0, 0, 'q1');
    },

    appendMoney: function(d, money, side, costItems, costCurrency, achievementPoints)
    {
        var _,
            __,
            ns = 0;

        if (side == 1 || side == 'alliance')
            side = 1;
        else if (side == 2 || side == 'horde')
            side = 2;
        else
            side = 3;

        if (money >= 10000)
        {
            ns = 1;

            _ = $WH.ce('span');
            _.className = 'moneygold';
            $WH.ae(_, $WH.ct($WH.number_format(Math.floor(money / 10000))));
            $WH.ae(d, _);
            money %= 10000;
        }

        if (money >= 100)
        {
            if (ns)
                $WH.ae(d, $WH.ct(' '));
            else
                ns = 1;

            _ = $WH.ce('span');
            _.className = 'moneysilver';
            $WH.ae(_, $WH.ct(Math.floor(money / 100)));
            $WH.ae(d, _);
            money %= 100;
        }

        if (money >= 1)
        {
            if (ns)
                $WH.ae(d, $WH.ct(' '));
            else
                ns = 1;

            _ = $WH.ce('span');
            _.className = 'moneycopper';
            $WH.ae(_, $WH.ct(money));
            $WH.ae(d, _);
        }

        if (costItems != null)
        {
            for (var i = 0; i < costItems.length; ++i)
            {
                if (ns)
                    $WH.ae(d, $WH.ct(' '));
                else
                    ns = 1;

                var itemId = costItems[i][0];
                var count = costItems[i][1];
                var icon = g_items.getIcon(itemId);

                _ = $WH.ce('a');
                _.href = '?item=' + itemId;
                _.className = 'moneyitem';
                _.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon.toLowerCase() + '.gif)';
                $WH.ae(_, $WH.ct(count));
                $WH.ae(d, _);
            }
        }

        if (costCurrency != null)
        {
            for (var i = 0; i < costCurrency.length; ++i)
            {
                if (ns)
                    $WH.ae(d, $WH.ct(' '));
                else
                    ns = 1;

                var currencyId = costCurrency[i][0];
                var count = costCurrency[i][1];
                var icon = ['inv_misc_questionmark', 'inv_misc_questionmark'];
                if (g_gatheredcurrencies[currencyId])
                    icon = g_gatheredcurrencies[currencyId].icon;

//  aowow: replacement
                _ = $WH.ce('a');
                _.href = '?currency=' + currencyId;
                _.onmousemove = $WH.Tooltip.cursorUpdate;
                _.onmouseout = $WH.Tooltip.hide;
                if (currencyId == 103)                      // arena
                {
                    _.className = 'moneyarena tip';
                    _.onmouseover = Listview.funcBox.moneyArenaOver;
                    $WH.ae(_, $WH.ct($WH.number_format(count)));
                }
                else if (currencyId == 104)                 // honor
                {
                    if (side == 3 && icon[0] == icon[1])
                        side = 1;

                    _.className = 'money' + (side == 1 ? 'alliance' : 'horde') + ' tip';
                    _.onmouseover = Listview.funcBox.moneyHonorOver;

                    if (side == 3)
                    {
                        __ = $WH.ce('span');
                        __.className = 'moneyalliance';
                        $WH.ae(__, $WH.ct($WH.number_format(count)));
                        $WH.ae(_, __);
                    }
                    else
                        $WH.ae(_, $WH.ct($WH.number_format(count)));
                }
                else                                        // tokens
                {
                    _.className = 'icontinyr tip q1';
                    _.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon[0].toLowerCase() + '.gif)';
                    _.onmouseover = Listview.funcBox.moneyCurrencyOver.bind(_, currencyId, count);
                    $WH.ae(_, $WH.ct($WH.number_format(count)));
                }
/*  aowow: original
                if (side == 3 && icon[0] == icon[1])
                    side = 1;

                _ = $WH.ce('a');
                _.href = '?currency=' + currencyId;
                _.className = 'icontinyr tip q1';
                _.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon[(side == 3 ? 1 : side - 1)].toLowerCase() + '.gif)';
                _.onmouseover = Listview.funcBox.moneyCurrencyOver.bind(_, currencyId, count);
                _.onmousemove = $WH.Tooltip.cursorUpdate;
                _.onmouseout = $WH.Tooltip.hide;
                $WH.ae(d, _);

                if (side == 3)
                {
                    __ = $WH.ce('span');
                    __.className = 'icontinyr';
                    __.style.backgroundImage = 'url(' + g_staticUrl + '/images/wow/icons/tiny/' + icon[0].toLowerCase() + '.gif)';
                    $WH.ae(_, __);
                    _ = __;
                }
*/
                $WH.ae(d, _);
            }
        }

        // aowow: changed because legitemately passing zero APs from the profiler is a thing
        // (achievementPoints > 0) {
        if (typeof achievementPoints == 'number')
        {
            if (ns)
                $WH.ae(d, $WH.ct(' '));
            else
                ns = 1;

            _ = $WH.ce('span');
            _.className = 'moneyachievement tip';
            _.onmouseover = Listview.funcBox.moneyAchievementOver;
            _.onmousemove = $WH.Tooltip.cursorUpdate;
            _.onmouseout = $WH.Tooltip.hide;
            $WH.ae(_, $WH.ct($WH.number_format(achievementPoints)));
            $WH.ae(d, _);
        }
    },

    getUpperSource: function(source, sm)
    {
        switch (source)
        {
            case 2: // Drop
                if (sm.bd)
                    return LANG.source_bossdrop;
                if (sm.z)
                    return LANG.source_zonedrop;
                break;

            case 4: // Quest
                return LANG.source_quests;

            case 5: // Vendor
                return LANG.source_vendors;
        }

        return g_sources[source];
    },

    getLowerSource: function(source, sm, type)
    {
        switch (source)
        {
            case 3: // PvP
                if (sm.p && g_sources_pvp[sm.p])
                    return { text: g_sources_pvp[sm.p] };
                break;
        }

        switch (type)
        {
            case 0: // None
            case 1: // NPC
            case 2: // Object
                if (sm.z)
                {
                    var res = {
                        url: '?zone=' + sm.z,
                        text: g_zones[sm.z]
                    };

                    if (sm.t && source == 5)
                        res.pretext = LANG.lvitem_vendorin;

                    if (sm.dd && sm.dd != 99)
                    {
                        if (sm.dd < 0) // Dungeon
                            res.posttext = $WH.sprintf(LANG.lvitem_dd, '', (sm.dd < -1 ? LANG.lvitem_heroic : LANG.lvitem_normal));
                        else // Raid
                            res.posttext = $WH.sprintf(LANG.lvitem_dd, (sm.dd & 1 ? LANG.lvitem_raid10 : LANG.lvitem_raid25), (sm.dd > 2 ? LANG.lvitem_heroic : LANG.lvitem_normal));
                    }

                    return res;
                }
                break;

            case 5: // Quest
                return {
                    url: '?quests=' + sm.c2 + '.' + sm.c,
                    text: Listview.funcBox.getQuestCategory(sm.c)
                };
                break;

            case 6: // Spell
                if (sm.c && sm.s)
                    return {
                        url: '?spells=' + sm.c + '.' + sm.s,
                        text: g_spell_skills[sm.s]
                    };
                else
                    return {
                        url: '?spells=0',
                        text: '??'
                    };
                break;
        }
    },

    getExpansionText: function(line)
    {
        var str = '';
        if (line.expansion == 1)
            str += ' bc';
        else if (line.expansion == 2)
            str += ' wotlk wrath';

        return str;
    }
};
