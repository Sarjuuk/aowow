<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    $this->brick('header');
?>
    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

    $this->brick('infobox');
?>

            <div class="text">
<?php
$this->brick('headIcons');

$this->brick('redButtons');
?>

                <h1><?=$this->h1; ?></h1>
                <?=$this->description.PHP_EOL; ?>
                <h3><?=Lang::achievement('criteria').($this->reqCrtQty ? ' &ndash; <small><b>'.Lang::achievement('reqNumCrt', [$this->reqCrtQty, count($this->criteria)]).'</b></small>' : ''); ?></h3>
<?php
$rows0 = $rows1 = '';
foreach ($this->criteria as $i => $icon):
    // every odd number of elements
    ${'rows' . ($i % 2)} .= $icon->renderContainer(20, $i, true);
endforeach;

if ($rows0):
    echo "                <div style=\"float: left; margin-right: 25px\"><table class=\"iconlist\">\n".$rows0."                </table></div>\n";
endif;
if ($rows1):
    echo "                <div style=\"float: left;\"><table class=\"iconlist\">\n".$rows1."                </table></div>\n";
endif;
?>

                <script type="text/javascript">//<![CDATA[
<?php
foreach ($this->criteria as $crt):
    echo $crt->renderJS(24);
endforeach;
?>
                //]]></script>

                <div style="clear: left"></div>

<?php
if ([$rewItems, $rewTitle, $rewText] = $this->rewards):
    if ($rewItems):
        echo '<h3>'.Lang::main('rewards')."</h3>\n";
        $this->brick('rewards', ['rewards' => $rewItems, 'rewTitle' => null]);
    endif;

    if ($rewTitle):
        echo '<h3>'.Lang::main('gains')."</h3>\n<ul>";
        foreach ($rewTitle as $i):
            echo '    <li><div>'.$i."</div></li>\n";
        endforeach;
        echo "</ul>\n";
    endif;

    if (!$rewTitle && !$rewItems && $rewText):
        echo '<h3>'.Lang::main('rewards')."</h3>\n<ul><li><div>".$rewText."</div></li></ul>\n";
    endif;
endif;

$this->brickIf($this->mail, 'mail');

if ($this->transfer):
    echo "    <div style=\"clear: left\"></div>";
    echo "    <div class=\"pad\"></div>\n    ".$this->transfer."\n";
endif;

?>

                <h2 class="clear"><?=Lang::main('related'); ?></h2>
            </div>

<?php
$this->brick('lvTabs');

$this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
