$(document).ready(function() {
    $WH.qs('#consent-overlay #accept-btn').onclick = function () {
        $WH.sc('consent', 1000, 1);
        $WH.ge('consent-overlay').style.display = 'none';
    };
    $WH.qs('#consent-overlay #reject-all').onclick = function () {
        $WH.sc('consent', 1000, 0);
        $WH.ge('consent-overlay').style.display = 'none';
    };
});
