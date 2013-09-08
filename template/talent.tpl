{include file='header.tpl'}

    <div id="main">
        <div id="main-precontents"></div>
        <div id="main-contents" class="main-contents">
{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}
            <div id="{$tcType}-classes">
                <div id="{$tcType}-classes-outer">
                    <div id="{$tcType}-classes-inner"><p>{if $tcType == 'tc'}{$lang.chooseClass}{else}{$lang.chooseFamily}{/if}{$lang.colon}</p></div>
                </div>
            </div>
            <div id="{$tcType}-itself"></div>
            <script type="text/javascript">
                {$tcType}_init();
            </script>
            <div class="clear"></div>
        </div>
    </div>

{include file='footer.tpl'}
