<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

if (['header' => $header, 'subject' => $subject, 'text' => $text, 'attachments' => $attachments] = $this->mail):
    $offset ??= 0;                                          // in case we have multiple icons on the page (prominently quest-rewards)

    echo '                        <h3>'.Lang::mail('mailDelivery', $header)."</h3>\n";

    if ($subject):
        echo '                        <div class="book"><div class="page">'.$subject."</div></div>\n";
    endif;

    if ($text):
        echo '                        <div class="book" style="float:left; margin-bottom:26px;"><div class="page">'.$text."</div></div>\n";
    endif;

    if ($attachments):
?>
                <table class="icontab icontab-box" style="padding-left:10px;">
<?php
        foreach ($attachments as $icon):
            echo $icon->renderContainer(20, $offset, true);
        endforeach;
?>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
        foreach ($attachments as $icon):
            echo $icon->renderJS(20);
        endforeach;
?>
                //]]></script>
<?php
    endif;
endif;
?>
