<?php
    namespace Aowow\Template;

    $this->brick('header');
?>
    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">
<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

if ($this->inputbox):
    $this->brick(...$this->inputbox);                       // $templateName, [$templateVars]
elseif ($this->confirm):
    $this->localizedBrick('confirm-delete-account');
else:
    $this->localizedBrick('delete-account');
endif;
?>
            </div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
