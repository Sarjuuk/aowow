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
                    <a href="http://www.wowhead.com/?{$query}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
        {if !empty($found)}
                    <h1>{$lang.foundResult} <i>{$search|escape:"html"}</i></h1>
                </div>
                <div id="tabs-generic"></div>
                <div id="listview-generic" class="listview"></div>
                <script type="text/javascript">
                    var myTabs = new Tabs({ldelim}parent: ge('tabs-generic'){rdelim});
{if isset($found.class)}       {include file='bricks/class_table.tpl'        data=$found.class.data       params=$found.class.params      } {/if}
{if isset($found.race)}        {include file='bricks/race_table.tpl'         data=$found.race.data        params=$found.race.params       } {/if}
{if isset($found.title)}       {include file='bricks/title_table.tpl'        data=$found.title.data       params=$found.title.params      } {/if}
{if isset($found.npc)}         {include file='bricks/creature_table.tpl'     data=$found.npc.data         params=$found.npc.params        } {/if}
{if isset($found.object)}      {include file='bricks/object_table.tpl'       data=$found.object.data      params=$found.object.params     } {/if}
{if isset($found.itemset)}     {include file='bricks/itemset_table.tpl'      data=$found.itemset.data     params=$found.itemset.params    } {/if}
{if isset($found.item)}        {include file='bricks/item_table.tpl'         data=$found.item.data        params=$found.item.params       } {/if}
{if isset($found.quest)}       {include file='bricks/quest_table.tpl'        data=$found.quest.data       params=$found.quest.params      } {/if}
{if isset($found.ability)}     {include file='bricks/spell_table.tpl'        data=$found.ability.data     params=$found.ability.params    } {/if}
{if isset($found.talent)}      {include file='bricks/spell_table.tpl'        data=$found.talent.data      params=$found.talent.params     } {/if}
{if isset($found.npcSpell)}    {include file='bricks/spell_table.tpl'        data=$found.npcSpell.data    params=$found.npcSpell.params   } {/if}
{if isset($found.petSpell)}    {include file='bricks/spell_table.tpl'        data=$found.petSpell.data    params=$found.petSpell.params   } {/if}
{if isset($found.spell)}       {include file='bricks/spell_table.tpl'        data=$found.spell.data       params=$found.spell.params      } {/if}
{if isset($found.unkSpell)}    {include file='bricks/spell_table.tpl'        data=$found.unkSpell.data    params=$found.unkSpell.params   } {/if}
{if isset($found.zone)}        {include file='bricks/zone_table.tpl'         data=$found.zone.data        params=$found.zone.params       } {/if}
{if isset($found.faction)}     {include file='bricks/faction_table.tpl'      data=$found.faction.data     params=$found.faction.params    } {/if}
{if isset($found.pet)}         {include file='bricks/npc_table.tpl'          data=$found.pet.data         params=$found.pet.params        } {/if}
{if isset($found.achievement)} {include file='bricks/achievement_table.tpl'  data=$found.achievement.data params=$found.achievement.params} {/if}
{if isset($found.event)}       {include file='bricks/event_table.tpl'        data=$found.event.data       params=$found.event.params      } {/if}
{if isset($found.skill)}       {include file='bricks/skill_table.tpl'        data=$found.skill.data       params=$found.skill.params      } {/if}
{if isset($found.currency)}    {include file='bricks/currency_table.tpl'     data=$found.currency.data    params=$found.currency.params   } {/if}
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
