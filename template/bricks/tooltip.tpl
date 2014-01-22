    <div id="ic{$page.typeId}" style="float: left"></div>
    <div id="tt{$page.typeId}" class="tooltip" style="float: left; padding-top: 1px"></div>
    <div style="clear: left"></div>
    <div id="sl{$page.typeId}" style="margin-left: 70px; margin-top: 4px;"></div>
    <div id="ks{$page.typeId}" style="margin-left: 70px; margin-top: 4px;"></div>

{if !empty($jsGlobals[6][2].buff)} {* not set with items *}
    <h3>{$lang._aura}</h3>
    <div id="btt{$page.typeId}" class="tooltip"></div>
{/if}

<script type="text/javascript">//<![CDATA[
    $WH.ge('ic{$page.typeId}').appendChild(Icon.create('{$lvData.page.icon}', 2, null, 0, {$lvData.page.stack}));
    var
        tt  = $WH.ge('tt{$page.typeId}'),
{if !empty($jsGlobals[6][2].buff)}
        btt = $WH.ge('btt{$page.typeId}'),
{/if}
        sl  = $WH.ge('sl{$page.typeId}'),
        ks  = $WH.ge('ks{$page.typeId}');

    tt.innerHTML = '<table><tr><td>' + ($WH.g_enhanceTooltip.bind(tt))({$page.typeId}, true, true, sl, null, [{$page.typeId}], ks, null) + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
    $WH.Tooltip.fixSafe(tt, 1, 1);
{if !empty($jsGlobals[6][2].buff)}
    btt.innerHTML = '<table><tr><td>' + ($WH.g_enhanceTooltip.bind(btt))({$page.typeId}, true, true, sl, tt, [{$page.typeId}], ks) + '</td><th style="background-position: top right"></th></tr><tr><th style="background-position: bottom left"></th><th style="background-position: bottom right"></th></tr></table>';
    $WH.Tooltip.fixSafe(btt, 1, 1);
{/if}
//]]></script>
