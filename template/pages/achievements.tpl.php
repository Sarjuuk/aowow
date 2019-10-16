<?php
$this->brick('header');
$f = $this->filter;                                         // shorthand
?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate', ['fi' => empty($f['query']) ? null : ['query' => $f['query'], 'menuItem' => 9]]);
?>

            <div id="fi" style="display: <?php echo empty($f['query']) ? 'none' : 'block'; ?>;">
                <form action="?filter=achievements<?php echo $this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <table>
                        <tr>
                            <td><?php echo Util::ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="3">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" <?php echo isset($f['na']) ? 'value="'.$f['na'].'"' : null; ?>/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="achievement-ex" <?php echo isset($f['ex']) ? 'checked="checked"' : null; ?>/></td>
                                    <td><label for="achievement-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_extendedachievementsearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><?php echo Lang::main('extSearch'); ?></span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?php echo Lang::main('side').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="si">
                                <option></option>
<?php
foreach (Lang::game('si') as $i => $str):
    if ($str):
        echo '                                    <option value="'.$i.'" '.((isset($f['si']) && $f['si'] == $i) ? 'selected' : null).'>'.$str."</option>\n";
    endif;
endforeach;
?>
                                </select>
                            </td>
                            <td class="padded"><table><tr>
                                <td>&nbsp;&nbsp;&nbsp;<?php echo Lang::achievement('points').Lang::main('colon'); ?></td>
                                <td>&nbsp;<input type="text" name="minpt" maxlength="2" class="smalltextbox" <?php echo isset($f['minpt']) ? 'value="'.$f['minpt'].'"' : null; ?>/> - <input type="text" name="maxpt" maxlength="2" class="smalltextbox" <?php echo isset($f['maxpt']) ? 'value="'.$f['maxpt'].'"' : null; ?>/></td>
                            </tr></table></td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div><div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?php echo Lang::main('addFilter'); ?></a></div>

                    <div class="padded2">
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

<?php $this->brick('filter', ['fi' => $f['initData']]); ?>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
