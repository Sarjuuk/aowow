<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Mail
{
    private  array  $attachments = [];
    private ?string $sender = null;
    private ?string $delay  = null;

    public function __construct(
        private  int       $id,
        public   LocString $subject,
        public   LocString $body,
                ?int       $senderNpcID = null,
                ?int       $delaySeconds = null
    )
    {
        if ($senderNpcID && ($_ = CreatureList::getName($senderNpcID)))
            $this->sender = Lang::mail('mailBy', [$senderNpcID, $_]);

        if ($delaySeconds)
            $this->delay = Lang::mail('mailIn', [DateTime::formatTimeElapsed($delaySeconds * 1000)]);
    }

    public function attachItem(int $itemId, string $name, int $num, int $quality) : void
    {
        $this->attachments[] = new IconElement(Type::ITEM, $itemId, $name, $num, quality: $quality);
    }

    public function renderHeader() : string
    {
        return Lang::mail('mailDelivery', [$this->id, $this->sender, $this->delay]);
    }

    public function renderAttachments(int $lPad = 0, int &$iconIdxOffset = 0) : string
    {
        if (!$this->attachments)
            return '';

        $out = str_repeat(' ', $lPad) . '<table class="icontab icontab-box" style="padding-left:10px;">' . PHP_EOL;

        foreach ($this->attachments as $icon)
            $out .= $icon->renderContainer($lPad + 4, $iconIdxOffset, true);

        $out .= str_repeat(' ', $lPad) . '</table>' . PHP_EOL;
        $out .= str_repeat(' ', $lPad) . '<script type="text/javascript">//<![CDATA[' . PHP_EOL;

        foreach ($this->attachments as $icon)
            $out .= $icon->renderJS($lPad + 4);

        $out .= str_repeat(' ', $lPad) . '//]]></script>' . PHP_EOL;

        return $out;
    }
}

?>
