<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    /** @var PageTemplate $this */

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
if (count($this->lvTabs)):
    echo '                <h1>'.Lang::main('foundResult').' <i>'.$this->search.'</i>';
    if ($this->invalidTerms):
        echo '<span class="sub">'.Lang::main('ignoredTerms', [$this->invalidTerms]).'</span>';
    endif;
    echo '</h1>'.PHP_EOL;
?>

            </div>

<?php
$this->brick('lvTabs');

else:
    echo '            <h1>'.Lang::main('noResult').' <i>'.$this->search.'</i>';
    if ($this->invalidTerms):
        echo '<span class="sub">'.Lang::main('ignoredTerms', [$this->invalidTerms]).'</span>';
    endif;
    echo '</h1>'.PHP_EOL;
?>

            <div class="search-noresults"></div>

<?php
    echo '            '.Lang::main('tryAgain').PHP_EOL;
endif;
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
