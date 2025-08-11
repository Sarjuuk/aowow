$WH.clickToCopy = function (el, textOrFn, opt)
{
    opt = opt || {};

    $WH.aE(el, 'click', $WH.clickToCopy.copy.bind(null, el, textOrFn, opt));
    // $WH.preventSelectStart(el);

    el.classList.add('click-to-copy');

    if (opt.modifyTooltip)
    {
        el._fixTooltip = function (e) {
            return e + '<br>' + $WH.ce('span', { className: 'q2', innerHTML: $WH.clickToCopy.getTooltip(false, opt) }).outerHTML;
        };

        opt.overrideOtherTooltips = false;
    }

    // aowow - fitted to old system
    // $WH.Tooltips.attach(
    $WH.Tooltip.simple(
        el,
        $WH.clickToCopy.getTooltip.bind(null, false, opt),
        undefined,
        // {
        /* byCursor: */ !opt.attachToElement,
        // stopPropagation: opt.overrideOtherTooltips
        // }
    );
};

$WH.clickToCopy.copy = function (el, textOrFn, opt, ev)
{
    ev.preventDefault();
    ev.stopPropagation();

    if (textOrFn === undefined)
    {
        if (!el.childNodes[0] || !el.childNodes[0].textContent)
        {
            let text = 'Could not find text to copy.';
            // $WH.error(text, el);

            if (opt.attachToElement)
                $WH.Tooltip.show(el, text, 'q10');
            else
                $WH.Tooltip.showAtCursor(ev, text, 'q10');

            return;
        }

        textOrFn = el.childNodes[0].textContent;
    }
    else if (typeof textOrFn === 'function')
        textOrFn = textOrFn();

    $WH.copyToClipboard(textOrFn);

    if (opt.attachToElement)
        $WH.Tooltip.show(el, $WH.clickToCopy.getTooltip(true, opt));
    else
        $WH.Tooltip.showAtCursor(ev, $WH.clickToCopy.getTooltip(true, opt));
};

$WH.clickToCopy.getTooltip = function (clicked, opt)
{
    let txt  = '';
    let attr = undefined;

    if (clicked)
    {
        txt = ' ' + LANG.copied;
        attr = { className: 'q1 icon-tick' };
    }
    else
        txt = LANG.clickToCopy;

    let tt = $WH.ce('div', attr, $WH.ct(txt));

    if (opt.prefix)
    {
        tt.style.marginTop = '10px';
        let prefix = typeof opt.prefix === 'function' ? opt.prefix() : opt.prefix;
        return prefix + tt.outerHTML;
    }

    return tt.outerHTML;
};

$WH.copyToClipboard = function (text, t)
{
    if (!$WH.copyToClipboard.hiddenInput)
    {
        $WH.copyToClipboard.hiddenInput = $WH.ce('textarea', { className: 'hidden-element' });
        $WH.ae(document.body, $WH.copyToClipboard.hiddenInput);
    }

    $WH.copyToClipboard.hiddenInput.value = text;

    let isEmpty = $WH.copyToClipboard.hiddenInput.value === '';
    if (isEmpty)
        $WH.copyToClipboard.hiddenInput.value = LANG.nothingToCopy_tip;

    $WH.copyToClipboard.hiddenInput.focus();
    $WH.copyToClipboard.hiddenInput.select();

    if (!document.execCommand('copy'))
        prompt(null, text);

    $WH.copyToClipboard.hiddenInput.blur();

    if (t)
    {
        if (isEmpty)
            $WH.Tooltips.showFadingTooltipAtCursor(LANG.nothingToCopy_tip, t, 'q10');
        else
        {
            let e = $WH.ce('span', { className: 'q1 icon-tick' }, $WH.ct(' ' + LANG.copied));
            $WH.Tooltips.showFadingTooltipAtCursor(e.outerHTML, t);
        }
    }
};
