// https://developers.google.com/tag-platform/security/guides/consent
$WH.Track = new function ()
{
    const trackAction = 'Click';
    const siteVariables = {
     // adsBlocked: 3,
     // adsUnblocked: 4,
        loggedInUserIsPremium: 2,
        userIsLoggedIn: 1,
     // userShouldSeeAds: 5
    };
    const maxRetryTime = 10000;
    const retryTimeout = 10;
    const scrollDepthPoints = [25, 50, 75, 90, 100];
    const _self = {
        gaReady: false,
        scriptAdded: undefined
    };

    this.gaInit = function (nTries)
    {
        if (!_self.scriptAdded)
        {
            (function (_window, _document, node, src, varName, gaJSNode, firstJSNode)
            {
                _window['GoogleAnalyticsObject'] = varName;
                _window[varName] = _window[varName] || function () { (_window[varName].q = _window[varName].q || []).push(arguments) },
                _window[varName].l = 1 * new Date;

                gaJSNode = _document.createElement(node),
                firstJSNode = _document.getElementsByTagName(node)[0];
                gaJSNode.async = 1;
                gaJSNode.src = src;
                firstJSNode.parentNode.insertBefore(gaJSNode, firstJSNode);
            })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

            let attachGTAG = () => {
                let script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=CFG_GTAG_MEASUREMENT_ID';
                document.body.appendChild(script);
            };

            if (document.body)
                attachGTAG();
            else
                $WH.aE(document, 'DOMContentLoaded', attachGTAG);

            window.dataLayer = window.dataLayer || [];
            window.gtag = function () { dataLayer.push(arguments) };

            gtag('js', new Date);
            gtag('config', 'CFG_GTAG_MEASUREMENT_ID');

            _self.scriptAdded = true;
        }

        if (!window.ga || !ga.create)
        {
            if (!nTries)
                nTries = 1;

            if (nTries > 100)
                return;

            setTimeout($WH.Track.gaInit.bind($WH.Track, nTries + 1), nTries * 9);
            return;
        }

        ga('create', 'UA_MEASUREMENT_KEY', 'CFG_UA_MEASUREMENT_KEY');
     // trackSiteVar(siteVariables.userShouldSeeAds, $WH.WAS.showAds());
        trackSiteVar(siteVariables.userIsLoggedIn, /* $WH.User.isLoggedIn() */g_user.id > 0);
     // if ($WH.User.isLoggedIn())
        if (g_user.id > 0)
            trackSiteVar(siteVariables.loggedInUserIsPremium, /* $WH.User.isPremium() */g_user.premium);

        ga('set', 'anonymizeIp', true);
        ga('send', 'pageview');

        _self.gaReady = true;
        scrollDepthPoints.forEach(registerTrackScroll);
    };

    this.interactiveEvent         =          evt  => trackEvent(evt                                                                          );
    this.nonInteractiveEvent      =          evt  => trackEvent(evt,                                                 { nonInteraction: true });
    this.interactiveEventOutgoing =          evt  => trackEvent(evt,                                                 { isOutgoing: true     });
    this.linkClick                = (anchor, evt) => trackEvent({ ...evt, action: trackAction, value: anchor.href }, { isOutgoing: true     });

    function trackSiteVar(idx, val)
    {
        ga('set', 'dimension' + idx, val)
    }

    function registerTrackScroll(depth)
    {
        let trackDone = false;
        const trackScroll = () => {
            if (trackDone)
                return;

            trackDone = true;
            requestAnimationFrame(() => {
                const y = window.scrollY;
                const h = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                if (y / h * 100 >= depth)
                {
                    trackEvent({
                        action: 'scroll_event',
                        event_category: 'Scroll Depth',
                        event_label: `${ depth }%`,
                        scroll_depth: depth
                    });

                    window.removeEventListener('scroll', trackScroll, { passive: true })
                }
                trackDone = false;
            });
        };

        window.addEventListener('scroll', trackScroll, { passive: true })
    }

    function trackEvent(evt, opts)
    {
        const { action: act, ...o                     } = evt;
        const { category: cat, label: lab, value: val } = o;
        const { nonInteraction: ni, isOutgoing: io    } = opts || {};
        let   { retryCount: rc                        } = opts || {};

        if (!_self.gaReady)
        {
            if ($WH.isset('g_dev') && g_dev)
                return;

            if (!rc)
                rc = 0;

            rc++;
            if (rc * retryTimeout > maxRetryTime)
                return;

            setTimeout(trackEvent.bind(null, evt, {
                nonInteraction: ni,
                isOutgoing: io,
                retryCount: rc
            }), retryTimeout);

            return;
        }

        let attr;
        if (typeof ni === 'boolean')
        {
            attr ??= {};
            attr.nonInteraction = ni ? 1 : 0;
        }

        if (io)
        {
            attr ??= {};
            attr.transport = 'beacon';
        }

        if (cat)
            ga('send', 'event', cat, act, lab, val, attr);

        gtag('event', act, o);
    }
};

// aowow - repurpose old tracking
$(document).ready(function () {
    var trackObjs = {
        'header-logo':       { 'label': 'Database Logo', 'actions': { 'Click image': (node) => true } },
        'home-logo':         { 'label': 'Homepage Logo', 'actions': { 'Click image': (node) => true } },
        'home-oneliner':     { 'label': 'Oneliner',      'actions': { 'Follow link': (node) => true } },
        'home-featuredbox':  { 'label': 'Featured Box',  'actions': { 'Follow link': (node) => node.parentNode.className != 'home-featuredbox-links',
                                                                      'Click image': (node) => node.parentNode.className == 'home-featuredbox-links' }
        }
    };

    Object.entries(trackObjs).forEach(([nodeId, trackInfo]) => {
        let parent = $WH.ge(nodeId);
        if (!parent)
            return;

        $WH.qsa('a', parent).forEach(link => {
            Object.entries(trackInfo.actions).forEach(([action, testFn]) => {
                $WH.aE(link, 'click', evt => {
                    if (!testFn(link))
                        return;

                    let txt = 'unknown';
                    if (_ = g_getFirstTextContent(link))
                        txt = g_urlize(_).substr(0, 80);
                    else if (link.title)
                        txt = g_urlize(link.title).substr(0, 80);
                    else if (link.id)
                        txt = g_urlize(link.id).substr(0, 80);

                    label = `${trackInfo.label}-${action}-${txt}`;
                    $WH.Track.linkClick(link, { category: PageTemplate.get('pageName') || 'unknown', label: label });
                });
            });
        });
    });
});
