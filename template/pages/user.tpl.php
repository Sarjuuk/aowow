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
            <script type="text/javascript">var g_pageInfo = { username: '<?=$this->escJS($this->username); ?>' }</script>
<?php
    $this->brick('infobox');
?>
            <div class="text">
<?php
    if ($this->userIcon):
?>
                <div id="h1-icon-generic" class="h1-icon"></div>
                <script type="text/javascript">
                    $WH.ge('h1-icon-generic').appendChild(Icon.createUser(<?=substr($this->json('userIcon', varRef: true), 1, -1); ?>));
                </script>
                <h1 class="h1-icon"><?=$this->h1; ?></h1>
<?php else: ?>
                <h1><?=$this->h1; ?></h1>
<?php endif; ?>
                <h3 class="first"><?=Lang::user('publicDesc'); ?></h3>
                <div id="description" class="left"><?php #  must follow directly, no whitespaces allowed
if ($this->description):
?>
                    <div id="description-generic"></div>
                    <script type="text/javascript">//<![CDATA[
                        <?=$this->description; ?>
                    //]]></script>
<?php
endif;
              ?></div>
                <script type="text/javascript">us_addDescription()</script>
<?php if (count($this->lvTabs)): ?>
                <h2 id="related" class="clear"><?=Lang::main('related'); ?></h2>
<?php endif; ?>
            </div>

            <div id="roster-status" class="profiler-message clear" style="display: none"></div>

<?php $this->brick('lvTabs'); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
