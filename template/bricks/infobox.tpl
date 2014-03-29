    <table class="infobox">
{if !empty($infobox)}
        <tr><th id="infobox-quick-facts">{$lang.quickFacts}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-contents0"></div></td></tr>
{/if}
{if !empty($series)}
    {foreach from=$series item=s}
        {include file="bricks/series.tpl" list=$s[0] listTitle=$s[1]}
    {/foreach}
{/if}
        <tr><th id="infobox-screenshots">{$lang.screenshots}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-ss"></div></td></tr>
{if $user.id > 0}
        <tr><th id="infobox-videos">{$lang.videos}</th></tr>
        <tr><td><div class="infobox-spacer"></div><div id="infobox-sticky-vi"></div></td></tr>
{/if}
    </table>
    <script type="text/javascript">ss_appendSticky()</script>
{if $user.id > 0}
    <script type="text/javascript">vi_appendSticky()</script>
{/if}
{if !empty($infobox)}
    <script type="text/javascript">
        Markup.printHtml("{$infobox}", "infobox-contents0", {ldelim} allow: Markup.CLASS_STAFF, dbpage: true {rdelim});
    </script>
{/if}