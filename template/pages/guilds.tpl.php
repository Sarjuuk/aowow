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
                action="?filter=guilds&<?=$this->subCat; ?>" method="post" name="fi" onsubmit="return fi_submit(this)" onreset="return fi_reset(this)">
                    <div class="text">
<?php
$this->brick('headIcons');

$this->brick('redButtons');
?>
                        <h1><?=$this->h1; ?></h1>
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
                            <td class="padded">&nbsp;</td>
                            <td class="padded">&nbsp;</td>
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

<?=$this->renderFilter(12); ?>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
