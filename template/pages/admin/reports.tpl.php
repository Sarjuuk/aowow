<?php
    namespace Aowow\Template;

    $this->brick('header');
?>

    <script type="text/javascript">
    </script>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate');
?>
            <div class="text">
                <h1><?=$this->h1;?></h1>

<?php
    $this->brick('markup', ['markup' => $this->article]);

    $this->brick('markup', ['markup' => $this->extraText]);

    echo $this->extraHTML ?? '';
?>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
