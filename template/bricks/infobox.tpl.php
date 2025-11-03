<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>

<?php
if ($this->infobox || $this->contributions || $this->series || $this->contribute & (CONTRIBUTE_SS | CONTRIBUTE_VI)):
echo "    <table class=\"infobox\">\n";

    if ($this->infobox):
?>
        <tr><th id="infobox-quick-facts"><?=Lang::main('quickFacts'); ?></th></tr>
        <tr><td>
            <div class="infobox-spacer"></div>
            <div id="infobox-contents0"></div>
            <script type="text/javascript">
                <?=$this->infobox; ?>
            </script>
        </td></tr>
<?php
    endif;

    if ($this->contributions):
?>
        <tr><th id="infobox-contributions"><?=Lang::user('contributions'); ?></th></tr>
        <tr><td>
            <div class="infobox-spacer"></div>
            <div id="infobox-contents1"></div>
            <script type="text/javascript">
                <?=$this->contributions; ?>
            </script>
        </td></tr>
<?php
    endif;

    if ($this->series):
        foreach ($this->series as [$list, $title]):
            $this->brick('series', ['list' => $list, 'listTitle' => $title]);
        endforeach;
    endif;

    if ($this->contribute & CONTRIBUTE_SS):
?>
        <tr><th id="infobox-screenshots"><?=Lang::main('screenshots'); ?></th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-ss"></div></td></tr>
<?php
    endif;

    if ($this->contribute & CONTRIBUTE_VI && ($this->user::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO) || !empty($this->community['vi']))):
?>
        <tr><th id="infobox-videos"><?=Lang::main('videos'); ?></th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-vi"></div></td></tr>
    <script type="text/javascript">$WH.prepInfobox()</script>
<?php
    endif;

    if ($this->contribute & CONTRIBUTE_SS):
?>
    <script type="text/javascript">ss_appendSticky()</script>
<?php
    endif;

    if ($this->contribute & CONTRIBUTE_VI && ($this->user::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO) || !empty($this->community['vi']))):
?>
    <script type="text/javascript">vi_appendSticky()</script>
<?php
    endif;

    echo "    </table>\n";
endif;
?>
