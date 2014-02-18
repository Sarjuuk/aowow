{if !empty($pageText)}
                <div class="clear"></div>
                <h3>{$lang.content}</h3>

                <div id="book-generic"></div>
                <script>//<![CDATA[
                    {strip}new Book({ldelim} parent: 'book-generic', pages: [
    {foreach from=$pageText item=page name=j}
                        '{$page|escape:"javascript"}'
                        {if $smarty.foreach.j.last}{else},{/if}
    {/foreach}
                    ]{rdelim}){/strip}
                //]]></script>

{/if}
