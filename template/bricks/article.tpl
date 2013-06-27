{if !empty($article)}
    <div id="article-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("{$article}", "article-generic", {ldelim}mode:Markup.MODE_ARTICLE{rdelim});
    //]]></script>

    <div class="pad2"></div>
{/if}