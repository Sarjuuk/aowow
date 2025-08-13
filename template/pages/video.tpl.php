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

$this->brick('infobox');

?>
            <div class="text">
                <h1><?=$this->h1; ?></h1>

                <h3><?=$this->viTitle;?></h3>
                <div class="pad"></div>
                <div id="vi-container"></div><script type="text/javascript">//<![CDATA[
                    let video = <?=$this->json($this->video); ?>;
                //]]></script>
                <a href="#" onClick="VideoViewer.show({ videos: video });"><img class="border" src="<?=$this->url; ?>" width="<?=$this->width; ?>" heigth="<?=$this->height; ?>"></img></a>

                <div class="pad3"></div>

                <form action="?video=complete&amp;<?=$this->destType.'.'.$this->destTypeId.'.'.$this->videoHash; ?>" method="post">
                    <?=Lang::screenshot('caption').Lang::main('colon'); ?><input type="text" name="caption" style="width: 55%" maxlength="200" /> <small> <?=Lang::screenshot('charLimit'); ?></small><br />
                    <div class="pad3"></div>

                    <input type="submit" value="<?=Lang::main('submit'); ?>" />
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
