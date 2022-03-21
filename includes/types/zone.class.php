<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ZoneList extends BaseType
{
    use listviewHelper;

    public static   $type      = Type::ZONE;
    public static   $brickFile = 'zone';
    public static   $dataTable = '?_zones';

    protected       $queryBase = 'SELECT z.*, id AS ARRAY_KEY FROM ?_zones z';

    public function __construct($conditions = [], $miscData = null)
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

    // use if you JUST need the name
    public static function getName($id)
    {
        $n = DB::Aowow()->selectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8 FROM ?_zones WHERE id = ?d', $id );
        return Util::localizedString($n, 'name');
    }

    public function getListviewData()
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

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::ZONE][$this->id] = ['name' => $this->getField('name', true)];

        return $data;
    }

    public function renderTooltip() { }
}

?>
