<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
$this->brick('announcement');

$this->brick('pageTemplate');

$this->brick('infobox');

if (isset($this->error)):
?>
            <div class="pad3"></div>

            <div class="inputbox">
                <h1><?php echo Lang::$main['prepError']; ?></h1>
                <div id="inputbox-error"><?php echo $this->error; ?><br><a href="<?php echo $this->destType ? '?'.Util::$typeStrings[$this->destType].'='.$this->destTypeId : 'javascript:history.back();'; ?>"><?php echo Lang::$main['back']; ?></a></div>
<?php
else:
?>
            <div class="text">
                <h1><?php echo $this->name; ?></h1>

<?php
    if ($this->mode == 'add'):
?>
                <div id="ss-container" style="float:right;"></div>
                <div id="img-edit-form">
                    <span><?php echo Lang::$main['cropHint']; ?></span>
                    <div class="pad"></div>
                    <b><?php echo Lang::$main['caption'].Lang::$main['colon']; ?></b><br>
                    <form action="?screenshot=finalize" method="post">
                        <textarea style="resize: none;" name="screenshotcaption" cols="27" rows="4"><?php echo $this->caption; ?></textarea>
                        <div class="text-counter">text counter ph</div>
                        <div style="clear:right;"></div>
                        <input type="hidden" id="selection" name="selection" value=""></input>
                        <input type="submit" value="<?php echo Lang::$main['ssSubmit']; ?>"></input>
                    </form>
                </div>
                <script type="text/javascript">//<![CDATA[
                    var
                        cropper       = new Cropper(<?php echo json_encode($this->cropper, JSON_NUMERIC_CHECK); ?>),
                        captionMaxLen = 200;

                    Body        = $('#img-edit-form').find('textarea');
                    TextCounter = $('#img-edit-form').find('div.text-counter');
                    Form        = $('#img-edit-form').find('form');
                    qfSizeDiv   = $('.infobox').find('#qf-newSize');

                    $('body').mouseup(UpdateImageSelection);

                    Form.submit(function () { return submitSS(); });

                    UpdateTextCounter();
                    UpdateImageSelection();

                    Body.keyup(function (e) { return UpdateTextCounter(); });
                    Body.keypress(function (e) {            // ENTER
                        if (e.keyCode == 13 || captionMaxLen - getCaptionLen() <= 0 )
                            return false;
                    });

                    Form.find('textarea').focus();

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

                    function UpdateImageSelection()
                    {
                        dims = cropper.getCoords().split(',');

                        w = Math.floor(cropper.oWidth  * dims[2]);
                        h = Math.floor(cropper.oHeight * dims[3]);

                        qfSizeDiv.html(w + ' x ' + h);
                    }

                    function submitSS()
                    {
                        $('#selection').val(cropper.getCoords());

                        return true;
                    }
                //]]></script>

                <div class="pad2"></div>
<?php
    endif;
endif;
?>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
