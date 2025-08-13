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

$this->brick('pageTemplate', ['fiQuery' => $this->filter->query, 'fiMenuItem' => [3]]);
?>
            <div id="fi" style="display: <?=($this->filter->query ? 'block' : 'none'); ?>;">
                <form action="?filter=quests<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="text">
<?php
$this->brick('headIcons');

$this->brick('redButtons');
?>
                        <h1><?=$this->h1; ?></h1>
                    </div>
                    <div class="rightpanel">
                        <div style="float: left"><?=Lang::game('type').Lang::main('colon'); ?></div><small><a href="javascript:;" onclick="document.forms['fi'].elements['ty[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="ty[]" size="6" multiple="multiple" class="rightselect">
<?=$this->makeOptionsList(Lang::quest('questInfo'), $f['ty'], 28); ?>
                        </select>
                    </div>

                    <table>
                        <tr>
                            <td><?=$this->ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="3">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" <?=($f['na'] ? 'value="'.$this->escHTML($f['na']).'" ' : ''); ?>/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="quest-ex" <?=($f['ex'] ? 'checked ' : ''); ?>/></td>
                                    <td><label for="quest-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_extendedquestsearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><?=Lang::main('extSearch'); ?></span></label></td>
                                </tr></table>
                            </td>
                        </tr>
                        <tr>
                            <td class="padded"><?=Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="2" class="smalltextbox" <?=($f['minle'] ? 'value="'.$f['minle'].'" ' : ''); ?>/> - <input type="text" name="maxle" maxlength="2" class="smalltextbox" <?=($f['maxle'] ? 'value="'.$f['maxle'].'" ' : ''); ?>/></td>
                            <td class="padded" width="100%">
                                <table><tr>
                                    <td>&nbsp;&nbsp;&nbsp;<?=Lang::main('_reqLevel').Lang::main('colon'); ?></td>
                                    <td>&nbsp;<input type="text" name="minrl" maxlength="2" class="smalltextbox" <?=($f['minrl'] ? 'value="'.$f['minrl'].'" ' : ''); ?>/> - <input type="text" name="maxrl" maxlength="2" class="smalltextbox" <?=($f['maxrl'] ? 'value="'.$f['maxrl'].'" ' : ''); ?>/></td>
                                </tr></table>
                            </td>
                        </tr>
                        <tr>
                            <td class="padded"><?=Lang::main('side').Lang::main('colon'); ?></td>
                            <td class="padded" colspan="3">&nbsp;<select name="si">
                                <option></option>
<?=$this->makeOptionsList(Lang::game('si'), $f['si'], 36); ?>
                            </select></td>
                        </tr>
                    </table>
                    <div id="fi_criteria" class="padded criteria"><div></div></div><div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?=Lang::main('addFilter'); ?></a></div>

                    <div class="padded2 clear">
                        <div style="float: right"><?=Lang::main('refineSearch'); ?></div>
                        <?=Lang::main('match'); ?><input type="radio" name="ma" value="" id="ma-0" <?=(!$f['ma'] ? 'checked="checked" ' : ''); ?>/><label for="ma-0"><?=Lang::main('allFilter'); ?></label><input type="radio" name="ma" value="1" id="ma-1" <?=($f['ma'] ? 'checked="checked" ' : ''); ?> /><label for="ma-1"><?=Lang::main('oneFilter'); ?></label>
                    </div>

                    <div class="clear"></div>

                    <div class="padded">
                        <input type="submit" value="<?=Lang::main('applyFilter'); ?>" />
                        <input type="reset" value="<?=Lang::main('resetForm'); ?>" />
                    </div>

                </form>
                <div class="pad"></div>
            </div>

<?=$this->renderFilter(12); ?>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
