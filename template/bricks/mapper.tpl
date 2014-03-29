{if !empty($map) && isset($map.data)}
    {if $map.data.zone < 0}
            <div id="mapper" style="width: 778px; margin: 0 auto">
        {if isset($map.som)}
                <div id="som-generic"></div>
        {/if}
                <div id="mapper-generic"></div>
                <div class="pad clear"></div>
            </div>
    {else}
            <div class="pad"></div>
        {if isset($map.som)}
            <div id="som-generic"></div>
        {/if}
        {if isset($map.mapperData)}
            <div>{$lang.foundIn} <span id="locations">{$map.mapSelector}.</span></div>
        {/if}
            <div id="mapper-generic"></div>
            <div style="clear: left"></div>
    {/if}

            <script type="text/javascript">//<![CDATA[
    {if $map.data.zone < 0}
                var g_pageInfo = {ldelim}id:{$map.data.zone}{rdelim};
    {elseif !empty($map.mapperData)}
                var g_mapperData = {$map.mapperData};
    {else}
                var g_mapperData = {ldelim}{$map.data.zone}: {ldelim}{rdelim}{rdelim};
    {/if}
                var myMapper = new Mapper({ldelim}{foreach from=$map.data key=k item=v}{$k}: {$v}, {/foreach}parent: 'mapper-generic'{rdelim});
    {if !empty($map.som)}
                new ShowOnMap({$map.som});
    {/if}
            //]]></script>
{/if}
