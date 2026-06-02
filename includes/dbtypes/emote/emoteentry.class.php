<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EmoteEntry extends DBTypeEntry
{
    public readonly int       $cuFlags;
    public readonly string    $cmd;
    public readonly string    $name;                        // alias of >cmd for generic access
    public readonly int       $flags;
    public readonly bool      $isAnimated;
    public readonly int       $state;
    public readonly int       $stateParam;
    public readonly int       $parentEmote;
    public readonly int       $soundId;

    public readonly LocString $meToExt;
    public readonly LocString $meToNone;
    public readonly LocString $extToMe;
    public readonly LocString $extToExt;
    public readonly LocString $extToNone;


    public static int    $dbType    = Type::EMOTE;
    public static string $brickFile = 'emote';
    public static string $dataTable = '::emotes';

    public const /* string */ QUERY_BASE = 'SELECT e.*, e.`id` AS ARRAY_KEY FROM ::emotes e';

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        $this->meToExt   = new LocString($initData, 'meToExt',   UIText::formatMarkup(...), true);
        $this->meToNone  = new LocString($initData, 'meToNone',  UIText::formatMarkup(...), true);
        $this->extToMe   = new LocString($initData, 'extToMe',   UIText::formatMarkup(...), true);
        $this->extToExt  = new LocString($initData, 'extToExt',  UIText::formatMarkup(...), true);
        $this->extToNone = new LocString($initData, 'extToNone', UIText::formatMarkup(...), true);

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'cmd':                                 // remap for generic access
                    $this->name = $v;                       // do not break, still fill cmd propery
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $preview = '';
        if (!$this->meToExt->isEmpty())
            $preview = $this->meToExt;
        else if (!$this->meToNone->isEmpty())
            $preview = $this->meToNone;
        else if (!$this->extToMe->isEmpty())
            $preview = $this->extToMe;
        else if (!$this->extToExt->isEmpty())
            $preview = $this->extToExt;
        else if (!$this->extToNone->isEmpty())
            $preview = $this->extToNone;

        return array(
            'id'      => $this->id,
            'name'    => $this->cmd,
            'preview' => $preview
        );
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_ANY) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->cmd
        )]];
    }

    public function renderTooltip() : ?string { return null; }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `cmd` AS "name_loc0" FROM %n WHERE `id` = %i', self::$dataTable, $id))
            return new LocString($n);
        return null;
    }
}

?>
