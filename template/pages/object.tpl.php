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
    $this->brick('markup', ['markup' => $this->article]);

if ($this->relBoss):
    echo "                <div>".sprintf(Lang::gameObject('npcLootPH'), $this->h1, $this->relBoss[0], $this->relBoss[1])."</div>\n";
    echo '                <div class="pad"></div>';
endif;

if ($this->map):
    $this->brick('mapper');
else:
    echo Lang::gameObject('unkPosition');
endif;

$this->brick('book');

$this->brick('markup', ['markup' => $this->smartAI]);

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
