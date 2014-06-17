<?php $this->brick('header'); ?>

    <div id="main">
        <div id="main-precontents"></div>
        <div id="main-contents" class="main-contents">

<?php $this->brick('announcement'); ?>

        <div id="<?php echo $this->tcType; ?>-classes">
                <div id="<?php echo $this->tcType; ?>-classes-outer">
                    <div id="<?php echo $this->tcType; ?>-classes-inner"><p><?php echo ($this->tcType == 'tc' ? Lang::$main['chooseClass'] : Lang::$main['chooseFamily']) . Lang::$main['colon']; ?></p></div>
                </div>
            </div>
            <div id="<?php echo $this->tcType; ?>-itself"></div>
            <script type="text/javascript">
                <?php echo $this->tcType; ?>_init();
            </script>
            <div class="clear"></div>
        </div>
    </div>

<?php $this->brick('footer'); ?>
