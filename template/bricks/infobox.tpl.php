    <table class="infobox">
<?php
if (!empty($this->infobox)):
?>
        <tr><th id="infobox-quick-facts"><?php echo Lang::$main['quickFacts']; ?></th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-contents0"></div></td></tr>
<?php
endif;

if (!empty($this->series)):
    foreach ($this->series as $s):
        $this->brick('series', ['list' => $s[0], 'listTitle' => $s[1]]);
    endforeach;
endif;

if (!empty($this->type) && !empty($this->typeId)):
?>
        <tr><th id="infobox-screenshots"><?php echo Lang::$main['screenshots']; ?></th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-ss"></div></td></tr>
        <tr><th id="infobox-videos"><?php echo Lang::$main['videos']; ?></th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-vi"></div></td></tr>
    </table>
    <script type="text/javascript">ss_appendSticky()</script>
    <script type="text/javascript">vi_appendSticky()</script>
<?php
else:
    echo "    </table>\n";
endif;

if (!empty($this->infobox)):
?>
    <script type="text/javascript">
        Markup.printHtml("<?php echo Util::jsEscape($this->infobox); ?>", "infobox-contents0", { allow: Markup.CLASS_STAFF, dbpage: true });
    </script>
<?php
endif;
?>
