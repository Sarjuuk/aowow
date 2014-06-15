{include file='header.tpl'}

    <div id="main">
        <div id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

{if !empty($announcements)}
    {foreach from=$announcements item=item}
        {include file='bricks/announcement.tpl' an=$item}
    {/foreach}
{/if}
            <div class="text">
                <a href="{$wowhead}" class="button-red"><em><b><i>Wowhead</i></b><span>Wowhead</span></em></a>
    {if !empty($found)}
                <h1>{$lang.foundResult} <i>{$search|escape:"html"}</i>{if $ignored}<span class="sub">{$lang.ignoredTerms|sprintf:$ignored}</span>{/if}</h1>
            </div>
            <div id="tabs-generic"></div>
            <div id="lv-generic" class="listview"></div>
            <script type="text/javascript">
                var myTabs = new Tabs({ldelim}parent: $WH.ge('tabs-generic'){rdelim});
{foreach from=$found item="f"}
    {include file="listviews/`$f.file`.tpl" data=$f.data params=$f.params}
{/foreach}
                myTabs.flush();
            </script>
    {else}
            <h1>{$lang.noResult} <i>{$search|escape:"html"}</i>{if $ignored}<span class="sub">{$lang.ignoredTerms|sprintf:$ignored}</span>{/if}</h1>
            <div class="search-noresults"/></div>

            {$lang.tryAgain}
    {/if}
            <div class="clear"></div>

        </div>

    </div>
    
{include file='footer.tpl'}
