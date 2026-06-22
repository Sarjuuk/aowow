$WH.Seo = new function()
{
    this.addJsonLdBreadcrumb = function(menu)
    {
        var path = $WH.Url.getCanonicalPath().replace(/[?&].*$/, "");
        var onDBPage = false;
        var breadcrumbList = {
            "@context": "http://schema.org",
            "@type": "BreadcrumbList",
            itemListElement: []
        };

        var attachListItem = function(pos, menuItem)
        {
            var listItem = {
                "@type": "ListItem",
                position: pos + 1,
                item: { name: menuItem.name }
            };

            if (menuItem.url)
            {
                listItem.item["@id"] = menuItem.url;
                if ($WH.Url.getPathFromUrl(menuItem.url).indexOf(path) >= 0)
                    onDBPage = true;
            }
            if (menuItem.image)
                listItem.item.image = $WH.ensureUrlProtocol(menuItem.image);

            breadcrumbList.itemListElement.push(listItem);
        };

        for (var i = 0, len = menu.length; i < len; i++)
            attachListItem(i, menu[i]);

        var _ = $WH.ge("json-ld-breadcrumbs");
        if (_)
            $WH.de(_);

        if (breadcrumbList.itemListElement.length)
        {
            if (!onDBPage)
            {
                var pageName = window.g_pageInfo ? g_pageInfo.name : undefined;
                if (!pageName)
                {
                 // var h1 = $WH.qsa(".heading-size-1");
                 // if (h1.length === 1)
                 //     pageName = h1[0].textContent;
                    var h1 = $WH.qsa("h1");
                    if (h1.length === 2)
                        pageName = h1[1].textContent;
                }

                if (pageName)
                {
                    attachListItem(i, {
                        name: pageName,
                        url: $WH.Url.getCanonical(),
                        image: getImageUrl() // undefined
                    });
                }
            }

            $WH.ae(document.head, $WH.ce("script", {
                id: "json-ld-breadcrumbs",
                text: JSON.stringify(breadcrumbList),
                type: "application/ld+json"
            }));
        }
    };

    function getImageUrl()
    {
        var intangible = $WH.qsa('script[type="application/ld+json"]') || [];
        for (var i = 0, len = intangible.length; i < len; i++)
        {
            try
            {
                var item = JSON.parse(intangible[i].textContent);
            }
            catch (e) { continue; }

            if (item.mainEntityOfPage && item.image && item.image.url)
                return item.image.url;
        }

        return undefined;
    }
 /*
    function onLoad()
    {
        if (!WH.User.hasCapability(WH.User.CAP_SEO_INFO) && !WH.isDev())
            return;

        if (WH.PageMeta.skipSeoValidation)
            return;

        $WH.onLoad(testSEO);
    }

    function testSEO()
    {
        if (!document.title)
            WH.Admin.Debug.error(WH.Admin.Debug.TYPE_SEO, "No page title!");

        $(function() {
            setTimeout(function() {
                if (!WH.PageMeta.skipSeoHeadingValidation && !WH.qs("h1"))
                    WH.Admin.Debug.error(WH.Admin.Debug.TYPE_SEO, "No H1!");
            }, 1000);
        });

        var description = $('meta[name="description"]').attr("content");
        if (description)
        {
            var text = '[span class="q0"]SEO Description:[/span] ' + description.replace("[", "\\[");
            var warn = false;
            if (description.length < 130)
            {
                text += ' [span class="q0"](short)[/span]';
                warn = true;
            }
            else if (description.length > 160)
            {
                text += ' [span class="q0"](long)[/span]';
                warn = true;
            }

            if (WH.Strings.hasReplacementValues(description))
                WH.Admin.Debug.error(WH.Admin.Debug.TYPE_SEO, text, true);
            else if (warn)
                WH.Admin.Debug.warn(WH.Admin.Debug.TYPE_SEO, text, true);

        }
        else
            WH.Admin.Debug.error(WH.Admin.Debug.TYPE_SEO, "No SEO description!");
    }

    requestAnimationFrame(onLoad);
 */
};
