new Listview({
    template:'zone',
<?php
    echo !isset($params['id'])     ? "id:'zones',"          : null;
    echo !isset($params['name'])   ? "name:LANG.tab_zones," : null;
    echo !isset($params['parent']) ? "parent:'lv-generic'," : null;

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
