<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

            <div class="text">
                <div id="compare-generic"></div>
                <script type="text/javascript">//<![CDATA[
<?php
foreach ($this->cmpItems as $iId => $iData):
    echo '                        g_items.add('.$iId.', '.Util::toJSON($iData).");\n";
endforeach;
?>
                    new Summary(<?=Util::toJSON($this->summary); ?>);
                //]]></script>
            </div>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
