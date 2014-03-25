{include file='header.tpl'}

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

            <script type="text/javascript">
                g_initPath({$path});
                var g_dataKey = '{$dataKey}';
                var temp_path = {$path}; {*kill with pageTemplate*}
{*
PageTemplate.set({pageName: 'profile', activeTab: 1, breadcrumb: [1,5,0,'eu']});
PageTemplate.init();

old:
var g_serverTime = new Date('2010/04/11 01:50:39');
var g_pageName = 'profile';
var g_pageValue = '';

*}
            </script>

            <div id="profilah-generic"></div>
            <script type="text/javascript">//<![CDATA[
                var profilah = new Profiler();
                profilah.initialize('profilah-generic', {ldelim} id: {$profileId} {rdelim});
                pr_setRegionRealm($WH.gE($WH.ge('topbar'), 'form')[0], '', '');
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}