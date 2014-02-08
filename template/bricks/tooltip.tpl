    <div id="ic{$typeId}" style="float: left"></div>
    <div id="tt{$typeId}" class="tooltip" style="float: left; padding-top: 1px"></div>
    <div style="clear: left"></div>
    <div id="sl{$typeId}" style="margin-left: 70px; margin-top: 4px;"></div>
    <div id="ks{$typeId}" style="margin-left: 70px; margin-top: 4px;"></div>

{if !empty($jsGlobals[6][2].buff)} {* not set with items *}
    <h3>{$lang._aura}</h3>
    <div id="btt{$typeId}" class="tooltip"></div>
{/if}

<script type="text/javascript">//<![CDATA[
    $WH.ge('ic{$typeId}').appendChild(Icon.create('{$headIcons[0]}', 2, null, 0, {$headIcons[1]}));
    var
        tt  = $WH.ge('tt{$typeId}'),
{if !empty($jsGlobals[6][2].buff)}
        btt = $WH.ge('btt{$typeId}'),
{/if}
        sl  = $WH.ge('sl{$typeId}'),
        ks  = $WH.ge('ks{$typeId}');

    tt.innerHTML = '<table><tr><td>' + ($WH.g_enhanceTooltip.bind(tt))({$typeId}, true, true, sl, null, [{$typeId}], ks, null) + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
    $WH.Tooltip.fixSafe(tt, 1, 1);
{if !empty($jsGlobals[6][2].buff)}
    btt.innerHTML = '<table><tr><td>' + ($WH.g_enhanceTooltip.bind(btt))({$typeId}, true, true, sl, tt, [{$typeId}], ks) + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
    $WH.Tooltip.fixSafe(btt, 1, 1);
{/if}
//]]></script>
