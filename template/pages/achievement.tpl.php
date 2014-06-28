<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php $this->brick('announcement'); ?>

            <script type="text/javascript">//<![CDATA[
<?php
    $this->brick('community');
            echo "                var g_pageInfo = ".json_encode($this->gPageInfo, JSON_NUMERIC_CHECK).";\n" .
                 "                g_initPath(".json_encode($this->path, JSON_NUMERIC_CHECK).");\n";
?>
            //]]></script>

<?php $this->brick('infobox', ['series' => $this->series]); ?>

            <div class="text">
<?php
$this->brick('headIcons');

$this->brick('redButtons');
?>
               <h1><?php echo $this->name; ?></h1>

<?php
    echo $this->description;

    echo '<h3>'.Lang::$achievement['criteria'].($this->criteria['reqQty'] ? ' &ndash; <small><b>'.Lang::$achievement['reqNumCrt'].' '.$this->criteria['reqQty'].' '.Lang::$achievement['outOf'].' '.count($this->criteria['data']).'</b></small>' : null)."</h3>\n";
?>


                <div style="float: left; margin-right: 25px">
                <table class="iconlist">
<?php
foreach ($this->criteria['data'] as $i => $cr):
    echo '<tr><th'.(isset($cr['icon']) ? ' align="right" id="iconlist-icon'.$cr['icon'].'"' : null).'>';

    if (!isset($cr['icon'])):
        echo '<ul><li><var>&nbsp;</var></li></ul>';
    endif;

    echo '</th><td>';

    if ($l = @$cr['link']):
        echo '<a href="'.$l['href'].'"'.(isset($l['quality']) ? ' class="q'.$l['quality'].'"' : null).'>'.Util::htmlEscape($l['text']).'</a>';
    endif;

    if (!empty($l['count']) && $l['count'] > 1):
        echo ' ('.$l['count'].')';
    endif;

    if (isset($cr['extraText'])):
        echo ' '.$cr['extraText'];
    endif;

    if (User::isInGroup(U_GROUP_STAFF)):
        echo ' <small title="'.Lang::$achievement['criteriaType'].' '.$cr['type'].'" class="q0">['.$cr['id'].']</small>';
    endif;

    echo '</td></tr>';

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
    echo '                    $WH.ge(\'iconlist-icon'.$ic['itr'].'\').appendChild('.$ic['type'].'.createIcon('.$ic['id'].', 0, '.(@$ic['count'] ?: 0)."));\n";
endforeach;
?>
                //]]></script>

                <div style="clear: left"></div>

<?php
if ($r = $this->rewards):
    if (!empty($r['item'])):
        echo '<h3>'.Lang::$main['rewards']."</h3>\n";
        $this->brick('rewards', ['rewards' => $this->rewards['item']]);
    endif;

    if (!empty($r['title'])):
        echo '<h3>'.Lang::$main['gains']."</h3>\n<ul>";
        foreach ($r['title'] as $i):
            echo '    <li><div>'.$i."</div></li>\n";
        endforeach;
        echo "</ul>\n";
    endif;

    if (empty($r['title']) && empty($r['item']) && $r['text']):
        echo '<h3>'.Lang::$main['rewards']."</h3>\n" .
             '<ul><li><div><'.$r['text']."</div></li></ul>\n";
    endif;
endif;
?>

                <h2 class="clear"><?php echo Lang::$main['related']; ?></h2>
            </div>

<?php
$this->brick('tabsRelated');

$this->brick('contribute');
?>

        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
