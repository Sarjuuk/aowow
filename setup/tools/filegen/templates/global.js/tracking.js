/*
TODO: Create "Tracking" class
*/

function g_trackPageview(tag)
{
    function track()
    {
        if (typeof ga == 'function')
            ga('send', 'pageview', tag);
    };

    $(document).ready(track);
}

function g_trackEvent(category, action, label, value)
{
    function track()
    {
        if (typeof ga == 'function')
            ga('send', 'event', category, action, label, value);
    };

    $(document).ready(track);
}

function g_attachTracking(node, category, action, label, value)
{
    var $node = $(node);

    $node.click(function()
    {
        g_trackEvent(category, action, label, value);
    });
}

function g_addAnalytics()
{
    var objs = {
        'home-logo': {
            'category': 'Homepage Logo',
            'actions': {
                'Click image': function(node) { return true; }
            }
        },
        'home-featuredbox': {
            'category': 'Featured Box',
            'actions': {
                'Follow link': function(node) { return (node.parentNode.className != 'home-featuredbox-links'); },
                'Click image': function(node) { return (node.parentNode.className == 'home-featuredbox-links'); }
            }
        },
        'home-oneliner': {
            'category': 'Oneliner',
            'actions': {
                'Follow link': function(node) { return true; }
            }
        },
        'sidebar-container': {
            'category': 'Page sidebar',
            'actions': {
                'Click image': function(node) { return true; }
            }
        },
        'toptabs-promo': {
            'category': 'Page header',
            'actions': {
                'Click image': function(node) { return true; }
            }
        }
    };

    for (var i in objs)
    {
        var e = $WH.ge(i);
        if (e)
            g_addAnalyticsToNode(e, objs[i]);
    }
}

function g_getNodeTextId(node)
{
    var id   = null,
        text = g_getFirstTextContent(node);

    if (text)
        id = g_urlize(text);
    else if (node.title)
        id = g_urlize(node.title);
    else if (node.id)
        id = g_urlize(node.id);

    return id;
}

function g_addAnalyticsToNode(node, opts, labelPrefix)
{
    if (!opts || !opts.actions || !opts.category)
    {
        if ($WH.isset('g_dev') && g_dev)
        {
            console.log('Tried to add analytics event without appropriate parameters.');
            console.log(node);
            console.log(opts);
        }

        return;
    }

    var category = opts.category;
    var tags = $WH.gE(node, 'a');
    for (var i = 0; i < tags.length; ++i)
    {
        var node = tags[i];
        var action = 'Follow link';
        for (var a in opts.actions)
        {
            if (opts.actions[a] && opts.actions[a](node))
            {
                action = a;
                break;
            }
        }
        var label = (labelPrefix ? labelPrefix + '-' : '') + g_getNodeTextId(node);

        g_attachTracking(node, category, action, label);
    }
}

$(document).ready(g_addAnalytics);
