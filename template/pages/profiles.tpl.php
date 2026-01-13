<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

$this->brick('header');
$f = $this->filter->values;                                 // shorthand
?>
    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate', ['fiQuery' => $this->filter->query, 'fiMenuItem' => array_slice($this->pageTemplate['breadcrumb'], 0, 3)]);

# pr_setRegionRealm($WH.ge('fi').firstChild, realm, region) - never have \n\s before <form>, it will become firstChild (a text node)
?>
            <div id="fi" style="display: <?=($this->filter->query ? 'block' : 'none'); ?>;"><form
                action="?filter=profiles<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="text">
<?php
$this->brick('headIcons');

$this->brick('redButtons');
?>
                        <h1><?=$this->h1; ?></h1>
                    </div>
                    <div class="rightpanel">
                        <div style="float: left"><?=$this->ucFirst(Lang::game('class')).Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="cl[]" size="7" multiple="multiple" class="rightselect" style="background-color: #181818">
<?=$this->makeOptionsList(Lang::game('cl') , $f['cl'], 28, fn($v, $k, &$e) => $v && ($e = ['class' => 'c'.$k])); ?>
                        </select>
                    </div>

                    <div class="rightpanel2">
                        <div style="float: left"><?=$this->ucFirst(Lang::game('race')).Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['ra[]'].selectedIndex = -1; pr_onChangeRace(); return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="ra[]" size="7" multiple="multiple" class="rightselect" onchange="pr_onChangeRace()">
<?=$this->makeOptionsList(Lang::game('ra') , $f['ra'], 28, fn($v, $k) => $v && $k > 0); ?>
                        </select>
                    </div>

                    <table>
                        <tr>
                            <td><?=$this->ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="3">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" <?=($f['na'] ? 'value="'.$this->escHTML($f['na']).'" ' : ''); ?>/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="profile-ex" <?=($f['ex'] ? 'checked="checked"' : ''); ?>/></td>
                                    <td><label for="profile-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_exactprofilesearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><?=Lang::main('exactMatch'); ?></span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?=Lang::profiler('region').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="rg" onchange="pr_onChangeRegion(this.form, null, null)">
                                <option></option>
<?=$this->makeOptionsList($this->regions, $f['rg'], 32); ?>
                            </select>&nbsp;</td>
                            <td style="width:50px;" class="padded">&nbsp;&nbsp;&nbsp;<?=Lang::profiler('realm').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="sv"><option></option></select><input type="hidden" name="bg" value="<?=($f['bg'] ? $this->escHTML($f['bg']) : ''); ?>" /></td>
                        </tr><tr>
                            <td class="padded"><?=Lang::main('side'); ?></td>
                            <td class="padded" style="width:80px;">&nbsp;<select name="si">
                                <option></option>
<?=$this->makeOptionsList(Lang::game('si'), $f['si'], 32, fn($v, $k) => in_array($k, [SIDE_ALLIANCE, SIDE_HORDE])); ?>
                            </select></td>
                            <td class="padded">&nbsp;&nbsp;&nbsp;<?=Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="3" class="smalltextbox" <?=($f['minle'] ? 'value="'.$f['minle'].'" ' : ''); ?>/> - <input type="text" name="maxle" maxlength="3" class="smalltextbox" <?=($f['maxle'] ? 'value="'.$f['maxle'].'" ' : ''); ?>/></td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div>
                    <div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?=Lang::main('addFilter'); ?></a></div>

                    <div class="padded2">
                        <?=Lang::main('match'); ?><input type="radio" name="ma" value="" id="ma-0" <?=(!$f['ma'] ? 'checked="checked" ' : ''); ?>/><label for="ma-0"><?=Lang::main('allFilter'); ?></label><input type="radio" name="ma" value="1" id="ma-1" <?=($f['ma'] ? 'checked="checked" ' : ''); ?>/><label for="ma-1"><?=Lang::main('oneFilter'); ?></label>
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

<?=$this->renderFilter(12); ?>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
