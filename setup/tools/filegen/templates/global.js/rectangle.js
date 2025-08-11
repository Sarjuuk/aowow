function Rectangle(left, top, width, height)
{
    this.l = left;
    this.t = top;
    this.r = left + width;
    this.b = top + height;
}

Rectangle.prototype = {

    intersectWith: function(rect)
    {
        var result = !(
            this.l >= rect.r || rect.l >= this.r ||
            this.t >= rect.b || rect.t >= this.b
        );

        return result;
    },

    contains: function(rect)
    {
        var result = (
            this.l <= rect.l && this.t <= rect.t &&
            this.r >= rect.r && this.b >= rect.b
        );
        return result;
    },

    containedIn: function(rect)
    {
        return rect.contains(this);
    }

};
