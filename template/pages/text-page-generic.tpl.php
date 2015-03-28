<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate');

if (isset($this->typeStr)):
?>
            <div class="pad3"></div>

            <div class="inputbox">
                <h1><?php echo Util::ucFirst($this->typeStr).' #'.$this->typeId; ?></h1>
                <div id="inputbox-error"><?php echo sprintf(Lang::main('pageNotFound'), $this->typeStr); ?></div>
<?php
else:
?>
            <div class="text">
                <h1><?php echo $this->name; ?></h1>

<?php
    $this->brick('article');

    if (isset($this->extraText)):
?>
                <div id="text-generic" class="left"></div>
                <script type="text/javascript">//<![CDATA[
                    Markup.printHtml("<?php echo Util::jsEscape($this->extraText); ?>", "text-generic", {
                        allow: Markup.CLASS_ADMIN,
                        dbpage: true
                    });
                //]]></script>

                <div class="pad2"></div>
<?php
    endif;

    if (isset($this->extraHTML)):
        echo $this->extraHTML;
    endif;

endif;
?>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
