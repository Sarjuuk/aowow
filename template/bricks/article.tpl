{if !empty($article)}
    <div id="article-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("{$article.text}", "article-generic", {strip}{ldelim}
            {if !empty($article.params)}{foreach from=$article.params key=k item=v}
                {$k}: {if $v[0] == '$'}{$v|substr:1}{else}'{$v}'{/if},
            {/foreach}{/if}
            allow: Markup.CLASS_ADMIN,
            dbpage: true
        {rdelim}{/strip});
    //]]></script>

    <div class="pad2"></div>
{/if}