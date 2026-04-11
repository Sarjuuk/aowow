<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    /** @var \PageTemplate $this */
?>

<?php
if ($this->contribute):
?>

<div class="clear"></div>
<div class="text">
<h2><?=Lang::main('contribute'); ?></h2>
</div>
<div id="tabs-contribute-generic" style="width: 50%"></div>
<div class="text" style="margin-right: 310px">
    <div class="tabbed-contents" style="clear: none">

<?php
    $this->localizedBrick('contrib', ['coError' => $this->community['coError'], 'ssError' => $this->community['ssError'], 'viError' => $this->community['viError']]);
?>

    </div>
</div>
<script type="text/javascript">
    var tabsContribute = new Tabs({parent: $WH.ge('tabs-contribute-generic')});

<?php
    if ($this->contribute & CONTRIBUTE_CO):
        echo "    tabsContribute.add(LANG.tab_addyourcomment, {id: 'add-your-comment'});".PHP_EOL;
    endif;
    if ($this->contribute & CONTRIBUTE_SS):
        echo "    tabsContribute.add(LANG.tab_submitascreenshot, {id: 'submit-a-screenshot'});".PHP_EOL;
    endif;
    if ($this->contribute & CONTRIBUTE_VI):
        echo "    if (g_user && g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO))".PHP_EOL;
        echo "        tabsContribute.add(LANG.tab_suggestavideo, {id: 'suggest-a-video'});".PHP_EOL;
    endif;
?>

    tabsContribute.flush();
</script>

<?php endif; ?>
