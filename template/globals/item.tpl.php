var _ = g_items;
<?php
foreach ($vars as $id => $data):
    echo '_['.$id.']={' .
            'name_'.User::$localeString.':\''.Util::jsEscape($data['name']).'\',' .
            'icon:\''.Util::jsEscape($data['icon']).'\'' .
            (isset($data['quality']) ? ', quality:\''.$data['quality'].'\'' : null) .
        '};';
endforeach;

if ($extra):
    echo '_['.$extra['id'].'].tooltip_'.User::$localeString.' = \''.$extra['tooltip'].'\'';
    echo '_['.$extra['id'].'].spells_'.User::$localeString.' = {'.json_encode($extra['spells'], JSON_NUMERIC_CHECK).'};';
endif;
?>
