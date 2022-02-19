<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

            <div class="text">
                <h1><?=Lang::profiler('profiler'); ?></h1>

                <p><?=Lang::profiler('_cpHint'); ?></p>

                <div class="pad"></div>

                <p><?=Lang::profiler('_cpHelp'); ?></p>

                <div class="profiler-home">
                    <div>
                        <h2><?=Util::ucFirst(Lang::main('name')).Lang::main('colon'); ?></h2>
                        <input type="text" name="na" value="" />
                    </div>

                    <div>
                        <h2><?=Lang::profiler('region').Lang::main('colon'); ?></h2>
<?php
foreach (Util::$regions as $idx => $n):
    echo '                        <input type="radio" name="rg" value="'.$n.'" id="rg-'.($idx+1).'" '.(!$idx ? 'checked="checked" ' : '').'/><label for="rg-'.($idx+1).'" class="profiler-button profiler-option-left'.(!$idx ? ' selected' : '').'"><em><i>'.Lang::profiler('regions', $n).'</i></em></label>';
endforeach;
 ?>
                    </div>

                    <div>
                        <h2><?=Lang::profiler('realm').Lang::main('colon'); ?></h2>
                        <input type="text" name="sv" autocomplete="off" />
                        <div class="profiler-autocomplete"></div>
                    </div>

                    <div class="profiler-buttons">
                        <a href="javascript:;" class="profiler-button" id="profiler-lookup"><em><?=Lang::profiler('viewCharacter'); ?></em></a>
                        <a href="javascript:;" class="profiler-button" id="profiler-search"><em><?=Lang::main('search'); ?></em></a>
                    </div>
                </div>

                <div class="clear pad3"></div>

                <p><?=Lang::profiler('_cpFooter'); ?></p>
            </div>

            <script type="text/javascript">//<![CDATA[
                pr_initProfilerHome();
            //]]></script>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
