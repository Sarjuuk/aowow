<?php
$this->brick('header');
$f = $this->filter;                                         // shorthand
?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate', ['fi' => empty($f['query']) ? null : ['query' => $f['query'], 'menuItem' => 4]]);
?>

            <div id="fi" style="display: <?php echo empty($f['query']) ? 'none' : 'block' ?>;">
                <form action="?npcs<?php echo $this->subCat; ?>&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="rightpanel">
                        <div style="float: left"><?php echo Lang::npc('classification').Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false"><?php echo Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="cl[]" size="5" multiple="multiple" class="rightselect" style="width: 9.5em">
<?php
foreach (Lang::npc('rank') as $i => $str):
    if ($str):
        echo '                            <option value="'.$i.'"'.(isset($f['cl']) && in_array($i, (array)$f['cl']) ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                        </select>
                    </div>
<?php if ($this->petFamPanel): ?>
                    <div class="rightpanel2">
                        <div style="float: left"><?php echo Lang::npc('petFamily').Lang::main('colon'); ?></div><small><a href="javascript:;" onclick="document.forms['fi'].elements['fa[]'].selectedIndex = -1; return false" onmousedown="return false"><?php echo Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="fa[]" size="7" multiple="multiple" class="rightselect">
<?php
foreach (Lang::game('fa') as $i => $str):
    if ($str):
        echo '                            <option value="'.$i.'"'.(isset($f['fa']) && in_array($i, (array)$f['fa']) ? ' selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                        </select>
                    </div>
<?php endif; ?>
                    <table>
                        <tr>
                            <td><?php echo Util::ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="2">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" <?php echo isset($f['na']) ? 'value="'.Util::htmlEscape($f['na']).'" ' : null; ?>/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="npc-ex" <?php echo isset($f['ex']) ? 'checked="checked" ' : null; ?>/></td>
                                    <td><label for="npc-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_extendednpcsearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><?php echo Lang::main('extSearch'); ?></span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?php echo Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="2" class="smalltextbox" <?php echo isset($f['minle']) ? 'value="'.$f['minle'].'" ' : null; ?>/> - <input type="text" name="maxle" maxlength="2" class="smalltextbox" <?php echo isset($f['maxle']) ? 'value="'.$f['maxle'].'" ' : null; ?>/></td>
                            <td class="padded" width="100%">
                                <table><tr>
                                    <td>&nbsp;&nbsp;&nbsp;<?php echo Lang::npc('react').Lang::main('colon'); ?></td>
                                    <td>&nbsp;<select name="ra" onchange="fi_dropdownSync(this)" onkeyup="fi_dropdownSync(this)" style="background-color: #181818"<?php echo isset($f['ra']) ? ' class="q'.($f['ra'] == 1 ? '2' : ($f['ra'] == -1 ? '10' : null)).'"' : null; ?>>
                                        <option></option>
                                        <option value="1" class="q2"<?php echo isset($f['ra']) && $f['ra'] == 1 ? ' selected' : null; ?>>A</option>
                                        <option value="0" class="q"<?php echo isset($f['ra']) && $f['ra'] == 0 ? ' selected' : null; ?>>A</option>
                                        <option value="-1" class="q10"<?php echo isset($f['ra']) && $f['ra'] == -1 ? ' selected' : null; ?>>A</option>
                                    </select>
                                    <select name="rh" onchange="fi_dropdownSync(this)" onkeyup="fi_dropdownSync(this)" style="background-color: #181818"<?php echo isset($f['rh']) ? ' class="q'.($f['rh'] == 1 ? '2' : ($f['rh'] == -1 ? '10' : null)).'"' : null; ?>>
                                        <option></option>
                                        <option value="1" class="q2"<?php echo isset($f['rh']) && $f['rh'] == 1 ? ' selected' : null; ?>>H</option>
                                        <option value="0" class="q"<?php echo isset($f['rh']) && $f['rh'] == 0 ? ' selected' : null; ?>>H</option>
                                        <option value="-1" class="q10"<?php echo isset($f['rh']) && $f['rh'] == -1 ? ' selected' : null; ?>>H</option>
                                    </select>
                                    </td>
                                </tr></table>
                            </td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div><div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?php echo Lang::main('addFilter'); ?></a></div>

                    <div class="padded2 clear">
                        <div style="float: right"><?php echo Lang::main('refineSearch'); ?></div>
                        <?php echo Lang::main('match').Lang::main('colon'); ?><input type="radio" name="ma" value="" id="ma-0" <?php echo !isset($f['ma']) ? 'checked="checked" ' : null ?>/><label for="ma-0"><?php echo Lang::main('allFilter'); ?></label><input type="radio" name="ma" value="1" id="ma-1" <?php echo isset($f['ma']) ? 'checked="checked" ' : null ?> /><label for="ma-1"><?php echo Lang::main('oneFilter'); ?></label>
                    </div>

                    <div class="clear"></div>

                    <div class="padded">
                        <input type="submit" value="<?php echo Lang::main('applyFilter'); ?>" />
                        <input type="reset" value="<?php echo Lang::main('resetForm'); ?>" />
                    </div>

                </form>
                <div class="pad"></div>
            </div>

            <script type="text/javascript">//<![CDATA[
                fi_init('npcs');
<?php
foreach ($f['fi'] as $str):
    echo '                '.$str."\n";
endforeach;
?>
            //]]></script>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
