<?php
$this->brick('header');
$f = $this->filter;                                         // shorthand
?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate', ['fi' => empty($f['query']) ? null : ['query' => $f['query'], 'menuItem' => 0]]);
?>

            <div id="fi" style="display: <?=(empty($f['query']) ? 'none' : 'block'); ?>;">
                <form action="?filter=items<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="rightpanel">
                        <div style="float: left"><?=Lang::item('_quality').Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['qu[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="qu[]" size="7" multiple="multiple" class="rightselect" style="background-color: #181818">
<?php
foreach (Lang::item('quality') as $k => $str):
    echo '                            <option value="'.$k.'" class="q'.$k.'"'.(isset($f['qu']) && in_array($k, (array)$f['qu']) ? ' selected' : null).'>'.$str."</option>\n";
endforeach;
?>
                        </select>
                    </div>

<?php
if (!empty($f['slot'])):
?>
                    <div class="rightpanel2">
                        <div style="float: left"><?=Lang::item('slot').Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['sl[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="sl[]" size="<?=min(count($f['slot']), 7); ?>" multiple="multiple" class="rightselect">
<?php
    foreach ($f['slot'] as $k => $str):
        echo '                            <option value="'.$k.'" '.(isset($f['sl']) && in_array($k, (array)$f['sl']) ? ' selected' : null).'>'.$str."</option>\n";
    endforeach;
?>
                        </select>
                    </div>
<?php
endif;

if (!empty($f['type'])):
?>
                    <div class="rightpanel2">
                        <div style="float: left"><?=Lang::game('type').Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['ty[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="ty[]" size="<?=min(count($f['type']), 7); ?>" multiple="multiple" class="rightselect">
<?php
    foreach ($f['type'] as $k => $str):
        $selected = false;
        if (isset($f['ty']) && in_array($k, (array)$f['ty'])):
            $selected = true;
        elseif (isset($this->category[1]) && $this->category[0] == 0 && $this->category[1] == $k):
            $selected = true;
        endif;

        echo '                            <option value="'.$k.'" '.( $selected ? ' selected' : null).'>'.(is_array($str) ? $str[0] : $str)."</option>\n";
    endforeach;
?>
                        </select>
                    </div>
<?php endif; ?>

                    <table>
                        <tr>
                            <td><?=Util::ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="2">&nbsp;<input type="text" name="na" size="30" <?=(isset($f['na']) ? 'value="'.Util::htmlEscape($f['na']).'" ' : null); ?>/></td>
                            <td></td>
                        </tr><tr>
                            <td class="padded"><?=Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="3" class="smalltextbox2" <?=(isset($f['minle']) ? 'value="'.$f['minle'].'" ' : null); ?>/> - <input type="text" name="maxle" maxlength="3" class="smalltextbox2" <?=(isset($f['maxle']) ? 'value="'.$f['maxle'].'" ' : null); ?>/></td>
                            <td class="padded">
                                <table>
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;<?=Lang::main('_reqLevel').Lang::main('colon'); ?></td>
                                        <td>&nbsp;<input type="text" name="minrl" maxlength="2" class="smalltextbox" <?=(isset($f['minrl']) ? 'value="'.$f['minrl'].'" ' : null); ?>/> - <input type="text" name="maxrl" maxlength="2" class="smalltextbox" <?=(isset($f['maxrl']) ? 'value="'.$f['maxrl'].'" ' : null); ?>/></td>
                                    </tr>
                                </table>
                            </td>
                            <td></td>
                        </tr><tr>
                            <td class="padded"><?=Lang::item('usableBy').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="si" style="margin-right: 0.5em">
                                <option></option>
<?php
foreach (Lang::game('si') as $k => $str):
    echo '                            <option value="'.$k.'"'.(isset($f['si']) && $k == $f['si'] ? ' selected' : null).'>'.$str."</option>\n";
endforeach;
?>
                            </select></td>
                            <td class="padded">
                                &nbsp;<select name="ub">
                                    <option></option>
<?php
foreach (Lang::game('cl') as $k => $str):
    if ($str):
        echo '                            <option value="'.$k.'"'.(isset($f['ub']) && $k == $f['ub'] ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                                </select></td>
                            </td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div><div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?=Lang::main('addFilter'); ?></a></div>

                    <div class="padded2">
                        <div style="float: right"><?=Lang::main('refineSearch'); ?></div>
                        <?=Lang::main('match').Lang::main('colon'); ?><input type="radio" name="ma" value="" id="ma-0" <?=(!isset($f['ma']) ? 'checked="checked" ' : null); ?>/><label for="ma-0"><?=Lang::main('allFilter'); ?></label><input type="radio" name="ma" value="1" id="ma-1" <?=(isset($f['ma']) ? 'checked="checked" ' : null); ?>/><label for="ma-1"><?=Lang::main('oneFilter'); ?></label>
                    </div>

                    <div class="pad3"></div>

                    <div class="text">
                        <h3 class="first"><a id="fi_weight_toggle" href="javascript:;" class="disclosure-off" onclick="return g_disclose($WH.ge('statweight-disclosure'), this)"><?=Lang::main('createWS'); ?></a></h3>
                    </div>

                    <div id="statweight-disclosure" style="display: none">
                        <div id="statweight-help">
                            <div><a href="?help=stat-weighting" target="_blank" id="statweight-help" class="icon-help"><?=Lang::main('help'); ?></a></div>
                        </div>

                        <table>
                            <tr>
                                <td><?=Lang::main('preset').Lang::main('colon'); ?></td>
                                <td id="fi_presets"></td>
                            </tr>
                            <tr>
                                <td class="padded"><?=Lang::item('gems').Lang::main('colon'); ?></td>
                                <td class="padded">
                                    <select name="gm">
                                        <option<?=(!isset($f['gm']) ? ' selected' : null); ?>></option>
                                        <option value="2"<?=(isset($f['gm']) && $f['gm'] == 2 ? ' selected' : null).'>'.Lang::item('quality', 2); ?></option>
                                        <option value="3"<?=(isset($f['gm']) && $f['gm'] == 3 ? ' selected' : null).'>'.Lang::item('quality', 3); ?></option>
                                        <option value="4"<?=(isset($f['gm']) && $f['gm'] == 4 ? ' selected' : null).'>'.Lang::item('quality', 4); ?></option>
                                    </select>
                                    &nbsp; <input type="checkbox" name="jc" value="1" id="jc" <?=(isset($f['jc']) && $f['jc'] == 1 ? 'checked="checked" ' : null); ?>/><label for="jc"><?=sprintf(Lang::main('jcGemsOnly'), ' class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_jconlygems, 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"'); ?></label>
                                </td>
                            </tr>
                        </table>

                        <div id="fi_weight" class="criteria" style="display: none"><div></div></div>
                        <div><a href="javascript:;" id="fi_addweight" onclick="fi_addCriterion(this); return false" style="display: none"><?=Lang::main('addWeight'); ?></a></div>
                        <div class="pad2"></div>
                        <small><?=Lang::main('cappedHint'); ?></small>

                    </div>

                    <div class="clear"></div>
                    <div class="padded">
<?php
echo Lang::main('groupBy').Lang::main('colon')."\n";
foreach (Lang::main('gb') as $k => $str):
    if ($k):
        echo '                        <input type="radio" name="gb" value="'.$k.'" id="gb-'.$str[1].'"'.(!empty($f['gb']) && $f['gb'] == $k ? ' checked="checked"' : null).'/><label for="gb-'.$str[1].'">'.$str[0]."</label>\n";
    else:
        echo '                        <input type="radio" name="gb" value="" id="gb-'.$str[1].'"'.(empty($f['gb']) ? ' checked="checked"' : null).'/><label for="gb-'.$str[1].'">'.$str[0]."</label>\n";
    endif;
endforeach;
?>
                    </div>

                    <div class="clear"></div>

                    <div class="padded">
                        <input type="submit" value="<?=Lang::main('applyFilter'); ?>" />
                        <input type="reset" value="<?=Lang::main('resetForm'); ?>" />
                    </div>

                    <input type="hidden" name="upg"<?=(!empty($f['upg']) ? ' value="'.$f['upg'].'"' : null); ?>/>

                    <div class="pad"></div>

                </form>
                <div class="pad"></div>
            </div>

<?php $this->brick('filter', ['fi' => $f['initData']]); ?>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
