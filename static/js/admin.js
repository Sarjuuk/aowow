function ar_IsValidUrl(a) {
    return a.match(/^[a-z0-9=_&\.\/\-]{2,64}$/i) != null;
}

function ar_ValidateUrl(a) {
    if (ar_IsValidUrl(a)) {
        return null;
    }

    if (a.length < 2) {
        return "URL must be at least 2 characters long.";
    }
    else if (a.length > 64) {
        return "URL must be at most 64 characters long.";
    }
    else {
        return "You used invalid characters in your URL.\n\nYou can only use the following:\n a to z\n 0 to 9\n = _ & . / -"
    }
};
