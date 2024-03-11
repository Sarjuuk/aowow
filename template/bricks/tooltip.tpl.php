    <div id="ic<?=$this->typeId; ?>" style="float: left"></div>
    <div id="tt<?=$this->typeId; ?>" class="wowhead-tooltip" style="float: left; padding-top: 1px"></div>
    <div style="clear: left"></div>
    <div id="sl<?=$this->typeId; ?>" style="margin-left: 70px; margin-top: 4px;"></div>
    <div id="ks<?=$this->typeId; ?>" style="margin-left: 70px; margin-top: 4px;"></div>

<?php
$hasBuff = !empty($this->jsGlobals[Type::SPELL][2][$this->typeId]['buff']); // not set with items

if ($hasBuff):
?>
    <h3><?=Lang::spell('_aura'); ?></h3>
    <div id="btt<?=$this->typeId; ?>" class="wowhead-tooltip"></div>
<?php
endif;

/*
  tt:  Tooltip
  btt: Buff-Tooltip
  sl:  Slider for CharLevel
  ks:  Known Spells
*/
?>

<script type="text/javascript">//<![CDATA[
    $WH.ge('ic<?=$this->typeId; ?>').appendChild(Icon.create('<?=$this->headIcons[0]; ?>', 2, null, 0, <?=$this->headIcons[1]; ?>));
    var
        tt  = $WH.ge('tt<?=$this->typeId; ?>'),
<?php if ($hasBuff): ?>
        btt = $WH.ge('btt<?=$this->typeId; ?>'),
<?php endif; ?>
        sl  = $WH.ge('sl<?=$this->typeId; ?>'),
        ks  = $WH.ge('ks<?=$this->typeId; ?>');

    tt.innerHTML = '<table><tr><td>' + ($WH.g_enhanceTooltip.bind(tt))(<?=$this->typeId; ?>, true, true, sl, null, [<?=$this->typeId; ?>], ks) + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
<?php if ($hasBuff): ?>
    btt.innerHTML = '<table><tr><td>' + ($WH.g_enhanceTooltip.bind(btt))(<?=$this->typeId; ?>, true, true, sl, tt, [<?=$this->typeId; ?>], ks) + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
<?php endif; ?>

    $WH.Tooltip.fixSafe(tt, 1, 1);
<?php if ($hasBuff): ?>
    $WH.Tooltip.fixSafe(btt, 1, 1);
<?php endif; ?>
//]]></script>
