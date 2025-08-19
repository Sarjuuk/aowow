<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    $this->brick('header');
?>
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
                        <h2><?=$this->ucFirst(Lang::main('name')).Lang::main('colon'); ?></h2>
                        <input type="text" name="na" value="" />
                    </div>

                    <div>
                        <h2><?=Lang::profiler('region').Lang::main('colon'); ?></h2>
<?=$this->makeRadiosList('rg', $this->regions, $this->rg, 24, function (&$v, $k, &$attribs) {
    $attribs = ['class' => 'profiler-button profiler-option-left'];
    $v = '<em><i>'.$v.'</i></em>';
    if ($k == $this->rg)
        $attribs['class'] .= ' selected';
    return true;
}); ?>
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
