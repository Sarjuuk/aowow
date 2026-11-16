$WH.Wow.Quest.setupScalingRewards = function (opts)
{
    const maxLevel = $WH.Wow.getMaxPlayerLevel();
    if (opts.maxLevel > maxLevel)
        opts.maxLevel = maxLevel;

    var curLevel     = opts.maxLevel || opts.minLevel || maxLevel;
    var realMaxLvl   = Math.min(opts.maxLevel + 10, maxLevel);
    var updateReward = function (unused1, unused2, levels)
    {
        var selectedLvl = levels[1];
        var level       = Math.min(selectedLvl, opts.maxLevel);
        var span;
        if (span = $WH.ge('quest-reward-coin'))
        {
            $WH.ee(span);
            var
                money      = 0,
                moneyAtCap = 0;

            if (opts.coin)
            {
                if (opts.coin.levels[level])
                    money = Math.round(opts.coin.levels[level] * opts.coin.multiplier);
             // aowow - seems wrong .. moneyAtCap can never be displayed
             // else if (opts.coin.rewardAtCap)
                if (opts.coin.rewardAtCap)
                {
                    if (selectedLvl >= maxLevel)
                        money = opts.coin.rewardAtCap;
                    else if (selectedLvl >= realMaxLvl)
                        moneyAtCap = opts.coin.rewardAtCap;
                }
            }

            if (money)
                $(span).html(g_getMoneyHtml(Math.round(money))); // WH.ae(span, WH.Wow.buildMoney({ copper: Math.round(money) }));
            else
                $WH.ae(span, $WH.ce('span', { className: 'moneycopper' }, $WH.ct('0')));

            if (moneyAtCap)
            {
                var div = $WH.ce('div');
                $(div).html(g_getMoneyHtml(Math.round(moneyAtCap))); // WH.ae(div, WH.Wow.buildMoney({ copper: Math.round(moneyAtCap) }));
                span.innerHTML += ' &nbsp;' + $WH.sprintf(/* WH.TERMS */LANG.atlevel_format, div.innerHTML, maxLevel)
            }
        }

        if (span = $WH.ge('quest-reward-xp'))
        {
            $WH.ee(span);
            var xpGain = 0;
            if (opts.xp && opts.xp.levels[level])
                xpGain = Math.round(opts.xp.levels[level] * opts.xp.multiplier);

            var levelOver = selectedLvl - opts.maxLevel;
            if (levelOver >= 10)
                xpGain *= 0.1;
            else if (levelOver >= 6)
                xpGain *= 1 - (levelOver - 5) * 0.2

            xpGain = $WH.Wow.Quest.roundQuestXp(xpGain);
            if (xpGain)
            {
                let buff = LANG.experience_format.replace('%s', xpGain.toLocaleString()); // let buff = WH.term('experience_format', xpGain.toLocaleString());

                $WH.st(span, buff);
            }
        }
    };

    updateReward(undefined, undefined, [undefined, curLevel]);

    var container = $WH.ge('quest-reward-slider');
    if (container)
        $WH.ee(container);

    if (container && opts.maxLevel && opts.minLevel && realMaxLvl > opts.minLevel)
    {
        var width  = $(container).outerWidth();
        var size   = width && width < 300 ? width : 300;
        var slider = Slider.init(container,
        {
            handleSize: 9,
            maxValue: realMaxLvl,
            minValue: opts.minLevel,
            onMove: updateReward,
            title: LANG.level, // WH.GlobalStrings.LEVEL,
            trackSize: size
        });
        Slider.setValue(slider, curLevel);

        $WH.Tooltip.simple(slider, LANG.tooltip_changelevel2, 'q2'); // WH.Tooltips.attach(slider, WH.TERMS.dragtochangelevel_stc, 'q2', { byCursor: true });
        $WH.Tooltip.simple(Slider.getInput(slider), LANG.tooltip_changelevel, 'q2', true); // WH.Tooltips.attach(Slider.getInput(slider), WH.TERMS.clicktochangelevel_stc, 'q2');
    }
};
