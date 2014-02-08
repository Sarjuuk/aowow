{if !empty($headIcons)}
    {foreach from=$headIcons key='k' item='v'}
        <div id="h1-icon-{$k}" class="h1-icon"></div>
    {/foreach}
    <script type="text/javascript">//<![CDATA[
    {foreach from=$headIcons key='k' item='v'}
        $WH.ge('h1-icon-{$k}').appendChild(Icon.create('{$v}', 1));
    {/foreach}
    //]]></script>
{/if}