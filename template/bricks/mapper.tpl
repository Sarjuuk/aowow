<div id="mapper" style="width: 778px; margin: 0 auto">
    {if isset($som)}<div id="som-generic"></div>{/if}
    <div id="mapper-generic"></div>
    <div class="pad clear"></div>
</div>

<script type="text/javascript">//<![CDATA[
    var g_pageInfo = {ldelim}id:{$map.zone}{rdelim};
    var myMapper = new Mapper({ldelim}{foreach from=$map key=k item=v}{$k}: {$v}, {/foreach}parent: 'mapper-generic'{rdelim});
    {if isset($som)}new ShowOnMap({$som});{/if}
//]]></script>
