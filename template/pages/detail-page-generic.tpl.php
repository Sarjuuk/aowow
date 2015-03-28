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
<?php
    $this->brick('headIcons');

    $this->brick('redButtons');
?>

                <h1<?php echo isset($this->expansion) ? ' class="h1-icon"><span class="icon-'.$this->expansion.'-right">'.$this->name.'</span>' : '>'.$this->name; ?></h1>

<?php
    $this->brick('article');

    $this->brick('mapper');

if (isset($this->extraText)):
?>
    <div id="text-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("<?php echo $this->extraText; ?>", "text-generic", {
            allow: Markup.CLASS_ADMIN,
            dbpage: true
        });
    //]]></script>

    <div class="pad2"></div>
<?php
endif;

if (isset($this->unavailable)):
?>
                <div class="pad"></div>
                <b style="color: red"><?php echo Lang::main('_unavailable'); ?></b>
<?php
endif;

if (!empty($this->transfer)):
    echo "    <div class=\"pad\"></div>\n    ".$this->transfer."\n";
endif;

?>
                <h2 class="clear"><?php echo Lang::main('related'); ?></h2>
            </div>

<?php
    $this->brick('lvTabs', ['relTabs' => true]);

    $this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
