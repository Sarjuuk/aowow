<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

            <div class="text">
                <h1><?=Lang::privileges('privileges');?></h1>
                <div style="float:right;line-height:1.2;max-width:410px;overflow:hidden;text-align:center"><img class="border" alt="" src="<?=Cfg::get('STATIC_URL');?>/images/help/privileges/example.jpg" /></div>
                <p><?=Lang::privileges('main');?></p>
                <br><br>
                <table class="wsa-list wsa-tbl">
                    <thead><th><?=Lang::privileges('privilege');?></th><th><?=Lang::privileges('requiredRep');?></th></thead>
                    <tbody>
<?php
    foreach ($this->privileges as $id => [$earned, $name, $value]):
        echo '                        <tr'.($earned ? ' class="wsa-earned"' : '').'><td><div class="wsa-check" style="float:left;margin:0 3px 0 0">&nbsp;</div><a href="?privilege='.$id.'">'.$name.'</a></td><td class="number-right"><span>'.Lang::nf($value)."</span></td></tr>\n";
    endforeach;
?>
                    </tbody>
                </table>
            </div>
            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
