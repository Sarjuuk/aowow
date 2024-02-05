<?php
if (!empty($this->lvTabs) || !empty($this->user['characterData']) || !empty($this->user['profileData']) || $this->contribute):
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
    foreach ($this->lvTabs as [$tplName, $tabData]):
        if ($tplName):
            continue;
        endif;

        echo '<div class="text tabbed-contents" id="tab-'.$tabData['id'].'" style="display:none;">'.$tabData['data'].'</div>';
    endforeach;
            ?></div>
            <script type="text/javascript">//<![CDATA[
<?php
    if (!empty($this->gemScores)):                              // inherited from items.tpl.php
        echo "                var fi_gemScores = ".Util::toJSON($this->gemScores).";\n";
    endif;

    if ($isTabbed):
        echo "                var ".$tabVar." = new Tabs({parent: \$WH.ge('tabs-generic')".(isset($this->type) ? ", trackable: '".ucfirst(Type::getFileString($this->type)."'") : null)."});\n";
    endif;

    foreach ($this->lvTabs as [$tplName, $tabData, $include]):
        if (empty($tabData['data']) && $relTabs && count($this->lvTabs) != 1):
            continue;
        endif;

        if ($isTabbed):
            $tabData['tabs'] = '$'.$tabVar;
        endif;

        if ($tplName):
            // extra functions on top of lv
            if (isset($include)):
                $this->lvBrick($include);
            endif;

            if (isset($this->lvTemplates[$tplName])):
                echo "new Listview(".Util::toJSON(array_merge($this->lvTemplates[$tplName], $tabData)).");\n";
            else:
                // does not appear as announcement, those have already been handled at this point
                trigger_error('requested undefined listview: '.$tplName, E_USER_ERROR);
            endif;
        elseif ($isTabbed):
            $n = $tabData['name'][0] == '$' ? substr($tabData['name'], 1) : "'".$tabData['name']."'";
            echo $tabVar.".add(".$n.", { id: '".$tabData['id']."' });\n";
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
        if ($this->contribute & CONTRIBUTE_CO):
            echo "                new Listview({template: 'comment', id: 'comments', name: LANG.tab_comments, tabs: ".$tabVar.", parent: 'lv-generic', data: lv_comments});\n";
        endif;
        if ($this->contribute & CONTRIBUTE_SS):
            echo "                new Listview({template: 'screenshot', id: 'screenshots', name: LANG.tab_screenshots, tabs: ".$tabVar.", parent: 'lv-generic', data: lv_screenshots});\n";
        endif;
        if ($this->contribute & CONTRIBUTE_VI):
            echo "                if (lv_videos.length || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)))\n";
            echo "                    new Listview({template: 'video', id: 'videos', name: LANG.tab_videos, tabs: ".$tabVar.", parent: 'lv-generic', data: lv_videos});\n";
        endif;
    endif;

    if ($isTabbed):
        echo "                ".$tabVar.".flush();\n";
    endif;
?>
            //]]></script>
<?php
endif;
?>
