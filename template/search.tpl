{include file='header.tpl'}

    <div id="main">
        <div id="main-precontents"></div>
        <div class="main-contents" id="main-contents">
{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}
            <div class="text">
                <a href="http://www.wowhead.com/?{$query[0]}={$query[1]}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
    {if !empty($found)}
                <h1>{$lang.foundResult} <i>{$search|escape:"html"}</i></h1>
            </div>
            <div id="tabs-generic"></div>
            <div id="listview-generic" class="listview"></div>
            <script type="text/javascript">
                var myTabs = new Tabs({ldelim}parent: ge('tabs-generic'){rdelim});
{if isset($found.class)}       {include file='bricks/listviews/class.tpl'        data=$found.class.data       params=$found.class.params      } {/if}
{if isset($found.race)}        {include file='bricks/listviews/race.tpl'         data=$found.race.data        params=$found.race.params       } {/if}
{if isset($found.title)}       {include file='bricks/listviews/title.tpl'        data=$found.title.data       params=$found.title.params      } {/if}
{if isset($found.npc)}         {include file='bricks/listviews/creature.tpl'     data=$found.npc.data         params=$found.npc.params        } {/if}
{if isset($found.object)}      {include file='bricks/listviews/object.tpl'       data=$found.object.data      params=$found.object.params     } {/if}
{if isset($found.itemset)}     {include file='bricks/listviews/itemset.tpl'      data=$found.itemset.data     params=$found.itemset.params    } {/if}
{if isset($found.item)}        {include file='bricks/listviews/item.tpl'         data=$found.item.data        params=$found.item.params       } {/if}
{if isset($found.quest)}       {include file='bricks/listviews/quest.tpl'        data=$found.quest.data       params=$found.quest.params      } {/if}
{if isset($found.ability)}     {include file='bricks/listviews/spell.tpl'        data=$found.ability.data     params=$found.ability.params    } {/if}
{if isset($found.talent)}      {include file='bricks/listviews/spell.tpl'        data=$found.talent.data      params=$found.talent.params     } {/if}
{if isset($found.glyph)}       {include file='bricks/listviews/spell.tpl'        data=$found.glyph.data       params=$found.glyph.params      } {/if}
{if isset($found.proficiency)} {include file='bricks/listviews/spell.tpl'        data=$found.proficiency.data params=$found.proficiency.params} {/if}
{if isset($found.profession)}  {include file='bricks/listviews/spell.tpl'        data=$found.profession.data  params=$found.profession.params } {/if}
{if isset($found.companion)}   {include file='bricks/listviews/spell.tpl'        data=$found.companion.data   params=$found.companion.params  } {/if}
{if isset($found.mount)}       {include file='bricks/listviews/spell.tpl'        data=$found.mount.data       params=$found.mount.params      } {/if}
{if isset($found.npcSpell)}    {include file='bricks/listviews/spell.tpl'        data=$found.npcSpell.data    params=$found.npcSpell.params   } {/if}
{if isset($found.spell)}       {include file='bricks/listviews/spell.tpl'        data=$found.spell.data       params=$found.spell.params      } {/if}
{if isset($found.unkSpell)}    {include file='bricks/listviews/spell.tpl'        data=$found.unkSpell.data    params=$found.unkSpell.params   } {/if}
{if isset($found.zone)}        {include file='bricks/listviews/zone.tpl'         data=$found.zone.data        params=$found.zone.params       } {/if}
{if isset($found.faction)}     {include file='bricks/listviews/faction.tpl'      data=$found.faction.data     params=$found.faction.params    } {/if}
{if isset($found.pet)}         {include file='bricks/listviews/pet.tpl'          data=$found.pet.data         params=$found.pet.params        } {/if}
{if isset($found.achievement)} {include file='bricks/listviews/achievement.tpl'  data=$found.achievement.data params=$found.achievement.params} {/if}
{if isset($found.statistic)}   {include file='bricks/listviews/achievement.tpl'  data=$found.statistic.data   params=$found.statistic.params  } {/if}
{if isset($found.event)}       {include file='bricks/listviews/event.tpl'        data=$found.event.data       params=$found.event.params      } {/if}
{if isset($found.skill)}       {include file='bricks/listviews/skill.tpl'        data=$found.skill.data       params=$found.skill.params      } {/if}
{if isset($found.currency)}    {include file='bricks/listviews/currency.tpl'     data=$found.currency.data    params=$found.currency.params   } {/if}
                myTabs.flush();
            </script>
    {else}
            <h1>{$lang.noResult} <i>{$search|escape:"html"}</i></h1>

            {$lang.tryAgain}
    {/if}
            <div class="clear"></div>

        </div>

    </div>
    
{include file='footer.tpl'}
