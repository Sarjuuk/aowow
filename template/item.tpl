{include file='header.tpl'}

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

            <script type="text/javascript">//<![CDATA[
{include file='bricks/community.tpl'}
                var g_pageInfo = {ldelim}type: {$page.type}, typeId: {$page.typeId}, name: '$lvData.name|escape:"quotes"}'{rdelim};
                g_initPath({$page.path});
            //]]></script>

{include file='bricks/infobox.tpl' info=$lvData.infobox}

            <div class="text">
                <a href="javascript:;" id="open-links-button" class="button-red" onclick="this.blur(); Links.show({ldelim} type: {$page.type}, typeId: {$page.typeId}, linkColor: 'ff{$lvData.page.color}', linkId: 'item:{$page.typeId}:0:0:0:0:0:0:0:0', linkName: '{$lvData.page.name}' {rdelim});"><em><b><i>{$lang.links}</i></b><span>{$lang.links}</span></em></a>
                <a href="javascript:;" id="view3D-button" class="button-red{if $lvData.page.displayId}" onclick="this.blur(); ModelViewer.show({ldelim} type: {$page.type}, typeId: {$page.typeId}, displayId: {$lvData.page.displayId}, slot: {$lvData.page.slot} {rdelim}){else} button-red-disabled{/if}"><em><b><i>{$lang.view3D}</i></b><span>{$lang.view3D}</span></em></a>
                <a href="javascript:;" class="button-red{if $lvData.buttons}" onclick="this.blur(); su_addToSaved('{$page.typeId}', 1){else} button-red-disabled{/if}"><em><b><i>{$lang.compare}</i></b><span>{$lang.compare}</span></em></a>
                <a href="javascript:;" class="button-red{if $lvData.buttons}" onclick="this.blur(); pr_showClassPresetMenu(this, {$page.typeId}, {$lvData.page.class}, {$lvData.page.slot}, event);{else} button-red-disabled{/if}"><em><b><i>{$lang.findUpgrades}</i></b><span>{$lang.findUpgrades}</span></em></a>
                <a href="{$wowhead}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
{* <div id="sdlkgnfdlkgndfg4"></div>  what the heck.. this div has neither data nor style or script associated with *}
                <h1>{$lvData.page.name}</h1>

                <div id="icon{$page.typeId}-generic" style="float: left"></div>
                <div id="tooltip{$page.typeId}-generic" class="tooltip" style="float: left; padding-top: 1px">
                <table><tr><td>{$lvData.tooltip}</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>
                </div>

                <div style="clear: left"></div>

                <script type="text/javascript">
                    $WH.ge('icon{$page.typeId}-generic').appendChild(Icon.create('{$lvData.page.icon}', 2, 0, 0, {$lvData.page.stack}));
                    $WH.Tooltip.fix($WH.ge('tooltip{$page.typeId}-generic'), 1, 1);
                </script>

                {if !empty($lvData.page.pagetext)}
                    <h3>Content</h3>
                    <div id="book-generic"></div>
                    {strip}
                        <script>
                            new Book({ldelim} parent: 'book-generic', pages: [
                            {foreach from=$lvData.page.pagetext item=pagetext name=j}
                                '{$pagetext|escape:"javascript"}'
                                {if $smarty.foreach.j.last}{else},{/if}
                            {/foreach}
                            ]{rdelim})
                        </script>
                    {/strip}
                {/if}

                <h2>{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData.relTabs}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
