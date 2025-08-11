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

$this->brick('pageTemplate', ['fiQuery' => $this->filter->query, 'fiMenuItem' => [4]]);
?>
            <div id="fi" style="display: <?=($this->filter->query ? 'block' : 'none'); ?>;">
                <form action="?filter=npcs<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="text">
<?php
$this->brick('headIcons');

$this->brick('redButtons');
?>
                        <h1><?=$this->h1; ?></h1>
                    </div>
                    <div class="rightpanel">
                        <div style="float: left"><?=Lang::npc('classification', ['']); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="cl[]" size="5" multiple="multiple" class="rightselect" style="width: 9.5em">
<?=$this->makeOptionsList(Lang::npc('rank'), $f['cl'], 28); ?>
                        </select>
                    </div>
<?php if ($this->petFamPanel): ?>
                    <div class="rightpanel2">
                        <div style="float: left"><?=Lang::npc('petFamily'); ?></div><small><a href="javascript:;" onclick="document.forms['fi'].elements['fa[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="fa[]" size="7" multiple="multiple" class="rightselect">
<?=$this->makeOptionsList(Lang::game('fa'), $f['fa'], 28); ?>
                        </select>
                    </div>
<?php endif; ?>
                    <table>
                        <tr>
                            <td><?=$this->ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="2">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" <?=($f['na'] ? 'value="'.$this->escHTML($f['na']).'" ' : ''); ?>/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="npc-ex" <?=($f['ex'] ? 'checked="checked" ' : ''); ?>/></td>
                                    <td><label for="npc-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_extendednpcsearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><?=Lang::main('extSearch'); ?></span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?=Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="2" class="smalltextbox" <?=($f['minle'] ? 'value="'.$f['minle'].'" ' : ''); ?>/> - <input type="text" name="maxle" maxlength="2" class="smalltextbox" <?=($f['maxle'] ? 'value="'.$f['maxle'].'" ' : ''); ?>/></td>
                            <td class="padded" width="100%">
                                <table><tr>
                                    <td>&nbsp;&nbsp;&nbsp;<?=Lang::npc('react', ['']); ?></td>
                                    <td>&nbsp;<select name="ra" onchange="fi_dropdownSync(this)" onkeyup="fi_dropdownSync(this)" style="background-color: #181818"<?=($f['ra'] ? ' class="q'.($f['ra'] == 1 ? '2' : ($f['ra'] == -1 ? '10' : '')).'"' : ''); ?>>
                                        <option></option>
                                        <option value="1" class="q2"<?=(  $f['ra'] ==  1 ? ' selected' : ''); ?>>A</option>
                                        <option value="0" class="q"<?=(   $f['ra'] ==  0 ? ' selected' : ''); ?>>A</option>
                                        <option value="-1" class="q10"<?=($f['ra'] == -1 ? ' selected' : ''); ?>>A</option>
                                    </select>
                                    <select name="rh" onchange="fi_dropdownSync(this)" onkeyup="fi_dropdownSync(this)" style="background-color: #181818"<?=($f['rh'] ? ' class="q'.($f['rh'] == 1 ? '2' : ($f['rh'] == -1 ? '10' : '')).'"' : ''); ?>>
                                        <option></option>
                                        <option value="1" class="q2"<?=(  $f['rh'] ==  1 ? ' selected' : ''); ?>>H</option>
                                        <option value="0" class="q"<?=(   $f['rh'] ==  0 ? ' selected' : ''); ?>>H</option>
                                        <option value="-1" class="q10"<?=($f['rh'] == -1 ? ' selected' : ''); ?>>H</option>
                                    </select>
                                    </td>
                                </tr></table>
                            </td>
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
