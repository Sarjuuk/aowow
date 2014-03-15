<div class="text">
<h2>{$lang.contribute}</h2>
</div>
<div id="tabs-contribute-generic" style="width: 50%"></div>
<div class="text" style="margin-right: 310px">
    <div class="tabbed-contents" style="clear: none">
{include file="bricks/contrib_`$user.locale`.tpl"}
    </div>
</div>
<script type="text/javascript">
    var tabsContribute = new Tabs({ldelim}parent: $WH.ge('tabs-contribute-generic'){rdelim});
    tabsContribute.add(LANG.tab_addyourcomment, {ldelim}id: 'add-your-comment'{rdelim});
    tabsContribute.add(LANG.tab_submitascreenshot, {ldelim}id: 'submit-a-screenshot'{rdelim});
    if(g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO))
        tabsContribute.add(LANG.tab_suggestavideo, {ldelim}id: 'suggest-a-video'{rdelim});
    tabsContribute.flush();
</script>
<div class="clear"></div>
