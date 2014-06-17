<?php $this->brick('header'); ?>

        <div id="main">
            <div id="main-precontents"></div>
            <div id="main-contents" class="main-contents">

<?php $this->brick('announcement'); ?>

                <script type="text/javascript">g_initPath([1,3])</script>
                <div class="text">
                    <div id="compare-generic"></div>
                    <script type="text/javascript">//<![CDATA[
<?php
foreach ($this->cmpItems as $item):
    echo '                        g_items.add('.$item[0].', {name_'.User::$localeString.':\''.Util::jsEscape($item[1]).'\', quality:'.$item[2].', icon:\''.$item[3].'\', jsonequip:'.json_encode($item[4], JSON_NUMERIC_CHECK)."});\n";
endforeach;
?>
                        new Summary({template:'compare',id:'compare',parent:'compare-generic',groups:<?php echo json_encode($this->summary, JSON_NUMERIC_CHECK); ?>});
                    //]]></script>
                </div>
                <div class="clear"></div>
            </div>
        </div>

<?php $this->brick('footer'); ?>
