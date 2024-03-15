<?php
if (!empty($this->infobox) || !empty($this->contributions) || !empty($this->series) || $this->contribute & (CONTRIBUTE_SS | CONTRIBUTE_VI)):
echo "    <table class=\"infobox\">\n";

    if (!empty($this->infobox)):
?>
        <tr><th id="infobox-quick-facts"><?=Lang::main('quickFacts'); ?></th></tr>
        <tr><td>
            <div class="infobox-spacer"></div>
            <div id="infobox-contents0"></div>
            <script type="text/javascript">
                Markup.printHtml("<?=Util::jsEscape($this->infobox); ?>", "infobox-contents0", { allow: Markup.CLASS_STAFF, dbpage: true });
            </script>
        </td></tr>
<?php
    endif;

    if (!empty($this->contributions)):
?>
        <tr><th id="infobox-contributions"><?=Lang::user('contributions'); ?></th></tr>
        <tr><td>
            <div class="infobox-spacer"></div>
            <div id="infobox-contents1"></div>
            <script type="text/javascript">
                Markup.printHtml('<?=Util::jsEscape($this->contributions); ?>', 'infobox-contents1', { allow: Markup.CLASS_STAFF });
            </script>
        </td></tr>
<?php
    endif;

    if (!empty($this->series)):
        foreach ($this->series as $s):
            $this->brick('series', ['list' => $s[0], 'listTitle' => $s[1]]);
        endforeach;
    endif;

    if ($this->contribute & CONTRIBUTE_SS):
?>
        <tr><th id="infobox-screenshots"><?=Lang::main('screenshots'); ?></th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-ss"></div></td></tr>
<?php
    endif;

    if ($this->contribute & CONTRIBUTE_VI && (User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO) || !empty($this->community['vi']))):
?>
        <tr><th id="infobox-videos"><?=Lang::main('videos'); ?></th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-vi"></div></td></tr>
<?php
    endif;

    if ($this->contribute & CONTRIBUTE_SS):
?>
    <script type="text/javascript">ss_appendSticky()</script>
<?php
    endif;

    if ($this->contribute & CONTRIBUTE_VI && (User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO) || !empty($this->community['vi']))):
?>
    <script type="text/javascript">vi_appendSticky()</script>
<?php
    endif;

    echo "    </table>\n";
endif;
?>
