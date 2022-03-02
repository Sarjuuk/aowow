<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

    if (isset($this->notFound)):
?>
            <div class="pad3"></div>

            <div class="inputbox">
                <h1><?=$this->notFound['title'];?></h1>
                <div id="inputbox-error"><?=$this->notFound['msg'];?></div>
<?php
    else:
?>
            <div class="text">
<?php
        $this->brick('redButtons');

        if (!empty($this->h1Links)):
            echo '                <div class="h1-links">'.$this->h1Links.'</div>';
        endif;

        if (!empty($this->name)):
            echo '                <h1>'.$this->name.'</h1>';
        endif;

        $this->brick('mapper');

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

    endif;

    if (!empty($this->tabsTitle)):
        echo '                <h2 class="clear">'.$this->tabsTitle.'</h2>';
    endif;
?>
            </div>
<?php
    if (!empty($this->lvTabs)):
        $this->brick('lvTabs');
?>
        <div class="clear"></div>
<?php
    endif;
?>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
