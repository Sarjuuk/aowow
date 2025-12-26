/* aowow - custom: TrinityCore Conditions */
var ConditionList = new function() {
    var self = this,
        _conditions = null;

    self.createCell = function(conditions)
    {
        if (!conditions)
            return null;

        _conditions = conditions;

        return _createCell();
    };

    self.createTab = function(conditions)
    {
        if (!conditions)
            return null;

        _conditions = conditions;

        return _createTab();
    };

    function _makeList(mask, src, tpl)
    {
        var arr  = Listview.funcBox.assocBinFlags(mask, src).sort(),
            buff = '';

        for (var i = 0, len = arr.length; i < len; ++i)
        {
            if (len > 1 && i == len - 1)
                buff += LANG.or;
            else if (i > 0)
                buff += LANG.comma;

            buff += $WH.sprintf(tpl, arr[i], src[arr[i]]);
        }

        return buff;
    }

    function _parseEntry(entry, targets, target)
    {
        var str    = '',
            negate = false,
            strIdx = 0,
            param  = [];

        [strIdx, ...param]  = entry;

        negate = strIdx < 0;
        strIdx = Math.abs(strIdx);

        if (!g_conditions[strIdx])
            return 'unknown condition index #' + strIdx;

        switch (strIdx)
        {
            case  5:
                var standings = {};
                for (let i in g_reputation_standings)
                    standings[i * 1 + 1] = g_reputation_standings[i];

                param[1] = _makeList(entry[2], standings, '$2');
                break;

            case  6:
                if (entry[1] == 1)
                    param[0] = $WH.sprintf('[span class=icon-alliance]$1[/span]', g_sides[1]);
                else if (entry[1] == 2)
                    param[0] = $WH.sprintf('[span class=icon-horde]$1[/span]', g_sides[2]);
                else
                    param[0] = $WH.sprintf('[span class=icon-alliance]$1[/span]$2[span class=icon-horde]$3[/span]', g_sides[1], LANG.or, g_sides[2]);
                break;

            case 10:
                param[0] = g_drunk_states[entry[1]] ?? 'UNK DRUNK STATE';
                break;

            case 13:
                param[2] = g_instance_info[entry[3]] ?? 'UNK INSTANCE INFO';
                break;

            case 15:
                param[0] = _makeList(entry[1], g_chr_classes, '[class=$1]');
                break;

            case 16:
                param[0] = _makeList(entry[1], g_chr_races, '[race=$1]');
                break;

            case 20:
                if (entry[1] == 0)
                    param[0] = $WH.sprintf('[span class=icon-$1]$2[/span]', g_file_genders[0], LANG.male);
                else if (entry[1] == 1)
                    param[0] = $WH.sprintf('[span class=icon-$1]$2[/span]', g_file_genders[1], LANG.female);
                else
                    param[0] = g_npc_types[10];             // not specified
                break;

            case 21:
                var states = {};
                for (let i in g_unit_states)
                    states[i * 1 + 1] = g_unit_states[i];

                param[0] = _makeList(entry[1], states, '$2');
                break;

            case 22:
                if (entry[2])
                    param[0] = '[zone=' + entry[2] + ']';
                else
                    param[0] = g_zone_categories[entry[1]] ?? 'UNK ZONE';
                break;

            case 24:
                param[0] = g_npc_types[entry[1]] ?? 'UNK NPC TYPE';
                break;

            case 26:
                var idx = 0, buff = [];
                while (entry[1] >= (1 << idx)) {
                    if (!(entry[1] & (1 << idx++)))
                        continue;

                    buff.push(idx);
                }
                param[0] = buff ? buff.join(LANG.comma) : '';
                break;

            case 27:
            case 37:
            case 38:
                param[1] = g_operators[entry[2]];
                break;

            case 31:
                if (entry[2] && entry[1] == 3)
                    param[0] = '[npc=' + entry[2] + ']';
                else if (entry[2] && entry[1] == 5)
                    param[0] = '[object=' + entry[2] + ']';
                else
                    param[0] = g_world_object_types[entry[1]] ?? 'UNK TYPEID';
                break;

            case 32:
                var objectTypes = {};
                for (let i in g_world_object_types)
                    objectTypes[i * 1 + 1] = g_world_object_types[i];

                param[0] = _makeList(entry[1], objectTypes, '$2');
                break;

            case 33:
                param[0] = targets[entry[1]];
                param[1] = g_relation_types[entry[2]] ?? 'UNK RELATION';
                param[2] = targets[target];
                break;

            case 34:
                param[0] = targets[entry[1]];

                var standings = {};
                for (let i in g_reputation_standings)
                    standings[i * 1 + 1] = g_reputation_standings[i];
                param[1] = _makeList(entry[2], standings, '$2');
                break;

            case 35:
                param[0] = targets[entry[1]];
                param[2] = g_operators[entry[3]];
                break;

            case 42:
                if (!entry[1])
                    param[0] = g_stand_states[entry[2]] ?? 'UNK STAND_STATE';
                else if (entry[1] == 1)
                    param[0] = g_stand_states[entry[2] ? 1 : 0];
                else
                    param[0] = '';
                break;

            case 47:
                var quest_states = {};
                for (let i in g_quest_states)
                    quest_states[i * 1 + 1] = g_quest_states[i];

                param[1] = _makeList(entry[2], quest_states, '$2');
                break;
        }

        str = g_conditions[strIdx];

        // fill in params
        return $WH.sprintfa(str, param[0], param[1], param[2], param[3])
        // resolve NegativeCondition
        .replace(/\$N([^:]*):([^;]*);/g, '$' + (negate > 0 ? 2 : 1))
        // resolve vars
        .replace(/\$C(\d+)([^:]*):([^;]*);/g, (_, i, y, n) => (i > 0 ? y : n));
    }

    function _createTab()
    {
        var buff = '';

        // tabs for conditionsTypes
        for (g in _conditions)
        {
            if (!g_condition_sources[g])
                continue;

            let k = 0;
            for (h in _conditions[g])
            {
                var srcGroup, srcEntry, srcId, target,
                    targets, desc,
                    nGroups  = Object.keys(_conditions[g][h]).length,
                    curGroup = 1;

                [srcGroup, srcEntry, srcId, target] = h.split(':').map((x) => parseInt(x));
                [targets, desc] = g_condition_sources[g];

                // resolve targeting
                let src  = desc.replace(/\$T([^:]*):([^;]*);/, (_, t1, t2) => (target ? t2 : t1).replace('%', targets[target]));
                let rand = $WH.rs();

                buff += '[h3][toggler' + (k ? '=hidden' : '') + ' id=' + rand + ']' + $WH.sprintfa(src, srcGroup, srcEntry, srcId) + '[/toggler][/h3][div' + (k++ ? '=hidden' : '') + ' id=' + rand + ']';

                if (nGroups > 1)
                {
                    buff += LANG.note_condition_group + '[br][br]';
                    buff += '[table class=grid]';
                }

                // table for elseGroups
                for (i in _conditions[g][h])
                {
                    var group    = _conditions[g][h][i],
                        nEntries = Object.keys(_conditions[g][h][i]).length;

                    if (nGroups <= 1 && nEntries > 1)
                        buff += '[div style="padding-left:15px"]' + LANG.note_condition + '[/div]';
                    if (nGroups > 1)
                        buff += '[tr][td width=70px valign=middle align=center]' + LANG.group + ' ' + (curGroup++) + LANG.colon + '[/td][td]';

                    // individual conditions
                    buff += '[ol]';
                    for (j in group)
                        buff += '[li]' + _parseEntry(group[j], targets, target) + '[/li]';
                    buff += '[/ol]';

                    if (nGroups > 1)
                        buff += '[/td][/tr]';
                }

                if (nGroups > 1)
                    buff += '[/tr][/table]';

                buff += '[/div]';
            }
        }

        return buff;
    }

    function _createCell()
    {
        var rows = [];

        // tabs for conditionsTypes
        for (let g in _conditions)
        {
            if (!g_condition_sources[g])
                continue;

            for (let h in _conditions[g])
            {
                var target, targets,

                [, , , target] = h.split(':').map((x) => parseInt(x));
                [targets, ] = g_condition_sources[g];

                let nElseGroups = Object.keys(_conditions[g][h]).length

                // table for elseGroups
                for (let i in _conditions[g][h])
                {
                    let subGroup = [],
                        group    = _conditions[g][h][i],
                        nEntries = Object.keys(_conditions[g][h][i]).length
                        buff     = '';

                    if (nElseGroups > 1)
                    {
                        let rand = $WH.rs();
                        buff += '[toggler' + (i > 0 ? '=hidden' : '') + ' id=cell-' + rand + ']' + (i > 0 ? LANG.cnd_or : LANG.cnd_either) + '[/toggler][div' + (i > 0 ? '=hidden' : '') + ' id=cell-' + rand + ']';
                    }

                    // individual conditions
                    for (let j in group)
                        subGroup.push(_parseEntry(group[j], targets, target));

                    for (j in subGroup)
                    {
                        if (nEntries > 1 && j > 0 && j == subGroup.length - 1)
                            buff += LANG.and + '[br]';
                        else if (nEntries > 1 && j > 0)
                            buff += ',[br]';

                        buff += subGroup[j];
                    }

                    if (nElseGroups > 1)
                        buff += '[/div]';

                    rows.push(buff);
                }
            }
        }

        return rows.length > 1 ? rows.join('[br]') : rows[0];
    }

}
/* end custom */
