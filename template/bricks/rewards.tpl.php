<?php
    namespace Aowow\Template;

$offset ??= 0;                                              // in case we have multiple icons on the page (prominently quest-rewards)

if ($rewTitle):
    echo $rewTitle.' '.($extra ?? '')."\n";
endif;

if ($rewards):
?>
                <div class="pad"></div>
                <table class="icontab icontab-box">
                    <tr>
<?php
    $last = array_key_last($rewards);
    foreach ($rewards as $k => $icon):
        echo $icon->renderContainer(24, $offset);
        echo $k % 2 && $k != $last ? str_repeat(' ', 24) . "</tr><tr>" : '';
    endforeach;

    if (count($rewards) % 2):
        echo '<th style="display: none"></th><td style="display: none"></td>';
    endif;
?>
                    </tr>
                </table>

                <script type="text/javascript">//<![CDATA[
<?php
    foreach ($rewards as $icon):
        echo $icon->renderJS(20);
    endforeach;
?>
                //]]></script>
<?php
endif;
?>
