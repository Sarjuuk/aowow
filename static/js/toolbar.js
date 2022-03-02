var TB_BLOG = 1;
var TB_ARTICLE = 2;
var TB_QUICKFACTS = 4;
var TB_CONTEST = 8;
var TB_CONTEST_SUMMARY = 16;
var TB_INLINE = 32;
var TB_GROUP_ALL = TB_BLOG | TB_ARTICLE | TB_QUICKFACTS | TB_CONTEST | TB_CONTEST_SUMMARY | TB_INLINE;
var TB_GROUP_MAIN = TB_BLOG | TB_ARTICLE | TB_CONTEST;
var TB_GROUP_COMMON = TB_BLOG | TB_ARTICLE | TB_QUICKFACTS | TB_CONTEST;

function __AddToolbar(mode, ta, tbParent, mnParent) {
    var a = function (typeStr, tag) {
        var id = prompt('Please enter the ID of the ' + typeStr + '.', '');
        if (id != null) {
            g_insertTag(ta, '[' + tag + '=' + (parseInt(id) || 0) + ']', '');
        }
    };

    var buttons = [
        { name: 'major-heading', icon: 'header',          mode: TB_GROUP_MAIN,        title: 'Major Heading: [h2]Text[/h2]',                           onclick: g_insertTag.bind(null, ta, '[h2]', '[/h2]')                      },
        { name: 'minor-heading', icon: 'header fa-size-s',mode: TB_GROUP_MAIN,        title: 'Minor Heading: [h3]Text[/h3]',                           onclick: g_insertTag.bind(null, ta, '[h3]', '[/h3]')                      },
        { name: 'b',             icon: 'bold',            mode: TB_GROUP_ALL,         title: 'Bold: [b]Text[/b]',                                      onclick: g_insertTag.bind(null, ta, '[b]', '[/b]')                        },
        { name: 'i',             icon: 'italic',          mode: TB_GROUP_ALL,         title: 'Italic: [i]Text[/i]',                                    onclick: g_insertTag.bind(null, ta, '[i]', '[/i]')                        },
        { name: 'u',             icon: 'underline',       mode: TB_GROUP_ALL,         title: 'Underline: [u]Text[/u]',                                 onclick: g_insertTag.bind(null, ta, '[u]', '[/u]')                        },
        { name: 's',             icon: 'strikethrough',   mode: TB_GROUP_ALL,         title: 'Strikethrough: [s]Text[/s]',                             onclick: g_insertTag.bind(null, ta, '[s]', '[/s]')                        },
        { name: 'small',         icon: 'text-height',     mode: TB_GROUP_MAIN,        title: 'Small: [small]Text[/small]',                             onclick: g_insertTag.bind(null, ta, '[small]', '[/small]')                },
        { name: 'url',           icon: 'link',            mode: TB_GROUP_ALL,         title: 'URL: [url=...]Text[/url]',                               onclick: function () { var e = prompt('Please enter a URL (e.g. /item=1234).', ''); if (e != null) { g_insertTag(ta, '[url=' + $WH.trim(e) + ']', '[/url]') } } },
        { name: 'quote',         icon: 'quote-left',      mode: TB_GROUP_MAIN,        title: 'Quote: [quote]Text[/quote]',                             onclick: g_insertTag.bind(null, ta, '[quote]', '[/quote]')                },
        { name: 'code',          icon: 'code',            mode: TB_GROUP_MAIN,        title: 'Code: [code]Text[/code]',                                onclick: g_insertTag.bind(null, ta, '[code]', '[/code]')                  },
        { name: 'ul',            icon: 'list-ul',         mode: TB_GROUP_MAIN,        title: 'Unordered list (bullets)',                               onclick: g_insertTag.bind(null, ta, '[ul][li]', '[/li][/ul]')             },
        { name: 'ol',            icon: 'list-ol',         mode: TB_GROUP_MAIN,        title: 'Ordered list (numbers)',                                 onclick: g_insertTag.bind(null, ta, '[ol][li]', '[/li][/ol]')             },
        { name: 'li',            icon: 'circle',          mode: TB_GROUP_COMMON,      title: 'List item',                                              onclick: g_insertTag.bind(null, ta, '[li]', '[/li]')                      },
        { name: 'img',           icon: 'picture-o',       mode: TB_GROUP_MAIN,        title: 'Image: [img src=... width=# height=# float=left|right]', onclick: function () { var e = prompt('Please enter a URL to the image.', ''); if (e != null) { g_insertTag(ta, '[img src=' + $WH.trim(e) + ']', '') } } },
        { name: 'pad',           icon: 'square-o',        mode: TB_GROUP_MAIN,        title: 'Padding: [pad]',                                         onclick: g_insertTag.bind(null, ta, '[pad]', '')                          },
        { name: 'winners',       icon: 'star',            mode: TB_CONTEST,           title: 'Winners',                                                onclick: g_insertTag.bind(null, ta, '[winners]', '')                      },
        { name: 'prizes',        icon: 'gift',            mode: TB_CONTEST,           title: 'Prizes',                                                 onclick: g_insertTag.bind(null, ta, '[prizes]', '')                       },
        { name: 'entryform',     icon: 'check',           mode: TB_CONTEST,           title: 'Entry Form',                                             onclick: g_insertTag.bind(null, ta, '[entryform]', '')                    },
        { name: 'fromhtml',      icon: 'html5',           mode: TB_BLOG | TB_ARTICLE, title: 'Convert HTML',                                           onclick: function () { $(ta).val(Markup.fromHtml($(ta).val())).change() } }
    ];

    var e = LANG.ellipsis;
    var menu = [
        [0, 'Color', , [
            [0, LANG.types[3][2], , [
                [0, 'Poor',      g_insertTag.bind(null, ta, '[color=q0]', '[/color]'), null, {className: 'q0'}],
                [1, 'Common',    g_insertTag.bind(null, ta, '[color=q1]', '[/color]'), null, {className: 'q1'}],
                [2, 'Uncommon',  g_insertTag.bind(null, ta, '[color=q2]', '[/color]'), null, {className: 'q2'}],
                [3, 'Rare',      g_insertTag.bind(null, ta, '[color=q3]', '[/color]'), null, {className: 'q3'}],
                [4, 'Epic',      g_insertTag.bind(null, ta, '[color=q4]', '[/color]'), null, {className: 'q4'}],
                [5, 'Legendary', g_insertTag.bind(null, ta, '[color=q5]', '[/color]'), null, {className: 'q5'}],
                [7, 'Heirloom',  g_insertTag.bind(null, ta, '[color=q7]', '[/color]'), null, {className: 'q7'}]
            //  [8, 'WoW Token', g_insertTag.bind(null, ta, '[color=q8]', '[/color]'), null, {className: 'q8'}]
            ]],
            [1, LANG.types[13][2], , [
                [ 6, g_chr_classes[6],  g_insertTag.bind(null, ta, '[color=c6]',  '[/color]'), null, {className: 'c6'}],
            //  [12, g_chr_classes[12], g_insertTag.bind(null, ta, '[color=c12]', '[/color]'), null, {className: 'c12'}],
                [11, g_chr_classes[11], g_insertTag.bind(null, ta, '[color=c11]', '[/color]'), null, {className: 'c11'}],
                [ 3, g_chr_classes[3],  g_insertTag.bind(null, ta, '[color=c3]',  '[/color]'), null, {className: 'c3'}],
                [ 8, g_chr_classes[8],  g_insertTag.bind(null, ta, '[color=c8]',  '[/color]'), null, {className: 'c8'}],
            //  [10, g_chr_classes[10], g_insertTag.bind(null, ta, '[color=c10]', '[/color]'), null, {className: 'c10'}],
                [ 2, g_chr_classes[2],  g_insertTag.bind(null, ta, '[color=c2]',  '[/color]'), null, {className: 'c2'}],
                [ 5, g_chr_classes[5],  g_insertTag.bind(null, ta, '[color=c5]',  '[/color]'), null, {className: 'c5'}],
                [ 4, g_chr_classes[4],  g_insertTag.bind(null, ta, '[color=c4]',  '[/color]'), null, {className: 'c4'}],
                [ 7, g_chr_classes[7],  g_insertTag.bind(null, ta, '[color=c7]',  '[/color]'), null, {className: 'c7'}],
                [ 9, g_chr_classes[9],  g_insertTag.bind(null, ta, '[color=c9]',  '[/color]'), null, {className: 'c9'}],
                [ 1, g_chr_classes[1],  g_insertTag.bind(null, ta, '[color=c1]',  '[/color]'), null, {className: 'c1'}],
            ]],
            // gems
            [2, LANG.gems, , [
                [ 1, g_gem_colors[1],  g_insertTag.bind(null, ta, '[color=meta-gem]',      '[/color]'), null, {className: 'gem1'}],
                [ 2, g_gem_colors[2],  g_insertTag.bind(null, ta, '[color=red-gem]',       '[/color]'), null, {className: 'gem2'}],
                [ 4, g_gem_colors[4],  g_insertTag.bind(null, ta, '[color=yellow-gem]',    '[/color]'), null, {className: 'gem4'}],
                [ 8, g_gem_colors[8],  g_insertTag.bind(null, ta, '[color=blue-gem]',      '[/color]'), null, {className: 'gem8'}],
                [ 6, g_gem_colors[6],  g_insertTag.bind(null, ta, '[color=orange-gem]',    '[/color]'), null, {className: 'gem6'}],
                [10, g_gem_colors[10], g_insertTag.bind(null, ta, '[color=purple-gem]',    '[/color]'), null, {className: 'gem10'}],
                [12, g_gem_colors[12], g_insertTag.bind(null, ta, '[color=green-gem]',     '[/color]'), null, {className: 'gem12'}],
                [14, g_gem_colors[14], g_insertTag.bind(null, ta, '[color=prismatic-gem]', '[/color]'), null, {className: 'gem14'}]
            ]],
            // npcs
            [3, LANG.types[1][2], , [
                [1, 'Yell',    g_insertTag.bind(null, ta, '[color=yell]',    '[/color]'), null, {className: 's1'}],
                [2, 'Say',     g_insertTag.bind(null, ta, '[color=say]',     '[/color]'), null, {className: 's2'}],
                [3, 'Whisper', g_insertTag.bind(null, ta, '[color=whisper]', '[/color]'), null, {className: 's3'}],
                [4, 'Emote',   g_insertTag.bind(null, ta, '[color=emote]',   '[/color]'), null, {className: 's4'}]
            ]],
            [4, LANG.reputation, , [
                [0, g_reputation_standings[0], g_insertTag.bind(null, ta, '[color=hated]',      '[/color]'), null, {className: 'rep-hated'}],
                [1, g_reputation_standings[2], g_insertTag.bind(null, ta, '[color=hostile]',    '[/color]'), null, {className: 'rep-hostile'}],
                [2, g_reputation_standings[2], g_insertTag.bind(null, ta, '[color=unfriendly]', '[/color]'), null, {className: 'rep-unfriendly'}],
                [3, g_reputation_standings[3], g_insertTag.bind(null, ta, '[color=neutral]',    '[/color]'), null, {className: 'rep-neutral'}],
                [4, g_reputation_standings[4], g_insertTag.bind(null, ta, '[color=friendly]',   '[/color]'), null, {className: 'rep-friendly'}],
                [5, g_reputation_standings[5], g_insertTag.bind(null, ta, '[color=honored]',    '[/color]'), null, {className: 'rep-honored'}],
                [6, g_reputation_standings[6], g_insertTag.bind(null, ta, '[color=revered]',    '[/color]'), null, {className: 'rep-revered'}],
                [7, g_reputation_standings[7], g_insertTag.bind(null, ta, '[color=exalted]',    '[/color]'), null, {className: 'rep-exalted'}],
            ]],
            [5, LANG.types[15][2], , [
                [10, g_gem_colors[2],  g_insertTag.bind(null, ta, '[color=q10]', '[/color]'), null, {className: 'q10'}],
                [1,  g_gem_colors[6],  g_insertTag.bind(null, ta, '[color=r1]',  '[/color]'), null, {className: 'r1'}],
                [2,  g_gem_colors[4],  g_insertTag.bind(null, ta, '[color=r2]',  '[/color]'), null, {className: 'r2'}],
                [3,  g_gem_colors[12], g_insertTag.bind(null, ta, '[color=r3]',  '[/color]'), null, {className: 'r3'}],
                [4,  'Gray',           g_insertTag.bind(null, ta, '[color=r4]',  '[/color]'), null, {className: 'r4'}],
            ]]
        ]],
        [1, LANG.icon, , [
            [0, LANG.su_note_other + LANG.ellipsis, function () {
                var e = prompt('Please enter the name of the icon.', '');
                if (e != null) {
                    g_insertTag('editBox', '[icon name=' + e + ']', '[/icon]')
                }
            }],
            [1, LANG.classes, , [
                [ 6, g_chr_classes[6],  g_insertTag.bind(null, ta, '[icon name=class_deathknight]', '[/icon]'), null, {className: 'c6',  tinyIcon: 'class_deathknight'}],
            //  [12, g_chr_classes[12], g_insertTag.bind(null, ta, '[icon name=class_demonhunter]', '[/icon]'), null, {className: 'c12'  tinyIcon: 'class_demonhunter'}],
                [11, g_chr_classes[11], g_insertTag.bind(null, ta, '[icon name=class_druid]', '[/icon]'),       null, {className: 'c11', tinyIcon: 'class_druid'}],
                [ 3, g_chr_classes[3],  g_insertTag.bind(null, ta, '[icon name=class_hunter]', '[/icon]'),      null, {className: 'c3',  tinyIcon: 'class_hunter'}],
                [ 8, g_chr_classes[8],  g_insertTag.bind(null, ta, '[icon name=class_mage]', '[/icon]'),        null, {className: 'c8',  tinyIcon: 'class_mage'}],
            //  [10, g_chr_classes[10], g_insertTag.bind(null, ta, '[icon name=class_monk]', '[/icon]'),        null, {className: 'c10', tinyIcon: 'class_monk'}],
                [ 2, g_chr_classes[2],  g_insertTag.bind(null, ta, '[icon name=class_paladin]', '[/icon]'),     null, {className: 'c2',  tinyIcon: 'class_paladin'}],
                [ 5, g_chr_classes[5],  g_insertTag.bind(null, ta, '[icon name=class_priest]', '[/icon]'),      null, {className: 'c5',  tinyIcon: 'class_priest'}],
                [ 4, g_chr_classes[4],  g_insertTag.bind(null, ta, '[icon name=class_rogue]', '[/icon]'),       null, {className: 'c4',  tinyIcon: 'class_rogue'}],
                [ 7, g_chr_classes[7],  g_insertTag.bind(null, ta, '[icon name=class_shaman]', '[/icon]'),      null, {className: 'c7',  tinyIcon: 'class_shaman'}],
                [ 8, g_chr_classes[8],  g_insertTag.bind(null, ta, '[icon name=class_warlock]', '[/icon]'),     null, {className: 'c9',  tinyIcon: 'class_warlock'}],
                [ 1, g_chr_classes[1],  g_insertTag.bind(null, ta, '[icon name=class_warrior]', '[/icon]'),     null, {className: 'c1',  tinyIcon: 'class_warrior'}]
            ]],
            [2, LANG.races, , [
                [, LANG.male],
                [10, g_chr_races[10], g_insertTag.bind(null, ta, '[icon name=race_bloodelf_male]', '[/icon]'), null, {tinyIcon: 'race_bloodelf_male'}],
                [11, g_chr_races[11], g_insertTag.bind(null, ta, '[icon name=race_draenei_male]', '[/icon]'),  null, {tinyIcon: 'race_draenei_male'}],
                [ 3, g_chr_races[3],  g_insertTag.bind(null, ta, '[icon name=race_dwarf_male]', '[/icon]'),    null, {tinyIcon: 'race_dwarf_male'}],
                [ 7, g_chr_races[7],  g_insertTag.bind(null, ta, '[icon name=race_gnome_male]', '[/icon]'),    null, {tinyIcon: 'race_gnome_male'}],
            //  [ 9, g_chr_races[9],  g_insertTag.bind(null, ta, '[icon name=race_goblin_male]', '[/icon]'),   null, {tinyIcon: 'race_goblin_male'}],
                [ 1, g_chr_races[1],  g_insertTag.bind(null, ta, '[icon name=race_human_male]', '[/icon]'),    null, {tinyIcon: 'race_human_male'}],
                [ 4, g_chr_races[4],  g_insertTag.bind(null, ta, '[icon name=race_nightelf_male]', '[/icon]'), null, {tinyIcon: 'race_nightelf_male'}],
                [ 2, g_chr_races[2],  g_insertTag.bind(null, ta, '[icon name=race_orc_male]', '[/icon]'),      null, {tinyIcon: 'race_orc_male'}],
            //  [24, g_chr_races[24], g_insertTag.bind(null, ta, '[icon name=race_pandaren_male]', '[/icon]'), null, {tinyIcon: 'race_pandaren_male'}],
                [ 6, g_chr_races[6],  g_insertTag.bind(null, ta, '[icon name=race_tauren_male]', '[/icon]'),   null, {tinyIcon: 'race_tauren_male'}],
                [ 8, g_chr_races[8],  g_insertTag.bind(null, ta, '[icon name=race_troll_male]', '[/icon]'),    null, {tinyIcon: 'race_troll_male'}],
                [ 5, g_chr_races[5],  g_insertTag.bind(null, ta, '[icon name=race_scourge_male]', '[/icon]'),  null, {tinyIcon: 'race_scourge_male'}],
            //  [22, g_chr_races[22], g_insertTag.bind(null, ta, '[icon name=race_worgen_male]', '[/icon]'),   null, {tinyIcon: 'race_worgen_male'}],

                [, LANG.female],
                [10, g_chr_races[10], g_insertTag.bind(null, ta, '[icon name=race_bloodelf_female]', '[/icon]'), null, {tinyIcon: 'race_bloodelf_female'}],
                [11, g_chr_races[11], g_insertTag.bind(null, ta, '[icon name=race_draenei_female]', '[/icon]'),  null, {tinyIcon: 'race_draenei_female'}],
                [ 3, g_chr_races[3],  g_insertTag.bind(null, ta, '[icon name=race_dwarf_female]', '[/icon]'),    null, {tinyIcon: 'race_dwarf_female'}],
                [ 7, g_chr_races[7],  g_insertTag.bind(null, ta, '[icon name=race_gnome_female]', '[/icon]'),    null, {tinyIcon: 'race_gnome_female'}],
            //  [ 9, g_chr_races[9],  g_insertTag.bind(null, ta, '[icon name=race_goblin_female]', '[/icon]'),   null, {tinyIcon: 'race_goblin_female'}],
                [ 1, g_chr_races[1],  g_insertTag.bind(null, ta, '[icon name=race_human_female]', '[/icon]'),    null, {tinyIcon: 'race_human_female'}],
                [ 4, g_chr_races[4],  g_insertTag.bind(null, ta, '[icon name=race_nightelf_female]', '[/icon]'), null, {tinyIcon: 'race_nightelf_female'}],
                [ 2, g_chr_races[2],  g_insertTag.bind(null, ta, '[icon name=race_orc_female]', '[/icon]'),      null, {tinyIcon: 'race_orc_female'}],
            //  [24, g_chr_races[24], g_insertTag.bind(null, ta, '[icon name=race_pandaren_female]', '[/icon]'), null, {tinyIcon: 'race_pandaren_female'}],
                [ 6, g_chr_races[6],  g_insertTag.bind(null, ta, '[icon name=race_tauren_female]', '[/icon]'),   null, {tinyIcon: 'race_tauren_female'}],
                [ 8, g_chr_races[8],  g_insertTag.bind(null, ta, '[icon name=race_troll_female]', '[/icon]'),    null, {tinyIcon: 'race_troll_female'}],
                [ 5, g_chr_races[5],  g_insertTag.bind(null, ta, '[icon name=race_scourge_female]', '[/icon]'),  null, {tinyIcon: 'race_scourge_female'}],
            //  [22, g_chr_races[22], g_insertTag.bind(null, ta, '[icon name=race_worgen_female]', '[/icon]'),   null, {tinyIcon: 'race_worgen_female'}]
            ]],
            [3, LANG.tab_factions, , [
                [1, g_sides[1], g_insertTag.bind(null, ta, '[icon name=side_alliance]', '[/icon]'), null, {tinyIcon: 'side_alliance'}],
                [2, g_sides[2], g_insertTag.bind(null, ta, '[icon name=side_horde]',    '[/icon]'), null, {tinyIcon: 'side_horde'}]
            ]],
            [4, g_skill_categories[11], , [
                [171, g_spell_skills[171], g_insertTag.bind(null, ta, '[icon name=trade_alchemy]', '[/icon]'),                null, {tinyIcon: 'trade_alchemy'}],
                [164, g_spell_skills[164], g_insertTag.bind(null, ta, '[icon name=trade_blacksmithing]', '[/icon]'),          null, {tinyIcon: 'trade_blacksmithing'}],
                [333, g_spell_skills[333], g_insertTag.bind(null, ta, '[icon name=trade_engraving]', '[/icon]'),              null, {tinyIcon: 'trade_engraving'}],
                [202, g_spell_skills[202], g_insertTag.bind(null, ta, '[icon name=trade_engineering]', '[/icon]'),            null, {tinyIcon: 'trade_engineering'}],
                [182, g_spell_skills[182], g_insertTag.bind(null, ta, '[icon name=spell_nature_naturetouchgrow]', '[/icon]'), null, {tinyIcon: 'spell_nature_naturetouchgrow'}],
                [773, g_spell_skills[773], g_insertTag.bind(null, ta, '[icon name=inv_inscription_tradeskill01]', '[/icon]'), null, {tinyIcon: 'inv_inscription_tradeskill01'}],
                [755, g_spell_skills[755], g_insertTag.bind(null, ta, '[icon name=inv_misc_gem_01]', '[/icon]'),              null, {tinyIcon: 'inv_misc_gem_01'}],
                [165, g_spell_skills[165], g_insertTag.bind(null, ta, '[icon name=inv_misc_armorkit_17]', '[/icon]'),         null, {tinyIcon: 'inv_misc_armorkit_17'}],
                [186, g_spell_skills[186], g_insertTag.bind(null, ta, '[icon name=trade_mining]', '[/icon]'),                 null, {tinyIcon: 'trade_mining'}],
                [393, g_spell_skills[393], g_insertTag.bind(null, ta, '[icon name=inv_misc_pelt_wolf_01]', '[/icon]'),        null, {tinyIcon: 'inv_misc_pelt_wolf_01'}],
                [197, g_spell_skills[197], g_insertTag.bind(null, ta, '[icon name=trade_tailoring]', '[/icon]'),              null, {tinyIcon: 'trade_tailoring'}]
            ]],
            [5, g_skill_categories[9], , [
            //  [794, g_spell_skills[794], g_insertTag.bind(null, ta, '[icon name=trade_archaeology]', '[/icon]'),          null, {tinyIcon: 'trade_archaeology'}],
                [185, g_spell_skills[185], g_insertTag.bind(null, ta, '[icon name=inv_misc_food_15]', '[/icon]'),           null, {tinyIcon: 'inv_misc_food_15'}],
                [129, g_spell_skills[129], g_insertTag.bind(null, ta, '[icon name=spell_holy_sealofsacrifice]', '[/icon]'), null, {tinyIcon: 'spell_holy_sealofsacrifice'}],
                [356, g_spell_skills[356], g_insertTag.bind(null, ta, '[icon name=trade_fishing]', '[/icon]'),              null, {tinyIcon: 'trade_fishing'}],
                [762, g_spell_skills[762], g_insertTag.bind(null, ta, '[icon name=spell_nature_swiftness]', '[/icon]'),     null, {tinyIcon: 'spell_nature_swiftness'}]
            ]],
            [6, LANG.sockets, , [
                [ 1, g_gem_colors[1],  g_insertTag.bind(null, ta, '[span class=socket-meta]', '[/span]'),      null, {className: 'socket-meta'}],
                [ 2, g_gem_colors[2],  g_insertTag.bind(null, ta, '[span class=socket-red]', '[/span]'),       null, {className: 'socket-red'}],
                [ 4, g_gem_colors[4],  g_insertTag.bind(null, ta, '[span class=socket-yellow]', '[/span]'),    null, {className: 'socket-yellow'}],
                [ 8, g_gem_colors[8],  g_insertTag.bind(null, ta, '[span class=socket-blue]', '[/span]'),      null, {className: 'socket-blue'}],
                [14, g_gem_colors[14], g_insertTag.bind(null, ta, '[span class=socket-prismatic]', '[/span]'), null, {className: 'socket-prismatic'}]
            ]]
        ]],
        [2, LANG.markup_links, , [
            [9,  LANG.types[10][0] + e, a.bind(null, LANG.types[10][1], "achievement")],
            [11, LANG.types[13][0] + e, a.bind(null, LANG.types[13][1], "class")],
            [7,  LANG.types[8][0]  + e, a.bind(null, LANG.types[8][1],  "faction")],
            [0,  LANG.types[3][0]  + e, a.bind(null, LANG.types[3][1],  "item")],
            [1,  LANG.types[4][0]  + e, a.bind(null, LANG.types[4][1],  "itemset")],
            [2,  LANG.types[1][0]  + e, a.bind(null, LANG.types[1][1],  "npc")],
            [3,  LANG.types[2][0]  + e, a.bind(null, LANG.types[2][1],  "object")],
            [8,  LANG.types[9][0]  + e, a.bind(null, LANG.types[9][1],  "pet")],
            [4,  LANG.types[5][0]  + e, a.bind(null, LANG.types[5][1],  "quest")],
            [12, LANG.types[14][0] + e, a.bind(null, LANG.types[14][1], "race")],
            [13, LANG.types[15][0] + e, a.bind(null, LANG.types[15][1], "skill")],
            [5,  LANG.types[6][0]  + e, a.bind(null, LANG.types[6][1],  "spell")],
            [6,  LANG.types[7][0]  + e, a.bind(null, LANG.types[7][1],  "zone")],
            [10, LANG.types[12][0] + e, a.bind(null, LANG.types[12][1], "event")],
            [14, LANG.types[16][0] + e, a.bind(null, LANG.types[16][1], 'statistic')]
        ]]
    ];

    if (tbParent) {
        var btn;
        tbParent.className += ' toolbar';

        for (var i in buttons) {
            if (buttons[i].mode & mode) {
                $WH.ae(tbParent, btn = $WH.ce('button', { title: buttons[i].title, onclick: buttons[i].onclick },
                    [$WH.ce('img', { className: 'toolbar-' + buttons[i].name, src: g_staticUrl + '/images/deprecated/pixel.gif' })]
                ));

                btn.setAttribute('type', 'button');
            }
        }
    }

    if (mnParent && ((TB_BLOG | TB_ARTICLE | TB_INLINE) & mode)) {
        if (mode == TB_ARTICLE) {
            menu[2][3] = menu[2][3].concat([
                [,   'Maps'],
                [10, 'Map...', insertMapLink],
                [11, 'Pin', g_insertTag.bind(null, ta, '[pin x=0 y=0 url=?]', '[/pin]')]
            ]);
        }

        mnParent.className += ' menu-buttons';
        Menu.addButtons(mnParent, menu);
    }
}

bl_AddToolbar          = __AddToolbar.bind(null, TB_BLOG);
ar_AddToolbar          = __AddToolbar.bind(null, TB_ARTICLE);
ar_AddQuickfactToolbar = __AddToolbar.bind(null, TB_QUICKFACTS);
ar_AddInlineToolbar    = __AddToolbar.bind(null, TB_INLINE);
cn_AddToolbar          = __AddToolbar.bind(null, TB_CONTEST);
cn_AddSummaryToolbar   = __AddToolbar.bind(null, TB_CONTEST_SUMMARY);
