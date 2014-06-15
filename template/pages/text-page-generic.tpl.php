<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcements');

if (isset($this->subject)):
?>
            <div class="pad3"></div>

            <div class="inputbox">
                <h1><?php echo Util::ucFirst($this->subject).' #'.$this->typeId; ?></h1>
                <div id="inputbox-error"><?php echo sprintf(Lang::$main['pageNotFound'], $this->subject); ?></div>
<?php
else:
?>
            <script type="text/javascript">//<![CDATA[
                var g_pageInfo = <?php echo json_encode($this->gPageInfo, JSON_NUMERIC_CHECK); ?>;
    <?php echo isset($this->path) ? 'g_initPath('.json_encode($this->path, JSON_NUMERIC_CHECK).');' : null; ?>
            //]]></script>

            <div class="text">
                <h1><?php echo $this->name; ?></h1>

<?php
    $this->brick('article');

if (isset($this->extraText)):
?>
                <div id="text-generic" class="left"></div>
                <script type="text/javascript">//<![CDATA[
                    Markup.printHtml("<?php echo Util::jsEscape($extraText); ?>", "text-generic", {
                        allow: Markup.CLASS_ADMIN,
                        dbpage: true
                    });
                //]]></script>

                <div class="pad2"></div>
<?php
    endif;
endif;
?>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
