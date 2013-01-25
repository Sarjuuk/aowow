<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class Title extends BaseType
{
    public    $name       = [];
    public    $source     = [];

    protected $setupQuery = "SELECT * FROM ?_titles WHERE `Id` = ?";

    public function __construct($data)
    {
        parent::__construct($data);

        // post processing
        $this->name[GENDER_MALE] = Util::localizedString($this->template, 'male');
        if ($this->template['female_loc0'] || $this->template['female_loc'.User::$localeId])
            $this->name[GENDER_FEMALE] = Util::localizedString($this->template, 'female');

        // preparse sources
        if (!empty($this->template['source']))
        {
            $sources = explode(' ', $this->template['source']);
            foreach ($sources as $src)
            {
                $src = explode(':', $src);
                $this->source[$src[0]][] = $src[1];
            }
        }
    }

    public function getListviewData()
    {
        $data = array(
            'id'        => $this->Id,
            'name'      => $this->name[GENDER_MALE],
            'side'      => $this->template['side'],
            'gender'    => $this->template['gender'],
            'expansion' => $this->template['expansion'],
            'category'  => $this->template['category'],
            'source'    => $this->source
        );

        if (isset($this->name[GENDER_FEMALE]))
            $data['namefemale'] = $this->name[GENDER_FEMALE];

        return $data;
    }

    public function addGlobalsToJScript(&$gTitles)
    {
        $gTitles[$this->Id] = ['name' => Util::jsEscape($this->name[GENDER_MALE])];

        if (isset($this->name[GENDER_FEMALE]))
            $gTitles[$this->Id]['namefemale'] = Util::jsEscape($this->name[GENDER_FEMALE]);

        return true;
    }

    public function getHtmlizedName($gender = GENDER_MALE)
    {
        return str_replace('%s', '<span class="q0">&lt;Name&gt;</span>', $this->name[$gender]);
    }
}



class TitleList extends BaseTypeList
{
    protected $setupQuery = 'SELECT *, Id AS ARRAY_KEY FROM ?_titles WHERE [cond] ORDER BY Id ASC';
}

?>
