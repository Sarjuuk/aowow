{include file='header.tpl'}

    <div id="main">
        <div id="main-precontents"></div>
        <div id="main-contents" class="main-contents">
            {if !empty($announcements)}
                {foreach from=$announcements item=item}
                    {include file='bricks/announcement.tpl' an=$item}
                {/foreach}
            {/if}
            <div class="text">
                <h1>{$lang.errNotFound}</h1>
                <div class="left">
                    {$lang.errPage}
                    <h2 id="links">{$lang.links}</h2>
                    <ul class="last">
                        <li>
                            <div>
                            {$lang.goStart}
                            </div>
                        </li>
                        <li>
                            <div>
                            {$lang.goForum}
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>

{include file='footer.tpl'}
