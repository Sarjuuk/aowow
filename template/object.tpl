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
                var g_pageInfo = {ldelim}type: {$type}, typeId: {$typeId}, name: '{$name|escape:"quotes"}'{rdelim};
                g_initPath({$path});
            //]]></script>

{include file='bricks/infobox.tpl'}

            <div class="text">
{include file='bricks/redButtons.tpl'}

                <h1>{$name}</h1>

{include file='bricks/article.tpl'}

{if $positions}
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
                    var myMapper = new Mapper({ldelim}parent: 'mapper-generic', zone: '{$position[0].atid}'{rdelim});
                    $WH.gE($WH.ge('locations'), 'a')[0].onclick();
                </script>
{else}
                {$lang.unkPosition}
{/if}
{include file='bricks/book.tpl'}

                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
