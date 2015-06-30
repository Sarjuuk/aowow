<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

    $this->brick('mapper');

if (!empty($this->name) || !empty($this->h1Links) || !empty($this->extraHTML)):
    echo '<div class="text">' .
        (!empty($this->h1Links)   ? '<div class="h1-links">'.$this->h1Links.'</div>' : null) .
        (!empty($this->name)      ? '<h1>'.$this->name.'</h1>'                       : null) .
        (!empty($this->extraHTML) ? $this->extraHTML                                 : null) .
    '</div>';
endif;

$this->brick('lvTabs');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
