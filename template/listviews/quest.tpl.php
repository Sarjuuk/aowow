<?php
if (!empty($params['extraCols']) && strpos($params['extraCols'], '_')):
?>
var _ = [
    {
        id: 'reputation',
        after: 'rewards',
        name: LANG.rep,
        tooltip: LANG.tooltip_repgain,
        width: '8%',
        value: 'reputation'
    }
];
<?php
endif;
?>

new Listview({
    template:'quest',
<?php
    echo !isset($params['id'])     ? "id:'quests',"          : null;
    echo !isset($params['name'])   ? "name:LANG.tab_quests," : null;
    echo !isset($params['parent']) ? "parent:'lv-generic',"  : null;

    foreach ($params as $k => $v):
        if ($v[0] == '$'):
            echo $k.':'.substr($v, 1).',';
        elseif ($v):
            echo $k.":'".$v."',";
        endif;
    endforeach;
?>
    data:<?php echo json_encode(array_values($data), JSON_NUMERIC_CHECK); ?>
});
