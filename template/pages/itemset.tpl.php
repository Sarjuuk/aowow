<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php $this->brick('announcement'); ?>

            <script type="text/javascript">//<![CDATA[
<?php
    $this->brick('community');
            echo "                var g_pageInfo = ".json_encode($this->gPageInfo, JSON_NUMERIC_CHECK).";\n" .
                 "                g_initPath(".json_encode($this->path, JSON_NUMERIC_CHECK).");\n";
?>
            //]]></script>

<?php $this->brick('infobox'); ?>

            <div class="text">
<?php
$this->brick('redButtons');

if ($this->expansion):
    echo '                <h1 class="h1-icon"><span class="icon-'.$this->expansion.'-right">'.$this->name."</span></h1>\n";
else:
    echo '                <h1>'.$this->name."</h1>\n";
endif;

$this->brick('article');

echo $this->description;
?>
                <script type="text/javascript">//<![CDATA[
<?php
foreach ($this->pieces as $p):
    echo "                    g_items.add(".$p['id'].", {name_".User::$localeString.":'".Util::jsEscape($p['name'])."', quality:".$p['quality'].", icon:'".$p['icon']."', jsonequip:".json_encode($p['json'])."});\n";
endforeach;
?>
                //]]></script>

                <table class="iconlist">
<?php
foreach ($this->pieces as $i => $p):
    echo '                    <tr><th align="right" id="iconlist-icon'.($i + 1).'"></th><td><span class="q'.$p['quality'].'"><a href="?item='.$p['id'].'">'.$p['name']."</a></span></td></tr>\n";
endforeach;
?>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
foreach ($this->pieces as $i => $p):
    echo "                    \$WH.ge('iconlist-icon".($i + 1)."').appendChild(g_items.createIcon(".$p['id'].", 0, 0));\n";
endforeach;
?>
                //]]></script>

<?php
if ($this->unavailable):
?>
                <div class="pad"></div>
                <b style="color: red"><?php echo Lang::$itemset['_unavailable']; ?></b>
<?php endif; ?>

                <h3><?php echo Lang::$itemset['_setBonuses'].$this->bonusExt; ?></h3>

<?php echo "                ".Lang::$itemset['_conveyBonus']."\n"; ?>
                <ul>
<?php
foreach ($this->spells as $i => $s):
    echo '                    <li><div>'.$s['bonus'].' '.Lang::$itemset['_pieces'].Lang::$main['colon'].'<a href="?spell='.$s['id'].'">'.$s['desc']."</a></div></li>\n";
endforeach;
?>
                </ul>

                <h2 class="clear"><?php echo Lang::$itemset['summary']; ?></h2>

                <div id="summary-generic"></div>
                <script type="text/javascript">//<![CDATA[
                    new Summary({ id: 'itemset', template: 'itemset', parent: 'summary-generic', groups: <?php echo json_encode($this->compare['items'], JSON_NUMERIC_CHECK).', level: '.$this->compare['level']; ?>});
                //]]></script>

                <h2 class="clear"><?php echo Lang::$main['related']; ?></h2>
            </div>

<?php
$this->brick('tabsRelated');

$this->brick('contribute');
?>

        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
