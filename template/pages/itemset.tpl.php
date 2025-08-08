<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    $this->brick('header');
?>
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
    echo '                <h1 class="h1-icon"><span class="icon-'.$this->expansion.'-right">'.$this->h1."</span></h1>\n";
else:
    echo '                <h1>'.$this->h1."</h1>\n";
endif;
if ($this->unavailable):
?>
                <div class="pad"></div>
                <b style="color: red"><?=Lang::itemset('_unavailable'); ?></b>
                <div class="pad"></div>
<?php
endif;
$this->brick('markup', ['markup' => $this->article]);

echo $this->description;
?>
                <script type="text/javascript">//<![CDATA[
<?php
foreach ($this->pieces as $iId => [$piece, ]):
    echo "                    g_items.add(".$iId.", ".$this->json($piece).");\n";
endforeach;
?>
                //]]></script>

                <table class="iconlist">
<?php
$iconIdx = 0;
foreach ($this->pieces as [, $icon]):
    echo $icon->renderContainer(20, $iconIdx, true);
endforeach;
?>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
foreach ($this->pieces as [, $icon]):
    echo $icon->renderJS(20);
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
                    <?=$this->summary; ?>
                //]]></script>
<?php
endif;
?>

                <h2 class="clear"><?=Lang::main('related'); ?></h2>
            </div>

<?php
$this->brick('lvTabs');

$this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
