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
<?php $this->brick('redButtons'); ?>

                <h1><?=$this->h1.($this->subname ? ' &lt;'.$this->subname.'&gt;' : ''); ?></h1>

<?php
    $this->brick('markup', ['markup' => $this->article]);

if ($this->accessory):
    echo '                <div>'.Lang::npc('accessoryFor').' ';
    echo Lang::concat($this->accessory, true, fn ($v) => '<a href="?npc='.$v[0].'">'.$v[1].'</a>');
    echo ".</div>\n";
endif;

if ($this->placeholder):
?>
                <div><?=Lang::npc('difficultyPH', $this->placeholder);?></div>
                <div class="pad"></div>
<?php
elseif ($this->map):
    $this->brick('mapper');
else:
    echo '                '.Lang::npc('unkPosition')."\n";
endif;

if ([$quoteGroups, $count] = $this->quotes):
?>
                <h3><a class="disclosure-off" onclick="return g_disclose($WH.ge('quotes-generic'), this)"><?=Lang::npc('quotes', [$count]); ?></a></h3>
                <div id="quotes-generic" style="display: none"><ul>
<?php
    foreach ($quoteGroups as $group):
        if (count($group) > 1 && count($quoteGroups) > 1):
            echo "<ul>\n";
        endif;

        foreach ($group as $itr):
            echo '<li>'.sprintf($itr['text'], $this->h1)."</li>\n";
        endforeach;

        if (count($group) > 1 && count($quoteGroups) > 1):
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

    foreach ($this->reputation as [$mode, $data]):
        if (count($this->reputation) > 1):
            echo '<ul><li><span class="rep-difficulty">'.$mode.'</span></li>';
        endif;

        echo '<ul>';

        foreach ($data as [$id, $qty, $name, $cap]):
            echo '<li><div'.($qty[0] < 0 ? ' class="reputation-negative-amount"' : '').'><span>'.($qty[1] ?: $qty[0]).'</span> '.Lang::npc('repWith') .
                ' <a href="?faction='.$id.'">'.$name.'</a>'.($cap && $qty[0] > 0 ? '&nbsp;('.Lang::npc('stopsAt', [$cap]).')' : '').'</div></li>';
        endforeach;

        echo '</ul>';

        if (count($this->reputation) > 1):
            echo '</ul>';
        endif;
    endforeach;
endif;

$this->brick('markup', ['markup' => $this->smartAI]);

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
