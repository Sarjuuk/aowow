{include file='header.tpl'}

    <div id="main">
        <div id="main-precontents"></div>
        <div id="main-contents" class="main-contents">
{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}
            <script type="text/javascript">
                g_initPath({$page.path});
            </script>

            <div id="lv-titles" class="listview"></div>
            <script type="text/javascript">
                {include file='bricks/title_table.tpl' data=$data.page params=$data.params}
            </script>

            <div class="clear"></div>
        </div>
    </div>

{include file='footer.tpl'}
