function Ajax(url, opt)
{
    if (!url)
        return;

    var _;

    try { _ = new XMLHttpRequest() } catch (e)
    {
        try { _ = new ActiveXObject("Msxml2.XMLHTTP") } catch (e)
        {
            try { _ = new ActiveXObject("Microsoft.XMLHTTP") } catch (e)
            {
                if (window.createRequest)
                    _ = window.createRequest();
                else
                {
                    alert(LANG.message_ajaxnotsupported);
                    return;
                }
            }
        }
    }

    this.request = _;

    $WH.cO(this, opt);
    this.method = this.method || (this.params && 'POST') || 'GET';

    _.open(this.method, url, this.async == null ? true : this.async);
    _.onreadystatechange = Ajax.onReadyStateChange.bind(this);

    if (this.method.toUpperCase() == 'POST')
        _.setRequestHeader('Content-Type', (this.contentType || 'application/x-www-form-urlencoded') + '; charset=' + (this.encoding || 'UTF-8'));

    _.send(this.params);
}

Ajax.onReadyStateChange = function()
{
    if (this.request.readyState == 4)
    {
        if (this.request.status == 0 || (this.request.status >= 200 && this.request.status < 300))
            this.onSuccess != null && this.onSuccess(this.request, this);
        else
            this.onFailure != null && this.onFailure(this.request, this);

        if (this.onComplete != null)
            this.onComplete(this.request, this);
    }
};
