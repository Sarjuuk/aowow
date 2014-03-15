var Draggable = new function () {
    var
        start = {},
        mouse = {},
        clickObj,
        dragObj;

    function onMouseDown(e) {
        e = $WH.$E(e);

        if (this._handle) {
            var _ = e._target,
            found = false,
            i = 0;

            while (_ && i <= 3) {
                if (_ == this._handle) {
                    found = true;
                    break;
                }

                _ = _.parentNode;
                ++i;
            }

            if (!found) {
                return false;
            }
        }

        clickObj = this;

        start = $WH.g_getCursorPos(e);

        $WH.aE(document, 'mousemove', onMouseMove);
        $WH.aE(document, 'mouseup', onMouseUp);

        if (clickObj.onClick) {
            clickObj.onClick(e, clickObj);
        }

        return false;
    }

    function onMouseMove(e) {
        e = $WH.$E(e);

        var pos = $WH.g_getCursorPos(e);

        if (clickObj) {
            if (Math.abs(pos.x - start.x) > 5 || Math.abs(pos.y - start.y) > 5) {
                onDragStart(e, clickObj);
                clickObj = null;
            }
        }

        if (!dragObj || !dragObj._bounds) {
            return false;
        }

        var
            bounds = getBounds(dragObj),
            dX     = pos.x - start.x,
            dY     = pos.y - start.y;

        dX = Math.max(dragObj._bounds.x1 - mouse.x, Math.min(dragObj._bounds.x2 - mouse.x - (bounds.x2 - bounds.x1), dX));
        dY = Math.max(dragObj._bounds.y1 - mouse.y, Math.min(dragObj._bounds.y2 - mouse.y - (bounds.y2 - bounds.y1), dY));

        setPosition(dX, dY);

        return false;
    }

    function onMouseUp(e) {
        e = $WH.$E(e);

        clickObj = null;

        if (dragObj) {
            onDragEnd(e);
        }
    }

    function onDragStart(e, obj) {
        if (dragObj) {
            onDragEnd(e);
        }

        var foo = $WH.ac(obj);
        mouse.x = foo[0];
        mouse.y = foo[1];

        if (obj._targets.length) {
            dragObj = obj.cloneNode(true);
            dragObj._orig = obj;

            $WH.ae($WH.ge('layers'), dragObj);
            // $WH.ae(document.body, dragObj); // 5.0.. why does it do that?
            setPosition(-2323, -2323);
        }
        else {
            dragObj = obj;
        }

        $WH.Tooltip.disabled = true;
        $WH.Tooltip.hide();

        if (obj.onDrag) {
            obj.onDrag(e, dragObj, obj);
        }

        dragObj._bounds = getBounds(obj._container);
        dragObj.className += ' dragged';
    }

    function onDragEnd(e) {
        var
            found = false,
            cursor = $WH.g_getCursorPos(e);

        if (dragObj._orig && dragObj._orig._targets.length) {
            clearPosition();

            var pos = {
                x1: dragObj._x,
                x2: dragObj._x + parseInt(dragObj.offsetWidth),
                y1: dragObj._y,
                y2: dragObj._y + parseInt(dragObj.offsetHeight)
            };

            $WH.de(dragObj);
            dragObj = dragObj._orig;

            for (var i = 0, len = dragObj._targets.length; i < len; ++i) {
                var targObj = dragObj._targets[i],
                bounds = getBounds(targObj);

                if (pos.x2 >= bounds.x1 && pos.x1 < bounds.x2 && pos.y2 >= bounds.y1 && pos.y1 < bounds.y2) {
                    found = true;
                    if (dragObj.onDrop) {
                        dragObj.onDrop(e, dragObj, targObj, (cursor.x >= bounds.x1 && cursor.x <= bounds.x2 && cursor.y >= bounds.y1 && cursor.y <= bounds.y2));
                    }
                    else {
                        $WH.ae(targObj, dragObj);
                    }
                }
            }
        }

        if (!found && dragObj.onDrop) {
            dragObj.onDrop(e, dragObj, null);
        }

        $WH.dE(document, 'mousemove', onMouseMove);
        $WH.dE(document, 'mouseup', onMouseUp);

        $WH.Tooltip.disabled = false;

        dragObj.className = dragObj.className.replace(/dragged/, '');
        dragObj = null;
    }

    function setPosition(dX, dY) {
        dragObj.style.position = 'absolute';
        dragObj.style.left = mouse.x + dX + 'px';
        dragObj.style.top = mouse.y + dY + 'px';

        dragObj._x = mouse.x + dX;
        dragObj._y = mouse.y + dY;
    }

    function clearPosition() {
        dragObj.style.left = '-2323px';
        dragObj.style.top = '-2323px';
    }

    function getBounds(obj) {
        var pos = $WH.ac(obj);

        return {
            x1: pos[0],
            x2: pos[0] + parseInt(obj.offsetWidth),
            y1: pos[1],
            y2: pos[1] + parseInt(obj.offsetHeight)
        };
    }

    this.init = function (obj, opt) {
        obj.onmousedown = onMouseDown;

        var a = obj.getElementsByTagName('a');
        for (var i = 0, len = a.length; i < len; ++i) {
            $WH.ns(a[i]);
        }

        if (!obj._targets) {
            obj._targets = [];
        }

        if (!obj._container) {
            obj._container = document.body;
        }

        if (opt != null) {
            if (opt.targets) {
                for (var i = 0, len = opt.targets.length; i < len; ++i) {
                    obj._targets.push($WH.ge(opt.targets[i]));
                }
            }

            if (opt.container) {
                obj._container = $WH.ge(opt.container);
            }

            // Functions
            if (opt.onClick) {
                obj.onClick = opt.onClick;
            }

            if (opt.onDrop) {
                obj.onDrop = opt.onDrop;
            }

            if (opt.onDrag) {
                obj.onDrag = opt.onDrag;
            }
        }
    }
};
