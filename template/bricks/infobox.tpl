    <table class="infobox">
        <tr><th id="infobox-quick-facts">{$lang.quickFacts}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-contents0"></div></td></tr>
        <tr><th id="infobox-screenshots">{$lang.screenshots}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-ss"></div></td></tr>
        <tr><th id="infobox-videos">{$lang.videos}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-vi"></div></td></tr>
    </table>
    <script type="text/javascript">ss_appendSticky()</script>
    <script type="text/javascript">vi_appendSticky()</script>
    <script type="text/javascript">
        Markup.printHtml("{$data.infobox}", "infobox-contents0", {ldelim}mode:Markup.MODE_QUICKFACTS{rdelim});
    </script>