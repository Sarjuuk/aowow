<?php
$this->brick('header');
$f = $this->filter;                                         // shorthand
?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate', ['fi' => empty($f['query']) ? null : ['query' => $f['query'], 'menuItem' => 2]]);

# for some arcane reason a newline (\n) means, the first childNode is a text instead of the form for the following div
?>
            <div id="fi" style="display: <?=(empty($f['query']) ? 'none' : 'block'); ?>;"><form
                action="?filter=profiles<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="rightpanel">
                        <div style="float: left"><?=Util::ucFirst(Lang::game('class')).Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="cl[]" size="7" multiple="multiple" class="rightselect" style="background-color: #181818">
<?php
foreach (Lang::game('cl') as $k => $str):
    if ($str):
        echo '                            <option class="c'.$k.'" value="'.$k.'"'.(isset($f['cl']) && in_array($k, (array)$f['cl']) ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                        </select>
                    </div>

                    <div class="rightpanel2">
                        <div style="float: left"><?=Util::ucFirst(Lang::game('race')).Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['ra[]'].selectedIndex = -1; pr_onChangeRace(); return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="ra[]" size="7" multiple="multiple" class="rightselect" onchange="pr_onChangeRace()">
<?php
foreach (Lang::game('ra') as $k => $str):
    if ($str && $k > 0):
        echo '                            <option value="'.$k.'"'.(isset($f['ra']) && in_array($k, (array)$f['ra']) ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>

                        </select>
                    </div>

                    <table>
                        <tr>
                            <td><?=Util::ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="3">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" <?=(isset($f['na']) ? 'value="'.Util::htmlEscape($f['na']).'" ' : null); ?>/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="profile-ex" <?=(isset($f['ex']) ? 'checked="checked"' : null); ?>/></td>
                                    <td><label for="profile-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_exactprofilesearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><?=Lang::main('exactMatch'); ?></span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?=Lang::profiler('region').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="rg" onchange="pr_onChangeRegion(this.form, null, null)">
                            <option></option>
<?php
    foreach (array_unique(array_column(Profiler::getRealms(), 'region')) as $rg):
        echo "                             <option value=\"".$rg."\">".Lang::profiler('regions', $rg)."</option>\n";
    endforeach;
?>
                            </select>&nbsp;</td>
                            <td style="width:50px;" class="padded">&nbsp;&nbsp;&nbsp;<?=Lang::profiler('realm').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="sv"><option></option></select><input type="hidden" name="bg" value="<?=(isset($f['bg']) ? Util::htmlEscape($f['bg']) : null); ?>" /></td>
                        </tr><tr>
                            <td class="padded"><?=Lang::main('side').Lang::main('colon'); ?></td>
                            <td class="padded" style="width:80px;">&nbsp;<select name="si">
                                <option></option>
                                <option value="1"<?=(!empty($f['si']) && $f['si'] == 1 ? ' selected' : null);?>><?=Lang::game('si', 1); ?></option>
                                <option value="2"<?=(!empty($f['si']) && $f['si'] == 2 ? ' selected' : null);?>><?=Lang::game('si', 2); ?></option>
                            </select></td>
                            <td class="padded">&nbsp;&nbsp;&nbsp;<?=Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="3" class="smalltextbox" <?=(isset($f['minle']) ? 'value="'.$f['minle'].'" ' : null); ?>/> - <input type="text" name="maxle" maxlength="3" class="smalltextbox" <?=(isset($f['maxle']) ? 'value="'.$f['maxle'].'" ' : null); ?>/></td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div>
                    <div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?=Lang::main('addFilter'); ?></a></div>

                    <div class="padded2">
                        <?=Lang::main('match').Lang::main('colon'); ?><input type="radio" name="ma" value="" id="ma-0" <?=(!isset($f['ma']) ? 'checked="checked" ' : null); ?>/><label for="ma-0"><?=Lang::main('allFilter'); ?></label><input type="radio" name="ma" value="1" id="ma-1" <?=(isset($f['ma']) ? 'checked="checked" ' : null); ?>/><label for="ma-1"><?=Lang::main('oneFilter'); ?></label>
                    </div>

                    <div class="clear"></div>

                    <div class="padded">
                        <input type="submit" value="<?=Lang::main('applyFilter'); ?>" />
                        <input type="reset" value="<?=Lang::main('resetForm'); ?>" />
                    </div>

                </form>

<?php
    if ($this->roster):
?>
                <div class="text"><h2 style="padding-top: 0;"><?=$this->roster;?></h2></div>
<?php
    else:
?>
                <div class="pad"></div>
<?php
    endif;
?>
            </div>

<?php $this->brick('filter', ['fi' => $f['initData']]); ?>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
