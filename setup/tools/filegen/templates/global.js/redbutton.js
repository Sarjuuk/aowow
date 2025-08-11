var RedButton = {
    create: function(text, enabled, func)
    {
        var
            a    = $WH.ce('a'),
            em   = $WH.ce('em'),
            b    = $WH.ce('b'),
            i    = $WH.ce('i'),
            span = $WH.ce('span');

        a.href = 'javascript:;';
        a.className = 'button-red';

        $WH.ae(b, i);
        $WH.ae(em, b);
        $WH.ae(em, span);
        $WH.ae(a, em);

        RedButton.setText(a, text);
        RedButton.enable(a, enabled);
        RedButton.setFunc(a, func);

        return a;
    },

    setText: function(button, text)
    {
        $WH.st(button.firstChild.childNodes[0].firstChild, text); // em, b, i
        $WH.st(button.firstChild.childNodes[1], text); // em, span
    },

    enable: function(button, enabled)
    {
        if (enabled || enabled == null)
        {
            button.className = button.className.replace('button-red-disabled', '');
        }
        else if (button.className.indexOf('button-red-disabled') == -1)
            button.className += ' button-red-disabled';
    },

    setFunc: function(button, func)
    {
		$(button).unbind();
		if(func)
			$(button).click(func);
    }
};
