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

    public const string QUERY_BASE = 'SELECT e.*, e.`id` AS ARRAY_KEY FROM ::emotes e';

    public function applyInitData(array $initData, array $opts) : void
    {
        parent::applyInitData($initData, $opts);

        $this->cuFlags     = $initData['cuFlags'];
        $this->cmd         = $initData['cmd'];
        $this->name        = $initData['cmd'];              // remap for generic access
        $this->flags       = $initData['flags'];
        $this->isAnimated  = $initData['isAnimated'];
        $this->state       = $initData['state'];
        $this->stateParam  = $initData['stateParam'];
        $this->parentEmote = $initData['parentEmote'];
        $this->soundId     = $initData['soundId'];

        $this->meToExt   = new LocString($initData, 'meToExt',   UIText::formatMarkup(...));
        $this->meToNone  = new LocString($initData, 'meToNone',  UIText::formatMarkup(...));
        $this->extToMe   = new LocString($initData, 'extToMe',   UIText::formatMarkup(...));
        $this->extToExt  = new LocString($initData, 'extToExt',  UIText::formatMarkup(...));
        $this->extToNone = new LocString($initData, 'extToNone', UIText::formatMarkup(...));
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

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->cmd
        )]];
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `cmd` AS "name_loc0" FROM %n WHERE `id` = %i', self::$dataTable, $id))
            return new LocString($n);
        return null;
    }
}

?>
