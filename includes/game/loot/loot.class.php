<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/* NOTE!
 *
 * TrinityCore uses "mode", a bitmask in a loot template, to distinguish dynamic properties of the template. i.e. entries get enabled if the boss fight progresses in a certain way.
 * WH/we uses "mode", different loot templates, to describe the raid/dungeon difficulty
 *
 * try to not mix this shit up
 */

abstract class Loot
{
    // Loot handles
    public const string FISHING     =       'fishing_loot_template'; // fishing_loot_template                                                                      (no relation entry is linked with ID of the fishing zone or area)
    public const string CREATURE    =      'creature_loot_template'; // creature_loot_template       entry   many <- many    creature_template   lootid
    public const string GAMEOBJECT  =    'gameobject_loot_template'; // gameobject_loot_template     entry   many <- many    gameobject_template data1             (see its lockType for mining, herbing, fishing or generic looting)
    public const string ITEM        =          'item_loot_template'; // item_loot_template           entry   many <- one     item_template       entry
    public const string DISENCHANT  =    'disenchant_loot_template'; // disenchant_loot_template     entry   many <- many    item_template       DisenchantID
    public const string PROSPECTING =   'prospecting_loot_template'; // prospecting_loot_template    entry   many <- one     item_template       entry
    public const string MILLING     =       'milling_loot_template'; // milling_loot_template        entry   many <- one     item_template       entry
    public const string PICKPOCKET  = 'pickpocketing_loot_template'; // pickpocketing_loot_template  entry   many <- many    creature_template   pickpocketloot
    public const string SKINNING    =      'skinning_loot_template'; // skinning_loot_template       entry   many <- many    creature_template   skinloot          (see the creatures flags for mining, herbing, salvaging or actual skinning)
    public const string MAIL        =          'mail_loot_template'; // mail_loot_template           entry                   quest_template      RewMailTemplateId (quest + achievement)
    public const string SPELL       =         'spell_loot_template'; // spell_loot_template          entry   many <- one     spell.dbc           id
    public const string REFERENCE   =     'reference_loot_template'; // reference_loot_template      entry   many <- many    *_loot_template     reference

    protected const array TEMPLATES = [self::REFERENCE, self::ITEM, self::DISENCHANT, self::PROSPECTING, self::CREATURE, self::MILLING, self::PICKPOCKET, self::SKINNING, self::FISHING, self::GAMEOBJECT, self::MAIL, self::SPELL];

    public array $jsGlobals = [];

    protected array $results = [];

    /**
     * builds stack info string for listview rows
     * issue: TC always has an equal distribution between min/max
     * and yes, it wants a string .. how weired is that..
     *
     * @param   int    $min min amount
     * @param   int    $max max amount
     * @param   float  $fraction manually set %pct of the max stack result
     * @return ?string stack info or null on error
     */
    protected static function buildStack(int $min, int $max, float $fraction = 0) : ?string
    {
        if (!$min || !$max || $max <= $min)
            return null;

        $stack = [];
        for ($i = $min; $i <= $max; $i++)
            $stack[$i] = round(100 / max(1, (($fraction ? 0 : 1) + $max - $min)), 3);

        if ($fraction)
        {
            $k = array_key_last($stack);
            $stack[$k] = $fraction;
        }

        // do not replace with Util::toJSON !
        return json_encode($stack, JSON_NUMERIC_CHECK);
    }

    /**
     * @param  array $data js global data to store
     * @return void
     */
    protected function storeJSGlobals(array $data) :  void
    {
        Util::mergeJsGlobals($this->jsGlobals, $data);
    }
}

?>
