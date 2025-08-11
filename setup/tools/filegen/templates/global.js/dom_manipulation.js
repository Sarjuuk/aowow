/*
Global functions related to DOM manipulation, events & forms that jQuery doesn't already provide
*/

function g_addCss(css)
{
    var style = $WH.ce('style');
    style.type = 'text/css';

    if (style.styleSheet) // ie
        style.styleSheet.cssText = css;
    else
        $WH.ae(style, $WH.ct(css));

    var head = $WH.gE(document, 'head')[0];
    $WH.ae(head, style);
}

function g_setTextNodes(n, text)
{
    if (n.nodeType == 3)
        n.nodeValue = text;
    else
    {
        for (var i = 0; i < n.childNodes.length; ++i)
            g_setTextNodes(n.childNodes[i], text);
    }
}

function g_setInnerHtml(n, text, nodeType)
{
    if (n.nodeName.toLowerCase() == nodeType)
        n.innerHTML = text;
    else
    {
        for (var i = 0; i < n.childNodes.length; ++i)
            g_setInnerHtml(n.childNodes[i], text, nodeType);
    }
}

function g_getFirstTextContent(node)
{
    for (var i = 0; i < node.childNodes.length; ++i)
    {
        if (node.childNodes[i].nodeName == '#text')
            return node.childNodes[i].nodeValue;

        var ret = g_getFirstTextContent(node.childNodes[i]);
        if (ret)
            return ret;
    }

    return false;
}

function g_getTextContent(el)
{
    var txt = '';
    for (var i = 0; i < el.childNodes.length; ++i)
    {
        if (el.childNodes[i].nodeValue)
            txt += el.childNodes[i].nodeValue;
        else if (el.childNodes[i].nodeName == 'BR')
            txt += '\n';

        txt += g_getTextContent(el.childNodes[i]);
    }

    return txt;
}

function g_toggleDisplay(el)
{
    el = $(el);
    el.toggle();
    if (el.is(':visible'))
        return true;

    return false;
}

function g_enableScroll(enabled)
{
    if (!enabled)
    {
        $WH.aE(document, 'mousewheel',     g_enableScroll.F);
        $WH.aE(window,   'DOMMouseScroll', g_enableScroll.F);
    }
    else
    {
        $WH.dE(document, 'mousewheel',     g_enableScroll.F);
        $WH.dE(window,   'DOMMouseScroll', g_enableScroll.F);
    }
}

g_enableScroll.F = function(e)
{
    if (e.stopPropagation)
        e.stopPropagation();
    if (e.preventDefault)
        e.preventDefault();

    e.returnValue = false;
    e.cancelBubble = true;

    return false;
};

// from http://blog.josh420.com/archives/2007/10/setting-cursor-position-in-a-textbox-or-textarea-with-javascript.aspx
function g_setCaretPosition(elem, caretPos)
{
    if (!elem)
        return;

    if (elem.createTextRange)
    {
        var range = elem.createTextRange();
        range.move('character', caretPos);
        range.select();
    }
    else if (elem.selectionStart != undefined)
    {
        elem.focus();
        elem.setSelectionRange(caretPos, caretPos);
    }
    else
        elem.focus();
}

function g_insertTag(where, tagOpen, tagClose, repFunc)
{
    var n = $WH.ge(where);

    n.focus();
    if (n.selectionStart != null)
    {
        var s  = n.selectionStart,
            e  = n.selectionEnd,
            sL = n.scrollLeft,
            sT = n.scrollTop;

            var selectedText = n.value.substring(s, e);
        if (typeof repFunc == 'function')
            selectedText = repFunc(selectedText);

        n.value = n.value.substr(0, s) + tagOpen + selectedText + tagClose + n.value.substr(e);
        n.selectionStart = n.selectionEnd = e + tagOpen.length;

        n.scrollLeft = sL;
        n.scrollTop  = sT;
    }
    else if (document.selection && document.selection.createRange)
    {
        var range = document.selection.createRange();

        if (range.parentElement() != n)
            return;

        var selectedText = range.text;
        if (typeof repFunc == 'function')
            selectedText = repFunc(selectedText);

        range.text = tagOpen + selectedText + tagClose;
/*
        range.moveEnd("character", -tagClose.length);
        range.moveStart("character", range.text.length);

        range.select();
*/
    }

    if (n.onkeyup)
        n.onkeyup();
}

function g_onAfterTyping(input, func, delay)
{
    var timerId;
    var ldsgksdgnlk623 = function()
    {
        if (timerId)
        {
            clearTimeout(timerId);
            timerId = null;
        }
        timerId = setTimeout(func, delay);
    };
    input.onkeyup = ldsgksdgnlk623;
}

function g_onClick(el, func)
{
    var firstEvent = 0;

    function rightClk(n)
    {
        if (firstEvent)
        {
            if (firstEvent != n)
                return;
        }
        else
            firstEvent = n;

        func(true);
    }

    el.onclick = function(e)
    {
        e = $WH.$E(e);

        if (e._button == 2) // middle click
            return true;

        return false;
    }

    el.oncontextmenu = function()
    {
        rightClk(1);

        return false;
    }

    el.onmouseup = function(e)
    {
        e = $WH.$E(e);

        if (e._button == 3 || e.shiftKey || e.ctrlKey) // Right/Shift/Ctrl
        {
            rightClk(2);
        }
        else if (e._button == 1) // Left
        {
            func(false);
        }

        return false;
    }
}

function g_isLeftClick(e)
{
    e = $WH.$E(e);
    return (e && e._button == 1);
}

function g_preventEmptyFormSubmission() // Used on the homepage and in the top bar
{
    if (!$.trim(this.elements[0].value))
        return false;
}
