<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    $this->brick('header');

    $iconOffset = 0;
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

                <h1 class="h1-icon"><?=$this->h1; ?></h1>

<?php
$this->brick('tooltip');

if ($this->tools):
    echo "                <div style=\"float: left; margin-right: 75px\">\n";
endif;

if ($this->reagents[1]):
    $iconOffset += count($this->reagents[1]);
    $this->brick('reagentList', ['reagents' => $this->reagents[1], 'enhanced' => $this->reagents[0]]);
endif;

if ($this->tools):
    echo "                </div>\n";

    if ($this->reagents[0]):
        echo "                <div style=\"float: left\">\n";
    endif;
?>
                <h3><?=Lang::spell('tools'); ?></h3>
                <table class="iconlist">
<?php
    foreach ($this->tools as $icon):
        echo $icon->renderContainer(20, $iconOffset, true);
    endforeach;
?>
                </table>
                <script type="text/javascript">
<?php
    foreach ($this->tools as $icon):
        echo $icon->renderJS(20);
    endforeach;
?>
                </script>
<?php
    if ($this->reagents[0]):
        echo "                </div>\n";
    endif;
endif;
?>
                <div class="clear"></div>

<?php
$this->brick('markup', ['markup' => $this->article]);

if ($this->transfer):
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
                                <td width="100%" style="border-top: 0"><?=($this->duration ?: '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            <tr>
                                <th style="border-left: 0"><?=Lang::game('school'); ?></th>
                                <td width="100%" style="border-top: 0"><?=($this->school ?: '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            <tr>
                                <th style="border-left: 0"><?=Lang::game('mechanic');?></th>
                                <td width="100%" style="border-top: 0"><?=($this->mechanic ?:'<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            <tr>
                                <th style="border-left: 0"><?=Lang::game('dispelType');?></th>
                                <td width="100%" style="border-top: 0"><?=($this->dispel ?: '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            <tr>
                                <th style="border-bottom: 0; border-left: 0"><?=Lang::spell('_gcdCategory');?></th>
                                <td style="border-bottom: 0"><?=($this->gcdCat ?: '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                            </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th style="border-top: 0"><?=Lang::spell('_cost');?></th>
                        <td style="border-top: 0"><?=($this->powerCost ?: Lang::spell('_none'));?></td>
                    </tr>
                    <tr>
                        <th><?=Lang::spell('_range');?></th>
                        <td><?=$this->range.' '.Lang::spell('_distUnit').' <small>('.$this->rangeName.')</small>';?></td>
                    </tr>
                    <tr>
                        <th><?=Lang::spell('_castTime');?></th>
                        <td><?=$this->castTime;?></td>
                    </tr>
                    <tr>
                        <th><?=Lang::spell('_cooldown');?></th>
                        <td><?=($this->cooldown ?: '<span class="q0">'.Lang::main('n_a').'</span>');?></td>
                    </tr>
                    <tr>
                        <th><dfn title="<?=Lang::spell('_globCD').'">'.Lang::spell('_gcd');?></dfn></th>
                        <td><?=$this->gcd;?></td>
                    </tr>
<?php
if ($this->stances):
?>
                    <tr>
                        <th><?=Lang::spell('_forms');?></th>
                        <td colspan="3"><?=$this->stances;?></td>
                    </tr>
<?php
endif;

if ($this->items):
?>
                    <tr>
                        <th><?=Lang::game('requires2');?></th>
                        <td colspan="3"><?=$this->items;?></td>
                    </tr>
<?php
endif;

$iconTabIdx = 0;
foreach ($this->effects as $i => $e):
?>
                    <tr>
                        <th><?=Lang::spell('_effect').' #'.$i;?></th>
                        <td colspan="3" style="line-height: 17px">
<?php
    echo '                            '.$e['name'];

    if ($e['footer']):
        echo "<small><br />".implode("<br />", $e['footer'])."</small>\n";
    endif;

    if ($e['markup']):
        echo '<br/><div id="spelleffectmarkup-'.$i.'" style="display: inline-block"></div><script type="text/javascript">//<![CDATA[
$WH.aE(window,\'load\',function(){$WH.ge(\'spelleffectmarkup-'.$i.'\').innerHTML = Markup.toHtml(\''.$e['markup'].'\');});
//]]></script>';
    endif;

    if ($e['icon']):
?>
                            <table class="icontab">
                                <tr>
                                    <?=$e['icon']->renderContainer(iconIdxOffset: $iconTabIdx); ?>
                                    <th></th><td></td>
                                </tr>
                            </table>
                            <script type="text/javascript">
                                <?=$e['icon']->renderJS(); ?>
                            </script>
<?php
    endif;

    if ($e['perfectItem']):
        ['spellId' => $si, 'spellName' => $sn, 'item' => $it, 'icon' => $ic, 'chance' => $ch] = $e['perfectItem'];
?>
                            <small><a href="?spell=<?=$si;?>" class="icontiny"><img src="<?=$this->gStaticUrl;?>/images/wow/icons/tiny/<?=$ic;?>.gif" align="absmiddle">
                                <span class="tinyicontxt"><?=$sn;?></span></a><?=Lang::main('colon').' '.$ch.'%';?></small><table class="icontab">
                            <?=$it->renderContainer(0, $iconTabIdx, true); ?></table>

                            <script type="text/javascript">//<![CDATA[
                                <?=$it->renderJS(); ?>
                            //]]></script>

<?php
    endif;

    if ($e['modifies']):
?>
                            <br /><small><?=Lang::spell('_affected');?></small>
<?php
        for ($type = 0; $type < 2; $type++):                // [classSpells, miscSpells]
            if (!$e['modifies'][$type])
                continue;

            $folded   = false;

            if ($type && count($e['modifies'][0]))          // #effectspells-856451 < the number is ID from SpellEffect.db2 (not available in 3.3.5a, use effectIdx instead)
                echo '<a href="javascript:" class="disclosure-off" onclick="return g_disclose($(\'#effectspells-'.$i.'\')[0], this);">'.Lang::spell('_seeMore').'</a><div id="effectspells-'.$i.'" style="display: none">';

            echo '<table class="icontab">';

            foreach ($e['modifies'][$type] as $idx => [$icon, $ranks]):
                if (!$idx || !($idx % 3))
                    echo "<tr".($folded ? ' style="display:none;"' : '').">";

                $icon->renderContainer(iconIdxOffset: $iconTabIdx); // just to assign iconOffset
                echo "<th id=\"icontab-icon".$iconTabIdx."\"></th><td><a href=\"?spell=".$icon->typeId."\">".($type ? $icon->text : "<b>".$icon->text."</b>")."</a>".($ranks ? "<br /><small>(".Lang::spell('_rankRange', $ranks).")</small>" : '')."</td>\n";

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
            foreach ($e['modifies'][$type] as [$icon, ])
                echo $icon->renderJS(32);
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

if ($this->attributes):
?>
                    <tr>
                        <th><?=Lang::game('flags');?></th>
                            <td colspan="3" style="line-height:17px">
                                <ul style="margin:0"><?php
    foreach ($this->attributes as $a):
        echo '<li>'.$a.'</li>';
    endforeach;
?></ul>
                        </td>
                    </tr>
<?php endif; ?>
                </table>

                <h2 class="clear"><?=Lang::main('related');?></h2>
            </div>

<?php
$this->brick('lvTabs');

$this->brick('contribute');
?>
            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
