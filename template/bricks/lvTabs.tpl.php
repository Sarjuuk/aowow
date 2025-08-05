<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>

<?php
if (($this->lvTabs && count($this->lvTabs)) || $this->charactersLvData || $this->profilesLvData || $this->contribute):
    if ($this->lvTabs?->isTabbed()):
?>
            <div class="clear"></div>
            <div id="tabs-generic"></div>
<?php endif; ?>
            <div id="lv-generic" class="listview">
<?php
    foreach ($this->lvTabs?->getDataContainer() ?? [] as $container):
        echo '                '.$container."\n";
    endforeach;
?>
            </div>
            <script type="text/javascript">//<![CDATA[
<?php
    // seems like WH keeps their modules separated, as fi_gemScores should be with the other fi_ items but are here instead and originally the dbtype globals used by the listviews were also here)
    // May 2025: WH no longer calculates gems into item scores. Dude .. why?
    if ($this->gemScores)                                   // set by ItemsBaseResponse
        echo '                var fi_gemScores = '.$this->json($this->gemScores).";\n";

    // g_items, g_spells, etc required by the listviews used to be here

    echo $this->lvTabs;

    if ($this->charactersLvData):
        echo '                us_addCharactersTab('.$this->json('charactersLvData').");\n";
    endif;
    if ($this->profilesLvData):
        echo '                us_addProfilesTab('.$this->json('profilesLvData').");\n";
    endif;
    if ($this->contribute & CONTRIBUTE_CO):
        echo "                new Listview({template: 'comment', id: 'comments', name: LANG.tab_comments".($this->lvTabs ? ", tabs: ".$this->lvTabs->__tabVar : '').", parent: 'lv-generic', data: lv_comments});\n";
    endif;
    if ($this->contribute & CONTRIBUTE_SS):
        echo "                new Listview({template: 'screenshot', id: 'screenshots', name: LANG.tab_screenshots".($this->lvTabs ? ", tabs: ".$this->lvTabs->__tabVar : '').", parent: 'lv-generic', data: lv_screenshots});\n";
    endif;
    if ($this->contribute & CONTRIBUTE_VI):
        echo "                if (lv_videos.length || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)))\n";
        echo "                    new Listview({template: 'video', id: 'videos', name: LANG.tab_videos".($this->lvTabs ? ", tabs: ".$this->lvTabs->__tabVar : '').", parent: 'lv-generic', data: lv_videos});\n";
    endif;

    if ($flushTabs = $this->lvTabs?->getFlush()):
        echo "                ".$flushTabs."\n";
    endif;
?>
            //]]></script>
<?php
endif;
?>
