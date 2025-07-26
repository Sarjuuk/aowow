<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Announcement implements \JsonSerializable
{
    public const MODE_PAGE_TOP    = 0;
    public const MODE_CONTENT_TOP = 1;

    public const STATUS_DISABLED  = 0;
    public const STATUS_ENABLED   = 1;
    public const STATUS_DELETED   = 2;

    public readonly int $status;
    private bool        $editable = false;

    public function __construct(
        public readonly int $id,
        private string      $name,
        private LocString   $text,
        private int         $mode = self::MODE_CONTENT_TOP,
                int         $status = self::STATUS_ENABLED,
        private string      $style = '')
    {
        // a negative id displays ENABLE/DISABLE and DELETE links for this announcement
        // TODO - the ugroup check mirrors the js. Add other checks like ownership status? (ownership currently not stored)
        if (User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU) /* && User::$id == $authorId */)
            $this->editable = true;

        if ($this->mode != self::MODE_PAGE_TOP && $this->mode != self::MODE_CONTENT_TOP)
            $this->mode = self::MODE_PAGE_TOP;

        if ($status != self::STATUS_DISABLED && $status != self::STATUS_ENABLED && $status != self::STATUS_DELETED)
            $this->status = self::STATUS_DELETED;
        else
            $this->status = $status;
    }

    public function jsonSerialize() : array
    {
        $json = array(
            'parent' => 'announcement-' . abs($this->id),
            'id'     => $this->editable ? -$this->id : $this->id,
            'mode'   => $this->mode,
            'status' => $this->status,
            'name'   => $this->name,
            'text'   => (string)$this->text                 // force LocString to naive string for display
        );

        if ($this->style)
            $json['style'] = $this->style;

        return $json;
    }

    public function __toString() : string
    {
        if ($this->status == self::STATUS_DELETED)
            return '';

        return "new Announcement(".Util::toJSON($this).");\n";
    }
}

?>
