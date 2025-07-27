<?php
    namespace Aowow\Template;
?>

<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">
<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

if ([$typeStr, $id] = $this->doResync):
?>
            <div id="roster-status" class="profiler-message clear"></div>
            <script type="text/javascript">//<![CDATA[
                pr_updateStatus('<?=$typeStr; ?>', $WH.ge('roster-status'), <?=$id; ?>, 1);
                pr_setRegionRealm($WH.gE($WH.ge('topbar'), 'form')[0], '<?=$this->region; ?>', '<?=$this->realm; ?>');
            //]]></script>
<?php
endif;

if ($this->inputbox):
    $this->brick(...$this->inputbox);                       // $templateName, [$templateVars]
else:
?>
            <div class="text">
                <h1><?=$this->h1; ?></h1>

<?php
    $this->brick('markup', ['markup' => $this->article]);

    $this->brick('markup', ['markup' => $this->extraText]);

    if ($this->extraHTML):
        echo $this->extraHTML;
    endif;
?>
            </div>
<?php
endif;
?>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
