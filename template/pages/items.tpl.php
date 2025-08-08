<?php
    namespace Aowow\Template;

    use Aowow\Lang;

$this->brick('header');
$f = $this->filter->values;                                 // shorthand
?>
    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate', ['fiQuery' => $this->filter->query, 'fiMenuItem' => [0]]);
?>
            <div id="fi" style="display: <?=($this->filter->query ? 'block' : 'none'); ?>;">
                <form action="?filter=items<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="text">
<?php
$this->brick('headIcons');

$this->brick('redButtons');
?>
                        <h1><?=$this->h1; ?></h1>
                    </div>
                    <div class="rightpanel">
                        <div style="float: left"><?=Lang::item('_quality'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['qu[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="qu[]" size="7" multiple="multiple" class="rightselect" style="background-color: #181818">
<?=$this->makeOptionsList(Lang::item('quality'), $f['qu'], 28, fn($v, $k, &$e) => $e = ['class' => 'q'.$k]); ?>
                        </select>
                    </div>

<?php
if ($this->slotList):
?>
                    <div class="rightpanel2">
                        <div style="float: left"><?=Lang::item('slot'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['sl[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="sl[]" size="<?=min(count($this->slotList), 7); ?>" multiple="multiple" class="rightselect">
<?=$this->makeOptionsList($this->slotList, $f['sl'], 28); ?>
                        </select>
                    </div>
<?php
endif;

if ($this->typeList):
?>
                    <div class="rightpanel2">
                        <div style="float: left"><?=Lang::game('type'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['ty[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="ty[]" size="<?=min(count($this->typeList), 7); ?>" multiple="multiple" class="rightselect">
<?=$this->makeOptionsList($this->typeList, $f['ty'], 28, function($v, $k, &$e) {
    if (($this->pageTemplate['breadcrumb'][2] ?? null) === 0 && ($this->pageTemplate['breadcrumb'][3] ?? null) === $k)
        $e = ['selected' => 'selected'];                    // preselect type for consumables .. blegh >:(
    return true;
}); ?>
                        </select>
                    </div>
<?php endif; ?>

                    <table>
                        <tr>
                            <td><?=$this->ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="2">&nbsp;<input type="text" name="na" size="30" <?=($f['na'] ? 'value="'.$this->escHTML($f['na']).'" ' : ''); ?>/></td>
                            <td></td>
                        </tr><tr>
                            <td class="padded"><?=Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="3" class="smalltextbox2" <?=($f['minle'] ? 'value="'.$f['minle'].'" ' : ''); ?>/> - <input type="text" name="maxle" maxlength="3" class="smalltextbox2" <?=($f['maxle'] ? 'value="'.$f['maxle'].'" ' : ''); ?>/></td>
                            <td class="padded">
                                <table>
                                    <tr>
                                        <td>&nbsp;&nbsp;&nbsp;<?=Lang::main('_reqLevel'); ?></td>
                                        <td>&nbsp;<input type="text" name="minrl" maxlength="2" class="smalltextbox" <?=($f['minrl'] ? 'value="'.$f['minrl'].'" ' : ''); ?>/> - <input type="text" name="maxrl" maxlength="2" class="smalltextbox" <?=($f['maxrl'] ? 'value="'.$f['maxrl'].'" ' : ''); ?>/></td>
                                    </tr>
                                </table>
                            </td>
                            <td></td>
                        </tr><tr>
                            <td class="padded"><?=Lang::item('usableBy'); ?></td>
                            <td class="padded">&nbsp;<select name="si" style="margin-right: 0.5em">
                                <option></option>
<?=$this->makeOptionsList(Lang::game('si'), $f['si'], 28); ?>
                            </select></td>
                            <td class="padded">
                                &nbsp;<select name="ub">
                                    <option></option>
<?=$this->makeOptionsList(Lang::game('cl'), $f['ub'], 28); ?>
                                </select></td>
                            </td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div><div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?=Lang::main('addFilter'); ?></a></div>

                    <div class="padded2">
                        <div style="float: right"><?=Lang::main('refineSearch'); ?></div>
                        <?=Lang::main('match'); ?>
                        <input type="radio" name="ma" value="" id="ma-0" <?=(!$f['ma'] ? 'checked="checked" ' : ''); ?>/><label for="ma-0"><?=Lang::main('allFilter'); ?></label><input type="radio" name="ma" value="1" id="ma-1" <?=($f['ma'] ? 'checked="checked" ' : ''); ?>/><label for="ma-1"><?=Lang::main('oneFilter'); ?></label>
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
                                <td><?=Lang::main('preset'); ?></td>
                                <td id="fi_presets"></td>
                            </tr>
                            <tr>
                                <td class="padded"><?=Lang::item('gems'); ?></td>
                                <td class="padded">
                                    <select name="gm">
                                        <option<?=(!$f['gm'] ? ' selected' : ''); ?>></option>
<?=$this->makeOptionsList(Lang::item('quality'), $f['gm'], 40, fn($v, $k) => in_array($k, [ITEM_QUALITY_UNCOMMON, ITEM_QUALITY_RARE, ITEM_QUALITY_EPIC])); ?>
                                    </select>
                                    &nbsp; <input type="checkbox" name="jc" value="1" id="jc" <?=($f['jc'] && $f['jc'] == 1 ? 'checked="checked" ' : ''); ?>/><label for="jc"><?=sprintf(Lang::main('jcGemsOnly'), ' class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_jconlygems, 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"'); ?></label>
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
                        <?=Lang::main('groupBy')."\n"; ?>
<?=$this->makeRadiosList('gb', Lang::main('gb'), $f['gb'] ?? '', 24, fn($v, &$k) => ($k = $k ?: '') || 1); ?>
                    </div>

                    <div class="clear"></div>

                    <div class="padded">
                        <input type="submit" value="<?=Lang::main('applyFilter'); ?>" />
                        <input type="reset" value="<?=Lang::main('resetForm'); ?>" />
                    </div>

                    <input type="hidden" name="upg" value ="<?=($f['upg'] ? implode(':', $f['upg']) : ''); ?>" />

                    <div class="pad"></div>

                </form>
                <div class="pad"></div>
            </div>

<?=$this->renderFilter(12); ?>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
