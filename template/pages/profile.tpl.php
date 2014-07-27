<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

            <div id="profilah-generic"></div>
            <script type="text/javascript">//<![CDATA[
                var profilah = new Profiler();
                profilah.initialize('profilah-generic', { id: <?php echo $this->profileId; ?> });
                pr_setRegionRealm($WH.gE($WH.ge('topbar'), 'form')[0], '<?php echo $this->region; ?>', '<?php echo $this->realm; ?>');
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
