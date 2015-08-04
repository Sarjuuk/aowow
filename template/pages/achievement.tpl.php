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
               <h1><?php echo $this->name; ?></h1>

<?php
    echo $this->description;

    echo '<h3>'.Lang::achievement('criteria').($this->criteria['reqQty'] ? ' &ndash; <small><b>'.Lang::achievement('reqNumCrt').' '.$this->criteria['reqQty'].' '.Lang::achievement('outOf').' '.count($this->criteria['data']).'</b></small>' : null)."</h3>\n";
?>


                <div style="float: left; margin-right: 25px">
                <table class="iconlist">
<?php
foreach ($this->criteria['data'] as $i => $cr):
    echo '<tr><th'.(isset($cr['icon']) ? ' align="right" id="iconlist-icon'.$cr['icon'].'"' : null).'>';

    if (!isset($cr['icon'])):
        echo '<ul><li><var>&nbsp;</var></li></ul>';
    endif;

    echo '</th><td><span class="tip" title="ID'.Lang::main('colon').$cr['id'].'">';

    if (!empty($cr['link'])):
        echo '<a href="'.$cr['link']['href'].'"'.(isset($cr['link']['quality']) ? ' class="q'.$cr['link']['quality'].'"' : null).'>'.Util::htmlEscape($cr['link']['text']).'</a>';
    endif;

    if (!empty($cr['link']['count']) && $cr['link']['count'] > 1):
        echo '&nbsp;('.$cr['link']['count'].')';
    endif;

    if (isset($cr['extraText'])):
        echo ' '.$cr['extraText'];
    endif;

    echo '</span></td></tr>';

    // every odd number of elements
    if ($i + 1 == round(count($this->criteria['data']) / 2)):
        echo '</table></div><div style="float: left"><table class="iconlist">';
    endif;
endforeach;
?>
                </table>
                </div>

                <script type="text/javascript">//<![CDATA[
<?php
foreach ($this->criteria['icons'] as $k => $ic):
    echo '                    $WH.ge(\'iconlist-icon'.$ic['itr'].'\').appendChild('.$ic['type'].'.createIcon('.$ic['id'].', 0, '.(!empty($ic['count']) ? $ic['count'] : 0)."));\n";
endforeach;
?>
                //]]></script>

                <div style="clear: left"></div>

<?php
if ($r = $this->rewards):
    if (!empty($r['item'])):
        echo '<h3>'.Lang::main('rewards')."</h3>\n";
        $this->brick('rewards', ['rewards' => $r['item'], 'rewTitle' => null]);
    endif;

    if (!empty($r['title'])):
        echo '<h3>'.Lang::main('gains')."</h3>\n<ul>";
        foreach ($r['title'] as $i):
            echo '    <li><div>'.$i."</div></li>\n";
        endforeach;
        echo "</ul>\n";
    endif;

    if (empty($r['title']) && empty($r['item']) && $r['text']):
        echo '<h3>'.Lang::main('rewards')."</h3>\n" .
             '<ul><li><div>'.$r['text']."</div></li></ul>\n";
    endif;
endif;

$this->brick('mail');

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
