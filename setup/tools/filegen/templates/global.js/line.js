function Line(x1, y1, x2, y2, type)
{
    var left   = Math.min(x1, x2),
        right  = Math.max(x1, x2),
        top    = Math.min(y1, y2),
        bottom = Math.max(y1, y2),

        width  = (right - left),
        height = (bottom - top),
        length = Math.sqrt(Math.pow(width, 2) + Math.pow(height, 2)),

        radian   = Math.atan2(height, width),
        sinTheta = Math.sin(radian),
        cosTheta = Math.cos(radian);

    var $line = $WH.ce('span');
    $line.className = 'line';
    $line.style.top    = top.toFixed(2) + 'px';
    $line.style.left   = left.toFixed(2) + 'px';
    $line.style.width  = width.toFixed(2) + 'px';
    $line.style.height = height.toFixed(2) + 'px';

    var v = $WH.ce('var');
    v.style.width = length.toFixed(2) + 'px';
    v.style.OTransform = 'rotate(' + radian + 'rad)';
    v.style.MozTransform = 'rotate(' + radian + 'rad)';
    v.style.webkitTransform = 'rotate(' + radian + 'rad)';
    v.style.filter = "progid:DXImageTransform.Microsoft.Matrix(sizingMethod='auto expand', M11=" + cosTheta + ', M12=' + (-1 * sinTheta) + ', M21=' + sinTheta + ', M22=' + cosTheta + ')';
    $WH.ae($line, v);

    if (!(x1 == left && y1 == top) && !(x2 == left && y2 == top))
        $line.className += ' flipped';

    if (type != null)
        $line.className += ' line-' + type;

    return $line;
}
