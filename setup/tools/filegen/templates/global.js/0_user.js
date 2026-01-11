// Needed for IE because it's dumb

'abbr article aside audio canvas details figcaption figure footer header hgroup mark menu meter nav output progress section summary time video'.replace(/\w+/g,function(n){document.createElement(n)})


// aowow - extend Date for holidaycal
Date.prototype.getLocaleDay = function() {
    const dayNo = this.getDay();
    switch (Locale.getId())
    {
        case LOCALE_FRFR:
        case LOCALE_DEDE:
        case LOCALE_ESES:
        case LOCALE_RURU:
            return !dayNo ? 6 : dayNo - 1;
        default:
            return dayNo;
    }
};

/*
User-related functions
TODO: Move global variables/functions into User class
*/

// IMPORTANT: If you update/change the permission groups below make sure to also update them in User.inc.php!

/*********/
/* ROLES */
/*********/

var U_GROUP_TESTER     = 0x1;
var U_GROUP_ADMIN      = 0x2;
var U_GROUP_EDITOR     = 0x4;
var U_GROUP_MOD        = 0x8;
var U_GROUP_BUREAU     = 0x10;
var U_GROUP_DEV        = 0x20;
var U_GROUP_VIP        = 0x40;
var U_GROUP_BLOGGER    = 0x80;
var U_GROUP_PREMIUM    = 0x100;
var U_GROUP_LOCALIZER  = 0x200;
var U_GROUP_SALESAGENT = 0x400;
var U_GROUP_SCREENSHOT = 0x800;
var U_GROUP_VIDEO      = 0x1000;
var U_GROUP_APIONLY    = 0x2000;
var U_GROUP_PENDING    = 0x4000;


/******************/
/* ROLE SHORTCUTS */
/******************/

var U_GROUP_STAFF               = U_GROUP_ADMIN   | U_GROUP_EDITOR    | U_GROUP_MOD | U_GROUP_BUREAU | U_GROUP_DEV | U_GROUP_BLOGGER | U_GROUP_LOCALIZER | U_GROUP_SALESAGENT;
var U_GROUP_EMPLOYEE            = U_GROUP_ADMIN   | U_GROUP_BUREAU    | U_GROUP_DEV;
var U_GROUP_GREEN_TEXT          = U_GROUP_MOD     | U_GROUP_BUREAU    | U_GROUP_DEV;
var U_GROUP_PREMIUMISH          = U_GROUP_PREMIUM | U_GROUP_EDITOR;
var U_GROUP_MODERATOR           = U_GROUP_ADMIN   | U_GROUP_MOD       | U_GROUP_BUREAU;
var U_GROUP_COMMENTS_MODERATOR  = U_GROUP_BUREAU  | U_GROUP_MODERATOR | U_GROUP_LOCALIZER;
var U_GROUP_PREMIUM_PERMISSIONS = U_GROUP_PREMIUM | U_GROUP_STAFF     | U_GROUP_VIP;

var g_users = {};
var g_favorites = [];
var g_customColors = {};

function g_isUsernameValid(username) {
    return (username.match(/[^a-z0-9]/i) == null && username.length >= 4 && username.length <= 16);
}

var User = new function() {
    var self = this;

    /**********/
    /* PUBLIC */
    /**********/

	self.hasPermissions = function(roles)
	{
		if(!roles)
			return true;

		return !!(g_user.roles & roles);
	}

    /**********/
    /* PRIVATE */
    /**********/

};
