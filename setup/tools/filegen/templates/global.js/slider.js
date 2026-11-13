var Slider = new function()
{
    const _self = this;

    var
        start,
        handleObj,
        timer;

    function onMouseDown(e)
    {
        handleObj = this;
        start     = $WH.g_getCursorPos(e);

        $WH.aE(document, 'mousemove', onMouseMove);
        $WH.aE(document, 'mouseup', onMouseUp);

        return false;
    }

    function onMouseMove(e)
    {
        if (!start || !handleObj)
            return;

        var
            cursor  = $WH.g_getCursorPos(e),
            delta   = cursor.x - start.x,
            outside = setPosition(handleObj, handleObj._pos + delta),
            current = getCurrentPosition(handleObj);

        if (current && handleObj.elements.input)
            handleObj.elements.input.value = current.value;

        if (!outside)
            start = cursor;

        if (handleObj.onMove)
            handleObj.onMove(e, handleObj, current);
    }

    function onMouseUp(e)
    {
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
        handleObj = obj;
        start     = $WH.g_getCursorPos(e);

        var
            offset = $WH.ac(handleObj.parentNode),
            center = Math.floor(getHandleWidth(handleObj) / 2);

        setPosition(handleObj, start.x - offset.x - center);

        var current = getCurrentPosition(handleObj);

        if (current && handleObj.elements.input)
            handleObj.elements.input.value = current.value;

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

        if (e.type === 'change' || e.type === 'keypress' && e.which === 13)
            onInput(obj, e);
        else
            timer = setTimeout(onInput.bind(0, obj, e), 1000);
    }

    function onInput(obj, e)
    {
        let
            value   = obj.elements.input.value,
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

    function setPosition(obj, offset, percent)
    {
        let outside = false;
        let maxPos  = getMaxPosition(obj);

        if (offset < 0)
        {
            offset  = 0;
            outside = true;
        }
        else if (offset > maxPos)
        {
            offset  = maxPos;
            outside = true;
        }

        let handleWidth = getHandleWidth(obj);
        if (!handleWidth && typeof percent === 'number')
            percent = Math.min(Math.max(percent, 0), 100);
        else
        {
            percent  = parseFloat(offset) / maxPos;
            percent -= handleWidth / getTrackerWidth(obj) * percent;
            percent *= 100;
        }

        obj.elements.trackBarProgress.style.width = percent + '%';
        obj.style.left = percent + '%';
        obj._pos = offset;

        return outside;
    }

    function getMaxPosition(obj)
    {
        return getTrackerWidth(obj) - getHandleWidth(obj);
    }

    function getCurrentPosition(obj)
    {
        var
            percent = obj._pos / getMaxPosition(obj),
            value   = Math.round(percent * (obj._max - obj._min) + obj._min),
            result  = [percent, value];

        result.percent = percent;
        result.value   = value;

        return result;
    }

    function getTrackerWidth(obj)
    {
        if (obj._tsz > 0)
            return obj._tsz;

        return obj.elements.track.offsetWidth;
    }

    function getHandleWidth(obj)
    {
        return typeof obj._hsz === 'number' ? obj._hsz : obj.offsetWidth;
    }

    this.getInput = function(obj)
    {
        return obj.elements.input;
    };

    this.getPercent = function(obj)
    {
        return getCurrentPosition(obj).percent;
    };

    this.getValue = function(obj)
    {
        return getCurrentPosition(obj).value;
    };

    this.setMax = function(obj, max)
    {
        obj._max = max;
        if (obj.elements.maxLabel)
            $WH.st(obj.elements.maxLabel, max);

        Slider.setValue(obj, getCurrentPosition(obj).value);
    };

    this.setPercent = function(obj, percent)
    {
        setPosition(obj, parseInt(percent * getMaxPosition(obj)));
    };

    this.setValue = function(obj, value, fromContainer)
    {
        if (value < obj._min)
            value = obj._min;
        else if (value > obj._max && fromContainer !== true)
            value = obj._max;

        if (value > obj._max)
            obj.elements.container.classList.add('exceeded-max');
        else
            obj.elements.container.classList.remove('exceeded-max');

        if (obj.elements.input)
            obj.elements.input.value = value;

        let pct = (value - obj._min) / (obj._max - obj._min);

        setPosition(obj, getMaxPosition(obj) * pct, 100 * pct);
    };

    this.setValueDisplay = function(obj, value, completion)
    {
        if (!obj.elements.values)
        {
            $WH.error("Can't set the slider value display, this slider doesn't support labels.", value, obj);
            return;
        }

        if (completion)
            obj.elements.values.dataset.completion = `${completion}`;
        else
            delete obj.elements.values.dataset.completion;

        $WH.ee(obj.elements.values);
        $WH.st(obj.elements.values, value);
    };

    this.setSize = function(obj, length)
    {
        let
            current = getCurrentPosition(obj),
            resized = getMaxPosition(obj);

        obj.elements.container.style.width = length + 'px';

        if (resized !== getMaxPosition(obj))
            this.setValue(obj, current.value);
    };

    this.setTitle = function(obj, title)
    {
        if (!obj.elements.labels)
        {
            $WH.error("Can't set the slider title, this slider doesn't support labels.", title, obj);
            return;
        }

        if (!obj.elements.title)
        {
            obj.elements.title = $WH.ce('span', { className: 'slider-labels-title' });
            $WH.aef(obj.elements.labels, obj.elements.title);
        }

        $WH.ee(obj.elements.title);
        $WH.st(obj.elements.title, title);
    };

    this.init = function(container, opt)
    {
        container.classList.add('slider');
        container.dataset.track = opt.remaining ? 'remaining' : 'progress';

        let obj      = $WH.ce('div', { className: 'slider-track-handle', onmousedown: onMouseDown });
        let track    = $WH.ce('div', { className: 'slider-track',        onmousedown: onClick.bind(0, obj) });
        let bar      = $WH.ce('div', { className: 'slider-track-bar' });
        let progress = $WH.ce('div', { className: 'slider-track-bar-progress' });

        $WH.ae(container, track);
        $WH.ae(track, obj);
        $WH.ae(track, bar);
        $WH.ae(bar, progress);

        obj.elements = {
            container: container,
            input: undefined,
            labels: undefined,
            maxLabel: undefined,
            title: undefined,
            track: track,
            trackBarProgress: progress,
            values: undefined
        };

        obj._min = 1;
        obj._max = 100;
        obj._pos = 0;
        obj._tsz = 0;
        obj._hsz = null;

        if (opt != null)
        {
            // Values
            if (opt.minValue || opt.minValue === 0)
                obj._min = opt.minValue;
            if (opt.maxValue)
                obj._max = opt.maxValue;

            // Functions
            if (opt.onMove)
                obj.onMove = opt.onMove;

            if (opt.trackSize)
                obj._tsz = opt.trackSize;

            if (typeof opt.handleSize === 'number')
                obj._hsz = opt.handleSize;

            if (opt.editable === false)
            {
                container.classList.add('not-editable');
                obj._hsz = 0;
            }

            if (opt.hasOwnProperty('value'))
                _self.setValue(obj, opt.value, true);

            // Labels
            if (opt.showLabels !== false)
            {
                container.classList.add('has-labels');

                obj.elements.labels = $WH.ce('div', { className: 'slider-labels' });
                $WH.ae(container, obj.elements.labels);

                obj.elements.values = $WH.ce('span', { className: 'slider-labels-values' });
                $WH.ae(obj.elements.labels, obj.elements.values);

                let resizeInput;
                if (opt.valueDisplay !== undefined)
                {
                    _self.setValueDisplay(obj, opt.valueDisplay);
                    resizeInput = function() {};
                }
                else
                {
                    let e = $WH.ce('span', { className: 'slider-labels-values-sizer' });
                    $WH.ae(obj.elements.values, e);

                    if (obj._min !== 1)
                    {
                     // let e = $WH.ce('span', { className: 'slider-labels-values-min' }, $WH.ct(WH.TERMS.minimum_abbrev + WH.TERMS.colon_punct + obj._min));
                        let e = $WH.ce('span', { className: 'slider-labels-values-min' }, $WH.ct(LANG.minimum_abbrev + LANG.colon + obj._min));
                        $WH.ae(obj.elements.values, e);
                    }

                    obj.elements.input = $WH.ce('input',
                    {
                        className: 'slider-labels-values-input',
                        value: opt.hasOwnProperty('value') ? opt.value : obj._max,
                        type: 'text'
                    });

                    if (opt.editable === false)
                        obj.elements.input.disabled = true;

                    $WH.ae(obj.elements.values, obj.elements.input);
                    obj.elements.maxLabel = $WH.ce('span', { className: 'slider-labels-values-max' }, $WH.ct(obj._max));
                    $WH.ae(obj.elements.values, obj.elements.maxLabel);

                    resizeInput = function()
                    {
                        $WH.st(e, obj.elements.input.value);
                        window.requestAnimationFrame(function()
                        {
                         // aowow - for some reason slider-labels-values-sizer is 1..2px too short
                         // obj.elements.input.style.width = (e.offsetWidth || 24) + 'px';
                            obj.elements.input.style.width = (e.offsetWidth ? e.offsetWidth + 2 : 24) + 'px';
                        });
                    };
                }

                if (typeof opt.title === 'string')
                    _self.setTitle(obj, opt.title);

                resizeInput();

                $(obj.elements.input)
                    .bind('focus',    function() { this.parentNode.classList.add('input-focused') })
                    .bind('blur',     function() { this.parentNode.classList.remove('input-focused') })
                    .bind('change',   resizeInput)
                    .bind('click',    function() { this.select() })
                    .bind('keypress', function(e)
                    {
                        let allowed = [];
                        let usedKey = e.which;

                        for (let i = 48; i < 58; i++)
                            allowed.push(i);

                        if (!($WH.in_array(allowed, usedKey) >= 0) && usedKey !== 13)
                            e.preventDefault();

                        resizeInput();
                    })
                    .bind('keyup keydown change', function(e)
                    {
                        onKeyPress(obj, e);

                        if (e.which === 13 || e.which === 27)
                            this.blur();

                        resizeInput();
                    });
            }
        }

        return obj;
    }
};
