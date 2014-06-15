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
{if isset($unavailable)}
                <div class="pad"></div>
                <b style="color: red">{$lang.unavailable}</b>
{/if}

{if isset($reqMinRep) || isset($reqMaxRep)}
                <h3>{$lang.additionalReq}{$lang.colon}</h3>
                <ul style="border-bottom:solid 1px #505050;">
                    {if isset($reqMinRep)}<li><div>{$reqMinRep}</div></li>{/if}
                    {if isset($reqMaxRep)}<li><div>{$reqMaxRep}</div></li>{/if}
                    <br /> {*shudder*}
                </ul>
{/if}

{if $objectives}
                {$objectives}
{elseif $requestItems}
                <h3>{$lang.progress}</h3>
                {$requestItems}
{elseif $offerReward}
                <h3>{$lang.completion}</h3>
                {$offerReward}
{/if}

{if $end}
                <table class="iconlist">
                    <tr><th><p style="height: 26px; width: 30px;">&nbsp;</p></th><td>{$end}</td></tr>
    {if $suggestedPl}
                    <tr><th><p style="height: 26px; width: 30px;">&nbsp;</p></th><td>{$lang.suggestedPl}{$lang.colon}{$suggestedPl}</td></tr>
    {/if}
                </table>
                <div class="pad"></div>
{/if}

{if $objectiveList}
    {if $end}
                {$lang.providedItem}{$lang.colon}
    {/if}
                <table class="iconlist">
    {foreach from=$objectiveList item=i key=k}
        {if isset($i.typeStr) && ($i.typeStr == 'item' || $i.typeStr == 'spell')}
                    <tr><th align="right" id="iconlist-icon-{$k}"></th><td><span class="q{$i.quality}"><a href="?{$i.typeStr}={$i.id}">{$i.name}</a></span>{if !$end}{$i.extraText}{/if}{if $i.qty > 1} ({$i.qty}){/if}</td></tr>
        {elseif !empty($i.typeStr)}
                    <tr><th><ul><li><var>&nbsp;</var></li></ul></th><td><span class="q"><a href="?{$i.typeStr}={$i.id}">{$i.name}</a></span>{if !$end}{$i.extraText}{/if}{if $i.qty > 1} ({$i.qty}){/if}</td></tr>
{*      todo: research, when we want plain text as unordered list element *}
        {else}
                    <tr><th><p style="height: 26px; width: 30px;">&nbsp;</p></th><td>{$i.text}</td></tr>
        {/if}
    {/foreach}
    {if $suggestedPl && !$end}
                    <tr><th><p style="height: 26px; width: 30px;">&nbsp;</p></th><td>{$lang.suggestedPl}{$lang.colon}{$suggestedPl}</td></tr>
    {/if}
                </table>

                <script type="text/javascript">//<![CDATA[
    {foreach from=$objectiveList item=i key=k}
        {if isset($i.typeStr) && ($i.typeStr == 'item' || $i.typeStr == 'spell')}
                    $WH.ge('iconlist-icon-{$k}').appendChild(g_{$i.typeStr}s.createIcon({$i.id}, 0, {$i.qty}));
        {/if}
    {/foreach}
                //]]></script>
{/if}

{include file='bricks/mapper.tpl'}

{* Description *}
{if $details}
                <h3>{$lang.description}</h3>
                {$details}
{/if}

{* Progress (disclosed) *}
{if $requestItems && $objectives}
                <h3><a href="javascript:;" class="disclosure-off" onclick="return g_disclose($WH.ge('disclosure-progress'), this)">{$lang.progress}</a></h3>
                <div id="disclosure-progress" style="display: none">{$requestItems}</div>
{/if}

{* Completion (disclosed) *}
{if $offerReward && ($requestItems || $objectives)}
                <h3><a href="javascript:;" class="disclosure-off" onclick="return g_disclose($WH.ge('disclosure-completion'), this)">{$lang.completion}</a></h3>
                <div id="disclosure-completion" style="display: none">{$offerReward}</div>
{/if}

{* Rewards *}
{if !empty($rewards.items) || !empty($rewards.money) || !empty($rewards.spells) || !empty($rewards.choice)}
    {assign var='offset' value=0}
                    <h3>{$lang.rewards}</h3>

    {if isset($rewards.choice)}
        {include file='bricks/rewards.tpl' rewTitle=$lang.chooseItems rewData=$rewards.choice offset=$offset}
        {math assign='offset' equation="x + y" x=$offset y=$rewards.choice|@count}
    {/if}

    {if isset($rewards.spells)}
        {if isset($rewards.choice)}
                        <div class="pad"></div>
        {/if}

        {if isset($rewards.spells.learn)}
            {include file='bricks/rewards.tpl' rewTitle=$lang.spellLearn rewData=$rewards.spells.learn offset=$offset extra=$rewards.spells.extra}
            {math assign='offset' equation="x + y" x=$offset y=$rewards.spells.learn|@count}
        {else}
            {include file='bricks/rewards.tpl' rewTitle=$lang.spellCast rewData=$rewards.spells.cast offset=$offset extra=$rewards.spells.extra}
            {math assign='offset' equation="x + y" x=$offset y=$rewards.spells.cast|@count}
        {/if}
    {/if}

    {if !empty($rewards.items) || !empty($rewards.money)}
        {if isset($rewards.choice) || isset($rewards.spells)}
                        <div class="pad"></div>
        {/if}

        {if isset($rewards.choice)} {* bullshit!! *}
            {include file='bricks/rewards.tpl' rewTitle=$lang.receiveAlso rewData=$rewards.items offset=$offset extra=$rewards.money}
        {else}
            {include file='bricks/rewards.tpl' rewTitle=$lang.receiveItems rewData=$rewards.items offset=$offset extra=$rewards.money}
        {/if}
    {/if}
{/if}

{* Gains *}
{if $gains}
                    <h3>{$lang.gains}</h3>
                    {$lang.gainsDesc}{$lang.colon}
                    <ul>{strip}
    {if isset($gains.xp)}
                        <li><div>{$gains.xp|number_format} {$lang.experience}</div></li>
    {/if}

    {if !empty($gains.rep)}
        {foreach from=$gains.rep item=i}
                        <li><div>{if $i.qty < 0}<b class="q10">{$i.qty}</b>{else}{$i.qty}{/if} {$lang.repWith} <a href="?faction={$i.id}">{$i.name}</a></div></li>
        {/foreach}
    {/if}

    {if isset($gains.title)}
                        <li><div>{$gains.title}</div></li>
    {/if}

    {if isset($gains.tp)}
                        <li><div>{$gains.tp} {$lang.bonusTalents}</div></li>
    {/if}
                    </ul>{/strip}
{/if}

{if $mail}
                        <h3>{$lang.mailDelivery|sprintf:$mail.delay}</h3>
    {if $mail.subject}
                        <div class="book"><div class="page">{$mail.subject}</div></div>
    {/if}
    {if $mail.text}
                        <div class="book"><div class="page">{$mail.text}</div></div>
    {/if}
{/if}
                <h2 class="clear">{$lang.related}</h2>
            </div>

{include file='bricks/tabsRelated.tpl' tabs=$lvData}

{include file='bricks/contribute.tpl'}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
