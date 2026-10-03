// New local passwords: Unicode code points and UTF-8 bytes, without trimming.
function g_isNewPasswordValid(password) {
    try {
        return Array.from(password).length >= 15 && encodeURIComponent(password).replace(/%[0-9A-F]{2}|[^%]/g, 'x').length <= 72 && !/[\x00-\x1F\x7F]/.test(password);
    } catch (e) {
        return false;
    }
}
