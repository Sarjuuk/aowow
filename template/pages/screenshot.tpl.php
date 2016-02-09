<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate');

$this->brick('infobox');

?>
            <div class="text">
                <h1><?=$this->name; ?></h1>

                <span><?=Lang::screenshot('cropHint'); ?></span>
                <div class="pad"></div>
                <div id="ss-container"></div><script type="text/javascript">//<![CDATA[
                    var myCropper = new Cropper(<?=Util::toJSON($this->cropper); ?>);
                //]]></script>

                <div class="pad"></div>
                <button style="margin:4px 0 0 5px" onclick="myCropper.selectAll()"><?=Lang::screenshot('selectAll'); ?></button>
                <div class="clear"></div>

                <div class="pad3"></div>

                <form action="?screenshot=complete&amp;<?=$this->destType.'.'.$this->destTypeId.'.'.$this->imgHash; ?>" method="post" onsubmit="this.elements['coords'].value = myCropper.getCoords()">
                    <?=Lang::screenshot('caption').Lang::main('colon'); ?><input type="text" name="screenshotalt" style="width: 55%" maxlength="200" /> <small> <?=Lang::screenshot('charLimit'); ?></small><br />
                    <div class="pad"></div>

<?php $this->localizedBrick('ssReminder', User::$localeId); ?>

                    <input type="submit" value="<?=Lang::main('submit'); ?>" />
                    <input type="hidden" name="coords" />
                </form>

                <script type="text/javascript">//<![CDATA[
                    var
                        captionMaxLen = 200;

                    Body        = $('#main-contents').find('input[type=text]');
                    TextCounter = $('#main-contents').find('small');

                    Body.focus();
                    Body.keyup(function (e) { return UpdateTextCounter(); });
                    Body.keypress(function (e) {            // ENTER
                        if (e.keyCode == 13 || captionMaxLen - getCaptionLen() <= 0 )
                            return false;
                    });

                    function getCaptionLen()
                    {
                        return Body.val().replace(/(\s+)/g, ' ').replace(/^\s*/, '').replace(/\s*$/, '').length;
                    }

                    function UpdateTextCounter()
                    {
                        var text      = '(error)';
                        var cssClass  = 'q0';
                        var chars     = getCaptionLen();
                        var charsLeft = captionMaxLen - chars;

                        text = $WH.sprintf(charsLeft == 1 ? LANG.replylength4_format : LANG.replylength3_format, charsLeft);

                        if (charsLeft < 25)
                            cssClass = 'q10';
                        else if (charsLeft < 50)
                            cssClass = 'q5';
                        else if (charsLeft < 75)
                            cssClass = 'q11';

                        TextCounter.html(text).attr('class', cssClass);
                    }

                //]]></script>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
