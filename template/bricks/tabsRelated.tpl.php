            <div id="tabs-generic"></div>
            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
                var tabsRelated = new Tabs({parent: $WH.ge('tabs-generic')});
<?php
foreach ($this->lvData as $lv):
    if (!empty($lv['data'])):
        $this->lvBrick($lv['file'], ['data' => $lv['data'], 'params' => $lv['params']]);
    endif;
endforeach;
?>
                new Listview({template: 'comment', id: 'comments', name: LANG.tab_comments, tabs: tabsRelated, parent: 'lv-generic', data: lv_comments});
                new Listview({template: 'screenshot', id: 'screenshots', name: LANG.tab_screenshots, tabs: tabsRelated, parent: 'lv-generic', data: lv_screenshots});
                if (lv_videos.length || (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO)))
                    new Listview({template: 'video', id: 'videos', name: LANG.tab_videos, tabs: tabsRelated, parent: 'lv-generic', data: lv_videos});
                tabsRelated.flush();
            //]]></script>

