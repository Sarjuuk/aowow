<?php
if (isset($this->map) && empty($this->map)):
    echo Lang::zone('noMap');
elseif (!empty($this->map['data'])):
    if ($this->type == TYPE_QUEST) :
        echo "            <div id=\"mapper-zone-generic\"></div>\n";
    elseif ($this->type != TYPE_ZONE):
        echo '            <div>';

        if ($this->type == TYPE_OBJECT):
            echo Lang::gameObject('foundIn');
        elseif ($this->type == TYPE_SOUND):
            echo Lang::sound('foundIn');
        elseif ($this->type == TYPE_NPC):
            echo Lang::npc('foundIn');
        elseif ($this->type == TYPE_AREATRIGGER):
            echo Lang::areatrigger('foundIn');
        else:
            echo "UNKNOWN TYPE";
        endif;

        echo ' <span id="mapper-zone-generic">';

        $extra = $this->map['extra'];
        echo Lang::concat($this->map['mapperData'], true, function ($areaData, $areaId) use ($extra) {
            return '<a href="javascript:;" onclick="myMapper.update({zone: '.$areaId.'}); g_setSelectedLink(this, \'mapper\'); return false" onmousedown="return false">'.$extra[$areaId].'</a>&nbsp;('.array_sum(array_column($areaData, 'count')).')';
        });

        echo ".</span></div>\n";
    endif;

    if (!empty($this->map['data']['zone']) && $this->map['data']['zone'] < 0):
?>
            <div id="mapper" style="width: 778px; margin: 0 auto">
<?php
        if (isset($this->map['som'])):
?>
                <div id="som-generic"></div>
<?php
        endif;
?>
                <div id="mapper-generic"></div>
                <div class="pad clear"></div>
            </div>
<?php
    else:
?>
<?php
        if (isset($this->map['som'])):
?>
            <div class="pad"></div>
            <div id="som-generic"></div>
<?php
        endif;
?>
            <div id="mapper-generic"></div>
            <div style="clear: left"></div>
<?php
    endif;
?>

            <script type="text/javascript">//<![CDATA[
<?php
    if (!empty($this->map['data']['zone'])):
        echo "                ".(!empty($this->gPageInfo) ? "$.extend(g_pageInfo, {id: ".$this->map['data']['zone']."})" : "var g_pageInfo = {id: ".$this->map['data']['zone']."}").";\n";
    elseif (isset($this->map['mapperData'])):
        echo "                var g_mapperData = ".Util::toJSON($this->map['mapperData'], empty($this->map['mapperData']) ? JSON_FORCE_OBJECT : 0).";\n";
    endif;

    // dont forget to set "parent: 'mapper-generic'"
    echo "                var myMapper = new Mapper(".Util::toJSON($this->map['data']).");\n";

    if (isset($this->map['som'])):
        echo "                new ShowOnMap(".Util::toJSON($this->map['som']).");\n";
    endif;

    if ($this->type != TYPE_ZONE && $this->type != TYPE_QUEST):
        echo "                \$WH.gE(\$WH.ge('mapper-zone-generic'), 'a')[0].onclick();\n";
    endif;

if (User::isIngroup(U_GROUP_MODERATOR)):
?>

                function spawnposfix(type, typeguid, area, floor)
                {
                    $.ajax({
                        type: 'GET',
                        url: '?admin=spawn-override',
                        data: { type: type, guid: typeguid, area : area, floor: floor, action: 1 },
                        dataType: 'json',
                        success: function (rsp) {
                            if (rsp > 0)
                                location.reload(true);
                            else if (rsp /* == x */)
                                alert('move failed. details tbd');
                        },
                    });
                }
<?php
endif;
?>
            //]]></script>
<?php endif; ?>
