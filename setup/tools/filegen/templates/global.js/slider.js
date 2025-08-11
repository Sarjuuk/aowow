var Slider = new function()
{
    var
        start,
        handleObj,
        timer;

    function onMouseDown(e)
    {
        e = $WH.$E(e);

        handleObj = this;
        start     = $WH.g_getCursorPos(e);

        $WH.aE(document, 'mousemove', onMouseMove);
        $WH.aE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onMouseMove(e)
    {
        e = $WH.$E(e);

        if (!start || !handleObj)
            return;

        var
            cursor  = $WH.g_getCursorPos(e),
            delta   = cursor[handleObj._dir] - start[handleObj._dir],
            outside = setPosition(handleObj, handleObj._pos + delta),
            current = getCurrentPosition(handleObj);

        if (current && handleObj.input)
            handleObj.input.value = current.value;

        if (!outside)
            start = cursor;

        if (handleObj.onMove)
            handleObj.onMove(e, handleObj, current);
    }

    function onMouseUp(e)
    {
        e = $WH.$E(e);

        if (handleObj.onMove)
            handleObj.onMove(e, handleObj, getCurrentPosition(handleObj));

        handleObj = null;
        start     = null;

        $WH.dE(document, 'mousemove', onMouseMove);
        $WH.dE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onClick(obj, e)
    {
        e = $WH.$E(e);

        handleObj = obj;
        start     = $WH.g_getCursorPos(e);

        var
            offset = $WH.ac(handleObj.parentNode),
            center = Math.floor(getHandleWidth(handleObj) / 2);

        setPosition(handleObj, start[handleObj._dir] - offset[handleObj._dir] - center);

        var current = getCurrentPosition(handleObj);

        if (current && handleObj.input)
            handleObj.input.value = current.value;

        if (handleObj.onMove)
            handleObj.onMove(e, handleObj, current);

        $WH.aE(document, 'mousemove', onMouseMove);
        $WH.aE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onKeyPress(obj, e)
    {
        if (timer)
            clearTimeout(timer);

        if (e.type == 'change' || e.type == 'keypress' && e.which == 13)
            onInput(obj, e);
        else
            timer = setTimeout(onInput.bind(0, obj, e), 1000);
    }

    function onInput(obj, e)
    {
        var
            value   = obj.input.value,
            current = getCurrentPosition(obj);

        if (isNaN(value))
            value = current.value;
        if (value > obj._max)
            value = obj._max;
        if (value < obj._min)
            value = obj._min;

        Slider.setValue(obj, value);

        if (obj.onMove)
            obj.onMove(e, obj, getCurrentPosition(obj));
    }

    function setPosition(obj, offset)
    {
        var outside = false;

        if (offset < 0)
        {
            offset  = 0;
            outside = true;
        }
        else if (offset > getMaxPosition(obj))
        {
            offset  = getMaxPosition(obj);
            outside = true;
        }

        obj.style[(obj._dir == 'y' ? 'top' : 'left')] = offset + 'px';
        obj._pos = offset;

        return outside;
    }

    function getMaxPosition(obj)
    {
        return getTrackerWidth(obj) - getHandleWidth(obj) + 2;
    }

    function getCurrentPosition(obj)
    {
        var
            percent = obj._pos / getMaxPosition(obj),
            value   = Math.round((percent * (obj._max - obj._min)) + obj._min),
            result  = [percent, value];

        result.percent = percent;
        result.value   = value;
        return result;
    }

    function getTrackerWidth(obj) {
        if (obj._tsz > 0) {
            return obj._tsz;
        }

        if (obj._dir == 'y') {
            return obj.parentNode.offsetHeight;
        }

        return obj.parentNode.offsetWidth;
    }

    function getHandleWidth(obj) {
        if (obj._hsz > 0)
            return obj._hsz;

        if (obj._dir == 'y')
            return obj.offsetHeight;

        return obj.offsetWidth;
    }

    this.setPercent = function(obj, percent)
    {
        setPosition(obj, parseInt(percent * getMaxPosition(obj)));
    }

    this.setValue = function(obj, value)
    {
        if (value < obj._min)
            value = obj._min;
        else if (value > obj._max)
            value = obj._max;

        if (obj.input)
            obj.input.value = value;

        this.setPercent(obj, (value - obj._min) / (obj._max - obj._min));
    }

    this.setSize = function(obj, length)
    {
        var
            current = getCurrentPosition(obj),
            resized = getMaxPosition(obj);

        obj.parentNode.style[(obj._dir == 'y' ? 'height' : 'width')] = length + 'px';

        if (resized != getMaxPosition(obj))
            this.setValue(obj, current.value);
    }

    this.init = function(container, opt)
    {
        var obj = $WH.ce('a');
        obj.href = 'javascript:;';
        obj.onmousedown = onMouseDown;
        obj.className = 'handle';

        var track = $WH.ce('a');
        track.href = 'javascript:;';
        track.onmousedown = onClick.bind(0, obj);
        track.className = 'track';

        $WH.ae(container, $WH.ce('span'));
        $WH.ae(container, track);
        $WH.ae(container, obj);

        obj._dir = 'x';
        obj._min = 1;
        obj._max = 100;
        obj._pos = 0;
        obj._tsz = 0;
        obj._hsz = 0;

        if (opt != null)
        {
            // Orientation
            if (opt.direction == 'y')
                obj._dir = 'y';

            // Values
            if (opt.minValue)
                obj._min = opt.minValue;
            if (opt.maxValue)
                obj._max = opt.maxValue;

            // Functions
            if (opt.onMove)
                obj.onMove = opt.onMove;

            if (opt.trackSize)
                obj._tsz = opt.trackSize;

            if (opt.handleSize)
                obj._hsz = opt.handleSize;

            // Labels
            if (opt.showLabels !== false)
            {
                var label = $WH.ce('div');

                label.innerHTML = obj._min;
                label.className = 'label min';
                $WH.ae(container, label);

                label = $WH.ce('div');
                label.innerHTML = obj._max;
                label.className = 'label max';
                $WH.ae(container, label);

                obj.input = $WH.ce('input');
                $(obj.input).attr({ value: obj._max, type: 'text' })
                            .bind('click', function () { this.select(); })
                            .keypress(function (e) {
                                var allowed = [];
                                var usedKey = e.which;
                                for (i = 48; i < 58; i++)
                                    allowed.push(i);

                                if (!($WH.in_array(allowed, usedKey) >= 0) && usedKey != 13)
                                    e.preventDefault();
                            })
                            .bind('keyup keydown change', onKeyPress.bind(0, obj));

                obj.input.className = 'input';
                $WH.ae(container, obj.input);
            }
        }

        container.className = 'slider-' + obj._dir + (opt == null || opt.showLabels !== false ? ' has-labels' : '');

        return obj;
    }
};
