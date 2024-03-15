    <tr><th id="infobox-series"><?=$listTitle ?: Lang::achievement('series'); ?></th></tr>
    <tr><td>
        <div class="infobox-spacer"></div>
        <table class="series">
<?php
foreach ($list as $idx => $itr):
    echo '            <tr><th>'.($idx + 1).'</th><td><div>';

    $_   = array_keys($itr);
    $end = array_pop($_);
    foreach ($itr as $k => $i):                             // itemItr
        switch ($i['side']):
            case 1:  $wrap = '<span class="icon-alliance-padded">%s</span>'; break;
            case 2:  $wrap = '<span class="icon-horde">%s</span>'; break;
            default: $wrap = '%s'; break;
        endswitch;

        if ($i['typeId'] == $this->typeId):
            echo sprintf($wrap, '<b>'.$i['name'].'</b>');
        else:
            echo sprintf($wrap, '<a href="?'.$i['typeStr'].'='.$i['typeId'].'">'.$i['name'].'</a>');
        endif;

        echo $end == $k ? null : '<br />';
    endforeach;
    echo "</div></td></tr>\n";
endforeach;
?>
        </table>
    </td></tr>
