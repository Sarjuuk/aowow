<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>

    <tr><th id="infobox-series"><?=$listTitle ?: Lang::achievement('series'); ?></th></tr>
    <tr><td>
        <div class="infobox-spacer"></div>
        <table class="series">
<?php
foreach ($list as $idx => $itr):
    echo $this->renderSeriesItem($idx, $itr, 12);
endforeach;
?>
        </table>
    </td></tr>
