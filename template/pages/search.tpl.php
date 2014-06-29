<?php $this->brick('header'); ?>

    <div id="main">
        <div id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php $this->brick('announcement'); ?>

            <div class="text">
                <a href="<?php echo Util::$wowheadLink; ?>" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
<?php
if ($this->lvData):
    echo '                <h1>'.Lang::$search['foundResult'].' <i>'.Util::htmlEscape($this->search).'</i>';
    if ($this->invalid):
        echo '<span class="sub">'.sprintf(Lang::$search['ignoredTerms'], implode(', ', $this->invalid)).'</span>';
    endif;
    echo "</h1>\n";
?>
            </div>
            <div id="tabs-generic"></div>
            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">
                var myTabs = new Tabs({parent: $WH.ge('tabs-generic')});
<?php
foreach ($this->lvData as $lv):
    $this->lvBrick($lv['file'], ['data' => $lv['data'], 'params' => $lv['params']]);
endforeach;
?>
                myTabs.flush();
            </script>
<?php
else:
    echo '            <h1>'.Lang::$search['noResult'].' <i>'.Util::htmlEscape($this->search).'</i>';
    if ($this->invalid):
        echo '<span class="sub">'.sprintf(Lang::$search['ignoredTerms'], implode(', ', $this->invalid)).'</span>';
    endif;
    echo "</h1>\n";
?>
            <div class="search-noresults"/></div>

<?php
    echo '            '.Lang::$search['tryAgain']."\n";
endif;
?>
            <div class="clear"></div>

        </div>

    </div>

<?php $this->brick('footer'); ?>
