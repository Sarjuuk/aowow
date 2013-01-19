{math equation="abs(x)" x=$an.Id assign="pId"}
{math equation="(x & 0xF)" x=$an.flags assign="mode"}
{math equation="(x & 0xF0) >> 4" x=$an.flags assign="status"}
{math equation="(x & 0xF00) >> 8" x=$an.flags assign="jsVar"}

    <div id="announcement-{$pId}"></div>
<script type="text/javascript">
    new Announcement({ldelim}
        parent: 'announcement-{$pId}',
        id: {$an.Id},
        text: {if $jsVar}{$an.text}{else}'{$an.text}'{/if},
        name: '{$an.name}',
        style: '{$an.style}',
        status: {$status},
        mode: {$mode}
    {rdelim});
</script>