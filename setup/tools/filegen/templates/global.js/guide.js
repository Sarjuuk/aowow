var g_localTime = new Date();

/* This function is to get the stars for the vote control for the guides. */

function GetStars(stars, ratable, userRating, guideId)
{
    var STARS_MAX = 5;
    var averageRating = stars;

    if (userRating)
        stars = userRating;

    stars = Math.round(stars*2)/2;
    var starsRounded = Math.round(stars);
    var ret = $("<span>").addClass('stars').addClass('max-' + STARS_MAX).addClass('stars-' + starsRounded);

    if (!g_user.id)
        ratable = false;

    if (ratable)
        ret.addClass('ratable');

    if (userRating)
        ret.addClass('rated');

    /* This is kinda lame but oh well */
    var contents = '<span>';

    var wbr = '&#8203;';
    var tmp = stars;
    for (var i = 1; i <= STARS_MAX; ++i)
    {
        if (tmp < 1 && tmp > 0)
            contents += '<b class="half">';
        else
            contents += '<b>';
        --tmp;

        contents += '<i class="clickable">' + wbr + '<i></i></i>';
    }

    for (var i = 1; i <= STARS_MAX; ++i)
        contents += '</b>';

    contents += '</span>';

    ret.append(contents);

    if (ratable)
    {
        var starNumber = 0;
        ret.find('i.clickable').each(function() { var starId = ++starNumber; $(this).click(function() { VoteGuide(guideId, averageRating, starId); }); })
    }

    if (userRating)
    {
        var clear = $("<span>").addClass('clear').click(function() { VoteGuide(guideId, averageRating, 0); });
        ret.append(clear);
    }

    if (stars >= 0)
        ret.mouseover(function(event) {$WH.Tooltip.showAtCursor(event, 'Rating:&nbsp;' + stars + '&nbsp;/&nbsp;' + STARS_MAX, 0, 0, 'q');}).mousemove(function(event) {$WH.Tooltip.cursorUpdate(event)}).mouseout(function() {$WH.Tooltip.hide()});

    return ret;
}

function VoteGuide(guideId, oldRating, newRating)
{
    // Update stars display
    $('#guiderating').html(GetStars(oldRating, true, newRating, guideId));

    // Vote
    $.ajax({cache: false, url: '?guide=vote', type: 'POST',
        error: function() {
            $('#guiderating').html(GetStars(oldRating, true, 0, guideId));
            alert('Voting failed. Try again later.');
        },
        success: function(json) {
            var data = eval('(' + json + ')');
            $('#guiderating-value').text(data.rating);
            $('#guiderating-votes').text(GetN5(data.nvotes));
        },
        data: { id: guideId, rating: newRating }
    });
}

/* g_enhanceTextarea and createOptionsMenuWidget are only ever used by the article/guide editor. Why are they in global.js? */

function g_enhanceTextarea (ta, opt) {
    if (!(ta instanceof jQuery))
        ta = $(ta);

    if (ta.data("wh-enhanced") || ta.prop("tagName") != "TEXTAREA")
        return;

    if (typeof opt != "object")
        opt = {};

    var canResize = (function(el) {
        if (!el.dynamicResizeOption)
            return true;

        if ($WH.localStorage.get("dynamic-textarea-resizing") === "true")
            return true;

        if ($WH.localStorage.get("dynamic-textarea-resizing") === "false")
            return false;

        return !el.hasOwnProperty("dynamicSizing") || el.dynamicSizing;
    }).bind(null, opt);

    var height  = ta.height() || 500;
    var wrapper = $("<div/>", { "class": "enhanced-textarea-wrapper" }).insertBefore(ta).append(ta);

    if (!opt.hasOwnProperty("color"))
        wrapper.addClass("enhanced-textarea-dark");
    else if (opt.color)
        wrapper.addClass("enhanced-textarea-" + opt.color);

    if (!opt.hasOwnProperty("dynamicSizing") || opt.dynamicSizing || opt.dynamicResizeOption) {
        var expander = $("<div/>", { "class": "enhanced-textarea-expander" }).prependTo(wrapper);
        var dynamicResize = function(textarea, exactHeight, canResizeFn) {
            if (!canResizeFn())
                return;

            // E.css("height", E.siblings(".enhanced-textarea-expander").html($WH.htmlentities(E.val()).replace(/\n/g, "<br>") + "<br>").height() + (D ? 14 : 34) + "px");
            textarea.css("height", textarea.siblings(".enhanced-textarea-expander").html($WH.htmlentities(textarea.val()) + "<br>").height() + (exactHeight ? 14 : 34) + "px");
        };

        ta.bind("keydown keyup change", dynamicResize.bind(this, ta, opt.exactLineHeights, canResize));
        dynamicResize(ta, opt.exactLineHeights, canResize);

        var setWidth = function(el) { el.css("width", el.parent().width() + "px"); };

        setWidth(expander);
        setTimeout(setWidth.bind(null, expander), 1);

        if (!opt.dynamicResizeOption || (opt.dynamicResizeOption && canResize()))
            wrapper.addClass("enhanced-textarea-dynamic-sizing");
    }

    if (!opt.hasOwnProperty("focusChanges") || opt.focusChanges)
        wrapper.addClass("enhanced-textarea-focus-changes");

    if (opt.markup) {
        var _markupMenu = $("<div/>", { "class": "enhanced-textarea-markup-wrapper" }).prependTo(wrapper);
        var _segments   = $("<div/>", { "class": "enhanced-textarea-markup" }).appendTo(_markupMenu);
        var _toolbar    = $("<div/>", { "class": "enhanced-textarea-markup-segment" }).appendTo(_segments);
        var _menu       = $("<div/>", { "class": "enhanced-textarea-markup-segment" }).appendTo(_segments);

        if (opt.markup == "inline")
            ar_AddInlineToolbar(ta.get(0), _toolbar.get(0), _menu.get(0));
        else
            ar_AddToolbar(ta.get(0), _toolbar.get(0), _menu.get(0));

        if (opt.dynamicResizeOption) {
            var _dynResize    = $("<div/>", { "class": "enhanced-textarea-markup-segment" }).appendTo(_segments);
            var _lblDynResize = $("<label/>").appendTo(_dynResize);
            var _iDynResize   = $("<input/>", { type: "checkbox", checked: canResize() }).appendTo(_lblDynResize);

            _iDynResize.change((function(_opt, taWrapper, textarea, resizable, areaHeight, dynResizeFn) {
                var isChecked = this.is(":checked");

                $WH.localStorage.set("dynamic-textarea-resizing", JSON.stringify(isChecked));
                if (isChecked) {
                    taWrapper.addClass("enhanced-textarea-dynamic-sizing");
                    dynResizeFn(textarea, _opt.exactLineHeights, resizable);
                }
                else {
                    taWrapper.removeClass("enhanced-textarea-dynamic-sizing");
                    textarea.css("height", areaHeight + "px");
                }
            }).bind(_iDynResize, opt, wrapper, ta, canResize, height, dynamicResize));

            $("<span/>", { text: LANG.autoresizetextbox }).appendTo(_lblDynResize);
        }

        if (opt.scrollingMarkup) {
            if (g_enhanceTextarea.scrollerCount)
                g_enhanceTextarea.scrollerCount++;
            else
                g_enhanceTextarea.scrollerCount = 1;

            var cssClassA = "fixable-markup-controls-" + g_enhanceTextarea.scrollerCount;
            var cssClassB = "fixed-markup-controls-"   + g_enhanceTextarea.scrollerCount;

            var getBGColor = function(el) {
                var color = el.css("backgroundColor");
                if (color == "rgba(0, 0, 0, 0)" || color == "transparent")
                    return getBGColor(el.parent());
                else
                    return color;
            };

            var bgColor = getBGColor(_segments);

            for (var css, i = 0; (css = window.document.styleSheets[i]) && css.href; i++) {}
            if (!css) {
                window.document.head.appendChild(document.createElement("style"));
                css = window.document.styleSheets[i];
            }
            css.insertRule("." + cssClassB + " ." + cssClassA + " .enhanced-textarea-markup {background:" + bgColor + ";padding-bottom:5px;padding-top:10px;position:fixed;top:0;z-index:3}", css.cssRules.length);
            css.insertRule(".touch-device ." + cssClassB + " ." + cssClassA + " .enhanced-textarea-markup {padding-top:50px}", css.cssRules.length);

            _markupMenu.addClass(cssClassA);

            var toggleFixedStyles = function(menuContainer, taContainer, cssClass, offset) {
                var pageY = this.scrollY || this.pageYOffset || 0;
                if (pageY > menuContainer.offset().top - 10 - offset && pageY < taContainer.offset().top + taContainer.height() - 100 - offset)
                    $("body").addClass(cssClass);
                else
                    $("body").removeClass(cssClass);
            };

            $(window).scroll(toggleFixedStyles.bind(window, _markupMenu, wrapper, cssClassB, 0));
            toggleFixedStyles.call(window, _markupMenu, wrapper, cssClassB, 0);

            var setSize = (function(D, E) {
                E.css("width",  D.width()  + "px");
                D.css("height", E.height() + "px");
            }).bind(null, _markupMenu, _segments);

            setSize();

            $(window).on("resize", setSize);
            $(function() { setTimeout(setSize, 2000) })
        }
    }

    ta.data("wh-enhanced", true);
};

$WH.createOptionsMenuWidget = function (id, txt, opt) {
    var chevron = $WH.createOptionsMenuWidget.chevron;
    if (opt.noChevron)
        chevron = '';

    var container = $WH.ce('span');
    container.id = 'options-menu-widget-' + id;
    container.className = 'options-menu-widget ' + container.id;
    container.innerHTML = txt + chevron;

    if (opt.id)
        container.id = opt.id;

    if (opt.className)
        container.className += ' ' + opt.className;

    if (opt.options instanceof Array) {
        var widgetmenu = [];
        for (var itr = 0, submenu; submenu = opt.options[itr]; itr++) {
            var menu = [itr, submenu[MENU_IDX_NAME]];

            if ((typeof opt.selected == 'number' || typeof opt.selected == 'string') && opt.selected == submenu[MENU_IDX_ID]) {
                container.innerHTML = submenu[MENU_IDX_NAME] + chevron;
                if (submenu[MENU_IDX_SUB]) {
                    switch (typeof submenu[MENU_IDX_SUB].className) {
                        case 'string':
                            $.data(container, 'options-menu-widget-class', submenu[MENU_IDX_SUB].className);
                            container.className += ' ' + submenu[MENU_IDX_SUB].className;
                            break;
                        case 'function':
                            $.data(container, 'options-menu-widget-class', submenu[MENU_IDX_SUB].className(submenu, true));
                            container.className += ' ' + submenu[MENU_IDX_SUB].className(submenu, true);
                            break;
                    }
                }
            }

            if (submenu[MENU_IDX_URL]) {
                menu.push(function (chevron, container, submenu, opt) {
                    switch (typeof submenu[MENU_IDX_URL]) {
                        case 'string':
                            window.location = submenu[MENU_IDX_URL];
                            break;
                        case 'function':
                            if (typeof opt.updateWidgetText == 'undefined' || opt.updateWidgetText) {
                                container.innerHTML = submenu[MENU_IDX_NAME] + chevron;

                                var o = $.data(container, 'options-menu-widget-class');
                                if (o)
                                    container.className = container.className.replace(new RegExp(' *\\b' + o + '\\b'), '');

                                if (submenu[MENU_IDX_SUB]) {
                                    switch (typeof submenu[MENU_IDX_SUB].className) {
                                        case 'string':
                                            $.data(container, 'options-menu-widget-class', submenu[MENU_IDX_SUB].className);
                                            container.className += ' ' + submenu[MENU_IDX_SUB].className;
                                            break;
                                        case 'function':
                                            $.data(container, 'options-menu-widget-class', submenu[MENU_IDX_SUB].className(submenu, true));
                                            container.className += ' ' + submenu[MENU_IDX_SUB].className(submenu, true);
                                            break;
                                    }
                                }
                            }

                            submenu[MENU_IDX_URL](container, submenu);
                            break;
                    }
                }.bind(null, chevron, Menu.add, submenu, opt))
            }
            else if (!submenu[MENU_IDX_SUB] || !submenu[MENU_IDX_SUB].menu)
                menu[0] = null;

            menu[MENU_IDX_OPT] = {};
            if (submenu[MENU_IDX_SUB]) {
                switch (typeof submenu[MENU_IDX_SUB].className) {
                    case 'string':
                        menu[MENU_IDX_OPT].className = submenu[MENU_IDX_SUB].className;
                        break;
                    case 'function':
                        menu[MENU_IDX_OPT].className = submenu[MENU_IDX_SUB].className.bind(null, submenu, false);
                        break;
                }
                switch (typeof submenu[MENU_IDX_SUB].column) {
                    case 'number':
                    case 'string':
                        menu[MENU_IDX_OPT].column = submenu[MENU_IDX_SUB].column;
                        break;
                    case 'function':
                        menu[MENU_IDX_OPT].column = submenu[MENU_IDX_SUB].column.bind(null, submenu);
                        break;
                }
                switch (typeof submenu[MENU_IDX_SUB].tinyIcon) {
                    case 'string':
                        menu[MENU_IDX_OPT].tinyIcon = submenu[MENU_IDX_SUB].tinyIcon;
                        break;
                    case 'function':
                        menu[MENU_IDX_OPT].tinyIcon = submenu[MENU_IDX_SUB].tinyIcon.bind(null, submenu);
                        break;
                }
                switch (typeof submenu[MENU_IDX_SUB].fontIcon) {
                    case 'string':
                        menu[MENU_IDX_OPT].fontIcon = submenu[MENU_IDX_SUB].fontIcon;
                        break;
                    case 'function':
                        menu[MENU_IDX_OPT].fontIcon = submenu[MENU_IDX_SUB].fontIcon.bind(null, submenu);
                        break;
                }

                if (typeof submenu[MENU_IDX_SUB].isChecked == 'function')
                    menu[MENU_IDX_OPT].checkedFunc = submenu[MENU_IDX_SUB].isChecked.bind(null, submenu);

                if (typeof submenu[MENU_IDX_SUB].menu == 'object' && submenu[MENU_IDX_SUB].menu instanceof Array)
                    Menu.setSubmenu(menu, submenu[MENU_IDX_SUB].menu); // <-- n.d. !

            }

            widgetmenu.push(menu);
        }

        container.menu = widgetmenu;

        if (opt.menuOnClick) {
            container.onmousedown = $WH.rf;
            Menu.add(container, widgetmenu, { showAtElement: true });
        }
        else
            Menu.add(container, widgetmenu);
    }

    if (opt.target)
        $(opt.target).append(container);
    else
        return container;
};
$WH.createOptionsMenuWidget.chevron = ' <i class="fa fa-chevron-down fa-color-gray">~</i>';
