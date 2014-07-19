new Listview({
    template:'commentpreview',
    id: 'comments',
<?php
    echo !isset($params['parent']) ? "parent:'lv-generic'," : null;

    foreach ($params as $k => $v):
        if ($v[0] == '$'):
            echo $k.':'.substr($v, 1).',';
        elseif ($v):
            echo $k.":'".$v."',";
        endif;
    endforeach;
/*  data:
{
    id:{$curr.id},
    user:'{$curr.user|escape:"javascript"}',
    date:'{$curr.date|date_format:"%Y/%m/%d %H:%M:%S"}',
    preview:'{$curr.preview|escape:"javascript"}',
    subject:'{$curr.subject|escape:"javascript"}',
    rating: {$curr.rating},
    deleted:{$curr.deleted},
    type:{$curr.type},
    typeId:{$curr.typeId}
}
*/
?>
    data:<?php echo json_encode(array_values($data), JSON_NUMERIC_CHECK); ?>
});
