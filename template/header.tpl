<!DOCTYPE html>
<html>
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
            <h1>{$title|escape:"html"}</h1>
        </div>
    </div>
    <div id="wrapper" class="nosidebar">
        <div id="toptabs">
            <div id="toptabs-inner">
                <div id="toptabs-right">
{include file="bricks/headerMenu.tpl"}
                </div>
                <div id="toptabs-generic"></div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="topbar" id="topbar">
            <div class="topbar-search"><form action="."><a href="javascript:;"></a><input name="search" size="35" value="" id="livesearch-generic" /></form></div>
            <div class="topbar-browse" id="topbar-browse"></div>
            <div class="topbar-buttons" id="topbar-buttons"></div>
        </div>

        {strip}<script type="text/javascript">
            g_initHeader({$tab});
            LiveSearch.attach($WH.ge('livesearch-generic'));
{foreach from=$jsGlobals item="glob"}
    {include file="globals/`$glob[0]`.tpl" data=$glob[1] extra=$glob[2]}
{/foreach}
        </script>{/strip}
