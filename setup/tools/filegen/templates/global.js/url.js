$WH.Url = new function()
{
    const _self = this;

    this.getCanonical = function()
    {
        var url = $WH.qs('link[rel="canonical"]');
        if (!url)
            return location.protocol + '//' + location.host + location.pathname + (location.search || '');

        return url.href;
    };

    this.getCanonicalPath = function()
    {
        var url = $WH.qs('link[rel="canonical"]');
        if (!url)
            return location.pathname + (location.search || '');

        return _self.getPathFromUrl(url.href, true);
    };

    this.getPathFromUrl = function(url, keepParams)
    {
        if (keepParams === true)
            return url.replace(/^(?:(?:https?:)?\/\/[^/]+)?\/?([^#]+).*$/, '/$1');

        return url.replace(/^(?:(?:https?:)?\/\/[^/]+)?\/?([^?&#]+).*$/, '/$1');
    };
}
