<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

        <div id="<?php echo $this->tcType; ?>-classes">
                <div id="<?php echo $this->tcType; ?>-classes-outer">
                    <div id="<?php echo $this->tcType; ?>-classes-inner"><p><?php echo ($this->tcType == 'tc' ? Lang::main('chooseClass') : Lang::main('chooseFamily')) . Lang::main('colon'); ?></p></div>
                </div>
            </div>
            <div id="<?php echo $this->tcType; ?>-itself"></div>
            <script type="text/javascript">
                <?php echo $this->tcType; ?>_init();
            </script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
