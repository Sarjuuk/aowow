new Listview({
    template:'genericmodel',
<?php
    echo !isset($params['id'])     ? "id:'same-model-as',"        : null;
    echo !isset($params['name'])   ? "name:LANG.tab_samemodelas," : null;
    echo !isset($params['parent']) ? "parent:'lv-generic',"       : null;
    /* genericlinktype: 'item|object', */

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
