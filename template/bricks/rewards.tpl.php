<?php
if ($this->rewards['title']):
    echo $this->rewards['title'].Lang::$main['colon'].(isset($this->rewards['extra']) ? $this->rewards['extra'] : null);
endif;
if ($this->rewards['data']):
?>
                <div class="pad"></div>
                <table class="icontab icontab-box">
                    <tr>
<?php
    foreach ($this->rewards['data'] as $k => $i):
        echo '<th id="icontab-icon'.($k + 1 + $offset).'"></th><td><span class="q'.(isset($i['quality']) ? $i['quality'] : null).'"><a href="?'.$i['typeStr'].'='.$i['id'].'">'.$i['name']."</a></span></td>\n";
        echo $k % 2 ? '</tr><tr>' : null;
    endforeach;

    if (count($this->rewards['data']) % 2):
        echo '<th style="display: none"></th><td style="display: none"></td>';
    endif;
?>
                    </tr>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
    foreach ($this->rewards['data'] as $k => $i):
        if (isset()):
            echo "\$WH.ge('icontab-icon".($k + 1 + $offset)."').appendChild(".$i['globalStr'].".createIcon(".$i['id'].", 1, ".(@$i['qty'] ?: 0)."));\n";
        endif;
    endforeach;
?>
                //]]></script>
<?php
endif;
?>