    <div id="ic<?php echo $this->typeId; ?>" style="float: left"></div>
    <div id="tt<?php echo $this->typeId; ?>" class="wowhead-tooltip" style="float: left; padding-top: 1px"></div>
    <div style="clear: left"></div>
    <div id="sl<?php echo $this->typeId; ?>" style="margin-left: 70px; margin-top: 4px;"></div>
    <div id="ks<?php echo $this->typeId; ?>" style="margin-left: 70px; margin-top: 4px;"></div>

<?php
$hasBuff = !empty($this->jsGlobals[6][2][$this->typeId]['buff']); // not set with items

if ($hasBuff):
?>
    <h3><?php echo Lang::spell('_aura'); ?></h3>
    <div id="btt<?php echo $this->typeId; ?>" class="wowhead-tooltip"></div>
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
    $WH.ge('ic<?php echo $this->typeId; ?>').appendChild(Icon.create('<?php echo $this->headIcons[0]; ?>', 2, null, 0, <?php echo $this->headIcons[1]; ?>));
    var
        tt  = $WH.ge('tt<?php echo $this->typeId; ?>'),
<?php if ($hasBuff): ?>
        btt = $WH.ge('btt<?php echo $this->typeId; ?>'),
<?php endif; ?>
        sl  = $WH.ge('sl<?php echo $this->typeId; ?>'),
        ks  = $WH.ge('ks<?php echo $this->typeId; ?>');

    tt.innerHTML = '<table><tr><td>' + ($WH.g_enhanceTooltip.bind(tt))(<?php echo $this->typeId; ?>, true, true, sl, null, [<?php echo $this->typeId; ?>], ks, null) + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
    $WH.Tooltip.fixSafe(tt, 1, 1);
<?php if ($hasBuff): ?>
    btt.innerHTML = '<table><tr><td>' + ($WH.g_enhanceTooltip.bind(btt))(<?php echo $this->typeId; ?>, true, true, sl, tt, [<?php echo $this->typeId; ?>], ks) + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
    $WH.Tooltip.fixSafe(btt, 1, 1);
<?php endif; ?>
//]]></script>
