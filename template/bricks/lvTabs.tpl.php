<?php
$relTabs  = !empty($relTabs);
$tabVar   = $relTabs || !empty($this->user) ? 'tabsRelated' : 'myTabs';
$isTabbed = !empty($this->forceTabs) || $relTabs || count($this->lvTabs) > 1;

// lvTab: [file, data, extraInclude]

if ($isTabbed):
?>
            <div class="clear"></div>
            <div id="tabs-generic"></div>
<?php endif; ?>
            <div id="lv-generic" class="listview"><?php
foreach ($this->lvTabs as $lv):
    if ($lv[0]):
        continue;
    endif;

    echo '<div class="text tabbed-contents" id="tab-'.$lv[1]['id'].'" style="display:none;">'.$lv[1]['data'].'</div>';
endforeach;
            ?></div>
            <script type="text/javascript">//<![CDATA[
<?php
if (!empty($this->gemScores)):                              // inherited from items.tpl.php
    echo "                var fi_gemScores = ".Util::toJSON($this->gemScores).";\n";
endif;

if ($isTabbed):
    echo "                var ".$tabVar." = new Tabs({parent: \$WH.ge('tabs-generic')".(isset($this->type) ? ", trackable: '".ucfirst(Util::$typeStrings[$this->type]."'") : null)."});\n";
endif;

foreach ($this->lvTabs as $lv):
    if (!empty($lv[1]['data']) || (count($this->lvTabs) == 1 && !$relTabs)):
        if ($isTabbed):
            $lv[1]['tabs'] = '$'.$tabVar;
        endif;

        if ($lv[0]):
            // extra functions on top of lv
            if (isset($lv[2])):
                $this->lvBrick($lv[2]);
            endif;

            if (isset($this->lvTemplates[$lv[0]])):
                echo "new Listview(".Util::toJSON(array_merge($this->lvTemplates[$lv[0]], $lv[1])).");\n";
            else:
                // does not appear as announcement, those have already been handled at this point
                trigger_error('requested undefined listview: '.$lv[0], E_USER_ERROR);
            endif;
        elseif ($isTabbed):
            $n = $lv[1]['name'][0] == '$' ? substr($lv[1]['name'], 1) : "'".$lv[1]['name']."'";
            echo $tabVar.".add(".$n.", { id: '".$lv[1]['id']."' });\n";
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
                new Listview({template: 'comment', id: 'comments', name: LANG.tab_comments, tabs: <?=$tabVar; ?>, parent: 'lv-generic', data: lv_comments});
                new Listview({template: 'screenshot', id: 'screenshots', name: LANG.tab_screenshots, tabs: <?=$tabVar; ?>, parent: 'lv-generic', data: lv_screenshots});
                if (lv_videos.length || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)))
                    new Listview({template: 'video', id: 'videos', name: LANG.tab_videos, tabs: <?=$tabVar; ?>, parent: 'lv-generic', data: lv_videos});
<?php
endif;

if ($isTabbed):
    echo "                ".$tabVar.".flush();\n";
endif;
?>
            //]]></script>

