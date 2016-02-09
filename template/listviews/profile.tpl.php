<?php
    Util::addNote(U_GROUP_NONE, 'Profile-listview NYI');
/* data:
{
    id:{$curr.id},
    name:'{$curr.name|escape:"javascript"}',
    race:{$curr.race},
    classs:{$curr.class},
    gender:{$curr.gender},
    level:{$curr.level},
    faction:{$curr.faction},
    talenttree1:{$curr.tree[0]},
    talenttree2:{$curr.tree[1]},
    talenttree3:{$curr.tree[2]},
    talentspec:{$curr.spec},
    achievementpoints:{$curr.acvPts},
    guild:'{$curr.guildName|escape:"javascript"}', {* 0 if none *}
    guildrank:{$curr.guildRank},
    {if isset($curr.description)}
        description:'{$curr.description|escape:"javascript"}',
    {/if}
    {if isset($curr.icon)}
        icon:'{$curr.icon|escape:"javascript"}',
    {/if}
    {if isset($curr.published)}
        published:1,
    {/if}
    {if isset($curr.pinned)}
        pinned:1,
    {/if}
    {if isset($curr.deleted)}
        deleted:1,
    {/if}
    realm:'{$curr.realmInternal|escape:"javascript"}',
    realmname:'{$curr.realmName|escape:"javascript"}',
    battlegroup:'{$curr.bgInternal|escape:"javascript"}',
    battlegroupname:'{$curr.bgName|escape:"javascript"}',
    region:'{$curr.region|escape:"javascript"}'
}
*/
?>
