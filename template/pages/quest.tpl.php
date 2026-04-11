<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    /** @var PageTemplate $this */

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

                <h1><?=$this->h1; ?></h1>

<?php if ($this->unavailable): ?>
                <div class="pad"></div>
                <b style="color: red"><?=Lang::quest('unavailable'); ?></b>
                <div class="pad"></div>

<?php
endif;

if ($this->objectives):
    echo $this->objectives.PHP_EOL;
elseif ($this->requestItems):
    echo '                <h3>'.Lang::quest('progress').'</h3>'.PHP_EOL;
    echo $this->requestItems.PHP_EOL;
elseif ($this->offerReward):
    echo '                <h3>'.Lang::quest('completion').'</h3>'.PHP_EOL;
    echo $this->offerReward.PHP_EOL;
endif;

$iconOffset = 0;
if ($this->end || $this->objectiveList):
?>

                <table class="iconlist">

<?php
    foreach ($this->objectiveList as $objective):
        if (is_string($objective)):                         // just text line
            echo '                    <tr><th><p style="height: 26px; width: 30px;">&nbsp;</p></th><td>'.$objective.'</td></tr>'.PHP_EOL;
        elseif (is_array($objective)):                      // proxy npc data
            ['id' => $id, 'text' => $text, 'qty' => $qty, 'proxy' => $proxies] = $objective;
            echo '                    <tr><th><p style="height: 26px">&nbsp;</p></th><td><a href="javascript:;" onclick="g_disclose($WH.ge(\'npcgroup-'.$id.'\'), this)" class="disclosure-off">'.$text.'</a>'.($qty ? '&nbsp;('.$qty.')' : '').'<div id="npcgroup-'.$id.'" style="display: none">'.PHP_EOL;
            foreach ($proxies as $block):
                echo '                        <div style="float: left"><table class="iconlist">'.PHP_EOL;
                foreach ($block as $pId => $pName):
                    echo '                            <tr><th><ul><li><var>&nbsp;</var></li></ul></th><td><a href="?npc='.$pId.'">'.$pName.'</a></td></tr>'.PHP_EOL;
                endforeach;
                echo '                        </table></div>'.PHP_EOL;
            endforeach;
            echo '                    </div></td></tr>'.PHP_EOL;
        elseif (is_object($objective)):                     // has icon set (spell / item / ...) or unordered linked list
            echo $objective?->renderContainer(20, $iconOffset, true);
        endif;
    endforeach;

    if ($this->end):
        echo '                    <tr><th><p style="height: 26px; width: 30px;">&nbsp;</p></th><td>'.$this->end.'</td></tr>'.PHP_EOL;
    endif;

    if ($this->suggestedPl):
        echo '                    <tr><th><p style="height: 26px; width: 30px;">&nbsp;</p></th><td>'.Lang::quest('suggestedPl', [$this->suggestedPl]).'</td></tr>'.PHP_EOL;
    endif;
?>

                </table>

                <script type="text/javascript">//<![CDATA[

<?php
    foreach (array_filter($this->objectiveList, fn($x) => is_object($x)) as $k => $objective):
        echo $objective?->renderJS();
    endforeach;
?>

                //]]></script>

<?php
    if ($this->providedItem):
?>

                <div class="pad"></div>
                <?=Lang::quest('providedItem').Lang::main('colon'); ?>
                <table class="iconlist">
                    <?=$this->providedItem->renderContainer(20, $iconOffset, true); ?>
                </table>

                <script type="text/javascript">//<![CDATA[
                    <?=$this->providedItem->renderJS(); ?>
                //]]></script>

<?php
    endif;
endif;

$this->brick('mapper');

if ($this->details):
    echo '                <h3>'.Lang::quest('description').'</h3>'.PHP_EOL;
    echo '                '.$this->details.PHP_EOL;
endif;

if ($this->requestItems && $this->objectives):
?>

                <h3><a href="javascript:;" class="disclosure-off" onclick="return g_disclose($WH.ge('disclosure-progress'), this)"><?=Lang::quest('progress'); ?></a></h3>
                <div id="disclosure-progress" style="display: none"><?=$this->requestItems; ?></div>

<?php
endif;

if ($this->offerReward && ($this->requestItems || $this->objectives)):
?>

                <h3><a href="javascript:;" class="disclosure-off" onclick="return g_disclose($WH.ge('disclosure-completion'), this)"><?=Lang::quest('completion'); ?></a></h3>
                <div id="disclosure-completion" style="display: none"><?=$this->offerReward; ?></div>

<?php
endif;

if ([$spells, $items, $choice, $money] = $this->rewards):
    echo '                <h3>'.Lang::main('rewards').'</h3>'.PHP_EOL;

    if ($choice):
        $this->brick('rewards', ['rewTitle' => Lang::quest('rewardChoices'), 'rewards' => $choice, 'offset' => $iconOffset]);
        $iconOffset += count($choice);
    endif;

    if ($spells):
        if ($choice):
            echo '                        <div class="pad"></div>'.PHP_EOL;
        endif;

        $this->brick('rewards', ['rewTitle' => $spells['title'], 'rewards' => $spells['cast'], 'offset' => $iconOffset, 'extra' => $spells['extra']]);
        $iconOffset += count($spells['cast']);
    endif;

    if ($items || $money):
        if ($choice || $spells):
            echo '                        <div class="pad"></div>'.PHP_EOL;
        endif;

        $this->brick('rewards', array(
            'rewTitle' => $choice ? Lang::quest('rewardAlso') : Lang::quest('rewardItems'),
            'rewards'  => $items ?: null,
            'offset'   => $iconOffset,
            'extra'    => $money ?: null
        ));
    endif;

endif;

if ([$xp, $rep, $title, $tp, $honor, $arena] = $this->gains):
?>

                    <h3><?=Lang::main('gains'); ?></h3>
                    <?=Lang::quest('gainsDesc').Lang::main('colon'); ?>
                    <ul>

<?php
    if ($xp):
        echo '                        <li><div>'.Lang::nf($xp).' '.Lang::quest('experience').'</div></li>'.PHP_EOL;
    endif;

    if ($rep):
        foreach ($rep as $r):
            echo '                        <li><div>'.sprintf($r['qty'][0] < 0 ? '<b class="q10">%s</b>' : '%s', $r['qty'][1]).' '.Lang::npc('repWith').' <a href="?faction='.$r['id'].'">'.$r['name'].'</a></div></li>'.PHP_EOL;
        endforeach;
    endif;

    if ($title):
        echo '                        <li><div>'.Lang::quest('rewardTitle', $title).'</div></li>'.PHP_EOL;
    endif;

    if ($tp):
        echo '                        <li><div>'.Lang::quest('bonusTalents', [$tp]).'</div></li>'.PHP_EOL;
    endif;

    if ($arena || $honor):
        echo '                        <li><div>';
        if ($honor[0]):
            $a = '<a href="?currency=104">'.$honor[0].'</a>';
            $a = $honor[1] == SIDE_BOTH ? '<span class="moneyalliance">'.$a.'</span>' : $a;
            echo '<span class="money'.($honor[1] == SIDE_ALLIANCE ? 'alliance' : 'horde').' tip q1" onmouseover="Listview.funcBox.moneyHonorOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">'.$a.'</span>';
        endif;
        if ($arena):
            echo ' <span class="moneyarena tip q1" onmouseover="Listview.funcBox.moneyArenaOver(event)" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><a href="?currency=103">'.$arena.'</a></span>';
        endif;
        echo                         '</div></li>'.PHP_EOL;
    endif;

    echo '                    </ul>'.PHP_EOL;
endif;

$this->brickIf($this->mail, 'mail', ['offset' => ++$iconOffset]);

if ($this->transfer):
    echo '    <div style="clear: left"></div>'.PHP_EOL;
    echo '    <div class="pad"></div>'.PHP_EOL;
    echo '    '.$this->transfer.PHP_EOL;
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
