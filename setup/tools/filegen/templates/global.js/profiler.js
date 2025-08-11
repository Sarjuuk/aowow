/*
Global Profiler-related functions
*/

function g_cleanCharacterName(name) {
    return (name.match && name.match(/^[A-Z]/) ? name.charAt(0).toLowerCase() + name.substr(1) : name);
}

function g_getProfileUrl(profile) {
    if (profile.region) // Armory character
        return '?profile=' + profile.region + '.' + profile.realm + '.' + g_cleanCharacterName(profile.name) + (profile.renameItr ? '-' + profile.renameItr : '');
     // return '?profile=' + profile.region + '.' + profile.realm + '.' + g_cleanCharacterName(profile.name);  // aowow custom
    else // Custom profile
        return '?profile=' + profile.id;
}

function g_getProfileRealmUrl(profile) {
    return '?profiles=' + profile.region + '.' + profile.realm;
}
