<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

if ([$mapper, $mapperData, $som, $foundIn] = $this->map):
    if ($foundIn):
        echo '            <div>'.$foundIn[0].' <span id="mapper-zone-generic">';
        echo Lang::concat($mapperData, true, function ($areaData, $areaId) use ($foundIn) {
            return '<a href="javascript:;" onclick="myMapper.update({zone: '.$areaId.'}); g_setSelectedLink(this, \'mapper\'); return false" onmousedown="return false">'.$foundIn[$areaId].'</a>&nbsp;('.array_sum(array_column($areaData, 'count')).')';
        });
        echo ".</span></div>\n";
    else:
        echo "            <div id=\"mapper-zone-generic\"></div>\n";
    endif;

    if (isset($mapper['zone']) && $mapper['zone'] < 0):
?>
            <div id="mapper" style="width: 778px; margin: 0 auto">
<?php
        if ($som):
?>
                <div id="som-generic"></div>
<?php
        endif;
?>
                <div id="mapper-generic"></div>
                <div class="pad clear"></div>
            </div>
<?php
    elseif ($mapper):
        if ($som):
?>
            <div class="pad"></div>
            <div id="som-generic"></div>
<?php
        endif;
?>
            <div id="mapper-generic"></div>
            <div style="clear: left"></div>
<?php
    else:
?>
            <?=Lang::zone('noMap');?>
            <div class="pad"></div>
<?php
    endif;
?>
            <script type="text/javascript">//<![CDATA[
<?php if (isset($mapper['zone']) && $this->gPageInfo): ?>
                $.extend(g_pageInfo, {id: <?=$mapper['zone'];?>});
<?php elseif (isset($mapper['zone'])): ?>
                var g_pageInfo = {id: <?=$mapper['zone'];?>};
<?php elseif ($mapperData): ?>
                var g_mapperData = <?=$this->json($mapperData);?>;
<?php endif; ?>
                var myMapper = new Mapper(<?=$this->json($mapper);?>);
<?php if ($som): ?>
                new ShowOnMap(<?=$this->json($som);?>);
<?php endif;
      if ($foundIn): ?>
                $WH.gE($WH.ge('mapper-zone-generic'), 'a')[0].onclick();
<?php endif;
      if ($this->user::isInGroup(U_GROUP_MODERATOR)): ?>

                function spawnposfix(type, typeguid, area, floor)
                {
                    $.ajax({
                        type: 'GET',
                        url: '?admin=spawn-override',
                        data: { type: type, guid: typeguid, area : area, floor: floor },
                        dataType: 'json',
                        success: function (rsp) {
                            if (rsp /* == x */)
                                alert('move failed. details tbd');
                            else
                                location.search += "&refresh";
                        },
                    });
                }
<?php endif; ?>
            //]]></script>
<?php endif; ?>
