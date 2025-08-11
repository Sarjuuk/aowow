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

$this->brick('pageTemplate', ['fiQuery' => $this->filter->query, 'fiMenuItem' => [1]]);
?>
            <div id="fi" style="display: <?=($this->filter->query ? 'block' : 'none'); ?>;">
                <form action="?filter=spells<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="text">
<?php
$this->brick('headIcons');

$this->brick('redButtons');
?>
                        <h1><?=$this->h1; ?></h1>
                    </div>
                    <div class="rightpanel">
                        <div style="float: left"><?=Lang::game('school').Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['sc[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="sc[]" size="7" multiple="multiple" class="rightselect" style="width: 8em">
<?=$this->makeOptionsList(Lang::game('sc') , $f['sc'], 28,); ?>
                        </select>
                    </div>
<?php if ($this->classPanel): ?>
                    <div class="rightpanel2">
                        <div style="float: left"><?=$this->ucFirst(Lang::game('class')).Lang::main('colon'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['cl[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="cl[]" size="8" multiple="multiple" class="rightselect" style="width: 8em; background-color: #181818">
<?=$this->makeOptionsList(Lang::game('cl') , $f['cl'], 28, fn($v, $k, &$e) => $v && ($e = ['class' => 'c'.$k])); ?>
                        </select>
                    </div>
<?php
endif;

if ($this->glyphPanel):
?>
                    <div class="rightpanel2">
                        <div style="float: left"><?=Lang::game('glyphType'); ?></div>
                        <small><a href="javascript:;" onclick="document.forms['fi'].elements['gl[]'].selectedIndex = -1; return false" onmousedown="return false"><?=Lang::main('clear'); ?></a></small>
                        <div class="clear"></div>
                        <select name="gl[]" size="2" multiple="multiple" class="rightselect" style="width: 8em">
<?=$this->makeOptionsList(Lang::game('gl') , $f['gl'], 28); ?>
                        </select>
                    </div>
<?php endif; ?>
                    <table>
                        <tr>
                            <td><?=$this->ucFirst(Lang::main('name')).Lang::main('colon'); ?></td>
                            <td colspan="2">
                                <table><tr>
                                    <td>&nbsp;<input type="text" name="na" size="30" <?=($f['na'] ? 'value="'.$this->escHTML($f['na']).'" ' : ''); ?>/></td>
                                    <td>&nbsp; <input type="checkbox" name="ex" value="on" id="spell-ex" <?=($f['ex'] ? 'checked="checked" ' : ''); ?>/></td>
                                    <td><label for="spell-ex"><span class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, LANG.tooltip_extendedspellsearch, 0, 0, 'q')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><?=Lang::main('extSearch'); ?></span></label></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?=Lang::game('level').Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<input type="text" name="minle" maxlength="2" class="smalltextbox" <?=($f['minle'] ? 'value="'.$f['minle'].'" ' : ''); ?>/> - <input type="text" name="maxle" maxlength="2" class="smalltextbox" <?=($f['maxle'] ? 'value="'.$f['maxle'].'" ' : ''); ?>/></td>
                            <td class="padded">
                                <table cellpadding="0" cellspacing="0" border="0"><tr>
                                    <td>&nbsp;&nbsp;&nbsp;<?=Lang::game('reqSkillLevel'); ?></td>
                                    <td>&nbsp;<input type="text" name="minrs" maxlength="3" class="smalltextbox2" <?=($f['minrs'] ? 'value="'.$f['minrs'].'" ' : ''); ?>/> - <input type="text" name="maxrs" maxlength="3" class="smalltextbox2" <?=($f['maxrs'] ? 'value="'.$f['maxrs'].'" ' : ''); ?>/></td>
                                </tr></table>
                            </td>
                        </tr><tr>
                            <td class="padded"><?=$this->ucFirst(Lang::game('race')).Lang::main('colon'); ?></td>
                            <td class="padded">&nbsp;<select name="ra">
                                <option></option>
<?=$this->makeOptionsList(Lang::game('ra') , $f['ra'], 28, fn($v, $k) => $v && $k > 0); ?>
                            </select></td>
                            <td class="padded"></td>
                        </tr><tr>
                            <td class="padded"><?=Lang::game('mechAbbr'); ?></td>
                            <td class="padded">&nbsp;<select name="me">
                                <option></option>
<?=$this->makeOptionsList(Lang::game('me') , $f['me'], 28); ?>
                            </select></td>
                            <td>
                                <table cellpadding="0" cellspacing="0" border="0"><tr>
                                    <td class="padded">&nbsp;&nbsp;&nbsp;<?=Lang::game('dispelType').Lang::main('colon'); ?></td>
                                    <td class="padded">&nbsp;<select name="dt">
                                        <option></option>
<?=$this->makeOptionsList(Lang::game('dt') , $f['dt'], 28, fn($v, $k) => $v && $k != 7 && $k != 8); ?>
                                    </select></td>
                                </tr></table>
                            </td>
                        </tr>
                    </table>

                    <div id="fi_criteria" class="padded criteria"><div></div></div><div><a href="javascript:;" id="fi_addcriteria" onclick="fi_addCriterion(this); return false"><?=Lang::main('addFilter'); ?></a></div>

                    <div class="padded2">
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
