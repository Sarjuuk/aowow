{include file='header.tpl'}

    <div id="main">

        <div id="main-precontents"></div>
        <div id="main-contents" class="main-contents">

            <script type="text/javascript">
                {include file='bricks/community.tpl'}
                var g_pageInfo = {ldelim}type: {$page.type}, typeId: {$page.typeId}, name: '{$object.name|escape:"quotes"}'{rdelim};
                g_initPath({$page.path});
            </script>

{if isset($object.key) or isset($object.lockpicking) or isset($object.mining) or isset($object.herbalism)}
            <table class="infobox">
                <tr><th>{#Quick_Facts#}</th></tr>
                <tr><td><div class="infobox-spacer"></div>
                <ul>
                    {if isset($object.key)}<li><div>{#Key#}{$lang.colon}<a class="q{$object.key.quality}" href="?item={$object.key.id}">[{$object.key.name}]</a></div></li>{/if}
                    {if isset($object.lockpicking)}<li><div>{#Lockpickable#} (<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, '{#Required_lockpicking_skill#}', 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{$object.lockpicking}</span>)</div></li>{/if}
                    {if isset($object.mining)}<li><div>{#Mining#} (<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, '{#Required_mining_skill#}', 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{$object.mining}</span>)</div></li>{/if}
                    {if isset($object.herbalism)}<li><div>{#Herb#} (<span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, '{#Required_herb_skill#}', 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">{$object.herbalism}</span>)</div></li>{/if}
                </ul>
                </td></tr>
            </table>
{/if}

            <div class="text">

                <a href="{$wowhead}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
                <h1>{$object.name}</h1>

{if $object.position}
                <div>{#This_Object_can_be_found_in#}
{strip}
                <span id="locations">
                    {foreach from=$object.position item=zone name=zone}
                        <a href="javascript:;" onclick="
                            myMapper.update(
                                {ldelim}
                                {if $zone.atid}
                                    zone:{$zone.atid}
                                    {if $zone.points}
                                        ,
                                    {/if}
                                {else}
                                    show:false
                                {/if}
                                {if $zone.points}
                                    coords:[
                                        {foreach from=$zone.points item=point name=point}
                                                [{$point.x},{$point.y},
                                                {ldelim}
                                                    label:'$<br>
                                                    <div class=q0>
                                                        <small>{#Respawn#}:
                                                            {if isset($point.r.h)} {$point.r.h}{#hr#}{/if}
                                                            {if isset($point.r.m)} {$point.r.m}{#min#}{/if}
                                                            {if isset($point.r.s)} {$point.r.s}{#sec#}{/if}
                                                            {if isset($point.events)}<br>{$point.events|escape:"quotes"}{/if}
                                                        </small>
                                                    </div>',type:'{$point.type}'
                                                {rdelim}]
                                                {if !$smarty.foreach.point.last},{/if}
                                        {/foreach}
                                    ]
                                {/if}
                                {rdelim});
                            g_setSelectedLink(this, 'mapper'); return false" onmousedown="return false">
                            {$zone.name}</a>{if $zone.population > 1}&nbsp;({$zone.population}){/if}{if $smarty.foreach.zone.last}.{else}, {/if}
                    {/foreach}
                </span></div>
{/strip}
                <div id="mapper-generic"></div>
                <div class="clear"></div>

                <script type="text/javascript">
                    var myMapper = new Mapper({ldelim}parent: 'mapper-generic', zone: '{$object.position[0].atid}'{rdelim});
                    $WH.gE($WH.ge('locations'), 'a')[0].onclick();
                </script>

{else}
                {#This_Object_cant_be_found#}
{/if}

{if isset($object.pagetext)}
    <h3>Content</h3>
    <div id="book-generic"></div>
    {strip}
        <script>
            new Book({ldelim} parent: 'book-generic', pages: [
            {foreach from=$object.pagetext item=pagetext name=j}
                '{$pagetext|escape:"javascript"}'
                {if $smarty.foreach.j.last}{else},{/if}
            {/foreach}
            ]{rdelim})
        </script>
    {/strip}
{/if}

                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData.relTabs}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
