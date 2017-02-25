<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

    $this->brick('infobox');
?>

            <script type="text/javascript">var g_pageInfo = { username: '<?php echo Util::jsEscape($this->user['displayName']); ?>' }</script>

            <div class="text">
                <div id="h1-icon-generic" class="h1-icon"></div>
                <script type="text/javascript">
                    $WH.ge('h1-icon-generic').appendChild(Icon.createUser(<?php echo (is_numeric($this->user['avatar']) ? 2 : 1).', \''.($this->user['avatar'] ?: 'inv_misc_questionmark').'\''?>, 1, null, <?php echo User::isInGroup(U_GROUP_PREMIUM) ? 0 : 2; ?>, false, Icon.getPrivilegeBorder(<?php echo $this->user['sumRep']; ?>)));
                </script>
                <h1 class="h1-icon"><?php echo $this->name; ?></h1>
            </div>

            <h3 class="first"><?php echo Lang::user('publicDesc'); ?></h3>
            <div id="description" class="left"><?php #  must follow directly, no whitespaces allowed
if (!empty($this->user['description'])):
?>
                <div id="description-generic"></div>
                <script type="text/javascript">//<![CDATA[
                    Markup.printHtml('<?php echo $this->user['description']; ?>', "description-generic", { allow: Markup.CLASS_USER, roles: "<?php echo $this->user['userGroups']; ?>" });
                //]]></script>
<?php
endif;
          ?></div>
            <script type="text/javascript">us_addDescription()</script>

            <div id="roster-status" class="profiler-message clear" style="display: none"></div>

<?php $this->brick('lvTabs', ['relTabs' => true]); ?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
