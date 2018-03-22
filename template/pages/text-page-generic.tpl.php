<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate');

if (isset($this->notFound)):
?>
<?php
    if (!empty($this->doResync)):
?>
            <div id="roster-status" class="profiler-message clear"></div>
<?php
    endif;
?>

            <div class="pad3"></div>

            <div class="inputbox">
                <h1><?=$this->notFound['title']; ?></h1>
                <div id="inputbox-error"><?=$this->notFound['msg']; ?></div>        <!-- style="background: no-repeat 3px 3px" -->
<?php
    if (!empty($this->doResync)):
?>
                <script type="text/javascript">//<![CDATA[
                    pr_updateStatus('<?=$this->doResync[0]; ?>', $WH.ge('roster-status'), <?=$this->doResync[1]; ?>, 1);
                    pr_setRegionRealm($WH.gE($WH.ge('topbar'), 'form')[0], '<?=$this->region; ?>', '<?=$this->realm; ?>');
                //]]></script>
<?php
    endif;
?>
                <div class="clear"></div>
            </div>
<?php
else:
?>
            <div class="text">
                <h1><?=$this->name; ?></h1>

<?php
    $this->brick('article');

    if (isset($this->extraText)):
?>
                <div id="text-generic" class="left"></div>
                <script type="text/javascript">//<![CDATA[
                    Markup.printHtml("<?=Util::jsEscape($this->extraText); ?>", "text-generic", {
                        allow: Markup.CLASS_ADMIN,
                        dbpage: true
                    });
                //]]></script>

                <div class="pad2"></div>
            </div>
<?php
    endif;

    if (isset($this->extraHTML)):
        echo $this->extraHTML;
    endif;

endif;
?>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
