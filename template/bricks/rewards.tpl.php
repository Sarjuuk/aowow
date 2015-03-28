<?php

if (!isset($offset))                                        // in case we have multiple icons on the page (prominently quest-rewards)
    $offset = 0;

if ($rewTitle):
    echo $rewTitle.Lang::main('colon').(isset($extra) ? $extra : null);
endif;

if ($rewards):
?>
                <div class="pad"></div>
                <table class="icontab icontab-box">
                    <tr>
<?php
    foreach ($rewards as $k => $i):
        echo '<th id="icontab-icon'.($k + 1 + $offset).'"></th><td><span class="q'.(isset($i['quality']) ? $i['quality'] : null).'"><a href="?'.$i['typeStr'].'='.$i['id'].'">'.$i['name']."</a></span></td>\n";
        echo $k % 2 ? '</tr><tr>' : null;
    endforeach;

    if (count($rewards) % 2):
        echo '<th style="display: none"></th><td style="display: none"></td>';
    endif;
?>
                    </tr>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
    foreach ($rewards as $k => $i):
        echo '                    $WH.ge(\'icontab-icon'.($k + 1 + $offset).'\').appendChild('.$i['globalStr'].'.createIcon('.$i['id'].', 1, '.(empty($i['qty']) ? 0 : $i['qty'])."));\n";
    endforeach;
?>
                //]]></script>
<?php
endif;
?>
