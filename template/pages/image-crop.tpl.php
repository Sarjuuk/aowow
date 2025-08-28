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
                <h1><?=$this->h1; ?></h1>

                <span><?=Lang::account('cropAvatar');?></span>
                <div class="pad"></div>
                <div id="av-container"></div><script type="text/javascript">//<![CDATA[
                    var myCropper = new Cropper(<?=$this->json($this->cropper); ?>);
                //]]></script>

                <div class="pad"></div>
                <button style="margin:4px 0 0 5px" onclick="myCropper.selectAll()"><?=Lang::screenshot('selectAll'); ?></button>
                <div class="clear"></div>

                <div class="pad3"></div>

                <form action="?upload=image-complete&amp;<?=$this->nextId.'.'.$this->imgHash; ?>" method="post" onsubmit="this.elements['coords'].value = myCropper.getCoords()">
                    <div class="pad"></div>

                    <h2><img src="<?=$this->gStaticUrl; ?>/images/icons/bubble-big.gif" width="32" height="29" alt="" style="vertical-align:middle;margin-right:8px"><?=Lang::account('reminder');?></h2>
                    <div class="pad3"><?=Lang::account('avatarCoC');?></div>

                    <input type="submit" value="<?=Lang::main('submit'); ?>" />
                    <input type="hidden" name="coords" />
                </form>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
