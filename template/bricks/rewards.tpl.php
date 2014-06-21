<?php

if (!isset($offset))                                        // in case we have multiple icons on the page (prominently quest-rewards)
    $offset = 0;

if ($rewards['title']):
    echo $rewards['title'].Lang::$main['colon'].(isset($rewards['extra']) ? $rewards['extra'] : null);
endif;
if ($rewards['data']):
?>
                <div class="pad"></div>
                <table class="icontab icontab-box">
                    <tr>
<?php
    foreach ($rewards['data'] as $k => $i):
        echo '<th id="icontab-icon'.($k + 1 + $offset).'"></th><td><span class="q'.(isset($i['quality']) ? $i['quality'] : null).'"><a href="?'.$i['typeStr'].'='.$i['id'].'">'.$i['name']."</a></span></td>\n";
        echo $k % 2 ? '</tr><tr>' : null;
    endforeach;

    if (count($rewards['data']) % 2):
        echo '<th style="display: none"></th><td style="display: none"></td>';
    endif;
?>
                    </tr>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
    foreach ($rewards['data'] as $k => $i):
        echo '                    $WH.ge(\'icontab-icon'.($k + 1 + $offset).'\').appendChild('.$i['globalStr'].'.createIcon('.$i['id'].', 1, '.(@$i['qty'] ?: 0)."));\n";
    endforeach;
?>
                //]]></script>
<?php
endif;
?>
