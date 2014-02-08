{include file='header.tpl'}

    <div id="main">
        <div id="main-precontents" class="main-precontents"></div>
        <div id="main-contents" class="main-contents">
{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}

            <div class="pad3"></div>

            <div class="inputbox">
            <h1>{$subject} #{$id}</h1>
            <div id="inputbox-error">{$notFound}</div>
            <!--  -->
            </div>
        </div>
    </div>
{include file='footer.tpl'}