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

            <div id="tabs-generic"></div>
			<div id="listview-generic" class="listview"></div>

			<script type="text/javascript">
                var myTabs = new Tabs({ldelim}parent: ge('tabs-generic'){rdelim});
				{include file='bricks/event_table.tpl'    data=$data.page params=$data.params}
				{* include file='bricks/calendar_table.tpl' data=$data.page params=$data.params *}
                myTabs.flush();
			</script>

			<div class="clear"></div>
		</div>
	</div>

{include file='footer.tpl'}
