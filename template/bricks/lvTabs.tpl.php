<?php
$relTabs  = !empty($relTabs);
$tabVar   = $relTabs || !empty($this->user) ? 'tabsRelated' : 'myTabs';
$isTabbed = !empty($this->forceTabs) || $relTabs || count($this->lvTabs) > 1;

if ($isTabbed):
?>
            <div class="clear"></div>
            <div id="tabs-generic"></div>
<?php endif; ?>
            <div id="lv-generic" class="listview"><?php
foreach ($this->lvTabs as $lv):
    if ($lv['file']):
        continue;
    endif;

    echo '<div class="text tabbed-contents" id="tab-'.$lv['params']['id'].'" style="display:none;">'.$lv['data'].'</div>';
endforeach;
            ?></div>
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

        if ($lv['file']):
            $this->lvBrick($lv['file'], ['data' => $lv['data'], 'params' => $lv['params']]);
        elseif ($isTabbed):
            $n = $lv['params']['name'][0] == '$' ? substr($lv['params']['name'], 1) : "'".$lv['params']['name']."'";
            echo $tabVar.".add(".$n.", { id: '".$lv['params']['id']."' });\n";
        endif;
    endif;
endforeach;

if (!empty($this->user)):
    if (!empty($this->user['characterData'])):
        echo '                us_addCharactersTab('.Util::toJSON($this->user['characterData']).");\n";
    endif;
    if (!empty($this->user['profileData'])):
        echo '                us_addProfilesTab('.Util::toJSON($this->user['profileData']).");\n";
    endif;
elseif ($relTabs):
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

