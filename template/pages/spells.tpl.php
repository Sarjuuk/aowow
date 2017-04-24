<?php
$this->brick('header');
$f = $this->filter;                                         // shorthand
?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate', ['fi' => empty($f['query']) ? null : ['query' => $f['query'], 'menuItem' => 1]]);
?>

            <div id="fi" style="display: <?=(empty($f['query']) ? 'none' : 'block'); ?>;">
                <form action="?filter=spells<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="rightpanel">
                        <div style="float: left"><?=Lang::game('school').Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['sc[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="sc[]" size="7" multiple="multiple" class="rightselect" style="width: 8em">
<?php
foreach (Lang::game('sc') as $i => $str):
    if ($str):
        echo '                                <option value="'.$i.'"'.(isset($f['sc']) && in_array($i, (array)$f['sc']) ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                        </select>
                    </div>
<?php if ($this->classPanel): ?>
                    <div class="rightpanel2">
                        <div style="float: left"><?=Util::ucFirst(Lang::game('class')).Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="cl[]" size="8" multiple="multiple" class="rightselect" style="width: 8em; background-color: #181818">
<?php
foreach (Lang::game('cl') as $i => $str):
    if ($str):
        echo '                                <option value="'.$i.'"'.(isset($f['cl']) && in_array($i, (array)$f['cl']) ? ' selected' : null).' class="c'.$i.'">'.$str."</option>\n";
    endif;
endforeach;
?>
                        </select>
                    </div>
<?php
endif;

if ($this->glyphPanel):
?>
                    <div class="rightpanel2">
                        <div style="float: left"><?=Util::ucFirst(Lang::game('glyphType')).Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['gl[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="gl[]" size="2" multiple="multiple" class="rightselect" style="width: 8em">
<?php
foreach (Lang::game('gl') as $i => $str):
    if ($str):
        echo '                                <option value="'.$i.'"'.(isset($f['gl']) && in_array($i, (array)$f['gl']) ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                        </select>
                    </div>
<?php endif; ?>
                    <table>
                        <tr>
                            <td><?=Util::ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="2">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" <?=(isset($f['na']) ? 'value="'.Util::htmlEscape($f['na']).'" ' : null); ?>/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="spell-ex" <?=(isset($f['ex']) ? 'checked="checked" ' : null); ?>/></td>
                                    <td><label for="spell-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_extendedspellsearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><?=Lang::main('extSearch'); ?></span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?=Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="2" class="smalltextbox" <?=(isset($f['minle']) ? 'value="'.$f['minle'].'" ' : null); ?>/> - <input type="text" name="maxle" maxlength="2" class="smalltextbox" <?=(isset($f['maxle']) ? 'value="'.$f['maxle'].'" ' : null); ?>/></td>
                            <td class="padded">
                                <table cellpadding="0" cellspacing="0" border="0"><tr>
                                    <td>&nbsp;&nbsp;&nbsp;<?=Lang::game('reqSkillLevel').Lang::main('colon'); ?></td>
                                    <td>&nbsp;<input type="text" name="minrs" maxlength="3" class="smalltextbox2" <?=(isset($f['minrs']) ? 'value="'.$f['minrs'].'" ' : null); ?>/> - <input type="text" name="maxrs" maxlength="3" class="smalltextbox2" <?=(isset($f['maxrs']) ? 'value="'.$f['maxrs'].'" ' : null); ?>/></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?=Util::ucFirst(Lang::game('race')).Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="ra">
                                <option></option>
<?php
foreach (Lang::game('ra') as $i => $str):
    if ($str && $i > 0):
        echo '                                <option value="'.$i.'"'.(isset($f['ra']) && $f['ra'] == $i ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                            </select></td>
                            <td class="padded"></td>
                        </tr><tr>
                            <td class="padded"><?=Lang::game('mechAbbr').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="me">
                                <option></option>
<?php
foreach (Lang::game('me') as $i => $str):
    if ($str):
        echo '                                <option value="'.$i.'"'.(isset($f['me']) && $f['me'] == $i ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                            </select></td>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0"><tr>
                                    <td class="padded">&nbsp;&nbsp;&nbsp;<?=Lang::game('dispelType').Lang::main('colon'); ?></td>
                                    <td class="padded">&nbsp;<select name="dt">
                                        <option></option>
<?php
foreach (Lang::game('dt') as $i => $str):
    if ($str):
        echo '                                <option value="'.$i.'"'.(isset($f['dt']) && $f['dt'] == $i ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                                    </select></td>
                                </tr></table>
                            </td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div><div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?=Lang::main('addFilter'); ?></a></div>

                    <div class="padded2">
                        <div style="float: right"><?=Lang::main('refineSearch'); ?></div>
                        <?=Lang::main('match').Lang::main('colon'); ?><input type="radio" name="ma" value="" id="ma-0" <?=(!isset($f['ma']) ? 'checked="checked" ' : null); ?>/><label for="ma-0"><?=Lang::main('allFilter'); ?></label><input type="radio" name="ma" value="1" id="ma-1" <?=(isset($f['ma']) ? 'checked="checked" ' : null); ?> /><label for="ma-1"><?=Lang::main('oneFilter'); ?></label>
                    </div>

                    <div class="clear"></div>

                    <div class="padded">
                        <input type="submit" value="<?=Lang::main('applyFilter'); ?>" />
                        <input type="reset" value="<?=Lang::main('resetForm'); ?>" />
                    </div>

                </form>
                <div class="pad"></div>
            </div>

<?php $this->brick('filter', ['fi' => $f['initData']]); ?>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
