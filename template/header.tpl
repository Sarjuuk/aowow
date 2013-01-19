<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3c.org/TR/1999/REC-html401-19991224/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
{include file='head.tpl'}
</head>

<body>
<div id="layers"></div>
<!--[if lte IE 6]><table id="ie6layout"><tr><th class="ie6layout-th"></th><td id="ie6layout-td"><div id="ie6layout-div"></div><![endif]-->
<div id="layout">
    <div id="header">
        <div id="header-logo">
            <a href="."></a>
            <h1>{$page.title|escape:"html"}</h1>
        </div>
    </div>
    <div id="wrapper" class="nosidebar">
        <div id="toptabs">
            <div id="toptabs-inner">
                <div id="toptabs-right">
                    {if $user.id}<a id="toptabs-menu-profiles">{$lang.profiles}</a>|<a id="toptabs-menu-user">{$user.name}</a></a>{else}<a href="?account=signin">{$lang.signIn}</a>{/if}
                    |<a href="javascript:;" id="toptabs-menu-language">{$lang.language} <small>&#9660;</small></a>
                    <script type="text/javascript">g_initHeaderMenus()</script>
                </div>
                <div id="toptabs-generic"></div>
                <div class="clear"></div>
            </div>
        </div>
        <div id="topbar-right"><div><form action="."><a href="javascript:;"></a><input name="search" size="35" value="" id="livesearch-generic" /></form></div></div>
        <div id="topbar"><span id="topbar-generic" class="menu-buttons"></span><div class="clear"></div></div>
        {strip}<script type="text/javascript">
            g_initHeader({$page.tab});
            LiveSearch.attach(ge('livesearch-generic'));
            {if isset($data.gAchievements)} { include file='bricks/allachievements_table.tpl'   data=$data.gAchievements }{/if}
            {if isset($data.gClasses)}      { include file='bricks/allclasses_table.tpl'        data=$data.gClasses      }{/if}
            {if isset($data.gCurrencies)}   { include file='bricks/allcurrencies_table.tpl'     data=$data.gCurrencies   }{/if}
            {if isset($data.gItems)}        { include file='bricks/allitems_table.tpl'          data=$data.gItems        }{/if}
            {if isset($data.gRaces)}        { include file='bricks/allraces_table.tpl'          data=$data.gRaces        }{/if}
            {if isset($data.gSpells)}       { include file='bricks/allspells_table.tpl'         data=$data.gSpells       }{/if}
            {if isset($data.gTitles)}       { include file='bricks/alltitles_table.tpl'         data=$data.gTitles       }{/if}
            {* TODO: Factions, Quests, NPCs, Objects, g_gatheredzones(?) *}
        </script>{/strip}
