{include file='header.tpl'}

    <div id="main">
        <div id="main-precontents"></div>
        <div id="main-contents" class="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}
{if !empty($lvData.map)}
    {include file='bricks/mapper.tpl' map=$lvData.map som=$lvData.som}
{/if}

            <script type="text/javascript">
                g_initPath({$page.path});
            </script>

{if !empty($lvData.page.name) || !empty($lvData.page.h1Links)}
            <div class="text">
{if !empty($lvData.page.h1Links)}
                <div class="h1-links">{$lvData.page.h1Links}</div>
{/if}
{if !empty($lvData.page.name)}
                <h1>{$lvData.page.name}</h1>
{/if}
            </div>
{/if}

{if !empty($lvData.listviews)}
    {if count($lvData.listviews) > 1}
            <div id="tabs-generic"></div>
    {/if}
            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">
    {if count($lvData.listviews) > 1}
                var myTabs = new Tabs({ldelim}parent: $WH.ge('tabs-generic'){rdelim});
    {/if}
    {foreach from=$lvData.listviews item=lv}
        {if isset($lv.file)}
                {include file="bricks/listviews/`$lv.file`.tpl" data=$lv.data params=$lv.params}
        {/if}
    {/foreach}
    {if count($lvData.listviews) > 1}
                myTabs.flush();
    {/if}
            </script>
            <div class="clear"></div>
{/if}

        </div>
    </div>

{include file='footer.tpl'}
