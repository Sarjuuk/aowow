<?php
    namespace Aowow\Template;

    $this->brick('header');
?>
    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

            <div class="text">
                <h1><?=$this->h1;?></h1>
                <p><?=$this->privReqPoints;?></p><br />
<?php
    $this->brick('markup', ['markup' => $this->article]);
?>
            </div>
            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
