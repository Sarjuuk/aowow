<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

    $this->brick('infobox');
?>

            <div class="text">
<?php
$this->brick('redButtons');

if ($this->expansion):
    echo '                <h1 class="h1-icon"><span class="icon-'.$this->expansion.'-right">'.$this->name."</span></h1>\n";
else:
    echo '                <h1>'.$this->name."</h1>\n";
endif;
if ($this->unavailable):
?>
                <div class="pad"></div>
                <b style="color: red"><?=Lang::itemset('_unavailable'); ?></b>
                <div class="pad"></div>
<?php
endif;
$this->brick('article');

echo $this->description;
?>
                <script type="text/javascript">//<![CDATA[
<?php
foreach ($this->pieces as $iId => $piece):
    echo "                    g_items.add(".$iId.", ".Util::toJSON($piece).");\n";
endforeach;
?>
                //]]></script>

                <table class="iconlist">
<?php
$idx = 0;
foreach ($this->pieces as $iId => $piece):
    echo '                    <tr><th align="right" id="iconlist-icon'.(++$idx).'"></th><td><span class="q'.$piece['quality'].'"><a href="?item='.$iId.'">'.$piece['name_'.User::$localeString]."</a></span></td></tr>\n";
endforeach;
?>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
$idx = 0;
foreach ($this->pieces as $iId => $__):
    echo "                    \$WH.ge('iconlist-icon".(++$idx)."').appendChild(g_items.createIcon(".$iId.", 0, 0));\n";
endforeach;
?>
                //]]></script>

                <h3><?=Lang::itemset('_setBonuses').$this->bonusExt; ?></h3>

<?="                ".Lang::itemset('_conveyBonus')."\n"; ?>
                <ul>
<?php
foreach ($this->spells as $i => $s):
    echo '                    <li><div>'.$s['bonus'].' '.Lang::itemset('_pieces').Lang::main('colon').'<a href="?spell='.$s['id'].'">'.$s['desc']."</a></div></li>\n";
endforeach;
?>
                </ul>
<?php
if ($this->summary):
?>

                <h2 class="clear"><?=Lang::itemset('summary'); ?></h2>

                <div id="summary-generic"></div>
                <script type="text/javascript">//<![CDATA[
                    new Summary(<?=Util::toJSON($this->summary); ?>);
                //]]></script>
<?php
endif;
?>

                <h2 class="clear"><?=Lang::main('related'); ?></h2>
            </div>

<?php
$this->brick('lvTabs', ['relTabs' => true]);

$this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
