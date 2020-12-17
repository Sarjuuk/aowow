<?php $this->brick('header'); ?>

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

                <h1<?=(isset($this->expansion) ? ' class="h1-icon"><span class="icon-'.$this->expansion.'-right">'.$this->name.'</span>' : '>'.$this->name); ?></h1>

<?php
    $this->brick('article');

if (isset($this->extraText)):
?>
    <div id="text-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("<?=$this->extraText; ?>", "text-generic", {
            allow: Markup.CLASS_ADMIN,
            dbpage: true
        });
    //]]></script>

    <div class="pad2"></div>
<?php
endif;

    $this->brick('mapper');

if (!empty($this->transfer)):
    echo "    <div class=\"pad\"></div>\n    ".$this->transfer."\n";
endif;

if (isset($this->smartAI)):
?>
    <div id="text-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("<?=$this->smartAI; ?>", "text-generic", {
            allow: Markup.CLASS_ADMIN,
            dbpage: true
        });
    //]]></script>

    <div class="pad2"></div>
<?php
endif;

if (!empty($this->zoneMusic)):
?>
                <div class="clear">
<?php
    if (!empty($this->zoneMusic['music'])):
?>
                <div id="zonemusicdiv-zonemusic" style="float: left">
                    <h3><?=Lang::sound('music'); ?></h3>
                </div>
                <script type="text/javascript">//<![CDATA[
                    (new AudioControls()).init(<?=Util::toJSON($this->zoneMusic['music']); ?>, $WH.ge('zonemusicdiv-zonemusic'), {loop: true});
                //]]></script>
<?php
    endif;
    if (!empty($this->zoneMusic['intro'])):
?>
                <div id="zonemusicdiv-zonemusicintro" style="float: left">
                <h3><?=Lang::sound('intro'); ?></h3>

                </div>
                <script type="text/javascript">//<![CDATA[
                    (new AudioControls()).init(<?=Util::toJSON($this->zoneMusic['intro']); ?>, $WH.ge('zonemusicdiv-zonemusicintro'), {});
                //]]></script>
<?php
    endif;
    if (!empty($this->zoneMusic['ambience'])):
?>
                <div id="zonemusicdiv-soundambience" style="float: left">
                <h3><?=Lang::sound('ambience'); ?></h3>

                </div>
                <script type="text/javascript">//<![CDATA[
                    (new AudioControls()).init(<?=Util::toJSON($this->zoneMusic['ambience']); ?>, $WH.ge('zonemusicdiv-soundambience'), {loop: true});
                //]]></script>
<?php
    endif;
?>
                <br clear="all"/></div>
<?php
endif;
?>
                <h2 class="clear"><?=Lang::main('related'); ?></h2>
            </div>
<?php
    $this->brick('lvTabs', ['relTabs' => true]);

    $this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
