<?php
$this->brick('header');
$f = $this->filter;                                         // shorthand
?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate');

# for some arcane reason a newline (\n) means, the first childNode is a text instead of the form for the following div
?>
            <div id="fi" style="display: <?php echo empty($f['query']) ? 'none' : 'block' ?>;"><form
                action="?profiles&filter" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="rightpanel">
                        <div style="float: left"><?php echo Util::ucFirst(Lang::game('class')).Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false"><?php echo Lang::main('clear'); ?></a></small>
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
                        <div style="float: left"><?php echo Util::ucFirst(Lang::game('race')).Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['ra[]'].selectedIndex = -1; pr_onChangeRace(); return false" onmousedown="return false"><?php echo Lang::main('clear'); ?></a></small>
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
                            <td><?php echo Util::ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="3">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" <?php echo isset($f['na']) ? 'value="'.Util::htmlEscape($f['na']).'" ' : null; ?>/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="profile-ex" <?php echo isset($f['ex']) ? 'checked="checked"' : null; ?>/></td>
                                    <td><label for="profile-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_exactprofilesearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><?php echo Lang::main('exactMatch'); ?></span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?php echo Lang::main('region').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="rg" onchange="pr_onChangeRegion(this.form, null, null)">
                            <option></option>
                            <option value="us">US & Oceanic</option>
                            <option value="eu" selected="selected">Europe</option>
                            </select>&nbsp;</td>
                            <td style="width:50px;" class="padded">&nbsp;&nbsp;&nbsp;<?php echo Lang::main('realm').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="sv"><option></option></select><input type="hidden" name="bg" value="" /></td>
                        </tr><tr>
                            <td class="padded"><?php echo Lang::main('side').Lang::main('colon'); ?></td>
                            <td class="padded" style="width:80px;">&nbsp;<select name="si">
                                <option></option>
                                <option value="1"><?php echo Lang::game('si', 1); ?></option>
                                <option value="2"><?php echo Lang::game('si', 2); ?></option>
                            </select></td>
                            <td class="padded">&nbsp;&nbsp;&nbsp;<?php echo Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="3" class="smalltextbox" <?php echo isset($f['minle']) ? 'value="'.$f['minle'].'" ' : null; ?>/> - <input type="text" name="maxle" maxlength="3" class="smalltextbox" <?php echo isset($f['maxle']) ? 'value="'.$f['maxle'].'" ' : null; ?>/></td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div>
                    <div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?php echo Lang::main('addFilter'); ?></a></div>

                    <div class="padded2">
                        <?php echo Lang::main('match').Lang::main('colon'); ?><input type="radio" name="ma" value="" id="ma-0" <?php echo !isset($f['ma']) ? 'checked="checked" ' : null; ?>/><label for="ma-0"><?php echo Lang::main('allFilter'); ?></label><input type="radio" name="ma" value="1" id="ma-1" <?php echo isset($f['ma']) ? 'checked="checked" ' : null; ?>/><label for="ma-1"><?php echo Lang::main('oneFilter'); ?></label>
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
                pr_setRegionRealm($WH.ge('fi').firstChild, '<?php echo $this->region; ?>', '<?php echo $this->realm; ?>');
                pr_onChangeRace();
                fi_init('profiles');
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
