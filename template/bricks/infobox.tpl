    <table class="infobox">
        <tr><th id="infobox-quick-facts">{$lang.quickFacts}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-contents0"></div></td></tr>
        <tr><th id="infobox-screenshots">{$lang.screenshots}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-ss"></div></td></tr>
{if $user.id > 0}
        <tr><th id="infobox-videos">{$lang.videos}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-vi"></div></td></tr>
{/if}
    </table>
    <script type="text/javascript">ss_appendSticky()</script>
{if $user.id > 0}    <script type="text/javascript">vi_appendSticky()</script>{/if}
    <script type="text/javascript">
        Markup.printHtml("{$lvData.infobox}", "infobox-contents0", {ldelim}mode:Markup.MODE_QUICKFACTS{rdelim});
    </script>