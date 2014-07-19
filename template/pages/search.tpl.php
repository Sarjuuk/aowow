<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php $this->brick('announcement'); ?>

            <div class="text">
                <a href="<?php echo Util::$wowheadLink; ?>" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
<?php
if ($this->lvTabs):
    echo '                <h1>'.Lang::$search['foundResult'].' <i>'.Util::htmlEscape($this->search).'</i>';
    if ($this->invalid):
        echo '<span class="sub">'.sprintf(Lang::$search['ignoredTerms'], implode(', ', $this->invalid)).'</span>';
    endif;
    echo "</h1>\n";
?>
            </div>
<?php
$this->brick('lvTabs');

else:
    echo '            <h1>'.Lang::$search['noResult'].' <i>'.Util::htmlEscape($this->search).'</i>';
    if ($this->invalid):
        echo '<span class="sub">'.sprintf(Lang::$search['ignoredTerms'], implode(', ', $this->invalid)).'</span>';
    endif;
    echo "</h1>\n";
?>
            <div class="search-noresults"></div>

<?php
    echo '            '.Lang::$search['tryAgain']."\n";
endif;
?>
            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
