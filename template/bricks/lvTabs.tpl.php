<?php
$relTabs  = !empty($relTabs);
$tabVar   = $relTabs ? 'tabsRelated' : 'myTabs';
$isTabbed = !empty($this->forceTabs) || $relTabs || count($this->lvTabs) > 1;

if ($isTabbed):
?>
            <div class="clear"></div>
            <div id="tabs-generic"></div>
<?php endif; ?>
            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
<?php
if (!empty($this->gemScores)):                              // inherited from items.tpl.php
    echo "                var fi_gemScores = ".json_encode($this->gemScores, JSON_NUMERIC_CHECK).";\n";
endif;

if ($isTabbed):
    echo "                var ".$tabVar." = new Tabs({parent: \$WH.ge('tabs-generic')});\n";
endif;

foreach ($this->lvTabs as $lv):
    if (!empty($lv['data']) || (count($this->lvTabs) == 1 && !$relTabs)):
        if ($isTabbed):
            $lv['params']['tabs'] = '$'.$tabVar;
        endif;

        $this->lvBrick($lv['file'], ['data' => $lv['data'], 'params' => $lv['params']]);
    endif;
endforeach;

if ($relTabs):
?>
                new Listview({template: 'comment', id: 'comments', name: LANG.tab_comments, tabs: <?php echo $tabVar; ?>, parent: 'lv-generic', data: lv_comments});
                new Listview({template: 'screenshot', id: 'screenshots', name: LANG.tab_screenshots, tabs: <?php echo $tabVar; ?>, parent: 'lv-generic', data: lv_screenshots});
                if (lv_videos.length || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)))
                    new Listview({template: 'video', id: 'videos', name: LANG.tab_videos, tabs: <?php echo $tabVar; ?>, parent: 'lv-generic', data: lv_videos});
<?php
endif;

if ($isTabbed):
    echo "                ".$tabVar.".flush();\n";
endif;
?>
            //]]></script>

