    <table class="infobox">
{if !empty($info)}
        <tr><th id="infobox-quick-facts">{$lang.quickFacts}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-contents0"></div></td></tr>
{/if}
{if !empty($series)}
        <tr><th id="infobox-series">{$lang.series}</th></tr>
        <tr><td>
            <div class="infobox-spacer"></div>
            <table class="series">
    {foreach from=$series key='idx' item='itr'}
                <tr>
                    <th>{$idx+1}.</th>
                    <td><div>
        {foreach name=itemItr from=$itr item='i'}
            {if $i.side == 1}<span class="alliance-icon-padded">{elseif $i.side == 2}<span class="horde-icon-padded">{/if}
                        {if ($i.typeId == $page.typeId)}
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
{/if}
        <tr><th id="infobox-screenshots">{$lang.screenshots}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-ss"></div></td></tr>
{if $user.id > 0}
        <tr><th id="infobox-videos">{$lang.videos}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-vi"></div></td></tr>
{/if}
    </table>
    <script type="text/javascript">ss_appendSticky()</script>
{if $user.id > 0}
    <script type="text/javascript">vi_appendSticky()</script>
{/if}
{if !empty($info)}
    <script type="text/javascript">
        Markup.printHtml("{$info}", "infobox-contents0", {ldelim} allow: Markup.CLASS_STAFF, dbpage: true {rdelim});
    </script>
{/if}