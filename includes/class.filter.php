<?php
if(!defined('AOWOW_REVISION'))
    die("illegal access");

abstract class Filter
{
    private static $pattern   = "/[^\p{L}0-9\s_\-\'\?\*]/ui";// delete any char not in unicode, number, hyphen, single quote or common wildcard
    private static $wildcards = ['*' => '%', '?' => '_'];
    private static $criteria  = ['cr', 'crs', 'crv'];       // [cr]iterium, [cr].[s]ign, [cr].[v]alue

    private        $fiData    = ['c' => [], 'v' =>[]];
    private        $query     = '';
    private        $form      = [];                         // unsanitized: needed to preselect form-fields
    private        $setCr     = [];                         // unsanitized: needed to preselect criteria

    public         $error     = false;                      // erronous search fields

    // parse the provided request into a usable format; recall self with GET-params if nessecary
    public function init()
    {
        // prefer POST over GET, translate to url
        if (!empty($_POST))
        {
            foreach ($_POST as $k => $v)
            {
                if (is_array($v))                           // array -> max depths:1
                {
                    if ($k == 'cr' && empty($v[0]))
                        continue;

                    $sub = [];
                    foreach ($v as $sk => $sv)
                    {
                        $sv = str_replace("'", "\'", stripslashes($sv));
                        $sub[$sk] = is_numeric($sv) ? (int)$sv : urlencode($sv);
                    }
                    if (!empty($sub) && in_array($k, Filter::$criteria))
                        $this->fiData['c'][$k] = $sub;
                    else if (!empty($sub))
                        $this->fiData['v'][$k] = $sub;
                }
                else                                        // stings and integer
                {
                    $v = str_replace("'", "\'", stripslashes($v));

                    if (in_array($k, Filter::$criteria))
                        $this->fiData['c'][$k] = is_numeric($v) ? (int)$v : urlencode($v);
                    else
                        $this->fiData['v'][$k] = is_numeric($v) ? (int)$v : urlencode($v);
                }
            }

            // create get-data
            $tmp = [];
            foreach (array_merge($this->fiData['c'], $this->fiData['v']) as $k => $v)
            {
                if ($v == '')
                    continue;
                else if (is_array($v))
                    $tmp[$k] = $k."=".implode(':', $v);
                else
                    $tmp[$k] = $k."=".$v;
            }

            // do get request
            $this->redirect(implode(';', $tmp));
        }
        // sanitize input and build sql
        else if (!empty($_GET['filter']))
        {
            $tmp = explode(';', $_GET['filter']);
            $cr = $crs = $crv = [];

            foreach (Filter::$criteria as $c)
            {
                foreach ($tmp as $i => $term)
                {
                    if (strpos($term, $c.'=') === 0)
                    {
                        $$c = explode(':', explode('=', $term)[1]);
                        $this->setCr[$c] = json_encode($$c, JSON_NUMERIC_CHECK);
                        unset($tmp[$i]);
                    }
                }
            }

            for ($i = 0; $i < max(count($cr), count($crv), count($crs)); $i++)
            {
                if (!isset($cr[$i])  || !isset($crs[$i]) || !isset($crv[$i]) ||
                    !intVal($cr[$i]) ||  $crs[$i] == ''  ||  $crv[$i] == '')
                {
                    $this->error = true;
                    continue;
                }

                $this->sanitize($crv[$i]);

                if ($crv[$i] != '')
                {
                    $this->fiData['c']['cr'][]  = intVal($cr[$i]);
                    $this->fiData['c']['crs'][] = $crs[$i];
                    $this->fiData['c']['crv'][] = $crv[$i];
                }
                else
                    $this->error = true;

            }

            foreach ($tmp as $v)
            {
                $w = explode('=', $v);

                if (strstr($w[1], ':'))
                {
                    $tmp2 = explode(':', $w[1]);

                    $this->form[$w[0]] = $tmp2;

                    array_walk($tmp2, function(&$v) { $v = intVal($v); });
                    $this->fiData['v'][$w[0]] = $tmp2;

                }
                else
                {
                    $this->form[$w[0]] = $w[1];

                    $this->sanitize($w[1]);

                    if ($w[1] != '')
                        $this->fiData['v'][$w[0]] = is_numeric($w[1]) ? (int)$w[1] : $w[1];
                    else
                        $this->error = true;
                }
            }

            return $this->fiData;
        }
    }

    public function buildQuery()
    {
        if (!empty($this->query))
            return $this->query;

        $parts = [];

        // values
        $parts = $this->createSQLForValues($this->fiData['v']);

        // criteria
        $c = &$this->fiData['c'];
        if (!empty($c))
        {
            if (is_array($c['cr']))
            {
                for ($i = 0; $i < count($c['cr']); $i++)
                    $parts[] = $this->createSQLForCriterium(array($c['cr'][$i], $c['crs'][$i], $c['crv'][$i]));
            }
            else
                $parts[] = $this->createSQLForCriterium(array($c['cr'], $c['crs'], $c['crv']));
        }

        $this->query = empty($parts) ? '' : '('.implode(empty($this->fiData['v']['ma']) ? ' AND ' : ' OR ', $parts).')';
        return $this->query;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function getSetCriteria()
    {
        if (!empty($this->setCr['cr']))
            return sprintf(Util::$setCriteriaString, $this->setCr['cr'], $this->setCr['crs'], $this->setCr['crv']);
        else
            return null;
    }

    // santas little helper..
    protected function int2Op($int)
    {
        switch ($int)
        {
            case 1: return '>';
            case 2: return '>=';
            case 3: return '=';
            case 4: return '<=';
            case 5: return '<';
            default: die('invalid op');
        }
    }

    protected function int2Bool($int)
    {
        switch ($int)
        {
            case 1: return true;
            case 2: return false;
            default: die('invalid op');
        }
    }

    protected function list2Mask($list)
    {
        $mask = 0x0;

        if (is_array($list))
        {
            foreach ($list as $itm)
                $mask += (1 << intVal($itm));
        }
        else
            $mask = (1 << intVal($list));

        return $mask;
    }

    private function sanitize(&$str)
    {
        $str = preg_replace(Filter::$pattern, '', trim($str));
        $str = strtr($str, Filter::$wildcards);
    }

    // if called with POST-data, convert to GET request and call self
    private function redirect($get)
    {
        header('Location: http://'.$_SERVER['SERVER_NAME'].str_replace('index.php', '', $_SERVER['PHP_SELF']).'?'.$_SERVER['QUERY_STRING'].'='.$get);
    }

    protected function createSQLForCommunity($cr)
    {
        switch ($cr[0])
        {
            case 14:                                        // has Comments [y|n]
                return '';                                  // IN / NOT IN (select Ids FROM aowow_comments ON type = X AND id Y and flags = valid)
            case 15:                                        // has Screenshots [y|n]
                return '';                                  // IN / NOT IN (select Ids FROM aowow_screenshots ON type = X AND id Y and flags = valid)
            case 16:                                        // has Videos [y|n]
                return '';                                  // IN / NOT IN (select Ids FROM aowow_videos ON type = X AND id Y and flags = valid)
        }
    }

    // apply Util::sqlEscape() and intVal() in the implementation of these
    abstract protected function createSQLForCriterium($cr);
    abstract protected function createSQLForValues($vl);
}


class AchievementListFilter extends Filter
{
    protected function createSQLForCriterium($cr)
    {
        if ($r = $this->createSQLForCommunity($cr))
            return $r;

        switch ($cr[0])
        {
            case 2:                                         // gives a reward [y|n]
                return 'reward_loc0 '.($this->int2Bool($cr[1]) ? '<>' : '=').' \'\'';
            case 3:                                         // reward text [str]
                return 'reward_loc'.User::$localeId.' LIKE \'%'.Util::sqlEscape($cr[2]).'%\'';
         // case 4:                                         // location [int]
             // return '';                                  // no plausible locations parsed yet
            case 5:                                         // first in series [y|n]
                return $this->int2Bool($cr[1]) ? '(series <> 0 AND (series & 0xFFFF0000) = 0)' : '(series & 0xFFFF0000) <> 0';
            case 6:                                         // last in series [y|n]
                return $this->int2Bool($cr[1]) ? '(series <> 0 AND (series & 0xFFFF) = 0)' : '(series & 0xFFFF) <> 0';
            case 7:                                         // part of series [y|n]
                return 'series '.($this->int2Bool($cr[1]) ? '<>' : '=').' 0';
            case 9:                                         // Id [op] [int]
                return 'id '.$this->int2Op($cr[1]).' '.intVal($cr[2]);
            case 10:                                        // Icon [str]
                return 'iconString LIKE \'%'.Util::sqlEscape($cr[2]).'%\'';
         // case 11:                                        // Related Event [int]
             // return '';                                  // >0:holidayId; -2323:any; -2324:none
            case 18:                                        // flags (staff only)
                if (User::isInGroup(U_GROUP_STAFF))
                    return 'flags &'.(1 << max($cr[1] - 1, 0)) ;
            default:
                return '1';
        }
    }

    protected function createSQLForValues($vl)
    {
        $parts = [];

        // name ex: +description, +rewards
        if (isset($vl['na']))
        {
            if (isset($vl['ex']) && $vl['ex'] == 'on')
                $parts[] = '(name_loc'.User::$localeId.' LIKE "%'.Util::sqlEscape($vl['na']).'%" OR description_loc'.User::$localeId.' LIKE "%'.Util::sqlEscape($vl['na']).'%" OR reward_loc'.User::$localeId.' LIKE "%'.Util::sqlEscape($vl['na']).'%")';
            else
                $parts[] = 'name_loc'.User::$localeId.' LIKE "%'.Util::sqlEscape($vl['na']).'%"';
        }

        // points min
        if (isset($vl['minpt']))
            $parts[] = 'points >= '.intVal($vl['minpt']);

        // points max
        if (isset($vl['maxpt']))
            $parts[] = 'points <= '.intVal($vl['maxpt']);

        // faction (side)
        if (isset($vl['si']))
        {
            if ($vl['si'] == 3)                             // both
                $parts[] = 'faction = '.intVal($vl['si']);
            else if ($vl['si'] > 0)                         // faction, inclusive both
                $parts[] = '(faction = 3 OR faction = '.intVal($vl['si']).')';
            else if ($vl['si'] < 0)                         // faction, exclusive both
                $parts[] = 'faction = '.intVal(-$vl['si']);

        }

        return $parts;
    }
}


// missing filter: "Available to Players"
class ItemsetListFilter extends Filter
{
    protected function createSQLForCriterium($cr)
    {
        if ($r = $this->createSQLForCommunity($cr))
            return $r;

        switch ($cr[0])
        {
            case 2:                                         // Id
                return 'id '.$this->int2Op($cr[1]).' '.intVal($cr[2]);
            case 3:                                         // pieces [int]
                return '(IF(item1 > 0, 1, 0) + IF(item2 > 0, 1, 0) + IF(item3 > 0, 1, 0) + IF(item4 > 0, 1, 0) + IF(item5 > 0, 1, 0) +
                         IF(item6 > 0, 1, 0) + IF(item7 > 0, 1, 0) + IF(item8 > 0, 1, 0) + IF(item9 > 0, 1, 0) + IF(item10 > 0, 1, 0)) ' .$this->int2Op($cr[1]).' '.intVal($cr[2]);
            case 4:                                         // bonustext [str]
                return 'bonusText_loc'.User::$localeId.' LIKE \'%'.Util::sqlEscape($cr[2]).'%\'';
            case 5:                                         // heroic [y|n]
                return 'heroic = '.($cr[1] == 1 ? '1' : '0');
            case 6:                                         // related event [int]
                $hId = intVal($cr[1]);                      // >0:holidayId; -2323:any; -2324:none
                if ($hId == -2323)
                    return 'holidayId <> 0';

                if ($hId == -2324)
                    $hId = 0;

                return 'holidayId = '.$hId;
         // case 12:                                        // available to players [y|n]
             // return '';                                  // ugh .. scan loot, quest and vendor templates and write to ?_itemset
            default:
                return '1';
        }
    }

    protected function createSQLForValues($vl)
    {
        $parts = [];

        //string (extended)
        if (isset($vl['na']))
            $parts[] = 'name_loc'.User::$localeId.' LIKE "%'.Util::sqlEscape($vl['na']).'%"';

        // quality [list]
        if (isset($vl['qu']))
            $parts[] = 'quality IN ('.implode(', ', (array)$vl['qu']).')';

        // type
        if (isset($vl['ty']))
            $parts[] = 'type IN ('.implode(', ', (array)$vl['ty']).')';

        // itemLevel min
        if (isset($vl['minle']))
            $parts[] = 'minLevel >= '.intVal($vl['minle']);

        // itemLevel max
        if (isset($vl['maxle']))
            $parts[] = 'maxLevel <= '.intVal($vl['maxle']);

        // reqLevel min
        if (isset($vl['minrl']))
            $parts[] = 'i.reqLevel <= '.intVal($vl['minle']);

        // reqLevel max
        if (isset($vl['maxrl']))
            $parts[] = 'i.reqLevel <= '.intVal($vl['maxle']);

        // class
        if (isset($vl['cl']))
            $parts[] = 'classMask & '.$this->list2Mask($vl['cl'] - 1);

        // tag
        if (isset($vl['ta']))
            $parts[] = 'contentGroup = '.$vl['ta'];

        return $parts;
    }
}

?>
