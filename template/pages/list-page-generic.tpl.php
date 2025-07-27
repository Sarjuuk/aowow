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
<?php
    $this->brick('redButtons');

    if ($this->h1Link):
        echo '                <div class="h1-links"><small><a href="'.$this->h1Link.'" class="icon-rss">'.Lang::main('subscribe').'</a></small></div>';
    endif;

    if ($this->h1):
        echo '                <h1>'.$this->h1.'</h1>';
    endif;

    $this->brick('mapper');

    $this->brick('markup', ['markup' => $this->article]);

    $this->brick('markup', ['markup' => $this->extraText]);

    echo $this->extraHTML ?? '';

    if ($this->tabsTitle):
        echo '                <h2 class="clear">'.$this->tabsTitle.'</h2>';
    endif;
?>
            </div>
<?php
    if ($this->lvTabs):
        $this->brick('lvTabs');
?>
        <div class="clear"></div>
<?php
    endif;
?>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
