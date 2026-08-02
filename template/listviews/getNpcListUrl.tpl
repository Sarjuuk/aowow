 // $WH.Page.Spell = new function ()
    $WH.PageSpell = new function ()
    {
        const _self = { npcCategoryPaths: undefined };

        this.getNpcListUrl = function(filter, npc)
        {
         // return $WH.Url.generatePath(getBasePath(npc.type) + '/min-level:' + npc.minlevel + '/max-level:' + npc.maxlevel + '?filter=' + filter);
            return '?npcs=' + npc.type + '&filter=' + filter + ';minle=' + npc.minlevel + ';maxle=' + npc.maxlevel;
        };

        function getBasePath(creatureType)
        {
            let url = '/npcs';

            if (_self.npcCategoryPaths[creatureType])
                url += '=' + creatureType + _self.npcCategoryPaths[creatureType];

            return url;
        }
    };
