{include file='header.tpl'}

		<div id="main">
			<div id="main-precontents"></div>
			<div id="main-contents" class="main-contents">
				<script type="text/javascript">
					g_initPath({$page.path});
				</script>

				<div id="lv-factions" class="listview"></div>

				<script type="text/javascript">
					{include file='bricks/factions_table.tpl' data=$factions.data params=$factions.params}
				</script>

				<div class="clear"></div>
			</div>
		</div>

{include file='footer.tpl'}
