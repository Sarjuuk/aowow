<?php
if ($m = $this->mail):
    if (!isset($offset))                                        // in case we have multiple icons on the page (prominently quest-rewards)
        $offset = 0;

    echo '                        <h3>'.sprintf(Lang::mail('mailDelivery'), $m['id'], $m['sender'], $m['delay'])."</h3>\n";

    if ($m['subject']):
        echo '                        <div class="book"><div class="page">'.$m['subject']."</div></div>\n";
    endif;

    if ($m['text']):
        echo '                        <div class="book" style="float:left; margin-bottom:26px;"><div class="page">'.$m['text']."</div></div>\n";
    endif;

    if ($m['attachments']):
?>
                <table class="icontab icontab-box" style="padding-left:10px;">
<?php
        foreach ($m['attachments'] as $k => $i):
            echo '<tr><th id="icontab-icon'.($k + 1 + $offset).'"></th><td><span class="q'.(isset($i['quality']) ? $i['quality'] : null).'"><a href="?'.$i['typeStr'].'='.$i['id'].'">'.$i['name']."</a></span></td></tr>\n";
        endforeach;
?>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
        foreach ($m['attachments'] as $k => $i):
            echo '                    $WH.ge(\'icontab-icon'.($k + 1 + $offset).'\').appendChild('.$i['globalStr'].'.createIcon('.$i['id'].', 1, '.(empty($i['qty']) ? 0 : $i['qty'])."));\n";
        endforeach;
?>
                //]]></script>
<?php
    endif;
endif;
?>
