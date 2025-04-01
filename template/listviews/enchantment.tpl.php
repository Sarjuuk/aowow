Listview.templates.enchantment = {
    sort: [1],
    searchable: 1,
    filtrable: 1,

    columns: [
        {
            id: 'name',
            name: LANG.name,
            type: 'text',
            align: 'left',
            value: 'name',
            compute: function(enchantment, td, tr) {
                var
                    wrapper = $WH.ce('div');

                var a = $WH.ce('a');
                a.style.fontFamily = 'Verdana, sans-serif';
                a.href = this.getItemLink(enchantment);

                if (!enchantment.name) {
                    var i = $WH.ce('i');
                    i.className = 'q0';

                    $WH.ae(i, $WH.ct('<' + LANG.pr_header_noname + '>'));
                    $WH.ae(a, i);
                }
                else
                    $WH.ae(a, $WH.ct(enchantment.name));

                $WH.ae(wrapper, a);

                $WH.ae(td, wrapper);
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(a.name, b.name);
            },
            getVisibleText: function(enchantment) {
                return enchantment.name || LANG.pr_header_noname;
            }
        },
        {
            id: 'trigger',
            name: LANG.types[6][2],
            type: 'text',
            align: 'left',
            value: 'name',
            width: '10%',
            hidden: 1,
            compute: function(enchantment, td, tr) {
                if (enchantment.spells) {
                    var d = $WH.ce('div');
                    d.style.width = (26 * Object.keys(enchantment.spells).length) + 'px';
                    d.style.margin = '0 auto';

                    $.each(enchantment.spells, function (spellId, charges) {
                        var icon = g_spells.createIcon(spellId, 0, charges);
                        icon.style.cssFloat = icon.style.styleFloat = 'left';
                        $WH.ae(d, icon);
                    });

                    $WH.ae(td, d);
                }
            },
            getVisibleText: function(enchantment) {
                if (!enchantment.spells)
                    return null;                            // no spell

                var spellId = $(enchantment.spells).first()[0];
                if (g_spells[spellId])
                    return g_spells[spellId]['name_' + Locale.getName()];

                return '';                                  // unk spell
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(this.getVisibleText(a), this.getVisibleText(b));
            }
        },
        {
            id: 'skill',
            name: LANG.skill,
            type: 'text',
            width: '16%',
            getValue: function(enchantment) {
                return enchantment.reqskillrank || 0;
            },
            compute: function(enchantment, td, tr) {
                if (enchantment.reqskill != null) {
                    var div = $WH.ce('div');
                    div.className = 'small';

                    // DK is the only class in use
                    if (enchantment.reqskill == 776) {
                        var a = $WH.ce('a');
                        a.className = 'q0';
                        a.href = '?class=6';

                        $WH.ae(a, $WH.ct(g_chr_classes[6]));
                        $WH.ae(div, a);
                        $WH.ae(div, $WH.ce('br'));
                    }

                    var a = $WH.ce('a');
                    a.className = 'q1';
                    a.href = '?skill=' + enchantment.reqskill;

                    $WH.ae(a, $WH.ct(g_spell_skills[enchantment.reqskill]));
                    $WH.ae(div, a);

                    if (enchantment.reqskillrank > 0) {
                        var sp = $WH.ce('span');

                        sp.className = 'q0';
                        $WH.ae(sp, $WH.ct(' (' + enchantment.reqskillrank + ')'));
                        $WH.ae(div, sp);
                    }

                    $WH.ae(td, div);
                }
            },
            getVisibleText: function(enchantment) {
                var buff = '';
                if (g_spell_skills[enchantment.reqskill]) {
                    buff += g_spell_skills[enchantment.reqskill];

                    if (enchantment.reqskillrank > 0) {
                        buff += ' ' + enchantment.reqskillrank;
                    }
                }
                return buff;
            },
            sortFunc: function(a, b, col) {
                return $WH.strcmp(g_spell_skills[a.reqskill], g_spell_skills[b.reqskill]) || $WH.strcmp(a.reqskillrank, b.reqskillrank);
            }
        },
    ],
    getItemLink: function(enchantment) {
        return '?enchantment=' + enchantment.id;
    }
}
