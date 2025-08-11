var Dialog = function()
{
var
    _self = this,
    _template,
    _onSubmit = null,
    _templateName,

    _funcs = {},
    _data,

    _inited = false,
    _form = $WH.ce('form'),
    _elements = {};

    _form.onsubmit = function() {
        _processForm();
        return false
    };

    this.show = function(template, opt)
    {
        if (template)
        {
            _templateName = template;
            _template = Dialog.templates[_templateName];

            _self.template = _template;
        }
        else
            return;

        if (_template.onInit && !_inited)
            (_template.onInit.bind(_self, _form, opt))();

        if (opt.onBeforeShow)
            _funcs.onBeforeShow = opt.onBeforeShow.bind(_self, _form);

        if (_template.onBeforeShow)
            _template.onBeforeShow = _template.onBeforeShow.bind(_self, _form);

        if (opt.onShow)
            _funcs.onShow = opt.onShow.bind(_self, _form);

        if (_template.onShow)
            _template.onShow = _template.onShow.bind(_self, _form);

        if (opt.onHide)
            _funcs.onHide = opt.onHide.bind(_self, _form);

        if (_template.onHide)
            _template.onHide = _template.onHide.bind(_self, _form);

        if (opt.onSubmit)
            _funcs.onSubmit = opt.onSubmit;

        if (_template.onSubmit)
            _onSubmit = _template.onSubmit.bind(_self, _form);

        if (opt.data)
        {
            _inited = false;
            _data = {};
            $WH.cO(_data, opt.data);
        }

        _self.data = _data;

        Lightbox.show('dialog-' + _templateName, {
            onShow: _onShow,
            onHide: _onHide
        });
    }

    this.getValue = function(id)
    {
        return _getValue(id);
    }

    this.setValue = function(id, value)
    {
        _setValue(id, value);
    }

    this.getSelectedValue = function(id)
    {
        return _getSelectedValue(id);
    }

    this.getCheckedValue = function(id)
    {
        return _getCheckedValue(id);
    }

    function _onShow(dest, first)
    {
        if (first || !_inited)
            _initForm(dest);

        if (_template.onBeforeShow)
            _template.onBeforeShow();

        if (_funcs.onBeforeShow)
            _funcs.onBeforeShow();

        Lightbox.setSize(_template.width, _template.height);
        dest.className = 'dialog';

        _updateForm();

        if (_template.onShow)
            _template.onShow();

        if (_funcs.onShow)
            _funcs.onShow();
    }

    function _initForm(dest)
    {
        $WH.ee(dest);
        $WH.ee(_form);

        var container = $WH.ce('div');
        container.className = 'text';
        $WH.ae(dest, container);

        $WH.ae(container, _form);

        if (_template.title)
            {
            var h = $WH.ce('h1');
            $WH.ae(h, $WH.ct(_template.title));
            $WH.ae(_form, h);
        }

        var t         = $WH.ce('table'),
            tb        = $WH.ce('tbody'),
            mergeCell = false;

        $WH.ae(t, tb);
        $WH.ae(_form, t);

        for (var i = 0, len = _template.fields.length; i < len; ++i)
        {
            var
                field = _template.fields[i],
                element;

            if (!mergeCell)
            {
                tr = $WH.ce('tr');
                th = $WH.ce('th');
                td = $WH.ce('td');
            }

            field.__tr = tr;

            if (_data[field.id] == null)
                _data[field.id] = (field.value ? field.value : '');

            var options;
            if (field.options)
            {
                options = [];

                if (field.optorder)
                    $WH.cO(options, field.optorder);
                else
                {
                    for (var j in field.options)
                        options.push(j);
                }

                if (field.sort)
                    options.sort(function(a, b) { return field.sort * $WH.strcmp(field.options[a], field.options[b]); });
            }

            switch (field.type)
            {
                case 'caption':

                    th.colSpan = 2;
                    th.style.textAlign = 'left';
                    th.style.padding = 0;

                    if (field.compute)
                        (field.compute.bind(_self, null, _data[field.id], _form, th, tr))();
                    else if (field.label)
                        $WH.ae(th, $WH.ct(field.label));

                    $WH.ae(tr, th);
                    $WH.ae(tb, tr);

                    continue;
                    break;

                case 'textarea':

                    var f = element = $WH.ce('textarea');

                    f.name = field.id;

                    if (field.disabled)
                        f.disabled = true;

                    f.rows = field.size[0];
                    f.cols = field.size[1];

                    td.colSpan = 2;

                    if (field.label)
                    {
                        th.colSpan = 2;
                        th.style.textAlign = 'left';
                        th.style.padding = 0;
                        td.style.padding = 0;

                        $WH.ae(th, $WH.ct(field.label));
                        $WH.ae(tr, th);
                        $WH.ae(tb, tr);

                        tr = $WH.ce('tr');
                    }

                    $WH.ae(td, f);

                    break;

                case 'select':

                    var f = element = $WH.ce('select');

                    f.name = field.id;

                    if (field.size)
                        f.size = field.size;

                    if (field.disabled)
                        f.disabled = true;

                    if (field.multiple)
                        f.multiple = true;

                    for (var j = 0, len2 = options.length; j < len2; ++j)
                    {
                        var o = $WH.ce('option');

                        o.value = options[j];

                        $WH.ae(o, $WH.ct(field.options[options[j]]));
                        $WH.ae(f, o)
                    }

                    $WH.ae(td, f);

                    break;

                case 'dynamic':

                    td.colSpan = 2;
                    td.style.textAlign = 'left';
                    td.style.padding = 0;

                    if (field.compute)
                        (field.compute.bind(_self, null, _data[field.id], _form, td, tr))();

                    $WH.ae(tr, td);
                    $WH.ae(tb, tr);

                    element = td;

                    break;

                case 'checkbox':
                case 'radio':

                    var k = 0;
                    element = [];
                    for (var j = 0, len2 = options.length; j < len2; ++j)
                    {
                        var
                            s = $WH.ce('span'),
                            f,
                            l,
                            uniqueId = 'sdfler46' + field.id + '-' + options[j];

                        if (j > 0 && !field.noInputBr)
                            $WH.ae(td, $WH.ce('br'));

                        l = $WH.ce('label');
                        l.setAttribute('for', uniqueId);
                        l.onmousedown = $WH.rf;

                        f = $WH.ce('input', { name: field.id, value: options[j], id: uniqueId });
                        f.setAttribute('type', field.type);

                        if (field.disabled)
                            f.disabled = true;

                        if (field.submitOnDblClick)
                            l.ondblclick = f.ondblclick = function(e) { _processForm(); };

                        if (field.compute)
                            (field.compute.bind(_self, f, _data[field.id], _form, td, tr))();

                        $WH.ae(l, f);
                        $WH.ae(l, $WH.ct(field.options[options[j]]));
                        $WH.ae(td, l);

                        element.push(f);
                    }

                    break;

                default: // Textbox

                    var f = element = $WH.ce('input');

                    f.name = field.id;

                    if (field.size)
                        f.size = field.size;

                    if (field.disabled)
                        f.disabled = true;

                    if (field.submitOnEnter)
                    {
                        f.onkeypress = function(e) {
                            e = $WH.$E(e);
                            if (e.keyCode == 13)
                                _processForm();
                        };
                    }

                    f.setAttribute('type', field.type);

                    $WH.ae(td, f);

                    break;
            }

            if (field.label)
            {
                if (field.type == 'textarea')
                {
                    if (field.labelAlign)
                        td.style.textAlign = field.labelAlign;

                    td.colSpan = 2;
                }
                else
                {
                    if (field.labelAlign)
                        th.style.textAlign = field.labelAlign;

                    $WH.ae(th, $WH.ct(field.label));
                    $WH.ae(tr, th);
                }
            }

            if (field.placeholder)
                f.placeholder = field.placeholder;

            if (field.type != 'checkbox' && field.type != 'radio')
            {
                if (field.width)
                    f.style.width = field.width;

                if (field.compute && field.type != 'caption' && field.type != 'dynamic')
                    (field.compute.bind(_self, f, _data[field.id], _form, td, tr))();
            }

            if (field.caption)
            {
                var s = $WH.ce('small');
                if (field.type != 'textarea')
                    s.style.paddingLeft = '2px';
                s.className = 'q0'; // commented in 5.0?
                $WH.ae(s, $WH.ct(field.caption));
                $WH.ae(td, s);
            }

            $WH.ae(tr, td);
            $WH.ae(tb, tr);

            mergeCell = field.mergeCell;

            _elements[field.id] = element;
        }

        for (var i = _template.buttons.length; i > 0; --i)
        {
            var
                button = _template.buttons[i - 1],
                a      = $WH.ce('a');

            a.onclick = _processForm.bind(a, button[0]);
            a.className = 'dialog-' + button[0];
            a.href = 'javascript:;';
            $WH.ae(a, $WH.ct(button[1]));
            $WH.ae(dest, a);
        }

        var _ = $WH.ce('div');
        _.className = 'clear';
        $WH.ae(dest, _);

        _inited = true;
    }

    function _updateForm()
    {
        for (var i = 0, len = _template.fields.length; i < len; ++i)
        {
            var
                field = _template.fields[i],
                f     = _elements[field.id];

            switch (field.type)
            {
                case 'caption': // Do nothing
                    break;

                case 'select':
                    for (var j = 0, len2 = f.options.length; j < len2; j++)
                        f.options[j].selected = (f.options[j].value == _data[field.id] || $WH.in_array(_data[field.id], f.options[j].value) != -1);
                    break;

                case 'checkbox':
                case 'radio':
                    for (var j = 0, len2 = f.length; j < len2; j++)
                        f[j].checked = (f[j].value == _data[field.id] || $WH.in_array(_data[field.id], f[j].value) != -1);
                    break;

                default:
                    f.value = _data[field.id];
                    break;
            }

            if (field.update)
                (field.update.bind(_self, null, _data[field.id], _form, f))();
        }
    }

    function _onHide()
    {
        if (_template.onHide)
            _template.onHide();

        if (_funcs.onHide)
            _funcs.onHide();
    }

    function _processForm(button)
    {
     // if (button == 'x') // aowow - button naming differs
        if (button == 'cancel') // Special case
            return Lightbox.hide();

        for (var i = 0, len = _template.fields.length; i < len; ++i)
        {
            var
                field = _template.fields[i],
                newValue;

            switch (field.type)
            {
                case 'caption': // Do nothing
                    continue;

                case 'select':
                    newValue = _getSelectedValue(field.id);
                    break;

                case 'checkbox':
                case 'radio':
                    newValue = _getCheckedValue(field.id);
                    break;

                case 'dynamic':
                    if (field.getValue)
                    {
                        newValue = field.getValue(field, _data, _form);
                        break;
                    }
                default:
                    newValue = _getValue(field.id);
                    break;
            }

            if (field.validate)
            {
                if (!field.validate(newValue, _data, _form))
                    return;
            }

            if (newValue && typeof newValue == 'string')
                newValue = $WH.trim(newValue);

            _data[field.id] = newValue;
        }

        _submitData(button);
    }

    function _submitData(button)
    {
        var ret;

        if (_onSubmit)
            ret = _onSubmit(_data, button, _form);

        if (_funcs.onSubmit)
            ret = _funcs.onSubmit(_data, button, _form);

        if (ret === undefined || ret)
            Lightbox.hide();

            return false;
    }

    function _getValue(id)
    {
        return _elements[id].value;
    }

    function _setValue(id, value)
    {
        _elements[id].value = value;
    }

    function _getSelectedValue(id)
    {
        var
            result = [],
            f = _elements[id];

            for (var i = 0, len = f.options.length; i < len; i++)
            {
                if (f.options[i].selected)
                    result.push(parseInt(f.options[i].value) == f.options[i].value ? parseInt(f.options[i].value) : f.options[i].value);
        }

        if (result.length == 1)
            result = result[0];

        return result;
    }

    function _getCheckedValue(id)
    {
        var
            result = [],
            f      = _elements[id];

        for (var i = 0, len = f.length; i < len; i++)
        {
            if (f[i].checked)
                result.push(parseInt(f[i].value) == f[i].value ? parseInt(f[i].value) : f[i].value);
        }

        return result;
    }
};

Dialog.templates   = {};
Dialog.extraFields = {};
