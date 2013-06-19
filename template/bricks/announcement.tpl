{strip}
    {math equation="abs(x)" x=$an.id assign="pId"}
{/strip}
                <div id="announcement-{$pId}"></div>
                <script type="text/javascript">
                    new Announcement({ldelim}
                        parent: 'announcement-{$pId}',
                        id: {$an.id},
                        text: {if $an.text[0] == '$'}{$an.text|substr:1}{else}'{$an.text}'{/if},
                        name: '{$an.name}',
                        style: '{$an.style}',
                        status: {$an.status},
                        mode: {$an.mode}
                    {rdelim});
                </script>
