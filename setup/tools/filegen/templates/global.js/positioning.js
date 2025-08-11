/*
Global utility functions related to positioning
*/

function g_getViewport()
{
	var win = $(window);

	return new Rectangle(win.scrollLeft(), win.scrollTop(), win.width(), win.height());
}
