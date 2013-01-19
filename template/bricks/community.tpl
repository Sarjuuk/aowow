                {strip}
var lv_comments = [
{foreach name=forCo from=$community.co key=number item=co}
	{ldelim}
		number:{$co.number},
		user:'{$co.user}',
		body:'{$co.body|escape:"javascript"}',
		date:'{$co.date|date_format:"%Y/%m/%d %H:%M:%S"}',
		{if $co.roles!=0}
			roles:{$co.roles},
		{/if}
		{if $co.indent!=0}
			indent:{$co.indent},
		{/if}
		rating:{$co.rating},
		replyTo:{$co.replyto},
		purged:{$co.purged},
		deleted:0,
		raters:[{foreach name=foo2 key=id from=$co.raters item=rater}[{$rater.userid},{$rater.rate}]{if $smarty.foreach.foo2.last}{else},{/if}{/foreach}],
		id:{$co.id}
        
        ,sticky:{$co.sticky}
        ,userRating:{$co.userRating}
	{rdelim}
	{if $smarty.foreach.forCo.last}{else},{/if}
{/foreach}
];
{/strip}
                {strip}
var lv_screenshots = [
{foreach name=forSc from=$community.sc key=number item=sc}
    {ldelim}
        id:{$sc.Id},
        user:'{$sc.user}',
        date:'{$sc.date|date_format:"%Y/%m/%d %H:%M:%S"}',
        width:{$sc.width},
        height:{$sc.height},
        type:{$page.type},
        typeId:{$page.typeId},
		{if isset($sc.sticky)}
			sticky:{$sc.sticky},
		{/if}
        caption:'{$sc.caption|escape:"javascript"}'
    {rdelim}
	{if $smarty.foreach.forSc.last}{else},{/if}
{/foreach}
];
{/strip}
                {strip}
var lv_videos = [
{foreach name=forVi from=$community.vi key=number item=vi}
    {ldelim}
        id:{$vi.Id},
        user:'{$vi.user}',
        date:'{$vi.date|date_format:"%Y/%m/%d %H:%M:%S"}',
        videoType:1, {* there is only youtube *}
        videoId:'{$vi.videoId}',
        type:{$page.type},
        typeId:{$page.typeId},
		{if isset($vi.sticky)}
			sticky:{$vi.sticky},
		{/if}
        caption:'{$vi.caption|escape:"javascript"}'
    {rdelim}
	{if $smarty.foreach.forVi.last}{else},{/if}
{/foreach}
];
{/strip}
