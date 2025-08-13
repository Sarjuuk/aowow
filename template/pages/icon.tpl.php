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
    $this->brick('redButtons');
?>

                <h1><?=$this->h1; ?></h1>
                <div id="h1-icon-0" class="h1-icon"></div>
                <script type="text/javascript">//<![CDATA[
                    $WH.ge('h1-icon-0').appendChild(Icon.create("<?=$this->icon;?>", 2));
                //]]></script>
<?php
    $this->brick('markup', ['markup' => $this->article]);
?>
                <div class="clear"></div>
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
