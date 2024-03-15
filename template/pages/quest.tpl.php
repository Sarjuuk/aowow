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

                <h1><?=$this->name; ?></h1>
<?php if ($this->unavailable): ?>
                <div class="pad"></div>
                <b style="color: red"><?=Lang::quest('unavailable'); ?></b>
                <div class="pad"></div>
<?php
endif;

if ($this->objectives):
    echo $this->objectives."\n";
elseif ($this->requestItems):
    echo '                <h3>'.Lang::quest('progress')."</h3>\n";
    echo $this->requestItems."\n";
elseif ($this->offerReward):
    echo '                <h3>'.Lang::quest('completion')."</h3>\n";
    echo $this->offerReward."\n";
endif;

if ($this->end || $this->objectiveList):
?>
                <table class="iconlist">
<?php
    if ($this->end):
        echo "                    <tr><th><p style=\"height: 26px; width: 30px;\">&nbsp;</p></th><td>".$this->end."</td></tr>\n";
    endif;

    if ($o = $this->objectiveList):
        foreach ($o as $i => $ol):
            if (isset($ol['text'])):
                echo '                    <tr><th><p style="height: 26px; width: 30px;">&nbsp;</p></th><td>'.$ol['text']."</td></tr>\n";
            elseif (!empty($ol['proxy'])):                      // this implies creatures
                echo '                    <tr><th><p style="height: 26px">&nbsp;</p></th><td><a href="javascript:;" onclick="g_disclose($WH.ge(\'npcgroup-'.$ol['id'].'\'), this)" class="disclosure-off">'.$ol['name'].$ol['extraText'].'</a>'.($ol['qty'] > 1 ? '&nbsp;('.$ol['qty'].')' : null).'<div id="npcgroup-'.$ol['id']."\" style=\"display: none\">\n";

                $block1 = array_slice($ol['proxy'], 0, ceil(count($ol['proxy']) / 2), true);
                $block2 = array_slice($ol['proxy'], ceil(count($ol['proxy']) / 2), null, true);

                echo "                        <div style=\"float: left\"><table class=\"iconlist\">\n";
                foreach ($block1 as $pId => $name):
                                echo '                            <tr><th><ul><li><var>&nbsp;</var></li></ul></th><td><a href="?npc='.$pId.'">'.$name."</a></td></tr>\n";
                endforeach;
                echo "                        </table></div>\n";

                if ($block2):                                   // may be empty
                    echo "                        <div style=\"float: left\"><table class=\"iconlist\">\n";
                    foreach ($block2 as $pId => $name):
                                    echo '                            <tr><th><ul><li><var>&nbsp;</var></li></ul></th><td><a href="?npc='.$pId.'">'.$name."</a></td></tr>\n";
                    endforeach;
                    echo "                        </table></div>\n";
                endif;

                echo "                    </div></td></tr>\n";
            elseif (isset($ol['typeStr'])):
                if (in_array($ol['typeStr'], ['item', 'spell'])):
                    echo '                    <tr><th align="right" id="iconlist-icon-'.$i.'"></th>';
                else /* if (in_array($ol['typeStr'], ['npc', 'object', 'faction'])) */:
                    echo '                    <tr><th><ul><li><var>&nbsp;</var></li></ul></th>';
                endif;

                echo '<td><span class="q'.(isset($ol['quality']) ? $ol['quality'] : null).'"><a href="?'.$ol['typeStr'].'='.$ol['id'].'">'.$ol['name'].'</a></span>'.($ol['extraText']).(!empty($ol['qty']) ? '&nbsp;('.$ol['qty'].')' : null)."</td></tr>\n";
            endif;
        endforeach;
    endif;

    if ($this->suggestedPl):
        echo '                    <tr><th><p style="height: 26px; width: 30px;">&nbsp;</p></th><td>'.Lang::quest('suggestedPl').Lang::main('colon').$this->suggestedPl."</td></tr>\n";
    endif;
?>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
    foreach ($o as $k => $i):
        if (isset($i['typeStr']) && ($i['typeStr'] == 'item' || $i['typeStr'] == 'spell')):
            echo "                    \$WH.ge('iconlist-icon-".$k."').appendChild(g_".$i['typeStr']."s.createIcon(".$i['id'].", 0, ".$i['qty']."));\n";
        endif;
    endforeach;
?>
                //]]></script>
<?php
    if ($p = $this->providedItem):
        echo "                <div class=\"pad\"></div>\n";
        echo '                '.Lang::quest('providedItem').Lang::main('colon')."\n";
        echo "                <table class=\"iconlist\">\n";
        echo '                    <tr><th align="right" id="iconlist-icon-'.count($this->objectiveList).'"></th>';
        echo '<td><span class="q'.$p['quality'].'"><a href="?item='.$p['id'].'">'.$p['name'].'</a></span>'.($p['qty'] ? '&nbsp;('.$ol['qty'].')' : null)."</td></tr>\n";
?>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
        echo "                    \$WH.ge('iconlist-icon-".count($this->objectiveList)."').appendChild(g_items.createIcon(".$p['id'].", 0, ".$p['qty']."));\n";
?>
                //]]></script>
<?php
    endif;
endif;

$this->brick('mapper');

if ($this->details):
    echo '                <h3>'.Lang::quest('description')."</h3>\n" . $this->details."\n";
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

$offset = 0;
if ($r = $this->rewards):
    echo '                <h3>'.Lang::main('rewards')."</h3>\n";

    if (!empty($r['choice'])):
        $this->brick('rewards', ['rewTitle' => Lang::quest('chooseItems'), 'rewards' => $r['choice'], 'offset' => $offset]);
        $offset += count($r['choice']);
    endif;

    if (!empty($r['spells'])):
        if (!empty($r['choice'])):
            echo "                        <div class=\"pad\"></div>\n";
        endif;

        if (!empty($r['spells']['learn'])):
            $this->brick('rewards', ['rewTitle' => Lang::quest('spellLearn'), 'rewards' => $r['spells']['learn'], 'offset' => $offset, 'extra' => $r['spells']['extra']]);
            $offset += count($r['spells']['learn']);
        elseif (!empty($r['spells']['cast'])):
            $this->brick('rewards', ['rewTitle' => Lang::quest('spellCast'), 'rewards' => $r['spells']['cast'], 'offset' => $offset, 'extra' => $r['spells']['extra']]);
            $offset += count($r['spells']['cast']);
        endif;
    endif;

    if (!empty($r['items']) || !empty($r['money'])):
        if (!empty($r['choice']) || !empty($r['spells'])):
            echo "                        <div class=\"pad\"></div>\n";
        endif;

        $addData = ['rewards' => !empty($r['items']) ? $r['items'] : null, 'offset' => $offset, 'extra' => !empty($r['money']) ? $r['money'] : null];
        $addData['rewTitle'] = empty($r['choice']) ? Lang::quest('receiveItems') : Lang::quest('receiveAlso');

        $this->brick('rewards', $addData);
    endif;

endif;

if ($g = $this->gains):
    echo '                    <h3>'.Lang::main('gains')."</h3>\n";
    echo '                    '.Lang::quest('gainsDesc').Lang::main('colon')."\n";
    echo "                    <ul>\n";

    if (!empty($g['xp'])):
        echo '                        <li><div>'.Lang::nf($g['xp']).' '.Lang::quest('experience')."</div></li>\n";
    endif;

    if (!empty($g['rep'])):
        foreach ($g['rep'] as $r):
            if ($r['qty'][1] && User::isInGroup(U_GROUP_EMPLOYEE))
                $qty = $r['qty'][0] . sprintf(Util::$dfnString, Lang::faction('customRewRate'), ($r['qty'][1] > 0 ? '+' : '').$r['qty'][1]);
            else
                $qty = array_sum($r['qty']);

            echo '                        <li><div>'.($r['qty'][0] < 0 ? '<b class="q10">'.$qty.'</b>' : $qty).' '.Lang::npc('repWith').' <a href="?faction='.$r['id'].'">'.$r['name']."</a></div></li>\n";
        endforeach;
    endif;

    if (!empty($g['title'])):
        echo '                        <li><div>'.Lang::quest('theTitle', [$g['title']])."</div></li>\n";
    endif;

    if (!empty($g['tp'])):
        echo '                        <li><div>'.Lang::quest('bonusTalents', [$g['tp']])."</div></li>\n";
    endif;

    echo "                    </ul>\n";
endif;

$this->brick('mail', ['offset' => ++$offset]);

if (!empty($this->transfer)):
    echo "    <div style=\"clear: left\"></div>";
    echo "    <div class=\"pad\"></div>\n    ".$this->transfer."\n";
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
