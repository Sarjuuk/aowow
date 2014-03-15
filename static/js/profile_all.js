var mn_profiles = [["us","US & Oceanic",null,[["pure-pwnage","Pure Pwnage",null,[["trinity","Trinity"]]]]],["eu","Europe",null,[["pure-pwnage","Pure Pwnage",null,[["dafuque","da'Fuqúe"]]]]]];

var mn_guilds = $.extend(true,[],mn_profiles);
var mn_arenateams = $.extend(true,[],mn_profiles);
Menu.fixUrls(mn_profiles,"?profiles=",{useSimpleIdsAfter:1});
Menu.fixUrls(mn_guilds,"?guilds=",{useSimpleIdsAfter:1});
Menu.fixUrls(mn_arenateams,"?arena-teams=",{useSimpleIdsAfter:1});