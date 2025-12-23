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

    $this->brick('infobox');
?>

            <div class="text">
<?php $this->brick('redButtons'); ?>

                <h1><?=$this->h1; ?></h1>
<?php
if ($this->unavailable):
?>
	<div class="pad"></div>
    <b style="color: red"><?=Lang::item('_unavailable'); ?></b>
	<div class="pad"></div>
<?php
endif;

    $this->brick('tooltip');

    $this->brick('markup', ['markup' => $this->article]);

if ($this->map):
    echo "            <h3>".$this->map[4]."</h3>\n";
    $this->brick('mapper');
endif;

if ($this->transfer):
    echo "    <div class=\"pad\"></div>\n    ".$this->transfer."\n";
endif;

if ($this->subItems):
?>
                <div class="clear"></div>
                <h3><?=Lang::item('_rndEnchants'); ?></h3>
<?php
    foreach (array_chunk($this->subItems['data'], ceil(count($this->subItems['data']) / 2)) as $columns):
?>
                <div class="random-enchantments" style="margin-right: 25px">
                    <ul>
<?php
        foreach ($columns as $k => ['name' => $name, 'enchantment' => $enchantment, 'chance' => $chance]):
            echo '                        <li><div><span title="ID'.Lang::main('colon').$this->subItems['randIds'][$k].'" class="tip q'.$this->subItems['quality'].'">...'.$name.'</span> <small class="q0">'.Lang::item('_chance', [$chance]).'</small><br />';
            echo Lang::concat($enchantment, Lang::CONCAT_NONE, fn($txt, $eId) => '<a style="text-decoration:none; color:#CCCCCC;" href="?enchantment='.$eId.'">'.$txt.'</a>')."</div></li>\n";
        endforeach;
?>
                    </ul>
                </div>
<?php
    endforeach;
endif;

$this->brick('book');
?>

                <h2 class="clear"><?=Lang::main('related'); ?></h2>
            </div>

<?php
    $this->brick('lvTabs');

    $this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
