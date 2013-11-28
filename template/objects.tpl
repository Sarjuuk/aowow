{include file='header.tpl'}

		<div id="main">
			<div id="main-precontents"></div>
			<div id="main-contents" class="main-contents">
				<script type="text/javascript">
					g_initPath({$page.path});
				</script>

				<div id="lv-objects" class="listview"></div>

				<script type="text/javascript">
					{include file='bricks/listviews/object.tpl' data=$objects.data params=$objects.params} 
				</script>

				<div class="clear"></div>
			</div>
		</div>

{include file='footer.tpl'}
