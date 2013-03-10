{include file='header.tpl'}

        <div id="main">
            <div id="main-precontents"></div>
            <div id="main-contents" class="main-contents">
{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

                <script type="text/javascript">g_initPath([1,3])</script>
                <div class="text">
                    <div id="compare-generic"></div>
                    <script type="text/javascript">//<![CDATA[
{foreach name=cmpItems from=$data.items item=curr}
                        g_items.add({$curr[0]}, {ldelim}name_{$user.language}:'{$curr[1]}', quality:{$curr[2]}, icon:'{$curr[3]}', jsonequip:{$curr[4]}{rdelim});
{/foreach}
                        new Summary({ldelim}template:'compare',id:'compare',parent:'compare-generic',groups:{$data.summary}{rdelim});
                    //]]></script>
                </div>
                <div class="clear"></div>
            </div>
        </div>

{include file='footer.tpl'}
