<?php
if ($m = $this->mail):
    echo '                        <h3>'.sprintf(Lang::quest('mailDelivery'), $m['sender'], $m['delay'])."</h3>\n";

    if ($m['subject']):
        echo '                        <div class="book"><div class="page">'.$m['subject']."</div></div>\n";
    endif;

    if ($m['text']):
        echo '                        <div class="book"><div class="page">'.$m['text']."</div></div>\n";
    endif;
endif;
?>
