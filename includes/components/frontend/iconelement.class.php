<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class IconElement
{
    public const SIZE_SMALL  = 0;
    public const SIZE_MEDIUM = 1;
    public const SIZE_LARGE  = 2;

    private const CREATE_ICON_TPL  = "\$WH.ge('%s%d').appendChild(%s.createIcon(%s));\n";

    private int    $idx    = 0;
    private string $href   = '';
    private bool   $noIcon = false;

    public readonly  string $quality;
    public readonly ?string $align;
    public readonly  int    $size;

    public function __construct(
        public  readonly  int        $type,
        public  readonly  int        $typeId,
        public  readonly  string     $text,
        public  readonly  int|string $num       = '',
        public  readonly  int|string $qty       = '',
                         ?string     $quality   = null,
                          int        $size      = self::SIZE_MEDIUM,
                          bool       $link      = true,
                          string     $url       = '',
                         ?string     $align     = null,
        public  readonly  string     $element   = 'icontab-icon',
        public           ?string     $extraText = null
    )
    {
        if (is_numeric($quality))
            $this->quality = 'q'.$quality;
        else if ($quality !== null)
            $this->quality = 'q';
        else
            $this->quality = '';

        if ($size < self::SIZE_SMALL || $size > self::SIZE_LARGE)
        {
            trigger_error('IconElement::__construct - invalid icon size '.$size.'. Normalied to 1 [small]', E_USER_WARNING);
            $this->size = self::SIZE_SMALL;
        }
        else
            $this->size = $size;

        if ($align && !in_array($align, ['left', 'right', 'center', 'justify']))
        {
            trigger_error('IconElement::__construct - unset invalid align value "'.$align.'".', E_USER_WARNING);
            $this->align = null;
        }
        else
            $this->align = $align;

        if ($type && $typeId && !Type::validateIds($type, $typeId))
        {
            $link = false;
            trigger_error('IconElement::__construct - invalid typeId '.$typeId.' for '.Type::getFileString($type).'.', E_USER_WARNING);
        }
        else if (!$type || !$typeId)
            $link = false;

        if ($link || $url)
            $this->href = $url ?: '?'.Type::getFileString($this->type).'='.$this->typeId;

        // see Spell/Tools having icon container but no actual icon and having to be inline with other IconElements
        $this->noIcon = !$typeId || !Type::hasIcon($type);
    }

    public function renderContainer(int $lpad = 0, int &$iconIdxOffset = 0, bool $rowWrap = false) : string
    {
        if (!$this->noIcon)
            $this->idx = ++$iconIdxOffset;

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $td = $dom->createElement('td');
        $th = $dom->createElement('th');

        if ($this->noIcon)                                  // see Spell/Tools or AchievementCriteria having no actual icon, but placeholder
        {
            $ul  = $dom->createElement('ul');
            $li  = $dom->createElement('li');
            $var = $dom->createElement('var', ' ');
            $li->appendChild($var);
            $ul->appendChild($li);
            $th->appendChild($ul);
        }
        else
        {
            $th->setAttribute('id', $this->element . $this->idx);
            if ($this->align)
                $th->setAttribute('align', $this->align);
        }

        if ($this->href)
            ($a = $dom->createElement('a', $this->text))->setAttribute('href', $this->href);
        else
            $a = $dom->createTextNode($this->text);

        if ($this->quality)
        {
            ($sp = $dom->createElement('span'))->setAttribute('class', $this->quality);
            $sp->appendChild($a);
            $td->appendChild($sp);
        }
        else
            $td->appendChild($a);

        // extraText can be HTML, so import it as a fragment
        if ($this->extraText)
        {
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML(' '.$this->extraText);
            $td->appendChild($fragment);
        }
        // only for objectives list..?
        if ($this->num && $this->size == self::SIZE_SMALL)
            $td->appendChild($dom->createTextNode(' ('.$this->num.')'));

        if ($rowWrap)
        {
            $tr = $dom->createElement('tr');
            $tr->appendChild($th);
            $tr->appendChild($td);
            $dom->append($tr);
        }
        else
            $dom->append($th, $td);

        return str_repeat(' ', $lpad) . $dom->saveHTML();
    }

    // $WH.ge('icontab-icon1').appendChild(g_spells.createIcon(40120, 1, '1-4', 0));

    public function renderJS(int $lpad = 0) : string
    {
        if ($this->noIcon)
            return '';

        $params = [$this->typeId, $this->size];
        if ($this->num || $this->qty)
            $params[] = is_numeric($this->num) ? $this->num : "'".$this->num."'";
        if ($this->qty)
            $params[] = is_numeric($this->qty) ? $this->qty : "'".$this->qty."'";

        return str_repeat(' ', $lpad) . sprintf(self::CREATE_ICON_TPL, $this->element, $this->idx, Type::getJSGlobalString($this->type), implode(', ', $params));
    }
}

?>
