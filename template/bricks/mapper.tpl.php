<?php
if (isset($this->map) && empty($this->map)):
    echo Lang::zone('noMap');
elseif (!empty($this->map['data'])):
    if ($this->type != TYPE_ZONE):
        echo '            <div>'.($this->type == TYPE_OBJECT ? Lang::gameObject('foundIn') : Lang::npc('foundIn')).' <span id="locations">';

        $n = count($this->map['mapperData']);
        $i = 0;
        foreach ($this->map['mapperData'] as $areaId => $areaData):
            if ($n > 1 && $i++ > 0):
                echo $i < $n ? ', ' : Lang::main('and');
            endif;

            echo '<a href="javascript:;" onclick="myMapper.update({zone: '.$areaId.'}); g_setSelectedLink(this, \'mapper\'); return false" onmousedown="return false">'.$this->map['extra'][$areaId].'</a>&nbsp;('.reset($areaData)['count'].')';
        endforeach;

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
            <div class="pad"></div>
<?php
        if (isset($this->map['som'])):
?>
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
    elseif (!empty($this->map['mapperData'])):
        echo "                var g_mapperData = ".Util::toJSON($this->map['mapperData']).";\n";
    endif;

    // dont forget to set "parent: 'mapper-generic'"
    echo "                var myMapper = new Mapper(".Util::toJSON($this->map['data']).");\n";

    if (isset($this->map['som'])):
        echo "                new ShowOnMap(".Util::toJSON($this->map['som']).");\n";
    endif;

    if ($this->type != TYPE_ZONE):
        echo "                                    \$WH.gE(\$WH.ge('locations'), 'a')[0].onclick();\n";
    endif;
?>
            //]]></script>
<?php endif; ?>
