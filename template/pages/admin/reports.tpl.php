<?php $this->brick('header'); ?>

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
                <h1><?=$this->name;?></h1>

<?php
    $this->brick('article');

    if (isset($this->extraText)):
?>
                <div id="text-generic" class="left"></div>
                <script type="text/javascript">//<![CDATA[
                    Markup.printHtml("<?=Util::jsEscape($this->extraText);?>", "text-generic", {
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
?>
                <h2>Edit<span id="wt-name"></span><span id="status-ic" style="float:right;"></span></h2>
                <div class="wt-edit">
                    <div style="display:inline-block; vertical-align:top;"><div class="pad2" style="color: white; font-size: 15px; font-weight: bold;">Icon</div><input type="text" id="wt-icon" size="30" /></div>
                    <div id="ic-container" style="display: inline-block; clear: left;"></div>
                </div>
                <div class="wt-edit">
                    <div class="pad2" style="color: white; font-size: 15px; font-weight: bold;">Scale</div>
                    <div id="su-container"></div>
                </div>
                <div id="su-controls" class="wt-edit" style="width:auto;"></div>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
