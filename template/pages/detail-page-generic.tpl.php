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

    if ($this->expansion && $this->h1):
        echo '                <h1 class="h1-icon"><span class="icon-'.$this->expansion.'-right">'.$this->h1."</span></h1>\n";
    elseif ($this->h1):
        echo '                <h1>'.$this->h1."</h1>\n";
    endif;

    $this->brick('markup', ['markup' => $this->article]);

    $this->brick('markup', ['markup' => $this->extraText]);

    $this->brick('mapper');

    if ($this->transfer):
        echo "    <div class=\"pad\"></div>\n    ".$this->transfer."\n";
    endif;

    $this->brick('markup', ['markup' => $this->smartAI]);

if ($this->zoneMusic):
?>
                <div class="clear">
<?php
    foreach ($this->zoneMusic as [$h3, $data, $divId, $opts]):
?>
                <div id="zonemusicdiv-<?=$divId; ?>" style="float: left">
                    <h3><?=$h3; ?></h3>
                </div>
                <script type="text/javascript">//<![CDATA[
                    (new AudioControls()).init(<?=$this->json($data); ?>, $WH.ge('zonemusicdiv-<?=$divId; ?>'), <?=$this->json($opts); ?>);
                //]]></script>
<?php
    endforeach;
?>
                <br clear="all"/></div>
<?php
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
