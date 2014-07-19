<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('mapper');
?>

            <script type="text/javascript">//<![CDATA[
                g_initPath(<?php echo json_encode($this->path, JSON_NUMERIC_CHECK); ?>);
            //]]></script>

<?php
if (!empty($this->name) || !empty($this->h1Links)):
    echo '<div class="text">' .
        (!empty($this->h1Links) ? '<div class="h1-links">'.$this->h1Links.'</div>' : null) .
        (!empty($this->name)    ? '<h1>'.$this->name.'</h1>'                       : null) .
    '</div>';
endif;

$this->brick('lvTabs');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
