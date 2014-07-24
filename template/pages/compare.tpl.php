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
foreach ($this->cmpItems as $item):
    echo '                        g_items.add('.$item[0].', {name_'.User::$localeString.':\''.Util::jsEscape($item[1]).'\', quality:'.$item[2].', icon:\''.$item[3].'\', jsonequip:'.json_encode($item[4], JSON_NUMERIC_CHECK)."});\n";
endforeach;
?>
                    new Summary({template:'compare',id:'compare',parent:'compare-generic',groups:<?php echo json_encode($this->summary, JSON_NUMERIC_CHECK); ?>});
                //]]></script>
            </div>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
