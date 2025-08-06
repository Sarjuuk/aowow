<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ZoneList extends DBTypeList
{
    use listviewHelper;

    public static int    $type      = Type::ZONE;
    public static string $brickFile = 'zone';
    public static string $dataTable = '?_zones';

    protected string $queryBase = 'SELECT z.*, z.`id` AS ARRAY_KEY FROM ?_zones z';

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        foreach ($this->iterate() as &$_curTpl)
        {
            // unpack attunements
            $_curTpl['attunes'] = [];

            if ($_curTpl['attunementsN'])
            {
                foreach (explode(' ', $_curTpl['attunementsN']) as $req)
                {
                    $req = explode(':', $req);
                    if (!isset($_curTpl['attunes'][$req[0]]))
                        $_curTpl['attunes'][$req[0]] = [$req[1]];
                    else
                        $_curTpl['attunes'][$req[0]][] = $req[1];
                }
            }
            if ($_curTpl['attunementsH'])
            {
                foreach (explode(' ', $_curTpl['attunementsH']) as $req)
                {
                    $req = explode(':', $req);
                    if (!isset($_curTpl['attunes'][$req[0]]))
                        $_curTpl['attunes'][$req[0]] = [-$req[1]];
                    else
                        $_curTpl['attunes'][$req[0]][] = -$req[1];
                }
            }

            unset($_curTpl['attunementsN']);
            unset($_curTpl['attunementsH']);
        }
    }

    public function getListviewData() : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'        => $this->id,
                'category'  => $this->curTpl['category'],
                'territory' => $this->curTpl['faction'],
                'minlevel'  => $this->curTpl['levelMin'],
                'maxlevel'  => $this->curTpl['levelMax'],
                'name'      => $this->getField('name', true)
            );

            if ($_ = $this->curTpl['expansion'])
                $data[$this->id]['expansion'] = $_;

            if ($_ = $this->curTpl['type'])
                $data[$this->id]['instance'] = $_;

            if ($_ = $this->curTpl['maxPlayer'])
                $data[$this->id]['nplayers'] = $_;

            if ($_ = $this->curTpl['levelReq'])
                $data[$this->id]['reqlevel'] = $_;

            if ($_ = $this->curTpl['levelReqLFG'])
                $data[$this->id]['lfgReqLevel'] = $_;

            if ($_ = $this->curTpl['levelHeroic'])
                $data[$this->id]['heroicLevel'] = $_;
        }

        return $data;
    }

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::ZONE][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function renderTooltip() : ?string { return null; }
}

?>
