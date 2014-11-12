function Cropper(opts) {
    var
        canvas,
        div,
        _;

    $WH.cO(this, opts);

    if (this.parent) {
        this.parent = $WH.ge(this.parent);
    }
    else {
        return;
    }

    this.locked    = 0;
    this.selection = { node: $WH.ce('div') };
    this.canvas    = { node: $WH.ce('div') };

    if (this.minCrop == null) {
        this.minCrop = [150, 150];
    }
    else if (!isNaN(this.minCrop)) {
        this.minCrop = [(this.minCrop || 150), (this.minCrop || 150)];
    }

    this.minCrop[0] = Math.ceil(this.minCrop[0] * this.rWidth / this.oWidth);
    this.minCrop[1] = Math.ceil(this.minCrop[1] * this.rHeight / this.oHeight);

    canvas = this.container = $WH.ce('div');
    canvas.className = 'cropper';
    canvas = this.canvas.node;
    canvas.className = 'canvas';

    if (this.rHeight < 325 && !this.blog) {
        canvas.style.marginTop = Math.floor((325 - this.rHeight) / 2) + 'px'
    }

    canvas.style.width = this.rWidth + 'px';
    canvas.style.height = this.rHeight + 'px';
    canvas.style.backgroundImage = 'url(' + this.url + ')';
    $WH.ns(canvas);

    var
        f = (this.minCrop[0] > this.minCrop[1] ? 0 : 1),
        e = (this.oWidth > this.oHeight ? 0 : 1),
        h = [this.oWidth, this.oHeight],
        g = this.minCrop[f] / this.minCrop[1 - f],
        l = [];

    if (Math.floor(h[e] / 2) >= this.minCrop[e]) {
        l[f] = 100;
    }
    else {
        l[f] = Math.ceil(this.minCrop[f] / h[f] * 100 * 1000) / 1000;
    }

    l[1 - f] = l[f] / g;
    if ((this.type == 1 && this.typeId == 15384) && g_user.roles & (U_GROUP_MODERATOR | U_GROUP_EDITOR)) {
        this.minCrop[0] = 1;
        this.minCrop[1] = 1;
        l = [100, 100];
    }

    canvas              = this.selection.node;
    canvas.className    = 'selection';
    canvas.style.width  = l[0] + '%';
    canvas.style.height = l[1] + '%';
    canvas.style.left   = (100 - l[0]) / 2 + '%';
    canvas.style.top    = (100 - l[1]) / 2 + '%';

    div = $WH.ce('div');
    div.className = 'opac';
    div.onmousedown = Cropper.lock.bind(this, 5);
    canvas.appendChild(div);

    _ = ['hborder', 'hborder2', 'vborder', 'vborder2'];
    for (var i = 0, len = _.length; i < len; ++i) {
        div = $WH.ce('div');
        div.className = _[i];
        canvas.appendChild(div);
    }

    _ = [['w', 4], ['e', 6], ['n', 8], ['s', 2], ['nw', 7], ['ne', 9], ['sw', 1], ['se', 3]];
    for (var i = 0, len = _.length; i < len; ++i) {
        div = $WH.ce('div');
        div.className = 'grab' + _[i][0];
        div.onmousedown = Cropper.lock.bind(this, _[i][1]);
        canvas.appendChild(div);
    }

    this.canvas.node.appendChild(this.selection.node);
    this.container.appendChild(this.canvas.node);
    this.parent.appendChild(this.container);

    $WH.aE(document, 'mousedown', Cropper.mouseDown.bind(this));
    $WH.aE(document, 'mouseup',   Cropper.mouseUp.bind(this));
    $WH.aE(document, 'mousemove', Cropper.mouseMove.bind(this));
}

Cropper.prototype = {
    refreshCoords: function () {
        this.selection.coords = $WH.ac(this.selection.node);

        this.selection.size = [this.selection.node.offsetWidth, this.selection.node.offsetHeight];

        this.canvas.coords = $WH.ac(this.canvas.node);
    },

    getCoords: function () {
        this.refreshCoords();

        var
            left   = this.selection.coords[0] - this.canvas.coords[0],
            top    = this.selection.coords[1] - this.canvas.coords[1],
            width  = this.selection.size[0],
            height = this.selection.size[1];

        var
            w = this.rWidth,
            h = this.rHeight;

        return [
            (left   / w).toFixed(3),
            (top    / h).toFixed(3),
            (width  / w).toFixed(3),
            (height / h).toFixed(3)
        ].join(',');
    },

    moveSelection: function (left, top, width, height) {
        this.selection.node.style.left   = left   + 'px';
        this.selection.node.style.top    = top    + 'px';
        this.selection.node.style.width  = width  + 'px';
        this.selection.node.style.height = height + 'px';
    },

    selectAll: function () {
        this.moveSelection(0, 0, this.rWidth, this.rHeight);
    }
};

Cropper.lock = function (locked) {
    this.locked = locked;

    return false;
};

Cropper.mouseDown = function (ev) {
    ev = $WH.$E(ev);

    this.drag = 1;
    this.anchorX = ev.clientX;
    this.anchorY = ev.clientY;
    this.refreshCoords();
};

Cropper.mouseUp = function (ev) {
    ev = $WH.$E(ev);

    this.drag = this.locked = 0;
};

Cropper.mouseMove = function (ev) {
    if (this.drag && this.locked) {
        ev = $WH.$E(ev);

        var
            left   = this.selection.coords[0] - this.canvas.coords[0],
            top    = this.selection.coords[1] - this.canvas.coords[1],
            width  = left + this.selection.size[0],
            height = top + this.selection.size[1];

        var
            x = (ev.clientX - this.anchorX),
            y = (ev.clientY - this.anchorY);

        var locks = null;

        if (this.locked == 5) {
            left   += x;
            width  += x;
            top    += y;
            height += y;
        }
        else {
            if (this.locked == 1 || this.locked == 4 || this.locked == 7) {
                left += Math.max(x, -left);
                left  = Math.min(left, this.rWidth);

                if (this.locked == 4) {
                    locks = 'x';
                }
                else {
                    locks = 'xy';
                }

                if (Math.abs(left - width) < this.minCrop[0]) {
                    if (left > width) {
                        left = width + this.minCrop[0];
                    }
                    else {
                        left = width - this.minCrop[0];
                    }
                }
            }
            else if (this.locked == 3 || this.locked == 6 || this.locked == 9) {
                width += Math.min(x, this.rWidth - width);
                width  = Math.max(width, 0);

                if (this.locked == 6) {
                    locks = 'x';
                }
                else {
                    locks = 'xy';
                }

                if (Math.abs(left - width) < this.minCrop[0]) {
                    if (width > left) {
                        width = left + this.minCrop[0];
                    }
                    else {
                        width = left - this.minCrop[0];
                    }
                }
            }

            if (this.locked == 1 || this.locked == 2 || this.locked == 3) {
                height += Math.min(y, this.rHeight - height);
                height  = Math.max(height, 0);

                if (this.locked == 2) {
                    locks = 'y';
                }
                else {
                    locks = 'xy';
                }

                if (Math.abs(top - height) < this.minCrop[1]) {
                    if (height > top) {
                        height = top + this.minCrop[1];
                    }
                    else {
                        height = top - this.minCrop[1];
                    }
                }
            }
            else if (this.locked == 7 || this.locked == 8 || this.locked == 9) {
                top += Math.max(y, -top);
                top  = Math.min(top, this.rHeight);

                if (this.locked == 8) {
                    locks = 'y';
                }
                else {
                    locks = 'xy';
                }

                if (Math.abs(top - height) < this.minCrop[1]) {
                    if (top > height) {
                        top = height + this.minCrop[1];
                    }
                    else {
                        top = height - this.minCrop[1];
                    }
                }
            }
        }

        if (left > width) {
            var _ = left;
            left = width;
            width = _;
        }

        if (top > height) {
            var _ = top;
            top = height;
            height = _;
        }

        if (this.constraint) {
            var
                absX = Math.abs(x),
                absY = Math.abs(y);

            if (locks == 'x' || (locks == 'xy' && absX > absY)) {
                var b = width - left;
                var k = this.constraint[1] / this.constraint[0];
                var p = b * k;

                height = top + p;
            }
            else if (locks == 'y' || (locks == 'xy' && absX < absY)) {
                var p = height - top;
                var k = this.constraint[0] / this.constraint[1];
                var b = p * k;

                width = left + b;
            }
        }

        if (left < 0) {
            width -= left;
            left = 0;
        }

        if (top < 0) {
            height -= top;
            top = 0;
        }

        if (width > this.rWidth) {
            left -= (width - this.rWidth);
            width = this.rWidth;
        }

        if (height > this.rHeight) {
            top -= (height - this.rHeight);
            height = this.rHeight;
        }

        this.moveSelection(left, top, width - left, height - top);
    }
};