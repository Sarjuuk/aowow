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
<?php $this->brick('redButtons'); ?>

                <h1><?=$this->name.($this->subname ? ' &lt;'.$this->subname.'&gt;' : null); ?></h1>

<?php
    $this->brick('article');

if ($this->accessory):
    echo '                <div>'.Lang::npc('accessoryFor').' ';
    echo Lang::concat($this->accessory, true, function ($v, $k) { return '<a href="?npc='.$v[0].'">'.$v[1].'</a>'; });
    echo ".</div>\n";
endif;

if (is_array($this->placeholder)):
    echo '                <div>'.Lang::npc('difficultyPH').' <a href="?npc='.$this->placeholder[0].'">'.$this->placeholder[1]."</a>.</div>\n";
?>
                <div class="pad"></div>
<?php
elseif (!empty($this->map)):
    $this->brick('mapper');
else:
    echo '                '.Lang::npc('unkPosition')."\n";
endif;

if ($this->quotes[0]):
?>
                <h3><a class="disclosure-off" onclick="return g_disclose($WH.ge('quotes-generic'), this)"><?=Lang::npc('quotes').'&nbsp;('.$this->quotes[1]; ?>)</a></h3>
                <div id="quotes-generic" style="display: none"><ul>
<?php
    foreach ($this->quotes[0] as $group):
        if (count($group) > 1 && count($this->quotes[0]) > 1):
            echo "<ul>\n";
        endif;

        echo '<li>';

        $last = end($group);
        foreach ($group as $itr):
            echo sprintf(sprintf($itr['text'], $itr['prefix']), $this->name);
            echo ($itr == $last) ? null : "</li>\n<li>";
        endforeach;

        echo "</li>\n";

        if (count($group) > 1 && count($this->quotes[0]) > 1):
            echo "</ul>\n";
        endif;

    endforeach;
?>
                </ul></div>
<?php
endif;

if ($this->reputation):
?>
                <h3><?=Lang::main('gains'); ?></h3>
<?php
    echo Lang::npc('gainsDesc').Lang::main('colon');

    foreach ($this->reputation as $set):
        if (count($this->reputation) > 1):
            echo '<ul><li><span class="rep-difficulty">'.$set[0].'</span></li>';
        endif;

        echo '<ul>';

        foreach ($set[1] as $itr):
            if ($itr['qty'][1] && User::isInGroup(U_GROUP_EMPLOYEE))
                $qty = intVal($itr['qty'][0]) . sprintf(Util::$dfnString, Lang::faction('customRewRate'), ($itr['qty'][1] > 0 ? '+' : '').intVal($itr['qty'][1]));
            else
                $qty = intVal(array_sum($itr['qty']));

            echo '<li><div'.($itr['qty'][0] < 0 ? ' class="reputation-negative-amount"' : null).'><span>'.$qty.'</span> '.Lang::npc('repWith') .
                ' <a href="?faction='.$itr['id'].'">'.$itr['name'].'</a>'.($itr['cap'] && $itr['qty'][0] > 0 ? '&nbsp;('.sprintf(Lang::npc('stopsAt'), $itr['cap']).')' : null).'</div></li>';
        endforeach;

        echo '</ul>';

        if (count($this->reputation) > 1):
            echo '</ul>';
        endif;
    endforeach;
endif;

if (isset($this->smartAI)):
?>
    <div id="text-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("<?=$this->smartAI; ?>", "text-generic", {
            allow: Markup.CLASS_ADMIN,
            dbpage: true
        });
    //]]></script>

    <div class="pad2"></div>
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
