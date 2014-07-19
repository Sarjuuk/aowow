<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php $this->brick('announcement'); ?>

            <script type="text/javascript">
                g_initPath(<?php echo json_encode($this->path, JSON_NUMERIC_CHECK); ?>);
                var temp_path = <?php echo json_encode($this->path, JSON_NUMERIC_CHECK); // kill with pageTemplate ?>;
<?php
    /*
        PageTemplate.set({pageName: 'profile', activeTab: 1, breadcrumb: [1,5,0,'eu']});
        PageTemplate.init();

        old:
        var g_serverTime = new Date('2010/04/11 01:50:39');
        var g_pageName = 'profile';                         // is not used in any jScript..?
        var g_pageValue = '';                               // is not used in any jScript..?
    */
?>
            </script>

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
