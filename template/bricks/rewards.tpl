{if $rewTitle}
                {$rewTitle}{$lang.colon}
        {if isset($extra)}{$extra}{/if}

{/if}
{if $rewData}
                <div class="pad"></div>
                <table class="icontab icontab-box">
                    <tr>
    {foreach from=$rewData item=i key=k}
                        <th id="icontab-icon{$k+1+$offset}"></th><td><span class="q{if isset($i.quality)}{$i.quality}{/if}"><a href="?{$i.typeStr}={$i.id}">{$i.name}</a></span></td>
        {if $k%2}
                    </tr><tr>
        {/if}

    {/foreach}
    {if count($rewData)%2}
                        <th style="display: none"></th><td style="display: none"></td>
    {/if}
                    </tr>
                </table>

                <script type="text/javascript">//<![CDATA[
    {foreach from=$rewData item=i key=k}{if isset($i.globalStr)}
                    $WH.ge('icontab-icon{$k+1+$offset}').appendChild({$i.globalStr}.createIcon({$i.id}, 1, {if isset($i.qty)}{$i.qty}{else}0{/if}));
    {/if}{/foreach}
                //]]></script>
{/if}