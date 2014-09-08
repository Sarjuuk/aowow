            <script type="text/javascript">//<![CDATA[
<?php
if (!empty($this->hasComContent)):
    echo "                var lv_comments = ".json_encode($this->community['co'], JSON_NUMERIC_CHECK).";\n";
    echo "                var lv_screenshots = ".json_encode($this->community['sc'], JSON_NUMERIC_CHECK).";\n";
    echo "                var lv_videos = ".json_encode($this->community['vi'], JSON_NUMERIC_CHECK).";\n";
endif;

if (!empty($this->gPageInfo)):
    echo "                var g_pageInfo = ".json_encode($this->gPageInfo, JSON_NUMERIC_CHECK).";\n";

    // only used by item.php
    if (User::$id > 0 && isset($this->redButtons[BUTTON_EQUIP]) && $this->redButtons[BUTTON_EQUIP]):
        echo "                DomContentLoaded.addEvent(function() { pr_addEquipButton('equip-pinned-button', ".$this->typeId."); });\n";
    endif;
endif;

if (!empty($this->pageTemplate)):
    if (User::$localeId && $this->pageTemplate['pageName'] != 'home'):
        echo "                Locale.set(".User::$localeId.");\n";
    endif;

    echo "                PageTemplate.set(".json_encode($this->pageTemplate, JSON_NUMERIC_CHECK).");\n";
    echo "                PageTemplate.init();\n";
endif;

if (!empty($fi)):
    echo "                Menu.modifyUrl(Menu.findItem(mn_database, [".$fi['menuItem']."]), { filter: '+=".$fi['query']."' }, { onAppendCollision: fi_mergeFilterParams, onAppendEmpty: fi_setFilterParams, menuUrl: Menu.getItemUrl(Menu.findItem(mn_database, [".$fi['menuItem']."])) });\n";
endif;
?>
            //]]></script>

