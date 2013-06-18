{include file='header.tpl'}
{assign var="file" value=$lvData.file}

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

            <div id="tabs-generic"></div>
            <div id="listview-generic" class="listview"></div>
            <script type="text/javascript">
                {if !empty($lvData.calendar)}var myTabs = new Tabs({ldelim}parent: $WH.ge('tabs-generic'){rdelim});{/if}
                {include file="bricks/listviews/$file.tpl" data=$lvData.data params=$lvData.params}
                {if !empty($lvData.calendar)}
                    {include file='bricks/listviews/calendar.tpl' data=$lvData.data params=$lvData.params}
                    myTabs.flush();
                {/if}
            </script>

            <div class="clear"></div>
        </div>
    </div>

{include file='footer.tpl'}
