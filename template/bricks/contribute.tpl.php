<div class="clear"></div>
<div class="text">
<h2><?php echo Lang::main('contribute'); ?></h2>
</div>
<div id="tabs-contribute-generic" style="width: 50%"></div>
<div class="text" style="margin-right: 310px">
    <div class="tabbed-contents" style="clear: none">
<?php
    $this->localizedBrick('contrib', User::$localeId);
?>
    </div>
</div>
<script type="text/javascript">
    var tabsContribute = new Tabs({parent: $WH.ge('tabs-contribute-generic')});
    tabsContribute.add(LANG.tab_addyourcomment, {id: 'add-your-comment'});
    tabsContribute.add(LANG.tab_submitascreenshot, {id: 'submit-a-screenshot'});
    if (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO))
        tabsContribute.add(LANG.tab_suggestavideo, {id: 'suggest-a-video'});
    tabsContribute.flush();
</script>
