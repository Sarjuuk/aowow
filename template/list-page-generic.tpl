{include file='header.tpl'}

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}
{if !empty($map)}
    {include file='bricks/mapper.tpl' map=$map.data som=$map.som}
{/if}

            <script type="text/javascript">//<![CDATA[
                g_initPath({$path});
            //]]></script>

{if !empty($name) || !empty($h1Links)}
            <div class="text">
{if !empty($h1Links)}
                <div class="h1-links">{$h1Links}</div>
{/if}
{if !empty($name)}
                <h1>{$name}</h1>
{/if}
            </div>
{/if}

{if !empty($lvData)}
    {if count($lvData) > 1}
            <div id="tabs-generic"></div>
    {/if}
            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
    {if count($lvData) > 1}
                var myTabs = new Tabs({ldelim}parent: $WH.ge('tabs-generic'){rdelim});
    {/if}
    {foreach from=$lvData item=lv}
        {if isset($lv.file)}
                {include file="bricks/listviews/`$lv.file`.tpl" data=$lv.data params=$lv.params}
        {/if}
    {/foreach}
    {if count($lvData) > 1}
                myTabs.flush();
    {/if}
            //]]></script>
            <div class="clear"></div>
{/if}

        </div><!-- main-contents -->
    </div><!-- main -->

{include file='footer.tpl'}
