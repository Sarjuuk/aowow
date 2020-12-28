            <script type="text/javascript">//<![CDATA[
<?php
if (!empty($this->hasComContent)):
    echo "                var lv_comments = ".Util::toJSON($this->community['co']).";\n";
    echo "                var lv_screenshots = ".Util::toJSON($this->community['sc']).";\n";
    echo "                var lv_videos = ".Util::toJSON($this->community['vi']).";\n";
endif;

if (!empty($this->gPageInfo)):
    echo "                var g_pageInfo = ".Util::toJSON($this->gPageInfo).";\n";

    // only used by item.php
    if (User::$id > 0 && isset($this->redButtons[BUTTON_EQUIP]) && $this->redButtons[BUTTON_EQUIP]):
        echo "                DomContentLoaded.addEvent(function() { pr_addEquipButton('equip-pinned-button', ".$this->typeId."); });\n";
    endif;
endif;

if (!empty($this->pageTemplate)):
    if (User::$localeId && $this->pageTemplate['pageName'] != 'home'):
        echo "                Locale.set(".User::$localeId.");\n";
    endif;

    echo "                PageTemplate.set(".Util::toJSON($this->pageTemplate).");\n";
    echo "                PageTemplate.init();\n";
endif;

if (!empty($fi)):
    echo "                Menu.modifyUrl(Menu.findItem(mn_database, [".$fi['menuItem']."]), { filter: '+=".Util::jsEscape($fi['query'])."' }, { onAppendCollision: fi_mergeFilterParams, onAppendEmpty: fi_setFilterParams, menuUrl: Menu.getItemUrl(Menu.findItem(mn_database, [".$fi['menuItem']."])) });\n";
        // $(document).ready(function(){ Menu.modifyUrl(Menu.findItem(mn_path, [1,5]), { filter: 'na=Malgayne'}, { onAppendCollision: fi_mergeFilterParams }) });
endif;
?>
            //]]></script>

