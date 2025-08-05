<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

if ($m = $this->mail):
    $offset ??= 0;                                          // in case we have multiple icons on the page (prominently quest-rewards)

    echo '                        <h3>'.Lang::mail('mailDelivery', $m['header'])."</h3>\n";

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
        foreach ($m['attachments'] as $icon):
            echo $icon->renderContainer(20, $offset, true);
        endforeach;
?>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
        foreach ($m['attachments'] as $icon):
            echo $icon->renderJS();
        endforeach;
?>
                //]]></script>
<?php
    endif;
endif;
?>
