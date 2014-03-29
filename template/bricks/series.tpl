    <tr><th id="infobox-series">{if !empty($listTitle)}{$listTitle}{else}{$lang.series}{/if}</th></tr>
    <tr><td>
        <div class="infobox-spacer"></div>
        <table class="series">
{foreach from=$list key='idx' item='itr'}
            <tr>
                <th>{$idx+1}.</th>
                <td><div>
    {foreach name=itemItr from=$itr item='i'}
        {if $i.side == 1}<span class="icon-alliance-padded">{elseif $i.side == 2}<span class="icon-horde">{/if}
                    {if ($i.typeId == $typeId)}
                        <b>{$i.name}</b>
                    {else}
                        <a href="?{$i.typeStr}={$i.typeId}">{$i.name}</a>
                    {/if}
        {if $i.side != 3}</span>{/if}
        {if $smarty.foreach.itemItr.last}{else}<br />{/if}
    {/foreach}
                </div></td>
            </tr>
{/foreach}
        </table>
    </td></tr>
