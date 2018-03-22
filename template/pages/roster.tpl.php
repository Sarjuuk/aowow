<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate');

?>
<div id="roster-status" class="profiler-message" style="display: none"></div>

            <div class="text">
<?php $this->brick('redButtons'); ?>
                <h1 class="first"><?=$this->name; ?></h1>

<?php
    // subject statistics here
    if (isset($this->extraHTML)):
        echo $this->extraHTML;
    endif;
?>

            </div>
<?php
    $this->brick('lvTabs');
?>
            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
