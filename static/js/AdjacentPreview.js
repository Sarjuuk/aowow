$WH.AdjacentPreview = new (function () {
    const idx = "edit.adjacentPreview";
    this.init = function () {
        let cb = $WH.qs(".adjacent-preview-controls");
        if (!cb) {
            console.error("Can't set up adjacent guide preview support without an option to toggle it.");
            return;
        }
        cb.style.display = "block";
        if (!$WH.g_isVisible(cb)) {
            return;
        }
        let mode = $WH.localStorage.get(idx);
        if (mode === null) {
            mode = $WH.localStorage.get("guide.edit.adjacentPreview");
            if (mode !== null) {
                $WH.localStorage.set(idx, mode);
                $WH.localStorage.remove("guide.edit.adjacentPreview");
            }
        }
        let i = $WH.qs(".adjacent-preview-checkbox");
            mode = JSON.parse(mode);                        // read from store as literal
        if (mode) {
            i.checked = "checked";
        }
        $WH.aE(i, "change", () => {
            let a = !!i.checked;
            $WH.localStorage.set(idx, a);
            togglePreview(a);
        });
        togglePreview(mode);
    };
    let togglePreview = function (en) {
        if (en) {
            window.scrollTo(0, 0);
            document.body.dataset.adjacentPreview = "enabled";
            $WH.qs(".adjacent-preview").style.top = $WH.g_getHeaderHeight() + "px";

            $WH.ge('layout').style.maxWidth = 'initial';
            $WH.ge('layout-inner').style.padding = '0px';
        }
        else {
            document.body.dataset.adjacentPreview = "disabled";

            $WH.ge('layout').style.maxWidth = '';
            $WH.ge('layout-inner').style.padding = '';
        }
    };
})();
