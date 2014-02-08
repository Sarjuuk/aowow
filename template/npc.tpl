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

                <h1>{$name}{if $subname} &lt;{$subname}&gt;{/if}</h1>

{include file='bricks/article.tpl'}

{if is_array($position)}
                <div>{$lang.difficultyPH} <a href="?npc={$position[0]}">{$position[1]}</a>.</div>
                <div class="pad"></div>
{elseif $position}
                <div>{#This_NPC_can_be_found_in#} {strip}<span id="locations">
                    {foreach from=$position item=zone name=zone}
                        <a href="javascript:;" onclick="
                        {if $zone.atid}
                            myMapper.update(
                                {ldelim}
                                    zone:{$zone.atid}
                                    {if $zone.points}
                                        ,
                                    {/if}
                                {if $zone.points}
                                    coords:[
                                        {foreach from=$zone.points item=point name=point}
                                                [{$point.x},{$point.y},
                                                {ldelim}
                                                    label:'$<br>
                                                    <div class=q0>
                                                        <small>
                                                            {if isset($point.r)}
                                                                {#Respawn#}:
                                                                {if isset($point.r.h)} {$point.r.h}{#hr#}{/if}
                                                                {if isset($point.r.m)} {$point.r.m}{#min#}{/if}
                                                                {if isset($point.r.s)} {$point.r.s}{#sec#}{/if}
                                                            {else}
                                                                {#Waypoint#}
                                                            {/if}
                                                            {if isset($point.events)}<br>{$point.events|escape:"quotes"}{/if}
                                                        </small>
                                                    </div>',type:'{$point.type}'
                                                {rdelim}]
                                                {if !$smarty.foreach.point.last},{/if}
                                        {/foreach}
                                    ]
                                {/if}
                                {rdelim});
                            $WH.ge('mapper-generic').style.display='block';
                        {else}
                            $WH.ge('mapper-generic').style.display='none';
                        {/if}
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

{if $quotes}
                <h3><a class="disclosure-off" onclick="return g_disclose($WH.ge('quotes-generic'), this)">{$lang.quotes} ({$quotes[1]})</a></h3>
                <div id="quotes-generic" style="display: none"><ul>{strip}
    {foreach from=$quotes[0] item=group}
        {if $group|@count > 1}<ul>{/if}
                <li>
            {foreach from=$group name=quote item=itr}
                    <div><span class="s{$itr.type}">{if $itr.type != 4}{$name} {$lang.textTypes[$itr.type]}{$lang.colon}{if $itr.lang}[{$itr.lang}] {/if}{/if}{$itr.text}</span></div>
                {if $smarty.foreach.quote.last}{else}</li><li>{/if}
            {/foreach}
                </li>
        {if $group|@count > 1}</li></ul>{/if}
    {/foreach}
                {/strip}</ul></div>
{/if}

{if $reputation}
                <h3>{$lang.gains}</h3>

{$lang.gainsDesc}{$lang.colon}
{foreach from=$reputation item=set}
    {if $reputation|@count > 1}<ul><li><span class="rep-difficulty">{$set[0]}</span></li>{/if}
                    <ul>
    {foreach from=$set[1] item=itr}
                        <li><div{if $itr.qty < 0} class="reputation-negative-amount"{/if}><span>{$itr.qty}</span> {$lang.repWith} <a href="?faction={$itr.id}">{$itr.name}</a>{if $itr.cap && $itr.qty > 0} ({$lang.stopsAt|@sprintf:$itr.cap}){/if}</div></li>
    {/foreach}
                    </ul>
    {if $reputation|@count > 1}</ul>{/if}
{/foreach}

{/if}
                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
