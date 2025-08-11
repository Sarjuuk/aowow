var ProgressBar = function(opt)
{
    this.opts = {
        text: '',
        hoverText: '',
        color: 'rep6',
        width: 0,
        progress: 0
    };

    this.elements = {
        text: null,
        hoverText: null,
        textContainer: null,
        progress: null,
        container: null
    };

    $WH.cO(this.opts, opt);

    this.build();
};

ProgressBar.prototype.build = function()
{
    var el = $('<a/>', { 'class': 'progressbar', href: 'javascript:;' });
    if(this.opts.width > 0)
        el.css('width', this.opts.width + 'px');
    else
        el.css('width', 'auto');

    var textDiv = $('<div/>', { 'class': 'progressbar-text' });
    if(this.opts.text)
    {
        this.elements.text = $('<del/>', { text: this.opts.text });
        textDiv.append(this.elements.text);
    }
    if(this.opts.hoverText)
    {
        this.elements.hoverText = $('<ins/>', { text: this.opts.hoverText });
        textDiv.append(this.elements.hoverText);
    }
    el.append(textDiv);

    var div = $('<div/>', { 'class': 'progressbar-' + this.opts.color, css: { width: this.opts.progress + '%' }, text: String.fromCharCode(160) });
    el.append(div);

    if(this.opts.text)
        textDiv.append($('<div/>', { 'class': 'progressbar-text progressbar-hidden', text: this.opts.text }));

    this.elements.container = el;
    this.elements.progress = div;
    this.elements.textContainer = textDiv;

    return el;
};

ProgressBar.prototype.setText = function(text)
{
    this.opts.text = text;

    if(this.elements.text)
        this.elements.text.text(this.opts.text);
    else
    {
        this.elements.text = $('<del/>', { text: this.opts.text });
        if(this.opts.hoverText)
            this.opts.hoverText.before(this.elements.text);
        else
            this.elements.textContainer.append(this.elements.text);
    }
};

ProgressBar.prototype.setHoverText = function(text)
{
    this.opts.hoverText = text;

    if(this.elements.hoverText)
        this.elements.hoverText.text(this.opts.hoverText);
    else
    {
        this.elements.hoverText = $('<ins/>', { text: this.opts.hoverText });
        this.elements.textContainer.append(this.elements.hoverText);
    }
};

ProgressBar.prototype.setProgress = function(percent)
{
    this.opts.progress = percent;

    this.elements.progress.css('width', this.opts.progress + '%');
};

ProgressBar.prototype.setWidth = function(width)
{
    this.opts.width = width;

    if(this.opts.width > 0)
        this.elements.container.css('width', this.opts.width + 'px');
    else
        this.elements.container.css('width', 'auto');
};

ProgressBar.prototype.getText = function()
{
    return this.opts.text;
};

ProgressBar.prototype.getHoverText = function()
{
    return this.opts.hoverText;
};

ProgressBar.prototype.getWidth = function()
{
    return this.opts.width;
};

ProgressBar.prototype.getContainer = function()
{
    return this.elements.container;
};
