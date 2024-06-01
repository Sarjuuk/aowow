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

                <h1 class="h1-icon"><?=$this->name; ?></h1>

<?php
$this->brick('tooltip');

if ($this->reagents[1]):
    if ($this->tools):
        echo "                <div style=\"float: left; margin-right: 75px\">\n";
    endif;

    $this->brick('reagentList', ['reagents' => $this->reagents[1], 'enhanced' => $this->reagents[0]]);

    if ($this->tools):
        echo "                </div>\n";

        if ($this->reagents[0]):
            echo "                <div style=\"float: left\">\n";
        endif;
?>
                <h3><?=Lang::spell('tools'); ?></h3>
                <table class="iconlist">
<?php
        foreach ($this->tools as $i => $t):
            echo '                    <tr><th align="right" id="iconlist-icon'.($i + 1).'"></th><td><span class="q1"><a href="'.$t['url'].'">'.$t['name']."</a></span></td></tr>\n";
        endforeach;
?>
                </table>
                <script type="text/javascript">
<?php
        foreach ($this->tools as $i => $t):
            if (isset($t['itemId'])):
                echo "                    \$WH.ge('iconlist-icon.".($i + 1)."').appendChild(g_items.createIcon(".$t['itemId'].", 0, 1));\n";
            endif;
        endforeach;
?>
                </script>
<?php
        if ($this->reagents[0]):
            echo "                </div>\n";
        endif;
    endif;
endif;
?>
                <div class="clear"></div>

<?php $this->brick('article'); ?>

<?php
if (!empty($this->transfer)):
    echo "    <div class=\"pad\"></div>\n    ".$this->transfer."\n";
endif;
?>

                <h3><?=Lang::spell('_spellDetails'); ?></h3>

                <table class="grid" id="spelldetails">
                    <colgroup>
                        <col width="8%" />
                        <col width="42%" />
                        <col width="50%" />
                    </colgroup>
                    <tr>
                        <td colspan="2" style="padding: 0; border: 0; height: 1px"></td>
                        <td rowspan="6" style="padding: 0; border-left: 3px solid #404040">
                            <table class="grid" style="border: 0">
                            <tr>
                                <td style="height: 0; padding: 0; border: 0" colspan="2"></td>
                            </tr>
                            <tr>
                                <th style="border-left: 0; border-top: 0"><?=Lang::game('duration');?></th>
                                <td width="100%" style="border-top: 0"><?=(!empty($this->duration) ? $this->duration : '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            <tr>
                                <th style="border-left: 0"><?=Lang::game('school'); ?></th>
                                <td width="100%" style="border-top: 0"><?=(!empty($this->school[1]) ? (User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, $this->school[0], $this->school[1]) : $this->school[1]) : '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            <tr>
                                <th style="border-left: 0"><?=Lang::game('mechanic');?></th>
                                <td width="100%" style="border-top: 0"><?=(!empty($this->mechanic) ? $this->mechanic : '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            <tr>
                                <th style="border-left: 0"><?=Lang::game('dispelType');?></th>
                                <td width="100%" style="border-top: 0"><?=(!empty($this->dispel) ? $this->dispel : '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            <tr>
                                <th style="border-bottom: 0; border-left: 0"><?=Lang::spell('_gcdCategory');?></th>
                                <td style="border-bottom: 0"><?=(!empty($this->gcdCat) ? $this->gcdCat : '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th style="border-top: 0"><?=Lang::spell('_cost');?></th>
                        <td style="border-top: 0"><?=(!empty($this->powerCost) ? $this->powerCost : Lang::spell('_none'));?></td>
                    </tr>
                    <tr>
                        <th><?=Lang::spell('_range');?></th>
                        <td><?=$this->range.' '.Lang::spell('_distUnit').' <small>('.$this->rangeName;?>)</small></td>
                    </tr>
                    <tr>
                        <th><?=Lang::spell('_castTime');?></th>
                        <td><?=$this->castTime;?></td>
                    </tr>
                    <tr>
                        <th><?=Lang::spell('_cooldown');?></th>
                        <td><?=(!empty($this->cooldown) ? $this->cooldown : '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                    </tr>
                    <tr>
                        <th><dfn title="<?=Lang::spell('_globCD').'">'.Lang::spell('_gcd');?></dfn></th>
                        <td><?=$this->gcd;?></td>
                    </tr>
<?php
// not default values
if (!in_array(array_values($this->scaling), [[-1, -1, 0, 0], [0, 0, 0, 0]])):
?>
                    <tr>
                        <th><?=Lang::spell('_scaling');?></th>
                        <td colspan="3">

<?php
    foreach ($this->scaling as $k => $s):
        if ($s > 0):
            echo '                            '.sprintf(Lang::spell('scaling', $k), $s * 100)."<br>\n";
        endif;
    endforeach;
?>
                        </td>
                    </tr>
<?php
endif;

if (!empty($this->stances)):
?>
                    <tr>
                        <th><?=Lang::spell('_forms');?></th>
                        <td colspan="3"><?=$this->stances;?></td>
                    </tr>
<?php
endif;

if (!empty($this->items)):
?>
                    <tr>
                        <th><?=Lang::game('requires2');?></th>
                        <td colspan="3"><?=(User::isInGroup(U_GROUP_STAFF) ? sprintf(Util::$dfnString, implode('<br />', $this->items[0]), $this->items[1]) : $this->items[1]);?></td>
                    </tr>
<?php
endif;

$iconTabIdx = -1;
foreach ($this->effects as $i => $e):
?>
                    <tr>
                        <th><?=Lang::spell('_effect').' #'.$i;?></th>
                        <td colspan="3" style="line-height: 17px">
<?php
    echo '                            '.$e['name'];

    $smallBuf = '';
    if (isset($e['value'])):
        $smallBuf .= '<br>'.Lang::spell('_value').Lang::main('colon').$e['value'];
    endif;

    if (isset($e['radius'])):
        $smallBuf .= '<br>'.Lang::spell('_radius').Lang::main('colon').$e['radius'].' '.Lang::spell('_distUnit');
    endif;

    if (isset($e['interval'])):
        $smallBuf .= '<br>'.Lang::spell('_interval').Lang::main('colon').$e['interval'];
    endif;

    if (isset($e['mechanic'])):
        $smallBuf .= '<br>'.Lang::game('mechanic')  .Lang::main('colon').$e['mechanic'];
    endif;

    if (isset($e['procData'])):
        $smallBuf .= '<br>';

        if ($e['procData'][0] < 0):
            $smallBuf .= sprintf(Lang::spell('ppm'), Lang::nf(-$e['procData'][0], 1));
        elseif ($e['procData'][0] < 100.0):
            $smallBuf .= Lang::spell('procChance').Lang::main('colon').$e['procData'][0].'%';
        endif;

        if ($e['procData'][1]):
            if ($e['procData'][0] < 100.0):
                $smallBuf .= '<br>';
            endif;
            $smallBuf .= sprintf(Lang::game('cooldown'), $e['procData'][1]);
        endif;
    endif;

    if ($smallBuf):
        echo "<small>".$smallBuf."</small>\n";
    endif;

    if (isset($e['markup'])):
        echo '<br/><div id="spelleffectmarkup-'.$i.'" style="display: inline-block"></div><script type="text/javascript">//<![CDATA[
$WH.aE(window,\'load\',function(){$WH.ge(\'spelleffectmarkup-'.$i.'\').innerHTML = Markup.toHtml(\''.$e['markup'].'\');});
//]]></script>';
    endif;

    if (isset($e['icon'])):
?>
                            <table class="icontab">
                                <tr>
                                    <th id="icontab-icon<?=++$iconTabIdx;?>"></th>
<?php
        if (isset($e['icon']['quality'])):
            echo '                                    <td><span class="q'.$e['icon']['quality'].'"><a href="?item='.$e['icon']['id'].'">'.$e['icon']['name']."</a></span></td>\n";
        else:
            echo '                                    <td>'.(strpos($e['icon']['name'], '#') ? $e['icon']['name'] : sprintf('<a href="?spell=%d">%s</a>', $e['icon']['id'], $e['icon']['name']))."</td>\n";
        endif;
?>
                                    <th></th><td></td>
                                </tr>
                            </table>
                            <script type="text/javascript">
                                <?='$WH.ge(\'icontab-icon'.$iconTabIdx.'\').appendChild('.(isset($e['icon']['quality']) ? 'g_items' : 'g_spells').'.createIcon('.$e['icon']['id'].', 1, '.$e['icon']['count']."));\n";?>
                            </script>
<?php
    endif;

    if (isset($e['perfItem'])):
?>
                            <small><a href="?spell=<?=$e['perfItem']['cndSpellId'];?>" class="icontiny"><img src="<?=Cfg::get('STATIC_URL');?>/images/wow/icons/tiny/<?=$e['perfItem']['icon'];?>.gif" align="absmiddle">
                                <span class="tinyicontxt"><?=$e['perfItem']['cndSpellName'];?></span></a><?=Lang::main('colon').' '.$e['perfItem']['chance'].'%';?></small><table class="icontab">
                            <tr><th id="icontab-icon<?=++$iconTabIdx;?>"></th><td><small><a href="?item=<?=$e['perfItem']['itemId'];?>" class="q<?=$e['perfItem']['quality'];?>"><?=$e['perfItem']['itemName'];?></a></small></td></tr></table>

                            <script type="text/javascript">//<![CDATA[
                                $WH.ge('icontab-icon<?=$iconTabIdx;?>').appendChild(g_items.createIcon(<?=$e['perfItem']['itemId'];?>, 0, "0"));
                            //]]></script>

<?php
    endif;

    if (isset($e['modifies'])):
?>
                            <br><small><?=Lang::spell('_affected').Lang::main('colon');?></small>
<?php
        for ($type = 0; $type < 2; $type++):
            if (!$e['modifies'][$type])
                continue;

            $folded   = false;
            $iconData = [];

            if ($type && count($e['modifies'][0]))
                echo '<a href="javascript:" class="disclosure-off" onclick="return g_disclose($(\'#effectspells-85645'.($i - 1).'\')[0], this);">'.Lang::spell('_seeMore').'</a><div id="effectspells-85645'.($i - 1).'" style="display: none">';

            echo '<table class="icontab">';

            foreach ($e['modifies'][$type] as $idx => [$id, $name, $minRank, $maxRank]):
                if (!$idx || !($idx % 3))
                    echo "<tr".($folded ? ' style="display:none;"' : '').">";

                $iconData[] = [++$iconTabIdx, $id];
                echo "<th id=\"icontab-icon".$iconTabIdx."\"></th><td><a href=\"?spell=".$id."\">".($type ? $name : "<b>".$name."</b>")."</a>".($minRank != $maxRank ? "<br><small>(".Lang::spell('_rankRange', [$minRank, $maxRank]).")</small>" : '')."</td>\n";

                if ($idx == count($e['modifies'][$type]) - 1 || !(($idx + 1) % 3))
                    echo "</tr>";

                if ($idx == 17 && count($e['modifies'][$type]) > 21):
                    $folded = true;
?>
                <tr class="icontab-revealer">
                    <td colspan="6">
                        <a onclick="$(this).closest('table').addClass('show-all')">
                            <?=Lang::spell('_showXmore', [count($e['modifies'][$type]) - 18]); ?>
                        </a>
                    </td>
                </tr>
<?php
                endif;

            endforeach;
?>
                            </table>

                            <script type="text/javascript">//<![CDATA[
<?php
            foreach ($iconData as [$icon, $spell])
                echo sprintf("                                \$WH.ge('icontab-icon%d').appendChild(g_spells.createIcon(%d, 0, \"0\"));\n", $icon, $spell);
?>
                            //]]></script>

<?php
            if ($type && count($e['modifies'][0]))
                echo '</div>';

        endfor;
    endif;
?>
                        </td>
                    </tr>
<?php
endforeach;
?>
                    <tr>
                        <th><?=Lang::game('flags');?></th>
                            <td colspan="3" style="line-height:17px">
                                <ul style="margin:0"><?php
foreach ($this->attributes as $cr):
    echo '<li><a href="?spells&filter=cr='.$cr.';crs=1;crv=0">'.Lang::spell('attributes', $cr).'</a></li>';
endforeach;
?></ul>
                        </td>
                    </tr>
                </table>

                <h2 class="clear"><?=Lang::main('related');?></h2>
            </div>

<?php
$this->brick('lvTabs', ['relTabs' => true]);

$this->brick('contribute');
?>
            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
