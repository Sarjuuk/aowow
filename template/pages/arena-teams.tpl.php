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
                action="?filter=arena-teams&<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
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
                            <td class="padded">&nbsp;&nbsp;&nbsp;Size<?=Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="sz">
                                <option></option>
                                <option value="2"<?=(!empty($f['sz']) && $f['sz'] == 2 ? ' selected' : null);?>>2v2</option>
                                <option value="3"<?=(!empty($f['sz']) && $f['sz'] == 3 ? ' selected' : null);?>>3v3</option>
                                <option value="5"<?=(!empty($f['sz']) && $f['sz'] == 5 ? ' selected' : null);?>>5v5</option>
                            </select></td>
                        </tr>
                    </table>

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
