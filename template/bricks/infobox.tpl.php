    <table class="infobox">
<?php
if (!empty($this->infobox)):
?>
        <tr><th id="infobox-quick-facts"><?php echo Lang::main('quickFacts'); ?></th></tr>
        <tr><td>
            <div class="infobox-spacer"></div>
            <div id="infobox-contents0"></div>
            <script type="text/javascript">
                Markup.printHtml("<?php echo Util::jsEscape($this->infobox); ?>", "infobox-contents0", { allow: Markup.CLASS_STAFF, dbpage: true });
            </script>
        </td></tr>
<?php
endif;

if (!empty($this->contributions)):
?>
        <tr><th id="infobox-contributions"><?php echo Lang::user('contributions'); ?></th></tr>
        <tr><td>
            <div class="infobox-spacer"></div>
            <div id="infobox-contents1"></div>
            <script type="text/javascript">
                Markup.printHtml('<?php echo Util::jsEscape($this->contributions); ?>', 'infobox-contents1', { allow: Markup.CLASS_STAFF });
            </script>
        </td></tr>
<?php
endif;

if (!empty($this->series)):
    foreach ($this->series as $s):
        $this->brick('series', ['list' => $s[0], 'listTitle' => $s[1]]);
    endforeach;
endif;

if (!empty($this->type) && !empty($this->typeId)):
?>
        <tr><th id="infobox-screenshots"><?php echo Lang::main('screenshots'); ?></th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-ss"></div></td></tr>
<?php
    if (User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO) || !empty($this->community['vi'])):
?>
        <tr><th id="infobox-videos"><?php echo Lang::main('videos'); ?></th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-vi"></div></td></tr>
<?php
    endif;
?>
    </table>
    <script type="text/javascript">ss_appendSticky()</script>
<?php
    if (User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO) || !empty($this->community['vi'])):
?>
    <script type="text/javascript">vi_appendSticky()</script>
<?php
    endif;
else:
    echo "    </table>\n";
endif;

?>