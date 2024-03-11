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
               <h1><?=$this->name; ?></h1>

<?php
    echo $this->description;

    echo '<h3>'.Lang::achievement('criteria').($this->criteria['reqQty'] ? ' &ndash; <small><b>'.Lang::achievement('reqNumCrt').' '.$this->criteria['reqQty'].' '.Lang::achievement('outOf').' '.count($this->criteria['data']).'</b></small>' : null)."</h3>\n";
?>


<?php
    $tbl1  = '<div style="float: left;"><table class="iconlist">%s</table></div>';
    $tbl2  = '<div style="float: left; margin-right: 25px"><table class="iconlist">%s</table></div>';
    $rows1 = '';
    $rows2 = '';

foreach ($this->criteria['data'] as $i => $cr):
    if (isset($cr['icon'])):
        $row = '<tr><th align="right" id="iconlist-icon'.$cr['icon'].'"></th>';
    else:
        $row = '<tr><th><ul><li><var>&nbsp;</var></li></ul></th>';
    endif;

    $row .= '<td><span class="tip" title="ID'.Lang::main('colon').$cr['id'].'">';

    if (!empty($cr['link'])):
        $row .= '<a href="'.$cr['link']['href'].'"'.(isset($cr['link']['quality']) ? ' class="q'.$cr['link']['quality'].'"' : null).'>'.Util::htmlEscape($cr['link']['text']).'</a>';
    endif;

    if (!empty($cr['link']['count']) && $cr['link']['count'] > 1):
        $row .= '&nbsp;('.$cr['link']['count'].')';
    endif;

    if (isset($cr['extraText'])):
        $row .= ' '.$cr['extraText'];
    endif;

    $row .= '</span>';

    if (!empty($cr['extraData'])):
        $buff = [];
        foreach ($cr['extraData'] as $xd):
            $buff[] = $xd[0] ? '<a href="'.$xd[0].'">'.$xd[1].'</a>' : '<span>'.$xd[1].'</span>';
        endforeach;

        $row .= '<br /><sup style="margin-left:8px;">('.implode(', ', $buff).')</sup>';
    endif;

    $row .= '</td></tr>';

    // every odd number of elements
    if ($i + 1 > round(count($this->criteria['data']) / 2)):
        $rows2 .= $row;
    else:
        $rows1 .= $row;
    endif;
endforeach;

if ($rows2):
    echo sprintf($tbl2, $rows2);
endif;
if ($rows1):
    echo sprintf($tbl1, $rows1);
endif;
?>

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
    echo "    <div style=\"clear: left\"></div>";
    echo "    <div class=\"pad\"></div>\n    ".$this->transfer."\n";
endif;

?>

                <h2 class="clear"><?=Lang::main('related'); ?></h2>
            </div>

<?php
$this->brick('lvTabs', ['relTabs' => true]);

$this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
