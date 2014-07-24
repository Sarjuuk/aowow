function Book(opt) {
    if (!opt.parent || !opt.pages || opt.pages.length == 0) {
        return;
    }

    opt.parent = $WH.ge(opt.parent);

    var
        d,
        a,
        _;

    this.nPages = opt.pages.length;

    this.parent = $WH.ge(opt.parent);
    this.parent.className += ' book';

    d = $WH.ce('div');
    d.className = 'paging';
    if (this.nPages == 1) {
        d.style.display = 'none';
    }
    $WH.ns(d);

    _ = $WH.ce('div');
    _.style.visibility = 'hidden';
    _.className = 'previous';
    a = $WH.ce('a');
    a.appendChild($WH.ct(String.fromCharCode(8249) + LANG.lvpage_previous));
    a.href = 'javascript:;';
    a.onclick = this.previous.bind(this);
    _.appendChild(a);
    d.appendChild(_);

    _ = $WH.ce('div');
    _.style.visibility = 'hidden';
    _.className = 'next';
    a = $WH.ce('a');
    a.appendChild($WH.ct(LANG.lvpage_next + String.fromCharCode(8250)));
    a.href = 'javascript:;';
    a.onclick = this.next.bind(this);
    _.appendChild(a);
    d.appendChild(_);

    _ = $WH.ce('b');
    _.appendChild($WH.ct('1'));
    d.appendChild(_);

    d.appendChild($WH.ct(LANG.lvpage_of));

    _ = $WH.ce('b');
    _.appendChild($WH.ct(this.nPages));
    d.appendChild(_);

    opt.parent.appendChild(d);

    for (var i = 0; i < this.nPages; ++i) {
        d = $WH.ce('div');
        d.className = 'page';
        d.style.display = 'none';

        d.innerHTML = opt.pages[i];

        opt.parent.appendChild(d);
    }

    this.page = 1;
    this.changePage(opt.page || 1);
}

Book.prototype = {
    changePage: function(page) {
        if (page < 1) {
            page = 1;
        }
        else if (page > this.nPages) {
            page = this.nPages;
        }

        var _ = this.parent.childNodes;
        _[this.page].style.display = 'none';
        _[page].style.display = '';
        this.page = page;

        _ = _[0].childNodes;
        _[0].style.visibility = (page == 1) ? 'hidden': 'visible';
        _[1].style.visibility = (page == this.nPages) ? 'hidden': 'visible';

        _[2].innerHTML = page;
    },

    next: function() {
        this.changePage(this.page + 1);
    },

    previous: function() {
        this.changePage(this.page - 1);
    }
};
