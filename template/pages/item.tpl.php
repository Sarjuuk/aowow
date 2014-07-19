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
    if (User::$id > 0 && $this->redButtons[BUTTON_EQUIP]):
        echo "                DomContentLoaded.addEvent(function() { pr_addEquipButton('equip-pinned-button', ".$this->typeId."); });\n";
    endif;
?>
            //]]></script>

<?php $this->brick('infobox'); ?>

            <div class="text">
<?php $this->brick('redButtons'); ?>

                <h1><?php echo $this->name; ?></h1>

<?php
    $this->brick('tooltip');

    $this->brick('article');

if ($this->disabled):
?>
	<div class="pad"></div>
    <b style="color: red"><?php echo Lang::$item['_unavailable']; ?></b>
<?php
endif;

if (!empty($this->transfer)):
    echo "    <div class=\"pad\"></div>\n    ".$this->transfer."\n";
endif;

if (!empty($this->subItems)):
?>
                <div class="clear"></div>
                <h3><?php echo Lang::$item['_rndEnchants']; ?></h3>

                <div class="random-enchantments" style="margin-right: 25px">
                    <ul>
<?php
        foreach ($this->subItems['data'] as $k => $i):
            if ($k < (count($this->subItems['data']) / 2)):
                echo '                        <li><div><span class="q'.$this->subItems['quality'].'">...'.$i['name'].'</span>';
                echo '                        <small class="q0">'.sprintf(Lang::$item['_chance'], $i['chance']).'</small><br />'.$i['enchantment'].'</div></li>';
            endif;
        endforeach;
?>
                    </ul>
                </div>
<?php
    if (count($this->subItems) > 1):
?>
                <div class="random-enchantments" style="margin-right: 25px">
                    <ul>
<?php
        foreach ($this->subItems['data'] as $k => $i):
            if ($k >= (count($this->subItems['data']) / 2)):
                echo '                        <li><div><span class="q'.$this->subItems['quality'].'">...'.$i['name'].'</span>';
                echo '                        <small class="q0">'.sprintf(Lang::$item['_chance'], $i['chance']).'</small><br />'.$i['enchantment'].'</div></li>';
            endif;
        endforeach;
?>
                    </ul>
                </div>
<?php
    endif;
endif;

$this->brick('book');
?>

                <h2 class="clear"><?php echo Lang::$main['related']; ?></h2>
            </div>

<?php
    $this->brick('lvTabs', ['relTabs' => true]);

    $this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
