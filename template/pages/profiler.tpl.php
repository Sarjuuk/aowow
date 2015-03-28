<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

            <div class="text">
                <h1><?php echo Lang::main('_cpHead'); ?></h1>

                <p><?php echo Lang::main('_cpHint'); ?></p>

                <div class="pad"></div>

                <p><?php echo Lang::main('_cpHelp'); ?></p>

                <div class="profiler-home">
                    <div>
                        <h2><?php echo Util::ucFirst(Lang::main('name')).Lang::main('colon'); ?></h2>
                        <input type="text" name="na" value="" />
                    </div>

                    <div>
                        <h2><?php echo Lang::main('region').Lang::main('colon'); ?></h2>
                        <input type="radio" name="rg" value="us" id="rg-1" checked="checked" /><label for="rg-1" class="profiler-button profiler-option-left selected"><em><i>US &amp; Oceanic</i></em></label>
                        <input type="radio" name="rg" value="eu" id="rg-2" /><label for="rg-2" class="profiler-button profiler-option-right"><em><i>Europe</i></em></label>
                    </div>

                    <div>
                        <h2><?php echo Lang::main('realm').Lang::main('colon'); ?></h2>
                        <input type="text" name="sv" autocomplete="off" />
                        <div class="profiler-autocomplete"></div>
                    </div>

                    <div class="profiler-buttons">
                        <a href="javascript:;" class="profiler-button" id="profiler-lookup"><em><?php echo Lang::main('viewCharacter'); ?></em></a>
                        <a href="javascript:;" class="profiler-button" id="profiler-search"><em><?php echo Lang::main('searchButton'); ?></em></a>
                    </div>
                </div>

                <div class="clear pad3"></div>

                <p><?php echo Lang::main('_cpFooter'); ?></p>
            </div>

            <script type="text/javascript">//<![CDATA[
                pr_initProfilerHome();
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
