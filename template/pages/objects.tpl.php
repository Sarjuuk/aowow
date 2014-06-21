<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php $this->brick('announcement'); ?>

            <script type="text/javascript">
                g_initPath(<?php echo json_encode($this->path, JSON_NUMERIC_CHECK).', '.(empty($this->filter['query']) ? 0 : 1) ?>);
<?php
if (!empty($this->filter['query'])):
  // todo: update menu-class         Menu.modifyUrl(Menu.findItem(mn_database, [5]), { filter: '+={$filter.query|escape:'quotes'}' }, { onAppendCollision: fi_mergeFilterParams, onAppendEmpty: fi_setFilterParams, menuUrl: Menu.getItemUrl(Menu.findItem(mn_database, [5])) });
endif;
?>
            </script>

            <div id="fi" style="display: <?php echo empty($this->filter['query']) ? 'none' : 'block' ?>;">
                <form action="?objects<?php echo $this->subCat; ?>&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <table>
                        <tr><td><?php echo Util::ucFirst(Lang::$main['name']).Lang::$main['colon']; ?></td><td>&nbsp;<input type="text" name="na" size="30" <?php echo isset($this->filter['na']) ? 'value="'.Util::htmlEscape($this->filter['na']).'" ' : null; ?>/></td></tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div>
                    <div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?php echo Lang::$main['addFilter']; ?></a></div>

                    <div class="padded2 clear">
                        <div style="float: right"><?php echo Lang::$main['refineSearch']; ?></div>
                        <?php echo Lang::$main['match'].Lang::$main['colon']; ?><input type="radio" name="ma" value="" id="ma-0" <?php echo !isset($this->filter['ma']) ? 'checked="checked" ' : null ?>/><label for="ma-0"><?php echo Lang::$main['allFilter']; ?></label><input type="radio" name="ma" value="1" id="ma-1" <?php echo isset($this->filter['ma']) ? 'checked="checked" ' : null ?> /><label for="ma-1"><?php echo Lang::$main['oneFilter']; ?></label>
                    </div>

                    <div class="clear"></div>

                    <div class="padded">
                        <input type="submit" value="<?php echo Lang::$main['applyFilter']; ?>" />
                        <input type="reset" value="<?php echo Lang::$main['resetForm']; ?>" />
                    </div>

                </form>
                <div class="pad"></div>
            </div>

            <script type="text/javascript">//<![CDATA[
                fi_init('objects');
<?php
foreach ($this->filter['fi'] as $str):
    echo '                '.$str."\n";
endforeach;
?>
            //]]></script>

            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">//<![CDATA[
<?php $this->lvBrick($this->lvData['file'], ['data' => $this->lvData['data'], 'params' => $this->lvData['params']]); ?>
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
