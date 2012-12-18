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
            {$curr}
            {/foreach}
                {$data.summary}
                //]]></script>
            </div>
            <div class="clear"></div>
        </div>
    </div>

{include file='footer.tpl'}
