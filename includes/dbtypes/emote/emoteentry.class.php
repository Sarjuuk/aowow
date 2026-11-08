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

    public function applyInitData(\Dibi\Row $dbRow, array $opts) : bool
    {
        parent::applyInitData($dbRow, $opts);

        $this->cuFlags     = $dbRow->cuFlags;
        $this->cmd         = $dbRow->cmd;
        $this->name        = $dbRow->cmd;                 // remap for generic access
        $this->flags       = $dbRow->flags;
        $this->isAnimated  = $dbRow->isAnimated;
        $this->state       = $dbRow->state;
        $this->stateParam  = $dbRow->stateParam;
        $this->parentEmote = $dbRow->parentEmote;
        $this->soundId     = $dbRow->soundId;

        $this->meToExt   = new LocString($dbRow, 'meToExt',   UIText::formatMarkup(...));
        $this->meToNone  = new LocString($dbRow, 'meToNone',  UIText::formatMarkup(...));
        $this->extToMe   = new LocString($dbRow, 'extToMe',   UIText::formatMarkup(...));
        $this->extToExt  = new LocString($dbRow, 'extToExt',  UIText::formatMarkup(...));
        $this->extToNone = new LocString($dbRow, 'extToNone', UIText::formatMarkup(...));

        return true;
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
